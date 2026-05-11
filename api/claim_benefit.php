<?php

include('../db_con.php');
$config = require('../config.php');

header('Access-Control-Allow-Origin: ' . $config['mp_url']);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

function jsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function generateRedeemCodeFromAPI($eventId, $qty, $discount = 100) {
    $url = 'https://hadiryuk.id/api/createpromo';
    $data = array(
        'id_event' => $eventId,
        'quota' => $qty,
        'diskon' => $discount,
        'is_mp' => 1
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response_api = curl_exec($ch);
    $redeem_code = '';
    if($response_api === false) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error_msg);
    } else {
        $redeem_code = str_replace('"', '',$response_api);
    }
    curl_close($ch);

    return $redeem_code;
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

// Validasi input
$benefitId = $input['benefit_id'] ?? null;
$benefitDraftId = $input['benefit_draft_id'] ?? null;
$pkId = $input['pk_id'] ?? null;
$eventId = $input['event_id'] ?? null;
$eventTitle = $input['event_title'] ?? null;
$qty = (int)($input['qty'] ?? 0);
$year = (int)($input['year'] ?? 0);
$description = $input['description'] ?? '';
$email = $input['email'] ?? null;

if (!$benefitId || !$benefitDraftId || !$pkId || !$eventId || $qty <= 0) {
    jsonResponse('error', 'Missing required fields', null, 400);
}

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

// Escape input
$benefitId = mysqli_real_escape_string($conn, $benefitId);
$benefitDraftId = mysqli_real_escape_string($conn, $benefitDraftId);
$pkId = mysqli_real_escape_string($conn, $pkId);
$eventId = mysqli_real_escape_string($conn, $eventId);
$eventTitle = mysqli_real_escape_string($conn, $eventTitle);
$description = mysqli_real_escape_string($conn, $description);
$email = mysqli_real_escape_string($conn, $email);

// 1. Cek user di mp_users berdasarkan email
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found', null, 404);
}

$user = mysqli_fetch_assoc($userResult);
$userId = $user['id'];

// 2. Cek apakah user memiliki benefit ini melalui pk
$checkBenefitSql = "SELECT p.id as pk_id, p.benefit_id, p.no_pk, p.start_at, p.expired_at
                    FROM mp_user_pks up
                    INNER JOIN pk p ON up.pk_id = p.id
                    INNER JOIN draft_benefit_list d ON p.benefit_id = d.id_draft
                    WHERE up.user_id = $userId AND d.id_benefit_list = '$benefitId'";

$benefitResult = mysqli_query($conn, $checkBenefitSql);

if (mysqli_num_rows($benefitResult) == 0) {
    jsonResponse('error', 'You do not have access to this benefit', null, 403);
}

$pkData = mysqli_fetch_assoc($benefitResult);

// 3. Cek expired
$expired = strtotime($pkData['expired_at']) < time();
if ($expired) {
    jsonResponse('error', 'Benefit has expired', null, 400);
}

// 4. Cek sisa quota berdasarkan tahun
$usageSql = "SELECT 
                COALESCE(SUM(qty1), 0) as total_qty1_used,
                COALESCE(SUM(qty2), 0) as total_qty2_used,
                COALESCE(SUM(qty3), 0) as total_qty3_used
            FROM benefit_usages 
            WHERE id_benefit_list = '$benefitId'";

$usageResult = mysqli_query($conn, $usageSql);
$usageTotals = mysqli_fetch_assoc($usageResult);

// Ambil quota dari draft_benefit_list
$quotaSql = "SELECT qty, qty2, qty3 FROM draft_benefit_list WHERE id_benefit_list = '$benefitId'";
$quotaResult = mysqli_query($conn, $quotaSql);
$quota = mysqli_fetch_assoc($quotaResult);

$totalQuota = 0;
$usedQuota = 0;

if ($year == 1) {
    $totalQuota = (int)$quota['qty'];
    $usedQuota = (int)$usageTotals['total_qty1_used'];
} elseif ($year == 2) {
    $totalQuota = (int)$quota['qty2'];
    $usedQuota = (int)$usageTotals['total_qty2_used'];
} elseif ($year == 3) {
    $totalQuota = (int)$quota['qty3'];
    $usedQuota = (int)$usageTotals['total_qty3_used'];
}

$availableQuota = $totalQuota - $usedQuota;

if ($qty > $availableQuota) {
    jsonResponse('error', 'Insufficient quota. Available: ' . $availableQuota, null, 400);
}

// 5. Generate redeem code dari API eksternal
$redeemCode = generateRedeemCodeFromAPI($eventId, $qty, 100);

if (!$redeemCode) {
    jsonResponse('error', 'Failed to generate redeem code. Please try again later.', null, 500);
}

// 6. Insert ke benefit_usages
$qty1 = ($year == 1) ? $qty : 0;
$qty2 = ($year == 2) ? $qty : 0;
$qty3 = ($year == 3) ? $qty : 0;

$insertSql = "INSERT INTO benefit_usages 
                (id_benefit_list, user_id, description, qty1, qty2, qty3, used_at, redeem_code, status) 
              VALUES 
                ('$benefitId', '200', '$description', 
                 $qty1, $qty2, $qty3, NOW(), '$redeemCode', 2)";

if (!mysqli_query($conn, $insertSql)) {
    // Rollback? API sudah generate redeem code tapi gagal insert
    error_log("Failed to insert benefit usage: " . mysqli_error($conn));
    jsonResponse('error', 'Failed to save claim record', null, 500);
}

// 7. Return success response
jsonResponse('success', 'Benefit claimed successfully', [
    'redeem_code' => $redeemCode,
    'qty' => $qty,
    'year' => $year
]);
?>