<?php
function recalculate_rent_balance($conn, $tenancy_id, $for_month = null) {

    if (!$for_month) {
        $for_month = date("Y-m");
    }

    // Get tenancy + house info
    $stmt = $conn->prepare("
        SELECT t.rent_amount, h.due_day, h.late_interest_per_day
        FROM tenancies t
        LEFT JOIN houses h ON t.house_id = h.id
        WHERE t.id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $tenancy_id);
    $stmt->execute();
    $tenancy = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tenancy) return false;

    $rent_amount = (float)$tenancy['rent_amount'];
    $due_day = (int)$tenancy['due_day'];
    $interest_per_day = (float)$tenancy['late_interest_per_day'];

    // Calculate penalty
    $today = new DateTime();
    list($y,$m) = explode('-', $for_month);
    $due_date = new DateTime("$y-$m-$due_day");

    $penalty = 0;
    if ($today > $due_date) {
        $days_late = $due_date->diff($today)->days;
        $penalty = $days_late * $interest_per_day;
    }

    // Get total approved payments for that month
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0) as total_paid
        FROM payments
        WHERE tenancy_id=? AND payment_month=? AND status='approved'
    ");
    $stmt->bind_param("is", $tenancy_id, $for_month);
    $stmt->execute();
    $paid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_paid = (float)$paid['total_paid'];

    $balance = ($rent_amount + $penalty) - $total_paid;
    if ($balance < 0) $balance = 0;

    // Update tenancy table
    $stmt = $conn->prepare("
        UPDATE tenancies
        SET rent_balance=?, last_rent_calculated_date=NOW()
        WHERE id=?
    ");
    $stmt->bind_param("di", $balance, $tenancy_id);
    $stmt->execute();
    $stmt->close();

    return $balance;
}
?>