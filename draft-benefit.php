
<?php 
    include 'header.php'; 

    $role    = $_SESSION['role'];
    $isAdmin = $_SESSION['role'] === 'admin';
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
                                <h5 class="fw-bold mb-0">Draft Benefit</h5>
                                <small class="text-muted">Manage draft status, approval, and documents</small>
                            </div>

                            <div class="d-flex gap-2 align-items-center">
                                <a href="update-benefit-ec-input-term.php" class="btn btn-outline-secondary btn-sm fw-semibold">
                                    <i class="fas fa-file me-2"></i> Update Program
                                </a>
                                <a href="new-benefit-ec-input.php" class="btn btn-primary btn-sm fw-semibold">
                                    <i class="fas fa-plus me-2"></i> Create Draft
                                </a>
                            </div>
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
                                        <th>Program</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                <?php
                                    $id_user = $_SESSION['id_user'];

                                    $order_by = ' ORDER BY a.date ASC';
                                    $sql = "SELECT a.*, b.*, IFNULL(sc.name, a.school_name) as school_name2, 
                                                a.verified, a.deleted_at, a.year, prog.name as program_name, seg.segment as segment_name
                                            FROM draft_benefit a
                                            LEFT JOIN schools as sc on sc.id = a.school_name
                                            LEFT JOIN user b on a.id_ec = b.id_user
                                            LEFT JOIN segments as seg on seg.id = a.segment
                                            LEFT JOIN programs AS prog ON (prog.name = a.program OR prog.code = a.program)
                                            WHERE a.deleted_at IS NULL
                                            AND prog.is_active = 1 AND prog.is_pk = 0"; 

                                    if($_SESSION['role'] == 'ec'){
                                        $sql.=" AND (a.id_ec = $id_user 
                                                OR b.leadId = $id_user 
                                                OR b.leadId2 = $id_user 
                                                OR b.leadId3 = $id_user)";
                                    }

                                    $sql .= " AND NOT (status IN (2) AND a.program IN ('cbls1', 'cbls3'))";
                                    $sql .= $order_by;

                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {

                                        $stat = ($row['status'] == 0 && $row['fileUrl']) ? 'Waiting Approval'
                                            : ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                        $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft' : $stat;

                                        $status_class = $row['status'] == 0 ? 'warning'
                                                    : ($row['status'] == 1 ? 'success' : 'danger');
                                        $status_class = ($row['status'] == 0 && !$row['fileUrl']) ? 'primary' : $status_class;

                                        $stat = ($row['verified'] == 1 && $row['status'] == 1 && $row['confirmed'] == 0)
                                            ? 'Waiting Confirmation'
                                            : ($row['verified'] == 0 && $row['status'] == 1
                                                ? 'Waiting Verification'
                                                : $stat);

                                        $is_ec_the_creator = $_SESSION['id_user'] == $row['id_ec'] || $role == 'admin';
                                        $programe_name = $row['year'] == 1 ? $row['program_name'] : ($row['program_name'] . " Perubahan Tahun Ke " . $row['year']);

                                        $isDraft     = $row['status'] == 0 && !$row['fileUrl'];
                                        $isRejected  = $row['status'] == 2;
                                        $isActive    = empty($row['deleted_at']);

                                ?>
                                <tr>
                                    <td><?= $row['id_draft'] ?></td>
                                    <td class="fw-semibold"><?= $row['generalname'] ?></td>
                                    <td><?= $row['school_name2'] ?></td>
                                    <td><?= ucfirst($row['segment_name'] ? $row['segment_name'] : $row['segment']) ?></td>
                                    <td><?= strtoupper($programe_name) ?></td>
                                    <td><?= $row['date'] ?></td>
                                    <td><?= $row['updated_at'] ?></td>

                                    <td>
                                    <span
                                        class="badge bg-<?= $status_class ?>"
                                        style="cursor:pointer"
                                        data-id="<?= $row['id_draft'] ?>"
                                        <?= $stat == 'Draft' ? '' : "data-bs-toggle='modal'" ?>
                                        data-bs-target="#approvalModal">
                                        <?= $stat ?>
                                    </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <i class="fas fa-ellipsis-v text-muted"
                                            data-bs-toggle="dropdown"
                                            style="cursor:pointer"></i>

                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                                <?php if($row['fileUrl']) { ?>
                                                    <li>
                                                        <a class="dropdown-item"
                                                        href="draft-benefit/<?= $row['fileUrl'] ?>.xlsx">
                                                        <i class="fa fa-paperclip me-2"></i> View File
                                                        </a>
                                                    </li>
                                                <?php } ?>

                                                <?php 
                                                    if (
                                                        $isActive &&
                                                        (
                                                            ($isDraft && ($is_ec_the_creator || $isAdmin)) ||
                                                            ($isRejected && ($is_ec_the_creator || $isAdmin))
                                                        )
                                                    ) {
                                                ?>
                                                    <li>
                                                        <a class="dropdown-item text-success"
                                                        href="new-benefit-ec-input2.php?edit=edit&id_draft=<?= $row['id_draft'] ?>">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                <?php } ?>

                                                <?php if(($is_ec_the_creator && $row['status'] == 0 && !$row['fileUrl'])){ ?>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-btn"
                                                        data-id="<?= $row['id_draft'] ?>" href="#">
                                                        <i class="fas fa-trash me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                <?php } ?>

                                                <?php if($isDraft && !$is_ec_the_creator){ ?>
                                                    <li>
                                                        <a class="dropdown-item text-secondary" href="#">
                                                        <i class="fas fa-ban me-2"></i> No Action
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
        // Cari modal yang sedang terbuka dan tutup modal tersebut
        $('#approvalModal').modal('hide');
    });

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
            order: [[0, 'desc']],
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
       