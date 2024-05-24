<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $program = $_POST['program'];                                                                      
    $sql = 'SELECT * FROM `draft_template_benefit` where avail = "all" or avail like "%'.$program.'%" order by benefit,subbenefit,benefit_name asc';
    // $sql = 'SELECT * FROM `draft_template_benefit` order by benefit,subbenefit,benefit_name asc';
    $result = $conn->query($sql);
    
    $options = "";
    if ($result->num_rows > 0) {
        $options .= "<option value=''>Select Benefit</option>";
      while ($row = $result->fetch_assoc()) {
        $option = $row['benefit_name'];
        $options .= "<option value='".$row['id_template_benefit']."'>".$row['benefit']." - ".$row['subbenefit']." - ".$option."</option>";
      }
    }
      
      $conn->close();
      
      echo $options;


    
    
    