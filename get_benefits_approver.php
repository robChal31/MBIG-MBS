<?php
ob_start();
session_start();
include 'db_con.php';

if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id_draft = $_POST['id_draft'];                                                                      
$sql = "SELECT 
            a.id_draft_approval, a.id_draft, a.status, a.token, a.approved_at,
            a.notes, c.generalname as ec_name, d.generalname as leadername 
        FROM `draft_approval` a 
        INNER JOIN draft_benefit b on a.id_draft = b.id_draft 
        LEFT JOIN user c on c.id_user = b.id_user 
        LEFT JOIN user d on d.id_user = a.id_user_approver 
        where a.id_draft = $id_draft";
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
              while ($row = $result->fetch_assoc()) { ?>
                <tr>
                  <td><?= $row['leadername'] ?></td>
                  <td><?= $row['approved_at'] ?? '-' ?></td>
                  <td>
                    <span class="py-1 px-2 text-white rounded bg-<?= $row['status'] == 0 ? 'warning' : ($row['status'] == 1 ? 'success' : 'danger') ?>">
                      <?= $row['status'] == 0 ? 'Waiting Approval' : ($row['status'] == 1 ? 'Approved' : 'Rejected') ?>
                    </span>
                  </td>
                  <td><?= $row['notes'] ?? '-' ?></td>
                </tr>
            <?php } ?>
        </tbody>
      </table>
    </div>
 
  
<?php } $conn->close();?>


    
    
    