<?php
require "../../config/session.php";
include "../../config/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord'){
    http_response_code(403);
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$tenancy_id = $data['tenancy_id'] ?? '';
$move_out_date = $data['move_out_date'] ?? '';

if(!$tenancy_id || !$move_out_date){
    echo json_encode(["status"=>"error","message"=>"All fields are required"]);
    exit;
}

// Update tenancy
$stmt = $conn->prepare("UPDATE tenancies SET move_out_date=?, status='ended' WHERE id=?");
$stmt->bind_param("si", $move_out_date, $tenancy_id);

if($stmt->execute()){
    // Set house to vacant
    $houseIdRes = $conn->query("SELECT house_id FROM tenancies WHERE id=".$tenancy_id);
    $houseId = $houseIdRes->fetch_assoc()['house_id'];

    $updateHouse = $conn->prepare("UPDATE houses SET status='vacant' WHERE id=?");
    $updateHouse->bind_param("i", $houseId);
    $updateHouse->execute();

    echo json_encode(["status"=>"success","message"=>"Tenant moved out successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Failed to move out tenant"]);
}
