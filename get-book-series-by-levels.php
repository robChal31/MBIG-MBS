<?php
include 'db_con.php';
header('Content-Type: application/json');

// 1. Ambil parameter levels
$levels = $_POST['levels'] ?? [];
if (!is_array($levels)) $levels = [$levels];
$levels = array_values(array_filter(array_map('intval', $levels)));

// 2. Ambil parameter subjects
$subjects = $_POST['subjects'] ?? [];
if (!is_array($subjects)) $subjects = [$subjects];
$subjects = array_values(array_filter(array_map('intval', $subjects)));

// 🔥 WAJIB ADA KEDUANYA: Jika salah satu kosong, kembalikan array kosong
if (empty($levels) || empty($subjects)) {
    echo json_encode([]);
    exit;
}

// 3. Susun Query (karena sudah pasti ada keduanya, langsung pakai AND)
$in_levels = implode(',', $levels);
$in_subjects = implode(',', $subjects);

$sql = "SELECT bs.*, l.name as level, s.name as subject 
        FROM book_series AS bs
        LEFT JOIN levels as l on l.id = bs.level_id
        LEFT JOIN subjects as s on s.id = bs.subject_id
        WHERE bs.is_active = 1 
          AND bs.deleted_at IS NULL 
          AND bs.level_id IN ($in_levels)
          AND bs.subject_id IN ($in_subjects)
        ORDER BY bs.name ASC";

$q = mysqli_query($conn, $sql);
if (!$q) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

$rows = [];
while ($r = mysqli_fetch_assoc($q)) {
    $rows[] = $r;
}
echo json_encode($rows);
?>