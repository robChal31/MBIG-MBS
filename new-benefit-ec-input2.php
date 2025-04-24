<?php include 'header.php'; ?>

<?php

$current_row = 1;
if($_GET['edit'] == 'edit'){ 
  $id_draft = $_GET['id_draft'];
  $sql      = "SELECT db.*, sc.name as school_name2
              FROM draft_benefit as db 
              LEFT JOIN schools as sc on sc.id = db.school_name
              where id_draft = $id_draft";
  $result   = mysqli_query($conn,$sql);
  
  if(mysqli_num_rows($result) < 1){
    header('Location: draft-benefit.php');
    exit;
  } else if(mysqli_num_rows($result) == 1){

    while ($data = $result->fetch_assoc()){
      $program                  = $data['program'];
      $sumalok                  = $data['alokasi'];
      $total_benefit            = $data['total_benefit'];
      $school_name              = $data['school_name2'];
      $selisih_benefit          = $data['selisih_benefit'];
      $year                     = $data['year'];
      $ref_id                   = $data['ref_id'];

      $_SESSION['program']      = $program;
      $_SESSION['sumalok']      = $sumalok;
      $_SESSION['id_draft']     = $id_draft;
      $_SESSION['school_name']  = $school_name;
      $_SESSION['segment']      = $data['segment'];

    }

    //get draft benefit list count
    $sql          = "SELECT b.*, a.* FROM draft_benefit_list a 
                      LEFT JOIN draft_template_benefit AS b on a.id_template = b.id_template_benefit 
                      WHERE a.id_draft = '$id_draft'";
    $result       = mysqli_query($conn,$sql);
    $current_row  = mysqli_num_rows($result);
  }

}else{
  $program  = $_SESSION['program'];
  $id_draft = $_SESSION['id_draft'];
  $sumalok  = $_SESSION['sumalok'];
}

$program = strtolower($program);

$query_status = "SELECT db.status  
                  FROM draft_benefit db 
                  INNER JOIN draft_approval da on da.id_draft = db.id_draft 
                  WHERE (da.status = 0 or da.status = 1)
                  AND db.id_draft = $id_draft
                ";
$result_status = mysqli_query($conn, $query_status);
$data_status = mysqli_fetch_assoc($result_status);

if($data_status && $data_status['status'] != 2 && $data_status['status'] != null){
  $msg = $data_status['status'] == 1 ? 'Draft telah Di Approve' : ($data_status['status'] == 0 ? 'Draft sedang dalam proses approval' : '');
  $_SESSION['toast_status'] = 'Unauthorized Access';
  $_SESSION['toast_msg'] = $msg;
  header('Location: ./draft-benefit.php');
  exit();
}

$sql = "SELECT id_template_benefit FROM draft_template_benefit WHERE benefit_name LIKE '%dana pengembangan%'";
$check_result = mysqli_query($conn, $sql);

$make_max_ids = [];
while ($row = mysqli_fetch_assoc($check_result)) {
    $make_max_ids[] = (int)$row['id_template_benefit'];
}

?>

<style>
  select {
    max-width: 400px; /* Adjust the value to your desired maximum width */
    word-wrap: break-word;
  }

  textarea {
    width: 100%;
    height: 130px;
    transition: width 3s ease;
  }

  /* .text-area-cont:hover {
    width: 35% !important;
  } */

  .benefit-ket {
    display: none;
  }

  table.dataTable tbody td {
      padding: 2px !important;
      vertical-align: middle !important;
      text-align: center !important;
  }

  /* .txt-area:hover {
    height: 200px;
  } */

    /* Style the dropdown options */
  .select2-container .select2-dropdown {
    width: 60vw !important; /* Set the dropdown's overall width */
  }

  .select2-container .select2-results__option {
      /* white-space: nowrap; 
      overflow: hidden; 
      text-overflow: ellipsis;  */
      max-width: 60vw; 
      font-size: 14px;
  }

  /* Optional styling for the optgroup label */
  .select2-optgroup-label {
      font-weight: bold;
      cursor: pointer;
  }

  /* Initially hide the options inside the optgroup */
  .select2-results__options optgroup {
      display: none;
  }

  .td-cust {
    vertical-align: middle;
    text-align: center;
  }

