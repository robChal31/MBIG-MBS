<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])){ 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
    exit();
}

$config = require 'config.php';

function error_json($msg){
    echo json_encode([
        'status' => 'error',
        'message' => $msg
    ]);
    exit();
}

function sanitize_input($conn, $input) {
    return mysqli_real_escape_string($conn, str_replace(["&#13;", "&#10;"], ["\r", "\n"], $input));
}

$id_program                 = sanitize_input($conn, $_POST['id_program']);
$name                       = sanitize_input($conn, $_POST['name']);
$code                       = trim(sanitize_input($conn, $_POST['code']));
$is_pk                      = $_POST['is_pk'];
$program_category_id        = $_POST['program_category_id'] ?? NULL;
$is_classified              = $_POST['is_classified'] ?? 1;
$is_dynamic                 = $_POST['is_dynamic'] ?? 1;
$discount                   = $_POST['discount'] ?? 0;
$schools                    = $_POST['schools'] ?? [];
$has_omzet_scheme_discount  = $_POST['has_omzet_scheme_discount'] ?? 0;
$ranges = $_POST['ranges'] ?? [];

if (preg_match('/\s/', $code)) {
    error_json('Program code cannot contain spaces.');
}

try {
    mysqli_begin_transaction($conn);

    if ($has_omzet_scheme_discount && empty($ranges)) {
        throw new Exception('Omzet scheme enabled but no ranges provided');
    }

    $school_data = [];
    if(count($schools) > 0) {
        $url = "https://mentarimarapp.com/admin/api/get-institutions.php";
        $post_fields = http_build_query([
            'key' => 'marapp2024',
            'schools' => $schools
        ]);
    
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
    
        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }
    
        curl_close($curl);
    
        $school_data = json_decode($response, true);
    }
   
    $program_exist_query = "SELECT * FROM programs WHERE id = '$id_program'";
    $is_program_exist_exec = $conn->query($program_exist_query);
    if ($is_program_exist_exec === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    $is_program_exist = $is_program_exist_exec->num_rows > 0;

    $program_code_exist_query = "SELECT * FROM programs WHERE code = '$code'";
    $is_program_code_exist_exec = $conn->query($program_code_exist_query);
    if ($is_program_code_exist_exec === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    if ($is_program_exist) {
        $program_row = $is_program_exist_exec->fetch_assoc();
        $old_code = $program_row['code'];
        $old_name = $program_row['name'];

        if ($is_program_code_exist_exec->num_rows > 0) {
            $program_code_row = $is_program_code_exist_exec->fetch_assoc();
            $id_old_code = $program_code_row['id'];
            if($id_old_code != $id_program) {
                throw new Exception('Program code already exists.');
            }
        }

        $program_category_id_sql = is_null($program_category_id) || $program_category_id === '' ? 'NULL' : "'" . sanitize_input($conn, $program_category_id) . "'";

        $sql = "UPDATE programs 
                SET name = '$name', 
                    code = '$code', 
                    is_pk = '$is_pk', 
                    updated_at = NOW(), 
                    is_classified = '$is_classified', 
                    program_category_id = $program_category_id_sql,
                    is_dynamic = '$is_dynamic',
                    discount = '$discount',
                    has_omzet_scheme_discount = '$has_omzet_scheme_discount'
                WHERE id = '$id_program'";

        if (!$conn->query($sql)) {
            throw new Exception('Query failed: ' . $conn->error);
        }

        $select_query = "SELECT id_template_benefit, avail FROM draft_template_benefit WHERE avail LIKE '%$old_code%'";
        $template_res = $conn->query($select_query);

        if ($template_res === false) {
            throw new Exception('Query failed: ' . $conn->error);
        }

        while ($row = $template_res->fetch_assoc()) {
            $id = $row['id_template_benefit'];
            $avail = $row['avail'];
            $new_avail = str_replace($old_code, $code, $avail);
            $update_query = "UPDATE draft_template_benefit SET avail = '$new_avail' WHERE id_template_benefit = '$id'";
            if (!$conn->query($update_query)) {
                throw new Exception('Update failed for ID ' . $id . ': ' . $conn->error);
            }
        }

        $name_up = strtoupper($name);
        $sql = "UPDATE draft_benefit SET program = '$code' WHERE program = '$old_name' or program = '$old_code'";
        if (!$conn->query($sql)) {
            throw new Exception('Query failed on update draft benefit: ' . $conn->error);
        }

    } else {
        if ($is_program_code_exist_exec->num_rows > 0) {
            throw new Exception('Program code already exists.');
        }

        $program_category_id_sql = is_null($program_category_id) || $program_category_id === '' 
            ? 'NULL' 
            : "'" . sanitize_input($conn, $program_category_id) . "'";

        $sql = "INSERT INTO programs (name, code, is_pk, created_at, is_classified, program_category_id, is_dynamic, discount, has_omzet_scheme_discount) VALUES (
            '$name', '$code', '$is_pk', NOW(), '$is_classified', $program_category_id_sql, '$is_dynamic', '$discount', '$has_omzet_scheme_discount')";
        
        if (!$conn->query($sql)) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        $id_program = $conn->insert_id;

    }

    if(count($school_data) > 0) {
        $delete_sql = "DELETE FROM program_schools WHERE program_id = $id_program";

        if (!$conn->query($delete_sql)) {
            throw new Exception('Query failed: ' . $conn->error);
        }

        foreach($school_data as $sch) {
            $school_id_new              = $sch['institutionid'];
            $school_name_new            = mysqli_real_escape_string($conn, $sch['name']);
            $school_address_new         = mysqli_real_escape_string($conn, $sch['address']);
            $school_phone_new           = $sch['phone'];
            $school_segment_new         = $sch['segment'];
            $school_ec_id_new           = $sch['ec_id'];
            $school_created_date_new    = $sch['created_date'];

            $sql = "SELECT * FROM schools WHERE id = $school_id_new";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) < 1) {
                $sql = "INSERT INTO `schools` (`id`, `name`, `address`, `phone`, `segment`, `ec_id`, `created_date`) VALUES
                ($school_id_new, '$school_name_new', '$school_address_new', '$school_phone_new', '$school_segment_new', '$school_ec_id_new', '$school_created_date_new')";
                mysqli_query($conn,$sql);
            }

            $sql = "INSERT INTO program_schools (program_id, school_id, created_at) VALUES (
                '$id_program', '$school_id_new', NOW())";
            
            if (!$conn->query($sql)) {
                throw new Exception('Query failed: ' . $conn->error);
            }

        }
    }


    $rangeIds = [];
    $q = $conn->query("SELECT id FROM program_omzet_ranges WHERE program_id = $id_program");
    while ($r = $q->fetch_assoc()) {
        $rangeIds[] = $r['id'];
    }

    // delete discounts dulu (child)
    if (!empty($rangeIds)) {
        $ids = implode(',', $rangeIds);
        $conn->query("DELETE FROM program_discounts WHERE omzet_range_id IN ($ids)");
    }

    // delete ranges (parent)
    $conn->query("DELETE FROM program_omzet_ranges WHERE program_id = $id_program");

    $openEndedUsed = false;

    // urutin dulu biar validasi overlap gampang
    usort($ranges, function ($a, $b) {
        return (int) str_replace('.', '', $a['omzet_min'])
            <=> (int) str_replace('.', '', $b['omzet_min']);
    });

    foreach ($ranges as $range) {

        // wajib ada omzet_min
        if (!isset($range['omzet_min']) || $range['omzet_min'] === '') {
            throw new Exception('Omzet min is required');
        }

        $omzet_min = (int) str_replace('.', '', $range['omzet_min']);

        $omzet_max = isset($range['omzet_max']) && $range['omzet_max'] !== ''
            ? (int) str_replace('.', '', $range['omzet_max'])
            : null;

        $max_discount = isset($range['max_discount'])
            ? (float) $range['max_discount']
            : 0;

        $discounts = array_map('floatval', $range['discounts'] ?? []);

        // ===== VALIDASI =====

        // max harus > min (kalau ada)
        if ($omzet_max !== null && $omzet_max <= $omzet_min) {
            throw new Exception('Omzet max must be greater than omzet min');
        }

        // hanya boleh 1 open-ended range
        if ($omzet_max === null) {
            if ($openEndedUsed) {
                throw new Exception('Only one omzet range may have empty max');
            }
            $openEndedUsed = true;
        }

        // ===== SIMPAN RANGE =====
        $sql = "INSERT INTO program_omzet_ranges (program_id, omzet_min, omzet_max, max_discount)
            VALUES (
                $id_program,
                $omzet_min,
                " . ($omzet_max === null ? 'NULL' : $omzet_max) . ",
                $max_discount
            )
        ";

        if (!$conn->query($sql)) {
            throw new Exception('Failed insert omzet range: ' . $conn->error);
        }

        $range_id = $conn->insert_id;

        // ===== SIMPAN DISCOUNTS =====
        foreach ($discounts as $d) {
            if ($d <= 0 || $d > $max_discount) {
                continue;
            }

            $conn->query("
                INSERT INTO program_discounts (omzet_range_id, amount)
                VALUES ($range_id, $d)
            ");
        }
    }

    mysqli_commit($conn);

    echo json_encode([
        'status' => 'success',
        'message' => 'Program saved successfully'
    ]);

} catch (\Throwable $th) {
    mysqli_rollback($conn);
    error_json($th->getMessage());
}
?>
