<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">TDMTA Benefit</h6>
                            <a href="benefit-usage-tdmta-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add TDMTA Usage</button>    
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
                                            <th scope="col">Date Training</th>
                                            <th scope="col">Desc</th>
                                            <th scope="col">Nama Peserta</th>
                                            <th>Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $a=$_GET['a'];
                                        $b=$_GET['b'];
                                            $sql = "SELECT a.id_tdmta,b.school_name, b.jenDok,c.ec_name,b.date,b.year,b.title,a.training_date, nama_peserta,description FROM `op_tdmta` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where a.training_date='$b' and a.id_master='$a'";
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
                                                    echo "<td>".$row['training_date']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    echo "<td>".$row['nama_peserta']."</td>";
                                                    echo "<td><a href='benefit-usage-tdmta-edit.php?id=".$row['id_tdmta']."'>EDIT</a> | <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-benefittype='4' data-bs-rowid='".$row['id_tdmta']."'>Delete</button></td>";
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