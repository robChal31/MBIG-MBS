<?php

session_start();
include 'db_con.php';

$id_draft = $_POST['id_draft'];
$action = $_POST['action'];
$role = $_SESSION['role'];                                                                 
$sql = "SELECT 
            b.*, 
            c.*, 
            IFNULL(sc.name, b.school_name) as school_name2, 
            dbl.total_qty,
            pk.*, pk.id as id_pk
        FROM draft_benefit as b
        LEFT JOIN schools as sc on sc.id = b.school_name
        LEFT JOIN user as c on c.id_user = b.id_user
        LEFT JOIN pk on pk.benefit_id = b.id_draft
        LEFT JOIN (
            SELECT 
                id_draft, 
                (SUM(qty) + SUM(qty2) + SUM(qty3)) as total_qty
            FROM draft_benefit_list
            GROUP BY id_draft
        ) as dbl on dbl.id_draft = b.id_draft
        where b.id_draft = $id_draft";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ec_name = $row['generalname'];
        $school = $row['school_name2'];
        $program = $row['program'];
        $segment = $row['segment'];
        $total_qty = $row['total_qty'];
        $id_pk = $row['id_pk'];
        $no_pk = $row['no_pk'];
        $start_date = $row['start_at'];
        $end_date = $row['expired_at'];
        $id_sa = $row['sa_id'];
        $file_pk = $row['file_pk'];
        $file_benefit = $row['file_benefit'];
        $fileUrl = $row['fileUrl'];
    }

    $sq_query = "SELECT * FROM dash_sa WHERE is_active = 1";
                
    $sa_exec_query = $conn->query($sq_query);
?>
    <div class="p-2">
        <h6>Detail Benefit</h6>
        <table class="table table-striped">
            <tr>
                <td style="width: 20%"><strong>EC</strong></td>
                <td style="width: 1%">:</td>
                <td><?= $ec_name ?></td>
            </tr>
            <tr>
                <td><strong>Sekolah</strong></td>
                <td>:</td>
                <td><?= $school ?></td>
            </tr>
            <tr>
                <td><strong>Segment</strong></td>
                <td>:</td>
                <td><?= strtoupper($segment) ?></td>
            </tr>
            <tr>
                <td><strong>Program</strong></td>
                <td>:</td>
                <td><?= strtoupper($program) ?></td>
            </tr>
            <tr>
                <td><strong>Total Quantity Adopsi</strong></td>
                <td>:</td>
                <td><?= number_format($total_qty, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td><strong>File Draft Benefit</strong></td>
                <td>:</td>
                <td><a href='draft-benefit/<?= $fileUrl.".xlsx" ?>' data-toggle='tooltip' title='View Doc'><i class="bi bi-paperclip"></i> Document</a></td>
            </tr>
        </table>

        <?php
            if($role == 'sa') { ?>
                <h6 class="mt-4 pt-4">Form PK</h6>
        <?php } ?>
        <form action="save-pk.php" method="POST" enctype="multipart/form-data" id="form_pk">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Nomor PK</label>
                    <input type="text" name="no_pk" class="form-control form-control-sm" value="<?= $no_pk ?>" placeholder="Nomor PK" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Active From</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $start_date ?>" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Expired At</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $end_date ?>" required>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Sales Admin</label>
                    <select name="id_sa" id="id_sa" class="form-control form-control-sm" required>
                        <?php while ($sa_list = $sa_exec_query->fetch_assoc()) { ?>
                            <option value="<?= $sa_list['id_sa'] ?>" <?= $id_sa == $sa_list['id_sa'] ? 'selected' : ''  ?>><?= $sa_list['sa_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">PK <span style="font-size: .7rem; color: #333"><?= $role == 'sa' ? '(must be pdf file)*' : '' ?></span></label>
                    <?php if($file_pk) { ?>
                        <a href="<?= $file_pk ?>" class="d-block m-0 p-0" target="_blank"><i class="fa fa-paperclip"></i> <span style="font-size: .85rem;">File PK</span></a>
                    <?php } ?>
                    <input type="file" name="file_pk" class="form-control form-control-sm" <?= $id_pk ? '' : 'required' ?>>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Benefit <span style="font-size: .7rem; color: #333"><?= $role == 'sa' ? '(must be pdf file)*' : '' ?></span></label>
                    <?php if($file_benefit) { ?>
                        <a href="<?= $file_benefit ?>" class="d-block m-0 p-0" target="_blank"><i class="fa fa-paperclip"></i> <span style="font-size: .85rem;">File Benefit</span></a>
                    <?php } ?>
                    <input type="file" name="file_benefit" class="form-control form-control-sm" <?= $id_pk ? '' : 'required' ?>>
                </div>
                <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
            </div>
            <?php
                if($role == 'sa' && $action != 'view') {?>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                        <button class="btn btn-primary btn-sm" id="submit_pk">Save</button>
                    </div>
            <?php } else {?>
                <div class="d-flex justify-content-end">
                        <button type="button" class="me-2 btn btn-secondary btn-sm close">Close</button>
                    </div>
            <?php } ?>
        </form>
    </div>

<script>
    $(document).ready(function() {
        let role = '<?= $role ?>';
        if(role != 'sa') {
            $('input').attr('disabled', true);
        }

        $('#form_pk').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-pk.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_pk').prop('disabled', true);
                },
                success: function(response) {
                    if(response.status == 'success') {
                        Swal.fire({
                            title: "Saved!",
                            text: response.message,
                            icon: "success"
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }else {
                        Swal.fire({
                            title: "Failed!",
                            text: response.message,
                            icon: "error"
                        });
                    }
                    $('#submit_pk').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_pk').prop('disabled', false);
                }
            });
        });
    })
</script>
 
<?php } $conn->close();?>


    
    
    