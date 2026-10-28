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

    function sendEmail($email, $name, $subject, $message, $config, $cc = [], $fileName, $extraAttachments = []) {
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

            // 🔥 BLOK UNTUK ATTACHMENT TAMBAHAN (DOC WORD)
            foreach ($extraAttachments as $att) {
                if (!empty($att['content']) && !empty($att['name'])) {
                    $mail->addStringAttachment(
                        $att['content'],
                        $att['name'],
                        PHPMailer::ENCODING_BASE64,
                        $att['mime'] ?? 'application/octet-stream'
                    );
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

    if (!function_exists('buildBenefitDescriptionExcel')) {
        function buildBenefitDescriptionExcel($item) {
            // 🔥 Sanitize: strip tags + decode entities (&#38; → &, &amp; → &, &nbsp; → space)
            $desc = trim(html_entity_decode(
                strip_tags((string)($item['description'] ?? '')),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ));

            $countMethod = $item['count_method'] ?? 'template';
            $qty         = $item['qty'] ?? null;
            $satuan      = trim((string)($item['satuan'] ?? ''));

            // ---- Breakdown per Subjek & Level ----
            // 🔥 subject_level  → breakdown DENGAN qty
            // 🔥 book_series    → breakdown TANPA qty
            $isSubjectLevel = ($countMethod === 'subject_level');
            $isBookSeries   = ($countMethod === 'book_series');
            $hasPairs       = !empty($item['subject_level_pairs']);
            $skipQty        = ($qty === null || $qty === '' || (is_numeric($qty) && (float)$qty == 0));

            // book_series tetap tampil walau qty kosong/0 (karena ga butuh qty)
            // subject_level wajib ada qty valid
            $showBreakdown = $hasPairs && (
                $isBookSeries || ($isSubjectLevel && !$skipQty)
            );

            if ($showBreakdown) {
                $pairs = [];
                foreach (explode(',', $item['subject_level_pairs']) as $pair) {
                    $pair = trim($pair);
                    if ($pair === '') continue;
                    $parts = explode('|', $pair);

                    // 🔥 Decode entities di nama subject/level
                    $subj = trim(html_entity_decode($parts[0] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    $lvl  = trim(html_entity_decode($parts[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                    if ($subj === '' && $lvl === '') continue;
                    $pairs[] = ['subject' => $subj, 'level' => $lvl];
                }

                usort($pairs, fn($a, $b) =>
                    strcasecmp($a['level'], $b['level']) ?: strcasecmp($a['subject'], $b['subject'])
                );

                if (!empty($pairs)) {
                    // Hitung qty per kombinasi HANYA kalau subject_level
                    $qtyText = null;
                    if ($isSubjectLevel) {
                        $qtyFloat = (float)$qty;
                        $qtyPer   = $qtyFloat / count($pairs);
                        $qtyText  = (floor($qtyPer) == $qtyPer)
                            ? (int)$qtyPer
                            : rtrim(rtrim(number_format($qtyPer, 2, '.', ''), '0'), '.');
                    }

                    $desc .= "\n\nRincian per Subjek & Level:";
                    foreach ($pairs as $pair) {
                        $line = '• ';
                        if ($pair['subject'] !== '' && $pair['level'] !== '') {
                            $line .= $pair['subject'] . ' (' . $pair['level'] . ')';
                        } elseif ($pair['subject'] !== '') {
                            $line .= $pair['subject'];
                        } else {
                            $line .= $pair['level'];
                        }

                        // 🔥 Append qty HANYA untuk subject_level
                        if ($isSubjectLevel && $qtyText !== null) {
                            $line .= ' : ' . $qtyText;
                            if ($satuan !== '') $line .= ' ' . $satuan;
                        }

                        $desc .= "\n" . $line;
                    }
                }
            }

            // ---- Book Series ----
            if (!empty($item['book_series_names'])) {
                $series = array_filter(array_map(function($s) {
                    return trim(html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }, explode(',', $item['book_series_names'])));

                if (!empty($series)) {
                    $desc .= "\n\nUntuk Penggunaan:";
                    foreach ($series as $s) {
                        $desc .= "\n• " . $s;
                    }
                }
            }

            return $desc;
        }
    }

    function cleanBenefitName($string) {
        if (!is_string($string) || $string === '') return '';
        $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $string = preg_replace('/^\d+\.\s*/', '', trim($string));
        $removePatterns = [
            '/\(?Luar\s*Jabodetabek\)?/i',
            '/\(?Jabodetabek\)?/i'
        ];
        foreach ($removePatterns as $pattern) {
            $string = preg_replace($pattern, '', $string);
        }
        $string = preg_replace('/\s+/', ' ', $string);
        return trim($string);
    }

    $user_role      = $_SESSION['role'];
    $draft_status   = $user_role == 'admin' ? 1 : 0;

    $id_draft       = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;

    $id_user        = $_POST['id_user'] ?? null;
    $myplan_id      = ISSET($_POST['myplan_id']) ? $_POST['myplan_id'] : null;
    $school_name    = $_POST['nama_sekolah'] ?? '';
    $id_master      = $_POST['nama_sekolah'] ?? '';
    $segment        = $_POST['segment'] ?? '';
    $program        = $_POST['program'] ?? '';
    $inputEC        = $_POST['inputEC'] ?? null;
    $wilayah        = ISSET($_POST['wilayah']) ? $_POST['wilayah'] : null;
    $jenis_pk       = $_POST['jenis_pk'] ?? null;
    $level          = ISSET($_POST['level']) ? $_POST['level'] : '';
    $id_school      = $school_name;
    $uc_program     = strtoupper($program);

    $discount_program          = isset($_POST['discount_program']) && $_POST['discount_program'] !== '' ? floatval(str_replace(',', '.', $_POST['discount_program'])) : "NULL";
    $program_adoption_levels   = $_POST['program_adoption_levels'] ?? [];
    $program_adoption_subjects = $_POST['program_adoption_subjects'] ?? [];
    $draft_book_series         = $_POST['book_series'] ?? [];

    //benefit lists
    $benefits       = $_POST['benefit'] ?? [];
    $id_templates   = $_POST['id_templates'] ?? [];
    $benefit_names  = array_map(function($val) use ($conn) { return mysqli_real_escape_string($conn, $val); }, $_POST['benefit_name'] ?? []);
    $subbenefits    = array_map(function($val) use ($conn) { return mysqli_real_escape_string($conn, $val); }, $_POST['subbenefit'] ?? []);
    $descriptions   = array_map(function($val) use ($conn) { return mysqli_real_escape_string($conn, $val); }, $_POST['description'] ?? []);
    $pelaksanaans   = array_map(function($val) use ($conn) { return mysqli_real_escape_string($conn, $val); }, $_POST['pelaksanaan'] ?? []);

    $qty1s = $_POST['qty1'];
    $qty2s = $_POST['qty2'];
    $qty3s = $_POST['qty3'];

    //pic
    $pic_name       = $_POST['pic_name'];
    $jabatan        = $_POST['jabatan'];
    $no_tlp         = $_POST['no_tlp'];
    $email_pic      = $_POST['email_pic'];
    $myplan_value   = isset($myplan_id) && $myplan_id !== '' ? "'$myplan_id'" : "NULL";
    
    try {
        mysqli_begin_transaction($conn);
        $program_q = "SELECT * FROM `programs` WHERE `name` = '$program' or `code` = '$program' LIMIT 1";
        $program_res = mysqli_query($conn, $program_q);
        if (mysqli_num_rows($program_res) > 0) {
            $program_data = mysqli_fetch_assoc($program_res);
            $uc_program = $program_data['name'];
        }
        $uc_segment = '';
        $segment_q = "SELECT * FROM `segments` WHERE id = $segment";
        $segment_res = mysqli_query($conn, $segment_q);
        if (mysqli_num_rows($segment_res) > 0) {
            $segment_data = mysqli_fetch_assoc($segment_res);
            $uc_segment = $segment_data['segment'];
        }

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
        $school_name2 = $school_name;
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
                $update_school_query = "UPDATE schools SET name = '$school_name_new', address = '$school_address_new', phone = '$school_phone_new', segment = '$school_segment_new', ec_id = '$school_ec_id_new' WHERE id = $school_id_new";
                mysqli_query($conn, $update_school_query);
                $school_name2   = $school_name_new;
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
                        program = '$program',
                        wilayah = '$wilayah',
                        myplan_id = $myplan_value,
                        level   = '$level',
                        total_benefit = '0',
                        selisih_benefit = '0',
                        fileUrl = '',
                        updated_at = current_timestamp(),
                        status = '$draft_status',
                        verified = 0,
                        confirmed = 0,
                        alokasi = '0',
                        jenis_pk = '$jenis_pk',
                        discount = $discount_program
                    WHERE id_draft = $id_draft";


            if (!mysqli_query($conn, $sql)) {
                throw new Exception("❌ Gagal insert draft: " . mysqli_error($conn));
            }

            $update_sql = "UPDATE school_pic SET 
                                name = '$pic_name',
                                jabatan = '$jabatan',
                                no_tlp = '$no_tlp',
                                email = '$email_pic'
                            WHERE id_draft = $id_draft";

            mysqli_query($conn, $update_sql);
        }else {
            $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`, `alokasi`, `wilayah`, `level`, `jenis_pk`, `myplan_id`, `discount`) VALUES (NULL, '$id_user','$inputEC', '$id_school', '$segment','$program', current_timestamp(), '$draft_status', '0', '$wilayah', '$level', '$jenis_pk', $myplan_value, $discount_program);";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("❌ Gagal insert draft: " . mysqli_error($conn));
            }

            $id_draft = mysqli_insert_id($conn);

            $pic_sql = "INSERT INTO `school_pic` (`id`, `id_draft`, `name`, `jabatan`, `no_tlp`, `email`) VALUES (NULL, '$id_draft', '$pic_name', '$jabatan', '$no_tlp', '$email_pic');";
            
            if (!mysqli_query($conn, $pic_sql)) {
                throw new Exception("❌ Gagal insert school pic: " . mysqli_error($conn));
            }
        }

        $query = "SELECT id_benefit_list FROM draft_benefit_list WHERE id_draft = '$id_draft'";
        $result = mysqli_query($conn, $query);
        $id_benefit_lists = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $id_benefit_lists[] = $row['id_benefit_list'];
        }

        // ========== 2. DELETE PIVOT TABLES ==========
        if (!empty($id_benefit_lists)) {
            $ids_string = implode(',', $id_benefit_lists);
            mysqli_query($conn, "DELETE bsl FROM benefit_subject_levels bsl
                INNER JOIN benefit_org_levels bol ON bol.id = bsl.benefit_org_levels_id
                WHERE bol.draft_benefit_list_id IN ($ids_string)
            ");
            mysqli_query($conn, "DELETE FROM benefit_org_levels WHERE draft_benefit_list_id IN ($ids_string)");
            mysqli_query($conn, "DELETE FROM benefit_book_series WHERE draft_benefit_list_id IN ($ids_string)");
        }

        mysqli_query($conn, "DELETE FROM `draft_benefit_list` WHERE id_draft = '$id_draft';");
        mysqli_query($conn, "DELETE FROM draft_approval WHERE id_draft = '$id_draft';");

        foreach($benefits as $key => $benefit) {
            // INSERT ke draft_benefit_list
            $sql = "INSERT INTO `draft_benefit_list` (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`) VALUES (NULL, '$id_draft', '0', '0', '".$benefit_names[$key]."', '".$subbenefits[$key]."', '".$descriptions[$key]."', '', '".$qty1s[$key]."', '".$qty2s[$key]."', '".$qty3s[$key]."', '".$pelaksanaans[$key]."', '".$benefits[$key]."','0','0', '".$id_templates[$key]."');";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("❌ Gagal insert benefit: " . mysqli_error($conn));
            }

            $id_benefit_list = mysqli_insert_id($conn);

            // ========== 🔥 SAVE SUBJECT + LEVEL (kombinasi) ==========
            // Format $_POST: pairs_{tid}[level_id][] = [subject_id, subject_id, ...]

            $pair_key = "pairs_" . $id_templates[$key];

            if (isset($_POST[$pair_key]) && is_array($_POST[$pair_key])) {
                foreach ($_POST[$pair_key] as $level_id => $subject_ids) {
                    $level_id = intval($level_id);
                    if ($level_id <= 0) continue;

                    // Skip kalau subject kosong
                    if (empty($subject_ids) || !is_array($subject_ids)) continue;

                    // 1) Insert benefit_org_levels DULU (buat dapetin ID)
                    $sql_lvl = "INSERT INTO benefit_org_levels (draft_benefit_list_id, level_id) 
                                VALUES ($id_benefit_list, $level_id)";
                    if (!mysqli_query($conn, $sql_lvl)) {
                        throw new Exception("❌ Gagal insert benefit_org_levels: " . mysqli_error($conn));
                    }
                    $bol_id = mysqli_insert_id($conn);

                    // 2) Insert benefit_subject_levels (TANPA draft_benefit_list_id)
                    $seen = [];
                    foreach ($subject_ids as $subject_id) {
                        $subject_id = intval($subject_id);
                        if ($subject_id <= 0) continue;
                        if (isset($seen[$subject_id])) continue;
                        $seen[$subject_id] = true;

                        $sql_sub = "INSERT INTO benefit_subject_levels 
                                    (subject_id, benefit_org_levels_id) 
                                    VALUES ($subject_id, $bol_id)";
                        if (!mysqli_query($conn, $sql_sub)) {
                            throw new Exception("❌ Gagal insert benefit_subject_levels: " . mysqli_error($conn));
                        }
                    }
                }
            }

            // BOOK SERIES
            if (isset($_POST["book_" . $id_templates[$key]]) && !empty($_POST["book_" . $id_templates[$key]])) {
                $book_series = $_POST["book_" . $id_templates[$key]];
                
                if (is_array($book_series)) {
                    foreach ($book_series as $book_series_id) {
                        if (!empty($book_series_id)) {
                            $book_series_id = intval($book_series_id);
                            mysqli_query($conn, "INSERT INTO benefit_book_series (draft_benefit_list_id, book_series_id) VALUES ($id_benefit_list, $book_series_id)");
                        }
                    }
                } else {
                    $book_series_id = intval($book_series);
                    if ($book_series_id > 0) {
                        mysqli_query($conn, "INSERT INTO benefit_book_series (draft_benefit_list_id, book_series_id) VALUES ($id_benefit_list, $book_series_id)");
                    }
                }
            }
        }

        // ===============================
        // RESET ADOPTION DATA
        // ===============================
        mysqli_query($conn, "DELETE FROM program_adoption_levels WHERE draft_id = '$id_draft'");
        mysqli_query($conn, "DELETE FROM program_adoption_subjects WHERE draft_id = '$id_draft'");
        mysqli_query($conn, "DELETE FROM draft_benefit_book_series WHERE id_draft = '$id_draft'");
        // ===============================
        // INSERT LEVELS
        // ===============================
        foreach ($program_adoption_levels as $lvl) {
            $lvl = mysqli_real_escape_string($conn, $lvl);
            $sql = "INSERT INTO program_adoption_levels (draft_id, level_id)
                    VALUES ('$id_draft', '$lvl')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("❌ Gagal insert program_adoption_levels: " . mysqli_error($conn));
            }
        }

        // ===============================
        // INSERT SUBJECTS
        // ===============================
        foreach ($program_adoption_subjects as $subj) {
            $subj = mysqli_real_escape_string($conn, $subj);
            $sql = "INSERT INTO program_adoption_subjects (draft_id, subject_id)
                    VALUES ('$id_draft', '$subj')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("❌ Gagal insert program_adoption_subjects: " . mysqli_error($conn));
            }
        }

        foreach ($draft_book_series as $bs) {
            $bs = intval($bs);
            if ($bs > 0) {
                $sql = "INSERT INTO draft_benefit_book_series (id_draft, book_series_id)
                        VALUES ('$id_draft', '$bs')";
                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("❌ Gagal insert draft_benefit_book_series: " . mysqli_error($conn));
                }
            }
        }

        $tokenLeader = generateRandomString(16);
        
        $sql = "SELECT 
                    ec.username, ec.generalname, ec.sa_email, 
                    lead1.generalname as lead_name1, lead1.username as lead_mail1, lead1.id_user as lead_id1,
                    lead2.generalname as lead_name2, lead2.username as lead_mail2, lead2.id_user as lead_id2,
                    lead3.generalname as lead_name3, lead3.username as lead_mail3, lead3.id_user as lead_id3
                FROM draft_benefit as db
                LEFT JOIN user as ec on ec.id_user = db.id_ec 
                LEFT JOIN user_segments as us ON us.id_user = db.id_ec AND us.segment_id = db.segment
                LEFT JOIN user as lead1 on us.leadid  = lead1.id_user
                LEFT JOIN user as lead2 on us.leadid2 = lead2.id_user
                LEFT JOIN user as lead3 on us.leadid3 = lead3.id_user
                WHERE db.id_draft = $id_draft";

        $result = mysqli_query($conn,$sql);

        $saemail = '';
        $ec_email = '';
        $ec_name = '';
        $lead_mail = '';
        $lead_name = '';
        $lead_id = '';

        while ($dra = $result->fetch_assoc()){
            $saemail    = $dra['sa_email'];
            $ec_email   = $dra['username'];
            $ec_name    = $dra['generalname'];
            $lead_mail  = $dra['lead_mail1']  ?: ($dra['lead_mail2']  ?: $dra['lead_mail3']);
            $lead_name  = $dra['lead_name1']  ?: ($dra['lead_name2']  ?: $dra['lead_name3']);
            $lead_id    = $dra['lead_id1']    ?: ($dra['lead_id2']    ?: $dra['lead_id3']);
        }

        // 🔥 Fallback kalau lead gak ketemu (EC gak punya row di user_segments untuk segment draft)
        if (empty($lead_id)) {
            $lead_id   = 1;
            $lead_mail = 'bany@mentarigroups.com';
            $lead_name = 'Bany';
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A2', 'PERHITUNGAN HARGA & BENEFIT PROGRAM');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A4:G4');
        $sheet->getStyle('A2:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A5', 'Nama Sekolah');
        $sheet->setCellValue('B5', ': '.strtoupper($school_name2));
        $sheet->setCellValue('A6', 'Nama EC');
        $sheet->setCellValue('B6', ': '.$ec_name);
        $sheet->setCellValue('A7', 'Program');
        $sheet->setCellValue('B7', ': '. strtoupper($uc_program));
        $sheet->setCellValue('A8', 'Diskon');
        $sheet->setCellValue('B8', ': '. ($discount_program ?? '0') . '%');
        $sheet->setCellValue('A9', 'Segment');
        $sheet->setCellValue('B9', ': '.ucfirst($uc_segment));
        $sheet->setCellValue('A10', 'Tanggal Dibuat');
        $sheet->setCellValue('B10', ': '.date('d M Y'));

        $row = 12;
        
        $sheet->setCellValue('A'.$row, 'No.');
        $sheet->setCellValue('B'.$row, 'Manfaat/fasilitas pengembangan sekolah');
        // $sheet->setCellValue('C'.$row, 'Sub-benefit');
        $sheet->setCellValue('C'.$row, 'Nama Manfaat');
        $sheet->setCellValue('D'.$row, 'Deskripsi');
        $sheet->setCellValue('E'.$row, 'Total Tahun 1');
        $sheet->setCellValue('F'.$row, 'Total Tahun 2');
        $sheet->setCellValue('G'.$row, 'Total Tahun 3');

        $row++;
        $row++;
        $sql = "SELECT 
                    dbl.*, 
                    b.benefit, b.benefit_name, b.subbenefit, b.description, 
                    b.allow_multiple_subject_and_level, b.count_method, b.countable,
                    dbl.qty, dbl.qty2, dbl.qty3, 
                    b.qty1 as qty1_template, b.qty2 as qty2_template, b.qty3 as qty3_template, b.satuan,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT CONCAT(s.name, '|', ol.name) SEPARATOR ', '), 
                        ''
                    ) AS subject_level_pairs,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT bs.name ORDER BY bs.name SEPARATOR ', '), 
                        ''
                    ) AS book_series_names
                FROM draft_benefit_list dbl
                LEFT JOIN draft_template_benefit b ON b.id_template_benefit = dbl.id_template
                LEFT JOIN benefit_org_levels bol ON bol.draft_benefit_list_id = dbl.id_benefit_list
                LEFT JOIN benefit_subject_levels bsl ON bsl.benefit_org_levels_id = bol.id
                LEFT JOIN subjects s ON s.id = bsl.subject_id
                LEFT JOIN levels ol ON ol.id = bol.level_id
                LEFT JOIN benefit_book_series bbs ON bbs.draft_benefit_list_id = dbl.id_benefit_list
                LEFT JOIN book_series bs ON bs.id = bbs.book_series_id
                WHERE dbl.id_draft = $id_draft
                GROUP BY dbl.id_benefit_list 
                ORDER BY dbl.id_benefit_list ASC";
        $result = $conn->query($sql);
        $j = 1;
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('A'.$row,$j);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('B'.$row, $data['benefit']);
            // $sheet->setCellValue('C'.$row, $data['subbenefit']);
            $sheet->setCellValue('C'.$row, cleanBenefitName($data['benefit_name']));
            $sheet->setCellValue('D'.$row, buildBenefitDescriptionExcel($data));
            $sheet->setCellValue('E'.$row, ($data['qty'] . ' ' . $data['satuan']) );
            $sheet->setCellValue('F'.$row, ($data['qty2'] . ' ' . $data['satuan']) );
            $sheet->setCellValue('G'.$row, ($data['qty3'] . ' ' . $data['satuan']) );
            
            $j++; $row++;
        }

        $row++;
    
        $sheet->setCellValue('A'.$row,'Dibuat oleh EC');
        $sheet->setCellValue('D'.$row,'Dicek dan disetujui oleh Pimpinan (HOR / HOS)');
        $sheet->setCellValue('H'.$row,'Disetujui oleh Pimpinan (Top Leader)');
        $row=$row+6;
        $sheet->setCellValue('A'.$row,'Nama');
        $sheet->setCellValue('D'.$row,'HOR / HOS');
        $sheet->setCellValue('G'.$row,'Dwinanto Setiawan');
        $row++;
        $sheet->setCellValue('A'.$row,'E-signature *wajib');
        $sheet->setCellValue('D'.$row,'E-signature *wajib');
        $sheet->setCellValue('G'.$row,'E-signature *wajib');

        // for($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
        //     $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        // }

        $columnIndexes = range('A', 'L');
        foreach ($columnIndexes as $columnIndex) {
            $width = in_array($columnIndex, ['B', 'C', 'D', 'E']) ? 35 : 13;
            // $center = in_array($columnIndex, ['A', 'F', 'G', 'H']) ? true : false;
            $sheet->getColumnDimension($columnIndex)->setWidth($width);
            $sheet->getStyle($columnIndex)->getAlignment()->setWrapText(true);
            $sheet->getStyle($columnIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            // if($center){
            //     $sheet->getStyle($columnIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // }
        }
        $sheet->getRowDimension(10)->setRowHeight(35);
        $sheet->getRowDimension(27)->setRowHeight(25);
        
        // $rowStyle = $sheet->getStyle('A10:Z10');
        // $rowStyle->getAlignment()->setWrapText(true);
        // $rowStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
        $writer = new Xlsx($spreadsheet);
        $pattern = '/[^a-zA-Z0-9\s]/';
        $school_name_file = preg_replace($pattern, '', $school_name2);
        $program_file_name = preg_replace($pattern, '', $uc_program);
        $fileName = "Draft Benefit - " . $program_file_name . " - " .$school_name_file."-".$_SESSION['generalname'].'-'.date('Y-m-d_H-i-s');
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
            $subject    = 'Keren, '.$_SESSION['generalname'].' telah mengajukan formulir '.$uc_program.' untuk '.$school_name2;
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
                                    $ec_name! Telah mengajukan formulir <strong>$uc_program</strong> untuk <strong>$school_name2</strong> 
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

        if ($user_role == 'admin') {
            // Penyesuaian variabel karena di file ini tidak ada proses approval
            $year = 1; 

            // 2. Generate Dokumen Word
            require_once 'draft-benefit-doc-render.php';
            $docContent = renderBenefitDoc($conn, (int)$id_draft, [
                'mhtml' => true,
            ]);

            $extraAttachments = [];
            if ($docContent !== '') {
                $safeSchool = preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $school_name2);
                $extraAttachments[] = [
                    'name'    => 'Benefit - ' . $safeSchool . '.doc',
                    'content' => $docContent,
                    'mime'    => 'application/msword',
                ];
            }

            // 3. Fetch PIC dari school_pic
            $pic_rows = [];
            $pic_q = mysqli_query($conn, "SELECT name, jabatan, no_tlp, email 
                                        FROM school_pic 
                                        WHERE id_draft = '$id_draft'
                                        ORDER BY id ASC");
            if ($pic_q && mysqli_num_rows($pic_q) > 0) {
                while ($p = mysqli_fetch_assoc($pic_q)) {
                    $pic_rows[] = $p;
                }
            }

            // 4. Build PIC HTML table
            $pic_html = '';
            if (!empty($pic_rows)) {
                $pic_html .= '
                    <br><br>
                    <strong>Informasi PIC Sekolah:</strong>
                    <table cellpadding="8" cellspacing="0" border="0" 
                        style="border-collapse:collapse; width:100%; margin-top:8px; font-size:13px; background:#ffffff; border:1px solid #dee2e6;">
                        <thead>
                            <tr style="background:#f1f3f5;">
                                <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Nama</th>
                                <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Jabatan</th>
                                <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">No. Telp</th>
                                <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Email</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($pic_rows as $p) {
                    $pic_html .= '<tr>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['name'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['jabatan'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['no_tlp'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['email'] ?? '-') . '</td>
                    </tr>';
                }

                $pic_html .= '</tbody></table>';
            }

            // 5. Siapkan Email untuk SA
            $subject = "Program $uc_program di $school_name2 Telah Berhasil Ditambahkan";
            $message = "<p>Kami ingin menginformasikan bahwa program <strong>$uc_program</strong> untuk <strong>$school_name2</strong> telah berhasil ditambahkan oleh Marketing Secretary.</p>
                        <p>Mohon untuk segera mengecek dan konfirmasi program tersebut.</p>
                        <p>Sarangheyo, Kamsahamnida💖💖💖</p>" . $pic_html;
            
            $cc = [];
            $cc[] = [
                'email' => "secretary@mentaribooks.com",
                'name' => "Putri"
            ];

            $cc[] = [
                'email' => "yully@mentaribooks.com",
                'name' => "Yully"
            ];
            if (!empty($saemail)) {
                $sa_name = explode('@', $saemail)[0];

                sendEmail($saemail, $sa_name, $subject, $message, $config, $cc, $fileName, $extraAttachments);
            } else {
                $subject = "Program $uc_program di $school_name2 Telah Berhasil Ditambahkan";
                $message = "<p>Kami ingin menginformasikan bahwa program $uc_program untuk $school_name2 telah berhasil ditambahkan oleh Marketing Secretary.</p>
                                 <p> Mohon untuk segera mengecek dan konfirmasi program tersebut.</p>
                                 <p>Sarangheyo, Kamsahamnida💖💖💖</p>";
                sendEmail('bany@mentarigroups.com', 'Bany', $subject, $message, $config, $cc, $fileName, $extraAttachments);
            }
        }
        
        mysqli_commit($conn);
        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menyimpan Draft Benefit';
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        mysqli_rollback($conn);
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit ' . $th->getMessage();
        $location = 'Location: ./draft-pk.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>