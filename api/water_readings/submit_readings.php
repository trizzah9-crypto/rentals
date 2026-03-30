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

$data = json_decode(file_get_contents("php://input"), true);

$house_id = $data['house_id'] ?? null;
$current_reading = $data['current_reading'] ?? null;

if(!$house_id || !$current_reading){
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}

$current_reading = intval($current_reading);


// GET LAST READING
$stmt = $conn->prepare("
SELECT current_reading
FROM water_readings
WHERE house_id = ?
ORDER BY reading_month DESC
LIMIT 1
");

$stmt->bind_param("i", $house_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    
    $previous = 0;

}else{

    $row = $result->fetch_assoc();
    $previous = intval($row['current_reading']);

}


// VALIDATION
if($current_reading < $previous){

    echo json_encode([
        "status"=>"error",
        "message"=>"Current reading cannot be lower than previous"
    ]);

    exit;
}


// CALCULATE UNITS
$units = $current_reading - $previous;


// CURRENT MONTH
$month = date("Y-m-01");


// PREVENT DOUBLE ENTRY SAME MONTH
$stmt = $conn->prepare("
SELECT id FROM water_readings
WHERE house_id=? AND reading_month=?
");

$stmt->bind_param("is",$house_id,$month);
$stmt->execute();
$check = $stmt->get_result();

if($check->num_rows > 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"Water reading already submitted this month"
    ]);

    exit;
}



// INSERT READING
$stmt = $conn->prepare("
INSERT INTO water_readings
(house_id, previous_reading, current_reading, units_used, reading_month)
VALUES(?,?,?,?,?)
");

$stmt->bind_param("iiiis",
    $house_id,
    $previous,
    $current_reading,
    $units,
    $month
);

$stmt->execute();


echo json_encode([
    "status"=>"success",
    "message"=>"Water reading recorded",
    "units_used"=>$units
]);