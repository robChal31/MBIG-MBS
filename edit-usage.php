<?php include 'header.php'; ?>

<?php
include 'db_con.php';

$id_usage           = $_GET['id'];
$role               = $_SESSION['role'];                                                            
$sql                = "SELECT bu.*, dtb.redeemable 
                        FROM benefit_usages as bu
                        LEFT JOIN draft_benefit_list as dbl ON bu.id_benefit_list = dbl.id_benefit_list
                        LEFT JOIN draft_template_benefit dtb on dtb.id_template_benefit = dbl.id_template 
                        WHERE bu.id = $id_usage";
$result             = $conn->query($sql);

if ($result->num_rows > 0) {
    $usages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $usages = $usages[0];
    $qty    = $usages['qty1'] + $usages['qty2'] + $usages['qty3']; 
?>

<div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="container-fluid p-2 pt-4">
            <div class="p-4 card">
                <h6>Edit Benefit Usage</h6>
                <form action="update-usage.php" method="POST" enctype="multipart/form-data" id="form-usage">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Used At</label>
                            <input type="date" name="used_at" class="form-control form-control-sm" value="<?= $usages['used_at'] ?>" disabled>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description_usage" class="form-control" style="height: 150px;"><?= $usages['description'] ?></textarea>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Year</label>
                            <select name="year" id="year" class="form-control form-control-sm" style="background-color: white;" disabled>
                                <option value="qty1" <?= $usages['qty1'] > 0 ? 'selected' : '' ?> >Year 1</option>
                                <option value="qty2" <?= $usages['qty2'] > 0 ? 'selected' : '' ?>>Year 2</option>
                                <option value="qty3" <?= $usages['qty3'] > 0 ? 'selected' : '' ?>>Year 3</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="qty" id="quantity" class="form-control form-control-sm" value="<?= $qty ?>" disabled>
                        </div>

                        <input type="hidden" name="id" value="<?= $id_usage ?>">

                        <!-- <?php 
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
                        <?php } ?> -->
                    
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="benefits.php" class="me-2 btn btn-secondary btn-sm close">Cancel</a>
                        <button class="btn btn-primary btn-sm" id="submit_usage">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php $conn->close();}else { ?>
 <div class="alert alert-danger">Something went wrong</div>
<?php } ?>
<?php include 'footer.php';?>

<script>
    $(document).ready(function() {

        $('#form-usage').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './update-usage.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
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
                    if(response.status) {
                        Swal.fire({
                            title: "Saved!",
                            text: response.message,
                            icon: "success"
                        });
                        setTimeout(function() {
                            window.location.href = 'benefits.php';
                        }, 1000);
                    }else {
                        Swal.fire({
                            title: "Failed!",
                            text: response.message,
                            icon: "error"
                        })
                    }
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
                }
            });
        });

    });
</script>   
    
    