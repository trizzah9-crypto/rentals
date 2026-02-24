<?php
require "../../config/session.php";
require "../../config/db.php";

header('Content-Type: application/json');

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}
// Read input
$data = json_decode(file_get_contents("php://input"), true);

$tenant_id = $data['tenant_id'] ?? null;
$house_id = $data['house_id'] ?? null;
$move_in_date = $data['move_in_date'] ?? null;
$deposit = (float)($data['deposit_paid'] ?? 0);
$first_rent_paid = $data['first_rent_paid'] ?? 'no';
$payment_method = $data['payment_method'] ?? 'cash';

if (!$tenant_id || !$house_id || !$move_in_date) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

$conn->begin_transaction();

try {

    // Block tenant with active tenancy
    $chk = $conn->prepare("SELECT id FROM tenancies WHERE tenant_id=? AND status='active'");
    $chk->bind_param("i", $tenant_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        throw new Exception("Tenant already has an active tenancy");
    }

    // Get house rent
    $stmt = $conn->prepare("SELECT rent_amount FROM houses WHERE id=? AND status='vacant'");
    $stmt->bind_param("i", $house_id);
    $stmt->execute();
    $house = $stmt->get_result()->fetch_assoc();
    if (!$house) throw new Exception("House not available");

    $rent_amount = (float)$house['rent_amount'];

    // Rent balance logic
    $rent_balance = ($first_rent_paid === 'yes') ? 0 : $rent_amount;

    // Insert tenancy
    $stmt = $conn->prepare("
        INSERT INTO tenancies
        (tenant_id, house_id, rent_amount, move_in_date, status, deposit, rent_balance, last_rent_calculated_date)
        VALUES (?, ?, ?, ?, 'active', ?, ?, ?)
    ");
    $stmt->bind_param(
        "iidsdds",
        $tenant_id,
        $house_id,
        $rent_amount,
        $move_in_date,
        $deposit,
        $rent_balance,
        $move_in_date
    );
    $stmt->execute();
    $tenancy_id = $stmt->insert_id;

    $payment_month = date('Y-m', strtotime($move_in_date));

    // Deposit payment
    if ($deposit > 0) {
        $stmt = $conn->prepare("
            INSERT INTO payments
            (tenancy_id, amount, payment_month, payment_date, status, payment_type, payment_method)
            VALUES (?, ?, ?, NOW(), 'completed', 'deposit', ?)
        ");
        $stmt->bind_param("idss", $tenancy_id, $deposit, $payment_month, $payment_method);
        $stmt->execute();
    }

    // First rent payment
    if ($first_rent_paid === 'yes') {
        $stmt = $conn->prepare("
            INSERT INTO payments
            (tenancy_id, amount, payment_month, payment_date, status, payment_type, payment_method)
            VALUES (?, ?, ?, NOW(), 'completed', 'rent', ?)
        ");
        $stmt->bind_param("idss", $tenancy_id, $rent_amount, $payment_month, $payment_method);
        $stmt->execute();
    }

    // Mark house occupied
    $stmt = $conn->prepare("UPDATE houses SET status='occupied' WHERE id=?");
    $stmt->bind_param("i", $house_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode(["status"=>"success","message"=>"Tenant moved in successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
