<?php

    $levels = ['tk', 'sd', 'smp', 'sma', 'yayasan', 'other'];
    $id_user        = $_SESSION['id_user'];
    $query_program  = "SELECT COUNT(db.id_draft) as total, program
                        from draft_benefit db
                        INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft 
                        group by db.program"; 

    $result         = mysqli_query($conn, $query_program);
    $program_label  = array();
    $program_total  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $program_label[] = strtoupper($row['program']);
            $program_total[] = floatval($row['total']);
        }
    }

    $query_segment  = "SELECT COUNT(db.id_draft) as total, segment
                        from draft_benefit db
                        INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft
                        group by db.segment"; 

    $result         = mysqli_query($conn, $query_segment);
    $segment_label  = array();
    $segment_total  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $segment_label[] = strtoupper($row['segment']);
            $segment_total[] = floatval($row['total']);
        }
    }


    $query_level  = "SELECT COUNT(db.id_draft) as total, level
                        from draft_benefit db
                        INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft
                        group by db.level"; 

    $result       = mysqli_query($conn, $query_level);
    $level_label  = array();
    $level_total  = array();
    $total_other  = 0 ;
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $is_exist = array_filter($levels, function($lv) use($row) {
                return $lv == $row['level'];
            });
            if($is_exist) {
                $level_label[] = strtoupper($row['level']);
                $level_total[] = floatval($row['total']);
            }else {
                $total_other += floatval($row['total']);
            }

        }
        $level_label[] = "Other";
        $level_total[] = $total_other;
    }

    $query_periode  = "SELECT 
                        DATE_FORMAT(db.date, '%Y-%m') AS periode,
                        COUNT(db.id_draft) AS total
                    FROM draft_benefit as db
                    INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft
                    GROUP BY 
                        periode"; 

    $result         = mysqli_query($conn, $query_periode);
    $periode_label  = array();
    $periode_total  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $periode_label[] = $row['periode'];
            $periode_total[] = floatval($row['total']);
        }
    }

    $query_yearly  = "SELECT 
                        DATE_FORMAT(db.date, '%Y') AS yearly,
                        COUNT(db.id_draft) AS total
                    FROM draft_benefit as db
                    INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft
                    GROUP BY 
                        yearly"; 

    $result         = mysqli_query($conn, $query_yearly);
    $yearly_label  = array();
    $yearly_total  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $yearly_label[] = $row['yearly'];
            $yearly_total[] = floatval($row['total']);
        }
    }
    

    
    $query_ec  = "SELECT COUNT(db.id_draft) as total, user.generalname as ec
                    from draft_benefit db 
                    INNER JOIN (
                            select * from draft_benefit_list dbl 
                            group by dbl.id_draft
                        ) as dbl_filtered on dbl_filtered.id_draft = db.id_draft
                    LEFT JOIN user as user on db.id_ec = user.id_user
                    group by db.id_ec"; 

    $result         = mysqli_query($conn, $query_ec);
    $ec_label  = array();
    $ec_total  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $ec_label[] = $row['ec'];
            $ec_total[] = floatval($row['total']);
        }
    }
    

    
        
    ?>