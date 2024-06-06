<?php include 'header.php'; ?>
<style>
  table.dataTable tbody td {
      vertical-align: middle !important;
      font-size: .75rem;
  }

  table.dataTable thead th {
      vertical-align: middle !important;
      font-size: .75rem;
  }
</style>
<?php
    $role = $_SESSION['role'];
    $types = [];

    $filter_sql = $role == 'admin' ? '' : "WHERE br.code = '$role'";
    $query_type = "SELECT GROUP_CONCAT(br.id_template SEPARATOR ',') as id_templates, br.benefit, br.code
                    FROM benefit_role br
                    $filter_sql
                    GROUP BY br.code, br.benefit;";
    
    $exec_type = mysqli_query($conn, $query_type);
    if (mysqli_num_rows($exec_type) > 0) {
        $types = mysqli_fetch_all($exec_type, MYSQLI_ASSOC);    
    }
    
?>

<div class="content">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid p-4">
        <div class="col-12">

            <div class="bg-white rounded h-100 p-4 mb-4">
                <h6 style="display: inline-block; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Filter Benefit</h6>
                <div class="row justify-content-center align-items-end">
                    <div class="col-6">
                        <label for="type">Benefit Type</label>
                       
                    </div>
                    <div class="col-6">
                        <button class="btn btn-primary" id="filter-btn"><i class="fa fa-filter"></i> Filter</button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded h-100 p-4">
                <h6 class="mb-4">Benefits</h6>                      
                <div class="" id="benefits-container"></div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Modal -->
<?php include 'footer.php';?>
