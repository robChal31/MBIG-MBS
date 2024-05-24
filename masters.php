<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-1 px-1">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Masters School</h6>
                            <a href="master-data-input.php">
                                <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add School Data</button>    
                            </a>
                            

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
                                            <th scope="col">Date</th>
                                            <th scope="col">Year</th>
                                            <th scope="col">Title Adopt</th>
                                            <th scope="col">Omset</th>
                                            <th scope="col">Benefit</th>
                                            <th scope="col">Status</th>
                                            <?php if($_SESSION['role']=='admin')
                                            {
                                                echo '<th scope="col">Function</th>';
                                                echo '<th scope="col">Edit</th>';
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,a.school_name,a.jenDok,a.fileUrl,b.ec_name,c.sa_name,a.date,a.year,a.nosor,a.statuspi,a.title,a.status,a.autoTemplate FROM op_masterdata a left join dash_ec b on a.id_ec=b.id_ec left join dash_sa c on a.id_sa=c.id_sa order by id_master DESC";
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
                                                    echo "<td>".$row['date']."</td>";
                                                    echo "<td>".$row['year']."</td>";
                                                    echo "<td>".$row['title'] ."<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal5' data-bs-rowid='".$row['id_master']."'>Update</button>"."</td>";
                                                    //omset
                                                    echo "<td>";
                                                    
                                                        echo "<table>";
                                                            $sql = "SELECT * from op_omset where id_master='".$row['id_master']."'";
                                                            $result2 = mysqli_query($conn, $sql);
                                                            if (mysqli_num_rows($result2) > 0) {
                                                              while($row2 = mysqli_fetch_assoc($result2)) {
                                                                  echo "<tr>";
                                                                    echo "<td>"; 
                                                                        echo $row2['year'];
                                                                    echo "</td>";
                                                                    echo "<td>";
                                                                    echo "<a href='omset-data-input.php?id=".$row2['id_omset']."'>";
                                                                    echo number_format( $row2['omset']);
                                                                    echo "</a>";
                                                                    echo "</td>";
                                                                  echo "</tr>";
                                                              }
                                                            }
                                                        echo "</table>";
                                                        
                                                    echo "</td>";
                                                    //benefit
                                                    echo "<td style='white-space:nowrap;overflow:scroll'>";
                                                    echo "<div class='table-wrap'>";
                                                        echo "<table class='table table-striped'><tbody>";
                                                            $sql = "SELECT * from op_simple_benefit where id_master='".$row['id_master']."'";
                                                            $result2 = mysqli_query($conn, $sql);
                                                            if (mysqli_num_rows($result2) > 0) {
                                                              while($row2 = mysqli_fetch_assoc($result2)) {     
                                                                  echo "<tr onClick=\"alert('".$row2['description']."');\" title='Deskripsi' data-content='asd'>";
                                                                    echo "<td>";
                                                                        echo $row2['subbenefit']." - ".$row2['benefit_name']." ";
                                                                    echo "</td>";
                                                                    echo "<td>";
                                                                        echo $row2['qty']+$row2['qty2']+$row2['qty3']."\n";
                                                                    echo "</td>";
                                                                  echo "</tr>";
                                                              }
                                                            }
                                                        echo "</tbody></table>";
                                                        echo "</div>";
                                                    echo "</td>";
                                                    if($row['status'])
                                                    {
                                                        echo "<td>Approved</td>";
                                                        if($_SESSION['role']=='admin')
                                                        {
                                                            echo "<td>&#10004;</td>";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        echo "<td>Belum</td>";
                                                        if($_SESSION['role']=='admin')
                                                        {
                                                            echo "<td><a href='approve-school.php?id=".$row['id_master']."'>APPROVE</a></td>";
                                                        }
                                                    }
                                                    if($_SESSION['role']=='admin')
                                                    {
                                                        echo "<td>";
                                                        echo "<a href='master-data-edit.php?id=".$row['id_master']."'>EDIT</a><br>";
                                                        if ($row['autoTemplate']=='0')
                                                        {
                                                            echo "<a href='new-benefit-template-input.php?act=edit&idm=".$row['id_master']."'>Template Benefit</a>";
                                                        }
                                                        echo "</td>";
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
       