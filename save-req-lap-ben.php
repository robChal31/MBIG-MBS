<?php

session_start();
include 'db_con.php';
require 'vendor/autoload.php';
$config = require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;

$mpdf = new \Mpdf\Mpdf();
if (!isset($_SESSION['username'])){ 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

$id_draft = $_POST['id_draft'] ?? null;
$period = date('Y-m');

try {
    $get_draft_benefit = "SELECT db.id_draft, p.name as program_name, db.segment, db.level, db.wilayah, db.id_ec,
                            IFNULL(s.name, db.school_name) AS school_name
                        FROM draft_benefit as db
                        LEFT JOIN programs as p on p.name = db.program
                        LEFT JOIN schools as s on s.id = db.school_name
                        WHERE id_draft = $id_draft";

    $get_draft_benefit_exec = $conn->query($get_draft_benefit);
    $draft_benefit = $get_draft_benefit_exec->fetch_assoc();

    $id_ec_r = $draft_benefit['id_ec'] ?? $_SESSION['id_user'];
    $program = $draft_benefit['program_name'];
    $segment = $draft_benefit['segment'];
    $level = $draft_benefit['level'];
    $wilayah = $draft_benefit['wilayah'];
    $school_name = $draft_benefit['school_name'];
    $uc_program = strtoupper($program);

    $check_latest_bir = "SELECT * FROM benefit_imp_report WHERE id_draft = '$id_draft' AND period = '$period' ORDER BY id DESC LIMIT 1";
    $check_latest_bir_exec = $conn->query($check_latest_bir);

    $latest_bir = $check_latest_bir_exec->fetch_assoc();
    $bir_id = $latest_bir['id'] ?? null;

    if(!$bir_id) {
        $insert_q = "INSERT INTO benefit_imp_report (id_draft, period, status) VALUES ($id_draft, '$period', '0')";
        $insert_exec = $conn->query($insert_q);
        $bir_id = $conn->insert_id;
    }

    // 1. Render template HTML
    ob_start();
    include 'generate-imp-ben-rep.php';
    $html = ob_get_clean();

    // 3. Simpan ke server
    $filename = 'laporan_' . date('Ymd_His') . '.pdf';
    $filepath = 'reports/' . $filename;
    $mpdf->WriteHTML($html);
    $pdfContent = $mpdf->Output('', 'S');

    // Simpan ke file
    file_put_contents($filepath, $pdfContent);

    $update_q = "UPDATE benefit_imp_report SET file = '$filepath' WHERE id = $bir_id";
    $update_exec = $conn->query($update_q);

    $checke_approval_q = "SELECT * FROM bir_approval WHERE bir_id = $bir_id";
    $checke_approval_exec = $conn->query($checke_approval_q);

    $approval = $checke_approval_exec->fetch_assoc();
    $approval_id = $approval['id'] ?? null;
    $new_token = bin2hex(random_bytes(16));
    if($approval_id) {
        $update_q = "UPDATE bir_approval SET status = 0, id_user_approver = $leaderId, token = '$new_token' WHERE bir_id = $bir_id";
        $update_exec = $conn->query($update_q);
    }else {
        $insert_q = "INSERT INTO bir_approval (bir_id, date, status, id_user_approver, token) VALUES ($bir_id, current_timestamp(), 0, $leaderId, '$new_token')";
        $insert_exec = $conn->query($insert_q);
    }

    $mail = new PHPMailer(true);

    $message = "
                    <style>
                        * {
                            font-family: Helvetica, sans-serif;
                        }
                        .container {
                            width: 80%;
                            margin: auto;
                        }
                    </style>

                    <div class='container'>
                        <p>
                            $ec_name! Telah mengajukan permintaan laporan penggunaan benefit <strong>$uc_program</strong> untuk <strong>$school_name</strong> 
                        </p>
                        <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                        <p style='margin: 20px 0px;'>
                            <a href='https://mentarigroups.com/benefit/approve_rep_req.php?token=$new_token' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>
                                Redirect me!
                            </a>
                        </p>
                        <div style='border-bottom: 1px solid #ddd;'></div>
                        <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                        <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve_rep_req.php?token=$new_token</p>
                        <div style='text-align: center; margin-top: 35px;'>
                            <span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span>
                        </div>
                    </div>
                ";
    $mail->isSMTP(); 
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $config['port'] ?? 465;

    //Recipients
    $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');

    $mail->addAddress($leaderEmail, $leaderName);
    $mail->addAttachment($filepath, $filename);
    //Content
    $mail->isHTML(true);
    $uc_program = strtoupper($program);
    $mail->Subject = 'Keren, '.$ec_name.' telah mengajukan request laporan manfaat '.$uc_program.' untuk '.$school_name;
    $mail->Body    = $message;
    $mail->send();

    echo json_encode([
        'status' => 'Success',
        'message' => 'Request saved successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}



exit;
