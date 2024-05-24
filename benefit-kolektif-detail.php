<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Kolektif Benefit</h6>
                            <a href="benefit-usage-kolektif-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Kolektif Usage</button>    
                            </a>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">Dokumen</th>
                                            <th scope="col">EC</th>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Tahun</th>
                                            <th scope="col">Judul</th>
                                            <th scope="col">Subjek</th>
                                            <th scope="col">Nama Peserta</th>
                                            <th scope="col">Judul Training</th>
                                            <th scope="col">Date Training</th>
                                            <th>EDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $id_master=$_GET['a'];
                                        $training_date=$_GET['b'];
                                            $sql = "SELECT a.id_kolektif,b.school_name,a.nama_peserta, b.jenDok,c.ec_name,b.date,b.year,b.title,a.training_name,a.training_date,subjek_peserta  FROM `op_kolektif` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where a.id_master='$id_master' and a.training_date='$training_date'";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['date']."</td>";
                                                    echo "<td>".$row['year']."</td>";
                                                    echo "<td>".$row['title']."</td>";
                                                    echo "<td>".$row['subjek_peserta']."</td>";
                                                    echo "<td>".$row['nama_peserta']."</td>";
                                                    
                                                    echo "<td>".$row['training_name']."</td>";
                                                    echo "<td>".$row['training_date']."</td>";
                                                    echo "<td><a href='benefit-usage-kolektif-edit.php?id=".$row['id_kolektif']."'>EDIT</a> | <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-benefittype='1' data-bs-rowid='".$row['id_kolektif']."'>Delete</button></td></td>";
                                                echo "</tr>";
                                              }
                                            }
                                            
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            </div>
            <!-- Sale & Revenue End -->

            <!-- Footer Start -->


       <?php include 'footer.php';?>