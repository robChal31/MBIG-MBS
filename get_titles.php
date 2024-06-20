<?php
    ob_start();
    session_start();
    include 'db_con.php';


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }
                                                                                    
      $sql = "SELECT * FROM books order by name asc";
      $result = $conn->query($sql);
      
      $options = "";
      if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
          $option = $row['name'];
          $options .= "<option value='$option'>$option</option>";
        }
      }
      
      $conn->close();
      
      echo $options;


    
    
    