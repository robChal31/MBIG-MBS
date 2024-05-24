<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-1 px-1">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Customer Data Master</h6>
                            <a href="customer-data-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Customer Data</button>    
                            </a>
                            

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">ID</th>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">Nama PIC</th>
                                            <th scope="col">Position</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Phone</th>
                                            <?php if($_SESSION['role']=='admin')
                                            {
                                                echo '<th scope="col">Edit</th>';
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_customerdata,a.fullname,a.position,a.email,a.phone,b.school_name FROM `op_customerdata` a left join op_masterdata b on a.id_master=b.id_master order by school_name,fullname asc";
                                            $result = mysqli_query($conn, $sql);
                                            setlocale(LC_MONETARY,"id_ID");
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>".$row['id_customerdata']."</td>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['fullname']."</td>";
                                                    echo "<td>".$row['position']."</td>";
                                                    echo "<td>".$row['email']."</td>";
                                                    echo "<td>".$row['phone']."</td>";

                                                    if($_SESSION['role']=='admin')
                                                    {
                                                        echo "<td><a href='customer-data-edit.php?id=".$row['id_customerdata']."'>EDIT</a></td>";
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
       