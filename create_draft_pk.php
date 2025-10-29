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
</style>

<?php
  $id_draft     = ISSET($_GET['id_draft']) ? $_GET['id_draft'] : null;
  $email        = '';
  $ecname       = '';
  $id_ec        = '';
  $school_name  = '';
  $segment      = '';
  $level        = '';
  $wilayah      = '';
  $program      = '';

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

    <div class="container-fluid p-4">
      <div class="row">
        <div class="col-12">
          <div class="bg-whites rounded h-100 p-4">
            <h6 class="mb-4">Create Draft Benefit PK</h6>
            <form method="POST" action="save-draft-pk.php" enctype="multipart/form-data" id="input_form_benefit">
              <table class="table table-striped">
                <tr>
                  <td style="width: 15%">Inputter</td>
                  <td style="width:5px">:</td>
                  <td>
                    <input type='hidden' name='id_user' value="<?= $_SESSION['id_user'] ?>"> 
                    <?php
                      if($_SESSION['role'] != 'admin') { ?>
                        <?= $_SESSION['username']?>
                        <input type="hidden" name="inputEC" value="<?= $_SESSION['id_user'] ?>">
                    <?php } else {?>
                      <select name="inputEC" class="form-select form-select-sm select2" required style="width: 100%;">
                        <?php foreach($ecs as $ec) { ?>
                          <option value="<?= $ec['id_user'] ?>" <?= $ec['id_user'] == $id_ec ? 'selected' : '' ?>><?= $ec['generalname'] ?></option>
                        <?php } ?>
                      </select>
                    <?php } ?>
                  </td>
                </tr>
                <tr>
                  <td>Nama Sekolah</td>
                  <td>:</td>
                  <td>
                    <div class="d-block w-100" id="select_school_div">
                      <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required style="width: 100%;">
                      </select>
                    </div>
                    <div class="d-none text-center" id="loading_school"><i class="fas fa-spinner fa-spin text-primary"></i></div>
                  </td>
                </tr>
                <tr>
                  <td>My Plan Ref</td>
                  <td>:</td>
                  <td>
                    <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2" style="width: 100%;">
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Segment Sekolah</td>
                  <td>:</td>
                  <td>
                    <select name="segment" class="form-select form-select-sm select2" required style="width: 100%;">
                      <option value="national" <?= $segment == 'national' ? 'selected' : '' ?>>National</option>
                      <option value="national plus" <?= $segment == 'national plus' ? 'selected' : '' ?>>National Plus</option>
                      <option value="internasional/spk" <?= $segment == 'internasional/spk' ? 'selected' : '' ?>>International/SPK</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Jenjang Sekolah</td>
                  <td>:</td>
                  <td>
                    <select name="level" class="form-select form-select-sm select2" required style="width: 100%;">
                      <option value="tk" <?= $level == 'tk' ? 'selected' : '' ?>>TK</option>
                      <option value="sd" <?= $level == 'sd' ? 'selected' : '' ?>>SD</option>
                      <option value="smp" <?= $level == 'smp' ? 'selected' : '' ?>>SMP</option>
                      <option value="sma" <?= $level == 'sma' ? 'selected' : '' ?>>SMA</option>
                      <option value="yayasan" <?= $level == 'yayasan' ? 'selected' : '' ?>>Yayasan</option>
                      <option value="other" id='level_manual_input' <?= $level ? (!in_array($level, ['tk', 'sd', 'smp', 'sma', 'yayasan']) ? 'selected' : '') : '' ?>>Lainnya (isi sendiri)</option>
                    </select>
                    <div class="my-1" id='other_level' style="display: none;">
                      <input type="text" name="level2" value="" placeholder="Jenjang..." class="form-control form-control-sm">
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>Wilayah Sekolah</td>
                  <td>:</td>
                  <td><input type="text" name="wilayah" placeholder="wilayah" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                </tr>
                <tr>
                  <td>Nama Lengkap PIC</td>
                  <td>:</td>
                  <td><input type="text" name="pic_name" placeholder="nama lengkap" class="form-control form-control-sm" value="<?= ISSET($pic_name) ? $pic_name : '' ?>" required></td>
                </tr>
                <tr>
                  <td>Jabatan PIC</td>
                  <td>:</td>
                  <td><input type="text" name="jabatan" placeholder="jabatan" class="form-control form-control-sm" value="<?= ISSET($jabatan) ? $jabatan : '' ?>" required></td>
                </tr>
                <tr>
                  <td>No. Telepon PIC</td>
                  <td>:</td>
                  <td><input type="text" name="no_tlp" placeholder="no telp" class="form-control form-control-sm" value="<?= ISSET($pic_phone) ? $pic_phone : '' ?>" required></td>
                </tr>
                <tr>
                  <td>E-mail PIC</td>
                  <td>:</td>
                  <td><input type="email" name="email_pic" placeholder="email" class="form-control form-control-sm" value="<?= ISSET($pic_email) ? $pic_email : '' ?>" required></td>
                </tr>
                <tr>
                  <td>Jenis PK</td>
                  <td>:</td>
                  <td>
                    <select name="jenis_pk" class="form-select form-select-sm select2" required id="jenis_pk" required style="width: 100%;">
                      <option value="">-- Select Jenis PK --</option>
                      <option value="1" <?= (ISSET($jenis_pk) && $jenis_pk == 1) ? 'selected' : '' ?>>PK Baru</option>
                      <option value="2" <?= (ISSET($jenis_pk) && $jenis_pk) == 2 ? 'selected' : '' ?>>Amandemen</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Program</td>
                  <td>:</td>
                  <td>
                    <select name="program" class="form-select form-select-sm select2" required id="program" required style="width: 100%;">
                      
                    </select>
                  </td>
                </tr>
              </table>

              <div class="mt-4" id="benefit_container"></div>

            </form>
          </div>
        </div>
      </div>
    </div>
      <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

  const idDraft = '<?= $id_draft ?>';
  const program = '<?= $program ?>';

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

    $('.select2').select2();

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