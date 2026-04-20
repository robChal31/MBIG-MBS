<?php
include 'db_con.php';
ob_start();
session_start();

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

set_time_limit(300);
ini_set('max_execution_time', 300);

// if (!isset($_SESSION['username'])) { 
//     header("Location: ./index.php");
//     exit();
// }

$role = $_SESSION['role'];
$types = isset($_POST['types']) ? $_POST['types'] : [];
$usage_year = isset($_POST['usage_year']) ? $_POST['usage_year'] : [];

// DataTable parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 20;
$searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
$orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';

// Column mapping
$columnMap = [
    0 => 'p.no_pk',
    1 => 'program_name',
    2 => 'school_name2',
    3 => 'ec.generalname',
    4 => 'dbl.benefit_name',
    5 => 'dbl.subbenefit',
    6 => 'dtb.subject',
    7 => 'p.start_at',
    8 => 'p.expired_at',
];

$orderBy = isset($columnMap[$orderColumn]) ? $columnMap[$orderColumn] : 'p.no_pk';
$orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

// ========== TEMPORARY TABLE UNTUK FILTER ID (IN CLAUSE) ==========
$temp_table_created = false;
if (!empty($types)) {
    // Gabungkan semua ID
    $all_ids = [];
    foreach ($types as $type) {
        $ids = explode(',', $type);
        $all_ids = array_merge($all_ids, $ids);
    }
    $all_ids = array_unique(array_map('intval', $all_ids));
    
    if (!empty($all_ids)) {
        // Buat temporary table
        $temp_table = "temp_benefit_ids_" . md5(uniqid());
        mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS $temp_table");
        mysqli_query($conn, "CREATE TEMPORARY TABLE $temp_table (id_template INT PRIMARY KEY)");
        
        // Insert ID ke temporary table (batch insert)
        $chunks = array_chunk($all_ids, 100);
        foreach ($chunks as $chunk) {
            $values = "(" . implode("),(", $chunk) . ")";
            mysqli_query($conn, "INSERT IGNORE INTO $temp_table VALUES $values");
        }
        $temp_table_created = true;
    }
}

// ========== BUILD WHERE CONDITIONS ==========
$where_conditions = [
    "db.confirmed = 1",
    "db.deleted_at IS NULL",
    "NOT EXISTS (SELECT 1 FROM draft_benefit ref WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1)"
];

if ($role == 'ec' && isset($_SESSION['id_user'])) {
    $where_conditions[] = "db.id_ec = " . intval($_SESSION['id_user']);
}

// Filter pakai temporary table (jauh lebih cepat dari IN clause)
if ($temp_table_created) {
    $where_conditions[] = "EXISTS (SELECT 1 FROM $temp_table tmp WHERE tmp.id_template = dbl.id_template)";
}

// Search condition
if (!empty($searchValue)) {
    $search = mysqli_real_escape_string($conn, $searchValue);
    $search_conditions = [
        "p.no_pk LIKE '%$search%'",
        "prog.name LIKE '%$search%'",
        "IFNULL(sc.name, db.school_name) LIKE '%$search%'",
        "ec.generalname LIKE '%$search%'",
        "dbl.benefit_name LIKE '%$search%'",
        "dbl.subbenefit LIKE '%$search%'",
        "dtb.subject LIKE '%$search%'"
    ];
    $where_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// ========== QUERY FOR TOTAL COUNT ==========
$count_query = "
    SELECT COUNT(DISTINCT dbl.id_benefit_list) as total
    FROM draft_benefit db
    INNER JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
    LEFT JOIN pk p ON p.benefit_id = db.id_draft
    LEFT JOIN schools sc ON sc.id = db.school_name
    LEFT JOIN user ec ON ec.id_user = db.id_ec
    LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)
    LEFT JOIN draft_template_benefit dtb ON dtb.id_template_benefit = dbl.id_template
    $where_clause
";

