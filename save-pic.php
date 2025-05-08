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

function sendEmail($email, $name, $subject, $message, $config, $fileUrl, $cc = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); 
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true; 
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'] ?? 465;

        //Recipients
        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
        $fileUrl1 = $fileUrl[0];
        $fileUrl2 = $fileUrl[1];
        $fileUrl3 = $fileUrl[2] ? ('draft-benefit/' . $fileUrl[2]) : null;

        if(file_exists($fileUrl1)) {
            $mail->addAttachment($fileUrl1);
        }
        if(file_exists($fileUrl2)) {
            $mail->addAttachment($fileUrl2);
        }

        if($fileUrl3) {
            if(file_exists($fileUrl3)) {
                $mail->addAttachment($fileUrl3);
            }
        }

        $mail->addAddress($email, $name);
        if(count($cc) > 0) {
            foreach ($cc as $key => $value) {
                $mail->addCC($value['email'], $value['name']);
            }
        }

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => "Failed to send email: {$mail->ErrorInfo}"
        ]);
        exit();
    }
}

function file_pk_error_session($msg = 'Gagal menambahkan PIC, pastikan inputan benar!'){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

$id_draft = $_POST['id_draft'];
$name = $_POST['name'];
$jabatan = $_POST['jabatan'];
$email = $_POST['email'];
$no_tlp = $_POST['no_tlp'];
$id_draft = $_POST['id_draft'];

try {
    $pic_exist_query = "SELECT * FROM school_pic_partner WHERE id_draft = $id_draft";
    $is_pic_exist_exec = $conn->query($pic_exist_query);
    $is_pic_exist = $is_pic_exist_exec->num_rows > 0;

    if(!$is_pic_exist) {
        $sql = "INSERT INTO school_pic_partner (id_draft, name, jabatan, email, no_tlp, created_at, updated_at) 
                        VALUES ($id_draft, '$name', '$jabatan', '$email', '$no_tlp', current_timestamp(), NULL)";
    }else {
        $sql = "UPDATE school_pic_partner SET name = '$name', jabatan = '$jabatan', email = '$email', no_tlp = '$no_tlp', updated_at = current_timestamp() 
                    WHERE id_draft = $id_draft";
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Saved successfully'
        ]);
    } else {
        file_pk_error_session($pic_exist_query);
    }

} catch (\Throwable $th) {
    file_pk_error_session($th);
}
?>
