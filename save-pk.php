<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    function file_pk_error_session(){
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal menambahkan PK, pastikan inputan, dan format file benar!';
        header('Location: ./approved_list.php');
        exit();
    }

    $id_draft = $_POST['id_draft'];
    $no_pk = $_POST['no_pk'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $id_sa = $_POST['id_sa'];

    $pk_exist_query = "SELECT * FROM pk where benefit_id = $id_draft";

    $is_pk_exist_exec = $conn->query($pk_exist_query);
    $is_pk_exist = $is_pk_exist_exec->num_rows > 0;

    $no_pk_exist_query = "SELECT * FROM pk where no_pk = '$no_pk' and benefit_id != $id_draft";

    $is_no_pk_exist_exec = $conn->query($no_pk_exist_query);
    $is_no_pk_exist = $is_no_pk_exist_exec->num_rows > 0;

    $target_dir = "dokumen/";
    $target_file_pk = $target_dir . basename($_FILES["file_pk"]["name"]);
    $target_file_benefit = $target_dir . basename($_FILES["file_benefit"]["name"]);

    $uploadOk = 1;
    $fileExtension_pk = strtolower(pathinfo($target_file_pk, PATHINFO_EXTENSION));
    $fileExtension_benefit = strtolower(pathinfo($target_file_benefit, PATHINFO_EXTENSION));

    // Allow certain file formats
    $validExtensions = "/(jpg|png|jpeg|gif|pdf|docx|doc|xls|xlsx)$/i";

    if($is_pk_exist) {
        if ($_FILES["file_pk"]["name"] && !preg_match($validExtensions, $fileExtension_pk)) {
            $uploadOk = 0;
        }
        
        if($_FILES["file_benefit"]["name"] && !preg_match($validExtensions, $fileExtension_benefit)) {
            $uploadOk = 0;
        }
    }else {
        if (!preg_match($validExtensions, $fileExtension_pk) || !preg_match($validExtensions, $fileExtension_benefit)) {
            $uploadOk = 0;
        }
    }
   

    if ($uploadOk == 1 && !$is_no_pk_exist) {
         
            if(!$is_pk_exist){
                if (move_uploaded_file($_FILES["file_pk"]["tmp_name"], $target_file_pk) && move_uploaded_file($_FILES["file_benefit"]["tmp_name"], $target_file_benefit)) {
                    $sql = "INSERT INTO `pk` 
                    (`benefit_id`, `no_pk`, `start_at`, `expired_at`, `sa_id`,`file_pk`, `file_benefit`, `created_at`, `updated_at`) VALUES 
                    ($id_draft, '$no_pk', '$start_date', '$end_date', $id_sa, '$target_file_pk', '$target_file_benefit', current_timestamp(), NULL)";
                }else {
                    echo $sql;die;
                    file_pk_error_session();
                }
                
            }else{
                $upadte_file_query = ",";
                if($_FILES["file_pk"]["name"]) {
                    move_uploaded_file($_FILES["file_pk"]["tmp_name"], $target_file_pk);
                    $upadte_file_query .= "file_pk = '$target_file_pk',";
                }
                if($_FILES["file_benefit"]["name"]){
                    move_uploaded_file($_FILES["file_benefit"]["tmp_name"], $target_file_benefit);
                    $upadte_file_query .= " file_benefit = '$target_file_benefit',";
                }
                
                $sql = "UPDATE pk set no_pk = '$no_pk', start_at = '$start_date', expired_at = '$end_date', sa_id = $id_sa $upadte_file_query updated_at = current_timestamp() where benefit_id = $id_draft";
            }
            
            $_SESSION['toast_status'] = 'Success';
            $_SESSION['toast_msg'] = 'Saved successfully';

            mysqli_query($conn, $sql);
            header('Location: ./approved_list.php');
            exit();
        
    }

    if($is_no_pk_exist) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Gagal menambahkan PK, nomor PK sudah ada!';
        header('Location: ./approved_list.php');
        exit();
    }else {
        file_pk_error_session();
    }
    





