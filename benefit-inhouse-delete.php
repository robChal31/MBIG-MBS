<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $id_inhouse = $_GET['id'];
    
    $sql = "select jumlah_peserta from op_inhouse where id_inhouse='$id_inhouse';";
    $result = mysqli_query($conn,$sql);
    $row  = mysqli_fetch_assoc($result);
    echo $row['jumlah_peserta'];
    
    $sql = "delete from op_inhouse where id_inhouse='$id_inhouse'";
    