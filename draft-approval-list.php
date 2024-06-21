<?php include 'header.php'; ?>
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
        <!-- <div class="row justify-content-end">
            <div class="col-4">
                <div class="mb-3 text-right">
                    <label for="searchInput" class="form-label"></label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                </div>
            </div>
        </div> -->
        <div class="col-12">
            
            <div class="bg-white rounded h-100 p-4">
                <h6 class="mb-4">Draft Benefit Approval List</h6>                      
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
                                <th scope="col">Approver</th>
                                <th scope="col" style="width: 13%">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql_q = " WHERE ";
                                $id_user = $_SESSION['id_user'];
                                $sql = "SELECT 
                                            a.id_draft_approval, a.id_draft, a.status, a.token, b.status as draft_status, b.verified,
                                            b.date, b.id_user, b.id_ec, b.school_name, b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2,
                                            c.generalname, d.generalname as leadername, a.token, d.id_user as id_user_approver, b.deleted_at
                                        FROM `draft_approval` a 
                                        INNER JOIN draft_benefit b on a.id_draft = b.id_draft
                                        LEFT JOIN schools as sc on sc.id = b.school_name 
                                        LEFT JOIN user c on c.id_user = b.id_user 
                                        LEFT JOIN user d on d.id_user = a.id_user_approver 
                                        LEFT JOIN (
                                            SELECT 
                                                id_draft,
                                                MAX(date) AS max_date,
                                                id_user_approver
                                            FROM `draft_approval`
                                            GROUP BY id_draft
                                        ) latest_approval ON a.id_draft = latest_approval.id_draft ";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver =" . $_SESSION['id_user'] . " or c.leadId='" . $_SESSION['id_user'] . "') ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q b.deleted_at IS NULL AND (a.date = latest_approval.max_date OR latest_approval.max_date IS NULL) AND b.status <> 1 ORDER BY id_draft";

                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                        $token = $row['token'];
                                        echo "<tr>";
                                            echo "<td>$id_draft</td>";
                                            echo "<td>".$row['generalname']."</td>";
                                            echo "<td>".$row['school_name2']."</td>";
                                            echo "<td>". ucfirst($row['segment'])."</td>";
                                            echo "<td>".$row['date']."</td>";
                                            echo "<td>".$row['program']."</td>";
                                            echo "<td>".$row['leadername']."</td>";
                                            $status_class = $row['draft_status'] == 0 ? 'bg-warning' : ($row['draft_status'] == 1 ? 'bg-success' : 'bg-danger');
                                            $status_msg = $row['draft_status'] == 0 ? 'Waiting Approval' : ($row['draft_status'] == 1 ? 'Approved' : 'Rejected');
                                            $status_msg = $row['verified'] == 1 && $status_msg == 'Approved' ? 'Verified' : ($row['verified'] == 0 && $status_msg == 'Approved' ? 'Waiting Verification' : $status_msg);
                                            echo "<td><span data-id='" . $row['id_draft'] . "' data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold $status_class py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.65rem'>". ($status_msg) ."</span></td>";
                                            if($row['status'] < 1 && $row['id_user_approver'] == $id_user) {
                                                echo "<td scope='col'><a target='_blank' href='approve-draft-benefit-form.php?id_draft=$id_draft&token=$token' class='btn btn-outline-primary btn-sm' style='font-size: .7rem' data-toggle='tooltip' title='Approve'><i class='fas fa-fingerprint'></i> Approve</a></td>";
                                            }else{
                                                echo "<td></td>";
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



<?php include 'footer.php';?>
<script>
    var approvalModal = document.getElementById('approvalModal')
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

    $('.close').click(function() {
        // Cari modal yang sedang terbuka dan tutup modal tersebut
        $('#approvalModal').modal('hide');
    });
</script>