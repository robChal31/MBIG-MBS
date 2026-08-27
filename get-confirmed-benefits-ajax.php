<?php
include 'db_con.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) { 
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
$types = isset($_POST['types']) ? $_POST['types'] : [];
$usage_year = isset($_POST['usage_year']) ? $_POST['usage_year'] : [];

// ========== DATATABLE PARAMETERS ==========
$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 20);
$search = $_POST['search']['value'] ?? '';
$order_column = $_POST['order'][0]['column'] ?? 7;
$order_dir = $_POST['order'][0]['dir'] ?? 'DESC';

$columns = ['no_pk', 'program_name', 'school_name', 'ec_name', 'benefit', 'subbenefit', 'subject', 'start_at', 'expired_at', 'qty', 'tot_usage1', 'qty2', 'tot_usage2', 'qty3', 'tot_usage3'];
$order_by = $columns[$order_column] ?? 'start_at';

// ========== TEMPORARY TABLE ==========
if (!empty($types)) {
    $all_ids = [];
    foreach ($types as $type) {
        $ids = explode(',', $type);
        $all_ids = array_merge($all_ids, $ids);
    }
    $all_ids = array_unique($all_ids);
    
    $temp_table = "temp_benefit_ids_" . md5(session_id());
    mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS $temp_table");
    mysqli_query($conn, "CREATE TEMPORARY TABLE $temp_table (id_template INT PRIMARY KEY)");
    
    if (!empty($all_ids)) {
        $chunks = array_chunk($all_ids, 100);
        foreach ($chunks as $chunk) {
            mysqli_query($conn, "INSERT IGNORE INTO $temp_table (id_template) VALUES (" . implode("),(", $chunk) . ")");
        }
    }
}

// ========== BASE QUERY (TANPA HAVING) ==========
$base_query = "
    FROM draft_benefit db
    LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
    LEFT JOIN v_benefit_usage_sum bu ON bu.id_benefit_list = dbl.id_benefit_list
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
    WHERE db.confirmed = 1 
        AND db.deleted_at IS NULL
";

// Filter ID template
if (!empty($types) && isset($temp_table)) {
    $base_query .= " AND EXISTS (SELECT 1 FROM $temp_table tmp WHERE tmp.id_template = dbl.id_template)";
}

// Filter role EC
if ($role == 'ec' && isset($_SESSION['id_user'])) {
    $base_query .= " AND db.id_ec = " . intval($_SESSION['id_user']);
}

// Filter exclude ref
$base_query .= " AND NOT EXISTS (SELECT 1 FROM draft_benefit ref WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1)";

$base_query .= " GROUP BY dbl.id_benefit_list";

// ========== COUNT TOTAL (TANPA HAVING) ==========
$count_query = "SELECT COUNT(*) as total FROM (SELECT dbl.id_benefit_list $base_query) as subquery";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'] ?? 0;

// ========== BUILD SEARCH CONDITION ==========
$search_condition = "";
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $search_condition = "
        p.no_pk LIKE '%$search_escaped%' OR
        prog.name LIKE '%$search_escaped%' OR
        school_name LIKE '%$search_escaped%' OR
        ec.generalname LIKE '%$search_escaped%' OR
        dbl.benefit_name LIKE '%$search_escaped%' OR
        dbl.subbenefit LIKE '%$search_escaped%' OR
        dtb.subject LIKE '%$search_escaped%'
    ";
}

// ========== BUILD FILTERED COUNT QUERY (DENGAN HAVING + SEARCH) ==========
$filtered_query = "
    SELECT COUNT(*) as total 
    FROM (
        SELECT 
            dbl.id_benefit_list,
            p.no_pk,
            prog.name as program_name,
            sc.name as school_name,
            ec.generalname as ec_name,
            dbl.benefit_name as benefit,
            dbl.subbenefit,
            dtb.subject,
            COALESCE(bu.tot_usage1, 0) as tot_usage1,
            COALESCE(bu.tot_usage2, 0) as tot_usage2,
            COALESCE(bu.tot_usage3, 0) as tot_usage3
        FROM draft_benefit db
        LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
        LEFT JOIN v_benefit_usage_sum bu ON bu.id_benefit_list = dbl.id_benefit_list
        LEFT JOIN draft_template_benefit dtb ON dtb.id_template_benefit = dbl.id_template
        LEFT JOIN pk p ON p.benefit_id = db.id_draft
        LEFT JOIN schools sc ON sc.id = db.school_name
        LEFT JOIN user ec ON ec.id_user = db.id_ec
        LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)
        WHERE db.confirmed = 1 
            AND db.deleted_at IS NULL
