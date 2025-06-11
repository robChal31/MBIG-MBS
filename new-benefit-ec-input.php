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
</style>

<?php include 'header.php'; ?>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
          <div class="row">
              <div class="col-12">
                  <div class="bg-whites rounded h-100 p-4">
                    <h6 class="mb-4">Create Draft Benefit</h6>
                    <form method="POST" action="new-benefit-ec-input-action1.php" enctype="multipart/form-data" id="input_form_benefit">
                        
                      <table class="table table-striped">
                        <tr>
                          <td style="width: 15%">Inputter</td>
                          <td style="width:5px">:</td>
                          <td><?= $_SESSION['username']?><input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>"></td>
                        </tr>
                        <?php if($_SESSION['username'] == 'secretary@mentaribooks.com') : ?>
                          <tr>
                            <td>Nama EC</td>
                            <td>:</td>
                            <td>
                              <select name="inputEC" id="inputEC" class="form-select form-select-sm select2">
                                  <?php 
                                    $sql = "SELECT * from user where role='ec' order by generalname ASC"; $resultsd1 = mysqli_query($conn, $sql);
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
                            <select name="nama_sekolah" id="select_school" class="form-select form-select-sm select2" required>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>My Plan Ref</td>
                          <td>:</td>
                          <td>
                            <select name="myplan_id" id="myplan_id" class="form-select form-select-sm select2">
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>Segment Sekolah</td>
                          <td>:</td>
                          <td>
                            <select name="segment" class="form-select form-select-sm" required>
                              <option value="national">National</option>
                              <option value="national plus" >National Plus</option>
                              <option value="internasional/spk">International/SPK</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>Jenjang Sekolah</td>
                          <td>:</td>
                          <td>
                            <select name="level" class="form-select form-select-sm" required>
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
                        </tr>
                        <tr>
                          <td>Wilayah Sekolah</td>
                          <td>:</td>
                          <td><input type="text" name="wilayah" placeholder="Wilayah" class="form-control form-control-sm" required></td>
                        </tr>
                        <tr>
                          <td>Program</td>
                          <td>:</td>
                          <td>
                            <select name="program" id="program" class="form-select form-select-sm select2" required>
                              <!-- <?php
                                  $programs = [];
                                  $query_program = "SELECT * FROM programs WHERE is_active = 1 AND is_pk = 0 AND code NOT IN ('cbls1', 'cbls3')";

                                  $exec_program = mysqli_query($conn, $query_program);
                                  if (mysqli_num_rows($exec_program) > 0) {
                                      $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);    
                                  }

                                  foreach($programs as $prog) : ?>
                                    <option value="<?= $prog['name'] ?>"><?= $prog['name'] ?></option>
                              <?php endforeach; ?> -->
                            </select>
                          </td>
                        </tr>
                      </table>

                      <div class="table-responsive mt-4">
                        <table class="table table-bordered dataTable no-footer" id="input_form">
                            <thead>
                                <th>Judul Buku</th>
                                <th style="width: 10%">Level</th>
                                <th style="width: 8%">Jenis Buku</th>
                                <th>Jumlah Siswa</th>
                                <th>Usulan Harga Program</th>
                                <th>Harga Buku Normal</th>
                                <th>Standard Discount</th>
                                <th>Harga Setelah Diskon</th>
                                <th>Revenue Harga Program</th>
                                <th>Revenue Harga Normal</th>
                                <th>Alokasi pengembangan sekolah</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                                <tr id="row1">
                                    <td><select name="titles[]" class="book form-select form-select-sm"></select></td>
                                    <td><select name="levels[]" class="level form-select form-select-sm"></select></td>
                                    <td><select name="booktype[]" class="booktype form-select form-select-sm"></select></td>
                                    <td><input type="text" name="jumlahsiswa[]" class="form-control only_number form-control-sm" placeholder="Jumlah Siswa" onchange="updateDisabledField(this)"></td>
                                    <td><input type="text" name="usulanharga[]" class="form-control only_number form-control-sm" placeholder="Usulan Harga" onchange="updateDisabledField(this)"></td>
                                    <td><input type="text" name="harganormal[]" class="form-control only_number form-control-sm" placeholder="Harga Buku Normal" onchange="updateDisabledField(this)"></td>
                                    <td><input type="text" name="diskon[]" max="30" class="form-control only_number form-control-sm" placeholder="Standard Discount" onchange="updateDisabledField(this)"></td>
                                    <td><input type="text" class="aftd form-control form-control-sm" name="aftd[]" placeholder="0" readonly></td>
                                    <td><input type="text" class="afto form-control form-control-sm" name="afto[]" placeholder="0" readonly></td>
                                    <td><input type="text" class="befo form-control form-control-sm" name="befo[]" placeholder="0" readonly></td>
                                    <td><input type="text" class="alok form-control form-control-sm" name="alokasi[]" placeholder="0" readonly></td>
                                    
                                    <td>
                                      <button type='button' class="add-button btn btn-success" id='add_row'>
                                          <i class="fas fa-plus"></i>
                                      </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table> 
                      </div>  
                      
                      <!-- <button type="button" class="btn btn-success mt-4" id="add_row">Add Row</button> -->

                      <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                        <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
                      </div>
                    </form>
                    <h4>Total Alokasi Benefit: <span id="accumulated_values"></span></h4>
                  </div>
              </div>
          </div>
      </div>
      <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
  $(document).ready(function(){
    $('.select2').select2();
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

    $("select[name='level']").on('change', function() {
        let value = $(this).val();
        if(value == 'other') {
            $('#other_level').show();
            $('input[name="level2"]').prop('required', true);
          } else {
            $('#other_level').hide();
            $('input[name="level2"]').prop('required', false);
        }
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
        success: function(response) {
          let options = '<option value="" disabled selected>Select a school</option>';
            response.map((data) => {
                options += `<option value="${data.id}">${data.name}</option>`
            }) 

            $('#select_school').html(options);
            $('#select_school').select2();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#select_school').html('Error: ' + textStatus);
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
              ec: ec
          },
          success: function(response) {
            let options = '<option value="" disabled selected>Select a plan</option>';
            response.map((data) => {
                options += `<option value="${data.value}">${data.label}</option>`
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
    var row = $(element).closest('tr');
    var disabledField = row.find('input[name="alokasi[]"]');
    var aftd = row.find('input[name="aftd[]"]');
    var befo = row.find('input[name="befo[]"]');
    var afto = row.find('input[name="afto[]"]');
    
    var jumlah = !isNaN(removeNonDigits(row.find('input[name="jumlahsiswa[]"').val())) ? removeNonDigits(row.find('input[name="jumlahsiswa[]"').val()) : 0;
    var usulan = !isNaN(removeNonDigits(row.find('input[name="usulanharga[]"').val())) ? removeNonDigits(row.find('input[name="usulanharga[]"').val()) : 0;
    var normal = !isNaN(removeNonDigits(row.find('input[name="harganormal[]"').val())) ? removeNonDigits(row.find('input[name="harganormal[]"').val()) : 0;
    var diskon = !isNaN(removeNonDigits(row.find('input[name="diskon[]"').val())) ? removeNonDigits(row.find('input[name="diskon[]"').val()) : 0;
    if(diskon > 30){
      diskon = 30;
      alert("Diskon melebihi ketentuan, silakan ajukan persetujuan ke HOR/Top Leader terlebih dahulu. Terima kasih");
      row.find('input[name="diskon[]"').val(30);
    }  
    
    var setelahDiskon = normal -  (diskon/100 * normal);
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