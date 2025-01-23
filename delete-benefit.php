<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';

    $id_user  = $_SESSION['id_user'];
    $id_draft = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : null;

    $sql      = "SELECT * FROM draft_benefit as db where id_draft = $id_draft";
    $result   = mysqli_query($conn,$sql);
    $data     = $result->fetch_assoc();

    $id_ec    = '';
    $status   = '';
    $fileUrl  = '';

    if($data) {
        $id_ec = $data['id_ec'];
        $status = $data['status'];
        $fileUrl = $data['fileUrl'];
    }
   
    $is_creator_or_admin = $id_user == $id_ec || $id_user == 70 || $id_user == 15;

    if (($id_user != $id_ec && $status != 0 && $fileUrl) || (($id_user != 70 || $id_user != 15) && $id_user != $id_ec)) {
        $response = [
            'status' => 'Error',
            'message' => 'Unauthorized Access'
        ];
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "UPDATE draft_benefit SET 
                    deleted_at = current_timestamp(),
                    updated_at = current_timestamp()
                WHERE id_draft = $id_draft";

        mysqli_query($conn, $sql);
        $response = [
            'status' => 'Success',
            'message' => 'Berhasil Menghapus Draft Benefit'
        ];
        echo json_encode($response);
        exit();
    } catch (\Throwable $th) {
        
        $response = [
            'status' => 'Error',
            'message' => 'Gagal Menghapus Draft Benefit'
        ];
        echo json_encode($response);

        mysqli_close($conn);
        exit();
    }

    
    
?>