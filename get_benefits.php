<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $program = $_POST['program'];
    
    $query_program = "SELECT code FROM programs WHERE name = '$program' AND is_active = 1 LIMIT 1";

    $exec_program = mysqli_query($conn, $query_program);

    $program_code = false;
    
    if ($exec_program && mysqli_num_rows($exec_program) > 0) {
        $prog = mysqli_fetch_assoc($exec_program);
        $program_code = $prog['code'];
    }

    $filter_program_q = $program_code ? "WHERE avail like '%$program_code%' " : false;
    $options = "";

    if($filter_program_q) {
      $sql = "SELECT * FROM `draft_template_benefit` $filter_program_q order by benefit, subbenefit, benefit_name asc";
      // $sql = 'SELECT * FROM `draft_template_benefit` order by benefit,subbenefit,benefit_name asc';
      $result = $conn->query($sql);
      

      if ($result->num_rows > 0) {
          $options .= "<option value=''>Select Benefit</option>";
        while ($row = $result->fetch_assoc()) {
          $option = $row['benefit_name'];
          $options .= "<option value='".$row['id_template_benefit']."'>".$row['benefit']." - ".$row['subbenefit']." - ".$option."</option>";
        }
      }
      $conn->close();
    
      echo $options;
    }else {
      $conn->close();
    
      echo "<option value=''>Program Not Found</option>";
    }

      
   


    
    
    