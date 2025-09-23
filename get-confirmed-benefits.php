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
        <div class="table-responsive">
            <div class="table-responsive">
                <table class="table" id="table_id">
                    <thead>
                        <tr>
                            <th style="width: 4%">No PK</th>
                            <th>Jenis Program</th>
                            <th>School</th>
                            <th>EC Name</th>
                            <th scope="col">Benefit</th>
                            <th style="width: 4%" scope="col">Sub Benefit</th>
                            <th scope="col">Active From</th>
                            <th scope="col">Expired At</th>
                            <th scope="col">Year 1</th>
                            <th scope="col">Total Usage Year 1</th>
                            <th scope="col">Year 2</th>
                            <th scope="col">Total Usage Year 2</th>
                            <th scope="col">Year 3</th>
                            <th scope="col">Total Usage Year 3</th>
                            <th scope="col" style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach($benefits as $loop => $benefit) {
                                if(strtolower($benefit['program']) == 'cbls3') {
                                    $benefit['qty2'] = $benefit['qty'];
                                    $benefit['qty3'] = $benefit['qty'];
                                }
                                $programe_name = $benefit['prog_year'] == 1 ? $benefit['program'] : ($benefit['program'] . " Perubahan Tahun Ke " . $benefit['prog_year']);
                                $is_expired = (!empty($benefit['expired_at']) && strtotime($benefit['expired_at']) < time())
                        ?>
                                <tr class="<?= $is_expired || $benefit['has_ref_usage'] ? "bg-danger text-white" : (!$query_selected_usage_year && ($benefit['tot_usage1'] > 0 || $benefit['tot_usage2'] > 0 || $benefit['tot_usage3'] > 0) ? 'bg-info text-white' : '') ?>" title="<?= $is_expired ? "Benefit Expired" : '' ?>" >
                                    <td><?= $benefit['no_pk'] ?></td>
                                    <td><?= strtoupper($programe_name) ?> <?= $benefit['perubahan_tahun'] ? " Perubahan Manual Tahun Ke " . $benefit['perubahan_tahun'] : '' ?></td>
                                    <td><?= $benefit['school_name2'] ?></td>
                                    <td><?= $benefit['generalname'] ?></td>
                                    <td><?= $benefit['benefit'] ?></td>
                                    <td><?= $benefit['subbenefit'] ?></td>
                                    <td><?= $benefit['start_at'] ?></td>
                                    <td><?= $benefit['expired_at'] ?></td>
                                    <td class="text-center"><?= $benefit['qty'] ?></td>
                                    <td class="text-center"><?= $benefit['tot_usage1'] ?? 0?></td>
                                    <td class="text-center"><?= $benefit['qty2'] ?></td>
                                    <td class="text-center"><?= $benefit['tot_usage2'] ?? 0?></td>
                                    <td class="text-center"><?= $benefit['qty3'] ?></td>
                                    <td class="text-center"><?= $benefit['tot_usage3'] ?? 0?></td>
                                    <td scope='col' >
                                       <div class="d-flex gap-1">
                                            <span data-id="<?= $benefit['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-eye'></i></span>
                                            
                                            <?php if($benefit['confirmed'] == 1) : ?>
                                                <?php if(($_SESSION['role'] == "ec" && $benefit['redeemable'] == 1) || $_SESSION['role'] != "ec" && !$benefit['has_ref_usage']) : ?>
                                                    <span data-id="<?= $benefit['id_benefit_list'] ?>" data-action='usage' data-bs-toggle='modal' data-bs-target='#usageModal' class='btn btn-outline-warning btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Usage'><i class='fa fa-clipboard-list'></i></span>
                                                <?php endif; ?>

                                                <span data-id="<?= $benefit['id_benefit_list'] ?>" data-action='history' data-bs-toggle='modal' data-bs-target='#historyUsageModal' class='btn btn-outline-success btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='History Usage'><i class='fa fa-history'></i></span>

                                                <span data-id="<?= $benefit['id_benefit_list'] ?>" data-action='note' data-bs-toggle='modal' data-bs-target='#noteUsageModal' class='btn btn-outline-secondary btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Note Usage'><i class='fa fa-sticky-note'></i></span>
                                            <?php endif; ?>
                                       </div>
                                    </td>
                                </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
 
<?php $conn->close();?>
<script>
    $('#table_id').DataTable({
        dom: 'Bfrtip',
        pageLength: 20,
        order: [
            // [0, 'desc'] 
        ],
        buttons: [
            { 
                extend: 'copyHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                }
            },
            { 
                extend: 'excelHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                }
            },
            { 
                extend: 'csvHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                }
            },
            { 
                extend: 'pdfHtml5',
                className: 'btn-custom',
                attr: {
                    style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                }
            }
        ]
    });
</script>


    
    
    