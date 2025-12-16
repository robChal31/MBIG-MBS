<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $series_id = isset($_GET['series_id']) ? (int)$_GET['series_id'] : 0;
    $bookType = isset($_GET['book_type']) ? $_GET['book_type'] : [];

    if ($series_id === 0) {
    echo json_encode([]);
    exit;
    }

    $whereType = $bookType ? "AND b.type IN ('" . implode("','", $bookType) . "')" : "";

    $sql = "SELECT b.id, b.name, b.grade, b.type, b.price
            FROM books b
            WHERE b.book_series_id = $series_id
                AND b.is_active = 1
                $whereType
            ORDER BY b.name ASC
            ";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'    => $row['id'],
        'name'  => $row['name'],
        'grade' => $row['grade'],
        'type'  => $row['type'],
        'price' => (float)$row['price']
    ];
    }

    echo json_encode($data);