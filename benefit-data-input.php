<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
<?php
    $id_benefit=$_GET['id'];
    $sql = "SELECT * from op_benefit where id_benefit='$id_benefit'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>

            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="benefit-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Master Data - School Benefit</h6>
                            <?php if(!is_null($_GET['i'])):?>
                            
                                <div class="alert alert-success" role="alert">
                                Input Berhasil
                            </div>
                            
                            <?php endif;?>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Nama Sekolah"
                                    aria-label="Nama Sekolah">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select id_master,school_name,year, jenDok from op_masterdata order by school_name ASC";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_master']."'";
                                                if($_GET['i']==$row['id_master'] || $rowa['id_master']==$row['id_master']){
                                                    echo " selected ";
                                                }
                                                echo ">".$row['school_name']." - ".$row['year']." ".$row['jenDok']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_benefittype" name="id_benefittype" placeholder="Nama Benefit"
                                    aria-label="Nama Benefit">
                                    <option disabled hidden value="">Pilih Sekolah - Tahun</option>
                                    <?php
                                        $sql="select id_benefittype,benefit_name from op_benefittype";
                                        if($result = mysqli_query($conn,$sql))
                                        {
                                            while($row = mysqli_fetch_assoc($result))
                                            {
                                                echo "<option value='".$row['id_benefittype']."'"; 
                                                if($rowa['id_benefittype']==$row['id_benefittype'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo ">".$row['benefit_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Benefit</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min="0" max="100"  class="form-control" id="qty" name="qty" <?php if($id_benefit>0){echo "value='".$rowa['qty']."' ";} else {echo 'value="0"';}?>
                                    placeholder="Tahun">
                                <label for="qty">Quantity</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="description" name="description" <?php if($id_benefit>0){echo "value='".$rowa['description']."' ";}?>
                                    placeholder="Deskripsi">
                                <label for="description">Deskripsi</label>
                            </div>
                            <?php
                                if(!is_null($id_benefit))
                                {
                                    echo "<input type='hidden' name='action' value='edit'>";
                                    echo "<input type='hidden' name='id_benefit' value='".$id_benefit."'>";
                                }
                            ?>
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>