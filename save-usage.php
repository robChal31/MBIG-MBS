<?php
    ob_start();
    session_start();
    include 'db_con.php';

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');

    error_reporting(E_ALL);

    $response = array();
    if (!isset($_SESSION['username'])){ 
        $response['status'] = 'error';
        $response['message'] = 'User not authenticated';
        echo json_encode($response);
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

    $event      = $_POST['event'] ?? NULL;
    $discount   = $_POST['diskon'] ?? NULL;
    $id_ticket  = $_POST['id_ticket'] ?? NULL;



    try {
        // Begin the transaction
        $conn->begin_transaction();

        if($event) {
            
            $url = 'https://hadiryuk.id/api/createpromo';
            $data = array(
                'id_event' => $event,
                'id_ticket' => $id_ticket,
                'quota' => $qty,
                'diskon' => $discount,
            );

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);            
            
            $response_api = curl_exec($ch);
            
            if($response_api === false) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                throw new Exception('cURL Error: ' . $error_msg);
            } else {
                $redeem_code = str_replace('"', '',$response_api);
            }
            curl_close($ch);
            $create_usage = "INSERT INTO `benefit_usages` (`id`, `id_benefit_list`, `user_id`, `description`, `qty1`,`qty2`, `qty3`, `used_at`, `redeem_code`) VALUES (NULL, $id_benefit_list, '$user_id', '$description', '$qty1', $qty2, '$qty3', '$used_at', '$redeem_code')";
        }else {
            $create_usage = "INSERT INTO `benefit_usages` (`id`, `id_benefit_list`, `user_id`, `description`, `qty1`,`qty2`, `qty3`, `used_at`) VALUES (NULL, $id_benefit_list, '$user_id', '$description', '$qty1', $qty2, '$qty3', '$used_at')";
        }
    
        if(mysqli_query($conn, $create_usage)) {
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
        $response['data'] = $create_usage;
    }

    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode($response);
?>
