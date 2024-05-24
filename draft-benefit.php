
<?php 
    include 'header.php'; 
?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="bg-white rounded h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-4">Draft Benefit</h6>
                            <a href="new-benefit-ec-input.php">
                                <button type="button" class="btn btn-primary m-2 btn-sm"><i class="fas fa-plus me-2"></i>Create Draft</button>    
                            </a>
                        </div>
                        

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table_id">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 15%">Nama EC</th>
                                        <th scope="col" style="width: 25%">Nama Sekolah</th>
                                        <th scope="col">Segment</th>
                                        <th scope="col">Jenis Program</th>
                                        <th scope="col">Tanggal Pembuatan</th>
                                        <th scope="col" style="width: 13%">Status</th>
                                        <th scope="col">View</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $order_by = ' ORDER BY date';
                                        $sql = "SELECT a.*, b.*, IFNULL(sc.name, a.school_name) as school_name2
                                                FROM draft_benefit a
                                                LEFT JOIN schools as sc on sc.id = a.school_name
                                                left join user b on a.id_user = b.id_user"; 
                                        if($_SESSION['role'] == 'ec'){
                                            $sql.=" where a.id_user=".$_SESSION['id_user']." or b.leadId='".$_SESSION['id_user']."'";
                                        }
                                        $sql .= $order_by;
                                        
                                        $result = mysqli_query($conn, $sql);
                                        setlocale(LC_MONETARY,"id_ID");
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $stat = ($row['status'] == 0 && $row['fileUrl']) ? 'Waiting Approval': ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                                $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft' : $stat;
                                                $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                                                $status_class = ($row['status'] == 0 && !$row['fileUrl']) ? 'bg-primary' : $status_class;
                                    ?>
                                                <tr>
                                                    <td><?= $row['generalname'] ?></td>
                                                    <td><?= $row['school_name2'] ?></td>
                                                    <td><?= $row['segment'] ?></td>
                                                    <td><?= $row['program'] ?></td>
                                                    <td><?= $row['date'] ?></td>
                                                    <td><span data-id="<?= $row['id_draft'] ?>" <?= $stat == 'Draft' ? '' : "data-bs-toggle='modal'" ?>  data-bs-target='#approvalModal' class="<?= $status_class ?> fw-bold py-1 px-2 text-white rounded" style="cursor:pointer; font-size:.65rem"><?= $stat ?></span></td>
                                                    <td>
                                                        <?php if($row['fileUrl']) { ?>
                                                            <a href='draft-benefit/<?= $row['fileUrl'].".xlsx" ?>'><i class="bi bi-paperclip"></i> Doc</a>
                                                        <?php } ?>
                                                    </td>
                                                    <td scope="col">
                                                        <!-- <?php
                                                            if($_SESSION['role'] == 'admin'){ ?>
                                                                <i class="fas fa-check text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Approve"></i>
                                                        <?php } ?> -->
                                                        <?php 
                                                            if(($_SESSION['id_user'] == $row['id_ec'] && $row['status'] == 0 && !$row['fileUrl']) || ($row['status'] == 2 && ($_SESSION['id_user'] == $row['id_ec'] || $_SESSION['role'] == 'admin')) || ($_SESSION['role'] == 'admin' && $row['status'] == 0 && !$row['fileUrl'])){ ?>
                                                                <a href="new-benefit-ec-input2.php?edit=edit&id_draft=<?=$row['id_draft']?>" class="ms-2 text-success"><i class="fas fa-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i></a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            
                                        <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sale & Revenue End -->

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
                    ...
                </div>
                </div>
            </div>
        </div>

<?php include 'footer.php';?>

<script>
    var approvalModal = document.getElementById('approvalModal')
    approvalModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        var modalTitle = approvalModal.querySelector('.modal-title')
        modalTitle.textContent = 'Approval History ' + rowid;
        console.log(rowid)
        $.ajax({
            url: 'get_benefits_approver.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#approvalModalBody').html(data)
            }
        });
    })

    $('.close').click(function() {
        // Cari modal yang sedang terbuka dan tutup modal tersebut
        $('#approvalModal').modal('hide');
    });
</script>
       