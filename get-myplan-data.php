<?php
ob_start();
session_start();
include 'db_con.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

try {
    $myplan_id = $_POST['myplan_id'];
   
    $query = "SELECT mp.*, prog.name as program_new
                FROM myplan as mp
                LEFT JOIN programs AS prog ON (prog.name = mp.program OR prog.code = mp.program)
                WHERE mp.id = $myplan_id";

    $result = mysqli_query($conn, $query);
    $response = array();
    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $response = $row;
    }

    $conn->close();

    echo json_encode($response);
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
