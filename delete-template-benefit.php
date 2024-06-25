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

$id_template_benefit = $_POST['id_template_benefit'];

try {
    $delete_template_q = "UPDATE draft_template_benefit SET is_active = 0 WHERE id_template_benefit = $id_template_benefit";
    $delete_template_exec = $conn->query($delete_template_q);
    if ($delete_template_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Template deleted successfully'
        ]);
    }
} catch (\Throwable $th) {
    error_json($th->getMessage());
}