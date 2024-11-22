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
$program = $programs[0] ?? [];
$is_pk = $program['is_pk'] ?? 1;
$is_classified = $program['is_classified'] ?? 1;
?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-program.php" method="POST" enctype="multipart/form-data" id="form_program">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Program Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $program['name'] ?? '' ?>" placeholder="program name..." required>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Program Code</label>
                    <input type="text" name="code" class="form-control form-control-sm" value="<?= $program['code'] ?? '' ?>" placeholder="code..." required>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Is PK</label>
                    <select name="is_pk" id="is_pk" class="form-control form-control-sm" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $is_pk == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $is_pk == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Is Classified</label>
                    <select name="is_classified" id="is_classified" class="form-control form-control-sm" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $is_classified == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $is_classified == 0 ? 'selected' : '' ?>>No</option>
                    </select>
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
                    console.log((response));
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


    
    
    