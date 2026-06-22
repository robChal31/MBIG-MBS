<?php
// send_email_reset_password.php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
include('../db_con.php');
$config = require('../config.php');

header('Access-Control-Allow-Origin: ' . $config['mp_url']);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

function sendResetPasswordEmail($email, $name, $resetToken, $config) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'] ?? 465;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Mentari Partner');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password - Mentari Partner';

        // Generate reset link
        $setupLink = $config['mp_url'] . "/setup-password?token=" . urlencode($resetToken);

        // Email template
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password - Mentari Partner</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    background-color: #f8fafc;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    padding: 40px 0;
                    line-height: 1.6;
                }
                .container {
                    max-width: 560px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 16px;
                    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
                    overflow: hidden;
                }
                .header {
                    background: linear-gradient(135deg, #3279FF 0%, #5e93ff 100%);
                    padding: 48px 40px 32px;
                    text-align: center;
                }
                .header h1 {
                    color: #ffffff;
                    font-size: 26px;
                    font-weight: 700;
                    margin: 0 0 8px;
                    letter-spacing: -0.5px;
                }
                .header p {
                    color: rgba(255, 255, 255, 0.85);
                    font-size: 15px;
                    margin: 0;
                }
                .header .logo {
                    display: inline-block;
                    margin-bottom: 16px;
                }
                .header .logo img {
                    height: 48px;
                    filter: brightness(0) invert(1);
                }
                .body {
                    padding: 40px;
                }
                .body h2 {
                    color: #1a202c;
                    font-size: 22px;
                    font-weight: 600;
                    margin: 0 0 12px;
                }
                .body p {
                    color: #4a5568;
                    font-size: 15px;
                    margin: 0 0 16px;
                }
                .body .highlight-box {
                    background: #f0f4ff;
                    border-left: 4px solid #3279FF;
                    border-radius: 8px;
                    padding: 16px 20px;
                    margin: 24px 0;
                }
                .body .highlight-box p {
                    margin: 0;
                    font-size: 14px;
                    color: #2d3748;
                }
                .body .highlight-box strong {
                    color: #3279FF;
                }
                .body .btn {
                    display: inline-block;
                    background: #3279FF;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 14px 40px;
                    border-radius: 50px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 8px 0 24px;
                    box-shadow: 0 4px 12px rgba(50, 121, 255, 0.3);
                    transition: background 0.2s;
                }
                .body .btn:hover {
                    background: #2b66d9;
                }
                .body .link-box {
                    background: #f7fafc;
                    border-radius: 8px;
                    padding: 12px 16px;
                    margin: 16px 0;
                    word-break: break-all;
                    font-size: 13px;
                    color: #4a5568;
                    border: 1px solid #e2e8f0;
                }
                .body .link-box a {
                    color: #3279FF;
                    text-decoration: none;
                }
                .body .link-box a:hover {
                    text-decoration: underline;
                }
                .body .divider {
                    border: none;
                    border-top: 1px solid #e2e8f0;
                    margin: 32px 0 24px;
                }
                .body .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 12px;
                    margin: 16px 0;
                }
                .body .info-grid .item {
                    background: #f7fafc;
                    padding: 12px 16px;
                    border-radius: 8px;
                    text-align: center;
                }
                .body .info-grid .item .label {
                    font-size: 11px;
                    color: #a0aec0;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .body .info-grid .item .value {
                    font-size: 18px;
                    font-weight: 600;
                    color: #2d3748;
                    margin-top: 4px;
                }
                .footer {
                    padding: 24px 40px 32px;
                    text-align: center;
                    border-top: 1px solid #e2e8f0;
                    background: #fafbfc;
                }
                .footer p {
                    color: #a0aec0;
                    font-size: 12px;
                    margin: 0 0 4px;
                }
                .footer a {
                    color: #3279FF;
                    text-decoration: none;
                }
                .footer a:hover {
                    text-decoration: underline;
                }
                @media (max-width: 480px) {
                    .header {
                        padding: 32px 24px 24px;
                    }
                    .header h1 {
                        font-size: 22px;
                    }
                    .body {
                        padding: 24px;
                    }
                    .body .btn {
                        display: block;
                        text-align: center;
                    }
                    .body .info-grid {
                        grid-template-columns: 1fr;
                    }
                    .footer {
                        padding: 20px 24px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <div class="logo">
                        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">Welcome to Mentari Partner</h1>
                    </div>
                    <h1>Reset Password</h1>
                    <p>Secure your account with a new password</p>
                </div>

                <!-- Body -->
                <div class="body">
                    <h2>Hello ' . htmlspecialchars($name) . ' 👋</h2>
                    <p>We received a request to reset your password for your <strong>Mentari Partner</strong> account.</p>
                    <p>Click the button below to create a new password:</p>

                    <div style="text-align: center;">
                        <a href="' . $setupLink . '" class="btn">🔐 Reset Password</a>
                    </div>

                    <div class="highlight-box">
                        <p>⏰ This link will expire in <strong>24 hour</strong> for your security.</p>
                    </div>

                    <p style="font-size: 13px; color: #718096;">
                        Or copy and paste this link into your browser:
                    </p>
                    <div class="link-box">
                        <a href="' . $setupLink . '">' . $setupLink . '</a>
                    </div>

                    <hr class="divider">

                    <div style="background: #fff5f5; border-radius: 8px; padding: 16px; margin: 16px 0;">
                        <p style="font-size: 13px; color: #e53e3e; margin: 0;">
                            ⚠️ If you didn\'t request this password reset, please ignore this email or contact our support team immediately.
                        </p>
                    </div>

                    <p style="font-size: 13px; color: #718096; text-align: center; margin: 24px 0 0;">
                        Need help? Contact us at<br>
                        <a href="mailto:support@mentarigroups.com" style="color: #3279FF; text-decoration: none;">support@mentarigroups.com</a>
                    </p>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' <a href="' . $config['mp_url'] . '">Mentari Partner</a>. All rights reserved.</p>
                    <p>Mentari Groups, Jakarta, Indonesia</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n"], $mail->Body));

        return $mail->send();

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ============ HANDLE REQUEST ============

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit();
}

$email = $input['email'] ?? '';
$resetToken = $input['resetToken'] ?? '';
$name = $input['userName'] ?? $email;

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

// Validasi
if (empty($email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is required'
    ]);
    exit();
}

if (empty($resetToken)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Reset token is required'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    exit();
}

// Kirim email
try {
    $sent = sendResetPasswordEmail($email, $name, $resetToken, $config);

    if ($sent) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Reset password email sent successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to send email'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>