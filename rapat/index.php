<?php
session_start();
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../include/navbar.php';

$q = mysqli_query($conn,"SELECT * FROM rapat ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Rapat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between">
    <strong>Daftar Rapat</strong>

    <?php if($_SESSION['role']=='admin'): ?>
      <a href="tambah.php" class="btn btn-info btn-sm text-white">+ Tambah</a>
    <?php endif; ?>
  </div>

  <div class="card-body table-responsive">
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Judul</th>
          <th>Tanggal</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php while($d=mysqli_fetch_assoc($q)): ?>
        <tr>
          <td><?= htmlspecialchars($d['judul']) ?></td>
          <td><?= $d['tanggal'] ?></td>
          <td><?= $d['status'] ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
