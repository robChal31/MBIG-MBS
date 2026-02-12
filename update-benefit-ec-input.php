
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

  $role     = $_SESSION['role'];
  $id_user  = $_SESSION['id_user'];
  $username = $_SESSION['username'];

  $query    = "SELECT max_price_percentage, max_discount_percentage, max_benefit_percentage FROM benefit_setting LIMIT 1";
  $result   = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $benefitSetting = mysqli_fetch_assoc($result);
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
                  <h5 class="mb-0 fw-semibold fs-5">Update PK yang Sudah Adopsi</h5>
                  <small class="text-muted fs-6 d-block">Hanya untuk Tahun ke 2 atau ke 3</small>
                  <small class="text-muted fs-10">Lengkapi data program dan judul buku yang akan diadopsi</small>
                </div>
              </div>

              <div class="card-body p-4">
                <div class="row g-4">

                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Program Year</label>
                  <select name="program_year" id="program_year" class="form-select form-select-sm select2">
                    <option value="">Select Year</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                  </select>
                  <small class="programRefferedNote text-danger d-block mt-1" style="font-size: 11px !important;">
                    Untuk bisa melanjutkan input program, silakan pilih tahun program
                  </small>
                </div>

                <!-- PROGRAM REFFERED -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Program Reffered</label>
                  <div class="select_year_div">
                    <select name="program_reffered" id="program_reffered" class="form-select form-select-sm select2" required>
                      <option value="" disabled selected>Select a program</option>
                    </select>
                  </div>
                  <div class="loading_year text-center d-none mt-1">
                    <i class="fas fa-spinner fa-spin text-primary"></i>
                  </div>
                </div>

                <!-- INPUTTER -->
                <input type="hidden" name="id_user" value="<?= $id_user ?>">
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
                            <option value="<?= $row['id_user'] ?>"><?= $row['generalname'] ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="loading_school text-center d-none mt-1">
                      <i class="fas fa-spinner fa-spin text-primary"></i>
                    </div>
                  </div>
                <?php else : ?>
                  <div class="col-md-6">
                    <label class="form-label small text-muted d-block">Nama EC</label>
                    <input type="text" class="form-control form-control-sm" value="<?= $generalname ?>" readonly>
                  </div>
                  <input type="hidden" name="inputEC" value="<?= $id_user ?>">
                <?php endif; ?>

                <!-- PROGRAM -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Program</label>
                  <input type="text" id="program_label" class="form-control form-control-sm" required readonly />
                  <input type="hidden" name="program" id="program" class="form-control form-control-sm" />
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

                <!-- SEGMENT -->
                <div class="col-md-6">
                  <label class="form-label small text-muted d-block">Segment</label>
                  <select name="segment" id="segment_input" class="form-select form-select-sm select2" required>
                    <option value="" disabled selected>- Select Segment -</option>
                    <?php 
                      $seg_sql = "SELECT * FROM segments";
                      $segQ = mysqli_query($conn, $seg_sql);
                      while ($row = mysqli_fetch_assoc($segQ)) : ?>
                        <option value='<?= $row['id'] ?>'><?= $row['segment'] ?></option>
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
                        <option value='<?= $row['id'] ?>'><?= $row['name'] ?></option>
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
                <div class="col-md-4">
                  <label class="form-label small text-muted">Wilayah Sekolah</label>
                  <input type="text" id="wilayah" name="wilayah" class="form-control form-control-sm" placeholder="Ex: Jakarta" required>
                </div>

                <!-- DISCOUNT -->
                <div class="col-md-4">
                  <label class="form-label small text-muted">Discount Program (%)</label>
                  <input id="discount_program" type="number" name="discount_program" class="form-control form-control-sm" max="100" placeholder="0 - 100">
                  <small class="text-muted d-block mt-1" style="font-size: 11px !important;">
                    Diskon ini akan digunakan pada semua judul, untuk memberikan diskon khusus pada judul tertentu, silahkan tambahkan diskon ketika menambahkan judul pada book list
                  </small>
                </div>

                <div class="col-md-4">
                  <label class="form-label small text-muted">Additional Price</label>
                  <input placeholder="Ex: 5000" id="additional_price" type="text" name="additional_price" class="form-control form-control-sm only_number">
                  <small class="text-muted d-block mt-1" style="font-size: 11px !important;">
                    Penambahan harga ini, akan digunakan untuk penyesuaian harga dasar setiap judul buku tergantung pada wilayah sekolah
                  </small>
                </div>

                </div>
              </div>

              <div class="card-footer bg-white">
                <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                  <button type="button" class="btn btn-primary m-2 fw-bold" id="proceed" disabled>
                    <span class="btn-icon">
                      <i class="bi bi-arrow-right"></i>
                    </span>
                    Proceed
                  </button>
                </div>
              </div>
            </div>

          </div>

          <div class="col-12 mt-4 d-none" id="contentWrapper">
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

              <div class="p-4 position-relative">
                <div id="draftContent">
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

  async function fetchAllBooksFromPreviousDraft() {
    const id_draft = $('select[name="program_reffered"]').val();

    if (!id_draft) return;

    try {
      const res = await $.ajax({
        url: 'get_partnership_books.php',
        type: 'POST',
        dataType: 'json',
        data: { id_draft },
        beforeSend() {
          Swal.fire({
            title: 'Loading...',
            html: 'Please wait while we get your data.',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading()
            }
          });
        },
        success(response) {
          Swal.close()
          let booksBySeries = response.data ?? {};
          for (const [seriesId, savedBooks] of Object.entries(booksBySeries)) {

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
          }
          Swal.fire({
            title: 'Success',
            html: 'Sekarang kamu bisa menyesuaikan buku yang akan diadopsi',
            icon: 'success',
            allowOutsideClick: true,
            showConfirmButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Oke',
            showClass: {
              popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
              popup: 'animate__animated animate__fadeOutUp'
            }

          });
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log('Error:', textStatus, errorThrown);
          alert("Failed to get books");
        }
      });

      if (res.status !== 'success') {
        console.error(res.message);
        return;
      }

      return res.data;

    } catch (err) {
      console.error('AJAX error:', err);
    }
  }


  let schoolReady = false;

