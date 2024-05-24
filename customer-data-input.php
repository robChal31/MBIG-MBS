<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->

            <?php
                $id_customerdata=$_GET['id'];
                $sql = "SELECT * from op_customerdata where id_customerdata='$id_customerdata'";
                $result = mysqli_query($conn, $sql);
                $rowa = mysqli_fetch_assoc($result);
            ?>    
            <!-- Form Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-light rounded h-100 p-4">
                            <form method="POST" action="customer-data-input-action.php" enctype="multipart/form-data">
                            <h6 class="mb-4">Customer Data - School</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" data-role="select" id="id_master" name="id_master" placeholder="Pilih Sekolah"
                                    aria-label="Pilih Sekolah">
                                    <option disabled hidden value="">Pilih Sekolah</option>
                                    <?php
                                        $sql="select id_master,school_name from op_masterdata order by school_name ASC";
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
                                <label for="id_master">Nama Sekolah / Institusi</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="fullname" name="fullname" <?php if($id_customerdata>0){ echo "value='".$rowa['fullname']."'";} ?>
                                    placeholder="Nama PIC">
                                <label for="floatingInput">Nama PIC</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="position" name="position" <?php if($id_customerdata>0){ echo "value='".$rowa['position']."'";} ?>
                                    placeholder="Posisi PIC">
                                <label for="floatingInput">Posisi</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" <?php if($id_customerdata>0){ echo "value='".$rowa['email']."'";} ?>
                                    placeholder="Email PIC">
                                <label for="floatingInput">Email PIC</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" id="phone" name="phone" <?php if($id_customerdata>0){ echo "value='".$rowa['phone']."'";} ?>
                                    placeholder="Telepon PIC">
                                <label for="floatingInput">Telepon PIC</label>
                            </div>
                            <?php
                                if($id_customerdata>0)
                                {
                                    echo "<input type='hidden' name='action' value='edit'>";
                                    echo "<input type='hidden' name='id_customerdata' value='".$id_customerdata."'>";
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