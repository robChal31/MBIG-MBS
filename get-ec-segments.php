<?php
session_start();
include 'db_con.php';

header('Content-Type: application/json');

$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;

if (!$id_user) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT seg.id, seg.segment
        FROM user_segments AS us
        LEFT JOIN segments AS seg ON us.segment_id = seg.id
        WHERE us.id_user = $id_user
          AND seg.id IS NOT NULL
        ORDER BY seg.segment ASC";

$res  = mysqli_query($conn, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($res)) {
    $data[] = [
        'id'      => $row['id'],
        'segment' => $row['segment']
    ];
}

echo json_encode($data);