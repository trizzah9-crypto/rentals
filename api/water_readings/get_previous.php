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

if(!$house_id){
    echo json_encode(["status"=>"error","message"=>"Missing house id"]);
    exit;
}

$stmt = $conn->prepare("
SELECT current_reading
FROM water_readings
WHERE house_id = ?
ORDER BY reading_date DESC
LIMIT 1
");

$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){

    echo json_encode([
        "status"=>"success",
        "previous_water_readings"=>0
    ]);

    exit;
}

$row = $result->fetch_assoc();

echo json_encode([
    "status"=>"success",
    "previous_water_readings"=>$row['current_reading']
]);