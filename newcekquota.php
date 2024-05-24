<?php
include 'db_con.php';

$id_master = $_GET['idm'];
$type = $_GET['idb'];

$sql = "select qty from op_new_benefit where id_master='$id_master' and id_template_benefit='$type'";

$result = mysqli_query($conn,$sql);

$row = mysqli_fetch_assoc($result);
echo $row['qty'];
mysqli_free_result($result);

?>