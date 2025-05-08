<?php
    ob_start();
    session_start();
    include 'db_con.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    require 'vendor/autoload.php';
    $config = require 'config.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $id_benefit = $_POST['id_benefit'];
    $keterangan = mysqli_escape_string($conn,$_POST['keterangan']);
    $keterangan = str_replace("'","",$keterangan);
    $sql = "update op_simple_benefit set keterangan='$keterangan' where id_benefit='$id_benefit'";
    mysqli_query($conn,$sql);
    
    $sql = "SELECT * FROM op_simple_benefit WHERE id_benefit='$id_benefit'";
    $result = mysqli_query($conn,$sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $subbenefit=$row['subbenefit'];
            $benefit_name=$row['benefit_name'];
            $descript = $row['description'];
            $id_master = $row['id_master'];
        }
    }


    $sql = "SELECT * FROM `op_masterdata` a left join dash_ec b on a.id_ec=b.id_ec where a.id_master=$id_master";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
         while($row = mysqli_fetch_assoc($result)) {
            $ec_email = $row['ec_email'];
            $ec_name = $row['ec_name'];
            $school_name = $row['school_name'];
            $jenDok = $row['jenDok'];
        }
    }

    $mail = new PHPMailer(true);
    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $config['host'];                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $config('smtp_username');                     //SMTP username
        $mail->Password   = $config('smtp_password');                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
        
        $mail->addAddress($ec_email, $ec_name);     //Add a recipient
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Pemberian Manfaat '.$subbenefit.' untuk '.$school_name;
        $mail->Body    = '<b>Pemberitahuan otomatis melalui Mentari Benefit System</b><br><br>Pemberian manfaat '.$school_name.' dari program '.$jenDok.' manfaat '.$subbenefit.' '.$benefit_name.' sudah diberikan ke sekolah dengan rincian '.$descript.' dan keterangan : '.$keterangan.'.';
        
        $mail->send();
        
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    mysqli_close($conn);
    
    

    exit();
    
?>