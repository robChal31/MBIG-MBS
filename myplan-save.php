<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

if (!isset($_SESSION['username'])) { 
    header("Location: ./index.php");
    exit();
}

try {
    mysqli_begin_transaction($conn);

    $plan_id = isset($_POST['plan_id']) && $_POST['plan_id'] !== '' ? $_POST['plan_id'] : null;

    $id_user    = $_POST['id_user'];
    $id_school  = $_POST['nama_sekolah'];
    $segment    = $_POST['segment'];
    $wilayah    = $_POST['wilayah'];
    $program    = $_POST['program'];
    $level      = ISSET($_POST['level']) ? $_POST['level'] : '';

    $start_timeline = $_POST['start_timeline'];
    $end_timeline   = $_POST['end_timeline'];
    $omset_proj     = $_POST['omset_projection'];

    $program_plan_adoption_levels   = $_POST['program_plan_adoption_levels'] ?? [];
    $program_plan_adoption_subjects = $_POST['program_plan_adoption_subjects'] ?? [];

    // ===============================
    // GET SCHOOL DATA
    // ===============================
    $url = "https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=$id_school";
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        throw new Exception(curl_error($curl));
    }
    curl_close($curl);

    $school_data = json_decode($response, true);

    if (!empty($school_data)) {
        $school = $school_data[0];

        $school_id_new   = $school['institutionid'];
        $school_name_new = mysqli_real_escape_string($conn, $school['name']);
        $school_address  = mysqli_real_escape_string($conn, $school['address']);
        $school_phone    = $school['phone'];
        $school_segment  = $school['segment'];
        $school_ec_id    = $school['ec_id'];
        $created_date    = $school['created_date'];

        $check = mysqli_query($conn, "SELECT id FROM schools WHERE id = $school_id_new");
        if (mysqli_num_rows($check) < 1) {
            $sql = "INSERT INTO schools (id, name, address, phone, segment, ec_id, created_date)
                    VALUES ($school_id_new, '$school_name_new', '$school_address', '$school_phone',
                            '$school_segment', '$school_ec_id', '$created_date')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception(mysqli_error($conn));
            }
        }

        $id_school = $school_id_new;
    }

    // ===============================
    // INSERT / UPDATE MYPLAN
    // ===============================
    if ($plan_id) {
        $sql = "UPDATE myplan SET
                    user_id = '$id_user',
                    school_id = '$id_school',
                    segment = '$segment',
                    program = '$program',
                    level = '$level',
                    wilayah = '$wilayah',
                    start_timeline = '$start_timeline',
                    end_timeline = '$end_timeline',
                    omset_projection = '$omset_proj',
                    updated_at = CURRENT_TIMESTAMP()
                WHERE id = $plan_id";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }
    } else {
        $sql = "INSERT INTO myplan
                (user_id, school_id, segment, program, level, wilayah,
                 start_timeline, end_timeline, omset_projection, created_at)
                VALUES
                ('$id_user', '$id_school', '$segment', '$program', '$level', '$wilayah',
                 '$start_timeline', '$end_timeline', '$omset_proj', CURRENT_TIMESTAMP())";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }

        $plan_id = mysqli_insert_id($conn);
    }

    // ===============================
    // RESET ADOPTION DATA
    // ===============================
    mysqli_query($conn, "DELETE FROM program_plan_adoption_levels WHERE plan_id = '$plan_id'");
    mysqli_query($conn, "DELETE FROM program_plan_adoption_subjects WHERE plan_id = '$plan_id'");

    // ===============================
    // INSERT LEVELS
    // ===============================
    foreach ($program_plan_adoption_levels as $lvl) {
        $lvl = mysqli_real_escape_string($conn, $lvl);
        $sql = "INSERT INTO program_plan_adoption_levels (plan_id, level_id)
                VALUES ('$plan_id', '$lvl')";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }
    }

    // ===============================
    // INSERT SUBJECTS
    // ===============================
    foreach ($program_plan_adoption_subjects as $subj) {
        $subj = mysqli_real_escape_string($conn, $subj);
        $sql = "INSERT INTO program_plan_adoption_subjects (plan_id, subject_id)
                VALUES ('$plan_id', '$subj')";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }
    }

    // ===============================
    // COMMIT
    // ===============================
    mysqli_commit($conn);

    $_SESSION['toast_status'] = 'Success';
    $_SESSION['toast_msg'] = 'Berhasil Menyimpan My Plan';

} catch (Throwable $e) {
    mysqli_rollback($conn);

    $_SESSION['toast_status'] = 'Error';
    $_SESSION['toast_msg'] = 'Gagal Menyimpan My Plan: ' . $e->getMessage();
}

mysqli_close($conn);
header("Location: ./myplan.php");
exit();
