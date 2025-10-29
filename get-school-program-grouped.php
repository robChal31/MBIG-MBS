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
    $role = $_SESSION['role'];

    $programs = [];
    $query_admin    = $role == 'admin' ? '' : " AND program.is_classified = 0";
    $query_program  = "SELECT program.*, IFNULL(category.name, 'Unset') as category
                        FROM programs as program
                        LEFT JOIN program_schools AS ps ON ps.program_id = program.id
                        LEFT JOIN program_categories as category on category.id = program.program_category_id
                        WHERE (ps.school_id = '$school_id' OR ps.program_id IS NULL)
                        AND program.is_active = 1 AND program.is_pk = 1 $query_admin ";          

    $exec_program = mysqli_query($conn, $query_program);

    if (!$exec_program) {
        http_response_code(500);
        echo json_encode(["error" => mysqli_error($conn)]);
        exit;
    }

    if (mysqli_num_rows($exec_program) > 0) {
        $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);    
    }

    $grouped_programs = [];

    foreach($programs as $prog) {
        $grouped_programs[$prog['category']][] = $prog;
    }

    $conn->close();

    echo json_encode($grouped_programs);
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(["error" => $th->getMessage()]);
}
?>
