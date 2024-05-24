<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Masters School</h6>
                            <a href="benefit-data-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add Benefit Data</button>    
                            </a>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">EC</th>
                                            <th scope="col">Benefit</th>
                                            <th scope="col">Qty</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col">Approval</th>
                                            <?php if($_SESSION['role']=="admin")
                                            {
                                                echo "<th scope='col'>Function</th>";
                                                echo "<th scope='col'>Edit</th>";
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $idmas=$_GET['idm'];
                                            $sql = "SELECT c.school_name,a.qty,b.benefit_name,ec_name,description,approval,id_benefit from op_benefit a left join op_benefittype b on a.id_benefittype=b.id_benefittype left join op_masterdata c on a.id_master=c.id_master left join dash_ec d on d.id_ec=c.id_ec where a.id_master='$idmas'";
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    echo "<td>".$row['benefit_name']."</td>";
                                                    echo "<td>".$row['qty']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    if($_SESSION['role']=="admin"){
                                                        if($row['approval'])
                                                        {
                                                            echo "<td>Approved</td>";
                                                            echo "<td>&#10004;</td>";
                                                        }
                                                        else
                                                        {
                                                            echo "<td>Belum</td>";
                                                            echo "<td><a href='approve-benefit.php?id=".$row['id_benefit']."'>APPROVE</a></td>";
                                                        }
                                                        echo "<td><a href='benefit-data-input.php?id=".$row['id_benefit']."'>EDIT</a></td>";
                                                    }
                                                    
                                                    
                                                    
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