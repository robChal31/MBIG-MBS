<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$config = require 'config.php';

/* ===== Reusable functions ===== */
function mp_sendEmail($email, $name, $subject, $message, $config, $cc = []) {
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
        foreach ($cc as $c) $mail->addCC($c['email'], $c['name']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

function mp_buildWelcomeEmail($name, $email, $setupLink) {
    return '
    <!DOCTYPE html><html><head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;background:#f4f7fc;font-family:\'Segoe UI\',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fc;padding:40px 0;">
      <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.08);">
          <tr><td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px 30px;text-align:center;">
            <h1 style="color:#fff;margin:0;font-size:28px;">Welcome to Mentari Partner</h1>
            <p style="color:rgba(255,255,255,0.9);margin:12px 0 0;">Complete your registration</p>
          </td></tr>
          <tr><td style="padding:40px 30px;">
            <h2 style="color:#2d3748;margin:0 0 12px;">Hello ' . htmlspecialchars($name) . '! 👋</h2>
            <p style="color:#4a5568;margin:0 0 24px;line-height:1.6;">Thank you for joining Mentari Partner. Please set up your password to access your account.</p>
            <table width="100%" style="background:#f7fafc;border-left:4px solid #667eea;border-radius:8px;margin:28px 0;">
              <tr><td style="padding:20px;">
                <p style="margin:0;color:#2d3748;font-size:14px;">
                  <strong style="color:#667eea;">📋 Account Details</strong><br>
                  Email: ' . htmlspecialchars($email) . '<br>
                  Status: Waiting for password setup
                </p>
              </td></tr>
            </table>
            <table width="100%"><tr><td align="center" style="padding:8px 0 24px;">
              <a href="' . $setupLink . '" style="display:inline-block;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;text-decoration:none;padding:14px 36px;border-radius:50px;font-weight:600;">🔐 Set Up Your Password</a>
            </td></tr></table>
            <p style="color:#718096;font-size:13px;text-align:center;">
              Atau copy link berikut:<br>
              <a href="' . $setupLink . '" style="color:#667eea;word-break:break-all;">' . $setupLink . '</a>
            </p>
          </td></tr>
          <tr><td style="padding:30px;text-align:center;">
            <p style="color:#a0aec0;font-size:12px;">Support: support@mentarigroups.com</p>
            <p style="color:#cbd5e0;font-size:11px;">© ' . date('Y') . ' Mentari Groups.</p>
          </td></tr>
        </table>
      </td></tr>
    </table></body></html>';
}

function mp_createNextJsUser($email, $name, $resetToken, $resetExpires, $config) {
    $postData = json_encode([
        'email' => $email, 'name' => $name,
        'resetToken' => $resetToken, 'resetExpires' => $resetExpires
    ]);
    $ch = curl_init($config['mp_url'] . '/api/mpartner/create');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Content-Length: ' . strlen($postData)],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200) {
        throw new Exception('Next.js error: ' . ($curlError ?: "HTTP $httpCode"));
    }
    $result = json_decode($response, true);
    if (($result['status'] ?? '') !== 'success') {
        throw new Exception($result['message'] ?? 'Failed to create user');
    }
}

/* ===== Main ===== */
$input = json_decode(file_get_contents('php://input'), true);
$rows = $input['rows'] ?? [];

if (empty($rows)) {
    echo json_encode(['status' => 'error', 'message' => 'No data']);
    exit;
}

$success = 0; 
$failed  = 0; 
$results = [];

/* ===== Lookup hanya untuk re-validate ===== */
$valid_school_ids = [];
$q = mysqli_query($conn, "SELECT id FROM schools");
while ($r = mysqli_fetch_assoc($q)) $valid_school_ids[(int)$r['id']] = true;

$valid_pk_ids = [];
$q = mysqli_query($conn, "SELECT id FROM pk");
while ($r = mysqli_fetch_assoc($q)) $valid_pk_ids[(int)$r['id']] = true;

foreach ($rows as $row) {
    $email = $row['email'] ?? '';
    try {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email');
        }

        /* ===== Validate School by ID ===== */
        $institution_id = (int)($row['institution_id'] ?? 0);
        if ($institution_id <= 0 || !isset($valid_school_ids[$institution_id])) {
            throw new Exception('School ID invalid');
        }

        /* ===== Validate PK by ID ===== */
        $pk_ids = $row['pk_ids'] ?? [];
        if (!is_array($pk_ids) || empty($pk_ids)) {
            throw new Exception('No PK');
        }
        foreach ($pk_ids as $pk) {
            $pk = (int)$pk;
            if (!isset($valid_pk_ids[$pk])) {
                throw new Exception("PK ID invalid: $pk");
            }
        }

        $name      = mysqli_real_escape_string($conn, $row['name']);
        $email_esc = mysqli_real_escape_string($conn, $email);
        $phone     = mysqli_real_escape_string($conn, $row['phone']);

        $conn->begin_transaction();

        $chk = $conn->query("SELECT id FROM mp_users WHERE email = '$email_esc'");
        if ($chk->num_rows > 0) throw new Exception('Email sudah terdaftar');

        if (!$conn->query("INSERT INTO mp_users (name, email, phone_number, institution_id, email_sent) 
                           VALUES ('$name', '$email_esc', '$phone', $institution_id, 0)")) {
            throw new Exception($conn->error);
        }
        $id = $conn->insert_id;

        foreach ($pk_ids as $pk) {
            $pk = (int)$pk;
            if (!$conn->query("INSERT INTO mp_user_pks (user_id, pk_id) VALUES ($id, $pk)")) {
                throw new Exception($conn->error);
            }
        }

        /* ===== Next.js user + welcome email ===== */
        $resetToken   = bin2hex(random_bytes(32));
        $resetExpires = date('Y-m-d H:i:s', strtotime('+48 hours'));

        mp_createNextJsUser($email, $row['name'], $resetToken, $resetExpires, $config);

        $setupLink = $config['mp_url'] . '/setup-password?token=' . urlencode($resetToken);
        $subject   = "Welcome to Mentari Partner";
        $body      = mp_buildWelcomeEmail($row['name'], $email, $setupLink);

        if (mp_sendEmail($email, $row['name'], $subject, $body, $config)) {
            $conn->query("UPDATE mp_users SET email_sent = 1, mp_acc_created = 1 WHERE id = $id");
        }

        $conn->commit();
        $success++;
        $results[] = ['email' => $email, 'status' => 'success'];

    } catch (Throwable $e) {
        if (isset($conn) && $conn->errno === 0) @$conn->rollback();
        $failed++;
        $results[] = ['email' => $email, 'status' => 'failed', 'message' => $e->getMessage()];
    }
}

echo json_encode([
    'status'  => 'success',
    'summary' => ['success' => $success, 'failed' => $failed],
    'results' => $results
]);