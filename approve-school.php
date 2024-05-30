<?php
   ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username']) && ($_SESSION['role']!=='admin')){ 
        header("Location: ./main.php");
        exit();
    }
    $id_school = $_GET['id'];
    
    $sql = "UPDATE op_masterdata SET `status` = '1', `statusApprovalDate` = CURRENT_TIME() WHERE `op_masterdata`.`id_master` = '$id_school'";
    mysqli_query($conn,$sql);
    
    header('Location: ./masters.php');
    exit();
    
