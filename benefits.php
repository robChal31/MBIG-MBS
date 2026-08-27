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

    #table_id {
        font-size: 0.8rem;
    }

    #table_id th,
    #table_id td {
        padding: 0.45rem 0.5rem;
        vertical-align: middle;
        font-size: 0.7rem;
    }

    #table_id .dropdown-menu {
        font-size: 0.8rem;
    }

    #table_id .badge {
        font-size: 0.7rem;
    }

    /* tighter + modern */
    #filterBenefitBody .form-label {
        margin-bottom: 4px;
    }

    #filterBenefitBody .select2-container--default .select2-selection--multiple {
        min-height: 34px;
        font-size: .8rem;
    }

    #filterBenefitBody .select2-selection__choice {
        font-size: .75rem;
    }

    #filterBenefitBody .btn {
        height: 34px;
    }

    .form-label { margin-bottom: 4px; }

    .select2-container--default .select2-selection--multiple {
        min-height: 34px;
        font-size: .8rem;
    }

    .select2-selection__choice {
        font-size: .75rem;
    }

    .btn-xs {
        padding: 2px 8px;
        font-size: .7rem;
    }

    /* ========== DATATABLE PROCESSING ========== */
    .dataTables_processing {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        z-index: 1000 !important;
        background: rgba(255, 255, 255, 0.95) !important;
        padding: 25px 50px !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border: 1px solid #e8e8e8 !important;
        width: auto !important;
        height: auto !important;
    }

    .dataTables_processing .spinner-border {
        width: 2rem;
        height: 2rem;
    }

    /* ========== TABLE CONTAINER ========== */
    .dataTables_wrapper {
        position: relative !important;
        min-height: 200px !important;
    }

    /* ========== FIX BLANK TABLE ========== */
    #table_id {
        width: 100% !important;
    }

    #table_id tbody {
        min-height: 100px;
    }

    /* ========== RESPONSIVE FIX ========== */
    @media (max-width: 768px) {
        .dataTables_processing {
            padding: 15px 25px !important;
            font-size: 0.85rem !important;
        }
        
        .dataTables_processing .spinner-border {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }
    }

</style>


<div class="content">
<?php 
    include 'navbar.php';

    $role = $_SESSION['role'];
    $types = [];

    $filter_sql = $role == 'admin' ? ' GROUP BY br.benefit' : " WHERE br.code = '$role' GROUP BY br.code, br.benefit";
    if($role == 'ec') {
        $query_type = "SELECT GROUP_CONCAT(dtb.id_template_benefit SEPARATOR ',') as id_templates, 
                        dtb.benefit, dtb.redeemable, dtb.subbenefit
                        FROM draft_template_benefit dtb
                        GROUP BY dtb.subbenefit
                        ";
    }else {
        $query_type = "SELECT GROUP_CONCAT(br.id_template SEPARATOR ',') as id_templates, br.benefit, br.code
                        FROM benefit_role br
                        $filter_sql
                        ";
    }
 
    $exec_type = mysqli_query($conn, $query_type);
    if (mysqli_num_rows($exec_type) > 0) {
        $types = mysqli_fetch_all($exec_type, MYSQLI_ASSOC);    
    }
    $matched_ec_default_benefits = ['Kolektif', 'MBMTA', 'FGB', 'RBMG'];
