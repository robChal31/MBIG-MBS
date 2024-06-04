<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>
<?php 
    $role = $_SESSION['role'];
    
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        var_dump($_POST);
    }
?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">

            <div class="bg-white rounded h-100 p-4 mb-4">
                <h6 class="mb-4" style="display: inline-block; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Filter Benefit</h6>
                <form action="./benefits.php" method="POST">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-6">
                            <label for="type">Benefit Type</label>
                            <select class="form-select select2" name="type[]" aria-label="Default select example" multiple>
                                <option selected>Segment</option>
                                <option value="1">One</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>
                
            </div>
            
            <div class="bg-white rounded h-100 p-4">
                <h6 class="mb-4">Benefits</h6>                      
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No PK</th>
                                <th scope="col" style="width:10%">EC</th>
                                <th scope="col" style="width: 20%">School Name</th>
                                <th scope="col">Segment</th>
                                <th scope="col">Active From</th>
                                <th scope="col">Expired At</th>
                                <th scope="col">Jenis Program</th>
                                <th scope="col">Created At</th>
                                <th scope="col" style="width: 13%">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql_q = " WHERE ";
                                $id_user = $_SESSION['id_user'];

                                $sql = "SELECT 
                                            b.id_draft, b.status, b.date, b.id_user, b.id_ec, b.school_name, b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2,
                                            c.generalname, pk.id as pk_id, b.verified, a.token, b.deleted_at, b.fileUrl, pk.file_pk, pk.no_pk, pk.start_at, pk.expired_at, pk.created_at
                                        FROM draft_benefit b
                                        LEFT JOIN draft_approval as a on a.id_draft = b.id_draft AND a.id_user_approver = $id_user
                                        LEFT JOIN schools sc on sc.id = b.school_name
                                        LEFT JOIN user c on c.id_user = b.id_user 
                                        LEFT JOIN pk pk on pk.benefit_id = b.id_draft";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver =" . $_SESSION['id_user'] . " or c.leadId='" . $_SESSION['id_user'] . "') ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q b.status = 1 AND b.verified = 1 AND b.deleted_at IS NULL ORDER BY id_draft";

                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                        $status_class = $row['verified'] == 1 ? 'bg-success' :  'bg-primary';
                                        $status_msg = ($row['verified'] == 1 ? 'Verified' : 'Waiting Verification');
                                ?>
                                        <tr>
                                            <td><?= $id_draft ?></td>
                                            <td><?= $row['no_pk'] ?></td>
                                            <td><?= $row['generalname'] ?></td>
                                            <td><?= $row['school_name2'] ?></td>
                                            <td><?= ucfirst($row['segment']) ?></td>
                                            <td><?= $row['start_at'] ?></td>
                                            <td><?= $row['expired_at'] ?></td>
                                            <td><?= $row['program'] ?></td>
                                            <td>
                                                <?= $row['created_at'] ?>
                                            </td>
                                            <td>
                                                <span data-id="<?= $row['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.65rem'><?= $status_msg  ?></span>
                                            </td>
                                            <td scope='col'>
                                               
                                                <span data-id="<?= $row['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm me-2' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-eye'></i></span>
                                               
                                            </td>
                                        </tr>
                               <?php     }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="approvalModalBody">
                ...
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pkModal" tabindex="-1" role="dialog" aria-labelledby="pkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pkModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pkModalBody">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id='detail-benefit'>See Details</a>
                </div>
            </div> 
        </div>
    </div>

<?php include 'footer.php';?>
<script>

    var approvalModal = document.getElementById('approvalModal');
    approvalModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        var modalTitle = approvalModal.querySelector('.modal-title')
        modalTitle.textContent = 'Approval History ' + rowid;
        $.ajax({
            url: 'get_benefits_approver.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#approvalModalBody').html(data)
            }
        });
    })

    var pkModal = document.getElementById('pkModal');
    pkModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = pkModal.querySelector('.modal-title')
        modalTitle.textContent = "Detail PK";
        $.ajax({
            url: 'detail-pk.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#pkModalBody').html(data);
                $('#detail-benefit').attr('href', 'detail-benefit.php?id=' + rowid);
            }
        });
    })

    $(document).ready(function() {
        $('.select2').select2({
        });
    });

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>