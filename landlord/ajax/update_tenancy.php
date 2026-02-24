<?php
require("../config/db.php");

header("Content-Type: application/json");

if (!isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$id = (int) $_POST['id'];
$rent = $_POST['rent_amount'];
$status = $_POST['status'];
$move_out = !empty($_POST['move_out_date']) ? $_POST['move_out_date'] : null;

$sql = "
    UPDATE tenancies 
    SET rent_amount = ?, status = ?, move_out_date = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed"]);
    exit;
}

/*
 TYPES:
 d = double (rent)
 s = string (status)
 s = string OR null (date)
 i = integer (id)
*/
$stmt->bind_param("dssi", $rent, $status, $move_out, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
