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
    $id_master = $_POST['idm'];
    $take = $_POST['take'];
    $benefit_name = $_POST['benefit_name'];
    $benefit = $_POST['benefit'];
    $subbenefit = $_POST['subbenefit'];
    $description = $_POST['description'];
    $pelaksanaan = $_POST['pelaksanaan'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $qty1 = $_POST['member'];
    $qty2 = $_POST['member2'];
    $qty3 = $_POST['member3'];
    $manualValue = $_POST['manualValue'];

    foreach($take as $taken)
    {
        $benefit_namex = str_replace("'","",$benefit_name[$taken]);
        $subbenefitx = str_replace("'","",$subbenefit[$taken]);
        $benefitx = str_replace("'","",$benefit[$taken]);
        $descriptionx = str_replace("'","",$description[$taken]);
        $pelaksanaanx = str_replace("'","",$pelaksanaan[$taken]);
        $tanggalx = $tanggal[$taken];
        $keteranganx = str_replace("'","",$keterangan[$taken]);
        $qty1x = $qty1[$taken];
        $qty2x = $qty2[$taken];
        $qty3x = $qty3[$taken];
        if($manualValue[$taken]>0)
        {
            $manualValuex = $manualValue[$taken];
        }
        else
        {
            $manualValuex=0;
        }
        
        if($tanggalx=='')
        {
            $tanggalx='0000-00-00';
        }

        if($_POST['act']=='edit')
        {
            mysqli_query($conn,"DELETE FROM op_simple_benefit where id_master='$id_master'");
        }
        $sql = "INSERT INTO `op_simple_benefit` (`id_benefit`, `id_master`, `status`, `isDeleted`, `progress_update`, `tanggal`, `benefit_name`, `subbenefit`, `description`, `qty`,qty2,qty3,`jamValue`,`manualValue`,`pelaksanaan`,`type`) 
        VALUES (NULL, '$id_master', '1', '0', '0', '".$tanggalx."', '".$benefit_namex."', '".$subbenefitx."', '".$descriptionx."', '$qty1x','$qty2x','$qty3x', '0', '$manualValuex','$pelaksanaanx','$benefitx');";
        mysqli_query($conn,$sql);
    }
      /*
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
    } */
    mysqli_close($conn);
    header('Location: ./masters.php');
    exit();
    
?>