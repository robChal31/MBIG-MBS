<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Materials Benefit</h6>
                            <a href="benefit-usage-input.php?type=materials">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Materials Usage</button>    
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
                                            <th scope="col">Tanggal Benefit</th>
                                            <th scope="col">Subbenefit</th>
                                            <th scope="col">Benefit</th>
                                            <th scope="col">Desc</th>
                                            <th scope="col">Qty</th>
                                            <th>EDIT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,a.id_materials,b.school_name, b.jenDok,c.ec_name,b.date,b.year,b.title,a.benefit_name, a.subbenefit,a.tanggal, a.description,a.qty FROM `op_materials` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where isDeleted=0";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['date']."</td>";
                                                    echo "<td>".$row['year']."</td>";
                                                    echo "<td>".$row['tanggal']."</td>";
                                                    echo "<td>".$row['subbenefit']."</td>";
                                                    echo "<td>".$row['benefit_name']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    echo "<td>".$row['qty']."</td>";
                                                    echo "<td><a href='benefit-delete.php?a=".$row['id_materials']."&type=materials'>Delete</a></td>";
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