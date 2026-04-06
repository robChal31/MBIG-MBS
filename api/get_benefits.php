<?php
header('Access-Control-Allow-Origin: http://localhost:3000'); // Ganti dengan domain Next.js lo
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

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

// Escape email untuk query
$email = mysqli_real_escape_string($conn, $email);

// 1. Cek user di mp_users berdasarkan email
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found in mp_users', null, 404);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Ambil semua pk_id dari mp_user_pks berdasarkan user_id
$getPksSql = "SELECT pk_id FROM mp_user_pks WHERE user_id = $userId";
$pksResult = mysqli_query($conn, $getPksSql);

if (mysqli_num_rows($pksResult) == 0) {
    jsonResponse('error', 'No PK found for this user', null, 404);
}

$pkIds = [];
while ($row = mysqli_fetch_assoc($pksResult)) {
    $pkIds[] = $row['pk_id'];
}

// 3. Ambil data dengan JOIN langsung
$pkIdsString = implode(',', $pkIds);
$sql = "SELECT 
            p.id as pk_id, p.benefit_id, p.no_pk, p.start_at, p.expired_at, d.id_draft, d.id_benefit_list, d.id_template, d.benefit_name, d.subbenefit, d.description, d.keterangan, d.qty, d.qty2, d.qty3, d.manualValue, dt.subject,
            d.calcValue, d.pelaksanaan, d.type, d.status, d.isDeleted, d.note, d.updated_at, dt.redeemable, sb.group as subbenefit_group
        FROM mp_user_pks up
        INNER JOIN pk p ON up.pk_id = p.id
        INNER JOIN draft_benefit_list d ON p.benefit_id = d.id_draft
        LEFT JOIN draft_template_benefit as dt on dt.id_template_benefit = d.id_template
        LEFT JOIN benefits as b on dt.benefit = b.name
        LEFT JOIN subbenefits as sb on sb.benefit_id = b.id and sb.name = dt.subbenefit
        WHERE up.user_id = $userId
        ORDER BY p.benefit_id, d.id_benefit_list
    ";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    jsonResponse('error', 'No benefits found for this user', null, 404);
}

// 4. Susun response - GROUP BY benefit_id
$responseData = [
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'institution_id' => $user['institution_id']
    ],
    'benefits' => [] // Array of benefits grouped by benefit_id
];

$benefitsMap = [];

while ($row = mysqli_fetch_assoc($result)) {
    $benefitId = $row['benefit_id'];
    
    // If benefit_id not exists in map, create it
    if (!isset($benefitsMap[$benefitId])) {
        $benefitsMap[$benefitId] = [
            'benefit_id' => $benefitId,
            'benefit_detail' => [], // Ini akan jadi array of benefit items
            'related_pks' => []
        ];
    }
    
    // Add benefit detail ke array (bisa multiple items per benefit_id)
    $benefitItem = [
        'id_benefit_list' => $row['id_benefit_list'],
        'id_draft' => $row['id_draft'],
        'id_template' => $row['id_template'],
        'benefit_name' => $row['benefit_name'],
        'subbenefit' => $row['subbenefit'],
        'description' => $row['description'],
        'keterangan' => $row['keterangan'],
        'qty' => $row['qty'],
        'qty2' => $row['qty2'],
        'qty3' => $row['qty3'],
        'manualValue' => $row['manualValue'],
        'calcValue' => $row['calcValue'],
        'pelaksanaan' => $row['pelaksanaan'],
        'type' => $row['type'],
        'status' => $row['status'],
        'isDeleted' => $row['isDeleted'],
        'note' => $row['note'],
        'updated_at' => $row['updated_at'],
        'redeemable' => $row['redeemable'],
        'subbenefit_group' => $row['subbenefit_group'],
        'subject_benefit' => $row['subject']
    ];
    
    // Cek duplikat benefit_detail berdasarkan id_benefit_list
    $exists = false;
    foreach ($benefitsMap[$benefitId]['benefit_detail'] as $existing) {
        if ($existing['id_benefit_list'] == $row['id_benefit_list']) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $benefitsMap[$benefitId]['benefit_detail'][] = $benefitItem;
    }
    
    // Add PK to benefit (cek duplikat berdasarkan pk_id)
    $pkExists = false;
    foreach ($benefitsMap[$benefitId]['related_pks'] as $existingPk) {
        if ($existingPk['id'] == $row['pk_id']) {
            $pkExists = true;
            break;
        }
    }
    
    if (!$pkExists) {
        $benefitsMap[$benefitId]['related_pks'][] = [
            'id' => $row['pk_id'],
            'benefit_id' => $row['benefit_id'],
            'no_pk' => $row['no_pk'],
            'start_at' => $row['start_at'],
            'expired_at' => $row['expired_at']
        ];
    }
}

// Convert map to array
$responseData['benefits'] = array_values($benefitsMap);

jsonResponse('success', 'Data retrieved successfully', $responseData);
?>