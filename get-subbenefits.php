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

    $benefit_name = isset($_POST['benefit_name']) ? $_POST['benefit_name'] : '';
    $subbenefits = [];

    if (empty($benefit_name)) {
        echo json_encode($subbenefits);
        exit;
    }

    // 1️⃣ Ambil benefit id dari name
    $benefitQuery = "SELECT id FROM benefits WHERE name = '$benefit_name' LIMIT 1";

    $benefitResult = mysqli_query($conn, $benefitQuery);

    if (!$benefitResult) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    if (mysqli_num_rows($benefitResult) === 0) {
        echo json_encode($subbenefits);
        exit;
    }

    $benefitRow = mysqli_fetch_assoc($benefitResult);
    $benefit_id = $benefitRow['id'];

    // 2️⃣ Ambil subbenefits berdasarkan benefit_id
    $subQuery = "SELECT id, name FROM subbenefits WHERE benefit_id = '$benefit_id' ORDER BY name ASC";

    $subResult = mysqli_query($conn, $subQuery);

    if (!$subResult) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($subResult)) {
        $subbenefits[] = [
            'id'   => $row['id'],
            'name' => $row['name']
        ];
    }

    $conn->close();

    echo json_encode($subbenefits);

} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
