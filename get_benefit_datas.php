<?php
    include 'db_con.php';
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    $program = $_POST['program'];   
    $benefitId = $_POST['benefitId'];                                                                   
    $sql = "SELECT dtb.*,
              COALESCE(
                  (SELECT GROUP_CONCAT(DISTINCT alb.level_id SEPARATOR ',')
                    FROM banned_level_benefits as alb
                    WHERE alb.id_template_benefit = dtb.id_template_benefit
                  ), ''
              ) AS banned_level_ids,
              COALESCE(
                  (SELECT GROUP_CONCAT(DISTINCT abb.book_series_id SEPARATOR ',')
                    FROM allowed_book_benefits as abb
                    WHERE abb.id_template_benefit = dtb.id_template_benefit
                  ), ''
              ) AS allowed_book_ids 
              FROM `draft_template_benefit` as dtb
              
              where dtb.id_template_benefit='$benefitId'";
    $result = $conn->query($sql);
    
    $data = array();

    $options = "";
    if ($result->num_rows > 0) {

      while ($row = $result->fetch_assoc()) {
        $data[] = $row;
      }
    }
    
    $conn->close();
    
    header('Content-Type: application/json');
    echo json_encode($data);


    
    
    