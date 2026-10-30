<?php
error_reporting(0);
ini_set('display_errors', 0);
include('../db_con.php');
$config = require('../config.php');

header('Access-Control-Allow-Origin: ' . '*');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Helper response
function jsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    ]);
    exit();
}

// ===== Sanitasi string: decode HTML entity + hapus karakter kontrol =====
function sanitizeString($value) {
    if ($value === null) return null;
    $value = (string)$value;

    // 1) Decode entity HTML → karakter asli (&#39; → ', &amp; → &, dst)
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // 2) Hapus karakter kontrol (kecuali \t \n \r) & zero-width chars
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]|\x{200B}|\x{FEFF}/u', '', $value);

    // 3) Normalisasi whitespace berlebih (opsional, aman untuk JSON)
    $value = preg_replace('/[ \t]+/', ' ', $value);

    return trim($value);
}

// ===== Sanitasi rekursif untuk array/objek =====
function sanitizeData($data) {
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[$k] = sanitizeData($v);
        }
        return $data;
    }
    if (is_string($data)) {
        return sanitizeString($data);
    }
    return $data; // int, float, bool, null dibiarkan
}

// ===== Validasi token =====
$expectedToken = $config['mbs_api_key'] ?? null;
if (!$expectedToken) {
    jsonResponse('error', 'API key not configured', null, 500);
}

$receivedToken = $_SERVER['HTTP_X_API_KEY'] ?? null;
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
    if (!$receivedToken) {
        $receivedToken = $input['token'] ?? null;
    }
} else {
    $input = $_GET;
}

if (!$receivedToken) {
    jsonResponse('error', 'API token is required', null, 401);
}
if ($receivedToken !== $expectedToken) {
    jsonResponse('error', 'Invalid API token', null, 401);
}

// ===== Validasi redeem_code =====
$redeemCode = $input['redeem_code'] ?? null;
if (!$redeemCode || trim($redeemCode) === '') {
    jsonResponse('error', 'redeem_code is required', null, 400);
}
$redeemCode = mysqli_real_escape_string($conn, trim($redeemCode));

/*
 * ============================================================
 * QUERY UTAMA
 * benefit_usages -> draft_benefit_list -> draft_benefit
 *                -> schools -> segments            (data sekolah + segment)
 *                -> `user`                         (data user / EC)
 *                -> programs -> program_categories -> program_event_groups
 * ============================================================
 */
$sql = "SELECT
            /* ---- benefit_usages ---- */
            bu.id                 AS usage_id,
            bu.redeem_code,
            bu.description        AS usage_description,
            bu.qty1, bu.qty2, bu.qty3,
            bu.used_at,
            bu.created_at         AS usage_created_at,

            /* ---- draft_benefit_list ---- */
            dbl.id_benefit_list,
            dbl.benefit_name,
            dbl.subbenefit,
            dbl.description       AS benefit_description,

            /* ---- draft_benefit ---- */
            db.id_draft,
            db.school_name        AS draft_school_name,
            db.segment            AS draft_segment,
            db.program            AS draft_program,
            db.year               AS draft_year,
            db.id_user            AS draft_user_id,
            db.id_ec              AS draft_ec_id,

            /* ---- schools ---- */
            s.id                  AS school_id,
            IFNULL(s.name, db.school_name) AS school_name,
            s.address             AS school_address,
            s.phone               AS school_phone,
            s.segment             AS school_segment_id,
            s.ec_id               AS school_ec_id,

            /* ---- segments ---- */
            seg.id                AS segment_id,
            seg.segment           AS segment_name,

            /* ---- user (EC) ---- */
            u.id_user             AS user_id,
            u.username            AS user_username,
            u.generalname         AS user_generalname,
            u.sa_email            AS user_email,
            u.role                AS user_role,

            /* ---- programs ---- */
            p.id                  AS program_id,
            p.code                AS program_code,
            p.name                AS program_name,

            /* ---- program_categories ---- */
            pc.id                 AS program_category_id,
            pc.name               AS program_category_name

        FROM benefit_usages bu
        INNER JOIN draft_benefit_list dbl ON dbl.id_benefit_list = bu.id_benefit_list
        INNER JOIN draft_benefit      db  ON db.id_draft          = dbl.id_draft
        LEFT  JOIN schools            s   ON s.id               = db.school_name
        LEFT  JOIN segments           seg ON seg.id               = s.segment
        LEFT  JOIN `user`             u   ON u.id_user            = db.id_ec
        LEFT  JOIN programs           p   ON (p.name = db.program OR p.code = db.program)
        LEFT  JOIN program_categories pc  ON pc.id                = p.program_category_id
        WHERE bu.redeem_code = '$redeemCode'
        ORDER BY bu.id DESC
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse('error', 'Query failed: ' . mysqli_error($conn), null, 500);
}

if (mysqli_num_rows($result) === 0) {
    jsonResponse('error', 'Redeem code not found', null, 404);
}

$row = mysqli_fetch_assoc($result);

// ===== Susun response =====
$responseData = [
    'redeem_code' => $row['redeem_code'],
    'usage' => [
        'id'              => (int)$row['usage_id'],
        'id_benefit_list' => (int)$row['id_benefit_list'],
        'benefit_name'    => $row['benefit_name'],
        'description'     => $row['usage_description'],
        'qty1'            => (int)$row['qty1'],
        'qty2'            => (int)$row['qty2'],
        'qty3'            => (int)$row['qty3'],
        'used_at'         => $row['used_at'],
        'created_at'      => $row['usage_created_at'],
    ],
    'school' => [
        'id'   => $row['school_id'] !== null ? (int)$row['school_id'] : null,
        'name' => $row['school_name'] ?? $row['draft_school_name'],
    ],
    'id_draft' => (int)$row['id_draft'],
    'segment'  => $row['segment_name'] ?? $row['draft_segment'],
    'ec_name'  => $row['user_generalname'],
    'program'  => [
        'id'   => $row['program_id'] !== null ? (int)$row['program_id'] : null,
        'code' => $row['program_code'],
        'name' => $row['program_name'] ?? $row['draft_program'],
        'program_category_name' => $row['program_category_name'],
    ]
];

// 🔥 SANITASI SEMUA STRING DI RESPONSE
$responseData = sanitizeData($responseData);

jsonResponse('success', 'Adopted program retrieved successfully', $responseData);