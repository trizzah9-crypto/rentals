<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require "../../config/db.php";
require "../../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

$id = isset($_POST['payment_id']) ? (int)$_POST['payment_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['approve','reject'])) {
    echo json_encode(['status'=>'error','message'=>'Invalid data']);
    exit;
}

try {
    // Get payment row
    $stmt = $conn->prepare("SELECT id, tenancy_id, payment_month, status FROM payments WHERE id=? LIMIT 1");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$payment) {
        echo json_encode(['status'=>'error','message'=>'Payment not found']);
        exit;
    }

    if ($action==='reject') {
        $stmt=$conn->prepare("UPDATE payments SET status='rejected' WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status'=>'success','message'=>'Payment rejected']);
        exit;
    }

    if ($action==='approve') {
        if ($payment['status']==='approved') {
            echo json_encode(['status'=>'error','message'=>'Already approved']);
            exit;
        }

        // Approve payment
        $stmt=$conn->prepare("UPDATE payments SET status='approved' WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();

        // Recalculate balance
        $tenancy_id = (int)$payment['tenancy_id'];
        $month = $payment['payment_month'];
        $new_balance = recalculate_rent_balance($conn,$tenancy_id,$month);

        echo json_encode([
            'status'=>'success',
            'message'=>'Payment approved',
            'rent_balance'=>$new_balance
        ]);
        exit;
    }

} catch(Exception $e) {
    echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()]);
    exit;
}
?>