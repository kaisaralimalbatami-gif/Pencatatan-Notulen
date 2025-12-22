<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// validasi id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// hapus data
mysqli_query($conn, "DELETE FROM rapat WHERE id='$id'");

// kembali ke list
header("Location: index.php");
exit;
