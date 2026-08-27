<?php
  include 'db_con.php';
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);

  /* ===================== DATA LOGIC (UNCHANGED) ===================== */
  $current_row    = 1;
  $id_draft       = ISSET($_GET['id_draft']) ? $_GET['id_draft'] : NULL;
  $program        = ISSET($_GET['program']) ? $_GET['program'] : NULL;
  $count_changes  = $_GET['countChanges'] ?? 0;

  $levels         = ISSET($_GET['levels']) ? $_GET['levels'] : [];
  $subjects       = ISSET($_GET['subjects']) ? $_GET['subjects'] : [];

  $data_templates = [];

  if($id_draft && $count_changes == 0) {

    $query_template_q = "SELECT dbl.id_benefit_list, dbl.id_draft, dbl.id_template, b.id_template_benefit, 
                        b.benefit, b.subbenefit, b.benefit_name, b.description, b.pelaksanaan, b.qty1, b.qty2, b.qty3, b.multiple_subject, b.multiple_level, b.optional,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT bs.subject_id SEPARATOR ',')
                             FROM benefit_subjects bs
                             WHERE bs.draft_benefit_list_id = dbl.id_benefit_list
                            ), ''
                        ) AS subject_ids,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT bol.level_id SEPARATOR ',')
                             FROM benefit_org_levels bol
                             WHERE bol.draft_benefit_list_id = dbl.id_benefit_list
                            ), ''
                        ) AS level_ids
                    FROM draft_benefit_list dbl
                    LEFT JOIN draft_template_benefit b ON dbl.id_template = b.id_template_benefit   
                    WHERE dbl.id_draft = '$id_draft'
                    ORDER BY dbl.id_benefit_list DESC";
    $result           = mysqli_query($conn, $query_template_q);
    $current_row      = mysqli_num_rows($result);

    while ($row = mysqli_fetch_assoc($result)) {
        $data_templates[] = $row;
    }

  }else if((!$id_draft && $program) || ($id_draft && $count_changes > 0)) {

    $query_program = "SELECT code FROM programs WHERE (name = '$program' OR code = '$program') AND is_active = 1 LIMIT 1";
    $exec_program = mysqli_query($conn, $query_program);

    $program_code = false;
    if ($exec_program && mysqli_num_rows($exec_program) > 0) {
      $prog = mysqli_fetch_assoc($exec_program);
      $program_code = $prog['code'];
    }

    $filter_program_q = $program_code ? "AND avail like '%$program_code%' " : '';
    $query_template_q = "SELECT * FROM `draft_template_benefit` 
                        WHERE is_active = 1 $filter_program_q 
                        AND (
                            subject IS NULL 
                            OR subject = ''
                            OR subject IN ('" . implode("','", $subjects) . "')
                        )
                        ORDER BY id_template_benefit ASC";

    $result_template = mysqli_query($conn, $query_template_q);

    while ($row = mysqli_fetch_assoc($result_template)) {
      $data_templates[] = $row;
    }

  }

  $level_ids = [];
  $subject_ids = [];
  $all_levels = [];
  $all_subjects = [];
  if(count($levels) > 0){
    $level_ids_query = implode(',', $levels);
    $query_level = "SELECT * FROM levels WHERE id IN ($level_ids_query)";
    $exec_level = mysqli_query($conn, $query_level);

    if ($exec_level && mysqli_num_rows($exec_level) > 0) {
      $all_levels = mysqli_fetch_all($exec_level, MYSQLI_ASSOC);
    }
  }

  if(count($subjects) > 0){
    $subject_ids_query = implode(',', $subjects);
    $query_subject = "SELECT * FROM subjects WHERE id IN ($subject_ids_query)";
    $exec_subject = mysqli_query($conn, $query_subject);

    if ($exec_subject && mysqli_num_rows($exec_subject) > 0) {
      $all_subjects = mysqli_fetch_all($exec_subject, MYSQLI_ASSOC);
    }
  }

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

  /* Container multiple */
  .select2-container--default .select2-selection--multiple {
      display: flex !important;
      align-items: flex-start !important;
      justify-content: flex-start !important;
      flex-wrap: wrap !important;
      min-height: 38px;
  }

  /* List selected item */
  .select2-container--default .select2-selection--multiple .select2-selection__rendered {
      display: flex !important;
      align-items: flex-start !important;
      justify-content: flex-start !important;
      flex-wrap: wrap !important;
      width: 100%;
      margin: 0;
      padding: 2px 5px;
      text-align: left !important;
  }

  /* Setiap tag */
  .select2-container--default .select2-selection--multiple .select2-selection__choice {
      margin: 2px !important;
  }

  /* Search field */
  .select2-container--default .select2-selection--multiple .select2-search--inline {
      display: inline-flex !important;
      align-items: flex-start !important;
  }
  
