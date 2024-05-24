<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-1 px-1">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">CBLS dan Program Prestasi Value</h6>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">Nama PIC</th>
                                            <th scope="col">Position</th>
                                            <th scope="col">Nama EC</th>
                                            <th scope="col">Jenis Dokumen</th>
                                            <th scope="col">View</th>
                                      
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,a.school_name,b.fullname,c.ec_name,a.jenDok,b.position FROM `op_masterdata` a left join op_customerdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=a.id_ec where a.jenDok in ('CBLS','CBLS 1','CBLS 3','Prestasi') order by  a.school_name ASC";
                                            $result = mysqli_query($conn, $sql);
                                            setlocale(LC_MONETARY,"id_ID");
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>".$row['id_master']."</td>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['fullname']."</td>";
                                                    echo "<td>".$row['position']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";

                                                    if($_SESSION['role']=='admin')
                                                    {
                                                        echo "<td><a href='benefit-value-detail.php?id=".$row['id_master']."&tipe=".$row['jenDok']."'>Detail</a></td>";
                                                    }
                                                    echo "</tr>";
                                              }
                                            } else {
                                              echo "0 results";
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
       