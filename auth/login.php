<?php
session_start();
require_once "../app/config/database.php";

$error = null;

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($q) === 1) {
        $user = mysqli_fetch_assoc($q);

        if (password_verify($password, $user['password'])) {

            $_SESSION['login'] = true;
            $_SESSION['id']    = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role']  = $user['role'];

            // redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: ../dashboard/admin.php");
            } else {
                header("Location: ../dashboard/user.php");
            }
            exit;

        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Email tidak ditemukan";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login | NotulenKita</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">
  <div class="row shadow rounded-4 overflow-hidden bg-white" style="max-width:850px; width:100%">

    <!-- LEFT -->
    <div class="col-md-4 bg-info text-center text-white d-flex flex-column justify-content-center">
      <img src="../assets/img/logo.png" width="130" class="mx-auto mb-3">
      <h4 class="fw-bold">NotulenKita</h4>
    </div>

    <!-- RIGHT -->
    <div class="col-md-8 p-5">
      <div class="text-center mb-4">
        <h5 class="fw-bold mt-2">Sign In</h5>
        <small class="text-muted">Please login to continue</small>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="login" class="btn btn-info w-100 fw-semibold text-white">
          Login
        </button>
      </form>

      <p class="text-center mt-3 small">
        No Account?
        <a href="register.php" class="fw-semibold text-info">Create Account</a>
      </p>
    </div>

  </div>
</div>

</body>
</html>
