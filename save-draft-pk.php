<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

    function sendEmail($email, $name, $subject, $message, $config, $cc = [], $fileName) {
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

            $file_path = 'draft-benefit/'.$fileName.'.xlsx';
        
            if (file_exists($file_path)) {
                $mail->addAttachment($file_path, $fileName.'.xlsx');
            }
    
            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->send();
            
        } catch (Exception $e) {
            $_SESSION['toast_status'] = "Error";
            $_SESSION['toast_msg'] = "Failed send e-mail to $email";
            var_dump($e);die;
            header('Location: ./draft-pk.php');
            exit();
        }
    }

    $user_role      = $_SESSION['role'];
    $draft_status   = $user_role == 'admin' ? 1 : 0;

    $id_draft       = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;

    $id_user        = $_POST['id_user'];
    $school_name    = $_POST['nama_sekolah'];
    $id_master      = $_POST['nama_sekolah'];
    $segment        = $_POST['segment'];
    $program        = $_POST['program'];
    $inputEC        = $_POST['inputEC'];
    $wilayah        = $_POST['wilayah'];
    $jenis_pk       = $_POST['jenis_pk'];
    $level          = $_POST['level'] == 'other' ? $_POST['level2'] : $_POST['level'];
    $id_school      = $school_name;

    $uc_program = strtoupper($program);

    //benefit lists
    $benefits       = $_POST['benefit'];
    $id_templates   = $_POST['id_templates'];
    $subbenefits    = $_POST['subbenefit'];
    $benefit_names  = $_POST['benefit_name'];
    $descriptions   = $_POST['description'];
    $pelaksanaans   = $_POST['pelaksanaan'];

    $qty1s = $_POST['qty1'];
    $qty2s = $_POST['qty2'];
    $qty3s = $_POST['qty3'];

    //pic
    $pic_name   = $_POST['pic_name'];
    $jabatan    = $_POST['jabatan'];
    $no_tlp     = $_POST['no_tlp'];
    $email_pic  = $_POST['email_pic'];

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
            $school_name_new            = mysqli_real_escape_string($conn, $school_data[0]['name']);
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
                        status = '$draft_status',
                        alokasi = '0'
                        jenis_pk = '$jenis_pk'
                    WHERE id_draft = $id_draft";

            mysqli_query($conn, $sql);

            mysqli_query($conn, "DELETE FROM `draft_benefit_list` WHERE id_draft = '$id_draft';");
            mysqli_query($conn, "DELETE FROM draft_approval WHERE id_draft = '$id_draft';");

            $update_sql = "UPDATE school_pic SET 
                                name = '$pic_name',
                                jabatan = '$jabatan',
                                no_tlp = '$no_tlp',
                                email = '$email_pic'
                            WHERE id_draft = $id_draft";

            mysqli_query($conn, $update_sql);
        }else {
            $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`, `alokasi`, `wilayah`, `level`, `jenis_pk`) VALUES (NULL, '$id_user','$inputEC', '$id_school', '$segment','$uc_program', current_timestamp(), '$draft_status', '0', '$wilayah', '$level', '$jenis_pk');";
            mysqli_query($conn,$sql);
            $id_draft = mysqli_insert_id($conn);

            mysqli_query($conn, "DELETE FROM `draft_benefit_list` WHERE id_draft = '$id_draft';");
            mysqli_query($conn, "DELETE FROM draft_approval WHERE id_draft = '$id_draft';");

            $pic_sql = "INSERT INTO `school_pic` (`id`, `id_draft`, `name`, `jabatan`, `no_tlp`, `email`) VALUES (NULL, '$id_draft', '$pic_name', '$jabatan', '$no_tlp', '$email_pic');";
            mysqli_query($conn, $pic_sql);
        }

        foreach($benefits as $key => $benefit) {
            $sql = "INSERT INTO `draft_benefit_list` (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`) VALUES (NULL, '$id_draft', '0', '0', '".$benefit_names[$key]."', '".$subbenefits[$key]."', '".$descriptions[$key]."', '', '".$qty1s[$key]."', '".$qty2s[$key]."', '".$qty3s[$key]."', '".$pelaksanaans[$key]."', '".$benefits[$key]."','0','0', '".$id_templates[$key]."');";
            mysqli_query($conn,$sql);
        }


        $tokenLeader = generateRandomString(16);
        
        $sql = "SELECT 
                    ec.username, ec.generalname, ec.sa_email, lead.generalname as lead_name, 
                    lead.username as lead_mail, lead.id_user as lead_id
                FROM draft_benefit as db
                LEFT JOIN user as ec on ec.id_user = db.id_ec 
                LEFT JOIN user as lead on ec.leadid3 = lead.id_user 
                WHERE db.id_draft = $id_draft";

        $result = mysqli_query($conn,$sql);

        $saemail = '';
        $ec_email = '';
        $ec_name = '';
        $lead_mail = '';
        $lead_name = '';
        $lead_id = '';

        while ($dra = $result->fetch_assoc()){
            $saemail = $dra['sa_email'];
            $ec_email = $dra['username'];
            $ec_name = $dra['generalname'];
            $lead_mail = $dra['lead_mail'];
            $lead_name = $dra['lead_name'];
            $lead_id = $dra['lead_id'];
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A2', 'DAFTAR BENEFIT');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A5', 'Nama Sekolah');
        $sheet->setCellValue('B5', ': '.strtoupper($school_name2));
        $sheet->setCellValue('A6', 'Nama EC');
        $sheet->setCellValue('B6', ': '.$ec_name);
        $sheet->setCellValue('A7', 'Program');
        $sheet->setCellValue('B7', ': '. strtoupper($uc_program));
        $sheet->setCellValue('A8', 'Segment');
        $sheet->setCellValue('B8', ': '.ucfirst($segment));
        $sheet->setCellValue('A9', 'Tanggal Dibuat');
        $sheet->setCellValue('B9', ': '.date('d M Y'));

        $row = 12;
        
        $sheet->setCellValue('A'.$row, 'No.');
        $sheet->setCellValue('B'.$row, 'Manfaat/fasilitas pengembangan sekolah');
        // $sheet->setCellValue('C'.$row, 'Sub-benefit');
        $sheet->setCellValue('C'.$row, 'Nama Manfaat');
        $sheet->setCellValue('D'.$row, 'Deskripsi');
        $sheet->setCellValue('E'.$row, 'Pelaksanaan');
        $sheet->setCellValue('F'.$row, 'Total Tahun 1');
        $sheet->setCellValue('G'.$row, 'Total Tahun 2');
        $sheet->setCellValue('H'.$row, 'Total Tahun 3');

        $row++;
        $sql = "SELECT 
                    a.status, b.benefit, b.benefit_name, b.subbenefit, b.description, a.keterangan, a.qty, a.qty2, a.qty3, 
                    a.manualValue, b.pelaksanaan, b.valueMoney, a.calcValue 
                FROM `draft_benefit_list` a 
                LEFT JOIN draft_template_benefit AS b on a.id_template = b.id_template_benefit
                where a.id_draft = $id_draft ORDER BY b.id_template_benefit ASC";
        $result = $conn->query($sql);
        $j = 1;
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('A'.$row,$j);
            $sheet->setCellValue('B'.$row, $data['benefit']);
            // $sheet->setCellValue('C'.$row, $data['subbenefit']);
            $sheet->setCellValue('C'.$row, $data['benefit_name']);
            $sheet->setCellValue('D'.$row, $data['description']);
            $sheet->setCellValue('E'.$row, $data['pelaksanaan']);
            $sheet->setCellValue('F'.$row, $data['qty']);
            $sheet->setCellValue('G'.$row, $data['qty2']);
            $sheet->setCellValue('H'.$row, $data['qty3']);
            
            $j++; $row++;
        }

        $row++;
    
        $sheet->setCellValue('A'.$row,'Dibuat oleh EC');
        $sheet->setCellValue('D'.$row,'Dicek dan disetujui oleh Pimpinan (HOR / HOS)');
        $sheet->setCellValue('H'.$row,'Disetujui oleh Pimpinan (Top Leader)');
        $row=$row+6;
        $sheet->setCellValue('A'.$row,'Nama');
        $sheet->setCellValue('D'.$row,'HOR / HOS');
        $sheet->setCellValue('H'.$row,'Dwinanto Setiawan');
        $row++;
        $sheet->setCellValue('A'.$row,'E-signature *wajib');
        $sheet->setCellValue('D'.$row,'E-signature *wajib');
        $sheet->setCellValue('H'.$row,'E-signature *wajib');

        for($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        $columnIndexes = range('C','G');
        foreach($columnIndexes as $columnIndex) {
            $sheet->getColumnDimension($columnIndex)->setWidth(75);
        }
        $sheet->getRowDimension(10)->setRowHeight(60);
        $rowStyle = $sheet->getStyle('A10:Z10');
        $rowStyle->getAlignment()->setWrapText(true);
        $rowStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
        $writer = new Xlsx($spreadsheet);
        $pattern = '/[^a-zA-Z0-9\s]/';
        $school_name_file = preg_replace($pattern, '', $school_name2);
        $fileName = "Draft Benefit - ".$school_name_file."-".$_SESSION['generalname'].'-'.date('Ymd');
        $fileName = addslashes($fileName);
    
        $excelFile = 'draft-benefit/'.$fileName.'.xlsx';
        $writer->save($excelFile);
        $sql = "UPDATE draft_benefit set fileUrl = '$fileName', updated_at = current_timestamp() where id_draft = '$id_draft'";
        
        mysqli_query($conn,$sql);

        //here border
        if($user_role != 'admin') {
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '$lead_id', '0');";
            mysqli_query($conn,$sql);
    
            $school_name2 = strtoupper($school_name2);
    
            //for ec mail
            $subject = 'Woohoo, Pengajuan kamu sudah berhasil diajukan! Untuk program ' . $uc_program. '  ' . $school_name2;
            $message    = 'Wah, keren abis! Kamu sudah selesai isi formulir manfaat kerja sama ' . $uc_program . ' untuk ' . $school_name2 . '. Selanjutnya, formulir kamu akan kita teruskan ke Leader untuk diperiksa, ya!';
    
            $cc = [];
    
            sendEmail($ec_email, $ec_name, $subject, $message, $config, $cc, $fileName);
    
            //for leader mail
            $subject    = 'Keren, '.$_SESSION['generalname'].' telah mengajukan formulir '.$uc_program.' untuk '.$school_name;
            $message    = "<style>
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
                                    $ec_name! Telah mengajukan formulir <strong>$uc_program</strong> untuk <strong>$school_name</strong> 
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
                            </div>";
            sendEmail($lead_mail, $lead_name, $subject, $message, $config, $cc, $fileName);
        }
        
        
        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menyimpan Draft Benefit';
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit';
        var_dump($th);die;
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>