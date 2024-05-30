<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $peserta = $_POST['name'];
    $id_master = $_POST['id_master'];
    $judul = $_POST['judul'];
    $tanggal = $_POST['tanggal'];
    if(!isset($_POST['tanggal']))
    {
        $tanggal = '0000-00-00';
    }
    $member = $_POST['member'];
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    
    echo $description;
    $sql = "select id_benefit from op_benefit where id_master='$id_master' and id_benefittype=13 and approval=1 and qty>0";

    if ($valid=mysqli_query($conn,$sql))
    {
        if(mysqli_num_rows($valid)<1)
        {
            header('Location: ./benefit-usage-tkt-input.php');
            exit();        
        }
    }
    
    
    for($i = 0; $i<$member ; $i++)
    {
       $sql = "INSERT INTO `op_tkt` (`id_tkt`, `id_master`, `training_date`, `nama_peserta`, `status`,`description`) VALUES 
       (NULL, '".$id_master."', '".$tanggal."', '".$peserta[$i]."', '1','".$description."');";

       mysqli_query($conn,$sql);

    }
    
    
    //hitung kuota
    $sql = "update op_benefit set qty=qty-".$member." where id_master='$id_master' and id_benefittype=13;";
    mysqli_query($conn,$sql);
    
    header('Location: ./benefit-usage-tkt-input.php');
   exit();
    
    
    //SELECT b.school_name, b.jenDok,b.date,b.title,a.training_name,a.training_date, sum(case when a.subjek_peserta = 'English' then 1 else 0 end) as peserta_english FROM `op_kolektif` a left join op_masterdata b on a.id_master=b.id_master group by a.id_master, a.training_name, a.subjek_peserta
?>