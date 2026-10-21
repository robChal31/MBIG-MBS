<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include 'db_con.php';
session_start();

// Kalau mysqli throw exception, biar ke-catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
        p.code AS program_code, pc.name AS program_category, pc.logo_file AS program_logo_file, pk.id as pk_id, pk.no_pk
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
$sql = "SELECT 
            dbl.*, b.benefit, b.benefit_name, b.subbenefit, b.description, b.pelaksanaan, b.allow_multiple_subject_and_level, b.count_method, b.countable,
            dbl.qty, dbl.qty2, dbl.qty3, b.qty1 as qty1_template, b.qty2 as qty2_template, b.qty3 as qty3_template, b.satuan,
            COUNT(DISTINCT bsl.id) AS total_combination,
            COUNT(DISTINCT bsl.subject_id) AS total_subject,
            COUNT(DISTINCT bol.level_id) AS total_level,
            COALESCE(
                GROUP_CONCAT(
                    DISTINCT CONCAT(s.name, '|', ol.name)
                    SEPARATOR ', '
                ), 
                ''
            ) AS subject_level_pairs,
            COALESCE(
                GROUP_CONCAT(
                    DISTINCT bs.name
                    ORDER BY bs.name
                    SEPARATOR ', '
                ), 
                ''
            ) AS book_series_names
        FROM draft_benefit_list dbl
        LEFT JOIN draft_template_benefit b ON b.id_template_benefit = dbl.id_template
        LEFT JOIN benefit_org_levels bol ON bol.draft_benefit_list_id = dbl.id_benefit_list
        LEFT JOIN benefit_subject_levels bsl ON bsl.benefit_org_levels_id = bol.id
        LEFT JOIN subjects s ON s.id = bsl.subject_id
        LEFT JOIN levels ol ON ol.id = bol.level_id
        LEFT JOIN benefit_book_series bbs ON bbs.draft_benefit_list_id = dbl.id_benefit_list
        LEFT JOIN book_series bs ON bs.id = bbs.book_series_id
        WHERE dbl.id_draft = $id_draft
        GROUP BY dbl.id_benefit_list 
        ORDER BY dbl.id_benefit_list ASC
        ";
$benefit_result = mysqli_query($conn, $sql);
$benefits = $benefit_result ? mysqli_fetch_all($benefit_result, MYSQLI_ASSOC) : [];

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

    // 🔥 Decode HTML entities dulu (&#38; → &, &amp; → &, dll)
    $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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

function formatSubjectLevelBreakdown($qty, $satuan, $item)
{
    $countMethod = $item['count_method'] ?? 'template';

    // 🔥 Cuma subject_level yang punya breakdown
    if ($countMethod !== 'subject_level') return '';

    if ($qty === null || $qty === '' || (is_numeric($qty) && (float)$qty == 0)) return '';
    if (empty($item['subject_level_pairs'])) return '';

    $qty    = (float)$qty;
    $satuan = trim((string)$satuan);

    // Parse pairs
    $pairs = [];
    foreach (explode(',', $item['subject_level_pairs']) as $pair) {
        $pair = trim($pair);
        if ($pair === '') continue;
        $parts = explode('|', $pair);
        $subj = trim($parts[0] ?? '');
        $lvl  = trim($parts[1] ?? '');
        if ($subj === '' && $lvl === '') continue;
        $pairs[] = ['subject' => $subj, 'level' => $lvl];
    }

    usort($pairs, function($a, $b) {
        $cmp = strcasecmp($a['level'], $b['level']);
        if ($cmp !== 0) return $cmp;
        return strcasecmp($a['subject'], $b['subject']);
    });


    if (empty($pairs)) return '';

    // 🔥 Per kombinasi = qty_db / jumlah kombinasi
    $qtyPerCombination = $qty / count($pairs);

    if (floor($qtyPerCombination) == $qtyPerCombination) {
        $qtyText = (int)$qtyPerCombination;
    } else {
        $qtyText = rtrim(rtrim(number_format($qtyPerCombination, 2, '.', ''), '0'), '.');
    }

    $html  = '<div class="breakdown">';
    $html .= '<span class="breakdown-title">Rincian per Subjek &amp; Level:</span>';

    foreach ($pairs as $pair) {
        $line = '&bull; ';
        if ($pair['subject'] !== '' && $pair['level'] !== '') {
            $line .= esc($pair['subject']) . ' (' . esc($pair['level']) . ')';
        } elseif ($pair['subject'] !== '') {
            $line .= esc($pair['subject']);
        } else {
            $line .= esc($pair['level']);
        }
        $line .= ' : ' . esc($qtyText);
        if ($satuan !== '') {
            $line .= ' ' . esc($satuan);
        }
        $html .= '<span class="breakdown-line">' . $line . '</span>';
    }

    $html .= '</div>';
    return $html;
}

