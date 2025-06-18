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
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-md-10 col-12">
                    <div class="bg-whites rounded h-100 p-4">
                        <h4 class="mb-4">Benefit Setting</h4>    
                        <form method="POST" id="form">
                            <div class="my-2 py-2">
                                <label for="max_price_percentage" class="form-label px-1 mb-0 pb-0" style="font-size: .85rem;">Max Program Price Percentage From Normal Price</label>
                                <input type="number" id="max_price_percentage" name="max_price_percentage" value="<?= $benefitSetting['max_price_percentage']; ?>" placeholder="input percentage..." class="form-control form-control-sm" required>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>
                            <div class="my-2 py-2">
                                <label for="max_discount_percentage" class="form-label px-1 mb-0 pb-0" style="font-size: .85rem;">Max Program Discount Percentage</label>
                                <input type="number" id="max_discount_percentage" name="max_discount_percentage" value="<?= $benefitSetting['max_discount_percentage']; ?>" placeholder="input percentage..." class="form-control form-control-sm" required>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>
                            <div class="my-2 py-2">
                                <label for="max_benefit_percentage" class="form-label px-1 mb-0 pb-0" style="font-size: .85rem;">Max Benefit Percentage From Allocation</label>
                                <input type="number" id="max_benefit_percentage" name="max_benefit_percentage" value="<?= $benefitSetting['max_benefit_percentage']; ?>" placeholder="input percentage..." class="form-control form-control-sm" required>
                                <div class="invalid-feedback">Please enter a valid percentage</div>
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-sm btn-primary" id="btn-submit">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sale & Revenue End -->

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
