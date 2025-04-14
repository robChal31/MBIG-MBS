<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    $id_draft       = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;

    $id_user        = $_POST['id_user'];
    $school_name    = $_POST['nama_sekolah'];
    $id_master      = $_POST['nama_sekolah'];
    $segment        = $_POST['segment'];
    $program        = $_POST['program'];
    $inputEC        = $_POST['inputEC'];
    $aloks          = $_POST['alokasi'];
    $book_titles    = $_POST['titles'];
    $wilayah        = $_POST['wilayah'];
    $level          = $_POST['level'];
    $level2         = $_POST['level2'];
    $row_length     = count($book_titles);

    $level          = ($level == 'other') ? $level2 : $level;
    $alokasi        = 0;
    $id_school      = $school_name;

    for($i = 0; $i < $row_length; $i++){
        $alokasi += preg_replace("/[^0-9-]/", "", $aloks[$i]); 
    }

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
            $school_address_new         = mysqli_real_escape_string($conn, $school_data[0]['address']);
            $school_phone_new           = $school_data[0]['phone'];
            $school_segment_new         = $school_data[0]['segment'];
            $school_ec_id_new           = $school_data[0]['ec_id'];
            $school_created_date_new    = $school_data[0]['created_date'];

            $sql = "SELECT * FROM schools WHERE id = $school_id_new";

            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) < 1) {
                $sql = "INSERT INTO `schools` (`id`, `name`, `address`, `phone`, `segment`, `ec_id`, `created_date`) VALUES
                ($school_id_new, '$school_name_new', '$school_address_new', '$school_phone_new', '$school_segment_new', 'school_ec_id_new', '$school_created_date_new')";
                mysqli_query($conn,$sql);
                $id_school = mysqli_insert_id($conn);     
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
                        level   = '$level',
                        total_benefit = '0',
                        selisih_benefit = '0',
                        fileUrl = '',
                        updated_at = current_timestamp(),
                        status = '0',
                        alokasi = $alokasi
                    WHERE id_draft = $id_draft";

            mysqli_query($conn, $sql);
        }else {
            $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`, `alokasi`, `wilayah`, `level`) VALUES (NULL, '$id_user','$inputEC', '$id_school', '$segment','$program', current_timestamp(), '0', $alokasi, '$wilayah', '$level');";

            mysqli_query($conn,$sql);
            $id_draft = mysqli_insert_id($conn);
        }

        $book_levels    = $_POST['levels'];
        $book_type      = $_POST['booktype'];
        $jumlah_siswas  = $_POST['jumlahsiswa'];
        $usulan_hargas  = $_POST['usulanharga'];
        $normals        = $_POST['harganormal'];
        $diskons        = $_POST['diskon'];
        
        $sumalok        = 0;

        if($id_draft){
            mysqli_query($conn, "DELETE FROM `calc_table` where id_draft = '$id_draft';");
            mysqli_query($conn, "DELETE FROM `draft_benefit_list` where id_draft = '$id_draft';");
            mysqli_query($conn, "DELETE FROM draft_approval where id_draft = '$id_draft';");
        }

        for($i = 0; $i < $row_length; $i++){
            $jumlah_siswas[$i]  = preg_replace("/[^0-9]/", "", $jumlah_siswas[$i]);
            $usulan_hargas[$i]  = preg_replace("/[^0-9]/", "", $usulan_hargas[$i]);
            $normals[$i]        = preg_replace("/[^0-9]/", "", $normals[$i]);
            $diskons[$i]        = preg_replace("/[^0-9]/", "", $diskons[$i]);
            $aloks[$i]          = preg_replace("/[^0-9-]/", "", $aloks[$i]);
            $new_title          = $book_titles[$i]." | ".$book_levels[$i]." | ".$book_type[$i];

            $sql = "INSERT INTO `calc_table` (`id_row`, `id_draft`, `book_title`, `qty`, `usulan_harga`, `normalprice`, `discount`, `alokasi`) VALUES (NULL, '$id_draft', '$new_title','$jumlah_siswas[$i]', '$usulan_hargas[$i]','$normals[$i]', '$diskons[$i]', '$aloks[$i]');";

            mysqli_query($conn, $sql);
            $sumalok += $aloks[$i];
        }
        $_SESSION['sumalok']        = $sumalok;
        $_SESSION['id_draft']       = $id_draft;
        $_SESSION['program']        = $program;
        $_SESSION['school_name']    = $id_school;
        $_SESSION['segment']        = $segment;
        
        $location = 'Location: ./new-benefit-ec-input2.php?edit=edit&id_draft='.$id_draft; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit ' . $th->getMessage();
        
        $location = 'Location: ./draft-benefit.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>