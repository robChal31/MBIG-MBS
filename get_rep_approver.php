<?php
ob_start();
session_start();
include 'db_con.php';

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];                                                                      
$sql = "SELECT 
            bir_a.id, bir.id_draft, bir_a.status, bir_a.token, bir_a.approved_at, b.status as draft_status, b.verified, b.program,
            bir_a.notes, c.generalname as ec_name, d.generalname as leadername, bir_a.id_user_approver as approver, b.confirmed
        FROM `bir_approval` AS bir_a 
        LEFT JOIN benefit_imp_report AS bir on bir.id = bir_a.bir_id
        INNER JOIN draft_benefit AS b on bir.id_draft = b.id_draft 
        LEFT JOIN user AS c on c.id_user = b.id_ec 
        LEFT JOIN user AS d on d.id_user = bir_a.id_user_approver 
        where bir_a.bir_id = $id";
$result = $conn->query($sql);

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
            ?>
                <tr>
                  <td><?= $row['leadername'] ?></td>
                  <td><?= $row['approved_at'] ?? '-' ?></td>
                  <td>
                    <span class="py-1 px-2 text-white rounded bg-<?= $row['status'] == 0 ? 'warning' : ($row['status'] == 1 ? 'success' : 'danger') ?>">
                      <?= $status_msg?>
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


    
    
    