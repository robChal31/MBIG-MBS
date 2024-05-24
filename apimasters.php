<?php 
    include 'db_con.php';
    $segment = $_POST['segment'];
    if($segment == 1)
    {
        $sql = "SELECT a.id_master,a.school_name,a.jenDok,a.fileUrl,b.ec_name,a.date,a.year,a.nosor,a.title FROM op_masterdata a left join dash_ec b on a.id_ec=b.id_ec left join dash_sa c on a.id_sa=c.id_sa order by id_master DESC";
    }
    else
    {
        $sql = "SELECT a.id_master,a.school_name,a.jenDok,a.fileUrl,b.ec_name,a.date,a.year,a.nosor,a.title FROM op_masterdata a left join dash_ec b on a.id_ec=b.id_ec left join dash_sa c on a.id_sa=c.id_sa where id_segment='$segment' order by id_master DESC";
    }
    
    $result = mysqli_query($conn, $sql);
    $rows = array();
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $rows[]= $row;
        }
    }
    mysqli_close($conn);
    print json_encode($rows);
    