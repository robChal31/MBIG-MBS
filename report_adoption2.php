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

    .rotate-icon {
        transition: transform 0.3s ease;
    }

    .collapse.show ~ .rotate-icon {
        transform: rotate(180deg);
    }
    
    .stats-card {
        border-radius: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .stats-icon {
        position: absolute;
        right: 15px;
        bottom: 10px;
        font-size: 3rem;
        opacity: 0.2;
    }
    
    .currency {
        text-align: right !important;
    }
    
    .info-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        padding: 15px;
        color: white;
        margin-bottom: 20px;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.5s ease;
    }
    
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
    }
    
    .dt-buttons {
        margin-bottom: 15px;
    }
    
    .dt-button {
        margin-right: 5px;
        padding: 5px 10px !important;
        font-size: 0.7rem !important;
    }
</style>

<?php
    $default_start_date = '2024-01-01';
    $default_end_date = date('Y-m-d');
    
    // Ambil EC user dengan role EC dan is_active = 1
    $ec_users = [];
    $query_ec = "SELECT id_user, generalname FROM user WHERE role = 'ec' AND is_active = 1 ORDER BY generalname";
    $exec_ec = mysqli_query($conn, $query_ec);
    if ($exec_ec && mysqli_num_rows($exec_ec) > 0) {
        while($ec = mysqli_fetch_assoc($exec_ec)) {
            $ec_users[] = $ec;
        }
    }
?>

<div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="col-12">
            
            <!-- Info Banner -->
            <div class="info-box mb-4 fade-in">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><i class="fas fa-chart-line me-2"></i>Adoption Report Dashboard</h4>
                        <p class="mb-0 opacity-75">Comprehensive report of adoption benefits with detailed analytics</p>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-calendar-alt me-1"></i> Period: Since 2024
                        </span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section shadow-sm fade-in">
                <form method="POST" action="" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i> Start Date
                            </label>
                            <input type="date" class="form-control" name="start_date" id="start_date" value="<?= $default_start_date ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i> End Date
                            </label>
                            <input type="date" class="form-control" name="end_date" id="end_date" value="<?= $default_end_date ?>">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user-tie me-1 text-primary"></i> EC Name (Multiple)
                            </label>
                            <select name="id_ec[]" id="id_ec" class="form-control select2" multiple>
                                <option value="">-- All EC --</option>
                                <?php foreach($ec_users as $ec) : ?>
                                    <option value="<?= $ec['id_user'] ?>"><?= htmlspecialchars($ec['generalname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Show Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Container -->
            <div id="report-container">
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chart-line fa-3x mb-3 opacity-25"></i>
                    <p>Select date range and click "Show Report" to display data</p>
                </div>
            </div>

        </div>
    </div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        getReport();
    });

    $('#id_ec').select2({
        placeholder: "Select EC (optional)",
        allowClear: true,
        width: '100%'
    });
});

function getReport() {
    let idEc = $('#id_ec').val();
    let startDate = $('#start_date').val();
    let endDate = $('#end_date').val();
    
    if (!startDate) {
        alert('Please select start date');
        return;
    }
    
    $.ajax({
        url: './get-report-adoption2.php',
        type: 'POST',
        data: {
            id_ec: idEc,
            start_date: startDate,
            end_date: endDate
        },
        beforeSend: function() {
            $('#report-container').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading data...</p></div>');
        },
        success: function(html) {
            $('#report-container').html(html);
        },
        error: function(xhr, status, error) {
            $('#report-container').html("<div class='alert alert-danger'>Error: " + error + "</div>");
        }
    });
}
</script>