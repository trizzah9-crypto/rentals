<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../../config/session.php";
require "../../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}

$house_id = $_GET['house_id'] ?? null;
if (!$house_id) {
    echo json_encode(["status"=>"error","message"=>"Missing house id"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT previous_water_readings
    FROM water_readings
    WHERE id=? 
");
$stmt->bind_param("i", $house_id, $_SESSION['user_id']);
$stmt->execute();
$prev = $stmt->get_result()->fetch_assoc();

if (!$house) {
    echo json_encode(["status"=>"error","message"=>"House not found"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "previous_water_readings" => $prev['previous_ water_readings']
]);
