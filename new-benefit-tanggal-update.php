<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_benefit = $_POST['id_benefit'];
    $tanggal = mysqli_escape_string($conn,$_POST['tanggal']);
    $sql = "update op_simple_benefit set tanggal='$tanggal' where id_benefit='$id_benefit'";

    mysqli_query($conn,$sql);
    exit();
    
?>