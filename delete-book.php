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

$id = $_POST['id'];

try {
    $delete_book_q = "UPDATE books SET is_active = 0, updated_at = NOW() WHERE id = $id";
    $delete_book_exec = $conn->query($delete_book_q);
    if ($delete_book_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Book deleted successfully'
        ]);
    }
} catch (\Throwable $th) {
    error_json($th->getMessage());
}