</style>


    <!-- Content Start -->
    <div class="content">
      <!-- Navbar Start -->
      <?php include 'navbar.php'; ?>
      <!-- Navbar End -->
        

      <!-- Form Start -->
      <div class="container-fluid p-4">
        <div class="row">
          <div class="col-12">
            <div class="bg-whites rounded h-100 p-4">

              <div class="d-flex justify-content-between mb-4 mt-2">
                <a href="<?= "edit-benefit-ec-input.php?id_draft=$id_draft" ?>" class="btn btn-primary btn-sm">
                  <i class="fas fa-edit me-2"></i>Back to Input Book
                </a>
                <div class="d-flex justify-content-end">
                  <!-- <span style="font-size: .75rem; display: inline-block;">click + for adding row</span> -->
                  <button type='button' class="add-button btn btn-success btn-sm me-2" style="display:block" id='add_row'>
                      <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
              <form method="POST" action="new-benefit-ec-input-action2.php" enctype="multipart/form-data" id='draft_form' >
                <div class="">
                  <div style="width: 100%; overflow-x: auto; padding: 15px 0px;">
                    <div style="width: 135%">
                      <input type="hidden" value="<?= $sumalok ?>" name="sumalok">
                      <input type="hidden" value="<?= $program ?>" name="program">
                      <input type="hidden" value="<?= $year ?>" name="year">
                      <input type="hidden" value="<?= $ref_id ?>" name="ref_id">
                      <table class="border table table-striped table-bordered dataTable no-footer" id="input_form">
                        <thead>
                          <tr>
                            <td class="td-cust text-center" rowspan="2">Benefit</td>
                            <td class="td-cust text-center" rowspan="2">Sub Benefit</td>
                            <td class="td-cust text-center" rowspan="2" style="width:15%">Nama Benefit</td>
                            <td class="td-cust text-center" rowspan="2" style="width:25%">Deskripsi</td>
                            <td class="td-cust text-center" rowspan="2" style="width: 15%">Pelaksanaan</td>
                            <td class="td-cust text-center" rowspan="2" style="min-width:100px">Nilai Benefit</td>
                            <!-- <td class="td-cust text-center" rowspan="2" class="benefit-ket">Keterangan</td> -->
                            <td class="td-cust text-center" colspan="3">Quantity Per Tahun</td>
                            <td class="td-cust text-center" rowspan="2">Nilai Value</td>
                            <td class="td-cust text-center" rowspan="2">Action</td>
                          </tr>
                          <tr>
                            <td style="width: 50px">1</td>
                            <td style="width: 50px">2</td>
                            <td style="width: 50px">3</td>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(($current_row == 1 && !ISSET($_GET['edit'])) || ($current_row < 1 && ISSET($_GET['edit']))): ?>
                            <tr id="row<?= $current_row ?>">
                                <td>
                                  <span class="benefit"><?= ucfirst($program); ?> Benefit</span>
                                  <input type='hidden' name='benefit[]' value=''>
                                  <input type='hidden' name='id_templates[]' value=''>
                                </td>
                                <td>
                                  <span class="subbenefit">Subbenefit</span>
                                  <input type='hidden' name='subbenefit[]' value=''>
                                </td>
                                <td>
                                  <select name="benefit_id[]" class="form-select form-select-sm" onchange="getBenefitData(this)"></select>
                                  <input type='hidden' name='benefit_name[]' value=''>
                                </td>
                                <td class="text-area-cont">
                                  <textarea id="description" name="description[]" class="form-control form-control-sm txt-area"></textarea>
                                </td>
                                <td>
                                  <textarea id="pelaksanaan" name="pelaksanaan[]" class="form-control form-control-sm txt-area"></textarea>
                                </td>
                                <td>
                                  <input type="text" class="form-control form-control-sm" id="valben" name="valben[]" placeholder="" value="0" readonly onchange="updateDisabledField(this)">
                                </td>

                                <!-- <td  class="benefit-ket">
                                  <input type="text" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan">
                                </td> -->
                                <input type="hidden" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan">
                                <td>
                                  <input type="number" class="form-control form-control-sm tah1" id="member" name="member[]" placeholder="Quantity Tahun 1" value="0" min="0" onchange="updateDisabledField(this)" <?php if($year == 2 || $year == 3){echo "readonly";} ?> />
                                </td>
                                <td>
                                  <input type="number" class="form-control form-control-sm tah2" id="member2" name="member2[]" placeholder="Quantity Tahun 2" value="0" min="0"  onchange="updateDisabledField(this)" <?php if($program=='cbls1' || $program=='cbls3' || $program=='bsp' || $year == 3){echo "readonly";} ?> />
                                </td>
                                <td> 
                                  <input type="number" class="form-control form-control-sm tah3" id="member3" name="member3[]" placeholder="Quantity Tahun 3" value="0" min="0"  onchange="updateDisabledField(this)" <?php if($program=='cbls1'|| $program=='cbls3' || $program=='bsp'){echo "readonly";} ?>>
                                </td>
                                <td>
                                  <input type="text" class="form-control form-control-sm usage" id="calcValue" name="calcValue[]" placeholder="0" value="0" readonly>
                                  <input type="text" class="form-control form-control-sm usage" name="manval[]" style="display:none;" onchange="updateDisabledField(this)" value="0" placeholder="Input nilai">
                                </td>
                            
                                <input type="hidden" name="valuedefault[]" value=""> 
                                <td>
                                    <button type="button" class="btn_remove btn btn-danger btn-sm" data-row="row<?= $current_row ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                          <?php else:
                            $x = 1;
                            echo '<input type="hidden" name="editmode" value="true">';
                            while ($data = $result->fetch_assoc()): ?>
                              <tr id="row<?= $x; ?>">
                                <td>
                                  <span class="benefit"><?= $data['type'] ?></span>
                                  <input type='hidden' name='benefit[]' value='<?= $data['type'] ?>'>
                                  <input type='hidden' name='id_templates[]' value='<?= $data['id_template'] ?>'>
                                </td>
                                <td>
                                  <span class="subbenefit"><?= $data['subbenefit'] ?></span>
                                  <input type='hidden' name='subbenefit[]' value="<?= $data['subbenefit'] ?>">
                                </td>
                                <td>
                                  <?= $data['benefit_name'] ?>
                                  <input type='hidden' name='benefit_name[]' value='<?= $data['benefit_name'] ?>'>
                                </td>
                                <td class="text-area-cont">
                                  <textarea id="description" name="description[]" class="form-control form-control-sm txt-area"><?= $data['description'] ?></textarea>
                                </td>
                                <?php 
                                  if($data['valueMoney'] == 0){
                                    $new_qty = ((int)$data['qty'] + (int)$data['qty2'] + (int)$data['qty3']) == 0 ? 1 : ((int)$data['qty'] + (int)$data['qty2'] + (int)$data['qty3']);
                                    $data['valueMoney'] = (int)$data['calcValue'] / ($new_qty);
                                  }
                                ?>
                                  <td>
                                    <textarea id="pelaksanaan" name="pelaksanaan[]" class="form-control form-control-sm txt-area"><?= $data['pelaksanaan'] ?></textarea>
                                  </td>
                                  <td>
                                    <input type="text" class="form-control form-control-sm" id="valben" name="valben[]" placeholder="0" onchange="updateDisabledField(this)" value="<?= number_format($data['valueMoney'], '0', ',', '.'); ?>" readonly>
                                  </td>
                                  <!-- <td class="benefit-ket">
                                    <input type="text" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan" value="<?= $data['keterangan'] ?>">
                                  </td> -->
                                  <input type="hidden" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan" value="<?= $data['keterangan'] ?>">
                                  <td>
                                    <input type="number" class="form-control form-control-sm tah1" id="member" name="member[]" placeholder="Quantity Tahun 1" value="<?= $data['qty'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($data['editable_qty'] == '0' || $year == 2 || $year == 3){echo "readonly";} ?>>
                                  </td>
                                  <td>
                                    <input type="number" class="form-control form-control-sm tah2" id="member2" name="member2[]" placeholder="Quantity Tahun 2" value="<?= $data['qty2'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($program=='cbls1'|| $program=='cbls3' || $program=='bsp' || $data['editable_qty'] == '0' || $year == 3){echo "readonly";} ?> >
                                  </td>
                                  <td>
                                    <input type="number" class="form-control form-control-sm tah3" id="member3" name="member3[]" placeholder="Quantity Tahun 3" value="<?= $data['qty3'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($program=='cbls1'|| $program=='cbls3' || $program=='bsp' || $data['editable_qty'] == '0'){echo "readonly";} ?>>
                                  </td>
                                  <td>
                                    <input type="text" class="form-control form-control-sm usage" id="calcValue" name="calcValue[]" placeholder="0" value="<?= number_format($data['calcValue'], '0', ',', '.') ?>" readonly>
                                  </td>
                                  <input type="hidden" name="valuedefault[]" value="<?= $data['valueMoney'] ?>">
                                  <td>
                                      <button type="button" class="btn_remove btn btn-danger btn-sm" data-row="row<?= $x ?>"><i class="fas fa-trash"></i></button>
                                  </td>
                                
                              </tr>
                            <?php $x++; endwhile; ?>
                          <?php endif; ?>

                        </tbody>
                      </table>   


                    </div>
                  </div>
                  <div class="mt-4">
                    <table class="table table-striped table-bordered float-right">
                      <tr>
                        <th>Periode</th>
                        <th>Tahun 1</th>
                        <th>Tahun 2</th>
                        <th>Tahun 3</th>
                      </tr>
                      <tr>
                        <td>Qty per tahun</td>
                        <td><span id="qtyth1">0</span></td>
                        <td><span id="qtyth2">0</span></td>
                        <td><span id="qtyth3">0</span></td>
                      </tr> 
                      <tr>
                        <td>Nilai per tahun</td>
                        <td>
                          <span id="valth1">0</span>
                          <input type="hidden" name="total_benefit1" id="total_benefit1" value="0">
                        </td>
                        <td>
                          <span id="valth2">0</span>
                          <input type="hidden" name="total_benefit2" id="total_benefit2" value="0">
                        </td>
                        <td>
                          <span id="valth3">0</span>
                          <input type="hidden" name="total_benefit3" id="total_benefit3" value="0">
                        </td>
                      </tr> 
                      <tr>
                          <td>Total Alokasi Benefit</td>
                          <td>Rp <?= number_format($sumalok, '0', ',', '.') ?></td>
                          <td><?=  $program == 'prestasi' ? ('Rp ' . number_format($sumalok, '0', ',', '.')) : '' ?></td>
                          <td><?=  $program == 'prestasi' ? ('Rp ' . number_format($sumalok, '0', ',', '.')) : '' ?></td>
                      </tr>
                      <!-- <tr>
                          <td>Total Benefit</td>
                          <td colspan="1"><p id="totalbenefit">Rp 0</p><input type="hidden" name="total_benefit" id="total_benefit" value="0"></td>
                          <td colspan="1"><p id="totalbenefit2">Rp 0</p><input type="hidden" name="total_benefit2" id="total_benefit2" value="0"></td>
                          <td colspan="1"><p id="totalbenefit3">Rp 0</p><input type="hidden" name="total_benefit3" id="total_benefit3" value="0"></td>
                      </tr> -->
                      <tr>
                          <td>Selisih</td>
                          <td><p id="selisihbenefit1"></p><input type="hidden" name="selisih_benefit1" id="selisih_benefit1" value="0"></td>
                          <?php if($program!='cbls1' || $program!='cbls3' || $program!='bsp'):?>
                            <td><p id="selisihbenefit2"></p><input type="hidden" name="selisih_benefit2" id="selisih_benefit2" value="0"></td>
                            <td><p id="selisihbenefit3"></p><input type="hidden" name="selisih_benefit3" id="selisih_benefit3" value="0"></td>
                          <?php endif; ?>
                      </tr>
                    </table>
                    
                    <div class="d-flex justify-content-between mt-4" style="cursor: pointer;">
                      <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="save_as_d" name="save_as_draft" value="1">
                        <label class="form-check-label" for="save_as_d">Check to save as draft</label>
                      </div>
                      <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
                    </div>
                  </div>
                  
                </div>
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
            // row.find('span[name="benefit"]').html(data[0].benefit);
            row.find('span.benefit').html(data[0].benefit);
            // row.find('span[name="subbenefit"]').html(data[0].subbenefit);
            row.find('span.subbenefit').html(data[0].subbenefit);
            row.find('textarea[name="description[]"]').html(data[0].description);
            row.find('input[name="subbenefit[]"]').val(data[0].subbenefit);
            row.find('input[name="benefit_name[]"]').val(data[0].benefit_name);
            row.find('textarea[name="pelaksanaan[]"]').html(data[0].pelaksanaan);
            row.find('input[name="valuedefault[]"]').val(data[0].valueMoney);
            row.find('input[name="valben[]"]').val(formatNumber(data[0].valueMoney));
            row.find('input[name="member[]"]').val(formatNumber(data[0].qty1));
            row.find('input[name="member2[]"]').val(formatNumber(data[0].qty2));
            row.find('input[name="member3[]"]').val(formatNumber(data[0].qty3));
            if(data[0].editable_qty == 0){
              row.find('input[name="member[]"]').prop("readonly", true);
              row.find('input[name="member2[]"]').prop("readonly", true);
              row.find('input[name="member3[]"]').prop("readonly", true);
            }
            var program = '<?= $program ?>';
            if((data[0].benefit_name==="Paket Literasi Menjadi Indonesia" && program=='bsp') || (data[0].benefit_name==="Paket Literasi Bahasa Inggris Storyland 20 series" && program=='bsp') || data[0].subbenefit==="Free Copy" || data[0].benefit_name.includes("ASTA") || data[0].benefit_name.includes("Oxford") || data[0].benefit_name.includes("OXFORD") || data[0].subbenefit==="Bebas Biaya Pengiriman" || data[0].subbenefit==="Deposit untuk Hidayatullah" || data[0].benefit_name == "Material" || data[0].manual_input == "1"){

              row.find('input[name="valben[]"]').prop("readonly", false);
            }else{
              row.find('input[name="valben[]"]').prop("readonly", true);
            }
            
            if(data[0].manual_input == "0"){
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
        hiddenValue = removeNonDigits(hiddenValue);
   
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

      handleInput(row.find('input[name="valben[]"]'));
      var disabledField2 = row.find('input[name="valben[]"]');
      var defaultvalue = row.find('input[name="valuedefault[]"]').val();

      let disabledFieldValue = disabledField2.val().replace(/[^0-9]/g, '');

      var total = parseInt(member1)+parseInt(member2)+parseInt(member3);
      if(defaultvalue != 0){
        disabledField.val(formatNumber(total*defaultvalue));
      }else{
        disabledField.val(formatNumber(total*disabledFieldValue));
      }
      accumulateValues();
    }

    function initializeUpdateDisabledFields() {
        var elements = document.querySelectorAll('input[name="member[]"]');
        
        elements.forEach(function(element) {
            updateDisabledField(element); // Panggil fungsi updateDisabledField untuk setiap elemen
        });
    }

