<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $id_rpp = $_POST['id_rpp'];
    $action = $_POST['action'];
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=10 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-rpp-input.php');
            exit();        
        }
    }
     
     
    if($action!='edit')
    {
        $sql = "INSERT INTO `op_rpp` (`id_rpp`, `id_master`, `date_rpp`, `description`, `status`) VALUES 
        (NULL, '".$id_master."', 'NOW()', '".$descripton."', '1');";
        //echo $sql;
        mysqli_query($conn,$sql);
        
        //hitung kuota
        $sql = "update op_benefit set qty=qty-1 where id_master='$id_master' and id_benefittype=10;";
        mysqli_query($conn,$sql);    
    }
    else
    {
        $sql = "UPDATE op_rpp set description='$description' where id_rpp='$id_rpp'";
        mysqli_query($conn,$sql);
    }
    
    //echo $sql;
    header('Location: ./benefit-usage-rpp-input.php');
    exit();
    
?>