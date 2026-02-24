<?php
require "../config/db.php";

$id = intval($_POST['house_id']);
$house_number = mysqli_real_escape_string($conn, $_POST['house_number']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$rent_amount = floatval($_POST['rent_amount']);
$status = mysqli_real_escape_string($conn, $_POST['status']);

$sql = "UPDATE houses SET house_number='$house_number', location='$location', rent_amount='$rent_amount', status='$status' WHERE id=$id";
if(mysqli_query($conn, $sql)){
    echo json_encode(['status'=>'success','message'=>'House updated successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>'Update failed']);
}
