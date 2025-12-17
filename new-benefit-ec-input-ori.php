
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

  $query = "SELECT max_price_percentage, max_discount_percentage, max_benefit_percentage FROM benefit_setting LIMIT 1";
  $result = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
      $benefitSetting = mysqli_fetch_assoc($result);
  }

  $role = $_SESSION['role'];
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

</style>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
        <div class="row">
          <form method="POST" action="new-benefit-ec-input-action1.php" enctype="multipart/form-data" id="input_form_benefit">
            <h6 class="mb-3 pb-1 border-bottom border-2 border-black" style="font-size: 18px; font-weight: bold;">Create Draft Benefit</h6>
            <div class="col-12">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-semibold d-flex align-items-center gap-2">
                  <i class="bi bi-info-circle"></i>
                  Detail Program
                </div>

                <div class="card-body p-4">
                  <div class="row g-4">

                    <!-- INPUTTER -->
                    <!-- <div class="col-md-6">
                      <label class="form-label small text-muted">Inputter</label>
                      <div class="fw-semibold">
                        <?= $_SESSION['username'] ?>
                        
                      </div>
                    </div> -->

                    <!-- EC -->
                    <?php if($role == 'admin') : ?>
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">Nama EC</label>
                      <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>">
                      <select name="inputEC" id="inputEC" class="form-select form-select-sm select2" required>
                        <option value="" disabled selected>- Select EC -</option>
                        <?php 
                          $sql = "SELECT * FROM user WHERE role = 'ec' AND is_active = 1 ORDER BY generalname ASC";
                          $resultsd1 = mysqli_query($conn, $sql);
                          while ($row = mysqli_fetch_assoc($resultsd1)) {
                            echo "<option value='{$row['id_user']}'>{$row['generalname']}</option>";
                          }
                        ?>
                      </select>
                    </div>
                    <?php else : ?>
                      <input type="hidden" name="inputEC" value="<?= $_SESSION['id_user'] ?>">
                    <?php endif; ?>

                    <!-- SEKOLAH -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">Nama Sekolah</label>
                      <div id="select_school_div">
                        <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required></select>
                      </div>
                      <div id="loading_school" class="text-center d-none mt-1">
                        <i class="fas fa-spinner fa-spin text-primary"></i>
                      </div>
                    </div>

                    <!-- PROGRAM -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">Program</label>
                      <select name="program" id="program" class="form-select form-select-sm select2" required>
                        <option value="" disabled selected>Select a program</option>
                      </select>
                      <small class="programNote text-danger d-block mt-1">
                        Pilih program terlebih dahulu sebelum menambahkan buku
                      </small>
                    </div>

                    <!-- MY PLAN -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">My Plan Reference</label>
                      <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2">
                        <option value="" disabled selected>Select a plan</option>
                      </select>
                    </div>

                    <!-- SEGMENT -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted d-block">Segment Sekolah</label>
                      <select name="segment" class="form-select form-select-sm select2" required>
                        <option value="" disabled selected>Select a segment</option>
                        <option value="national">National</option>
                        <option value="national plus">National Plus</option>
                        <option value="internasional/spk">International / SPK</option>
                      </select>
                    </div>

                    <!-- ADOPTION LEVEL -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Cakupan Jenjang Program</label>
                      <select name="program_adoption_level"
                        class="form-select form-select-sm select2" multiple required>
                        <?php
                          $lvl_sql = "SELECT * FROM levels";
                          $levelsQ = mysqli_query($conn, $lvl_sql);
                          while ($row = mysqli_fetch_assoc($levelsQ)) {
                            echo "<option value='{$row['id']}'>{$row['name']}</option>";
                          }
                        ?>
                      </select>
                    </div>

                    <!-- ADOPTION SUBJECT -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Cakupan Subjek Program</label>
                      <select name="program_adoption_subject"
                        class="form-select form-select-sm select2" multiple required>
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
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Wilayah Sekolah</label>
                      <input type="text" name="wilayah" class="form-control form-control-sm" placeholder="Contoh: Jakarta" required>
                    </div>

                    <!-- DISCOUNT -->
                    <div class="col-md-6">
                      <label class="form-label small text-muted">Discount Program (%)</label>
                      <input id="discount_program" type="number" name="discount_program" class="form-control form-control-sm" max="100" placeholder="0 - 100">
                      <small class="text-muted d-block mt-1" style="font-size: 12px;">
                        Diskon ini akan digunakan pada semua judul, untuk memberikan diskon khusus pada judul tertentu, silahkan tambahkan diskon ketika menambahkan judul pada book list
                      </small>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label small text-muted">Additional Price</label>
                      <input id="additional_price" type="text" name="additional_price" class="form-control form-control-sm only_number">
                      <small class="text-muted d-block mt-1" style="font-size: 12px;">
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

                <div class="p-4">
                  
                  <!-- <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#draftBenefitModal">
                      <i class="bi bi-plus"></i> Add Title
                    </button>
                  </div> -->
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
                  <div id="titleList" class="d-flex flex-column gap-3"></div>

                  <!-- FOOTER -->
                  <div class="d-flex justify-content-between align-items-center mt-4">
                    <h5 class="mb-0">
                      Total Alokasi Benefit:
                      <span id="accumulated_values" class="fw-bold text-primary">0</span>
                    </h5>

                    <button type="submit" class="btn btn-primary fw-bold px-4" id="submt" disabled>
                      Submit
                    </button>
                  </div>

                </div>

              </div>
            </div>
          </form>
        </div>
      </div>
      <!-- Form End -->

      <template id="seriesCardTemplate">
        <div class="card shadow-sm border series-card" data-series-id="">
          <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
              <strong class="text-primary series-title"></strong>
              <div class="">
                <button type="button" class="btn btn-sm btn-outline-danger remove-series">
                  <i class="bi bi-trash"></i> Remove Series
                </button>

                <button type="button" class="btn btn-sm btn-outline-success add-book">
                  <i class="bi bi-plus"></i> Add Book
                </button>
              </div>
            </div>

            <div class="book-list d-flex flex-column gap-3"></div>

          </div>
        </div>
      </template>

      <template id="bookRowTemplate">
        <div class="book-row border rounded p-3 position-relative">

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

