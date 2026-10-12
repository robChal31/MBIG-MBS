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

    #uploadMpartnerModal .table td,
    #uploadMpartnerModal .table th {
        font-size: .7rem;
        vertical-align: middle;
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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#uploadMpartnerModal">
                            <i class="fa fa-upload me-1"></i> Bulk Upload
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" 
                                data-action="create" data-bs-toggle="modal" data-bs-target="#mpartnerModal" id="add_mpartner">
                            <i class="fa fa-plus me-1"></i> Add 
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-sm" id="table_id" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr>
                                <th style="width:4%">No</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Phone Number</th>
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
                                        mps.phone_number,
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
                                <td class="fw-semibold"><?= $row['phone_number'] ?></td>
                                <td><?= $row['school_name2'] ?></td>
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
                                        <i class="fas fa-ellipsis-v text-muted" data-bs-toggle="dropdown" style="cursor:pointer"></i>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.75rem">

                                            <li>
                                                <a class="dropdown-item text-success" data-id="<?= $row['id'] ?>" data-action="picAct" data-bs-toggle="modal" data-bs-target="#mpartnerModal">
                                                    <i class="fa fa-pen me-2"></i> Edit PIC
                                                </a>
                                            </li>

                                            <?php if(!$row['email_sent'] || !$row['mp_acc_created']) { ?>
                                                <li>
                                                    <a class="dropdown-item text-info" data-id="<?= $row['id'] ?>" data-action="sendAct" data-bs-toggle="modal" data-bs-target="#mpResendModal">
                                                        <i class="fa fa-envelope me-2"></i> 
                                                        <?php 
                                                        if(!$row['email_sent'] && !$row['mp_acc_created']) {
                                                            echo 'Send Email & Create Account';
                                                        } elseif(!$row['email_sent']) {
                                                            echo 'Resend Welcome Email';
                                                        } else {
                                                            echo 'Create MP Account & Send Email';
                                                        }
                                                        ?>
                                                    </a>
                                                </li>
                                            <?php } ?>

                                            <li>
                                                <a class="dropdown-item text-primary" href="<?= $config['mp_url'] ?>" target="_blank">
                                                    <i class="fa fa-link me-2"></i> MP Portal
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

    <!-- ===== MODAL: Add/Edit MP ===== -->
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

    <!-- ===== MODAL: Resend / Create Account ===== -->
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

    <!-- ===== MODAL: Bulk Upload ===== -->
    <div class="modal fade" id="uploadMpartnerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Upload Mentari Partner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- STEP 1: Upload -->
                    <div id="uploadStep1">
                        <div class="alert alert-info small mb-3">
                            <strong>Format file:</strong> 
                            <code>Name | Email | Phone Number | School ID | School Name | PK ID | No PK</code>
                            <br>Validasi pakai <b>School ID</b> &amp; <b>PK ID</b>. Multiple PK ID pisahkan dengan koma 
                            (contoh: <code>101, 102</code>).
                            <br><span class="text-muted">School Name &amp; No PK hanya sebagai info referensi.</span>
                        </div>

                        <div class="mb-3">
                            <a href="download-mpartner-template.php" class="btn btn-sm btn-outline-success">
                                <i class="fa fa-download me-1"></i> Download Template
                            </a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Pilih File (.xls / .xlsx / .csv)</label>
                            <input type="file" id="xlsFile" class="form-control form-control-sm" 
                                accept=".xls,.xlsx,.csv">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary btn-sm" id="btnPreview">
                                <i class="fa fa-search me-1"></i> Preview Data
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Preview -->
                    <div id="uploadStep2" style="display:none;">
                        <div class="mb-2">
                            <span class="badge bg-success">Valid: <span id="validCount">0</span></span>
                            <span class="badge bg-danger">Invalid: <span id="invalidCount">0</span></span>
                        </div>

                        <div style="max-height: 400px; overflow:auto;">
                            <table class="table table-sm table-bordered" style="font-size:.75rem">
                                <thead class="table-light">
                                    <tr>
                                        <th>Row</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>School ID</th>
                                        <th>School Name</th>
                                        <th>PK ID</th>
                                        <th>No PK</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-secondary btn-sm" id="btnBackUpload">
                                <i class="fa fa-arrow-left me-1"></i> Back
                            </button>
                            <button class="btn btn-primary btn-sm" id="btnSubmitUpload">
                                <i class="fa fa-paper-plane me-1"></i> Submit Valid Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>