</script>
<script>
  let username = '<?= $username ?? 'null' ?>';

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
    
    $('#proceed').on('click', async function () {
      let valid = true;

      const required = [
        '[name="program_year"]',
        '[name="program"]',
        '[name="inputEC"]',
        '[name="segment"]',
        '[name="program_reffered"]'
      ];

      $('.is-invalid').removeClass('is-invalid');

      required.forEach(sel => {
        const el = $(sel);

        if (!el.length || !el.val()) {
          valid = false;

          if (el.hasClass('select2')) {
            el.next('.select2-container')
              .find('.select2-selection')
              .addClass('is-invalid');
          } else {
            el.addClass('is-invalid');
          }
        }
      });

      if (!valid) {
        const first = $('.is-invalid').first();
        if (first.length) {
          $('html, body').animate({
            scrollTop: first.offset().top - 120
          }, 300);
        }
        return;
      }

      // optional: lock field awal
      $('#wilayah').prop('readonly', true);
      required.forEach(sel => {
        $(sel).on('select2:opening select2:selecting', e => e.preventDefault());
      });

      await fetchAllBooksFromPreviousDraft();

      // ===== VALID =====
      // buka stage berikutnya
      $('#contentWrapper').removeClass('d-none');
      $('#btnAddTitle').prop('disabled', false);
      $('#submt').prop('disabled', false);
      $('.card-footer').toggleClass('d-none', true);
    });

    $('#draftBenefitModal').on('shown.bs.modal', function () {
      $(this).find('.select2').select2({ width:'100%', dropdownParent:$(this) });
    });

    $(document).on('input', '.only_number', function() {
        let sanitizedValue = $(this).val().replace(/^0+|\D/g, '');

        let formattedValue = sanitizedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        $(this).val(formattedValue);
    });

    $(document).on('input', '.only_decimal', function () {
      let val = $(this).val();

      // hapus semua kecuali angka & koma
      val = val.replace(/[^0-9,]/g, '');

      // cuma boleh 1 koma
      const parts = val.split(',');
      if (parts.length > 2) {
        val = parts[0] + ',' + parts.slice(1).join('');
      }

      // max 2 digit desimal
      if (parts[1]) {
        parts[1] = parts[1].slice(0, 2);
        val = parts.join(',');
      }

      // format ribuan (bagian sebelum koma)
      let integerPart = parts[0].replace(/\./g, '');
      integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

      val = parts[1] !== undefined
        ? integerPart + ',' + parts[1]
        : integerPart;

      $(this).val(val);
    });

    $("#program_year").change(function () {
      var year = $(this).val();

      $('#proceed').prop('disabled', true);

      if (year !== "") {
        $('.programRefferedNote').addClass('d-none');

        $.ajax({
          url: "get_dynamic_programs.php",
          type: "POST",
          data: { year: year },
          beforeSend() {
            $('.select_year_div').addClass('d-none');
            $('.loading_year').removeClass('d-none');
          },
          success: function (response) {
            $("#program_reffered").html(response);
          },
          error: function () {
            $('.programRefferedNote').removeClass('d-none');
          },
          complete() {
            $('.loading_year').addClass('d-none');
            $('.select_year_div').removeClass('d-none');
          }
        });

      } else {
        $("#program_reffered").html('<option value="">Select Program</option>');
        $('.programRefferedNote').removeClass('d-none');
      }
    });

    $("#program_reffered").change(function () {
      var id_draft = $(this).val();
      if (id_draft !== "") {
        $.ajax({
          url: "get_dynamic_program_detail.php",
          type: "POST",
          data: { id_draft: id_draft },
          success: function (response) {
            console.log('response: ', response);
            if(response["data"]) {
              let programData = response["data"];
              $("#select_school_label").val(programData.school_name);
              $("#segment_input").val(programData.segment).trigger('change');
              $("#level").val(programData.level);
              $("#wilayah").val(programData.wilayah);
              $("#program").val(programData.code);
              $("#program_label").val(programData.program_name);
              $('.programNote').toggleClass('d-none', true);
              $("#inputEC").val(programData.id_ec).trigger('change');
              $("#select_school").val(programData.school_id ? parseInt(programData.school_id) : '').trigger('change').on('select2:opening select2:selecting', e => e.preventDefault());
              if (programData.level_ids.length) {
                $('#adoption_levels').val(programData.level_ids).trigger('change');
              }
              if (programData.subject_ids.length) {
                $('#adoption_subjects').val(programData.subject_ids).trigger('change');
              }
              $('#btnAddTitle').prop('disabled', false);
              $('#submt').prop('disabled', false);
              $('#proceed').prop('disabled', false);
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.log("status: ", errorThrown)
          }
        });
      } else {
        alert("Please select a program first.");
      }
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

    $('#discount_program').on('input', function () {
      let discountProgram = parseFloat($(this).val()) || 0;

      if (programOmzetSettings && programOmzetSettings.enabled) {
        // update SEMUA input diskon[]
        $('input[name="diskon[]"]').each(function () {
          $(this).val(discountProgram);
        });
        $('.book-row').each(function () {
          updateDisabledField(
            $(this).find('[name="jumlahsiswa[]"]')[0]
          );
        });
      } else {
        if (discountProgram > 100) {
          $(this).val(100);
        }
      }
    });

    $('#cashbackinput').on('input', function () {
      $('.book-row').each(function () {
        updateDisabledField(
          $(this).find('[name="jumlahsiswa[]"]')[0]
        );
      });
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
    fetchShool(username);

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
      reevaluateOmzetDiscount();
      return;
    }

    /* ADD BOOK */
    if (e.target.closest('.add-book')) {
      const seriesCard = e.target.closest('.series-card');
      const seriesId   = seriesCard.dataset.seriesId;
      const bookList   = seriesCard.querySelector('.book-list');
      const prevRow    = bookList.querySelector('.book-row:last-child');

      if (!seriesId) {
        Swal.fire({
          title: "Failed!",
          text: 'Series ID not found',
          icon: "error"
        })
        return;
      }

      $.getJSON('get_books.php', { series_id: seriesId }, function (books) {

        if (!books || books.length === 0) {
          Swal.fire({
            title: "Failed!",
            text: 'Tidak ada buku untuk series ini',
            icon: "error"
          })
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

        const els = row.querySelectorAll('.remove_if_has_omzet_scheme');
        const omzetWrapper = row.querySelectorAll('.omzet_wrapper');

        els.forEach(el => {
          el.classList.toggle('d-none', programOmzetSettings.enabled);
        });

        omzetWrapper.forEach(omzet => {
          omzet.classList.toggle('d-none', !programOmzetSettings.enabled);
        });

        row.querySelector('[name="jumlahsiswa[]"]').value = prevRow.querySelector('[name="jumlahsiswa[]"]').value || '';
        row.querySelector('[name="diskon[]"]').value = prevRow.querySelector('[name="diskon[]"]').value || '';
        row.querySelector('[name="diskon[]"]').readOnly = programOmzetSettings.enabled;

        // onchange buku → auto isi field lain
        $(bookSelect).on('change', function () {
          const opt = $(this).find(':selected');
          if (!opt.length) return;

          hiddenBook.value = opt.data('id');

          levelSelect.innerHTML = `<option value="${opt.data('level')}">${opt.data('level')}</option>`;
          typeSelect.innerHTML  = `<option value="${opt.data('type')}">${opt.data('type')}</option>`;

          const basePrice = Number(opt.data('price')) || 0;
          const additionalPriceVal = removeNonDigits($('#additional_price').val()) || 0;

          const finalPrice = basePrice + additionalPriceVal;

          const hargaNormalInput = row.querySelector('[name="harganormal[]"]');
          hargaNormalInput.dataset.bookPrice = basePrice;
          hargaNormalInput.value = formatNumber(finalPrice);

          if (prevRow && !programOmzetSettings.enabled) {
            row.querySelector('[name="usulanharga[]"]').value = prevRow.querySelector('[name="usulanharga[]"]').value || '';
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