<?php include 'header.php'; ?>
<?php include 'dashboard_data.php'; ?>
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="col-12">
                <h4>Dashboard</h4> 
                <div class="bg-white rounded h-100 p-4 my-4">
                    <div class="row my-2 py-4 justify-content-center">
                        <div class="col-md-6 col-12">
                            <canvas id="periode-chart"></canvas>
                        </div> 
                        <div class="col-md-6 col-12">
                            <canvas id="yearly-chart"></canvas>
                        </div> 
                    </div>
                </div>

                <div class="bg-white rounded h-100 p-4 my-4">
                    <div class="row my-2 py-2 justify-content-center">
                        <div class="col-md-3 col-12">
                            <canvas id="program-chart"></canvas>
                        </div>
                        <div class="col-md-3 col-12">
                            <canvas id="segment-chart"></canvas>
                        </div>
                        <div class="col-md-3 col-12">
                            <canvas id="level-chart"></canvas>
                        </div>
                    </div>
                    
                </div>

                <div class="bg-white rounded h-100 p-4 my-4">
                    <div class="row my-2 py-2 justify-content-center">
                        <div class="col-md-12 col-12">
                            <canvas id="ec-chart"></canvas>
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
                    ...
                </div>
                </div>
            </div>
        </div>


       <?php include 'footer.php';?>

       <script>

            let programData     = <?= json_encode($program_total) ?>;
            let programLabel    = <?= json_encode($program_label) ?>;
            let segmentData     = <?= json_encode($segment_total) ?>;
            let segmentLabel    = <?= json_encode($segment_label) ?>;
            let levelData       = <?= json_encode($level_total) ?>;
            let levelLabel      = <?= json_encode($level_label) ?>;
            let periodeData     = <?= json_encode($periode_total) ?>;
            let periodeLabel    = <?= json_encode($periode_label) ?>;
            let yearlyData     = <?= json_encode($yearly_total) ?>;
            let yearlyLabel    = <?= json_encode($yearly_label) ?>;
            let ecData     = <?= json_encode($ec_total) ?>;
            let ecLabel    = <?= json_encode($ec_label) ?>;

            var programCtx  = document.getElementById('program-chart').getContext('2d');
            var segmentCtx  = document.getElementById('segment-chart').getContext('2d');
            var levelCtx    = document.getElementById('level-chart').getContext('2d');
            var periodeCtx  = document.getElementById('periode-chart').getContext('2d');
            var yearlyCtx  = document.getElementById('yearly-chart').getContext('2d');
            var ecCtx  = document.getElementById('ec-chart').getContext('2d');

            var programChart = new Chart(programCtx, {
                type: 'pie',
                data: {
                    labels: programLabel,
                    datasets: [{
                        label: 'Total',
                        data: programData,
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
                    }
                }
            });

            var segmentChart = new Chart(segmentCtx, {
                type: 'pie',
                data: {
                    labels: segmentLabel,
                    datasets: [{
                        label: 'Total',
                        data: segmentData,
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
                    }
                }
            });

            var levelChart = new Chart(levelCtx, {
                type: 'pie',
                data: {
                    labels: levelLabel,
                    datasets: [{
                        label: 'Total',
                        data: levelData,
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
                    }
                }
            });

            var periodeChart = new Chart(periodeCtx, {
                type: 'line',
                data: {
                    labels: periodeLabel,
                    datasets: [{
                        label: 'Total',
                        data: periodeData,
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
                    }
                }
            });

            var yearlyChart = new Chart(yearlyCtx, {
                type: 'bar',
                data: {
                    labels: yearlyLabel,
                    datasets: [{
                        label: 'Total',
                        data: yearlyData,
                        backgroundColor: ['#ff6000', '#c32f27', '#da4167', '#00a896', '#99582a', '#f78764', '#168aad', '#3a0ca3', '#ff8b10', '#001c55'],
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
                            text: 'Draft Benefit Yearly',
                            color: '#282828',
                            font: {
                                size: 18
                            }
                        },
                    },
                    scales: {
                        y: {
                           display: false
                        }
                    }
                }
            });

            var ecChart = new Chart(ecCtx, {
                type: 'bar',
                data: {
                    labels: ecLabel,
                    datasets: [{
                        label: 'Total',
                        data: ecData,
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
                    }
                }
            });
        </script>