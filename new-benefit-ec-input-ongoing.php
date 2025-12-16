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


</style>
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
<?php include 'header.php'; ?>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
        <div class="row">
          <form method="POST" action="new-benefit-ec-input-action1.php" enctype="multipart/form-data" id="input_form_benefit">
            <h6 class="mb-3 pb-1 border-bottom border-2 border-black" style="font-size: 18px; font-weight: bold;">Create Draft Benefit</h6>
            <div class="col-12">
              <div class="card">
                <div class="card-header bg-primary text-white" style="font-size: 16px;">Detail Program</div>
                <div class="p-4">
                  <table class="table table-striped">
                    <tr>
                      <td style="width: 18%">Inputter</td>
                      <td style="width:5px">:</td>
                      <td><?= $_SESSION['username']?><input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>"></td>
                    </tr>
                    <?php if($role == 'admin') : ?>
                      <tr>
                        <td>Nama EC</td>
                        <td>:</td>
                        <td>
                          <select name="inputEC" id="inputEC" class="form-select form-select-sm select2">
                            <option value="" disabled selected>- Select EC -</option>
                            <?php 
                              $sql = "SELECT * FROM user WHERE role = 'ec' AND is_active = 1 order by generalname ASC"; $resultsd1 = mysqli_query($conn, $sql);
                              while ($row = mysqli_fetch_assoc($resultsd1)){
                                echo "<option value='".$row['id_user']."'>".$row['generalname']."</option>";
                              } 
                            ?>
                          </select>
                        </td>
                      </tr>
                    <?php else : ?> 
                      <input type='hidden' name='inputEC' value="<?= $_SESSION['id_user'] ?> "> 
                    <?php endif; ?>
                    <tr>
                      <td>Nama Sekolah</td>
                      <td>:</td>
                      <td>
                        <div class="d-block w-100" id="select_school_div">
                          <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required>
                          </select>
                        </div>
                        <div class="d-none text-center" id="loading_school"><i class="fas fa-spinner fa-spin text-primary"></i></div>
                      </td>
                    </tr>
                    <tr>
                      <td>Program</td>
                      <td>:</td>
                      <td>
                        <select name="program" id="program" class="form-select form-select-sm select2" required>
                          <option value="" disabled selected>Select a program</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>My Plan Ref</td>
                      <td>:</td>
                      <td>
                        <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2">
                          <option value="" disabled selected>Select a plan</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Segment Sekolah</td>
                      <td>:</td>
                      <td>
                        <select name="segment" class="form-select form-select-sm select2" required>
                          <option value="" disabled selected>Select a segment</option>
                          <option value="national">National</option>
                          <option value="national plus" >National Plus</option>
                          <option value="internasional/spk">International/SPK</option>
                        </select>
                      </td>
                    </tr>
                    <!-- <tr>
                      <td>Jenjang Sekolah</td>
                      <td>:</td>
                      <td>
                        <select id="level" name="level" class="form-select form-select-sm select2" required>
                          <option value="" disabled selected>Select a level</option>
                          <option value="tk">TK</option>
                          <option value="sd">SD</option>
                          <option value="smp">SMP</option>
                          <option value="sma">SMA</option>
                          <option value="yayasan">Yayasan</option>
                          <option value="other" id='level_manual_input'>Lainnya (isi sendiri)</option>
                        </select>
                        <div class="my-1" id='other_level' style="display: none;">
                          <input type="text" name="level2" value="" placeholder="Jenjang..." class="form-control form-control-sm">
                        </div>
                      </td>
                    </tr> -->
                    <tr>
                      <td>Cakupan Jenjang Program</td>
                      <td>:</td>
                      <td>
                        <select name="program_adoption_level" id="program_adoption_level" class="form-select form-select-sm select2" multiple required>
                          <?php 
                            $lvl_sql = "SELECT * from levels"; $levelsQ = mysqli_query($conn, $lvl_sql);
                            while ($row = mysqli_fetch_assoc($levelsQ)){
                              echo "<option value='".$row['id']."'>".$row['name']."</option>";
                            } 
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Cakupan Subjek Program</td>
                      <td>:</td>
                      <td>
                        <select name="program_adoption_subject" id="program_adoption_subject" class="form-select form-select-sm select2" multiple required>
                          <?php 
                            $sub_sql = "SELECT * from subjects"; $subsq = mysqli_query($conn, $sub_sql);
                            while ($row = mysqli_fetch_assoc($subsq)){
                              echo "<option value='".$row['id']."'>".$row['name']."</option>";
                            } 
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Wilayah Sekolah</td>
                      <td>:</td>
                      <td><input type="text" name="wilayah" placeholder="Wilayah" class="form-control form-control-sm" required></td>
                    </tr>
                    <tr>
                      <td>Discount</td>
                      <td>:</td>
                      <td><input type="number" max="100" name="discount_program" placeholder="Discount" class="form-control form-control-sm" required></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>

            <div class="col-12 mt-4">
              <div class="card">
                <div class="card-header bg-primary text-white" style="font-size: 16px;">Book List</div>


                <div class="p-4">

                  <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#draftBenefitModal">
                      <i class="bi bi-plus"></i> Add Title
                    </button>
                  </div>

                  <div id="titleList" class="d-flex flex-column gap-3">
                    
                  </div>

                  <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                    <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
                  </div>

                  <h4>Total Alokasi Benefit: <span id="accumulated_values"></span></h4>
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
  // addTitleCard();
  function addTitleCard() {
    const seriesId   = $('#modalBookSeries').val();
    const seriesText = $('#modalBookSeries option:selected').text();
    const bookType   = $('#modalBookType').val();
    const jumlah     = $('#modalJumlah').val();
    const harga      = $('#modalHarga').val();
    const diskon     = $('#modalDiskon').val();

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

          bookClone.querySelector('.book').innerHTML = `<option value="${book.id}">${book.name}</option>`;

          bookClone.querySelector('.level').innerHTML = `<option value="${book.grade}">${book.grade}</option>`;

          bookClone.querySelector('.booktype').innerHTML = `<option value="${bookType || book.type}">${book.type || bookType}</option>`;

          bookClone.querySelector('[name="jumlahsiswa[]"]').value = jumlah;
          bookClone.querySelector('[name="usulanharga[]"]').value = harga;
          bookClone.querySelector('[name="diskon[]"]').value = diskon;
          bookClone.querySelector('[name="harganormal[]"]').value = formatNumber(book.price);

          bookList.appendChild(bookClone);

          updateDisabledField(
            bookList.lastElementChild.querySelector('[name="jumlahsiswa[]"]')
          );
        });

        $('#draftBenefitModal').modal('hide');
        document.getElementById('titleList').appendChild(seriesClone);
      }

    });
  }

  document.addEventListener('click', function (e) {

    /* REMOVE BOOK */
    if (e.target.closest('.remove-book')) {
      const bookRow = e.target.closest('.book-row');
      const series  = e.target.closest('.series-card');

      bookRow.remove();

      if (series && series.querySelectorAll('.book-row').length === 0) {
        series.remove();
      }
      return;
    }

    /* ADD BOOK */
    if (e.target.closest('.add-book')) {
      const seriesCard = e.target.closest('.series-card');
      const bookList   = seriesCard.querySelector('.book-list');

      const tpl   = document.getElementById('bookRowTemplate');
      const clone = tpl.content.cloneNode(true);

      bookList.appendChild(clone);
      return;
    }

    /* REMOVE SERIES */
    if (e.target.closest('.remove-series')) {
      const seriesCard = e.target.closest('.series-card');

      if (confirm('Remove this series and all books?')) {
        seriesCard.remove();
      }
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

    var maxRows = 75; // Maximum rows allowed
    var x = 1; // Initial row counter

    // Add row
    $('#add_row').click(function(){
      if(x < maxRows){
        x++;
        var newRow = '<tr id="row'+x+'"><td><select name="titles[]" class="form-select form-select-sm book"></select></td><td><select name="levels[]" class="form-select form-select-sm level"></select></td><td><select name="booktype[]" class="form-select form-select-sm booktype"></select></td><td><input type="text" name="jumlahsiswa[]" class="form-control only_number form-control-sm" placeholder="Jumlah Siswa" onchange="updateDisabledField(this)"></td><td><input type="text" name="usulanharga[]" class="form-control only_number form-control-sm" placeholder="Usulan Harga" onchange="updateDisabledField(this)"></td><td><input type="text" name="harganormal[]" class="form-control only_number form-control-sm" placeholder="Harga Buku Normal" onchange="updateDisabledField(this)"></td><td><input type="text" name="diskon[]" max="30" class="form-control only_number form-control-sm" placeholder="Standard Discount" onchange="updateDisabledField(this)"></td><td><input type="text" class="aftd form-control form-control-sm" name="aftd[]" placeholder="0" readonly></td><td><input type="text" class="afto form-control form-control-sm" name="afto[]" placeholder="0" readonly></td><td><input type="text" class="befo form-control form-control-sm" name="befo[]" placeholder="0" readonly></td><td><input type="text" class="alok form-control form-control-sm" name="alokasi[]" placeholder="0" readonly></td><td><button type="button" class="btn_remove btn btn-danger" data-row="row'+x+'"><i class="fas fa-trash"></i></button></td></tr>';
        $('#input_form').append(newRow);
          populateDropdown('row'+x);
      }
    });

    // Remove row
    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
    });

    // $("select[name='level']").on('change', function() {
    //     let value = $(this).val();
    //     if(value == 'other') {
    //         $('#other_level').show();
    //         $('input[name="level2"]').prop('required', true);
    //       } else {
    //         $('#other_level').hide();
    //         $('input[name="level2"]').prop('required', false);
    //     }
    // });

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
            $('#program').select2();
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

    $('#myplan_id').on('change', function () {
      const selectedId = $(this).val();

      if (!selectedId) return;

      getPlanData(selectedId);
      
    });

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

    // Populate dropdown options
    function populateDropdown(rowId) {
      $.ajax({
        url: 'get_titles.php', // Replace with the URL to retrieve options from the database
        type: 'GET',
        success: function(data) {
          var dropdown = $('#' + rowId + ' .book');
          var dropdown2 = $('#' + rowId + ' .level');
          var dropdown3 = $('#' + rowId + ' .booktype');
          dropdown.html(data);
          dropdown2.html('<option value="Level Starter">Level Starter</option><option value="Level 1">Level 1</option><option value="Level 2">Level 2</option><option value="Level 3">Level 3</option><option value="Level 4">Level 4</option><option value="Level 5">Level 5</option><option value="Level 6">Level 6</option>');
          dropdown3.html('<option value="Textbook">Textbook</option><option value="Workbook">Workbook</option>');
        }
      });
    }

    $('#submt').click(function(){
      $('#submt').prop('disabled',true);
      $('#input_form_benefit').submit();
    });

    populateDropdown('row1');
    $('#submt').prop('disabled',true);
    
  });
</script>
<script type="text/javascript">

  function removeNonDigits(numberString) {
    let nonDigitRegex = /[^\d-]/g;

    let result = numberString.replace(nonDigitRegex, '');

    return result;
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
    var accumulatedValues = [];

    $('.alok').each(function() {
      var value = parseFloat(removeNonDigits($(this).val()));
      if (!isNaN(value)) {
          accumulatedValues.push(value);
      }
    });
    var accumulatedValues = sumArray(accumulatedValues);
    if(accumulatedValues < 0){
        $('#submt').prop('disabled', true);
    }else{
        $('#submt').prop('disabled', false);
    }

    $('#accumulated_values').text(formatNumber(accumulatedValues));
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