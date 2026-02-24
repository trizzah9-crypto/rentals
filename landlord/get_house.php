<?php
require "../config/db.php";

$id = intval($_GET['id']);
$sql = "SELECT * FROM houses WHERE id = $id LIMIT 1";
$res = mysqli_query($conn, $sql);
if($res && mysqli_num_rows($res) > 0){
    $house = mysqli_fetch_assoc($res);
    echo json_encode(['status'=>'success','house'=>$house]);
} else {
    echo json_encode(['status'=>'error','message'=>'House not found']);
}
