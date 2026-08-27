<?php
include 'db_con.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ./index.php");
    exit();
}

$id_draft = isset($_GET['id_draft']) ? intval($_GET['id_draft']) : 0;
if ($id_draft <= 0) die('ID Draft tidak valid');

// ========== AMBIL DATA DRAFT ==========
$sql = "SELECT db.*, sc.name AS school_name2, ec.generalname AS ec_name, p.name AS program_name, p.code AS program_code
        FROM draft_benefit db
        LEFT JOIN schools sc ON sc.id = db.school_name
        LEFT JOIN user ec ON ec.id_user = db.id_ec
        LEFT JOIN programs p ON p.name = db.program OR p.code = db.program
        WHERE db.id_draft = $id_draft LIMIT 1";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$draft = mysqli_fetch_assoc($result) or die('Data draft tidak ditemukan');

// ========== AMBIL DATA BENEFIT ==========
$sql = "SELECT dbl.*, b.benefit, b.benefit_name, b.subbenefit, b.description, b.pelaksanaan, b.qty1, b.qty2, b.qty3
        FROM draft_benefit_list dbl
        LEFT JOIN draft_template_benefit b ON b.id_template_benefit = dbl.id_template
        WHERE dbl.id_draft = $id_draft AND (dbl.qty > 0 OR dbl.qty2 > 0 OR dbl.qty3 > 0)
        ORDER BY dbl.id_benefit_list ASC";
$benefits = mysqli_query($conn, $sql) ? mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC) : [];

