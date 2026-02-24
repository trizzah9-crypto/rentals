<?php
include "../config/db.php";

$users = [
    [
        "name" => "Test Landlord",
        "email" => "landlord@test.com",
        "password" => password_hash("123456", PASSWORD_DEFAULT),
        "role" => "landlord"
    ],
    [
        "name" => "Test Tenant",
        "email" => "tenant@test.com",
        "password" => password_hash("123456", PASSWORD_DEFAULT),
        "role" => "tenant"
    ]
];

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

foreach ($users as $u) {
    $stmt->bind_param("ssss", $u['name'], $u['email'], $u['password'], $u['role']);
    $stmt->execute();
}

echo "Test users created successfully!";
