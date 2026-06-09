<?php
include 'db_con.php';
ob_start();
session_start();

if (!isset($_SESSION['username'])){ 
    header("Location: ./index.php");
    exit();
}

$id_ec = isset($_POST['id_ec']) ? $_POST['id_ec'] : '';
$start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : '2024-01-01';
$end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : date('Y-m-d');

// Build EC filter untuk multiple select
$ec_filter = "";
if (!empty($id_ec) && is_array($id_ec)) {
    $ec_ids = array_map('intval', $id_ec);
    $ec_filter = " AND db.id_ec IN ('" . implode("','", $ec_ids) . "')";
} elseif (!empty($id_ec) && !is_array($id_ec)) {
    $ec_filter = " AND db.id_ec = '$id_ec'";
} else {
    $ec_filter = "";
}

// Main Query
$query = "SELECT 
    db.id_draft, 
    u.generalname, 
    db.year, 
    IFNULL(sc.name, db.school_name) as school_name, 
    prog.name as program, 
    db.date, 
    p.no_pk, 
    p.start_at,
    COALESCE(calc.total_qty_x_harga, 0) as total_qty_x_harga,
    COALESCE(calc.total_setelah_diskon, 0) as total_setelah_diskon
FROM `draft_benefit` as db 
LEFT JOIN user as u on u.id_user = db.id_ec
LEFT JOIN schools as sc on sc.id = db.school_name
LEFT JOIN programs as prog on (prog.code = db.program or prog.name = db.program)
LEFT JOIN pk as p on p.benefit_id = db.id_draft
LEFT JOIN (
    SELECT 
        id_draft,
        SUM(qty * usulan_harga) as total_qty_x_harga,
        SUM(qty * usulan_harga * (1 - discount/100)) as total_setelah_diskon
    FROM calc_table
    GROUP BY id_draft
) as calc on calc.id_draft = db.id_draft
WHERE db.confirmed = 1 
    AND db.deleted_at IS NULL
    AND p.start_at >= '$start_date' 
    AND p.start_at <= '$end_date'
    $ec_filter
ORDER BY p.start_at DESC, db.year DESC";

$result = mysqli_query($conn, $query);
$data = [];
$total_qty_price = 0;
$total_after_discount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    $total_qty_price += $row['total_qty_x_harga'];
    $total_after_discount += $row['total_setelah_diskon'];
}

// Get monthly trend data
$monthly_query = "SELECT 
    DATE_FORMAT(p.start_at, '%Y-%m') as month,
    SUM(COALESCE(calc.total_setelah_diskon, 0)) as total_value
FROM draft_benefit db
LEFT JOIN pk p ON p.benefit_id = db.id_draft
LEFT JOIN (
    SELECT id_draft, SUM(qty * usulan_harga * (1 - discount/100)) as total_setelah_diskon
    FROM calc_table GROUP BY id_draft
) calc ON calc.id_draft = db.id_draft
WHERE db.confirmed = 1 AND db.deleted_at IS NULL
    AND p.start_at >= '$start_date' AND p.start_at <= '$end_date'
    $ec_filter
GROUP BY DATE_FORMAT(p.start_at, '%Y-%m')
ORDER BY month ASC";

$monthly_result = mysqli_query($conn, $monthly_query);
$monthly_labels = [];
$monthly_values = [];
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_values[] = (float)$row['total_value'];
}

// Get top 5 schools data
$school_query = "SELECT 
    IFNULL(sc.name, db.school_name) as school_name,
    SUM(COALESCE(calc.total_setelah_diskon, 0)) as total_value
FROM draft_benefit db
LEFT JOIN schools sc ON sc.id = db.school_name
LEFT JOIN pk p ON p.benefit_id = db.id_draft
LEFT JOIN (
    SELECT id_draft, SUM(qty * usulan_harga * (1 - discount/100)) as total_setelah_diskon
    FROM calc_table GROUP BY id_draft
) calc ON calc.id_draft = db.id_draft
WHERE db.confirmed = 1 AND db.deleted_at IS NULL
    AND p.start_at >= '$start_date' AND p.start_at <= '$end_date'
    $ec_filter
GROUP BY school_name
ORDER BY total_value DESC
LIMIT 5";

$school_result = mysqli_query($conn, $school_query);
$school_labels = [];
$school_values = [];
while ($row = mysqli_fetch_assoc($school_result)) {
    $short_name = strlen($row['school_name']) > 20 ? substr($row['school_name'], 0, 18) . '...' : $row['school_name'];
    $school_labels[] = $short_name;
    $school_values[] = (float)$row['total_value'];
}

$total_draft = count($data);
$total_qty_price_formatted = 'Rp ' . number_format($total_qty_price, 0, ',', '.');
$total_after_discount_formatted = 'Rp ' . number_format($total_after_discount, 0, ',', '.');
$total_discount_formatted = 'Rp ' . number_format($total_qty_price - $total_after_discount, 0, ',', '.');
?>

<?php if (count($data) > 0): ?>

