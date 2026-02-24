<?php
require("../config/db.php");

if (!isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$id = intval($_POST['id']);

$stmt = $conn->prepare("DELETE FROM tenancies WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Delete failed"]);
}
