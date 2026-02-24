<?php
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
    SELECT rent_amount
    FROM houses
    WHERE id=? AND landlord_id=? AND status='vacant'
");
$stmt->bind_param("ii", $house_id, $_SESSION['user_id']);
$stmt->execute();
$house = $stmt->get_result()->fetch_assoc();

if (!$house) {
    echo json_encode(["status"=>"error","message"=>"House not found"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "rent_amount" => $house['rent_amount']
]);
