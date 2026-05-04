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
    
    // 🔥 Hitung selisih bulan secara akurat (menggunakan diff, bukan year/month saja)
    $diff = $startDate->diff($currentDate);
    $totalMonths = ($diff->y * 12) + $diff->m;
    
    // 🔥 Jika selisih hari > 0 dan bulan sudah genap, tambah 1 bulan
    // Contoh: 2025-05-27 ke 2026-05-04 → 11 bulan 7 hari → masih 11 bulan
    // Contoh: 2025-05-27 ke 2026-05-28 → 12 bulan 1 hari → 12 bulan
    if ($diff->days >= 365 && $diff->m == 0 && $diff->d > 0) {
        $totalMonths = 12;
    }
    
    // Tentukan tahun ke berapa
    // Tahun ke-1: 0 - 11 bulan (belum genap 12 bulan)
    // Tahun ke-2: 12 - 23 bulan
    // Tahun ke-3: 24 - 35 bulan
    if ($totalMonths < 12) {
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
    } elseif ($totalMonths < 24) {
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
    } elseif ($totalMonths < 36) {
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

// Validasi email dan benefit_id
$email = $input['email'] ?? null;
$benefitId = $input['benefit_id'] ?? null;

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!$benefitId) {
    jsonResponse('error', 'benefit_id is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

// Escape input
$email = mysqli_real_escape_string($conn, $email);
$benefitId = mysqli_real_escape_string($conn, $benefitId);

// 1. Cek user di mp_users berdasarkan email
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'Not allowed', null, 403);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Cek apakah user memiliki benefit_id ini melalui pk
$checkBenefitSql = "SELECT p.id as pk_id, p.benefit_id, p.no_pk, p.start_at, p.expired_at
                        FROM mp_user_pks up
                        INNER JOIN pk p ON up.pk_id = p.id
                        INNER JOIN draft_benefit_list d ON p.benefit_id = d.id_draft
                        WHERE up.user_id = $userId AND d.id_benefit_list = '$benefitId'
                    ";

$benefitResult = mysqli_query($conn, $checkBenefitSql);

if (mysqli_num_rows($benefitResult) == 0) {
    jsonResponse('error', 'You do not have access to this benefit', null, 403);
}

$pkData = mysqli_fetch_assoc($benefitResult);

// 3. Ambil data benefit dari draft_benefit_list
$benefitSql = "SELECT 
                    d.id_draft,
                    d.id_benefit_list,
                    d.id_template,
                    d.benefit_name,
                    d.subbenefit,
                    d.description,
                    d.keterangan,
                    d.qty,
                    d.qty2,
                    d.qty3,
                    d.manualValue,
                    d.calcValue,
                    d.pelaksanaan,
                    d.type,
                    d.status,
                    d.isDeleted,
                    d.note,
                    d.updated_at,
                    dt.redeemable,
                    dt.subject as subject_benefit,
                    sb.group as subbenefit_group,
                    peg.code as event_group_code
                FROM draft_benefit_list d
                LEFT JOIN draft_template_benefit dt ON d.id_template = dt.id_template_benefit
                LEFT JOIN benefits b ON dt.benefit = b.name
                LEFT JOIN subbenefits sb ON sb.benefit_id = b.id AND sb.name = dt.subbenefit
                LEFT JOIN draft_benefit as db ON db.id_draft = d.id_draft
                LEFT JOIN programs as prog ON prog.name = db.program OR prog.code = db.program
                LEFT JOIN program_categories as pc ON pc.id = prog.program_category_id
                LEFT JOIN program_event_groups as peg ON peg.id = pc.program_event_group_id
                WHERE d.id_benefit_list = '$benefitId'
            ";

$result = mysqli_query($conn, $benefitSql);

if (mysqli_num_rows($result) == 0) {
    jsonResponse('error', 'Benefit not found', null, 404);
}

$benefitDetail = mysqli_fetch_assoc($result);

// 4. Ambil data usage dari benefit_usages (untuk perhitungan total)
$usageTotalSql = "SELECT 
                    COALESCE(SUM(qty1), 0) as total_qty1_used,
                    COALESCE(SUM(qty2), 0) as total_qty2_used,
                    COALESCE(SUM(qty3), 0) as total_qty3_used
                FROM benefit_usages 
                WHERE id_benefit_list = '$benefitId'";

$usageTotalResult = mysqli_query($conn, $usageTotalSql);
$usageTotals = mysqli_fetch_assoc($usageTotalResult);

// 5. Ambil detail usage untuk history
$usageDetailSql = "SELECT 
                        id,
                        id_benefit_list,
                        description,
                        qty1,
                        qty2,
                        qty3,
                        used_at,
                        redeem_code,
                        created_at,
                        updated_at
                    FROM benefit_usages 
                    WHERE id_benefit_list = '$benefitId'
                    ORDER BY used_at DESC
                    ";

$usageDetailResult = mysqli_query($conn, $usageDetailSql);
$usages = [];

if (mysqli_num_rows($usageDetailResult) > 0) {
    while ($usageRow = mysqli_fetch_assoc($usageDetailResult)) {
        $usages[] = [
            'id' => $usageRow['id'],
            'id_benefit_list' => $usageRow['id_benefit_list'],
            'description' => $usageRow['description'],
            'qty1' => (int)$usageRow['qty1'],
            'qty2' => (int)$usageRow['qty2'],
            'qty3' => (int)$usageRow['qty3'],
            'used_at' => $usageRow['used_at'],
            'redeem_code' => $usageRow['redeem_code'],
            'created_at' => $usageRow['created_at'],
            'updated_at' => $usageRow['updated_at']
        ];
    }
}

// 6. Hitung sisa quota per tahun
$remainingQty1 = (int)$benefitDetail['qty'] - (int)$usageTotals['total_qty1_used'];
$remainingQty2 = (int)$benefitDetail['qty2'] - (int)$usageTotals['total_qty2_used'];
$remainingQty3 = (int)$benefitDetail['qty3'] - (int)$usageTotals['total_qty3_used'];

// 7. 🔥 Tentukan quota aktif berdasarkan tanggal sekarang
$activeQuota = getActiveQuota(
    $pkData['start_at'],
    $pkData['expired_at'],
    $benefitDetail['qty'],
    $benefitDetail['qty2'],
    $benefitDetail['qty3'],
    $usageTotals['total_qty1_used'],
    $usageTotals['total_qty2_used'],
    $usageTotals['total_qty3_used']
);

// 8. Susun response
$responseData = [
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'institution_id' => $user['institution_id']
    ],
    'benefit' => [
        'id_benefit_list' => $benefitDetail['id_benefit_list'],
        'id_draft' => $benefitDetail['id_draft'],
        'event_group_code' => $benefitDetail['event_group_code'],
        'id_template' => $benefitDetail['id_template'],
        'benefit_name' => $benefitDetail['benefit_name'],
        'subbenefit' => $benefitDetail['subbenefit'],
        'description' => $benefitDetail['description'],
        'keterangan' => $benefitDetail['keterangan'],
        'pelaksanaan' => $benefitDetail['pelaksanaan'],
        'type' => $benefitDetail['type'],
        'redeemable' => (bool)$benefitDetail['redeemable'],
        'subbenefit_group' => $benefitDetail['subbenefit_group'],
        'subject_benefit' => $benefitDetail['subject_benefit'],
        'quota' => [
            'year1' => [
                'total' => (int)$benefitDetail['qty'],
                'used' => (int)$usageTotals['total_qty1_used'],
                'remaining' => $remainingQty1
            ],
            'year2' => [
                'total' => (int)$benefitDetail['qty2'],
                'used' => (int)$usageTotals['total_qty2_used'],
                'remaining' => $remainingQty2
            ],
            'year3' => [
                'total' => (int)$benefitDetail['qty3'],
                'used' => (int)$usageTotals['total_qty3_used'],
                'remaining' => $remainingQty3
            ]
        ],
        // 🔥 FIELD BARU: quota yang aktif saat ini
        'active_quota' => [
            'year' => $activeQuota['active_year'],
            'total' => $activeQuota['total_quota'],
            'used' => $activeQuota['used_quota'],
            'available' => $activeQuota['available_quota'],
            'is_expired' => $activeQuota['is_expired']
        ]
    ],
    'pk' => [
        'id' => $pkData['pk_id'],
        'benefit_id' => $pkData['benefit_id'],
        'no_pk' => $pkData['no_pk'],
        'start_at' => $pkData['start_at'],
        'expired_at' => $pkData['expired_at']
    ],
    'usages' => $usages
];

jsonResponse('success', 'Benefit retrieved successfully', $responseData);
?>