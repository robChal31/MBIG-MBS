<?php $type = $_GET['type']; ?>

<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-12">
                        <div class="bg-white rounded h-100 p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-4"><?=ucwords($type)?> Benefit</h6>
                                <a href="new-benefit-input.php?type=<?=$type;?>">
                                    <button type="button" class="btn btn-primary m-2"><i class="fa fa-home me-2"></i>Add <?=ucwords($type)?> Usage</button>    
                                </a>
                            </div>

                            <div class="">
                                <form action="">
                                    <select name="" id=""></select>
                                </form>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">Nama Sekolah</th>
                                            <th scope="col">Dokumen</th>
                                            <th scope="col">EC</th>
                                            <th scope="col">Tanggal PK</th>
                                            <th scope="col">Tanggal Expired</th>
                                            <th scope="col">Tanggal Benefit</th>
                                            <th scope="col">Subbenefit</th>
                                            <th scope="col">Benefit</th>
                                            <th scope="col">Desc</th>
                                            <th scope="col">Keterangan</th>
                                            <th scope="col">Pelaksanaan</th>
                                            <th scope="col">Qty Tahun Ini</th>
                                            <th scope="col">Used Qty</th>
                                            <th scope="col">Sisa Qty</th>
                                            <th scope="col">EDIT</th>
                                            
                                            <th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.id_master,a.id_benefit,a.pelaksanaan,b.school_name, 
                                                        b.jenDok,c.ec_name,b.date as tanggalpk,expiredDate,b.year,keterangan,
                                                        b.title,a.benefit_name, a.subbenefit,a.tanggal, a.description,a.qty 
                                                    FROM `op_simple_benefit` a 
                                                    left join op_masterdata b on a.id_master = b.id_master 
                                                    left join dash_ec c on c.id_ec = b.id_ec where a.type = '".$type."' and isDeleted=0";
        
                                            $result = mysqli_query($conn, $sql);
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                    echo "<td>".$row['school_name']."</td>";
                                                    echo "<td>".$row['jenDok']."</td>";
                                                    echo "<td>".$row['ec_name']."</td>";
                                                    //detect qty use
                                                    echo "<td>".$row['tanggalpk']."</td>";
                                                    echo "<td>".$row['expiredDate']."</td>";
                                                    echo "<td>".$row['tanggal']."<br>
                                                        <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal4' data-bs-rowid='".$row['id_benefit']."' data-bs-tanggal='".$row['tanggal']."'>Update</button>
                                                    </td>";
                                                    echo "<td>".$row['subbenefit']."</td>";
                                                    echo "<td>".$row['benefit_name']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    echo "<td>".$row['keterangan']."<br>  
                                                        <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal3' data-bs-rowid='".$row['id_benefit']."' data-bs-keterangan='".$row['keterangan']."'>Update</button>
                                                    </td>";
                                                    echo "<td>".$row['pelaksanaan']."</td>";
                                                    echo "<td>".$row['qty']."</td>"; // ubah jadi deteksi tahun
                                                    echo "<td>Used Qty</td>";
                                                    echo "<td>Sisa Qty</td>";
                                                    //test commit
                                                    echo "<td><a href='new-benefit-delete.php?a=".$row['id_benefit']."&type=".$type."'>Delete</a></td>";
                                                    if($type==='training'){
                                                        if($row['benefit_name']!=='Familirisasi' && $row['subbenefit']!=="In house")
                                                        {
                                                            echo "<td>";
                                                                echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal6' data-bs-rowid='".$row['id_benefit']."' data-bs-qty='".$row['qty']."'>Generate Voucher</button>";
                                                            echo"</td>";
                                                        }
                                                        else
                                                        {
                                                            echo "<td>-</td>";
                                                        }
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
            </div>
            <!-- Sale & Revenue End -->

            <!-- Footer Start -->


       <?php include 'footer.php';?>