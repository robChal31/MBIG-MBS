<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./main.php");
        exit();
    }
    $peserta = $_POST['nama_peserta'];
    $id_master = $_POST['id_master'];
    $judul = $_POST['judul'];
    $tanggal = $_POST['tanggal'];
    $id_lg = $_POST['id_lg'];

    $sql = "update op_lg set id_master='$id_master',training_name='$judul',training_date='$tanggal',nama_peserta='$peserta' where id_lg='$id_lg'";    
    mysqli_query($conn,$sql);
    
    header('Location: ./benefit-usage-lg-input.php');
    exit();
    
    
    //SELECT b.school_name, b.jenDok,b.date,b.title,a.training_name,a.training_date, sum(case when a.subjek_peserta = 'English' then 1 else 0 end) as peserta_english FROM `op_kolektif` a left join op_masterdata b on a.id_master=b.id_master group by a.id_master, a.training_name, a.subjek_peserta
?>