function formatBookSeriesList($item)
{
    if (empty($item['book_series_names'])) {
        return '';
    }

    // Parse nama series
    $series = array_filter(
        array_map('trim', explode(',', $item['book_series_names']))
    );

    if (empty($series)) return '';

    $html  = '<div class="book-series">';
    $html .= '<span class="book-series-title">Untuk Penggunaan:</span>';

    foreach ($series as $s) {
        $html .= '<span class="book-series-line">&bull; ' . esc($s) . '</span>';
    }

    $html .= '</div>';

    return $html;
}
$logo_program = imageToDataUri(__DIR__ . '/img/prestasi-logo.png');
$logo_mentari = imageToDataUri(__DIR__ . '/img/comp-logo.png');
// Ambil nama file dari DB, fallback ke default kalau kosong
$logo_file = trim($draft['program_logo_file'] ?? '');
if ($logo_file !== '' && file_exists(__DIR__ . '/img/' . $logo_file)) {
    $logo_program = imageToDataUri(__DIR__ . '/img/' . $logo_file);
} else {
    // Fallback kalau logo belum di-set di DB
    $logo_program = imageToDataUri(__DIR__ . '/img/prestasi-logo.png');
}

// ==================================================
// 5. DATA UNTUK VIEW
// ==================================================
$program_title = trim(preg_replace('/^PROGRAM\s+/i', '', strtoupper($draft['program_category'] ?? $draft['program_name'] ?? 'PRESTASI'))) ?: 'PRESTASI';
$school_name   = strtoupper($draft['school_name2'] ?? $draft['school_name'] ?? '-');
$no_pk         = $draft['no_pk'] ?? '-';
$date          = formatTanggal($draft['created_at'] ?? date('Y-m-d'));
$ec_name       = $draft['ec_name'] ?? 'EC';

