<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }


    $id_user = $_POST['id_user'];
    $school_name = $_POST['nama_sekolah'];
    $id_master = $_POST['nama_sekolah'];
    $segment = $_POST['segment'];
    $program = $_POST['program'];
    $inputEC = $_POST['inputEC'];
    $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`) VALUES (NULL, '$id_user','$inputEC', '$school_name', '$segment','$program', current_timestamp(), '0');";
    mysqli_query($conn,$sql);
    $id_draft = mysqli_insert_id($conn);

    $book_titles = $_POST['titles'];
    $book_levels = $_POST['levels'];
    $book_type = $_POST['booktype'];
    $jumlah_siswas = $_POST['jumlahsiswa'];
    $usulan_hargas = $_POST['usulanharga'];
    $normals = $_POST['harganormal'];
    $diskons = $_POST['diskon'];
    $aloks = $_POST['alokasi'];
    $row_length = count($book_titles);
    $sumalok = 0;
    for($i=0;$i<$row_length;$i++)
    {
        $new_title = $book_titles[$i]." - ".$book_levels[$i]." - ".$book_type[$i];
        $sql = "INSERT INTO `calc_table` (`id_row`, `id_draft`, `book_title`,`qty`, `usulan_harga`,`normalprice`, `discount`, `alokasi`) VALUES (NULL, '$id_draft', '$new_title','$jumlah_siswas[$i]', '$usulan_hargas[$i]','$normals[$i]', '$diskons[$i]', '$aloks[$i]');";
        echo $sql;
        mysqli_query($conn,$sql);
        $sumalok += $aloks[$i];
    }

    $_SESSION['sumalok']=$sumalok;
    $_SESSION['id_draft'] = $id_draft;
    $_SESSION['program'] = $program;
    $_SESSION['school_name'] = $school_name;
    $_SESSION['segment'] = $segment;
    mysqli_close($conn);
    header('Location: ./new-benefit-ec-input2.php');
    exit();
    
?>