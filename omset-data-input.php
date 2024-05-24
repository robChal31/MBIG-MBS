<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
<?php
    $id_omset=$_GET['id'];
    $sql = "SELECT * from op_omset where id_omset='$id_omset'";
    $result = mysqli_query($conn, $sql);
    $rowa = mysqli_fetch_assoc($result);
?>

            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="omset-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Master Data - School Omset</h6>
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
                                                echo "<option value='".$row['id_master']."'"; 
                                                if($rowa['id_master']==$row['id_master'])
                                                {
                                                    echo " selected ";
                                                }
                                                echo">".$row['school_name']." - ".$row['year']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                                <label for="id_sa">Nama Sekolah</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min="2000" max="2099" value="2021" class="form-control" id="thn" name="thn" <?php if($rowa['year']>0) echo "value='".$rowa['year']."'"; ?>
                                    placeholder="Tahun" required>
                                <label for="tgl">Tahun</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="omset" name="omset" <?php if($rowa['omset']>0) echo "value='".$rowa['omset']."'"; ?>
                                    placeholder="Nilai Omset" required>
                                <label for="omset">Nilai Omset</label>
                            </div>
                            <?php if(!is_null($id_omset))
                                    {
                                     echo "<input type='hidden' name='id_omset' value='".$id_omset."'>";
                                     echo "<input type='hidden' name='action' value='edit'>";
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