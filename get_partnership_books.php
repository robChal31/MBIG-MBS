<?php
ob_start();
session_start();
include 'db_con.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit;
}

function error_json($msg) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit;
}

try {

    if (empty($_POST['id_draft'])) {
        error_json('id_draft is required');
    }

    $id_draft = (int) $_POST['id_draft'];

    $query = "SELECT 
                calc.*, 
                b.*, 
                bs.name AS series_name, 
                bs.id AS series_id
              FROM calc_table AS calc
              LEFT JOIN books AS b ON b.id = calc.book_id
              LEFT JOIN book_series AS bs ON bs.id = b.book_series_id
              WHERE calc.id_draft = $id_draft
            ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $books = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $seriesId = $row['series_id'] ?? 0;
        $books[$seriesId][] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $books
    ]);

    $conn->close();

} catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $th->getMessage()
    ]);
}
