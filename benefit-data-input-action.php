<?php
    ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_master=$_POST['id_master'];
    $id_benefittype=$_POST['id_benefittype'];
    $qty=$_POST['qty'];
    $description = mysqli_escape_string($conn,$_POST['description']);
    $id_benefit=$_POST['id_benefit'];
    $action = $_POST['action'];
    if($action!='edit')
    {
        $sql = "INSERT INTO `op_benefit` (`id_benefit`, `id_master`, `id_benefittype`,`qty`,`description`) VALUES 
        (NULL, '".$id_master."', '".$id_benefittype."', '".$qty."','".$description."');";
    }
    else
    {
        $sql="update op_benefit set id_master='$id_master',id_benefittype='$id_benefittype',qty='$qty',description='$description' where id_benefit='$id_benefit'";
    }
    mysqli_query($conn,$sql);
    
    header('Location: ./benefit-data-input.php?i='.$id_master);
    exit();


    
    
    