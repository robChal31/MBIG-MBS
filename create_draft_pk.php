<?php include 'header.php'; ?>
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

    /* .benefit-desc:hover {
      width: 40% !important;
    } */
    
    .benefit-ket {
        display: none;
    }

    table.dataTable tbody td {
      padding : 5px !important;
      vertical-align: middle !important;
      text-align: center !important;
      font-size: .9rem !important;
    }

    table.dataTable tbody td.benefit-desc{
      text-align: start !important;
    }

    table.dataTable thead th {
        font-size: .85rem !important;
    }

    table.dataTable tbody td {
        font-size: .75rem !important;
    }

    * {
      font-size: .9rem !important;
    }

    .is-invalid {
      border-color: #dc3545 !important;
    }

    .is-invalid:focus {
      box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
    }

    .select2-selection.is-invalid {
      border-color: #dc3545 !important;
      box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
    }
</style>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

  $id_draft     = ISSET($_GET['id_draft']) ? $_GET['id_draft'] : null;
  $email        = '';
  $ecname       = '';
  $id_ec        = '';
  $school_name  = '';
  $segment      = '';
  $level        = '';
  $wilayah      = '';
  $program      = '';
  $jenis_pk     = '';
  $selected_levels = [];
  $selected_subjects = [];
  $pic_name     = '';
  $pic_email    = '';
  $pic_phone    = '';
  $jabatan      = '';
  
  if($id_draft) {
    $sql = "SELECT *
              FROM draft_benefit as db
            LEFT JOIN user as ec on ec.id_user = db.id_ec
            LEFT JOIN school_pic as sp on sp.id_draft = db.id_draft
            WHERE db.id_draft = $id_draft";

    $result = mysqli_query($conn,$sql);

    while ($dra = $result->fetch_assoc()){
      $email        = $dra['username'];
      $ecname       = $dra['generalname'];
      $id_ec        = $dra['id_ec'];
      $school_name  = $dra['school_name'];
      $segment      = $dra['segment'];
      $level        = $dra['level'];
      $wilayah      = $dra['wilayah'];
      $program      = $dra['program'];
      $pic_name     = $dra['name'];
      $pic_email    = $dra['email'];
      $pic_phone    = $dra['no_tlp'];
      $jabatan      = $dra['jabatan'];
      $jenis_pk     = $dra['jenis_pk'];
    }

    if(($id_ec != $_SESSION['id_user'] && $_SESSION['role'] != 'admin') && $dra['status'] != 2) {
      $_SESSION['toast_status'] = 'Error';
      $_SESSION['toast_msg'] = 'Unauthorized Access';
      header('Location: ./draft-pk.php');
      exit();
    }

    // levels
    $q = mysqli_query($conn, "SELECT level_id FROM program_adoption_levels WHERE draft_id = $id_draft");
    while ($r = mysqli_fetch_assoc($q)) {
        $selected_levels[] = $r['level_id'];
    }

    // subjects
    $q = mysqli_query($conn, "SELECT subject_id FROM program_adoption_subjects WHERE draft_id = $id_draft");
    while ($r = mysqli_fetch_assoc($q)) {
        $selected_subjects[] = $r['subject_id'];
    }
  }
  
  $ecs = [];
  $ecs_q = "SELECT * FROM user WHERE role = 'ec' ORDER BY generalname";
  $ec_exec = mysqli_query($conn, $ecs_q);
  if (mysqli_num_rows($ec_exec) > 0) {
    $ecs = mysqli_fetch_all($ec_exec, MYSQLI_ASSOC);    
  }

  $role = $_SESSION['role'];

  // $programs = [];
  // $query_admin = $role == 'admin' ? '' : " AND program.is_classified = 0";
  // $query_program = "SELECT program.*, IFNULL(category.name, 'Unset') as category
  //                     FROM programs as program
  //                     LEFT JOIN program_categories as category on category.id = program.program_category_id
  //                     WHERE program.is_active = 1 AND program.is_pk = 1 $query_admin ";          

  // $exec_program = mysqli_query($conn, $query_program);
  // if (mysqli_num_rows($exec_program) > 0) {
  //     $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);    
  // }

  // $grouped_programs = [];

  // foreach($programs as $prog) {
  //   $grouped_programs[$prog['category']][] = $prog;
  // }

