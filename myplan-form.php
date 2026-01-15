
<?php 
  include 'header.php'; 

  $id_plan  = ISSET($_GET['plan_id']) ? $_GET['plan_id'] : NULL;
  $username = $_SESSION['username'];
  $id_user  = $_SESSION['id_user'];
  $role     = $_SESSION['role'];

  $segment = NULL;
  $school_id = NULL;
  $program = NULL;
  $wilayah = NULL;
  $level = NULL;
  $start_timeline = NULL;
  $end_timeline = NULL;
  $omset_projection = NULL;
  $user_id = NULL;
  $selected_levels = [];
  $selected_subjects = [];

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

    // levels
    $q = mysqli_query($conn, "SELECT level_id FROM program_plan_adoption_levels WHERE plan_id = $id_plan");
    while ($r = mysqli_fetch_assoc($q)) {
        $selected_levels[] = $r['level_id'];
    }

    // subjects
    $q = mysqli_query($conn, "SELECT subject_id FROM program_plan_adoption_subjects WHERE plan_id = $id_plan");
    while ($r = mysqli_fetch_assoc($q)) {
        $selected_subjects[] = $r['subject_id'];
    }

  }

  $segment_query    = "SELECT * from segments";
  $result           = mysqli_query($conn, $segment_query);
  $segment_rows     = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>

<style>
  * {
    font-size: .9rem !important;
  }


  /* ===== FORM GRID ===== */
  .plan-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 24px;
  }

  .plan-form .form-group {
    display: flex;
    flex-direction: column;
  }

  .plan-form .form-group.full {
    grid-column: span 2;
  }

  /* ===== LABEL ===== */
  .plan-form label {
    font-weight: 600;
    color: #495057;
    font-size: .8rem !important;
    margin-bottom: 6px;
  }

  .tag-ungu {
    background: #4f46e5 !important;
    color: white !important;
  }
</style>

  <!-- Content Start -->
