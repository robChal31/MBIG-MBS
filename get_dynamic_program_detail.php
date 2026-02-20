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
    if (!isset($_POST['id_draft'])) {
        error_json('id_draft is required');
    }

    $id_draft = (int) $_POST['id_draft'];

    $query = "SELECT 
                db.id_draft,
                IFNULL(sch.name, db.school_name) AS school_name,
                sch.id AS school_id,
                prog.code,
                prog.name AS program_name,
                db.id_ec,
                db.segment,
                db.level,
                db.wilayah,
                db.cashback,
                GROUP_CONCAT(DISTINCT dal.level_id)   AS level_ids,
                GROUP_CONCAT(DISTINCT das.subject_id) AS subject_ids

            FROM draft_benefit AS db
            LEFT JOIN schools AS sch 
                ON sch.id = db.school_name
            LEFT JOIN programs AS prog 
                ON (prog.name = db.program OR prog.code = db.program)
            LEFT JOIN program_adoption_levels AS dal 
                ON dal.draft_id = db.id_draft
            LEFT JOIN program_adoption_subjects AS das 
                ON das.draft_id = db.id_draft

            WHERE db.id_draft = $id_draft
            GROUP BY db.id_draft
            ";

    $result = $conn->query($query);

    if (!$result) {
        error_json($conn->error);
    }

    $row = $result->fetch_assoc();

    if (!$row) {
        error_json('No records found');
    }

    // convert CSV → array
    $row['level_ids'] = $row['level_ids']
        ? explode(',', $row['level_ids'])
        : [];

    $row['subject_ids'] = $row['subject_ids']
        ? explode(',', $row['subject_ids'])
        : [];

    echo json_encode([
        'status' => 'success',
        'data' => $row
    ]);

    $conn->close();
} catch (Throwable $th) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $th->getMessage()
    ]);
}
