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
                            <form method="POST" action="benefit-usage-tdmta-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">School Benefit Input</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select id_master,school_name,year from op_masterdata order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."'".;
                                                if($_GET['i']==$row['id_master']){
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
                                <select class="form-select" data-role="select" id="id_benefittype" name="id_benefittype" placeholder="Tipe Benefit"
                                    aria-label="Tipe Benefit">
                                    <option disabled hidden value="">Pilih Tipe Benefit</option>
                                    <?php
                                        $sql="select id_benefittype,benefit_name from op_benefittype order by benefit_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_benefittype']."'>".$row['benefit_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_benefittype">Benefit Type</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="qty" name="qty"
                                    placeholder="Quota Benefit">
                                <label for="qty">Quota Benefit</label>
                            </div>
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>