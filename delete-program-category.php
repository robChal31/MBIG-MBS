<?php

session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

$id_program_category = $_POST['id_program_category'];

try {
    $delete_program_q = "UPDATE program_categories SET deleted_at = NOW(), updated_at = NOW() WHERE id = $id_program_category";
    $delete_program_exec = $conn->query($delete_program_q);
    if ($delete_program_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Program category deleted successfully'
        ]);
    }
} catch (\Throwable $th) {
    error_json($th->getMessage());
}