<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'landlord') {
    header("Location: landlord/dashboard.php");
    exit;
}

if ($_SESSION['role'] === 'tenant') {
    header("Location: tenant/dashboard.php");
    exit;
}

session_destroy();
header("Location: login.php");
exit;
