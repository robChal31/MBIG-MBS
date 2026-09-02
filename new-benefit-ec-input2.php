<?php include 'header.php'; ?>

<?php

  $current_row = 0;
  $tpl_data = [];
  $use_template_as_default = false;

  if($_GET['edit'] == 'edit'){ 
    $id_draft = (int) $_GET['id_draft'];
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
      $sql = "SELECT 
                dbl.type, dbl.id_template, dbl.subbenefit, dbl.benefit_name, dbl.description, dtb.pelaksanaan, dtb.multiple_subject, dtb.multiple_level,
                dbl.keterangan, dbl.qty, dbl.qty2, dbl.qty3, dtb.valueMoney, dbl.manualValue, dbl.calcValue, dtb.editable_qty, dtb.book_selection,
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
                  WHERE alb.id_template_benefit = dtb.id_template_benefit
                ), ''
            ) AS banned_level_ids,
            COALESCE(
                (SELECT GROUP_CONCAT(DISTINCT abb.book_series_id SEPARATOR ',')
                  FROM allowed_book_benefits as abb
                  WHERE abb.id_template_benefit = dtb.id_template_benefit
                ), ''
            ) AS allowed_book_ids
        FROM draft_benefit db
        LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
        LEFT JOIN draft_template_benefit dtb ON dtb.id_template_benefit = dbl.id_template
        WHERE db.id_draft = $id_draft
        GROUP BY dbl.id_benefit_list";

      $template_data_result = mysqli_query($conn, $sql);
      $current_row = mysqli_num_rows($template_data_result);

      if ($current_row < 1) {
        $use_template_as_default = true;

        $tpl_sql = "SELECT id_template_benefit, benefit_name
                    FROM draft_template_benefit
                    WHERE valueMoney = 0
                      AND avail LIKE '%$program%' AND is_active = 1
                    ORDER BY benefit_name ASC";

        $tpl_result = mysqli_query($conn, $tpl_sql);


        while ($row = mysqli_fetch_assoc($tpl_result)) {
          $tpl_data[] = $row;
        }
      }
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

  $show_year_2_and_3 = false;
  $programs_data_q = "SELECT * FROM programs WHERE name = '$program' or code = '$program' LIMIT 1";
  $result_programs_data = mysqli_query($conn, $programs_data_q);
  $data_programs = mysqli_fetch_assoc($result_programs_data);
  
  $show_year_2_and_3 = $data_programs['show_year_2_and_3'] ?? false;

  $sql = "SELECT id_template_benefit FROM draft_template_benefit WHERE benefit_name LIKE '%dana pengembangan%'";
  $check_result = mysqli_query($conn, $sql);

  $make_max_ids = [];
  while ($row = mysqli_fetch_assoc($check_result)) {
      $make_max_ids[] = (int)$row['id_template_benefit'];
  }

  $benefitSetting = [
      'max_price_percentage' => '',
      'max_discount_percentage' => '',
      'max_benefit_percentage' => ''
  ];

  $query = "SELECT max_price_percentage, max_discount_percentage, max_benefit_percentage FROM benefit_setting LIMIT 1";
  $result_price = mysqli_query($conn, $query);

  if ($result_price && mysqli_num_rows($result_price) > 0) {
      $benefitSetting = mysqli_fetch_assoc($result_price);
  }
  // 🔥 Ambil semua subject (1 query aja)
  $all_subjects = [];
  $subject_query = "SELECT sub.id, sub.name FROM program_adoption_subjects as pas LEFT JOIN subjects as sub on pas.subject_id = sub.id  WHERE pas.draft_id = $id_draft";
  $subject_result = mysqli_query($conn, $subject_query);
  while ($row = mysqli_fetch_assoc($subject_result)) {
      $all_subjects[] = $row;
  }


  $all_levels = [];
  $book_series = [];
  $level_query = "SELECT lv.id, lv.name FROM program_adoption_levels as pal LEFT JOIN levels as lv on pal.level_id = lv.id  WHERE pal.draft_id = $id_draft";
  $level_result = mysqli_query($conn, $level_query);
  while ($row = mysqli_fetch_assoc($level_result)) {
      $all_levels[] = $row;
  }
  $query_book_series = "SELECT * FROM book_series WHERE is_active = 1 ORDER BY name ASC";
  $exec_book_series = mysqli_query($conn, $query_book_series);

  if ($exec_book_series && mysqli_num_rows($exec_book_series) > 0) {
    $book_series = mysqli_fetch_all($exec_book_series, MYSQLI_ASSOC);
  }
?>

