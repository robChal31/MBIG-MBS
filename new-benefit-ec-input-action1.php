<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    $id_draft       = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : NuLL;
    $program_ref    = ISSET($_POST['program_reffered']) ? $_POST['program_reffered'] : NuLL;

    $id_user     = isset($_POST['id_user'])        ? $_POST['id_user']        : null;
    $school_name = isset($_POST['nama_sekolah'])   ? $_POST['nama_sekolah']   : null;
    $segment     = isset($_POST['segment'])        ? $_POST['segment']        : null;
    $program     = isset($_POST['program'])        ? $_POST['program']        : null;
    $inputEC     = isset($_POST['inputEC'])        ? $_POST['inputEC']        : null;
    $aloks       = isset($_POST['alokasi'])        ? $_POST['alokasi']        : [];
    $book_titles = isset($_POST['titles'])         ? $_POST['titles']         : [];
    $wilayah     = isset($_POST['wilayah'])        ? $_POST['wilayah']        : null;
    $cashback    = isset($_POST['cashback'])       ? $_POST['cashback']       : 0;

    $program_year   = ISSET($_POST['program_year']) ? $_POST['program_year'] : 1;
    $level          = ISSET($_POST['level']) ? $_POST['level'] : '';
    $myplan_id      = ISSET($_POST['myplan_id']) && $_POST['myplan_id'] != '' ? $_POST['myplan_id'] : NuLL;
    $row_length     = count($book_titles);
    $alokasi        = 0;
    $id_school      = $school_name;

    $program_adoption_levels   = $_POST['program_adoption_levels'] ?? [];
    $program_adoption_subjects = $_POST['program_adoption_subjects'] ?? [];

    for($i = 0; $i < $row_length; $i++){
        $alokasi += preg_replace("/[^0-9-]/", "", $aloks[$i]); 
    }

    try {
        mysqli_begin_transaction($conn);
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
            $school_address_new         = mysqli_real_escape_string($conn, $school_data[0]['address']);
            $school_phone_new           = $school_data[0]['phone'];
            $school_segment_new         = $school_data[0]['segment'];
            $school_ec_id_new           = $school_data[0]['ec_id'];
            $school_created_date_new    = $school_data[0]['created_date'];

            $sql = "SELECT * FROM schools WHERE id = $school_id_new";

            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) < 1) {
                $sql = "INSERT INTO `schools` (`id`, `name`, `address`, `phone`, `segment`, `ec_id`, `created_date`) VALUES
                ($school_id_new, '$school_name_new', '$school_address_new', '$school_phone_new', '$school_segment_new', '$school_ec_id_new', '$school_created_date_new')";
                mysqli_query($conn,$sql);
                $id_school = mysqli_insert_id($conn);     
            }
        }

        if ($id_draft) {
            $sql = "UPDATE draft_benefit SET 
                        id_user = '$id_user',
                        id_ec = '$inputEC',
                        school_name = '$id_school',
                        segment = '$segment',
                        program = '$program',
                        wilayah = '$wilayah',
                        level = '$level',
                        myplan_id = '$myplan_id',
                        total_benefit = '0',
                        selisih_benefit = '0',
                        cashback = '$cashback',
                        fileUrl = '',
                        updated_at = current_timestamp(),
                        status = '0',
                        alokasi = $alokasi
                    WHERE id_draft = $id_draft";

            if (!mysqli_query($conn, $sql)) {
               throw new Exception("❌ Gagal update: " . mysqli_error($conn));
            }

        } else {
            $sql = "INSERT INTO draft_benefit (
                        id_user, id_ec, school_name, segment, program, date, status, alokasi, wilayah, level, myplan_id, ref_id, year, cashback
                    ) VALUES (
                        '$id_user', '$inputEC', '$id_school', '$segment', '$program',
                        current_timestamp(), '0', $alokasi, '$wilayah', '$level', '$myplan_id', '$program_ref', '$program_year', '$cashback'
                    )";

            if (mysqli_query($conn, $sql)) {
                $id_draft = mysqli_insert_id($conn);
            } else {
                throw new Exception("❌ Gagal insert: " . mysqli_error($conn));
            }
        }

        $book_levels    = $_POST['levels'];
        $book_type      = $_POST['booktype'];
        $jumlah_siswas  = $_POST['jumlahsiswa'];
        $usulan_hargas  = $_POST['usulanharga'];
        $normals        = $_POST['harganormal'];
        $diskons        = $_POST['diskon'];
        $book_ids       = $_POST['book_ids'];
        
        $sumalok        = 0;
        
        if ($id_draft) {
            // Ambil program lama dari database
            $result = mysqli_query($conn, "SELECT program FROM draft_benefit WHERE id_draft = '$id_draft'");
            $row = mysqli_fetch_assoc($result);
            $existingProgram = $row['program'];

            // Cek apakah program berubah
            if ($existingProgram != $program) {
                mysqli_query($conn, "DELETE FROM `draft_benefit_list` WHERE id_draft = '$id_draft'");
            }

            // Always delete calc_table and draft_approval
            mysqli_query($conn, "DELETE FROM `calc_table` WHERE id_draft = '$id_draft'");
            mysqli_query($conn, "DELETE FROM `draft_approval` WHERE id_draft = '$id_draft'");
        }

        for($i = 0; $i < $row_length; $i++){
            $jumlah_siswas[$i]  = preg_replace("/[^0-9]/", "", $jumlah_siswas[$i]);
            $usulan_hargas[$i]  = preg_replace("/[^0-9]/", "", $usulan_hargas[$i]);
            $normals[$i]        = preg_replace("/[^0-9]/", "", $normals[$i]);
            $value = $diskons[$i];
            $value = trim($value);
            $value = str_replace(['.', ','], ['','.' ], $value);
            $diskons[$i] = (float) $value;
            $aloks[$i]          = preg_replace("/[^0-9-]/", "", $aloks[$i]);
            $book_ids[$i]       = $book_ids[$i];
            // $new_title          = $book_titles[$i]." | ".$book_levels[$i]." | ".$book_type[$i];
            $new_title          = $book_titles[$i];

            $sql = "INSERT INTO `calc_table` (`id_row`, `book_id`, `id_draft`, `book_title`, `qty`, `usulan_harga`, `normalprice`, `discount`, `alokasi`) VALUES (NULL, '$book_ids[$i]', '$id_draft', '$new_title','$jumlah_siswas[$i]', '$usulan_hargas[$i]','$normals[$i]', '$diskons[$i]', '$aloks[$i]');";

            mysqli_query($conn, $sql);
            $sumalok += $aloks[$i];
        }

        // ===============================
        // RESET ADOPTION DATA
        // ===============================
        mysqli_query($conn, "DELETE FROM program_adoption_levels WHERE draft_id = '$id_draft'");
        mysqli_query($conn, "DELETE FROM program_adoption_subjects WHERE draft_id = '$id_draft'");

        // ===============================
        // INSERT LEVELS
        // ===============================
        foreach ($program_adoption_levels as $lvl) {
            $lvl = mysqli_real_escape_string($conn, $lvl);
            $sql = "INSERT INTO program_adoption_levels (draft_id, level_id)
                    VALUES ('$id_draft', '$lvl')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception(mysqli_error($conn));
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
                throw new Exception(mysqli_error($conn));
            }
        }
        
        if($program_ref) {
            $query = "SELECT * FROM draft_benefit_list WHERE id_draft = $program_ref";
            $result = mysqli_query($conn, $query);

            while ($row = $result->fetch_assoc()) {
                $status = mysqli_real_escape_string($conn, $row['status']);
                $isDeleted = mysqli_real_escape_string($conn, $row['isDeleted']);
                $benefit_name = mysqli_real_escape_string($conn, $row['benefit_name']);
                $subbenefit = mysqli_real_escape_string($conn, $row['subbenefit']);
                $description = mysqli_real_escape_string($conn, $row['description']);
                $keterangan = mysqli_real_escape_string($conn, $row['keterangan']);
                $qty = mysqli_real_escape_string($conn, $row['qty']);
                $qty2 = mysqli_real_escape_string($conn, $row['qty2']);
                $qty3 = mysqli_real_escape_string($conn, $row['qty3']);
                $pelaksanaan = mysqli_real_escape_string($conn, $row['pelaksanaan']);
                $type = mysqli_real_escape_string($conn, $row['type']);
                $manualValue = mysqli_real_escape_string($conn, $row['manualValue']);
                $calcValue = mysqli_real_escape_string($conn, $row['calcValue']);
                $id_template = mysqli_real_escape_string($conn, $row['id_template']);

                $insert = "INSERT INTO draft_benefit_list (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`)
                            VALUES (NULL, $id_draft, '$status', '$isDeleted', '$benefit_name', '$subbenefit', '$description', '$keterangan', '$qty', '$qty2', '$qty3', '$pelaksanaan', '$type','$manualValue','$calcValue', '$id_template')";
                mysqli_query($conn, $insert);
            }

            $pk_query = "SELECT * FROM pk WHERE benefit_id = $program_ref";
            $pk_result = mysqli_query($conn, $pk_query);
            
            if (mysqli_num_rows($pk_result) > 0) {
                $pk_row = mysqli_fetch_assoc($pk_result);

                $no_pk                  = $pk_row['no_pk'];
                $start_date             = $pk_row['start_at'];
                $end_date               = $pk_row['expired_at'];
                $id_sa                  = $pk_row['sa_id'];
                $target_file_pk         = $pk_row['file_pk'];
                $target_file_benefit    = $pk_row['file_benefit'];
            
                $insert_query = "INSERT INTO pk (benefit_id, no_pk, start_at, expired_at, sa_id, file_pk, file_benefit, created_at, updated_at) 
                                VALUES ($id_draft, '$no_pk', '$start_date', '$end_date', $id_sa, '$target_file_pk', '$target_file_benefit', current_timestamp(), NULL)";
            
                mysqli_query($conn, $insert_query);
            }
        }

        $_SESSION['sumalok']        = $sumalok;
        $_SESSION['id_draft']       = $id_draft;
        $_SESSION['program']        = $program;
        $_SESSION['school_name']    = $id_school;
        $_SESSION['segment']        = $segment;
        $_SESSION['toast_status']   = 'Success';
        $_SESSION['toast_msg']      = 'Berhasil Menyimpan, silahkan lanjutkan mengisi benefit';
        mysqli_commit($conn);
        $location = 'Location: ./new-benefit-ec-input2.php?edit=edit&id_draft='.$id_draft; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit ' . $th->getMessage();
        mysqli_rollback($conn);
        $location = 'Location: ./draft-benefit.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>