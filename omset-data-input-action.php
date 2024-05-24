<?php
    ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $action=$_POST['action'];
    $id_master=$_POST['id_master'];
    $year=$_POST['thn'];
    $omset = $_POST['omset'];
    $id_omset = $_POST['id_omset'];
    if($action!=='edit')
    {
        $sql = "INSERT INTO `op_omset` (`id_omset`, `id_master`, `year`, `omset`) VALUES (NULL, '".$id_master."', '".$year."', '".$omset."');";
    }
    else
    {
        $sql = "UPDATE op_omset set year='$year',omset='$omset',id_master='$id_master' where id_omset='$id_omset'";
    }
    mysqli_query($conn,$sql);
    
    header('Location: ./masters.php');
    exit();


    
    
    