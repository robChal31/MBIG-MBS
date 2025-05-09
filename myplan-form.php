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

  $username = $_SESSION['username'];
  $id_user  = $_SESSION['id_user'];

?>
  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">
      <div class="row">
        <div class="col-12">
          <div class="bg-whites rounded h-100 p-4">
            <h6 class="mb-4">Create Plan</h6>
            <form method="POST" action="myplan-save.php" enctype="multipart/form-data" id="myplan-form">
                
              <table class="table table-striped">
                <?php if($id_user == 70) : ?>
                  <tr>
                    <td>Nama EC</td>
                    <td>:</td>
                    <td>
                      <select name="id_user" class="form-select form-select-sm">
                        <?php 
                          $sql = "SELECT * from user where role='ec' order by generalname ASC"; 
                          $resultsd1 = mysqli_query($conn, $sql);
                          while ($row = mysqli_fetch_assoc($resultsd1)){
                            echo "<option value='".$row['id_user']."'>".$row['generalname']."</option>";
                          } 
                        ?>
                      </select>
                    </td>
                  </tr>
                <?php else : ?> 
                  <tr>
                    <td>Nama EC</td>
                    <td>:</td>
                    <td>
                      <?= $username?><input type="hidden" name="id_user" value="<?= $id_user ?>">
                    </td>
                  </tr>
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
                  <td>Program</td>
                  <td>:</td>
                  <td>
                    <select name="program" id="program" class="form-select form-select-sm select2" required>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>Wilayah Sekolah</td>
                  <td>:</td>
                  <td><input type="text" name="wilayah" placeholder="Wilayah" class="form-control form-control-sm" required></td>
                </tr>
                <tr>
                  <td>Proyeksi Siswa</td>
                  <td>:</td>
                  <td>
                    <input type="number" name="student_projection" placeholder="Proyeksi Siswa" class="form-control form-control-sm" required>
                  </td>
                </tr>
                <tr>
                  <td>Proyeksi Omset</td>
                  <td>:</td>
                  <td>
                    <input type="number" name="omset_projection" placeholder="Proyeksi Omset" class="form-control form-control-sm" required>
                  </td>
                </tr>
              </table>

              <div class="d-flex justify-content-end mt-4" style="cursor: pointer;">
                <button type="submit" class="btn btn-primary m-2 fw-bold" id="submt">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Form End -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
  $(document).ready(function(){
    $('.select2').select2();

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
      } else {
          alert('No school selected');
      }
    });

    $('#submt').click(function(){
      $('#submt').prop('disabled',true);
      $('#myplan-form').submit();
    });
    
  });
</script>

<?php include 'footer.php'; ?>