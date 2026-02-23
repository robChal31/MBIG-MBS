<?php
session_start();
include 'db_con.php';

$id_benefit_list = isset($_POST['id_benefit_list']) ? (int)$_POST['id_benefit_list'] : 0;

if ($id_benefit_list <= 0) {
    echo '<div class="alert alert-danger">Invalid Benefit ID</div>';
    exit;
}

/* ================================
   1. GET ID DRAFT
================================ */
$get_id_draft = "SELECT id_draft 
                 FROM draft_benefit_list 
                 WHERE id_benefit_list = $id_benefit_list";

$id_draft_exc = mysqli_query($conn, $get_id_draft);

if (!$id_draft_exc || mysqli_num_rows($id_draft_exc) == 0) {
    echo '<div class="alert alert-danger">No draft found</div>';
    exit;
}

$row_draft = mysqli_fetch_assoc($id_draft_exc);
$id_draft = (int)$row_draft['id_draft'];

/* ================================
   2. GET PK + CHECK EXPIRED
================================ */
$get_pk = "SELECT expired_at 
           FROM pk 
           WHERE benefit_id = $id_draft";

$pk_exc = mysqli_query($conn, $get_pk);

if (!$pk_exc || mysqli_num_rows($pk_exc) == 0) {
    echo '<div class="alert alert-danger">PK data not found</div>';
    exit;
}

$row_pk = mysqli_fetch_assoc($pk_exc);
$expired_at = $row_pk['expired_at'];

if (!$expired_at) {
    echo '<div class="alert alert-danger">Expired date not found</div>';
    exit;
}

$expiredDate = new DateTime($expired_at);
$limitDate   = clone $expiredDate;
$limitDate->modify('+6 months');
$now = new DateTime();

if ($now > $expiredDate) {
    echo '<div class="alert alert-danger">Benefit sudah melewati expired date</div>';
    exit;
}

/* ================================
   LANJUT QUERY UTAMA
================================ */
$role = $_SESSION['role'];                                                            
$subbenefit = '';
$sql = "SELECT
            dbl.*, dtb.redeemable, db.ref_id, db.year, db.program,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM draft_benefit AS ref 
                    WHERE ref.ref_id = db.id_draft
                    AND ref.confirmed = 1
                ) THEN 1 
                ELSE 0 
            END AS has_ref_usage,
            SUM(COALESCE(bu.qty1, 0)) AS tot_usage1,
            SUM(COALESCE(bu.qty2, 0)) AS tot_usage2,
            SUM(COALESCE(bu.qty3, 0)) AS tot_usage3
        FROM draft_benefit_list AS dbl
        LEFT JOIN draft_benefit AS db on db.id_draft = dbl.id_draft
        LEFT JOIN benefit_usages AS bu ON dbl.id_benefit_list = bu.id_benefit_list
        LEFT JOIN draft_template_benefit dtb on dtb.id_template_benefit = dbl.id_template 
        WHERE dbl.id_benefit_list = $id_benefit_list";

$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo '<div class="alert alert-danger">Something went wrong</div>';
    exit;
}

$usages = mysqli_fetch_assoc($result);

$subbenefit = $usages['subbenefit'] ? trim($usages['subbenefit']) : '';
$subbenefit_q = "SELECT * FROM subbenefits WHERE name = '$subbenefit'";
$subBQ_result = $conn->query($subbenefit_q);
$subbenefit_data = $subBQ_result->fetch_assoc();
$group = $subbenefit_data ? trim($subbenefit_data['group']) : '';

if(strtolower($usages['program']) == 'cbls3' && $usages['year'] == 1) {
    $usages['qty2'] = $usages['qty'];
    $usages['qty3'] = $usages['qty'];
}

