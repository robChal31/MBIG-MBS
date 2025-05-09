<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    try {
        $plan_id        = ISSET($_POST['plan_id']) ? $_POST['plan_id'] : null;

        $id_user        = $_POST['id_user'];
        $school_name    = $_POST['nama_sekolah'];
        $segment        = $_POST['segment'];
        $wilayah        = $_POST['wilayah'];
        $program        = $_POST['program'];
        $level          = $_POST['level'];
        $level2         = $_POST['level2'];
        $student_proj   = $_POST['student_projection'];
        $omset_proj     = $_POST['omset_projection'];
        $level          = ($level == 'other') ? $level2 : $level;
        $id_school      = $school_name;

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

        if($plan_id) {
            $sql = "UPDATE myplan SET 
                        user_id = '$id_user',
                        school_id = '$id_school',
                        segment = '$segment',
                        program = '$program',
                        level   = '$level',
                        wilayah = '$wilayah',
                        student_projection = '$student_proj',
                        omset_projection = '$omset_proj',
                        updated_at = current_timestamp()
                    WHERE id = $plan_id";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Error: " . mysqli_error($conn));
            }

        }else {
            $sql = "INSERT INTO `myplan` (`user_id`, `school_id`, `segment`, `program`, `created_at`, `student_projection`, `omset_projection`, `wilayah`, `level`) VALUES ('$id_user', '$id_school', '$segment', '$program', current_timestamp(), '$student_proj', '$omset_proj', '$wilayah', '$level');";

            if (mysqli_query($conn, $sql)) {
                $plan_id = mysqli_insert_id($conn);
            } else {
                throw new Exception("Error: " . mysqli_error($conn));
            }
            
        }

        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menyimpan My Plan';
        
        $location = 'Location: ./myplan.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menyimpan My Plan ' . $th->getMessage();
        
        $location = 'Location: ./myplan.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>