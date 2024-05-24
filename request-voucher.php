<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    ob_start();
    session_start();
    include 'db_con.php';
    $inputJSON = file_get_contents('php://input');
    $inputData = json_decode($inputJSON, true);
    if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        die('Invalid JSON data');
    }

    $rowid = isset($inputData['rowid']) ? $inputData['rowid'] : null;
    $idevent = isset($inputData['idevent']) ? $inputData['idevent'] : null;
    $idticket = isset($inputData['idticket']) ? $inputData['idticket'] : null;
    $quota = isset($inputData['quota']) ? $inputData['quota'] : null;

    $sql = "SELECT * from op_simple_benefit where id_benefit=$rowid and type='training' and isDeleted=0";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            //getqtytotal,kurangi qty
            //echo $row['qty'];
            $inputData['qty']=$row['qty'];
        }
    }
    // Use $_POST to retrieve values sent via POST
    $response = [
        'status' => 'success',
        'message' => 'Data received successfully',
        'data' => $inputData
    ];
    


    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>