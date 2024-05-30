<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_master=$_POST['id_master'];
    $id_template_benefit=$_POST['id_template_benefit'];
    $qty=$_POST['qty'];
    $description = mysqli_escape_string($conn,$_POST['description']);
    $keterangan = mysqli_escape_string($conn, $_POST['keterangan']);
    $id_benefit=$_POST['id_benefit'];
    $action = $_POST['action'];
    if($action!='edit')
    {
        $sql = "INSERT INTO `op_new_benefit` (`id_benefit`, `id_master`, `id_template_benefit`,`qty`,`description`,`keterangan`) VALUES 
        (NULL, '".$id_master."', '".$id_template_benefit."', '".$qty."','".$description."','".$keterangan."');";

    }
    else
    {
        $sql="update op_new_benefit set id_master='$id_master',id_template_benefit='$id_template_benefit',qty='$qty',description='$description',keterangan='$keterangan' where id_benefit='$id_benefit'";
    }
    mysqli_query($conn,$sql);


    
 
    header('Location: ./new-benefit-data-input.php?i='.$id_master);
    exit();


    
    
    