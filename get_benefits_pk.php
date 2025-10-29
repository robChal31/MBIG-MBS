<?php
include 'db_con.php';

$current_row  = 1;
$id_draft     = '';
if($_GET['id_draft'] && $_GET['id_draft'] != '') { 
  $id_draft = $_GET['id_draft'];
  $sql      = "SELECT db.*, sc.name as school_name2
              FROM draft_benefit as db 
              LEFT JOIN schools as sc on sc.id = db.school_name
              where id_draft = $id_draft";
  $result   = mysqli_query($conn,$sql);
  
  while ($data = $result->fetch_assoc()){
    $program                  = $data['program'];
    $sumalok                  = $data['alokasi'];
    $total_benefit            = $data['total_benefit'];
    $school_name              = $data['school_name2'];
    $selisih_benefit          = $data['selisih_benefit'];
  }

  $sql          = "SELECT a.*, b.* FROM draft_benefit_list a 
                    LEFT JOIN draft_template_benefit AS b on a.id_template = b.id_template_benefit   
                    WHERE a.id_draft = '$id_draft'
                    ORDER BY a.id_benefit_list DESC";
  $result       = mysqli_query($conn, $sql);
  $current_row  = mysqli_num_rows($result);
  $data_templates = [];
  while ($row = mysqli_fetch_assoc($result)) {
      $data_templates[] = $row;
  }

}else if(!$_GET['id_draft'] && $_GET['program']){
  $program  = $_GET['program'];

  $query_program = "SELECT code FROM programs WHERE (name = '$program' OR code = '$program') AND is_active = 1 LIMIT 1";

  $exec_program = mysqli_query($conn, $query_program);

  $program_code = false;
  
  if ($exec_program && mysqli_num_rows($exec_program) > 0) {
      $prog = mysqli_fetch_assoc($exec_program);
      $program_code = $prog['code'];
  }

  $filter_program_q = $program_code ? "AND avail like '%$program_code%' " : '';

  $query_template = "SELECT * FROM `draft_template_benefit` WHERE is_active = 1 $filter_program_q order by id_template_benefit ASC";
  $result_template = mysqli_query($conn, $query_template);
  $data_templates = [];
  while ($row = mysqli_fetch_assoc($result_template)) {
      $data_templates[] = $row;
  }
}

$program = strtolower($program);
?>

    <div class="row">
      <div class="col-12">
        <?php
          if($id_draft || $program) { ?>
            <h6 class="mb-5">Benefits</h6>

            <!-- <div class="d-flex justify-content-end">
              <div>
                <span style="font-size: .75rem; display: inline-block;">click + for adding row</span>
                <div class="d-flex justify-content-end">
                  <button type='button' class="add-button btn btn-success me-2" style="display:block" id='add_row'>
                      <i class="fas fa-plus"></i>
                  </button>
                </div>
                
              </div>
            </div> -->

            <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
            <div class="row">
              <input type="hidden" value="<?= $program ?>" name="program">
              <table class="table table-striped table-bordered dataTable no-footer" id="input_form">
                <thead>
                    <td>Benefit</td>
                    <td>Sub Benefit</td>
                    <td>Nama Benefit</td>
                    <td style="width: 25%">Deskripsi</td>
                    <td style="width: 25%">Pelaksanaan</td>
                    <td>Qty Th 1</td>
                    <td>Qty Th 2</td>
                    <td>Qty Th 3</td>
                    <td>Action</td>
                </thead>
                <tbody>
                  <?php
                    $row = 1; 
                    foreach($data_templates as $data_template) : ?>
                      <tr id="row<?= $row ?>">
                          <td>
                            <span><?= ucfirst($data_template['benefit']); ?></span>
                            <input type='hidden' name='benefit[]' value='<?= $data_template['benefit'] ?>'>
                            <input type='hidden' name='id_templates[]' value='<?= $data_template['id_template_benefit'] ?>'>
                          </td>
                          <td>
                            <span><?= ucfirst($data_template['subbenefit']); ?></span>
                            <input type='hidden' name='subbenefit[]' value='<?= $data_template['subbenefit'] ?>'>
                          </td>
                          <td>
                            <!-- <select name="benefit_id[]" class="form-select form-select-sm" onchange="getBenefitData(this)"></select> -->
                            <span><?= ucfirst($data_template['benefit_name']); ?></span>
                            <input type='hidden' name='benefit_name[]' value='<?= $data_template['benefit_name'] ?>'>
                          </td>
                          <td class="benefit-desc">
                            <input type="hidden" id="description" name="description[]" value="<?= $data_template['description'] ?>" />
                            <span><?= $data_template['description'] ?></span>
                          </td>
                          <td class="benefit-desc">
                            <input type="hidden" id="pelaksanaan" name="pelaksanaan[]" value="<?= $data_template['pelaksanaan'] ?>" />
                            <span><?= $data_template['pelaksanaan'] ?></span>
                          </td>
                          <td>
                            <input type="hidden" class="form-control form-control-sm tah1" id="qty1" name="qty1[]" placeholder="Quantity Tahun 1" value="<?= $data_template['qty1'] ?>">
                            <span><?= $data_template['qty1'] ?></span>
                          </td>
                          <td>
                            <input type="hidden" class="form-control form-control-sm tah2" id="qty2" name="qty2[]" placeholder="Quantity Tahun 2" value="<?= $data_template['qty2'] ?>">
                            <span><?= $data_template['qty2'] ?></span>
                          </td>
                          <td> 
                            <input type="hidden" class="form-control form-control-sm tah3" id="qty3" name="qty3[]" placeholder="Quantity Tahun 3" value="<?= $data_template['qty3'] ?>">
                            <span><?= $data_template['qty3'] ?></span>
                          </td>

                          <td>
                              <?php if($data_template['optional'] == 1) { ?>
                                <button type="button" class="btn_remove btn btn-danger btn-sm" data-row="row<?= $row ?>"><i class="fas fa-trash"></i></button>
                              <?php } ?>
                          </td>
                      </tr>
                  <?php $row++; endforeach ; ?>
                </tbody>
              </table>   
            </div>
            <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
              <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
            </div>
        <?php } else { ?>
          <?php if($program == '') : ?>
            <div class="alert alert-info">Select a Program</div>
          <?php else: ?>
            <div class="alert alert-danger">Program or Saved Template Invalid</div>
          <?php endif; ?>
        <?php } ?>
      </div>
    </div>

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
        row.find('.ben').html(data[0].benefit);
        row.find('.sub_ben').html(data[0].subbenefit);
        row.find('input[name="description[]"]').val(data[0].description);
        row.find('input[name="subbenefit[]"]').val(data[0].subbenefit);
        row.find('input[name="benefit_name[]"]').val(data[0].benefit_name);
        row.find('input[name="qty1[]"]').val(data[0].qty1);
        row.find('input[name="qty2[]"]').val(data[0].qty2);
        row.find('input[name="qty3[]"]').val(data[0].qty3);
        row.find('.ben_qty1').html(data[0].qty1);
        row.find('.ben_qty2').html(data[0].qty2);
        row.find('.ben_qty3').html(data[0].qty3);
        row.find('.ben_desc').html(data[0].description);
        row.find('.ben_pel').html(data[0].pelaksanaan);
        row.find('input[name="pelaksanaan[]"]').val(data[0].pelaksanaan);
      }
    });

  }

