
<?php 
    include 'header.php';

    $role = $_SESSION['role'];
    $id_user = $_SESSION['id_user'];
?>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="card rounded h-100 p-4 shadow">

                        <!-- HEADER -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">My Plan</h5>
                                <small class="text-muted">Manage and track plan progress</small>
                            </div>

                            <?php if ($role == 'admin' || $role == 'ec') { ?>
                                <a href="myplan-form.php" class="btn btn-primary btn-sm fw-semibold">
                                <i class="fas fa-plus me-2"></i> Create Plan
                                </a>
                            <?php } ?>
                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive">
                            <table class="table align-middle" id="table_draft">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama EC</th>
                                        <th>Nama Sekolah</th>
                                        <th>Segment</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Program</th>
                                        <th>Proyeksi Omset</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                        $sql = "SELECT mp.*, user.*, sc.name as school_name, db.id_draft as id_draft, db.fileUrl,
                                                db.status, db.verified, db.confirmed,
                                                IFNULL(seg.segment, mp.segment) as new_segment,
                                                IFNULL(prog.name, mp.program) as new_program
                                                FROM myplan AS mp
                                                LEFT JOIN schools AS sc ON sc.id = mp.school_id
                                                LEFT JOIN draft_benefit AS db ON mp.id = db.myplan_id
                                                LEFT JOIN user ON mp.user_id = user.id_user
                                                LEFT JOIN programs AS prog ON (prog.name = mp.program OR prog.code = mp.program)
                                                LEFT JOIN segments AS seg ON seg.id = mp.segment
                                                WHERE mp.deleted_at IS NULL";

                                        if ($role == 'ec') {
                                            $sql .= " AND (mp.user_id = $id_user 
                                                    OR user.leadId = $id_user 
                                                    OR user.leadId2 = $id_user 
                                                    OR user.leadId3 = $id_user)";
                                        }

                                        $sql .= " ORDER BY mp.created_at DESC";
                                        $result = mysqli_query($conn, $sql);

                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {

                                            $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft'
                                                    : ($row['status'] == 0 ? 'Waiting Approval'
                                                    : ($row['status'] == 1 ? 'Approved' : 'Rejected'));

                                            if ($row['verified'] == 1 && $row['status'] == 1 && $row['confirmed'] == 0) {
                                                $stat = 'Waiting Confirmation';
                                            } elseif ($row['verified'] == 0 && $row['status'] == 1) {
                                                $stat = 'Waiting Verification';
                                            }

                                            $badgeClass = match ($stat) {
                                                'Draft' => 'bg-primary',
                                                'Approved' => 'bg-success',
                                                'Rejected' => 'bg-danger',
                                                'Waiting Approval', 'Waiting Verification', 'Waiting Confirmation' => 'bg-warning text-dark',
                                                default => 'bg-primary'
                                            };

                                            $isCreator = $_SESSION['id_user'] == $row['user_id'] || $role == 'admin';
                                    ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['generalname'] ?></td>
                                        <td><?= $row['school_name'] ?></td>
                                        <td><?= ucfirst($row['new_segment']) ?></td>
                                        <td><?= $row['start_timeline'] ?></td>
                                        <td><?= $row['end_timeline'] ?></td>
                                        <td><?= strtoupper($row['new_program']) ?></td>
                                        <td><?= number_format($row['omset_projection']) ?></td>

                                        <!-- STATUS -->
                                        <td>
                                            <span class="badge <?= $badgeClass ?>" style="cursor:pointer" data-id="<?= $row['id_draft'] ?>"
                                            <?= $stat === 'Draft' ? '' : "data-bs-toggle='modal' data-bs-target='#approvalModal'" ?>>
                                            <?= $stat ?>
                                            </span>
                                        </td>

                                        <td><?= $row['created_at'] ?></td>
                                        <td><?= $row['updated_at'] ?></td>

                                        <!-- ACTION -->
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <i class="fas fa-ellipsis-v text-muted"
                                                    data-bs-toggle="dropdown"
                                                    style="cursor:pointer"></i>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <a href="myplan-updates.php?plan_id=<?= $row['id'] ?>"
                                                            class="dropdown-item">
                                                            <i class="fa fa-calendar me-2"></i>
                                                            <?= $isCreator ? 'Add Update Plan' : 'View Update Plan' ?>
                                                        </a>
                                                    </li>

                                                    <?php if ($isCreator) { ?>
                                                    <li>
                                                        <a href="myplan-form.php?plan_id=<?= $row['id'] ?>"
                                                            class="dropdown-item text-success">
                                                            <i class="fas fa-edit me-2"></i> Edit
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a href="#"
                                                            class="dropdown-item text-danger delete-btn"
                                                            data-id="<?= $row['id'] ?>">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } } ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>


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
                    Loading...
                </div>
                </div>
            </div>
        </div>

<?php include 'footer.php';?>

<script>
    var approvalModal = document.getElementById('approvalModal')
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

    $('.close').click(function() {
        $('#approvalModal').modal('hide');
    });

    $('.delete-btn').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You will delete this plan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: 'delete-plan.php',
                    type: 'POST',
                    data: {
                        id: id
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
                        Swal.close();
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
        $('#table_draft').DataTable({
            dom: 'Bfrtilp',
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            order: [[9, 'desc']],
            buttons: [
                { 
                    extend: 'copyHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                    }
                },
                { 
                    extend: 'excelHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                    }
                },
                { 
                    extend: 'csvHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                    }
                },
                { 
                    extend: 'pdfHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                    }
                }
            ],
            initComplete: function () {
                $('#table_draft_length label').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'font-size': '.7rem',
                    'font-weight': 'bold',
                    'margin-left': '20px',
                    'margin-top': '8px'
                });

                $('#table_draft_length select').css({
                    'font-size': '.7rem',
                    'font-weight': 'bold',
                    'border-radius': '5px',
                    'padding': '2px 6px',
                    'border': '1px solid #ccc'
                });
            }
        });
    })
</script>
       