<?php

session_start();
include 'db_con.php';

$id_benefit_llist = $_POST['id_benefit_list'];  
$role = $_SESSION['role'];                                                            
$sql = "SELECT 
            dbl.*, bu.qty1 as usage1, bu.qty2 as usage2, bu.qty3 as usage3, bu.description as descr, bu.created_at as created, dtb.redeemable, bu.used_at, bu.redeem_code
        FROM benefit_usages AS bu
        LEFT JOIN draft_benefit_list AS dbl ON dbl.id_benefit_list = bu.id_benefit_list
        LEFT JOIN draft_template_benefit dtb on dtb.id_template_benefit = dbl.id_template 
        WHERE bu.id_benefit_list = $id_benefit_llist
        ORDER BY bu.used_at";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usages = mysqli_fetch_all($result, MYSQLI_ASSOC); 
?>
    <div class="p-2">
        <div class="table-responsive">
            <table class="table" id="table_id2">
                <thead>
                    <tr>
                        <th scope="col">Used At</th>
                        <th scope="col">Description</th>
                        <?php
                            if(count($usages) > 0 && $usages[0]['redeemable'] == 1) { ?>
                            <th>Code</th>
                                
                        <?php } ?>
                        <th scope="col">Year 1</th>
                        <th scope="col">Remaining Year 1</th>
                        <th scope="col">Year 2</th>
                        <th scope="col">Remaining Year 2</th>
                        <th scope="col">Year 3</th>
                        <th scope="col">Remaining Year 3</th>
                        <th scope="col">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $acc_qty1 = 0;
                        $acc_qty2 = 0;
                        $acc_qty3 = 0;
                        foreach($usages as $usage) {
                            $acc_qty1 += $usage['usage1'];
                            $acc_qty2 += $usage['usage2'];
                            $acc_qty3 += $usage['usage3'];
                    ?>
                            <tr>
                                <td><?= $usage['used_at'] ?></td>
                                <td><?= $usage['descr'] ?></td>
                                <?php
                                    if(count($usages) > 0 && $usages[0]['redeemable'] == 1) { ?>
                                        <td><?= $usage['redeem_code'] ?></td>
                                <?php } ?>
                                <td class="text-center"><?= $usage['usage1'] ?></td>
                                <td class="text-center"><?= $usage['qty'] - $acc_qty1 ?></td>
                                <td class="text-center"><?= $usage['usage2'] ?></td>
                                <td class="text-center"><?= $usage['qty2'] - $acc_qty2 ?></td>
                                <td class="text-center"><?= $usage['usage3'] ?></td>
                                <td class="text-center"><?= $usage['qty3'] - $acc_qty3 ?></td>
                                <td><?= $usage['created'] ?></td>
                            </tr>
                        <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
 
<?php $conn->close();}else { ?>
    <div class="p-2">
        <div class="table-responsive">
            <table class="table" id="table_id2">
                <thead>
                    <tr>
                        <th scope="col">Used At</th>
                        <th scope="col">Description</th>
                        <th scope="col">Year 1</th>
                        <th scope="col">Remaining Year 1</th>
                        <th scope="col">Year 2</th>
                        <th scope="col">Remaining Year 2</th>
                        <th scope="col">Year 3</th>
                        <th scope="col">Remaining Year 3</th>
                        <th scope="col">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
<script>
    $(document).ready(function() {
        $('#table_id2').DataTable({
            dom: 'Bfrtip',
            pageLength: 20,
            order: [
                [0, 'desc'] 
            ],
            buttons: [
               
            ],
            ordering: false,
            searching: false
        });
    });
</script>

    
    
    