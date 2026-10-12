<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include 'db_con.php';
require 'draft-benefit-doc-render.php';
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['username'])) {
    header("Location: ./index.php");
    exit();
}

$id_draft = isset($_GET['id_draft']) ? intval($_GET['id_draft']) : 0;
if ($id_draft <= 0) die('ID Draft tidak valid');

$isDownload = isset($_GET['download']);

$html = renderBenefitDoc($conn, $id_draft, [
    'mhtml' => $isDownload,  // download → MHTML (Word-friendly), preview → HTML biasa
]);

if ($html === '') die('Data draft tidak ditemukan');

if ($isDownload) {
    // Ambil nama sekolah untuk nama file
    $schoolRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT IFNULL(sc.name, db.school_name) AS s
         FROM draft_benefit db
         LEFT JOIN schools sc ON sc.id = db.school_name
         WHERE db.id_draft = $id_draft LIMIT 1"));
    $school = $schoolRow['s'] ?? 'School';

    $filename = 'Benefit - ' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $school) . '.doc';

    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
}

echo $html;