<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
                                                                                    
      $sql = "SELECT * FROM calc_title order by title_name asc";
      $result = $conn->query($sql);
      
      $options = "";
      if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
          $option = $row['title_name'];
          $options .= "<option value='$option'>$option</option>";
        }
      }
      
      $conn->close();
      
      echo $options;


    
    
    