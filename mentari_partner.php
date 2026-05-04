<?php 

    include 'header.php';
    $config = require 'config.php';
?>
<style>
    select {
        max-width: 400px;
        word-wrap: break-word;
    }

    textarea {
        width: 100%;
    }

    .benefit-desc {
      transition: width 0.5s ease;
      text-align: start !important;
    }

    .benefit-desc:hover {
      width: 40% !important;
    }
    
    .benefit-ket {
        display: none;
    }

    table.dataTable tbody td {
      padding : 5px !important;
      vertical-align: middle !important;
      /* text-align: center !important; */
      font-size: .65rem !important;
    }

    table.dataTable tbody td.benefit-desc{
      text-align: start !important;
    }

    table.dataTable thead th {
        font-size: .7rem !important;
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

?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            
            <div class="card rounded shadow-sm p-3">
                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-semibold mb-0">Mentari Partner</h6>
                        <small class="text-muted">Manage Mentari Partner</small>
                    </div>
                   <button type="button" class="btn btn-primary btn-sm fw-semibold" data-action="create" data-bs-toggle="modal" data-bs-target="#mpartnerModal" id="add_mpartner">
                        <i class="fa fa-plus me-1"></i> Add 
                    </button>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-sm" id="table_id" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr>
                                <th style="width:4%">No</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Institution Name</th>
                                <th>No PK</th>
                                <th>E-mail have been sent</th>
                                <th>MP Account Created</th>
                                <th>Created At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                            $sql_q = " WHERE ";
                            $id_user = $_SESSION['id_user'];

                            $sql = "SELECT 
                                        mps.id, 
                                        mps.name, 
                                        mps.email,
                                        mps.email_sent, mps.mp_acc_created,
                                        GROUP_CONCAT(DISTINCT pk.no_pk SEPARATOR ', ') as no_pk,
                                        c.generalname, 
                                        IFNULL(sc.name, db.school_name) as school_name2, 
                                        prog.is_pk,
                                        mps.created_at
                                    FROM mp_users as mps
                                    LEFT JOIN mp_user_pks as mpup ON mpup.user_id = mps.id
                                    LEFT JOIN pk ON pk.id = mpup.pk_id
                                    LEFT JOIN draft_benefit as db on db.id_draft = pk.benefit_id
                                    LEFT JOIN draft_approval a
                                        ON a.id_draft = db.id_draft AND a.id_user_approver = $id_user
                                    LEFT JOIN schools sc ON sc.id = db.school_name
                                    LEFT JOIN user c ON c.id_user = db.id_ec
                                    LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)";

                            if($_SESSION['role'] == 'ec'){
                                $sql .= " WHERE (a.id_user_approver = $id_user
                                        OR c.leadId = $id_user
                                        OR db.id_ec = $id_user)";
                                $sql_q = " AND ";
                            }

                            $sql .= "$sql_q db.status = 1
                                    AND db.verified = 1
                                    AND db.deleted_at IS NULL
                                    AND prog.is_active = 1
                                    GROUP BY mps.id
                                    ORDER BY mps.id DESC";

                            $result = mysqli_query($conn, $sql);
                            if (!$result) {
                                die("Query Error: " . mysqli_error($conn));
                            }
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['name'] ?></td>
                                <td class="fw-semibold"><?= $row['email'] ?></td>
                                <td style="width: 25%"><?= $row['school_name2'] ?></td>
                                <td><?= $row['no_pk'] ?: '<span class="text-muted">-</span>' ?></td>
                                <td class="text-center">
                                    <?= $row['email_sent']
                                    ? "<i class='fa fa-check-circle text-success'></i>"
                                    : "<i class='fa fa-minus-circle text-danger'></i>" ?>
                                </td>
                                <td>
                                    <?= $row['mp_acc_created']
                                    ? "<i class='fa fa-check-circle text-success'></i>"
                                    : "<i class='fa fa-minus-circle text-danger'></i>" ?>
                                </td>
                                <td><?= $row['created_at'] ?: '<span class="text-muted">-</span>' ?></td>

                                <!-- ACTION -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <i class="fas fa-ellipsis-v text-muted"
                                        data-bs-toggle="dropdown"
                                        style="cursor:pointer"></i>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.75rem">

                                            <li>
                                                <a class="dropdown-item text-success" data-id="<?= $row['id'] ?>" data-action="picAct" data-bs-toggle="modal" data-bs-target="#mpartnerModal">
                                                    <i class="fa fa-pen me-2"></i> Edit PIC
                                                </a>
                                            </li>

                                            <?php if(!$row['email_sent']) { ?>
                                                <li>
                                                    <a class="dropdown-item text-info" data-id="<?= $row['id'] ?>" data-action="emailAct" data-bs-toggle="modal" data-bs-target="#mpResendModal">
                                                        <i class="fa fa-envelope me-2"></i> Send E-mail
                                                    </a>
                                                </li>
                                            <?php } ?>

                                           <?php if(!$row['mp_acc_created']) { ?>
                                                <li>
                                                    <a class="dropdown-item text-warning" data-id="<?= $row['id'] ?>" data-action="mpAct" data-bs-toggle="modal" data-bs-target="#mpResendModal">
                                                        <i class="fa fa-user me-2"></i> Create MP Account
                                                    </a>
                                                </li>
                                            <?php } ?>

                                            <li>
                                                <a class="dropdown-item text-primary" href="<?= $config['mp_url'] ?>" target="_blank" title="Qty manfaat PK3 refill tiap Juli">
                                                    <i class="fa fa-link me-2"></i> MPP Portal
                                                </a>
                                            </li>

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

    <div class="modal fade" id="mpartnerModal" tabindex="-1" role="dialog" aria-labelledby="mpartnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mpartnerModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mpartnerModalBody">
                    Loading...
                </div>
               
            </div> 
        </div>
    </div>

    <div class="modal fade" id="mpResendModal" tabindex="-1" role="dialog" aria-labelledby="mpResendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mpResendModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mpResendModalBody">
                    Loading...
                </div>
               
            </div> 
        </div>
    </div>

<?php include 'footer.php';?>
<script>
    let selectedPk = [];
    var addmpartnerModal = document.getElementById('mpartnerModal');
    addmpartnerModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var rowid = button.getAttribute('data-id');
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = addmpartnerModal.querySelector('.modal-title')
        modalTitle.textContent = action == 'create' ?  "Add Mentari Partner" : "Edit Mentari Partner";
        
        $.ajax({
            url: 'input-mpartner.php',
            type: 'POST',
            data: {
                id: rowid,
            },
            success: function(data) {
                $('#mpartnerModalBody').html(data);
            }
        });
    })

    var mpResendModal = document.getElementById('mpResendModal');
    mpResendModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var rowid = button.getAttribute('data-id');
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = mpResendModal.querySelector('.modal-title')
        modalTitle.textContent = action == 'emailAct' ?  "Send E-mail" : "Create MP Account";
        
        $.ajax({
            url: 'mp-act.php',
            type: 'POST',
            data: {
                id: rowid,
                action: action
            },
            success: function(data) {
                $('#mpResendModalBody').html(data);
            }
        });
    })

    $(document).ready(function() {
    
    })

    $(document).on('click', '.close', function() {
        $('#mpartnerModal').modal('hide');
    });
</script>