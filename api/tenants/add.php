<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../../config/session.php";
include "../../config/db.php";

// Only landlord can add tenants
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name  = $data['name'] ?? '';
$email = $data['email'] ?? '';
$username = $data['username'] ?? '';
$rawPassword = $data['password'] ?? '';

$phone = $data['phone'] ?? '';
$id_number = $data['id_number'] ?? '';
$next_of_kin_name = $data['next_of_kin_name'] ?? '';
$next_of_kin_phone = $data['next_of_kin_phone'] ?? '';
$address = $data['address'] ?? '';

if (!$name || !$email || !$username || !$rawPassword) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

$password = password_hash($rawPassword, PASSWORD_DEFAULT);

$conn->begin_transaction();

try {
    // Insert into users table
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, username, password, role)
        VALUES (?, ?, ?, ?, 'tenant')
    ");
    $stmt->bind_param("ssss", $name, $email, $username, $password);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    // Insert tenant profile (NO password here)
    $stmt2 = $conn->prepare("
        INSERT INTO tenants 
        (user_id, name, email, phone, id_number, next_of_kin_name, next_of_kin_phone, address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param(
        "isssssss",
        $user_id,
        $name,
        $email,
        $phone,
        $id_number,
        $next_of_kin_name,
        $next_of_kin_phone,
        $address
    );
    $stmt2->execute();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Tenant added and login created successfully"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => "error",
        "message" => "Username or email already exists"
    ]);
}
