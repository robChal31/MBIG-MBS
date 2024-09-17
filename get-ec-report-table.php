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
    
    if($role != 'admin') {
        header("Location: ./main.php");
        exit();
    }

    $selectedPrograms   = ISSET($_POST['selectedPrograms']) ? $_POST['selectedPrograms'] : null;
    $selectedEc         = ISSET($_POST['selectedEc']) ? $_POST['selectedEc'] : null;
    $selectedPeriode    = ISSET($_POST['selectedPeriode']) ? $_POST['selectedPeriode'] : null; 
    $selectedStatus2    = ISSET($_POST['selectedStatus']) ? $_POST['selectedStatus'] : null;
    $startDate2         = ISSET($_POST['startDate']) ? $_POST['startDate'] : date('Y-m', strtotime('-6 month'));
    $endDate2           = ISSET($_POST['endDate']) ? $_POST['endDate'] : date('Y-m');

    $selected_programs      = $selectedPrograms ? implode(", ", $selectedPrograms) : 'all';
    $programs_temp          = [];
    $query_param_program    = $selected_programs == 'all' ? "" : (preg_match('/\b9999\b/', $selected_programs) ? " AND (id IN ($selected_programs) OR is_pk = 1)" : " AND id IN ($selected_programs)");
    $query_program          = "SELECT * from programs WHERE is_active = 1 $query_param_program;"; 

    $result         = mysqli_query($conn, $query_program);
    while ($data = $result->fetch_assoc()) {
        $programs_temp[] = $data['name'];
    }

    $selected_programs = implode("', '", $programs_temp);

?>

<div class="row">
    <div class="col-12">          
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-4">Detail Data</h6>
            <div>
                <button type="button" class="btn btn-success m-2 btn-sm" id="back-to-main"><i class="fas fa-arrow-left me-2"></i>Back</button>    
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="table_draft">
                <thead>
                    <tr>
                        <th>#</th>
                        <th scope="col" style="width: 10%">Nama EC</th>
                        <th scope="col" style="width: 20%">Nama Sekolah</th>
                        <th scope="col">Segment</th>
                        <th scope="col">Program</th>
                        <th scope="col">Level</th>
                        <th scope="col">Created at</th>
                        <th scope="col" style="width: 13%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $query_program = " AND a.program IN ('$selected_programs') ";
                        $sql2 = "SELECT a.*, b.*, IFNULL(sc.name, a.school_name) as school_name2, a.verified, a.deleted_at
                                    FROM draft_benefit a
                                LEFT JOIN schools as sc on sc.id = a.school_name
                                LEFT JOIN user b on a.id_ec = b.id_user
                                WHERE a.deleted_at IS NULL AND a.status IN ($selectedStatus2) 
                                $query_program 
                                AND DATE_FORMAT(a.date, '%Y-%m') BETWEEN '$startDate2' AND '$endDate2'
                                AND b.generalname = '$selectedEc'
                                ORDER BY a.date ASC";
                        
                        $result = mysqli_query($conn, $sql2);
                        setlocale(LC_MONETARY,"id_ID");
                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $stat = ($row['status'] == 0 && $row['fileUrl']) ? 'Waiting Approval': ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft' : $stat;
                                $stat = $row['verified'] == 1 && $stat == 'Approved' ? 'Verified' : ($row['verified'] == 0 && $stat == 'Approved' ? 'Waiting Verification' : $stat);
                    ?>
                            <tr>
                                <th scope="row"><?= $row['id_draft'] ?></th>
                                <td><?= $row['generalname'] ?></td>
                                <td><?= $row['school_name2'] ?></td>
                                <td><?= ucfirst($row['segment']) ?></td>
                                <td><?= $row['program'] ?></td>
                                <td><?= strtoupper($row['level']) ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $stat ?></td>
                            </tr>
                            
                        <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('#table_draft').DataTable({
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

        $("#back-to-main").click(function() {
            $('#report-loading').addClass('d-none');
            $('#report-error').addClass('d-none');
            $('#report-chart').removeClass('d-none');
            $('#report-table').addClass('d-none');
        })
    })

</script>
       