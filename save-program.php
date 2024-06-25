<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

if (!isset($_SESSION['username'])){ 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

$config = require 'config.php';

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

function sanitize_input($conn, $input) {
    return mysqli_real_escape_string($conn, str_replace(["&#13;", "&#10;"], ["\r", "\n"], $input));
}

$id_program = sanitize_input($conn, $_POST['id_program']);
$name = sanitize_input($conn, $_POST['name']);
$code = sanitize_input($conn, $_POST['code']);
$is_pk = $_POST['is_pk'];


try {
    $program_exist_query = "SELECT * FROM programs WHERE id = '$id_program'";
    $is_program_exist_exec = $conn->query($program_exist_query);
    if ($is_program_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }
    $is_program_exist = $is_program_exist_exec->num_rows > 0;

    $program_code_exist_query = "SELECT * FROM programs WHERE code = '$code'";
    $is_program_code_exist_exec = $conn->query($program_code_exist_query);

    if ($is_program_code_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }

    if ($is_program_exist) {
        $program_row = $is_program_exist_exec->fetch_assoc();
        $old_code = $program_row['code'];

        if ($is_program_code_exist_exec->num_rows > 0) {
            $program_code_row = $is_program_code_exist_exec->fetch_assoc();
            $id_old_code = $program_code_row['id'];
            if($id_old_code != $id_program) {
                error_json('Program code already exists.');
            }
        }

        $sql = "UPDATE programs 
                    SET name = '$name', code = '$code', is_pk = '$is_pk', updated_at = NOW()
                WHERE id = '$id_program'";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }

        $select_query = "SELECT id_template_benefit, avail FROM draft_template_benefit WHERE avail LIKE '%$old_code%'";
        $template_res = $conn->query($select_query);

        if ($template_res === false) {
            error_json('Query failed: ' . $conn->error);
        }

        // Iterasi melalui setiap baris hasil query
        while ($row = $template_res->fetch_assoc()) {
            $id = $row['id_template_benefit'];
            $avail = $row['avail'];

            // Ganti nilai lama dengan nilai baru dalam string avail
            $new_avail = str_replace($old_code, $code, $avail);

            // Query untuk memperbarui nilai avail di database
            $update_query = "UPDATE draft_template_benefit SET avail = '$new_avail' WHERE id_template_benefit = '$id'";
            $update_result = $conn->query($update_query);

            if ($update_result === false) {
                error_json('Update failed for ID ' . $id . ': ' . $conn->error);
            }
        }
    } else {
        
        if ($is_program_code_exist_exec->num_rows > 0) {
            error_json('Program code already exists.');
        }
        $sql = "INSERT INTO programs (name, code, is_pk, created_at) VALUES (
            '$name', '$code', '$is_pk', NOW())";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Program saved successfully'
    ]);

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>
