<?php

session_start();
include 'db_con.php';

$id_benefit_llist = $_POST['id_benefit_list'];  
$role = $_SESSION['role'];                                                            
$sql = "SELECT
            dbl.*,
            SUM(bu.qty1) AS tot_usage1,
            SUM(bu.qty2) AS tot_usage2,
            SUM(bu.qty3) AS tot_usage3
        FROM benefit_usages AS bu
        LEFT JOIN draft_benefit_list AS dbl ON dbl.id_benefit_list = bu.id_benefit_list
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
               

                <input type="hidden" name="id_benefit_list" value="<?= $id_benefit_llist ?>">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_usage">Save</button>
            </div>
        </form>
    </div>

<script>
    $(document).ready(function() {
        $('#quantity').on('input', function() {
            var value = $(this).val();
            var isValid = /^\d+$/.test(value) && parseInt(value) > 0;


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
                },
                success: function(response) {
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
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                }
            });
        });
    });
</script>
 
<?php $conn->close();}else { ?>
 <div class="alert alert-danger">Something went wrong</div>
<?php } ?>


    
    
    