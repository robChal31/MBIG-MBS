<?php include 'header.php'; ?>

<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>

<?php 

$role = $_SESSION['role'];
$id_draft = $_GET['id'];                                                                      
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

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            
            <div class="bg-whites rounded h-100 p-4 mb-4">
                <div class="p-2 mb-2">
                    <h6>Detail Partnership Agreement</h6>                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="table-responsive">   
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%"><strong>EC</strong></td>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="table-responsive">   
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%"><strong>Active From</strong></td>
                                            <td style="width: 1%">:</td>
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
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-whites rounded h-100 p-4 mb-4">
                <div class="p-2 mb-2">
                    <h6>List of Books Ordered</h6>                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Book</th>
                                    <th>Student Qty</th>
                                    <th>Suggested Book Price</th>
                                    <th>Normal Book Price</th>
                                    <th>Discount</th>
                                    <th>Price After Discount</th>
                                    <th>Revenue After One Piece</th>
                                    <th>Revenue Before One Piece</th>
                                    <th>Alocation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $sql = "SELECT * FROM calc_table WHERE id_draft = '$id_draft'";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)) {
                                            $after_disc = $row['normalprice'] - $row['normalprice'] * ($row['discount'] / 100);
                                ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $row['book_title'] ?></td>
                                        <td><?= number_format($row['qty'], '0', ',', '.') ?></td>
                                        <td><?= number_format($row['usulan_harga'], '0', ',', '.') ?></td>
                                        <td><?= number_format($row['normalprice'], '0', ',', '.') ?></td>
                                        <td><?= $row['discount'] ?></td>
                                        <td><?= number_format($after_disc, '0', ',', '.') ?></td>
                                        <td><?= number_format(($row['usulan_harga'] * $row['qty']), '0', ',', '.') ?></td>
                                        <td><?= number_format(($after_disc  * $row['qty']), '0', ',', '.') ?></td>
                                        <td><?= number_format($row['alokasi'], '0', ',', '.') ?></td>
                                    </tr>
                                <?php $no++;} } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-whites rounded h-100 p-4">
                <div class="p-2 mb-2">
                    <h6>List of Benefit</h6>                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Benefit</th>
                                    <th>Sub Benefit</th>
                                    <th>Benefit Name</th>
                                    <th>Description</th>
                                    <th>Benefit Value</th>
                                    <th>Implementation</th>
                                    <th>Year 1 Qty</th>
                                    <th>Year 2 Qty</th>
                                    <th>Year 3 Qty</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $sql = "SELECT * FROM draft_benefit_list WHERE id_draft = '$id_draft'";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)) {
                                            $after_disc = $row['normalprice'] - $row['normalprice'] * ($row['discount'] / 100);
                                ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $row['type'] ?></td>
                                        <td><?= $row['subbenefit'] ?></td>
                                        <td><?= $row['benefit_name'] ?></td>
                                        <td><?= $row['description'] ?></td>
                                        <td><?= number_format($row['manualValue'], '0', ',', '.') ?></td>
                                        <td><?= $row['pelaksanaan'] ?></td>
                                        <td><?= number_format($row['qty'], '0', ',', '.') ?></td>
                                        <td><?= number_format($row['qty2'], '0', ',', '.') ?></td>
                                        <td><?= number_format($row['qty3'], '0', ',', '.') ?></td>
                                        <td><?= number_format($row['calcValue'], '0', ',', '.') ?></td>
                                    </tr>
                                <?php $no++;} } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Sale & Revenue End -->
<?php } ?>
    <!-- Modal -->
    <div class="modal fade" id="pkModal" tabindex="-1" role="dialog" aria-labelledby="pkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pkModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pkModalBody">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id='detail-benefit'>See Details</a>
                </div>
            </div> 
        </div>
    </div>

<?php include 'footer.php';?>
<script>

    var pkModal = document.getElementById('pkModal');
    pkModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = pkModal.querySelector('.modal-title')
        modalTitle.textContent = "Detail PK";
        $.ajax({
            url: 'detail-pk.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#pkModalBody').html(data);
                $('#detail-benefit').attr('href', 'detail-benefit.php?id=' + rowid);
            }
        });
    })

    $(document).ready(function() {
    
    })

    $(document).on('click', '.close', function() {
        $('#pkModal').modal('hide');
    });
</script>