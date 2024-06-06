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
