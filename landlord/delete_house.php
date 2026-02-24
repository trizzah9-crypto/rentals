<?php
require "../config/db.php";

header('Content-Type: application/json');

if(!isset($_POST['house_id'])){
    echo json_encode(['status'=>'error','message'=>'No house ID sent']);
    exit;
}

$id = intval($_POST['house_id']); // make sure it's an integer

$sql = "DELETE FROM houses WHERE id = $id";
if(mysqli_query($conn, $sql)){
    echo json_encode(['status'=>'success','message'=>'House deleted successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>'Delete failed: ' . mysqli_error($conn)]);
}
