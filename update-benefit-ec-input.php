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

<?php
include 'header.php';

$posts      = $_POST ?? NULL;

if($posts && $posts['program_reffered']) {
 try {
  $id_draft       = $_POST['program_reffered'] ?? NULL;
  $inputEC        = $_POST['inputEC'] ?? NULL;
  $id_ec          = $_POST['id_user'] ?? NULL;
  $program_year   = $_POST['program_year'] ?? NULL;

  $emp_sql    = "SELECT * from user where id_user = $inputEC"; 
  $resultsd1  = mysqli_query($conn, $emp_sql);

  while ($row_emp = mysqli_fetch_assoc($resultsd1)){
    $ec_name = $row_emp['generalname'];
  } 

  $sql      = "SELECT db.id_draft, db.segment, db.level, db.wilayah, db.id_ec, u.generalname, 
                  db.alokasi, IFNULL(sch.name, db.school_name) AS school_name, prog.name AS program_name, 
                  sch.id AS school_id, db.program
                FROM draft_benefit as db
                LEFT JOIN user as u on u.id_user = db.id_ec
                LEFT JOIN schools sch ON sch.id = db.school_name
                LEFT JOIN programs prog ON prog.code = db.program
                where id_draft = $id_draft";
  $levels   = ['tk', 'sd', 'smp', 'sma', 'yayasan', 'other'];

  $result   = mysqli_query($conn,$sql);
  if(mysqli_num_rows($result) < 1){
    header('Location: draft-benefit.php');
    exit;
  } else if(mysqli_num_rows($result) == 1){

    while ($data = $result->fetch_assoc()){
      $program_name      = $data['program_name'];
      $program           = $data['program'];
      $segment           = $data['segment'];
      $level             = $data['level'];
      $wilayah           = $data['wilayah'];
      $id_user           = $data['id_ec'];
      $username          = $data['generalname'];
      $sumalok           = $data['alokasi'];
      $school_name       = $data['school_name'];
      $school_id         = $data['school_id'];

      $program_reffered  = "[ " . $program_name . " - " . $school_name  . " ]";
    }

  }

  $book_list_query  = "SELECT * 
                        from draft_benefit as db
                        left join calc_table ct on ct.id_draft = db.id_draft 
                        WHERE db.id_draft = $id_draft";
  $exec_query       = mysqli_query($conn, $book_list_query);
  if(mysqli_num_rows($exec_query) < 1){
    header('Location: draft-benefit.php');
    exit;
  }

  $query_list_book = "SELECT * FROM books WHERE is_active = 1 order by name asc";
  $exec_list_book = $conn->query($query_list_book);

  $options = [];
  if ($exec_list_book->num_rows > 0) {

    while ($row = $exec_list_book->fetch_assoc()) {
      array_push($options, $row['name']);
    }
  }
 } catch (\Throwable $th) {
  var_dump($th);
 }
}
?>

  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">

      <div class="row">
        <div class="bg-whites rounded h-100 p-4">
          <div class="col-12">
            <h4>Update Draft Benefit</h4>
            <?php
              if(ISSET($id_draft)) { ?>
                <form class="mt-4" method="POST" action="new-benefit-ec-input-action3.php" enctype="multipart/form-data" id="input_form_benefit">
                    
                  <table class="table table-striped">
                    <tr>
                      <td style="width: 15%">Proram Year</td>
                      <td style="width:5px">:</td>
                      <td>
                        <input type="text" value="Year <?= $program_year ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="program_year" value="<?= $program_year ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 15%">Program Reffered</td>
                      <td style="width:5px">:</td>
                      <td>
                        <input type="text" value="<?= $program_reffered ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="program_reffered" value="<?= $id_draft ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td style="width: 15%">Inputter</td>
                      <td style="width:5px">:</td>
                      <td>
                        <input type="text" value="<?= $ec_name ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="id_user" value="<?= $id_ec ?>">
                        <input type="hidden" name="inputEC" value="<?= $inputEC ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>Nama Sekolah</td>
                      <td>:</td>
                      <td>
                        <input type="text" value="<?= $school_name ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="nama_sekolah" value="<?= $school_id ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Segment Sekolah</td>
                      <td>:</td>
                      <td>
                        <input type="text" value="<?= $segment ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="segment" value="<?= $segment ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Jenjang Sekolah</td>
                      <td>:</td>
                      <td>
                        <input type="text" value="<?= $level ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="level" value="<?= $level ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Wilayah Sekolah</td>
                      <td>:</td>
                      <td>
                        <input type="text" value="<?= $wilayah ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="wilayah" value="<?= $wilayah ?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Program</td>
                      <td>:</td>
                      <td>
                        <input type="text" value="<?= $program_name ?>" class="form-control form-control-sm" required readonly />
                        <input type="hidden" name="program" value="<?= $program ?>" />
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
                          <?php
                            $row = 1;
                            while ($data = $exec_query->fetch_assoc()){ 
                              $book = explode(' | ', $data['book_title']);  
                              
                            ?>
                              <tr id="row<?= $row ?>">
                                <td>
                                  <select name="titles[]" class="book form-select form-select-sm">
                                    <?php foreach($options as $option) : ?>
                                      <option value="<?= $option ?>" <?= $book[0] == $option ? 'selected' : '' ?>><?= $option ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                </td>
                                <td>
                                  <select name="levels[]" class="level form-select form-select-sm">
                                    <option value="Level Starter" <?= $book[1] == 'Level Starter' ? 'selected' : '' ?>>Level Starter</option>
                                    <option value="Level 1" <?= $book[1] == 'Level 1' ? 'selected' : '' ?>>Level 1</option>
                                    <option value="Level 2" <?= $book[1] == 'Level 2' ? 'selected' : '' ?>>Level 2</option>
                                    <option value="Level 3" <?= $book[1] == 'Level 3' ? 'selected' : '' ?>>Level 3</option>
                                    <option value="Level 4" <?= $book[1] == 'Level 4' ? 'selected' : '' ?>>Level 4</option>
                                    <option value="Level 5" <?= $book[1] == 'Level 5' ? 'selected' : '' ?>>Level 5</option>
                                    <option value="Level 6" <?= $book[1] == 'Level 6' ? 'selected' : '' ?>>Level 6</option>
                                  </select>
                                </td>
                                <td>
                                  <select name="booktype[]" class="booktype form-select form-select-sm">
                                    <option value="Textbook" <?= $book[2] == 'Textbook' ? 'selected' : '' ?>>Textbook</option>
                                    <option value="Workbook" <?= $book[2] == 'Workbook' ? 'selected' : '' ?>>Workbook</option>
                                  </select>
                                </td>
                                <td>
                                  <input type="text" name="jumlahsiswa[]" value="<?= number_format($data['qty'], '0', ',', '.') ?>" class="form-control only_number form-control-sm" placeholder="Jumlah Siswa..." onchange="updateDisabledField(this)">
                                </td>
                                <td>
                                  <input type="text" name="usulanharga[]" value="<?= number_format($data['usulan_harga'], '0', ',', '.') ?>" class="form-control only_number form-control-sm" placeholder="Usulan Harga..." onchange="updateDisabledField(this)">
                                </td>
                                <td>
                                  <input type="text" name="harganormal[]" value="<?= number_format($data['normalprice'], '0', ',', '.') ?>" class="form-control only_number form-control-sm" placeholder="Harga Buku Normal..." onchange="updateDisabledField(this)">
                                </td>
                                <td>
                                  <input type="text" name="diskon[]" value="<?= number_format($data['discount'], '0', ',', '.') ?>" max="30" class="form-control only_number form-control-sm" placeholder="Standard Discount..." onchange="updateDisabledField(this)">
                                </td>
                                <td><input type="text" class="aftd form-control form-control-sm" name="aftd[]" placeholder="0" readonly></td>
                                <td><input type="text" class="afto form-control form-control-sm" name="afto[]" placeholder="0" readonly></td>
                                <td><input type="text" class="befo form-control form-control-sm" name="befo[]" placeholder="0" readonly></td>
                                <td><input type="text" class="alok form-control form-control-sm" name="alokasi[]" placeholder="0" readonly></td>
                                
                                <td>
                                  <?php
                                    if($row == 1) {?>
                                    <button type='button' class="add-button btn btn-success" id='add_row'>
                                      <i class="fas fa-plus"></i>
                                  </button>
                                <?php } else {?>
                                  <button type="button" class="btn_remove btn btn-danger" data-row="row<?= $row ?>"><i class="fas fa-trash"></i></button>
                                <?php } ?>
                                </td>
                              </tr>
                          <?php $row++; } ?>
                        </tbody>
                    </table> 
                  </div>  

                  <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                    <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
                  </div>
                </form>
                <h4>Total Alokasi Benefit: <span id="accumulated_values"></span></h4>
            <?php }else { ?>
              <h6 class="mb-4" style="font-weight: 200" >Select Year and Program to proceed updating benefit</h6>
              <form method="POST" enctype="multipart/form-data" id="input_form_benefit">
                    
                <table class="table table-striped">
                  <tr>
                    <td style="width: 15%">Proram Year</td>
                    <td style="width:5px">:</td>
                    <td>
                      <select name="program_year" id="program_year" class="form-select form-select-sm">
                        <option value="">Select Year</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="width: 15%">Program Reffered</td>
                    <td style="width:5px">:</td>
                    <td>
                      <select name="program_reffered" id="program_reffered" class="form-select form-select-sm w-full">
                        <option value=''>Select Program</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td style="width: 15%">Inputter</td>
                    <td style="width:5px">:</td>
                    <td>
                      <input class="form-control form-control-sm" value="<?= $_SESSION['username'] ?>" disabled>
                      <input type="hidden" name="id_user" value="<?= $_SESSION['id_user'] ?>">
                    </td>
                  </tr>
                  <?php if($_SESSION['username'] == 'secretary@mentaribooks.com') : ?>
                    <tr>
                      <td>Nama EC</td>
                      <td>:</td>
                      <td>
                        <select name="inputEC" id="inputEC" class="form-select form-select-sm" required>
                          <option value=''>Select EC</option>
                          <?php 
                            $emp_sql    = "SELECT * from user where role='ec' order by generalname ASC"; 
                            $resultsd1  = mysqli_query($conn, $emp_sql);
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
                      <input type="text" id="select_school_label" class="form-control form-control-sm"  readonly required />
                      <input type="hidden" id="select_school" name="nama_sekolah" class="form-control form-control-sm" />
                    </td>
                  </tr>
                  <tr>
                    <td>Segment Sekolah</td>
                    <td>:</td>
                    <td>
                      <input type="text" name="segment" id="segment" class="form-control form-control-sm" required readonly />
                    </td>
                  </tr>
                  <tr>
                    <td>Jenjang Sekolah</td>
                    <td>:</td>
                    <td>
                      <input type="text" name="level" id="level" class="form-control form-control-sm" required readonly />
                    </td>
                  </tr>
                  <tr>
                    <td>Wilayah Sekolah</td>
                    <td>:</td>
                    <td>
                      <input type="text" name="wilayah" id="wilayah" class="form-control form-control-sm" required readonly />
                    </td>
                  </tr>
                  <tr>
                    <td>Program</td>
                    <td>:</td>
                    <td>
                      <input type="text" id="program_label" class="form-control form-control-sm" required readonly />
                      <input type="hidden" name="program" id="program" class="form-control form-control-sm" />
                    </td>
                  </tr>
                </table>

                <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                  <button type="submit" class="btn btn-primary m-2 fw-bold" id="proceed" disabled>Proceed</button>
                </div>
              </form>
            <?php } ?>
            </div>
          </div>
        </div>

      </div>
    
    <!-- Form End -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
  $(document).ready(function(){
    $('#inputEC').select2();
    var maxRows = 75; // Maximum rows allowed
    var x = <?= $row ?? 0 ?>; // Initial row counter
    x = x ? parseInt(x) : 1;
    // Add row
    $('#add_row').click(function(){
      if(x < maxRows){
        x++;
        var newRow = '<tr id="row'+x+'"><td><select name="titles[]" class="form-select form-select-sm book"></select></td><td><select name="levels[]" class="form-select form-select-sm level"></select></td><td><select name="booktype[]" class="form-select form-select-sm booktype"></select></td><td><input type="text" name="jumlahsiswa[]" class="form-control only_number form-control-sm" placeholder="Jumlah Siswa" onchange="updateDisabledField(this)"></td><td><input type="text" name="usulanharga[]" class="form-control only_number form-control-sm" placeholder="Usulan Harga" onchange="updateDisabledField(this)"></td><td><input type="text" name="harganormal[]" class="form-control only_number form-control-sm" placeholder="Harga Buku Normal" onchange="updateDisabledField(this)"></td><td><input type="text" name="diskon[]" max="30" class="form-control only_number form-control-sm" placeholder="Standard Discount" onchange="updateDisabledField(this)"></td><td><input type="text" class="aftd form-control form-control-sm" name="aftd[]" placeholder="0" readonly></td><td><input type="text" class="afto form-control form-control-sm" name="afto[]" placeholder="0" readonly></td><td><input type="text" class="befo form-control form-control-sm" name="befo[]" placeholder="0" readonly></td><td><input type="text" class="alok form-control form-control-sm" name="alokasi[]" placeholder="0" readonly></td><td><button type="button" class="btn_remove btn btn-danger" data-row="row'+x+'"><i class="fas fa-trash"></i></button></td></tr>';
        $('#input_form').append(newRow);
          populateDropdown('row'+x);
      }
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

    // Remove row
    $('#input_form').on('click', '.btn_remove', function(){
      var rowId = $(this).data('row');
      $('#' + rowId).remove();
      x--;
      accumulateAlokasi();
    });

    $(document).on('input', '.only_number', function() {
        let sanitizedValue = $(this).val().replace(/^0+|\D/g, '');

        let formattedValue = sanitizedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        $(this).val(formattedValue);
    });

    $('#draft_form').submit(function(e) {
      e.preventDefault();
      Swal.fire({
        title: "Are you sure?",
        text: "You will lost all benefit that you have entered!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, update it!"
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Updated!",
            text: "Your draft has been updated.",
            icon: "success"
          });
          $(this).unbind('submit').submit();
        }
      });
      
    })

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

    if(x <= 1) {
      populateDropdown('row1');
    }

    $('#submt').prop('disabled', true);
    initializeUpdateDisabledFields();

    $("#program_year").change(function () {
        var year = $(this).val();
        if (year !== "") {
            $.ajax({
                url: "get_dynamic_programs.php",
                type: "POST",
                data: { year: year },
                success: function (response) {
                  $("#program_reffered").html(response);
                  $('#program_reffered').select2();
                  $('#proceed').prop('disabled', true);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  console.log("status: ", errorThrown)
                }
            });
        } else {
            $("#program_reffered").html('<option value="">Select Program</option>');
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
              if(response["data"] && response["data"][0]) {
                let programData = response["data"][0];
                $("#select_school").val(programData.school_id);
                $("#select_school_label").val(programData.school_name);
                $("#segment").val(programData.segment);
                $("#level").val(programData.level);
                $("#wilayah").val(programData.wilayah);
                $("#program").val(programData.code);
                $("#program_label").val(programData.programe_name);
                $("#inputEC").val(programData.id_ec);
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

  });
</script>
<script type="text/javascript">

  function removeNonDigits(numberString) {
    let nonDigitRegex = /[^\d-]/g;
    let result = numberString.replace(nonDigitRegex, '');

    return result;
  }

  function initializeUpdateDisabledFields() {
    var elements = document.querySelectorAll('input[name="jumlahsiswa[]"]');
    elements.forEach(function(element) {
        updateDisabledField(element); 
    });
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

    var accumulatedResult = sumArray(accumulatedValues);
    if(accumulatedResult < 0){
        $('#submt').prop('disabled', true);
    }else{
        $('#submt').prop('disabled', false);
    }

    $('#accumulated_values').text(formatNumber(accumulatedResult));
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