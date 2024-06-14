<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    //error_reporting(E_ALL);
    //ini_set('display_errors', 'On');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PHPMailer\PHPMailer\PHPMailer;
    $config = require 'config.php';

    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    
    function cleanData($str) {
        $str = urldecode ($str );
        $str = filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);
        $str = filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS);
        return $str ;
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

    $program = ISSET($_POST["program"]) ? $_POST["program"] : (ISSET($_SESSION["program"]) ? $_SESSION['program'] : '');
    $sumalok = ISSET($_POST["sumalok"]) ? $_POST["sumalok"] : (ISSET($_SESSION["sumalok"]) ? $_SESSION['sumalok'] : 0);

    $program = strtolower($program);

    $id_draft       = $_SESSION["id_draft"];
    $school_name    = $_SESSION["school_name"];
    $school_name    = str_replace("'", "", $school_name);

    $segment        = $_SESSION["segment"];
    unset($_SESSION["program"]);
    unset($_SESSION["sumalok"]);
    unset($_SESSION["id_draft"]);
    unset($_SESSION["segment"]);
    unset($_SESSION["school_name"]);

    $total_benefit1 = ISSET($_POST["total_benefit1"]) ? $_POST["total_benefit1"] : 0;
    $total_benefit2 = ISSET($_POST["total_benefit2"]) ? $_POST["total_benefit2"] : 0;
    $total_benefit3 = ISSET($_POST["total_benefit3"]) ? $_POST["total_benefit3"] : 0;
    $total_benefit = $total_benefit1 + $total_benefit2 + $total_benefit3;
    
    $selisih_benefit1 = ISSET($_POST["selisih_benefit1"]) ? $_POST["selisih_benefit1"] : 0;
    $selisih_benefit2 = ISSET($_POST["selisih_benefit2"]) ? $_POST["selisih_benefit2"] : 0;
    $selisih_benefit3 = ISSET($_POST["selisih_benefit3"]) ? $_POST["selisih_benefit3"] : 0;
    $selisih_benefit = $selisih_benefit1 + $selisih_benefit2 + $selisih_benefit3;

    $save_as_draft = ISSET($_POST["save_as_draft"]) ? true : false;

    $benefits = $_POST["benefit"];
    $subbenefits = $_POST["subbenefit"];
    $benefitIds = $_POST["benefit_id"];
    $benefitNames = $_POST["benefit_name"];
    $descriptions = $_POST["description"];
    $pelaksanaans = $_POST["pelaksanaan"];
    $keterangans = $_POST["keterangan"];
    $members = $_POST["member"];
    $members2 = $_POST["member2"];
    $members3 = $_POST["member3"];
    $calcValues = $_POST["calcValue"];
    $manvals = $_POST["manval"];
    $valbens = $_POST["valben"];
    $id_templates = $_POST["id_templates"];
    $editmode = $_POST["editmode"];
    
    if($selisih_benefit < 0){
        echo "Selisih Benefit Minus";
        exit();
    }
    
    if($program == 'cbls3' || $program == 'bsp' || $program == 'pk3' || $program == 'cbls1'){
        if($total_benefit > $sumalok){
            echo "Total benefit melebihi alokasi";
            exit();
        }
    }else{
        if($total_benefit > ($sumalok*3)){
            echo "Total benefit melebihi alokasi";
            exit();
        }
    }

    
    $leng = count($benefits);
    
    //update value benefit di table draft_benefit
    $sql = "UPDATE draft_benefit set alokasi = $sumalok, total_benefit = $total_benefit, selisih_benefit = $selisih_benefit, status = 0, fileUrl = NULL, updated_at = current_timestamp() where id_draft = $id_draft";
    mysqli_query($conn,$sql);
    
    if($editmode == 'true'){
        mysqli_query($conn, "DELETE FROM `draft_benefit_list` where id_draft = '$id_draft';");
        mysqli_query($conn, "DELETE FROM draft_approval where id_draft = '$id_draft';");
    }

    for($i = 0; $i < $leng; $i++){
        $manual_val = preg_replace("/[^0-9-]/", "", $valbens[$i]);
        $calc_val = preg_replace("/[^0-9-]/", "", $calcValues[$i]);
        if($members[$i] > 0 || $members2[$i] > 0 || $members3[$i] > 0 ){
            $sql = "INSERT INTO `draft_benefit_list` (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`) VALUES (NULL, '$id_draft', '0', '0', '".$benefitNames[$i]."', '".$subbenefits[$i]."', '".$descriptions[$i]."', '".$keterangans[$i]."', '".$members[$i]."', '".$members2[$i]."', '".$members3[$i]."', '".$pelaksanaans[$i]."', '".$benefits[$i]."','".$manual_val."','".$calc_val."', '".$id_templates[$i]."');";
            mysqli_query($conn,$sql);
        }
    }
    
    //create excel
    if(!$save_as_draft) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A2', 'FORM PERHITUNGAN HARGA DAN BENEFIT');
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A3', 'PROGRAM COMPETENCY BASED LEARNING SOLUTION (CBLS ) 2023');
        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A4', 'TAHUN AJARAN');
        $sheet->mergeCells('A4:J4');
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A5', 'Nama Sekolah');
        $sheet->setCellValue('B5', ': '.$school_name);
        $sheet->setCellValue('A6', 'Nama EC');
        $sheet->setCellValue('B6', ': '.$_SESSION['generalname']);
        $sheet->setCellValue('A7', 'Program');
        $sheet->setCellValue('B7', ': '. strtoupper($program));
        $sheet->setCellValue('A8', 'Segment');
        $sheet->setCellValue('B8', ': '.ucfirst($segment));
        $sheet->setCellValue('A9', 'Tanggal Dibuat');
        $sheet->setCellValue('B9', ': '.date('d M Y'));
        
        $sheet->setCellValue('A10','Mata Ajar');
        $sheet->setCellValue('B10','Judul Buku');
        $sheet->setCellValue('C10','Jumlah Siswa');
        $sheet->setCellValue('D10','USULAN Harga Program');
        $sheet->setCellValue('E10','Harga Buku Normal');
        $sheet->setCellValue('F10','Standard Discount (%)');
        $sheet->setCellValue('G10','Harga Buku Setelah Diskon');
        $sheet->setCellValue('H10','Total Revenue Dengan Harga Normal');
        $sheet->setCellValue('I10','Total Revenue Dengan Harga Diskon');
        $sheet->setCellValue('J10','Alokasi manfaat dengan harga program');
    
        $sql = "SELECT * FROM calc_table where id_draft = $id_draft";
        $result = $conn->query($sql);
        $row = 11; // Start from row 2 for data
        $totalqty = 0;
        $totalnormal = 0;
        $totalrevenuenormal = 0;
        $totalrevenuediskon = 0;
       
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('B' . $row, $data['book_title']);
            $sheet->setCellValue('C' . $row, $data['qty']);
            $sheet->setCellValue('D' . $row, $data['usulan_harga']);
            $sheet->setCellValue('E' . $row, $data['normalprice']);
            $sheet->setCellValue('F' . $row, $data['discount']);
            $sheet->setCellValue('G' . $row, $data['normalprice']-($data['discount']/100*$data['normalprice']));
            $sheet->setCellValue('H' . $row, $data['usulan_harga']*$data['qty']);
            $totalrevenuenormal = $totalrevenuenormal + $data['usulan_harga']*$data['qty'];
            $sheet->setCellValue('I'.$row, $data['qty']*($data['normalprice']-($data['discount']/100*$data['normalprice'])));
            $totalrevenuediskon = $totalrevenuediskon + ($data['qty']*($data['normalprice']-($data['discount']/100*$data['normalprice'])));
            $sheet->setCellvalue('J'.$row, $data['alokasi']);
            $sheet->getStyle('D'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
            $totalqty=$totalqty+(int)$data['qty'];
        }
        $sheet->setCellValue('B'.$row, 'Jumlah');
        $sheet->setCellValue('C'.$row, $totalqty);
        $sheet->setCellValue('H'.$row, $totalrevenuenormal);
        $sheet->setCellValue('I'.$row, $totalrevenuediskon);
        $sheet->getStyle('D'.$row.':L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        
        $row++;
        $sheet->setCellValue('I'.$row, 'Total alokasi benefit per tahun');
        $sheet->setCellValue('J'.$row, $sumalok);

        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;
        
        $sheet->setCellValue('A'.$row, 'Manfaat/fasilitas pengembangan sekolah');
        $sheet->mergeCells('A'.$row.':F'.$row);
        $sheet->setCellValue('G'.$row, 'Harga satuan per unit');
        $sheet->mergeCells('G'.$row.':H'.$row);
        $sheet->setCellValue('I'.$row, 'Usulan Total (durasi/guru/ siswa)');
        $sheet->setCellValue('J'.$row, 'Total Tahun 1');
        if($program == 'prestasi'){
            $sheet->setCellValue('K'.$row, 'Total Tahun 2');
            $sheet->setCellValue('L'.$row, 'Total Tahun 3');
        }
        $row++;
        $sql = "SELECT 
                    a.status, a.benefit_name, a.subbenefit, a.description, a.keterangan, a.qty, a.qty2, a.qty3, 
                    a.manualValue, a.pelaksanaan, b.valueMoney, a.calcValue 
                FROM `draft_benefit_list` a 
                LEFT JOIN draft_template_benefit AS b on a.id_template = b.id_template_benefit
                where a.id_draft = $id_draft";
        $result = $conn->query($sql);
        $j = 1;
        while ($data = $result->fetch_assoc()) {
            $benefit_name = explode(' - ', $data['benefit_name']);
            $sheet->setCellValue('A'.$row,$j);
            // $sheet->setCellValue('B'.$row,$data['subbenefit']." - ".$data['benefit_name']);
            // $sheet->setCellValue('B'.$row, $benefit_name[0]);
            $sheet->setCellValue('B'.$row, $data['benefit_name']);
            $sheet->mergeCells('B'.$row.':C'.$row);
            $sheet->setCellValue('D'.$row,$data['description']);
            $sheet->mergeCells('D'.$row.':F'.$row);
    
            /*somwhere around here*/
            if($data['manualValue'] == 0){
                if($editmode != 'true'){
                    if($data['valueMoney'] == 0){
                        $data['valueMoney'] = $data['calcValue'];
                    }
                    $sheet->setCellValue('G'.$row, $data['valueMoney']);
                }else{
                    if($data['valueMoney'] == 0){
                        $data['valueMoney'] = (int)$data['calcValue']/((int)$data['qty']+(int)$data['qty2']+(int)$data['qty3']);
                    }
                    $sheet->setCellValue('G'.$row, $data['valueMoney']);
                }
                  
                $sheet->setCellValue('J'.$row, ($data['qty'] * $data['valueMoney']));
                if($program == 'prestasi'){
                    $sheet->setCellValue('K'.$row, ($data['qty2'] * $data['valueMoney']));
                    $sheet->setCellValue('L'.$row, ($data['qty3'] * $data['valueMoney']));
                }
                
            }else{
                $sheet->setCellValue('G'.$row, $data['manualValue']);
                $sheet->setCellValue('J'.$row, $data['manualValue'] * $data['qty']);
                if($program == 'prestasi'){
                    $sheet->setCellValue('K'.$row, $data['manualValue'] * $data['qty2']);
                    $sheet->setCellValue('L'.$row, $data['manualValue'] * $data['qty3']);
                }
            }
            
            $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->setCellValue('H'.$row,$data['pelaksanaan']);
            if($program=='prestasi' || $program=='cbls3' || $program=='bsp'){
                $text = "Tahun 1 : ".$data['qty']." | Tahun 2 : ".$data['qty2']." | Tahun 3 : ".$data['qty3'];
                $sheet->setCellValue('I'.$row, $text);
                
            }else{
                $sheet->setCellValue('I'.$row,$data['qty']);
            }
            
            $sheet->getStyle('G'.$row.':I'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $j++; $row++;
        }
        $sheet->setCellValue('A'.$row, 'TOTAL MANFAAT');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $sheet->setCellValue('J'.$row, $total_benefit1);

        if($program == 'prestasi'){
            $sheet->setCellValue('K'.$row, $total_benefit2);
            $sheet->setCellValue('L'.$row, $total_benefit3);
        }

        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->setCellValue('I'.$row,'Total Alokasi Benefit');
        $sheet->setCellValue('J'.$row, $sumalok);
        
        if($program == 'prestasi'){
            $sheet->setCellValue('K'.$row, $sumalok);
            $sheet->setCellValue('L'.$row, $sumalok);
        }

        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;

        $sheet->setCellValue('I'.$row, 'Total Benefit');
        $sheet->setCellValue('J'.$row, $total_benefit1);

        if($program == 'prestasi'){
            $sheet->setCellValue('K'.$row, $total_benefit2);
            $sheet->setCellValue('L'.$row, $total_benefit3);
        }

        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->setCellValue('I'.$row,'Selisih Margin');
        $sheet->setCellValue('J'.$row, ($sumalok - $total_benefit1));

        
        if($program == 'prestasi'){
            $sheet->setCellValue('K'.$row, ($sumalok - $total_benefit2));
            $sheet->setCellValue('L'.$row, ($sumalok - $total_benefit3));
        }
        
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        
        $row++;
        $sheet->setCellValue('A'.$row,'Catatan Penting');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Maksimal diskon yang diberikan untuk sekolah sebesar 20%-25% berdasarkan qty ( min 75 cps/ level ke atas- diskon bisa 25%)');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Pemberian program & qty/durasi disesuaikan dengan kebutuhan sekolah, jika terdapat kelebihan budget benefit maka sisa budget akan menjadi margin perusahaan.');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Jika EC mengajukan free copy, maka EC harus melampirkan list buku dan total nilai Price list');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Tidak ada dana cashback untuk perorangan/individu');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Jika ada budget lebih, tidak dapat dialihkan ke benefit sponsorship dan program lain');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'EC tidak diperbolehkan mengubah/menambahkan benefit selain yang tertera dalam PI yang sudah ditandatangani');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Tidak berlaku sistem retur dalam program ini');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Total benefit dari harga program yang diberikan ke sekolah akan memotong perhitungan komisi/insentif EC');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $sheet->setCellValue('A'.$row,'Semua benefit dan nominal akan di input di sistem setelah PI di tandatangani sekolah');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;
        $row++;
    
        $sheet->setCellValue('A'.$row,'Dibuat oleh EC');
        $sheet->setCellValue('E'.$row,'Dicek dan disetujui oleh Pimpinan (HOR / HOS)');
        $sheet->setCellValue('I'.$row,'Disetujui oleh Pimpinan (Top Leader)');
        $row=$row+6;
        $sheet->setCellValue('A'.$row,'Nama');
        $sheet->setCellValue('E'.$row,'HOR / HOS');
        $sheet->setCellValue('I'.$row,'Dwinanto Setiawan');
        $row++;
        $sheet->setCellValue('A'.$row,'E-signature *wajib');
        $sheet->setCellValue('E'.$row,'E-signature *wajib');
        $sheet->setCellValue('I'.$row,'E-signature *wajib');
        
        for($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }
        $columnIndexes = range('C','K');
        foreach($columnIndexes as $columnIndex) {
            $sheet->getColumnDimension($columnIndex)->setWidth(75);
        }
        $sheet->getRowDimension(10)->setRowHeight(60);
        $rowStyle = $sheet->getStyle('A10:Z10');
        $rowStyle->getAlignment()->setWrapText(true);
        $rowStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
        $writer = new Xlsx($spreadsheet);
        $pattern = '/[^a-zA-Z0-9\s]/';
        $school_name_file = preg_replace($pattern, '', $school_name);
        $fileName = "Draft Benefit - ".$school_name_file."-".$_SESSION['generalname'].'-'.date('Ymd');
        $fileName = addslashes($fileName);
    
        $excelFile = 'draft-benefit/'.$fileName.'.xlsx';
        $writer->save($excelFile);
        $sql = "UPDATE draft_benefit set fileUrl = '$fileName', updated_at = current_timestamp() where id_draft = '$id_draft'";
        
        mysqli_query($conn,$sql);
        //add approval
        $tokenLeader = generateRandomString(16);
         
        //get Leader ID;
        $leaderId = 1;
        $sql = "Select leadid from user where id_user='".$_SESSION['id_user']."';";
        $ress= mysqli_query($conn,$sql);
        while ($datt = mysqli_fetch_assoc($ress)){
            $leaderId = $datt['leadid'];
            $sql = "SELECT username, generalname from user where id_user = '$leaderId';";
            $ress = mysqli_query($conn,$sql);
            while ($datt = mysqli_fetch_assoc($ress)){
                $leaderName = $datt['generalname'];
                $leaderEmail = $datt['username'];
            }
        }
        if(is_null($leaderId)){
            $leaderId = 1;
            $leaderEmail = 'michaelct.mbig@gmail.com';
            $leaderName = 'Mike';
        }
    
        $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leaderId."', '0');";
        mysqli_query($conn,$sql);
    
        $mail = new PHPMailer(true);
        $ec_name = $_SESSION['generalname'];
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
            
            $mail->addAddress($leaderEmail,$leaderName);
            $mail->addAttachment($excelFile,$fileName);
            //Content
            $mail->isHTML(true);
            $uc_program = strtoupper($program);
            $mail->Subject = 'Keren, '.$_SESSION['generalname'].' telah mengajukan formulir '.$uc_program.' untuk '.$school_name;
            $mail->Body    = "<style>
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
            $mail->send();
    
    
            
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    
    
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
            
            $mail->addAddress($_SESSION['username'],$_SESSION['generalname']);
            $mail->addAttachment($excelFile,$fileName);
            //Content
            $mail->isHTML(true);
            $uc_program = strtoupper($program); 
            $mail->Subject = 'Woohoo, Pengajuan kamu sudah berhasil diajukan! Untuk program ' . $uc_program. '  ' . $school_name;
            $mail->Body    = 'Wah, keren abis! Kamu sudah selesai isi formulir manfaat kerja sama ' . $uc_program . ' untuk ' . $school_name . '. Selanjutnya, formulir kamu akan kita teruskan ke Leader untuk diperiksa, ya!';
            $mail->send();
    
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    
    mysqli_close($conn);
    $_SESSION['toast_status'] = 'Success';
    $_SESSION['toast_msg'] = 'Berhasil Menyimpan Draft Benefit';
    header('Location: ./draft-benefit.php');
    exit();
    
?>