<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: https://mentarigroups.com/benefit/index.php");
        exit();
    }
    $id_benefit = $_POST['id_benefit'];
    $id_benefittype = $_POST['id_benefittype'];
    $progress_update = $_POST['progress_update'];
    $ct_note = $_POST['ct_note'];
    $benefit_name = ["-","kolektif","inhouse","mbmta","tdmta","rbmg","assessment","","","cchd","rpp","supervisi","mta","tkt","lg"];
    
    /*

        1 Kolektif      op_kolektif     id_kolektif     perpeserta
        2 Inhouse       op_inhouse      id_inhouse      jumlah_peserta
        3 MBMTA         op_mbmta        id_mbmta        jumlah_peserta
        4 TDMTA         op_tdmta        id_tdmta        perpeserta
        5 RBMG          op_rbmg         id_rbmg         jumlah_peserta
        6 Assessment    op_assessment   id_assessment   perpeserta
        9 CCHD          op_cchd         id_cchd         satuan
       10 RPP           op_rpp          id_rpp          satuan
       11 Supervisi     op_supervisi    id_supervisi    satuan
       12 MTA           op_mta          id_mta          satuan
       13 TKT           op_tkt          id_tkt          perpeserta
       14 LG            op_lg           id_lg           perpeserta

    */

    if($id_benefittype!='11' && $id_benefittype!='2')
    {
        $sql = "UPDATE op_".$benefit_name[$id_benefittype]." set progress_update='$progress_update' where id_".$benefit_name[$id_benefittype]."='$id_benefit'";
    }
    else
    {
        $sql = "UPDATE op_".$benefit_name[$id_benefittype]." set progress_update='$progress_update',ct_note='$ct_note' where id_".$benefit_name[$id_benefittype]."='$id_benefit'";
    }

    if(!mysqli_query($conn,$sql)){
        echo("Error description: " . mysqli_error($conn));
        exit();
    }

    mysqli_close($conn);


