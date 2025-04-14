<?php
    ob_start();
    session_start();
    include 'db_con.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    require 'vendor/autoload.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $peserta = $_POST['name'];
    $id_master = $_POST['id_master'];
    $tanggal = $_POST['tanggal'];
    $qty = $_POST['member'];
    $qty2 = $_POST['member2'];
    $qty3 = $_POST['member3'];
    $benefit_name = str_replace("'","",$_POST['benefit_name']);
    $subbenefit = str_replace("'","",$_POST['subbenefit']);
    $descript = str_replace("'","",$_POST['descript']);
    $type= $_POST['type'];
    $id_template_benefit= $_POST['id_template_benefit'];
    $pelaksanaan = $_POST['pelaksanaan'];
    if($_POST['jamValue']>0)
    {
        $jamValue = $_POST['jamValue'];
    }
    else
    {
        $jamValue=0;
    }
    if($_POST['manualValue']>0)
    {
        $manualValue = $_POST['manualValue'];
    }
    else
    {
        $manualValue=0;
    }
    
    if($tanggal=='')
    {
        $tanggal='0000-00-00';
    }
    

    $sql = "INSERT INTO `op_simple_benefit` (`id_benefit`, `id_master`, `status`, `isDeleted`, `progress_update`, `tanggal`, `benefit_name`, `subbenefit`, `description`, `qty`,qty2,qty3,`jamValue`,`manualValue`,`pelaksanaan`,`type`) VALUES (NULL, '$id_master', '1', '0', '0', '".$tanggal."', '".$benefit_name."', '".$subbenefit."', '".$descript."', '$qty',,'$qty2','$qty3', '$jamValue', '$manualValue','$pelaksanaan','$type');";
    mysqli_query($conn,$sql);

    //hitung kuota
    $sql = "update op_new_benefit set qty=qty-$qty where id_master='$id_master' and id_template_benefit='$id_template_benefit' and approval = 1;";

    mysqli_query($conn,$sql);
    

    $sql = "SELECT * FROM `op_masterdata` a left join dash_ec b on a.id_ec=b.id_ec where a.id_master=$id_master";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
    // output data of each row
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
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'mentarigroups.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'benefit@mentarigroups.com';                     //SMTP username
        $mail->Password   = 'BenefitMentari2023';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = $config['port'] ?? 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('benefit@mentarigroups.com', 'Benefit Auto Mailer');
        
        $mail->addAddress($ec_email, $ec_name);     //Add a recipient
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Pemberian Manfaat '.$subbenefit.' untuk '.$school_name;
        $mail->Body    = '<b>Pemberitahuan otomatis melalui Mentari Benefit System</b><br><br>Pemberian manfaat '.$school_name.' dari program '.$jenDok.' manfaat '.$subbenefit.' '.$benefit_name.' sudah diberikan ke sekolah dengan rincian '.$descript;
    
        $mail->send();
        
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    mysqli_close($conn);
    header('Location: ./new-benefit-input.php?type='.$type);
    exit();
    
?>