?>
    <div class="container-fluid p-4">
        <div class="col-12">
            <div class="card rounded shadow-sm p-3 mb-4">
                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0">Filter Benefit</h6>
                        <small class="text-muted">Refine data based on benefit type</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filterBenefitBody">
                        <i class="fa fa-sliders-h me-1"></i> Toggle
                    </button>
                </div>

                <!-- BODY -->
                <div class="collapse" id="filterBenefitBody">
                    <div class="row g-3 align-items-end">

                        <!-- BENEFIT TYPE -->
                        <div class="col-12 <?= $role == 'ec' ? 'd-none' : '' ?>">
                            <label class="form-label small fw-semibold">Benefit Type</label>

                            <select class="form-select form-select-sm select2" id="benefitType" name="type[]" multiple>
                                <?php foreach($types as $type) : ?>
                                    <option value="<?= $type['id_templates'] ?>" selected>
                                        <?= $type['benefit'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- helper buttons -->
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-outline-secondary btn-xs" id="selectAllBenefit">
                                    Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-xs" id="clearAllBenefit">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- USAGE YEAR (NO SELECT ALL) -->
                        <div class="col-md-6 col-12">
                            <label class="form-label small fw-semibold">Usage Year</label>
                            <select class="form-select form-select-sm select2 w-100" id="usageYear" name="usage_year[]" multiple>
                                <option value="1">Year 1</option>
                                <option value="2">Year 2</option>
                                <option value="3">Year 3</option>
                            </select>
                        </div>

                        <!-- ACTION -->
                        <div class="col-md-6 col-12 d-flex justify-content-md-end align-items-end">
                            <button class="btn btn-primary btn-sm px-4 fw-semibold" id="filter-btn">
                                <i class="fa fa-filter me-1"></i> Apply Filter
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="card shadow rounded h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-0">Benefits</h5>
                        <small class="text-muted">Manage benefit usage, history, and details</small>
                    </div>
                </div>                     
                <div class="" id="benefits-container" style="min-height: 150px"></div>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="noteUsageModal" tabindex="-1" role="dialog" aria-labelledby="noteUsageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteUsageModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="noteUsageModalBody">
                    Loading...
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
    let group = '';
    let subject = '';
    let event_group_code = '';
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

    $('#noteUsageModal').on('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        let action = event.relatedTarget.getAttribute('data-action');
        $('#noteUsageModalBody').html('');
        $('#noteUsageModalLabel').html("Note Benefit Usage");
        $.ajax({
            url: 'input-benefit-note.php',
            type: 'POST',
            data: {
                id_benefit_list : rowid,
            },
            success: function(data) {
                $('#noteUsageModalBody').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error); 
            }
        });
    })

    $('#selectAllBenefit').on('click', function () {
        $('#benefitType option').prop('selected', true);
        $('#benefitType').trigger('change');
    });

    $('#clearAllBenefit').on('click', function () {
        $('#benefitType').val(null).trigger('change');
    });

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
        getBenefit();

        $('#filter-btn').click(function() {
            getBenefit();
        })
    });

    // function getBenefit() {
    //     let selectedType = $('select[name="type[]"]').val();
    //     let usage_year = $('select[name="usage_year[]"]').val();

    //     $.ajax({
    //         url: './get-confirmed-benefits.php',
    //         type: 'POST',
    //         data: {
    //             types: selectedType,
    //             usage_year : usage_year
    //         },
    //         beforeSend: function() {
    //             $('#benefits-container').html('<div class="text-center" style="height: 200px; display: flex; align-items: center; justify-content: center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>')
    //         },
    //         success: function(response) {
    //             $('#benefits-container').html(response)
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error:', error);
    //             $('#benefits-container').html("<div class='alert alert-danger'>Error: " + error + "</div>");
    //         }
    //     });
    // }

    function getBenefit() {
        let selectedType = $('select[name="type[]"]').val();
        let usage_year = $('select[name="usage_year[]"]').val();

        // 🔥 Destroy existing DataTable
        if ($.fn.DataTable.isDataTable('#table_id')) {
            $('#table_id').DataTable().destroy();
            $('#table_id').empty();
        }

        // 🔥 Show loading state di container
        $('#benefits-container').html(`
            <div class="text-center py-5" id="loadingIndicator">
                <p class="mt-2 text-muted">Memuat data benefit...</p>
            </div>
            <table class="table align-middle w-100 d-none" id="table_id">
                <thead>
                    <tr>
                        <th>No PK</th>
                        <th>Jenis Program</th>
                        <th>School</th>
                        <th>EC Name</th>
                        <th>Benefit</th>
                        <th>Sub Benefit</th>
                        <th>Subject</th>
                        <th>Active From</th>
                        <th>Expired At</th>
                        <th class="text-center">Year 1</th>
                        <th class="text-center">Total Usage Y1</th>
                        <th class="text-center">Year 2</th>
                        <th class="text-center">Total Usage Y2</th>
                        <th class="text-center">Year 3</th>
                        <th class="text-center">Total Usage Y3</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        `);

        // 🔥 Init DataTable
        let table = $('#table_id').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Bfrtilp',
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            order: [[8, 'desc']],
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
            // 🔥 Language dengan processing indicator
            language: {
                processing: `
                    <div class="d-flex align-items-center justify-content-center py-4">
                        <span class="text-muted">Memuat data...</span>
                    </div>
                `,
                emptyTable: "Tidak ada data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                lengthMenu: "Tampilkan _MENU_ data",
                search: "Cari:",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            ajax: {
                url: './get-confirmed-benefits-ajax.php',
                type: 'POST',
                data: {
                    types: selectedType,
                    usage_year: usage_year
                },
                // 🔥 Before send: show loading
                beforeSend: function() {
                    $('#benefits-container .dataTables_processing').show();
                },
                // 🔥 Complete: hide loading
                complete: function() {
                    $('#benefits-container .dataTables_processing').hide();
                }
            },
            columns: [
                { data: 'no_pk' },
                { data: 'program_name' },
                { data: 'school_name' },
                { data: 'ec_name' },
                { data: 'benefit' },
                { data: 'subbenefit' },
                { data: 'subject' },
                { data: 'start_at' },
                { data: 'expired_at' },
                { data: 'qty', className: 'text-center' },
                { data: 'tot_usage1', className: 'text-center' },
                { data: 'qty2', className: 'text-center' },
                { data: 'tot_usage2', className: 'text-center' },
                { data: 'qty3', className: 'text-center' },
                { data: 'tot_usage3', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false }
            ],
            // 🔥 Draw callback: hide loading after draw
            drawCallback: function() {
                $('#benefits-container .dataTables_processing').hide();
                // Show table
                $('#table_id').removeClass('d-none');
                $('#loadingIndicator').addClass('d-none');
            },
            // 🔥 Init complete
            initComplete: function() {
                // Styling
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

                // Hide loading indicator after init
                $('#loadingIndicator').addClass('d-none');
                $('#table_id').removeClass('d-none');
            }
        });

        // 🔥 Force table to show
        $('#table_id').removeClass('d-none');
        $('#loadingIndicator').addClass('d-none');
    }


    $(document).on('click', '.close', function() {
        $('#approvalModal').modal('hide');
        $('#pkModal').modal('hide');
        $('#usageModal').modal('hide');
        $('#historyUsageModal').modal('hide');
        $('#noteUsageModal').modal('hide');
    });
</script>