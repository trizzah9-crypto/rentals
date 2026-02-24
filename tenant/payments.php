<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

require "../config/db.php";
require "includes/tenant_auth.php";
require "includes/functions.php";

$user_id = $_SESSION['user_id'];

/* -------------------- GET TENANT -------------------- */
$stmt = $conn->prepare("SELECT id,name FROM tenants WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$tenant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenant) die("Tenant record not found");

$tenant_id = $tenant['id'];

/* -------------------- ACTIVE TENANCY -------------------- */
$stmt = $conn->prepare("
    SELECT t.id AS tenancy_id, t.rent_balance, h.house_number
    FROM tenancies t
    INNER JOIN houses h ON t.house_id=h.id
    WHERE t.tenant_id=? AND t.status='active'
    ORDER BY t.move_in_date DESC LIMIT 1
");
$stmt->bind_param("i",$tenant_id);
$stmt->execute();
$tenancy = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenancy) die("No active tenancy found");

$tenancy_id = $tenancy['tenancy_id'];
$rent_balance = is_null($tenancy['rent_balance']) ? 0 : (float)$tenancy['rent_balance'];
$house_number = $tenancy['house_number'];

/* -------------------- HANDLE FORM -------------------- */
$message = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $payment_month = date("Y-m");

    if($amount <=0 || empty($payment_method) || empty($reference)){
        $message = "All fields are required and amount must be greater than 0.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO payments 
            (tenancy_id, amount, payment_month, payment_date, status, payment_type, payment_method, reference)
            VALUES (?, ?, ?, NOW(), 'pending', 'rent', ?, ?)
        ");
        $stmt->bind_param("idsss", $tenancy_id, $amount, $payment_month, $payment_method, $reference);
        if($stmt->execute()){
            $message = "Payment submitted successfully and is pending approval.";
        } else {
            $message = "Error: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Make Payment - Tenant Panel</title>
<style>
/* RESET */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#f4f7fb;color:#2d3748;}

/* LAYOUT */
.container{display:flex;min-height:100vh;}
.sidebar{
    width:230px;background:#1e293b;color:#fff;padding:25px 20px;
}
.sidebar h2{margin-bottom:30px;font-size:20px;}
.sidebar a{
    display:block;color:#cbd5e1;text-decoration:none;padding:10px 12px;border-radius:6px;margin-bottom:8px;transition:0.2s;
}
.sidebar a:hover{background:#334155;color:#fff;}
.sidebar .active{background:#2563eb;color:#fff;}
.sidebar button{
    width:100%;margin-top:15px;padding:10px 0;background:#dc3545;color:#fff;border:none;border-radius:6px;cursor:pointer;
}
.main{flex:1;padding:30px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;}
.card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 8px 20px rgba(0,0,0,0.05);margin-bottom:20px;}
.card h2{text-align:center;margin-bottom:15px;}
input, select{width:100%;padding:10px;margin-top:5px;border-radius:6px;border:1px solid #ccc;}
label{display:block;margin-top:15px;font-weight:600;}
button[type=submit]{margin-top:20px;padding:12px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;transition:0.2s;}
button[type=submit]:hover{background:#1d4ed8;}
.message{margin-top:15px;font-weight:600;}
.message.success{color:#198754;}
.message.error{color:#dc2626;}
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
    <a href="payments.php" class="active">Make Payment</a>
    <a href="rent_history.php">Rent History</a>
    <a href="tenancy.php">My Tenancy</a>
    <form method="POST" action="logout.php">
        <button class="btn-danger" style="margin-top:15px;width:100%;">Logout</button>
    </form>
</div>

<!-- MAIN CONTENT -->
<div class="main">

<div class="card">
    <h2>Make Payment for House <?= htmlspecialchars($house_number) ?></h2>

    <?php if($message): ?>
        <p class="message <?= strpos($message,'success')!==false ? 'success':'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Amount (KES)</label>
        <input type="number" name="amount" step="0.01" max="<?= $rent_balance ?>" value="<?= $rent_balance ?>" required>

        <label>Payment Method</label>
        <select name="payment_method" required>
            <option value="">Select</option>
            <option value="mpesa">Mpesa</option>
            <option value="cash">Cash</option>
            <option value="bank">Bank Transfer</option>
        </select>

        <label>Reference</label>
        <input type="text" name="reference" placeholder="Enter transaction reference" required>

        <button type="submit">Submit Payment</button>
    </form>

    <p style="margin-top:15px;"><a href="dashboard.php">← Back to Dashboard</a></p>
</div>

</div>
</div>

</body>
</html>