<script>
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

    $.ajax({
        url: 'https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=<?= $_SESSION['username'] ?>', 
        type: 'GET', 
        dataType: 'json',
        beforeSend: function() {
          $('#select_school_div').addClass('d-none');
          $('#loading_school').removeClass('d-none');
        }, 
        success: function(response) {
          let options = '<option value="" disabled selected>Select a school</option>';
          response.map((data) => {
              options += `<option value="${data.id}">${data.name}</option>`
          }) 

          $('#select_school').html(options);
          $('#select_school').select2({ width: '100%' });
          $('#loading_school').addClass('d-none');
          $('#select_school_div').removeClass('d-none');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#select_school').html('Error: ' + textStatus);
            $('#loading_school').addClass('d-none');
            $('#select_school_div').removeClass('d-none');
        },
        complete: function () {
          $('#loading_school').addClass('d-none');
          $('#select_school_div').removeClass('d-none');
        }
    });

    $('#inputEC').on('change', function() {
        getMyPlanRef();
    });

    $('#select_school').on('change', function() {
      var schoolId = $(this).val();

      if (schoolId) {
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
                options += `<option value="${data.code}">${data.name}</option>`
            }) 

            $('#program').html(options);
            // $('#program').select2();
          },
          error: function(jqXHR, textStatus, errorThrown) {
              console.log('Error:', textStatus, errorThrown);
              alert("Failed to get program")
          }
        });
        
        getMyPlanRef();

      } else {
          alert('No school and ec selected');
      }
    });

    $('#program').on('change', function() {
      console.log('program changed');

      const hasProgram = !!$(this).val();

      $('#btnAddTitle').prop('disabled', !hasProgram);
      $('#submt').prop('disabled', !hasProgram);

      $('.programNote').toggleClass('d-none', hasProgram);

    });

    $('#myplan_id').on('change', function () {
      const selectedId = $(this).val();

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

      // reset semua error
      $(form)
        .find('.is-invalid')
        .removeClass('is-invalid');

      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();

        // trigger native browser tooltip
        form.reportValidity();

        // handle semua required field
        $(form).find('[required]').each(function () {
          const el = this;

          // kalau invalid
          if (!el.checkValidity()) {
            // SELECT2
            if ($(el).hasClass('select2')) {
              $(el)
                .next('.select2-container')
                .find('.select2-selection')
                .addClass('is-invalid');
            } 
            // INPUT / SELECT BIASA
            else {
              $(el).addClass('is-invalid');
            }
          }
        });

        return;
      }

      // valid → submit
      $(this).prop('disabled', true);
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

  
  });
</script>
<script type="text/javascript">

  function addTitleCard() {
    const seriesId          = $('#modalBookSeries').val();
    const seriesText        = $('#modalBookSeries option:selected').text();
    const bookType          = $('#modalBookType').val();
    const jumlah            = $('#modalJumlah').val();
    const harga             = $('#modalHarga').val();
    const diskon            = $('#modalDiskon').val();
    const additionalPrice   = $('#additional_price').val();
    
    if (!seriesId) return alert('Select book series');

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
          bookClone.querySelector('[name="diskon[]"]').value = diskon || $('#discount_program').val();

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

  function getMyPlanRef() {
    const ec = $('input[name="inputEC"]').val() ?? $('select[name="inputEC"]').val();
    const schoolId = $('select[name="nama_sekolah"]').val();
    if(ec && schoolId) {
      $.ajax({
        url: 'get-ec-plan.php',
        type: 'POST',
        dataType: 'json',
        data: {
            school_id: schoolId,
            ec: ec,
        },
        success: function(response) {
          let options = '<option value="" disabled selected>Select a plan</option>';
          response.map((data, index) => {
              let selected = index === 0 ? 'selected' : '';
              if(selected == 'selected') {
                getPlanData(data.value);
              }
              options += `<option value="${data.value}" ${selected}>${data.label}</option>`
          }) 

          $('#myplan_id').html(options);
          $('#myplan_id').select2();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error:', textStatus, errorThrown);
            alert("Failed to get myplan")
        }
      });
    }
  }

  function getPlanData(planId) {
    $.ajax({
      url: 'get-myplan-data.php',
      method: 'POST',
      data: { myplan_id: planId },
      dataType: 'json',
      success: function (res) {
        if (res && res.program) {
          const programName = res.program.trim();
          $('#program').val(programName).trigger('change');
          // $('#level').val(res.level);
        } else {
          $('#program').val('').trigger('change');
          // $('#level').val('');
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


</script>
<?php include 'footer.php'; ?>