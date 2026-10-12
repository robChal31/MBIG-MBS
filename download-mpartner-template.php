<?php
session_start();
include 'db_con.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data');

// Header: 7 kolom
$headers = ['Name', 'Email', 'Phone Number', 'School ID', 'School Name', 'PK ID', 'No PK'];
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:G1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
    'alignment' => ['horizontal' => 'center'],
]);

// Highlight kolom validasi (School ID & PK ID)
$sheet->getStyle('D1')->getFill()->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setRGB('F59E0B');
$sheet->getStyle('F1')->getFill()->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setRGB('F59E0B');

// Sample row
$sheet->fromArray(
    ['John Doe', 'john@example.com', '08123456789', 123, 'Sample School Name', '101, 102', 'PK001, PK002'],
    null, 'A2'
);

foreach (['A'=>25, 'B'=>30, 'C'=>20, 'D'=>12, 'E'=>35, 'F'=>20, 'G'=>40] as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

/* ===== Sheet Reference ===== */
$refSheet = $spreadsheet->createSheet();
$refSheet->setTitle('Reference');

// --- Blok A: Schools ---
$refSheet->setCellValue('A1', 'School ID');
$refSheet->setCellValue('B1', 'School Name');
$refSheet->getStyle('A1:B1')->getFont()->setBold(true);

$q = mysqli_query($conn, "SELECT id, name FROM schools WHERE name IS NOT NULL ORDER BY name ASC");
$r = 2;
while ($row = mysqli_fetch_assoc($q)) {
    $refSheet->setCellValue('A' . $r, $row['id']);
    $refSheet->setCellValue('B' . $r, $row['name']);
    $r++;
}

// --- Blok B: PK ---
$refSheet->setCellValue('D1', 'PK ID');
$refSheet->setCellValue('E1', 'No PK');
$refSheet->setCellValue('F1', 'School');
$refSheet->setCellValue('G1', 'Program');
$refSheet->getStyle('D1:G1')->getFont()->setBold(true);

$q = mysqli_query($conn, "
    SELECT pk.id, pk.no_pk,
           IFNULL(sc.name, db.school_name) AS school_name,
           IFNULL(prog.name, db.program) AS program
    FROM pk
    LEFT JOIN draft_benefit db ON db.id_draft = pk.benefit_id
    LEFT JOIN programs prog ON (prog.name = db.program OR prog.code = db.program)
    LEFT JOIN schools sc ON sc.id = db.school_name
    WHERE db.deleted_at IS NULL
    ORDER BY pk.id ASC
");
$r = 2;
while ($row = mysqli_fetch_assoc($q)) {
    $refSheet->setCellValue('D' . $r, $row['id']);
    $refSheet->setCellValue('E' . $r, $row['no_pk']);
    $refSheet->setCellValue('F' . $r, $row['school_name']);
    $refSheet->setCellValue('G' . $r, $row['program']);
    $r++;
}
foreach (['A'=>12, 'B'=>40, 'D'=>12, 'E'=>25, 'F'=>35, 'G'=>30] as $c => $w) {
    $refSheet->getColumnDimension($c)->setWidth($w);
}

$spreadsheet->setActiveSheetIndex(0);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="mpartner-bulk-template.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;