<?php

include 'db_con.php';
require 'vendor/autoload.php';
$config = require 'config.php';


$id_draft = 986;

$dbl = [];

$dbl_q = "SELECT bir.id, bir.period, bir.file,
            b.id_draft, b.status, b.date, b.id_user, b.id_ec, b.school_name, b.segment, b.program, IFNULL(sc.name, b.school_name) as school_name2, b.alokasi, b.year, c.generalname, pk.id as pk_id, b.verified, b.deleted_at, b.fileUrl, pk.file_pk, 
            pk.no_pk, pk.start_at, pk.expired_at, pk.created_at, b.confirmed, b.jenis_pk, c.leadId
        FROM benefit_imp_report AS bir
        LEFT JOIN draft_benefit AS b on b.id_draft = bir.id_draft
        LEFT JOIN schools sc on sc.id = b.school_name
        LEFT JOIN user c on c.id_user = b.id_ec 
        LEFT JOIN pk pk on pk.benefit_id = b.id_draft
        WHERE b.id_draft = $id_draft";
$dbl_exec = mysqli_query($conn, $dbl_q);

if ($dbl_exec && mysqli_num_rows($dbl_exec) > 0) {
    $dbl = mysqli_fetch_assoc($dbl_exec);
}

$school_name = $dbl['school_name'] ? trim($dbl['school_name']) : null;
$segment = $dbl['segment'] ?? null;
$program = $dbl['program'] ?? null;
$alokasi = $dbl['alokasi'] ?? null;
$year = $dbl['year'] ?? null;
$generalname = $dbl['generalname'] ?? null;
$no_pk = $dbl['no_pk'] ?? null;
$start_at = $dbl['start_at'] ?? null;
$expired_at = $dbl['expired_at'] ?? null;
$created_at = $dbl['created_at'] ?? null;
$leadId = $dbl['leadId'] ?? null;
$period = $dbl['period'] ?? null;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Implementasi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.5; padding: 0px 25px; margin: 0px; }
        .header { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 30px; }
        .section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 4px solid #000; }
        
        .table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }

        .table th, 
        .table td {
            border: 1px solid #000;
            padding: 5px 10px;
        }

        /* Zebra striping untuk baris */
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <table width="100%" style="margin: 0px; margin-bottom: 10px;">
        <tr>
            <td width="50%">
                <img src="http://localhost/benefit/img/shield.png" width="100" alt="shield Mentari Group">
            </td>
            <td width="50%" align="right">
                <img src="http://localhost/benefit/img/comp-logo.png" width="100" alt="Logo Mentari Group">
            </td>
        </tr>
    </table>

    <div class="header">
        LAPORAN IMPLEMENTASI MANFAAT PERJANJIAN KERJA SAMA<br>
        MENTARI GROUP DAN <span style="text-transform: uppercase"><?= htmlspecialchars($school_name) ?></span>
    </div>

<div class="section">
    <table width="100%" cellpadding="2" cellspacing="0">
        <tr>
            <td width="160"><strong>Nomor PK</strong></td>
            <td width="10">:</td>
            <td><?= htmlspecialchars($no_pk) ?></td>
        </tr>
        <tr>
            <td><strong>Periode Kerja Sama</strong></td>
            <td>:</td>
            <td><?= date('d M Y', strtotime($start_at)) ?> - <?= date('d M Y', strtotime($expired_at)) ?></td>
        </tr>
    </table>
