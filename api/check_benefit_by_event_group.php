<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

include('../db_con.php');
$config = require('../config.php');

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
    
    // Hitung selisih bulan dari start date
    $monthDiff = ($currentDate->format('Y') - $startDate->format('Y')) * 12 + 
                 ($currentDate->format('n') - $startDate->format('n'));
    
    // Tentukan tahun ke berapa
    if ($monthDiff < 12) {
        // Tahun ke-1
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
    } elseif ($monthDiff < 24) {
        // Tahun ke-2
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
    } elseif ($monthDiff < 36) {
        // Tahun ke-3
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
    
    // Melebihi 3 tahun
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

// Validasi email dan benefit_type
$email = $input['email'] ?? null;
$benefitType = $input['benefit_type'] ?? null;
$subject = $input['subject'] ?? null;

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!$benefitType) {
    jsonResponse('error', 'benefit_type is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

// Escape input
$email = mysqli_real_escape_string($conn, $email);
$benefitType = mysqli_real_escape_string($conn, $benefitType);

// 1. Cek user di mp_users berdasarkan email
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found', null, 404);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Ambil semua pk_id dari mp_user_pks berdasarkan user_id 
//    dan join ke pk untuk cek expired_at (hanya yang belum expired)
$getPksSql = "SELECT up.pk_id, p.expired_at, p.benefit_id, p.no_pk, p.start_at
              FROM mp_user_pks up
              INNER JOIN pk p ON up.pk_id = p.id
              WHERE up.user_id = $userId 
                AND p.expired_at > CURDATE()";

$pksResult = mysqli_query($conn, $getPksSql);

if (mysqli_num_rows($pksResult) == 0) {
    jsonResponse('success', 'No active PK found for this user', [
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

// 3. Ambil semua benefit dari pk -> draft_benefit_list -> draft_template_benefit -> benefits -> subbenefits
//    yang match dengan benefit_type
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
        WHERE p.id IN ($pkIdsString)
            AND (d.isDeleted = 0 OR d.isDeleted IS NULL)
            AND sb.group = '$benefitType'";

// Tambahkan filter subject jika ada
if (!empty($subject)) {
    $sql .= " AND (dt.subject IS NULL OR dt.subject = '' OR dt.subject = '$subject')";
}

$result = mysqli_query($conn, $sql);

// 4. Loop semua hasil, filter yang available quota > 0, dan susun response
$benefitsList = [];
while ($benefit = mysqli_fetch_assoc($result)) {
    $pkId = $benefit['pk_id'];
    $pkInfo = $pkDataList[$pkId];
    
    // Cek expired
    $expired = strtotime($pkInfo['expired_at']) < time();
    
    // Ambil usage per qty
    $usageSql = "SELECT 
                    COALESCE(SUM(qty1), 0) as total_qty1_used,
                    COALESCE(SUM(qty2), 0) as total_qty2_used,
                    COALESCE(SUM(qty3), 0) as total_qty3_used
                FROM benefit_usages 
                WHERE id_benefit_list = {$benefit['id_benefit_list']}";
    
    $usageResult = mysqli_query($conn, $usageSql);
    $usage = mysqli_fetch_assoc($usageResult);
    
    // Hitung sisa quota per tahun
    $remainingQty1 = (int)$benefit['qty'] - (int)$usage['total_qty1_used'];
    $remainingQty2 = (int)$benefit['qty2'] - (int)$usage['total_qty2_used'];
    $remainingQty3 = (int)$benefit['qty3'] - (int)$usage['total_qty3_used'];
    
    // Hitung active quota berdasarkan tanggal
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
    
    // 🔥 SKIP jika available quota = 0
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
            'quota' => [
                'year1' => [
                    'total' => (int)$benefit['qty'],
                    'used' => (int)$usage['total_qty1_used'],
                    'remaining' => $remainingQty1
                ],
                'year2' => [
                    'total' => (int)$benefit['qty2'],
                    'used' => (int)$usage['total_qty2_used'],
                    'remaining' => $remainingQty2
                ],
                'year3' => [
                    'total' => (int)$benefit['qty3'],
                    'used' => (int)$usage['total_qty3_used'],
                    'remaining' => $remainingQty3
                ]
            ],
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

// Cek apakah ada benefit yang tersedia
if (count($benefitsList) == 0) {
    jsonResponse('success', 'No active benefit with available quota', [
        'hasBenefit' => false,
        'message' => 'No active benefit with available quota found',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
}

// 5. Siapkan response data
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