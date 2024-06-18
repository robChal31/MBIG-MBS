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

function file_pk_error_session(){
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan PK, pastikan inputan, dan format file benar!'
    ]);
    exit();
}

$id_template = $_POST['id_template'];
$benefit = $_POST['benefit'];
$subbenefit = $_POST['subbenefit'];
$benefit_name = $_POST['benefit_name'];
$avail = $_POST['avail'];
$description = $_POST['description'];
$pelaksanaan = $_POST['pelaksanaan'];
$qty1 = $_POST['qty1'];
$qty2 = $_POST['qty2'];
$qty3 = $_POST['qty3'];
$unit_bisnis = $_POST['unit_bisnis'];
$value = $_POST['value'];

echo json_encode([
    'status' => 'error',
    'message' => json_encode($_POST)
]);

try {
    $tenplate_exist_query = "SELECT * FROM draft_template_benefit WHERE id_template_benefit = $id_template";
    $is_tenplate_exist_exec = $conn->query($tenplate_exist_query);
    $is_tenplate_exist = $is_tenplate_exist_exec->num_rows > 0;

    if($is_tenplate_exist) {
        $sql = "UPDATE draft_template_benefit 
                    SET no_pk = '$no_pk', start_at = '$start_date', expired_at = '$end_date', sa_id = $id_sa, 
                    $update_file_query updated_at = current_timestamp() 
                WHERE benefit_id = $id_draft";
    }else {
        $sql = "INSERT INTO pk (id_template_benefit, benefit, subbenefit, benefit_name, description, pelaksanaan, avail, qty1, qty2, qty3, valueMoney) 
                    VALUES ($id_template, '$no_pk', '$start_date', '$end_date', $id_sa, '$target_file_pk', '$target_file_benefit', current_timestamp(), NULL)";
    }
    mysqli_query($conn, $sql);
  
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan PK, nomor PK sudah ada!'
    ]);

    file_pk_error_session();
    
} catch (\Throwable $th) {
    file_pk_error_session();
}
?>
