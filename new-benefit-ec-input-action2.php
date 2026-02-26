<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    //error_reporting(E_ALL);
    //ini_set('display_errors', 'On');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
    $program_name   = $program;
    $id_draft       = $_SESSION["id_draft"];
    $school_name    = $_SESSION["school_name"];
    $school_name    = str_replace("'", "", $school_name);

    $segment        = $_SESSION["segment"];
    $segment        = trim($segment ?? '');
    $segment_name   = $segment;

    unset($_SESSION["program"]);
    unset($_SESSION["sumalok"]);
    unset($_SESSION["id_draft"]);
    unset($_SESSION["segment"]);
    unset($_SESSION["school_name"]);

    $total_benefit1 = ISSET($_POST["total_benefit1"]) ? $_POST["total_benefit1"] : 0;
    $total_benefit2 = ISSET($_POST["total_benefit2"]) ? $_POST["total_benefit2"] : 0;
    $total_benefit3 = ISSET($_POST["total_benefit3"]) ? $_POST["total_benefit3"] : 0;
    $total_benefit  = $total_benefit1 + $total_benefit2 + $total_benefit3;
    
    $selisih_benefit1   = ISSET($_POST["selisih_benefit1"]) ? $_POST["selisih_benefit1"] : 0;
    $selisih_benefit2   = ISSET($_POST["selisih_benefit2"]) ? $_POST["selisih_benefit2"] : 0;
    $selisih_benefit3   = ISSET($_POST["selisih_benefit3"]) ? $_POST["selisih_benefit3"] : 0;
    $selisih_benefit    = $selisih_benefit1 + $selisih_benefit2 + $selisih_benefit3;


    $save_as_draft = ISSET($_POST["save_as_draft"]) ? true : false;

    $ref_id         = ISSET($_POST["ref_id"]) ? $_POST["ref_id"] : NULL;
    $program_year   = ISSET($_POST["year"]) ? $_POST["year"] : NULL;

    $benefits       = $_POST["benefit"];
    $subbenefits    = $_POST["subbenefit"];
    $benefitIds     = ISSET($_POST["benefit_id"]) ? $_POST["benefit_id"] : NULL;
    $benefitNames   = $_POST["benefit_name"];
    $descriptions   = $_POST["description"];
    $pelaksanaans   = $_POST["pelaksanaan"];
    $keterangans    = $_POST["keterangan"];
    $members        = $_POST["member"];
    $members2       = $_POST["member2"];
    $members3       = $_POST["member3"];
    $calcValues     = $_POST["calcValue"];
    $manvals        = ISSET($_POST["manval"]) ? $_POST["manval"] : NULL;
    $valbens        = $_POST["valben"];
    $id_templates   = $_POST["id_templates"];
    $editmode       = ISSET($_POST["editmode"]) ? $_POST["editmode"] : NULL;


    if($selisih_benefit < 0){
        echo "Selisih Benefit Minus";
        exit();
    }
    
    if($program == 'cbls3' || $program == 'bsp' || $program == 'pk3' || $program == 'cbls1'){
        if(($total_benefit1 > $sumalok) || ($total_benefit2 > $sumalok) || ($total_benefit3 > $sumalok)){
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
    
    mysqli_autocommit($conn, false);

    $temp_status = $save_as_draft ? 9 : 0;
    try {

        //update value benefit di table draft_benefit
        $sql = "UPDATE draft_benefit set alokasi = $sumalok, total_benefit = $total_benefit, selisih_benefit = $selisih_benefit, status = $temp_status, fileUrl = NULL, updated_at = current_timestamp() where id_draft = $id_draft";
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

        $ec_query = "SELECT ec.*
                            FROM draft_benefit as db
                        LEFT JOIN user as ec on ec.id_user = db.id_ec
                        WHERE db.id_draft = $id_draft
                        ";

        $ec_result = mysqli_query($conn, $ec_query);
        $ec_row = mysqli_fetch_assoc($ec_result);

        $ec_name = $ec_row['generalname'] ?? 'EC';
        $id_ec_r = $ec_row['id_user'] ?? $_SESSION['id_user'];
        $ec_email = $ec_row['username'] ?? $_SESSION['username'];
    
        if ($segment !== '' && is_numeric($segment)) {
            $segment_id = (int) $segment;

            $q_segment = "SELECT * FROM segments WHERE id = $segment_id LIMIT 1";
            $r_segment = mysqli_query($conn, $q_segment);

            if ($r_segment && mysqli_num_rows($r_segment) === 1) {
                $segment_data = mysqli_fetch_assoc($r_segment);
                $segment_name = $segment_data['segment'];
            }
        }

        $show_year_2_and_3 = false;
        $q_program = "SELECT * FROM programs WHERE code = '$program' OR name = '$program' LIMIT 1";
        $r_program = mysqli_query($conn, $q_program);
        $has_omzet_scheme_discount = false;
        if ($r_program && mysqli_num_rows($r_program) === 1) {
            $selected_program = mysqli_fetch_assoc($r_program);
            $has_omzet_scheme_discount = $selected_program['has_omzet_scheme_discount'] == 1;
            $program_name = $selected_program['name'];
            $show_year_2_and_3 = $selected_program['show_year_2_and_3'] ?? false;
        }

        //create excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A2', 'PERHITUNGAN HARGA DAN BENEFIT');
        $sheet->mergeCells('A2:J2');

        $sheet->setCellValue(
            'A3',
            "PROGRAM " . strtoupper($program_name) .
            ($program_year != 1
                ? ($program_year == 2 ? " PERUBAHAN TAHUN KE 2" : " PERUBAHAN TAHUN KE 3")
                : " TAHUN KE I")
        );
        $sheet->mergeCells('A3:J3');

        $sheet->setCellValue('A4', 'TAHUN AJARAN');
        $sheet->mergeCells('A4:J4');
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A5', 'Nama Sekolah');
        $sheet->setCellValue('B5', ': '.$school_name);
        $sheet->setCellValue('A6', 'Nama EC');
        $sheet->setCellValue('B6', ': '.$ec_name);
        $sheet->setCellValue('A7', 'Program');
        $sheet->setCellValue('B7', ': '. strtoupper($program_name));
        $sheet->setCellValue('A8', 'Segment');
        $sheet->setCellValue('B8', ': '.ucfirst($segment_name));
        $sheet->setCellValue('A9', 'Tanggal Dibuat');
        $sheet->setCellValue('B9', ': '.date('d M Y'));

        $priceCol = [
            'mapel' => 'A',
            'judul' => 'B',
            'qty'   => 'C',
        ];

        if(!$has_omzet_scheme_discount){
            $priceCol += [
                'usulan' => 'D',
                'normal' => 'E',
                'disc'   => 'F',
                'after'  => 'G',
                'rev_n'  => 'H',
                'rev_d'  => 'I',
                'alok'   => 'J',
            ];
        }else{
            $priceCol += [
                'normal' => 'D',
                'disc'   => 'E',
                'after'  => 'F',
                'rev_d'  => 'G',
                'alok'   => 'H',
            ];
        }

        $sheet->setCellValue($priceCol['mapel'].'10','Mata Ajar');
        $sheet->setCellValue($priceCol['judul'].'10','Judul Buku');
        $sheet->setCellValue($priceCol['qty'].'10','Jumlah Siswa');

        if(!$has_omzet_scheme_discount){
            $sheet->setCellValue($priceCol['usulan'].'10','Usulan Harga Program');
        }

        $sheet->setCellValue($priceCol['normal'].'10','Harga Buku Normal');
        $sheet->setCellValue($priceCol['disc'].'10','Standard Discount (%)');
        $sheet->setCellValue($priceCol['after'].'10','Harga Buku Setelah Diskon');

        if(!$has_omzet_scheme_discount){
            $sheet->setCellValue($priceCol['rev_n'].'10','Total Revenue Dengan Harga Normal');
        }

        $sheet->setCellValue($priceCol['rev_d'].'10','Total Revenue Dengan Harga Diskon');
        $sheet->setCellValue($priceCol['alok'].'10','Alokasi Manfaat Dengan Harga Program');

        $sql = "SELECT * FROM calc_table WHERE id_draft = $id_draft";
        $result = $conn->query($sql);

        $row = 11;
        $totalqty = 0;
        $totalrevenuenormal = 0;
        $totalrevenuediskon = 0;

        while ($data = $result->fetch_assoc()) {

            $sheet->setCellValue($priceCol['judul'].$row, $data['book_title']);
            $sheet->setCellValue($priceCol['qty'].$row, $data['qty']);

            if(!$has_omzet_scheme_discount){
                $sheet->setCellValue($priceCol['usulan'].$row, $data['usulan_harga']);
            }

            $sheet->setCellValue($priceCol['normal'].$row, $data['normalprice']);
            $discCell = $priceCol['disc'].$row;

            $sheet->setCellValueExplicit(
                $discCell,
                (float)$data['discount'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );

            $sheet->getStyle($discCell)
                ->getNumberFormat()
                ->setFormatCode('0.00');


            $after = $data['normalprice'] - ($data['discount']/100 * $data['normalprice']);
            $sheet->setCellValue($priceCol['after'].$row, $after);

            if(!$has_omzet_scheme_discount){
                $revNormal = $data['usulan_harga'] * $data['qty'];
                $sheet->setCellValue($priceCol['rev_n'].$row, $revNormal);
                $totalrevenuenormal += $revNormal;
            }

            $revDiskon = $data['qty'] * $after;
            $sheet->setCellValue($priceCol['rev_d'].$row, $revDiskon);
            $totalrevenuediskon += $revDiskon;

            $sheet->setCellValue($priceCol['alok'].$row, $data['alokasi']);
            $sheet->getStyle('D'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($discCell)
            ->getNumberFormat()
            ->setFormatCode('0.0');
            $totalqty += (int)$data['qty'];
            $row++;
        }

        $sheet->setCellValue($priceCol['judul'].$row, 'Jumlah');
        $sheet->setCellValue($priceCol['qty'].$row, $totalqty);

        if(!$has_omzet_scheme_discount){
            $sheet->setCellValue($priceCol['rev_n'].$row, $totalrevenuenormal);
        }
        $sheet->getStyle('D'.$row.':L'.$row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue($priceCol['rev_d'].$row, $totalrevenuediskon);
        $sheet->getStyle('D'.$row.':L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;

        if(!$has_omzet_scheme_discount){
            $sheet->setCellValue('I'.$row, 'Total alokasi benefit per tahun');
            $sheet->setCellValue('J'.$row, $sumalok);
            $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        }else {
            $sheet->setCellValue('G'.$row, 'Total alokasi benefit per tahun');
            $sheet->setCellValue('H'.$row, $sumalok);
            $sheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode('#,##0');
        }
       
        $row += 3;

        $sheet->setCellValue('A'.$row, 'Manfaat/fasilitas pengembangan sekolah');
        $sheet->mergeCells('A'.$row.':F'.$row);
        $sheet->setCellValue('G'.$row, 'Harga satuan per unit');
        $sheet->setCellValue('H'.$row, 'Satuan');
        $sheet->setCellValue('I'.$row, 'Usulan Total (durasi/guru/ siswa)');
        $sheet->setCellValue('J'.$row, 'Total Tahun 1');
        if($program == 'prestasi' || $show_year_2_and_3 == 1){
            $sheet->setCellValue('K'.$row, 'Total Tahun 2');
            $sheet->setCellValue('L'.$row, 'Total Tahun 3');
        }
        $row++;

        $sql = "SELECT 
                    a.status, a.benefit_name, a.subbenefit, a.description, a.keterangan,
                    a.qty, a.qty2, a.qty3, a.manualValue, a.pelaksanaan,
                    b.valueMoney, a.calcValue
                FROM draft_benefit_list a
                LEFT JOIN draft_template_benefit b
                    ON a.id_template = b.id_template_benefit
                WHERE a.id_draft = $id_draft";
        $result = $conn->query($sql);

        $j = 1;
        while ($data = $result->fetch_assoc()) {
            $description = html_entity_decode($data['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $description = str_replace(["&#13;", "\r", "\n"], PHP_EOL, $description);

            $sheet->setCellValue('A'.$row, $j);
            $sheet->setCellValue('B'.$row, $data['benefit_name']);
            $sheet->mergeCells('B'.$row.':C'.$row);
            $sheet->setCellValue('D'.$row, $description);
            $sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
            $sheet->mergeCells('D'.$row.':F'.$row);

            if($data['manualValue'] == 0){
                $valueMoney = $data['valueMoney'] ?: (
                    $editmode != 'true'
                        ? $data['calcValue']
                        : ((int)$data['calcValue'] / max(1, ((int)$data['qty']+(int)$data['qty2']+(int)$data['qty3'])))
                );
                $sheet->setCellValue('G'.$row, $valueMoney);
                $sheet->setCellValue('J'.$row, $data['qty'] * $valueMoney);

                if($program == 'prestasi' || $show_year_2_and_3 == 1){
                    $sheet->setCellValue('K'.$row, $data['qty2'] * $valueMoney);
                    $sheet->setCellValue('L'.$row, $data['qty3'] * $valueMoney);
                }
            }else{
                $sheet->setCellValue('G'.$row, $data['manualValue']);
                $sheet->setCellValue('J'.$row, $data['manualValue'] * $data['qty']);

                if($program == 'prestasi' || $show_year_2_and_3 == 1){
                    $sheet->setCellValue('K'.$row, $data['manualValue'] * $data['qty2']);
                    $sheet->setCellValue('L'.$row, $data['manualValue'] * $data['qty3']);
                }
            }

            $sheet->setCellValue('H'.$row, $data['pelaksanaan']);

            if(in_array($program, ['prestasi','cbls3','bsp']) || $show_year_2_and_3 == 1){
                $sheet->setCellValue('I'.$row, "Tahun 1 : {$data['qty']} | Tahun 2 : {$data['qty2']} | Tahun 3 : {$data['qty3']}");
            }else{
                $sheet->setCellValue('I'.$row, $data['qty']);
            }

            $sheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $j++;
            $row++;
        }

        $sheet->setCellValue('A'.$row, 'TOTAL MANFAAT');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $sheet->setCellValue('J'.$row, $total_benefit1);
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');

        if($program == 'prestasi' || $show_year_2_and_3 == 1){
            $sheet->setCellValue('K'.$row, $total_benefit2);
            $sheet->setCellValue('L'.$row, $total_benefit3);
        }

        $row++;
        $sheet->setCellValue('I'.$row,'Total Alokasi Benefit');
        $sheet->setCellValue('J'.$row, $sumalok);
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        if($program == 'prestasi' || $show_year_2_and_3 == 1){
            $sheet->setCellValue('K'.$row, $sumalok);
            $sheet->setCellValue('L'.$row, $sumalok);
        }

        $row++;
        $sheet->setCellValue('I'.$row, 'Total Benefit');
        $sheet->setCellValue('J'.$row, $total_benefit1);
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        if($program == 'prestasi' || $show_year_2_and_3 == 1){
            $sheet->setCellValue('K'.$row, $total_benefit2);
            $sheet->setCellValue('L'.$row, $total_benefit3);
        }

        $row++;
        $sheet->setCellValue('I'.$row,'Selisih Margin');
        $sheet->setCellValue('J'.$row, ($sumalok - $total_benefit1));
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        if($program == 'prestasi' || $show_year_2_and_3 == 1){
            $sheet->setCellValue('K'.$row, ($sumalok - $total_benefit2));
            $sheet->setCellValue('L'.$row, ($sumalok - $total_benefit3));
        }

        $row++;
        $sheet->setCellValue('A'.$row,'Catatan Penting');
        $sheet->mergeCells('A'.$row.':I'.$row);
        $row++;

        $notes = [
            'Maksimal diskon yang diberikan untuk sekolah sebesar 20%-25% berdasarkan qty (min 75 cps/level ke atas)',
            'Pemberian program & qty/durasi disesuaikan dengan kebutuhan sekolah',
            'Jika EC mengajukan free copy, EC wajib melampirkan list buku',
            'Tidak ada dana cashback untuk perorangan/individu',
            'Jika ada budget lebih, tidak dapat dialihkan',
            'EC tidak diperbolehkan mengubah benefit',
            'Tidak berlaku sistem retur',
            'Total benefit memotong komisi EC',
            'Semua benefit diinput setelah PI ditandatangani'
        ];

        foreach($notes as $note){
            $sheet->setCellValue('A'.$row, $note);
            $sheet->mergeCells('A'.$row.':I'.$row);
            $row++;
        }

        $row += 2;
        $sheet->setCellValue('A'.$row,'Dibuat oleh EC');
        $sheet->setCellValue('E'.$row,'Dicek & disetujui HOR/HOS');
        $sheet->setCellValue('I'.$row,'Disetujui Top Leader');

        $row += 6;
        $sheet->setCellValue('A'.$row,'Nama');
        $sheet->setCellValue('E'.$row,'HOR / HOS');
        $sheet->setCellValue('I'.$row,'Dwinanto Setiawan');

        $row++;
        $sheet->setCellValue('A'.$row,'E-signature *wajib');
        $sheet->setCellValue('E'.$row,'E-signature *wajib');
        $sheet->setCellValue('I'.$row,'E-signature *wajib');

        $columnIndexes = range('A','L');
        foreach($columnIndexes as $colIndex){
            $sheet->getColumnDimension($colIndex)->setWidth(18);
            $sheet->getStyle($colIndex)->getAlignment()->setWrapText(true);
            $sheet->getStyle($colIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('J'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getRowDimension(10)->setRowHeight(50);

        $writer = new Xlsx($spreadsheet);
        $pattern = '/[^a-zA-Z0-9\s]/';
        $school_name_file = preg_replace($pattern, '', $school_name);
        $fileName = "Draft Benefit - ".$school_name_file."-".$ec_name.'-'.date('Ymd');
        $fileName = addslashes($fileName);

        $excelFile = 'draft-benefit/'.$fileName.'.xlsx';
        $writer->save($excelFile);

        $sql = "UPDATE draft_benefit 
                SET fileUrl = '$fileName', updated_at = current_timestamp() 
                WHERE id_draft = '$id_draft'";
        mysqli_query($conn, $sql);


        if(!$save_as_draft) {
            //add approval
            $tokenLeader = generateRandomString(16);
            
            //get Leader ID;
            $leaderId   = null;
            $sql        = "SELECT * from user where id_user='$id_ec_r';";
            $ress       = mysqli_query($conn,$sql);
            $leaderId1  = null;
            $leaderId2  = null;
            $leaderId3  = null;

            while ($datt = mysqli_fetch_assoc($ress)){
                $leaderId1  = $datt['leadid'];
                $leaderId2  = $datt['leadid2'];
                $leaderId3  = $datt['leadid3'];
                $leaderId   = $datt['leadid'] ? $datt['leadid'] : ($datt['leadid2'] ? $datt['leadid2'] : 70);
                $sql        = "SELECT username, generalname from user where id_user = '$leaderId';";
                $ress       = mysqli_query($conn,$sql);
                while ($datt = mysqli_fetch_assoc($ress)){
                    $leaderName     = $datt['generalname'];
                    $leaderEmail    = $datt['username'];
                }
            }

            $cc = [];
                            
            $cc[] = [
                'email' => "kelly@mentarigroups.com",
                'name' => "Kelly"
            ];

            $cc[] = [
                'email' => "yully.mentarigroups@gmail.com",
                'name' => "Yully"
            ];

            if(is_null($leaderId)){
                $leaderId = 1;
                $leaderEmail = 'bany@mentarigroups.com';
                $leaderName = 'Bany';
            }
        
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '".$leaderId."', '0');";
            mysqli_query($conn,$sql);

            $previous_year = $program_year - 1;
            $is_adendum = $program_year != 1 ? ", formulir ini adalah perubahan pada tahun ke $program_year dan adalah perubahan dari tahun sebelumnya yaitu tahun ke $previous_year, " : "";
        
            $mail = new PHPMailer(true);

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
                                    $ec_name! Telah mengajukan formulir <strong>$uc_program</strong>$is_adendum untuk <strong>$school_name</strong> 
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

            if($leaderId == $leaderId3 || $leaderId == 70) {
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
                                    $ecname telah mengajukan formulir <strong>$uc_program</strong>$is_adendum untuk <strong>$school_name</strong> 
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
            }
                
            try {
        
                $mail->isSMTP(); 
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_username'];
                $mail->Password   = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['port'] ?? 465;
            
                //Recipients
                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                
                // $mail->addAddress('bany@mentarigroups.com', $leaderName);
                $mail->addAddress($leaderEmail, $leaderName);
                if ($leaderId == 70) {
                    foreach ($cc as $c) {
                        $mail->addCC($c['email'], $c['name']);
                    }
                }

                $mail->addAttachment($excelFile,$fileName);
                //Content
                $mail->isHTML(true);
                $uc_program = strtoupper($program_name);
                $mail->Subject = 'Keren, '.$ec_name.' telah mengajukan formulir '.$uc_program.' untuk '.$school_name;
                $mail->Body    = $message;
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
                $mail->Port       = $config['port'] ?? 465; 
            
                //Recipients
                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                
                // $mail->addAddress('bany@mentarigroups.com', $ec_name);
                $mail->addAddress($ec_email, $ec_name);
                $mail->addAttachment($excelFile,$fileName);
                //Content
                $mail->isHTML(true);
                $uc_program = strtoupper($program_name); 
                $mail->Subject = 'Woohoo, Pengajuan kamu sudah berhasil diajukan! Untuk program ' . $uc_program. '  ' . $school_name;
                $mail->Body    = 'Wah, keren abis! Kamu sudah selesai isi formulir manfaat kerja sama ' . $uc_program . ' untuk ' . $school_name . '. Selanjutnya, formulir kamu akan kita teruskan ke Leader untuk diperiksa, ya!';
                $mail->send();
        
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }

        mysqli_commit($conn);
        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menyimpan Draft Benefit';
        header('Location: ./draft-benefit.php');
        exit();
    } catch (\Throwable $th) {
        mysqli_rollback($conn);
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Transaksi gagal: '.$th->getMessage();
        header('Location: ./draft-benefit.php');
        exit();
    }
    mysqli_autocommit($conn, true);
    mysqli_close($conn);
    
?>