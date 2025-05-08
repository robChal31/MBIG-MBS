<?php
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
$config = require 'config.php';
    echo "Mohon tunggu sebentar";
    echo "<br />";
    ob_start();
    session_start();
    
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if($_GET['tok']==''){
        echo 'Invalid Token'; 
        exit();
    } 
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    $token = $_GET['tok'];
    //$token = (preg_match('/^[a-zA-Z0-9]{0,5}$/',$token) ? $token : "");
    $status = (int)$_GET['stat'];
    $id_draft = (int) $_GET['idr'];
    $sql = "UPDATE draft_approval set status = $status where token = '".$token."'";
    mysqli_query($conn,$sql);
    $leadid =  null;
    if(mysqli_affected_rows($conn) == 1){
        // $sql = "select * from draft_benefit a left join user b on a.id_user = b.id_user where id_draft = $id_draft";
        $sql = "SELECT 
                    db.fileUrl, ec.username, ec.generalname, ec.id_user as ec_id_user, leader.id_user as leader_id, 
                    leader.leadid, db.school_name, db.program, leader.sa_email, leader.generalname as approver_name
                FROM draft_approval da 
                LEFT JOIN draft_benefit db on db.id_draft = da.id_draft 
                LEFT JOIN user leader on da.id_user_approver = leader.id_user
                LEFT join user as ec on ec.id_user = db.id_ec  
                WHERE da.token = '$token'
                AND db.id_draft = $id_draft";
        $result = mysqli_query($conn,$sql);

        while ($dra = $result->fetch_assoc()){
            $fileUrl = $dra['fileUrl'];
            $email  = $dra['username'];
            $school_name = $dra['school_name'];
            $program = $dra['program'];
            $ecname = $dra['generalname'];
            $approver_name = $dra['approver_name'];
            $saemail = $dra['sa_email'];
            $leadid = $dra['leadid'];
        }
    }else{
        exit("Sudah pernah approve. Operation terminated");
    }

    if(!is_null($leadid) && $leadid != 16){
        $sql = "select * from user where id_user = $leadid";
        $result = mysqli_query($conn,$sql);
        while ($dra = $result->fetch_assoc()){
            $leademail = $dra['username'];
            $leadname = $dra['generalname'];   
        }
        $mail = new PHPMailer(true);
        try {
            //Server settings                     //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $config['host'];                    //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $config['smtp_username'];            //SMTP username
            $mail->Password   = $config['smtp_password'];                   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            //Recipients
            $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
            $mail->addAttachment('draft-benefit/'.$fileUrl.'.xlsx',$fileUrl.'.xlsx');
            $mail->addAddress($email, $ecname);
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Mentari Benefit | Formulir $school_name sudah disetujui oleh $approver_name";
            $mail->Body    = "Yeay, formulir $program buat $school_name sudah disetujui oleh leader! Menunggu persetujuan oleh $leadname";
            $mail->send();
            
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        $tokenLeader = generateRandomString(16);
        $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leadid."', '0');";
        mysqli_query($conn,$sql);
    
        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $config['host'];                   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $config['smtp_username'];            //SMTP username
            $mail->Password   = $config['smtp_password'];                   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
            
            $mail->addAddress($leademail,$leadname);     //Add a recipient
            //$mail->addCC($_SESSION['username'],$_SESSION['generalname']);
            $mail->addAttachment('draft-benefit/'.$fileUrl.'.xlsx',$fileUrl.'.xlsx');
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Keren, '.$approver_name.' telah menyetujui formulir '.$program.' untuk '.$school_name;
            $mail->Body    = $ecname.'! Telah mengajukan formulir '.$program.' untuk '.$school_name.'. Ayo, cepat dicek agar bisa segera diajukan ke Top Leader! Jangan lupa untuk memberikan semangat dan dukungan untuk tim kamu, ya! Sukses untuk kita bersama!
            <br>Apabila sudah sesuai, silakan klik tombol berikut untuk approval.
            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
            <tr>
                <td align="center" bgcolor="#19cca3" role="presentation" style="border:none;border-radius:6px;cursor:auto;padding:11px 20px;background:#19cca3;" valign="middle">
                <a href="https://mentarigroups.com/benefit/draft-approval.php?tok='.$tokenLeader.'&stat=1&idr='.$id_draft.'" style="background:#19cca3;color:#ffffff;font-family:Helvetica, sans-serif;font-size:18px;font-weight:600;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
                    Approve / Setujui!
                </a>
                </td>
            </tr>
            </table>
            ';
            $mail->send();

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }else{
        $leademail='dwinanto@mentaribooks.com';
        $leadname = 'Dwinanto';

        if($status == 1){
            $mail = new PHPMailer(true);
            try {
                //Server settings                     //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = $config['host'];                   //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $config['smtp_username'];            //SMTP username
                $mail->Password   = $config['smtp_password'];                   //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                //Recipients
                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                $mail->addAttachment('draft-benefit/'.$fileUrl.'.xlsx',$fileUrl.'.xlsx');
                $mail->addAddress($email, $ecname);
                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Mentari Benefit | Formulir '.$school_name.' sudah disetujui oleh leader';
                $mail->Body    = 'Yeay, formulir '.$program.' buat '.$school_name.' sudah disetujui oleh leader! Menunggu persetujuan top leader';
                $mail->send();
                
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
    
            $tokenLeader = generateRandomString(16);
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '16', '0');";
            mysqli_query($conn,$sql);
    
            $mail = new PHPMailer(true);
            try {
                //Server settings                     //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = $config['host'];                    //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = $config['smtp_username'];            //SMTP username
                $mail->Password   = $config['smtp_password'];                   //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                //Recipients
                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                $mail->addAttachment('draft-benefit/'.$fileUrl.'.xlsx',$fileUrl.'.xlsx');
                $mail->addAddress($leademail,'Dwinanto Setiawan');
                //$mail->addCC($saemail,$ecname);
                //$mail->addCC($leademail,$leadname);
                // $mail->addCC("novitasari@mentaribooks.com","Novi / Mentaribooks");
                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Mentari Benefit | Formulir '.$school_name.' sedang menunggu persetujuan Anda';
                $mail->Body    = 'Telah mengajukan formulir '.$program.' untuk '.$school_name.'. Ayo, cepat dicek agar bisa segera dilanjutkan ke SA! Jangan lupa untuk memberikan semangat dan dukungan untuk tim kamu, ya! Sukses untuk kita bersama!
                <br>Apabila sudah sesuai, silakan klik tombol berikut untuk approval.
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
                <tr>
                    <td align="center" bgcolor="#19cca3" role="presentation" style="border:none;border-radius:6px;cursor:auto;padding:11px 20px;background:#19cca3;" valign="middle">
                    <a href="https://mentarigroups.com/benefit/draft-approval-top.php?tok='.$tokenLeader.'&stat=1&idr='.$id_draft.'" style="background:#19cca3;color:#ffffff;font-family:Helvetica, sans-serif;font-size:18px;font-weight:600;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
                        Approve / Setujui!
                    </a>
                    </td>
                </tr>
                </table>';
                $mail->send();
                
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }else{
            $mail = new PHPMailer(true);
            try {
                //Server settings
                        //Enable verbose debug output
                $mail->isSMTP();                                        //Send using SMTP
                $mail->Host       = $config['host'];                   //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                               //Enable SMTP authentication
                $mail->Username   = $config['smtp_username'];    //SMTP username
                $mail->Password   = $config['smtp_password'];                  //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        //Enable implicit TLS encryption
                $mail->Port       = $config['port'] ?? 465;                                //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            
                //Recipients
                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                $mail->addAddress($email,$ecname);
                
                //context
    
                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Mentari Benefit | Formulir '.$school_name.' masih perlu diperbaiki';
                $mail->Body    = 'Wah, sedikit lagi nih! Formulir yang kamu ajukan masih perlu diperbaiki. Tenang aja, kamu cukup berdiskusi dengan Leader kamu dan ajukan kembali ke Top Leader. Kamu pasti bisa, semangat ya!';
                $mail->send();
                
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }



    echo "Operation completed successfully";
    echo "<script>
    setTimeout(function() {
        window.close()
    }, 2000);
    </script>";
exit();