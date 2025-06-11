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
    $school_id = $_POST['school_id'];
    $ec = $_POST['ec'];
    $programs = [];

    $query = "SELECT plan.*, school.name as school_name, prog.name as program_name
        FROM myplan AS plan
        LEFT JOIN schools as school on school.id = plan.school_id
        LEFT JOIN programs as prog on prog.code = plan.program
        WHERE plan.school_id = '$school_id' AND plan.user_id = $ec
        AND NOT EXISTS (
            SELECT 1 FROM draft_benefit db
            WHERE db.myplan_id = plan.id
        )
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $programs[] = [
            'value' => $row['id'],
            'label' => $row['school_name'] . ' - ' . $row['program_name'],
        ];
    }

    $conn->close();

    echo json_encode($programs);
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
