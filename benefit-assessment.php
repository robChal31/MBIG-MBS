<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Assessment Benefit</h6>
                            <a href="benefit-usage-assessment-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Assessment Usage</button>    
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
                                            <th scope="col">Sub Benefit</th>
                                            <th scope="col">Catatan</th>
                                            <th scope="col">Date Ujian</th>
                                            <th scope="col">Jumlah</th>
                                            <th scope="col">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT b.school_name,a.progress_update,a.subbenefit,a.description,b.jenDok,c.ec_name,b.date,b.year,b.title,a.test_date, count(nama_peserta) as jum FROM `op_assessment` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where isDeleted=0 group by a.id_master, a.test_date";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['date']."</td>";
                                                    echo "<td>".$row['year']."</td>";
                                                    echo "<td>".$row['subbenefit']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    echo "<td>".$row['test_date']."</td>";
                                                    echo "<td>".$row['jum']."</td>";
                                                    echo "<td>".$progress[$row['progress_update']]."<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal2' data-bs-benefittype='6' data-bs-rowid='".$row['id_assessment']."' data-bs-progressupdate='".$row['progress_update']."'>Update</button></td>";
                                                    
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