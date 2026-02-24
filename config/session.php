<?php

// Start a session that expires when browser closes
session_set_cookie_params([
    'lifetime' => 0, // 0 = until browser closes
    'path' => '/',
    'domain' => '',   // leave blank for current domain
    'secure' => false, // true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

