<?php include 'header.php'; ?>
<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
    }

    .select2-container {
        z-index: 2050 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }
</style>
<?php 
    $role = $_SESSION['role'];
    $user_id = $_SESSION['id_user'];
?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            
<div class="card rounded shadow-sm p-3">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="fw-semibold mb-0">Approved Benefit List</h6>
            <small class="text-muted">Approved & verified draft benefits</small>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table align-middle table-hover table-sm" id="table_id" style="font-size:.8rem">
            <thead class="table-light">
                <tr>
                    <th style="width:5%">No Draft</th>
                    <th style="width:12%">Nama EC</th>
                    <th style="width:18%">Nama Sekolah</th>
                    <th>Segment</th>
                    <th>Tanggal</th>
                    <th>Jenis Program</th>
                    <th>Jenis PK</th>
                    <th style="width:14%">Status</th>
                    <th class="text-center" style="width:12%">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php
                $sql_q = " WHERE ";
                $id_user = $_SESSION['id_user'];

                $sql = "SELECT 
                            b.id_draft, b.status, b.date, b.id_user, b.id_ec, b.school_name, b.segment,
                            b.program, IFNULL(sc.name, b.school_name) as school_name2,
                            c.generalname, pk.id as pk_id, b.verified, a.token,
                            b.deleted_at, b.fileUrl, pk.file_pk, b.confirmed,
                            b.jenis_pk, c.leadid, c.leadid2, c.leadid3, pk.perubahan_tahun
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
                    $sql .= " WHERE (a.id_user_approver = $id_user
                              OR c.leadid = $id_user
                              OR c.leadid2 = $id_user
                              OR c.leadid3 = $id_user
                              OR b.id_ec = $id_user)";
                    $sql_q = " AND ";
                }

                $sql .= "$sql_q b.status = 1 AND b.deleted_at IS NULL ORDER BY id_draft";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {

                        $status_class = 'bg-info';
                        $status_msg   = 'Waiting Verification';

                        if($row['verified'] == 1){
                            $status_class = $row['confirmed'] == 1 ? 'bg-success' : 'bg-primary';
                            $status_msg   = $row['confirmed'] == 1 ? 'Confirmed' : 'Waiting Confirmation';
                        }
            ?>
                <tr>
                    <td><?= $row['id_draft'] ?></td>
                    <td class="fw-semibold"><?= $row['generalname'] ?></td>
                    <td><?= $row['school_name2'] ?></td>
                    <td><?= ucfirst($row['segment']) ?></td>
                    <td><?= $row['date'] ?></td>
                    <td>
                        <?= strtoupper($row['program']) ?>
                        <?= $row['perubahan_tahun'] ? "<br><small class='text-muted'>Perubahan Tahun {$row['perubahan_tahun']}</small>" : '' ?>
                    </td>
                    <td><?= $row['jenis_pk'] == 2 ? 'Amandemen' : 'Baru' ?></td>

                    <td>
                        <span
                            class="badge <?= $status_class ?>"
                            style="font-size:.65rem; cursor:pointer"
                            data-id="<?= $row['id_draft'] ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#approvalModal">
                            <?= $status_msg ?>
                        </span>
                    </td>

                    <!-- ACTION -->
                    <td class="text-center">
                        <div class="dropdown">
                            <i class="fas fa-ellipsis-v text-muted"
                               data-bs-toggle="dropdown"
                               style="cursor:pointer"></i>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.75rem">

                                <?php if($row['status'] == 1 && $role == 'sa' && !$row['pk_id'] && !$row['verified']) : ?>
                                    <li>
                                        <a class="dropdown-item text-warning"
                                           data-id="<?= $row['id_draft'] ?>"
                                           data-bs-toggle="modal"
                                           data-bs-target="#pkModal">
                                            <i class="fa fa-plus me-2"></i> Create PK
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if($row['pk_id']) : ?>
                                    <li>
                                        <a class="dropdown-item text-success"
                                           data-id="<?= $row['id_draft'] ?>"
                                           data-bs-toggle="modal"
                                           data-bs-target="#pkModal">
                                            <i class="fa fa-eye me-2"></i> Detail PK
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if($role == 'admin' && !$row['verified'] && $row['file_pk']) : ?>
                                    <li>
                                        <a class="dropdown-item text-primary"
                                           href="approve-draft-benefit-form.php?id_draft=<?= $row['id_draft'] ?>&token=<?= $row['token'] ?>">
                                            <i class="fas fa-fingerprint me-2"></i> Verify
                                        </a>
                                    </li>
                                    <!-- <li>
                                        <a class="dropdown-item text-danger delete-btn"
                                           data-id="<?= $row['id_draft'] ?>">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </a>
                                    </li> -->
                                <?php endif; ?>

                                <?php if($role == 'sa' && $row['verified'] && $user_id == 5 && !$row['confirmed']) : ?>
                                    <li>
                                        <a class="dropdown-item text-primary"
                                           href="approve-draft-benefit-form.php?id_draft=<?= $row['id_draft'] ?>&token=<?= $row['token'] ?>">
                                            <i class="fas fa-fingerprint me-2"></i> Verify
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if($role != 'ec' && $row['confirmed'] == 1) : ?>
                                    <li>
                                        <a class="dropdown-item text-warning"
                                           data-id="<?= $row['id_draft'] ?>"
                                           data-bs-toggle="modal"
                                           data-bs-target="#pkModal">
                                            <i class="fa fa-pen me-2"></i> Update PK
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </td>
                </tr>
            <?php
                    }
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
        $('.select2').select2({
            width: '100%'
        });
    })

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>