<!-- Statistics Cards Row -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1">Total PK Draft</h6>
                <h2 class="mb-0"><?= $total_draft ?></h2>
                <i class="fas fa-file-invoice stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1">Total Qty × Harga</h6>
                <h4 class="mb-0"><?= $total_qty_price_formatted ?></h4>
                <i class="fas fa-calculator stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1">After Discount</h6>
                <h4 class="mb-0"><?= $total_after_discount_formatted ?></h4>
                <i class="fas fa-tags stats-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <h6 class="mb-1">Total Discount</h6>
                <h4 class="mb-0"><?= $total_discount_formatted ?></h4>
                <i class="fas fa-percent stats-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart Row -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Monthly Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Top 5 Schools</h6>
            </div>
            <div class="card-body">
                <canvas id="schoolChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Report Table -->
<div class="card shadow-sm fade-in">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold text-primary"><i class="fas fa-table me-2"></i>Adoption Report Details</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="table_data" style="font-size: 0.7rem;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. PK</th>
                        <th>Nama EC</th>
                        <th>Nama Sekolah</th>
                        <th>Program</th>
                        <th>Year</th>
                        <th>PK Date</th>
                        <th>Start At</th>
                        <th class="currency">Qty × Harga</th>
                        <th class="currency">After Diskon</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td style="width: 10%;"><?= htmlspecialchars($row['no_pk'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['generalname'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['school_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['program'] ?? '-') ?></td>
                        <td class="text-center"><?= $row['year'] ?? '-' ?></td>
                        <td><?= $row['date'] ? date('d M Y', strtotime($row['date'])) : '-' ?></td>
                        <td><?= $row['start_at'] ? date('d M Y', strtotime($row['start_at'])) : '-' ?></td>
                        <td class="currency text-end"><?= number_format($row['total_qty_x_harga'], 0, ',', '.') ?></td>
                        <td class="currency text-end"><?= number_format($row['total_setelah_diskon'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- <tr class="table-secondary fw-bold">
                        <td colspan="8" class="text-end">TOTAL</td>
                        <td class="currency text-end"><?= number_format($total_qty_price, 0, ',', '.') ?></td>
                        <td class="currency text-end"><?= number_format($total_after_discount, 0, ',', '.') ?></td>
                    </tr> -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Inisialisasi Chart
const monthlyLabels = <?= json_encode($monthly_labels) ?>;
const monthlyValues = <?= json_encode($monthly_values) ?>;
const schoolLabels = <?= json_encode($school_labels) ?>;
const schoolValues = <?= json_encode($school_values) ?>;

// Monthly Chart
const ctx1 = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Total Value (After Discount)',
            data: monthlyValues,
            borderColor: '#4facfe',
            backgroundColor: 'rgba(79, 172, 254, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { 
                callbacks: { 
                    label: function(context) { 
                        return 'Rp ' + context.raw.toLocaleString('id-ID'); 
                    } 
                } 
            }
        },
        scales: { 
            y: { 
                beginAtZero: true, 
                ticks: { 
                    callback: function(value) { 
                        return 'Rp ' + value.toLocaleString('id-ID'); 
                    } 
                } 
            } 
        }
    }
});

// School Chart
const ctx2 = document.getElementById('schoolChart').getContext('2d');
const backgroundColors = ['#4facfe', '#43e97b', '#fa709a', '#f093fb', '#f5576c'];
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: schoolLabels,
        datasets: [{
            label: 'Total Value',
            data: schoolValues,
            backgroundColor: backgroundColors.slice(0, schoolLabels.length),
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { 
                callbacks: { 
                    label: function(context) { 
                        return 'Rp ' + context.raw.toLocaleString('id-ID'); 
                    } 
                } 
            }
        },
        scales: { 
            y: { 
                beginAtZero: true, 
                ticks: { 
                    callback: function(value) { 
                        return 'Rp ' + value.toLocaleString('id-ID'); 
                    } 
                } 
            } 
        }
    }
});

// Inisialisasi DataTable
setTimeout(function() {
    if ($.fn.DataTable.isDataTable('#table_data')) {
        $('#table_data').DataTable().destroy();
    }
    
    $('#table_data').DataTable({
        dom: 'Bfrtilp',
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        order: [[7, 'desc']],
        buttons: [
            { 
                extend: 'copyHtml5',
                className: 'btn-custom',
                attr: { style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;' }
            },
            { 
                extend: 'excelHtml5',
                className: 'btn-custom',
                attr: { style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' }
            },
            { 
                extend: 'csvHtml5',
                className: 'btn-custom',
                attr: { style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;' }
            },
            { 
                extend: 'pdfHtml5',
                className: 'btn-custom',
                attr: { style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;' }
            }
        ]
    });
}, 150);
</script>

<?php else: ?>
<div class="alert alert-info text-center py-5">
    <i class="fas fa-info-circle fa-3x mb-3"></i>
    <h5>No Data Found</h5>
    <p>No adoption report found for the selected period.</p>
</div>
<?php endif; ?>