?>

    <div class="p-2">
        <h6>Benefit Usage</h6>
        <form action="save-usage.php" method="POST" enctype="multipart/form-data" id="form-usage">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Used At</label>
                    <input type="date" name="used_at" class="form-control form-control-sm" value="" required>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <?php if($usages['redeemable'] == 1) { ?>
                        <textarea name="description" id="description_usage" class="form-control" style="height: 150px;">
Kelas/Sesi:
Nama Peserta: </textarea>
                    <?php }else { ?>
                        <textarea name="description" id="description_usage" class="form-control" style="height: 150px;"></textarea>
                    <?php } ?>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Year</label>
                    <select name="year" id="year" class="form-control form-control-sm select2" style="background-color: white;" required>

                    <?php
                        $yearOptions = [
                            'qty1' => [
                                'label' => 'Year 1',
                                'max'   => $usages['qty'],
                                'used'  => $usages['tot_usage1']
                            ],
                            'qty2' => [
                                'label' => 'Year 2',
                                'max'   => $usages['qty2'],
                                'used'  => $usages['tot_usage2']
                            ],
                            'qty3' => [
                                'label' => 'Year 3',
                                'max'   => $usages['qty3'],
                                'used'  => $usages['tot_usage3']
                            ],
                        ];

                        foreach ($yearOptions as $key => $year) {

                            $remain = (int)$year['max'] - (int)$year['used'];
                            $isDisabled = ($year['max'] == 0 || $remain <= 0);

                            $text = $year['label'];

                            if ($year['max'] == 0) {
                                $text .= ' (Not Available)';
                            } elseif ($remain <= 0) {
                                $text .= ' (Quota Full)';
                            } else {
                                $text .= ' (Remaining: ' . $remain . ')';
                            }

                            echo '<option value="'.$key.'" '
                                .($isDisabled ? 'disabled style="color:#999;"' : '')
                                .'>'.$text.'</option>';
                        }
                    ?>

                    </select>

                    <small class="text-muted">
                        Year yang bertuliskan <b>Not Available</b> atau <b>Quota Full</b> tidak bisa dipilih.
                    </small>

                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="qty" id="quantity" class="form-control form-control-sm" value="" required>
                </div>

                <?php 
                    if($usages['redeemable'] == 1) { ?>
                        <div class="col-md-12 col-12 mb-3">
                            <label class="form-label d-block">Events</label>
                            <select name="event" id="event" class="form-control form-control-sm select2" style="background-color: white; width: 100%;" required>
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Ticket ID</label>
                            <input type="text" name="id_ticket" id="id_ticket" class="form-control form-control-sm" value="" readonly required>
                        </div>   
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Diskon</label>
                            <input type="number" name="diskon" id="diskon" class="form-control form-control-sm" value="" required>
                        </div>              
                                  
                <?php } ?>
               

                <input type="hidden" name="id_benefit_list" value="<?= $id_benefit_list ?>">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_usage" <?= $usages['redeemable'] == 1 ? 'disabled' : '' ?>>Save</button>
            </div>
        </form>
    </div>

