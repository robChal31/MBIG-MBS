<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">MBMTA Benefit</h6>
                            <a href="benefit-usage-mbmta-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add MBMTA Usage</button>    
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
                                            <th scope="col">MBMTA1</th>
                                            <th scope="col">MBMTA2</th>
                                            <th scope="col">MBMTA3</th>
                                            <th scope="col">Jumlah Peserta</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">EDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT b.school_name,a.id_mbmta, b.jenDok,c.ec_name,b.date,b.year,b.title,a.mbmta1,a.mbmta2,a.mbmta3, sum(jumlah_peserta) as jum FROM `op_mbmta` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where isDeleted=0 group by a.id_master";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['date']."</td>";
                                                    echo "<td>".$row['year']."</td>";
                                                    echo "<td>".$row['mbmta1']."</td>";
                                                    echo "<td>".$row['mbmta2']."</td>";
                                                    echo "<td>".$row['mbmta3']."</td>";
                                                    echo "<td>".$row['jum']."</td>";
                                                    echo "<td>".$row['status']."</td>";
                                                    echo "<td><a href='benefit-usage-mbmta-input.php?id=".$row['id_mbmta']."'>EDIT</a> | <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-benefittype='3' data-bs-rowid='".$row['id_mbmta']."'>Delete</button></td></td>";
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