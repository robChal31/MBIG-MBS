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

    .filter-card {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .filter-header h6 {
        font-weight: 600;
        margin-bottom: 0;
    }

    .filter-header small {
        color: #6c757d;
    }

    .btn-xs {
        padding: .15rem .4rem;
        font-size: .75rem;
        line-height: 1.2;
    }

    .rotate-icon {
        transition: transform .3s ease;
    }

    .collapse.show + .rotate-icon {
        transform: rotate(180deg);
    }

    .form-label { margin-bottom: 4px; }

    .select2-container--default .select2-selection--multiple {
        min-height: 34px;
        font-size: .8rem;
    }

    .select2-selection__choice {
        font-size: .75rem;
    }

    .btn-xs {
        padding: 2px 8px;
        font-size: .7rem;
    }
</style>

<?php
    $programs = [];
    $query_programs = "SELECT * FROM programs WHERE is_active = 1";
    $exec_programs = mysqli_query($conn, $query_programs);
    if (mysqli_num_rows($exec_programs) > 0) {
        $programs = mysqli_fetch_all($exec_programs, MYSQLI_ASSOC);
    }

    $default_start_date = date('Y-m-d', strtotime('-6 month'));
    $default_end_date   = date('Y-m-d');
?>

<div class="content">
<?php include 'navbar.php'; ?>

<div class="container-fluid p-4">
<div class="col-12">

<div class="filter-card">
    <div class="filter-header">
        <div>
            <h6>Filter Report</h6>
            <small>Refine report data</small>
        </div>
        <button class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="collapse"
                data-bs-target="#filterReportBody">
            <i class="fa fa-sliders-h me-1"></i> Toggle
        </button>
    </div>

    <div class="collapse show" id="filterReportBody">
        <form id="filterForm">
            <div class="row g-3 align-items-end">

                <div class="col-md-6 col-12">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="text"
                           class="form-control form-control-sm dateFilter"
                           name="start_date"
                           value="<?= $default_start_date ?>">
                </div>

                <div class="col-md-6 col-12">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="text"
                           class="form-control form-control-sm dateFilter"
                           name="end_date"
                           value="<?= $default_end_date ?>">
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold">Program</label>
                    <select name="program[]"
                            id="program"
                            class="form-select form-select-sm select2"
                            multiple>
                        <?php foreach($programs as $program): ?>
                            <option value="<?= $program['id'] ?>" selected>
                                <?= $program['name'] ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="9999" selected>PK Reguler</option>
                    </select>

                    <div class="d-flex gap-2 mt-1">
                        <button type="button" class="btn btn-outline-secondary btn-xs" id="selectAllProgram">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-xs" id="clearAllProgram">Clear</button>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="fa fa-filter me-1"></i> Apply Filter
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="card rounded h-100 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">Report Data</h5>
            <small class="text-muted">Manage benefit usage, history, and details</small>
        </div>
    </div>
    <div id="report-container"></div>
</div>

</div>
</div>

<?php include 'footer.php'; ?>

<script>
flatpickr(".dateFilter", {
    dateFormat: "Y-m-d",
    allowInput: true
});

$(document).ready(function () {

    $('.select2').select2({
        width: '100%'
    });

    $('#selectAllProgram').on('click', function () {
        $('#program option').prop('selected', true);
        $('#program').trigger('change');
    });

    $('#clearAllProgram').on('click', function () {
        $('#program option').prop('selected', false);
        $('#program').trigger('change');
    });

    getReport();

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        getReport();
    });
});

function getReport() {
    $.ajax({
        url: './get-usage-report.php',
        type: 'POST',
        data: {
            selectedPrograms: $('#program').val(),
            startDate: $('input[name="start_date"]').val(),
            endDate: $('input[name="end_date"]').val()
        },
        beforeSend: function () {
            $('#report-container').html(
                '<div class="text-center d-flex align-items-center justify-content-center" style="height:200px">' +
                '<div class="spinner-border"></div></div>'
            );
        },
        success: function (response) {
            $('#report-container').html(response);
        },
        error: function (xhr, status, error) {
            $('#report-container').html('<div class="alert alert-danger">'+error+'</div>');
        }
    });
}
</script>