</div>

    <p>Dengan hormat,</p>
    <p>Bapak/Ibu Pimpinan Sekolah<br>Di tempat</p>

    <p>
        Terima kasih atas kepercayaan dan kerja sama yang telah terjalin dengan baik antara Mentari Group dan <span style="text-transform: uppercase"><?=  htmlspecialchars($school_name) ?></span>.
        Sebagai bentuk komitmen kami dalam menjaga dan meningkatkan kualitas kerja sama tersebut, kami menyusun laporan ini sebagai bagian dari pelaksanaan Perjanjian Kerja Sama antara Mentari Group dan <span style="text-transform: uppercase"><?=  htmlspecialchars($school_name) ?></span>. Laporan ini bertujuan untuk memberikan dokumentasi terkait implementasi manfaat yang telah direalisasikan hingga <?= htmlspecialchars($period) ?>.
    </p>

    <p>Sebagai mitra strategis, Mentari Group berkomitmen untuk mendukung peningkatan mutu pembelajaran melalui penyediaan materi ajar berkualitas, pelatihan guru, serta asesmen siswa sesuai standar internasional.</p>

    <p>
        Berikut adalah tabel implementasi manfaat:<br><br>
    </p>
    <p style="text-align: end; font-size: 10px;">
        TKT = Total Kuota Tahun
        <br>
        TPT = Total Penggunaan Per Tahun
    </p>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Manfaat</th>
                <th>Deskripsi</th>
                <th>TKT1</th>
                <th>TPT1</th>
                <th>TKT2</th>
                <th>TPT2</th>
                <th>TKT3</th>
                <th>TPT3</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $sql = "SELECT dbl.*, bu.*, dbt.redeemable
                        FROM draft_benefit_list AS dbl
                        LEFT JOIN (
                            SELECT 
                                SUM(COALESCE(bu.qty1, 0)) AS tot_usage1,
                                SUM(COALESCE(bu.qty2, 0)) AS tot_usage2,
                                SUM(COALESCE(bu.qty3, 0)) AS tot_usage3,
                                bu.id_benefit_list as id_bl
                            FROM benefit_usages bu
                            GROUP BY bu.id_benefit_list
                        ) as bu on bu.id_bl = dbl.id_benefit_list
                        LEFT JOIN draft_template_benefit AS dbt on dbt.id_template_benefit = dbl.id_template
                        WHERE dbl.id_draft = '$id_draft'";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    $no = 1;
                    while($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                    <td style="width: 5%; padding: 5px; border: 1px solid #000; text-align: center;"><?= $no ?></td>
                    <td style="width: 15%; min-width: 120px; max-width: 25%; padding: 5px; border: 1px solid #000; word-wrap: break-word;"><?= $row['benefit_name'] ?></td>
                    <td style="width: 20%; min-width: 160px; max-width: 30%; padding: 5px; border: 1px solid #000; word-wrap: break-word;"><?= $row['description'] ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= $row['qty'] ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= $row['tot_usage1'] ?? 0 ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= strtolower($program) == 'cbls3' ? $row['qty'] : $row['qty2'] ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= $row['tot_usage2'] ?? 0 ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= strtolower($program) == 'cbls3' ? $row['qty'] : $row['qty3'] ?></td>
                    <td style="width: 8%; padding: 5px; border: 1px solid #000; text-align: end;"><?= $row['tot_usage3'] ?? 0 ?></td>
                </tr>
            <?php $no++;} } ?>
        </tbody>
    </table>
    <p>
        Implementasi manfaat dalam perjanjian kerja sama telah berjalan secara bertahap berdasarkan skala prioritas, sejalan dengan rencana dan komitmen kedua belah pihak. Pelaksanaan kerja sama ini diharapkan memberikan kontribusi nyata terhadap peningkatan kompetensi guru, pimpinan, dan siswa. Mentari Group tetap berkomitmen untuk mendukung kelanjutan program secara konsisten hingga akhir masa perjanjian.
    </p>

    <p>
        Demikian laporan ini disampaikan. Besar harapan kami, kerja sama yang telah terjalin terus memberikan dampak positif dan berkelanjutan bagi pengembangan kualitas pembelajaran di sekolah.
    </p>

    <p style="margin-top: 60px;">
        Salam hangat,<br>
        <strong>Dwinanto Setiawan</strong><br><br><br><br><br>
        National Manager<br>
        Mentari Group
    </p>

</body>
</html>
