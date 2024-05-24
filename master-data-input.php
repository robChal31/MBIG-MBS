<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->


            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="master-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Master Data - School</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namSek" name="namSek"
                                    placeholder="Nama Sekolah">
                                <label for="floatingInput">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="jenDok" name="jenDok" placeholder="Jenis Dokumen"
                                    aria-label="Jenis Dokumen">
                                    <option value="PK3">PK3</option>
                                    <option value="PK5">PK5</option>
                                    <option value="Oneprice">Oneprice</option>
                                    <option value="CBLS 1">CBLS 1</option>
                                    <option value="CBLS 3">CBLS 3</option>
                                    <option value="SKKS 1">SKKS 1</option>
                                    <option value="SKKS 1">SKKS 3</option>
                                    <option value="Prestasi">Prestasi</option>
                                </select>
                                <label for="statPI">Jenis Dokumen</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="minQty" name="minQty"
                                    placeholder="Min Qty Adopsi">
                                <label for="minQty">Min Qty Adopsi</label>
                            </div>
                            <div class="mb-3">
                                <label for="fileToUpload" class="form-label">Unggah Dokumen</label>
                                <input class="form-control" type="file" id="fileToUpload" name="fileToUpload">
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
                                                echo "<option value='".$row['id_sa']."'>".$row['sa_name']."</option>";
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
                                                echo "<option value='".$row['id_ec']."'>".$row['ec_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_ec">Education Consultant</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tgl" name="tgl"
                                    placeholder="Tanggal">
                                <label for="tgl">Tanggal</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tglExp" name="tglExp"
                                    placeholder="Expired Date">
                                <label for="tgl">Expired Date</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min="2017" max="2099" value="2021" class="form-control" id="thn" name="thn"
                                    placeholder="Tahun">
                                <label for="tgl">Tahun</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="noSOR" name="noSOR"
                                    placeholder="No SOR / No PK">
                                <label for="noSOR">No SOR / No PK</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="statPI" name="statPI" placeholder="Status PI / PK"
                                    aria-label="Status PI / PK">
                                    <option value="1">Aktif</option>
                                    <option value="0">Cancel Order</option>
                                </select>
                                <label for="statPI">Status PI / PK</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="titleAdopt" name="titleAdopt[]" placeholder="Judul Adopsi"
                                    aria-label="Judul Adopsi" onchange="yesnoCheck(this);" multiple style="height:200px;">
                                    <option value="Aim High">Aim High</option>
                                    <option value="Big Show">Big Show</option>
                                    <option value="Camb Primary Maths">Camb Primary Maths</option>
                                    <option value="Camb Primary Science">Camb Primary Science</option>
                                    <option value="Cambridge Checkpoint">Cambridge Checkpoint</option>
                                    <option value="Chun Hui">Chun Hui</option>
                                    <option value="Elevator">Elevator</option>
                                    <option value="English Ahead">English Ahead</option>
                                    <option value="English Chest">English Chest</option>
                                    <option value="English In Mind">English In Mind</option>
                                    <option value="Everybody Up">Everybody Up</option>
                                    <option value="Family & Friend">Family & Friend</option>
                                    <option value="Hang Out">Hang Out</option>
                                    <option value="Ibu Pertiwi">Ibu Pertiwi</option>
                                    <option value="IGCSE CUP">IGCSE CUP</option>
                                    <option value="IGCSE MCE">IGCSE MCE</option>
                                    <option value="Juara Matematika">Juara Matematika</option>
                                    <option value="Juara Sains">Juara Sains</option>
                                    <option value="Maths Ahead">Maths Ahead</option>
                                    <option value="MC Maths ">MC Maths </option>
                                    <option value="MC Science">MC Science</option>
                                    <option value="Meihua ">Meihua </option>
                                    <option value="Menjadi Indonesia">Menjadi Indonesia</option>
                                    <option value="MPH English">MPH English</option>
                                    <option value="MPH Maths ">MPH Maths </option>
                                    <option value="MPH Science">MPH Science</option>
                                    <option value="My Book">My Book</option>
                                    <option value="New Frontiers">New Frontiers</option>
                                    <option value="New Maths Champion">New Maths Champion</option>
                                    <option value="New Syllabus Mathematics">New Syllabus Mathematics</option>
                                    <option value="OWL English">OWL English</option>
                                    <option value="OWL Maths">OWL Maths</option>
                                    <option value="Prepare">Prepare</option>
                                    <option value="Rainbow English">Rainbow English</option>
                                    <option value="Rainbow Maths">Rainbow Maths</option>
                                    <option value="Rainbow Science">Rainbow Science</option>
                                    <option value="Science Ahead">Science Ahead</option>
                                    <option value="Sounds Great">Sounds Great</option>
                                    <option value="Super Minds">Super Minds</option>
                                    <option value="Take Off with English">Take Off with English</option>
                                    <option value="Think ">Think </option>
                                    <option value="Think Maths">Think Maths</option>
                                    <option value="Tracing is Fun">Tracing is Fun</option>
                                    <option value="Others">Others</option>
                                </select>
                                <label for="titleAdopt">Judul Adopsi</label>
                                <input type="text" class="form-control titleOther" id="titleOther" name="titleOther"
                                    placeholder="Title Other" style="display:none;">
                                
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