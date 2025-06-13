<?php

session_start();
include 'db_con.php';

$role = $_SESSION['role'];
$myplan_id          = $_POST['myplan_id'];
$myplan_update_id   = ISSET($_POST['myplan_update_id']) ? $_POST['myplan_update_id'] : '';
$role               = $_SESSION['role'];

$sql        = "SELECT mp.*, user.*, sc.name as school_name
                  FROM myplan AS mp
              LEFT JOIN schools AS sc ON sc.id = mp.school_id
              LEFT JOIN user ON mp.user_id = user.id_user
              LEFT JOIN programs AS prog ON prog.name = mp.program
              WHERE mp.deleted_at IS NULL AND mp.id = $myplan_id";
$result = $conn->query($sql);

$update = '';
$feedback = '';
$added_at = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ec_name            = $row['generalname'];
        $ec_mail            = $row['username'];
        $school             = $row['school_name'];
        $segment            = $row['segment'];
        $program            = $row['program'];
        $level              = $row['level'];
        $wilayah            = $row['wilayah'];
        $student_projection = $row['student_projection'];
        $omset_projection   = $row['omset_projection'];
        $created_at         = $row['created_at'];
        $updated_at         = $row['updated_at'];
        $user_id            = $row['user_id'];
    }

    if ($myplan_update_id) {
      $mp_update_query = "SELECT * FROM myplan_update WHERE id = $myplan_update_id";
      $mp_update_exec_query = $conn->query($mp_update_query);
  
      if ($mp_update_exec_query) {
          $mp_update_data = $mp_update_exec_query->fetch_assoc();
          $update = $mp_update_data['update_note'];
          $feedback = $mp_update_data['feedback'];
          $added_at = date('Y-m-d', strtotime($mp_update_data['added_at']));
      } else {
          throw new Exception("Query error: " . $conn->error);
      }
  }
  
?>
    <div class="p-2">
        <h6>Detail Benefit</h6>
        <table class="table table-striped">
          <tr>
              <td style="width: 20%"><strong>EC</strong></td>
              <td style="width: 1%">:</td>
              <td><?= $ec_name ?></td>
          </tr>
          <tr>
              <td><strong>Sekolah</strong></td>
              <td>:</td>
              <td><?= $school ?></td>
          </tr>
          <tr>
              <td><strong>Segment</strong></td>
              <td>:</td>
              <td><?= strtoupper($segment) ?></td>
          </tr>
          <tr>
              <td><strong>Program</strong></td>
              <td>:</td>
              <td><?= strtoupper($program) ?></td>
          </tr>
          <!-- <tr>
              <td><strong>Level</strong></td>
              <td>:</td>
              <td><?= strtoupper($program) ?></td>
          </tr>
          <tr>
              <td><strong>Wilayah</strong></td>
              <td>:</td>
              <td><?= strtoupper($wilayah) ?></td>
          </tr> -->
          <tr>
              <td><strong>Student Projection</strong></td>
              <td>:</td>
              <td><?= number_format($student_projection, 0, ',', '.') ?></td>
          </tr>
          <tr>
              <td><strong>Omset Projection</strong></td>
              <td>:</td>
              <td><?= number_format($omset_projection, 0, ',', '.') ?></td>
          </tr>
        </table>

        <h6 class="mt-4 mb-2">EC Plan</h6>

        <form action="save-pk.php" method="POST" enctype="multipart/form-data" id="form-id">
          <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">EC Update Plan</label>
                <textarea name="update_note" class="form-control" style="height: 150px;" readonly><?= $update ?></textarea>
            </div>

            <div class="col-md-7 col-12 mb-3">
                <label class="form-label">Added At</label>
                <input type="date" name="added_at" class="form-control form-control-sm" value="<?= $added_at ?>" readonly>
            </div>
            <input type="hidden" name="myplan_id" value="<?= $myplan_id ?>">
            <input type="hidden" name="ec_email" value="<?= $ec_mail ?>">
            <input type="hidden" name="ec_name" value="<?= $ec_name ?>">
            <?php
                if($myplan_update_id) : ?>
                  <input type="hidden" name="myplan_update_id" value="<?= $myplan_update_id ?>">
            <?php endif; ?>
            <div class="col-12 mb-3">
                <label class="form-label">Your Feedback</label>
                <textarea name="feedback" class="form-control" style="height: 150px;" <?= $role != 'Admin' ? 'readonly' : '' ?>><?= $feedback ?></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <?php
                    if($role == 'admin' || $role == 'ec') { ?>
                        <button class="btn btn-primary btn-sm" id="submit_btn">Save</button>
                <?php } ?>
          </div>
        </form>
    </div>

<script>
    $(document).ready(function() {
        let role = '<?= $role ?>';

        $('#form-id').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './myplan-update-save.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_btn').prop('disabled', true);
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Please wait while we save your data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                },
                success: function(response) {
                    Swal.close();
                    if(response.status) {
                        Swal.fire({
                            title: "Saved!",
                            text: response.message,
                            icon: "success"
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }else {
                        Swal.fire({
                            title: "Failed!",
                            text: response.message,
                            icon: "error"
                        });
                    }
                    $('#submit_btn').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_btn').prop('disabled', false);
                }
            });
        });

        $('.close').click(function() {
          $('#createModal').modal('hide');
      });
    })
</script>
 
<?php } $conn->close();?>