<script>
    let selectedPk = [];

    // ===== Add/Edit MP modal =====
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
            data: { id: rowid },
            success: function(data) {
                $('#mpartnerModalBody').html(data);
            }
        });
    })

    // ===== Resend modal =====
    var mpResendModal = document.getElementById('mpResendModal');
    mpResendModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var rowid = button.getAttribute('data-id');
        let action = event.relatedTarget.getAttribute('data-action');
        
        $.ajax({
            url: 'mp-act.php',
            type: 'POST',
            data: { id: rowid, action: action },
            success: function(data) {
                $('#mpResendModalBody').html(data);
            }
        });
    })

    // ===== Escape helper =====
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, m => 
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
    }

    // ===== Bulk upload state =====
    let previewData = [];

    $(document).on('click', '#btnPreview', function() {
        var fileInput = document.getElementById('xlsFile');
        if (!fileInput.files.length) {
            Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
            return;
        }
        var formData = new FormData();
        formData.append('file', fileInput.files[0]);

        $.ajax({
            url: 'preview-upload-mpartner.php',
            type: 'POST', 
            data: formData, 
            contentType: false, 
            processData: false,
            dataType: 'json',
            beforeSend: () => Swal.fire({ title:'Processing...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() }),
            success: function(res) {
                Swal.close();
                if (res.status !== 'success') return Swal.fire('Error', res.message, 'error');
                renderPreview(res.data);
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire('Error', 'Gagal baca file: ' + (xhr.responseText || xhr.statusText), 'error');
            }
        });
    });

    function renderPreview(data) {
        previewData = data;
        const tbody = $('#previewTableBody').empty();
        let valid = 0, invalid = 0;

        data.forEach(r => {
            r.valid ? valid++ : invalid++;
            const statusHtml = r.valid
                ? '<span class="badge bg-success">Valid</span>'
                : '<span class="badge bg-danger">Invalid</span><br><small class="text-danger">' 
                  + r.errors.map(escapeHtml).join('<br>') + '</small>';

            tbody.append(`<tr class="${r.valid ? '' : 'table-danger'}">
                <td>${r.row}</td>
                <td>${escapeHtml(r.name)}</td>
                <td>${escapeHtml(r.email)}</td>
                <td>${escapeHtml(r.phone)}</td>
                <td><code>${escapeHtml(r.school_id)}</code></td>
                <td>${escapeHtml(r.school_name)}</td>
                <td><code>${escapeHtml(r.pk_id)}</code></td>
                <td>${escapeHtml(r.no_pk)}</td>
                <td>${statusHtml}</td>
            </tr>`);
        });

        $('#validCount').text(valid);
        $('#invalidCount').text(invalid);
        $('#btnSubmitUpload').prop('disabled', valid === 0);
        $('#uploadStep1').hide();
        $('#uploadStep2').show();
    }

    $(document).on('click', '#btnBackUpload', function() {
        $('#uploadStep2').hide();
        $('#uploadStep1').show();
    });

    $(document).on('click', '#btnSubmitUpload', function() {
        const validRows = previewData.filter(r => r.valid);
        if (!validRows.length) return;

        Swal.fire({
            title: 'Konfirmasi',
            text: `Proses ${validRows.length} data valid?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Submit'
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: 'save-bulk-mpartner.php',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ rows: validRows }),
                beforeSend: () => Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() }),
                success: function(res) {
                    Swal.close();
                    if (res.status !== 'success') return Swal.fire('Error', res.message, 'error');

                    let html = `<b>Berhasil:</b> ${res.summary.success}<br><b>Gagal:</b> ${res.summary.failed}<br><br>`;
                    res.results.filter(r => r.status !== 'success').forEach(r => {
                        html += `<div class="text-danger small">${escapeHtml(r.email)}: ${escapeHtml(r.message || '')}</div>`;
                    });

                    Swal.fire({
                        title: 'Selesai', 
                        html, 
                        icon: res.summary.failed ? 'warning' : 'success'
                    }).then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('Error', xhr.responseText || 'Unknown error', 'error');
                }
            });
        });
    });

    // ===== Close handler =====
    $(document).on('click', '.close', function() {
        $('#mpartnerModal').modal('hide');
        $('#mpResendModal').modal('hide');
        $('#uploadMpartnerModal').modal('hide');
    });
</script>