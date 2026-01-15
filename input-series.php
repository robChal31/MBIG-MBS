<?php
session_start();
include 'db_con.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$series_id = $_POST['series_id'] ?? 0;

/* BOOK */
$book = [];
$r = mysqli_query($conn, "SELECT * FROM book_series WHERE id = '$series_id'");
if (mysqli_num_rows($r) > 0) {
    $book = mysqli_fetch_assoc($r);
}

/* SUBJECTS */
$subjects = mysqli_fetch_all(
    mysqli_query($conn, "SELECT id, name FROM subjects ORDER BY name ASC"),
    MYSQLI_ASSOC
);

/* LEVELS */
$levels = mysqli_fetch_all(
    mysqli_query($conn, "SELECT id, name FROM levels ORDER BY name ASC"),
    MYSQLI_ASSOC
);
?>

<style>
.modal-body{
    background:#f8fafc;
}
.form-label{
    font-size:.8rem;
}
.select2-container .select2-selection--single{
    height:38px;
    border-radius:8px;
}
.select2-selection__rendered{
    line-height:36px !important;
}
.select2-selection__arrow{
    height:36px !important;
}
</style>

<div class="p-2">
    <form id="form_book">

        <div class="row g-3">

            <div class="col-12">
                <label class="form-label fw-semibold">Book Title</label>
                <input type="text" name="name" class="form-control form-control-sm rounded-3" placeholder="Enter book title" value="<?= $book['name'] ?? '' ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Subject</label>
                <select name="subject_id" class="form-select form-select-sm select2" data-placeholder="Select subject" required>
                    <option></option>
                    <?php foreach($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($book['subject_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                            <?= $s['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Level</label>
                <select name="level_id" class="form-select form-select-sm select2" data-placeholder="Select level" required>
                    <option></option>
                    <?php foreach($levels as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ($book['level_id'] ?? '') == $l['id'] ? 'selected' : '' ?>>
                            <?= $l['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Status</label>

                <div class="d-flex gap-4 align-items-center mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1"
                            <?= ($book['is_active'] ?? 1) == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status_active">
                            Active
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_active" id="status_inactive" value="0"
                            <?= ($book['is_active'] ?? 1) == 0 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status_inactive">
                            Inactive
                        </label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="series_id" value="<?= $series_id ?>">

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-secondary btn-sm close">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary btn-sm px-4" id="submit_book">
                Save
            </button>
        </div>

    </form>
</div>

<script>
$(document).ready(function () {
    $('.select2').select2({
        width: '100%',
        minimumResultsForSearch: 5
    });

    $('#form_book').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: './save-series.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#submit_book').prop('disabled', true);
                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (res) {
                Swal.close();
                if (res.status === 'success') {
                    Swal.fire('Saved', res.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    Swal.fire('Failed', res.message, 'error');
                }
                $('#submit_book').prop('disabled', false);
            },
            error: function (err) {
                Swal.close();
                Swal.fire('Error', err.responseText, 'error');
                $('#submit_book').prop('disabled', false);
            }
        });
    });
});
</script>
