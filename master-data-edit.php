<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->

<!--GRAB CURRENT DATA-->
<?php
    $id_master=$_GET['id'];
    $sql = "SELECT * from op_masterdata where id_master='$id_master'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>
            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="master-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Master Data - School</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namSek" name="namSek" value='<?=$rowa['school_name']?>'
                                    placeholder="Nama Sekolah">
                                <label for="floatingInput">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="jenDok" name="jenDok" placeholder="Jenis Dokumen"
                                    aria-label="Jenis Dokumen">
                                    <option value="PK3" <?php if($rowa['jenDok']=='PK3'){echo "selected";}?>>PK3</option>
                                    <option value="PK5" <?php if($rowa['jenDok']=='PK5'){echo "selected";}?>>PK5</option>
                                    <option value="Oneprice" <?php if($rowa['jenDok']=='Oneprice'){echo "selected";}?>>Oneprice</option>
                                    <option value="CBLS" <?php if($rowa['jenDok']=='CBLS'){echo "selected";}?>>CBLS</option>
                                    <option value="CBLS" <?php if($rowa['jenDok']=='Prestasi'){echo "selected";}?>>Prestasi</option>
                                </select>
                                <label for="statPI">Jenis Dokumen</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="minQty" name="minQty" value='<?=$rowa['minQty']?>'
                                    placeholder="Min Qty Adopsi">
                                <label for="minQty">Min Qty Adopsi</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_sa" name="id_sa" placeholder="Sales Admin"
                                    aria-label="Sales Admin">
                                    <option disabled hidden value="">Pilih Sales Admin</option>
                                    <?php
                                        $sql="select * from dash_sa order by sa_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_sa']."'";
                                                if($rowa['id_sa']==$row['id_sa'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo ">".$row['sa_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Sales Admin</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_ec" name="id_ec" placeholder="Education Consultant"
                                    aria-label="Education Consultant">
                                    <option disabled hidden value="">Pilih Education Consultant</option>
                                    <?php
                                        $sql="select * from dash_ec order by ec_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_ec']."'";
                                                if($rowa['id_ec']==$row['id_ec'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo ">".$row['ec_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_ec">Education Consultant</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tgl" name="tgl" value='<?=$rowa['date']?>'
                                    placeholder="Tanggal">
                                <label for="tgl">Tanggal</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tglExp" name="tglExp" value='<?=$rowa['expiredDate']?>'
                                    placeholder="Expired Date">
                                <label for="tgl">Expired Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min="2020" max="2099" value="2021" class="form-control" id="thn" name="thn" value='<?=$rowa['year']?>'
                                    placeholder="Tahun">
                                <label for="tgl">Tahun</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="noSOR" name="noSOR" value='<?=$rowa['nosor']?>'
                                    placeholder="No SOR / No PK">
                                <label for="noSOR">No SOR / No PK</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="statPI" name="statPI" placeholder="Status PI / PK"
                                    aria-label="Status PI / PK">
                                    <option value="1" <?php if($rowa['statuspi']==1){echo " selected ";}?>>Aktif</option>
                                    <option value="0" <?php if($rowa['statuspi']==0){echo " selected ";}?>>Cancel Order</option>
                                </select>
                                <label for="statPI">Status PI / PK</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="titleAdopt" name="titleAdopt[]" placeholder="Judul Adopsi"
                                    aria-label="Judul Adopsi" onchange="yesnoCheck(this);" multiple style="height:200px;">
                                    <option <?php if($rowa['title']=="Aim High"){echo "selected";}?> value="Aim High">Aim High</option>
                                    <option <?php if($rowa['title']=="Big Show"){echo "selected";}?> value="Big Show">Big Show</option>
                                    <option <?php if($rowa['title']=="Camb Primary Maths"){echo "selected";}?> value="Camb Primary Maths">Camb Primary Maths</option>
                                    <option <?php if($rowa['title']=="Camb Primary Science"){echo "selected";}?> value="Camb Primary Science">Camb Primary Science</option>
                                    <option <?php if($rowa['title']=="Cambridge Checkpoint"){echo "selected";}?> value="Cambridge Checkpoint">Cambridge Checkpoint</option>
                                    <option <?php if($rowa['title']=="Chun Hui"){echo "selected";}?> value="Chun Hui">Chun Hui</option>
                                    <option <?php if($rowa['title']=="Elevator"){echo "selected";}?> value="Elevator">Elevator</option>
                                    <option <?php if($rowa['title']=="English Ahead"){echo "selected";}?> value="English Ahead">English Ahead</option>
                                    <option <?php if($rowa['title']=="English Chest"){echo "selected";}?> value="English Chest">English Chest</option>
                                    <option <?php if($rowa['title']=="English In Mind"){echo "selected";}?> value="English In Mind">English In Mind</option>
                                    <option <?php if($rowa['title']=="Everybody Up"){echo "selected";}?> value="Everybody Up">Everybody Up</option>
                                    <option <?php if($rowa['title']=="Family & Friend"){echo "selected";}?> value="Family & Friend">Family & Friend</option>
                                    <option <?php if($rowa['title']=="Hang Out"){echo "selected";}?> value="Hang Out">Hang Out</option>
                                    <option <?php if($rowa['title']=="Ibu Pertiwi"){echo "selected";}?> value="Ibu Pertiwi">Ibu Pertiwi</option>
                                    <option <?php if($rowa['title']=="IGCSE CUP"){echo "selected";}?> value="IGCSE CUP">IGCSE CUP</option>
                                    <option <?php if($rowa['title']=="IGCSE MCE"){echo "selected";}?> value="IGCSE MCE">IGCSE MCE</option>
                                    <option <?php if($rowa['title']=="Juara Matematika"){echo "selected";}?> value="Juara Matematika">Juara Matematika</option>
                                    <option <?php if($rowa['title']=="Juara Sains"){echo "selected";}?> value="Juara Sains">Juara Sains</option>
                                    <option <?php if($rowa['title']=="Maths Ahead"){echo "selected";}?> value="Maths Ahead">Maths Ahead</option>
                                    <option <?php if($rowa['title']=="MC Maths "){echo "selected";}?> value="MC Maths ">MC Maths </option>
                                    <option <?php if($rowa['title']=="MC Science"){echo "selected";}?> value="MC Science">MC Science</option>
                                    <option <?php if($rowa['title']=="Meihua "){echo "selected";}?> value="Meihua ">Meihua </option>
                                    <option <?php if($rowa['title']=="Menjadi Indonesia"){echo "selected";}?> value="Menjadi Indonesia">Menjadi Indonesia</option>
                                    <option <?php if($rowa['title']=="MPH English"){echo "selected";}?> value="MPH English">MPH English</option>
                                    <option <?php if($rowa['title']=="MPH Maths "){echo "selected";}?> value="MPH Maths ">MPH Maths </option>
                                    <option <?php if($rowa['title']=="MPH Science"){echo "selected";}?> value="MPH Science">MPH Science</option>
                                    <option <?php if($rowa['title']=="My Book"){echo "selected";}?> value="My Book">My Book</option>
                                    <option <?php if($rowa['title']=="New Frontiers"){echo "selected";}?> value="New Frontiers">New Frontiers</option>
                                    <option <?php if($rowa['title']=="New Maths Champion"){echo "selected";}?> value="New Maths Champion">New Maths Champion</option>
                                    <option <?php if($rowa['title']=="New Syllabus Mathematics"){echo "selected";}?> value="New Syllabus Mathematics">New Syllabus Mathematics</option>
                                    <option <?php if($rowa['title']=="OWL English"){echo "selected";}?> value="OWL English">OWL English</option>
                                    <option <?php if($rowa['title']=="OWL Maths"){echo "selected";}?> value="OWL Maths">OWL Maths</option>
                                    <option <?php if($rowa['title']=="Prepare"){echo "selected";}?> value="Prepare">Prepare</option>
                                    <option <?php if($rowa['title']=="Rainbow English"){echo "selected";}?> value="Rainbow English">Rainbow English</option>
                                    <option <?php if($rowa['title']=="Rainbow Maths"){echo "selected";}?> value="Rainbow Maths">Rainbow Maths</option>
                                    <option <?php if($rowa['title']=="Rainbow Science"){echo "selected";}?> value="Rainbow Science">Rainbow Science</option>
                                    <option <?php if($rowa['title']=="Science Ahead"){echo "selected";}?> value="Science Ahead">Science Ahead</option>
                                    <option <?php if($rowa['title']=="Sounds Great"){echo "selected";}?> value="Sounds Great">Sounds Great</option>
                                    <option <?php if($rowa['title']=="Super Minds"){echo "selected";}?> value="Super Minds">Super Minds</option>
                                    <option <?php if($rowa['title']=="Take Off with English"){echo "selected";}?> value="Take Off with English">Take Off with English</option>
                                    <option <?php if($rowa['title']=="Think "){echo "selected";}?> value="Think ">Think </option>
                                    <option <?php if($rowa['title']=="Think Maths"){echo "selected";}?> value="Think Maths">Think Maths</option>
                                    <option <?php if($rowa['title']=="Tracing is Fun"){echo "selected";}?> value="Tracing is Fun">Tracing is Fun</option>
                                    <option <?php if($rowa['title']=="Others"){echo "selected";}?> value="Others">Others</option>
                                </select>
                                <label for="titleAdopt">Judul Adopsi</label>
                                <input type="text" class="form-control titleOther" id="titleOther" name="titleOther"
                                    placeholder="Title Other" style="display:none;">
                                <input type="hidden" name="action" value="edit"><input type="hidden" name="id_master" value="<?=$rowa['id_master']?>">
                            </div>
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->
    <script type="text/javascript">
        function yesnoCheck(that) {
            if (that.value == "Others") {
                document.getElementById("titleOther").style.display = "block";
            } else {
                document.getElementById("titleOther").style.display = "none";
            }
        }
    </script>

<?php include 'footer.php'; ?>