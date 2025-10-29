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

$query_program = "SELECT db.id_draft, IFNULL(sch.name, db.school_name) AS school_name, prog.code, db.id_ec,
                         prog.name as programe_name, sch.id AS school_id, db.segment, db.level, db.wilayah
                  FROM draft_benefit AS db
                  LEFT JOIN schools AS sch ON sch.id = db.school_name
                  LEFT JOIN programs AS prog ON (prog.name = db.program OR prog.code = db.program)
                  WHERE db.id_draft = $id_draft";

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