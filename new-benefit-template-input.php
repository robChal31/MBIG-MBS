<?php include 'header.php'; ?>
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
            <?php $type=$_GET['type'];?>
    <style>
        table.dataTable tbody td {
            padding : 2px !important;
        }
        </style>
            <!-- Form Start -->
            <div class="container-fluid pt-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-12">
                        <div class="bg-light rounded h-100">
                            <form method="POST" action="new-benefit-template-input-action.php" enctype="multipart/form-data">
                                <input type="hidden" name="idm" value="<?php echo $_GET['idm']; ?>">
                                <?php if($_GET['act']==='edit')
                                {
                                   echo '<input type="hidden" name="act" value="edit">';
                                }?>
                             <table class="table table-striped table-bordered dataTable no-footer">
                                <thead>
                                    <td>Ambil</td>
                                    <td>Benefit</td>
                                    <td>Sub Benefit</td>
                                    <td>Nama Benefit</td>
                                    <td>Deskripsi</td>
                                    <td>Pelaksanaan</td>
                                    <td>Tanggal</td>
                                    <td>Keterangan</td>
                                    <td>Qty Th 1</td>
                                    <td>Qty Th 2</td>
                                    <td>Qty Th 3</td>
                                    <td>Nilai Rupiah Manual</td>
                                </thead>
                                <tbody>
                                    <?php

                                        $action = $_GET['act'];
                          
                                        if($action!='edit')
                                        {
                                            $sql = 'SELECT * FROM `op_template_benefit`  order by benefit,subbenefit,benefit_name asc';
                                            if($result = mysqli_query($conn,$sql))
                                            {
                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    echo "<tr>";
                                                        echo '<td> <input type="checkbox" name="take[]" value="'.$row['id_template_benefit'].'">';
                                                        echo "<td>".$row['benefit']."</td><input type='hidden' name='benefit[".$row['id_template_benefit']."]' value='".$row['benefit']."'>";
                                                        echo "<td>".$row['subbenefit']."</td><input type='hidden' name='subbenefit[".$row['id_template_benefit']."]' value='".$row['subbenefit']."'>";
                                                        echo "<td>".$row['benefit_name']."</td><input type='hidden' name='benefit_name[".$row['id_template_benefit']."]' value='".$row['benefit_name']."'>";
                                                        echo '<td><textarea id="description" name="description['.$row['id_template_benefit'].']" cols="16">'.$row['description'].'</textarea></td>';
                                                        echo '<td><input type="text" class="form-control" id="pelaksanaan" name="pelaksanaan['.$row['id_template_benefit'].']" value="'.$row['pelaksanaan'].'"></td>';
                                                        echo '<td><input type="date" class="form-control" id="tanggal" name="tanggal['.$row['id_template_benefit'].']" placeholder="Tanggal"></td>';
                                                        echo '<td><input type="text" class="form-control" id="keterangan" name="keterangan['.$row['id_template_benefit'].']" placeholder="Keterangan"></td>';
                                                        echo '<td><input type="number" class="form-control" id="member" name="member['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 1" value="0"></td>';
                                                        echo '<td><input type="number" class="form-control" id="member" name="member2['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 2" value="0"></td>';
                                                        echo '<td><input type="number" class="form-control" id="member" name="member3['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 3" value="0"></td>';
                                                        echo '<td><input type="number" class="form-control" id="manualValue" name="manualValue['.$row['id_template_benefit'].']" placeholder="0" value="0"></td>';
                                                    echo "</tr>";
                                                }
                                            }
                                        }

                                        else
                                        { 
                                            $sql = 'SELECT * FROM `op_template_benefit`  order by benefit,subbenefit,benefit_name asc';
                                   
                                            if($result = mysqli_query($conn,$sql))
                                            {
                                                while($row = mysqli_fetch_assoc($result))
                                                {
                                                    $sql = "SELECT * from op_simple_benefit where id_master='".$_GET['idm']."' and subbenefit='".$row['subbenefit']."' and benefit_name='".$row['benefit_name']."' and isDeleted=0 and status=1; ";
                                           
                                                    $result2=mysqli_query($conn,$sql);
                                                    while($row2=mysqli_fetch_assoc($result2))
                                                    {
                                                        $id_template_benefit=$row2['id_template_benefit'];
                                                        $benefit = $row2['benefit'];
                                                        $subbenefit = $row2['subbenefit'];
                                                        $benefit_name = $row2['benefit_name'];
                                                        $description = $row2['description'];
                                                        $pelaksanaan = $row2['pelaksanaan'];
                                                        $tanggal = $row2['tanggal'];
                                                        $keterangan = $row2['keterangan'];
                                                        $qty = $row2['qty'];
                                                        $qty2 = $row2['qty2'];
                                                        $qty3 = $row2['qty3'];
                                                        $manualValue = $row2['manualValue'];
                                                        echo "<tr>";
                                                            echo '<td> <input type="checkbox" name="take[]" value="'.$row2['id_template_benefit'].'" checked>';
                                                            echo "<td>".$row['benefit']."</td><input type='hidden' name='benefit[".$row['id_template_benefit']."]' value='".$row['benefit']."'>";
                                                            echo "<td>".$row['subbenefit']."</td><input type='hidden' name='subbenefit[".$row['id_template_benefit']."]' value='".$row['subbenefit']."'>";
                                                            echo "<td>".$row['benefit_name']."</td><input type='hidden' name='benefit_name[".$row['id_template_benefit']."]' value='".$row['benefit_name']."'>";
                                                            echo '<td><textarea id="description" name="description['.$row['id_template_benefit'].']" cols="16">'.$row2['description'].'</textarea></td>';
                                                            echo '<td><input type="text" class="form-control" id="pelaksanaan" name="pelaksanaan['.$row['id_template_benefit'].']" value="'.$row2['pelaksanaan'].'"></td>';
                                                            echo '<td><input type="date" class="form-control" id="tanggal" name="tanggal['.$row['id_template_benefit'].']" placeholder="Tanggal" value="'.$row2['tanggal'].'"></td>';
                                                            echo '<td><input type="text" class="form-control" id="keterangan" name="keterangan['.$row['id_template_benefit'].']" placeholder="Keterangan" value="'.$row2['keterangan'].'"></td>';
                                                            echo '<td><input type="number" class="form-control" id="member" name="member['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 1" value="'.$row2['qty'].'"></td>';
                                                            echo '<td><input type="number" class="form-control" id="member" name="member2['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 2" value="'.$row2['qty2'].'"></td>';
                                                            echo '<td><input type="number" class="form-control" id="member" name="member3['.$row['id_template_benefit'].']" placeholder="Quantity Tahun 3" value="'.$row2['qty2'].'"></td>';
                                                            echo '<td><input type="number" class="form-control" id="manualValue" name="manualValue['.$row['id_template_benefit'].']" placeholder="0" value="'.$row2['manualValue'].'"></td>';
                                                        echo "</tr>"; 
                                                    }
                                                }
                                            }
                                        }
                                        
                                    ?>
                                </tbody>
                             </table>   
                            
                    

                           
                            
                            <button type="submit" class="btn btn-lg btn-primary m-2" id="submt">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Form End -->


<?php include 'footer.php'; ?>