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
      $email    = $dra['username'];
      $ecname   = $dra['generalname'];
      $id_ec   = $dra['id_ec'];
      $school_name   = $dra['school_name'];
      $segment   = $dra['segment'];
      $level    = $dra['level'];
      $wilayah = $dra['wilayah'];
      $program = $dra['program'];
  
      if(($id_ec != $_SESSION['id_user'] && $_SESSION['role'] != 'admin') || $dra['status'] != 0) {
        $_SESSION['toast_status'] = 'Error';
        $_SESSION['toast_msg'] = 'Unauthorized Access';
        header('Location: ./draft-pk.php');
        exit();
      }
    }
  }  
?>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
          <div class="row">
              <div class="col-12">
                  <div class="bg-white rounded h-100 p-4">
                    <h6 class="mb-4">Create Draft Benefit PK</h6>
                    <form method="POST" action="save-draft-pk.php" enctype="multipart/form-data" id="input_form_benefit">
                        
                      <table class="table table-striped">
                        <tr>
                          <td style="width: 15%">Inputter</td>
                          <td style="width:5px">:</td>
                          <td><?= $_SESSION['username']?><input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>"></td>
                          <input type='hidden' name='inputEC' value="<?= $_SESSION['id_user'] ?> "> 
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

    function removeNonDigits(numberString) {
        let nonDigitRegex = /\D/g;

        let result = numberString.replace(nonDigitRegex, '');

        return result;
    }

    function formatNumber(number) {
      let parts = number.toString().split('.');
      let integerPart = parts[0];

      let formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      if (parts.length > 1) {
          let decimalPart = parts[1];
          return formattedIntegerPart + ',' + decimalPart;
      } else {
          return formattedIntegerPart;
      }
    }

    function getBenefitData(element){
        var row = $(element).closest('tr');
        var benefitId = row.find('select[name="benefit_id[]"]').find(":selected").val();
        var manval = row.find('input[name="manval[]"]')

        $.ajax({
        url: 'get_benefit_datas.php',
        type: 'POST',
        data: {
            benefitId: benefitId,
            program : '<?= $program ?>'
        },
        success: function(data) {
            row.find('input[name="benefit[]"]').val(data[0].benefit);
            row.find('input[name="id_templates[]"]').val(data[0].id_template_benefit);
            row.find('span[name="benefit"]').html(data[0].benefit);
            row.find('span[name="subbenefit"]').html(data[0].subbenefit);
            row.find('textarea[name="description[]"]').html(data[0].description);
            row.find('input[name="subbenefit[]"]').val(data[0].subbenefit);
            row.find('input[name="benefit_name[]"]').val(data[0].benefit_name);
            row.find('input[name="pelaksanaan[]"]').val(data[0].pelaksanaan);
            row.find('input[name="valuedefault[]"]').val(data[0].valueMoney);
            row.find('input[name="valben[]"]').val(formatNumber(data[0].valueMoney));
            var program = '<?= $program ?>';
            if((data[0].benefit_name==="Paket Literasi Menjadi Indonesia" && program=='bsp') || (data[0].benefit_name==="Paket Literasi Bahasa Inggris Storyland 20 series" && program=='bsp') || data[0].subbenefit==="Free Copy" || data[0].benefit_name==="input manual" || data[0].benefit_name==="Dana Pengembangan" || data[0].benefit_name.includes("ASTA") || data[0].benefit_name.includes("Oxford") || data[0].benefit_name.includes("OXFORD") || data[0].subbenefit==="Bebas Biaya Pengiriman" || data[0].subbenefit==="Deposit untuk Hidayatullah"){

              row.find('input[name="valben[]"]').prop("readonly", false);
            }else{
              row.find('input[name="valben[]"]').prop("readonly", true);
            }
            updateDisabledField(element);
        }
      });

    }

    function fillTheValue(id) {
      var total = 0;
      var moni = 0;
      $('.tah' + id).each(function() {
        var row   = $(this).closest('tr');
        var value = parseFloat($(this).val());
        var hiddenValue = row.find('input[name="valuedefault[]"]').val();
        hiddenValue = hiddenValue <= 0 ? row.find('input[name="valben[]"]').val() : hiddenValue;
   
        if (!isNaN(value)) {
          total += value;
          moni += hiddenValue * value;
        }
      });
      $('#qtyth' + id).text(total);
      $('#valth' + id).text("Rp "+ moni.toLocaleString("id-ID"));

      let total_alokasi = $('input[name="sumalok"]').val();
      let selisih = total_alokasi - moni;

      $('#total_benefit' + id).val(moni);

      $('#selisih_benefit' + id).val(selisih);
      $('#selisihbenefit' + id).html("Rp " + selisih.toLocaleString("id-ID"));

      return selisih;
    }

    function accumulateValues() {
      let total_alokasi = $('input[name="sumalok"]').val();
      let program = $('input[name="program"]').val();

      let year1 = fillTheValue(1)

      let checkIfStillMinus = $('#selisih_benefit1').val();
      checkIfStillMinus = checkIfStillMinus < 0 ? true : false;

      if(program == 'prestasi') {
        let year2 = fillTheValue(2);
        let year3 = fillTheValue(3);
        checkIfStillMinus = (checkIfStillMinus || year2 < 0 || year3 < 0) ? true : false;
      }

      if (checkIfStillMinus){
        $('#submt').prop('disabled', true);
      }else{
        $('#submt').prop('disabled', false);
      }

    }

    function updateDisabledField(element) {
      var row = $(element).closest('tr');
      var disabledField = row.find('input[name="calcValue[]"]');
      var member1 = row.find('input[name="member[]"]').val();
      var member2 = row.find('input[name="member2[]"]').val();
      var member3 = row.find('input[name="member3[]"]').val();

      var disabledField2 = row.find('input[name="valben[]"]');
      var defaultvalue = row.find('input[name="valuedefault[]"]').val();

      var total = parseInt(member1)+parseInt(member2)+parseInt(member3);
      if(defaultvalue != 0){
        disabledField.val(formatNumber(total*defaultvalue));
      }else{
        disabledField.val(formatNumber(total*disabledField2.val()));
      }
      accumulateValues();
    }

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