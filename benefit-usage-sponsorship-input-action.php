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
    $id_sponsorship = $_POST['id_sponsorship'];
    $action = $_POST['action'];
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=8 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-sponsorship-input.php');
            exit();        
        }
    }
     
     if($action!='edit')
     {
        $sql = "INSERT INTO `op_sponsorship` (`id_sponsorship`, `id_master`, `description`, `status`) VALUES 
        (NULL, '".$id_master."', '".$description."', '1');";
        mysqli_query($conn,$sql);
        //echo $sql;
        //hitung kuota
        $sql = "update op_benefit set qty=qty-1 where id_master='$id_master' and id_benefittype=8;";
        mysqli_query($conn,$sql);
     }
     else
     {
         $sql = "UPDATE op_sponsorship set description='$description' where id_sponsorship='$id_sponsorship'";
         mysqli_query($conn,$sql);
     }
    
    header('Location: ./benefit-usage-sponsorship-input.php');
    exit();
    
?>