<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $judul = $_POST['judul'];
    $trainer = $_POST['trainer'];
    $tanggal = $_POST['tanggal'];
    $member = $_POST['member'];
    $id_inhouse = $_POST['inhouse'];
    $action = $_POST['action'];
    $onoff = $_POST['onoff'];
    
    if($tanggal=='')
    {
        $tanggal='0000-00-00';
    }
    if($trainer=='')
    {
        $trainer='-';
    }
    
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=2 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            //echo 'a';
            header('Location: ./benefit-usage-inhouse-input.php');
            exit();        
        }
    }

    if($action!='edit')
    {
       $sql = "INSERT INTO `op_inhouse` (`id_inhouse`, `id_master`, `training_date`, `training_name`, `trainer_name`, `jumlah_peserta`, `status`,`onoff`) VALUES 
       (NULL, '".$id_master."', '".$tanggal."', '".$judul."', '".$trainer."', '".$member."', '1','$onoff');";
       //echo $sql;
       mysqli_query($conn,$sql); 
       //hitung kuota
        $sql = "update op_benefit set qty=qty-".$member." where id_master='$id_master' and id_benefittype=2;";
        mysqli_query($conn,$sql);
    }
    else
    {
        $sql = "select jumlah_peserta from op_inhouse where id_inhouse='$id_inhouse'";
        $result = mysqli_query($conn, $sql);
        $rowa = mysqli_fetch_assoc($result);
        $jumlah_now = $rowa['jumlah_peserta'];
        $selisih = $jumlah_now-$member;
        
        $sql = "UPDATE op_inhouse set id_master='$id_master',training_date='$tanggal',training_name='$judul',trainer_name='$trainer',jumlah_peserta='$member',onoff='$onoff' where id_inhouse='$id_inhouse'";

        mysqli_query($conn,$sql);
        if($selisih<0)
        {
            $sql = "update op_benefit set qty=qty".$selisih." where id_master='$id_master' and id_benefittype=2;";
        }
        else
        {
            $sql = "update op_benefit set qty=qty+".$selisih." where id_master='$id_master' and id_benefittype=2;";    
        }
        mysqli_query($conn,$sql);
    }
       
    

    
    
    header('Location: ./benefit-usage-inhouse-input.php');
    exit();
    
?>