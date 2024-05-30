<?php
   ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username']) && ($_SESSION['role']!=='admin')){ 
        header("Location: ./main.php");
        exit();
    }
    $id_benefit = $_GET['id'];
    
    $sql = "UPDATE `op_benefit` SET `approval` = '1', `dateApproval` = CURRENT_TIME() WHERE `op_benefit`.`id_benefit` = '$id_benefit';";
    mysqli_query($conn,$sql);
    
    header('Location: ./masterb.php');
    exit();
    
