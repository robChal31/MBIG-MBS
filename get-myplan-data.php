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
   
    $query = "SELECT 
                    mp.*,
                    prog.name AS program_name,
                    GROUP_CONCAT(DISTINCT ppal.level_id)   AS level_ids,
                    GROUP_CONCAT(DISTINCT ppas.subject_id) AS subject_ids
                FROM myplan AS mp
                LEFT JOIN programs AS prog 
                    ON (prog.name = mp.program OR prog.code = mp.program)
                LEFT JOIN program_plan_adoption_levels AS ppal 
                    ON mp.id = ppal.plan_id
                LEFT JOIN program_plan_adoption_subjects AS ppas 
                    ON mp.id = ppas.plan_id
                WHERE mp.id = $myplan_id
                GROUP BY mp.id
            ";

    $result = mysqli_query($conn, $query);
    $response = array();
    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $row['level_ids'] = $row['level_ids'] 
            ? explode(',', $row['level_ids']) 
            : [];

        $row['subject_ids'] = $row['subject_ids'] 
            ? explode(',', $row['subject_ids']) 
            : [];

        echo json_encode($row);
    } else {
        echo json_encode([]);
    }


    $conn->close();
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
