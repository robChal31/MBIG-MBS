<?php

include('../db_con.php');
$config = require('../config.php');

header('Access-Control-Allow-Origin: ' . $config['mp_url']);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');


// Fungsi untuk response JSON
function jsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Fungsi untuk menentukan quota aktif berdasarkan tanggal
function getActiveQuota($startAt, $expiredAt, $qty1, $qty2, $qty3, $usedQty1, $usedQty2, $usedQty3) {
    $startDate = new DateTime($startAt);
    $currentDate = new DateTime();
    $expiredDate = new DateTime($expiredAt);
    
    // Jika sudah expired
    if ($currentDate > $expiredDate) {
        return [
            'active_year' => null,
            'total_quota' => 0,
            'used_quota' => 0,
            'available_quota' => 0,
            'is_expired' => true
        ];
    }
    
    // Hitung selisih bulan secara akurat
    $diff = $startDate->diff($currentDate);
    $totalMonths = ($diff->y * 12) + $diff->m;
    
    if ($diff->days >= 365 && $diff->m == 0 && $diff->d > 0) {
        $totalMonths = 12;
    }
    
    if ($totalMonths < 12) {
        $totalQuota = (int)$qty1;
        $usedQuota = (int)$usedQty1;
        $availableQuota = $totalQuota - $usedQuota;
        return [
            'active_year' => 1,
            'total_quota' => $totalQuota,
            'used_quota' => $usedQuota,
            'available_quota' => $availableQuota > 0 ? $availableQuota : 0,
            'is_expired' => false
        ];
    } elseif ($totalMonths < 24) {
        $totalQuota = (int)$qty2;
        $usedQuota = (int)$usedQty2;
        $availableQuota = $totalQuota - $usedQuota;
        return [
            'active_year' => 2,
            'total_quota' => $totalQuota,
            'used_quota' => $usedQuota,
            'available_quota' => $availableQuota > 0 ? $availableQuota : 0,
            'is_expired' => false
        ];
    } elseif ($totalMonths < 36) {
        $totalQuota = (int)$qty3;
        $usedQuota = (int)$usedQty3;
        $availableQuota = $totalQuota - $usedQuota;
        return [
            'active_year' => 3,
            'total_quota' => $totalQuota,
            'used_quota' => $usedQuota,
            'available_quota' => $availableQuota > 0 ? $availableQuota : 0,
            'is_expired' => false
        ];
    }
    
    return [
        'active_year' => null,
        'total_quota' => 0,
        'used_quota' => 0,
        'available_quota' => 0,
        'is_expired' => true
    ];
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed', null, 405);
}

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse('error', 'Invalid JSON input', null, 400);
}

// Validasi token
$receivedToken = $input['token'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null;
$expectedToken = $config['mbs_api_key'] ?? null;

if (!$expectedToken) {
    jsonResponse('error', 'API key not configured', null, 500);
}

if (!$receivedToken) {
    jsonResponse('error', 'API token is required', null, 401);
}

if ($receivedToken !== $expectedToken) {
    jsonResponse('error', 'Invalid API token', null, 401);
}

// Validasi email
$email = $input['email'] ?? null;
$benefitType = $input['benefit_type'] ?? null;
$subject = $input['subject'] ?? null;
$event_group_code = $input['event_group'] ?? '';

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

// 🔥 FIX: Jika benefit_type null atau kosong, set ke array kosong
$benefitTypes = [];
if (!empty($benefitType)) {
    $benefitTypes = explode(';', $benefitType);
    $benefitTypes = array_map('trim', $benefitTypes);
}

// 🔥 FIX: Subject handling
$subjects = [];
if (!empty($subject)) {
    $subjects = explode(';', $subject);
    $subjects = array_map('trim', $subjects);
}

// Escape input
$email = mysqli_real_escape_string($conn, $email);

// 1. Cek user
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found', null, 404);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Ambil pk aktif
$getPksSql = "SELECT up.pk_id, p.expired_at, p.benefit_id, p.no_pk, p.start_at
              FROM mp_user_pks up
              INNER JOIN pk p ON up.pk_id = p.id
              WHERE up.user_id = $userId 
                AND p.expired_at > CURDATE()";

$pksResult = mysqli_query($conn, $getPksSql);

if (mysqli_num_rows($pksResult) == 0) {
    jsonResponse('success', 'No active PK found', [
        'hasBenefit' => false,
        'message' => 'No active benefit packages found'
    ]);
}

$pkIds = [];
$pkDataList = [];
while ($row = mysqli_fetch_assoc($pksResult)) {
    $pkIds[] = $row['pk_id'];
    $pkDataList[$row['pk_id']] = $row;
}
$pkIdsString = implode(',', $pkIds);

// 🔥 FIX: Build benefit type conditions - lebih simpel dan aman
$benefitTypeCondition = "";
if (!empty($benefitTypes)) {
    $conditions = [];
    foreach ($benefitTypes as $type) {
        $type = mysqli_real_escape_string($conn, $type);
        $conditions[] = "sb.group = '$type'";
    }
    // ✅ Tambahkan Kolektif Offline sebagai OR tambahan
    $conditions[] = "sb.group = 'Kolektif Offline'";
    $benefitTypeCondition = " AND (" . implode(' OR ', $conditions) . ")";
} else {
    // Jika benefit_type kosong, hanya cari Kolektif Offline
    $benefitTypeCondition = " AND sb.group = 'Kolektif Offline'";
}

// 🔥 FIX: Subject conditions
$subjectCondition = "";
if (!empty($subjects)) {
    $subjectConditions = [];
    foreach ($subjects as $subj) {
        $subj = mysqli_real_escape_string($conn, $subj);
        $subjectConditions[] = "(dt.subject IS NULL OR dt.subject = '' OR dt.subject = '$subj')";
    }
    $subjectCondition = " AND (" . implode(' OR ', $subjectConditions) . ")";
}

// 🔥 FIX: event_group_code condition - lebih aman
$eventGroupCondition = "";
if (!empty($event_group_code)) {
    $event_group_code = mysqli_real_escape_string($conn, $event_group_code);
    $eventGroupCondition = " AND peg.code = '$event_group_code'";
}

// 3. Query utama
$sql = "SELECT 
            p.id as pk_id,
            p.benefit_id as pk_benefit_id,
            p.start_at as pk_start_at,
            p.expired_at as pk_expired_at,
            d.id_benefit_list,
            d.id_draft,
            d.id_template,
            d.benefit_name,
            d.subbenefit,
            d.description,
            d.keterangan,
            d.qty,
            d.qty2,
            d.qty3,
            d.pelaksanaan,
            d.type,
            d.status,
            d.isDeleted,
            d.updated_at,
            dt.redeemable,
            dt.subject as subject_benefit,
            b.id as benefit_table_id,
            b.name as benefit_table_name,
            sb.id as subbenefit_id,
            sb.name as subbenefit_name,
            sb.group as subbenefit_group
        FROM pk p
        INNER JOIN draft_benefit_list d ON p.benefit_id = d.id_draft
        LEFT JOIN draft_template_benefit dt ON d.id_template = dt.id_template_benefit
        LEFT JOIN benefits b ON dt.benefit = b.name
        LEFT JOIN subbenefits sb ON sb.benefit_id = b.id AND sb.name = dt.subbenefit
        LEFT JOIN draft_benefit as db ON db.id_draft = d.id_draft
        LEFT JOIN programs as prog ON prog.name = db.program OR prog.code = db.program
        LEFT JOIN program_categories as pc ON pc.id = prog.program_category_id
        LEFT JOIN program_event_groups as peg ON peg.id = pc.program_event_group_id
        WHERE p.id IN ($pkIdsString)
            AND (d.isDeleted = 0 OR d.isDeleted IS NULL)
            $benefitTypeCondition
            $subjectCondition
            $eventGroupCondition";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse('error', 'Database error: ' . mysqli_error($conn), null, 500);
}

