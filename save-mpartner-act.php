<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

if (!isset($_SESSION['username'])){ 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

$config = require 'config.php';

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

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
        return false;
    }
}

function sanitize_input($conn, $input) {
    return mysqli_real_escape_string($conn, str_replace(["&#13;", "&#10;"], ["\r", "\n"], $input));
}

$id     = $_POST['id'] ?? 0;

$mpartner = [];
$mp_sql = "SELECT mpu.* FROM mp_users AS mpu WHERE mpu.id = $id";
$draft_exec = mysqli_query($conn, $mp_sql);
if (mysqli_num_rows($draft_exec) > 0) {
    $mpartner = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}

$mpartner = $mpartner[0] ?? [];

if($mpartner == []) {
    error_json("User not found");
}

$email = sanitize_input($conn, $mpartner['email'] ?? '');
$name = sanitize_input($conn, $mpartner['name'] ?? '');

try {
    // Generate secure token (32 bytes = 64 karakter hex)
    $resetToken = bin2hex(random_bytes(32));
    $resetExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Kirim data ke Next.js API
    $postData = json_encode([
        'email' => $email,
        'name' => $name,
        'resetToken' => $resetToken,
        'resetExpires' => $resetExpires
    ]);
    
    $nextjs_url = $config['mp_url'] . '/api/mpartner/create';
    
    $ch = curl_init($nextjs_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError || $httpCode !== 200) {
        throw new Exception('Failed to create user in Next.js: ' . ($curlError ?: 'HTTP ' . $httpCode));
    }
    
    $result = json_decode($response, true);
    
    if ($result['status'] !== 'success') {
        throw new Exception($result['message'] ?? 'Failed to create user in Next.js');
    }
    
    // Buat link setup password dengan TOKEN
    $setupLink = $config['mp_url'] . "/setup-password?token=" . urlencode($resetToken);
    
    // Kirim email dengan link token
    $subject = "Welcome to Mentari Partner";
    
    $message = '
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to Mentari Partner</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #f4f7fc; font-family: \'Segoe UI\', \'Helvetica Neue\', Arial, sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7fc; padding: 40px 0;">
                <tr>
                    <td align="center">
                        <table width="100%" max-width="600px" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); overflow: hidden;">
                            <tr>
                                <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">Welcome to Mentari Partner</h1>
                                    <p style="color: rgba(255,255,255,0.9); margin: 12px 0 0; font-size: 16px;">Complete your registration</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <h2 style="color: #2d3748; margin: 0 0 12px; font-size: 24px;">Hello ' . htmlspecialchars($name) . '! 👋</h2>
                                    <p style="color: #4a5568; margin: 0 0 24px; font-size: 16px; line-height: 1.6;">Thank you for joining Mentari Partner. Please set up your password to access your account.</p>
                                    
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7fafc; border-left: 4px solid #667eea; border-radius: 8px; margin: 28px 0;">
                                        <tr>
                                            <td style="padding: 20px;">
                                                <p style="margin: 0; color: #2d3748; font-size: 14px;">
                                                    <strong>📋 Account Details</strong><br>
                                                    Email: ' . htmlspecialchars($email) . '<br>
                                                    Status: Waiting for password setup
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center" style="padding: 8px 0 24px;">
                                                <a href="' . $setupLink . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 50px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(102,126,234,0.3);">🔐 Set Up Your Password</a>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <p style="color: #718096; margin: 16px 0 0; font-size: 13px; text-align: center; line-height: 1.5;">
                                        Or copy and paste this link into your browser:<br>
                                        <a href="' . $setupLink . '" style="color: #667eea; text-decoration: none; word-break: break-all;">' . $setupLink . '</a>
                                    </p>
                                </td>
                            </tr>
                            <hr style="margin: 0; border: none; border-top: 1px solid #e2e8f0;">
                            <div style="padding: 30px; text-align: center;">
                                <p style="color: #a0aec0; margin: 0 0 12px; font-size: 12px;">
                                    Need help? Contact our support team at<br>
                                    <a href="mailto:support@mentarigroups.com" style="color: #667eea; text-decoration: none;">support@mentarigroups.com</a>
                                </p>
                                <p style="color: #cbd5e0; margin: 0; font-size: 11px;">
                                    © ' . date('Y') . ' Mentari Groups. All rights reserved.
                                </p>
                            </div>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
    </html>';
    
    // Kirim email
    $sent = sendEmail($email, $name, $subject, $message, $config);
    
    if ($sent) {
        $conn->query("UPDATE mp_users SET email_sent = 1 WHERE id = $id");
    }
    
    // Update mp_acc_created = 1 (karena user sudah dibuat di Next.js)
    $conn->query("UPDATE mp_users SET mp_acc_created = 1 WHERE id = $id");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Account created and email sent successfully'
    ]);
    exit();

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>