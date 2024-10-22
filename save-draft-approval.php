<?php
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$config = require 'config.php';

ob_start();
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendEmail($email, $name, $subject, $message, $config, $fileUrl, $cc = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); 
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true; 
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
        $file_path = 'draft-benefit/'.$fileUrl.'.xlsx';
        
        if (file_exists($file_path)) {
            $mail->addAttachment($file_path, $fileUrl.'.xlsx');
        }

        $mail->addAddress($email, $name);
        if(count($cc) > 0) {
            foreach ($cc as $key => $value) {
                $mail->addCC($value['email'], $value['name']);
            }
            // $mail->addCC($leademail,$leadname);
            // $mail->addCC("novitasari@mentaribooks.com", "Novi / Mentaribooks");
        }

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();
        
    } catch (Exception $e) {
        $_SESSION['toast_status'] = "Error";
        $_SESSION['toast_msg'] = "Failed send e-mail to $email";
        header('Location: ./draft-approval-list.php');
        exit();
    }
}

    if(!$_POST){
        $_SESSION['toast_status'] = "Error";
        $_SESSION['toast_msg'] = "Unauthorized Access";
        header('Location: ./draft-approval-list.php');
        exit();
    }

    $token              = ISSET($_POST['token']) ? $_POST['token'] : '';
    $status             = ISSET($_POST['status']) ? $_POST['status'] : '';
    $id_draft           = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : '';
    $id_user            = ISSET($_POST['id_user']) ? $_POST['id_user'] : '';
    $id_draft_approval  = ISSET($_POST['id_draft_approval']) ? $_POST['id_draft_approval'] : '';
    $notes              = ISSET($_POST['notes']) ? $_POST['notes'] : '';
    $approver_id        = $_SESSION['id_user'];

    $status_msg = $status == 1 ? 'Approve' : 'Reject';

    $url_redirect = $approver_id == 70 ? 'Location: ./approved_list.php' : 'Location: ./draft-approval-list.php';

    date_default_timezone_set('Asia/Jakarta');
    $current_time = date('Y-m-d H:i:s');

    $sql = "UPDATE draft_approval 
            SET status = '$status', 
                notes = '$notes', 
                approved_at = '$current_time'
            WHERE token = '$token' AND id_user_approver = '$approver_id'";

    mysqli_query($conn, $sql);
    $leadid     =  null;
    $uc_program = '';

    $tokenLeader = generateRandomString(16);
    $for_approve_cc_ec_email    = '';
    $for_approve_cc_ec_name     = '';
    $cc                         = [];

    if(mysqli_affected_rows($conn) == 1){
        // $sql = "select * from draft_benefit a left join user b on a.id_user = b.id_user where id_draft = $id_draft";
        $sql = "SELECT 
                    db.fileUrl, ec.username, ec.generalname, ec.id_user as ec_id_user, leader.id_user as leader_id, 
                    ec.leadid, ec.leadid2, ec.leadid3, IFNULL(sc.name, db.school_name) as school_name, db.program, ec.sa_email, leader.generalname as approver_name
                FROM draft_approval da 
                LEFT JOIN draft_benefit db on db.id_draft = da.id_draft
                LEFT JOIN schools as sc on sc.id = db.school_name 
                LEFT JOIN user leader on da.id_user_approver = leader.id_user
                LEFT join user as ec on ec.id_user = db.id_ec  
                WHERE da.token = '$token'
                AND db.id_draft = $id_draft";
        $result = mysqli_query($conn,$sql);

        while ($dra = $result->fetch_assoc()){
            $fileUrl                    = $dra['fileUrl'];
            $email                      = $dra['username'];
            $for_approve_cc_ec_email    = $dra['username'];
            $school_name                = $dra['school_name'];
            $program                    = $dra['program'];
            $ecname                     = $dra['generalname'];
            $for_approve_cc_ec_name     = $dra['generalname'];
            $approver_name              = $dra['approver_name'];
            $saemail                    = $dra['sa_email'];
            $leadid                     = $dra['leadid'];
            $leadid2                    = $dra['leadid2'];
            $leadid3                    = $dra['leadid3'];
            $id_ec                      = $dra['ec_id_user'];
        }
    }else{
        $_SESSION['toast_status'] = "Error";
        $_SESSION['toast_msg'] = "Sudah pernah approve. Operation terminated";
        header($url_redirect);
        exit();
    }

    $uc_program = ISSET($program) ? strtoupper($program) : '';

    if($status == 1) {
        
        if($leadid == $approver_id && $leadid2) {
            $sql = "SELECT * from user where id_user = $leadid2";
            $result = mysqli_query($conn,$sql);
            while ($dra = $result->fetch_assoc()){
                $leademail = $dra['username'];
                $leadname = $dra['generalname'];   
            }

            // this is for ec mail
            $subject = "Mantap! Formulir PK $school_name kamu sudah disetujui oleh Leader";
            $message = "<p>Mantap! Formulir kamu sudah disetujui oleh Leader, sekarang kita akan ajukan formulir kerja sama $program untuk $school_name ke Leader $leadname.</p><p> Jika ada yang perlu direvisi atau disetujui, kita bakal kasih tau kamu lewat email.</p><p> Terima kasih.</p>";

            sendEmail($email, $ecname, $subject, $message, $config, $fileUrl);
            
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leadid2."', '0');";
            mysqli_query($conn,$sql);

            // this is for lead ec mail
            $subject    = "Keren $approver_name telah menyetujui formulir $program untuk $school_name";
            $message    = "
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
                                        $ecname telah mengajukan formulir <strong>$uc_program</strong> untuk <strong>$school_name</strong> 
                                    </p>
                                    <p>Ayo, cepat-cepat dicek agar bisa segera diajukan ke Top Leader! Sukses untuk kita bersama! 👍😊</p>
                                    <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                                    <p style='margin: 20px 0px;'>
                                        <a href='https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$tokenLeader' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>
                                            Redirect me!
                                        </a>
                                    </p>
                                    <div style='border-bottom: 1px solid #ddd;'></div>
                                    <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                                    <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$tokenLeader</p>
                                    <div style='text-align: center; margin-top: 35px;'>
                                        <span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span>
                                    </div>
                                </div>
                            ";
            sendEmail($leademail, $leadname, $subject, $message, $config, $fileUrl);
            
            $mail = new PHPMailer(true);
            
        }else if(($leadid2 == $approver_id) || ($leadid == $approver_id && !$leadid2)) {
            $sql = "SELECT * from user where id_user = $leadid3";
            $result = mysqli_query($conn,$sql);
            while ($dra = $result->fetch_assoc()){
                $leademail  = $dra['username'];
                $leadname   = $dra['generalname'];   
            }

            $subject    = 'Mentari Benefit | Formulir '.$school_name.' sudah disetujui oleh leader';
            $message    = 'Yeay, formulir '.$program.' buat '.$school_name.' sudah disetujui oleh leader! Menunggu persetujuan top leader';
            
            if(ISSET($saemail)) {
                $sa_name = explode('@', $saemail)[0];
                $cc[] = [
                    'email' => $saemail,
                    'name' => $sa_name
                ];
            }
            sendEmail($email, $ecname, $subject, $message, $config, $fileUrl, $cc);
    
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leadid3."', '0');";
            mysqli_query($conn,$sql);
            
            // this is for top lead mail
            $subject = 'Mentari Benefit | Formulir '.$school_name.' sedang menunggu persetujuan Anda';
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
                                $ecname telah mengajukan formulir <strong>$uc_program</strong> untuk <strong>$school_name</strong> 
                            </p>
                            <p>Wah, seru banget nih! $ecname sudah menunggu kamu untuk memeriksa formulir $uc_program di $school_name. Jika ada beberapa hal yang belum disetujui, berikan arahan dan masukan dengan baik dan konstruktif untuk membantu tim meningkatkan formulirnya.</p>
                            <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                            <p style='margin: 20px 0px;'>
                                <a href='https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$tokenLeader' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>
                                    Redirect me!
                                </a>
                            </p>
                            <div style='border-bottom: 1px solid #ddd;'></div>
                            <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                            <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$tokenLeader</p>
                            <div style='text-align: center; margin-top: 35px;'>
                                <span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span>
                            </div>
                        </div>
                    ";

            sendEmail($leademail, $leadname, $subject, $message, $config, $fileUrl);
        }else if($leadid3 == $approver_id) {
            //mail for ec
            $subject = "Yeay, formulir $school_name sudah disetujui!";
            $message = "<p>Yeay, formulir $uc_program buat $school_name sudah disetujui! Sekarang kamu bisa download formulirnya dan ajukan ke divisi terkait dengan happy-happy!</p>";
            
            $cc = [];
            if(ISSET($saemail)) {
                $sa_name = explode('@', $saemail)[0];
                $cc[] = [
                    'email' => $saemail,
                    'name' => $sa_name
                ];
            }
            $cc[] = [
                    'email' => "novitasari@mentaribooks.com",
                    'name' => "Novi / Mentaribooks"
            ];
            
            $cc[] = [
                'email' => "ar@mentaribooks.com",
                'name' => "AR"
            ];

            $cc[] = [
                'email' => "secretary@mentaribooks.com",
                'name' => "Putri"
            ];

            sendEmail($email, $ecname, $subject, $message, $config, $fileUrl, $cc);

            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '70', '0');";
            mysqli_query($conn,$sql);
        }

        $choosen_status = ($approver_id == $leadid || $approver_id == $leadid2) ? 0 : 1;
        $sql            = "UPDATE draft_benefit set status = '$choosen_status' where id_draft = '$id_draft';";
        mysqli_query($conn, $sql);

        if($approver_id == 70) {
            $sql = "UPDATE draft_benefit set verified = 1 where id_draft = '$id_draft';";
            mysqli_query($conn, $sql);       
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '5', '0');";
            mysqli_query($conn,$sql);
        }else if($approver_id == 5) {
            $sql = "UPDATE draft_benefit set confirmed = 1 where id_draft = '$id_draft';";
            mysqli_query($conn, $sql);
    
            $sql = "SELECT 
                        db.*, 
                        dbl.id_template, 
                        br.code, 
                        IFNULL(sc.name, db.school_name) AS school_name2, 
                        user.*
                    FROM 
                        draft_benefit db 
                    LEFT JOIN 
                        draft_benefit_list dbl ON db.id_draft = dbl.id_draft
                    LEFT JOIN 
                        benefit_role br ON br.id_template = dbl.id_template 
                    LEFT JOIN 
                        schools AS sc ON sc.id = db.school_name
                    LEFT JOIN 
                        user AS user ON 
                            (CASE 
                                WHEN br.code = 'mkt' THEN user.role = 'admin' 
                                ELSE user.role = br.code 
                            END)
                    WHERE 
                        db.verified = 1
                        AND db.id_draft = $id_draft
                    GROUP BY 
                        br.code;
                    ";
            $result                 = mysqli_query($conn,$sql);
            $unit_bussiness_code    = [];
            $program                = '';
            $school_name            = '';
            $fileUrl                = '';
            while ($dra = $result->fetch_assoc()){
                $unit_bussiness_code[]  = $dra['code'];
                $program                = strtoupper($dra['program']);
                $school_name            = $dra['school_name2'];
                $fileUrl                = $dra['fileUrl'];
            }
    
            $unit_bussiness_code = implode("','", $unit_bussiness_code);
    
            $sql = "SELECT * FROM user WHERE role IN ('$unit_bussiness_code')";
    
            $result = mysqli_query($conn, $sql);
    
            $cc = [
                [
                    'email' => $for_approve_cc_ec,
                    'name' => $for_approve_cc_ec_name
                ]
            ];
            
            while ($dra = $result->fetch_assoc()){   
                $cc[] = [
                    'email' => $dra['username'],
                    'name' => $dra['generalname']
                ];
            }
    
            $email = 'tuankrab31@gmail.com';
            $name = 'Putri';
    
            $subject = "Program $program di $school_name Telah Berhasil Dikonfirmasi";
            $message = "<p>Kami ingin menginformasikan bahwa program $program untuk $school_name telah berhasil dikonfirmasi oleh Head of Sales Admin.</p>
                        <p> Namun, untuk manfaat PDMTA, Beasiswa S2, Studi Banding dan Assessment, Admin wajib melakukan konfirmasi ulang kepada AR/ACCT terkait dengan ketentuan pembayaran sekolah.</p>
    
                        <p>Mohon untuk memperbarui data secara berkala di MBS selama perencanaan dan implementasi benefit berlangsung.<p>
    
                        <p>Sarangheyo, Kamsahamnida💖💖💖</p>";
    
    
            sendEmail($email, $name, $subject, $message, $config, $fileUrl, $cc);
        }

    }else if($status == 2) {
        $mail = new PHPMailer(true);

        // this is for ec mail when rejected
        $subject = "Pengajuan $uc_program $school_name BELUM DISETUJUI";
        $message = 'Wah, sedikit lagi nih! Formulir yang kamu ajukan masih perlu diperbaiki. SEGERA Lakukan revisi sesuai notes dari Leader kamu dan ajukan kembali ke Top Leader. Kamu pasti bisa, semangat ya!';
        $approver_query = "SELECT u.generalname as name, u.username as email 
                            FROM `draft_approval` AS da 
                            LEFT JOIN user as u on u.id_user = da.id_user_approver
                            where da.id_draft = $id_draft AND da.status = 1";

        $result = $conn->query($approver_query);
        
        $cc = [];
        if ($result->num_rows > 0) {  
          while ($row = $result->fetch_assoc()) {
            $cc[] = [
                'email' => $row['email'],
                'name' => $row['name']
            ];
          }
        }

        sendEmail($email, $ecname, $subject, $message, $config, $fileUrl, $cc);
        $sql = "UPDATE draft_benefit set status = 2, verified = 0 where id_draft = '$id_draft';";
        mysqli_query($conn, $sql);

        $sql = "INSERT INTO `approval_reject_history` (`id`, `id_draft`, `id_user`, `id_user_approver`, `note`, `created_at`) VALUES (NULL, '$id_draft', $id_ec, $approver_id, '$notes', current_timestamp());";
        mysqli_query($conn,$sql);
    }
    
    $_SESSION['toast_status']   = "Success";
    $_SESSION['toast_msg']      = "Berhasil $status_msg Draft Benefit";
    if($approver_id == 70 || $approver_id == 5) {
        header('Location: ./approved_list.php');
    }else {
        header($url_redirect);
    }

    exit();