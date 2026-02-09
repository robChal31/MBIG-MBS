<?php
include 'db_con.php';
header('Content-Type: application/json');

$program_code = ($_GET['program_code'] ?? null);

if (!$program_code) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid program'
    ]);
    exit;
}

$program_id = $conn->query("SELECT id FROM programs WHERE code = '$program_code'")->fetch_assoc()['id'];
// cek flag dulu
$program = $conn->query("SELECT has_omzet_scheme_discount 
    FROM programs 
    WHERE id = $program_id
")->fetch_assoc();

if (!$program || !$program['has_omzet_scheme_discount']) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'enabled' => false,
            'ranges' => []
        ]
    ]);
    exit;
}

// ambil ranges
$ranges = [];
$q = $conn->query("SELECT *
    FROM program_omzet_ranges
    WHERE program_id = $program_id
    ORDER BY omzet_min ASC
");

while ($row = $q->fetch_assoc()) {

    // ambil discounts per range
    $discounts = [];
    $d = $conn->query("SELECT amount
        FROM program_discounts
        WHERE omzet_range_id = {$row['id']}
        ORDER BY amount ASC
    ");

    while ($dd = $d->fetch_assoc()) {
        $discounts[] = (float) $dd['amount'];
    }

    $ranges[] = [
        'id' => (int) $row['id'],
        'omzet_min' => (int) $row['omzet_min'],
        'omzet_max' => $row['omzet_max'] !== null
            ? (int) $row['omzet_max']
            : null,
        'max_discount' => (float) $row['max_discount'],
        'discounts' => $discounts
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'enabled' => true,
        'ranges' => $ranges
    ]
]);
