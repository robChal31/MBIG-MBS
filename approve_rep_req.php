
<?php 
    include 'header.php';
    include 'db_con.php';
    require 'vendor/autoload.php';
    $config = require 'config.php';
    use PHPMailer\PHPMailer\PHPMailer;
?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>

<?php

    $id_user    = $_SESSION['id_user'];
    $role       = $_SESSION['role'];
    $token      = ISSET($_GET['token']) ? $_GET['token'] : NULL;
    $bir_id     = '';
    if(!$token) {
        $_SESSION['toast_status'] = 'Invalid Token';
        $_SESSION['toast_msg'] = "Token tidak valid";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }else {
        $sql = "SELECT * FROM bir_approval WHERE token = '$token'";
        $result = $conn->query($sql);
        if($result->num_rows == 0) {
            $_SESSION['toast_status'] = 'Invalid Token';
            $_SESSION['toast_msg'] = "Token tidak valid";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }else {
            $row = $result->fetch_assoc();
            $bir_id = $row['bir_id'];
            $status = $row['status'];
            $id_user_approver = $row['id_user_approver'];
            if($id_user != $id_user_approver) {
                $_SESSION['toast_status'] = 'Unauthorized';
                $_SESSION['toast_msg'] = "Kamu tidak memiliki akses untuk melakukan approve ini";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            if($status == 1) {
                $_SESSION['toast_status'] = 'Unauthorized';
                $_SESSION['toast_msg'] = "Kamu sudah melakukan approve ini";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
        }
    }

    $sql        = "SELECT 
                    bir_a.token, b.generalname as ec_name, b.username as ec_username, a.school_name, a.program, a.segment, bir_a.id as bir_a_id,
                    bir_a.notes, bir_a.status, IFNULL(sc.name, a.school_name) AS school_name2, a.id_draft
                FROM bir_approval bir_a 
                LEFT JOIN benefit_imp_report bir on bir.id = bir_a.bir_id
                LEFT JOIN draft_benefit a on a.id_draft = bir.id_draft 
                LEFT JOIN user b on a.id_ec = b.id_user 
                LEFT JOIN user c on c.id_user = bir_a.id_user_approver 
                LEFT JOIN schools AS sc ON sc.id = a.school_name
                WHERE bir_a.token = '$token'";

    if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'bani') {
        $sql .= " AND bir_a.id_user_approver = $id_user";
    }

    $ec_name    = '';
    $ec_mail    = '';
    $program    = '';
    $segment    = '';
    $program    = '';
    $uc_program  = '';
    $token      = '';
    $notes      = '';
    $status     = 1;
    $bir_a_id   = '';
    $id_draft   = '';
    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($result)) {
        $ec_name        = $row['ec_name'];
        $ec_mail        = $row['ec_username'];
        $school_name    = $row['school_name2'];
        $segment        = $row['segment'];
        $program        = $row['program'];
        $token          = $row['token'];
        $notes          = $row['notes'];
        $status         = $row['status'];
        $bir_a_id       = $row['bir_a_id'];
        $id_draft       = $row['id_draft'];
        $notes          = $row['notes'];
        $uc_program     = strtoupper($program);
    }

    if(ISSET($_POST['token'])) {

        try {
            $token      = $_POST['token'];
            $status     = $_POST['status'];
            $notes      = $_POST['notes'];
            $id_user    = $_POST['id_user'];
            $id_draft   = $_POST['id_draft'];
            $bir_a_id   = $_POST['bir_a_id'];

            mysqli_begin_transaction($conn); // <-- START TRANSACTION

            $update_bir_approval = "UPDATE bir_approval SET status = '$status', approved_at = current_timestamp(), notes = '$notes' WHERE id = $bir_a_id";
            $result = mysqli_query($conn, $update_bir_approval);

            if (!$result) {
                throw new Exception('Gagal update bir_approval: ' . mysqli_error($conn));
            }

            $get_bir = "SELECT * FROM benefit_imp_report WHERE id = $bir_id";
            $get_bir_exec = $conn->query($get_bir);
            $bir = $get_bir_exec->fetch_assoc();

            if (!$bir) {
                throw new Exception('Data benefit_imp_report tidak ditemukan.');
            }

            $file = $bir['file'];
            $filename = basename($file); // lebih aman dari explode

            if ($role != 'admin') {
                $new_token = bin2hex(random_bytes(16));
                $insert_q = "INSERT INTO bir_approval (bir_id, date, status, id_user_approver, token) VALUES ($bir_id, current_timestamp(), 0, 70, '$new_token')";
                $insert_exec = $conn->query($insert_q);

                if (!$insert_exec) {
                    throw new Exception('Gagal insert bir_approval baru: ' . mysqli_error($conn));
                }    

                $mail = new PHPMailer(true);

                $message = "
                    <style>
                        * { font-family: Helvetica, sans-serif; }
                        .container { width: 80%; margin: auto; }
                    </style>
                    <div class='container'>
                        <p>Leader sudah menyetujui permintaan laporan penggunaan benefit <strong>$uc_program</strong> untuk <strong>$school_name</strong></p>
                        <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                        <p style='margin: 20px 0px;'>
                            <a href='https://mentarigroups.com/benefit/approve_rep_req.php?token=$new_token' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>
                                Redirect me!
                            </a>
                        </p>
                        <div style='border-bottom: 1px solid #ddd;'></div>
                        <p>Jika tombol tidak berfungsi, salin tautan berikut: </p>
                        <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve_rep_req.php?token=$new_token</p>
                        <div style='text-align: center; margin-top: 35px;'>
                            <span style='font-size: .85rem; color: #333'>Mentari Benefit System</span>
                        </div>
                    </div>
                ";

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_username'];
                $mail->Password   = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['port'] ?? 465;

                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                $mail->addAddress('secretary@mentaribooks.com', 'Putri');
                $mail->addCC('yully.mentarigroups@gmail.com', 'Yully');

                $mail->addAttachment($file, $filename);

                $mail->isHTML(true);
                $uc_program = strtoupper($program);
                $mail->Subject = 'Keren, '.$ec_name.' telah mengajukan request laporan manfaat '.$uc_program.' untuk '.$school_name;
                $mail->Body    = $message;
                $mail->send();
            }else {
                $mail = new PHPMailer(true);

                $message = "
                    <style>
                        * { font-family: Helvetica, sans-serif; }
                        .container { width: 80%; margin: auto; }
                    </style>
                    <div class='container'>
                        <p>Permintaan laporan penggunaan benefit <strong>$uc_program</strong> untuk <strong>$school_name</strong> sudah disetujui</p>
                        <p>Silakan cek laporan pada lampiran email ini.</p>
                       
                        <div style='text-align: center; margin-top: 35px;'>
                            <span style='font-size: .85rem; color: #333'>Mentari Benefit System</span>
                        </div>
                    </div>
                ";

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_username'];
                $mail->Password   = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['port'] ?? 465;

                $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');
                $mail->addAddress($ec_mail, $ec_name);
                $mail->addAttachment($file, $filename);

                $mail->isHTML(true);
                $uc_program = strtoupper($program);
                $mail->Subject = 'Keren, '.$ec_name.' telah mengajukan request laporan manfaat '.$uc_program.' untuk '.$school_name;
                $mail->Body    = $message;
                $mail->send();
            }

            $bir_status = ($role != 'admin' && $status == 1) ? 0 : $status;
            $update_bir = "UPDATE benefit_imp_report SET status = '$bir_status' WHERE id_draft = $id_draft";
            $update_exec = $conn->query($update_bir);

            if (!$update_exec) {
                throw new Exception('Gagal update status BIR: ' . mysqli_error($conn));
            }

            mysqli_commit($conn); // <-- COMMIT TRANSACTION

            $_SESSION['toast_status'] = 'Success';
            $_SESSION['toast_msg'] = "Berhasil approve request report penggunaan manfaat";
            header('Location: ./detail-benefit.php?id='.$id_draft);
            exit;
        } catch (\Throwable $th) {
            mysqli_rollback($conn); // <-- ROLLBACK TRANSACTION

            $_SESSION['toast_status'] = 'Error';
            $_SESSION['toast_msg'] = "Gagal approve request report penggunaan manfaat: " . $th->getMessage();
            header('Location: ./detail-benefit.php?id='.$id_draft);
            exit;
        }

        exit();
    }
    

