
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
    $selectedStatus = ISSET($_POST['selectedStatus']) ? $_POST['selectedStatus'] : null;
    $startDate = ISSET($_POST['startDate']) ? $_POST['startDate'] : date('Y-m', strtotime('-6 month'));
    $endDate = ISSET($_POST['endDate']) ? $_POST['endDate'] : date('Y-m');

    $selected_programs = $selectedPrograms ? implode(", ", $selectedPrograms) : 'all';
    $selected_status = $selectedStatus ? implode(", ", $selectedStatus) : '0,1,2';
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

<?php include 'report-data.php'; ?>

    <!-- Sale & Revenue Start -->
    <div class="container-fluid p-4">
        <div class="col-12" id="report-chart">
            <?php if ($program_total && $level_total && $periode_total && $ec_total) { ?>
                <h4>Report Visualization</h4>
                <span class="text-muted">You can click on all the chart to see more detail</span>
                <div class="bg-whites rounded h-100 p-4 my-4">
                    <div class="row my-2 py-2 justify-content-center">
                        <div class="col-md-4 col-12">
                            <canvas id="program-chart"></canvas>
                        </div>
                        <div class="col-md-4 col-12">
                            <canvas id="level-chart"></canvas>
                        </div>
                        <div class="col-md-4 col-12">
                        <canvas id="segment-chart"></canvas>
                    </div>
                    </div>
                </div>

                <hr>
                <h5>All Data</h5>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="table_data">
                            <thead>
                                <tr>
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
                                            WHERE a.deleted_at IS NULL AND a.status IN ($selected_status) 
                                            $query_program 
                                            AND DATE_FORMAT(a.date, '%Y-%m') BETWEEN '$startDate' AND '$endDate' 
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

                <hr>
                <div class="bg-whites rounded h-100 p-4 my-4">
                    <div class="row my-2 py-4 justify-content-center">
                        <div class="col-md-10 col-12">
                            <canvas id="periode-chart"></canvas>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="bg-whites rounded h-100 p-4 my-4">
                    <div class="row my-2 py-2 justify-content-center">
                        <div class="col-md-12 col-12">
                            <canvas id="ec-chart"></canvas>
                        </div>
                    </div>  
                </div>

            <?php }else { ?>
                <div class="">
                    <h4>No Report Available</h4>
                </div>
            <?php } ?>
        </div>
        <div class="col-12 d-none" id="report-table">
            
        </div>
        <div class="col-12 d-none" id="report-loading">
            <div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
        </div>
        <div class="col-12 d-none" id="report-error">
            
        </div>
    </div>


