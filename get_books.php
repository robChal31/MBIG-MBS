<?php
session_start();
include 'db_con.php';

header('Content-Type: application/json');

// 🔥 penting biar encoding aman
mysqli_set_charset($conn, "utf8mb4");

// handle connection error
if ($conn->connect_error) {
    echo json_encode([
        "error" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// ambil parameter
$series_id = isset($_GET['series_id']) ? (int)$_GET['series_id'] : 0;
$bookType  = isset($_GET['book_type']) ? $_GET['book_type'] : [];

// kalau series kosong, langsung return empty
if ($series_id === 0) {
    echo json_encode([]);
    exit;
}

// pastikan bookType array
if (!is_array($bookType)) {
    $bookType = [$bookType];
}

// filter type (optional)
$whereType = '';
if (!empty($bookType)) {
    $escapedTypes = array_map(function($type) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $type) . "'";
    }, $bookType);

    $whereType = "AND b.type IN (" . implode(",", $escapedTypes) . ")";
}

// query
$sql = "
    SELECT b.id, b.name, b.grade, b.type, b.price
    FROM books b
    WHERE b.book_series_id = $series_id
      AND b.is_active = 1
      $whereType
    ORDER BY b.name ASC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "error" => "Query failed: " . $conn->error
    ]);
    exit;
}

$data = [];

// ambil data
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'    => $row['id'],
        'name'  => $row['name'],
        'grade' => $row['grade'],
        'type'  => $row['type'],
        'price' => (float)$row['price']
    ];
}

// 🔥 SAFE JSON OUTPUT (anti UTF-8 error)
$json = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);

// kalau encoding gagal (very rare, tapi jaga-jaga)
if ($json === false) {
    echo json_encode([
        "error" => json_last_error_msg()
    ]);
    exit;
}

echo $json;
exit;