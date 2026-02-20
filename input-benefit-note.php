<?php

session_start();
include 'db_con.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id_benefit_list = $_POST['id_benefit_list'] ? $_POST['id_benefit_list'] : 0;

if($id_benefit_list == 0) {
    throw new Exception('id_benefit_list is required');
}

$dbl = [];

$dbl_q = "SELECT * FROM draft_benefit_list WHERE id_benefit_list = $id_benefit_list";
$dbl_exec = mysqli_query($conn, $dbl_q);

if ($dbl_exec && mysqli_num_rows($dbl_exec) > 0) {
    $dbl = mysqli_fetch_assoc($dbl_exec);
}

$note = $dbl['note'] ?? '';
?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-note.php" method="POST" enctype="multipart/form-data" id="form_note">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="form-floating">
                        <textarea class="form-control" style="height: 200px;" name="note" required><?= $note ?></textarea>
                        <label for="floatingTextarea">Note</label>
                    </div>
                </div>
                
                <input type="hidden" name="id_benefit_list" value="<?= $id_benefit_list ?>">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_note">Save</button>
            </div>
           
        </form>
    </div>

<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#form_note').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-note.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_note').prop('disabled', true);
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
                    $('#submit_note').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close()
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_note').prop('disabled', false);
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


    
    
    