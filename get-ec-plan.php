<?php
session_start();
include 'db_con.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0); // 🔥 IMPORTANT

try {
    $ec         = $_POST['ec'] ?? null;

    if (!$ec) {
        echo json_encode([]);
        exit;
    }

    $id_draft       = $_POST['id_draft'] ?? null;
    $is_pk          = $_POST['is_pk'] ?? null;
    $is_pk_query    = $is_pk !== null ? "AND prog.is_pk = 1" : "AND prog.is_pk = 0";

    $query = "SELECT plan.*, school.name AS school_name, prog.name AS program_name
                FROM myplan AS plan
                LEFT JOIN schools AS school ON school.id = plan.school_id
                LEFT JOIN programs AS prog ON (prog.name = plan.program OR prog.code = plan.program)
            WHERE plan.user_id = $ec
            AND prog.is_active = 1
            $is_pk_query
            AND NOT EXISTS (
                SELECT 1 FROM draft_benefit db
                WHERE db.myplan_id = plan.id
                " . ($id_draft ? "AND db.id_draft != '$id_draft'" : "") . "
            ) ORDER BY plan.id DESC
            ";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $programs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $programs[] = [
            'value' => $row['id'],
            'label' => $row['school_name'] . ' - ' . $row['program_name'],
        ];
    }

    echo json_encode($programs);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
