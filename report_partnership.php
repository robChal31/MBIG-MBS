<?php include 'header.php'; ?>
<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
        font-size: .65rem !important;
    }

    table.dataTable thead th {
        vertical-align: middle !important;
        font-size: .65rem;
    }

    /* ===== FILTER STYLE (SAMA DENGAN BENEFIT PAGE) ===== */
    .filter-wrapper {
        background: #fff;
        border-radius: .375rem;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .75rem;
    }

    .filter-header h6 {
        font-weight: 600;
        margin-bottom: 0;
        font-size: .9rem;
    }

    .filter-header small {
        font-size: .7rem;
    }

    .form-label {
        margin-bottom: 4px;
        font-size: .75rem;
        font-weight: 600;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: .75rem;
        padding: .25rem .5rem;
    }

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

<!-- FILTER -->
<div class="filter-wrapper">
    <div class="filter-header">
        <div>
            <h6 class="fw-semibold mb-0">Filter Report</h6>
            <small class="text-muted">Refine data based on program & date</small>
        </div>
        <button class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="collapse"
                data-bs-target="#filterBody">
            <i class="fa fa-sliders-h me-1"></i> Toggle
        </button>
    </div>

    <div class="collapse show" id="filterBody">
        <form id="filterForm">
            <div class="row g-3 align-items-end">

                <div class="col-md-6 col-6">
                    <label class="form-label">From</label>
                    <input type="text"
                           class="form-control form-control-sm dateFilter"
                           name="start_date"
                           value="<?= $default_start_date ?>">
                </div>

                <div class="col-md-6 col-6">
                    <label class="form-label">To</label>
                    <input type="text"
                           class="form-control form-control-sm dateFilter"
                           name="end_date"
                           value="<?= $default_end_date ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Program</label>
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
                        <button type="button"
                                class="btn btn-outline-secondary btn-xs"
                                id="selectAllProgram">
                            Select All
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary btn-xs"
                                id="clearProgram">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit"
                            class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="fa fa-filter me-1"></i> Filter
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- RESULT -->
<div class="card shadow rounded p-4">
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
    $('.select2').select2({ width: '100%' });

    getReport();

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        getReport();
    });

    $('#selectAllProgram').on('click', function () {
        $('#program option').prop('selected', true);
        $('#program').trigger('change');
    });

    $('#clearProgram').on('click', function () {
        $('#program').val(null).trigger('change');
    });
});

function getReport() {
    $.ajax({
        url: './get-partnership-report.php',
        type: 'POST',
        data: {
            selectedPrograms: $('#program').val(),
            startDate: $('input[name="start_date"]').val(),
            endDate: $('input[name="end_date"]').val()
        },
        beforeSend: function () {
            $('#report-container').html(
                '<div class="text-center d-flex align-items-center justify-content-center" style="height:200px">' +
                '<div class="spinner-border spinner-border-sm"></div></div>'
            );
        },
        success: function (response) {
            $('#report-container').html(response);
        }
    });
}
</script>
