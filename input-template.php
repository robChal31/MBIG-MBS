<?php

session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id_template = $_POST['id_template'] ? $_POST['id_template'] : 0;

$template = [];
$draft_template_q = "SELECT * 
                      FROM draft_template_benefit AS dtb
                      LEFT JOIN benefit_role AS br ON br.id_template = dtb.id_template_benefit
                      WHERE dtb.id_template_benefit = $id_template";
$draft_exec = mysqli_query($conn, $draft_template_q);
if (mysqli_num_rows($draft_exec) > 0) {
  $template = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}
$template = $template[0] ?? [];

$business_units = [];
$business_unit_q = "SELECT * 
                      FROM business_units
                      WHERE is_active = 1";
$bu_unit_exec = mysqli_query($conn, $business_unit_q);
if (mysqli_num_rows($bu_unit_exec) > 0) {
  $business_units = mysqli_fetch_all($bu_unit_exec, MYSQLI_ASSOC);    
}

$programs = [];
$program_q = "SELECT * 
            FROM programs
            WHERE is_active = 1";
$program_exec = mysqli_query($conn, $program_q);
if (mysqli_num_rows($program_exec) > 0) {
  $programs = mysqli_fetch_all($program_exec, MYSQLI_ASSOC);    
}

?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-template.php" method="POST" enctype="multipart/form-data" id="form_template">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Benefit</label>
                    <input type="text" name="benefit" class="form-control form-control-sm" value="<?= $template['benefit'] ?? '' ?>" placeholder="benefit..." required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Sub-benefit</label>
                    <input type="text" name="subbenefit" class="form-control form-control-sm" value="<?= $template['subbenefit'] ?? '' ?>" placeholder="sub-benefit..." required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Benefit Name</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="text" name="benefit_name" class="form-control form-control-sm" value="<?= $template['benefit_name'] ?? '' ?>" placeholder="benefit name..." required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label d-flex" style="font-size: .85rem;">Avail Code</label>
                    <select name="avail[]" id="avail" class="form-control form-control-sm select2 col-12" style="width: 100%;" multiple required>
                        <?php foreach($programs as $prog) { ?>
                            <option value="<?= $prog['code'] ?>" <?= strpos(($template['avail'] ?? ''), $prog['code']) !== false ? 'selected' : '' ?>><?= $prog['name'] ?></option>
                        <?php } ; ?>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Description</label>
                    <textarea name="description" class="form-control" id="" style="height: 150px;"><?= $template['description'] ?? ''  ?></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Implementation</label>
                    <textarea name="pelaksanaan" class="form-control" id="" style="height: 100px;"><?= $template['pelaksanaan'] ?? ''  ?></textarea>
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Qty Year 1</label>
                    <input type="text" name="qty1" class="form-control form-control-sm only_number" value="<?= $template['qty1'] ?? '' ?>" placeholder="quantity...">
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Qty Year 2</label>
                    <input type="text" name="qty2" class="form-control form-control-sm only_number" value="<?= $template['qty2'] ?? '' ?>" placeholder="quantity...">
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Qty Year 3</label>
                    <input type="text" name="qty3" class="form-control form-control-sm only_number" value="<?= $template['qty3'] ?? '' ?>" placeholder="quantity...">
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Value</label>
                    <input type="text" name="value" class="form-control form-control-sm only_number" value="<?= $template['valueMoney'] ?? '' ?>" placeholder="value...">
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Business Unit</label>
                    <select name="unit_bisnis" id="unit_bisnis" class="form-control form-control-sm" required>
                        <?php foreach($business_units as $bu) { ?>
                            <option value="<?= $bu['code'] ?>" <?= $bu['code'] == ($template['code'] ?? '') ? 'selected' : '' ?>><?= $bu['name'] ?></option>
                        <?php } ; ?>
                    </select>
                </div>
                <div class="col-4 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Optional</label>
                    <select name="optional" id="optional" class="form-control form-control-sm" required>
                       <option value="0" <?= ($template['optional'] ?? '') == 0 ? 'selected' : '' ?>>No</option>
                       <option value="1" <?= ($template['optional'] ?? '') == 1 ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Subject</label>
                    <select name="subject" id="subject" class="form-control form-control-sm">
                        <option value="" disabled selected>--Select Subject--</option>
                        <option value="English" <?= ($template['subject'] ?? '') == 'English' ? 'selected' : '' ?>>English</option>
                        <option value="Maths" <?= ($template['subject'] ?? '') == 'Maths' ? 'selected' : '' ?>>Maths</option>
                        <option value="Science" <?= ($template['subject'] ?? '') == 'Science' ? 'selected' : '' ?>>Science</option>
                        <option value="Mandarin" <?= ($template['subject'] ?? '') == 'Mandarin' ? 'selected' : '' ?>>Mandarin</option>
                        <option value="Bahasa Indonesia" <?= ($template['subject'] ?? '') == 'Bahasa Indonesia' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                        <option value="Penguatan Karakter" <?= ($template['subject'] ?? '') == 'Penguatan Karakter' ? 'selected' : '' ?>>Penguatan Karakter</option>
                        <option value="IGCSE" <?= ($template['subject'] ?? '') == 'IGCSE' ? 'selected' : '' ?>>IGCSE</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Redeemable</label>
                    <select name="redeemable" id="redeemable" class="form-control form-control-sm">
                        <option value="0" <?= ($template['redeemable'] ?? '') == '0' ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= ($template['redeemable'] ?? '') == '1' ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Order</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="number" name="benefit_order" class="form-control form-control-sm" value="<?= $template['benefit_order'] ?? '' ?>" placeholder="benefit order..." required>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Highlight Color</label>
                    <select name="highlight_color" id="highlight_color" class="form-control form-control-sm bg-white ">
                        <option value="" disabled selected>--Select Highlight Color --</option>
                        <option value="8338ec" <?= ($template['highlight_color'] ?? '') == '8338ec' ? 'selected' : '' ?> style="color: #8338ec">Purple</option>
                        <option value="ff006e" <?= ($template['highlight_color'] ?? '') == 'ff006e' ? 'selected' : '' ?> style="color: #ff006e">Red</option>
                        <option value="fb5607" <?= ($template['highlight_color'] ?? '') == 'fb5607' ? 'selected' : '' ?> style="color: #fb5607">Orange</option>
                        <option value="3a86ff" <?= ($template['highlight_color'] ?? '') == '3a86ff' ? 'selected' : '' ?> style="color: #3a86ff">Blue</option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Info</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="text" name="info" class="form-control form-control-sm" value="<?= $template['info'] ?? '' ?>" placeholder="info..." required>
                </div>

                <input type="hidden" name="id_template" value="<?= $id_template == 0 ? '' : $id_template ?>">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_template">Save</button>
            </div>
           
        </form>
    </div>

<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#form_template').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-template.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_template').prop('disabled', true);
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
                    console.log((response));
                    Swal.close();
                    if(response.status == 'success') {
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
                        });
                    }
                    $('#submit_template').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_template').prop('disabled', false);
                }
            });
        });

        $(document).on('input', '.only_number', function() {
            let sanitizedValue = $(this).val().replace(/^0+|\D/g, '');

            let formattedValue = sanitizedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            $(this).val(formattedValue);
        });
    })
</script>
 
<!-- <?php //}else { echo "Error: " . $conn->error; } $conn->close();?> -->


    
    
    