<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
            <?php $type=$_GET['type'];?>
    <style>
        table.dataTable tbody td {
            padding : 2px !important;
        }
        </style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  
<script>
  $(document).ready(function(){
    var maxRows = 75; // Maximum rows allowed
    var x = 1; // Initial row counter

    // Add row
    $('#add_row').click(function(){
      if(x < maxRows){
        x++;
        var newRow = '<tr id="row'+x+'"><td><select name="titles[]" class="book"></select></td><td><select name="levels[]" class="level"></select></td><td><select name="booktype[]" class="booktype"></select></td><td><input type="number" name="jumlahsiswa[]" placeholder="Jumlah Siswa" onchange="updateDisabledField(this)"></td><td><input type="number" name="usulanharga[]" placeholder="Usulan Harga" onchange="updateDisabledField(this)"></td><td><input type="number" name="harganormal[]" placeholder="Harga Buku Normal" onchange="updateDisabledField(this)"></td><td><input type="number" name="diskon[]" max="25" placeholder="Standard Discount" onchange="updateDisabledField(this)"></td><td><input type="number" class="aftd" name="aftd[]" placeholder="0" readonly></td><td><input type="number" class="afto" name="afto[]" placeholder="0" readonly></td><td><input type="number" class="befo" name="befo[]" placeholder="0" readonly></td><td><input type="number" class="alok" name="alokasi[]" placeholder="0" readonly></td><td><button type="button" class="btn_remove" data-row="row'+x+'">Remove</button></td></tr>';
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
          dropdown2.html('<option value="Level 1">Level 1</option><option value="Level 2">Level 2</option><option value="Level 3">Level 3</option><option value="Level 4">Level 4</option><option value="Level 5">Level 5</option><option value="Level 6">Level 6</option>');
          dropdown3.html('<option value="Textbook">Textbook</option><option value="Workbook">Workbook</option>');
        }
      });
    }

    populateDropdown('row1');
    $('#submt').prop('disabled',true);
    
  });
