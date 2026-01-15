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

    #event .select2-container {
        z-index: 2050 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
    pointer-events: auto; /* Ensure clicks are registered */
    cursor: text;         /* Change cursor to text input style */
}

</style>

<div class="content">
    <?php include 'navbar.php'; ?>
<?php
    $role = $_SESSION['role'];
    $types = [];

    $filter_sql = $role == 'admin' ? 'GROUP BY br.benefit' : "WHERE br.code = '$role' GROUP BY br.code, br.benefit";
    $query_type = "SELECT GROUP_CONCAT(br.id_template SEPARATOR ',') as id_templates, br.benefit, br.code
                    FROM benefit_role br
                    $filter_sql
                    ";
    
    $exec_type = mysqli_query($conn, $query_type);
    if (mysqli_num_rows($exec_type) > 0) {
        $types = mysqli_fetch_all($exec_type, MYSQLI_ASSOC);    
    }
    
?>

    <div class="container-fluid p-4">
        <div class="col-12">

            <div class="card rounded h-100 p-4 mb-4">
                <h6 style="display: inline-block; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Filter Benefit</h6>
                <div class="row">
                    <div class="col-md-5 col-12 mb-4">
                        <label for="type">Benefit Type</label>
                        <select class="form-select form-select-sm select2" name="type[]" aria-label="Default select example" multiple>
                            <?php foreach($types as $type) : ?>
                                <option value="<?= $type['id_templates'] ?>" selected><?= $type['benefit'] ?></option>
                            <?php endforeach; ?>
                        </select>
                       
                    </div>
                    <div class="col-md-5 col-12 mb-4">
                        <label for="type">Usage Year</label>
                        <select class="form-select form-select-sm select2" name="usage_year[]" aria-label="Default select example" multiple>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                        </select>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-end">  
                            <button class="btn btn-primary" id="filter-btn"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card rounded h-100 p-4">
                <h6 class="mb-4">Benefits</h6>                      
                <div class="" id="benefits-container"></div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Modal -->

    <div class="modal fade" id="pkModal" tabindex="-1" role="dialog" aria-labelledby="pkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pkModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pkModalBody">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id='detail-benefit'>See Details</a>
                </div>
            </div> 
        </div>
    </div>

    <div class="modal fade" id="historyUsageModal" tabindex="-1" role="dialog" aria-labelledby="historyUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyUsageModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="historyUsageModalBody">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                </div>
            </div> 
        </div>
    </div>

    <div class="modal fade" id="usageModal" tabindex="-1" role="dialog" aria-labelledby="usageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usageModalLabel">Usage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="usageModalBody">
                    Loading...
                </div>
                
            </div> 
        </div>
    </div>
<?php include 'footer.php';?>
<script>

    $('#pkModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');
        $('#pkModalBody').html('');
        $('#pkModalLabel').html("Detail PK");
        $.ajax({
            url: 'detail-pk.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#pkModalBody').html(data);
                $('#detail-benefit').attr('href', 'detail-benefit.php?id=' + rowid);
            }
        });
    })

    $('#usageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');
        $('#usageModalBody').html('');

        $('#usageModalLabel').html("Add Benefit Usage");
        $.ajax({
            url: 'input-usage.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
            },
            success: function(data) {
                $('#usageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    });

    $('#historyUsageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');
        $('#historyUsageModalBody').html('');
        $('#historyUsageModalLabel').html("History Benefit Usage");
        $.ajax({
            url: 'history-usage.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
            },
            success: function(data) {
                $('#historyUsageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    })

    $(document).ready(function() {
        $('.select2').select2({});
        getBenefit();

        $('#filter-btn').click(function() {
            getBenefit();
        })
    });

    function getBenefit() {
        let selectedType = $('select[name="type[]"]').val();
        let usage_year = $('select[name="usage_year[]"]').val();
        $.ajax({
            url: './get-confirmed-benefits.php',
            type: 'POST',
            data: {
                types: selectedType,
                usage_year : usage_year
            },
            beforeSend: function() {
                $('#benefits-container').html('<div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>')
            },
            success: function(response) {
                $('#benefits-container').html(response)
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#benefits-container').html("<div class='alert alert-danger'>Error: " + error + "</div>");
            }
        });
    }

    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
        $('#usageModal').modal('hide');
        $('#historyUsageModal').modal('hide');
    });
</script>