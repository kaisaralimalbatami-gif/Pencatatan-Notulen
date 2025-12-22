<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    mysqli_query($conn, "
        INSERT INTO users (email, password, role)
        VALUES ('$email', '$password', '$role')
    ");

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Tambah User</strong></div>
  <div class="card-body">

    <form method="post">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button name="simpan" class="btn btn-info text-white">Simpan</button>
      <a href="user.php" class="btn btn-secondary">Kembali</a>
    </form>

  </div>
</div>
</div>

</body>
</html>