</script>

<script>
  $(document).ready(function(){
    var maxRows = 100; 
    var x = <?= $current_row; ?>;
    var editRowLength = <?= $current_row; ?>;
    
    $('#add_row').click(function(){
      if(x < maxRows){
        x++;
        var newRow = '<tr id="row'+x+'"><td><span class="benefit">Benefit</span><input type="hidden" name="benefit[]" value=""><input type="hidden" name="id_templates[]" value=""></td><td><span class="subbenefit">Subbenefit</span><input type="hidden" name="subbenefit[]" value=""></td><td><select name="benefit_id[]" class="form-select form-select-sm" onchange="getBenefitData(this)"></select></td><input type="hidden" name="benefit_name[]" value=""><td class="text-area-cont"><textarea id="description" name="description[]" cols="16" class="form-control form-control-sm txt-area"></textarea></td><td><textarea id="pelaksanaan" name="pelaksanaan[]" class="form-control form-control-sm txt-area"></textarea></td><td><input type="text" class="form-control form-control-sm" id="valben" name="valben[]"  onchange="updateDisabledField(this)"  placeholder="0" value="0" readonly></td><input type="hidden" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan"><td><input type="number" class="form-control form-control-sm tah1" id="member" name="member[]" min="0" placeholder="Quantity Tahun 1" value="0" onchange="updateDisabledField(this)" <?php if($year == 2 || $year == 3){echo "readonly";} ?> /></td><td><input type="number" class="form-control form-control-sm tah2" id="member2" name="member2[]" min="0" placeholder="Quantity Tahun 2" value="0" onchange="updateDisabledField(this)"  <?php if($program=='cbls1' || $program=='cbls3' || $program=='bsp' || $year == 3){echo "readonly";} ?>></td><td><input type="number" class="form-control form-control-sm tah3" id="member3" min="0" name="member3[]" placeholder="Quantity Tahun 3" value="0" onchange="updateDisabledField(this)"  <?php if($program=='cbls1' || $program=='cbls3' ||$program=='bsp'){echo "readonly";} ?>></td><td><input type="text" class="form-control form-control-sm usage" id="calcValue" name="calcValue[]"  placeholder="0" value="0" readonly><input type="text" class="form-control form-control-sm usage" value="0" name="manval[]" style="display:none;" onchange="updateDisabledField(this)" placeholder="Input nilai"></td><input type="hidden" name="valuedefault[]" value=""><td class="action-row" data-action-row="row'+x+'"><button type="button" class="btn_remove btn btn-danger btn-sm" data-row="row'+x+'"><i class="fas fa-trash"></i></button></td></tr>';
        $('#input_form').append(newRow); 
        populateDropdown('row'+x);
      }
    });

    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
      accumulateValues();

    });

    $('#draft_form').submit(function(e) {
      e.preventDefault();
      Swal.fire({
        title: "Are you sure?",
        text: "Make sure the data is correct before submitting it!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, save it!"
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Processing...",
            html: '<div class="spinner"></div>', // You can use a CSS spinner here
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            didOpen: () => {
              Swal.showLoading(); // This shows a built-in loading animation
            }
          });

          $(this).unbind('submit').submit();
        }
      });
    })

    // Populate dropdown options
    function populateDropdown(rowId) {
      var selectedTemplate = $('select[name="benefit_id[]"]').map(function() {
          return $(this).val();
      }).get();

      selectedTemplate = selectedTemplate.filter(el => el)
      $.ajax({
        url: 'get_benefits.php',
        type: 'POST',
        data: {
            program: '<?= $program ?>',
            selectedTemplate: selectedTemplate
        },
        success: function(data) {
          var dropdown = $('#' + rowId + ' select');
          dropdown.html(data);
          $('#' + rowId + ' select').select2({
            placeholder: 'Select a benefit',
            templateResult: formatGroupItems,
            closeOnSelect: false,
          });

          $(document).on('mouseenter', '.select2-results__option', function () {
              const title = $(this).attr('title');
              if (title) {
                  $(this).tooltip({ title, placement: 'top' }).tooltip('show');
              }
          });
        },
        error: function(xhr, status, error) {
          console.log('error', error);
          console.log('status', status);
          console.log('xhr', xhr);
        }
      });
    }

    // Custom function to format the group items and make them clickable
    function formatGroupItems(data) {

      if (data.element && data.element.tagName === 'OPTGROUP') {
          return $(`<div class="select2-optgroup-label" style=" color: #333; padding: 5px; cursor: pointer;">
                      <b>${data.text}</b>
                  </div>`);
      }

      if (data.element && data.element.tagName === 'OPTION') {
        let colorHighlight = $(data.element).attr('data-color'); 
        if(colorHighlight) {
          return $(`<span style="background-color: #${colorHighlight}; padding: 5px; color: white">${data.text}</span>`);
        }

        // You can use this value to customize how each option is rendered
        // return $(`<span style="display: block; background-color: #f5f5f5; padding: 5px; color: blue;">${data.text}</span> - ${colorHighlight}`);
        
      }

      return data.text;
    }

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

    populateDropdown('row' + <?= $current_row ?>);
    $('#submt').prop('disabled', true);
    initializeUpdateDisabledFields();
  });


  $(document).on('mousedown', 'select[name="benefit_id[]"]', function(event) {
      // Lakukan sesuatu saat select ditekan mouse
      var row = $(this).closest('tr');
      var rowId = row.attr('id');

      let selected = row.find('select[name="benefit_id[]"]').val()
      
      var selectedTemplate = $('select[name="benefit_id[]"]').map(function() {
          return $(this).val();
      }).get();

      selectedTemplate = selectedTemplate.filter(el => el && el != selected);

      $.ajax({
        url: 'get_benefits.php',
        type: 'POST',
        data: {
            program: '<?= $program ?>',
            selectedTemplate: selectedTemplate,
            selected: selected
        },
        success: function(data) {
          var dropdown = $('#' + rowId + ' select');
          dropdown.html(data);
        }
      });
  });
  
  function formatAndValidate(input, alokasi, row) {
    var cleanedInput = input.replace(/[^0-9]/g, '');
    var number = parseFloat(cleanedInput) || 0;

    // Get member values from the row
    var member1 = parseInt(row.find('input[name="member[]"]').val()) || 0;
    var member2 = parseInt(row.find('input[name="member2[]"]').val()) || 0;
    var member3 = parseInt(row.find('input[name="member3[]"]').val()) || 0;

    let total_member1 = member1 * number;
    let total_member2 = member2 * number;
    let total_member3 = member3 * number;

    let max_alokasi = alokasi * 0.3

    if(total_member1 > max_alokasi || total_member2 > max_alokasi || total_member3 > max_alokasi) {
      alert('Total nilai tidak boleh lebih dari 30% dari alokasi.');
      return '0';
    }

    var formatted = number.toLocaleString('id-ID', { maximumFractionDigits: 2 });

    return number;
  }



  // Event saat nilai input berubah
  // $(document).on('input', 'input[name="valben[]"]', function(event) {
  //     var row = $(this).closest('tr');
  //     var rowId = row.attr('id');
  //     let selected = row.find('select[name="benefit_id[]"]').val()

  //     var value = $(this).val();
  //     let alokasi = <?= $sumalok ?? 0 ?>;
  //     if(selected == 72 || selected == 391) {
  //       var formattedValue = formatAndValidate(value, alokasi);
  //       $(this).val(formattedValue);
  //     }else {
  //       var cleanedInput = value.replace(/[^0-9]/g, '');
  //       var number = parseFloat(cleanedInput);

  //       let formated = number.toLocaleString('id-ID', { maximumFractionDigits: 2 })
  //       $(this).val(formated);
  //     }
     
  // });

  $(document).on('input', 'input[name="valben[]"]', function(event) {
    handleInput($(this));
  });

  const makeMaxIds = <?= json_encode($make_max_ids) ?>;

  function handleInput(inputElement) {
    var row = inputElement.closest('tr');
    let selected = row.find('select[name="benefit_id[]"]').val();

    var value = inputElement.val();
    let alokasi = <?= $sumalok ?? 0 ?>;
    if (makeMaxIds.includes(parseInt(selected))) {
        var formattedValue = formatAndValidate(value, alokasi, row);
        inputElement.val(formattedValue);
    } else {
        var cleanedInput = value.replace(/[^0-9]/g, '');
        var number = parseFloat(cleanedInput);

        let formatted = number.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        inputElement.val(formatted);
    }
  }


</script>
<?php include 'footer.php'; ?>