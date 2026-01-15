<?php

    session_start();
    include 'db_con.php';
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);


    $id_program = $_POST['id_program'] ? $_POST['id_program'] : 0;

    $programs = [];
    $draft_program_q = "SELECT * FROM programs WHERE id = $id_program";
    $draft_exec = mysqli_query($conn, $draft_program_q);
    if (mysqli_num_rows($draft_exec) > 0) {
    $programs = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
    }
    $program        = $programs[0] ?? [];
    $is_pk          = $program['is_pk'] ?? 1;
    $is_classified  = $program['is_classified'] ?? 1;
    $is_dynamic     = $program['is_dynamic'] ?? 0;
    $discount       = $program['discount'] ?? '';

    $program_categories = [];
    $program_categories_q = "SELECT * FROM program_categories AS program WHERE program.deleted_at IS NULL";
    $program_categories_exec = mysqli_query($conn, $program_categories_q);
    if (mysqli_num_rows($program_categories_exec) > 0) {
    $program_categories = mysqli_fetch_all($program_categories_exec, MYSQLI_ASSOC);    
    }

    $selected_school_ids = [];
    $result = mysqli_query($conn, "SELECT school_id FROM program_schools WHERE program_id = $id_program");

    while ($row = mysqli_fetch_assoc($result)) {
        $selected_school_ids[] = $row['school_id'];
    }
    $selected_school_ids_js = json_encode($selected_school_ids);
?>

<style>
    * {
        font-size: .9rem !important;
    }
</style>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-program.php" method="POST" enctype="multipart/form-data" id="form_program">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Program Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $program['name'] ?? '' ?>" placeholder="Ex: Program 1" required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Program Code</label>
                    <input type="text" name="code" class="form-control form-control-sm" value="<?= $program['code'] ?? '' ?>" placeholder="Ex: SKKS_2027" required>
                    <span class="text-muted small" style="font-size: 10px !important;">Gunakan _ sebagai pemisah</span>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label d-flex">Program Category</label>
                    <select name="program_category_id" class="form-control form-control-sm select2 col-12" style="width: 100%;">
                        <option value="" disabled selected>Select Category</option>
                        <?php foreach($program_categories as $pc) { ?>
                            <option value="<?= $pc['id'] ?>" <?= $pc['id'] == ($program['program_category_id'] ?? '') ? 'selected' : '' ?>><?= $pc['name'] ?></option>
                        <?php } ; ?>
                    </select>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label small">Maximum Discount (%)</label>
                    <input type="text" placeholder="Ex: 35" name="discount" class="form-control form-control-sm" value="<?= $discount ?>">
                    <small class="text-muted d-block mt-1" style="font-size: 10px !important;">
                       Masukan jumlah maksimum diskon untuk program ini, jika ada ketentuan diskon pada program tersebut, jika tidak ada maka biarkan kosong
                    </small>
                </div>

                <div class="col-4 mb-3">
                    <label class="form-label d-flex">Is PK</label>
                    <select name="is_pk" id="is_pk" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $is_pk == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $is_pk == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="col-4 mb-3">
                    <label class="form-label d-flex">Is General</label>
                    <select name="is_classified" id="is_classified" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $is_classified == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $is_classified == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="col-4 mb-3">
                    <label class="form-label">Is Dynamic</label>
                    <select name="is_dynamic" id="is_dynamic" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $is_dynamic == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $is_dynamic == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label d-flex">Schools</label>
                    <select name="schools[]" id="select_schools" class="form-control form-control-sm select2 col-12" style="width: 100%;" multiple>
                    </select>
                    <span class="text-muted small">Leave it blank if it is for all schools</span>
                </div>

                <input type="hidden" name="id_program" value="<?= $id_program == 0 ? '' : $id_program ?>">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_program">Save</button>
            </div>
           
        </form>
    </div>

<script>
    $(document).ready(function() {
        $('.select2').select2();

        $.ajax({
            url: 'https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=select&ec_email=<?= $_SESSION['username'] ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let selected_school_ids = <?php echo $selected_school_ids_js; ?>;

                let options = "";

                response.map((data) => {
                    let isSelected = selected_school_ids.includes(data.id) ? 'selected' : '';
                    options += `<option value="${data.id}" ${isSelected}>${data.name}</option>`;
                });

                $('#select_schools').html(options);
                $('#select_schools').select2({
                    placeholder: 'Select a school',
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#select_schools').html('Error: ' + textStatus);
            }
        });

        $('#form_program').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-program.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_program').prop('disabled', true);
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
                    Swal.close()
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
                    $('#submit_program').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_program').prop('disabled', false);
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


    
    
    