<?php
    ob_start();
    session_start();
    include 'db_con.php';
    if (!isset($_SESSION['username'])){ 
        header("Location: ./index.php");
        exit();
    }
    $id_master = $_POST['id_master'];
    $titleAdopt = $_POST['titleAdopt'];
    //$titleAdopt = implode(", ",$_POST['titleAdopt']);
    $titleAdopt=str_replace('&#34;','"',$titleAdopt);
    $titleAdopt = json_decode($titleAdopt,false );
    $tits = '';
    foreach($titleAdopt as $tit)
    {
        $tits=$tits." ".$tit->value.",";
    }
    $tits = trim($tits, ' ');
    $tits = trim($tits, ',');
    
    $sql = "UPDATE op_masterdata set title='$tits' where id_master='$id_master'";
    echo $sql;
    if(!mysqli_query($conn,$sql)){
        echo("Error description: " . mysqli_error($conn));
        exit();
    }

    mysqli_close($conn);


