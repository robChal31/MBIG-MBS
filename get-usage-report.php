
<?php

    include 'db_con.php';
    ob_start();
    session_start();

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');

    error_reporting(E_ALL);

    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }

    $role = $_SESSION['role'];
    $selectedPrograms = ISSET($_POST['selectedPrograms']) ? $_POST['selectedPrograms'] : null;
    $startDate = ISSET($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-d', strtotime('-6 month'));
    $endDate = ISSET($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-d');

    $selected_programs = $selectedPrograms ? implode(", ", $selectedPrograms) : 'all';
    $reports = [];

    $programs_temp = [];
    $query_param_program = $selected_programs == 'all' ? "" : (preg_match('/\b9999\b/', $selected_programs) ? " AND (id IN ($selected_programs) OR is_pk = 1)" : " AND id IN ($selected_programs)");
    $query_program  = "SELECT * from programs WHERE is_active = 1 $query_param_program;"; 

    $result         = mysqli_query($conn, $query_program);
    while ($data = $result->fetch_assoc()) {
        $programs_temp[] = $data['name'];
    }

    $selected_programs = implode("', '", $programs_temp);

    $query_reports = "";

    // $exec_reports = mysqli_query($conn, $query_reports);
    // if (mysqli_num_rows($exec_reports) > 0) {
    //     $reports = mysqli_fetch_all($exec_reports, MYSQLI_ASSOC);    
    // }
?>

    <!-- Sale & Revenue Start -->
    <div class="container-fluid p-1">
        <div class="col-12" id="report-chart">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle"
                    id="table_data"
                    style="font-size: .78rem;">

                    <thead class="table-light text-nowrap">
                        <tr>
                            <th style="width:3%" class="text-center">#</th>
                            <th style="width:10%">EC</th>
                            <th style="width:20%">School</th>
                            <!-- <th>Category</th> -->
                            <th>Program</th>
                            <th>No PK</th>
                            <th>Benefit</th>
                            <th>Description</th>
                            <th>Usage</th>
                            <th class="text-center">Qty</th>
                            <th class="text-nowrap">Used At</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                        $query_program = " AND db.program IN ('$selected_programs') ";
                        $sql2 = "SELECT db.id_draft, bu.id, bu.description, bu.qty1, bu.qty2, bu.qty3,
                                        bu.used_at, bu.redeem_code, dbl.benefit_name,
                                        dbl.description as benefit_desc,
                                        IFNULL(sc.name, db.school_name) as school_name2,
                                        prog.name as program_name,
                                        b.generalname as ec_name,
                                        cat.name as program_category,
                                        pk.no_pk
                                FROM benefit_usages bu
                                LEFT JOIN draft_benefit_list dbl
                                    ON dbl.id_benefit_list = bu.id_benefit_list
                                LEFT JOIN draft_benefit db
                                    ON db.id_draft = dbl.id_draft
                                LEFT JOIN schools sc ON sc.id = db.school_name
                                LEFT JOIN programs prog
                                    ON (prog.name = db.program OR prog.code = db.program)
                                LEFT JOIN program_categories cat
                                    ON cat.id = prog.program_category_id
                                LEFT JOIN user b ON db.id_ec = b.id_user
                                LEFT JOIN pk pk ON pk.benefit_id = db.id_draft
                                WHERE db.deleted_at IS NULL
                                AND db.confirmed = 1
                                AND bu.used_at BETWEEN '$startDate' AND '$endDate'
                                $query_program
                                ";

                        $result = mysqli_query($conn, $sql2);

                        if (!$result) {
                            die("MySQL Error: " . mysqli_error($conn));
                        }

                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td class="text-center text-muted">
                                <?= $row['id_draft'] ?>
                            </td>

                            <td class="fw-semibold">
                                <?= $row['ec_name'] ?>
                            </td>

                            <td><?= $row['school_name2'] ?></td>

                            <!-- <td>
                                <?= $row['program_category'] ?: 
                                    '<span class="text-muted">Not set</span>' ?>
                            </td> -->

                            <td><?= $row['program_name'] ?></td>

                            <td class="text-nowrap"><?= $row['no_pk'] ?></td>

                            <td><?= $row['benefit_name'] ?></td>

                            <td class="text-muted">
                                <?= $row['benefit_desc'] ?>
                            </td>

                            <td><?= $row['description'] ?></td>

                            <td class="text-center fw-semibold">
                                <?= $row['qty1'] != 0
                                    ? $row['qty1']
                                    : ($row['qty2'] != 0
                                        ? $row['qty2']
                                        : $row['qty3']) ?>
                            </td>

                            <td class="text-nowrap">
                                <?= date('d M Y', strtotime($row['used_at'])) ?>
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

        <div class="col-12 d-none" id="report-loading">
            <div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
        </div>
        <div class="col-12 d-none" id="report-error">
            
        </div>
    </div>

<script>
    $(document).ready(function() {
        $('#table_data').DataTable({
            dom: 'Bfrtilp',
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            order: [[6, 'desc']],
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
                $('#table_data_length label').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'font-size': '.7rem',
                    'font-weight': 'bold',
                    'margin-left': '20px',
                    'margin-top': '8px'
                });

                $('#table_data_length select').css({
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
    
    
    