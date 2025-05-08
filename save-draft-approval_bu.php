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
        $mail->Host       = $config['host']; 
        $mail->SMTPAuth   = true; 
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'] ?? 465;

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
        $_SESSION['toast_msg'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
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

    try {
        mysqli_begin_transaction($conn);
        
        $token              = ISSET($_POST['token']) ? $_POST['token'] : '';
        $status             = ISSET($_POST['status']) ? $_POST['status'] : '';
        $id_draft           = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : '';
        $id_user            = ISSET($_POST['id_user']) ? $_POST['id_user'] : '';
        $id_draft_approval  = ISSET($_POST['id_draft_approval']) ? $_POST['id_draft_approval'] : '';
        $notes              = ISSET($_POST['notes']) ? $_POST['notes'] : '';
        $approver_id        = $_SESSION['id_user'];

        $status_msg = $status == 1 ? 'Approve' : 'Reject';

        $url_redirect = $approver_id == 70 || $approver_id == 5 || $approver_id == 15 ? 'Location: ./approved_list.php' : 'Location: ./draft-approval-list.php';

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
                        db.fileUrl, ec.username, ec.generalname, ec.id_user as ec_id_user, leader.id_user as leader_id, db.year, db.verified, db.status,
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
                $year                       = $dra['year'];
                $verified                   = $dra['verified'];
                $draft_status               = $dra['status'];
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
                    $leademail = 'tuankrab31@gmail.com';
                    $leadname = 'Approve pertama oleh supervisior 1 atau 2 untuk leader dan punya lead 2';   
                }

                // this is for ec mail
                $subject = $year ==  1 ? "Mantap! Formulir PK $school_name kamu sudah disetujui oleh Leader" : "Mantap! Formulir Perubahan PK Tahun Ke-$year $school_name kamu sudah disetujui oleh Leader";
                $message = "<p>Mantap! Formulir kamu sudah disetujui oleh Leader, sekarang kita akan ajukan formulir kerja sama $program untuk $school_name ke Leader $leadname.</p><p> Jika ada yang perlu direvisi atau disetujui, kita bakal kasih tau kamu lewat email.</p><p> Terima kasih.</p>";

                sendEmail('tuankrab31@gmail.com', 'approve pertama untuk ec', $subject, $message, $config, $fileUrl);
                
                $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leadid2."', '0');";
                mysqli_query($conn,$sql);

                // this is for lead ec mail
                $subject = $year ==  1 ? "Keren $approver_name telah menyetujui formulir $program untuk $school_name" : "Keren $approver_name telah menyetujui formulir perubahan PK Tahun Ke-$year $program untuk $school_name.";
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
                $cc = [];
                $leademail  = 'tuankrab31@gmail.com';
                $leadname   = 'approve pertama jika tidak ada lead 1 atau approve kedua tanpa lead 1 untuk ke putri';

                $sql = "SELECT * from user where id_user = $leadid3";
                $result = mysqli_query($conn,$sql);
                while ($dra = $result->fetch_assoc()){
                    $cc[] = [
                        'email' => $dra['username'],
                        'name' => $dra['generalname']
                    ];
                }
                
                $cc[] = [
                    'email' => "kelly@mentarigroups.com",
                    'name' => "Kelly"
                ];

                if($leadid3 == 16) {
                    $cc[] = [
                        'email' => "santo@mentaribooks.com",
                        'name' => "Santo"
                    ];
                }else {
                    $cc[] = [
                        'email' => "dwinanto@mentaribooks.com",
                        'name' => "Dwinanto"
                    ];
                }

                $subject    = 'Mentari Benefit | Formulir '.$school_name.' sudah disetujui oleh leader';
                $message    = $year == 1 ? "Yeay, formulir $program buat $school_name sudah disetujui oleh leader! Menunggu pengecekan dari secretary dan persetujuan top leader" : "Yeay, formulir perubahan PK Tahun Ke-$year $program buat $school_name sudah disetujui oleh leader! Menunggu pengecekan dari secretary dan persetujuan top leader";
                
                if(ISSET($saemail)) {
                    $sa_name = explode('@', $saemail)[0];
                    $cc[] = [
                        'email' => $saemail,
                        'name' => $sa_name
                    ];
                }
                sendEmail('tuankrab31@gmail.com', 'unrtuk ec stlh approve lv 2', $subject, $message, $config, $fileUrl);
        
                $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '70', '0');";
                mysqli_query($conn,$sql);
                
                // this is for top lead mail
                $subject = $year == 1 ? "Mentari Benefit | Formulir $school_name sedang menunggu pengecekan dan persetujuan Anda" : "Mentari Benefit | Formulir perubahan PK Tahun Ke-$year $school_name sedang menunggu persetujuan Anda";
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
                $subject = $year == 1 ? "Yeay, formulir $school_name sudah disetujui!" : "Yeay, formulir perubahan PK Tahun Ke-$year $school_name sudah disetujui!";
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

                sendEmail('tuankrab31@gmail.com', 'di approve final lead 3', $subject, $message, $config, $fileUrl);

                $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '70', '0');";
                mysqli_query($conn,$sql);

                $sql            = "UPDATE draft_benefit set status = 1 where id_draft = '$id_draft';";
                mysqli_query($conn, $sql);
            }

            if(($approver_id == 70 || $approver_id == 15) && $draft_status == 1) {
                $sql = "UPDATE draft_benefit set verified = 1 where id_draft = '$id_draft';";
                mysqli_query($conn, $sql);       
                $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '5', '0');";
                mysqli_query($conn,$sql);

                $subject = $year == 1 ? "Program $program di $school_name Telah Berhasil diverifikasi" : "Program perubahan PK Tahun Ke-$year di $school_name Telah Berhasil diverifikasi";
                $message = "<p>Kami ingin menginformasikan bahwa program $program untuk $school_name telah berhasil diverifikasi oleh Marketing Secretary.</p>
                            <p> Mohon untuk segera mengecek dan konfirmasi program tersebut.</p>
        
                            <p>Sarangheyo, Kamsahamnida💖💖💖</p>";
        
        
                sendEmail('tuankrab31@gmail.com', 'approve untuk verify', $subject, $message, $config, $fileUrl);
            }else if(($approver_id == 70 || $approver_id == 15) && $draft_status == 0) {
                $sql = "SELECT * from user where id_user = $leadid3";
                $result = mysqli_query($conn,$sql);
                while ($dra = $result->fetch_assoc()){
                    $leademail  = 'tuankrab31@gmail.com';
                    $leadname   = 'approve lvl 3 untuk bdb setelah putri cek utk bdb';   
                }

                $subject    = 'Mentari Benefit | Formulir '.$school_name.' sudah diperiksa oleh secretary';
                $message    = $year == 1 ? "Yeay, formulir $program buat $school_name sudah diperiksa oleh secretary! Menunggu persetujuan top leader" : "Yeay, formulir perubahan PK Tahun Ke-$year $program buat $school_name sudah diperiksa oleh secretary! Menunggu persetujuan top leader";
                
                if(ISSET($saemail)) {
                    $sa_name = explode('@', $saemail)[0];
                    $cc[] = [
                        'email' => $saemail,
                        'name' => $sa_name
                    ];
                }
                sendEmail('tuankrab31@gmail.com', 'approve lvl 3 untuk bdb setelah putri cek utk bdb untuk ec', $subject, $message, $config, $fileUrl);
        
                $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leadid3."', '0');";
                mysqli_query($conn,$sql);
                
                // this is for top lead mail
                $subject = $year == 1 ? "Mentari Benefit | Formulir $school_name sedang menunggu persetujuan Anda" : "Mentari Benefit | Formulir perubahan PK Tahun Ke-$year $school_name sedang menunggu persetujuan Anda";
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
            }else if($approver_id == 5) {
                $sql = "UPDATE draft_benefit set confirmed = 1 where id_draft = '$id_draft';";
                mysqli_query($conn, $sql);

                $sql = "SELECT 
                            dbl.id_benefit_list AS target_benefit_list, -- current draft
                            dbl2.id_benefit_list AS source_benefit_list, -- from ref_id
                            bu.user_id,
                            bu.description,
                            bu.qty1,
                            bu.qty2,
                            bu.qty3,
                            bu.used_at,
                            bu.redeem_code
                        FROM draft_benefit db
                        LEFT JOIN draft_benefit_list dbl ON dbl.id_draft = db.id_draft
                        LEFT JOIN draft_benefit_list dbl2 ON dbl2.id_draft = db.ref_id AND dbl.id_template = dbl2.id_template
                        LEFT JOIN benefit_usages bu ON bu.id_benefit_list = dbl2.id_benefit_list
                        WHERE db.confirmed = 1
                        AND db.id_draft = $id_draft
                        AND bu.id_benefit_list IS NOT NULL
                    ";

                $result = mysqli_query($conn, $sql);

                if (!$result) {
                    die("Query failed: " . mysqli_error($conn));
                }

                while ($row = mysqli_fetch_assoc($result)) {
                    $target_id = $row['target_benefit_list'];
                    $user_id = $row['user_id'];
                    $description = mysqli_real_escape_string($conn, $row['description']);
                    $qty1 = $row['qty1'] ?? 0;
                    $qty2 = $row['qty2'] ?? 0;
                    $qty3 = $row['qty3'] ?? 0;
                    $used_at = $row['used_at'] ?? date('Y-m-d H:i:s');
                    $redeem_code = mysqli_real_escape_string($conn, $row['redeem_code']);

                    $insert = "INSERT INTO benefit_usages (id, id_benefit_list, user_id, description, qty1, qty2, qty3, used_at, redeem_code) 
                                VALUES (NULL, $target_id, '$user_id', '$description', '$qty1', '$qty2', '$qty3', '$used_at', '$redeem_code')
                            ";
                            
                    mysqli_query($conn, $insert);
                }

        
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
        
                $cc[] = [
                        'email' => $for_approve_cc_ec_email,
                        'name' => $for_approve_cc_ec_name
                ];
                
                while ($dra = $result->fetch_assoc()){   
                    $cc[] = [
                        'email' => $dra['username'],
                        'name' => $dra['generalname']
                    ];
                }
        
                $email = 'secretary@mentaribooks.com';
                $name = 'Putri';
        
                $subject = $year == 1 ? "Program $program di $school_name Telah Berhasil Dikonfirmasi" : "Program perubahan PK Tahun Ke-$year di $school_name Telah Berhasil Dikonfirmasi";
                $message = "<p>Kami ingin menginformasikan bahwa program $program untuk $school_name telah berhasil dikonfirmasi oleh Head of Sales Admin.</p>
                            <p> Namun, untuk manfaat PDMTA, Beasiswa S2, Studi Banding dan Assessment, Admin wajib melakukan konfirmasi ulang kepada AR/ACCT terkait dengan ketentuan pembayaran sekolah.</p>
        
                            <p>Mohon untuk memperbarui data secara berkala di MBS selama perencanaan dan implementasi benefit berlangsung.<p>
        
                            <p>Sarangheyo, Kamsahamnida💖💖💖</p>";
        
        
                sendEmail('tuankrab31@gmail.com', 'confirm oleh nopi', $subject, $message, $config, $fileUrl);
            }

        }else if($status == 2) {
            $mail = new PHPMailer(true);

            // this is for ec mail when rejected
            $subject = $year == 1 ? "Pengajuan $uc_program $school_name BELUM DISETUJUI" : "Pengajuan perubahan PK Tahun Ke-$year $school_name BELUM DISETUJUI";
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

            sendEmail('tuankrab31@gmail.com', 'reject', $subject, $message, $config, $fileUrl);
            $sql = "UPDATE draft_benefit set status = 2, verified = 0 where id_draft = '$id_draft';";
            mysqli_query($conn, $sql);

            $sql = "INSERT INTO `approval_reject_history` (`id`, `id_draft`, `id_user`, `id_user_approver`, `note`, `created_at`) VALUES (NULL, '$id_draft', $id_ec, $approver_id, '$notes', current_timestamp());";
            mysqli_query($conn,$sql);
        }
        mysqli_commit($conn);
        $_SESSION['toast_status']   = "Success";
        $_SESSION['toast_msg']      = "Berhasil $status_msg Draft Benefit";
        header($url_redirect);

        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status']   = "Error";
        $_SESSION['toast_msg']      = "Failed " . json_encode($th->getMessage());
        header($url_redirect);

        exit();
    }
    