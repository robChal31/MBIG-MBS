<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $program = $_POST['program'];
    $selected_template = $_POST['selectedTemplate'] ?? NULL;
    $selected = $_POST['selected'] ?? NULL;
    
    $query_program = "SELECT code FROM programs WHERE code = '$program' AND is_active = 1 LIMIT 1";

    $exec_program = mysqli_query($conn, $query_program);

    $program_code = false;
    
    if ($exec_program && mysqli_num_rows($exec_program) > 0) {
        $prog = mysqli_fetch_assoc($exec_program);
        $program_code = $prog['code'];
    }

    $filter_program_q = $program_code ? "AND avail like '%$program_code%' " : false;
    $options = "";

    try {
      if($filter_program_q) {
        if($selected_template) {
          $sql = "SELECT * FROM `draft_template_benefit` WHERE is_active = 1 $filter_program_q AND id_template_benefit NOT IN (" . implode(',', $selected_template) . ") order by benefit, subbenefit, benefit_name, benefit_order asc";
        }else {
          $sql = "SELECT * FROM `draft_template_benefit` WHERE is_active = 1 $filter_program_q order by benefit, subbenefit, benefit_name asc";
        }
        
        // $sql = 'SELECT * FROM `draft_template_benefit` order by benefit,subbenefit,benefit_name asc';
        $result = $conn->query($sql);
        
        $grouped_benefits = [];
        if ($result->num_rows > 0) {

          $number = 1;
          while ($row = $result->fetch_assoc()) {
            $grouped_benefits[$row['benefit']][$number] = $row;
            $number++;
          }

          $options .= "<option value=''>Select Benefit</option>";

          foreach ($grouped_benefits as $key => $grouped_benefit) {
            $options .= "<optgroup label='$key'>";
            foreach ($grouped_benefit as $key => $benefit) {
              $option = $benefit['benefit_name'];
              $is_selected = '';
              if($selected) {
                $is_selected = $selected == $benefit['id_template_benefit'] ? 'selected' : '';
              }
              $id_template = $benefit['id_template_benefit'];
              $benefit_group = $benefit['benefit'];
              $subbenefit = $benefit['subbenefit'];
              $info = $benefit['info'] ? "data-bs-toggle='tooltip' title='$benefit[info]'" : '';
              $highlight_color = $benefit['highlight_color'] ? "data-color='$benefit[highlight_color]'" : '';
              $options .= "<option value='$id_template' $is_selected $info $highlight_color >$benefit_group - $subbenefit - $option</option>";
  
            }
            $options .= "</optgroup>";
          }
          $conn->close();
      
          echo $options;
        }else {
          $conn->close();
          echo "<option value=''>No Benefit Found</option>";
        }
       
      }else {
        $conn->close();
      
        echo "<option value=''>Program Not Found</option>";
      }
    } catch (\Throwable $th) {
      echo $th;
    }

      
   


    
    
    