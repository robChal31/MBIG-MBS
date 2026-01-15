<?php 
    include 'header.php';
    include 'db_con.php';
    require 'vendor/autoload.php';
    $config = require 'config.php';

    $id_user = $_SESSION['id_user'];

    $benefitSetting = [
        'max_price_percentage' => '',
        'max_discount_percentage' => '',
        'max_benefit_percentage' => ''
    ];

    $query = "SELECT max_price_percentage, max_discount_percentage, max_benefit_percentage FROM benefit_setting LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $benefitSetting = mysqli_fetch_assoc($result);
    }

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];

        $max_price_percentage = $_POST['max_price_percentage'];
        $max_discount_percentage = $_POST['max_discount_percentage'];
        $max_benefit_percentage = $_POST['max_benefit_percentage'];

        // Validation
        if ($max_price_percentage === '' || !is_numeric($max_price_percentage) || $max_price_percentage < 0) {
            $errors[] = "Max Program Price Percentage must be a positive number";
        }

        if ($max_discount_percentage === '' || !is_numeric($max_discount_percentage) || $max_discount_percentage < 0) {
            $errors[] = "Max Program Discount Percentage must be a positive number";
        }

        if ($max_benefit_percentage === '' || !is_numeric($max_benefit_percentage) || $max_benefit_percentage < 0) {
            $errors[] = "Max Benefit Percentage From Allocation must be a positive number";
        }

        if (empty($errors)) {
            // Update langsung tanpa prepare/bind
           try {
                $query = "
                    UPDATE benefit_setting SET 
                        max_price_percentage = $max_price_percentage, 
                        max_discount_percentage = $max_discount_percentage, 
                        max_benefit_percentage = $max_benefit_percentage
                    LIMIT 1
                ";

                $result = mysqli_query($conn, $query);

                if ($result) {
                    $_SESSION['toast_status'] = 'Success';
                    $_SESSION['toast_msg'] = "Berhasil mengupdate settings";
                    header("Location: ".$_SERVER['PHP_SELF']);
                    exit;
                } else {
                    echo "<script>alert('Gagal mengupdate settings.');</script>";
                }
               
           } catch (Exception $e) {
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
           }
        } else {
            $error_message = implode("\\n", $errors);
            echo "<script>alert('Perbaiki input berikut:\\n{$error_message}');</script>";
        }
    }
?>
<style>
    table.dataTable tbody td {
        vertical-align: middle !important;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .invalid-feedback {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
</style>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid p-4">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-12">
                    <div class="card rounded shadow-sm p-4">

                        <!-- HEADER -->
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-1">Benefit Setting</h5>
                            <small class="text-muted">Configure global benefit and pricing limitations</small>
                        </div>

                        <form method="POST" id="form">
                            <!-- FIELD -->
                            <div class="mb-3">
                                <label for="max_price_percentage" class="form-label small fw-semibold">
                                    Max Program Price (% from normal price)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number"
                                        id="max_price_percentage"
                                        name="max_price_percentage"
                                        value="<?= $benefitSetting['max_price_percentage']; ?>"
                                        class="form-control"
                                        placeholder="e.g. 80"
                                        required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>

                            <!-- FIELD -->
                            <div class="mb-3">
                                <label for="max_discount_percentage" class="form-label small fw-semibold">
                                    Max Program Discount (%)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number"
                                        id="max_discount_percentage"
                                        name="max_discount_percentage"
                                        value="<?= $benefitSetting['max_discount_percentage']; ?>"
                                        class="form-control"
                                        placeholder="e.g. 20"
                                        required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>

                            <!-- FIELD -->
                            <div class="mb-3">
                                <label for="max_benefit_percentage" class="form-label small fw-semibold">
                                    Max Benefit (% from allocation)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number"
                                        id="max_benefit_percentage"
                                        name="max_benefit_percentage"
                                        value="<?= $benefitSetting['max_benefit_percentage']; ?>"
                                        class="form-control"
                                        placeholder="e.g. 50"
                                        required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>

                            <!-- ACTION -->
                            <div class="d-flex justify-content-end pt-3 border-top mt-4">
                                <button type="submit"
                                        class="btn btn-primary btn-sm px-4 fw-semibold"
                                        id="btn-submit">
                                    <i class="fa fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>


<?php include 'footer.php';?>
<script>
    $(document).ready(function () {
        $('#form').submit(function (e) {
            let isValid = true;

            $('[required]').each(function () {
                const value = $(this).val().trim();
                const numberValue = parseFloat(value);
                let message = "Please enter positive numbers only.";
                if (value === '' || isNaN(numberValue) || numberValue < 0) {
                    $(this).addClass('is-invalid');
                    message = "Please enter positive numbers only.";
                    isValid = false;
                } else if(numberValue > 100) {
                    $(this).addClass('is-invalid');
                    message = "Please enter percentage less than or equal to 100.";
                    isValid = false;
                }else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert(message);
                return false;
            }

            $('#btn-submit').prop('disabled', true);
        });

        $('input').on('input', function () {
            const value = $(this).val().trim();
            if (value !== '' && !isNaN(value) && parseFloat(value) >= 0) {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
