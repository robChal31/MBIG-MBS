<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-1 px-1">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">School Hitory</h6>

                            

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID Master</th>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">Jenis Docs</th>
                                            <th scope="col">File Upload</th>
                                            <th scope="col">EC</th>
                                            <th scope="col">SA</th>
                                            <th scope="col">Expired Date</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,a.school_name,a.jenDok,a.expiredDate,a.fileUrl,b.ec_name,c.sa_name,a.date,a.year,a.nosor,a.statuspi,a.title,a.status FROM op_masterdata a left join dash_ec b on a.id_ec=b.id_ec left join dash_sa c on a.id_sa=c.id_sa order by id_master DESC";
                                            $result = mysqli_query($conn, $sql);
                                            setlocale(LC_MONETARY,"id_ID");
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>".$row['id_master']."</td>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td><a href='./".$row['fileUrl']."'>File</a></td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['sa_name']."</td>";
                                                    if(!is_null($row['expiredDate']))
                                                    {
                                                        echo "<td>".$row['expiredDate']."</td>";
                                                    }
                                                    else
                                                    {
                                                        echo "<td>No Data</td>";
                                                    }
                                                    
                                                    if(date("Y-m-d")<=$row['expiredDate'])
                                                    {
                                                        echo "<td>Active</td>";
                                                    }
                                                    elseif(date("Y-m-d")<=$row['expiredDate'] && !is_null($row['expiredDate']))
                                                    {
                                                        echo "<td>Expired</td>";
                                                    }
                                                    else
                                                    {
                                                        echo "<td>-</td>";
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
       