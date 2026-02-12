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
            
            <div class="card rounded shadow-sm p-3">
                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-semibold mb-0">Program PIC</h6>
                        <small class="text-muted">Person in Charge per approved program</small>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-sm" id="table_id" style="font-size:.8rem">
                        <thead class="table-light">
                            <tr>
                                <th style="width:4%">No</th>
                                <th>No PK</th>
                                <th>EC</th>
                                <th>School Name</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                            $sql_q = " WHERE ";
                            $id_user = $_SESSION['id_user'];

                            $sql = "SELECT b.id_draft, pk.no_pk, c.generalname, spp.jabatan, spp.name,
                                        spp.email, spp.no_tlp, IFNULL(sc.name, b.school_name) as school_name2,
                                        prog.is_pk
                                    FROM draft_benefit b
                                    LEFT JOIN draft_approval a
                                        ON a.id_draft = b.id_draft
                                    AND a.id_user_approver = $id_user
                                    LEFT JOIN schools sc ON sc.id = b.school_name
                                    LEFT JOIN user c ON c.id_user = b.id_ec
                                    INNER JOIN pk pk ON pk.benefit_id = b.id_draft
                                    LEFT JOIN school_pic_partner spp ON spp.id_draft = b.id_draft
                                    LEFT JOIN programs prog ON (prog.name = b.program OR prog.code = b.program)";

                            if($_SESSION['role'] == 'ec'){
                                $sql .= " WHERE (a.id_user_approver = $id_user
                                        OR c.leadId = $id_user
                                        OR b.id_ec = $id_user)";
                                $sql_q = " AND ";
                            }

                            $sql .= "$sql_q b.status = 1
                                    AND b.verified = 1
                                    AND b.deleted_at IS NULL
                                    AND prog.is_active = 1
                                    ORDER BY b.id_draft DESC";

                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?= $row['id_draft'] ?></td>
                                <td><?= $row['no_pk'] ?></td>
                                <td class="fw-semibold"><?= $row['generalname'] ?></td>
                                <td style="width: 25%"><?= $row['school_name2'] ?></td>
                                <td><?= $row['name'] ?: '<span class="text-muted">-</span>' ?></td>
                                <td><?= $row['jabatan'] ?: '<span class="text-muted">-</span>' ?></td>
                                <td><?= $row['no_tlp'] ?: '<span class="text-muted">-</span>' ?></td>
                                <td><?= $row['email'] ?: '<span class="text-muted">-</span>' ?></td>

                                <!-- ACTION -->
                                <td class="text-center">
                                    <div class="dropdown">
                                        <i class="fas fa-ellipsis-v text-muted"
                                        data-bs-toggle="dropdown"
                                        style="cursor:pointer"></i>

                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.75rem">

                                            <?php if(!$row['name']) : ?>
                                                <li>
                                                    <a class="dropdown-item text-success"
                                                    data-id="<?= $row['id_draft'] ?>"
                                                    data-action="picAct"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#picModal">
                                                        <i class="fa fa-plus me-2"></i> Add PIC
                                                    </a>
                                                </li>
                                            <?php else : ?>
                                                <li>
                                                    <a class="dropdown-item text-primary"
                                                    data-id="<?= $row['id_draft'] ?>"
                                                    data-action="picAct"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#picModal">
                                                        <i class="fa fa-pen me-2"></i> Edit PIC
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php if($row['is_pk']) : ?>
                                                <li>
                                                    <a class="dropdown-item text-primary"
                                                    href="https://mentaripartner.com"
                                                    target="_blank"
                                                    title="Qty manfaat PK3 refill tiap Juli">
                                                        <i class="fa fa-link me-2"></i> MPP Portal
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php
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

    <div class="modal fade" id="picModal" tabindex="-1" role="dialog" aria-labelledby="picModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="picModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="picModalBody">
                    Loading...
                </div>
               
            </div> 
        </div>
    </div>

<?php include 'footer.php';?>
<script>

    var picModal = document.getElementById('picModal');
    picModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');

        var modalTitle = picModal.querySelector('.modal-title')
        modalTitle.textContent = "PIC Detail";
        $.ajax({
            url: 'input-pic.php',
            type: 'POST',
            data: {
                id_draft: rowid,
                action: 'create'
            },
            success: function(data) {
                $('#picModalBody').html(data);
                $('#detail-benefit').attr('href', 'detail-benefit.php?id=' + rowid);
            }
        });
    })

    $(document).ready(function() {
    
    })

    $(document).on('click', '.close', function() {
        $('#picModal').modal('hide');
    });
</script>