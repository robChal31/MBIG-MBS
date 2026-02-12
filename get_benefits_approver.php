<?php
ob_start();
session_start();
include 'db_con.php';

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id_draft = (int)$_POST['id_draft'];
$role = $_SESSION['role'];                                                                    

$sql = "SELECT 
            a.id_draft_approval, 
            a.id_draft, 
            a.status, 
            a.token, 
            a.approved_at, 
            b.status as draft_status, 
            b.verified, 
            b.program,
            a.notes, 
            c.generalname as ec_name, 
            d.generalname as leadername, 
            a.id_user_approver as approver, 
            b.confirmed, 
            d.role
        FROM draft_approval a 
        INNER JOIN draft_benefit b ON a.id_draft = b.id_draft 
        LEFT JOIN user c ON c.id_user = b.id_ec 
        LEFT JOIN user d ON d.id_user = a.id_user_approver 
        WHERE a.id_draft = $id_draft
        ORDER BY a.id_draft_approval ASC";

$result = $conn->query($sql);

/* ================= PROGRAM CHECK ================= */
$programs = [];
$query_program = "SELECT * FROM programs WHERE is_active = 1 AND is_pk = 1";
$exec_program = mysqli_query($conn, $query_program);

if (mysqli_num_rows($exec_program) > 0) {
    $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);
    $program_names = array_map(function($item) {
        return strtoupper($item['name']);
    }, $programs);
} else {
    $program_names = [];
}

$sec_has_approved = false;
?>

<div class="container-fluid p-3">

  <?php if ($result->num_rows > 0) { ?>

  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
      <i class="fas fa-history me-2"></i>Approval History
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Approver</th>
              <th width="180">Created At</th>
              <th width="220">Status</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>

          <?php while ($row = $result->fetch_assoc()) { 

            /* ===== STATUS LOGIC ===== */
            $status_class = $row['status'] == 0 ? 'warning' : 
                          ($row['status'] == 1 ? 'success' : 'danger');

            $status_msg = $row['status'] == 0 ? 'Waiting Approval' : 
                        ($row['status'] == 1 ? 'Approved' : 'Rejected');

            if($row['approver'] == 70) {
              $status_msg = $row['status'] == 0 
                ? ($sec_has_approved ? 'Waiting Verification' : 'Waiting Approval') 
                : ($row['status'] == 1 ? ($sec_has_approved ? 'Verified' : 'Approved') : $status_msg);
              $sec_has_approved = true;
            }

            if($row['approver'] == 5) {
              $status_msg = $row['confirmed'] == 1 && $row['status'] == 1 
                ? 'Confirmed' 
                : ($row['confirmed'] == 0 ? 'Waiting Confirmation' : $status_msg);
            }

            $is_pk = in_array(strtoupper($row['program']), $program_names);
            $is_pk_label = ($row['approver'] == 16 && $is_pk) 
                ? "<div class='small text-muted mt-1'>(Pengajuan sudah di SA)</div>" 
                : '';

            $approved_at = $row['approved_at'] 
                ? date('d M Y H:i', strtotime($row['approved_at'])) 
                : '-';
          ?>

            <tr>
              <td class="fw-semibold"><?= $row['leadername'] ?></td>

              <td class="text-muted">
                <?= $approved_at ?>
              </td>

              <td>
                <span class="badge bg-<?= $status_class ?> px-3 py-2">
                  <?= $status_msg ?>
                </span>
                <?= $is_pk_label ?>
              </td>

              <td>
                <div style="max-width: 450px; white-space: pre-wrap;">
                  <?= $row['notes'] ?? '-' ?>
                </div>
              </td>
            </tr>

          <?php } ?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<?php } else { ?>

  <div class="alert alert-info shadow-sm">
    <i class="fas fa-info-circle me-2"></i>
    Approval History Is Empty
  </div>

<?php } ?>


<?php
/* ================= REJECT HISTORY ================= */

$sql_reject = "SELECT 
                  arh.id, 
                  approver.generalname as approver, 
                  arh.created_at, 
                  arh.note
              FROM approval_reject_history AS arh 
              LEFT JOIN user AS approver ON approver.id_user = arh.id_user_approver 
              WHERE arh.id_draft = $id_draft 
              ORDER BY arh.created_at ASC, arh.id ASC";

$result_reject = $conn->query($sql_reject);

if ($result_reject->num_rows > 0) { ?>

<div class="card shadow-sm border-0">
  <div class="card-header bg-danger text-white">
    <i class="fas fa-times-circle me-2"></i>Reject History
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Approver</th>
            <th width="180">Created At</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>

          <?php while ($row = $result_reject->fetch_assoc()) { 

            $created_at = $row['created_at'] 
                ? date('d M Y H:i', strtotime($row['created_at'])) 
                : '-';
          ?>

          <tr>
            <td class="fw-semibold"><?= $row['approver'] ?></td>
            <td class="text-muted"><?= $created_at ?></td>
            <td>
              <div style="white-space: pre-wrap;">
                <?= $row['note'] ?? '-' ?>
              </div>
            </td>
          </tr>

          <?php } ?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<?php } ?>

</div>

<?php $conn->close(); ?>
