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

function file_pk_error_session(){
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan PK, pastikan inputan, dan format file benar!'
    ]);
    exit();
}

$id_draft = $_POST['id_draft'];
$no_pk = $_POST['no_pk'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$id_sa = $_POST['id_sa'];

try {
    $pk_exist_query = "SELECT * FROM pk WHERE benefit_id = $id_draft";
    $is_pk_exist_exec = $conn->query($pk_exist_query);
    $is_pk_exist = $is_pk_exist_exec->num_rows > 0;

    $no_pk_exist_query = "SELECT * FROM pk WHERE no_pk = '$no_pk' AND benefit_id != $id_draft";
    $is_no_pk_exist_exec = $conn->query($no_pk_exist_query);
    $is_no_pk_exist = $is_no_pk_exist_exec->num_rows > 0;

    $target_dir = "dokumen/";
    $target_file_pk = $target_dir . basename($_FILES["file_pk"]["name"]);
    $target_file_benefit = $target_dir . basename($_FILES["file_benefit"]["name"]);

    $uploadOk = 1;
    $fileExtension_pk = strtolower(pathinfo($target_file_pk, PATHINFO_EXTENSION));
    $fileExtension_benefit = strtolower(pathinfo($target_file_benefit, PATHINFO_EXTENSION));

    // Allow certain file formats
    // $validExtensions = "/(jpg|png|jpeg|gif|pdf|docx|doc|xls|xlsx)$/i";
    $validExtensions = "/(pdf)$/i";

    if ($is_pk_exist) {
        if ($_FILES["file_pk"]["name"] && !preg_match($validExtensions, $fileExtension_pk)) {
            $uploadOk = 0;
        }
        
        if ($_FILES["file_benefit"]["name"] && !preg_match($validExtensions, $fileExtension_benefit)) {
            $uploadOk = 0;
        }
    } else {
        if (!preg_match($validExtensions, $fileExtension_pk) || !preg_match($validExtensions, $fileExtension_benefit)) {
            $uploadOk = 0;
        }
    }

    if ($uploadOk == 1 && !$is_no_pk_exist) {
        $sa_action = $is_pk_exist ? "mengupdate" : "menambahkan";
        
        if (!$is_pk_exist) {
            if (move_uploaded_file($_FILES["file_pk"]["tmp_name"], $target_file_pk) && move_uploaded_file($_FILES["file_benefit"]["tmp_name"], $target_file_benefit)) {
                $sql = "INSERT INTO pk (benefit_id, no_pk, start_at, expired_at, sa_id, file_pk, file_benefit, created_at, updated_at) 
                        VALUES ($id_draft, '$no_pk', '$start_date', '$end_date', $id_sa, '$target_file_pk', '$target_file_benefit', current_timestamp(), NULL)";
            } else {
                file_pk_error_session();
            }
        } else {
            $update_file_query = "";
            if ($_FILES["file_pk"]["name"]) {
                move_uploaded_file($_FILES["file_pk"]["tmp_name"], $target_file_pk);
                $update_file_query .= "file_pk = '$target_file_pk', ";
            }
            if ($_FILES["file_benefit"]["name"]) {
                move_uploaded_file($_FILES["file_benefit"]["tmp_name"], $target_file_benefit);
                $update_file_query .= "file_benefit = '$target_file_benefit', ";
            }
            $sql = "UPDATE pk SET no_pk = '$no_pk', start_at = '$start_date', expired_at = '$end_date', sa_id = $id_sa, $update_file_query updated_at = current_timestamp() 
                    WHERE benefit_id = $id_draft";
        }

        if (mysqli_query($conn, $sql)) {
            $result = mysqli_query($conn, "SELECT * FROM pk WHERE benefit_id = $id_draft");
            $data_pk = mysqli_fetch_assoc($result);

            $file_pk = $data_pk['file_pk'];
            $file_benefit = $data_pk['file_benefit'];

            $sql_benefit = "SELECT draft_benefit.*, IFNULL(sc.name, draft_benefit.school_name) AS school_name 
                            FROM draft_benefit 
                            LEFT JOIN schools AS sc ON sc.id = draft_benefit.school_name 
                            WHERE id_draft = $id_draft";

            $result = mysqli_query($conn, $sql_benefit);
            $data_benefit = mysqli_fetch_assoc($result);

            $file_exc_benefit = $data_benefit['fileUrl'];
            $school_name = $data_benefit['school_name'];
            $program = $data_benefit['program'];
            $uc_program = strtoupper($program);

            $email = "secretary@mentaribooks.com";
            $name = "Secretary Mentari Books";
            $subject = "Pengajuan PK";
            $message = "<style>
                            * { font-family: Helvetica, sans-serif; }
                            .container { width: 80%; margin: auto; }
                        </style>
                        <div class='container'>
                            <p>Sales Administrator telah $sa_action PK Program <strong>$uc_program</strong> untuk <strong>$school_name</strong></p>
                            <p>Ayo, cepat-cepat dicek agar benefit bisa segera didistribusikan ke Unit Bisnis terkait! 👍😊</p>
                            <p>Silakan klik tombol berikut untuk verifikasi dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                            <p style='margin: 20px 0px;'>
                                <a href='https://mentarigroups.com/benefit/approved_list.php' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>Redirect me!</a>
                            </p>
                            <div style='border-bottom: 1px solid #ddd;'></div>
                            <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                            <p style='color: #0096c7'>https://mentarigroups.com/benefit/approved_list.php</p>
                            <div style='text-align: center; margin-top: 35px;'>
                                <span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span>
                            </div>
                        </div>";
            $fileUrl = [$file_pk, $file_benefit, $file_exc_benefit];

            sendEmail($email, $name, $subject, $message, $config, $fileUrl);

            echo json_encode([
                'status' => 'success',
                'message' => 'Saved successfully'
            ]);
        } else {
            file_pk_error_session();
        }
    } elseif ($is_no_pk_exist) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan PK, nomor PK sudah ada!'
        ]);
    } else {
        file_pk_error_session();
    }
} catch (\Throwable $th) {
    file_pk_error_session();
}
?>
