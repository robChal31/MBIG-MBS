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
    $start_date = $_POST['start_date'] ?? date('Y-m-d', strtotime('-3 year'));
    $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+3 year'));

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
            <div class="row">
                <div class="col-12">

                    <div class="bg-whites rounded h-100 p-4 shadow">

                        <!-- HEADER -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0">Agreement List</h5>
                                <small class="text-muted">
                                    Verified and confirmed benefit agreements
                                </small>
                            </div>
                        </div>

                        <!-- FILTER -->
                        <div class="border rounded mb-4 shadow-sm">

                            <!-- FILTER HEADER -->
                            <div class="d-flex justify-content-between align-items-center px-3 py-2
                                        bg-light-subtle fw-semibold cursor-pointer"
                                data-bs-toggle="collapse"
                                data-bs-target="#filterCollapse"
                                aria-expanded="false">

                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-sliders-h text-primary"></i>
                                    <span>Filter Agreement</span>
                                </div>

                                <i class="fas fa-chevron-down transition rotate-icon"></i>
                            </div>

                            <!-- FILTER BODY -->
                            <div id="filterCollapse" class="collapse">
                                <div class="p-3 bg-white rounded-bottom">

                                    <form method="POST" action="" id="filterForm">
                                        <div class="row g-3 align-items-start">

                                            <div class="col-md-6 col-12">
                                                <label class="form-label fw-semibold">Active From</label>
                                                <input type="text"
                                                    class="form-control dateFilter"
                                                    name="start_date"
                                                    value="<?= $start_date ?>"
                                                    placeholder="Select date">
                                               
                                            </div>

                                            <div class="col-md-6 col-12">
                                                <label class="form-label fw-semibold">Expired At</label>
                                                <input type="text"
                                                    class="form-control dateFilter"
                                                    name="end_date"
                                                    value="<?= $end_date ?>"
                                                    placeholder="Select date">
                                               
                                            </div>

                                            <div class="col-md-12 col-12">
                                                <label class="form-label fw-semibold">Program <small class="text-muted" style="font-size: 12px;">(You can select multiple programs)</small></label>
                                                <select name="programs[]" id="program" class="form-control select2" multiple>
                                                    <?php foreach ($programs as $program) { ?>
                                                        <option value="<?= trim($program['code']) ?>"
                                                            <?= !$selected_program ? '' : (in_array($program['code'], $selected_program) ? 'selected' : '') ?>>
                                                            <?= $program['name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <div class="d-flex justify-content-end gap-2 mt-1 col-12">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="select-all">
                                                        Select All
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-all">
                                                        Clear
                                                    </button>
                                                </div>
                                                 
                                            </div>

                                            <div class="col-12 d-flex justify-content-md-end align-items-end">
                                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                                    <i class="fas fa-filter me-1"></i> Apply
                                                </button>
                                                
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- TABLE -->
                        <div class="table-responsive">
                            <table class="table align-middle" id="table_id">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>EC</th>
                                        <th>School</th>
                                        <th>Segment</th>
                                        <th>Program</th>
                                        <th>PK Type</th>
                                        <th>Alokasi</th>
                                        <th>No PK</th>
                                        <th>Active</th>
                                        <th>Expired</th>
                                        <th>Created</th>
                                        <!-- <th>Status</th> -->
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                <?php
                                    $sql_q = " WHERE ";
                                    $id_user = $_SESSION['id_user'];

                                    $sql = "SELECT 
                                                b.id_draft, b.status, b.date, b.id_ec, b.school_name,
                                                b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2,
                                                b.alokasi, b.year, c.generalname, pk.id as pk_id,
                                                b.verified, b.confirmed, b.jenis_pk,
                                                pk.no_pk, pk.start_at, pk.expired_at, pk.created_at,
                                                IFNULL(seg.segment, b.segment) as new_segment, prog.name as program_name
                                            FROM draft_benefit b
                                            LEFT JOIN draft_approval a ON a.id_draft = b.id_draft
                                            LEFT JOIN segments seg ON seg.id = b.segment
                                            LEFT JOIN schools sc ON sc.id = b.school_name
                                            LEFT JOIN user c ON c.id_user = b.id_ec
                                            LEFT JOIN pk pk ON pk.benefit_id = b.id_draft
                                            LEFT JOIN programs as prog ON prog.name = b.program or prog.code = b.program";

                                    if($_SESSION['role'] == 'ec'){
                                        $sql .= " WHERE (a.id_user_approver = $id_user
                                                OR c.leadId = $id_user
                                                OR b.id_ec = $id_user)";
                                        $sql_q = " AND ";
                                    }

                                    $sql .= "$sql_q
                                            b.status = 1
                                            AND b.verified = 1
                                            AND b.confirmed = 1
                                            AND b.deleted_at IS NULL";

                                    $sql .= $selected_program ? " AND b.program IN ('$selected_programs_q')" : '';
                                    $sql .= $start_date ? " AND pk.start_at >= '$start_date'" : '';
                                    $sql .= $end_date ? " AND pk.expired_at <= '$end_date'" : '';
                                    $sql .= " GROUP BY b.id_draft ORDER BY b.date DESC";

                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while($row = mysqli_fetch_assoc($result)) {

                                            $program_name = $row['year'] == 1
                                                ? $row['program_name']
                                                : $row['program_name']." Perubahan Tahun Ke ".$row['year'];

                                            $status_class = $row['confirmed'] == 1 ? 'success' : 'warning';
                                            $status_text  = $row['confirmed'] == 1 ? 'Confirmed' : 'Waiting';
                                ?>
                                    <tr>
                                        <td><?= $row['id_draft'] ?></td>
                                        <td class="fw-semibold"><?= $row['generalname'] ?></td>
                                        <td><?= $row['school_name2'] ?></td>
                                        <td><?= ucfirst($row['new_segment']) ?></td>
                                        <td><?= strtoupper($program_name) ?></td>
                                        <td><?= $row['jenis_pk'] == 2 ? 'Amandemen' : 'Baru' ?></td>
                                        <td><?= number_format($row['alokasi'], 0, ',', '.') ?></td>
                                        <td><?= $row['no_pk'] ?></td>
                                        <td><?= $row['start_at'] ?></td>
                                        <td><?= $row['expired_at'] ?></td>
                                        <td><?= $row['created_at'] ?></td>

                                        <!-- <td>
                                            <span class="badge bg-<?= $status_class ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </td> -->

                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-sm"
                                                    data-id="<?= $row['id_draft'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#pkModal">
                                                <i class="fa fa-eye"></i>
                                            </button>
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

    })


    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
    });
</script>