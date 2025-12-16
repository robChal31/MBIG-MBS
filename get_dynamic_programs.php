<?php
ob_start();
session_start();
include 'db_con.php';

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$year_selected = $_POST['year'];
$year = $year_selected && $year_selected == 2 ? (intval($year_selected) - 1) : 1;
$options = "";

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'];
$is_admin = $role == 'admin';
$where_clause = $is_admin ? "" : " AND db.id_ec = $id_user";
$query_program = "SELECT * FROM (
                    SELECT db.id_draft, 
                        IFNULL(sch.name, db.school_name) AS school_name,
                        prog.name AS program_name, 
                        sch.id AS school_id, db2.deleted_at,
                        db.year, db.ref_id, db2.ref_id AS ref_id2, db2.id_draft AS id_draft2, db2.year AS year2
                    FROM draft_benefit db
                    LEFT JOIN schools sch ON sch.id = db.school_name
                    LEFT JOIN programs AS prog ON (prog.name = db.program OR prog.code = db.program)
                    LEFT JOIN draft_benefit db2 ON db2.ref_id = db.id_draft
                    WHERE prog.is_dynamic = 1
                    $where_clause
                    AND db.confirmed = 1 
                    AND db.year = $year
                    ORDER BY db.id_draft DESC
                ) AS benefit
                    WHERE 
                benefit.deleted_at IS NULL AND (benefit.year2 IS NULL OR benefit.year2 != $year_selected)
                OR benefit.deleted_at IS NOT NULL;";

$result = $conn->query($query_program);

try {
    if ($result && $result->num_rows > 0) {
        $options .= "<option value=''>Select Program</option>";

        while ($row = $result->fetch_assoc()) {
          $options .= "<option value='" . $row['id_draft'] . "'>[ " . $row['program_name'] . ' - ' . $row['school_name'] . " ]</option>";
        }

    } else {
        $options = "<option value=''>No Program Found</option>";
    }
} catch (\Throwable $th) {
    $options = "<option value=''>Error: " . htmlspecialchars($th->getMessage()) . "</option>";
}

$conn->close();
echo $options;
