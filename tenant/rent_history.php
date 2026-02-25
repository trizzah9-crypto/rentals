<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require "../config/db.php";
require "includes/tenant_auth.php";

$user_id = $_SESSION['user_id'];

/* Get Tenant */
$stmt = $conn->prepare("SELECT id,name FROM tenants WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$tenant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenant){
    die("Tenant not found");
}

$tenant_id = $tenant['id'];

/* Get Payments */
$sql = "
SELECT p.amount, 
       p.payment_month, 
       p.payment_date, 
       p.status, 
       p.payment_method
FROM payments p
INNER JOIN tenancies t ON p.tenancy_id = t.id
WHERE t.tenant_id=?
ORDER BY p.payment_date DESC, p.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$tenant_id);
$stmt->execute();
$payments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Rent History</title>

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

.sidebar h2{margin-bottom:30px;font-size:20px;}

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

.sidebar .active{
    background:#2563eb;
    color:#fff;
}

.main{flex:1;padding:30px;}

.card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th,.table td{
    padding:12px;
    border-bottom:1px solid #e2e8f0;
}

.table th{
    background:#f8fafc;
    text-align:left;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.approved{background:#e6f4ea;color:#198754;}
.pending{background:#fff4e5;color:#b45309;}
.rejected{background:#fdeaea;color:#dc3545;}

.btn-danger{
    background:#dc3545;
    color:#fff;
    border:none;
    padding:10px 15px;
    border-radius:6px;
    cursor:pointer;
}
</style>
</head>
<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Tenant Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="payments.php">Make Payment</a>
    <a href="rent_history.php" class="active">Rent History</a>
    <a href="tenancy.php">My Tenancy</a>

    <form method="POST" action="logout.php">
        <button class="btn-danger" style="margin-top:15px;width:100%;">Logout</button>
    </form>
</div>

<!-- MAIN -->
<div class="main">
<h2>Rent History</h2>
<br>

<div class="card">
<table class="table">
<tr>
<th>Month</th>
<th>Amount</th>
<th>Date</th>
<th>Method</th>
<th>Status</th>
</tr>

<?php if($payments->num_rows > 0): ?>
    <?php while($row = $payments->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['payment_month']) ?></td>
        <td>KES <?= number_format((float)$row['amount'],2) ?></td>
        <td><?= date("d M Y", strtotime($row['payment_date'])) ?></td>
        <td><?= htmlspecialchars($row['payment_method']) ?></td>
        <td>
            <span class="badge <?= strtolower($row['status']) ?>">
                <?= ucfirst($row['status']) ?>
            </span>
        </td>

        
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="5">No payment records found.</td>
</tr>
<?php endif; ?>

</table>
</div>

</div>
</div>

</body>
</html>