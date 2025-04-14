<?php 
error_reporting(E_ALL);
ini_set('display_errors', 'On');
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
session_start();
include 'db_con.php';

$username = $_POST['uname'];
$password = md5($_POST['psw']);

$stmt = $conn->prepare('select * from user where username=? and password=?');
$stmt->bind_param('ss', $username, $password);

$stmt->execute();
$result = $stmt->get_result();

$log=0;
 while ($row = $result->fetch_assoc()) {
    //print_r($row);
    $log=0;
    if($row['username']==$username)
    {
        $log=1;
        $_SESSION['id_user'] = $row['id_user'];
        $_SESSION['generalname'] = $row['generalname'];
        $_SESSION['username'] = $username;
	    $_SESSION['status'] = $row['role'];
	    $_SESSION['role'] = $row['role'];
	    
	    header("location:main.php");
    }
    

}
if($log==0){
header("location:index.php?pesan=gagal");
}
?>