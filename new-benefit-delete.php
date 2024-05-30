<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_benefit = $_POST['id_benefit'];
    $id_template_benefit = $_POST['id_template_benefit'];

    $sql = "UPDATE op_simple_benefit set isDeleted=1 where id_benefit='$id_benefit' and isDeleted=0";

    if(!mysqli_query($conn,$sql)){
        echo("Error description: " . mysqli_error($conn));
        exit();
    }

    $sql = "SELECT * From op_simple_benefit where id_benefit='$id_benefit'";
    $result = mysqli_query($conn,$sql);
    $row  = mysqli_fetch_assoc($result);
    $id_master = $row['id_master'];
    $qty = $row['qty'];

    $sql = "UPDATE op_new_benefit set qty=qty+$qty where id_master='$id_master' and id_template_benefit='$id_template_benefit' and approval=1";
    if(!mysqli_query($conn,$sql)){
        echo("Error description: " . mysqli_error($conn));
        exit();
    }

    mysqli_close($conn);


