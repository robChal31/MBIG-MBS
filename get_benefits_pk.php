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
                        b.benefit, b.subbenefit, b.benefit_name, b.description, b.pelaksanaan, b.qty1, b.qty2, b.qty3, b.multiple_subject, 
                        b.multiple_level, b.optional, b.book_selection,
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
                        ) AS level_ids,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT bbs.book_series_id SEPARATOR ',')
                             FROM benefit_book_series bbs
                             WHERE bbs.draft_benefit_list_id = dbl.id_benefit_list
                            ), ''
                        ) AS book_series_ids,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT alb.level_id SEPARATOR ',')
                             FROM banned_level_benefits as alb
                             WHERE alb.id_template_benefit = b.id_template_benefit
                            ), ''
                        ) AS banned_level_ids,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT abb.book_series_id SEPARATOR ',')
                             FROM allowed_book_benefits as abb
                             WHERE abb.id_template_benefit = b.id_template_benefit
                            ), ''
                        ) AS allowed_book_ids
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

    $filter_program_q = $program_code ? "AND b.avail like '%$program_code%' " : '';
    $query_template_q = "SELECT b.*, 
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT alb.level_id SEPARATOR ',')
                             FROM banned_level_benefits as alb
                             WHERE alb.id_template_benefit = b.id_template_benefit
                            ), ''
                        ) AS banned_level_ids,
                        COALESCE(
                            (SELECT GROUP_CONCAT(DISTINCT abb.book_series_id SEPARATOR ',')
                             FROM allowed_book_benefits as abb
                             WHERE abb.id_template_benefit = b.id_template_benefit
                            ), ''
                        ) AS allowed_book_ids
                        FROM `draft_template_benefit` as b
                        WHERE b.is_active = 1 $filter_program_q 
                        AND (
                            b.subject IS NULL 
                            OR b.subject = ''
                            OR b.subject IN ('" . implode("','", $subjects) . "')
                        )
                        ORDER BY b.id_template_benefit ASC";

    $result_template = mysqli_query($conn, $query_template_q);

    while ($row = mysqli_fetch_assoc($result_template)) {
      $data_templates[] = $row;
    }

  }

  $level_ids = [];
  $subject_ids = [];
  $all_levels = [];
  $all_subjects = [];
  $book_series = [];
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

  $query_book_series = "SELECT * FROM book_series WHERE is_active = 1 ORDER BY name ASC";
  $exec_book_series = mysqli_query($conn, $query_book_series);

  if ($exec_book_series && mysqli_num_rows($exec_book_series) > 0) {
    $book_series = mysqli_fetch_all($exec_book_series, MYSQLI_ASSOC);
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
              <td class="d-none">Pelaksanaan</td>
              <td style="width: 15%">Subjek</td>
              <td style="width: 15%">Level</td>
              <td style="width: 15%">Book</td>
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
                <td class="benefit-desc d-none">
                  <input type="hidden" name="pelaksanaan[]" value="<?= $data_template['pelaksanaan'] ?>">
                  <span><?= $data_template['pelaksanaan'] ?></span>
                </td>
                <td>
                  <?php if($data_template['multiple_subject'] == 1) { 
                          $selected_subjects = !empty($data_template['subject_ids']) ? explode(',', $data_template['subject_ids']) : [];
                  ?>
                    <select name="subject_<?= $data_template['id_template_benefit'] ?>[]" class="select2" multiple required>
                      <?php foreach($all_subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>" <?= in_array($subject['id'], $selected_subjects) ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } else { ?>
                    -
                  <?php } ?>
                </td>
                <td>
                  <?php if($data_template['multiple_level'] == 1) { 
                          $selected_levels = !empty($data_template['level_ids']) ? explode(',', $data_template['level_ids']) : [];
                          $banned_levels = $data_template['banned_level_ids'] ? explode(',', $data_template['banned_level_ids']) : [];
                          $allowed_levels = array_filter($all_levels, function($level) use ($banned_levels) {
                            return !in_array($level['id'], $banned_levels);
                          });
                  ?>
                    <select name="level_<?= $data_template['id_template_benefit'] ?>[]" class="select2" multiple required>
                      <?php foreach($allowed_levels as $level_row): ?>
                        <option value="<?= $level_row['id'] ?>" <?= in_array($level_row['id'], $selected_levels) ? 'selected' : '' ?>><?= htmlspecialchars($level_row['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } else { ?>
                    -
                  <?php } ?>
                </td>
                <td>
                  <?php if($data_template['book_selection'] == 1) { 
                          $selected_book_series_id = !empty($data_template['book_series_ids']) ? explode(',', $data_template['book_series_ids']) : [];
                          $allowed_book_ids = !empty($data_template['allowed_book_ids']) ? explode(',', $data_template['allowed_book_ids']) : [];
                          
                          $allowed_book_series = array_filter($book_series, function($book) use ($allowed_book_ids) {
                              return in_array($book['id'], $allowed_book_ids);
                          });
                  ?>
                    <select name="book_<?= $data_template['id_template_benefit'] ?>[]" class="select2" multiple required>
                      <?php foreach($allowed_book_series as $book_row): ?>
                        <option value="<?= $book_row['id'] ?>" <?= in_array($book_row['id'], $selected_book_series_id) ? 'selected' : '' ?>><?= htmlspecialchars($book_row['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php } else { ?>
                    -
                  <?php } ?>
                </td>
                <td>
                    <input type="hidden" name="qty1_default[]" value="<?= $data_template['qty1'] ?>">
                    <input type="hidden" name="qty1[]" value="<?= $data_template['qty1'] ?>">
                    <span class="qty1-display"><?= $data_template['qty1'] ?></span>
                </td>
                <td>
                    <input type="hidden" name="qty2_default[]" value="<?= $data_template['qty2'] ?>">
                    <input type="hidden" name="qty2[]" value="<?= $data_template['qty2'] ?>">
                    <span class="qty2-display"><?= $data_template['qty2'] ?></span>
                </td>
                <td>
                    <input type="hidden" name="qty3_default[]" value="<?= $data_template['qty3'] ?>">
                    <input type="hidden" name="qty3[]" value="<?= $data_template['qty3'] ?>">
                    <span class="qty3-display"><?= $data_template['qty3'] ?></span>
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

    function updateQtyBySelection(row) {

      let subjectCount = row.find('select[name^="subject_"]').val()?.length || 1;
      let levelCount = row.find('select[name^="level_"]').val()?.length || 1;

      let multiplier = subjectCount * levelCount;
      // Ambil default dari hidden
      let defaultQty1 = parseInt(row.find('input[name="qty1_default[]"]').val()) || 0;
      let defaultQty2 = parseInt(row.find('input[name="qty2_default[]"]').val()) || 0;
      let defaultQty3 = parseInt(row.find('input[name="qty3_default[]"]').val()) || 0;
      
      // Update span tampilan
      row.find('.qty1-display').text(defaultQty1 * multiplier);
      row.find('.qty2-display').text(defaultQty2 * multiplier);
      row.find('.qty3-display').text(defaultQty3 * multiplier);
      
      // Update hidden input buat submit
      row.find('input[name="qty1[]"]').val(defaultQty1 * multiplier);
      row.find('input[name="qty2[]"]').val(defaultQty2 * multiplier);
      row.find('input[name="qty3[]"]').val(defaultQty3 * multiplier);
    }

</script>

<script>

  $(document).on('change', 'select[name^="subject_"], select[name^="level_"]', function() {
    let row = $(this).closest('tr');
    updateQtyBySelection(row);
  });

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

    $('select[name^="subject_"]').each(function() {
      let row = $(this).closest('tr');
      updateQtyBySelection(row);
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