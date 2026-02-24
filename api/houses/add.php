<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../../config/session.php";
include "../../config/db.php";

// Check landlord session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    http_response_code(403);
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Collect fields
$house_number = $data['house_number'] ?? '';
$location = $data['location'] ?? '';
$rent_amount = $data['rent_amount'] ?? '';
$due_day = $data['due_day'] ?? '';
$late_interest_per_day = $data['late_interest_per_day'] ?? 0;

// Validate input
if (!$house_number || !$location || !$rent_amount || !$due_day) {
    echo json_encode(["status"=>"error","message"=>"All fields are required"]);
    exit;
}


// // Optional: Check if building belongs to this landlord
// $checkBuilding = $conn->prepare("SELECT id FROM buildings WHERE id=? AND landlord_id=?");
// $checkBuilding->bind_param("ii", $building_id, $_SESSION['user_id']);
// $checkBuilding->execute();
// $checkBuilding->store_result();
// if ($checkBuilding->num_rows === 0) {
//     echo json_encode(["status"=>"error","message"=>"Invalid building selection"]);
//     exit;
// }

// Insert house
$stmt = $conn->prepare("
    INSERT INTO houses 
    (landlord_id, house_number, location, rent_amount, due_day, late_interest_per_day, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'vacant')
");

$stmt->bind_param(
    "issdid",
    $_SESSION['user_id'],
    $house_number,
    $location,
    $rent_amount,
    $due_day,
    $late_interest_per_day
);


if ($stmt->execute()) {
    echo json_encode(["status"=>"success","message"=>"House added successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Failed to add house"]);
}
?>