";

// Filter ID template
if (!empty($types) && isset($temp_table)) {
    $filtered_query .= " AND EXISTS (SELECT 1 FROM $temp_table tmp WHERE tmp.id_template = dbl.id_template)";
}

// Filter role EC
if ($role == 'ec' && isset($_SESSION['id_user'])) {
    $filtered_query .= " AND db.id_ec = " . intval($_SESSION['id_user']);
}

// Filter exclude ref
$filtered_query .= " AND NOT EXISTS (SELECT 1 FROM draft_benefit ref WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1)";

$filtered_query .= " GROUP BY dbl.id_benefit_list";

// 🔥 HAVING untuk usage year
if (!empty($usage_year)) {
    $usage_conditions = [];
    foreach ($usage_year as $value) {
        $usage_conditions[] = "COALESCE(tot_usage$value, 0) > 0";
    }
    $filtered_query .= " HAVING " . implode(" OR ", $usage_conditions);
}

// 🔥 HAVING untuk SEARCH
if (!empty($search_condition)) {
    $filtered_query .= " HAVING ($search_condition)";
}

$filtered_query .= ") as subquery";

// Eksekusi filtered count
$filtered_result = mysqli_query($conn, $filtered_query);

if (!$filtered_result) {
    echo json_encode(['error' => 'Filtered count query failed: ' . mysqli_error($conn)]);
    exit();
}

$records_filtered = mysqli_fetch_assoc($filtered_result)['total'] ?? 0;

// ========== BUILD DATA QUERY ==========
$data_query = "
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
        ec.generalname as ec_name,
        IFNULL(sc.name, db.school_name) AS school_name,
        prog.name as program_name,
        COALESCE(bu.tot_usage1, 0) as tot_usage1,
        COALESCE(bu.tot_usage2, 0) as tot_usage2,
        COALESCE(bu.tot_usage3, 0) as tot_usage3,
        CASE 
            WHEN ref_count.ref_count > 0 THEN 1 
            ELSE 0 
        END AS has_ref_usage
    FROM draft_benefit db
    LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
    LEFT JOIN v_benefit_usage_sum bu ON bu.id_benefit_list = dbl.id_benefit_list
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
    WHERE db.confirmed = 1 
        AND db.deleted_at IS NULL
";

// Filter ID template
if (!empty($types) && isset($temp_table)) {
    $data_query .= " AND EXISTS (SELECT 1 FROM $temp_table tmp WHERE tmp.id_template = dbl.id_template)";
}

// Filter role EC
if ($role == 'ec' && isset($_SESSION['id_user'])) {
    $data_query .= " AND db.id_ec = " . intval($_SESSION['id_user']);
}

// Filter exclude ref
$data_query .= " AND NOT EXISTS (SELECT 1 FROM draft_benefit ref WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1)";

$data_query .= " GROUP BY dbl.id_benefit_list";

// 🔥 HAVING untuk usage year
if (!empty($usage_year)) {
    $usage_conditions = [];
    foreach ($usage_year as $value) {
        $usage_conditions[] = "COALESCE(tot_usage$value, 0) > 0";
    }
    $data_query .= " HAVING " . implode(" OR ", $usage_conditions);
}

// 🔥 HAVING untuk SEARCH
if (!empty($search_condition)) {
    $data_query .= " HAVING ($search_condition)";
}

// ========== ORDER & LIMIT ==========
$data_query .= " ORDER BY $order_by $order_dir LIMIT $start, $length";

// Eksekusi data query
$result = mysqli_query($conn, $data_query);

if (!$result) {
    echo json_encode(['error' => 'Data query failed: ' . mysqli_error($conn)]);
    exit();
}

