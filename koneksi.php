<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "project"; // Nama Database

// Melakukan koneksi ke database
$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Gagal konek: " . mysqli_connect_error());
}
?>
