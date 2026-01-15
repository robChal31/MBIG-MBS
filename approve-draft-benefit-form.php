
<?php 
    include 'header.php'; 
?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>

<?php

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    $id_user    = $_SESSION['id_user'];
    $role       = $_SESSION['role'];
    $id_draft   = $_GET['id_draft'];
    $token      = $_GET['token'];

    if ((!$token || $token == '') && ($role == 'admin')) {
        $tokenLeader = generateRandomString(16);
        $sql_check = "SELECT * FROM draft_approval WHERE id_draft = '$id_draft' AND id_user_approver = '$id_user'";
        $result = $conn->query($sql_check);
        
        if ($result->num_rows > 0) {
            $token = '';
            while ($row = $result->fetch_assoc()) {
                $token = $row['token'];
            }
            if($token) {
                $url = "./approve-draft-benefit-form.php?id_draft=$id_draft&token=$token";
                header("Location: $url");
                exit;
            }else {
                $url = "./approved-list.php";
                header("Location: $url");
                exit;
            }
           
           
        } else {
            $sql = "INSERT INTO `draft_approval` (`id_draft_approval`, `id_draft`, `date`, `token`, `id_user_approver`, `status`) VALUES (NULL, '$id_draft', current_timestamp(), '".$tokenLeader."', '$id_user', '0');";
        
            if (mysqli_query($conn, $sql)) {
                $url = "./approve-draft-benefit-form.php?id_draft=$id_draft&token=$tokenLeader";
                header("Location: $url");
                exit;
            } else {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
        }
        
    }
    
    $sql        = "SELECT 
                        da.token, b.generalname as ec_name, a.school_name, a.program, a.segment, da.id_draft_approval,
                        da.notes, da.status, IFNULL(sc.name, a.school_name) AS school_name2, IFNULL(seg.segment, a.segment) AS new_segment
                    FROM draft_approval da 
                    LEFT JOIN draft_benefit a  on a.id_draft = da.id_draft 
                    LEFT JOIN user b on a.id_ec = b.id_user 
                    LEFT JOIN user c on c.id_user = da.id_user_approver 
                    LEFT JOIN segments as seg on seg.id = a.segment
                    LEFT JOIN schools AS sc ON sc.id = a.school_name
                    WHERE da.id_draft = $id_draft
                    AND da.token = '$token'";

    if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'bani') {
        $sql .= " AND da.id_user_approver = $id_user";
    }

    $ec_name    = '';
    $program    = '';
    $segment    = '';
    $program    = '';
    $token      = '';
    $notes      = '';
    $status     = 1;
    $id_draft_approval = '';
    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($result)) {
        $ec_name        = $row['ec_name'];
        $school_name    = $row['school_name2'];
        $segment        = $row['new_segment'];
        $program        = $row['program'];
        $token          = $row['token'];
        $notes          = $row['notes'];
        $status         = $row['status'];
        $id_draft_approval = $row['id_draft_approval'];
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
                        <div class="card rounded h-100 p-4">
                            <h6 class="mb-4"><?= ($role == 'admin') ? 'Verify' : 'Approve' ?> Draft Benefit</h6>    
                            <form action="save-draft-approval.php" method="POST" id="form">
                                <input type="hidden" name="token" value="<?= $token ?>">
                                <input type="hidden" name="id_user" value="<?= $id_user ?>">
                                <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
                                <input type="hidden" name="id_draft_approval" value="<?= $id_draft_approval ?>">
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
                                        <option value="1" <?= $status == 1 ? 'selected' : '' ?>><?= ($role == 'admin') ? 'Verify' : 'Approve'; ?></option>
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
       