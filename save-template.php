<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

if (!isset($_SESSION['username'])){ 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

$config = require 'config.php';

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

function sanitize_input($conn, $input) {
    return mysqli_real_escape_string($conn, str_replace(["&#13;", "&#10;"], ["\r", "\n"], $input));
}

$id_template    = sanitize_input($conn, $_POST['id_template']);
$benefit        = sanitize_input($conn, $_POST['benefit']);
$subbenefit     = sanitize_input($conn, $_POST['subbenefit']);
$benefit_name   = sanitize_input($conn, $_POST['benefit_name']);
$avail          = $_POST['avail'];
$description    = sanitize_input($conn, $_POST['description']);
$pelaksanaan    = sanitize_input($conn, $_POST['pelaksanaan']);
$qty1           = $_POST['qty1'] ?? 0;
$qty2           = $_POST['qty2'] ?? 0;
$qty3           = $_POST['qty3'] ?? 0;
$unit_bisnis    = sanitize_input($conn, $_POST['unit_bisnis']);
$value          = $_POST['value'] ?? 0;
$value          = str_replace(".", "", $value);
$optional       = $_POST['optional'] ?? 0;
$subject        = $_POST['subject'] ?? NULL;
$redeemable     = $_POST['redeemable'] ?? 0;
$order          = $_POST['benefit_order'] ?? 1000;
$color          = $_POST['highlight_color'] ?? NULL;
$info           = $_POST['info'] ?? NULL;
$manual_input   = $_POST['manual_input'] ?? 0;
$editable_qty   = $_POST['editable_qty'] ?? 0;

if ($avail == '' || !is_array($avail) || count($avail) == 0) {
    error_json("Please select at least one availability");
}

$avail = implode(" ", $avail);

$query_unit_bisnis = "SELECT * FROM business_units WHERE code = '$unit_bisnis'";
$query_unit_bisnis_exec = $conn->query($query_unit_bisnis);
if ($query_unit_bisnis_exec === false) {
    error_json('Query failed: ' . $conn->error);
}

if ($query_unit_bisnis_exec->num_rows > 0) {
    $row_unit_bisnis = $query_unit_bisnis_exec->fetch_assoc();
    $unit_name = $row_unit_bisnis['name'];
    $unit_code = $row_unit_bisnis['code'];
} else {
    error_json('Unit Bisnis not found: ' . $unit_bisnis);
}

try {
    // Mulai transaksi
    $conn->begin_transaction();

    $template_exist_query = "SELECT * FROM draft_template_benefit WHERE id_template_benefit = '$id_template'";
    $is_template_exist_exec = $conn->query($template_exist_query);
    if ($is_template_exist_exec === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    $is_template_exist = $is_template_exist_exec->num_rows > 0;

    if ($is_template_exist) {
        $sql = "UPDATE draft_template_benefit 
                    SET benefit = '$benefit', subbenefit = '$subbenefit', benefit_name = '$benefit_name', description = '$description', pelaksanaan = '$pelaksanaan', avail = '$avail', qty1 = '$qty1', qty2 = '$qty2', qty3 = '$qty3', valueMoney = '$value', optional = '$optional', subject = '$subject', redeemable = '$redeemable', benefit_order = '$order', highlight_color = '$color', info = '$info', manual_input = '$manual_input', editable_qty = '$editable_qty'
                WHERE id_template_benefit = '$id_template'";

        if (!$conn->query($sql)) {
            throw new Exception('Query failed: ' . $conn->error);
        }

        $query_benefit_role = "UPDATE benefit_role SET benefit = '$benefit', sub_benefit = '$subbenefit', benefit_name = '$benefit_name', unit_bisnis = '$unit_name', code = '$unit_code' WHERE id_template = '$id_template'";
        if (!$conn->query($query_benefit_role)) {
            throw new Exception('Query failed: ' . $conn->error);
        }
    } else {
        $sql = "INSERT INTO draft_template_benefit (benefit, subbenefit, benefit_name, description, pelaksanaan, avail, qty1, qty2, qty3, valueMoney, optional, subject, redeemable, benefit_order, highlight_color, info, manual_input, editable_qty) VALUES (
            '$benefit', '$subbenefit', '$benefit_name', '$description', '$pelaksanaan', '$avail', '$qty1', '$qty2', '$qty3', '$value', '$optional', '$subject', '$redeemable', '$order', '$color', '$info', '$manual_input', '$editable_qty')";

        if (!$conn->query($sql)) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        $id_template = $conn->insert_id;

        $query_benefit_role = "INSERT INTO benefit_role (id_template, benefit, sub_benefit, benefit_name, unit_bisnis, code) VALUES ('$id_template', '$benefit', '$subbenefit', '$benefit_name', '$unit_name', '$unit_code')";
        if (!$conn->query($query_benefit_role)) {
            throw new Exception('Query failed: ' . $conn->error);
        }
    }

    // Commit transaksi jika semuanya berhasil
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Template saved successfully'
    ]);

} catch (\Throwable $th) {
    // Rollback transaksi jika terjadi error
    $conn->rollback();
    error_json($th->getMessage());
}

?>
