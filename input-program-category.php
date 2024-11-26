<?php

session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$id_program_category = $_POST['id_program_category'] ? $_POST['id_program_category'] : 0;

$program_categories = [];
$draft_program_q = "SELECT * FROM program_categories WHERE id = $id_program_category";
$draft_exec = mysqli_query($conn, $draft_program_q);
if (mysqli_num_rows($draft_exec) > 0) {
  $program_categories = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}
$program_category = $program_categories[0] ?? [];
?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-program-category.php" method="POST" enctype="multipart/form-data" id="form_program">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Program Category Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $program_category['name'] ?? '' ?>" placeholder="program category name..." required>
                </div>
                
                <input type="hidden" name="id_program_category" value="<?= $id_program_category == 0 ? '' : $id_program_category ?>">
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
                url: './save-program-category.php', 
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


    
    
    