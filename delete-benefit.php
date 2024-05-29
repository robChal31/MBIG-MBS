<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';

    $id_user    = $_SESSION['id'];

    if (!isset($_SESSION['username']) && $id_user != 70){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }

    $id_draft   = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;
    $id_user    = $_SESSION['id'];

    try {
        $sql = "UPDATE draft_benefit SET 
                    deleted_at = current_timestamp(),
                    updated_at = current_timestamp()
                WHERE id_draft = $id_draft";

        mysqli_query($conn, $sql);
        $_SESSION['toast_status'] = 'Success';
        $_SESSION['toast_msg'] = 'Berhasil Menghapus Draft Benefit';
        exit();
    } catch (\Throwable $th) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal Menghapus Draft Benefit';
        
        $location = 'Location: ./draft-benefit.php'; 
        mysqli_close($conn);
        header($location);
        exit();
    }

    
    
?>