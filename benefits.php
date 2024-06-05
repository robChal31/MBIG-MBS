<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
      font-size: .75rem;
  }

  table.dataTable thead th {
      vertical-align: middle !important;
      font-size: .75rem;
  }
</style>
<?php
    $role = $_SESSION['role'];
    $selected_type = [];

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $selected_type = $_POST['type'];
    }

    $types = [];
    $query_type = "SELECT GROUP_CONCAT(br.id_template SEPARATOR ',') as id_templates, br.benefit, br.code
                    FROM benefit_role br
                    WHERE br.code = 'mkt'
                    GROUP BY br.code, br.benefit;";

    $exec_type = mysqli_query($conn, $query_type);
    if (mysqli_num_rows($exec_type) > 0) {
        $types = mysqli_fetch_all($exec_type, MYSQLI_ASSOC);    
    }

    $benefits = [];
    $query_benefits = "SELECT *, IFNULL(sc.name, db.school_name) as school_name2
                        FROM draft_benefit db 
                        LEFT JOIN draft_benefit_list dbl on db.id_draft = dbl.id_draft
                        LEFT JOIN pk p on p.benefit_id = db.id_draft
                        LEFT JOIN schools as sc on sc.id = db.school_name
                    WHERE db.verified = 1
                    AND dbl.id_template IN (8,9,77);";

    $exec_benefits = mysqli_query($conn, $query_benefits);
    if (mysqli_num_rows($exec_benefits) > 0) {
        $benefits = mysqli_fetch_all($exec_benefits, MYSQLI_ASSOC);    
    }
?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">

            <div class="bg-white rounded h-100 p-4 mb-4">
                <h6 class="mb-4" style="display: inline-block; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Filter Benefit</h6>
                <form action="./benefits.php" method="POST">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-6">
                            <label for="type">Benefit Type</label>
                            <select class="form-select select2" name="type[]" aria-label="Default select example" multiple>
                                <?php foreach($types as $type) : ?>
                                    <option value="<?= $type['id_templates'] ?>" <?= count($selected_type) < 1 ? 'selected' : (in_array($type['id_templates'], $selected_type) ? 'selected' : '') ?>><?= $type['benefit'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>
                
            </div>
            
            <div class="bg-white rounded h-100 p-4">
                <h6 class="mb-4">Benefits</h6>                      
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No PK</th>
                                <th scope="col" style="width: 20%">School Name</th>
                                <th scope="col">Active From</th>
                                <th scope="col">Expired At</th>
                                <th scope="col" style="width: 13%">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach($benefits as $benefit) {
                            ?>
                                    <tr>
                                        <td><?= $benefit['no_pk'] ?></td>
                                        <td><?= $benefit['school_name2'] ?></td>
                                        <td><?= $benefit['start_at'] ?></td>
                                        <td><?= $benefit['expired_at'] ?></td>
                                        <td>
                                            <span data-id="<?= $benefit['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.65rem'><?= $status_msg  ?></span>
                                        </td>
                                        <td scope='col'>
                                            
                                            <span data-id="<?= $benefit['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm me-2' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-eye'></i></span>
                                            
                                        </td>
                                    </tr>
                               <?php } ?>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id='detail-benefit'>See Details</a>
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
        $('.select2').select2({});

        // $.ajax({
        //     url: './get-verified-benefits.php',
        //     type: 'POST',
        //     data: {
        //         types: rowid,
        //     },
            
        //     success: function(response) {
        //         console.log(response);
                
        //     },
        //     error: function(xhr, status, error) {
        //         console.error('Error:', error); 
        //     }
        // });
    });

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>