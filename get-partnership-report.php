
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
            <table class="table align-middle" id="table_data">
                <thead>
                    <tr>
                        <th style="width:4%">#</th>
                        <th>Nama EC</th>
                        <th>Nama Sekolah</th>
                        <th>Program Category</th>
                        <th>Program</th>
                        <th>No PK</th>
                        <th class="text-center">Alokasi Benefit</th>
                        <th class="text-center">Total Benefit</th>
                        <th>Active From</th>
                        <th>Expired At</th>
                        <th class="text-center" style="width:10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $query_program = " AND db.program IN ('$selected_programs') ";
                    $sql2 = "SELECT db.id_draft, db.alokasi, db.total_benefit,
                                    IFNULL(sc.name, db.school_name) as school_name2,
                                    prog.name as program_name,
                                    b.generalname as ec_name,
                                    cat.name as program_category,
                                    pk.no_pk, pk.start_at, pk.expired_at
                            FROM draft_benefit as db 
                            LEFT JOIN schools as sc on sc.id = db.school_name
                            LEFT JOIN programs AS prog ON (prog.name = db.program OR prog.code = db.program)
                            LEFT JOIN program_categories as cat on cat.id = prog.program_category_id
                            LEFT JOIN user b on db.id_ec = b.id_user
                            LEFT JOIN pk as pk on pk.benefit_id = db.id_draft
                            WHERE db.deleted_at IS NULL 
                            AND db.confirmed = 1
                            AND pk.start_at BETWEEN '$startDate' AND '$endDate'
                            AND pk.expired_at >= CURDATE() 
                            $query_program";

                    $result = mysqli_query($conn, $sql2);
                    if (!$result) {
                        die("MySQL Error: " . mysqli_error($conn));
                    }

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?= $row['id_draft'] ?></td>
                        <td class="fw-semibold"><?= $row['ec_name'] ?></td>
                        <td><?= $row['school_name2'] ?></td>
                        <td><?= $row['program_category'] ?: 'Belum dilengkapi' ?></td>
                        <td><?= $row['program_name'] ?></td>
                        <td><?= $row['no_pk'] ?></td>
                        <td class="text-center"><?= number_format($row['alokasi'], 0, ',', '.') ?></td>
                        <td class="text-center"><?= number_format($row['total_benefit'], 0, ',', '.') ?></td>
                        <td><?= $row['start_at'] ?></td>
                        <td><?= $row['expired_at'] ?></td>
                        <td class="text-center">
                            <div class="dropdown" data-bs-boundary="window">
                                <i class="fas fa-ellipsis-v text-muted"
                                data-bs-toggle="dropdown"
                                style="cursor:pointer"></i>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item">
                                            <i class="fa fa-eye me-2"></i> Detail
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
            order: [[4, 'desc']],
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
    
    
    