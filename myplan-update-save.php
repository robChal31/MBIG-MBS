<?php
    use PHPMailer\PHPMailer\PHPMailer;

    include 'db_con.php';
    require 'vendor/autoload.php';

    $config = require 'config.php';

    ob_start();
    session_start();
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    function sendEmail($email, $name, $subject, $message, $config, $cc = []) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP(); 
            $mail->Host       = $config['host']; 
            $mail->SMTPAuth   = true; 
            $mail->Username   = $config['smtp_username'];
            $mail->Password   = $config['smtp_password']; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config['port'] ?? 465;
    
            //Recipients
            $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
    
            $mail->addAddress($email, $name);
            if(count($cc) > 0) {
                foreach ($cc as $key => $value) {
                    $mail->addCC($value['email'], $value['name']);
                }
            }
    
            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            // $mail->send();
            
        } catch (Exception $th) {
            $response['status']     = false;
            $response['message']    = 'Error saving : ' . $th->getMessage();
        }
    }

    $role       = $_SESSION['role'];
    $id_user    = $_SESSION['id_user'];
    $response   = array();
    try {
        $myplan_update_id = ISSET($_POST['myplan_update_id']) ? $_POST['myplan_update_id'] : null;

        $update         = $_POST['update_note'];
        $myplan_id      = $_POST['myplan_id'];
        $added_at       = $_POST['added_at'];
        $ec_email       = $_POST['ec_email'];
        $ec_name        = $_POST['ec_name'];
        $feedback       = ISSET($_POST['feedback']) ? $_POST['feedback'] : NULL;

        if($myplan_update_id) {
            $sql = "UPDATE myplan_update SET 
                        update_note = '$update',
                        myplan_id = '$myplan_id',
                        added_at = '$added_at',
                        feedback = '$feedback',
                        updated_at = current_timestamp()
                    WHERE id = $myplan_update_id";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Error: " . mysqli_error($conn));
            }

            if($feedback && $role == 'admin') {

                $message = "<p>Hallo $id_user, ada feedback dari admin.
                    <br>
                    <b>Update Note</b> : $update
                    <br>
                    <b>Feedback</b> : $feedback
                </p>";

                // sendEmail($ec_email, $ec_name, 'Feedback Myplan Dari Admin', $message, $config);
            }

        }else {
            $sql = "INSERT INTO `myplan_update` (`update_note`, `myplan_id`, `added_at`, `created_at`) VALUES ('$update', '$myplan_id', '$added_at', current_timestamp());";

            if (mysqli_query($conn, $sql)) {
                $myplan_update_id = mysqli_insert_id($conn);
            } else {
                throw new Exception("Error: " . mysqli_error($conn));
            }
        }
        $response['status']     = true;
        $response['message']    = 'Saved successfully.';
    } catch (\Throwable $th) {
        $response['status']     = false;
        $response['message']    = 'Error saving : ' . $th->getMessage();
    }

    ob_end_clean();

    header('Content-Type: application/json');
    echo json_encode($response);
    
?>