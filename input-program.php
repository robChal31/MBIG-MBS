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
    $has_omzet_scheme_discount = $program['has_omzet_scheme_discount'] ?? '';

    $program_categories = [];
    $program_categories_q = "SELECT * FROM program_categories AS program WHERE program.deleted_at IS NULL";
    $program_categories_exec = mysqli_query($conn, $program_categories_q);
    if (mysqli_num_rows($program_categories_exec) > 0) {
        $program_categories = mysqli_fetch_all($program_categories_exec, MYSQLI_ASSOC);    
    }

    $program_discounts = [];
    $program_discounts_q = "SELECT * FROM program_discounts WHERE omzet_range_id = $id_program";
    $program_discounts_exec = mysqli_query($conn, $program_discounts_q);
    if (mysqli_num_rows($program_discounts_exec) > 0) {
        $program_discounts = mysqli_fetch_all($program_discounts_exec, MYSQLI_ASSOC);    
    }

    $selected_school_ids = [];
    $result = mysqli_query($conn, "SELECT school_id FROM program_schools WHERE program_id = $id_program");

    while ($row = mysqli_fetch_assoc($result)) {
        $selected_school_ids[] = $row['school_id'];
    }
    $selected_school_ids_js = json_encode($selected_school_ids);

    $omzet_ranges = [];
    $q = mysqli_query($conn, "SELECT r.id, r.omzet_min, r.omzet_max, r.max_discount
        FROM program_omzet_ranges r
        WHERE r.program_id = $id_program
        ORDER BY r.omzet_min ASC
    ");

    while ($r = mysqli_fetch_assoc($q)) {
        $r['discounts'] = [];
        $dq = mysqli_query($conn, "SELECT amount 
            FROM program_discounts 
            WHERE omzet_range_id = {$r['id']}
            ORDER BY amount ASC
        ");
        while ($d = mysqli_fetch_assoc($dq)) {
            $r['discounts'][] = $d['amount'];
        }
        $omzet_ranges[] = $r;
    }

    $omzet_ranges_js = json_encode($omzet_ranges);

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
                    <label class="form-label small">Maximum Discount By Program (%)</label>
                    <input type="text" placeholder="Ex: 35" name="discount" class="form-control form-control-sm" value="<?= $discount ?>" inputmode="decimal">
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

                <div class="col-6 mb-3">
                    <label class="form-label">Has Omzet Scheme Discount </label>
                    <select name="has_omzet_scheme_discount" id="has_omzet_scheme_discount" class="form-control form-control-sm select2 col-12" style="width: 100%;" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="1" <?= $has_omzet_scheme_discount == 1 ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= $has_omzet_scheme_discount == 0 ? 'selected' : '' ?>>No</option>
                    </select>
                </div>

                <div class="d-none justify-content-end" id="rangeControls">
                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="addRange()">
                        + Add Omzet Range
                    </button>
                </div>

                <div id="rangeContainer"></div>

                <input type="hidden" name="id_program" value="<?= $id_program == 0 ? '' : $id_program ?>">
            </div>

            <hr class="my-4 border-secondary">
            <div class="d-flex justify-content-end">
                <button type="button" class="me-2 btn btn-secondary btn-sm close">Cancel</button>
                <button class="btn btn-primary btn-sm" id="submit_program">Save</button>
            </div>
           
        </form>
    </div>

    <template id="rangeTemplate">
        <div class="border rounded p-3 mb-3 range-item">
            <div class="row mb-2">
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm omzet-min formatNumber" placeholder="Omzet Min" required>
                </div>
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm omzet-max formatNumber" placeholder="Omzet Max">
                </div>
                <div class="col-3">
                    <input type="number" class="form-control form-control-sm max-discount" placeholder="Max %">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-danger btn-remove">✕</button>
                </div>
            </div>

            <div class="input-group input-group-sm mb-2">
                <input type="number" step="0.1" class="form-control discount-input">
                <button type="button" class="btn btn-outline-primary btn-add-discount">
                    Add
                </button>
            </div>

            <div class="discount-list d-flex flex-wrap gap-1"></div>
        </div>
    </template>


<script>
    existingRanges = <?= $omzet_ranges_js ?>;

    function renderExistingRanges() {
        if (!existingRanges || existingRanges.length === 0) return;

        existingRanges.forEach((range, index) => {
            addRange(); // bikin wrapper dulu

            const wrapper = document.querySelectorAll('.range-item')[index];

            // set value
            wrapper.querySelector('.omzet-min').value =
                formatNumber(range.omzet_min);

            if (range.omzet_max !== null) {
                wrapper.querySelector('.omzet-max').value =
                    formatNumber(range.omzet_max);
            }

            wrapper.querySelector('.max-discount').value =
                range.max_discount;

            // render discount badges
            const list = wrapper.querySelector('.discount-list');

            (range.discounts || []).forEach(d => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-secondary d-flex align-items-center';
                badge.innerHTML = `
                    ${d}%
                    <input type="hidden"
                        name="ranges[${index}][discounts][]"
                        value="${d}">
                    <button type="button"
                        class="btn-close btn-close-white ms-2"
                        style="font-size:8px"></button>
                `;
                badge.querySelector('button').onclick = () => badge.remove();
                list.appendChild(badge);
            });
        });
    }

    function addRange() {
        const container = document.getElementById('rangeContainer');
        const index = container.querySelectorAll('.range-item').length;

        const tpl = document.getElementById('rangeTemplate');
        const clone = tpl.content.cloneNode(true);
        const wrapper = clone.querySelector('.range-item');

        wrapper.querySelector('.omzet-min').name =
            `ranges[${index}][omzet_min]`;

        wrapper.querySelector('.omzet-max').name =
            `ranges[${index}][omzet_max]`;

        wrapper.querySelector('.max-discount').name =
            `ranges[${index}][max_discount]`;

        wrapper.querySelector('.btn-remove').onclick = () => wrapper.remove();

        wrapper.querySelector('.btn-add-discount').onclick = () =>
            addDiscount(wrapper, index);

        container.appendChild(wrapper);
    }

    function addDiscount(wrapper, index) {
        const input = wrapper.querySelector('.discount-input');
        const maxDiscount = parseFloat(
            wrapper.querySelector('.max-discount').value
        );
        const list = wrapper.querySelector('.discount-list');
        const value = parseFloat(input.value);

        if (!maxDiscount || maxDiscount <= 0) {
            alert('Isi max discount dulu');
            return;
        }

        if (!value || value <= 0 || value > maxDiscount) {
            alert(`Discount harus 1–${maxDiscount}`);
            return;
        }

        const existing = [...list.querySelectorAll('input')]
            .map(i => i.value);

        if (existing.includes(String(value))) {
            alert('Duplicate discount');
            input.value = '';
            return;
        }

        const badge = document.createElement('span');
        badge.className = 'badge bg-secondary d-flex align-items-center';
        badge.innerHTML = `
            ${value}%
            <input type="hidden"
            name="ranges[${index}][discounts][]"
            value="${value}">
            <button type="button"
            class="btn-close btn-close-white ms-2"
            style="font-size:8px"></button>
        `;

        badge.querySelector('button').onclick = () => badge.remove();
        list.appendChild(badge);
        input.value = '';
    }

    function getExistingDiscounts() {
        return [...document.querySelectorAll('input[name="selectable_discounts[]"]')]
            .map(i => i.value);
    }

    function sortDiscounts() {
        const list = document.getElementById('discountList');
        const badges = [...list.children];

        badges.sort((a, b) =>
            parseFloat(a.innerText) - parseFloat(b.innerText)
        );

        badges.forEach(b => list.appendChild(b));
    }

    function formatNumber(number) {
        let parts = number.toString().split('.');
        let integerPart = parts[0];

        let formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (parts.length > 1) {
            let decimalPart = parts[1];
            return formattedIntegerPart + ',' + decimalPart;
        } else {
            return formattedIntegerPart;
        }
    }

    $(document).on('input', '.formatNumber', function () {
        let raw = $(this).val().replace(/\D/g, '');
        if (!raw) {
            $(this).val('');
            return;
        }
        $(this).val(formatNumber(raw));
    });
    
    $(document).on('input', '.max-discount', function () {
        let value = $(this).val()
        if(value > 100){
            $(this).val(100)
        }
    });


    $(document).ready(function() {
        $('.select2').select2();

        $('#has_omzet_scheme_discount').on('change', function () {
            if ($(this).val() == '1') {
                $('#rangeControls').removeClass('d-none').addClass('d-flex');
            } else {
                $('#rangeControls').addClass('d-none').removeClass('d-flex');
            }
        });

        if ($('#has_omzet_scheme_discount').val() == '1') {
            $('#rangeControls').removeClass('d-none').addClass('d-flex');
            renderExistingRanges();
        }

        $('input[name="max_discount"]').on('input', function () {
            let value = $(this).val()

            value = value
                .replace(/[^0-9.]/g, '')
                .replace(/(\..*)\./g, '$1')

            $(this).val(value)

            const $this = $(this)
            const discount = Number($this.val())

            const isEmpty = $this.val() === ''
            const isInvalid = discount < 0 || discount > 100

            if (isInvalid) {
                alert('Discount must be between 0 and 100')
                $this.val('')
                $('.discount-input').each(function() {
                    $(this).prop('disabled', true)
                })
                $('#discountList').empty()
                return
            }

            if (isEmpty) {
                $('.discount-input').each(function() {
                    $(this).prop('disabled', true)
                })
                $('#discountList').empty()
                return
            }

            $('.discount-input').each(function() {
                $(this).prop('disabled', false)
            })
        })

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


    
    
    