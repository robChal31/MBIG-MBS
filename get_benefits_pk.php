<?php
  include 'db_con.php';

  /* ===================== DATA LOGIC (UNCHANGED) ===================== */
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

<style>

.benefit-title{
  font-size:.85rem;
  font-weight:600;
  color:#495057;
  margin-bottom:12px;
}

.benefit-table table{
  font-size:.75rem;
  border-collapse:separate;
  border-spacing:0;
}

.benefit-table thead td{
  background: #f8f9fa;
  font-size: .7rem !important;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .03em;
  padding: 8px;
  border-bottom: 1px solid #dee2e6;
  white-space: nowrap;
}

.benefit-table tbody td{
  padding:8px;
  vertical-align:top;
  border-bottom:1px solid #f1f3f5;
}

.benefit-table tbody tr:hover{
  background:#f9fafb;
}

.benefit-table span{
  display:block;
  line-height:1.35;
  color:#343a40;
}

.benefit-table .btn_remove{
  padding:3px 7px;
  font-size:.65rem;
}

.benefit-actions{
  margin-top:12px;
  text-align:right;
}

.benefit-actions .btn{
  font-size:.75rem;
  padding:6px 18px;
  border-radius:8px;
}

td span{
  font-size: .75rem !important;
}
</style>

<div class="row">
  <div class="col-12">
    <?php if($id_draft || $program) { ?>

      <div class="benefit-wrapper border">

        <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
        <input type="hidden" value="<?= $program ?>" name="program">

        <div class="table-responsive benefit-table p-2">
          <table class="table table-borderless dataTable no-footer" id="input_form">
            <thead>
              <td>Benefit</td>
              <td>Sub</td>
              <td>Nama</td>
              <td style="width:25%">Deskripsi</td>
              <td style="width:25%">Pelaksanaan</td>
              <td>Th 1</td>
              <td>Th 2</td>
              <td>Th 3</td>
              <td></td>
            </thead>
            <tbody>
            <?php $row = 1; foreach($data_templates as $data_template) : ?>
              <tr id="row<?= $row ?>">
                <td>
                  <span><?= ucfirst($data_template['benefit']); ?></span>
                  <input type="hidden" name="benefit[]" value="<?= $data_template['benefit'] ?>">
                  <input type="hidden" name="id_templates[]" value="<?= $data_template['id_template_benefit'] ?>">
                </td>
                <td>
                  <span><?= ucfirst($data_template['subbenefit']); ?></span>
                  <input type="hidden" name="subbenefit[]" value="<?= $data_template['subbenefit'] ?>">
                </td>
                <td>
                  <span><?= ucfirst($data_template['benefit_name']); ?></span>
                  <input type="hidden" name="benefit_name[]" value="<?= $data_template['benefit_name'] ?>">
                </td>
                <td class="benefit-desc">
                  <input type="hidden" name="description[]" value="<?= $data_template['description'] ?>">
                  <span><?= $data_template['description'] ?></span>
                </td>
                <td class="benefit-desc">
                  <input type="hidden" name="pelaksanaan[]" value="<?= $data_template['pelaksanaan'] ?>">
                  <span><?= $data_template['pelaksanaan'] ?></span>
                </td>
                <td>
                  <input type="hidden" name="qty1[]" value="<?= $data_template['qty1'] ?>">
                  <span><?= $data_template['qty1'] ?></span>
                </td>
                <td>
                  <input type="hidden" name="qty2[]" value="<?= $data_template['qty2'] ?>">
                  <span><?= $data_template['qty2'] ?></span>
                </td>
                <td>
                  <input type="hidden" name="qty3[]" value="<?= $data_template['qty3'] ?>">
                  <span><?= $data_template['qty3'] ?></span>
                </td>
                <td>
                  <?php if($data_template['optional'] == 1) { ?>
                    <button type="button" class="btn_remove btn btn-outline-danger btn-sm" data-row="row<?= $row ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  <?php } ?>
                </td>
              </tr>
            <?php $row++; endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="benefit-actions m-4">
          <button type="submit" class="btn btn-primary" id="submt">
            <span class="btn-icon">
              <i class="bi bi-arrow-right"></i>
            </span>  
            Submit
          </button>
        </div>
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
    
    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
    });

    // populateDropdown('row' + <?= $current_row ?>);
  });

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
  }

  $('#submt').on('click', function (e) {
    const form = document.getElementById('input_form_benefit');
    const $btn = $(this);

    e.preventDefault();

    // reset error
    $(form).find('.is-invalid').removeClass('is-invalid');
    $('.select2-selection').removeClass('is-invalid');

    let invalidFields = [];
    let firstInvalid = null;

    // =========================
    // CEK BENEFIT LIST ADA / TIDAK
    // =========================
    if ($('#input_form tbody tr').length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Benefit belum dipilih',
        text: 'Silakan pilih program dan pastikan benefit sudah muncul.'
      });
      return;
    }

    // =========================
    // HTML5 REQUIRED VALIDATION
    // =========================
    $(form).find('[required]').each(function () {
      const el = this;

      if (!el.checkValidity()) {
        if (!firstInvalid) firstInvalid = el;

        let label =
          $(el).closest('.col-md-6, .col-md-12')
            .find('label')
            .first()
            .text()
            .trim() || el.name;

        invalidFields.push(label);

        // select2 handling
        if ($(el).hasClass('select2')) {
          $(el)
            .next('.select2-container')
            .find('.select2-selection')
            .addClass('is-invalid');
        } else {
          $(el).addClass('is-invalid');
        }
      }
    });

    // =========================
    // JIKA ADA ERROR
    // =========================
    if (invalidFields.length > 0) {
      Swal.fire({
        icon: 'error',
        title: 'Form belum lengkap',
        html: `
          <div style="text-align:left">
            <p>Field berikut wajib diisi:</p>
            <ul>
              ${invalidFields.map(f => `<li>${f}</li>`).join('')}
            </ul>
          </div>
        `
      });

      if (firstInvalid) {
        $('html, body').animate({
          scrollTop: $(firstInvalid).offset().top - 120
        }, 300);
      }

      return;
    }

    // =========================
    // VALID → SUBMIT
    // =========================
    $btn.prop('disabled', true);
    $btn.find('.btn-icon').addClass('d-none');
    $btn.append('<span class="spinner-border spinner-border-sm ms-2"></span>');

    form.submit();
  });

</script>