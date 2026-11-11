<?php
include 'db_con.php';
header('Content-Type: application/json');

$school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;

if (!$school_id) {
    echo json_encode([]);
    exit;
}

// Ambil nama sekolah (buat handle legacy data yang isinya nama)
$school_name = '';
$q = mysqli_query($conn, "SELECT name FROM schools WHERE id = $school_id");
if (mysqli_num_rows($q) > 0) {
    $row = mysqli_fetch_assoc($q);
    $school_name = mysqli_real_escape_string($conn, $row['name']);
}

// Filter: school_name = ID (data baru) ATAU school_name = NAMA (legacy)
$sql = "SELECT pk.benefit_id, pk.no_pk, pk.id,
               IFNULL(sc.name, db.school_name) as school_name,
               IFNULL(prog.name, db.program) as program
        FROM pk as pk
        LEFT JOIN draft_benefit as db ON db.id_draft = pk.benefit_id
        LEFT JOIN programs as prog ON (prog.name = db.program OR prog.code = db.program)
        LEFT JOIN schools as sc ON sc.id = db.school_name
        WHERE db.deleted_at IS NULL
          AND (
            db.school_name = '$school_id'          -- data baru: isinya ID
            OR db.school_name = '$school_name'     -- legacy: isinya NAMA
          )
        ORDER BY pk.no_pk ASC";

$result = mysqli_query($conn, $sql);
$pks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pks[] = $row;
}

echo json_encode($pks);