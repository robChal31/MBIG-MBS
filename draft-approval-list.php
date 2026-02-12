<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>
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
                            <h5 class="fw-bold mb-0">Draft Benefit Approval</h5>
                            <small class="text-muted">
                                Review, approve, or track draft benefit submissions
                            </small>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive">
                        <table class="table align-middle" id="table_id">
                            <thead class="table-light">
                                <tr>
                                    <th>No Draft</th>
                                    <th>Nama EC</th>
                                    <th>Nama Sekolah</th>
                                    <th>Segment</th>
                                    <th>Tanggal</th>
                                    <th>Program</th>
                                    <th>Approver</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width:10%">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                                $sql_q      = " WHERE ";
                                $id_user    = $_SESSION['id_user'];

                                $sql = "SELECT 
                                            a.id_draft_approval, a.id_draft, a.status, a.token,
                                            b.status as draft_status, b.verified, b.year, b.date,
                                            b.id_user, b.id_ec, b.school_name, b.segment, b.program,
                                            IFNULL(sc.name, b.school_name) as school_name2,
                                            c.generalname, d.generalname as leadername,
                                            d.id_user as id_user_approver, b.deleted_at,
                                            IFNULL(seg.segment, b.segment) as segment_name
                                        FROM draft_approval a
                                        INNER JOIN draft_benefit b ON a.id_draft = b.id_draft
                                        LEFT JOIN schools sc ON sc.id = b.school_name
                                        LEFT JOIN segments seg ON seg.id = b.segment
                                        LEFT JOIN user c ON c.id_user = b.id_ec
                                        LEFT JOIN user d ON d.id_user = a.id_user_approver
                                        LEFT JOIN (
                                            SELECT id_draft, MAX(date) AS max_date
                                            FROM draft_approval
                                            GROUP BY id_draft
                                        ) latest_approval
                                        ON a.id_draft = latest_approval.id_draft";

                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE a.id_user_approver = $id_user ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q
                                        b.deleted_at IS NULL
                                        AND (a.date = latest_approval.max_date OR latest_approval.max_date IS NULL)
                                        AND b.status <> 1
                                        ORDER BY a.id_draft";

                                $result = mysqli_query($conn, $sql);

                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {

                                        $program_name = $row['year'] == 1
                                            ? $row['program']
                                            : $row['program'] . " Perubahan Tahun Ke " . $row['year'];

                                        $status_text = $row['draft_status'] == 0 ? 'Waiting Approval'
                                            : ($row['draft_status'] == 1 ? 'Approved' : 'Rejected');

                                        $status_text = ($row['verified'] == 1 && $status_text == 'Approved')
                                            ? 'Verified'
                                            : ($row['verified'] == 0 && $status_text == 'Approved'
                                                ? 'Waiting Verification'
                                                : $status_text);

                                        $status_class = $row['draft_status'] == 0 ? 'warning'
                                            : ($row['draft_status'] == 1 ? 'success' : 'danger');
                            ?>
                                <tr>
                                    <td><?= $row['id_draft'] ?></td>
                                    <td class="fw-semibold"><?= $row['generalname'] ?></td>
                                    <td><?= $row['school_name2'] ?></td>
                                    <td><?= ucfirst($row['segment_name']) ?></td>
                                    <td><?= $row['date'] ?></td>
                                    <td><?= strtoupper($program_name) ?></td>
                                    <td><?= $row['leadername'] ?></td>

                                    <!-- STATUS -->
                                    <td>

                                        <span
                                            class="badge bg-<?= $status_class ?>"
                                            style="font-size:.65rem; cursor:pointer"
                                            data-id="<?= $row['id_draft'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approvalModal">
                                            <?= $status_text ?>
                                        </span>
                                    </td>

                                    <!-- ACTION -->
                                    <td class="text-center">
                                        <?php if($row['status'] < 1 && $row['id_user_approver'] == $id_user) { ?>
                                            <a target="_blank" href="approve-draft-benefit-form.php?id_draft=<?= $row['id_draft'] ?>&token=<?= $row['token'] ?>" class="btn btn-outline-primary btn-sm" style="font-size: .7rem;">
                                                <i class="fas fa-fingerprint"></i> Approve
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-muted">—</span>
                                        <?php } ?>
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
</script>