function displayBenefitQty($qty, $satuan, $item)
{
    if ($qty === null || $qty === '' || (is_numeric($qty) && (float)$qty == 0)) {
        return '-';
    }

    $qty    = (float)$qty;
    $satuan = trim((string)$satuan);

    $countMethod = $item['count_method'] ?? 'template';

    // 🔥 Hanya subject_level yang pakai breakdown
    if ($countMethod !== 'subject_level') {
        $text = (floor($qty) == $qty) ? (int)$qty : $qty;
        $text = esc($text);
        if ($satuan !== '') {
            $text .= ' ' . esc($satuan);
        }
        return $text;
    }

    // Parse pairs
    $pairs = [];
    if (!empty($item['subject_level_pairs'])) {
        foreach (explode(',', $item['subject_level_pairs']) as $pair) {
            $pair = trim($pair);
            if ($pair === '') continue;
            $parts = explode('|', $pair);
            $pairs[] = [
                'subject' => trim($parts[0] ?? ''),
                'level'   => trim($parts[1] ?? ''),
            ];
        }
    }

    if (empty($pairs)) {
        $text = (floor($qty) == $qty) ? (int)$qty : $qty;
        $text = esc($text);
        if ($satuan !== '') {
            $text .= ' ' . esc($satuan);
        }
        return $text;
    }

    // 🔥 Per kombinasi = qty_db / jumlah kombinasi
    $qtyPerCombination = $qty / count($pairs);

    if (floor($qtyPerCombination) == $qtyPerCombination) {
        $qtyText = (int)$qtyPerCombination;
    } else {
        $qtyText = rtrim(rtrim(number_format($qtyPerCombination, 2, '.', ''), '0'), '.');
    }

    $results = [];
    foreach ($pairs as $pair) {
        $text = esc($qtyText);
        if ($satuan !== '') {
            $text .= ' ' . esc($satuan);
        }
        if ($pair['subject'] !== '') {
            $text .= ' / ' . esc($pair['subject']);
        }
        if ($pair['level'] !== '') {
            $text .= ' / ' . esc($pair['level']);
        }
        $results[] = $text;
    }

    return implode('<br>', $results);
}
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
            vertical-align: middle;
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

        .breakdown {
            margin-top: 2mm;
            padding-top: 1.5mm;
            border-top: 1px dashed #999;
            font-size: 7.5pt;
            line-height: 1.3;
        }
        .breakdown-title {
            display: block;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 0.8mm;
        }
        .breakdown-line {
            display: block;
        }
        /* ===== BOOK SERIES ===== */
        .book-series {
            margin-top: 2mm;
            padding-top: 1.5mm;
            border-top: 1px dashed #999;
            font-size: 7.5pt;
            line-height: 1.3;
        }
        .book-series-title {
            display: block;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 0.8mm;
        }
        .book-series-line {
            display: block;
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
                ...., berikut adalah daftar dukungan yang kami berikan untuk sekolah:
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
                $qty1 = $item['qty'] ?? 0;
                $qty2 = $item['qty2'] ?? 0;
                $qty3 = $item['qty3'] ?? 0;
                $name = $item['benefit_name'] ?? $item['benefit'] ?? '-';
                $cleanedName = cleanBenefitName($name);
                $desc = formatDescription($item['description'] ?? '');

                if ($pel = trim((string)($item['pelaksanaan'] ?? ''))) {
                    $desc .= '<span class="pelaksanaan">' . esc($pel) . '</span>';
                }

                // 🔥 Breakdown per subjek & level — conditional by count_method (sudah dihandle di function)
                $breakdown = formatSubjectLevelBreakdown($qty1, $item['satuan'] ?? '', $item);
                if ($breakdown !== '') {
                    $desc .= $breakdown;
                }
                // 🔥 Book Series
                $bookSeriesHtml = formatBookSeriesList($item);
                if ($bookSeriesHtml !== '') {
                    $desc .= $bookSeriesHtml;
                }


            ?>
                <tr>
                    <td class="col-no"><?= $no++ ?></td>
                    <td class="col-program"><?= esc($cleanedName) ?></td>
                    <td class="col-description">
                        <?= $desc ?>
                    </td>
                    <?php 
                        if($item['countable'] == 1) { ?>
                            <td class="col-year">
                                <!-- <?= displayBenefitQty($qty1, $item['satuan'] ?? '', $item) ?> -->
                                <?= $qty1 ? ($qty1 . ' ' . $item['satuan']) : '-' ?>
                            </td>
                            <td class="col-year">
                                <!-- <?= displayBenefitQty($qty2, $item['satuan'] ?? '', $item) ?> -->
                                <?= $qty2 ? ($qty2 . ' ' . $item['satuan']) : '-' ?>
                            </td>
                            <td class="col-year">
                                <!-- <?= displayBenefitQty($qty3, $item['satuan'] ?? '', $item) ?> -->
                                <?= $qty3 ? ($qty3 . ' ' . $item['satuan']) : '-' ?>
                            </td>
                    <?php }else{ ?>
                            <td colspan="3" style="text-align: center; vertical-align: middle;">Sesuai ketentuan</td>
                    <?php } ?>
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