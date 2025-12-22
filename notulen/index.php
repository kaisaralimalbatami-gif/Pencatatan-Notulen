<?php
session_start();
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../include/navbar.php';

$q = mysqli_query($conn,"
  SELECT n.id, r.judul, n.dibuat_pada
  FROM notulen n
  JOIN rapat r ON r.id=n.rapat_id
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Daftar Notulen</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
<div class="card-header bg-white"><strong>Daftar Notulen</strong></div>

<div class="card-body table-responsive">
<table class="table table-bordered">
<thead class="table-light">
<tr>
  <th>Rapat</th>
  <th>Dibuat</th>
  <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php while($d=mysqli_fetch_assoc($q)): ?>
<tr>
  <td><?= htmlspecialchars($d['judul']) ?></td>
  <td><?= $d['dibuat_pada'] ?></td>
  <td>
    <a href="pdf.php?id=<?= $d['id'] ?>" class="btn btn-danger btn-sm">
      PDF
    </a>

    <?php if($_SESSION['role']=='admin'): ?>
      <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
    <?php endif; ?>
  </td>
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
