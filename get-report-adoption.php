
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

    $role               = $_SESSION['role'];
    $selectedPrograms   = ISSET($_POST['selectedPrograms']) ? $_POST['selectedPrograms'] : null;
    // $selectedStatus     = ISSET($_POST['selectedStatus']) ? $_POST['selectedStatus'] : null;
    $startDate          = ISSET($_POST['startDate']) ? $_POST['startDate'] : date('Y-m', strtotime('-6 month'));
    $endDate            = ISSET($_POST['endDate']) ? $_POST['endDate'] : date('Y-m');

    $selected_programs  = $selectedPrograms ? implode(", ", $selectedPrograms) : 'all';
    // $selected_status    = $selectedStatus ? implode(", ", $selectedStatus) : '0,1,2';
    $reports            = [];

    $programs_temp      = [];
    $query_param_program = $selected_programs == 'all' ? "" : (preg_match('/\b9999\b/', $selected_programs) ? " AND (id IN ($selected_programs) OR is_pk = 1)" : " AND id IN ($selected_programs)");
    $query_program  = "SELECT * from programs WHERE is_active = 1 $query_param_program;"; 

    $result         = mysqli_query($conn, $query_program);
    while ($data = $result->fetch_assoc()) {
        $programs_temp[] = $data['name'];
    }

    $selected_programs = implode("', '", $programs_temp);

    $query_reports = "";
?>

    <!-- Sale & Revenue Start -->
    <div class="container-fluid py-2">
        <div class="col-12" id="report-chart">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="table_data">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th scope="col" style="width: 10%">Nama EC</th>
                                <th scope="col" style="width: 20%">Nama Sekolah</th>
                                <th scope="col" style="width: 10%">Program</th>
                                <th scope="col">Judul Buku</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query_program = " AND a.program IN ('$selected_programs') ";
                                $sql2 = "SELECT a.*, b.*, IFNULL(sc.name, a.school_name) as school_name2, a.verified, a.deleted_at, calc.book_title, calc.qty
                                            FROM draft_benefit a
                                        LEFT JOIN schools as sc on sc.id = a.school_name
                                        LEFT JOIN user b on a.id_ec = b.id_user
                                        LEFT JOIN calc_table AS calc on calc.id_draft = a.id_draft
                                        WHERE a.deleted_at IS NULL AND status = 1 AND verified = 1 AND confirmed = 1
                                        $query_program 
                                        AND DATE_FORMAT(a.date, '%Y-%m') BETWEEN '$startDate' AND '$endDate' 
                                        ORDER BY a.date ASC";
                                

                                $result = mysqli_query($conn, $sql2);
                                setlocale(LC_MONETARY,"id_ID");
                                if (mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                            ?>
                                    <tr>
                                        <th><?= $row['id_draft'] ?></th>
                                        <td><?= $row['generalname'] ?></td>
                                        <td><?= $row['school_name2'] ?></td>
                                        <td><?= strtoupper($row['program']) ?></td>
                                        <td><?= $row['book_title'] ?></td>
                                        <td><?= $row['qty'] ?></td>
                                        <td><?= $row['date'] ?></td>
                                    </tr>
                                    
                                <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 d-none" id="report-loading">
            <div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
        </div>
        <div class="col-12 d-none" id="report-error">
            
        </div>
    </div>


<script>

        function hideBeforeSend() {
            $('#report-loading').removeClass('d-none');
            $('#report-error').addClass('d-none');
            $('#report-chart').addClass('d-none');
            $('#report-table').addClass('d-none');
        }

        function showAfterSuccess(response) {
            $('#report-loading').addClass('d-none');
            $('#report-error').addClass('d-none');
            $('#report-chart').addClass('d-none');
            $('#report-table').removeClass('d-none');
            $('#report-table').html(response)
        }

        function showError(error) {
            $('#report-loading').addClass('d-none');
            $('#report-chart').addClass('d-none');
            $('#report-table').addClass('d-none');
            console.error('Error:', error);
            $('#report-error').removeClass('d-none');
            $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
        }

        $(document).ready(function() {
            $('#table_data').DataTable({
                dom: 'Bfrtip',
                pageLength: 20,
                order: [
                    [4, 'desc'] 
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


    
    
    