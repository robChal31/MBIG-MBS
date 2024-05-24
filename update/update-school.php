<?php
    include './db_con.php';

    $id_school = ISSET($id_school) ? $id_school : 257;

    $url = "https://mentarimarapp.com/admin/api/get-institution.php?key=marapp2024&param=$id_school";

    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Error: ' . curl_error($curl);
        die;
    }

    curl_close($curl);

    $school_data = json_decode($response, true);

    if(count($school_data) > 0) {
        $school_id_new          = $school_data[0]['institutionid'];
        $school_name_new        = $school_data[0]['name'];
        $school_address_new     = $school_data[0]['address'];
        $school_phone_new       = $school_data[0]['phone'];
        $school_segment_new     = $school_data[0]['segment'];
        $school_ec_id_new       = $school_data[0]['ec_id'];
        $school_created_date_new    = $school_data[0]['created_date'];

        $sql = "SELECT * FROM schools WHERE id = $school_id_new";

        $result = mysqli_query($conn, $sql);
        $institutionid = false;
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $institutionid  = $row['id'];
                $name           = $row['name'];
                $address        = $row['address'];
            }
        }
        var_dump($institutionid);die;
        if(!$institutionid) {
            $sql = "INSERT INTO `schools` (`institutionid`, `name`, `address`, `phone`, `segment`, `ec_id`, `created_date`) VALUES
            ($school_id_new, '$school_name_new', '$school_address_new', '$school_phone_new', '$school_segment_new', 'school_ec_id_new', $school_created_date_new)";

            mysqli_query($conn,$sql);
            $id_school = mysqli_insert_id($conn);         
        }
    }