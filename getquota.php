<?php
   ob_start();
   session_start();
   include 'db_con.php';
   if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    
    $id_benefittype = $_GET['idb'];
    $id_master = $_GET['idm'];
    
    $sql = "SELECT qty from op_benefit where id_masterdata='$id_master' and id_benefittype='$id_benefittype' LIMIT 1";
    if($result = mysqli_query($conn,$sql))
    {
        while($row = mysqli_fetch_assoc($result))
        {
            echo $row['qty'];
        }
    }
    mysqli_close($conn);