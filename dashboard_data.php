<?php

    $levels = ['tk', 'sd', 'smp', 'sma', 'yayasan', 'other'];
    $id_user        = $_SESSION['id_user'];
    $role           = $_SESSION['role'];
    
    $query_filter_draft = $role == 'ec' ? "WHERE db.id_ec = $id_user AND " : "WHERE ";

    $query_program  = "SELECT 
                            total_draft,
                            prog.program,
                            prog.code,
                            prog.is_pk,
                            prog.is_active,
                            SUM(prog.total_draft) AS total
                        FROM (
                            SELECT COUNT(db.id_draft) as total_draft, program, prog.code, prog.is_pk, prog.is_active 
                            FROM draft_benefit db 
                            left join programs as prog on prog.name = db.program
                            $query_filter_draft db.status = 1
                            group by prog.code
                        ) AS prog
                        GROUP BY 
                        CASE 
                            WHEN prog.is_pk = 1 THEN prog.is_pk 
                            ELSE prog.code 
                        END;"; 

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
                        FROM draft_benefit db
                        $query_filter_draft
                        db.status = 1
                        GROUP BY db.segment"; 

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
                        FROM draft_benefit db
                        $query_filter_draft
                        db.status = 1
                        GROUP BY db.level"; 

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
                        $query_filter_draft
                        db.status = 1
                        GROUP BY periode"; 

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
                        $query_filter_draft
                        db.status = 1
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
                    FROM user as user 
                    LEFT JOIN draft_benefit db on db.id_ec = user.id_user
                    WHERE db.status = 1 AND user.role = 'ec'
                    GROUP BY user.id_user"; 

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

    $query_filter_reject = $role == 'admin' ? 'WHERE ' : "WHERE arh.id_user = '$id_user' AND";
    $query_rejected  = "SELECT COUNT(arh.id_draft) as total, user.generalname as ec,
                        (
                            select COUNT(db.id_draft) as total_draft
                            from draft_benefit as db 
                            where db.id_ec = $id_user
                        ) as total_draft
                        FROM user as user 
                        LEFT JOIN approval_reject_history AS arh on arh.id_user = user.id_user
                        $query_filter_reject user.role = 'ec'
                        GROUP BY user.id_user; ";
    $result    = mysqli_query($conn, $query_rejected);
    $ec_reject_label  = array();
    $ec_total_reject  = array();
    $ec_total_draft  = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $ec_reject_label[] = $row['ec'];
            $ec_total_reject[] = floatval($row['total']);
            $ec_total_draft[] = floatval($row['total_draft']);
        }
    }

    $query_schools  = "SELECT 
                            COUNT(b.id_draft) as total, IFNULL(sc.name, b.school_name) as school_name2
                        FROM draft_benefit b
                        LEFT JOIN schools sc on sc.id = b.school_name
                        GROUP BY school_name2";
    $result         = mysqli_query($conn, $query_schools);
    $school_labels  = array();
    $school_data    = array();
    setlocale(LC_MONETARY,"id_ID");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $school_labels[] = $row['school_name2'];
            $school_data[] = floatval($row['total']);
        }
    }

?>