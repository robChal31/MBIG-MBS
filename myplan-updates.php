
<?php 
    include 'header.php';

    $role = $_SESSION['role'];
    $id_user = $_SESSION['id_user'];
    $myplan_id = ISSET($_GET['plan_id']) ? $_GET['plan_id'] : null;
?>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="bg-whites rounded h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-4">Update My Plan</h6>
                            <?php
                                if($role == 'admin' || $role == 'ec') { ?>
                                    <div class="d-flex align-items-center">
                                        <span style="cursor: pointer;" data-bs-toggle='modal' data-bs-target='#createModal' class="bg-primary fw-bold py-1 px-2 text-white rounded"><i class="fas fa-plus" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"></i> Add</span>
                                    </div>
                            <?php } ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table_draft">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 5%">ID</th>
                                        <th scope="col" style="width: 20%">Plan Update</th>
                                        <th scope="col" style="width: 20%">Feedback</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Updated at</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                        $sql = "SELECT mpu.*, user.*, sc.name as school_name
                                                    FROM myplan_update AS mpu
                                                LEFT JOIN myplan AS mp ON mp.id = mpu.myplan_id
                                                LEFT JOIN schools AS sc ON sc.id = mp.school_id
                                                LEFT JOIN user ON mp.user_id = user.id_user
                                                LEFT JOIN programs AS prog ON prog.name = mp.program
                                                WHERE mp.deleted_at IS NULL";

                                        if($role == 'ec'){
                                            $sql .=" AND (mp.user_id = $id_user OR user.leadId = $id_user OR user.leadId2 = $id_user OR user.leadId3 = $id_user)";
                                        }

                                        $sql .= " ORDER BY mp.created_at DESC";
                                        
                                        $result = mysqli_query($conn, $sql);
                                        setlocale(LC_MONETARY,"id_ID");
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= $row['update_note'] ?></td>
                                                    <td><?= $row['feedback'] ?></td>
                                                    <td><?= $row['created_at'] ?></td>
                                                    <td><?= $row['updated_at'] ?></td>
                                                    <td>
                                                        <span data-id="<?= $row['id'] ?>" style="cursor: pointer;" data-bs-toggle='modal' data-bs-target='#createModal' class="fw-bold py-1 px-2 rounded text-success"><i class="fas fa-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Update"></i> Update</span>

                                                        <span data-id="<?= $row['id'] ?>" style="cursor: pointer;" data-bs-toggle='modal' data-bs-target='#feedbackModal' class="fw-bold py-1 px-2 rounded text-primary"><i class="fa fa-comment" data-bs-toggle="tooltip" data-bs-placement="top" title="Feedback"></i> Feedback</span>
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

        <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="createModalBody">
                    Loading...
                </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="feedbackModalBody">
                    Loading...
                </div>
                </div>
            </div>
        </div>

<?php include 'footer.php';?>

<script>
    const myPlanId = "<?= $myplan_id ?>";

    var createModal = document.getElementById('createModal')
    createModal.addEventListener('show.bs.modal', function (event) {
        var myplanUpdateId = event.relatedTarget.getAttribute('data-id')
        var modalTitle = createModal.querySelector('.modal-title')
        modalTitle.textContent = 'Add Update My Plan';

        $.ajax({
            url: 'myPlanFormUpdate.php',
            type: 'POST',
            data: {
                myplan_id: myPlanId,
                myplan_update_id: myplanUpdateId,
            },
            success: function(data) {
                $('#createModalBody').html(data)
            }
        });
    })

    var feedbackModal = document.getElementById('feedbackModal')
    feedbackModal.addEventListener('show.bs.modal', function (event) {
        var myplanUpdateId = event.relatedTarget.getAttribute('data-id')
        var modalTitle = feedbackModal.querySelector('.modal-title')
        modalTitle.textContent = 'Add Feedback';

        $.ajax({
            url: 'myPlanFormFeedbackUpdate.php',
            type: 'POST',
            data: {
                myplan_id: myPlanId,
                myplan_update_id: myplanUpdateId,
            },
            success: function(data) {
                $('#feedbackModalBody').html(data)
            }
        });
    })

    $('.close').click(function() {
        $('#createModal').modal('hide');
        $('#feedbackModal').modal('hide');
    });

    $(document).ready(function() {
        $('#table_draft').DataTable({
            dom: 'Bfrtip',
            pageLength: 20,
            order: [
                [0, 'desc'] 
            ],
            buttons: [
                { 
                    extend: 'copyHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                    }
                },
                { 
                    extend: 'excelHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                    }
                },
                { 
                    extend: 'csvHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                    }
                },
                { 
                    extend: 'pdfHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                    }
                }
            ]
        })
    })
</script>
       