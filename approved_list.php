<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>
<?php 
    $role = $_SESSION['role'];
  
?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            
            <div class="bg-whites rounded h-100 p-4">
                <h6 class="mb-4">Approved Benefit List</h6>                      
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No Draft</th>
                                <th scope="col" style="width:10%">Nama EC</th>
                                <th scope="col" style="width: 20%">Nama Sekolah</th>
                                <th scope="col">Segment</th>
                                <th scope="col">Tanggal Pembuatan</th>
                                <th scope="col">Jenis Program</th>
                                <th scope="col">Jenis PK</th>
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
                                            c.generalname, pk.id as pk_id, b.verified, a.token, b.deleted_at, b.fileUrl, pk.file_pk, b.confirmed, b.jenis_pk, c.leadid, c.leadid2, c.leadid3, pk.perubahan_tahun
                                        FROM draft_benefit b
                                        LEFT JOIN (
                                            SELECT *
                                            FROM draft_approval da
                                            WHERE da.id_user_approver = $id_user
                                            AND da.date = (
                                                SELECT MAX(date)
                                                FROM draft_approval
                                                WHERE id_draft = da.id_draft
                                                AND id_user_approver = $id_user
                                            )
                                        ) a ON a.id_draft = b.id_draft
                                        LEFT JOIN schools sc on sc.id = b.school_name
                                        LEFT JOIN user c on c.id_user = b.id_ec 
                                        LEFT JOIN pk pk on pk.benefit_id = b.id_draft";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver = $id_user or c.leadid = $id_user or c.leadid2 = $id_user or c.leadid3 = $id_user or b.id_ec = $id_user) ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q b.status = 1 AND b.deleted_at IS NULL ORDER BY id_draft";

                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                        $status_class = $row['verified'] == 1 ? 'bg-success' :  'bg-info';
                                        $status_msg = ($row['verified'] == 1 ? 'Verified' : 'Waiting Verification');
                                        if($row['verified'] == 1) {
                                            $status_class = $row['confirmed'] == 1 ? 'bg-success' :  'bg-primary';
                                            $status_msg = ($row['confirmed'] == 1 ? 'Confirmed' : 'Waiting Confirmation');
                                        }

                                ?>
                                        <tr>
                                            <td><?= $id_draft ?></td>
                                            <td><?= $row['generalname'] ?></td>
                                            <td><?= $row['school_name2'] ?></td>
                                            <td><?= ucfirst($row['segment']) ?></td>
                                            <td><?= $row['date'] ?></td>
                                            <td><?= $row['program'] ?> <?= $row['perubahan_tahun'] != null ? '(Perubahan Manual Tahun ke '.$row['perubahan_tahun'].')' : '' ?></td>
                                            <td><?= $row['jenis_pk'] == 2 ? 'Amandemen' : 'Baru' ?></td>
                                           
                                            <td>
                                                <span data-id="<?= $row['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.65rem'><?= $status_msg  ?></span>
                                            </td>
                                            <td scope='col'>

                                                <div class="d-flex gap-1">
                                                    <?php if($row['status'] == 1 && $role == 'sa' && $row['pk_id'] == null && $row['verified'] == 0) { ?>
                                                        <span data-id="<?= $row['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-warning btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Create'><i class='fa fa-plus'></i></span>
                                                    <?php }else if($row['status'] == 1 && $row['pk_id']) { ?>
                                                        <?php if($role == 'sa' && $row['verified'] == 0) { ?>
                                                            <span data-id="<?= $row['id_draft'] ?>" data-action='edit' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-success btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Edit'><i class='fas fa-pen'></i></span>
                                                        <?php }else { ?>
                                                            <span data-id="<?= $row['id_draft'] ?>" data-action='view' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-success btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fas fa-eye'></i></span>
                                                        <?php } ?>
                                                    <?php } ?>

                                                    <!-- <?php if($row['verified'] == 0 && ($id_user == 70 || $id_user == 15) && $row['file_pk']) {?>
                                                        <a href='approve-draft-benefit-form.php?id_draft=<?= $id_draft ?>&token=<?= $row['token'] ?>' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Verify'><i class='fas fa-fingerprint'></i></a>
                                                    <?php } ?> -->

                                                    <?php if(($id_user == 70 || $id_user == 15) && $row['verified'] == 0 && $row['file_pk']) { ?>
                                                        <a href='approve-draft-benefit-form.php?id_draft=<?= $id_draft ?>&token=<?= $row['token'] ?>' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Verify'><i class='fas fa-fingerprint'></i></a>

                                                        <a href='#' data-id="<?= $id_draft ?>" class='btn btn-outline-danger btn-sm me-1 delete-btn' style='font-size: .75rem' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></a>
                                                    <?php } ?>

                                                    <?php if($id_user == 5 && $row['verified'] == 1) { ?>
                                                        <a href='approve-draft-benefit-form.php?id_draft=<?= $id_draft ?>&token=<?= $row['token'] ?>' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Confirm'><i class='fas fa-fingerprint'></i></a>
                                                    <?php } ?>

                                                    <?php if($role != 'ec' && $row['confirmed'] == 1) { ?>
                                                        <span data-id="<?= $row['id_draft'] ?>" data-action='updatePK' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-warning btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Update'><i class='fa fa-pen'></i></span>
                                                    <?php } ?>
                                                </div>
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
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="approvalModalBody">
                Lodaing...
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pkModal" tabindex="-1" role="dialog" aria-labelledby="pkModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pkModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pkModalBody">
                Lodaing...
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="pkModalBody">
                Lodaing...
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

    let role = '<?= $role ?>';
    var pkModal = document.getElementById('pkModal');
    pkModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = pkModal.querySelector('.modal-title')
        modalTitle.textContent = action == 'create' ?  "Input PK" : "Edit PK";
        if(role != 'sa') {
            modalTitle.textContent = "Detail PK";
        }
        $.ajax({
            url: 'input-pk.php',
            type: 'POST',
            data: {
                id_draft: rowid,
                action: action
            },
            success: function(data) {
                $('#pkModalBody').html(data);
            }
        });
    })

    $('.delete-btn').click(function() {
        let idDraft = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You will delete this draft!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: 'delete-benefit.php',
                    type: 'POST',
                    data: {
                        id_draft: idDraft
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            html: 'Please wait while we save your data.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function(data) {
                        let resData = JSON.parse(data)
                        Swal.close()
                        if(resData.status == 'Success') {
                            Swal.fire({
                                title: "Deleted!",
                                text: resData.message,
                                icon: "success"
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                            
                            
                        }else {
                            Swal.fire({
                                title: "Error!",
                                text: resData.message,
                                icon: "error"
                            });
                        }
                        // location.reload();
                    },
                    error: function(data) {
                        Swal.close();
                        let resData = JSON.parse(data)
                        Swal.fire({
                            title: "Error!",
                            text: resData.message,
                            icon: "error"
                        });
                            // location.reload();
                    }
                });
            }
        });
    })

    $(document).ready(function() {
    
    })

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>