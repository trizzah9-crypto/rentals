<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require "../config/db.php";
require "includes/tenant_auth.php";
require "includes/functions.php";

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

        $total_due = $rent_amount + $penalty;
        if($total_due > 0){
            $progress = min(100, ($amount_paid / $total_due) * 100);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SaaS Tenant Dashboard</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#f4f7fb;color:#2d3748;}

.container{display:flex;min-height:100vh;}

.sidebar{
    width:230px;
    background:#1e293b;
    color:#fff;
    padding:25px 20px;
}

.sidebar h2{
    margin-bottom:30px;
    font-size:20px;
}

.sidebar a{
    display:block;
    color:#cbd5e1;
    text-decoration:none;
    padding:10px 12px;
    border-radius:6px;
    margin-bottom:8px;
    transition:0.2s;
}

.sidebar a:hover{
    background:#334155;
    color:#fff;
}
.sidebar .active{background:#2563eb;color:#fff;}

.main{
    flex:1;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    margin-bottom:25px;
}

.card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
    margin-bottom:20px;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-4px);
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
}

.metric h4{
    font-size:13px;
    color:#718096;
}

.metric p{
    font-size:22px;
    font-weight:700;
    margin-top:5px;
}

.progress-bar{
    background:#e2e8f0;
    border-radius:50px;
    height:14px;
    overflow:hidden;
    margin-top:10px;
}

.progress-fill{
    height:100%;
    background:#2563eb;
    width:0%;
    transition:width 1.5s ease;
}

.status-good{
    background:#e6f4ea;
    color:#198754;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.status-late{
    background:#fdeaea;
    color:#dc3545;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

button{
    padding:10px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.btn-primary{
    background:#2563eb;
    color:#fff;
}

.btn-danger{
    background:#dc3545;
    color:#fff;
}
</style>
</head>
<body>

<div class="container">

<div class="sidebar">
    <h2>Tenant Panel</h2>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="payments.php">Make Payment</a>
    <a href="rent_history.php">Rent History</a>
    <a href="tenancy.php">My Tenancy</a>
    <form method="POST" action="logout.php">
        <button class="btn-danger" style="margin-top:15px;width:100%;">Logout</button>
    </form>
</div>

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
let progress = <?= round($progress) ?>;
document.getElementById("progressFill").style.width = progress + "%";

/* Animated metric counter */
function animateValue(id, start, end, duration) {
    let obj = document.getElementById(id);
    if(!obj) return;
    let range = end - start;
    let current = start;
    let increment = end > start ? 1 : -1;
    let stepTime = Math.abs(Math.floor(duration / range));
    let timer = setInterval(function() {
        current += increment;
        obj.innerHTML = current.toLocaleString();
        if (current == end) {
            clearInterval(timer);
        }
    }, stepTime);
}

animateValue("rent", 0, <?= (int)$rent_amount ?>, 9000);
animateValue("paid", 0, <?= (int)$amount_paid ?>, 9000);
</script>

</body>
</html>