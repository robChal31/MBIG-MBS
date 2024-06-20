<?php

session_start();
include 'db_con.php';

$id_book = $_POST['id_book'];

$books = [];
$draft_book_q = "SELECT * FROM books WHERE id = $id_book";
$draft_exec = mysqli_query($conn, $draft_book_q);
if (mysqli_num_rows($draft_exec) > 0) {
  $books = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}
$book = $books[0] ? $books[0] : [];

?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-book.php" method="POST" enctype="multipart/form-data" id="form_book">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Title</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $book['name'] ?>" placeholder="book name..." required>
                </div>
                
                <input type="hidden" name="id_book" value="<?= $id_book ?>">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_book">Save</button>
            </div>
           
        </form>
    </div>

<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#form_book').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-book.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submit_book').prop('disabled', true);
                },
                success: function(response) {
                    console.log((response));
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
                    $('#submit_book').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submit_book').prop('disabled', false);
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


    
    
    