?>
  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4 f-1">
      <div class="row justify-content-center">
        <div class="col-12">
          <form method="POST" action="save-draft-pk.php" enctype="multipart/form-data" id="input_form_benefit">
            <div class="card rounded-4 shadow-sm p-4">

              <div class="d-flex align-items-center mb-4 border-bottom border-2 border-primary p-2">
                <div class="me-3">
                  <i class="fas fa-file-signature text-primary fs-4"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-semibold fs-5">Create Draft Benefit PK</h5>
                  <small class="text-muted fs-6">Lengkapi data sekolah & PIC</small>
                </div>
              </div>

              <div class="row g-4 border rounded shadow-md p-3 m-2 mb-4">
                <!-- INPUTTER -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">EC</label>
                  <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>"> 
                  <?php if($_SESSION['role'] != 'admin') { ?>
                    <div class="form-control form-control-sm bg-light">
                      <?= $_SESSION['username']?>
                    </div>
                    <input type="hidden" name="inputEC" value="<?= $_SESSION['id_user'] ?>">
                  <?php } else { ?>
                    <select name="inputEC" class="form-select form-select-sm select2" required>
                      <?php foreach($ecs as $ec) { ?>
                        <option value="<?= $ec['id_user'] ?>" <?= $ec['id_user'] == $id_ec ? 'selected' : '' ?>>
                          <?= $ec['generalname'] ?>
                        </option>
                      <?php } ?>
                    </select>
                  <?php } ?>
                </div>

                <!-- NAMA SEKOLAH -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Nama Sekolah</label>
                  <div id="select_school_div">
                    <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required style="width:100%;"></select>
                  </div>
                  <div class="d-none text-center mt-2" id="loading_school">
                    <i class="fas fa-spinner fa-spin text-primary"></i>
                  </div>
                </div>
                
                <!-- PK & PROGRAM -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Jenis PK</label>
                  <select name="jenis_pk" id="jenis_pk" class="form-select form-select-sm select2" required style="width:100%;">
                    <option value="">-- Pilih Jenis PK --</option>
                    <option value="1" <?= $jenis_pk == 1 ? 'selected' : '' ?>>PK Baru</option>
                    <option value="2" <?= $jenis_pk == 2 ? 'selected' : '' ?>>Amandemen</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Program</label>
                  <select name="program" id="program" class="form-select form-select-sm select2" required style="width:100%;"></select>
                </div>
                
                <!-- MY PLAN -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">My Plan Ref</label>
                  <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2" style="width:100%;"></select>
                </div>

                <!-- SEGMENT -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Segment</label>
                  <select name="segment" id="segment_input" class="form-select form-select-sm select2" required>
                    <option value="" disabled selected>- Select Segment -</option>
                    <?php 
                      $seg_sql = "SELECT * FROM segments";
                      $segQ = mysqli_query($conn, $seg_sql);
                      while ($row = mysqli_fetch_assoc($segQ)) : ?>
                        <option value='<?= $row['id'] ?>' <?=  ($segment == $row['id'] || $segment == strtolower($row['segment'])) ? 'selected' : '' ?>><?= $row['segment'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- ADOPTION LEVEL -->
                <div class="col-md-6">
                  <label class="form-label small text-muted">Cakupan Jenjang Program</label>
                  <select name="program_adoption_levels[]" id="adoption_levels" class="form-select form-select-sm select2" multiple required>
                    <?php
                      $lvl_sql = "SELECT * FROM levels";
                      $levelsQ = mysqli_query($conn, $lvl_sql);
                      while ($row = mysqli_fetch_assoc($levelsQ)) : ?>
                        <option value='<?= $row['id'] ?>' <?=  ($level == $row['id'] || $level == strtolower($row['name'])) ? 'selected' : '' ?>><?= $row['name'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- ADOPTION SUBJECT -->
                <div class="col-md-6">
                  <label class="form-label small text-muted">Cakupan Subjek Program</label>
                  <select name="program_adoption_subjects[]" id="adoption_subjects" class="form-select form-select-sm select2" multiple required>
                    <?php
                      $sub_sql = "SELECT * FROM subjects";
                      $subsq = mysqli_query($conn, $sub_sql);
                      while ($row = mysqli_fetch_assoc($subsq)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>

              <!-- PIC SECTION -->
              <div class="border rounded shadow-md p-3 m-2">
                <h6 class="fw-bold mb-3 border-bottom border-2 border-primary d-inline-block" style="font-size: 18px !important;">Informasi PIC</h6>
                <div class="row g-4 ">
                  <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="pic_name" value="<?= $pic_name ?>" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" value="<?= $jabatan ?>" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_tlp" value="<?= $pic_phone ?>" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email_pic" value="<?= $pic_email ?>" class="form-control form-control-sm" required>
                  </div>
                </div>
              </div>
            
            </div>

            <div class="col-12 mt-4">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                  <div>
                    <strong style="font-size: 16px;">📚 Benefit List</strong>
                    <div class="small opacity-75">Benefit yang akan digunakan dalam program</div>
                  </div>

                  <!-- <button type="button" id="btnAddTitle" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="" disabled>
                    <i class="bi bi-plus"></i> Add Title
                  </button> -->
                </div>
                <div class="position-relative" id="contentWrapper">
                <div id="benefit_container">
                  <div class="p-4">

                    <!-- EMPTY STATE -->
                    <div id="emptyState" class="text-center text-muted py-5 border rounded bg-light">
                      <i class="bi bi-book-half fs-1 mb-3 d-block"></i>

                      <p class="mb-3" style="max-width: 420px; margin: 0 auto;">
                        <i class="bi bi-exclamation-triangle"></i>
                      <small>
                        Silakan <strong>pilih program terlebih dahulu, </strong>
                      </small> untuk menampilkan benefit yang tersedia.
                      </p>

                      <span class="badge bg-warning text-dark px-3 py-2">
                        ⚠️ Program wajib dipilih
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

      <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

  const idDraft = '<?= $id_draft ?>';
  const program = '<?= $program ?>';
  const selectedLevels = <?= json_encode($selected_levels) ?>;
  const selectedSubjects = <?= json_encode($selected_subjects) ?>;

  if (selectedLevels.length) {
    $('#adoption_levels').val(selectedLevels).trigger('change');
  }

  if (selectedSubjects.length) {
    $('#adoption_subjects').val(selectedSubjects).trigger('change');
  }

  function getMyPlanRef(schoolPlanId = null) {
    const ec = $('input[name="inputEC"]').val() ?? $('select[name="inputEC"]').val();
    const schoolId = schoolPlanId ? schoolPlanId : $('select[name="nama_sekolah"]').val();

    if(ec && schoolId) {
      $.ajax({
        url: 'get-ec-plan.php',
        type: 'POST',
        dataType: 'json',
        data: {
            school_id: schoolId,
            ec: ec,
            is_pk: 1,
            id_draft: idDraft
        },
        success: function(response) {
          let options = '<option value="" disabled selected>Select a plan</option>';
          response.map((data, index) => {
            let selected = index === 0 ? 'selected' : '';
            if(selected == 'selected') {
              getPlanData(data.value);
            }
            options += `<option value="${data.value}" ${selected}>${data.label}</option>`
          }) 

          $('#myplan_id').html(options);
          $('#myplan_id').select2();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log('Error:', textStatus, errorThrown);
          alert("Failed to get myplan")
        }
      });
    }
  }

  $('#myplan_id').on('change', function () {
    const selectedId = $(this).val();

    if (!selectedId) return;

    getPlanData(selectedId);
    
  });

  function getPlanData(planId) {
    $.ajax({
      url: 'get-myplan-data.php',
      method: 'POST',
      data: { myplan_id: planId },
      dataType: 'json',
      success: function (res) {
        if (res && res.program) {
          const programName = res.program.trim();
          $('#program').val(programName).trigger('change');
          $('#level').val(res.level);
        } else {
          $('#program').val('').trigger('change');
          $('#level').val('');
          $('#program').val('').trigger('change');
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('Error:', textStatus, errorThrown);
        alert("Failed to get myplan");
      }
    });
  }

  function getPrograms(schoolId, formatGroupItems, selectedProgram = null) {
    $.ajax({
      url: 'get-school-program-grouped.php',
      type: 'POST',
      dataType: 'json',
      data: {
          school_id: schoolId,
      },
      success: function(response) {
        let options = '<option value="" disabled selected>Select a Program</option>';

        Object.entries(response).forEach(([category, programs]) => {
          options += `<optgroup label="${category}">`;

          programs.forEach((program) => {
            options += `<option value="${program.code}">${program.name}</option>`;
          });

          options += `</optgroup>`;
        });

        $('#program').html(options);
        $('#program').select2({
          placeholder: 'Select a Program',
          templateResult: formatGroupItems,
          closeOnSelect: false,
        });
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log('Error:', textStatus, errorThrown);
        alert("Failed to get program")
      }
    });
  }

  $(document).ready(function(){

    $('.select2').select2({
      width: '100%'
    });

    $('#select_school').on('change', function() {
      var schoolId = $(this).val();

      if (schoolId) {
        getPrograms(schoolId, formatGroupItems);
        
        getMyPlanRef();

      } else {
          alert('No school and ec selected');
      }
    });

    $('#program').select2({
      placeholder: 'Select a Program',
      templateResult: formatGroupItems,
      closeOnSelect: false,
    });

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

    let level = '<?= $level ?>';
    let levels = ['tk', 'sd', 'smp', 'sma', 'yayasan'];

    if(level) {
      if(levels.indexOf(level) === -1) {
        $('#other_level').show();
        $('input[name="level2"]').prop('required', true);
        $('input[name="level2"]').val('<?= $level ?>');
      }
    }

    const draftSchoolId = '<?= $school_name ?>';

    if (idDraft) {
      $.ajax({
        url: './get_benefits_pk.php?id_draft=<?= $id_draft ?>&program=' + program,
        type: 'GET',
        success: function (response) {
          $('#benefit_container').html(response);
          loadSchoolSelect();
          getPrograms(draftSchoolId, formatGroupItems, program);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log('Error benefit:', textStatus);
          loadSchoolSelect();
        }
      });
    } else {
      loadSchoolSelect();
    }

    function loadSchoolSelect() {
      $.ajax({
        url: 'https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=<?= $_SESSION['username'] ?>',
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
          $('#select_school_div').addClass('d-none');
          $('#loading_school').removeClass('d-none');
        },
        success: function (response) {
          let options = '<option value="" disabled selected>Select a school</option>';
          let schoolId = '<?= $school_name ?>';
          getMyPlanRef(schoolId)
          response.map((data) => {
            options += `<option value="${data.id}" ${schoolId == data.id ? 'selected' : ''}>${data.name}</option>`;
          });

          $('#select_school').html(options);
          $('#select_school').select2({ width: '100%' });

          $('#loading_school').addClass('d-none');
          $('#select_school_div').removeClass('d-none');
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log('Error school:', textStatus);
          $('#select_school').html('Error: ' + textStatus);
          $('#loading_school').addClass('d-none');
          $('#select_school_div').removeClass('d-none');
        },
        complete: function () {
          $('#loading_school').addClass('d-none');
          $('#select_school_div').removeClass('d-none');
        }
      });
    }

    $("#program").change(function (e) {
      let selectedProgram = $(this).val();
      let id_draft = '<?= $id_draft ?>';

      $.ajax({
        url: './get_benefits_pk.php?id_draft=<?= $id_draft ?>&program='+selectedProgram, 
        type: 'GET', 
        // dataType: 'json', 
        success: function(response) {
            $('#benefit_container').html(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
        }
      });
    })

    function formatGroupItems(data) {

      if (data.element && data.element.tagName === 'OPTGROUP') {
          return $(`<div class="select2-optgroup-label" style=" color: #333; padding: 5px; cursor: pointer;">
                      <b>${data.text}</b>
                  </div>`);
      }
      return data.text;
    }

  });

  // Add event listener to toggle groups when clicking on the group label
  $(document).on('click', '.select2-optgroup-label', function (e) {
    const $group = $(this).closest('.select2-results__group');
    $group.nextUntil('.select2-results__group').toggle(); // Hide/show options
    e.stopPropagation(); // Prevent dropdown close
  });

  // Additional event listener for when options in a group are shown
  $(document).on('click', '.select2-results__group', function () {
      $(this).find('.select2-results__options').toggle(); // Show or hide options on group click
  });

</script>
<?php include 'footer.php'; ?>