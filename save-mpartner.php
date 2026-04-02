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

$id             = (int) ($_POST['id'] ?? 0);
$name           = sanitize_input($conn, $_POST['name'] ?? '');
$email          = sanitize_input($conn, $_POST['email'] ?? '');
$institution_id = (int) ($_POST['institution_id'] ?? 0);
$pks            = $_POST['pks'] ?? [];

if (!is_array($pks) || count($pks) == 0) {
    error_json("Please select at least one PK");
}

try {
    $conn->begin_transaction();

    // CHECK UNIQUE EMAIL
    $check_email_q = "SELECT id FROM mp_users WHERE email = '$email' AND id != $id";
    $check_email_exec = $conn->query($check_email_q);
    if ($check_email_exec === false) {
        throw new Exception($conn->error);
    }

    if ($check_email_exec->num_rows > 0) {
        throw new Exception('Email already exists');
    }

    // CHECK EXIST
    $mpartner_exist_q = "SELECT id, email_sent FROM mp_users WHERE id = '$id'";
    $is_mpartner_exist_exec = $conn->query($mpartner_exist_q);

    if ($is_mpartner_exist_exec === false) {
        throw new Exception($conn->error);
    }

    $is_mpartner_exist = $is_mpartner_exist_exec->num_rows > 0;

    $isNew = false;
    $email_sent = 0;

    if ($is_mpartner_exist) {
        $row = $is_mpartner_exist_exec->fetch_assoc();
        $email_sent = (int) $row['email_sent'];
    }

    if ($is_mpartner_exist) {
        $sql = "UPDATE mp_users 
                SET name = '$name', email = '$email', institution_id = '$institution_id'
                WHERE id = '$id'";

        if (!$conn->query($sql)) {
            throw new Exception($conn->error);
        }

    } else {
        $isNew = true;

        $sql = "INSERT INTO mp_users (name, email, institution_id, email_sent) 
                VALUES ('$name', '$email', '$institution_id', 0)";

        if (!$conn->query($sql)) {
            throw new Exception($conn->error);
        }

        $id = $conn->insert_id;
    }

    // DELETE RELATION
    if (!$conn->query("DELETE FROM mp_user_pks WHERE user_id = '$id'")) {
        throw new Exception($conn->error);
    }

    foreach ($pks as $pk) {
        $pk = (int)$pk;
        if (!$conn->query("INSERT INTO mp_user_pks (user_id, pk_id) VALUES ($id, $pk)")) {
            throw new Exception($conn->error);
        }
    }

    // SEND EMAIL ONLY IF NEW
    if ($isNew ) {
        $setupLink = "https://mentaripartner.com/setup-password.php?email=" . urlencode($email);

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
                            <!-- Main Container -->
                            <table width="100%" max-width="600px" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); overflow: hidden;">
                                
                                <!-- Header with Gradient -->
                                <tr>
                                    <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                                        <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">Welcome to Mentari Partner</h1>
                                        <p style="color: rgba(255,255,255,0.9); margin: 12px 0 0; font-size: 16px;">Your journey begins here</p>
                                    </td>
                                </tr>
                                
                                <!-- Content Area -->
                                <tr>
                                    <td style="padding: 40px 30px;">
                                        <!-- Greeting -->
                                        <h2 style="color: #2d3748; margin: 0 0 12px; font-size: 24px; font-weight: 600;">Hello ' . htmlspecialchars($name) . '! 👋</h2>
                                        <p style="color: #4a5568; margin: 0 0 24px; font-size: 16px; line-height: 1.6;">Thank you for joining Mentari Partner. We\'re excited to have you on board and look forward to building a successful partnership together.</p>
                                        
                                        <!-- Info Box -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7fafc; border-left: 4px solid #667eea; border-radius: 8px; margin: 28px 0;">
                                            <tr>
                                                <td style="padding: 20px;">
                                                    <p style="margin: 0; color: #2d3748; font-size: 14px; line-height: 1.6;">
                                                        <strong style="color: #667eea;">📋 Account Details</strong><br>
                                                        You\'ve been registered as a Mentari Partner. Please set up your password to activate your account and access all features.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Button -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="padding: 8px 0 24px;">
                                                    <a href="' . $setupLink . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 50px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(102,126,234,0.3); transition: all 0.3s ease;">🔐 Set Up Your Password</a>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Alternative Link -->
                                        <p style="color: #718096; margin: 16px 0 0; font-size: 13px; text-align: center; line-height: 1.5;">
                                            Or copy and paste this link into your browser:<br>
                                            <a href="' . $setupLink . '" style="color: #667eea; text-decoration: none; word-break: break-all;">' . $setupLink . '</a>
                                        </p>
                                    </td>
                                </tr>
                                <hr style="margin: 0; border: none; border-top: 1px solid #e2e8f0;">
                                <!-- Footer -->
                                <tr>
                                    <td style="padding: 30px; text-align: center;">
                                        <p style="color: #a0aec0; margin: 0 0 12px; font-size: 12px;">
                                            Need help? Contact our support team at<br>
                                            <a href="mailto:support@mentarigroups.com" style="color: #667eea; text-decoration: none;">support@mentarigroups.com</a>
                                        </p>
                                        <p style="color: #cbd5e0; margin: 0; font-size: 11px;">
                                            © ' . date('Y') . ' Mentari Groups. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html>';

        $sent = sendEmail('bany@mentarigroups.com', $name, $subject, $message, $config);

        if ($sent) {
            $conn->query("UPDATE mp_users SET email_sent = 1 WHERE id = $id");
        }
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Saved successfully'
    ]);

} catch (\Throwable $th) {
    $conn->rollback();
    error_json($th->getMessage());
}
?>