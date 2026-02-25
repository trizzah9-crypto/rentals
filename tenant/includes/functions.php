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

    // Due date
    list($y,$m) = explode('-', $for_month);
    $due_date = new DateTime("$y-$m-$due_day");
    $today = new DateTime();

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

    // Calculate penalty ONLY if rent not fully paid
    $penalty = 0;
    $days_late = 0;

    if ($today > $due_date && $total_paid < $rent_amount) {
        $days_late = $due_date->diff($today)->days;
        $penalty = $days_late * $interest_per_day;
    }

    $balance = ($rent_amount + $penalty) - $total_paid;
    $credit = 0;
        if ($balance < 0) {
            $credit = abs($balance);
            $balance = 0;
        }

    // Update tenancy table
    $stmt = $conn->prepare("
        UPDATE tenancies
        SET rent_balance=?, last_rent_calculated_date=NOW()
        WHERE id=?
    ");
    $stmt->bind_param("di", $balance, $tenancy_id);
    $stmt->execute();
    $stmt->close();

    return [
    'balance' => $balance,
    'penalty' => $penalty,
    'days_late' => $days_late,
    'rent_amount' => $rent_amount,
    'total_paid' => $total_paid,
    'credit' => $credit
   ];
}
?>