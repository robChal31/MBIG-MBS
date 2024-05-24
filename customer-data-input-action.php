<?php
    ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $action = $_POST['action'];
    $id_master = $_POST['id_master'];
    $fullname = str_replace("'","\'",$_POST['fullname']);
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $position = str_replace("'","\'",$_POST['position']);
    if(is_null($action))
    {
      $sql = "INSERT INTO `op_customerdata` (`id_customerdata`, `id_master`, `fullname`, `position`, `email`, `phone`) VALUES (NULL, '$id_master', '$fullname', '$position', '$email', '$phone');";
    }
    else if ($action=='edit')
    {
        $id_customerdata = $_POST['id_customerdata'];
        $sql = "update op_customerdata set id_master='$id_master',fullname='$fullname',position='$position',email='$email', phone='$phone' where id_customerdata='$id_customerdata'";        
    }
    mysqli_query($conn,$sql);
    header('Location: ./customer_data.php');
    exit();