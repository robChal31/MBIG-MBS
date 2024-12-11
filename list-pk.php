<?php include 'header.php'; ?>
<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
    }

    .rotate-icon {
        transition: transform 0.3s ease;
    }

    /* Rotate the icon if the collapsible is shown by default */
    .collapse.show ~ .rotate-icon {
        transform: rotate(180deg);
    }
</style>
<?php 
    $role = $_SESSION['role'];
    $selected_program = $_POST['programs'] ?? NULL;
    $start_date = $_POST['start_date'] ?? date('Y-m-d', strtotime('-1 year'));
    $end_date = $_POST['end_date'] ?? date('Y-m-d');

    $selected_programs_q = $selected_program ? implode("', '", $selected_program) : 'all';

    $programs = [];
    $programs_q = "SELECT * FROM programs AS program WHERE program.is_active = 1";
    $programs_exec = mysqli_query($conn, $programs_q);
    if (mysqli_num_rows($programs_exec) > 0) {
      $programs = mysqli_fetch_all($programs_exec, MYSQLI_ASSOC);    
    }

?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white" data-bs-toggle="collapse" data-bs-target="#collapseCard">
                   Filter
                    <i class="fas fa-chevron-down rotate-icon"></i>
                </div>
                <div id="collapseCard" class="collapse show">
                    <div class="card-body">
                        <form method="POST" action="" id="filterForm">
                            <div class="row">
                                <div class="col-md-2 col-12">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">Start Date</label>
                                        <input type="text" class="form-control dateFilter" name="start_date" placeholder="Select Date" value="<?= $start_date ?>">
                                    </div>
                                </div>
                                <div class="col-md-2 col-12">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">End Date</label>
                                        <input type="text" class="form-control dateFilter" name="end_date" placeholder="Select Date" value="<?= $end_date ?>">
                                    </div>
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    <div class="mb-2">
                                        <label for="program" class="form-label">Program</label>
                                        <select placeholder="Select Program" name="programs[]" id="program" class="form-control form-control-sm select2" style="background-color: white;" multiple>
                                            <?php foreach ($programs as $program) { ?>
                                                <option value="<?= trim($program['name']) ?>" <?= !$selected_program ? '' : (in_array($program['name'], $selected_program) ? 'selected' : '') ?>><?= $program['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="select-all">Select All</button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="clear-all">Clear All</button>
                                    </div>
                                </div>
                            </div>
                           
                            
                            <div class="d-flex justify-content-end my-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>

                        </form>
                 
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            
            <div class="bg-whites rounded h-100 p-4">
                <h6 class="mb-4">Agreement List</h6>    
            
                <div class="table-responsive">
                    <table class="table table-striped" id="table_id">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th scope="col" style="width:10%">EC</th>
                                <th scope="col" style="width: 20%">School Name</th>
                                <th scope="col">Segment</th>
                                <th scope="col">Jenis Program</th>
                                <th scope="col">Jenis PK</th>
                                <th scope="col">Alokasi</th>
                                <th>No PK</th>
                                <th scope="col">Active From</th>
                                <th scope="col">Expired At</th>
                                <th scope="col">Created At</th>
                                <th scope="col" style="width: 13%">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $sql_q = " WHERE ";
                                $id_user = $_SESSION['id_user'];

                                $sql = "SELECT 
                                            b.id_draft, b.status, b.date, b.id_user, b.id_ec, b.school_name, b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2, b.alokasi,
                                            c.generalname, pk.id as pk_id, b.verified, a.token, b.deleted_at, b.fileUrl, pk.file_pk, pk.no_pk, pk.start_at, pk.expired_at, pk.created_at, b.confirmed, b.jenis_pk, c.leadId
                                        FROM draft_benefit b
                                        LEFT JOIN draft_approval as a on a.id_draft = b.id_draft AND a.id_user_approver = $id_user
                                        LEFT JOIN schools sc on sc.id = b.school_name
                                        LEFT JOIN user c on c.id_user = b.id_ec 
                                        LEFT JOIN pk pk on pk.benefit_id = b.id_draft";
                                if($_SESSION['role'] == 'ec'){
                                    $sql .= " WHERE (a.id_user_approver = $id_user or c.leadId = $id_user or b.id_ec = $id_user) ";
                                    $sql_q = " AND ";
                                }

                                $sql .= "$sql_q b.status = 1 AND b.verified = 1 AND b.confirmed = 1 AND b.deleted_at IS NULL ";
                                $sql .= $selected_program ? " AND b.program IN ('$selected_programs_q') " : '';
                                $sql .= $start_date ? " AND b.date >= '$start_date' " : '';
                                $sql .= $end_date ? " AND b.date <= '$end_date' " : '';        
                                $sql .= " ORDER BY b.date DESC";


                                $result = mysqli_query($conn, $sql);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $id_draft = $row['id_draft'];
                                        $status_class = $row['verified'] == 1 ? 'bg-success' :  'bg-primary';
                                        $status_msg = ($row['verified'] == 1 ? 'Verified' : 'Waiting Verification');
                                        if($row['verified'] == 1) {
                                            $status_class = $row['confirmed'] == 1 ? 'bg-success' :  'bg-primary';
                                            $status_msg = ($row['confirmed'] == 1 ? 'Confirmed' : 'Waiting Confirmation');
                                        }
                                ?>
                                        <tr>
                                            <td><?= $id_draft ?></td>
                                            <td><?= $row['generalname'] ?></td>
                                            <td><?= $row['school_name2'] ?></td>
                                            <td><?= ucfirst($row['segment']) ?></td>
                                            <td><?= strtoupper($row['program']) ?></td>
                                            <td><?= $row['jenis_pk'] == 2 ? 'Amandemen' : 'Baru' ?></td>
                                            <td><?= number_format($row['alokasi'], 0, ',', '.') ?></td>
                                            <td><?= $row['no_pk'] ?></td>
                                            <td><?= $row['start_at'] ?></td>
                                            <td><?= $row['expired_at'] ?></td>
                                            
                                            <td>
                                                <?= $row['created_at'] ?>
                                            </td>
                                            <td>
                                                <span data-id="<?= $row['id_draft'] ?>" data-bs-toggle='modal' data-bs-target='#approvalModal' class='fw-bold <?= $status_class ?> py-1 px-2 text-white rounded' style='cursor:pointer; font-size:.55rem'><?= $status_msg  ?></span>
                                            </td>
                                            <td scope='col'>
                                               
                                                <span data-id="<?= $row['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm me-2' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-eye'></i></span>
                                               
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
                Loading...
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

    flatpickr(".dateFilter", {
        dateFormat: "Y-m-d",
        allowInput: true,
    });

    $(document).ready(function() {
        const element = document.getElementById('program');
        const choices = new Choices(element, {
            placeholder: true,
            placeholderValue: 'Select Program',
            searchEnabled: true,
            removeItemButton: true
        });

        // Select All functionality
        document.getElementById('select-all').addEventListener('click', () => {
            const allOptions = Array.from(element.options);
            allOptions.forEach(option => {
                if (!option.selected) {
                    choices.setChoiceByValue(option.value);
                }
            });
        });

        // Clear All functionality
        document.getElementById('clear-all').addEventListener('click', () => {
            choices.removeActiveItems();
        });


        document.querySelector('.card-header').addEventListener('click', function () {
            this.classList.toggle('collapsed');
        });

    })


    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>