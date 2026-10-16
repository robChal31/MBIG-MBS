<?php
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();

/* ============================================================
   SHEET 1: Data
   ============================================================ */
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data');

$headers = ['Name', 'Email', 'Phone Number', 'School ID', 'School Name', 'PK ID', 'No PK'];
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:G1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
    'alignment' => ['horizontal' => 'center'],
]);

$sheet->getStyle('D1')->getFill()->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setRGB('F59E0B');
$sheet->getStyle('F1')->getFill()->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setRGB('F59E0B');

$sheet->fromArray(
    ['John Doe', 'john@example.com', '08123456789', 123, 'Sample School Name', '101, 102', 'PK001, PK002'],
    null, 'A2'
);

foreach (['A'=>25, 'B'=>30, 'C'=>20, 'D'=>12, 'E'=>35, 'F'=>20, 'G'=>40] as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

/* ============================================================
   SHEET 2: Reference — dump apa adanya
   ============================================================ */
$refSheet = $spreadsheet->createSheet();
$refSheet->setTitle('Reference');

$q = mysqli_query($conn, "SELECT
        sc.id           AS school_id,
        pk.id           AS id,
        pk.no_pk        AS no_pk,
        IFNULL(sc.name, db.school_name) AS school_name,
        IFNULL(prog.name, db.program)   AS program,
        pk.start_at     AS start_at,
        pk.expired_at   AS expired_at
    FROM draft_benefit db
    LEFT JOIN pk
           ON pk.benefit_id = db.id_draft
    LEFT JOIN programs prog
           ON (prog.name = db.program OR prog.code = db.program)
    LEFT JOIN schools sc
           ON sc.name = db.school_name OR sc.id = db.school_name
    WHERE db.deleted_at IS NULL
      AND db.confirmed = 1
    ORDER BY db.id_draft ASC, pk.id ASC
");

if (!$q) {
    die('Query error: ' . mysqli_error($conn));
}

// Header = nama kolom hasil query, urut
$refSheet->fromArray(
    ['ID PK', 'No PK', 'School ID', 'School Name', 'Program', 'Start At', 'Expired At'],
    null, 'A1'
);
$refSheet->getStyle('A1:G1')->getFont()->setBold(true);
$refSheet->getStyle('A1:G1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('E5E7EB');

// Isi data
$r = 2;
while ($row = mysqli_fetch_assoc($q)) {
    $refSheet->setCellValue('A' . $r, $row['id']);
    $refSheet->setCellValue('B' . $r, $row['no_pk']);
    $refSheet->setCellValue('C' . $r, $row['school_id']);
    $refSheet->setCellValue('D' . $r, $row['school_name']);
    $refSheet->setCellValue('E' . $r, $row['program']);
    $refSheet->setCellValue('F' . $r, $row['start_at']);
    $refSheet->setCellValue('G' . $r, $row['expired_at']);
    $r++;
}

foreach (['A'=>12, 'B'=>10, 'C'=>38, 'D'=>40, 'E'=>32, 'F'=>14, 'G'=>14] as $c => $w) {
    $refSheet->getColumnDimension($c)->setWidth($w);
}

/* ============================================================
   Output
   ============================================================ */
$spreadsheet->setActiveSheetIndex(0);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="mpartner-bulk-template.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;