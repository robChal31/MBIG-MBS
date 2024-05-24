<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->

<?php

    $id_master=$_GET['id'];
    if(!is_null($id_master))
    {
        $sql = "SELECT * from op_masterdata where id_master='$id_master'";
        $result = mysqli_query($conn, $sql);
        $rowa = mysqli_fetch_assoc($result);
    }
    
?>
            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="new-master-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Master Data - School</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namSek" name="namSek"
                                    placeholder="Nama Sekolah">
                                <label for="floatingInput">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namPIC" name="namPIC"
                                    placeholder="Nama PIC">
                                <label for="namPIC">Nama PIC</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="jabatan" name="jabatan"
                                    placeholder="Jabatan PIC">
                                <label for="jabatan">Jabatan PIC</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="noHP" name="noHP"
                                    placeholder="HP PIC">
                                <label for="noHP">HP PIC</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="emailPIC" name="emailPIC"
                                    placeholder="E-mail PIC">
                                <label for="emailPIC">E-mail PIC</label>
                            </div>
                           
                            <div class="mb-3">
                                <label for="fileToUpload" class="form-label">Unggah Dokumen</label>
                                <input class="form-control" type="file" id="fileToUpload" name="fileToUpload">
                            </div>
                            <?php if($_SESSION['role']=='admin'):?>
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
                            <?php else:?>
                            <input type='hidden' name='id_ec' value='<?=$_SESSION['id_user']?>'>";
                            <?php endif; ?>
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
                                   <?php 
                                    $sql = "SELECT * from  calc_title order by title_name";
                                    if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['title_name']."'>".$row['title_name']."</option>";
                                            }
                                            echo "<option value='Others'>Others</option>";
                                        }
                                    
                                   ?>
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