<?php
// app/config/database.php

$host = 'localhost';
$user = 'root'; 
$pass = ''; 
$dbname = 'notulenkita';

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed: " . mysqli_connect_error());
    
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        die("❌ Koneksi database gagal: " . mysqli_connect_error());
    } else {
        die("❌ Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.");
    }
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Optional: Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Jangan echo apapun di config file
?>