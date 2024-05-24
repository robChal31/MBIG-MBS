<?php
    include 'db_con.php';
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    $program = $_POST['program'];   
    $benefitId = $_POST['benefitId'];                                                                   
    $sql = 'SELECT * FROM `draft_template_benefit` where id_template_benefit="'.$benefitId.'"';
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


    
    
    