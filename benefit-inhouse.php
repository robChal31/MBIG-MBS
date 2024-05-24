<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Inhouse</h6>
                            <a href="benefit-usage-inhouse-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Inhouse Usage</button>    
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
                                            <th scope="col">Judul Training</th>
                                            <th>On/Offline</th>
                                            <th scope="col">Date Training</th>
                                            <th scope="col">Jumlah</th>
                                            <th scope="col">Progress</th>
                                            <th>CT Note</th>
                                            <th>Action</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_inhouse,a.onoff,a.progress_update,a.ct_note,b.school_name, b.jenDok,c.ec_name,b.date,b.year,b.title,a.training_name,a.training_date,a.trainer_name, jumlah_peserta FROM `op_inhouse` a left join op_masterdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=b.id_ec where isDeleted=0";
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
                                                    echo "<td>".$row['training_name']."</td>";
                                                    if($row['onoff']==0)
                                                    {
                                                        echo "<td>Offline</td>";
                                                    }
                                                    else{
                                                        echo "<td>Online</td>";
                                                    }
                                                   
                                                    echo "<td>".$row['training_date']."</td>";
                                                    echo "<td>".$row['jumlah_peserta']."</td>";
                                                    $progress = ['Belum','On Progress','Sudah'];
                                                    echo "<td>".$progress[$row['progress_update']]."<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal2' data-bs-benefittype='2' data-bs-rowid='".$row['id_inhouse']."' data-bs-progressupdate='".$row['progress_update']."'>Update</button></td>";
                                                    echo "<td>".$row['ct_note']."</td>";
                                                    echo "<td><a href='benefit-usage-inhouse-input.php?id=".$row['id_inhouse']."'>EDIT</a> | <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-benefittype='2' data-bs-rowid='".$row['id_inhouse']."'>Delete</button></td>";
                                                    
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