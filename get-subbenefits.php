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
    // 1. Tangkap ID yang dikirim dari Javascript
    $benefit_id = isset($_POST['benefit_id']) ? (int)$_POST['benefit_id'] : 0;
    $subbenefits = [];

    if (empty($benefit_id)) {
        echo json_encode($subbenefits);
        exit;
    }

    // 2. LANGSUNG Ambil subbenefits berdasarkan benefit_id (Tanpa perlu cari nama lagi!)
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