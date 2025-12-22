<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// proteksi login (USER & ADMIN BOLEH)
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">

<div class="alert alert-info">
  Selamat datang, <strong><?= htmlspecialchars($_SESSION['email']) ?></strong>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5>Hak Akses User</h5>
    <ul>
      <li>✔ Lihat daftar rapat</li>
      <li>✔ Lihat daftar notulen</li>
      <li>✔ Download PDF notulen</li>
      <li>❌ Tidak bisa input / edit / hapus</li>
    </ul>
  </div>
</div>

</div>
</body>
</html>
