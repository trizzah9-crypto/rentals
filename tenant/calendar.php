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

/* ===============================
   GET ACTIVE TENANCY
================================ */

$sql = "
SELECT t.id AS tenancy_id,
       t.move_in_date,
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
$tenancy = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$tenancy){
    echo "<div class='main'><div class='card'>No active tenancy found.</div></div>";
    exit;
}

$tenancy_id = $tenancy['tenancy_id'];
$move_in_date = $tenancy['move_in_date'];

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

$move_year = date("Y", strtotime($move_in_date));

if($selected_year < $move_year){
    echo "<div class='main'><div class='card'>No active tenancy found for this year.</div></div>";
    exit;
}

$current_month = date("Y-m");
?>

<div class="main">

<div class="topbar">
    <h2>Rent Calendar</h2>
</div>

<form method="GET" style="margin-bottom:20px;">
    <select name="year" onchange="this.form.submit()">
        <?php
        for($y = $move_year; $y <= date("Y")+1; $y++){
            $selected = ($y == $selected_year) ? "selected" : "";
            echo "<option value='$y' $selected>$y</option>";
        }
        ?>
    </select>
</form>

<div class="calendar-grid">

<?php
for($m = 1; $m <= 12; $m++):

    $month_string = $selected_year . "-" . str_pad($m,2,"0",STR_PAD_LEFT);
    $month_label = date("F", strtotime($month_string));

    if($month_string < date("Y-m", strtotime($move_in_date))){
        continue;
    }

    $status_class = "future";
    $badge = "";
    $amount_paid = 0;
    $penalty = 0;
    $balance = 0;

    if($month_string <= $current_month){

        $rentData = recalculate_rent_balance($conn, $tenancy_id, $month_string);

        if($rentData){
            $amount_paid = $rentData['total_paid'];
            $penalty = $rentData['penalty'];
            $balance = $rentData['balance'];

            if($balance <= 0){
                $status_class = "paid";
                $badge = "✓";
            }
            elseif($penalty > 0){
                $status_class = "penalty";
                $badge = "!";
            }
            else{
                $status_class = "unpaid";
                $badge = "•";
            }
        }
    }
?>
<div class="month-card <?= $status_class ?>"
     onclick="openModal('<?= $month_label ?>', <?= $amount_paid ?>, <?= $penalty ?>, <?= $balance ?>)">
     
    <div class="month-name"><?= $month_label ?></div>

    <?php if($badge): ?>
        <div class="badge"><?= $badge ?></div>
    <?php endif; ?>

</div>
<?php endfor; ?>

</div>
</div>

<style>
.calendar-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
}

.month-card{
    padding:30px;
    border-radius:12px;
    text-align:center;
    font-weight:600;
    font-size:16px;
    transition:0.2s;
}

.month-card.paid{
    background:#d4edda;
    color:#155724;
}

.month-card.penalty{
    background:#fff3cd;
    color:#856404;
}

.month-card.unpaid{
    background:#f8d7da;
    color:#721c24;
}

.month-card.future{
    background:#f1f1f1;
    color:#777;
}
.calendar-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
}

.month-card{
    position:relative;
    padding:35px 20px;
    border-radius:16px;
    text-align:center;
    font-weight:600;
    font-size:17px;
    cursor:pointer;
    transition:all 0.25s ease;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.month-card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 25px rgba(0,0,0,0.1);
}

.month-card.paid{
    background:linear-gradient(135deg,#28a745,#218838);
    color:white;
}

.month-card.penalty{
    background:linear-gradient(135deg,#ffc107,#e0a800);
    color:#333;
}

.month-card.unpaid{
    background:linear-gradient(135deg,#dc3545,#c82333);
    color:white;
}

.month-card.future{
    background:#f4f4f4;
    color:#999;
}

.badge{
    position:absolute;
    top:10px;
    right:15px;
    background:white;
    color:black;
    font-size:12px;
    padding:5px 8px;
    border-radius:50%;
    font-weight:bold;
}

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:white;
    padding:30px;
    border-radius:15px;
    width:350px;
    animation:fadeIn 0.2s ease;
}

@keyframes fadeIn{
    from{transform:scale(0.9);opacity:0;}
    to{transform:scale(1);opacity:1;}
}

.close{
    float:right;
    font-size:20px;
    cursor:pointer;
}

.modal-grid{
    margin-top:20px;
    display:grid;
    gap:15px;
}
</style>

<div id="monthModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle"></h3>

        <div class="modal-grid">
            <div>
                <h4>Amount Paid</h4>
                <p id="modalPaid"></p>
            </div>
            <div>
                <h4>Penalty</h4>
                <p id="modalPenalty"></p>
            </div>
            <div>
                <h4>Outstanding Balance</h4>
                <p id="modalBalance"></p>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(month, paid, penalty, balance){
    document.getElementById("modalTitle").innerText = month + " Breakdown";
    document.getElementById("modalPaid").innerText = "KES " + paid.toLocaleString();
    document.getElementById("modalPenalty").innerText = "KES " + penalty.toLocaleString();
    document.getElementById("modalBalance").innerText = "KES " + balance.toLocaleString();

    document.getElementById("monthModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("monthModal").style.display = "none";
}

window.onclick = function(e){
    let modal = document.getElementById("monthModal");
    if(e.target == modal){
        modal.style.display = "none";
    }
}
</script>

</body>
</html>