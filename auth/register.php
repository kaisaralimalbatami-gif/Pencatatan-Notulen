<?php
include "../app/config/database.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $role     = $_POST['role'];

    if ($password != $confirm) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar!";
        } else {

            // INSERT USER
            mysqli_query($conn, "
                INSERT INTO users (nama,email,password,role)
                VALUES ('$nama','$email','$hash','$role')
            ");

            // AMBIL ID USER BARU
            $user_id = mysqli_insert_id($conn);

            // CATAT AKTIVITAS
            mysqli_query($conn, "
                INSERT INTO aktivitas (user_id, aktivitas)
                VALUES ('$user_id', 'Registrasi akun baru')
            ");

            $success = "Registrasi berhasil! Silakan login.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register | NotulenKita</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row shadow rounded-4 overflow-hidden bg-white" style="max-width:900px; width:100%">

    <!-- LEFT -->
    <div class="col-md-4 bg-info text-center text-white d-flex flex-column justify-content-center">
      <img src="../assets/img/logo.png" width="130" class="mx-auto mb-3">
      <h4 class="fw-bold">NotulenKita</h4>
    </div>

    <!-- RIGHT -->
    <div class="col-md-8 p-5">
      <div class="text-center mb-4">
        <img src="../assets/img/logo.png" width="40">
        <h5 class="fw-bold mt-2">Create an Account</h5>
        <small class="text-muted">Register your new account</small>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success text-center"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm" class="form-control" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <button type="submit" name="register" class="btn btn-info w-100 fw-semibold">
          Register
        </button>
      </form>

      <p class="text-center mt-3 small">
        Already have account?
        <a href="login.php" class="fw-semibold text-info">Sign in</a>
      </p>
    </div>

  </div>
</div>

</body>
</html>