// ========== BUILD DATA ==========
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Process program name for CBLS3
    if (strtolower($row['program'] ?? '') === 'cbls3' && ($row['prog_year'] ?? 0) == 1) {
        $row['qty2'] = $row['qty'] ?? 0;
        $row['qty3'] = $row['qty'] ?? 0;
    }

    $program_name = ($row['prog_year'] ?? 0) == 1
        ? ($row['program_name'] ?? '-')
        : (($row['program_name'] ?? '-') . " Perubahan Tahun Ke " . ($row['prog_year'] ?? ''));

    // Check expired
    $expiredDate = !empty($row['expired_at']) ? date('Y-m-d', strtotime($row['expired_at'])) : null;
    $is_expired = $expiredDate && date('Y-m-d') > $expiredDate;
    $has_ref_usage = $row['has_ref_usage'] ?? 0;

    // Row class
    $row_class = '';
    if ($is_expired || $has_ref_usage) {
        $row_class = 'table-danger';
    } elseif (empty($usage_year) && (($row['tot_usage1'] ?? 0) > 0 || ($row['tot_usage2'] ?? 0) > 0 || ($row['tot_usage3'] ?? 0) > 0)) {
        $row_class = 'table-info';
    }

    // Build action dropdown
    $action = '
    <div class="dropdown" data-bs-boundary="window">
        <i class="fas fa-ellipsis-v text-muted" data-bs-toggle="dropdown" style="cursor:pointer"></i>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item" data-id="' . $row['id_draft'] . '" data-bs-toggle="modal" data-bs-target="#pkModal">
                    <i class="fa fa-eye me-2"></i> Detail
                </a>
            </li>';

    if (($row['confirmed'] ?? 0) == 1) {
        if ((!$is_expired && 
            (($_SESSION['role'] === "ec" && ($row['redeemable'] ?? 0) == 1) || ($_SESSION['role'] !== "ec" && !$has_ref_usage))) || $_SESSION['role'] !== "ec") {
            $action .= '
            <li>
                <a class="dropdown-item text-warning" data-id="' . $row['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#usageModal">
                    <i class="fa fa-clipboard-list me-2"></i> Usage
                </a>
            </li>';
        }
        $action .= '
            <li>
                <a class="dropdown-item text-success" data-id="' . $row['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#historyUsageModal">
                    <i class="fa fa-history me-2"></i> History Usage
                </a>
            </li>
            <li>
                <a class="dropdown-item text-secondary" data-id="' . $row['id_benefit_list'] . '" data-bs-toggle="modal" data-bs-target="#noteUsageModal">
                    <i class="fa fa-sticky-note me-2"></i> Note Usage
                </a>
            </li>';
    }

    $action .= '
        </ul>
    </div>';

    $data[] = [
        'no_pk' => htmlspecialchars($row['no_pk'] ?? '-'),
        'program_name' => htmlspecialchars($program_name),
        'school_name' => htmlspecialchars($row['school_name'] ?? '-'),
        'ec_name' => htmlspecialchars($row['ec_name'] ?? '-'),
        'benefit' => htmlspecialchars($row['benefit'] ?? '-'),
        'subbenefit' => htmlspecialchars($row['subbenefit'] ?? '-'),
        'subject' => htmlspecialchars($row['subject'] ?? '-'),
        'start_at' => htmlspecialchars($row['start_at'] ?? '-'),
        'expired_at' => htmlspecialchars($row['expired_at'] ?? '-'),
        'qty' => intval($row['qty'] ?? 0),
        'tot_usage1' => intval($row['tot_usage1'] ?? 0),
        'qty2' => intval($row['qty2'] ?? 0),
        'tot_usage2' => intval($row['tot_usage2'] ?? 0),
        'qty3' => intval($row['qty3'] ?? 0),
        'tot_usage3' => intval($row['tot_usage3'] ?? 0),
        'action' => $action,
        'row_class' => $row_class
    ];
}

// Clean up
if (isset($temp_table)) {
    mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS $temp_table");
}

// ========== RESPONSE ==========
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total_records,
    'recordsFiltered' => $records_filtered,
    'data' => $data
]);