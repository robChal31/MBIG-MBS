<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
             <?php
    $id_lainnya=$_GET['id'];
    $sql = "SELECT * from op_lainnya where id_lainnya='$id_lainnya'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>   



            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="new-benefit-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - lainnya</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select id_master,school_name,year from op_masterdata  order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."' ";
                                                if($rowa['id_master']==$row['id_master'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo ">".$row['school_name']." - ".$row['year']."</option>";
                                            }
                                        }
                                    ?>
                                    
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <div id="sisaquota">
                                
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" <?php if($id_lainnya>0){ echo "value='".$rowa['tanggal']."'";} ?>
                                    placeholder="Tanggal" required>
                                <label for="tanggal">Tanggal</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="descript" name="descript" <?php if($id_lainnya>0){ echo "value='".$rowa['description']."'";} ?>
                                    placeholder="Deskripsi" required>
                                <label for="description">Deskripsi</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="pelaksanaan" name="pelaksanaan" <?php if($id_lainnya>0){ echo "value='".$rowa['pelaksanaan']."'";} ?>
                                    placeholder="Pelaksanaan" required>
                                <label for="pelaksanaan">Pelaksanaan</label>
                            </div>
                            <div class="form-floating mb-3" id="containerss"> </div>
                            <input type="hidden" name="benefit_name" id="benefit_name" value="Lainnya">
                            <input type="hidden" name="subbenefit" id="subbenefit" value="Lainnya">
                            <input type="hidden" name="id_template_benefit" id="id_template_benefit" value="58">
                            <?php
                                if($id_lainnya>0)
                                {
                                    echo "<input type='hidden' name='id_lainnya' value='".$id_lainnya."'>";
                                    echo "<input type='hidden' name='action' value='edit'>";
                                }
                            ?>
                            <button type="submit" class="btn btn-lg btn-primary m-2"  id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->
            <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script type="text/javascript">
    $('#id_master').change(function(){
                $('#descript').val($(this).children('option:selected').data('descript'));
                var str = $('#id_master').val();
                    if (str.length == 0) {
                    document.getElementById("sisaquota").innerHTML = "";
                    return;
                    } else {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            if(this.responseText!=""){
                            document.getElementById("sisaquota").innerHTML = "Sisa kuotanya adalah " +this.responseText+" pax"; 
                            $('#submt').show(200); 
                            $('#submt').prop("disabled", false);
                            document.getElementById("member").max = this.responseText;
                            }
                            else
                            {
                                document.getElementById("sisaquota").innerHTML = "Belum menginput benefit";
                                $('#submt').hide(200); 
                            }
                        }
                    };
                    var time_stamp = new Date().getTime();
                    xmlhttp.open("GET", "newcekquota.php?idb=58&idm=" + str+"&time="+time_stamp, true);
                    xmlhttp.send();
                    }
            });
</script>
<?php include 'footer.php'; ?>