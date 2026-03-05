<?php
require "../../config/session.php";
require "../../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}

$house_id = isset($_GET['house_id']) ? (int)$_GET['house_id'] : null;
$move_in_date = $_GET['move_in_date'] ?? null;

if (!$house_id || !$move_in_date) {
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}

$stmt = $conn->prepare("SELECT rent_amount, due_day FROM houses WHERE id=?");
$stmt->bind_param("i", $house_id);
$stmt->execute();
$house = $stmt->get_result()->fetch_assoc();

if (!$house) {
    echo json_encode(["status"=>"error","message"=>"House not found"]);
    exit;
}

$rent = (float)$house['rent_amount'];
$due_day = (int)($house['due_day'] ?? 1);

try {
    $move = new DateTime($move_in_date);
} catch(Exception $e) {
    echo json_encode(["status"=>"error","message"=>"Invalid date"]);
    exit;
}

$move_day = (int)$move->format('d');

if ($move_day === $due_day) {
    echo json_encode([
        "status"=>"success",
        "type"=>"full",
        "full_rent" => round($rent,2),
        "amount" => round($rent,2),
        "period_days" => null,
        "days_owed" => null
    ]);
    exit;
}

// compute next due date
$year = (int)$move->format('Y');
$month = (int)$move->format('m');

$due_this_month = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $due_day));
if (!$due_this_month) {
    // fallback to full rent
    echo json_encode(["status"=>"success","type"=>"full","amount"=>round($rent,2)]);
    exit;
}

if ($move <= $due_this_month) {
    $next_due = $due_this_month;
} else {
    $next_due = clone $due_this_month;
    $next_due->modify('+1 month');
}

$prev_due = clone $next_due;
$prev_due->modify('-1 month');

$period_days = (int)$prev_due->diff($next_due)->days;
$days_owed = (int)$move->diff($next_due)->days;

if ($period_days <= 0 || $days_owed <= 0) {
    echo json_encode(["status"=>"success","type"=>"full","amount"=>round($rent,2)]);
    exit;
}

$prorated = round($rent * ($days_owed / $period_days), 2);

echo json_encode([
    "status"=>"success",
    "type"=>"prorated",
    "full_rent" => round($rent,2),
    "period_days" => $period_days,
    "days_owed" => $days_owed,
    "amount" => $prorated
]);