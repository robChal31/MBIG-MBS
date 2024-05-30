<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $tanggal = $_POST['tanggal'];
    if(!isset($tanggal))
    {
        $tanggal='0000-00-00';
    }
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $id_supervisi = $_POST['id_supervisi'];
    $action = $_POST['action'];
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=11 and approval=1 and qty>0";

     
     if($action!='edit')
     {
        if ($valid=mysqli_query($conn,$sql))
        {
            if(mysqli_num_rows($valid)<1)
            {
                header('Location: ./benefit-usage-supervisi-input.php');
                exit();        
            }
        }
        $sql = "INSERT INTO `op_supervisi` (`id_supervisi`, `id_master`, `date_spv`, `description`, `status`) VALUES 
        (NULL, '".$id_master."', '".$tanggal."', '".$description."', '1');";
        mysqli_query($conn,$sql);
        //echo $sql;
        //hitung kuota
        $sql = "update op_benefit set qty=qty-1 where id_master='$id_master' and id_benefittype=11;";
        mysqli_query($conn,$sql);
     }
     else
     {
         $sql = "UPDATE op_supervisi set date_spv='$tanggal',description='$description' where id_supervisi='$id_supervisi'";
         //echo $sql;
         mysqli_query($conn,$sql);
     }
     
    header('Location: ./benefit-usage-supervisi-input.php');
    exit();
    
?>