<div class="content">
  <?php include 'navbar.php'; ?>

  <div class="container-fluid p-4">
    <div class="row">
      <div class="col-12">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center mb-4">
              <div class="me-3">
                <i class="fas fa-file-signature text-primary fs-4"></i>
              </div>
              <div>
                <h5 class="mb-0 fw-semibold fs-5">Create Plan</h5>
                <small class="text-muted fs-6">Lengkapi data rencana adopsi program</small>
              </div>
            </div>

            <div class="">
              <a href="myplan.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
              </a>
            </div>
          </div>

          <form method="POST" action="myplan-save.php" id="myplan-form">
            <?php if($id_plan): ?>
              <input type="hidden" name="plan_id" value="<?= $id_plan ?>">
            <?php endif; ?>

            <div class="plan-form">

              <div class="form-group">
                <label>Nama EC</label>
                <?php if($role == 'admin'): ?>
                  <select name="id_user" id="select_ec" class="form-select form-select-sm select2">
                    <option value="" disabled selected>- Select EC -</option>
                    <?php
                      $q = mysqli_query($conn,"SELECT * FROM user WHERE role = 'ec' AND is_active = 1 ORDER BY generalname");
                      while($r=mysqli_fetch_assoc($q)): ?>
                        <option value="<?= $r['id_user'] ?>" <?= $user_id==$r['id_user']?'selected':'' ?>>
                          <?= $r['generalname'] ?>
                        </option>
                      <?php endwhile; ?>
                  </select>
                <?php else: ?>
                  <input type="text" class="form-control form-control-sm" value="<?= $username ?>" disabled>
                  <input type="hidden" name="id_user" value="<?= $id_user ?>">
                <?php endif; ?>
              </div>

              <div class="form-group">
                <label>Nama Institusi</label>
                <div class="select_school_div">
                  <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required>
                    <option value="" disabled selected>Select a school</option>
                  </select>
                  <?php if($role == 'admin'): ?>
                    <small class="d-block mt-1" style="font-size: 11px !important;">
                      Untuk memuncukkan data, silahkan pilih EC terlebih dahulu
                    </small>
                  <?php endif; ?>
                </div>
                <div class="loading_school text-center d-none mt-1">
                  <i class="fas fa-spinner fa-spin text-primary"></i>
                </div>
              </div>

              <div class="form-group">
                <label>Program</label>
                <select name="program" id="program" class="form-select form-select-sm select2" required>
                  <option value="" disabled selected>Select a program</option>
                </select>
              </div>

              <div class="form-group">
                <label>Segment</label>
                <select name="segment" class="form-select form-select-sm select2" required>
                  <option value="" disabled selected>Select a segment</option>
                  <?php foreach($segment_rows as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $segment == $s['id'] || (strtolower($segment) == strtolower($s['segment'])) ? 'selected' : '' ?>>
                      <?= $s['segment'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- ADOPTION LEVEL -->
              <div class="form-group">
                <label class="form-label small text-muted">Cakupan Jenjang Program</label>
                <select name="program_plan_adoption_levels[]" id="adoption_levels" class="form-select form-select-sm select2" multiple required>
                  <?php
                    $lvl_sql = "SELECT * FROM levels";
                    $levelsQ = mysqli_query($conn, $lvl_sql);
                    while ($row = mysqli_fetch_assoc($levelsQ)) {
                      echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                  ?>
                </select>
              </div>

              <!-- ADOPTION SUBJECT -->
              <div class="form-group">
                <label class="form-label small text-muted">Cakupan Subjek Program</label>
                <select name="program_plan_adoption_subjects[]" id="adoption_subjects" class="form-select form-select-sm select2" multiple required>
                  <?php
                    $sub_sql = "SELECT * FROM subjects";
                    $subsq = mysqli_query($conn, $sub_sql);
                    while ($row = mysqli_fetch_assoc($subsq)) {
                      echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label>Start Timeline</label>
                <input type="text" placeholder="Start Timeline" name="start_timeline" value="<?= $start_timeline ?>" class="form-control form-control-sm dateFilter" required>
              </div>

              <div class="form-group">
                <label>End Timeline</label>
                <input type="text" placeholder="End Timeline" name="end_timeline" value="<?= $end_timeline ?>" class="form-control form-control-sm dateFilter" required>
              </div>

              <div class="form-group">
                <label>Wilayah Sekolah</label>
                <input type="text" name="wilayah" placeholder="Ex: Jakarta" value="<?= $wilayah ?>" class="form-control form-control-sm" required>
              </div>

              <div class="form-group">
                <label>Proyeksi Omset</label>
                <input type="text" placeholder="Ex: 100.000.000" name="omset_projection" value="<?= $omset_projection ? number_format((float)$omset_projection,0,',','.') : '' ?>" class="form-control form-control-sm" oninput="formatNumber(this)" required>
              </div>

            </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-primary fw-bold" id="submt">Submit</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>

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

  function fetchShool(email, schoolId = null) {
    $.ajax({
      url: `https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=${email}`,
      type: 'GET',
      dataType: 'json',
      beforeSend: function () {
        $('.select_school_div').addClass('d-none');
        $('.loading_school').removeClass('d-none');
      },
      success: function (response) {
        let options = '<option value="" disabled selected>Select a school</option>';
        response.forEach(data => {
          options += `<option value="${data.id}" ${schoolId == data.id ? 'selected' : ''}>${data.name}</option>`;
        });

        $('#select_school').html(options).select2({ width: '100%' });
      },
      error: function () {
        alert('Failed to load school');
      },
      complete: function () {
        $('.loading_school').addClass('d-none');
        $('.select_school_div').removeClass('d-none');
      }
    });
  }

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
          let options = '<option value="" disabled selected>Select a program</option>';
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

  async function getUserData(userId) {
    try {
      $('.loading_school').removeClass('d-none');

      const res = await $.ajax({
        url: 'get-user-data.php',
        type: 'POST',
        dataType: 'json',
        data: { id_user: userId }
      });

      return res;

    } catch (err) {
      console.error('Error:', err);
      alert('Failed to get user data');
      return null;

    } finally {
      $('.loading_school').addClass('d-none');
    }
  }

  async function initPlanUser(userIdPlan, school_id) {
    if (!userIdPlan) return;

    const user = await getUserData(userIdPlan);
    if (user) {
      fetchShool(user.username, school_id);
    }
  }

  let school_id   = "<?= $school_id ?>";
  school_id       = school_id.trim();
  let role        = "<?= $role ?>";
  let idPlan      = "<?= $id_plan ?>";
  let userIdPlan  = "<?= $user_id ?>";
  let idUser      = "<?= $id_user ?>";
  let username    = "<?= $username ?>";
  let program     = "<?= $program ?? '' ?>";

  const selectedLevels = <?= json_encode($selected_levels) ?>;
  const selectedSubjects = <?= json_encode($selected_subjects) ?>;

  if (selectedLevels.length) {
    $('#adoption_levels').val(selectedLevels).trigger('change');
  }

  if (selectedSubjects.length) {
    $('#adoption_subjects').val(selectedSubjects).trigger('change');
  }

  $(document).ready(function(){
    $('.select2').select2();

    $('.select2[multiple]').select2({
      placeholder: 'Select option',
      templateSelection: function (data, container) {
        $(container).addClass('tag-ungu');
        return data.text;
      }
    });

    $('#select_ec').on('change', async function () {
      const userId = $(this).val();
      if (!userId) return;
      const userData = await getUserData(userId);
      if(userData) {
        fetchShool(userData.username);
      }
    });

    if(school_id !== '' && school_id !== null) {
      loadPrograms(school_id, program);
    }

    $('#select_school').on('change', function () {
      const newSchoolId = $(this).val();
      loadPrograms(newSchoolId);
    });

    initPlanUser(userIdPlan, school_id);

    if(role == 'ec') {
      fetchShool(username);
    }

  });

  document.getElementById('myplan-form').addEventListener('submit', function() {
    const input = this.querySelector('[name="omset_projection"]');
    input.value = input.value.replace(/\./g, '');
  });

  flatpickr(".dateFilter", {
    dateFormat: "Y-m-d",
    allowInput: true,
  });
</script>

<?php include 'footer.php'; ?>