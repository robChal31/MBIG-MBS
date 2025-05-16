<?php
ob_start();
session_start();
include 'db_con.php';

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id_draft = $_POST['id_draft'];                                                                      
$sql = "SELECT 
            a.id_draft_approval, a.id_draft, a.status, a.token, a.approved_at, b.status as draft_status, b.verified, b.program,
            a.notes, c.generalname as ec_name, d.generalname as leadername, a.id_user_approver as approver, b.confirmed
        FROM `draft_approval` a 
        INNER JOIN draft_benefit b on a.id_draft = b.id_draft 
        LEFT JOIN user c on c.id_user = b.id_ec 
        LEFT JOIN user d on d.id_user = a.id_user_approver 
        where a.id_draft = $id_draft";
$result = $conn->query($sql);

$programs = [];
$query_program = "SELECT * FROM programs WHERE is_active = 1 AND is_pk = 1";
$program_names = false;
$exec_program = mysqli_query($conn, $query_program);
if (mysqli_num_rows($exec_program) > 0) {
    $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);
    $program_names = array_map(function($item) {
        return strtoupper($item['name']);
    }, $programs);
}

if ($result->num_rows > 0) { 
?>
    <div class="p-2">
      <table class="table table-striped">
        <thead>
          <th>Approver</th>
          <th>Created at</th>
          <th>Status</th>
          <th style="width: 40%">Note</th>
        </thead>
        <tbody>
            <?php 
              while ($row = $result->fetch_assoc()) { 
                $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                $status_msg = $row['status'] == 0 ? 'Waiting Approval' : ($row['status'] == 1 ? 'Approved' : 'Rejected');
                if($row['approver'] == 70 || $row['approver'] == 15) {
                  $status_msg = $row['draft_status'] == 1 ? ($row['verified'] == 1 && $row['status'] == 1 ? 'Verified' : ($row['verified'] == 0 ? 'Waiting Verification' : $status_msg)) : $status_msg;    
                }

                if($row['approver'] == 5) {
                  $status_msg = $row['confirmed'] == 1 && $row['status'] == 1 ? 'Confirmed' : ($row['confirmed'] == 0 ? 'Waiting Confirmation' : $status_msg);    
                }

                $is_pk = in_array($row['program'], $program_names);
                $is_pk = $row['approver'] == 16 && $is_pk ? "<span class='text-white' style='font-size: .65rem; font-weight: 300'> (Pengajuan sudah di SA)</span>" : '';
            ?>
                <tr>
                  <td><?= $row['leadername'] ?></td>
                  <td><?= $row['approved_at'] ?? '-' ?></td>
                  <td>
                    <span class="py-1 px-2 text-white rounded bg-<?= $row['status'] == 0 ? 'warning' : ($row['status'] == 1 ? 'success' : 'danger') ?>">
                      <?= $status_msg . $is_pk?>
                    </span>
                  </td>
                  <td><?= $row['notes'] ?? '-' ?></td>
                </tr>
            <?php } ?>
        </tbody>
      </table>
    </div>  
<?php } else { ?>
  <div class="alert alert-info">Approval History Is Empty</div>
<?php } ?>

<?php $conn->close();?>


    
    
    