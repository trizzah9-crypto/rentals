<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require "../config/db.php";
require "includes/functions.php";
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id,name FROM tenants WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$tenant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenant) die("Tenant record not found");

$tenant_id = $tenant['id'];

$stmt = $conn->prepare("
    SELECT t.id AS tenancy_id,
           t.status,
           t.move_in_date,
           t.rent_amount,
           h.house_number,
           h.location,
           h.due_day,
           h.late_interest_per_day
    FROM tenancies t
    INNER JOIN houses h ON t.house_id=h.id
    WHERE t.tenant_id=?
    ORDER BY t.move_in_date DESC
    LIMIT 1
");
$stmt->bind_param("i",$tenant_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$balance = $days_late = $penalty = 0;

if($data){
    $rentData = recalculate_rent_balance($conn,$data['tenancy_id'],date("Y-m"));
    if(is_array($rentData)){
        $balance = (float)$rentData['balance'];
        $penalty = (float)$rentData['penalty'] ?? 0;
        $days_late = (int)$rentData['days_late'] ?? 0;
        $rent_amount = (float)$rentData['rent_amount'] ?? $data['rent_amount'];
    } else {
        $balance = (float)$rentData;
        $rent_amount = (float)$data['rent_amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Tenancy - SaaS Dashboard</title>
<style>
/* Global */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f4f6fa;color:#2d3748;}

/* Container layout */
.container{display:flex;min-height:100vh;}

/* Sidebar */
.sidebar{
    width:250px;background:#1e293b;color:#fff;padding:30px 20px;display:flex;flex-direction:column;
}
.sidebar h2{margin-bottom:40px;font-size:22px;color:#fff;}
.sidebar a{
    display:block;color:#cbd5e1;text-decoration:none;padding:12px 16px;border-radius:8px;margin-bottom:10px;transition:0.2s;
}
.sidebar a:hover{background:#334155;color:#fff;}
.sidebar .active{background:#2563eb;color:#fff;}
.sidebar button{
    margin-top:auto;padding:12px 0;background:#dc2626;color:#fff;border:none;border-radius:6px;cursor:pointer;
}

/* Main content */
.main{flex:1;padding:35px;overflow:auto;}
.main h2{font-size:28px;margin-bottom:25px;color:#111827;}

/* Card */
.card{
    background:#fff;border-radius:14px;padding:25px;box-shadow:0 10px 25px rgba(0,0,0,0.05);margin-bottom:25px;transition:0.3s;
}
.card:hover{transform:translateY(-3px);}
.card p{margin-bottom:15px;font-size:16px;}
.card p strong{display:inline-block;width:160px;color:#374151;}

/* Grid Metrics */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:20px;}
.metric{
    background:#fff;border-radius:12px;padding:20px;text-align:center;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);transition:0.3s;
}
.metric:hover{transform:translateY(-3px);}
.metric h4{font-size:13px;color:#718096;margin-bottom:8px;}
.metric p{font-size:20px;font-weight:700;}

/* Balance highlight */
.balance{color:#2563eb;font-size:20px;font-weight:700;}

/* Status messages */
.status-good{background:#e6f4ea;color:#198754;padding:12px;border-radius:10px;margin-bottom:20px;}
.status-late{background:#fdeaea;color:#dc2626;padding:12px;border-radius:10px;margin-bottom:20px;}

/* Progress bar */
.progress-bar{
    background:#e2e8f0;border-radius:50px;height:14px;overflow:hidden;margin-top:10px;
}
.progress-fill{
    height:100%;background:#2563eb;width:0%;transition:width 1.5s ease;
}
</style>
</head>
<body>

<div class="container">

<!-- Sidebar -->
<div class="sidebar">
    <h2>Tenant Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="payments.php">Make Payment</a>
    <a href="rent_history.php">Rent History</a>
    <a href="tenancy.php" class="active">My Tenancy</a>
    <form method="POST" action="logout.php">
        <button>Logout</button>
    </form>
</div>

<!-- Main -->
<div class="main">
<h2>My Tenancy Overview</h2>

<?php if($data): ?>

<!-- Metrics Grid -->
<div class="grid">
    <div class="metric">
        <h4>Monthly Rent</h4>
        <p>KES <?= number_format($rent_amount,2) ?></p>
    </div>
    <div class="metric">
        <h4>Outstanding Balance</h4>
        <p class="balance">KES <?= number_format($balance,2) ?></p>
    </div>
    <div class="metric">
        <h4>Late Penalty</h4>
        <p>KES <?= number_format($penalty,2) ?></p>
    </div>
    <div class="metric">
        <h4>Days Late</h4>
        <p><?= $days_late ?></p>
    </div>
</div>

<!-- Status message -->
<?php if($days_late>0): ?>
<div class="status-late">
    ⚠ You are <?= $days_late ?> day(s) late. Please pay your rent.
</div>
<?php else: ?>
<div class="status-good">
    ✅ Your rent is up to date. Great job!
</div>
<?php endif; ?>

<!-- Detailed Tenancy Card -->
<div class="card">
    <p><strong>House Number:</strong> <?= htmlspecialchars($data['house_number']) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($data['location']) ?></p>
    <p><strong>Due Day:</strong> <?= $data['due_day'] ?> of each month</p>
    <p><strong>Move In Date:</strong> <?= date("d M Y",strtotime($data['move_in_date'])) ?></p>
    <p><strong>Status:</strong> <?= ucfirst($data['status']) ?></p>
</div>

<?php else: ?>
<div class="card">
    No tenancy found.
</div>
<?php endif; ?>

</div>
</div>

<script>
// Optional: animated counters
document.querySelectorAll('.metric p').forEach(el=>{
    let end = parseInt(el.innerText.replace(/[^0-9]/g,''));
    let start = 0;
    let increment = Math.ceil(end/100);
    let counter = setInterval(()=>{
        start+=increment;
        if(start>=end){start=end;clearInterval(counter);}
        el.innerText = 'KES '+start.toLocaleString();
    },10);
});
</script>

</body>
</html>