</script>

<script>
  $(document).ready(function(){
    var x = <?= $current_row; ?>;
    
    $('#add_row').click(function(){
        x++;
        $('#input_form').append(addRow(x)); 
    });

    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
    });

    // populateDropdown('row' + <?= $current_row ?>);
  });

  function addRow(x) {
    var newRow = '<tr id="row'+x+'"><td><span class="ben">Benefit</span><input type="hidden" name="benefit[]" value=""><input type="hidden" name="id_templates[]" value=""></td><td><span class="sub_ben">Subbenefit</span><input type="hidden" name="subbenefit[]" value=""></td><td><input type="hidden" name="benefit_name[]" value=""><select name="benefit_id[]" class="form-select form-select-sm" onchange="getBenefitData(this)"></select></td><td class="benefit-desc"><input type="hidden" id="description" name="description[]" value="" /><span class="ben_desc"></span></td><td class="benefit-desc"><input type="hidden" id="pelaksanaan" name="pelaksanaan[]" value="" /><span class="ben_pel"></span></td><td><input type="hidden" class="form-control form-control-sm tah1" id="qty1" name="qty1[]" placeholder="Quantity Tahun 1" value=""><span class="ben_qty1"></span></td><td><input type="hidden" class="form-control form-control-sm tah2" id="qty2" name="qty2[]" placeholder="Quantity Tahun 2" value=""><span class="ben_qty2"></span></td><td><input type="hidden" class="form-control form-control-sm tah3" id="qty3" name="qty3[]" placeholder="Quantity Tahun 3" value=""><span class="ben_qty3"></span></td><td class="action-row" data-action-row="row'+x+'"><button type="button" class="btn_remove btn btn-danger btn-sm" data-row="row'+x+'"><i class="fas fa-trash"></i></button></td></tr>';
    populateDropdown('row'+x);  
    return newRow;
  }

  function populateDropdown(rowId) {
    let selectedTemplate = [];
    $.ajax({
      url: 'get_benefits.php',
      type: 'POST',
      data: {
        program : '<?= $program ?>',
        selectedTemplate : selectedTemplate
      },
      success: function(data) {
        var dropdown = $('#' + rowId + ' select');
        dropdown.html(data);
      }
    });

    $('#input_form_benefit').submit(function(e) {
      $('#submt').prop('disabled', true);
    })
  }

</script>