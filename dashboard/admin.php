<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

/* ================== STATISTIK ================== */

// TOTAL RAPAT
$qRapatAll = mysqli_query($conn, "SELECT COUNT(*) AS total FROM rapat");
$totalRapatAll = $qRapatAll ? mysqli_fetch_assoc($qRapatAll)['total'] : 0;

// TOTAL USER
$qUserAll = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalUserAll = $qUserAll ? mysqli_fetch_assoc($qUserAll)['total'] : 0;

// TOTAL RAPAT BULAN INI
$bulan = date('m');
$tahun = date('Y');

$qRapat = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM rapat 
    WHERE MONTH(tanggal)='$bulan' 
    AND YEAR(tanggal)='$tahun'
");
$totalRapat = $qRapat ? mysqli_fetch_assoc($qRapat)['total'] : 0;

// TOTAL NOTULEN
$qCheckNotulen = mysqli_query($conn, "SHOW TABLES LIKE 'notulen'");
if ($qCheckNotulen && mysqli_num_rows($qCheckNotulen) > 0) {
    $qNotulen = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notulen");
    $totalNotulen = $qNotulen ? mysqli_fetch_assoc($qNotulen)['total'] : 0;
} else {
    $totalNotulen = 0;
}

// USER AKTIF HARI INI
$qUserAktif = mysqli_query($conn, "
    SELECT COUNT(DISTINCT user_id) AS total 
    FROM aktivitas 
    WHERE DATE(created_at)=CURDATE()
");
$userAktif = $qUserAktif ? mysqli_fetch_assoc($qUserAktif)['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin | NotulenKita</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid px-4 mt-4">

<!-- ================== STAT CARD ================== -->
<div class="row g-3">

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h3 class="fw-bold mb-0"><?= $totalRapat ?></h3>
          <small class="text-muted">Rapat bulan ini</small>
        </div>
        <div class="stat-icon bg-primary">
          <i class="bi bi-calendar-event"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h3 class="fw-bold mb-0"><?= $totalNotulen ?></h3>
          <small class="text-muted">Notulen terdokumentasi</small>
        </div>
        <div class="stat-icon bg-success">
          <i class="bi bi-file-earmark-text"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h3 class="fw-bold mb-0"><?= $userAktif ?></h3>
          <small class="text-muted">User aktif hari ini</small>
        </div>
        <div class="stat-icon bg-warning">
          <i class="bi bi-people"></i>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ================== AKTIVITAS ================== -->
<div class="card shadow-sm mt-4">
  <div class="card-header bg-white fw-semibold">
    Aktivitas Terbaru
  </div>
  <div class="card-body text-center text-muted" style="min-height:200px">
    Belum ada aktivitas terbaru
  </div>
</div>

</div>

<!-- BOOTSTRAP JS (WAJIB, UNTUK HAMBURGER + MODAL) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
