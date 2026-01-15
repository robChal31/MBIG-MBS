
<?php include 'header.php'; ?>
<?php
  include 'db_con.php';
  require 'vendor/autoload.php';
  $config = require 'config.php';
  $benefitSetting = [
      'max_price_percentage' => '',
      'max_discount_percentage' => '',
      'max_benefit_percentage' => ''
  ];

  $id_draft = ISSET($_GET['id_draft']) ? $_GET['id_draft'] : NULL;
  $role     = $_SESSION['role'];
  $id_user  = $_SESSION['id_user'];
  $username = $_SESSION['username'];

  $query    = "SELECT max_price_percentage, max_discount_percentage, max_benefit_percentage FROM benefit_setting LIMIT 1";
  $result   = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
      $benefitSetting = mysqli_fetch_assoc($result);
  }

  $my_plan_id   = NULL;
  $program      = NULL;
  $school_name  = NULL;
  $ec_id        = NULL;
  $segment      = NULL;
  $level        = NULL;
  $wilayah      = NULL;
  $books        = [];
  $selected_levels = [];
  $selected_subjects = [];

  if($id_draft != NULL){
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
        $my_plan_id   = $data['myplan_id'];
        $program      = $data['program'];
        $school_name  = $data['school_name'];
        $ec_id        = $data['id_ec'];
        $segment      = $data['segment'];
        $level        = $data['level'];
        $wilayah      = $data['wilayah'];
      }

      $books_query  = "SELECT calc.*, b.*, bs.name as series_name, bs.id as series_id
                        FROM calc_table as calc
                        LEFT JOIN books as b on b.id = calc.book_id
                        LEFT JOIN book_series as bs on bs.id = b.book_series_id
                        WHERE calc.id_draft = $id_draft";

      $books_result = mysqli_query($conn, $books_query);
      while ($data = $books_result->fetch_assoc()){
        $books[$data['series_id']][] = $data;
      }

      // levels
      $q = mysqli_query($conn, "SELECT level_id FROM program_adoption_levels WHERE draft_id = $id_draft");
      while ($r = mysqli_fetch_assoc($q)) {
          $selected_levels[] = $r['level_id'];
      }

      // subjects
      $q = mysqli_query($conn, "SELECT subject_id FROM program_adoption_subjects WHERE draft_id = $id_draft");
      while ($r = mysqli_fetch_assoc($q)) {
          $selected_subjects[] = $r['subject_id'];
      }
    }
  }
  
?>
<style>
  table.dataTable tbody td {
      padding : 2px !important;
      vertical-align: middle !important;
      text-align: center !important;
      font-size: .9rem !important;
  }

  table.dataTable thead th {
      font-size: .9rem !important;
  }

  .tag-ungu {
      background: #4f46e5 !important;
      color: white !important;
  }

  #event .select2-container {
      z-index: 2050 !important;
  }

  .modal {
      z-index: 1050;
  }

  .modal-backdrop {
      z-index: 1040;
  }

  .select2-container--default .select2-search--dropdown .select2-search__field {
    pointer-events: auto; /* Ensure clicks are registered */
    cursor: text;         /* Change cursor to text input style */
  }

  .series-card {
    transition: all .2s ease;
  }

  .series-card:hover {
    box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,.08);
  }

  .is-invalid {
    border-color: #dc3545 !important;
  }

  .is-invalid:focus {
    box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
  }

  .select2-selection.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 .25rem rgba(220,53,69,.25);
  }

  .btn-submit {
    border-radius: 999px;
    padding: 10px 26px;
    font-size: 15px;
    transition: all 0.25s ease;
    box-shadow: 0 6px 14px rgba(13, 110, 253, 0.25);
  }

  .btn-submit:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(13, 110, 253, 0.35);
  }

  .btn-submit:disabled {
    opacity: 0.65;
    box-shadow: none;
    cursor: not-allowed;
  }

  .btn-submit .btn-icon {
    transition: transform 0.3s ease;
  }

  .btn-submit:not(:disabled):hover .btn-icon {
    transform: translateX(3px);
  }

  * {
    font-size: .9rem !important;
  }

  #contentLoading {
    transition: opacity .2s ease;
  }

  #draftContent {
    transition: opacity .15s ease;
  }

