<?php

include 'db_con.php';

$id_draft = $_POST['id_draft'];                                                                      
$sql = "SELECT 
            b.*, c.*, pk.*,
            IFNULL(sc.name, b.school_name) as school_name2, dbl.total_qty, pk.id as id_pk, dash_sa.sa_name
        FROM draft_benefit as b
        LEFT JOIN schools as sc on sc.id = b.school_name
        LEFT JOIN user as c on c.id_user = b.id_user
        LEFT JOIN pk on pk.benefit_id = b.id_draft
        LEFT JOIN dash_sa on dash_sa.id_sa = pk.sa_id
        LEFT JOIN (
            SELECT 
                id_draft, 
                (SUM(qty) + SUM(qty2) + SUM(qty3)) as total_qty
            FROM draft_benefit_list
            GROUP BY id_draft
        ) as dbl on dbl.id_draft = b.id_draft
        where b.id_draft = $id_draft";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ec_name = $row['generalname'];
        $school = $row['school_name2'];
        $program = $row['program'];
        $segment = $row['segment'];
        $total_qty = $row['total_qty'];
        $id_pk = $row['id_pk'];
        $no_pk = $row['no_pk'];
        $start_date = $row['start_at'];
        $end_date = $row['expired_at'];
        $id_sa = $row['sa_id'];
        $file_pk = $row['file_pk'];
        $file_benefit = $row['file_benefit'];
        $sa_name = $row['sa_name'];
    }

    $sq_query = "SELECT * FROM dash_sa WHERE is_active = 1";
                
    $sa_exec_query = $conn->query($sq_query);
?>
    <div class="p-2">
        <h6>Detail Partnership Agreement</h6>
        <table class="table table-striped">
            <tr>
                <td style="width: 20%"><strong>EC</strong></td>
                <td style="width: 1%">:</td>
                <td><?= $ec_name ?></td>
            </tr>
            <tr>
                <td><strong>Sekolah</strong></td>
                <td>:</td>
                <td><?= $school ?></td>
            </tr>
            <tr>
                <td><strong>Segment</strong></td>
                <td>:</td>
                <td><?= strtoupper($segment) ?></td>
            </tr>
            <tr>
                <td><strong>Program</strong></td>
                <td>:</td>
                <td><?= strtoupper($program) ?></td>
            </tr>
            <tr>
                <td><strong>Total Quantity Adopsi</strong></td>
                <td>:</td>
                <td><?= number_format($total_qty, 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td><strong>Nomor PK</strong></td>
                <td>:</td>
                <td><?= $no_pk ?></td>
            </tr>
            <tr>
                <td><strong>Active From</strong></td>
                <td>:</td>
                <td><?= $start_date ?></td>
            </tr>
            <tr>
                <td><strong>Expired At</strong></td>
                <td>:</td>
                <td><?= $end_date ?></td>
            </tr>
            <tr>
                <td><strong>Sales Admin</strong></td>
                <td>:</td>
                <td><?= $sa_name ?></td>
            </tr>
            <tr>
                <td><strong>File PK</strong></td>
                <td>:</td>
                <td><a href="<?= $file_pk ?>" class="d-block m-0 p-0" target="_blank"><i class="fa fa-paperclip"></i> <span style="font-size: .85rem;">File PK</span></a></td>
            </tr>
            <tr>
                <td><strong>File Benefit</strong></td>
                <td>:</td>
                <td><a href="<?= $file_benefit ?>" class="d-block m-0 p-0" target="_blank"><i class="fa fa-paperclip"></i> <span style="font-size: .85rem;">File Benefit</span></a></td>
            </tr>
        </table>
    </div>
 
<?php } else { ?>
    <div class="alert alert-danger" role="alert">
        Something went wrong
    </div>
<?php } $conn->close();?>


    
    
    