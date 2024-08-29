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
            
            <div class="bg-whites rounded h-100 p-4">
                <h6 class="mb-4">Program PIC</h6>                      
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No PK</th>
                                <th scope="col" style="width:10%">EC</th>
                                <th scope="col" style="width: 20%">School Name</th>
                                <th scope="col">Name</th>
                                <th scope="col">Position</th>
                                <th scope="col">Phone No</th>
                                <th scope="col">Email</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql_q = " WHERE ";
                                $id_user = $_SESSION['id_user'];

                                $sql = "SELECT b.id_draft, pk.no_pk, c.generalname, spp.jabatan, spp.name, spp.email, spp.no_tlp, IFNULL(sc.name, b.school_name) as school_name2, prog.is_pk
                                        FROM draft_benefit b
                                        LEFT JOIN draft_approval as a on a.id_draft = b.id_draft AND a.id_user_approver = $id_user
                                        LEFT JOIN schools sc on sc.id = b.school_name
                                        LEFT JOIN user c on c.id_user = b.id_ec 
                                        INNER JOIN pk pk on pk.benefit_id = b.id_draft
                                        LEFT JOIN school_pic_partner spp on spp.id_draft = b.id_draft
                                        LEFT JOIN programs as prog on prog.name = b.program ";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver = $id_user or c.leadId = $id_user or b.id_ec = $id_user) ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q b.status = 1 AND b.verified = 1 AND b.deleted_at IS NULL AND prog.is_active = 1 ORDER BY b.id_draft DESC";

                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                       
                                ?>
                                        <tr>
                                            <td><?= $row['id_draft'] ?></td>
                                            <td><?= $row['no_pk'] ?></td>
                                            <td><?= $row['generalname'] ?></td>
                                            <td><?= $row['school_name2'] ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['jabatan'] ?></td>
                                            <td><?= $row['no_tlp'] ?></td>
                                            <td><?= $row['email'] ?></td>
                                            <td scope='col'>
                                                <?php if(!$row['name']) { ?>
                                                    <span data-id="<?= $row['id_draft'] ?>" data-action='picAct' data-bs-toggle='modal' data-bs-target='#picModal' class='btn btn-outline-success btn-sm me-2' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-plus'></i></span>
                                                <?php } else { ?>
                                                    <span data-id="<?= $row['id_draft'] ?>" data-action='picAct' data-bs-toggle='modal' data-bs-target='#picModal' class='btn btn-outline-primary btn-sm me-2' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-pen'></i></span>
                                                <?php } ?>
                                               <?php if($row['is_pk']) : ?>
                                                    <a href='https://mentaripartner.com' target="_blank" data-toggle='tooltip' title='MPP Link'><img style="width: 50px" class="img-fluid rounded shadow" src="img/mpp.jfif" alt=""></a>
                                                <?php endif; ?>
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