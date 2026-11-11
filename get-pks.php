<?php
include 'db_con.php';
header('Content-Type: application/json');

// Jangan paksa intval di sini, biarkan string masuk
$school_id = isset($_POST['school_id']) ? trim($_POST['school_id']) : '';

if (!$school_id) {
    echo json_encode([]);
    exit;
}

// Cari nama sekolah berdasarkan ID atau NAMA yang dikirim
$school_name = '';
$esc_id = mysqli_real_escape_string($conn, $school_id);
$q = mysqli_query($conn, "SELECT name FROM schools WHERE id = '$esc_id' OR name = '$esc_id' LIMIT 1");
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
            db.school_name = '$esc_id'             -- data baru: isinya ID
            OR db.school_name = '$school_name'     -- legacy: isinya NAMA
          )
        ORDER BY pk.no_pk ASC";

$result = mysqli_query($conn, $sql);
$pks = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pks[] = $row;
}

echo json_encode($pks);