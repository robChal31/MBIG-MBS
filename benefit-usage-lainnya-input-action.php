<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $tanggal = $_POST['tanggal'];
    if(!isset($tanggal))
    {
        $tanggal='0000-00-00';
    }
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $id_lainnya = $_POST['id_lainnya'];
    $action = $_POST['action'];
     
     if($action!='edit')
     {
        $sql = "INSERT INTO `op_lainnya` (`id_lainnya`, `id_master`, `description`, `status`) VALUES 
        (NULL, '".$id_master."', '".$description."', '1');";
        mysqli_query($conn,$sql);
        //echo $sql;
        //hitung kuota
     }
     else
     {
         $sql = "UPDATE op_lainnya set description='$description' where id_lainnya='$id_lainnya'";
         mysqli_query($conn,$sql);
     }
    
    header('Location: ./benefit-usage-lainnya-input.php');
    exit();
    
?>