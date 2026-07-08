<?php

include('../db_con.php');
$config = require('../config.php');

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

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

// Fungsi send email dengan PHPMailer
function sendEmail($email, $name, $subject, $message, $config, $cc = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); 
        $mail->Host       = $config['host']; 
        $mail->SMTPAuth   = true; 
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'] ?? 465;

        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Mentari Partner');

        $mail->addAddress($email, $name);

        if(count($cc) > 0) {
            foreach ($cc as $value) {
                $mail->addCC($value['email'], $value['name']);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        return $mail->send();

    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
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
$email = $input['email'] ?? null;
$pk_id = $input['pk_id'] ?? null;

if (!$email) {
    jsonResponse('error', 'Email is required', null, 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse('error', 'Invalid email format', null, 400);
}

if (!$pk_id) {
    jsonResponse('error', 'pk_id is required', null, 400);
}

// Escape
$email = mysqli_real_escape_string($conn, $email);
$pk_id = mysqli_real_escape_string($conn, $pk_id);

// 1. Cek user
$checkUserSql = "SELECT id, name, email, institution_id FROM mp_users WHERE email = '$email'";
$userResult = mysqli_query($conn, $checkUserSql);

if (mysqli_num_rows($userResult) == 0) {
    jsonResponse('error', 'User not found', null, 404);
}

$user = mysqli_fetch_assoc($userResult);

// 2. Ambil data untuk email
$sql = "SELECT 
            u.username AS ec_email, 
            u.generalname AS ec_name, 
            p.no_pk, 
            prog.name AS program_name,
            p.start_at,
            p.expired_at,
            ds.sa_name AS sa_name, 
            ds.sa_email AS sa_email,
            mp.name AS pic_name,
            mp.email AS pic_email,
            IFNULL(s.name, db.school_name) AS school_name
        FROM draft_benefit AS db
        LEFT JOIN programs AS prog ON (prog.code = db.program OR prog.name = db.program)
        LEFT JOIN pk AS p ON p.benefit_id = db.id_draft
        LEFT JOIN dash_sa AS ds ON ds.id_sa = p.sa_id
        LEFT JOIN user AS u ON db.id_ec = u.id_user
        LEFT JOIN mp_users AS mp ON mp.id = " . $user['id'] . "
        LEFT JOIN schools AS s ON (s.id = db.school_name OR s.name = db.school_name)
        WHERE db.id_draft = '$pk_id'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse('error', 'Database query failed: ' . mysqli_error($conn), null, 500);
}

if (mysqli_num_rows($result) == 0) {
    jsonResponse('error', 'Program not found', null, 404);
}

$row = mysqli_fetch_assoc($result);

// Data dari query
$saEmail = $row['sa_email'];
$saName = $row['sa_name'];
$ecEmail = $row['ec_email'];
$ecName = $row['ec_name'];
$programName = $row['program_name'];
$noPk = $row['no_pk'];
$startAt = $row['start_at'];
$expiredAt = $row['expired_at'];
$schoolName = $row['school_name'] ?? $user['name'] ?? 'Sekolah';
$pic_name = $row['pic_name'] ?? 'Sekolah';
$schoolEmail = $row['pic_email'] ?? $email;

// Format tanggal
$expiredDate = new DateTime($expiredAt);
$formattedExpired = $expiredDate->format('d F Y');
$formattedStart = new DateTime($startAt);
$formattedStart = $formattedStart->format('d F Y');

// Email subject - Notifikasi internal
$subject = "Pengajuan Perpanjangan Program - $schoolName - $programName ($noPk)";

// HTML Email Body - Internal Notification
$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Pengajuan Perpanjangan Program</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f7f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #97262C 0%, #7a1e22 100%); padding: 25px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 22px; margin: 0; font-weight: 700; }
        .header .badge { display: inline-block; background: #ffffff; color: #1a1a2e; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .content { padding: 30px 40px; }
        .content h2 { color: #1a1a2e; font-size: 18px; margin-top: 0; }
        .content p { color: #6b6b6b; font-size: 14px; line-height: 1.6; margin: 8px 0; }
        .content .school-info { background-color: #f5e6e7; border-left: 4px solid #97262C; padding: 15px 20px; border-radius: 8px; margin: 15px 0; }
        .content .school-info strong { color: #97262C; }
        .content .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 15px 0; }
        .content .info-item { background-color: #f8f7f4; padding: 10px 14px; border-radius: 8px; }
        .content .info-item .label { font-size: 11px; color: #6b6b6b; display: block; }
        .content .info-item .value { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-top: 2px; }
        .content .info-item .value.highlight { color: #97262C; }
        .content .info-item .value.expired { color: #dc2626; }
        .alert-box { background-color: #fef3c7; border: 1px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin: 15px 0; }
        .alert-box p { color: #92400e; font-size: 13px; margin: 0; }
        .footer { background-color: #f8f7f4; padding: 16px 40px; text-align: center; border-top: 1px solid #e8e4e0; }
        .footer p { color: #6b6b6b; font-size: 11px; margin: 3px 0; }
        .footer .brand { color: #97262C; font-weight: 600; }
        @media (max-width: 480px) {
            .content .info-grid { grid-template-columns: 1fr; }
            .header { padding: 20px; }
            .content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔄 Pengajuan Perpanjangan Program</h1>
            <span class='badge'>⚡ Perlu Tindakan</span>
        </div>
        
        <div class='content'>
            <h2>📢 Notifikasi Perpanjangan</h2>
            
            <p>Ada pengajuan perpanjangan program dari sekolah mitra. Mohon segera ditindaklanjuti.</p>
            
            <div class='school-info'>
                <p style='margin: 0;'><strong>🏫 Sekolah:</strong> $schoolName</p>
                <p style='margin: 0;'><strong>👤 PIC:</strong> $pic_name</p>
                <p style='margin: 5px 0 0 0;'><strong>✉️ Email:</strong> $schoolEmail</p>
            </div>
            
            <div class='info-grid'>
                <div class='info-item'>
                    <span class='label'>📌 Program</span>
                    <span class='value highlight'>$programName</span>
                </div>
                <div class='info-item'>
                    <span class='label'>📋 No. PK</span>
                    <span class='value'>$noPk</span>
                </div>
                <div class='info-item'>
                    <span class='label'>📅 Tanggal Mulai</span>
                    <span class='value'>$formattedStart</span>
                </div>
                <div class='info-item'>
                    <span class='label'>⏰ Tanggal Berakhir</span>
                    <span class='value expired'>$formattedExpired</span>
                </div>
            </div>
            
        </div>
        
        <div class='footer'>
            <p><span class='brand'>Mentari Partner</span> — Education Platform</p>
            <p>© " . date('Y') . " Mentari Partner. All rights reserved.</p>
            <p style='font-size: 10px; color: #9ca3af;'>
                Email notifikasi internal. Dikirim secara otomatis oleh sistem.
            </p>
        </div>
    </div>
</body>
</html>
";

// Kirim email ke SA (primary) dan CC ke EC
$sent = false;
$cc = [];

if ($saEmail) {
    // CC list - EC
    if ($ecEmail) {
        $cc = [
            ['email' => 'tuankrab31@gmail.com', 'name' => $ecName]
        ];
    }
    
    $sent = sendEmail('bany@mentarigroups.com', $saName, $subject, $emailBody, $config, $cc);

}

// Response
$responseData = [
    'sa_email' => $saEmail,
    'ec_email' => $ecEmail,
    'sa_name' => $saName,
    'ec_name' => $ecName,
    'school' => [
        'name' => $schoolName,
        'email' => $schoolEmail
    ],
    'program' => [
        'name' => $programName,
        'no_pk' => $noPk,
        'start_at' => $startAt,
        'expired_at' => $expiredAt
    ],
    'email_sent' => $sent
];

if ($sent) {
    jsonResponse('success', 'Notifikasi renewal berhasil dikirim', $responseData);
} else {
    jsonResponse('error', 'Gagal mengirim notifikasi', $responseData, 500);
}

?>