</script>
            <!-- Form Start -->
            <div class="container-fluid pt-4">
                <div class="row g-4">
                    
                    <div class="col-sm-12 col-xl-12">
                        <div class="bg-light rounded h-100">
                            <form method="POST" action="new2-benefit-ec-input-action1.php" enctype="multipart/form-data">
                            <table class="table table-striped">
                                <tr><td>Inputter : <?=$_SESSION['username']?><input type="hidden" name="id_user" value="<?=$_SESSION['id_user']?>"></td></tr>
                                <?php if($_SESSION['username']=='putri@mentarigroups.com'):?>
                                <tr><td>Nama EC : <select name="inputEC" id="inputEC"><?php $sql = "SELECT * from user where role='ec' order by generalname ASC"; $resultsd1 = mysqli_query($conn, $sql);

                                while ($row = mysqli_fetch_assoc($resultsd1))
                                {
                                  echo "<option value='".$row['id_user']."'>".$row['generalname']."</option>";
                                } ?>
                                </select></td></tr><?php else: {echo "<input type='hidden' name='inputEC' value='".$_SESSION['id_user']."'>";} endif; ?>
                                <tr><td>Sekolah :
                                <select class="form-select" data-role="select" id="id_master" name="id_master"
                                    aria-label="Education Consultant">
                                    <option disabled hidden value="">Pilih Sekolah</option>
                                    <?php
                                        $sql="select * from op_masterdata where id_ec='".$_SESSION['id_user']."' and newMaster=1 order by school_name ASC";                                
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."'>".$row['school_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                              </td></tr>
                                <tr><td>Wilayah / Segment : <input type="text" name="segment" placeholder="Wilayah / Segment"  required></td></tr>
                                <tr><td>Program : <select name="program">
                                    <option value="cbls1">CBLS 1</option>
                                    <option value="cbls3">CBLS 3</option>
                                    <option value="prestasi" >Prestasi</option>
                                    <option value="pk3">PK3</option>
                                </select></td></tr>
                            </table>
                             <table class="table table-striped table-bordered dataTable no-footer" id="input_form">
                                <thead>
                                    <td>Judul Buku</td>
                                    <td>Level</td>
                                    <td>Jenis Buku</td>
                                    <td>Jumlah Siswa</td>
                                    <td>Usulan Harga Program</td>
                                    <td>Harga Buku Normal</td>
                                    <td>Standard Discount</td>
                                    <td>Harga Setelah Diskon</td>
                                    <th>Revenue Harga Program</th>
                                    <th>Revenue Harga Normal</th>
                                    <td>Alokasi pengembangan sekolah</td>
                                    <td></td>
                                </thead>
                                <tbody>
                                    <tr id="row1">
                                        <td><select name="titles[]" class="book"></select></td>
                                        <td><select name="levels[]" class="level"></select></td>
                                        <td><select name="booktype[]" class="booktype"></select></td>
                                        <td><input type="number" name="jumlahsiswa[]" placeholder="Jumlah Siswa" onchange="updateDisabledField(this)"></td>
                                        <td><input type="number" name="usulanharga[]" placeholder="Usulan Harga" onchange="updateDisabledField(this)"></td>
                                        <td><input type="number" name="harganormal[]" placeholder="Harga Buku Normal" onchange="updateDisabledField(this)"></td>
                                        <td><input type="number" name="diskon[]" max="25" placeholder="Standard Discount" onchange="updateDisabledField(this)"></td>
                                        <td><input type="number" class="aftd" name="aftd[]" placeholder="0" readonly></td>
                                        <td><input type="number" class="afto" name="afto[]" placeholder="0" readonly></td>
                                        <td><input type="number" class="befo" name="befo[]" placeholder="0" readonly></td>
                                        <td><input type="number" class="alok" name="alokasi[]" placeholder="0" readonly></td>
                                        
                                        <td></td>
                                    </tr>
                                </tbody>
                             </table>   
                            
                    
                             <br>
                            <button type="button" id="add_row">Add Row</button>
                            <br><br>
                           
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt">Submit</button>
                        </div>
                        </form>
                        <H3>Total Alokasi Benefit: <span id="accumulated_values"></span></H3>
                    </div>
                </div>
            </div>
            <!-- Form End -->

<script type="text/javascript">
    function updateDisabledField(element) {
      var row = $(element).closest('tr');
      var disabledField = row.find('input[name="alokasi[]"]');
      var aftd = row.find('input[name="aftd[]"]');
      var befo = row.find('input[name="befo[]"]');
      var afto = row.find('input[name="afto[]"]');
      
      if(!isNaN(row.find('input[name="jumlahsiswa[]"').val()))
      {
        var jumlah = row.find('input[name="jumlahsiswa[]"').val();
      }
      else
      {
        var jumlah = 0;
      }
      if(!isNaN(row.find('input[name="usulanharga[]"').val()))
      {
        var usulan = row.find('input[name="usulanharga[]"').val();
      }
      else
      {
        var usulan = 0;
      }
      if(!isNaN(row.find('input[name="harganormal[]"').val()))
      {
        var normal = row.find('input[name="harganormal[]"').val();
      }
      else
      {
        var normal = 0;
      } 
      
      if(!isNaN(row.find('input[name="diskon[]"').val()))
      {
       
        var diskon = row.find('input[name="diskon[]"').val();
        if(diskon>25)
        {
            diskon = 25;
            alert("Diskon melebihi ketentuan, silakan ajukan persetujuan ke HOR/Top Leader terlebih dahulu. Terima kasih");
            row.find('input[name="diskon[]"').val(25);
        }
      }
      else
      {
        var diskon = 0;
      }
      if(Number(usulan)<parseInt(normal))
      {
          row.find('input[name="usulanharga[]"').val(normal);
          alert("Harga Usulan Invalid");
          row.find('input[name="diskon[]"').val(0);
      }
      
      
        var setelahDiskon = normal -  (diskon/100 * normal);
        aftd.val(setelahDiskon);
        console.log("setelah diskon :"+setelahDiskon);
        var onepriceRevenue = jumlah * usulan;
        afto.val(onepriceRevenue);
        console.log("revenue oneprice :"+onepriceRevenue);
        var sebelumOneprice = jumlah * setelahDiskon;
        befo.val(sebelumOneprice);
        console.log("sebelum oneprice :"+sebelumOneprice);
        var alokasi = onepriceRevenue - sebelumOneprice;
        disabledField.val(alokasi);
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
        var value = parseFloat($(this).val());
        if (!isNaN(value)) {
            accumulatedValues.push(value);
        }
      });
      if(accumulatedResult <=0)
      {
          $('#submt').prop('disabled',true);
      }
      else
      {
          $('#submt').prop('disabled',false);
      }
      var accumulatedResult = sumArray(accumulatedValues);
      $('#accumulated_values').text(accumulatedResult);
    }
</script>
<?php include 'footer.php'; ?>