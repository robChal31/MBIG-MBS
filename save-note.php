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

$id_benefit_list = sanitize_input($conn, $_POST['id_benefit_list']);
$note = sanitize_input($conn, $_POST['note']);


try {
    $benefit_exist_query = "SELECT * FROM draft_benefit_list WHERE id_benefit_list = '$id_benefit_list'";
    $is_benefit_exist_exec = $conn->query($benefit_exist_query);
    if ($is_benefit_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }
    $is_benefit_exist = $is_benefit_exist_exec->num_rows > 0;

    if ($is_benefit_exist) {
        $sql = "UPDATE draft_benefit_list 
                    SET note = '$note'
                WHERE id_benefit_list = '$id_benefit_list'";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    } else {
        throw new Exception('Benefit not found');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Note saved successfully'
    ]);

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>