// ========== LOGO ==========
function imageToDataUri($path) {
    return file_exists($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : '';
}
$logo_program = imageToDataUri(__DIR__ . '/p1_img2.png');
$logo_mentari = imageToDataUri(__DIR__ . '/p1_img1.png');

// ========== HELPER ==========
function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function displayQty($v) { return ($v === null || $v === '' || (is_numeric($v) && (float)$v == 0)) ? '-' : esc($v); }
function formatTanggal($date) {
    if (!$date) return '-';
    $t = strtotime($date);
    if (!$t) return $date;
    $bulan = [1 => 'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function formatDescription($v) {
    $v = nl2br(esc(strip_tags((string)$v)), false);
    return $v;
}

// ========== GROUP BENEFIT ==========
$grouped = ['Guru dan Pimpinan Sekolah' => [], 'Sekolah' => [], 'Siswa' => []];
$keywords = [
    'guru' => 'Guru dan Pimpinan Sekolah',
    'pimpinan' => 'Guru dan Pimpinan Sekolah',
    'teacher' => 'Guru dan Pimpinan Sekolah',
    'training' => 'Guru dan Pimpinan Sekolah',
    'pelatihan' => 'Guru dan Pimpinan Sekolah',
    'assessment' => 'Guru dan Pimpinan Sekolah',
    'masterclass' => 'Guru dan Pimpinan Sekolah',
    'supervisi' => 'Guru dan Pimpinan Sekolah',
    'forum guru' => 'Guru dan Pimpinan Sekolah',
    'native teacher' => 'Guru dan Pimpinan Sekolah',
    'tkt' => 'Guru dan Pimpinan Sekolah',
    'cept' => 'Guru dan Pimpinan Sekolah',
    'beasiswa s2' => 'Guru dan Pimpinan Sekolah',
    'konvensi' => 'Guru dan Pimpinan Sekolah',
    'siswa' => 'Siswa',
    'memo' => 'Siswa',
    'mec' => 'Siswa',
    'olympiad' => 'Siswa',
    'pelajar' => 'Siswa'
];
foreach ($benefits as $b) {
    $text = strtolower(trim(($b['benefit'] ?? '') . ' ' . ($b['benefit_name'] ?? '')));
    $group = 'Sekolah';
    foreach ($keywords as $key => $g) {
        if (strpos($text, $key) !== false) { $group = $g; break; }
    }
    $grouped[$group][] = $b;
}

// ========== GENERATE HTML ==========
$program_title = trim(preg_replace('/^PROGRAM\s+/i', '', strtoupper($draft['program_name'] ?? $draft['program'] ?? 'PRESTASI'))) ?: 'PRESTASI';
$school_name = strtoupper($draft['school_name2'] ?? $draft['school_name'] ?? '-');
$no_pk = $draft['no_pk'] ?? '-';
$date = formatTanggal($draft['created_at'] ?? date('Y-m-d'));
$ec_name = $draft['ec_name'] ?? 'EC';

$html = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Benefit - ' . esc($school_name) . '</title>
<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:DoNotOptimizeForBrowser/><w:AllowPNG/><w:DoNotPromoteQF/><w:Compatibility><w:UsePrinterMetrics/><w:DoNotUseHTMLParagraphAutoSpacing/></w:Compatibility></w:WordDocument></xml><![endif]-->
<style>
@page { size:21cm 29.7cm; margin:1.10cm 0.70cm 1.10cm 0.70cm; mso-page-orientation:portrait; }
@page Section1 { size:21cm 29.7cm; margin:1.10cm 0.70cm 1.10cm 0.70cm; mso-page-orientation:portrait; }
div.Section1 { page:Section1; }
body { background:#fff; color:#000; font-family:Arial,Helvetica,sans-serif; font-size:9pt; line-height:1.15; margin:0; padding:0; }
.document { width:100%; margin:0; padding:0; }
.document-number { width:100%; height:6mm; text-align:right; font-weight:bold; font-size:9pt; white-space:nowrap; }
.logo-table { width:100%; border-collapse:collapse; table-layout:fixed; margin:0; padding:0; }
.logo-table td { border:none; padding:0; margin:0; vertical-align:top; }
.logo-left { width:50%; text-align:left; }
.logo-right { width:50%; text-align:right; }
.logo-program { display:block; width:53mm; height:auto; }
.logo-mentari { display:inline-block; width:49mm; height:auto; }
.document-title { width:100%; text-align:center; font-size:16pt; font-weight:bold; line-height:1; margin-top:8mm; margin-bottom:6mm; }
.introduction { width:100%; text-align:center; font-size:9pt; line-height:1.15; margin:0 0 5.5mm 0; padding:0; }
.introduction p { margin:0; padding:0; }
.benefit-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:8.5pt; line-height:1.12; mso-table-lspace:0pt; mso-table-rspace:0pt; }
.benefit-table col.col-no { width:11mm; }
.benefit-table col.col-program { width:47mm; }
.benefit-table col.col-description { width:69mm; }
.benefit-table col.col-year { width:23mm; }
.benefit-table th, .benefit-table td { border:1px solid #000; color:#000; padding:2mm 1.2mm; vertical-align:top; }
.benefit-table thead { display:table-header-group; }
.benefit-table thead tr { page-break-after:avoid; }
.benefit-table thead th { height:16mm; padding:1.2mm 1mm; font-size:8.5pt; font-weight:bold; text-align:center; vertical-align:middle; line-height:1.05; }
.year-header { text-align:center; vertical-align:middle; line-height:1.05; }
.col-no { text-align:center; }
.benefit-table td.col-no { text-align:center; vertical-align:middle; }
.col-program { text-align:left; }
.benefit-table td.col-program { vertical-align:middle; text-align:left; }
.col-description { text-align:left; }
.benefit-table td.col-description { vertical-align:top; text-align:left; }
.col-year { text-align:center; }
.benefit-table td.col-year { text-align:center; vertical-align:middle; }
.section-row td { background:#fff; font-size:8.5pt; font-weight:bold; text-align:left; vertical-align:middle; padding:1.5mm 1.2mm; line-height:1; }
.description { margin:0; padding:0; }
.pelaksanaan { display:block; margin-top:1.5mm; font-size:8pt; }
tr { page-break-inside:avoid; }
.benefit-table tr { page-break-inside:avoid; }
@media screen { body { background:#e5e5e5; padding:20px; } .document { width:210mm; min-height:297mm; margin:0 auto; padding:11mm 7mm; background:#fff; box-shadow:0 0 10px rgba(0,0,0,.15); } }
@media print { body { background:#fff; padding:0; } .document { width:100%; margin:0; padding:0; box-shadow:none; } }
</style>
</head>
<body><div class="Section1"><div class="document">
<div class="document-number">Lampiran II - No : ' . esc($no_pk) . '</div>
<table class="logo-table"><tr>
<td class="logo-left">' . ($logo_program ? '<img src="' . $logo_program . '" class="logo-program" alt="Program Prestasi">' : '') . '</td>
<td class="logo-right">' . ($logo_mentari ? '<img src="' . $logo_mentari . '" class="logo-mentari" alt="Mentari">' : '') . '</td>
</tr></table>
<div class="document-title">MANFAAT PROGRAM ' . esc($program_title) . '</div>
<div class="introduction">
<p>Sebagai bentuk komitmen dukungan dari Mentari Group atas Perjanjian Kerja Sama Program ' . esc($program_title) . ' antara Mentari Group<br>
dan ' . esc($school_name) . ' dengan Ref. No : ' . esc($no_pk) . ', yang ditandatangani pada<br>
' . esc($date) . ', berikut adalah daftar dukungan yang kami berikan untuk sekolah:</p>
</div>
<table class="benefit-table"><colgroup><col class="col-no"><col class="col-program"><col class="col-description"><col class="col-year"><col class="col-year"><col class="col-year"></colgroup>
<thead><tr>
<th class="col-no">No</th>
<th class="col-program">Jenis Kegiatan/Program</th>
<th class="col-description">Deskripsi</th>
<th class="col-year year-header">(durasi/guru/<br>siswa)<br>Tahun ke-1</th>
<th class="col-year year-header">(durasi/guru/<br>siswa)<br>Tahun ke 2</th>
<th class="col-year year-header">(durasi/guru/<br>siswa)<br>Tahun ke 3</th>
</tr></thead><tbody>';

$counter = 1;
$sectionCounter = 1;
foreach ($grouped as $section => $items) {
    if (empty($items)) continue;
    $html .= '<tr class="section-row"><td colspan="6">' . $sectionCounter . '. MANFAAT UNTUK ' . esc(strtoupper($section)) . '</td></tr>';
    $sectionCounter++;
    foreach ($items as $item) {
        $qty1 = $item['qty1'] ?? $item['qty'] ?? 0;
        $qty2 = $item['qty2'] ?? 0;
        $qty3 = $item['qty3'] ?? 0;
        $name = $item['benefit_name'] ?? $item['benefit'] ?? '-';
        $desc = formatDescription($item['description'] ?? '');
        if ($pel = trim((string)($item['pelaksanaan'] ?? ''))) {
            $desc .= '<span class="pelaksanaan">' . esc($pel) . '</span>';
        }
        $html .= '<tr>
            <td class="col-no">' . $counter . '</td>
            <td class="col-program">' . esc($name) . '</td>
            <td class="col-description">' . $desc . '</td>
            <td class="col-year">' . displayQty($qty1) . '</td>
            <td class="col-year">' . displayQty($qty2) . '</td>
            <td class="col-year">' . displayQty($qty3) . '</td>
        </tr>';
        $counter++;
    }
}
$html .= '</tbody></table></div></div></body></html>';

// ========== OUTPUT ==========
if (isset($_GET['download'])) {
    $filename = 'Benefit - ' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $draft['school_name2'] ?? 'School') . '.doc';
    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo $html;
    exit;
}
echo $html;