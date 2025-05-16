<?php
    ob_start();
    session_start();
    include 'db_con.php';
    require 'vendor/autoload.php';

    $id_user  = $_SESSION['id_user'];
    $id = ISSET($_POST['id']) ? $_POST['id'] : null;

    $sql      = "SELECT * FROM myplan as db where id = $id";
    $result   = mysqli_query($conn,$sql);
    $data     = $result->fetch_assoc();

    $user_id    = '';
    $status   = '';
    $fileUrl  = '';

    if($data) {
        $user_id = $data['user_id'];
    }
   
    $is_creator_or_admin = $id_user == $user_id || $id_user == 70 || $id_user == 15;

    if (!$is_creator_or_admin && ($status != 0)) {
        $response = [
            'status' => 'Error',
            'message' => 'Unauthorized Access'
        ];
        echo json_encode($response);
        exit();
    }

    try {
        $sql = "UPDATE myplan SET 
                    deleted_at = current_timestamp(),
                    updated_at = current_timestamp()
                WHERE id = $id";

        mysqli_query($conn, $sql);
        $response = [
            'status' => 'Success',
            'message' => 'Berhasil Menghapus Plan'
        ];
        echo json_encode($response);
        exit();
    } catch (\Throwable $th) {
        
        $response = [
            'status' => 'Error',
            'message' => 'Gagal Menghapus Plan'
        ];
        echo json_encode($response);

        mysqli_close($conn);
        exit();
    }

    
    
?>