<script>
    group = '<?= $group ?>';
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        let quantity = {
            qty1: {
                max : <?= isset($usages['qty']) ? $usages['qty'] : 0 ?>,
                used : <?= isset($usages['tot_usage1']) ? $usages['tot_usage1'] : 0 ?>,
                remain : <?= isset($usages['qty']) ? $usages['qty'] - $usages['tot_usage1'] : 0 ?>
            },
            qty2: {
                max : <?= isset($usages['qty2']) ? $usages['qty2'] : 0 ?>,
                used : <?= isset($usages['tot_usage2']) ? $usages['tot_usage2'] : 0 ?>,
                remain : <?= isset($usages['qty2']) ? $usages['qty2'] - $usages['tot_usage2'] : 0 ?>
            },
            qty3: {
                max : <?= isset($usages['qty3']) ? $usages['qty3'] : 0 ?>,
                used : <?= isset($usages['tot_usage3']) ? $usages['tot_usage3'] : 0 ?>,
                remain : <?= isset($usages['qty3']) ? $usages['qty3'] - $usages['tot_usage3'] : 0 ?>
            }
        };

        $('#quantity').on('input', function() {
            var value = $(this).val();
            var isValid = /^\d+$/.test(value) && parseInt(value) > 0;

            let yearValue = $('#year').val();

            let maxQty = quantity[yearValue]['remain'];

            if (!isValid || value.includes(',')) {
                $(this).val('');
            }

            if(value > maxQty) {
                $(this).val(maxQty);
                alert('Quantity cannot be more than ' + maxQty);
            }
        });

        $('#diskon').on('input', function() {
            var value = parseInt($(this).val(), 10);
            if (value > 100) {
                $(this).val(100);
                alert('Diskon cannot be more than 100%');
            } else if (value < 0) {
                $(this).val(0);
                alert('Diskon cannot be less than 0%');
            }
        });

        $('#form-usage').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-usage.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_usage').prop('disabled', true);
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Please wait while we save your data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                },
                success: function(response) {
                    console.log('response: ', response);
                    Swal.close();
                    if(response.status) {
                        Swal.fire({
                            title: "Saved!",
                            text: response.message,
                            icon: "success"
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }else {
                        Swal.fire({
                            title: "Failed!",
                            text: response.message,
                            icon: "error"
                        })
                    }
                    $('#submit_usage').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.log('error', error);
                    console.log('xhr', xhr);
                    console.log('status', status);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_usage').prop('disabled', false);
                    $('#usageModal').modal('hide');
                }
            });
        });

        let redeemable = <?= $usages['redeemable'] ?>

        if(redeemable == 1 && group) {
            console.log(`https://hadiryuk.id/api/EventBenefit?type=${group}`);
            $.ajax({
                url: `https://hadiryuk.id/api/EventBenefit?type=${group}`, 
                method: 'GET',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_usage').prop('disabled', true);
                },
                success: function(response) {
                    if(response.length > 0) {
                        let options = '<option value="">--Select event--</option>';
                        response.map((el, idx) => {
                            options += '<option value="' + el.id_event + '">' + el.title + ' [' + el.location_place + ' - ' + el.date_start +  '] |' + '</option>';
                        });

                        const element = document.getElementById('event');

                        // Destroy existing instance if already initialized
                        if (element.choicesInstance) {
                            element.choicesInstance.destroy();
                        }

                        // Replace options in the select element
                        $('#event').html(options);
                        
                        const choices = new Choices(element, {
                            searchEnabled: true,
                            removeItemButton: true,
                            searchResultLimit: 100
                        });

                        $('#submit_usage').prop('disabled', false);
                    } else {
                        Swal.fire({
                            title: "No event found.",
                            text: `No Event found for ${group}.`,
                            icon: "info"
                        });
                        setTimeout(function() {
                            Swal.close();
                            // $('#usageModal').modal('hide');
                        }, 5000);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: "Failed to get event list.",
                        text: error + '. \nPlease try again later or contact the developer.',
                        icon: "error"
                    });
                    setTimeout(function() {
                        Swal.close();
                        $('#usageModal').modal('hide');
                    }, 3000);
                }
            });

            $('#event').on('change', function() {
                if($(this).val()) {
                    $.ajax({
                        url: 'https://hadiryuk.id/api/ticket/' + $(this).val(), 
                        method: 'GET',
                        cache:false,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if(response.length > 0) {
                                $('#id_ticket').val(response[0].id_ticket) 
                            }else {
                                Swal.fire({
                                    title: "Failed to get ticket, please try again!",
                                    text: response.message,
                                    icon: "error"
                                });
                                
                            }
                            
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: "Failed to get ticket",
                                text: error + '. \nPlease try again later or contact the developer.',
                                icon: "error"
                            });
                        }
                    });
                }else {
                    $('#id_ticket').val('')
                }
            })
        }else if(redeemable == 1 && !group) {
            Swal.fire({
                title: "No event found.",
                text: 'Uncategorized Benefit Group.  \n Please notify the developer.',
                icon: "info"
            });
            setTimeout(function() {
                Swal.close();
                // $('#usageModal').modal('hide');
            }, 5000);
        }

    });
</script>



    
    
    