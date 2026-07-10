<?php
include 'db_con.php';
ob_start();
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

set_time_limit(300);
ini_set('max_execution_time', 300);

if (!isset($_SESSION['username'])) { 
    header("Location: ./index.php");
    exit();
}

$role = $_SESSION['role'];

// ========== BUILD QUERY ==========
$query_benefits = "
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
    LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
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
    WHERE db.confirmed = 1 
        AND db.deleted_at IS NULL
";

// Filter untuk mengecualikan yang sudah punya ref
$query_benefits .= " AND NOT EXISTS (SELECT 1 FROM draft_benefit ref WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1)";

$query_benefits .= " GROUP BY dbl.id_benefit_list";
$query_benefits .= " ORDER BY p.no_pk DESC";

// Eksekusi query
$exec_benefits = mysqli_query($conn, $query_benefits);
$benefits = [];

if ($exec_benefits && mysqli_num_rows($exec_benefits) > 0) {
    $benefits = mysqli_fetch_all($exec_benefits, MYSQLI_ASSOC);
}
?>

<div class="container-fluid p-1">
    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table align-middle" id="table_id">
            <thead>
                <tr>
                    <th style="width:4%">No PK</th>
                    <th>Jenis Program</th>
                    <th>School</th>
                    <th>EC Name</th>
                    <th>Benefit</th>
                    <th style="width:6%">Sub Benefit</th>
                    <th>Subject</th>
                    <th>Active From</th>
                    <th>Expired At</th>
                    <th class="text-center">Year 1</th>
                    <th class="text-center">Total Usage Y1</th>
                    <th class="text-center">Year 2</th>
                    <th class="text-center">Total Usage Y2</th>
                    <th class="text-center">Year 3</th>
                    <th class="text-center">Total Usage Y3</th>
                    <th class="text-center" style="width:10%">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($benefits)): ?>
                <tr>
                    <td colspan="16" class="text-center text-muted py-4">
                        <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                        Tidak ada data benefit
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($benefits as $loop => $benefit): ?>
                <?php
                    if (strtolower($benefit['program'] ?? '') === 'cbls3' && ($benefit['prog_year'] ?? 0) == 1) {
                        $benefit['qty2'] = $benefit['qty'] ?? 0;
                        $benefit['qty3'] = $benefit['qty'] ?? 0;
                    }

                    $programe_name = ($benefit['prog_year'] ?? 0) == 1
                        ? ($benefit['program_name'] ?? '-')
                        : (($benefit['program_name'] ?? '-') . " Perubahan Tahun Ke " . ($benefit['prog_year'] ?? ''));

                    $expiredDate = !empty($benefit['expired_at'])
                        ? date('Y-m-d', strtotime($benefit['expired_at']))
                        : null;

                    $is_expired = $expiredDate && date('Y-m-d') > $expiredDate;
                    $has_ref_usage = $benefit['has_ref_usage'] ?? 0;

                    $row_class = $is_expired || $has_ref_usage ? "table-danger" : "";
                ?>
                <tr class="<?= $row_class ?>" title="<?= $is_expired ? 'Benefit Expired' : '' ?>">
                    <td><?= htmlspecialchars($benefit['no_pk'] ?? '-') ?></td>
                    <td>
                        <?= strtoupper(htmlspecialchars($programe_name)) ?>
                        <?= ($benefit['perubahan_tahun'] ?? '') ? " Perubahan Manual Tahun Ke " . htmlspecialchars($benefit['perubahan_tahun']) : '' ?>
                    </td>
                    <td><?= htmlspecialchars($benefit['school_name2'] ?? '-') ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($benefit['generalname'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($benefit['benefit'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($benefit['subbenefit'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($benefit['subject'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($benefit['start_at'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($benefit['expired_at'] ?? '-') ?></td>

                    <td class="text-center"><?= htmlspecialchars($benefit['qty'] ?? 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($benefit['tot_usage1'] ?? 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($benefit['qty2'] ?? 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($benefit['tot_usage2'] ?? 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($benefit['qty3'] ?? 0) ?></td>
                    <td class="text-center"><?= htmlspecialchars($benefit['tot_usage3'] ?? 0) ?></td>

                    <td class="text-center">
                        <div class="dropdown" data-bs-boundary="window">
                            <i class="fas fa-ellipsis-v text-muted"
                            data-bs-toggle="dropdown"
                            style="cursor:pointer"></i>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item"
                                    data-id="<?= $benefit['id_draft'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#pkModal">
                                        <i class="fa fa-eye me-2"></i> Detail
                                    </a>
                                </li>

                                <?php if (($benefit['confirmed'] ?? 0) == 1): ?>
                                    <?php if ((!$is_expired && 
                                        (($_SESSION['role'] === "ec" && ($benefit['redeemable'] ?? 0) == 1) || ($_SESSION['role'] !== "ec" && !$has_ref_usage))) || $_SESSION['role'] !== "ec"
                                    ): ?>
                                        <li>
                                            <a class="dropdown-item text-warning"
                                            data-id="<?= $benefit['id_benefit_list'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#usageModal">
                                                <i class="fa fa-clipboard-list me-2"></i> Usage
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <li>
                                        <a class="dropdown-item text-success"
                                        data-id="<?= $benefit['id_benefit_list'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#historyUsageModal">
                                            <i class="fa fa-history me-2"></i> History Usage
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item text-secondary"
                                        data-id="<?= $benefit['id_benefit_list'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#noteUsageModal">
                                            <i class="fa fa-sticky-note me-2"></i> Note Usage
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); ?>

<script>
$(document).ready(function() {
    $('#table_id').DataTable({
        dom: 'Bfrtilp',
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        order: [[0, 'desc']],
        buttons: [
            { 
                extend: 'copyHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                }
            },
            { 
                extend: 'excelHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                }
            },
            { 
                extend: 'csvHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                }
            },
            { 
                extend: 'pdfHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .6rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                }
            }
        ],
        initComplete: function () {
            $('#table_id_length label').css({
                'display': 'flex',
                'align-items': 'center',
                'gap': '8px',
                'font-size': '.7rem',
                'font-weight': 'bold',
                'margin-left': '20px',
                'margin-top': '8px'
            });

            $('#table_id_length select').css({
                'font-size': '.7rem',
                'font-weight': 'bold',
                'border-radius': '5px',
                'padding': '2px 6px',
                'border': '1px solid #ccc'
            });
        }
    });
});
</script>