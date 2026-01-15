<?php
session_start();
include 'db_con.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

try {
    $id_user = $_POST['id_user'] ?? null;

    if (!$id_user) {
        echo json_encode(null);
        exit;
    }

    $query = "SELECT * FROM user WHERE id_user = '$id_user' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);

    echo json_encode($row ?: null);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
