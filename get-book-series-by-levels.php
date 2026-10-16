<?php
include 'db_con.php';
header('Content-Type: application/json');

$levels = $_POST['levels'] ?? [];
if (!is_array($levels)) $levels = [$levels];
$levels = array_values(array_filter(array_map('intval', $levels)));

if (empty($levels)) {
    echo json_encode([]);
    exit;
}

$in = implode(',', $levels);

// ⚠️ Asumsi: ada tabel pivot `book_series_levels` (book_series_id, level_id).
// Kalau di DB kamu relasinya beda (misal book_series punya kolom level_id),
// tinggal ubah query di bawah.
$sql = "SELECT id, name FROM book_series WHERE level_id IN ($in) ORDER BY name ASC";

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