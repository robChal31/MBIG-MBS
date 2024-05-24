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
                                            <th scope="col">English</th>
                                            <th scope="col">Maths</th>
                                            <th scope="col">Science</th>
                                            <th scope="col">Indo</th>
                                            <th scope="col">Mandarin</th>
                                            <th scope="col">Judul Training</th>
                                            <th scope="col">Date Training</th>
                                            <th>EDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,b.school_name, b.jenDok,c.ec_name,b.date,b.year,b.title,a.training_name,a.training_date, sum(case when a.subjek_peserta = 'English' then 1 else 0 end) as peserta_english, sum(case when a.subjek_peserta = 'Maths' then 1 else 0 end) as peserta_maths, sum(case when a.subjek_peserta = 'Science' then 1 else 0 end) as peserta_science, sum(case when a.subjek_peserta = 'Indonesia' then 1 else 0 end) as peserta_indonesia, sum(case when a.subjek_peserta = 'Mandarin' then 1 else 0 end) as peserta_mandarin FROM `op_kolektif` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where isDeleted=0 group by a.id_master, a.training_name";
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
                                                    echo "<td>".$row['peserta_english']."</td>";
                                                    echo "<td>".$row['peserta_maths']."</td>";
                                                    echo "<td>".$row['peserta_science']."</td>";
                                                    echo "<td>".$row['peserta_indonesia']."</td>";
                                                    echo "<td>".$row['peserta_mandarin']."</td>";
                                                    echo "<td>".$row['training_name']."</td>";
                                                    echo "<td>".$row['training_date']."</td>";
                                                    echo "<td><a href='benefit-kolektif-detail.php?a=".$row['id_master']."&b=".$row['training_date']."'>Details</a></td>";
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