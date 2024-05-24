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
    if($_GET['tok'] == ''){
        echo 'Invalid Token'; 
        exit();
    } 
    
    $token = $_GET['tok'];
    //$token = (preg_match('/^[a-zA-Z0-9]{0,5}$/',$token) ? $token : "");
    $status = (int)$_GET['stat'];
    $id_draft = (int) $_GET['idr'];
    $sql = "UPDATE draft_approval set status = $status where token = '".$token."'";
    mysqli_query($conn, $sql);
    $sql = "UPDATE draft_benefit set status = $status where id_draft = '$id_draft';";
    mysqli_query($conn, $sql);
    $leadid =  null;
    if(mysqli_affected_rows($conn) == 1){
        $sql = "select * from draft_benefit a left join user b on a.id_user = b.id_user where id_draft = $id_draft";
        $result = mysqli_query($conn,$sql);

        while ($dra = $result->fetch_assoc()){
            $fileUrl = $dra['fileUrl'];
            $email  = $dra['username'];
            $school_name = $dra['school_name'];
            $program = $dra['program'];
            $ecname = $dra['generalname'];
            $saemail = $dra['sa_email'];
            $leadid = $dra['leadid'];
        }
    }
    if(!is_null($leadid)){
        $sql = "select * from user where id_user = $leadid";
        $result = mysqli_query($conn,$sql);
        while ($dra = $result->fetch_assoc()){
            $leademail  = $dra['username'];
            $leadname = $dra['generalname'];   
        }
    }else{
        $leademail='dwinanto@mentaribooks.com';
        $leadname = 'Dwinanto';
    }

    if($status == 1){
        $mail = new PHPMailer(true);
        try {
            //Server settings                     //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $config['smtp_username'];            //SMTP username
            $mail->Password   = $config['smtp_password'];                   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            //Recipients
            $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
            $mail->addAttachment('draft-benefit/'.$fileUrl.'.xlsx',$fileUrl.'.xlsx');
            $mail->addAddress($email,$ecname);
            // $mail->addCC($saemail,$ecname);
            $mail->addCC($leademail,$leadname);
            $mail->addCC("novitasari@mentaribooks.com","Novi / Mentaribooks");
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Mentari Benefit | Formulir '.$school_name.' sudah disetujui';
            $mail->Body    = 'Yeay, formulir '.$program.' buat '.$school_name.' sudah disetujui! Sekarang kamu bisa download formulirnya dan ajukan ke divisi terkait dengan happy-happy!';
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
            $mail->Host       = 'smtp.gmail.com';                   //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                               //Enable SMTP authentication
            $mail->Username   = $config['smtp_username'];    //SMTP username
            $mail->Password   = $config['smtp_password'];                  //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        //Enable implicit TLS encryption
            $mail->Port       = 465;                                //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
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

    echo "Operation completed successfully";
    echo "<script>
            setTimeout(function() {
                window.close()
            }, 2000);
            </script>";
    exit();