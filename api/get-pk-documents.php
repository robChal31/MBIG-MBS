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
    ], JSON_PRETTY_PRINT);
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

// Escape email
$email = mysqli_real_escape_string($conn, $email);

// 1. Cek user
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found', null, 404);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Ambil semua PK dari user
$sql = "SELECT 
            p.id as pk_id, 
            p.no_pk, 
            p.start_at, 
            p.expired_at,
            db.id_draft as benefit_id,
            db.program as program_name,
            sp.name as pic_name,
            sp.jabatan as pic_position,
            sp.email as pic_email,
            sp.no_tlp as pic_phone
        FROM mp_user_pks as up
        INNER JOIN pk as p ON up.pk_id = p.id
        LEFT JOIN draft_benefit as db ON db.id_draft = p.benefit_id
        LEFT JOIN school_pic as sp ON sp.id_draft = db.id_draft
        WHERE up.user_id = $userId
        GROUP BY p.id
        ORDER BY p.expired_at DESC
    ";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse('error', 'Database query failed: ' . mysqli_error($conn), null, 500);
}

// 3. Susun response
$pkDocuments = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Tentukan status berdasarkan expired_at
    $currentDate = date('Y-m-d');
    $status = ($row['expired_at'] >= $currentDate) ? 'active' : 'expired';
    
    $pkDocuments[] = [
        'id' => (string)$row['pk_id'],
        'name' => $row['program_name'] ?? 'Program',
        'pk' => [
            'no_pk' => $row['no_pk'],
            'id_draft' => $row['benefit_id'],
            'start_at' => $row['start_at'],
            'expired_at' => $row['expired_at'],
            'status' => $status
        ],
        'pic' => [
            'name' => $row['pic_name'] ?? '-',
            'position' => $row['pic_position'] ?? '-',
            'email' => $row['pic_email'] ?? '-',
            'phone' => $row['pic_phone'] ?? '-'
        ]
    ];
}

// 4. Response sukses
$responseData = [
    'user' => [
        'id' => (string)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'institution_id' => $user['institution_id']
    ],
    'pk_documents' => $pkDocuments
];

jsonResponse('success', 'Data retrieved successfully', $responseData);

?>