<style>
  /* ===== GLOBAL STYLE ===== */
  :root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #00b09b, #96c93d);
    --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
    --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
    --radius: 16px;
    --radius-sm: 10px;
  }

  /* ===== CARD HEADER ===== */
  .card-header-custom {
    background: var(--primary-gradient);
    color: white;
    padding: 20px 24px;
    border-radius: var(--radius) var(--radius) 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .card-header-custom .title {
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: 0.3px;
  }

  .card-header-custom .subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
    font-weight: 300;
  }

  .card-header-custom .badge-custom {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    border: 1px solid rgba(255,255,255,0.3);
  }

  /* ===== CARD BODY ===== */
  .card-body-custom {
    background: #ffffff;
    padding: 24px;
    border-radius: 0 0 var(--radius) var(--radius);
  }

  .card-modern {
    border: none;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card-modern:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
  }

    /* ===== BUTTONS ===== */
  .btn-gradient-primary {
      background: var(--primary-gradient);
      border: none;
      color: white;
      font-weight: 600;
      padding: 10px 28px;
      border-radius: 50px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }

  .btn-gradient-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
      color: white;
  }

  /* 🔥 Tambahin ini buat disabled */
  .btn-gradient-primary:disabled {
      background: #ced4da !important;
      color: #6c757d !important;
      cursor: not-allowed !important;
      transform: none !important;
      box-shadow: none !important;
      opacity: 0.65;
  }

  .btn-gradient-primary:disabled:hover {
      transform: none !important;
      box-shadow: none !important;
      color: #6c757d !important;
  }

  .btn-gradient-success {
    background: var(--success-gradient);
    border: none;
    color: white;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 176, 155, 0.3);
  }

  .btn-gradient-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 176, 155, 0.4);
    color: white;
  }

  .btn-gradient-danger {
    background: var(--danger-gradient);
    border: none;
    color: white;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
  }

  .btn-gradient-danger:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 25px rgba(245, 87, 108, 0.4);
    color: white;
  }

  .btn-back {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    border-radius: 50px;
    padding: 6px 18px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
  }

  .btn-back:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    transform: translateX(-3px);
  }

  /* ===== TABLE STYLING ===== */
  #input_form {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--radius-sm);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    width: 100%;
  }

  #input_form thead td {
    background: linear-gradient(135deg, #f8f9fc 0%, #eef1f5 100%);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #2d3748;
    padding: 12px 8px !important;
    border-bottom: 2px solid #e2e8f0;
  }

  #input_form tbody td {
    padding: 6px 4px !important;
    vertical-align: middle !important;
    text-align: center !important;
    border-bottom: 1px solid #f0f2f5;
    transition: background 0.2s ease;
  }

  #input_form tbody tr:hover {
    background: rgba(102, 126, 234, 0.04);
  }

  #input_form tbody tr:last-child td {
    border-bottom: none;
  }

  #input_form .form-control-sm {
    min-height: 32px;
    padding: 4px 10px;
  }

  /* ===== SUMMARY CARD ===== */
  .summary-card {
    background: linear-gradient(135deg, #fafbff 0%, #f0f4ff 100%);
    border-radius: var(--radius-sm);
    border: 1px solid #e8edf5;
    padding: 16px 20px;
  }

  .summary-card table {
    margin-bottom: 0;
  }

  .summary-card th {
    font-weight: 600;
    font-size: 12px;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .summary-card td {
    font-size: 13px;
    padding: 6px 8px;
    color: #2d3748;
  }

  .summary-card .value-highlight {
    font-weight: 700;
    color: #667eea;
  }

  .summary-card .value-success {
    font-weight: 700;
    color: #00b09b;
  }

  .summary-card .value-danger {
    font-weight: 700;
    color: #f5576c;
  }

  /* ===== HIDDEN COLUMNS ===== */
  .col-benefit,
  .col-subbenefit {
    display: none !important;
  }

  /* ===== SCROLLBAR ===== */
  .table-wrapper::-webkit-scrollbar {
    height: 8px;
  }

  .table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .table-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 10px;
  }

  .table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #5a6fd6;
  }

  /* ===== ANIMATIONS ===== */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .card-modern {
    animation: fadeInUp 0.6s ease forwards;
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
  }

  .btn-gradient-primary:hover {
    animation: pulse 1s ease infinite;
  }

  /* ===== RESPONSIVE ===== */
  @media (max-width: 768px) {
    .card-header-custom {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }
    
    .card-header-custom .d-flex {
      flex-wrap: wrap;
      gap: 8px;
    }

    #input_form {
      font-size: 11px;
    }

    #input_form thead td {
      font-size: 9px;
      padding: 8px 4px !important;
    }

    #input_form input,
    #input_form textarea,
    #input_form select {
      font-size: 10px;
    }
  }

  .select2-benefit + .select2-container--default .select2-selection--single {
    border-radius: 6px;
    border-color: #e2e8f0;
    height: 34px;
  }

  .select2-benefit + .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    font-size: 12px;
  }

  .select2-benefit + .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
  }

  .select2-benefit + .select2-container .select2-results__option--highlighted {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
  }

  .select2-benefit + .select2-container .select2-optgroup-label {
    font-weight: 700;
    color: #2d3748;
    cursor: pointer;
    padding: 8px 12px;
    background: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
  }

  /* ===== SELECT2 MULTIPLE (SUBJECT & LEVEL) ===== */
  .select2-container--default .select2-selection--multiple {
    min-height: 34px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-size: 11px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 2px 8px;
    font-size: 10px;
    margin: 2px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: white;
    margin-right: 4px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #f5576c;
  }

  /* ===== SELECT2 DEFAULT (BIASA) ===== */
  .select2-container .select2-dropdown {
    border-radius: 8px;
    border-color: #e2e8f0;
    box-shadow: var(--shadow-md);
  }

  .select2-container--default .select2-selection--single {
    border-radius: 6px;
    border-color: #e2e8f0;
    height: 34px;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    font-size: 12px;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
  }

  .select2-results__option {
    font-size: 12px;
    padding: 8px 12px;
  }

  .select2-results__option--highlighted {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
  }

  .select2-optgroup-label {
    font-weight: 700;
    color: #2d3748;
    cursor: pointer;
    padding: 8px 12px;
    background: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
  }

  .select2-container .select2-dropdown {
    width: 40vw !important;
  }

  .select2-container .select2-results__option {
    max-width: 40vw; 
    font-size: 14px;
  }

  /* ===== CHECKBOX STYLING ===== */
  .custom-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #4a5568;
  }

  .custom-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #667eea;
    cursor: pointer;
    border-radius: 4px;
  }

  /* ===== TOOLTIP ===== */
  .tooltip-inner {
    background: #2d3748;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 11px;
  }

  .bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #2d3748;
  }

  /* FIX SELECT2 MULTIPLE */
  #input_form .select2-container {
      width: 100% !important;
  }

  #input_form .select2-container--default .select2-selection--multiple {
      min-height: 34px !important;
      height: auto !important;
      padding: 2px 4px !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 6px !important;
      box-sizing: border-box !important;
  }

  /* Search input Select2 jangan sampai jadi vertikal */
  #input_form .select2-container--default
  .select2-selection--multiple
  .select2-search--inline {
      float: none !important;
      display: inline-block !important;
      vertical-align: middle !important;
  }

  #input_form .select2-container--default
  .select2-selection--multiple
  .select2-search--inline
  .select2-search__field {
      width: 100% !important;
      min-width: 80px !important;
      height: 28px !important;
      min-height: 28px !important;
      margin: 0 !important;
      padding: 4px 6px !important;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
      writing-mode: horizontal-tb !important;
  }

  /* Choice/tag */
  #input_form .select2-selection--multiple .select2-selection__choice {
      float: left !important;
      margin-top: 3px !important;
      margin-bottom: 3px !important;
  }

  /* Dropdown */
  #input_form .select2-container--open .select2-dropdown {
      width: auto !important;
      min-width: 200px !important;
      max-width: 400px !important;
  }

  /* ===== TABLE STYLING ===== */
  #input_form {
      border-collapse: separate;
      border-spacing: 0;
      border-radius: var(--radius-sm);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      width: 100%;
      table-layout: fixed;  /* 🔥 KRUSIAL */
  }

  /* ===== KOLOM WIDTH ===== */
  #input_form thead td {
      background: linear-gradient(135deg, #f8f9fc 0%, #eef1f5 100%);
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #2d3748;
      padding: 12px 8px !important;
      border-bottom: 2px solid #e2e8f0;
  }

  #input_form tbody td {
      padding: 6px 4px !important;
      vertical-align: middle !important;
      text-align: center !important;
      border-bottom: 1px solid #f0f2f5;
      transition: background 0.2s ease;
      overflow: hidden;        /* 🔥 Potong konten yang kepanjangan */
      text-overflow: ellipsis; /* 🔥 Tambah ... jika kepanjangan */
      white-space: nowrap;     /* 🔥 Cegah wrap */
  }

  /* 🔥 Kolom Nama Benefit - biarkan teks wrap jika perlu */
  #input_form tbody td:nth-child(3) {
      white-space: normal;
      word-wrap: break-word;
  }
