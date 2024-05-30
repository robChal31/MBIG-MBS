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
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $id_mta = $_POST['id_mta'];
    $action = $_POST['action'];
    
    if(!isset($tanggal))
    {
        $tanggal='0000-00-00';
    }

    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=12 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-mta-input.php');
            exit();        
        }
    }
     
     if($action!='edit')
     {
        $sql = "INSERT INTO `op_mta` (`id_mta`, `id_master`, `date_mta`, `deskripsi`, `status`) VALUES 
        (NULL, '".$id_master."', '".$tanggal."', '".$description."', '1');";     
        mysqli_query($conn,$sql);
        $sql = "update op_benefit set qty=qty-1 where id_master='$id_master' and id_benefittype=12;";
        mysqli_query($conn,$sql);
     }
     else
     {
         $sql = "Update op_mta set id_master='$id_master',date_mta='$tanggal',deskripsi='$description' where id_mta='$id_mta'";
         mysqli_query($conn,$sql);
     }
    
    
    
    
    //hitung kuota
    
    
    header('Location: ./benefit-usage-mta-input.php');
    exit();
    
?>