<?php
    ob_start();
    session_start();
    include 'db_con.php';

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');

    error_reporting(E_ALL);

    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    $user_id        = $_SESSION['id_user'];
    $id             = $_POST['id'];
    $description    = $_POST['description'];

    $response = array();

    try {
        // Begin the transaction
        $conn->begin_transaction();

        $update_usage = "UPDATE `benefit_usages` SET `description` = '$description' WHERE `benefit_usages`.`id` = $id";
    
        if(mysqli_query($conn, $update_usage)) {
            $response['status'] = true;
            $response['message'] = 'Usage saved successfully.';
        } else {
            throw new Exception(mysqli_error($conn));
        }
        $conn->commit();
    } catch (Exception $e) {
        // If an error occurs, roll back the transaction
        $conn->rollback();
        $response['status'] = false;
        $response['message'] = 'Error saving usage: ' . $e->getMessage();
        $response['data'] = $update_usage;
    }

    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode($response);
?>
