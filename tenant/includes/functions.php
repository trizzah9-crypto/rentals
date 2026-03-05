<?php
function recalculate_rent_balance($conn, $tenancy_id, $for_month = null) {

    if (!$for_month) {
        $for_month = date("Y-m");
    }

    // Get tenancy + house info, including last_rent_calculated_date
    $stmt = $conn->prepare("
        SELECT t.rent_amount, t.last_rent_calculated_date, h.due_day, h.late_interest_per_day
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
    $interest_per_day = (float)($tenancy['late_interest_per_day'] ?? 0.0);
    $last_calc_date_str = $tenancy['last_rent_calculated_date']; // may be NULL

    $today = new DateTime();

    // Determine cycle_start and next_due:
    if ($last_calc_date_str) {
        // Use tenancy's last_rent_calculated_date as cycle start
        try {
            $cycle_start = new DateTime($last_calc_date_str);
        } catch (Exception $e) {
            // fallback to month method
            $cycle_start = null;
        }
    } else {
        $cycle_start = null;
    }

    // If we don't have a valid cycle_start, fall back to using $for_month + due_day
    if (!$cycle_start) {
        list($y, $m) = explode('-', $for_month);
        // create due_date for this month
        $cycle_start = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $y, $m, $due_day));
        if (!$cycle_start) {
            // final fallback: first day of month
            $cycle_start = new DateTime("$for_month-01");
        }
    }

    $next_due = clone $cycle_start;
    $next_due->modify('+1 month');

    // Use payment_month equal to the cycle_start month-year
    $payment_month = $cycle_start->format('Y-m');

    // Get total payments for that cycle (include completed and approved)
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0) as total_paid
        FROM payments
        WHERE tenancy_id=? AND payment_month=? AND status IN ('completed','approved')
    ");
    $stmt->bind_param("is", $tenancy_id, $payment_month);
    $stmt->execute();
    $paid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_paid = (float)$paid['total_paid'];

    // Calculate penalty ONLY if next_due has passed AND rent not fully paid
    $penalty = 0;
    $days_late = 0;

    if ($today > $next_due && $total_paid < $rent_amount) {
        $days_late = $next_due->diff($today)->days;
        $penalty = $days_late * $interest_per_day;
    }

    $balance = ($rent_amount + $penalty) - $total_paid;
    $credit = 0;
    if ($balance < 0) {
        $credit = abs($balance);
        $balance = 0;
    }

    // Update tenancy table: update rent_balance but do NOT overwrite last_rent_calculated_date here
    $stmt = $conn->prepare("
        UPDATE tenancies
        SET rent_balance=?
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
        'credit' => $credit,
        'cycle_start' => $cycle_start->format('Y-m-d'),
        'next_due' => $next_due->format('Y-m-d')
    ];
}
?>