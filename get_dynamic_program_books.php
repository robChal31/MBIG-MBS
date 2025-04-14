<?php
ob_start();
session_start();
include 'db_con.php';

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

$id_draft = $_POST['id_draft'];

$query_program = "SELECT ct.* FROM calc_table AS ct WHERE ct.id_draft = $id_draft";

$result = $conn->query($query_program);

$data = [];

try {
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        error_json('No records found.');
    }
} catch (\Throwable $th) {
    error_json($th->getMessage());
}

$conn->close();
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'data' => $data
]);
exit();