<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "db.php";

$role = $_POST['role'];
$email = $_POST['email'];
$password = $_POST['password'];

if ($role === 'tenant') {
    $sql = "SELECT * FROM tenants WHERE email = ?";
} else {
    $sql = "SELECT * FROM landlords WHERE email = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;

        if ($role === 'tenant') {
            header("Location: ../../tenant/dashboard.php");
        } else {
            header("Location: ../../landlord/dashboard.php");
        }
        exit;
    }
}

echo "Invalid login details";
?>