<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// ADMIN ONLY
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/user.php");
    exit;
}

$q = mysqli_query($conn, "SELECT id, email, role FROM users");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manajemen User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between">
    <strong>Manajemen User</strong>
    <a href="user_tambah.php" class="btn btn-info btn-sm text-white">+ Tambah User</a>
  </div>

  <div class="card-body table-responsive">
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Email</th>
          <th>Role</th>
          <th width="160">Aksi</th>
        </tr>
      </thead>
      <tbody>

      <?php $no=1; while($d=mysqli_fetch_assoc($q)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($d['email']) ?></td>
          <td>
            <span class="badge bg-<?= $d['role']=='admin'?'primary':'secondary' ?>">
              <?= $d['role'] ?>
            </span>
          </td>
          <td>
            <a href="user_edit.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">Edit</a>

            <?php if ($d['id'] != $_SESSION['id']): ?>
              <a href="user_hapus.php?id=<?= $d['id'] ?>"
                 onclick="return confirm('Hapus user ini?')"
                 class="btn btn-danger btn-sm">Hapus</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>

      </tbody>
    </table>
  </div>
</div>
</div>

</body>
</html>
