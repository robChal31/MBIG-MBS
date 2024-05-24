<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
            <?php $type=$_GET['type'];?>

            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="new-benefit-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Usage - <?=ucwords($type)?> Input</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah" >
                                    <option disabled selected hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select b.id_master,b.school_name,b.year from op_new_benefit a left join op_masterdata b on a.id_master=b.id_master where a.approval=1 group by id_master order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."'>".$row['school_name']." - ".$row['year']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_template_benefit" name="id_template_benefit" placeholder="Pilih Benefit"
                                    aria-label="Pilih Benefit">
                                    <option disabled hidden selected value="">Pilih Benefit</option>
                                    <?php
                                        $sql="select * from op_template_benefit where benefit='".$type."'";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_template_benefit']."' data-descript='".$row['description']."' data-subbenefit='".$row['subbenefit']."' data-benefitname='".$row['benefit_name']."' data-pelaksanaan='".$row['pelaksanaan']."'>".$row['subbenefit']."-".$row['benefit_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <input type="hidden" name="subbenefit" id="subbenefit" value="">
                                <input type="hidden" name="benefit_name" id="benefit_name" value="">
                                <input type="hidden" name="type" id="type" value="<?=$type;?>">
                                <label for="id_template_benefit">Pilihan Benefit</label>
                            </div>
                            <div class="form-floating mb-3">
                                <div id="kuota">
                                    
                                </div>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="pelaksanaan" name="pelaksanaan"
                                    placeholder="Pelaksanaan"  required>
                                <label for="pelaksanaan">Pelaksanaan</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="descript" name="descript"
                                    placeholder="Deskripsi" required>
                                <label for="descript">Deskripsi</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    placeholder="Tanggal">
                                <label for="description">Tanggal</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="keterangan" name="keterangan"
                                    placeholder="Deskripsi" required>
                                <label for="descript">Keterangan</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member" name="member"
                                    placeholder="Quantity Tahun 1"><div id="sisaquota"></div>
                                <label for="description">Quantity Tahun 1</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member2" name="member2"
                                    placeholder="Quantity Tahun 2">
                                <label for="description">Quantity Tahun 2</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="member3" name="member3"
                                    placeholder="Quantity Tahun 3">
                                <label for="description">Quantity Tahun 3</label>
                            </div>
                            <?php if($type!='sarana prasarana' || $type!='lainnya'):?>
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" id="manualValue" name="manualValue"
                                    placeholder="0" value="0">
                                <label for="description">Manual Value (tidak diisi = nilai default)</label>
                            </div>
                            <?php endif; ?>
                            <?php if($type=='training'):?>
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="jamValue" name="jamValue"
                                        placeholder="Jumlah Jam">
                                    <label for="jamValue">Jumlah Jam</label>
                                </div>
                            <?php endif; ?>
                            <?php if($type=='sarana prasarana' || $type=='lainnya'):?>
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="manualValue" name="manualValue"
                                        placeholder="Nilai dalam Rupiah">
                                    <label for="manualValue">Nilai dalam Rupiah</label>
                                </div>
                            <?php endif; ?>
                            <div class="form-floating mb-3" id="containerss"> </div>
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2" disabled id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>