<?php
include 'db_con.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ./index.php");
    exit();
}

$id_draft = isset($_GET['id_draft']) ? intval($_GET['id_draft']) : 0;
if ($id_draft <= 0) die('ID Draft tidak valid');

// ==================================================
// 1. AMBIL DATA DRAFT
// ==================================================
$sql = "SELECT db.*, sc.name AS school_name2, ec.generalname AS ec_name, p.name AS program_name, 
        p.code AS program_code, pc.name AS program_category, pk.id as pk_id, pk.no_pk
        FROM draft_benefit db
        LEFT JOIN schools sc ON sc.id = db.school_name
        LEFT JOIN user ec ON ec.id_user = db.id_ec
        LEFT JOIN programs p ON p.name = db.program OR p.code = db.program
        LEFT JOIN program_categories pc ON pc.id = p.program_category_id
        LEFT JOIN pk as pk ON pk.benefit_id = db.id_draft
        WHERE db.id_draft = $id_draft
        LIMIT 1";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$draft = mysqli_fetch_assoc($result) or die('Data draft tidak ditemukan');

// ==================================================
// 2. AMBIL DATA BENEFIT
// ==================================================
$sql = "SELECT dbl.*, b.benefit, b.benefit_name, b.subbenefit, b.description, b.pelaksanaan, b.qty1, b.qty2, b.qty3, b.satuan
        FROM draft_benefit_list dbl
        LEFT JOIN draft_template_benefit b ON b.id_template_benefit = dbl.id_template
        WHERE dbl.id_draft = $id_draft
        ORDER BY dbl.id_benefit_list ASC";
$benefits = mysqli_query($conn, $sql) ? mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC) : [];

