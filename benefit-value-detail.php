<?php include 'header.php'; ?>

<?php 
/*
SELECT a.id_master,a.school_name,c.ec_name,a.jenDok,d.nilaiCBLS,d.nilaiPrestasi,d.manualValue FROM `op_masterdata` a left join op_customerdata b on a.id_master=b.id_master left join dash_ec c on c.id_ec=a.id_ec left join (SELECT a.*,sum(b.valuePrestasi*a.qty) as nilaiPrestasi,sum(b.valueCBLS*a.qty) as nilaiCBLS,sum(manualValue) FROM `op_simple_benefit` a left join op_template_benefit b on a.benefit_name=b.benefit_name  and isDeleted=0 group by id_master) d on a.id_master=d.id_master where a.jenDok in ('CBLS','CBLS 1','CBLS 3','Prestasi') order by  a.school_name ASC;
*/
    $id = $_GET['id']; $tipe=$_GET['tipe'];
    $sql = "SELECT school_name FROM `op_masterdata` where id_master=$id";
    $result = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_assoc($result))
    {
        $school_name = $row['school_name'];
    }
?>
        <!-- Content Start -->
        <div class="content">
            <?php include 'navbar.php'; ?>
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-1 px-1">
                <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-2">Detail Value Benefit</h6>
                            <h4 class="mb-4"><?=$school_name?></h4>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="table_id">
                                    <thead>
                                        <tr>
                                            <th scope="col">Kategori</th>
                                            <th scope="col">Sub Kategori</th>
                                            <th scope="col">Nama Benefit</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col">Jumlah</th>
                                            <th scope="col">Pelaksanaan</th>
                                            <th scope="col">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $sql = "SELECT a.*,b.valuePrestasi,b.valueCBLS,(b.valuePrestasi*a.qty) as nilaiPrestasi,(b.valueCBLS*a.qty) as nilaiCBLS,manualValue FROM `op_simple_benefit` a left join op_template_benefit b on a.benefit_name=b.benefit_name where id_master=$id and isDeleted=0";
                                            $result = mysqli_query($conn, $sql);
                                            setlocale(LC_MONETARY,"id_ID");
                                            $total = 0;
                                            if (mysqli_num_rows($result) > 0) {
                                              while($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>".$row['type']."</td>";
                                                    echo "<td>".$row['subbenefit']."</td>";
                                                    echo "<td>".$row['benefit_name']."</td>";
                                                    echo "<td>".$row['description']."</td>";
                                                    echo "<td>".$row['qty']."</td>";
                                                    echo "<td>".$row['pelaksanaan']."</td>";
                                                    if($row['manualValue']==0)
                                                    {
                                                        if($row['nilaiPrestasi']>=$row['nilaiCBLS'])
                                                        {
                                                            echo "<td>".number_format($row['nilaiPrestasi'])."</td>";
                                                            $total = $total + $row['nilaiPrestasi'];
                                                        }
                                                        else
                                                        {
                                                            echo "<td>".number_format($row['nilaiCBLS'])."</td>";
                                                            $total = $total + $row['nilaiCBLS'];
                                                        }
                                                    }
                                                    else
                                                    {
                                                        echo "<td>".number_format($row['manualValue'])."</td>";
                                                        $total = $total + $row['manualValue'];
                                                    }
                                                    
                                                    
                                                    echo "</tr>";
                                              }
                                            } else {
                                              echo "0 results";
                                            }
                                            echo "<tr><td colspan='6'>Grand Total</td><td><strong>".number_format($total)."</strong></td></tr>";
                                        ?>
                                    </tbody>
                                </table>
                                <h5>Total : <?=number_format($total)?></h5>
                            </div>
                        </div>
                    </div>
            </div>
            <!-- Sale & Revenue End -->
            
            <!-- Footer Start -->


       <?php include 'footer.php';?>
       