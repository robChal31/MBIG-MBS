<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $peserta = $_POST['nama_peserta'];
    $id_master = $_POST['id_master'];
    $tanggal = $_POST['tanggal'];
    $id_s2 = $_POST['id_s2'];
    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $sql = "update op_s2 set training_date='$tanggal',nama_peserta='$peserta',description='$description' where id_s2='$id_s2'";    
    mysqli_query($conn,$sql);
    
    header('Location: ./benefit-usage-s2-input.php');
    exit();
    
    
    //SELECT b.school_name, b.jenDok,b.date,b.title,a.training_name,a.training_date, sum(case when a.subjek_peserta = 'English' then 1 else 0 end) as peserta_english FROM `op_kolektif` a left join op_masterdata b on a.id_master=b.id_master group by a.id_master, a.training_name, a.subjek_peserta
?>