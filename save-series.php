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

$series_id  = sanitize_input($conn, $_POST['series_id']);
$name       = sanitize_input($conn, $_POST['name']);
$subject_id = sanitize_input($conn, $_POST['subject_id']);
$level_id   = sanitize_input($conn, $_POST['level_id']);
$is_active  = sanitize_input($conn, $_POST['is_active']);

try {
    $book_exist_query = "SELECT * FROM book_series WHERE id = '$series_id'";
    $is_book_exist_exec = $conn->query($book_exist_query);
    if ($is_book_exist_exec === false) {
        error_json('Query failed: ' . $conn->error);
    }
    $is_book_exist = $is_book_exist_exec->num_rows > 0;

    if ($is_book_exist) {
        $sql = "UPDATE book_series 
                    SET name = '$name',
                        subject_id = '$subject_id',
                        level_id = '$level_id',
                        is_active = '$is_active'
                WHERE id = '$series_id'";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    } else {
        $sql = "INSERT INTO book_series (name, subject_id, level_id, is_active) VALUES (
            '$name', '$subject_id', '$level_id', '$is_active')";

        if (!$conn->query($sql)) {
            error_json('Query failed: ' . $conn->error);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Book series saved successfully'
    ]);

} catch (\Throwable $th) {
    error_json($th->getMessage());
}
?>
