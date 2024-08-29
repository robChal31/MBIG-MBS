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
            pk.*, pk.id as id_pk, spp.name as pic_name, spp.jabatan as pic_jabatan, spp.no_tlp as pic_tlp, spp.email as pic_email
        FROM draft_benefit as b
        LEFT JOIN schools as sc on sc.id = b.school_name
        LEFT JOIN user as c on c.id_user = b.id_ec
        LEFT JOIN pk on pk.benefit_id = b.id_draft
        LEFT JOIN (
            SELECT 
                id_draft, 
                (SUM(qty) + SUM(qty2) + SUM(qty3)) as total_qty
            FROM draft_benefit_list
            GROUP BY id_draft
        ) as dbl on dbl.id_draft = b.id_draft
        LEFT JOIN school_pic_partner spp on spp.id_draft = b.id_draft
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
        $pic_name = $row['pic_name'];
        $pic_jabatan = $row['pic_jabatan'];
        $pic_tlp = $row['pic_tlp'];
        $pic_email = $row['pic_email'];
    }

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
                <td><a _blank="true" href='draft-benefit/<?= $fileUrl.".xlsx" ?>' data-toggle='tooltip' title='View Doc'><i class="bi bi-paperclip"></i> Document</a></td>
            </tr>
            <tr>
                <td><strong>MPP Website</strong></td>
                <td>:</td>
                <td><a href='https://mentaripartner.com' target="_blank" data-toggle='tooltip' title='MPP Link'><i class="bi bi-link"></i> https://mentaripartner.com</a></td>
            </tr>
        </table>

        <h6 class="mt-3 pt-3">Form PIC Program</h6>

        <form action="save-pk.php" method="POST" enctype="multipart/form-data" id="form_pic">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $pic_name ?>" placeholder="Name" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" name="jabatan" class="form-control form-control-sm" value="<?= $pic_jabatan ?>" placeholder="Position" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="text" name="email" class="form-control form-control-sm" value="<?= $pic_email ?>" placeholder="E-mail" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="no_tlp" class="form-control form-control-sm" value="<?= $pic_tlp ?>" placeholder="Phone Number" required>
                </div>
                

                <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_pic">Save</button>
            </div>

        </form>
    </div>

<script>
    $(document).ready(function() {
        let role = '<?= $role ?>';

        $('#form_pic').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-pic.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_pic').prop('disabled', true);
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Please wait while we save your data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                },
                success: function(response) {
                    Swal.close();
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
                    $('#submit_pic').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_pic').prop('disabled', false);
                }
            });
        });
    })
</script>
 
<?php } $conn->close();?>


    
    
    