<?php include 'header.php'; ?>

<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
        font-size: .6rem;
    }

    #event .select2-container {
        z-index: 2050 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }
</style>

<?php 

$role = $_SESSION['role'];
$id_draft = $_GET['id'];                                                                      
$sql = "SELECT 
            b.*, c.*, pk.*, prog.is_pk,
            IFNULL(sc.name, b.school_name) as school_name2, dbl.total_qty, pk.id as id_pk, dash_sa.sa_name
        FROM draft_benefit as b
        LEFT JOIN schools as sc on sc.id = b.school_name
        LEFT JOIN user as c on c.id_user = b.id_ec
        LEFT JOIN pk on pk.benefit_id = b.id_draft
        LEFT JOIN dash_sa on dash_sa.id_sa = pk.sa_id
        LEFT JOIN programs AS prog ON prog.name = b.program
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
        $is_pk = $row['is_pk'];
        $confirmed = $row['confirmed'];
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
                    <h6>Detail Agreement</h6>                    
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

            <?php if(!$is_pk) { ?>
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
            <?php } ?>

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
                                    <th>Implementation</th>
                                    <th>Benefit Value</th>
                                    <th>Year 1</th>
                                    <th>Total Usage Year 1</th>
                                    <th>Year 2</th>
                                    <th>Total Usage Year 2</th>
                                    <th>Year 3</th>
                                    <th>Total Usage Year 3</th>
                                    <th>Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $sql = "SELECT dbl.*, bu.*, dbt.redeemable
                                            FROM draft_benefit_list AS dbl
                                            LEFT JOIN (
                                                SELECT 
                                                    SUM(COALESCE(bu.qty1, 0)) AS tot_usage1,
                                                    SUM(COALESCE(bu.qty2, 0)) AS tot_usage2,
                                                    SUM(COALESCE(bu.qty3, 0)) AS tot_usage3,
                                                    bu.id_benefit_list as id_bl
                                                FROM benefit_usages bu
                                                GROUP BY bu.id_benefit_list
                                            ) as bu on bu.id_bl = dbl.id_benefit_list
                                            LEFT JOIN draft_template_benefit AS dbt on dbt.id_template_benefit = dbl.id_template
                                            WHERE dbl.id_draft = '$id_draft'";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr class="<?= ($row['tot_usage1'] > 0 || $row['tot_usage2'] > 0 || $row['tot_usage3'] > 0) ? 'bg-info text-white' : '' ?>">
                                        <td><?= $no ?></td>
                                        <td><?= $row['type'] ?></td>
                                        <td><?= $row['subbenefit'] ?></td>
                                        <td><?= $row['benefit_name'] ?></td>
                                        <td style="width: 20%"><?= $row['description'] ?></td>
                                        <td style="width: 20%"><?= $row['pelaksanaan'] ?></td>
                                        <td class="text-end"><?= number_format($row['manualValue'], '0', ',', '.') ?></td>
                                        <td class="text-end"><?= $row['qty'] ?></td>
                                        <td class="text-end"><?= $row['tot_usage1'] ?? 0?></td>
                                        <td class="text-end"><?= strtolower($program) == 'cbls3'? $row['qty'] : $row['qty2'] ?></td>
                                        <td class="text-end"><?= $row['tot_usage2'] ?? 0?></td>
                                        <td class="text-end"><?= strtolower($program) == 'cbls3'? $row['qty'] : $row['qty3'] ?></td>
                                        <td class="text-end"><?= $row['tot_usage3'] ?? 0?></td>
                                        <td><?= number_format($row['calcValue'], '0', ',', '.') ?></td>
                                        <td>
                                            <div class="d-flex gap-1">                                           
                                                <?php if($confirmed == 1 && $row['redeemable'] == 1) : ?>
                                                    <span data-id="<?= $row['id_benefit_list'] ?>" data-action='usage' data-bs-toggle='modal' data-bs-target='#usageModal' class='btn btn-outline-warning btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Usage'><i class='fa fa-clipboard-list'></i></span>
                                                <?php endif; ?>

                                                <span data-id="<?= $row['id_benefit_list'] ?>" data-action='history' data-bs-toggle='modal' data-bs-target='#historyUsageModal' class='btn btn-outline-success btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='History Usage'><i class='fa fa-history'></i></span>

                                                <span data-id="<?= $row['id_benefit_list'] ?>" data-action='note' data-bs-toggle='modal' data-bs-target='#noteUsageModal' class='btn btn-outline-secondary btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Note Usage'><i class='fa fa-sticky-note'></i></span>                                               
                                            </div>
                                        </td>
 
                                    </tr>
                                <?php $no++;} } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-whites rounded h-100 p-4">
                <div class="p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <h6>Benefit Implementation Report </h6>                        
                        <button type="button" class="btn btn-primary btn-sm" data-id="<?= $id_draft ?>" id="reqReport">
                            <i class="fa fa-download me-2"></i> Request Report
                        </button>
                    </div>                
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Created at</th>
                                    <th>Updated at</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $id_user = $_SESSION['id_user'];
                                    $sql = "SELECT * 
                                                FROM benefit_imp_report AS bir
                                                LEFT JOIN (
                                                    SELECT token, bir_id
                                                    FROM bir_approval
                                                    WHERE id_user_approver = $id_user
                                                ) AS bir_a ON bir_a.bir_id = bir.id
                                            WHERE id_draft = '$id_draft'";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)) {
                                            $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                                            $status_msg = $row['status'] == 0 ? 'Waiting Approval' : ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $row['period'] ?></td>
                                        <td>
                                            <span data-id="<?= $row['id'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.55rem'><?= $status_msg  ?></span>
                                        </td>
                                        <td>
                                            <?php if($row['status'] != 1) : ?>
                                                <span class="badge bg-danger">
                                                    <i class="fa fa-times"></i> Not Available
                                                </span>
                                            <?php else : ?>
                                                <a href="<?= $row['file'] ?>" target="_blank" class="badge bg-primary"><i class="fa fa-download"></i> Download</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $row['created_at'] ?></td>
                                        <td><?= $row['updated_at'] ?></td>
                                        <td>
                                            <a href="approve_rep_req.php?token=<?= $row['token'] ?>" class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Approve'><i class='fas fa-fingerprint'></i></a>
                                        </td>
                                    </tr>
                                <?php $no++;} } else {?>
                                    <tr>
                                        <td colspan="6" class="text-center">Data Not Found</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Sale & Revenue End -->
<?php } ?>

    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="approvalModalBody">
                Loading...
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyUsageModal" tabindex="-1" role="dialog" aria-labelledby="historyUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyUsageModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="historyUsageModalBody">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                </div>
            </div> 
        </div>
    </div>

    <div class="modal fade" id="usageModal" tabindex="-1" role="dialog" aria-labelledby="usageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usageModalLabel">Usage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="usageModalBody">
                    Loading...
                </div>
                
            </div> 
        </div>
    </div>

    <div class="modal fade" id="noteUsageModal" tabindex="-1" role="dialog" aria-labelledby="noteUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteUsageModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="noteUsageModalBody">
                    Loading...
                </div>
            </div> 
        </div>
    </div>