</style>

<!-- Content Start -->
<div class="content">
  <!-- Navbar Start -->
  <?php include 'navbar.php'; ?>
  <!-- Navbar End -->

  <!-- Form Start -->
  <div class="container-fluid py-3">
    
    <!-- Back Button -->
    <div class="mb-3">
      <a href="<?= "new-benefit-ec-input.php?id_draft=$id_draft" ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 50px; font-size: 0.8rem; padding: 6px 18px; border-color: #d1d5db;">
        <i class="fas fa-arrow-left"></i>
        Back to input
      </a>
    </div>

    <!-- Main Card -->
    <div class="card-modern">
      
      <!-- Card Header -->
      <div class="card-header-custom">
        <div>
          <div class="d-flex align-items-center gap-3">
            <div style="width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
              <i class="fas fa-calculator fs-5" style="color: white;"></i>
            </div>
            <div>
              <div class="title">
                <!-- <i class="fas fa-file-invoice me-2"></i> -->
                Draft Benefit Calculation
              </div>
              <div class="subtitle">
                Atur benefit, quantity, dan perhitungan nilai program
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <button type="button" class="btn-gradient-success" id="add_row">
            <i class="fas fa-plus me-1"></i> Add Row
          </button>
        </div>
      </div>

      <!-- Card Body -->
      <div class="card-body-custom">

        <form method="POST" action="new-benefit-ec-input-action2.php" enctype="multipart/form-data" id='draft_form'>
      
          <input type="hidden" value="<?= $sumalok ?>" name="sumalok">
          <input type="hidden" value="<?= $program ?>" name="program">
          <input type="hidden" value="<?= $year ?>" name="year">
          <input type="hidden" value="<?= $ref_id ?>" name="ref_id">
          <div style="width: 100%; overflow-x: auto; padding: 15px 0px;">
            <div style="width: 135%">
              <!-- Table Wrapper -->
              <div class="table-wrapper" style="width: 100%; overflow-x: auto; padding: 4px 0;">
                <table class="table table-bordered mb-0" id="input_form">
                  <thead>
                      <tr>
                        <td class="td-cust text-center col-benefit" rowspan="2" style="width:5%;">Benefit</td>
                        <td class="td-cust text-center col-subbenefit" rowspan="2" style="width:5%;">Sub Benefit</td>
                        <td class="td-cust text-center" rowspan="2" style="width:15%;">Nama Benefit</td>
                        <td class="td-cust text-center" rowspan="2" style="width:18%;">Deskripsi</td>
                        <!-- <td class="td-cust text-center" rowspan="2" style="width:10%;">Pelaksanaan</td> -->
                        <td class="td-cust text-center" rowspan="2" style="width:10%;">Subject</td>
                        <td class="td-cust text-center" rowspan="2" style="width:10%;">Level</td>
                        <td class="td-cust text-center" rowspan="2" style="width:10%;">Book</td>
                        <td class="td-cust text-center" rowspan="2" style="width:8%;">Nilai Benefit</td>
                        <td class="td-cust text-center" colspan="3" style="width:12%; background: linear-gradient(135deg, #e8edf5, #dce4f0);">Quantity Per Tahun</td>
                        <td class="td-cust text-center" rowspan="2" style="width:8%;">Nilai Value</td>
                        <td class="td-cust text-center" rowspan="2" style="width:5%;">Action</td>
                      </tr>
                      <tr>
                        <td style="width:40px; text-align:center; background: linear-gradient(135deg, #e8edf5, #dce4f0);">
                          <span class="badge" style="background: #667eea; color: white; font-size: 10px; padding: 5px 10px; border-radius: 50px;">1</span>
                        </td>
                        <td style="width:40px; text-align:center; background: linear-gradient(135deg, #e8edf5, #dce4f0);">
                          <span class="badge" style="background: #764ba2; color: white; font-size: 10px; padding: 5px 10px; border-radius: 50px;">2</span>
                        </td>
                        <td style="width:40px; text-align:center; background: linear-gradient(135deg, #e8edf5, #dce4f0);">
                          <span class="badge" style="background: #f5576c; color: white; font-size: 10px; padding: 5px 10px; border-radius: 50px;">3</span>
                        </td>
                      </tr>
                  </thead>
                  <tbody>
                    <?php if (!$use_template_as_default) {
                      $x = 1;
                      while ($data = $template_data_result->fetch_assoc()): ?>
                        <tr id="row<?= $x; ?>">
                          <td class="col-benefit">
                            <span class="benefit"><?= $data['type'] ?></span>
                            <input type='hidden' name='benefit[]' value='<?= $data['type'] ?>'>
                            <input type='hidden' name='id_templates[]' value='<?= $data['id_template'] ?>'>
                          </td>
                          <td class="col-subbenefit">
                            <span class="subbenefit"><?= $data['subbenefit'] ?></span>
                            <input type='hidden' name='subbenefit[]' value="<?= $data['subbenefit'] ?>">
                          </td>
                          <td>
                            <span style="font-weight: 500; font-size: 12px;"><?= $data['benefit_name'] ?></span>
                            <input type='hidden' name='benefit_name[]' value='<?= $data['benefit_name'] ?>'>
                          </td>
                          <td class="text-area-cont">
                            <textarea id="description" name="description[]" class="form-control form-control-sm txt-area" cols="16"><?= $data['description'] ?></textarea>
                          </td>
                          <?php 
                            $new_qty = ((int)$data['qty'] + (int)$data['qty2'] + (int)$data['qty3']) == 0 ? 1 : ((int)$data['qty'] + (int)$data['qty2'] + (int)$data['qty3']);
                            $data['valueMoney'] = (int)$data['calcValue'] / ($new_qty);
                          ?>
                          <!-- <td>
                            <textarea id="pelaksanaan" name="pelaksanaan[]" class="form-control form-control-sm txt-area" cols="16"><?= $data['pelaksanaan'] ?></textarea>
                          </td> -->
                          <td>
                            <?php if ($data['multiple_subject'] == 1): ?>
                              <select name="subject_<?= $data['id_template'] ?>[]" class="form-select form-select-sm subject-select select2" multiple required>
                                <?php 
                                  // Parse subject_ids dari string ke array
                                  $selected_subjects = !empty($data['subject_ids']) 
                                      ? explode(',', $data['subject_ids']) 
                                      : [];
                                  
                                  // Ambil semua subject (1 query aja di awal)
                                  foreach ($all_subjects as $subject): 
                                ?>
                                  <option value="<?= $subject['id'] ?>" <?= in_array($subject['id'], $selected_subjects) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subject['name']) ?>
                                  </option>
                                <?php endforeach; ?>
                              </select>
                            <?php else: ?>
                              <input type="hidden" name="subject_<?= $data['id_template'] ?>[]" value="">
                              <span class="text-muted" style="font-size: 11px;">-</span>
                            <?php endif; ?>
                          </td>
                          
                          <!-- 🔥 LEVEL (MULTIPLE) -->
                          <td>
                            <?php if ($data['multiple_level'] == 1): ?>
                              <select name="level_<?= $data['id_template'] ?>[]" class="form-select form-select-sm level-select select2" multiple required>
                                <?php 
                                // Parse level_ids dari string ke array
                                  $selected_levels = !empty($data['level_ids']) ? explode(',', $data['level_ids']) : [];
                                  $banned_levels = $data['banned_level_ids'] ? explode(',', $data['banned_level_ids']) : [];
                                  $allowed_levels = array_filter($all_levels, function($level) use ($banned_levels) {
                                    return !in_array($level['id'], $banned_levels);
                                  });
                                  foreach ($all_levels as $level): 
                                ?>
                                  <option value="<?= $level['id'] ?>" <?= in_array($level['id'], $selected_levels) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($level['name']) ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                            <?php else: ?>
                              <input type="hidden" name="level_<?= $data['id_template'] ?>[]" value="">
                              <span class="text-muted" style="font-size: 11px;">-</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <?php if($data['book_selection'] == 1) { 
                                    $selected_book_series_id = !empty($data['book_series_ids']) ? explode(',', $data['book_series_ids']) : [];
                                    $allowed_book_ids = !empty($data['allowed_book_ids']) ? explode(',', $data['allowed_book_ids']) : [];
                                    
                                    $allowed_book_series = array_filter($book_series, function($book) use ($allowed_book_ids) {
                                        return in_array($book['id'], $allowed_book_ids);
                                    });
                            ?>
                              <select name="book_series_<?= $data['id_template'] ?>[]" class="select2 book-select form-select form-select-sm" multiple required>
                                <?php foreach($allowed_book_series as $book_row): ?>
                                  <option value="<?= $book_row['id'] ?>" <?= in_array($book_row['id'], $selected_book_series_id) ? 'selected' : '' ?>><?= htmlspecialchars($book_row['name']) ?></option>
                                <?php endforeach; ?>
                              </select>
                            <?php } else { ?>
                              -
                            <?php } ?>
                          </td>
                          <td>
                            <input type="text" class="form-control form-control-sm" id="valben" name="valben[]" placeholder="0" onchange="updateDisabledField(this)" value="<?= number_format($data['valueMoney'], '0', ',', '.'); ?>" readonly>
                          </td>
                          <input type="hidden" class="form-control form-control-sm" id="keterangan" name="keterangan[]" placeholder="Keterangan" value="<?= $data['keterangan'] ?>">
                          <td>
                            <input type="number" class="form-control form-control-sm tah1" id="member" name="member[]" placeholder="0" value="<?= $data['qty'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($data['editable_qty'] == '0' || $year == 2 || $year == 3){echo "readonly";} ?>>
                          </td>
                          <td>
                            <input type="number" class="form-control form-control-sm tah2" id="member2" name="member2[]" placeholder="0" value="<?= $data['qty2'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($program=='cbls1'|| ($program=='cbls3' && !$ref_id) || $program=='bsp' || $data['editable_qty'] == '0' || $year == 3){echo "readonly";} ?> >
                          </td>
                          <td>
                            <input type="number" class="form-control form-control-sm tah3" id="member3" name="member3[]" placeholder="0" value="<?= $data['qty3'] ?>" min="0" onchange="updateDisabledField(this)" onload="updateDisabledField(this)" <?php if($program=='cbls1'|| ($program=='cbls3' && !$ref_id) || $program=='bsp' || $data['editable_qty'] == '0'){echo "readonly";} ?>>
                          </td>
                          <td>
                            <input type="text" class="form-control form-control-sm usage" id="calcValue" name="calcValue[]" placeholder="0" value="<?= number_format($data['calcValue'], '0', ',', '.') ?>" readonly>
                          </td>
                          <input type="hidden" name="valuedefault[]" value="<?= $data['valueMoney'] ?>">
                          <td>
                            <button type="button" class="btn_remove btn btn-gradient-danger btn-sm" data-row="row<?= $x ?>" style="padding: 4px 10px; font-size: 12px;">
                              <i class="fas fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php $x++; endwhile; ?>
                    <?php }; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- Summary Section -->
          <div class="mt-4">
            <div class="summary-card">
              <table class="table table-sm mb-0">
                <tr>
                  <th style="width: 12%;">Periode</th>
                  <th style="width: 22%;">
                    <span class="badge" style="background: #667eea; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px;">Tahun 1</span>
                  </th>
                  <th style="width: 22%;">
                    <span class="badge" style="background: #764ba2; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px;">Tahun 2</span>
                  </th>
                  <th style="width: 22%;">
                    <span class="badge" style="background: #f5576c; color: white; padding: 4px 12px; border-radius: 50px; font-size: 11px;">Tahun 3</span>
                  </th>
                </tr>
                <tr>
                  <td style="font-weight: 500; color: #4a5568;">Qty per tahun</td>
                  <td><span id="qtyth1" class="value-highlight">0</span></td>
                  <td><span id="qtyth2" class="value-highlight">0</span></td>
                  <td><span id="qtyth3" class="value-highlight">0</span></td>
                </tr> 
                <tr>
                  <td style="font-weight: 500; color: #4a5568;">Nilai per tahun</td>
                  <td>
                    <span id="valth1" class="value-success">Rp 0</span>
                    <input type="hidden" name="total_benefit1" id="total_benefit1" value="0">
                  </td>
                  <td>
                    <span id="valth2" class="value-success">Rp 0</span>
                    <input type="hidden" name="total_benefit2" id="total_benefit2" value="0">
                  </td>
                  <td>
                    <span id="valth3" class="value-success">Rp 0</span>
                    <input type="hidden" name="total_benefit3" id="total_benefit3" value="0">
                  </td>
                </tr> 
                <tr style="background: rgba(102, 126, 234, 0.06); border-radius: 8px;">
                  <td style="font-weight: 600; color: #2d3748;">Total Alokasi Benefit</td>
                  <td style="font-weight: 700; color: #667eea;">Rp <?= number_format($sumalok, '0', ',', '.') ?></td>
                  <td style="font-weight: 700; color: #667eea;"><?= ($program == 'prestasi' || $ref_id || $show_year_2_and_3 == 1) ? ('Rp ' . number_format($sumalok, '0', ',', '.')) : '' ?></td>
                  <td style="font-weight: 700; color: #667eea;"><?= ($program == 'prestasi' || $ref_id || $show_year_2_and_3 == 1) ? ('Rp ' . number_format($sumalok, '0', ',', '.')) : '' ?></td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: #2d3748;">Selisih</td>
                  <td>
                    <p id="selisihbenefit1" style="margin: 0; font-weight: 700;"></p>
                    <input type="hidden" name="selisih_benefit1" id="selisih_benefit1" value="0">
                  </td>
                  <?php if($program != 'cbls1' || ($program == 'cbls3' && !$ref_id) || $program!='bsp'):?>
                    <td>
                      <p id="selisihbenefit2" style="margin: 0; font-weight: 700;"></p>
                      <input type="hidden" name="selisih_benefit2" id="selisih_benefit2" value="0">
                    </td>
                    <td>
                      <p id="selisihbenefit3" style="margin: 0; font-weight: 700;"></p>
                      <input type="hidden" name="selisih_benefit3" id="selisih_benefit3" value="0">
                    </td>
                  <?php endif; ?>
                </tr>
              </table>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top" style="border-top-color: #e2e8f0 !important;">
            <div class="custom-checkbox">
              <input type="checkbox" id="save_as_d" name="save_as_draft" value="1">
              <label for="save_as_d">
                <i class="far fa-save me-1" style="color: #667eea;"></i>
                Save as draft
              </label>
            </div>
            <button type="submit" class="btn-gradient-primary" id="submt">
              <i class="fas fa-paper-plane me-2"></i> Submit
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Template Row -->
  <template id="row-template">
    <tr>
      <td class="col-benefit">
        <span class="benefit"></span>
        <input type="hidden" name="benefit[]" value="">
        <input type="hidden" name="id_templates[]" value="">
      </td>
      <td class="col-subbenefit">
        <span class="subbenefit"></span>
        <input type="hidden" name="subbenefit[]" value="">
      </td>
      <td>
        <select name="benefit_id[]" class="form-select form-select-sm select2-benefit" onchange="getBenefitData(this)" required>
        </select>
        <input type="hidden" name="benefit_name[]" value="">
      </td>
      <td class="text-area-cont">
        <textarea name="description[]" class="form-control form-control-sm txt-area"></textarea>
      </td>
      <!-- <td>
        <textarea name="pelaksanaan[]" class="form-control form-control-sm txt-area"></textarea>
      </td> -->
      <td>
        <select name="subject[]" class="form-select form-select-sm subject-select select2" multiple required>
        </select>
      </td>
      <td>
        <select name="level[]" class="form-select form-select-sm level-select select2" multiple required>
        </select>
      </td>
      <td>
        <select name="book_series[]" class="form-select form-select-sm select2 book-select select2" multiple required>
        </select>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm" name="valben[]" value="0" readonly onchange="updateDisabledField(this)">
      </td>
      <input type="hidden" name="keterangan[]" value="">
      <td>
        <input type="number" class="form-control form-control-sm tah1" name="member[]" value="0" min="0" onchange="updateDisabledField(this)">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm tah2" name="member2[]" value="0" min="0" onchange="updateDisabledField(this)">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm tah3" name="member3[]" value="0" min="0" onchange="updateDisabledField(this)">
      </td>
      <td>
        <input type="text" class="form-control form-control-sm usage" name="calcValue[]" value="0" readonly>
      </td>
      <input type="hidden" name="valuedefault[]" value="">
      <td>
        <button type="button" class="btn_remove btn btn-gradient-danger btn-sm" style="padding: 4px 10px; font-size: 12px;">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  </template>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
  let isDirty = false;
  let isSubmitting = false;
    
  const tpl_data = <?= json_encode($tpl_data) ?>;
  const allSubjects = <?= json_encode($all_subjects) ?>;
  const allLevels = <?= json_encode($all_levels) ?>;
  const allBookSeries = <?= json_encode($book_series) ?: '[]' ?>;
  let programYear = <?= $year ?? 1 ?>;
  var maxRows = 100; 
  let x = <?=  $current_row ?>;
  x = x ? parseInt(x) : 0;
  let use_template_as_default = '<?= $use_template_as_default ?? false; ?>';
  let refId = JSON.parse('<?php echo json_encode($ref_id); ?>');

  function initEditCalculation() {
    $('input[name="member[]"]').each(function () {
      updateDisabledField(this);
    });
    accumulateValues();
  }

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

  async function getBenefitData(element){
      var row = $(element).closest('tr');
      var benefitId = row.find('select[name="benefit_id[]"]').find(":selected").val();

      $.ajax({
          url: 'get_benefit_datas.php',
          type: 'POST',
          data: {
              benefitId: benefitId,
              program: '<?= $program ?>'
          },
          success: function(response) {
            const data = response[0] ?? [];
            console.log('data: ', data);
            row.find('input[name="benefit[]"]').val(data.benefit);
            row.find('input[name="id_templates[]"]').val(data.id_template_benefit);
            row.find('span.benefit').html(data.benefit);
            row.find('span.subbenefit').html(data.subbenefit);
            row.find('textarea[name="description[]"]').html(data.description);
            row.find('input[name="subbenefit[]"]').val(data.subbenefit);
            row.find('input[name="benefit_name[]"]').val(data.benefit_name);
            row.find('textarea[name="pelaksanaan[]"]').html(data.pelaksanaan);
            row.find('input[name="valuedefault[]"]').val(data.valueMoney);
            row.find('input[name="valben[]"]').val(formatNumber(data.valueMoney));
            row.find('input[name="member[]"]').val(formatNumber(data.qty1));
            row.find('input[name="member2[]"]').val(formatNumber(data.qty2));
            row.find('input[name="member3[]"]').val(formatNumber(data.qty3));

            row.find('input[name="member[]"]').prop("readonly", data.editable_qty == 0 || (programYear == 2 || programYear == 3));
            row.find('input[name="member2[]"]').prop("readonly", data.editable_qty == 0 || programYear == 3);
            row.find('input[name="member3[]"]').prop("readonly", data.editable_qty == 0);

            var templateId = data.id_template_benefit;

            // ========== SUBJECT ==========
            var subjectSelect = row.find('select.subject-select');
            var subjectContainer = subjectSelect.closest('td');

            // Hapus span/text-muted yang mungkin ada dari sebelumnya
            subjectContainer.find('span.text-muted').remove();
            // Hapus hidden input lama kalo ada
            subjectContainer.find('input[type="hidden"]').remove();

            // 🔥 Destroy select2 kalo ada
            if (subjectSelect.hasClass('select2-hidden-accessible')) {
              subjectSelect.select2('destroy');
            }

            if (data.multiple_subject == 1) {
              subjectSelect.attr('name', 'subject_' + templateId + '[]');
              subjectSelect.show();
              subjectSelect.empty();
              $.each(allSubjects, function(index, subject) {
                  subjectSelect.append($('<option>', {
                      value: subject.id,
                      text: subject.name
                  }));
              });
              if (data.subject_ids) {
                  var subjectIds = data.subject_ids.split(',').map(Number);
                  subjectSelect.val(subjectIds).trigger('change');
              }
              // 🔥 Re-init select2
              subjectSelect.select2({
                width: '100%',
                placeholder: 'Pilih Subject'
              });
            } else {
              // 🔥 Bersihin isi select
              subjectSelect.empty();
              // Hide select
              subjectSelect.hide();
              // Tambah span dan hidden input
              subjectContainer.append('<span class="text-muted" style="font-size: 11px;">-</span>');
              subjectContainer.append('<input type="hidden" name="subject_' + templateId + '[]" value="">');
            }

            // ========== LEVEL ==========
            var levelSelect = row.find('select.level-select');
            var levelContainer = levelSelect.closest('td');

            // Bersihin
            levelContainer.find('span.text-muted').remove();
            levelContainer.find('input[type="hidden"]').remove();

            // 🔥 Destroy select2 kalo ada
            if (levelSelect.hasClass('select2-hidden-accessible')) {
              levelSelect.select2('destroy');
            }

            if (data.multiple_level == 1) {
              levelSelect.attr('name', 'level_' + templateId + '[]');
              levelSelect.show();
              levelSelect.empty();
              
              // Filter levels berdasarkan banned_level_ids
              let bannedLevels = data.banned_level_ids ? data.banned_level_ids.split(',').map(Number) : [];
              let allowedLevels = allLevels.filter(function(level) {
                return !bannedLevels.includes(Number(level.id));
              });
              // Add options from allowedLevels
              $.each(allowedLevels, function(index, level) {
                levelSelect.append($('<option>', {
                  value: level.id,
                  text: level.name
                }));
              });
              
              // Set selected values
              if (data.level_ids) {
                let levelIds = data.level_ids.split(',').map(Number);
                levelSelect.val(levelIds).trigger('change');
              }
              
              // 🔥 Re-init select2
              levelSelect.select2({
                width: '100%',
                placeholder: 'Pilih Level'
              });
            } else {
              // 🔥 Bersihin isi select
              levelSelect.empty();
              // Hide select
              levelSelect.hide();
              levelContainer.append('<span class="text-muted" style="font-size: 11px;">-</span>');
              levelContainer.append('<input type="hidden" name="level_' + templateId + '[]" value="">');
            }

            // ========== BOOK SERIES ==========
            let bookSeriesSelect = row.find('select.book-select');
            let bookSeriesContainer = bookSeriesSelect.closest('td');

            bookSeriesContainer.find('span.text-muted').remove();
            bookSeriesContainer.find('input[type="hidden"]').remove();

            // 🔥 Destroy select2 kalo ada
            if (bookSeriesSelect.hasClass('select2-hidden-accessible')) {
              bookSeriesSelect.select2('destroy');
            }

            if (data.book_selection == 1) {
              bookSeriesSelect.attr('name', 'book_series_' + templateId + '[]');
              bookSeriesSelect.show();
              bookSeriesSelect.empty();
              
              // Filter book_series berdasarkan allowed_book_ids
              let allowedBookIds = data.allowed_book_ids ? data.allowed_book_ids.split(',').map(Number) : [];
              let allowedBookSeries = allBookSeries.filter(function(book) {
                return allowedBookIds.includes(Number(book.id));
              });

              $.each(allowedBookSeries, function(index, book) {
                bookSeriesSelect.append($('<option>', {
                  value: book.id,
                  text: book.name
                }));
              });
              
              // 🔥 Re-init select2
              bookSeriesSelect.select2({
                width: '100%',
                placeholder: 'Pilih Buku'
              });
            } else {
              // 🔥 Bersihin isi select
              bookSeriesSelect.empty();
              // Hide select
              bookSeriesSelect.hide();
              bookSeriesContainer.append('<span class="text-muted" style="font-size: 11px;">-</span>');
              bookSeriesContainer.append('<input type="hidden" name="book_series_' + templateId + '[]" value="">');
            }

            let program = '<?= $program ?>';
            if((data.benefit_name==="Paket Literasi Menjadi Indonesia" && program=='bsp') || (data.benefit_name==="Paket Literasi Bahasa Inggris Storyland 20 series" && program=='bsp') || data.subbenefit==="Free Copy" || data.benefit_name.includes("ASTA") || data.benefit_name.includes("Oxford") || data.benefit_name.includes("OXFORD") || data.subbenefit==="Bebas Biaya Pengiriman" || data.subbenefit==="Deposit untuk Hidayatullah" || data.benefit_name == "Material" || data.manual_input == "1"){
              row.find('input[name="valben[]"]').prop("readonly", false);
            }else{
              row.find('input[name="valben[]"]').prop("readonly", true);
            }

            if(data.manual_input == "0"){
              row.find('input[name="valben[]"]').prop("readonly", true);
            }
            updateDisabledField(element);
        },
        error: function(xhr, status, error) {
          console.error('Error fetching benefit data:', error);
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
      hiddenValue = hiddenValue <= 1 ? row.find('input[name="valben[]"]').val() : hiddenValue;
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
    console.log('programYear: ', programYear);
    let year1 = fillTheValue(1)

    let checkIfStillMinus = (programYear == 2 || programYear == 3) ? 0 : $('#selisih_benefit1').val();
    checkIfStillMinus = checkIfStillMinus < 0 ? true : false;

    if(program == 'prestasi' || refId) {
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
    var member1 = parseInt(row.find('input[name="member[]"]').val()) || 0;
    var member2 = parseInt(row.find('input[name="member2[]"]').val()) || 0;
    var member3 = parseInt(row.find('input[name="member3[]"]').val()) || 0;

    handleInput(row.find('input[name="valben[]"]'));
    var disabledField2 = row.find('input[name="valben[]"]');
    var defaultvalue = row.find('input[name="valuedefault[]"]').val();
    defaultvalue = isNaN(defaultvalue) ? 0 : defaultvalue;

    let disabledFieldValue = disabledField2.val().replace(/[^0-9]/g, '');

    var total = parseInt(member1)+parseInt(member2)+parseInt(member3);
    if(defaultvalue > 1){
      disabledField.val(formatNumber(total*defaultvalue));
    }else{
      disabledField.val(formatNumber(total*disabledFieldValue));
    }
    accumulateValues();
  }

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

  function populateDropdown(rowId, templateId = null) {
    var selectedTemplate = $('select[name="benefit_id[]"]').map(function() {
      return $(this).val();
    }).get().filter(el => el && el != templateId);

    selectedTemplate = selectedTemplate.filter(el => el)
    $.ajax({
      url: 'get_benefits.php',
      type: 'POST',
      data: {
        program: '<?= $program ?>',
        selectedTemplate: selectedTemplate
      },
      success: async function(data) {
        const $select = $('#' + rowId).find('select[name="benefit_id[]"]');
        const $selectSubject = $('#' + rowId).find('select[name="subject[]"]');
        const $selectLevel = $('#' + rowId).find('select[name="level[]"]');
        const $selectBookSeries = $('#' + rowId).find('select[name="book_series[]"]');
        $select.html(data).select2({
          placeholder: 'Select a benefit',
          templateResult: formatGroupItems,
          closeOnSelect: false,
        });

        $selectSubject.select2({
          placeholder: 'Select a subject',
          closeOnSelect: false,
        })
        $selectLevel.select2({
          placeholder: 'Select a level',
          closeOnSelect: false,
        })
        $selectBookSeries.select2({
          placeholder: 'Select a book series',
          closeOnSelect: false,
        })
        
        $(document).on('mouseenter', '.select2-results__option', function () {
          const title = $(this).attr('title');
          if (title) {
            $(this).tooltip({ title, placement: 'top' }).tooltip('show');
          }
        });

        if (templateId) {
          if ($select.find('option[value="' + templateId + '"]').length) {
            $select.val(templateId).trigger('change.select2');
            await new Promise(r => setTimeout(r, 0));
            getBenefitData($select[0]);
          }
        }
      },
      error: function(xhr, status, error) {
        console.log('error', error);
      }
    });
  }

  function formatGroupItems(data) {
    if (data.element && data.element.tagName === 'OPTGROUP') {
        return $(`<div class="select2-optgroup-label" style="color: #333; padding: 6px 12px; cursor: pointer; background: #f7fafc; border-bottom: 1px solid #e2e8f0;">
                    <b>${data.text}</b>
                </div>`);
    }
    if (data.element && data.element.tagName === 'OPTION') {
      let colorHighlight = $(data.element).attr('data-color'); 
      if(colorHighlight) {
        return $(`<span style="background-color: #${colorHighlight}; padding: 4px 12px; border-radius: 4px; color: white;">${data.text}</span>`);
      }
    }
    return data.text;
  }

  function formatAndValidate(input, alokasi, row) {
    var cleanedInput = input.replace(/[^0-9]/g, '');
    var number = parseFloat(cleanedInput) || 0;
    var formatted = number.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    return number;
  }

  function addRow(tpl) {
    if (x >= maxRows) return;
    x++;

    const tplNode = document.getElementById('row-template');
    const clone = tplNode.content.cloneNode(true);
    const $row = $(clone).find('tr');
    const newRow = 'row' + x;
    $row.attr('id', newRow);
    $row.find('.btn_remove').attr('data-row', newRow);

    $row.find('input[name="id_templates[]"]').val(tpl.id_template_benefit);

    $('#input_form').append($row);

    populateDropdown(newRow, tpl.id_template_benefit);
  }
</script>

<script>
  $(document).ready(function(){
    $('.select2').select2({
      width: '100%'
    });

    $('.select2-benefit').select2({
      placeholder: 'Select a benefit',
      width: '100%',
      dropdownAutoWidth: true
    });
    
    // Atau kalau mau dengan delay biar lebih smooth
    setTimeout(function() {
        $('.sidebar-toggler').click();
    }, 100);
    
    $('#add_row').on('click', function () {
      if (x >= maxRows) return;
      x++;

      const tpl = document.getElementById('row-template');
      const clone = tpl.content.cloneNode(true);
      const $row = $(clone).find('tr');

      $row.attr('id', 'row' + x);
      $row.find('.btn_remove').attr('data-row', 'row' + x);

      $('#input_form').append($row);
      populateDropdown('row' + x);
    });

    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      accumulateValues();
    });

    $('#draft_form').submit(function(e) {
      e.preventDefault();
      Swal.fire({
        title: "Are you sure?",
        text: "Make sure the data is correct before submitting it!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#667eea",
        cancelButtonColor: "#f5576c",
        confirmButtonText: "Yes, save it!"
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Processing...",
            html: '<div class="spinner"></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
          $(this).unbind('submit').submit();
        }
      });
    })

    $(document).on('click', '.select2-optgroup-label', function (e) {
        const $group = $(this).closest('.select2-results__group');
        $group.nextUntil('.select2-results__group').toggle();
        e.stopPropagation();
    });

    $(document).on('click', '.select2-results__group', function () {
        $(this).find('.select2-results__options').toggle();
    });

    $('#submt').prop('disabled', true);
    if (tpl_data.length > 0) {
      tpl_data.forEach(tpl => {
        addRow(tpl);
      });
    }

    setTimeout(() => {
      initEditCalculation();
    }, 0);

    $('form :input').on('change keyup', function () {
      isDirty = true;
    });

    $('form select').on('select2:select select2:unselect', function () {
      isDirty = true;
    });

    $('form').on('submit', function () {
      isSubmitting = true;
      isDirty = false;
    });

    window.addEventListener('beforeunload', function (e) {
      if (isDirty && !isSubmitting) {
        e.preventDefault();
        e.returnValue = '';
      }
    });
  });

  $(document).on('mousedown', 'select[name="benefit_id[]"]', function(event) {
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

  $(document).on('input', 'input[name="valben[]"]', function(event) {
    handleInput($(this));
  });

  const makeMaxIds = <?= json_encode($make_max_ids) ?>;

</script>
<?php include 'footer.php'; ?>