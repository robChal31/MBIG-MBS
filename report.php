<?php include 'header.php'; ?>
<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
        font-size: .6rem;
    }

    table.dataTable thead th {
        vertical-align: middle !important;
        font-size: .65rem;
    }

    #event .select2-container {
        z-index: 2050 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        pointer-events: auto; /* Ensure clicks are registered */
        cursor: text;         /* Change cursor to text input style */
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
    $programs = [];

    $query_programs = "SELECT * FROM programs where is_active = 1 AND is_pk = 0";

    $exec_programs = mysqli_query($conn, $query_programs);
    if (mysqli_num_rows($exec_programs) > 0) {
        $programs = mysqli_fetch_all($exec_programs, MYSQLI_ASSOC);    
    }

    $default_start_date = date('Y-m', strtotime('-6 month'));
    $default_end_date = date('Y-m');
    
?>

<div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="col-12">

            <div class="card mb-4">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center text-white" data-bs-toggle="collapse" data-bs-target="#collapseCard">
                   Filter Report
                    <i class="fas fa-chevron-down rotate-icon"></i>
                </div>
                <div id="collapseCard" class="collapse show">
                    <div class="card-body">
                        <form method="POST" action="" id="filterForm">
                            <div class="row mb-2">
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">Start From</label>
                                        <input type="text" class="form-control dateFilter" name="start_date" value="<?= $default_start_date ?>" placeholder="Start Date">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">Expired at</label>
                                        <input type="text" class="form-control dateFilter" name="end_date" value="<?= $default_end_date ?>" placeholder="End Date">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">Program</label>
                                        <select name="program[]" id="program" class="form-control form-control-sm" style="background-color: white; width: 100%;" multiple>
                                            <?php foreach($programs as $program) : ?>
                                                <option value="<?= $program['id'] ?>" selected><?= $program['name'] ?></option>
                                            <?php endforeach; ?>
                                                <option value="9999" selected >PK Reguler</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="dateFilter" class="form-label">Status</label>
                                        <select name="status[]" id="status" class="form-control form-control-sm" style="background-color: white; width: 100%;" multiple>
                                            <option value="0" selected>Draft</option>
                                            <option value="1" selected>Approved</option>
                                            <option value="2" selected>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <div class="d-flex justify-content-end px-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>

                        </form>
                 
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Reports</span>           
                </div>
                <div class="card-body">
                    <div class="" id="report-container"></div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php';?>
<script>

    flatpickr(".dateFilter", {
        dateFormat: "Y-m",
        allowInput: true,
    });

    const element = document.getElementById('program');
    const choices = new Choices(element, {
        searchEnabled: true,
        removeItemButton: true
    });

    const element2 = document.getElementById('status');
    const choices2 = new Choices(element2, {
        searchEnabled: true,
        removeItemButton: true
    });

    document.querySelector('.card-header').addEventListener('click', function () {
        this.classList.toggle('collapsed');
    });

    $(document).ready(function() {
        $('.select2').select2({});

        getReport();

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            getReport();
        });
    });



    function getReport() {
        let selectedPrograms = $('select[name="program[]"]').val();
        let selectedStatus = $('select[name="status[]"]').val();
        let startDate = $('input[name="start_date"]').val();
        let endDate = $('input[name="end_date"]').val();

        $.ajax({
            url: './get-report.php',
            type: 'POST',
            data: {
                selectedPrograms: selectedPrograms,
                selectedStatus: selectedStatus,
                startDate: startDate,
                endDate: endDate
            },
            beforeSend: function() {
                $('#report-container').html('<div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            },
            success: function(response) {
                $('#report-container').html(response)
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.log(xhr);
                console.log(status);
                $('#report-container').html("<div class='alert alert-danger'>Error: " + error + "</div>");
            }
        });
    }

</script>