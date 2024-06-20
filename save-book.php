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

$id_book = sanitize_input($conn, $_POST['id_book']);
$name = sanitize_input($conn, $_POST['name']);


try {
    $book_exist_query = "SELECT * FROM books WHERE id = '$id_book'";
    $is_book_exist_exec = $conn->query($book_exist_query);
    if ($is_book_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }
    $is_book_exist = $is_book_exist_exec->num_rows > 0;

    if ($is_book_exist) {
        $sql = "UPDATE books 
                    SET name = '$name', updated_at = NOW()
                WHERE id = '$id_book'";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    } else {
        $sql = "INSERT INTO books (name, created_at) VALUES (
            '$name', NOW())";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Book saved successfully'
    ]);

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>
