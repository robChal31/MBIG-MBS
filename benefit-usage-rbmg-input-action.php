<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $member1 = $_POST['member1'];
    $member2 = $_POST['member2'];
    $member3 = 0;
    $member = $_POST['member'];
    $action = $_POST['action'];
    $id_rbmg = $_POST['id_rbmg'];
  
  if($action!='edit')
  {
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=5 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-rbmg-input.php');
            exit();        
        }
    }
    
    $sql = "INSERT INTO `op_rbmg` (`id_rbmg`, `id_master`, `rbmg1`, `rbmg2`, `rbmg3`, `jumlah_peserta`, `status`) VALUES (NULL, '$id_master', '$member1', '$member2', '$member3', '$member', '1');";
    mysqli_query($conn,$sql);
    
    //hitung kuota
    $sql = "update op_benefit set qty=qty-".$member." where id_master='$id_master' and id_benefittype=5;";
    mysqli_query($conn,$sql);  
  }
    
    else
    {
        $sql = "select jumlah_peserta from op_rbmg where id_rbmg='$id_rbmg'";
        $result = mysqli_query($conn, $sql);
        $rowa = mysqli_fetch_assoc($result);
        $jumlah_now = $rowa['jumlah_peserta'];
        $selisih = $jumlah_now-$member;
        
        $sql = "update op_rbmg set rbmg1='$member1',rbmg2='$member2',rbmg3='$member3',jumlah_peserta='$member' where id_rbmg='$id_rbmg'";
        mysqli_query($conn,$sql);
        if($selisih<0)
        {
            $sql = "update op_benefit set qty=qty".$selisih." where id_master='$id_master' and id_benefittype=5;";
        }
        else
        {
            $sql = "update op_benefit set qty=qty+".$selisih." where id_master='$id_master' and id_benefittype=5;";
        }
        mysqli_query($conn,$sql);
    }
    
    header('Location: ./benefit-usage-rbmg-input.php');
    exit();
    
?>