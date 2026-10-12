<?php
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

/* ===== Column index (top-level scope) ===== */
define('COL_NAME',        0);
define('COL_EMAIL',       1);
define('COL_PHONE',       2);
define('COL_SCHOOL_ID',   3);
define('COL_SCHOOL_NAME', 4);
define('COL_PK_ID',       5);
define('COL_NO_PK',       6);

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File tidak valid']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
    echo json_encode(['status' => 'error', 'message' => 'Format harus .xls/.xlsx/.csv']);
    exit;
}

try {
    $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

    if (count($rows) < 2) throw new Exception('File kosong');

    array_shift($rows); // remove header

    /* ===== Lookup by ID ===== */
    $schools_map = [];
    $q = mysqli_query($conn, "SELECT id, name FROM schools WHERE name IS NOT NULL");
    while ($r = mysqli_fetch_assoc($q)) {
        $schools_map[(string)$r['id']] = ['id' => (int)$r['id'], 'name' => $r['name']];
    }

    $pks_map = [];
    $q = mysqli_query($conn, "SELECT pk.id, pk.no_pk,
               IFNULL(sc.name, db.school_name) AS school_name,
               IFNULL(prog.name, db.program) AS program
        FROM pk
        LEFT JOIN draft_benefit db ON db.id_draft = pk.benefit_id
        LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)
        LEFT JOIN schools sc ON sc.id = db.school_name
        WHERE db.deleted_at IS NULL
    ");
    while ($r = mysqli_fetch_assoc($q)) {
        $pks_map[(string)$r['id']] = $r;
    }

    /* ===== Existing emails ===== */
    $existing_emails = [];
    $q = mysqli_query($conn, "SELECT LOWER(email) e FROM mp_users WHERE email IS NOT NULL AND email != ''");
    while ($r = mysqli_fetch_assoc($q)) $existing_emails[$r['e']] = true;

    $result = [];
    $seen = [];
    $rowNum = 1;

    foreach ($rows as $row) {
        $rowNum++;
        if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) continue;

        $name        = trim((string)($row[COL_NAME]        ?? ''));
        $email       = trim((string)($row[COL_EMAIL]       ?? ''));
        $phone       = trim((string)($row[COL_PHONE]       ?? ''));
        $school_id   = trim((string)($row[COL_SCHOOL_ID]   ?? ''));
        $school_name = trim((string)($row[COL_SCHOOL_NAME] ?? ''));
        $pk_id_raw   = trim((string)($row[COL_PK_ID]       ?? ''));
        $no_pk_info  = trim((string)($row[COL_NO_PK]       ?? ''));

        // Normalize float-string "123.0" → "123"
        if (preg_match('/^\d+\.0+$/', $school_id)) $school_id = (string)(int)$school_id;
        if (preg_match('/^\d+\.0+$/', $pk_id_raw)) $pk_id_raw = (string)(int)$pk_id_raw;

        $errors = [];

        if ($name === '')  $errors[] = 'Name required';
        if ($phone === '') $errors[] = 'Phone required';

        if ($email === '') {
            $errors[] = 'Email required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email';
        } elseif (isset($existing_emails[strtolower($email)])) {
            $errors[] = 'Email sudah terdaftar';
        } elseif (isset($seen[strtolower($email)])) {
            $errors[] = 'Email duplikat di file';
        }

        /* ===== Validate School by ID ===== */
        $institution_id = null;
        $resolved_school_name = $school_name;
        if ($school_id === '') {
            $errors[] = 'School ID required';
        } elseif (isset($schools_map[$school_id])) {
            $institution_id = $schools_map[$school_id]['id'];
            $resolved_school_name = $schools_map[$school_id]['name'];
        } else {
            $errors[] = "School ID tidak ditemukan: $school_id";
        }

        /* ===== Validate PK by ID (multiple) ===== */
        $pk_ids = [];
        $resolved_no_pk = [];
        if ($pk_id_raw === '') {
            $errors[] = 'PK ID required';
        } else {
            $parts = array_filter(array_map('trim', preg_split('/[,;]+/', $pk_id_raw)));
            foreach ($parts as $p) {
                if (preg_match('/^\d+\.0+$/', $p)) $p = (string)(int)$p;
                if (isset($pks_map[$p])) {
                    $pk_ids[] = (int)$pks_map[$p]['id'];
                    $resolved_no_pk[] = $pks_map[$p]['no_pk'] ?: '(no pk)';
                } else {
                    $errors[] = "PK ID tidak ditemukan: $p";
                }
            }
        }

        if (empty($errors)) $seen[strtolower($email)] = true;

        $result[] = [
            'row'              => $rowNum,
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone,
            'school_id'        => $school_id,
            'school_name'      => $resolved_school_name ?: '-',
            'institution_id'   => $institution_id,
            'pk_id'            => $pk_id_raw,
            'no_pk'            => implode(', ', $resolved_no_pk) ?: ($no_pk_info ?: '-'),
            'pk_ids'           => $pk_ids,
            'valid'            => empty($errors),
            'errors'           => $errors,
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $result]);

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}