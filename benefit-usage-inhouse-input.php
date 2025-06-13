<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
  <?php
    $id_inhouse=$_GET['id'];
    $sql = "SELECT * from op_inhouse where id_inhouse='$id_inhouse'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>          

             <script>
                function cekquota(selectObject) {
                    var str = selectObject.value;  
                  if (str.length == 0) {
                    document.getElementById("quota").innerHTML = "";
                    return;
                  } else {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                      if (this.readyState == 4 && this.status == 200) {
                          if(this.responseText!=""){
                            document.getElementById("sisaquota").innerHTML = "Sisa kuotanya adalah " +this.responseText+" pax"; 
                            document.getElementById("member").max = this.responseText;
                          }
                          else
                          {
                             document.getElementById("sisaquota").innerHTML = "Belum menginput benefit"; 
                          }
                      }
                    };
                    var time_stamp = new Date().getTime();
                        xmlhttp.open("GET", "cekquota.php?type=2&idm=" + str+"&time="+time_stamp, true);
                    xmlhttp.send();
                  }
                }
                </script>

            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="benefit-usage-inhouse-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - Inhouse</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah" onchange="cekquota(this)">
                                    <option disabled hidden value="" >Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select b.id_master,b.school_name,b.year from op_benefit a left join op_masterdata b on a.id_master=b.id_master where a.approval=1 and a.id_benefittype=2 order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."' ";
                                                if($rowa['id_master']==$row['id_master'])
                                                {
                                                    echo " selected";
                                                }
                                                echo ">".$row['school_name']." - ".$row['year']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <div id="kuota">
                                    
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="judul" name="judul" <?php if($id_inhouse>0){ echo "value='".$rowa['training_name']."'";} ?>
                                    placeholder="Judul Training" required>
                                <label for="description">Judul Training</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" <?php if($id_inhouse>0){ echo "value='".$rowa['training_date']."'";} ?>
                                    placeholder="Tanggal Training">
                                <label for="description">Tanggal Training</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="trainer" name="trainer" <?php if($id_inhouse>0){ echo "value='".$rowa['trainer_name']."'";} ?>
                                    placeholder="Trainer">
                                <label for="description">Trainer</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-control" id="onoff" name="onoff" >
                                    <option value="0">Offline</option>
                                    <option value="1">Online</option>
                                </select>
                                <label for="onoff">Online Offline</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member" name="member"  <?php if($id_inhouse>0){ echo "value='".$rowa['jumlah_peserta']."'";} ?>
                                    placeholder="Jumlah total peserta" required><div id="sisaquota"></div>
                                <label for="description">Jumlah total peserta</label>
                            </div>
                            <?php
                                if($id_inhouse>0)
                                {
                                    echo "<input type='hidden' name='action' value='edit'>";
                                    echo "<input type='hidden' name='id_inhouse' value='".$id_inhouse."'>";
                                }
                            ?>
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>