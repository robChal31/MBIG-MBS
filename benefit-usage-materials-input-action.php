<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $peserta = $_POST['name'];
    $id_master = $_POST['id_master'];
    $tanggal = $_POST['tanggal'];
    $qty = $_POST['member'];
    $benefit_name = str_replace("'","",$_POST['benefit_name']);
    $subbenefit = str_replace("'","",$_POST['subbenefit']);
    $descript = str_replace("'","",$_POST['descript']);
    if($tanggal=='')
    {
        $tanggal='0000-00-00';
    }
    
    /*$sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=1 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-materials-input.php');
            exit();        
        }
    }*/

    $sql = "INSERT INTO `op_materials` (`id_materials`, `id_master`, `status`, `isDeleted`, `progress_update`, `tanggal`, `benefit_name`, `subbenefit`, `description`, `qty`) VALUES (NULL, '', '1', '0', '0', '".$tanggal."', '".$benefit_name."', '".$subbenefit."', '".$descript."', '$qty');";
    mysqli_query($conn,$sql);

    //hitung kuota
    /*$sql = "update op_benefit set qty=qty-".$qty." where id_master='$id_master' and id_benefittype=1;";
    mysqli_query($conn,$sql);
    */
    
    header('Location: ./benefit-usage-materials-input.php');
    exit();
    
?>