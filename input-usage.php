<?php

session_start();
include 'db_con.php';

$id_benefit_llist = $_POST['id_benefit_list'];  
$role = $_SESSION['role'];                                                            
$sql = "SELECT
            dbl.*, dtb.redeemable,
            SUM(COALESCE(bu.qty1, 0)) AS tot_usage1,
            SUM(COALESCE(bu.qty2, 0)) AS tot_usage2,
            SUM(COALESCE(bu.qty3, 0)) AS tot_usage3
        FROM draft_benefit_list AS dbl
        LEFT JOIN benefit_usages AS bu ON dbl.id_benefit_list = bu.id_benefit_list
        LEFT JOIN draft_template_benefit dtb on dtb.id_template_benefit = dbl.id_template 
        WHERE dbl.id_benefit_list = $id_benefit_llist";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $usages = $usages[0];  

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
                    <textarea name="description" id="description_usage" class="form-control"></textarea>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Year</label>
                    <select name="year" id="year" class="form-control form-control-sm" style="background-color: white;" required>
                       <option value="qty1" <?= $usages['qty'] == 0 || $usages['qty'] <= $usages['tot_usage1'] ? 'disabled' : '' ?>>Year 1</option>
                       <option value="qty2" <?= $usages['qty2'] == 0 || $usages['qty2'] <= $usages['tot_usage2'] ? 'disabled' : '' ?>>Year 2</option>
                       <option value="qty3" <?= $usages['qty3'] == 0 || $usages['qty3'] <= $usages['tot_usage3'] ? 'disabled' : '' ?>>Year 3</option>
                    </select>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="qty" id="quantity" class="form-control form-control-sm" value="" required>
                </div>

                <?php 
                    if($usages['redeemable'] == 1) { ?>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label d-block">Events</label>
                            <select name="event" id="event" class="form-control form-control-sm select2" style="background-color: white;" required>
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Diskon</label>
                            <input type="number" name="diskon" id="diskon" class="form-control form-control-sm" value="" required>
                        </div>               
                <?php } ?>
               

                <input type="hidden" name="id_benefit_list" value="<?= $id_benefit_llist ?>">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_usage" <?= $usages['redeemable'] == 1 ? 'disabled' : '' ?>>Save</button>
            </div>
        </form>
    </div>

<script>
    $(document).ready(function() {

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
            console.log('maxQty', maxQty)

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

        $('#form-usage').on('submit', function(event) {
            event.preventDefault();
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
                    Swal.close();
                    Swal.fire({
                        title: "Saved!",
                        text: response.message,
                        icon: "success"
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                }
            });
        });

        let redeemable = <?= $usages['redeemable'] ?>

        if(redeemable == 1) {
            $.ajax({
                url: 'https://hadiryuk.id/api/eventBenefit', 
                method: 'GET',
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_usage').prop('disabled', true);
                },
                success: function(response) {
                    console.log((response));
                    if(response.length > 0) {
                        let options = '';
                        response.map((el,idx) => {
                            options += '<option value="' + el.id_event + '">' + el.title + '</option>';
                        })

                        $('#event').html(options);
                        $('#event').select2();

                        $('#submit_usage').prop('disabled', false);
                    }else {
                        Swal.fire({
                            title: "Failed get event list!",
                            text: response.message,
                            icon: "error"
                        });
                        setTimeout(function() {
                            Swal.close();
                            $('#usageModal').modal('hide');
                        }, 3000);
                    }
                    
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
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
        }

    });
</script>
 
<?php $conn->close();}else { ?>
 <div class="alert alert-danger">Something went wrong</div>
<?php } ?>


    
    
    