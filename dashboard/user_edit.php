<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id'"));

if (isset($_POST['update'])) {
    $role = $_POST['role'];

    mysqli_query($conn, "
        UPDATE users SET role='$role' WHERE id='$id'
    ");

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Edit User</strong></div>
  <div class="card-body">

    <form method="post">
      <div class="mb-3">
        <label>Email</label>
        <input type="text" class="form-control" value="<?= $user['email'] ?>" disabled>
      </div>

      <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-control">
          <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
          <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
      </div>

      <button name="update" class="btn btn-warning">Update</button>
      <a href="user.php" class="btn btn-secondary">Kembali</a>
    </form>

  </div>
</div>
</div>

</body>
</html>