</style>

<div class="row">
  <div class="col-12">
    <?php if(count($data_templates)) { ?>

      <div class="benefit-wrapper border">

        <div class="table-responsive benefit-table p-2">
          <table class="table table-borderless dataTable no-footer" id="input_form">
            <thead>
              <!-- <td>Benefit</td>
              <td>Sub</td> -->
              <td style="width:15%">Benefit Nama</td>
              <td style="width:25%">Deskripsi</td>
              <td>Pelaksanaan</td>
              <td style="width: 15%">Subjek</td>
              <td style="width: 15%">Level</td>
              <td>Th 1</td>
              <td>Th 2</td>
              <td>Th 3</td>
              <td></td>
            </thead>
            <tbody>
            <?php $row = 1; foreach($data_templates as $data_template) : ?>
              <tr id="row<?= $row ?>">
                <input type="hidden" name="benefit[]" value="<?= $data_template['benefit'] ?>">
                <input type="hidden" name="id_templates[]" value="<?= $data_template['id_template_benefit'] ?>">
                <input type="hidden" name="subbenefit[]" value="<?= $data_template['subbenefit'] ?>">

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
                  <?php if($data_template['multiple_subject']) { 
                          $selected_subjects = !empty($data_template['subject_ids']) ? explode(',', $data_template['subject_ids']) : [];
                  ?>
                    <select name="subject_<?= $data_template['id_template_benefit'] ?>[]" class="select2" multiple>
                      <?php foreach($all_subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" <?= in_array($subject['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } else { ?>
                    -
                  <?php } ?>
                </td>
                <td>
                  <?php if($data_template['multiple_level']) { 
                          $selected_levels = !empty($data_template['level_ids']) ? explode(',', $data_template['level_ids']) : [];
                  ?>
                    <select name="level_<?= $data_template['id_template_benefit'] ?>[]" class="select2" multiple>
                      <?php foreach($all_levels as $level_row): ?>
                        <option value="<?= $level_row['id'] ?>" <?= in_array($level_row['id'], $selected_levels) ? 'selected' : '' ?>><?= htmlspecialchars($level_row['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } else { ?>
                    -
                  <?php } ?>
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
          <button type="submit" class="btn btn-primary" id="submt" >
            <span class="btn-icon">
              <i class="bi bi-arrow-right"></i>
            </span>  
            Submit
          </button>
        </div>
      </div>

    <?php } else { ?>
      <div style="height: 100px; display: flex; align-items: center; justify-content: center">
        <?php if($program == '') : ?>
          <div class="alert alert-info">Select a Program</div>
        <?php else: ?>
          <div class="alert alert-danger">Program or Saved Template Invalid</div>
        <?php endif; ?>
      </div>
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

</script>

<script>
  $(document).ready(function(){
    var x = <?= $current_row; ?>;
    $('.select2').select2({
      width: '100%',
    });

    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
    });

  });

  $('#submt').on('click', function (e) {
    const form = document.getElementById('input_form_benefit');
    const $btn = $(this);
    isSubmitting = true;
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