// 4. Loop hasil
$benefitsList = [];
while ($benefit = mysqli_fetch_assoc($result)) {
    $pkId = $benefit['pk_id'];
    $pkInfo = $pkDataList[$pkId];
    
    $expired = strtotime($pkInfo['expired_at']) < time();
    
    // Ambil usage
    $usageSql = "SELECT 
                    COALESCE(SUM(qty1), 0) as total_qty1_used,
                    COALESCE(SUM(qty2), 0) as total_qty2_used,
                    COALESCE(SUM(qty3), 0) as total_qty3_used
                FROM benefit_usages 
                WHERE id_benefit_list = {$benefit['id_benefit_list']}";
    
    $usageResult = mysqli_query($conn, $usageSql);
    $usage = mysqli_fetch_assoc($usageResult);
    
    // Hitung active quota
    $activeQuota = getActiveQuota(
        $benefit['pk_start_at'],
        $benefit['pk_expired_at'],
        $benefit['qty'],
        $benefit['qty2'],
        $benefit['qty3'],
        $usage['total_qty1_used'],
        $usage['total_qty2_used'],
        $usage['total_qty3_used']
    );
    
    // SKIP jika available quota = 0
    if ($activeQuota['available_quota'] <= 0) {
        continue;
    }
    
    $benefitsList[] = [
        'benefit' => [
            'id' => $benefit['id_benefit_list'],
            'id_draft' => $benefit['id_draft'],
            'name' => $benefit['benefit_name'],
            'subbenefit' => $benefit['subbenefit'],
            'description' => $benefit['description'],
            'type' => $benefit['type'],
            'redeemable' => $benefit['redeemable'] == 1,
            'active_quota' => [
                'year' => $activeQuota['active_year'],
                'total' => $activeQuota['total_quota'],
                'used' => $activeQuota['used_quota'],
                'available' => $activeQuota['available_quota'],
                'is_expired' => $activeQuota['is_expired']
            ]
        ],
        'pk' => [
            'id' => $pkInfo['pk_id'],
            'benefit_id' => $pkInfo['benefit_id'],
            'no_pk' => $pkInfo['no_pk'],
            'start_at' => $pkInfo['start_at'],
            'expired_at' => $pkInfo['expired_at'],
            'is_expired' => $expired
        ],
        'subbenefit_info' => [
            'id' => $benefit['subbenefit_id'],
            'group' => $benefit['subbenefit_group'],
            'name' => $benefit['subbenefit_name'],
            'benefit_name' => $benefit['benefit_table_name']
        ]
    ];
}

if (count($benefitsList) == 0) {
    jsonResponse('success', 'No active benefit with available quota', [
        'hasBenefit' => false,
        'message' => 'No benefit available',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
}

$responseData = [
    'hasBenefit' => true,
    'total_benefits' => count($benefitsList),
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'institution_id' => $user['institution_id']
    ],
    'benefits' => $benefitsList
];

jsonResponse('success', 'Benefits found', $responseData);
?>