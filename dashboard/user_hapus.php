<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

header("Location: user.php");
exit;
