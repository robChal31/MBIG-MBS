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

    $role                       = $_SESSION['role'];
    $types                      = ISSET($_POST['types']) ? $_POST['types'] : [];
    $usage_year                 = ISSET($_POST['usage_year']) ? $_POST['usage_year'] : [];
    $selected_type              = implode(",", $types);
    $query_selected_usage_year  = "";
    
    foreach ($usage_year as $key => $value) {
        $query_selected_usage_year .= $key == 0 ? " WHERE tab.tot_usage$value > 0" : " OR tab.tot_usage$value > 0";
    }

    $benefits               = [];
    $query_selected_type    = $selected_type ? " AND dbl.id_template IN ($selected_type)" : "";
    $query_role             = $role == 'ec' ? " AND db.id_ec = $_SESSION[id_user]" : "";  

    $query_benefits = "SELECT * 
                        FROM (
                            SELECT 
                                db.*, db.year as prog_year, dbl.id_benefit_list, dbl.benefit_name as benefit, dbl.subbenefit, dbl.pelaksanaan, dbl.description, dbl.qty, dbl.qty2, dbl.qty3, p.no_pk, p.start_at, p.expired_at, dtb.redeemable, ec.generalname,
                                IFNULL(sc.name, db.school_name) AS school_name2, p.perubahan_tahun,
                                bu.tot_usage1,
                                bu.tot_usage2,
                                bu.tot_usage3,
                                CASE 
                                    WHEN EXISTS (
                                        SELECT 1 
                                        FROM draft_benefit AS ref 
                                        WHERE ref.ref_id = db.id_draft
                                        AND ref.confirmed = 1
                                    ) THEN 1 
                                    ELSE 0 
                                END AS has_ref_usage
                            FROM draft_benefit db
                            LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
                            LEFT JOIN (
                                SELECT 
                                    SUM(COALESCE(bu.qty1, 0)) AS tot_usage1, 
                                    SUM(COALESCE(bu.qty2, 0)) AS tot_usage2, 
                                    SUM(COALESCE(bu.qty3, 0)) AS tot_usage3, 
                                    bu.id_benefit_list AS id_bl 
                                FROM benefit_usages bu 
                                GROUP BY bu.id_benefit_list
                            ) AS bu ON bu.id_bl = dbl.id_benefit_list
                            LEFT JOIN draft_template_benefit dtb ON dtb.id_template_benefit = dbl.id_template
                            LEFT JOIN pk p ON p.benefit_id = db.id_draft
                            LEFT JOIN schools sc ON sc.id = db.school_name
                            LEFT JOIN user ec ON ec.id_user = db.id_ec
                            WHERE db.confirmed = 1
                            $query_selected_type
                            $query_role 
                            AND NOT EXISTS (
                                SELECT 1 FROM draft_benefit ref 
                                WHERE ref.ref_id = db.id_draft AND ref.confirmed = 1
                            )
                        ) AS tab $query_selected_usage_year;";

    $exec_benefits = mysqli_query($conn, $query_benefits);
    // var_dump($query_benefits);
    if (mysqli_num_rows($exec_benefits) > 0) {
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
            <?php foreach ($benefits as $loop => $benefit): ?>
                <?php
                    if (strtolower($benefit['program']) === 'cbls3' && $benefit['prog_year'] == 1) {
                        $benefit['qty2'] = $benefit['qty'];
                        $benefit['qty3'] = $benefit['qty'];
                    }

                    $programe_name = $benefit['prog_year'] == 1
                        ? $benefit['program']
                        : ($benefit['program'] . " Perubahan Tahun Ke " . $benefit['prog_year']);

                    $expiredTime = !empty($benefit['expired_at'])
                        ? strtotime($benefit['expired_at'])
                        : null;

                    // expired asli
                    $is_expired = ($expiredTime && $expiredTime < time());

                    $is_grace_expired = ($expiredTime && time() > strtotime('+6 months', $expiredTime));

                    $row_class = $is_expired || $benefit['has_ref_usage']
                        ? "table-danger"
                        : (
                            !$query_selected_usage_year &&
                            ($benefit['tot_usage1'] > 0 || $benefit['tot_usage2'] > 0 || $benefit['tot_usage3'] > 0)
                            ? "table-info"
                            : ""
                        );
                ?>
                <tr class="<?= $row_class ?>" title="<?= $is_expired ? 'Benefit Expired' : '' ?>">
                    <td><?= $benefit['no_pk'] ?></td>
                    <td>
                        <?= strtoupper($programe_name) ?>
                        <?= $benefit['perubahan_tahun'] ? " Perubahan Manual Tahun Ke ".$benefit['perubahan_tahun'] : '' ?>
                    </td>
                    <td><?= $benefit['school_name2'] ?></td>
                    <td class="fw-semibold"><?= $benefit['generalname'] ?></td>
                    <td><?= $benefit['benefit'] ?></td>
                    <td><?= $benefit['subbenefit'] ?></td>
                    <td><?= $benefit['start_at'] ?></td>
                    <td><?= $benefit['expired_at'] ?></td>

                    <td class="text-center"><?= $benefit['qty'] ?></td>
                    <td class="text-center"><?= $benefit['tot_usage1'] ?? 0 ?></td>
                    <td class="text-center"><?= $benefit['qty2'] ?></td>
                    <td class="text-center"><?= $benefit['tot_usage2'] ?? 0 ?></td>
                    <td class="text-center"><?= $benefit['qty3'] ?></td>
                    <td class="text-center"><?= $benefit['tot_usage3'] ?? 0 ?></td>

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

                                <?php if ($benefit['confirmed'] == 1): ?>
                                    <?php if (!$is_expired && 
                                    (($_SESSION['role'] === "ec" && $benefit['redeemable'] == 1) || 
                                    ($_SESSION['role'] !== "ec" && !$benefit['has_ref_usage']))): ?>
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
            </tbody>
        </table>
    </div>

</div>

 
<?php $conn->close();?>
<script>
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
</script>


    
    
    