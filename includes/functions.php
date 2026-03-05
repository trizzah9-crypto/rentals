<?php
function recalculate_rent_balance($conn, $tenancy_id, $for_month = null) {

    if (!$for_month) {
        $for_month = date("Y-m");
    }

    // Fetch tenancy + house info (also grab stored rent_balance and move_in_date)
    $stmt = $conn->prepare("
        SELECT t.rent_amount, t.rent_balance AS stored_rent_balance, t.last_rent_calculated_date, t.move_in_date,
               h.due_day, h.late_interest_per_day
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
    $stored_rent_balance = (float)($tenancy['stored_rent_balance'] ?? 0.0); // prorated remainder set at move-in
    $due_day = (int)($tenancy['due_day'] ?? 1);
    $interest_per_day = (float)($tenancy['late_interest_per_day'] ?? 0.0);
    $last_calc_date_str = $tenancy['last_rent_calculated_date']; // may be NULL
    $move_in_date_str = $tenancy['move_in_date'];

    $today = new DateTime();

    // Determine cycle_start:
    // Prefer last_rent_calculated_date (set at move-in to the cycle start).
    // If absent, derive cycle_start from move_in_date (previous or same due_day).
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
        // fallback: compute cycle_start that contains move_in_date
        $move = new DateTime($move_in_date_str);
        $move_day = (int)$move->format('d');

        if ($move_day >= $due_day) {
            $cycle_start = DateTime::createFromFormat('Y-m-d', $move->format("Y-m") . "-" . $due_day);
        } else {
            $prev = clone $move;
            $prev->modify('-1 month');
            $cycle_start = DateTime::createFromFormat('Y-m-d', $prev->format("Y-m") . "-" . $due_day);
        }
        if (!$cycle_start) {
            // ultimate fallback: first day of move month
            $cycle_start = new DateTime($move->format("Y-m-01"));
        }
    }

    // next due is start + 1 month (first full rent due after cycle_start)
    $next_due = clone $cycle_start;
    $next_due->modify('+1 month');

    // Start with stored rent_balance (prorated remainder set at move-in)
    // This is the amount the tenant still owes for the partial cycle.
    $outstanding = $stored_rent_balance;

    // We'll accumulate penalty totals and days late for reporting
    $total_penalty = 0;
    $total_days_late = 0;

    // If next_due is in the future, do not add the next month's rent yet.
    // Only when next_due <= today do we start adding unpaid full months.
    if ($today >= $next_due) {

        // Iterate each month due from next_due up to the month that contains today.
        // For each due_date (d), compute unpaid rent for that month and penalty.
        $iter_due = clone $next_due;

        // To avoid infinite loop, limit iterations to a reasonable max (e.g., 60 months)
        $max_months = 60;
        $months_count = 0;

        while ($iter_due <= $today && $months_count < $max_months) {
            $months_count++;

            $payment_month = $iter_due->format('Y-m');

            // Sum payments recorded for that payment_month (include completed & approved)
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(amount),0) as total_paid
                FROM payments
                WHERE tenancy_id=? AND payment_month=? AND status IN ('completed','approved')
            ");
            $stmt->bind_param("is", $tenancy_id, $payment_month);
            $stmt->execute();
            $paid_row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $paid_for_month = (float)($paid_row['total_paid'] ?? 0.0);

            // If less than full rent was paid for that month, tenant owes the difference
            if ($paid_for_month < $rent_amount) {
                $month_due = round($rent_amount - $paid_for_month, 2);

                // Penalty: only count days late up to today (if today's date is past the due date)
                $days_late_for_month = 0;
                if ($today > $iter_due) {
                    $days_late_for_month = $iter_due->diff($today)->days;
                }
                $penalty_for_month = round($days_late_for_month * $interest_per_day, 2);

                // Add to outstanding
                $outstanding += $month_due + $penalty_for_month;

                // accumulate totals
                $total_penalty += $penalty_for_month;
                // days late contribution (use max days; not additive exactness but useful as indicator)
                $total_days_late = max($total_days_late, $days_late_for_month);
            }

            // move to next due date
            $iter_due->modify('+1 month');
        }
    }

    // If outstanding negative (overpayment / credit), convert to credit and zero balance
    $credit = 0.0;
    if ($outstanding < 0) {
        $credit = abs($outstanding);
        $outstanding = 0.0;
    }

    // Update tenancy.rent_balance to the authoritative outstanding amount
    $stmt = $conn->prepare("
        UPDATE tenancies
        SET rent_balance=?
        WHERE id=?
    ");
    $stmt->bind_param("di", $outstanding, $tenancy_id);
    $stmt->execute();
    $stmt->close();

    return [
        'balance' => round($outstanding, 2),
        'penalty' => round($total_penalty, 2),
        'days_late' => (int)$total_days_late,
        'rent_amount' => $rent_amount,
        'total_paid_this_cycle' => (float)$stored_rent_balance + 0, // kept for backward compat (we can change)
        'credit' => round($credit, 2),
        'cycle_start' => $cycle_start->format('Y-m-d'),
        'next_due' => $next_due->format('Y-m-d')
    ];
}
?>