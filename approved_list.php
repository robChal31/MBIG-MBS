<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
  }
</style>
<?php 
    $role = $_SESSION['role'];

?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            
            <div class="bg-white rounded h-100 p-4">
                <h6 class="mb-4">Approved Benefit List</h6>                      
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No Draft</th>
                                <th scope="col" style="width:10%">Nama EC</th>
                                <th scope="col" style="width: 20%">Nama Sekolah</th>
                                <th scope="col">Segment</th>
                                <th scope="col">Tanggal Pembuatan</th>
                                <th scope="col">Jenis Program</th>
                                <th scope="col" style="width: 13%">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql_q = " WHERE ";
                                $id_user = $_SESSION['id_user'];
                                $sql = "SELECT * from draft_benefit a left join user b on a.id_user=b.id_user"; 
                                $sql = "SELECT 
                                            a.id_draft_approval, a.id_draft, b.status, a.token,
                                            b.date, b.id_user, b.id_ec, b.school_name, b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2,
                                            c.generalname, d.generalname as leadername, a.token, d.id_user as id_user_approver
                                        FROM `draft_approval` a 
                                        INNER JOIN draft_benefit b on a.id_draft = b.id_draft 
                                        LEFT JOIN schools sc on sc.id = b.school_name
                                        LEFT JOIN user c on c.id_user = b.id_user 
                                        LEFT JOIN user d on d.id_user = a.id_user_approver 
                                        LEFT JOIN (
                                            SELECT 
                                                id_draft,
                                                MAX(date) AS max_date
                                            FROM `draft_approval`
                                            GROUP BY id_draft
                                        ) latest_approval ON a.id_draft = latest_approval.id_draft ";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver =" . $_SESSION['id_user'] . " or c.leadId='" . $_SESSION['id_user'] . "') ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q (a.date = latest_approval.max_date OR latest_approval.max_date IS NULL) AND b.status = 1 ORDER BY id_draft";

                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                        $token = $row['token'];
                                        $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                                ?>
                                        <tr>
                                            <td><?= $id_draft ?></td>
                                            <td><?= $row['generalname'] ?></td>
                                            <td><?= $row['school_name2'] ?></td>
                                            <td><?= ucfirst($row['segment']) ?></td>
                                            <td><?= $row['date'] ?></td>
                                            <td><?= $row['program'] ?></td>
                                           
                                            <td>
                                                <span data-id="<?= $row['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.65rem'><?=  ($row['status'] == 0 ? 'Waiting Approval' : ($row['status'] == 1 ? 'Approved' : 'Rejected'))  ?></span>
                                            </td>
                                            <td scope='col'>
                                                <?php if($row['status'] == 1 && $role == 'admin') { ?>
                                                    <span data-id="<?= $row['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm' style='font-size: .7rem' data-toggle='tooltip' title='Approve'><i class='fas fa-pen'></i> Input PK</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                               <?php     }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Modal -->
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
                ...
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

    var pkModal = document.getElementById('pkModal');
    pkModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        var modalTitle = pkModal.querySelector('.modal-title')
        modalTitle.textContent = "Input PK";
        $.ajax({
            url: 'input-pk.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#pkModalBody').html(data)
            }
        });
    })

    $('.close').click(function() {
        // Cari modal yang sedang terbuka dan tutup modal tersebut
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>