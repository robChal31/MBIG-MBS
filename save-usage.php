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

    $user_id            = $_SESSION['id_user'];
    $id_benefit_list    = $_POST['id_benefit_list'];
    $used_at            = $_POST['used_at'];
    $description        = $_POST['description'];
    $year               = $_POST['year'];
    
    $qty    = $_POST['qty'];
    $qty1   = $year == 'qty1' ? $qty : 0;
    $qty2   = $year == 'qty2' ? $qty : 0;
    $qty3   = $year == 'qty3' ? $qty : 0;

    $response = array();

    try {
        // Begin the transaction
        $conn->begin_transaction();

        $create_usage = "INSERT INTO `benefit_usages` (`id`, `id_benefit_list`, `user_id`, `description`, `qty1`,`qty2`, `qty3`, `used_at`) VALUES (NULL, $id_benefit_list, '$user_id', '$description', '$qty1', $qty2, '$qty3', '$used_at')";
    
        if(mysqli_query($conn, $create_usage)) {
            $response['success'] = true;
            $response['message'] = 'Usage saved successfully.';
        } else {
            throw new Exception(mysqli_error($conn));
        }
        $conn->commit();
    } catch (Exception $e) {
        // If an error occurs, roll back the transaction
        $conn->rollback();
        $response['success'] = false;
        $response['message'] = 'Error saving usage: ' . $e->getMessage();
        $response['data'] = $create_usage;
    }

    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode($response);
?>
