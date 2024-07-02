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
        LEFT JOIN user c on c.id_user = b.id_ec 
        LEFT JOIN user d on d.id_user = a.id_user_approver 
        where a.id_draft = $id_draft";
$result = $conn->query($sql);

if ($result->num_rows > 0) { 
?>
<style>
  .timeline-with-icons {
    border-left: 1px solid hsl(0, 0%, 90%);
    position: relative;
    list-style: none;
  }

  .timeline-with-icons .timeline-item {
    position: relative;
  }

  .timeline-with-icons .timeline-item:after {
    position: absolute;
    display: block;
    top: 0;
  }

  .timeline-with-icons .timeline-icon {
    position: absolute;
    left: -48px;
    background-color: hsl(217, 88.2%, 90%);
    color: hsl(217, 88.8%, 35.1%);
    border-radius: 50%;
    height: 31px;
    width: 31px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  
</style>
    <div class="py-2 px-4">
      <ul class="timeline-with-icons" style="font-size: .85rem;">
        <?php 
          while ($row = $result->fetch_assoc()) { ?>
            <li class="timeline-item mb-2">
              <span class="timeline-icon">
                <i class="fas fa-fingerprint text-primary fa-sm fa-fw"></i>
              </span>


              <h6 class="fw-bold mb-0 p-0"><?= $row['leadername'] ?></h6>
              <span style="font-size: .7rem;" class="px-2 text-white rounded bg-<?= $row['status'] == 0 ? 'warning' : ($row['status'] == 1 ? 'success' : 'danger') ?>">
                  <?= $row['status'] == 0 ? 'Waiting Approval' : ($row['status'] == 1 ? 'Approved' : 'Rejected') ?>
              </span>
              <p class="text-muted mb-2" style="margin: 0px; padding: 0px;"><?= $row['approved_at'] ?? '-' ?></p>
              <p class="text-muted fw-bold"><?= $row['notes'] ?? '-' ?></p>
            </li>
        <?php } ?>
      </ul> 
    </div>
 
  
<?php } $conn->close();?>


    
    
    