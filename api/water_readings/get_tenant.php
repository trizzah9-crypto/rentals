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
     SELECT t.id, u.name, h.house_number
      FROM tenancies t
      JOIN tenants te ON t.tenant_id=te.id
      JOIN users u ON te.user_id=u.id
      JOIN houses h ON t.house_id=h.id
      WHERE t.status='active'
");
$stmt->bind_param("iisi", $tenant_id, $user_name, $house_id, $_SESSION['user_id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$house) {
    echo json_encode(["status"=>"error","message"=>"House not found"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "user_name" => $data['username']
]);
