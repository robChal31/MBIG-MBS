<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

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

$id_program_category    = sanitize_input($conn, $_POST['id_program_category']);
$program_event_group_id = $_POST['program_event_group_id'];
$name                   = sanitize_input($conn, $_POST['name']);


try {
    $program_category_exist_query = "SELECT * FROM program_categories WHERE id = '$id_program_category'";
    $is_program_category_exist_exec = $conn->query($program_category_exist_query);

    if ($is_program_category_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }

    $is_program_category_exist = $is_program_category_exist_exec->num_rows > 0;

    if ($is_program_category_exist) {
        $program_row = $is_program_category_exist_exec->fetch_assoc();
        $old_name = $program_row['name'];

        $sql = "UPDATE program_categories 
                    SET name = '$name', program_event_group_id = '$program_event_group_id', updated_at = NOW()
                WHERE id = '$id_program_category'";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }

    } else {
        
        $sql = "INSERT INTO program_categories (name, program_event_group_id, created_at) VALUES (
            '$name', '$program_event_group_id', NOW())";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Program Category saved successfully'
    ]);

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>
