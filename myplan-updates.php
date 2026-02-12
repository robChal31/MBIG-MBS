
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
                    <div class="card rounded h-100 p-4 shadow">

                        <!-- HEADER -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">Update My Plan</h5>
                                <small class="text-muted">Track progress, feedback, and updates</small>
                            </div>

                            <div class="">
                                <a href="myplan.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back
                                </a>
                                <?php if ($role == 'admin' || $role == 'ec') { ?>
                                    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createModal">
                                        <i class="fas fa-plus me-2"></i> Add Update
                                    </button>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive">
                        <table class="table align-middle" id="table_draft">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th>Plan Update</th>
                                <th>Feedback</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            $sql = "SELECT mpu.*, user.*, sc.name as school_name
                                        FROM myplan_update AS mpu
                                        LEFT JOIN myplan AS mp ON mp.id = mpu.myplan_id
                                        LEFT JOIN schools AS sc ON sc.id = mp.school_id
                                        LEFT JOIN user ON mp.user_id = user.id_user
                                        LEFT JOIN programs AS prog ON (prog.name = mp.program OR prog.code = mp.program)
                                        WHERE mp.deleted_at IS NULL
                                        AND mp.id = $myplan_id";

                            if ($role == 'ec') {
                                $sql .= " AND (mp.user_id = $id_user
                                        OR user.leadId = $id_user
                                        OR user.leadId2 = $id_user
                                        OR user.leadId3 = $id_user)";
                            }

                            $sql .= " ORDER BY mpu.created_at DESC";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= $row['update_note'] ?></div>
                                </td>
                                <td>
                                    <?= $row['feedback']
                                    ? "<span class='text-muted'>{$row['feedback']}</span>"
                                    : "<span class='badge bg-secondary'>No feedback</span>" ?>
                                </td>
                                <td><?= $row['created_at'] ?></td>
                                <td><?= $row['updated_at'] ?></td>

                                <!-- ACTION -->
                                <td class="text-center">
                                    <div class="dropdown">
                                    <i class="fas fa-ellipsis-v text-muted"
                                        data-bs-toggle="dropdown"
                                        style="cursor:pointer"></i>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                        <a class="dropdown-item"
                                            data-id="<?= $row['id'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#createModal">
                                            <i class="fas fa-edit me-2"></i> Update
                                        </a>
                                        </li>

                                        <li>
                                        <a class="dropdown-item text-primary"
                                            data-id="<?= $row['id'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#feedbackModal">
                                            <i class="fa fa-comment me-2"></i> Feedback
                                        </a>
                                        </li>
                                    </ul>
                                    </div>
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
            dom: 'Bfrtilp',
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            order: [[0, 'desc']],
            buttons: [
                { 
                    extend: 'copyHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                    }
                },
                { 
                    extend: 'excelHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                    }
                },
                { 
                    extend: 'csvHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                    }
                },
                { 
                    extend: 'pdfHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                    }
                }
            ],
            initComplete: function () {
                $('#table_draft_length label').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'font-size': '.7rem',
                    'font-weight': 'bold',
                    'margin-left': '20px',
                    'margin-top': '8px'
                });

                $('#table_draft_length select').css({
                    'font-size': '.7rem',
                    'font-weight': 'bold',
                    'border-radius': '5px',
                    'padding': '2px 6px',
                    'border': '1px solid #ccc'
                });
            }
        });
    })
</script>
       