$count_result = mysqli_query($conn, $count_query);
$total_records = 0;
if ($count_result) {
    $row = mysqli_fetch_assoc($count_result);
    $total_records = $row['total'];
}

// ========== MAIN QUERY WITH PAGINATION ==========
$query = "
    SELECT 
        db.id_draft,
        db.year as prog_year,
        db.school_name,
        db.program,
        db.confirmed,
        dbl.id_benefit_list,
        dbl.benefit_name as benefit,
        dbl.subbenefit,
        dbl.pelaksanaan,
        dbl.description,
        dbl.qty,
        dbl.qty2,
        dbl.qty3,
        p.no_pk,
        p.start_at,
        p.expired_at,
        p.perubahan_tahun,
        dtb.redeemable,
        dtb.subject,
        ec.generalname,
        IFNULL(sc.name, db.school_name) AS school_name2,
        prog.name as program_name,
        COALESCE(bu.tot_usage1, 0) as tot_usage1,
        COALESCE(bu.tot_usage2, 0) as tot_usage2,
        COALESCE(bu.tot_usage3, 0) as tot_usage3,
        CASE 
            WHEN ref_count.ref_count > 0 THEN 1 
            ELSE 0 
        END AS has_ref_usage
    FROM draft_benefit db
    INNER JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
    LEFT JOIN (
        SELECT 
            id_benefit_list,
            SUM(COALESCE(qty1, 0)) AS tot_usage1,
            SUM(COALESCE(qty2, 0)) AS tot_usage2,
            SUM(COALESCE(qty3, 0)) AS tot_usage3
        FROM benefit_usages 
        GROUP BY id_benefit_list
    ) bu ON bu.id_benefit_list = dbl.id_benefit_list
    LEFT JOIN draft_template_benefit dtb ON dtb.id_template_benefit = dbl.id_template
    LEFT JOIN pk p ON p.benefit_id = db.id_draft
    LEFT JOIN schools sc ON sc.id = db.school_name
    LEFT JOIN user ec ON ec.id_user = db.id_ec
    LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)
    LEFT JOIN (
        SELECT ref.ref_id, COUNT(*) as ref_count
        FROM draft_benefit ref
        WHERE ref.confirmed = 1
        GROUP BY ref.ref_id
    ) ref_count ON ref_count.ref_id = db.id_draft
    $where_clause
    GROUP BY dbl.id_benefit_list
";

// Apply usage year filter in HAVING
if (!empty($usage_year)) {
    $usage_conditions = [];
    foreach ($usage_year as $value) {
        $usage_conditions[] = "COALESCE(bu.tot_usage$value, 0) > 0";
    }
    $query .= " HAVING " . implode(" OR ", $usage_conditions);
    
    // Re-count with usage filter
    $count_with_usage = "SELECT COUNT(*) as total FROM ($query) as filtered";
    $count_usage_result = mysqli_query($conn, $count_with_usage);
    if ($count_usage_result) {
        $row = mysqli_fetch_assoc($count_usage_result);
        $total_records = $row['total'];
    }
}

$query .= " ORDER BY $orderBy $orderDir LIMIT $start, $length";

$exec_benefits = mysqli_query($conn, $query);
$benefits = [];
if ($exec_benefits && mysqli_num_rows($exec_benefits) > 0) {
    $benefits = mysqli_fetch_all($exec_benefits, MYSQLI_ASSOC);
}

// ========== CLEAN UP TEMPORARY TABLE ==========
if ($temp_table_created) {
    mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS $temp_table");
}