?>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <?php 
                    if(!$ec_name) : 
                ?>
                   <div class="" style="height: 75vh;">
                    <div class="alert alert-danger">
                            <span><i class="fas fa-times me-2"></i>Unauthorized</span>
                        </div>
                   </div>
                <?php else: ?>
                    <div class="col-md-7 col-12">
                        <div class="bg-whites rounded h-100 p-4">
                            <h6 class="mb-4">Approve Draft Benefit</h6>    
                            <form method="POST" id="form">
                                <input type="hidden" name="token" value="<?= $token ?>">
                                <input type="hidden" name="id_user" value="<?= $id_user ?>">
                                <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
                                <input type="hidden" name="bir_a_id" value="<?= $bir_a_id ?>">
                                <div class="mb-2 row">
                                    <table class="table table-striped table-bordered" id="">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">EC Name</td>
                                                <td><?= $ec_name ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">School Name</td>
                                                <td><?= $school_name ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Segment</td>
                                                <td><?= $segment ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Program</td>
                                                <td><?= $program ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="my-2 py-2">
                                    <label for="approval_status" class="form-label px-1 mb-0 pb-0" style="font-size: .85rem;">Status</label>
                                    <select class="form-select form-select-sm" aria-label="Default select" name='status' id="approval_status" required>
                                        <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Approve</option>
                                        <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                </div>
                                
                                <div class="my-2">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Notes" style="height: 200px;" name="notes" required><?= $notes ?></textarea>
                                        <label for="floatingTextarea">Notes</label>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button class="btn btn-sm btn-primary" id="btn-submit">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        <!-- Sale & Revenue End -->

<?php include 'footer.php';?>
<script>
    $(document).ready( function () {
        $('#btn-submit').click(function() {
            $('#btn-submit').prop('disabled', true);
            $('#form').submit();
        });
    });
</script>
       