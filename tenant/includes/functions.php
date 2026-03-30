<?php
function recalculate_rent_balance($conn, $tenancy_id, $for_month = null) {

    if (!$for_month) {
        $for_month = date("Y-m");
    }

    /* ==========================================
       GET TENANCY + HOUSE DATA
       ========================================== */

    $stmt = $conn->prepare("
        SELECT 
            t.rent_amount,
            t.first_cycle_rent,
            t.last_rent_calculated_date,
            t.move_in_date,
            h.due_day,
            h.late_interest_per_day
        FROM tenancies t
        LEFT JOIN houses h ON t.house_id = h.id
        WHERE t.id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $tenancy_id);
    $stmt->execute();
    $tenancy = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tenancy) return false;

    $rent_amount = (float)$tenancy['rent_amount'];
    $first_cycle_rent = (float)$tenancy['first_cycle_rent'];
    $move_in_date = new DateTime($tenancy['move_in_date']);
    $due_day = (int)$tenancy['due_day'];
    $interest_per_day = (float)($tenancy['late_interest_per_day'] ?? 0.0);
    $last_calc_date_str = $tenancy['last_rent_calculated_date'];

    $today = new DateTime();

    /* ==========================================
       DETERMINE RENT CYCLE
       ========================================== */

    if ($last_calc_date_str) {
        try {
            $cycle_start = new DateTime($last_calc_date_str);
        } catch (Exception $e) {
            $cycle_start = null;
        }
    } else {
        $cycle_start = null;
    }

    if (!$cycle_start) {

        list($y, $m) = explode('-', $for_month);

        $cycle_start = DateTime::createFromFormat(
            'Y-m-d',
            sprintf('%04d-%02d-%02d', $y, $m, $due_day)
        );

        if (!$cycle_start) {
            $cycle_start = new DateTime("$for_month-01");
        }
    }

    $next_due = clone $cycle_start;
    $next_due->modify('+1 month');

    /* ==========================================
       FIRST CYCLE DETECTION (PRORATED RENT)
       ========================================== */

    $is_first_cycle = false;

    if ($first_cycle_rent > 0) {

        $cycle_start_check = new DateTime($tenancy['last_rent_calculated_date']);

        if ($move_in_date >= $cycle_start_check && $move_in_date < $next_due) {
            $is_first_cycle = true;
        }
    }

    if ($is_first_cycle) {
        $rent_amount = $first_cycle_rent;
    }

    /* ==========================================
       GET PAYMENTS FOR THIS RENT CYCLE
       ========================================== */

    $payment_month = $cycle_start->format('Y-m');

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0) as total_paid
        FROM payments
        WHERE tenancy_id=? 
        AND payment_month=? 
        AND status IN ('completed','approved')
    ");

    $stmt->bind_param("is", $tenancy_id, $payment_month);
    $stmt->execute();
    $paid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_paid = (float)$paid['total_paid'];

    /* ==========================================
       PENALTY CALCULATION
       ========================================== */

    $penalty = 0;
    $days_late = 0;

    if ($today > $next_due && $total_paid < $rent_amount) {

        $days_late = $next_due->diff($today)->days;

        $penalty = $days_late * $interest_per_day;
    }

    /* ==========================================
       FINAL BALANCE CALCULATION
       ========================================== */

    $balance = ($rent_amount + $penalty) - $total_paid;

    $credit = 0;

    if ($balance < 0) {
        $credit = abs($balance);
        $balance = 0;
    }

    /* ==========================================
       UPDATE TENANCY BALANCE
       ========================================== */

    $stmt = $conn->prepare("
        UPDATE tenancies
        SET rent_balance=?
        WHERE id=?
    ");

    $stmt->bind_param("di", $balance, $tenancy_id);
    $stmt->execute();
    $stmt->close();

    /* ==========================================
       RETURN DATA
       ========================================== */

    return [
        'balance' => $balance,
        'penalty' => $penalty,
        'days_late' => $days_late,
        'rent_amount' => $rent_amount,
        'total_paid_this_cycle' => $total_paid,
        'credit' => $credit,
        'cycle_start' => $cycle_start->format('Y-m-d'),
        'next_due' => $next_due->format('Y-m-d')
    ];
}
?>