<?php include 'footer.php';?>
<script>

    var approvalModal = document.getElementById('approvalModal');
    approvalModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        var modalTitle = approvalModal.querySelector('.modal-title')
        modalTitle.textContent = 'Approval History ' + rowid;
        $.ajax({
            url: 'get_rep_approver.php',
            type: 'POST',
            data: {
                id: rowid,
            },
            success: function(data) {
                $('#approvalModalBody').html(data)
            }
        });
    })

    $('#usageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        $('#usageModalLabel').html("Add Benefit Usage");
        $.ajax({
            url: 'input-usage.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
                program : '<?= $program ?>',
            },
            success: function(data) {
                $('#usageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    });

    $('#historyUsageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        $('#historyUsageModalLabel').html("History Benefit Usage");
        $.ajax({
            url: 'history-usage.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
            },
            success: function(data) {
                $('#historyUsageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    })

    $('#noteUsageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');
        $('#noteUsageModalBody').html('');
        $('#noteUsageModalLabel').html("Note Benefit Usage");
        $.ajax({
            url: 'input-benefit-note.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
            },
            success: function(data) {
                $('#noteUsageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    })

    $('#reqReport').click(function () {
        var idDraft = $(this).data('id');
        Swal.fire({
            title: "You will send request report?",
            text: "This action will send request report to Top Leader and Secretary.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, send it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'save-req-lap-ben.php',
                    type: 'POST',
                    data: {
                        id_draft: idDraft
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            html: 'Please wait while we save your data.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    success: function(data) {
                        let resData = JSON.parse(data)
                        Swal.close()
                        if(resData.status == 'Success') {
                            Swal.fire({
                                title: "Success!",
                                text: resData.message,
                                icon: "success"
                            });
                        }else {
                            Swal.fire({
                                title: "Error!",
                                text: resData.message,
                                icon: "error"
                            });
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    },
                    error: function(data) {
                        Swal.close();
                        let resData = JSON.parse(data)
                        Swal.fire({
                            title: "Error!",
                            text: resData.message,
                            icon: "error"
                        });
                        // location.reload();
                    }
                });
            }
        });
        
    })

    $(document).ready(function() {
    
    })

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#usageModal').modal('hide');
        $('#historyUsageModal').modal('hide');
        $('#noteUsageModal').modal('hide');
    });
</script>