// ========== FORMAT DATA FOR DATATABLE ==========
$data = [];
foreach ($benefits as $benefit) {
    if (strtolower($benefit['program'] ?? '') === 'cbls3' && ($benefit['prog_year'] ?? 0) == 1) {
        $benefit['qty2'] = $benefit['qty'] ?? 0;
        $benefit['qty3'] = $benefit['qty'] ?? 0;
    }

    $programe_name = ($benefit['prog_year'] ?? 0) == 1
        ? ($benefit['program_name'] ?? '-')
        : (($benefit['program_name'] ?? '-') . " Perubahan Tahun Ke " . ($benefit['prog_year'] ?? ''));

    $expiredTime = !empty($benefit['expired_at']) ? strtotime($benefit['expired_at']) : null;
    $is_expired = ($expiredTime && $expiredTime < time());
    $has_ref_usage = $benefit['has_ref_usage'] ?? 0;

    $row_class = '';
    if ($is_expired || $has_ref_usage) {
        $row_class = 'table-danger';
    } elseif (empty($usage_year) && (($benefit['tot_usage1'] ?? 0) > 0 || ($benefit['tot_usage2'] ?? 0) > 0 || ($benefit['tot_usage3'] ?? 0) > 0)) {
        $row_class = 'table-info';
    }

    $program_display = strtoupper($programe_name);
    if (!empty($benefit['perubahan_tahun'])) {
        $program_display .= " Perubahan Manual Tahun Ke " . htmlspecialchars($benefit['perubahan_tahun']);
    }

    $action_buttons = '
        <div class="dropdown" data-bs-boundary="window">
            <i class="fas fa-ellipsis-v text-muted" data-bs-toggle="dropdown" style="cursor:pointer"></i>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a class="dropdown-item" data-id="' . $benefit['id_draft'] . '" data-bs-toggle="modal" data-bs-target="#pkModal">
                        <i class="fa fa-eye me-2"></i> Detail
                    </a>
                </li>';

    if (($benefit['confirmed'] ?? 0) == 1) {
        if ((!$is_expired && (($_SESSION['role'] === "ec" && ($benefit['redeemable'] ?? 0) == 1) || ($_SESSION['role'] !== "ec" && !$has_ref_usage))) || $_SESSION['role'] !== "ec") {
            $action_buttons .= '
                <li>
                    <a class="dropdown-item text-warning" data-id="' . $benefit['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#usageModal">
                        <i class="fa fa-clipboard-list me-2"></i> Usage
                    </a>
                </li>';
        }
        $action_buttons .= '
                <li>
                    <a class="dropdown-item text-success" data-id="' . $benefit['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#historyUsageModal">
                        <i class="fa fa-history me-2"></i> History Usage
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-secondary" data-id="' . $benefit['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#noteUsageModal">
                        <i class="fa fa-sticky-note me-2"></i> Note Usage
                    </a>
                </li>';
    }
    $action_buttons .= '
            </ul>
        </div>';

    $data[] = [
        'no_pk' => htmlspecialchars($benefit['no_pk'] ?? '-'),
        'program' => $program_display,
        'school' => htmlspecialchars($benefit['school_name2'] ?? '-'),
        'ec_name' => htmlspecialchars($benefit['generalname'] ?? '-'),
        'benefit' => htmlspecialchars($benefit['benefit'] ?? '-'),
        'subbenefit' => htmlspecialchars($benefit['subbenefit'] ?? '-'),
        'subject' => htmlspecialchars($benefit['subject'] ?? '-'),
        'start_at' => htmlspecialchars($benefit['start_at'] ?? '-'),
        'expired_at' => htmlspecialchars($benefit['expired_at'] ?? '-'),
        'qty' => $benefit['qty'] ?? 0,
        'tot_usage1' => $benefit['tot_usage1'] ?? 0,
        'qty2' => $benefit['qty2'] ?? 0,
        'tot_usage2' => $benefit['tot_usage2'] ?? 0,
        'qty3' => $benefit['qty3'] ?? 0,
        'tot_usage3' => $benefit['tot_usage3'] ?? 0,
        'action' => $action_buttons,
        'row_class' => $row_class,
    ];
}

// Output JSON for DataTables
$response = [
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $total_records,
    'data' => $data
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>