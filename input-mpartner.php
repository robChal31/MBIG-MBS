<?php
session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$mpartner = [];
$mp_sql = "SELECT mpu.* FROM mp_users AS mpu WHERE mpu.id = $id";
$draft_exec = mysqli_query($conn, $mp_sql);
if (mysqli_num_rows($draft_exec) > 0) {
    $mpartner = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);
}
$mpartner = $mpartner[0] ?? [];

// ==========================================
// 🔥 FALLBACK: Ambil institution_id dari PK jika di user kosong
// ==========================================
$raw_institution = $mpartner['institution_id'] ?? null;

if (empty($raw_institution) && $id > 0) {
    // Cari sekolah dari relasi PK milik user ini
    $q_pk = mysqli_query($conn, "
        SELECT db.school_name 
        FROM mp_user_pks mpup
        JOIN pk ON pk.id = mpup.pk_id
        JOIN draft_benefit db ON db.id_draft = pk.benefit_id
        WHERE mpup.user_id = $id LIMIT 1
    ");
    if ($q_pk && mysqli_num_rows($q_pk) > 0) {
        $row_pk = mysqli_fetch_assoc($q_pk);
        $raw_institution = $row_pk['school_name'];
    }
}

// ==========================================
// 🔥 RESOLVE NAMA/ID SEKOLAH
// ==========================================
$resolved_institution_id = null;

if (!empty($raw_institution)) {
    $esc_inst = mysqli_real_escape_string($conn, $raw_institution);
    // Cari ID sekolah asli (apakah raw_institution ini ID atau Nama)
    $q_inst = mysqli_query($conn, "SELECT id, name FROM schools WHERE id = '$esc_inst' OR name = '$esc_inst' LIMIT 1");
    if ($q_inst && mysqli_num_rows($q_inst) > 0) {
        $row_inst = mysqli_fetch_assoc($q_inst);
        $resolved_institution_id = $row_inst['id'];
    }
}

// ==========================================
// QUERY LIST SEKOLAH
// ==========================================
$schools = [];
$school_q = "SELECT DISTINCT sc.id, sc.name
             FROM draft_benefit AS db
             INNER JOIN schools AS sc 
                 ON (db.school_name = sc.id OR db.school_name = sc.name)
             WHERE db.confirmed = 1
             ORDER BY sc.name ASC";
$school_exec = mysqli_query($conn, $school_q);
if (mysqli_num_rows($school_exec) > 0) {
    $schools = mysqli_fetch_all($school_exec, MYSQLI_ASSOC);
}

// Pastikan sekolah yang terpilih (resolved) selalu ada di list dropdown
if ($resolved_institution_id) {
    $hasCurrent = false;
    foreach ($schools as $s) {
        if ((string)$s['id'] === (string)$resolved_institution_id) {
            $hasCurrent = true;
            break;
        }
    }
    if (!$hasCurrent && isset($row_inst)) {
        array_unshift($schools, $row_inst);
    }
}

// Ambil selected PKs
$selected_pks = [];
$q = mysqli_query($conn, "SELECT pk_id FROM mp_user_pks WHERE user_id = $id");
while ($r = mysqli_fetch_assoc($q)) {
    $selected_pks[] = (string)$r['pk_id'];
}
?>

    <div class="p-2">
        <form method="POST" enctype="multipart/form-data" id="form_input">
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

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Phone Number</label>
                    <span style="display: inline-block; color: #ddd; font-size: .65rem">&nbsp;</span>
                    <input type="number" name="phone_number" class="form-control form-control-sm" value="<?= $mpartner['phone_number'] ?? '' ?>" placeholder="phone number...">
                </div>

                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size: .85rem;">Institution</label>
                    <select name="institution_id" id="institution_id" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled <?= empty($resolved_institution_id) ? 'selected' : '' ?>>--Select Institution --</option>
                        <?php foreach($schools as $school) { ?>
                            <option value="<?= $school['id'] ?>" 
                                <?= ((string)$school['id'] === (string)$resolved_institution_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($school['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label d-flex" style="font-size: .85rem;">PK</label>
                    <select name="pks[]" id="pks" class="form-control form-control-sm select2 col-12" 
                            style="width: 100%;" multiple required>
                        <!-- Di-populate via AJAX -->
                    </select>
                    <small class="text-muted" id="pk-hint" style="font-size:.7rem;">
                        Pilih Institution dulu untuk memuat PK
                    </small>
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
(function() {
    var mppSelectedPk = <?= json_encode($selected_pks) ?>;
    var mppIsEdit = <?= $id > 0 ? 'true' : 'false' ?>;
    var mppInitialInstitution = <?= json_encode($resolved_institution_id) ?>;

    var initialized = false;

    function initForm() {
        if (initialized) return;
        initialized = true;

        var $body = $('#mpartnerModalBody');

        // Bersihkan sisa Select2
        $body.find('.select2').each(function() {
            if ($(this).data('select2')) $(this).select2('destroy');
        });

        // Init Select2
        $body.find('.select2').select2({
            width: '100%',
            dropdownParent: $('#mpartnerModal')
        });

        // Set value institution
        if (mppInitialInstitution) {
            $('#institution_id')
                .val(String(mppInitialInstitution))
                .trigger('change.select2');
        }

        // Event ganti institution
        $('#institution_id').off('change.mpp').on('change.mpp', function() {
            mppSelectedPk = [];
            loadPks($(this).val());
        });

        // Load PK pertama kali kalau mode edit
        if (mppIsEdit && mppInitialInstitution) {
            loadPks(mppInitialInstitution, function() {
                if (mppSelectedPk.length) {
                    $('#pks').val(mppSelectedPk).trigger('change');
                }
            });
        }

        // Submit Form
        $('#form_input').off('submit.mpp').on('submit.mpp', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: './save-mpartner.php',
                method: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#submt').prop('disabled', true);
                    Swal.fire({
                        title: 'Loading...',
                        html: 'Please wait while we save your data.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });
                },
                success: function(response) {
                    Swal.close();
                    if (response.status == 'success') {
                        Swal.fire({ title: "Saved!", text: response.message, icon: "success" });
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        Swal.fire({ title: "Failed!", text: response.message, icon: "error" });
                    }
                    $('#submt').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({ title: "Failed!", text: error, icon: "error" });
                    $('#submt').prop('disabled', false);
                },
                complete: function() {
                    $('#submt').prop('disabled', false);
                }
            });
        });
    }

    function loadPks(schoolId, callback) {
        if (!schoolId) {
            $('#pks').empty().trigger('change');
            $('#pk-hint').text('Pilih Institution dulu untuk memuat PK');
            if (callback) callback();
            return;
        }

        $('#pk-hint').text('Memuat PK...');

        $.ajax({
            url: './get-pks.php',
            method: 'POST',
            data: { school_id: schoolId },
            dataType: 'json',
            success: function(pks) {
                $('#pks').empty();
                $('#pk-hint').text(pks.length ? pks.length + ' PK tersedia' : 'Tidak ada PK untuk institution ini');

                pks.forEach(function(pk) {
                    var text = '[ ' + (pk.school_name || '-') + ' - ' + (pk.program || '-') + ' ] - ' + pk.no_pk;
                    $('#pks').append(new Option(text, pk.id, false, false));
                });

                $('#pks').trigger('change');
                if (callback) callback();
            },
            error: function(xhr) {
                console.error('Error load PK:', xhr.responseText);
                $('#pk-hint').text('Gagal memuat PK');
                if (callback) callback();
            }
        });
    }

    // Trigger setelah modal selesai transisi
    var $modal = $('#mpartnerModal');
    $modal.one('shown.bs.modal.mpp-init', initForm);

    // Fallback kalau loaded duluan
    setTimeout(function() {
        if (!initialized && $modal.hasClass('show')) {
            initForm();
        }
    }, 400);
})();
</script>