<?php

session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id = $_POST['id'] ? $_POST['id'] : 0;

$mpartner = [];
$mp_sql = "SELECT mpu.*
            FROM mp_users AS mpu
            WHERE mpu.id = $id";
$draft_exec = mysqli_query($conn, $mp_sql);
if (mysqli_num_rows($draft_exec) > 0) {
  $mpartner = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}
$mpartner = $mpartner[0] ?? [];

$schools = [];
$school_q = "SELECT * 
                FROM schools as sc 
            INNER JOIN draft_benefit AS db ON db.school_name = sc.id
            WHERE db.confirmed = 1
            GROUP BY sc.id ORDER BY name ASC";
$school_exec = mysqli_query($conn, $school_q);
if (mysqli_num_rows($school_exec) > 0) {
  $schools = mysqli_fetch_all($school_exec, MYSQLI_ASSOC);    
}

$pks = [];
$pks_q = "SELECT pk.benefit_id, pk.no_pk, pk.id, IFNULL(sc.name, db.school_name) as school_name
            FROM pk as pk
            LEFT JOIN draft_benefit as db on db.id_draft = pk.benefit_id
            LEFT JOIN schools as sc on sc.id = db.school_name
            WHERE db.deleted_at IS NULL";
$pks_exec = mysqli_query($conn, $pks_q);
if (mysqli_num_rows($pks_exec) > 0) {
  $pks = mysqli_fetch_all($pks_exec, MYSQLI_ASSOC);    
}

$selected_pks = [];
// selected_pks
$q = mysqli_query($conn, "SELECT pk_id FROM mp_user_pks WHERE user_id = $id");
while ($r = mysqli_fetch_assoc($q)) {
    $selected_pks[] = $r['pk_id'];
}

?>
    <div class="p-2">
        <!-- <h6>Detail Benefit</h6> -->
        <form action="save-template.php" method="POST" enctype="multipart/form-data" id="form_input">
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Name</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="text" name="name" class="form-control form-control-sm" value="<?= $mpartner['name'] ?? '' ?>" placeholder="name..." required>
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">E-mail</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="email" name="email" class="form-control form-control-sm" value="<?= $mpartner['email'] ?? '' ?>" placeholder="email...">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Institution</label>
                    <select name="institution_id" id="institution_id" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled selected>--Select Institution --</option>
                        <?php foreach($schools as $school) { ?>
                            <option value="<?= $school['id'] ?>" 
                                <?= $school['id'] == ($mpartner['institution_id'] ?? null) ? 'selected' : '' ?>>
                                <?= $school['name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label d-flex" style="font-size: .85rem;">PK</label>
                    <select name="pks[]" id="pks" class="form-control form-control-sm select2 col-12" style="width: 100%;" multiple required>
                        <?php foreach($pks as $pk) { ?>
                            <option value="<?= $pk['id'] ?>">[ <?= $pk['school_name'] ?> ] - <?= $pk['no_pk'] ?></option>
                        <?php } ; ?>
                    </select>
                </div>

                <input type="hidden" name="id" value="<?= $id == 0 ? '' : $id ?>">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submt">Save</button>
            </div>
           
        </form>
    </div>

<script>
    selectedPk = <?= json_encode($selected_pks) ?>;
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        if (selectedPk.length) {
            $('#pks').val(selectedPk).trigger('change');
        }

        $('#form_input').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-mpartner.php', 
                method: 'POST',
                data: formData,
                cache:false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submt').prop('disabled', true);
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
                    $('#submt').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        title: "Failed!",
                        text: error,
                        icon: "error"
                    });
                    $('#submt').prop('disabled', false);
                },
                complete: function() {
                    $('#submt').prop('disabled', false);
                }
            });
        });

    })
</script>



    
    
    