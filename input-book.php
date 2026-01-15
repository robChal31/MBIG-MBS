<?php

session_start();
include 'db_con.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id_book = $_POST['id_book'] ?? 0;
$book_series_id = $_POST['book_series_id'] ?? null;

/* get book */
$book = [];
$q_book = "SELECT * FROM books WHERE id = $id_book";
$e_book = mysqli_query($conn, $q_book);
if ($e_book && mysqli_num_rows($e_book) > 0) {
    $book = mysqli_fetch_assoc($e_book);
}

/* get series */
$series = [];
$q_series = "SELECT * FROM book_series WHERE deleted_at IS NULL";
$e_series = mysqli_query($conn, $q_series);
if ($e_series && mysqli_num_rows($e_series) > 0) {
    $series = mysqli_fetch_all($e_series, MYSQLI_ASSOC);
}

?>
<div class="p-2">
    <form action="save-book.php" method="POST" enctype="multipart/form-data" id="form_book">
        <div class="row g-3">

            <div class="col-md-12">
                <label class="form-label fw-semibold">Book Series</label>
                <select name="book_series_id" class="form-select form-select-sm select2" data-placeholder="Select series" required <?= $book_series_id ? 'readonly' : '' ?>>
                    <option></option>
                    <?php foreach ($series as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (($book['book_series_id'] ?? '') == $s['id'] || $book_series_id == $s['id']) ? 'selected' : '' ?>> <?= $s['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Kode Barang</label>
                <input type="text" name="kode_barang" class="form-control form-control-sm" value="<?= $book['kode_barang'] ?? '' ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Title</label>
                <input type="text" name="name" class="form-control form-control-sm" value="<?= $book['name'] ?? '' ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-select form-select-sm select2" data-placeholder="Select type" required>
                    <option></option>
                    <?php foreach(['Textbook', 'Workbook', 'Teacher Guide', 'Other'] as $l): ?>
                        <option value="<?= $l ?>" <?= ($book['type'] ?? '') == $l ? 'selected' : '' ?>>
                            <?= $l ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Grade</label>
                <input type="text" name="grade" class="form-control form-control-sm" value="<?= $book['grade'] ?? '' ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Price</label>
                <input type="text" name="price" class="form-control form-control-sm only_number" value="<?= isset($book['price']) ? number_format($book['price'], 0, ',', '.') : '' ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>
                <div class="d-flex gap-3 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" value="1" <?= ($book['is_active'] ?? 1) == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" value="0" <?= ($book['is_active'] ?? 1) == 0 ? 'checked' : '' ?>>
                        <label class="form-check-label">Inactive</label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="id_book" value="<?= $id_book ?>">

        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
            <button class="btn btn-primary btn-sm" id="submit_book">Save</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {

    $('.select2').select2({
        width: '100%'
    });

    $('#form_book').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: './save-book.php',
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_book').prop('disabled', true);
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire('Saved!', response.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    Swal.fire('Failed!', response.message, 'error');
                }
                $('#submit_book').prop('disabled', false);
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire('Failed!', xhr.responseText, 'error');
                $('#submit_book').prop('disabled', false);
            }
        });
    });

    $(document).on('input', '.only_number', function () {
        let v = $(this).val().replace(/\D/g, '');
        $(this).val(v.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });

});
</script>
