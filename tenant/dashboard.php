<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require "../config/db.php";
require "includes/tenant_auth.php";
require "includes/functions.php";
require "includes/head.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id,name FROM tenants WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$tenant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenant){
    die("Tenant record not found");
}

$tenant_id = $tenant['id'];

$sql = "
    SELECT t.id AS tenancy_id,
           t.status,
           h.house_number,
           h.location,
           h.due_day
    FROM tenancies t
    INNER JOIN houses h ON t.house_id = h.id
    WHERE t.tenant_id=? AND t.status='active'
    ORDER BY t.move_in_date DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$tenant_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$balance = 0;
$penalty = 0;
$days_late = 0;
$rent_amount = 0;
$amount_paid = 0;
$progress = 0;
$credit = 0;

if($data){

    $tenancy_id = $data['tenancy_id'];
    $current_month = date("Y-m");

    $rentData = recalculate_rent_balance($conn, $tenancy_id, $current_month);

    if($rentData){
        $balance = $rentData['balance'];
        $penalty = $rentData['penalty'];
        $days_late = $rentData['days_late'];
        $rent_amount = $rentData['rent_amount'];
        $amount_paid = $rentData['total_paid'];
        $credit = $rentData['credit']; // ADD THIS

        $total_due = $rent_amount + $penalty;
        if($total_due > 0){
            $progress = min(100, ($amount_paid / $total_due) * 100);
        }
    }
}
?>


<div class="main">

<div class="topbar">
    <h2>Welcome, <?= htmlspecialchars($tenant['name']) ?></h2>
    <span><?= date("F Y") ?></span>
</div>

<?php if($data): ?>

<?php if($days_late > 0): ?>
    <div class="status-late">
        ⚠ You are <?= $days_late ?> days late. Penalty: KES <?= number_format($penalty,2) ?>
    </div>
<?php else: ?>
    <div class="status-good">
        ✅ Your rent is up to date.
    </div>
<?php endif; ?>

<div class="grid">

    <div class="card metric">
        <h4>Monthly Rent</h4>
        <p id="rent"><?= number_format($rent_amount,2) ?></p>
    </div>

    <div class="card metric">
        <h4>Amount Paid</h4>
        <p id="paid"><?= number_format($amount_paid,2) ?></p>
    </div>

    <div class="card metric">
    <h4>Excess Payment (Credit)</h4>
    <p id="credit"><?= number_format($credit,2) ?></p>
    </div>

    <div class="card metric">
        <h4>Penalty</h4>
        <p><?= number_format($penalty,2) ?></p>
    </div>

    <div class="card metric">
        <h4>Outstanding Balance</h4>
        <p><?= number_format($balance,2) ?></p>
    </div>

</div>

<div class="card">
    <h4>Payment Progress</h4>
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
    </div>
    <p style="margin-top:10px;"><?= round($progress) ?>% Paid</p>
</div>

<?php else: ?>
    <div class="card">
        No active tenancy found.
    </div>
<?php endif; ?>

</div>
</div>

<script>

/* ===========================================
   PAYMENT PROGRESS BAR
   =========================================== */

// Get progress percentage from PHP
let progress = <?= round($progress) ?>;

// Animate progress bar immediately (fast)
document.getElementById("progressFill").style.width = progress + "%";


/* ===========================================
   SMOOTH & FAST NUMBER COUNTER
   =========================================== */

/*
   This version:
   - Uses requestAnimationFrame (VERY SMOOTH)
   - No freezing
   - Faster counting
   - Works for large numbers
*/

function animateValue(id, endValue, duration = 1500) {

    const element = document.getElementById(id);
    if (!element) return;

    let startValue = 0;
    let startTime = null;

    function animate(currentTime) {

        if (!startTime) startTime = currentTime;

        const elapsed = currentTime - startTime;

        // Calculate progress percentage
        const progress = Math.min(elapsed / duration, 1);

        // Ease-out effect (smooth finish)
        const easedProgress = 1 - Math.pow(1 - progress, 3);

        const currentValue = Math.floor(easedProgress * endValue);

        element.innerHTML = currentValue.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            element.innerHTML = endValue.toLocaleString();
        }
    }

    requestAnimationFrame(animate);
}

/* ===========================================
   START ANIMATIONS
   =========================================== */

// Faster animation (1.5 seconds)
animateValue("rent", <?= (int)$rent_amount ?>, 1200);
animateValue("paid", <?= (int)$amount_paid ?>, 1200);
animateValue("credit", <?= (int)$credit ?>, 1200);
</script>

</body>
</html>