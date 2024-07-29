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

    .benefit-desc:hover {
      width: 40% !important;
    }
    
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
        font-size: .9rem !important;
    }
</style>

<?php include 'header.php'; ?>

<?php
  $id_draft = $_GET['id_draft'];
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
  
      if(($id_ec != $_SESSION['id_user'] || $_SESSION['role'] != 'admin') || $dra['status'] != 0) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Unauthorized Access';
        header('Location: ./draft-pk.php');
        exit();
      }
    }
  }
  
  $ecs = [];
  $ecs_q = "SELECT * FROM user WHERE role = 'ec' ORDER BY generalname";
  $ec_exec = mysqli_query($conn, $ecs_q);
  if (mysqli_num_rows($ec_exec) > 0) {
    $ecs = mysqli_fetch_all($ec_exec, MYSQLI_ASSOC);    
  }

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
                            <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required style="width: 100%;">
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
                          <td><input type="text" name="wilayah" placeholder="Wilayah" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                        </tr>
                        <tr>
                          <td>Nama Lengkap PIC</td>
                          <td>:</td>
                          <td><input type="text" name="pic_name" placeholder="nama lengkap" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                        </tr>
                        <tr>
                          <td>Jabatan PIC</td>
                          <td>:</td>
                          <td><input type="text" name="jabatan" placeholder="jabatan" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                        </tr>
                        <tr>
                          <td>No. Telepon PIC</td>
                          <td>:</td>
                          <td><input type="text" name="no_tlp" placeholder="no telp" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                        </tr>
                        <tr>
                          <td>E-mail PIC</td>
                          <td>:</td>
                          <td><input type="email" name="email_pic" placeholder="email" class="form-control form-control-sm" value="<?= $wilayah ?>" required></td>
                        </tr>
                        <tr>
                          <td>Jenis PK</td>
                          <td>:</td>
                          <td>
                            <select name="jenis_pk" class="form-select form-select-sm select2" required id="jenis_pk" required style="width: 100%;">
                              <option value="">-- Select Jenis PK --</option>
                              <option value="1">PK Baru</option>
                              <option value="2">Amandemen</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>Program</td>
                          <td>:</td>
                          <td>
                            <select name="program" class="form-select form-select-sm select2" required id="program" required style="width: 100%;">
                              <option value="">-- Select Program --</option>
                              <?php
                                  $programs = [];
                                  $query_program = "SELECT * FROM programs WHERE is_active = 1 AND is_pk = 1";

                                  $exec_program = mysqli_query($conn, $query_program);
                                  if (mysqli_num_rows($exec_program) > 0) {
                                      $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);    
                                  }

                                  foreach($programs as $prog) : ?>
                                    <option value="<?= $prog['name'] ?>" <?= strtolower($prog['name']) == strtolower($program) ? 'selected' : '' ?>><?= $prog['name'] ?></option>
                            <?php endforeach; ?>
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

      let level = '<?= $level ?>';
      let levels = ['tk', 'sd', 'smp', 'sma', 'yayasan'];

      if(level) {
        if(levels.indexOf(level) === -1) {
          $('#other_level').show();
          $('input[name="level2"]').prop('required', true);
          $('input[name="level2"]').val('<?= $level ?>');
        }
      }

      $.ajax({
        url: 'https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=<?= $_SESSION['username'] ?>', 
        type: 'GET', 
        dataType: 'json', 
        success: function(response) {
            let options = '';
            let schoolId = '<?= $school_name ?>';
            response.map((data) => {
                options += `<option value="${data.id}" ${schoolId == data.id ? 'selected' : ''}>${data.name}</option>`
            }) 

            $('#select_school').html(options);
            $('#select_school').select2();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#select_school').html('Error: ' + textStatus);
        }
      });

      let idDraft = '<?= $id_draft ?>';
      let program = '<?= $program ?>';

      if(idDraft) {
        $.ajax({
          url: './get_benefits_pk.php?id_draft=<?= $id_draft ?>&program='+program, 
          type: 'GET', 
          // dataType: 'json', 
          success: function(response) {
              $('#benefit_container').html(response);
          },
          error: function(jqXHR, textStatus, errorThrown) {
              console.log(textStatus);
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

    });

</script>
<?php include 'footer.php'; ?>