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

    $role = $_SESSION['role'];
    $types = $_POST['types'];
    $selected_type = implode(",", $types);
    $benefits = [];

    $query_benefits = "SELECT *, IFNULL(sc.name, db.school_name) as school_name2
                        FROM draft_benefit db 
                        LEFT JOIN draft_benefit_list dbl on db.id_draft = dbl.id_draft
                        LEFT JOIN (
                            SELECT 
                                SUM(COALESCE(bu.qty1, 0)) AS tot_usage1,
                                SUM(COALESCE(bu.qty2, 0)) AS tot_usage2,
                                SUM(COALESCE(bu.qty3, 0)) AS tot_usage3,
                                bu.id_benefit_list as id_bl
                            FROM benefit_usages bu
                            GROUP BY bu.id_benefit_list
                        ) as bu on bu.id_bl = dbl.id_benefit_list
                        LEFT JOIN draft_template_benefit dtb on dtb.id_template_benefit = dbl.id_template 
                        LEFT JOIN pk p on p.benefit_id = db.id_draft
                        LEFT JOIN schools as sc on sc.id = db.school_name
                        WHERE db.confirmed = 1
                        AND dbl.id_template IN ($selected_type);
                        ";

    $exec_benefits = mysqli_query($conn, $query_benefits);
    if (mysqli_num_rows($exec_benefits) > 0) {
        $benefits = mysqli_fetch_all($exec_benefits, MYSQLI_ASSOC);    
    }
?>

<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
      font-size: .7rem;
  }

  table.dataTable thead th {
      vertical-align: middle !important;
      font-size: .7rem;
  }
</style>

    <div class="container-fluid p-4">
        <div class="col-12">                 
            <div class="table-responsive">
                <div class="table-responsive">
                    <table class="table" id="table_id">
                        <thead>
                            <tr>
                                <th>No PK</th>
                                <th scope="col">Benefit</th>
                                <th scope="col">Sub Benefit</th>
                                <th scope="col" style="width: 15%">Description</th>
                                <th scope="col">Implementation</th>
                                <th scope="col">Year 1</th>
                                <th scope="col">Total Usage Year 1</th>
                                <th scope="col">Year 2</th>
                                <th scope="col">Total Usage Year 2</th>
                                <th scope="col">Year 3</th>
                                <th scope="col">Total Usage Year 3</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach($benefits as $benefit) {
                                    $status_class = $benefit['verified'] == 1 ? 'bg-success' :  'bg-primary';
                                    $status_msg = ($benefit['verified'] == 1 ? 'Verified' : 'Waiting Verification');
                            ?>
                                    <tr>
                                        <td><?= $benefit['no_pk'] ?></td>
                                        <td><?= $benefit['benefit'] ?></td>
                                        <td><?= $benefit['subbenefit'] ?></td>
                                        <td><?= $benefit['description'] ?></td>
                                        <td><?= $benefit['pelaksanaan'] ?></td>
                                        <td><?= $benefit['qty'] ?></td>
                                        <td><?= $benefit['tot_usage1'] ?? 0?></td>
                                        <td><?= $benefit['qty2'] ?></td>
                                        <td><?= $benefit['tot_usage2'] ?? 0?></td>
                                        <td><?= $benefit['qty3'] ?></td>
                                        <td><?= $benefit['tot_usage3'] ?? 0?></td>
                                        <td scope='col'>
                                            <span data-id="<?= $benefit['id_draft'] ?>" data-action='create' data-bs-toggle='modal' data-bs-target='#pkModal' class='btn btn-outline-primary btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Detail'><i class='fa fa-eye'></i></span>
                                            
                                            <span data-id="<?= $benefit['id_benefit_list'] ?>" data-action='usage' data-bs-toggle='modal' data-bs-target='#usageModal' class='btn btn-outline-warning btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='Usage'><i class='fa fa-clipboard-list'></i></span>
                                            
                                            <span data-id="<?= $benefit['id_benefit_list'] ?>" data-action='history' data-bs-toggle='modal' data-bs-target='#historyUsageModal' class='btn btn-outline-success btn-sm me-1 mb-1' style='font-size: .75rem' data-toggle='tooltip' title='History Usage'><i class='fa fa-history'></i></span>
                                        </td>
                                    </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        </div>
    </div>
 
<?php $conn->close();?>
<script>
    $('#table_id').DataTable({
        dom: 'Bfrtip',
        pageLength: 20,
        order: [
            [0, 'desc'] 
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


    
    
    