<?php
ob_start();
session_start();
include 'db_con.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

function error_json($msg) {
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

function s($conn, $v) {
    return mysqli_real_escape_string($conn, trim($v ?? ''));
}

/* ===== get & sanitize input ===== */
$id_book        = s($conn, $_POST['id_book'] ?? 0);
$book_series_id = s($conn, $_POST['book_series_id'] ?? '');
$kode_barang    = s($conn, $_POST['kode_barang'] ?? '');
$name           = s($conn, $_POST['name'] ?? '');
$type           = s($conn, $_POST['type'] ?? '');
$grade          = s($conn, $_POST['grade'] ?? '');
$price_raw      = $_POST['price'] ?? '';
$is_active      = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

/* normalize price (1.000.000 -> 1000000) */
$price = str_replace('.', '', $price_raw);
$price = is_numeric($price) ? $price : 0;

if ($name === '' || $book_series_id === '' || $kode_barang === '') {
    error_json('Required field is missing');
}

try {

    /* check exist */
    $check = $conn->query("SELECT id FROM books WHERE id = '$id_book'");

    if ($check === false) {
        error_json($conn->error);
    }

    if ($check->num_rows > 0) {
        /* ===== UPDATE ===== */
        $sql = "
            UPDATE books SET
                book_series_id = '$book_series_id',
                kode_barang    = '$kode_barang',
                name           = '$name',
                type           = '$type',
                grade          = '$grade',
                price          = '$price',
                is_active      = '$is_active',
                updated_at     = NOW()
            WHERE id = '$id_book'
        ";

        if (!$conn->query($sql)) {
            error_json($conn->error);
        }

    } else {
        /* ===== INSERT ===== */
        $sql = "
            INSERT INTO books (
                book_series_id,
                kode_barang,
                name,
                type,
                grade,
                price,
                is_active,
                created_at
            ) VALUES (
                '$book_series_id',
                '$kode_barang',
                '$name',
                '$type',
                '$grade',
                '$price',
                '$is_active',
                NOW()
            )
        ";

        if (!$conn->query($sql)) {
            error_json($conn->error);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Book saved successfully'
    ]);

} catch (Throwable $e) {
    error_json($e->getMessage());
}