</style>
  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">
      <div class="row">
        <form method="POST" action="new-benefit-ec-input-action1.php" enctype="multipart/form-data" id="input_form_benefit">
          <div class="col-12">
            <div class="card rounded-4 shadow-sm p-4">
              <div class="d-flex align-items-center mb-2">
                <div class="me-3">
                  <i class="fas fa-file-signature text-primary fs-4"></i>
                </div>
                <div>
                  <h5 class="mb-0 fw-semibold fs-5"><?=  $id_draft != NULL ? 'Update' : 'Create' ?> Draft Benefit</h5>
                  <small class="text-muted fs-6">Lengkapi data program dan judul buku yang akan diadopsi</small>
                </div>
              </div>

              <div class="card-body p-4">
                <div class="row g-4">

                  <!-- INPUTTER -->
                  <input type="hidden" name="id_user" value="<?= $id_user ?>">
                  <?php if($id_draft != NULL) : ?>
                    <input type="hidden" name="id_draft" value="<?= $id_draft ?>">
                  <?php endif; ?>
                  <!-- EC -->
                  <?php if($role == 'admin') : ?>
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">Nama EC</label>
                      <div class="select_school_div">
                        <select name="inputEC" id="inputEC" class="form-select form-select-sm select2" required>
                          <option value="" disabled selected>- Select EC -</option>
                          <?php 
                            $sql = "SELECT * FROM user WHERE role = 'ec' AND is_active = 1 ORDER BY generalname ASC";
                            $resultsd1 = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($resultsd1)) : ?>
                              <option value="<?= $row['id_user'] ?>" <?= $row['id_user'] == $ec_id ? 'selected' : '' ?>><?= $row['generalname'] ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="loading_school text-center d-none mt-1">
                        <i class="fas fa-spinner fa-spin text-primary"></i>
                      </div>
                    </div>
                  <?php else : ?>
                    <input type="hidden" name="inputEC" value="<?= $id_user ?>">
                  <?php endif; ?>

                  <!-- MY PLAN -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted d-block">My Plan Reference</label>
                    <div class="select_school_div">
                      <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2">
                        <option value="" selected>Select a plan</option>
                      </select>
                    </div>
                    <div class="loading_school text-center d-none mt-1">
                      <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                  </div>

                  <!-- SEKOLAH -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted d-block">Nama Sekolah</label>
                    <div class="select_school_div">
                      <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required>
                        <option value="" disabled selected>- Select School -</option>
                      </select>
                    </div>
                    <div class="loading_school text-center d-none mt-1">
                      <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                  </div>

                  <!-- PROGRAM -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted d-block">Program</label>
                    <select name="program" id="program" class="form-select form-select-sm select2" required>
                      <option value="" disabled selected>Select a program</option>
                    </select>
                    <small class="programNote text-danger d-block mt-1" style="font-size: 11px !important;">
                      Pilih program terlebih dahulu sebelum menambahkan buku
                    </small>
                  </div>

                  <!-- SEGMENT -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted d-block">Segment</label>
                    <select name="segment" id="segment_input" class="form-select form-select-sm select2" required>
                      <option value="" disabled selected>- Select Segment -</option>
                      <?php 
                        $seg_sql = "SELECT * FROM segments";
                        $segQ = mysqli_query($conn, $seg_sql);
                        while ($row = mysqli_fetch_assoc($segQ)) : ?>
                          <option value='<?= $row['id'] ?>' <?=  ($segment == $row['id'] || $segment == strtolower($row['segment'])) ? 'selected' : '' ?>><?= $row['segment'] ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- ADOPTION LEVEL -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Cakupan Jenjang Program</label>
                    <select name="program_adoption_levels[]" id="adoption_levels" class="form-select form-select-sm select2" multiple required>
                      <?php
                        $lvl_sql = "SELECT * FROM levels";
                        $levelsQ = mysqli_query($conn, $lvl_sql);
                        while ($row = mysqli_fetch_assoc($levelsQ)) : ?>
                          <option value='<?= $row['id'] ?>' <?=  ($level == $row['id'] || $level == strtolower($row['name'])) ? 'selected' : '' ?>><?= $row['name'] ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- ADOPTION SUBJECT -->
                  <div class="col-md-6">
                    <label class="form-label small text-muted">Cakupan Subjek Program</label>
                    <select name="program_adoption_subjects[]" id="adoption_subjects" class="form-select form-select-sm select2" multiple required>
                      <?php
                        $sub_sql = "SELECT * FROM subjects";
                        $subsq = mysqli_query($conn, $sub_sql);
                        while ($row = mysqli_fetch_assoc($subsq)) {
                          echo "<option value='{$row['id']}'>{$row['name']}</option>";
                        }
                      ?>
                    </select>
                  </div>

                  <!-- WILAYAH -->
                  <div class="<?= $role == 'ec' ? 'col-md-4' : 'col-md-6' ?>">
                    <label class="form-label small text-muted">Wilayah Sekolah</label>
                    <input type="text" name="wilayah" value="<?=  $wilayah ?>" class="form-control form-control-sm" placeholder="Ex: Jakarta" required>
                  </div>

                  <!-- DISCOUNT -->
                  <div class="<?= $role == 'ec' ? 'col-md-4' : 'col-md-6' ?>">
                    <label class="form-label small text-muted">Discount Program (%)</label>
                    <input id="discount_program" type="number" name="discount_program" class="form-control form-control-sm" max="100" placeholder="0 - 100">
                    <small class="text-muted d-block mt-1" style="font-size: 11px !important;">
                      Diskon ini akan digunakan pada semua judul, untuk memberikan diskon khusus pada judul tertentu, silahkan tambahkan diskon ketika menambahkan judul pada book list
                    </small>
                  </div>

                  <div class="<?= $role == 'ec' ? 'col-md-4' : 'col-md-6' ?>">
                    <label class="form-label small text-muted">Additional Price</label>
                    <input placeholder="Ex: 5000" id="additional_price" type="text" name="additional_price" class="form-control form-control-sm only_number">
                    <small class="text-muted d-block mt-1" style="font-size: 11px !important;">
                      Penambahan harga ini, akan digunakan untuk penyesuaian harga dasar setiap judul buku tergantung pada wilayah sekolah
                    </small>
                  </div>

                </div>
              </div>
            </div>

          </div>

          <div class="col-12 mt-4">
            <div class="card shadow-sm border-0">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                  <strong style="font-size: 16px;">📚 Book List</strong>
                  <div class="small opacity-75">Tambahkan buku yang akan digunakan dalam program</div>
                </div>

                <button type="button" id="btnAddTitle" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#draftBenefitModal" disabled>
                  <i class="bi bi-plus"></i> Add Title
                </button>
              </div>

              <div class="p-4 position-relative" id="contentWrapper">

                <!-- LOADING -->
                <?php if ($id_draft != null): ?>
                  <div id="contentLoading"
                      class="position-absolute top-0 start-0 w-100 d-flex flex-column justify-content-center align-items-center bg-white" style="z-index: 10; height: 250px;">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <small class="text-muted">Loading draft data...</small>
                  </div>
                <?php endif; ?>

                <div id="draftContent" class="<?= $id_draft ? 'd-none' : '' ?>">
                  <!-- NOTE -->
                  <div class="programNote alert alert-warning d-flex align-items-center gap-2 py-2 px-3 mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    <small>
                      Silakan <strong>pilih program terlebih dahulu</strong> sebelum menambahkan buku.
                    </small>
                  </div>

                  <!-- EMPTY STATE -->
                  <div id="emptyState" class="text-center text-muted py-5 border rounded bg-light">
                    <i class="bi bi-book-half fs-1 mb-3 d-block"></i>

                    <h5 class="fw-semibold mb-2">Belum ada buku yang ditambahkan</h5>

                    <p class="mb-3" style="max-width: 420px; margin: 0 auto;">
                      Pilih program terlebih dahulu, lalu tambahkan buku sesuai dengan rencana.
                    </p>

                    <span class="badge bg-warning text-dark px-3 py-2">
                      ⚠️ Program wajib dipilih
                    </span>
                  </div>

                  <!-- TITLE LIST -->
                  <div id="listWrapper">
                    <div id="titleList" class="d-flex flex-column gap-3"></div>
                  </div>

                  <!-- FOOTER -->
                  <div class="d-flex justify-content-between align-items-center mt-4">
                    <h5 class="mb-0">
                      Total Alokasi Benefit:
                      <span id="accumulated_values" class="fw-bold text-primary">0</span>
                    </h5>

                    <button type="submit" class="btn btn-primary btn-submit fw-semibold px-4 d-flex align-items-center gap-2" id="submt" disabled>

                      <span class="btn-icon">
                        <i class="bi bi-arrow-right"></i>
                      </span>

                      <span class="btn-text">Submit</span>

                      <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm"></span>
                      </span>
                    </button>

                  </div>

                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- Form End -->

    <template id="seriesCardTemplate">
      <div class="card shadow-sm border border-primary series-card" data-series-id="">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <strong class="text-primary series-title" style="font-size: 15px !important;"></strong>

            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary toggle-series">
                <i class="bi bi-chevron-up"></i>
              </button>

              <button type="button" class="btn btn-sm btn-outline-danger remove-series">
                <i class="bi bi-trash"></i>
              </button>

              <button type="button" class="btn btn-sm btn-outline-success add-book">
                <i class="bi bi-plus"></i>
              </button>
            </div>
          </div>

          <div class="book-list"></div>

        </div>
      </div>
    </template>

    <template id="bookRowTemplate">
      <div class="book-row border rounded p-3 position-relative mb-3">

        <button type="button"
          class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-book">
          <i class="bi bi-x"></i>
        </button>

        <div class="row g-3">

          <div class="col-md-5">
            <label class="form-label small">Judul Buku</label>
            <input type="hidden" name="book_ids[]" class="form-control form-control-sm">
            <select name="titles[]" class="form-select form-select-sm book"></select>
          </div>

          <div class="col-md-1">
            <label class="form-label small">Level</label>
            <select name="levels[]" class="form-select form-select-sm level"></select>
          </div>

          <div class="col-md-2">
            <label class="form-label small">Jenis Buku</label>
            <select name="booktype[]" class="form-select form-select-sm booktype"></select>
          </div>

          <div class="col-md-2">
            <label class="form-label small">Jumlah Siswa</label>
            <input type="text" name="jumlahsiswa[]" class="form-control form-control-sm only_number" onchange="updateDisabledField(this)">
          </div>

          <div class="col-md-2">
            <label class="form-label small">Usulan Harga</label>
            <input type="text" name="usulanharga[]" class="form-control form-control-sm only_number" onchange="updateDisabledField(this)">
          </div>

          <div class="col-md-2">
            <label class="form-label small">Harga Normal</label>
            <input type="text" name="harganormal[]" class="form-control form-control-sm only_number" onchange="updateDisabledField(this)">
          </div>

          <div class="col-md-2">
            <label class="form-label small">Diskon (%)</label>
            <input type="text" name="diskon[]" class="form-control form-control-sm only_number" onchange="updateDisabledField(this)">
          </div>

          <div class="col-md-2">
            <label class="form-label small">Harga Diskon</label>
            <input type="text" name="aftd[]" class="form-control form-control-sm" readonly>
          </div>

          <div class="col-md-2">
            <label class="form-label small">Revenue Program</label>
            <input type="text" name="afto[]" class="form-control form-control-sm" readonly>
          </div>

          <div class="col-md-2">
            <label class="form-label small">Revenue Normal</label>
            <input type="text" name="befo[]" class="form-control form-control-sm" readonly>
          </div>

          <div class="col-md-2">
            <label class="form-label small">Alokasi Sekolah</label>
            <input type="text" name="alokasi[]" class="form-control form-control-sm" readonly>
          </div>

        </div>
      </div>
    </template>

    <!-- Modal -->
    <div class="modal fade" id="draftBenefitModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header bg-primary">
            <h6 class="modal-title fw-bold text-white">Select Title to Add</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-7">
                  <label class="form-label fw-semibold">Series</label>
                  <select class="form-select form-select-sm select2" id="modalBookSeries">
                    <option value="">Select series</option>
                    <?php
                      $sql = "SELECT bs.id, bs.name, lv.name as level 
                              FROM book_series as bs
                              LEFT JOIN levels as lv on lv.id = bs.level_id
                                WHERE bs.is_active = 1";
                      $result = $conn->query($sql);
                      while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . $row['name'] . ' - [' . $row['level'] . ']</option>';
                      }
                    ?>
                  </select>
                </div>

                <div class="col-md-5">
                  <label class="form-label fw-semibold">Type</label>
                  <select class="form-select form-select-sm select2" id="modalBookType" multiple>
                    <option value="workbook">Workbook</option>
                    <option value="textbook">Textbook</option>
                    <option value="teacher guide">Teacher Guide</option>
                    <option value="other">Other</option>
                  </select>
                  <small class="d-block mt-1" style="font-size: 11px !important;">
                    Kosongkan jika ingin mengambil semua jenis buku
                  </small>
                </div>

                <div class="col-md-4">
                  <label class="form-label small">Jumlah Siswa</label>
                  <input type="text" id="modalJumlah" class="form-control form-control-sm only_number">
                </div>

                <div class="col-md-4">
                  <label class="form-label small">Usulan Harga</label>
                  <input type="text" id="modalHarga" class="form-control form-control-sm only_number">
                </div>

                <div class="col-md-4">
                  <label class="form-label small">Diskon (%)</label>
                  <input type="text" id="modalDiskon" class="form-control form-control-sm only_number">
                  <small class="d-block mt-1" style="font-size: 11px !important;">
                    Diskon ini digunakan hanya pada series yang akan dipilih, kosongkan jika ingin menggunakan diskon program pada input sebelumnya(jika ada)
                  </small>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary btn-sm" onclick="addTitleCard();">Add</button>
            </div>

        </div>
      </div>
    </div>  


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
  
  function fetchShool(email, schoolId = null) {
    schoolReady = false;
    return $.ajax({
      url: `https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=${email}`,
      type: 'GET',
      dataType: 'json',
      beforeSend() {
        $('.select_school_div').addClass('d-none');
        $('.loading_school').removeClass('d-none');
      },
      success(response) {
        let options = '<option value="" disabled>Select a school</option>';

        response.forEach(data => {
          const selected = schoolId == data.id ? 'selected' : '';
          options += `<option value="${data.id}" ${selected}>${data.name}</option>`;
        });

        $('#select_school').html(options).val(schoolId).trigger('change').select2({ width: '100%' });
        schoolReady = true;
      },
      complete() {
        $('.loading_school').addClass('d-none');
        $('.select_school_div').removeClass('d-none');
      }
    });
  }

  function clearMyplan() {
    $('#select_school').val('').trigger('change').off('select2:opening select2:selecting');
    $('#program').val('').trigger('change').off('select2:opening select2:selecting');
    $('#segment_input').val('').trigger('change').off('select2:opening select2:selecting');
    $('#adoption_levels').val('').trigger('change').off('select2:opening select2:selecting');
    $('#adoption_subjects').val('').trigger('change').off('select2:opening select2:selecting');
    $('input[name="wilayah"]').val('').prop('readonly', false);
  }

  function addTitleCard() {
    const seriesId          = $('#modalBookSeries').val();
    const seriesText        = $('#modalBookSeries option:selected').text();
    const bookType          = $('#modalBookType').val();
    const jumlah            = $('#modalJumlah').val();
    const harga             = $('#modalHarga').val();
    const diskon            = $('#modalDiskon').val();
    const additionalPrice   = $('#additional_price').val();
    const discountProgram   = $('#discount_program').val();

    if (!seriesId) return alert('Select book series');
    if (!jumlah) return alert('Input jumlah siswa');
    if (!harga) return alert('Input harga usulan');

    if (document.querySelector(`.series-card[data-series-id="${seriesId}"]`)) {
      return alert('Series already added');
    }

    $.getJSON('get_books.php', { series_id: seriesId, book_type: bookType }, function (books) {

      if(books.length === 0) {
        alert('Daftar buku kosong');
      }else {
        const seriesTpl   = document.getElementById('seriesCardTemplate');
        const seriesClone = seriesTpl.content.cloneNode(true);
        const seriesCard  = seriesClone.querySelector('.series-card');
        seriesCard.dataset.seriesId = seriesId;
        seriesClone.querySelector('.series-title').textContent = seriesText;

        const bookList = seriesClone.querySelector('.book-list');
        books.forEach(book => {
          const bookTpl   = document.getElementById('bookRowTemplate');
          const bookClone = bookTpl.content.cloneNode(true);

          bookClone.querySelector('.book').innerHTML = `<option value="${book.name}">${book.name}</option>`;
          $(bookClone.querySelector('.book')).select2({
            width: '100%',
            placeholder: 'Select book',
            dropdownParent: seriesCard
          })
          bookClone.querySelector('.level').innerHTML = `<option value="${book.grade}">${book.grade}</option>`;

          bookClone.querySelector('.booktype').innerHTML = `<option value="${bookType || book.type}">${book.type || bookType}</option>`;

          bookClone.querySelector('[name="book_ids[]"]').value = book.id;
          bookClone.querySelector('[name="jumlahsiswa[]"]').value = jumlah;
          bookClone.querySelector('[name="usulanharga[]"]').value = harga;
          bookClone.querySelector('[name="diskon[]"]').value = diskon || discountProgram;

          let bookPrice = (book.price + (additionalPrice ? removeNonDigits(additionalPrice) : 0));
          const hargaNormalInput = bookClone.querySelector('[name="harganormal[]"]');
          hargaNormalInput.dataset.bookPrice = book.price;
          hargaNormalInput.value = formatNumber(bookPrice);

          bookList.appendChild(bookClone);

          updateDisabledField(
            bookList.lastElementChild.querySelector('[name="jumlahsiswa[]"]')
          );
        });

        $('#draftBenefitModal').modal('hide');
        $('#emptyState').fadeOut(200);

        document.getElementById('titleList').appendChild(seriesClone);
        accumulateAlokasi();
      }

    });
  }

  function getSchoolPrograms(schoolId, selectedProgramCode = null) {
    $.ajax({
      url: 'get-school-program.php',
      type: 'POST',
      dataType: 'json',
      data: {
          school_id: schoolId,
      },
      success: function(response) {
        let options = '<option value="" disabled selected>Select a program</option>';
        response.map((data) => {
          const selected = selectedProgramCode && data.code && selectedProgramCode.toLowerCase() === data.code.toLowerCase() ? 'selected' : '';
          options += `<option value="${data.code}" ${selected}>${data.name}</option>`
        }) 

        $('#program').html(options);
        // $('#program').select2();
      },
      error: function(jqXHR, textStatus, errorThrown) {
          console.log('Error:', textStatus, errorThrown);
          alert("Failed to get program")
      }
    });
  }

  function getMyPlanRef(myplanId = null, action = null) {
    const ec = $('input[name="inputEC"]').val() ?? $('select[name="inputEC"]').val();
    if(ec) {
      return $.ajax({
        url: 'get-ec-plan.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_draft: idDraft,
            ec: ec,
        },
        success: function(response) {
          let options = '<option value="" selected>Select a plan</option>';
          response.map((data, index) => {
            let selected = myplanId !== null ? (myplanId == data.value ? 'selected' : '') : (index === 0 ? 'selected' : '');
            options += `<option value="${data.value}" ${selected}>${data.label}</option>`
          }) 

          $('#myplan_id').html(options);
          let planId = $('#myplan_id').val();
          if(planId) {
            getPlanData(planId);
          }else {
            if(action != 'init') clearMyplan();
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log('Error:', textStatus, errorThrown);
          alert("Failed to get myplan")
        }
      });
    }
    return;
  }

  function getPlanData(planId) {

    if (!schoolReady) {
      console.warn('School not ready yet');
      return;
    }

    $.ajax({
      url: 'get-myplan-data.php',
      method: 'POST',
      data: { myplan_id: planId },
      dataType: 'json',
      success: function (res) {
        console.log('plan data: ', res);
        if (res && res.program) {
          const programName = res.program.trim();
          if (res.level_ids.length) {
            $('#adoption_levels').val(res.level_ids).trigger('change').on('select2:opening select2:selecting', e => e.preventDefault());
          }
          if (res.subject_ids.length) {
            $('#adoption_subjects').val(res.subject_ids).trigger('change').on('select2:opening select2:selecting', e => e.preventDefault());
          }
          $('#program').val(programName).trigger('change').on('select2:opening select2:selecting', e => e.preventDefault());
          $('#select_school').val(res.school_id).trigger('change.select2').on('select2:opening select2:selecting', e => e.preventDefault());
          if($('#select_school').val() == null) {
            $('#select_school').val('').trigger('change').off('select2:opening select2:selecting');
          }
          $('#segment_input').val(res.segment).trigger('change').on('select2:opening select2:selecting', e => e.preventDefault());
          $('input[name="wilayah"]').val(res.wilayah).prop('readonly', true);
        } else {
          $('#program').val('').trigger('change');
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('Error:', textStatus, errorThrown);
        alert("Failed to get myplan");
      }
    });
  }

  function removeNonDigits(numberString) {
    let nonDigitRegex = /[^\d-]/g;

    let result = numberString.replace(nonDigitRegex, '');

    return result ? parseInt(result) : 0;
  }
  
  function updateDisabledField(element) {
    var row = $(element).closest('.book-row');

    var disabledField = row.find('input[name="alokasi[]"]');
    var aftd = row.find('input[name="aftd[]"]');
    var befo = row.find('input[name="befo[]"]');
    var afto = row.find('input[name="afto[]"]');

    var benefitSetting = <?php echo json_encode($benefitSetting); ?>;

    let maxProgramPrice = benefitSetting.max_price_percentage ?? 100;
    let maxDiscount     = benefitSetting.max_discount_percentage ?? 30;

    var jumlah = removeNonDigits(row.find('input[name="jumlahsiswa[]"]').val()) || 0;
    var usulan = removeNonDigits(row.find('input[name="usulanharga[]"]').val()) || 0;
    var normal = removeNonDigits(row.find('input[name="harganormal[]"]').val()) || 0;
    var diskon = removeNonDigits(row.find('input[name="diskon[]"]').val()) || 0;

    if (diskon > maxDiscount) {
      diskon = maxDiscount;
      alert("Diskon melebihi ketentuan, silakan ajukan persetujuan ke HOR/Top Leader terlebih dahulu. Terima kasih");
      row.find('input[name="diskon[]"]').val(maxDiscount);
    }

    var setelahDiskon = normal - (diskon / 100 * normal);
    aftd.val(formatNumber(setelahDiskon));

    var onepriceRevenue = jumlah * usulan;
    afto.val(formatNumber(onepriceRevenue));

    var sebelumOneprice = jumlah * setelahDiskon;
    befo.val(formatNumber(sebelumOneprice));

    var alokasi = onepriceRevenue - sebelumOneprice;
    disabledField.val(formatNumber(alokasi));

    accumulateAlokasi();
  }

  function sumArray(array) {
    return array.reduce(function (accumulator, currentValue) {
      return accumulator + currentValue;
    }, 0);
  }

  function accumulateAlokasi() {
    let total = 0;
    $('input[name="alokasi[]"]').each(function () {
      const val = removeNonDigits($(this).val()) || 0;
      total += val;
    });

    $('#accumulated_values').text(formatNumber(total));
    $('#submt').prop('disabled', total < 0);
  }

  function formatNumber(number) {
    number = Math.ceil(number)
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

  async function getUserData(userId) {
    try {
      $('.loading_school').removeClass('d-none');

      const res = await $.ajax({
        url: 'get-user-data.php',
        type: 'POST',
        dataType: 'json',
        data: { id_user: userId }
      });

      return res;

    } catch (err) {
      console.error('Error:', err);
      alert('Failed to get user data');
      return null;

    } finally {
      $('.loading_school').addClass('d-none');
    }
  }

  function hideContentLoading() {
    $('#contentLoading').fadeOut(200, function () {
      $(this).remove();
      $('#draftContent').toggleClass('d-none').fadeIn(150);
    });
  }

  async function initDraftData(ecId, schoolId, programCode) {
    if (!ecId) {
      hideContentLoading(); // safety
      return;
    }

    try {
      const user = await getUserData(ecId);
      if (!user) return;

      await fetchShool(user.username, schoolId);

      await getMyPlanRef(myplanId, 'init');
    } catch (err) {
      console.error('Init draft failed:', err);
    } finally {
      hideContentLoading();
      $('#program').val(programCode.toLowerCase()).trigger('change');
    }
  }

  let schoolReady = false;

</script>
<script>
  let idDraft         = '<?= $id_draft ?? 'null' ?>';
  let myplanId        = '<?= $my_plan_id ?? 'null' ?>';
  let ecDraftOwnerID  = '<?= $ec_id ?? 'null' ?>';
  let schoolId        = '<?= $school_name ?? 'null' ?>';
  let programCode     = '<?= $program ?? 'null' ?>';
  let booksBySeries   = <?= json_encode($books) ?>;
  console.log('booksBySeries: ', booksBySeries);
  const selectedLevels = <?= json_encode($selected_levels) ?>;
  const selectedSubjects = <?= json_encode($selected_subjects) ?>;

  if (selectedLevels.length) {
    $('#adoption_levels').val(selectedLevels).trigger('change');
  }

  if (selectedSubjects.length) {
    $('#adoption_subjects').val(selectedSubjects).trigger('change');
  }

  $(document).on('click', '.toggle-series', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $btn = $(this);
    const $card = $btn.closest('.series-card');
    const $bookList = $card.find('.book-list').first();
    const $icon = $btn.find('i');

    if (!$bookList.length) return;

    $bookList.stop(true, true).slideToggle(200);

    $icon.toggleClass('bi-chevron-up bi-chevron-down');
  });

  $(document).ready(function(){
    $('.select2').select2();

    $('.select2[multiple]').select2({
      placeholder: 'Select option',
      templateSelection: function (data, container) {
        $(container).addClass('tag-ungu');
        return data.text;
      }
    });
    
    $('#draftBenefitModal').on('shown.bs.modal', function () {
      $(this).find('.select2').select2({ width:'100%', dropdownParent:$(this) });
    });

    $(document).on('input', '.only_number', function() {
        let sanitizedValue = $(this).val().replace(/^0+|\D/g, '');

        let formattedValue = sanitizedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        $(this).val(formattedValue);
    });

    $('#inputEC').on('change', async function () {
      const userId = $(this).val();
      if (!userId) return;

      const userData = await getUserData(userId);
      if (!userData) return;

      await fetchShool(userData.username);
      getMyPlanRef();
    });

    $('#select_school').on('change', function() {
      var schoolId = $(this).val();
      if (schoolId) {
        let isMyplanSelected = $('#myplan_id').val();
        if (!isMyplanSelected) {
          getSchoolPrograms(schoolId);
        }
      }
    });

    $('#program').on('change', function() {
      const hasProgram = !!$(this).val();
      $('#btnAddTitle').prop('disabled', !hasProgram);
      $('#submt').prop('disabled', !hasProgram);
      $('.programNote').toggleClass('d-none', hasProgram);
      if(!hasProgram) {
        $('#emptyState').fadeIn(200);
        $('#listWrapper').fadeOut(200);
      }else {
        if ($('#titleList .series-card').length === 0) {
          $('#emptyState').fadeIn(200);
        }else {
          $('#emptyState').fadeOut(200);
        }
        $('#listWrapper').fadeIn(200);
      }
    });

    $('#myplan_id').on('change', function () {
      const selectedId = $(this).val();
      clearMyplan()

      if (!selectedId) return;
      getPlanData(selectedId);
      
    });

    $('#additional_price').on('change', function () {
      const additionalPrice = removeNonDigits($(this).val()) || 0;

      $('input[name="harganormal[]"]').each(function () {
        const basePrice = parseInt($(this).data('bookPrice')) || 0;
        const newPrice  = basePrice + additionalPrice;

        $(this).val(formatNumber(newPrice));

        // hitung ulang semua turunan
        updateDisabledField(
          $(this).closest('.book-row')
            .find('[name="jumlahsiswa[]"]')[0]
        );
      });

      accumulateAlokasi();
    });

    $('#submt').on('click', function (e) {
      const form = document.getElementById('input_form_benefit');
      const $btn = $(this);

      // reset error
      $(form).find('.is-invalid').removeClass('is-invalid');

      // ===== CEK ALOKASI =====
      const $alokasiInputs = $('input[name="alokasi[]"]');
      let total = 0;

      if ($alokasiInputs.length === 0) {
        e.preventDefault();
        alert('Tambahkan buku terlebih dahulu.');
        return;
      }

      $alokasiInputs.each(function () {
        const val = removeNonDigits($(this).val()) || 0;
        total += val;
      });

      if (total < 0) {
        e.preventDefault();
        alert('Total alokasi tidak boleh kurang dari 0.');
        return;
      }

      // ===== HTML5 VALIDATION =====
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();

        form.reportValidity();

        let firstInvalid = null;

        $(form).find('[required]').each(function () {
          const el = this;

          if (!el.checkValidity()) {
            if (!firstInvalid) firstInvalid = el;

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

        if (firstInvalid) {
          $('html, body').animate({
            scrollTop: $(firstInvalid).offset().top - 120
          }, 300);
        }

        return;
      }

      // ===== VALID =====
      $btn.prop('disabled', true);
      $btn.find('.btn-text').text('Submitting...');
      $btn.find('.btn-icon').addClass('d-none');
      $btn.find('.btn-spinner').removeClass('d-none');

      form.submit();
    });

    // input & select biasa
    $(document).on('input change', '[required]', function () {
      if (this.checkValidity()) {
        $(this).removeClass('is-invalid');
      }
    });

    // select2
    $(document).on('change', 'select.select2', function () {
      if ($(this).val()) {
        $(this)
          .next('.select2-container')
          .find('.select2-selection')
          .removeClass('is-invalid');
      }
    });

    getSchoolPrograms();
    
    initDraftData(ecDraftOwnerID, schoolId, programCode)

    for (const [seriesId, savedBooks] of Object.entries(booksBySeries)) {
      console.log('seriesId', seriesId);
      if(seriesId) {
        $.getJSON('get_books.php', { series_id: seriesId}, function (books) {

          if(books.length === 0) {
            alert('Daftar buku kosong, mohon infokan ke tim developer');
          }else {
            const seriesTpl   = document.getElementById('seriesCardTemplate');
            const seriesClone = seriesTpl.content.cloneNode(true);
            const seriesCard  = seriesClone.querySelector('.series-card');
            seriesCard.dataset.seriesId = seriesId;
            seriesClone.querySelector('.series-title').textContent = savedBooks[0].series_name ?? 'Unknown Series';

            const bookList = seriesClone.querySelector('.book-list');
            let additionalPriceToAdd = 0;

            books.forEach(book => {
              const isBookSaved = savedBooks.filter(el => el.book_id === book.id);
              if(isBookSaved.length <= 0) return;
              let selectedBook = isBookSaved[0];
              const bookTpl   = document.getElementById('bookRowTemplate');
              const bookClone = bookTpl.content.cloneNode(true);

              bookClone.querySelector('.book').innerHTML = `<option value="${book.name}">${book.name}</option>`;
              $(bookClone.querySelector('.book')).select2({
                width: '100%',
                placeholder: 'Select book',
                dropdownParent: seriesCard
              })
              bookClone.querySelector('.level').innerHTML = `<option value="${book.grade}">${book.grade}</option>`;

              bookClone.querySelector('.booktype').innerHTML = `<option value="${book.type}">${book.type}</option>`;

              bookClone.querySelector('[name="book_ids[]"]').value = book.id;
              bookClone.querySelector('[name="jumlahsiswa[]"]').value = selectedBook.qty;
              bookClone.querySelector('[name="usulanharga[]"]').value = formatNumber(selectedBook.usulan_harga);
              bookClone.querySelector('[name="diskon[]"]').value = selectedBook.discount;

              let additionalPrice = parseFloat(selectedBook.normalprice) - parseFloat(selectedBook.price);
              let bookPrice = parseFloat(selectedBook.price) + additionalPrice;
              const hargaNormalInput = bookClone.querySelector('[name="harganormal[]"]');
              hargaNormalInput.dataset.bookPrice = book.price;
              hargaNormalInput.value = formatNumber(bookPrice);

              bookList.appendChild(bookClone);

              updateDisabledField(
                bookList.lastElementChild.querySelector('[name="jumlahsiswa[]"]')
              );
              additionalPriceToAdd = additionalPrice;
            });

            $('#additional_price').val(formatNumber(additionalPriceToAdd))

            $('#draftBenefitModal').modal('hide');
            $('#emptyState').fadeOut(200);

            document.getElementById('titleList').appendChild(seriesClone);
            accumulateAlokasi();
          }

        });
      }else {
        // masih dipertimbangkan
        let listofSavedBooksWithoudIds = {};

        for (const value of Object.values(savedBooks)) {
          let bookTitle = value.book_title.split('|').map(el => el.trim());
          const rawTitle = bookTitle[0] ?? '';
          const rawLevel = bookTitle[1] ?? '';
          const rawType  = bookTitle[2] ?? '';

          const tempBookTitle = rawTitle.trim();
          const tempBookLevel = rawLevel.includes(' ')
            ? rawLevel.split(' ').pop()
            : rawLevel.trim();

          const tempBookType = rawType.trim();

          // safety guard
          if (!tempBookTitle) continue;

          if (!listofSavedBooksWithoudIds[tempBookTitle]) {
            listofSavedBooksWithoudIds[tempBookTitle] = {
              level: [],
              type: []
            };
          }

          // level (avoid duplicate)
          if (!listofSavedBooksWithoudIds[tempBookTitle].level.includes(tempBookLevel)) {
            listofSavedBooksWithoudIds[tempBookTitle].level.push(tempBookLevel);
          }

          // type (avoid duplicate)
          if (!listofSavedBooksWithoudIds[tempBookTitle].type.includes(tempBookType)) {
            listofSavedBooksWithoudIds[tempBookTitle].type.push(tempBookType);
          }
        }

        console.log('listofSavedBooksWithoudIds: ', listofSavedBooksWithoudIds)
      }

    }

  });

  document.addEventListener('click', function (e) {

    /* REMOVE BOOK */
    if (e.target.closest('.remove-book')) {
      const bookRow = e.target.closest('.book-row');
      const series  = e.target.closest('.series-card');

      bookRow.remove();

      if (series && series.querySelectorAll('.book-row').length === 0) {
        series.remove();
      }
      accumulateAlokasi();
      return;
    }

    /* ADD BOOK */
    if (e.target.closest('.add-book')) {
      const seriesCard = e.target.closest('.series-card');
      const seriesId   = seriesCard.dataset.seriesId;
      const bookList   = seriesCard.querySelector('.book-list');
      const prevRow    = bookList.querySelector('.book-row:last-child');

      if (!seriesId) {
        alert('Series ID not found');
        return;
      }

      $.getJSON('get_books.php', { series_id: seriesId }, function (books) {

        if (!books || books.length === 0) {
          alert('Tidak ada buku untuk series ini');
          return;
        }

        const tpl   = document.getElementById('bookRowTemplate');
        const clone = tpl.content.cloneNode(true);

        const row = clone.querySelector('.book-row');

        const bookSelect  = row.querySelector('.book');
        $(bookSelect).select2({
          width: '100%',
          placeholder: 'Select book',
          dropdownParent: seriesCard
        });

        const levelSelect   = row.querySelector('.level');
        const typeSelect    = row.querySelector('.booktype');
        const hiddenBook    = row.querySelector('input[name="book_ids[]"]');

        // populate judul buku
        bookSelect.innerHTML = '<option value="">Select book</option>';
        books.forEach(b => {
          bookSelect.innerHTML += `<option value="${b.name}" data-id="${b.id}" data-price="${b.price}" data-level="${b.grade}" data-type="${b.type}">
            ${b.name}
          </option>`;
        });

        // onchange buku → auto isi field lain
        $(bookSelect).on('change', function (e) {
          const opt = $(this).find(':selected');
          if (!opt.length) return;

          hiddenBook.value = opt.data('id');

          levelSelect.innerHTML = `<option value="${opt.data('level')}">${opt.data('level')}</option>`;

          typeSelect.innerHTML = `<option value="${opt.data('type')}">${opt.data('type')}</option>`;

          const additionalPrice   = $('#additional_price').val();

          let bookPrice = (book.price + (additionalPrice ? removeNonDigits(additionalPrice) : 0));
          const hargaNormalInput = row.querySelector('[name="harganormal[]"]');
          hargaNormalInput.dataset.bookPrice = book.price;
          hargaNormalInput.value = formatNumber(bookPrice);

          if (prevRow) {
            row.querySelector('[name="jumlahsiswa[]"]').value =
              prevRow.querySelector('[name="jumlahsiswa[]"]').value || '';

            row.querySelector('[name="usulanharga[]"]').value =
              prevRow.querySelector('[name="usulanharga[]"]').value || '';

            row.querySelector('[name="diskon[]"]').value =
              prevRow.querySelector('[name="diskon[]"]').value || '';
          }

          updateDisabledField(
            row.querySelector('[name="jumlahsiswa[]"]')
          );
        });

        bookList.appendChild(clone);
      });

      return;
    }

    /* REMOVE SERIES */
    if (e.target.closest('.remove-series')) {
      const seriesCard = e.target.closest('.series-card');

      if (!confirm('Remove this series and all books?')) return;

      $(seriesCard).fadeOut(200, function () {
        $(this).remove();

        // hitung ulang alokasi setelah DOM bersih
        accumulateAlokasi();

        // kalau sudah tidak ada series → tampilkan empty state
        if ($('#titleList .series-card').length === 0) {
          $('#emptyState').fadeIn(200);
        }
      });

      return;
    }

  });
</script>

<?php include 'footer.php'; ?>