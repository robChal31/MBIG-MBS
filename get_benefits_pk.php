<?php
  include 'db_con.php';
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);

  function esc($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
  }
  
  function formatDescription($text) {
    $text = (string)$text;

    // 1. Decode HTML entities DULU (&#38; → &, &amp; → &, &nbsp; → spasi)
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // 2. Buang tag HTML
    $text = strip_tags($text);

    // 3. Normalize newline (Windows \r\n, Mac \r → \n)
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // 4. Auto newline untuk marker list "1. ", "a. ", "1) ", "a) "
    //    - Cuma match kalau didahului spasi / awal string
    //    - JANGAN match kalau udah di awal baris (biar gak double \n)
    //    - JANGAN match "- ", "* " → biar dash biasa kayak "1,5 - 2 jam" gak kena
    $text = preg_replace(
        '/(?<!\n)(?<=\s|^)((?:[a-zA-Z]|\d+)[\.\)])\s/u',
        "\n$1 ",
        $text
    );

    // 5. Rapihin whitespace
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    $text = trim($text);

    // 6. Esc + nl2br
    return nl2br(esc($text), false);
  }
  /* ===================== DATA LOGIC (UNCHANGED) ===================== */
  $current_row    = 1;
  $id_draft       = ISSET($_GET['id_draft']) ? $_GET['id_draft'] : NULL;
  $program        = ISSET($_GET['program']) ? $_GET['program'] : NULL;
  $count_changes  = $_GET['countChanges'] ?? 0;

  $levels         = ISSET($_GET['levels']) ? $_GET['levels'] : [];
  $subjects       = ISSET($_GET['subjects']) ? $_GET['subjects'] : [];

  $data_templates  = [];

  if($id_draft && $count_changes == 0) {

    $query_template_q = "SELECT dbl.id_benefit_list, dbl.id_draft, dbl.id_template, b.id_template_benefit, 
                        b.benefit, b.subbenefit, b.benefit_name, b.description, b.pelaksanaan, b.qty1, b.qty2, b.qty3,  b.optional, b.book_selection, b.allow_multiple_subject_and_level, b.count_method, b.satuan,
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
                    ORDER BY b.id_template_benefit ASC";

    $result           = mysqli_query($conn, $query_template_q);
    if (!$result) {
        die("SQL Error: " . mysqli_error($conn) . "\n\nQuery: " . $query_template_q);
    }

    $current_row = mysqli_num_rows($result);
    while ($row = mysqli_fetch_assoc($result)) {
        // === Ambil kombinasi level-subject dari benefit_subject_levels ===
        $id_bl = $row['id_benefit_list'];
        
        $level_subject_map = []; // [level_id => [subject_ids]]
        
        $q2 = "SELECT bol.level_id, bsl.subject_id 
              FROM benefit_subject_levels bsl
              LEFT JOIN benefit_org_levels bol ON bol.id = bsl.benefit_org_levels_id
              WHERE bol.draft_benefit_list_id = $id_bl";
        $r2 = mysqli_query($conn, $q2);
        
        if ($r2) {
            while ($pair = mysqli_fetch_assoc($r2)) {
                $level_subject_map[$pair['level_id']][] = $pair['subject_id'];
            }
        }
        
        $row['level_subject_map'] = $level_subject_map;
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
    
    $subject_names = [];
    if (!empty($subjects)) {
        $subject_ids_query = implode(',', array_map('intval', $subjects));
        $query_subject_names = "SELECT name FROM subjects WHERE id IN ($subject_ids_query)";
        $exec_subject_names = mysqli_query($conn, $query_subject_names);
        
        if ($exec_subject_names && mysqli_num_rows($exec_subject_names) > 0) {
            while ($subj = mysqli_fetch_assoc($exec_subject_names)) {
                $subject_names[] = $subj['name'];
            }
        }
    }
    
    // Buat filter subject dengan nama
    $filter_subject_q = '';
    if (!empty($subject_names)) {
        $filter_subject_q = "AND (
            b.subject IS NULL 
            OR b.subject = ''
            OR b.subject IN ('" . implode("','", array_map('mysqli_real_escape_string', array_fill(0, count($subject_names), $conn), $subject_names)) . "')
        )";
    } else {
        // Jika tidak ada subject dipilih, tampilkan semua
        $filter_subject_q = " AND (b.subject IS NULL OR b.subject = '')";
    }

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
                        $filter_subject_q
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
  
  .benefit-table td.subject-level-cell {
    width: 30%;
    min-width: 340px;
    padding: 12px 10px;
    vertical-align: middle;
    text-align: left;
    white-space: normal;
  }

  .lv-grid {
    display: grid;
    grid-template-columns: repeat(var(--cols), 1fr);
    gap: 6px;
  }

  .lv-head {
    background: #0077b6;
    color: #ffff;
    font-weight: 700;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    text-align: center;
    padding: 6px 8px;
    border-radius: 6px;
    white-space: nowrap;
  }

  .lv-cell .select2-container {
    width: 100% !important;
  }

  .lv-cell .select2-selection--multiple {
    min-height: 36px;
    font-size: .75rem;
  }
</style>

<div class="row">
  <div class="col-12">
    <?php if(count($data_templates)) { ?>

      <div class="benefit-wrapper border">

        <div class="table-responsive benefit-table p-2">
          <table class="table table-borderless dataTable no-footer" id="input_form">
            <thead>
              <td style="width:5%">ID</td>
              <td style="width:15%">Benefit Nama</td>
              <td style="width:20%">Deskripsi</td>
              <td class="d-none">Pelaksanaan</td>
              <td style="width: 25%">Subjek × Level</td>
              <td style="width: 15%">Judul Buku untuk IHT/SPV</td>
              <td>Tahun 1</td>
              <td>Tahun 2</td>
              <td>Tahun 3</td>
              <td>Satuan</td>
              <td></td>
            </thead>
            <tbody>
              <?php $row = 1; foreach($data_templates as $data_template) : ?>
                <tr id="row<?= $row ?>">
                  <input type="hidden" name="benefit[]" value="<?= $data_template['benefit'] ?>">
                  <input type="hidden" name="id_templates[]" value="<?= $data_template['id_template_benefit'] ?>">
                  <input type="hidden" name="subbenefit[]" value="<?= $data_template['subbenefit'] ?>">
                  <td class="col-id">
                    <span><?= $data_template['id_template_benefit']; ?></span>
                  </td>
                  <td>
                    <span><?= ucfirst($data_template['benefit_name']); ?></span>
                    <input type="hidden" name="benefit_name[]" value="<?= $data_template['benefit_name'] ?>">
                  </td>
                  <td class="benefit-desc">
                    <input type="hidden" name="description[]" value="<?= $data_template['description'] ?>">
                    <span><?= formatDescription($data_template['description']) ?></span>
                  </td>
                  <td class="benefit-desc d-none">
                    <input type="hidden" name="pelaksanaan[]" value="<?= $data_template['pelaksanaan'] ?>">
                    <span><?= $data_template['pelaksanaan'] ?></span>
                  </td>
                  <td class="subject-level-cell">
                    <?php 
                      $tid = $data_template['id_template_benefit'];
                      $level_subject_map = $data_template['level_subject_map'] ?? [];

                      /* ===== Filter banned level KHUSUS row ini ===== */
                      $banned_per_row = !empty($data_template['banned_level_ids']) 
                          ? array_map('trim', explode(',', $data_template['banned_level_ids'])) 
                          : [];
                      $banned_per_row = array_values(array_filter($banned_per_row, fn($v) => $v !== ''));

                      /* ===== Visible levels = $all_levels - banned_row ===== */
                      $visible_levels = array_values(array_filter($all_levels, function($l) use ($banned_per_row) {
                          return !in_array((string)$l['id'], $banned_per_row, true);
                      }));
                    ?>

                    <?php if($data_template['allow_multiple_subject_and_level'] == 1 && count($visible_levels) > 0) { ?>
                      <div class="lv-grid" style="--cols: <?= count($visible_levels) ?>;">

                        <?php // ===== Headers ===== ?>
                        <?php foreach($visible_levels as $l): ?>
                          <div class="lv-head"><?= htmlspecialchars($l['name']) ?></div>
                        <?php endforeach; ?>

                        <?php // ===== Select per visible level ===== ?>
                        <?php foreach($visible_levels as $l): 
                          $saved = $level_subject_map[$l['id']] ?? [];
                        ?>
                          <div class="lv-cell">
                            <select name="pairs_<?= $tid ?>[<?= $l['id'] ?>][]" class="select2 lv-subj-select2" multiple>
                              <?php foreach($all_subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= in_array($s['id'], $saved) ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($s['name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        <?php endforeach; ?>

                      </div>

                    <?php } else { ?>
                      <span class="text-muted">—</span>
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
                      <select name="book_<?= $data_template['id_template_benefit'] ?>[]" class="book_series_list select2" multiple required>
                        <?php foreach($allowed_book_series as $book_row): ?>
                          <option value="<?= $book_row['id'] ?>" 
                                  data-level="<?= $book_row['level_id'] ?? '' ?>" 
                                  data-subject="<?= $book_row['subject_id'] ?? '' ?>" 
                                  <?= in_array($book_row['id'], $selected_book_series_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($book_row['name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    <?php } else { ?>
                      -
                    <?php } ?>
                  </td>
                  <?php 
                    $count_method = $data_template['count_method'] ?? 'template';
                    $base_qty1 = (int)($data_template['qty1'] ?? 0);
                    $base_qty2 = (int)($data_template['qty2'] ?? 0);
                    $base_qty3 = (int)($data_template['qty3'] ?? 0);
                  ?>

                  <!-- ===== Th 1 ===== -->
                  <td class="qty-cell">
                    <input type="hidden" class="qty-default" name="qty1_default[]" value="<?= $base_qty1 ?>">
                    <input type="hidden" class="qty-base" value="<?= $base_qty1 ?>">
                    <input type="hidden" class="count-method" value="<?= $count_method ?>">

                    <?php if($count_method === 'manual') { ?>
                      <input type="number" class="form-control form-control-sm qty-manual" 
                            name="qty1[]" value="<?= $base_qty1 ?>" min="0" 
                            style="width:70px; margin:auto;">
                    <?php } else { ?>
                      <input type="hidden" class="qty-value" name="qty1[]" value="<?= $base_qty1 ?>">
                      <span class="qty1-display"><?= $base_qty1 ?></span>
                    <?php } ?>
                  </td>

                  <!-- ===== Th 2 ===== -->
                  <td class="qty-cell">
                    <input type="hidden" class="qty-default" name="qty2_default[]" value="<?= $base_qty2 ?>">
                    <input type="hidden" class="qty-base" value="<?= $base_qty2 ?>">
                    <input type="hidden" class="count-method" value="<?= $count_method ?>">

                    <?php if($count_method === 'manual') { ?>
                      <input type="number" class="form-control form-control-sm qty-manual" 
                            name="qty2[]" value="<?= $base_qty2 ?>" min="0" 
                            style="width:70px; margin:auto;">
                    <?php } else { ?>
                      <input type="hidden" class="qty-value" name="qty2[]" value="<?= $base_qty2 ?>">
                      <span class="qty2-display"><?= $base_qty2 ?></span>
                    <?php } ?>
                  </td>

                  <!-- ===== Th 3 ===== -->
                  <td class="qty-cell">
                    <input type="hidden" class="qty-default" name="qty3_default[]" value="<?= $base_qty3 ?>">
                    <input type="hidden" class="qty-base" value="<?= $base_qty3 ?>">
                    <input type="hidden" class="count-method" value="<?= $count_method ?>">

                    <?php if($count_method === 'manual') { ?>
                      <input type="number" class="form-control form-control-sm qty-manual" 
                            name="qty3[]" value="<?= $base_qty3 ?>" min="0" 
                            style="width:70px; margin:auto;">
                    <?php } else { ?>
                      <input type="hidden" class="qty-value" name="qty3[]" value="<?= $base_qty3 ?>">
                      <span class="qty3-display"><?= $base_qty3 ?></span>
                    <?php } ?>
                  </td>

                  <td>
                    <?= ucfirst($data_template['satuan'] ?? '') ?>
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
          <div class="alert alert-danger">No Benefits for this Program or no benefits for this subject</div>
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
    const countMethod = row.find('.count-method').first().val() || 'template';

    // Cuma proses kalau subject_level
    if (countMethod !== 'subject_level') return;

    // Hitung total subject di semua level
    let total = 0;
    row.find('.lv-subj-select2').each(function () {
      total += ($(this).val() || []).length;
    });
    const multiplier = total || 1;

    // Update tiap kolom qty (Th 1, 2, 3)
    row.find('.qty-cell').each(function () {
      const $cell = $(this);
      const base = parseInt($cell.find('.qty-base').val()) || 0;
      const newVal = base * multiplier;

      $cell.find('.qty-value').val(newVal);
      $cell.find('.qty1-display, .qty2-display, .qty3-display').text(newVal);
    });
  }

  function filterRowBookSeries(row) {
    let $bookSelect = row.find('.book_series_list');
    if ($bookSelect.length === 0) return;

    // 1. Ambil value global dari parent (#book_series di new-benefit-pk.php)
    let globalSelected = $('#book_series').val() || [];
    if (!Array.isArray(globalSelected)) globalSelected = [globalSelected];
    let globalSelectedStr = globalSelected.map(String);

    // 2. Cek apakah row ini punya filter level/subject (grid)
    let hasLevelSubjectGrid = row.find('.lv-subj-select2').length > 0;
    let selectedMap = {};
    let hasSubject = false;

    if (hasLevelSubjectGrid) {
      row.find('.lv-subj-select2').each(function() {
        let name = $(this).attr('name');
        let match = name ? name.match(/\[(\d+)\]\[\]/) : null;
        if (match) {
          let levelId = match[1];
          let subjects = $(this).val() || [];
          if (subjects.length > 0) {
            selectedMap[levelId] = subjects.map(String);
            hasSubject = true;
          }
        }
      });
      
      // Jika wajib punya subject tapi belum ada yang dipilih, disable semua opsi
      if (!hasSubject) {
        $bookSelect.val([]).trigger('change.select2');
        $bookSelect.find('option').prop('disabled', true).hide();
        return;
      }
    }

    // 3. Loop semua option buku dan filter
    $bookSelect.find('option').each(function() {
      let $opt = $(this);
      let optVal = String($opt.val());
      let optLevel = String($opt.data('level') || '');
      let optSubjectsRaw = String($opt.data('subject') || '');
      let optSubjects = optSubjectsRaw.split(',').map(s => s.trim());

      // Cek A: Apakah dipilih di global "Judul Buku Adopsi"?
      let isGlobalAllowed = globalSelectedStr.includes(optVal);

      // Cek B: Apakah cocok dengan Level & Subject di baris ini?
      let isRowMatch = false;
      if (!hasLevelSubjectGrid) {
        isRowMatch = true; // Jika tidak ada grid, loloskan (hanya filter global & benefit)
      } else {
        if (selectedMap[optLevel]) {
          isRowMatch = optSubjects.some(subj => selectedMap[optLevel].includes(subj));
        }
      }

      if (isGlobalAllowed && isRowMatch) {
        $opt.prop('disabled', false).show();
      } else {
        $opt.prop('disabled', true).hide();
      }
    });

    // 4. Hapus pilihan buku yang sudah tidak valid
    let currentSelected = $bookSelect.val() || [];
    let validValues = currentSelected.filter(val => {
      let $opt = $bookSelect.find(`option[value="${val}"]`);
      return $opt.length > 0 && !$opt.prop('disabled');
    });

    if (validValues.length !== currentSelected.length) {
      $bookSelect.val(validValues).trigger('change.select2');
    }
  }

</script>

<script>

  $(document).on('change', '.lv-subj-select2', function() {
    let row = $(this).closest('tr');
    updateQtyBySelection(row);
    filterRowBookSeries(row);
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

    $('#input_form tbody tr').each(function() {
      updateQtyBySelection($(this));
      filterRowBookSeries($(this));
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