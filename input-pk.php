<?php

include 'db_con.php';

$id_draft = $_POST['id_draft'];                                                                      
$sql = "SELECT 
            b.*, 
            c.*, 
            IFNULL(sc.name, b.school_name) as school_name2, 
            dbl.total_qty
        FROM draft_benefit as b
        LEFT JOIN schools as sc on sc.id = b.school_name
        LEFT JOIN user as c on c.id_user = b.id_user
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
    }
?>
    <div class="p-2">
        <h6>Detail Benefit</h6>
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
                <td><?= $segment ?></td>
            </tr>
            <tr>
                <td><strong>Total Quantity Adopsi</strong></td>
                <td>:</td>
                <td><?= $total_qty ?></td>
            </tr>
        </table>
    
        <h6 class="mt-4 pt-4">Form Input PK</h6>
        <form action="">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Nomor PK</label>
                    <input type="text" name="no_pk" class="form-control form-control-sm" placeholder="Nomor PK" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Berlaku Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Berakhir Sampai</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">PK</label>
                    <input type="file" name="file_pk" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Benefit</label>
                    <input type="file" name="file_benefit" class="form-control form-control-sm" required>
                </div>
                
            </div>
        </form>
    </div>
 
  
<?php } $conn->close();?>


    
    
    