<script>
    <?php if ($program_total && $level_total && $periode_total && $ec_total) { ?>
        var programCtx      = document.getElementById('program-chart').getContext('2d');
        var levelCtx        = document.getElementById('level-chart').getContext('2d');
        var periodeCtx      = document.getElementById('periode-chart').getContext('2d');
        var ecCtx           = document.getElementById('ec-chart').getContext('2d');
        var segmentCtx      = document.getElementById('segment-chart').getContext('2d');

        var programChart = new Chart(programCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($program_label) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($program_total) ?>,
                    backgroundColor: ['#219ebc', '#fb8500', '#023047', '#ffb703', '#8ecae6'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Draft Benefit Per Program',
                        color: '#282828',
                        font: {
                            size: 17
                        }
                    },
                },
                scales: {
                    y: {
                        display: false
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0) {
                        // Get index of clicked element
                        const elementIndex = activeElements[0].index;
                        // Get label (program)
                        const selectedProgram = programChart.data.labels[elementIndex];
                        // Get data (total value)
                        const selectedData = programChart.data.datasets[0].data[elementIndex];
                    
                        changeDisplayedProgramReport(selectedProgram)
                    }
                }
            },
        });

        var levelChart = new Chart(levelCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($level_label) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($level_total) ?>,
                    backgroundColor: ['#0077b6', '#d00000', '#ffc300', '#f95738', '#231942', '#6a994e'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Draft Benefit Per Level',
                        color: '#282828',
                        font: {
                            size: 17
                        }
                    },
                },
                scales: {
                    y: {
                        display: false
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0) {
                        // Get index of clicked element
                        const elementIndex = activeElements[0].index;
                        // Get label (program)
                        const selectedLevel = levelChart.data.labels[elementIndex];
                        // Get data (total value)
                        const selectedData = levelChart.data.datasets[0].data[elementIndex];
                    
                        changeDisplayedLevelReport(selectedLevel)
                    }
                }
            }
        });

        var periodeChart = new Chart(periodeCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($periode_label) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($periode_total) ?>,
                    backgroundColor: ['#ff6000', '#ebebd3', '#da4167', '#00a896', '#99582a', '#f78764', '#168aad', '#3a0ca3', '#ff8b10', '#001c55'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Draft Benefit Trends',
                        color: '#282828',
                        font: {
                            size: 18
                        }
                    },
                },
                scales: {
                    y: {
                        display: true
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0) {
                        // Get index of clicked element
                        const elementIndex = activeElements[0].index;
                        // Get label (program)
                        const selectedPeriode = periodeChart.data.labels[elementIndex];
                        // Get data (total value)
                        const selectedData = periodeChart.data.datasets[0].data[elementIndex];
                    
                        changeDisplayedPeriodeReport(selectedPeriode)
                    }
                }
            }
        });

        var ecChart = new Chart(ecCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($ec_label) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($ec_total) ?>,
                    backgroundColor: ['#ff6000', '#c32f27', '#745ae8', '#00a896', '#99582a', '#f78764', '#168aad', '#3a0ca3', '#ff8b10', '#001c55'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Draft Benefit Leaderboard',
                        color: '#282828',
                        font: {
                            size: 18
                        }
                    },
                },
                scales: {
                    y: {
                        display: true
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0) {
                        // Get index of clicked element
                        const elementIndex = activeElements[0].index;
                        // Get label (program)
                        const selectedEc = ecChart.data.labels[elementIndex];
                        // Get data (total value)
                        const selectedData = ecChart.data.datasets[0].data[elementIndex];
                    
                        changeDisplayedEcReport(selectedEc)
                    }
                }
            }
        });

        var segmentChart = new Chart(segmentCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($segment_label) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($segment_total) ?>,
                    backgroundColor: ['#ff6000', '#5f0f40', '#9a031e', '#fb8b24', '#0f4c5c'],
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: 'rgb(255, 99, 132)'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Draft Benefit Per Segment',
                        color: '#282828',
                        font: {
                            size: 17
                        }
                    },
                },
                scales: {
                    y: {
                        display: false
                    }
                },
                onClick: (event, activeElements) => {
                    if (activeElements.length > 0) {
                        // Get index of clicked element
                        const elementIndex = activeElements[0].index;
                        // Get label (program)
                        const selectedSegment = segmentChart.data.labels[elementIndex];
                        // Get data (total value)
                        const selectedData = segmentChart.data.datasets[0].data[elementIndex];
                    
                        changeDisplayedSegmentReport(selectedSegment)
                    }
                }
            }
        });

        function changeDisplayedProgramReport(selectedPrograms) {
            let selectedStatus = <?= json_encode($selected_status) ?>;
            let startDate = $('input[name="start_date"]').val();
            let endDate = $('input[name="end_date"]').val();
            $.ajax({
                url: './get-program-report-table.php',
                type: 'POST',
                data: {
                    selectedPrograms: selectedPrograms,
                    selectedStatus: selectedStatus,
                    startDate: startDate,
                    endDate: endDate
                },
                beforeSend: function() {
                    $('#report-loading').removeClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                },
                success: function(response) {
                    $('#report-loading').addClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').removeClass('d-none');
                    $('#report-table').html(response)
                },
                error: function(xhr, status, error) {
                    $('#report-loading').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                    $('#report-error').removeClass('d-none');
                    $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
                }
            });
        }

        function changeDisplayedLevelReport(selectedLevel) {
            let selectedStatus = <?= json_encode($selected_status) ?>;
            let startDate = $('input[name="start_date"]').val();
            let endDate = $('input[name="end_date"]').val();
            $.ajax({
                url: './get-level-report-table.php',
                type: 'POST',
                data: {
                    selectedLevel: selectedLevel,
                    selectedStatus: selectedStatus,
                    startDate: startDate,
                    endDate: endDate
                },
                beforeSend: function() {
                    $('#report-loading').removeClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                },
                success: function(response) {
                    $('#report-loading').addClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').removeClass('d-none');
                    $('#report-table').html(response)
                },
                error: function(xhr, status, error) {
                    $('#report-loading').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                    console.error('Error:', error);
                    $('#report-error').removeClass('d-none');
                    $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
                }
            });
        }

        function changeDisplayedSegmentReport(selectedSegment) {
            let selectedStatus = <?= json_encode($selected_status) ?>;
            let startDate = $('input[name="start_date"]').val();
            let endDate = $('input[name="end_date"]').val();
            $.ajax({
                url: './get-segment-report-table.php',
                type: 'POST',
                data: {
                    selectedSegment: selectedSegment,
                    selectedStatus: selectedStatus,
                    startDate: startDate,
                    endDate: endDate
                },
                beforeSend: function() {
                    $('#report-loading').removeClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                },
                success: function(response) {
                    $('#report-loading').addClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').removeClass('d-none');
                    $('#report-table').html(response)
                },
                error: function(xhr, status, error) {
                    $('#report-loading').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                    console.error('Error:', error);
                    $('#report-error').removeClass('d-none');
                    $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
                }
            });
        }

        function changeDisplayedPeriodeReport(selectedPeriode) {
            let selectedStatus = <?= json_encode($selected_status) ?>;
            let selectedPrograms = <?= json_encode($selectedPrograms) ?>;
            let startDate = $('input[name="start_date"]').val();
            let endDate = $('input[name="end_date"]').val();

            $.ajax({
                url: './get-periode-report-table.php',
                type: 'POST',
                data: {
                    selectedPeriode: selectedPeriode,
                    selectedPrograms: selectedPrograms,
                    selectedStatus: selectedStatus,
                    startDate: startDate,
                    endDate: endDate
                },
                beforeSend: function() {
                    $('#report-loading').removeClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                },
                success: function(response) {
                    $('#report-loading').addClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').removeClass('d-none');
                    $('#report-table').html(response)
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                },
                error: function(xhr, status, error) {
                    $('#report-loading').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                    console.error('Error:', error);
                    $('#report-error').removeClass('d-none');
                    $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
                }
            });
        }

        function changeDisplayedEcReport(selectedEc) {
            let selectedStatus = <?= json_encode($selected_status) ?>;
            let selectedPrograms = <?= json_encode($selectedPrograms) ?>;
            let startDate = $('input[name="start_date"]').val();
            let endDate = $('input[name="end_date"]').val();

            $.ajax({
                url: './get-ec-report-table.php',
                type: 'POST',
                data: {
                    selectedEc: selectedEc,
                    selectedPrograms: selectedPrograms,
                    selectedStatus: selectedStatus,
                    startDate: startDate,
                    endDate: endDate
                },
                beforeSend: function() {
                    $('#report-loading').removeClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                },
                success: function(response) {
                    $('#report-loading').addClass('d-none');
                    $('#report-error').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').removeClass('d-none');
                    $('#report-table').html(response)
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                },
                error: function(xhr, status, error) {
                    $('#report-loading').addClass('d-none');
                    $('#report-chart').addClass('d-none');
                    $('#report-table').addClass('d-none');
                    console.error('Error:', error);
                    $('#report-error').removeClass('d-none');
                    $('#report-error').html("<div class='alert alert-danger'>Error: " + error + "</div>");
                }
            });
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
    <?php } ?>
</script>


    
    
    