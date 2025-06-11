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
    $programs = [];

    $query = "SELECT prog.*
        FROM programs AS prog
        LEFT JOIN program_schools AS ps ON ps.program_id = prog.id
        WHERE (ps.school_id = '$school_id' OR ps.program_id IS NULL)
        AND prog.is_active = 1 AND prog.is_pk = 0 AND prog.code NOT IN ('cbls1', 'cbls3')
        AND prog.is_pk = 0
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $programs[] = [
            'code' => $row['code'],
            'name' => $row['name']
        ];
    }

    $conn->close();

    echo json_encode($programs);
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
