<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;

    $config = require 'config.php';

    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
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

    function sendEmail($email, $name, $subject, $message, $config, $cc = []) {
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
            $_SESSION['toast_status'] = "Error";
            $_SESSION['toast_msg'] = "Failed send e-mail to $email";
            header('Location: ./draft-pk.php');
            exit();
        }
    }

    $id_draft        = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;

    $id_user        = $_POST['id_user'];
    $school_name    = $_POST['nama_sekolah'];
    $id_master      = $_POST['nama_sekolah'];
    $segment        = $_POST['segment'];
    $program        = $_POST['program'];
    $inputEC        = $_POST['inputEC'];
    $wilayah        = $_POST['wilayah'];
    $level          = $_POST['level'] == 'other' ? $_POST['level2'] : $_POST['level'];
    $id_school      = $school_name;

    $uc_program = strtoupper($program);

    //benefit lists
    $benefits = $_POST['benefit'];
    $id_templates = $_POST['id_templates'];
    $subbenefits = $_POST['subbenefit'];
    $benefit_names = $_POST['benefit_name'];
    $descriptions = $_POST['description'];
    $pelaksanaans = $_POST['pelaksanaan'];
    $qty1s = $_POST['qty1'];
    $qty2s = $_POST['qty2'];
    $qty3s = $_POST['qty3'];

    try {
        $url = "https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=$id_school";

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Error: ' . curl_error($curl);
            die;
        }

        curl_close($curl);

        $school_data = json_decode($response, true);

        if(count($school_data) > 0) {
            $school_id_new              = $school_data[0]['institutionid'];
            $school_name_new            = $school_data[0]['name'];
            $school_address_new         = $school_data[0]['address'];
            $school_phone_new           = $school_data[0]['phone'];
            $school_segment_new         = $school_data[0]['segment'];
            $school_ec_id_new           = $school_data[0]['ec_id'];
            $school_created_date_new    = $school_data[0]['created_date'];

            $sql    = "SELECT name FROM schools WHERE id = $school_id_new";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                $row            = mysqli_fetch_assoc($result);
                $school_name2   = $row['name'];
            } else {
                $sql = "INSERT INTO `schools` (`id`, `name`, `address`, `phone`, `segment`, `ec_id`, `created_date`) VALUES
                        ($school_id_new, '$school_name_new', '$school_address_new', '$school_phone_new', '$school_segment_new', '$school_ec_id_new', '$school_created_date_new')";
                mysqli_query($conn, $sql);
                $school_name2 = $school_name_new;
            }
        }

        if($id_draft){
            $sql = "UPDATE draft_benefit SET 
                        id_user = '$id_user',
                        id_ec = '$inputEC',
                        school_name = '$id_school',
                        segment = '$segment',
                        program = '$uc_program',
                        wilayah = '$wilayah',
                        level   = '$level',
                        total_benefit = '0',
                        selisih_benefit = '0',
                        fileUrl = '',
                        updated_at = current_timestamp(),
                        status = '1',
                        alokasi = '0'
                    WHERE id_draft = $id_draft";

            mysqli_query($conn, $sql);

            mysqli_query($conn, "DELETE FROM `draft_benefit_list` where id_draft = '$id_draft';");
            mysqli_query($conn, "DELETE FROM draft_approval where id_draft = '$id_draft';");
        }else {
            $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`, `alokasi`, `wilayah`, `level`) VALUES (NULL, '$id_user','$inputEC', '$id_school', '$segment','$uc_program', current_timestamp(), '1', '0', '$wilayah', '$level');";
            mysqli_query($conn,$sql);
            $id_draft = mysqli_insert_id($conn);
        }

        foreach($benefits as $key => $benefit) {
            $sql = "INSERT INTO `draft_benefit_list` (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`) VALUES (NULL, '$id_draft', '0', '0', '".$benefit_names[$key]."', '".$subbenefits[$key]."', '".$descriptions[$key]."', '', '".$qty1s[$key]."', '".$qty2s[$key]."', '".$qty3s[$key]."', '".$pelaksanaans[$key]."', '".$benefits[$key]."','0','0', '".$id_templates[$key]."');";
            mysqli_query($conn,$sql);
        }

        $tokenLeader = generateRandomString(16);

        $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '70', '0');";
        mysqli_query($conn,$sql);

        $sql = "SELECT 
                    *
                FROM draft_benefit as db
                LEFT JOIN user as ec on ec.id_user = db.id_ec   
                WHERE db.id_draft = $id_draft";
        $result = mysqli_query($conn,$sql);

        while ($dra = $result->fetch_assoc()){
            $saemail = $dra['sa_email'];
            $email = $dra['username'];
            $ecname = $dra['generalname'];
        }

        $school_name2 = strtoupper($school_name2);

        $subject = "INI TEST, formulir $uc_program $school_name2 sudah disubmit!";
        $message = "<p>INI TEST, formulir $uc_program buat $school_name2 sudah disubmit! Sekarang kamu bisa verifikasi dan ajukan ke divisi terkait dengan happy-happy!</p>";
        $cc = [];
        if(ISSET($saemail)) {
            $sa_name = explode('@', $saemail)[0];
            $cc[] = [
                'email' => $saemail,
                'name' => $sa_name
            ];
        }
        // $cc[] = [
        //         'email' => "novitasari@mentaribooks.com",
        //         'name' => "Novi / Mentaribooks"
        // ];
        
        // $cc[] = [
        //     'email' => "ar@mentaribooks.com",
        //     'name' => "AR"
        // ];

        $cc[] = [
            'email' => "secretary@mentaribooks.com",
            'name' => "Putri"
        ];

        sendEmail($email, $ecname, $subject, $message, $config, $cc);
        
        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menyimpan Draft Benefit';
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit';
        
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>