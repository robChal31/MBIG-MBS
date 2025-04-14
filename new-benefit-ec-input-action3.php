<?php
ob_start();
session_start();
include 'db_con.php';
require 'vendor/autoload.php';
if (!isset($_SESSION['username'])){ 
    header("Location: ./index.php");
    exit();
}

$id_draft       = ISSET($_POST['id_draft']) ? $_POST['id_draft'] : NULL;
$program_ref    = ISSET($_POST['program_reffered']) ? $_POST['program_reffered'] : NULL;
$id_user        = $_POST['id_user'];
$inputEC        = $_POST['inputEC'];
$school_name    = $_POST['nama_sekolah'];
$segment        = $_POST['segment'];
$program        = $_POST['program'];
$aloks          = $_POST['alokasi'];
$book_titles    = $_POST['titles'];
$wilayah        = $_POST['wilayah'];
$level          = $_POST['level'];
$program_year   = $_POST['program_year'];
$row_length     = count($book_titles);

$alokasi        = 0;
$id_school      = $school_name;
$pk_type        = $program_year == 1 ? 0 : 1;

for($i = 0; $i < $row_length; $i++){
    $alokasi += preg_replace("/[^0-9-]/", "", $aloks[$i]); 
}

try {
    mysqli_begin_transaction($conn);

    if($id_draft){
        $sql = "UPDATE draft_benefit SET 
                    id_user = '$id_user',
                    id_ec = '$inputEC',
                    school_name = '$id_school',
                    segment = '$segment',
                    program = '$program',
                    wilayah = '$wilayah',
                    level   = '$level',
                    total_benefit = '0',
                    selisih_benefit = '0',
                    fileUrl = '',
                    updated_at = current_timestamp(),
                    status = '0',
                    alokasi = $alokasi,
                    year = $program_year
                WHERE id_draft = $id_draft";
        mysqli_query($conn, $sql);
    } else {
        $sql = "INSERT INTO `draft_benefit` (`id_draft`, `id_user`,`id_ec`, `school_name`, `segment`,`program`, `date`, `status`, `alokasi`, `wilayah`, `level`, `ref_id`, `year`, `jenis_pk`)
                VALUES (NULL, '$id_user','$inputEC', '$id_school', '$segment','$program', current_timestamp(), '0', $alokasi, '$wilayah', '$level', '$program_ref', '$program_year', '$pk_type')";
        mysqli_query($conn, $sql);
        $id_draft = mysqli_insert_id($conn);
    }

    $book_levels    = $_POST['levels'];
    $book_type      = $_POST['booktype'];
    $jumlah_siswas  = $_POST['jumlahsiswa'];
    $usulan_hargas  = $_POST['usulanharga'];
    $normals        = $_POST['harganormal'];
    $diskons        = $_POST['diskon'];
    
    $sumalok        = 0;

    if($id_draft){
        mysqli_query($conn, "DELETE FROM `calc_table` WHERE id_draft = '$id_draft'");
        mysqli_query($conn, "DELETE FROM `draft_benefit_list` WHERE id_draft = '$id_draft'");
        mysqli_query($conn, "DELETE FROM `draft_approval` WHERE id_draft = '$id_draft'");
    }

    for($i = 0; $i < $row_length; $i++){
        $jumlah_siswas[$i]  = preg_replace("/[^0-9]/", "", $jumlah_siswas[$i]);
        $usulan_hargas[$i]  = preg_replace("/[^0-9]/", "", $usulan_hargas[$i]);
        $normals[$i]        = preg_replace("/[^0-9]/", "", $normals[$i]);
        $diskons[$i]        = preg_replace("/[^0-9]/", "", $diskons[$i]);
        $aloks[$i]          = preg_replace("/[^0-9-]/", "", $aloks[$i]);
        $new_title          = $book_titles[$i]." | ".$book_levels[$i]." | ".$book_type[$i];

        $sql = "INSERT INTO `calc_table` (`id_row`, `id_draft`, `book_title`, `qty`, `usulan_harga`, `normalprice`, `discount`, `alokasi`, `year`)
                VALUES (NULL, '$id_draft', '$new_title','$jumlah_siswas[$i]', '$usulan_hargas[$i]','$normals[$i]', '$diskons[$i]', '$aloks[$i]', '$program_year')";
        mysqli_query($conn, $sql);
        $sumalok += $aloks[$i];
    }

    $query = "SELECT * FROM draft_benefit_list WHERE id_draft = $program_ref";
    $result = mysqli_query($conn, $query);

    while ($row = $result->fetch_assoc()) {
        $status = mysqli_real_escape_string($conn, $row['status']);
        $isDeleted = mysqli_real_escape_string($conn, $row['isDeleted']);
        $benefit_name = mysqli_real_escape_string($conn, $row['benefit_name']);
        $subbenefit = mysqli_real_escape_string($conn, $row['subbenefit']);
        $description = mysqli_real_escape_string($conn, $row['description']);
        $keterangan = mysqli_real_escape_string($conn, $row['keterangan']);
        $qty = mysqli_real_escape_string($conn, $row['qty']);
        $qty2 = mysqli_real_escape_string($conn, $row['qty2']);
        $qty3 = mysqli_real_escape_string($conn, $row['qty3']);
        $pelaksanaan = mysqli_real_escape_string($conn, $row['pelaksanaan']);
        $type = mysqli_real_escape_string($conn, $row['type']);
        $manualValue = mysqli_real_escape_string($conn, $row['manualValue']);
        $calcValue = mysqli_real_escape_string($conn, $row['calcValue']);
        $id_template = mysqli_real_escape_string($conn, $row['id_template']);

        $insert = "INSERT INTO draft_benefit_list (`id_benefit_list`, `id_draft`, `status`, `isDeleted`, `benefit_name`, `subbenefit`, `description`, `keterangan`, `qty`, `qty2`, `qty3`, `pelaksanaan`, `type`,`manualValue`,`calcValue`, `id_template`)
                    VALUES (NULL, $id_draft, '$status', '$isDeleted', '$benefit_name', '$subbenefit', '$description', '$keterangan', '$qty', '$qty2', '$qty3', '$pelaksanaan', '$type','$manualValue','$calcValue', '$id_template')";
        mysqli_query($conn, $insert);
    }

    $pk_query = "SELECT * FROM pk WHERE benefit_id = $program_ref";
    $pk_result = mysqli_query($conn, $pk_query);
    
    if (mysqli_num_rows($pk_result) > 0) {
        $pk_row = mysqli_fetch_assoc($pk_result);

        $no_pk                  = $pk_row['no_pk'];
        $start_date             = $pk_row['start_at'];
        $end_date               = $pk_row['expired_at'];
        $id_sa                  = $pk_row['sa_id'];
        $target_file_pk         = $pk_row['file_pk'];
        $target_file_benefit    = $pk_row['file_benefit'];
    
        $insert_query = "INSERT INTO pk (benefit_id, no_pk, start_at, expired_at, sa_id, file_pk, file_benefit, created_at, updated_at) 
                        VALUES ($id_draft, '$no_pk', '$start_date', '$end_date', $id_sa, '$target_file_pk', '$target_file_benefit', current_timestamp(), NULL)";
    
        mysqli_query($conn, $insert_query);
    }
    
    mysqli_commit($conn);
    $_SESSION['sumalok']        = $sumalok;
    $_SESSION['id_draft']       = $id_draft;
    $_SESSION['program']        = $program;
    $_SESSION['school_name']    = $id_school;
    $_SESSION['segment']        = $segment;

    $location = 'Location: ./new-benefit-ec-input2.php?edit=edit&id_draft='.$id_draft; 
    mysqli_close($conn);
    header($location);
    exit();
} catch (\Throwable $th) {
    mysqli_rollback($conn);
    // var_dump($th);die;
    $_SESSION['toast_status'] = 'Error';
    $_SESSION['toast_msg'] = 'Gagal Menyimpan Draft Benefit ' . $th->getMessage();

    $location = 'Location: ./draft-benefit.php';
    mysqli_close($conn);
    header($location);
    exit();
}
?>
