<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Pastikan PHPMailer sudah di-install

$mail = new PHPMailer(true);
$config = require 'config.php';

try {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password']; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('mbigbenefit@mentarigroups.com', 'SMTP Test');
    $mail->addAddress('tuankrab31@gmail.com'); // Ubah ke email kamu

    $mail->Subject = 'SMTP Test';
    $mail->Body = 'Tes SMTP berhasil!';

    if ($mail->send()) {
        echo "SMTP Berhasil: Email terkirim!";
    }
} catch (Exception $e) {
    echo "SMTP Gagal: " . $mail->ErrorInfo;
}
?>
