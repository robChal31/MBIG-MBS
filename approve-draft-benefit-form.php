
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
                        da.token, b.generalname as ec_name, a.school_name, a.program, a.segment, da.id_draft_approval, p.name as program_name,
                        da.notes, da.status, IFNULL(sc.name, a.school_name) AS school_name2, IFNULL(seg.segment, a.segment) AS new_segment
                    FROM draft_approval da 
                    LEFT JOIN draft_benefit a  on a.id_draft = da.id_draft 
                    LEFT JOIN user b on a.id_ec = b.id_user 
                    LEFT JOIN user c on c.id_user = da.id_user_approver 
                    LEFT JOIN segments as seg on seg.id = a.segment
                    LEFT JOIN schools AS sc ON sc.id = a.school_name
                    LEFT JOIN programs AS p ON p.code = a.program or p.name = a.program
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
        $program        = $row['program_name'];
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
            <div class="row justify-content-center">

                <?php if(!$ec_name): ?>
                    <div class="col-12 d-flex align-items-center justify-content-center" style="height:75vh;">
                        <div class="text-center">
                            <div class="alert alert-danger px-4 py-3">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Unauthorized</strong><br>
                                You don’t have access to this draft.
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="col-lg-8 col-md-10 col-12">
                        <div class="card shadow-sm rounded-4 p-4">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="mb-0 fw-semibold">
                                    <?= ($role == 'admin') ? 'Verify' : 'Approve' ?> Draft Benefit
                                </h6>
                                <span class="badge bg-light text-dark border">
                                    Draft ID: <?= $id_draft ?>
                                </span>
                            </div>

                            <!-- SUMMARY -->
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted small" width="35%">EC Name</td>
                                            <td class="fw-medium"><?= $ec_name ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">School Name</td>
                                            <td class="fw-medium"><?= $school_name ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Segment</td>
                                            <td><?= $segment ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Program</td>
                                            <td><?= $program ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr class="my-3">

                            <!-- FORM -->
                            <form action="save-draft-approval.php" method="POST" id="form">
                                <input type="hidden" name="token" value="<?= $token ?>">
                                <input type="hidden" name="id_user" value="<?= $id_user ?>">
                                <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
                                <input type="hidden" name="id_draft_approval" value="<?= $id_draft_approval ?>">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select class="form-select form-select-sm select2" name="status" required>
                                            <option value="1" <?= $status == 1 ? 'selected' : '' ?>>
                                            <?= ($role == 'admin') ? 'Verify' : 'Approve'; ?>
                                            </option>
                                            <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label small text-muted mb-1">Notes</label>
                                        <textarea class="form-control form-control-md" name="notes" rows="5" placeholder="Write your notes here..." required><?= $notes ?></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button class="btn btn-sm btn-primary px-4" id="btn-submit">
                                        <i class="fas fa-paper-plane me-1"></i>
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
        $('.select2').select2();
        $('#btn-submit').click(function() {
            $('#btn-submit').prop('disabled', true);
            $('#form').submit();
        });
    });
</script>
       