// ==================================================
// 3. HELPER FUNCTIONS
// ==================================================
function esc($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function displayQty($v) {
    if ($v === null || $v === '' || (is_numeric($v) && (float)$v == 0)) {
        return '-';
    }
    return esc($v);
}

function formatTanggal($date) {
    if (!$date) return '-';
    $t = strtotime($date);
    if (!$t) return $date;
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function formatDescription($text) {
    $text = strip_tags((string)$text);
    $text = esc($text);
    return nl2br($text, false);
}

// ==================================================
// 4. LOGO (BASE64)
// ==================================================
function imageToDataUri($path) {
    if (!file_exists($path)) return '';
    return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
}

function cleanBenefitName($string)
{
    if (!is_string($string) || $string === '') {
        return '';
    }

    // Hapus nomor di awal: "1. ", "2. ", "10. ", dst
    $string = preg_replace('/^\d+\.\s*/', '', trim($string));

    // Daftar kata lokasi yang akan dihapus (case-insensitive)
    $removePatterns = [
        '/\(?Luar\s*Jabodetabek\)?/i',
        '/\(?Jabodetabek\)?/i'
    ];

    foreach ($removePatterns as $pattern) {
        $string = preg_replace($pattern, '', $string);
    }

    // Bersihkan spasi berlebih
    $string = preg_replace('/\s+/', ' ', $string);
    $string = trim($string);

    return $string;
}

$logo_program = imageToDataUri(__DIR__ . '/img/prestasi-logo.png');
$logo_mentari = imageToDataUri(__DIR__ . '/img/comp-logo.png');

// ==================================================
// 5. DATA UNTUK VIEW
// ==================================================
$program_title = trim(preg_replace('/^PROGRAM\s+/i', '', strtoupper($draft['program_category'] ?? $draft['program_name'] ?? 'PRESTASI'))) ?: 'PRESTASI';
$school_name   = strtoupper($draft['school_name2'] ?? $draft['school_name'] ?? '-');
$no_pk         = $draft['no_pk'] ?? '-';
$date          = formatTanggal($draft['created_at'] ?? date('Y-m-d'));
$ec_name       = $draft['ec_name'] ?? 'EC';

// ==================================================
// 6. HTML TEMPLATE
// ==================================================
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Benefit - <?= esc($school_name) ?></title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:DoNotOptimizeForBrowser/>
            <w:AllowPNG/>
            <w:DoNotPromoteQF/>
            <w:Compatibility>
                <w:UsePrinterMetrics/>
                <w:DoNotUseHTMLParagraphAutoSpacing/>
            </w:Compatibility>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        /* ===== PAGE SETUP (A4) ===== */
        @page {
            size: 21cm 29.7cm;
            margin: 1.10cm 0.70cm 1.10cm 0.70cm;
            mso-page-orientation: portrait;
        }
        @page Section1 {
            size: 21cm 29.7cm;
            margin: 1.10cm 0.70cm 1.10cm 0.70cm;
            mso-page-orientation: portrait;
        }
        div.Section1 { page: Section1; }

        /* ===== BODY ===== */
        body {
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .document {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* ===== HEADER ===== */
        .document-number {
            width: 100%;
            height: 6mm;
            text-align: right;
            font-weight: bold;
            font-size: 9pt;
            white-space: nowrap;
        }
        .logo-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }
        .logo-table td {
            border: none;
            padding: 0;
            margin: 0;
            vertical-align: top;
        }
        .logo-left {
            width: 50%;
            text-align: left;
        }
        .logo-right {
            width: 50%;
            text-align: right;
        }
        .logo-program {
            display: block;
            width: 53mm;
            height: auto;
        }
        .logo-mentari {
            display: inline-block;
            width: 49mm;
            height: auto;
        }

        /* ===== TITLE ===== */
        .document-title {
            width: 100%;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            line-height: 1;
            margin-top: 8mm;
            margin-bottom: 6mm;
        }

        /* ===== INTRO ===== */
        .introduction {
            width: 100%;
            text-align: center;
            font-size: 9pt;
            line-height: 1.15;
            margin: 0 0 5.5mm 0;
            padding: 0;
        }
        .introduction p {
            margin: 0;
            padding: 0;
        }

        /* ===== TABLE ===== */
        .benefit-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.5pt;
            line-height: 1.12;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        .benefit-table col.col-no         { width: 11mm; }
        .benefit-table col.col-program    { width: 47mm; }
        .benefit-table col.col-description{ width: 69mm; }
        .benefit-table col.col-year       { width: 23mm; }

        .benefit-table th,
        .benefit-table td {
            border: 1px solid #000;
            color: #000;
            padding: 2mm 1.2mm;
            vertical-align: top;
        }
        /* Header */
        .benefit-table thead {
            display: table-header-group;
        }
        .benefit-table thead tr {
            page-break-after: avoid;
        }
        .benefit-table thead th {
            height: 16mm;
            padding: 1.2mm 1mm;
            font-size: 10.5pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            line-height: 1.05;
        }
        .year-header {
            text-align: center;
            vertical-align: middle;
            line-height: 1.05;
        }

        /* Kolom */
        .col-no         { text-align: center; }
        .col-program    { text-align: left; }
        .col-description{ text-align: left; }
        .col-year       { text-align: center; }

        .benefit-table td.col-no         { text-align: center; vertical-align: middle; }
        .benefit-table td.col-program    { vertical-align: middle; text-align: left; }
        .benefit-table td.col-description{ vertical-align: top; text-align: left; }
        .benefit-table td.col-year       { text-align: center; vertical-align: middle; }

        /* Deskripsi */
        .description {
            margin: 0;
            padding: 0;
        }
        .pelaksanaan {
            display: block;
            margin-top: 1.5mm;
            font-size: 8pt;
        }

        /* Page Break */
        tr {
            page-break-inside: avoid;
        }
        .benefit-table tr {
            page-break-inside: avoid;
        }

        /* ===== PREVIEW (layar) ===== */
        @media screen {
            body {
                background: #e5e5e5;
                padding: 20px;
            }
            .document {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                padding: 11mm 7mm;
                background: #fff;
                box-shadow: 0 0 10px rgba(0,0,0,.15);
            }
        }

        /* ===== PRINT ===== */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .document {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="Section1">
    <div class="document">

        <!-- ============================================ -->
        <!-- HEADER : Nomor Dokumen & Logo                  -->
        <!-- ============================================ -->

        <div class="document-number">
            Lampiran II - No : <?= esc($no_pk) ?>
        </div>

        <table class="logo-table">
            <tr>
                <td class="logo-left">
                    <?php if ($logo_program): ?>
                        <img src="<?= $logo_program ?>" class="logo-program" alt="Program Prestasi">
                    <?php endif; ?>
                </td>
                <td class="logo-right">
                    <?php if ($logo_mentari): ?>
                        <img src="<?= $logo_mentari ?>" class="logo-mentari" alt="Mentari">
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- ============================================ -->
        <!-- TITLE                                        -->
        <!-- ============================================ -->

        <div class="document-title">
            MANFAAT PROGRAM <?= esc($program_title) ?>
        </div>

        <!-- ============================================ -->
        <!-- INTRODUKSI                                   -->
        <!-- ============================================ -->

        <div class="introduction">
            <p>
                Sebagai bentuk komitmen dukungan dari Mentari Group atas Perjanjian Kerja Sama Program <?= esc($program_title) ?>
                antara Mentari Group<br>
                dan <?= esc($school_name) ?> dengan Ref. No : <?= esc($no_pk) ?>, yang ditandatangani pada<br>
                <?= esc($date) ?>, berikut adalah daftar dukungan yang kami berikan untuk sekolah:
            </p>
        </div>

        <!-- ============================================ -->
        <!-- TABEL BENEFIT                               -->
        <!-- ============================================ -->

        <table class="benefit-table">
            <colgroup>
                <col class="col-no">
                <col class="col-program">
                <col class="col-description">
                <col class="col-year">
                <col class="col-year">
                <col class="col-year">
            </colgroup>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-program">Jenis Kegiatan/Program</th>
                    <th class="col-description">Deskripsi</th>
                    <th class="col-year year-header">Tahun ke 1</th>
                    <th class="col-year year-header">Tahun ke 2</th>
                    <th class="col-year year-header">Tahun ke 3</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            foreach ($benefits as $item):
                $qty1 = $item['qty1'] ?? $item['qty'] ?? 0;
                $qty2 = $item['qty2'] ?? 0;
                $qty3 = $item['qty3'] ?? 0;
                $name = $item['benefit_name'] ?? $item['benefit'] ?? '-';
                $cleanedName = cleanBenefitName($name);
                $desc = formatDescription($item['description'] ?? '');
                if ($pel = trim((string)($item['pelaksanaan'] ?? ''))) {
                    $desc .= '<span class="pelaksanaan">' . esc($pel) . '</span>';
                }
            ?>
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="col-program"><?= esc($cleanedName) ?></td>
                    <td class="col-description"><?= $desc ?></td>
                    <td class="col-year"><?= displayQty($qty1) ?> <?= $item['satuan'] && $qty1 ? $item['satuan'] : '' ?></td>
                    <td class="col-year"><?= displayQty($qty2) ?> <?= $item['satuan'] && $qty2 ? $item['satuan'] : '' ?></td>
                    <td class="col-year"><?= displayQty($qty3) ?> <?= $item['satuan'] && $qty3 ? $item['satuan'] : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div><!-- end .document -->
</div><!-- end .Section1 -->
</body>
</html>

<?php
// ==================================================
// 7. DOWNLOAD ATAU TAMPILKAN
// ==================================================
if (isset($_GET['download'])) {
    $filename = 'Benefit - ' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $draft['school_name2'] ?? 'School') . '.doc';
    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    // HTML sudah di-echo di atas, kita cukup exit
    exit;
}
// Jika tidak ada ?download, maka otomatis ditampilkan di browser (preview)
?>