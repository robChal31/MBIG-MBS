<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;

    $config = require 'config.php';

    $mail = new PHPMailer(true);
    $mail->isSMTP(); 
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $config['port'] ?? 465;

    //Recipients
    $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
    
    $mail->addAddress('bany@mentarigroups.com', 'Bany');
    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = '$message';
    $mail->send();
    