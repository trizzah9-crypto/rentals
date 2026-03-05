<?php
require "../../config/session.php";
require "../../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    echo json_encode(["status"=>"error","message"=>"Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$tenant_id = isset($data['tenant_id']) ? (int)$data['tenant_id'] : null;
$house_id = isset($data['house_id']) ? (int)$data['house_id'] : null;
$move_in_date = $data['move_in_date'] ?? null;
$deposit = isset($data['deposit_paid']) ? (float)$data['deposit_paid'] : 0.0;
$amount_paid = isset($data['amount_paid']) ? (float)$data['amount_paid'] : 0.0;
$payment_method = $data['payment_method'] ?? 'cash';

if (!$tenant_id || !$house_id || !$move_in_date) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

$conn->begin_transaction();

try {

    $chk = $conn->prepare("SELECT id FROM tenancies WHERE tenant_id=? AND status='active'");
    $chk->bind_param("i", $tenant_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        throw new Exception("Tenant already has an active tenancy");
    }

    $stmt = $conn->prepare("SELECT rent_amount, due_day FROM houses WHERE id=? AND status='vacant'");
    $stmt->bind_param("i", $house_id);
    $stmt->execute();
    $house = $stmt->get_result()->fetch_assoc();
    if (!$house) throw new Exception("House not available");

    $rent_amount = (float)$house['rent_amount'];
    $due_day = (int)($house['due_day'] ?? 1);

    $move = new DateTime($move_in_date);
    $move_day = (int)$move->format('d');

    // ==============================
    // PRORATION (UNCHANGED)
    // ==============================
    if ($move_day === $due_day) {
        $prorated_amount = round($rent_amount, 2);
    } else {

        $year = (int)$move->format('Y');
        $month = (int)$move->format('m');

        $due_this_month = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $due_day));

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

        $prorated_amount = round($rent_amount * ($days_owed / $period_days), 2);
    }

    // ==============================
    // 🔥 NEW: DETERMINE CYCLE START
    // ==============================

    if ($move_day >= $due_day) {
        $cycle_start = DateTime::createFromFormat('Y-m-d', $move->format("Y-m") . "-" . $due_day);
    } else {
        $prev = clone $move;
        $prev->modify('-1 month');
        $cycle_start = DateTime::createFromFormat('Y-m-d', $prev->format("Y-m") . "-" . $due_day);
    }

    $last_rent_calc_date = $cycle_start->format('Y-m-d');

    // ==============================
    // RENT BALANCE
    // ==============================

    $rent_balance = round($prorated_amount - $amount_paid, 2);

    // ==============================
    // INSERT TENANCY (FIXED HERE)
    // ==============================

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
        $last_rent_calc_date   // ✅ FIXED
    );

    $stmt->execute();
    $tenancy_id = $stmt->insert_id;

    $payment_month = $cycle_start->format('Y-m'); // 🔥 also fixed

    if ($deposit > 0) {
        $stmt = $conn->prepare("
            INSERT INTO payments
            (tenancy_id, amount, payment_month, payment_date, status, payment_type, payment_method)
            VALUES (?, ?, ?, NOW(), 'completed', 'deposit', ?)
        ");
        $stmt->bind_param("idss", $tenancy_id, $deposit, $payment_month, $payment_method);
        $stmt->execute();
    }

    if ($amount_paid > 0) {
        $stmt = $conn->prepare("
            INSERT INTO payments
            (tenancy_id, amount, payment_month, payment_date, status, payment_type, payment_method)
            VALUES (?, ?, ?, NOW(), 'completed', 'rent', ?)
        ");
        $stmt->bind_param("idss", $tenancy_id, $amount_paid, $payment_month, $payment_method);
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE houses SET status='occupied' WHERE id=?");
    $stmt->bind_param("i", $house_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Tenant moved in successfully",
        "prorate_amount" => $prorated_amount,
        "amount_paid" => $amount_paid,
        "rent_balance" => $rent_balance
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
    exit;
}