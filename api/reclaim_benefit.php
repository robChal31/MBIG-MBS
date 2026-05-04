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
        'id_event' => 939,
        'quota' => $qty,
        'diskon' => $discount,
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response_api = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError || $httpCode !== 200) {
        error_log("cURL Error createpromo: " . $curlError);
        return null;
    }
    
    $redeem_code = str_replace('"', '', $response_api);
    
    return $redeem_code;
}

function updateVoucherCodeInHadirYuk($oldRedeemCode, $newQty) {
    $url = 'https://hadiryuk.id/api/update_voucher_code';
    $data = array(
        'redeem_code' => $oldRedeemCode,
        'quota' => $newQty
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response_api = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function transferVoucherCodeInHadirYuk($oldRedeemCode, $id_event) {
    $url = 'https://hadiryuk.id/api/transfer_voucher_code';
    $data = array(
        'redeem_code' => $oldRedeemCode,
        'id_event' => $id_event
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response_api = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
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
$historyId = $input['history_id'] ?? null;
$oldEventId = $input['old_event_id'] ?? null;
$newEventId = $input['new_event_id'] ?? null;
$qty = (int)($input['qty'] ?? 0);
$usedQty = (int)($input['usedQty'] ?? 0);
$benefitId = $input['benefit_id'] ?? null;
$benefitDraftId = $input['benefit_draft_id'] ?? null;
$pkId = $input['pk_id'] ?? null;
$email = $input['email'] ?? null;

if (!$historyId || !$newEventId || $qty <= 0 || !$benefitId || !$email) {
    jsonResponse('error', 'Missing required fields', null, 400);
}

// Escape input
$historyId = mysqli_real_escape_string($conn, $historyId);
$oldEventId = mysqli_real_escape_string($conn, $oldEventId);
$newEventId = mysqli_real_escape_string($conn, $newEventId);
$benefitId = mysqli_real_escape_string($conn, $benefitId);
$benefitDraftId = mysqli_real_escape_string($conn, $benefitDraftId);
$pkId = mysqli_real_escape_string($conn, $pkId);
$email = mysqli_real_escape_string($conn, $email);

// Mulai database transaction
mysqli_begin_transaction($conn);

try {
    // 1. Cek user
    $checkUserSql = "SELECT id FROM mp_users WHERE email = '$email'";
    $userResult = mysqli_query($conn, $checkUserSql);
    
    if (mysqli_num_rows($userResult) == 0) {
        throw new Exception('User not found');
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
        throw new Exception('You do not have access to this benefit');
    }
    
    // 3. Cek history milik user
    $checkHistorySql = "SELECT id, qty1, qty2, qty3, redeem_code 
                        FROM benefit_usages 
                        WHERE id = '$historyId'";
    $historyResult = mysqli_query($conn, $checkHistorySql);
    
    if (mysqli_num_rows($historyResult) == 0) {
        throw new Exception('History not found');
    }
    
    $history = mysqli_fetch_assoc($historyResult);
    
    // 4. Tentukan tahun yang aktif (qty mana yang dipakai)
    $activeQty = 0;
    $activeYear = 0;
    $currentQtyField = '';
    
    if ($history['qty1'] > 0) {
        $activeQty = (int)$history['qty1'];
        $activeYear = 1;
        $currentQtyField = 'qty1';
    } elseif ($history['qty2'] > 0) {
        $activeQty = (int)$history['qty2'];
        $activeYear = 2;
        $currentQtyField = 'qty2';
    } elseif ($history['qty3'] > 0) {
        $activeQty = (int)$history['qty3'];
        $activeYear = 3;
        $currentQtyField = 'qty3';
    }
    
    // Validasi: qty yang dipindahkan tidak boleh melebihi (activeQty - usedQty)
    $availableToMove = $activeQty - $usedQty;
    if ($qty > $availableToMove) {
        throw new Exception("Cannot move $qty slots. Only $availableToMove slots available (Total: $activeQty, Used: $usedQty)");
    }
    
    // 5. Hitung sisa quota setelah dipindah
    $remainingQty = $availableToMove - $qty;
    $totalNewQty = $usedQty + $remainingQty;
    
    if($totalNewQty > 0) {
        // 6. Update voucher code di HadirYuk untuk history lama (kurangi quota)
        $newRedeemCodeForOld = updateVoucherCodeInHadirYuk($history['redeem_code'], $totalNewQty);
        
        if (!$newRedeemCodeForOld) {
            throw new Exception('Failed to update voucher code in HadirYuk');
        }
        
        // 7. Update history lama
        $updateSql = "UPDATE benefit_usages 
                    SET $currentQtyField = $totalNewQty,
                        description = CONCAT(description, ' (Moved $qty slots to another event, from: $activeQty, used: $usedQty, new total: $totalNewQty)')
                    WHERE id = '$historyId'";
        
        if (!mysqli_query($conn, $updateSql)) {
            throw new Exception('Failed to update history: ' . mysqli_error($conn));
        }
        
        // 8. Generate redeem code baru untuk event baru
        $newRedeemCodeForNew = generateRedeemCodeFromAPI($newEventId, $qty, 100);
        
        if (!$newRedeemCodeForNew) {
            throw new Exception('Failed to generate redeem code for new event');
        }
        
        // 9. Insert history baru untuk event baru
        $description = "Moved from previous event (ID: $historyId) - New event: $newEventId";
        $insertSql = "INSERT INTO benefit_usages 
                        (id_benefit_list, user_id, description, $currentQtyField, used_at, redeem_code) 
                    VALUES 
                        ('$benefitId', '$userId', '$description', $qty, NOW(), '$newRedeemCodeForNew')";
        
        if (!mysqli_query($conn, $insertSql)) {
            throw new Exception('Failed to create new benefit record: ' . mysqli_error($conn));
        }
        
        // Jika semua berhasil, commit transaction
        mysqli_commit($conn);
        
        jsonResponse('success', 'Benefit moved successfully', [
            'new_redeem_code' => $newRedeemCodeForNew,
            'old_redeem_code' => $newRedeemCodeForOld,
            'qty' => $qty,
            'used_qty' => $usedQty,
            'old_history_id' => $historyId,
            'remaining_qty' => $remainingQty
        ]);
    }else {
        $newRedeemCodeForOld = transferVoucherCodeInHadirYuk($history['redeem_code'], $newEventId);
        
        if (!$newRedeemCodeForOld) {
            throw new Exception('Failed to update voucher code in HadirYuk');
        }
        
        // 7. Update history lama
        $updateSql = "UPDATE benefit_usages 
                    SET description = CONCAT(description, ' ( Change event to another event)')
                    WHERE id = '$historyId'";
        
        if (!mysqli_query($conn, $updateSql)) {
            throw new Exception('Failed to update history: ' . mysqli_error($conn));
        }
        
        // Jika semua berhasil, commit transaction
        mysqli_commit($conn);
        
        jsonResponse('success', 'Benefit moved successfully', [
            'new_redeem_code' => $newRedeemCodeForOld,
            'old_redeem_code' => $newRedeemCodeForOld,
            'qty' => $qty,
            'used_qty' => $usedQty,
            'old_history_id' => $historyId,
            'remaining_qty' => $remainingQty
        ]);
    }
    
    
} catch (Exception $e) {
    // Jika ada error, rollback semua perubahan database
    mysqli_rollback($conn);
    
    error_log("Reclaim benefit error: " . $e->getMessage());
    
    jsonResponse('error', $e->getMessage(), null, 500);
}

// Tutup koneksi
mysqli_close($conn);
?>