<?php
session_start();


// Check if user is logged in AND role is tenant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    // redirect to tenant login if not allowed
    header("Location: login.php");
    exit;
}
