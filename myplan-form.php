<style>
  table.dataTable tbody td {
      padding : 2px !important;
      vertical-align: middle !important;
      text-align: center !important;
      font-size: .9rem !important;
  }

  table.dataTable thead th {
      font-size: .9rem !important;
  }
</style>

<?php 
  include 'header.php'; 

  $id_plan  = ISSET($_GET['plan_id']) ? $_GET['plan_id'] : NULL;
  $username = $_SESSION['username'];
  $id_user  = $_SESSION['id_user'];
  $levels = ['tk', 'sd', 'smp', 'sma', 'yayasan', 'other'];

  $segment = NULL;
  $school_id = NULL;
  $program = NULL;
  $wilayah = NULL;
  $level = NULL;
  $start_timeline = NULL;
  $end_timeline = NULL;
  $omset_projection = NULL;
  $user_id = NULL;

  if($id_plan){
    $sql    = "SELECT * from myplan where id = $id_plan";
    $result = mysqli_query($conn, $sql);
    $row    = mysqli_fetch_assoc($result);
    if(!$row){
      header("Location: myplan.php");
    }
    $segment             = $row['segment'];
    $user_id             = $row['user_id'];
    $school_id           = $row['school_id'];
    $program             = $row['program'];
    $wilayah             = $row['wilayah'];
    $start_timeline      = $row['start_timeline'];
    $end_timeline        = $row['end_timeline'];
    $omset_projection    = $row['omset_projection'];
    $level               = strtolower(trim($row['level']));

    $selected_lv = array_values(array_filter($levels, function ($lv) use ($level) {
        return strcasecmp(trim($lv), $level) === 0;
    }));

    $level2 = count($selected_lv) < 1 ? $level : '';
  }

  $segment_query    = "SELECT * from segments";
  $result           = mysqli_query($conn, $segment_query);
  $segment_rows     = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">
      <div class="row">
        <div class="col-12">
          <div class="bg-whites rounded h-100 p-4">
            <h6 class="mb-4">Create Plan</h6>
            <form method="POST" action="myplan-save.php" enctype="multipart/form-data" id="myplan-form">
              <?php
                if($id_plan) : ?>
                <Input type='hidden' value="<?= $id_plan ?>" name="plan_id" /> 
              <?php endif; ?>

              <table class="table table-striped">
                <?php if($id_user == 70) : ?>
                  <tr>
                    <td>Nama EC</td>
                    <td>:</td>
                    <td>
                      <select name="id_user" class="form-select form-select-sm select2">
                        <?php 
                          $sql = "SELECT * from user where role='ec' order by generalname ASC"; 
                          $resultsd1 = mysqli_query($conn, $sql);
                          while ($row = mysqli_fetch_assoc($resultsd1)) { ?>
                           <option value="<?= $row['id_user'] ?>" <?= ($user_id == $row['id_user']) ? 'selected' : '' ?>><?= $row['generalname'] ?></option>
                        <?php } ?>
                      </select>
                    </td>
                  </tr>
                <?php else : ?> 
                  <tr>
                    <td>Nama EC</td>
                    <td>:</td>
                    <td>
                      <?= $username?><input type="hidden" name="id_user" value="<?= $id_user ?>">
                    </td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td>Nama Sekolah</td>
                  <td>:</td>
                  <td>
                    <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Start Timeline Penyelesaian</td>
                  <td>:</td>
                  <td>
                    <input type="text" class="form-control form-control-sm dateFilter" name="start_timeline" value="<?= $start_timeline ?>" placeholder="Pick the date" required>
                  </td>
                </tr>
                <tr>
                  <td>End Timeline Penyelesaian</td>
                  <td>:</td>
                  <td>
                    <input type="text" class="form-control form-control-sm dateFilter" name="end_timeline" value="<?= $end_timeline ?>" placeholder="Pick the date" required>
                  </td>
                </tr>
                <tr>
                  <td>Segment</td>
                  <td>:</td>
                  <td>
                    <select name="segment" class="select2 form-select form-select-sm" required>
                      <?php foreach($segment_rows as $row) : ?>
                        <option value="<?= $row['id'] ?>" <?= $segment == $row['id'] ? 'selected' : '' ?>><?= $row['segment'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Jenjang Sekolah</td>
                  <td>:</td>
                  <td>
                    <select name="level" class="form-select form-select-sm" required>
                      <option value="tk" <?= $level == 'tk' ? 'selected' : '' ?>>TK</option>
                      <option value="sd" <?= $level == 'sd' ? 'selected' : '' ?>>SD</option>
                      <option value="smp" <?= $level == 'smp' ? 'selected' : '' ?>>SMP</option>
                      <option value="sma" <?= $level == 'sma' ? 'selected' : '' ?>>SMA</option>
                      <option value="yayasan" <?= $level == 'yayasan' ? 'selected' : '' ?>>Yayasan</option>
                      <option value="other" <?= $level2 != '' ? 'selected' : '' ?> id='level_manual_input'>Lainnya (isi sendiri)</option>
                    </select>
                    <div class="my-1" id='other_level' style="display: none;">
                      <input type="text" name="level2" value="<?= $level2 ?>" placeholder="Jenjang..." class="form-control form-control-sm">
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>Program</td>
                  <td>:</td>
                  <td>
                    <select name="program" id="program" class="form-select form-select-sm select2" required>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Wilayah Sekolah</td>
                  <td>:</td>
                  <td><input type="text" name="wilayah" value="<?= $wilayah ?>" placeholder="Wilayah" class="form-control form-control-sm" required></td>
                </tr>
                <tr>
                  <td>Proyeksi Omset</td>
                  <td>:</td>
                  <td>
                    <input type="text" name="omset_projection" value="<?= number_format((float)$omset_projection, 0, ',', '.') ?>" placeholder="Proyeksi Omset" class="form-control form-control-sm" oninput="formatNumber(this)" required>
                  </td>
                </tr>
              </table>

              <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
  flatpickr(".dateFilter", {
    dateFormat: "Y-m-d",
    allowInput: true,
  });

  function formatNumber(el) {
    // Hilangkan semua karakter non-digit
    let value = el.value.replace(/\D/g, '');
    if (!value) {
      el.value = '';
      return;
    }
    
    // Format pakai titik sebagai delimiter ribuan
    el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  document.getElementById('myplan-form').addEventListener('submit', function() {
    const input = this.querySelector('[name="omset_projection"]');
    input.value = input.value.replace(/\./g, '');
  });
  
  let school_id = "<?= $school_id ?>";
  school_id = school_id.trim();
  let program = "<?= $program ?? '' ?>";

  $(document).ready(function(){
    $('.select2').select2();

    $("select[name='level']").on('change', function() {
      let value = $(this).val();
      if(value == 'other') {
        $('#other_level').show();
        $('input[name="level2"]').prop('required', true);
      } else {
        $('#other_level').hide();
        $('input[name="level2"]').prop('required', false);
      }
    });

    $.ajax({
        url: 'https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=<?= $username ?>', 
        type: 'GET', 
        dataType: 'json', 
        success: function(response) {
          let options = '<option value="" disabled selected>Select a school</option>';
            response.map((data) => {
                options += `<option value="${data.id}" ${data.id == school_id ? 'selected' : ''}>${data.name}</option>`
            }) 

            $('#select_school').html(options);
            $('#select_school').select2();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#select_school').html('Error: ' + textStatus);
        }
    });

    function loadPrograms(schoolId, programId = false) {
      if (schoolId !== '' && schoolId !== null) {
        $.ajax({
            url: 'get-school-program.php',
            type: 'POST',
            dataType: 'json',
            data: {
                school_id: schoolId,
            },
            success: function(response) {
                let options = '<option value="" disabled>Select a program</option>';
                response.map((data) => {
                    const selected = programId ? (data.code == programId ? 'selected' : '') : '';
                    options += `<option value="${data.code}" ${selected}>${data.name}</option>`;
                });

                $('#program').html(options).select2();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus, errorThrown);
                alert("Failed to get program");
            }
        });
      } else {
        alert('No school selected');
      }
    }

    if(school_id !== '' && school_id !== null) {
      loadPrograms(school_id, program);
    }

    $('#select_school').on('change', function () {
        const newSchoolId = $(this).val();
        loadPrograms(newSchoolId);
    });

    // $('#submt').click(function(){
    //   $('#submt').prop('disabled',true);
    //   $('#myplan-form').submit();
    // });
    
  });
</script>

<?php include 'footer.php'; ?>