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

            // 1. INSERT USER KE DATABASE
            $q_insert = mysqli_query($conn, "
                INSERT INTO users (nama, email, password, role)
                VALUES ('$nama','$email','$hash','$role')
            ");

            if ($q_insert) {
                // 2. AMBIL ID USER YANG BARU AJA DIBUAT
                $new_user_id = mysqli_insert_id($conn);

                // 3. CATAT AKTIVITAS SECARA MANUAL
                // (Kita ga pake helper log.php karena user belum login session)
                $aksi = "Mendaftar akun baru sebagai " . ucfirst($role);
                
                // Pastikan nama kolomnya sesuai tabel 'aktivitas' (user_id, nama_user, aksi)
                $q_log = "INSERT INTO aktivitas (user_id, nama_user, aksi) VALUES ('$new_user_id', '$nama', '$aksi')";
                mysqli_query($conn, $q_log);

                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(-45deg, #0ea5e9, #0284c7, #1e293b, #0f172a);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    overflow-x: hidden;
    position: relative;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Partikel Melayang (Sesuai Login) */
.bg-bubbles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    pointer-events: none;
}

.bg-bubbles li {
    position: absolute;
    list-style: none;
    display: block;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    bottom: -160px;
    animation: bubble 25s infinite linear;
}

.bg-bubbles li:nth-child(1) { left: 10%; }
.bg-bubbles li:nth-child(2) { left: 20%; width: 80px; height: 80px; animation-delay: 2s; animation-duration: 17s; }
.bg-bubbles li:nth-child(3) { left: 25%; animation-delay: 4s; }
.bg-bubbles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-duration: 22s; background-color: rgba(255, 255, 255, 0.25); }
.bg-bubbles li:nth-child(5) { left: 70%; }
.bg-bubbles li:nth-child(6) { left: 80%; width: 120px; height: 120px; animation-delay: 3s; background-color: rgba(255, 255, 255, 0.2); }
.bg-bubbles li:nth-child(7) { left: 32%; width: 160px; height: 160px; animation-delay: 7s; }

@keyframes bubble {
    0% { transform: translateY(0); }
    100% { transform: translateY(-1000px) rotate(600deg); opacity: 0; }
}

.register-container {
    max-width: 1000px;
    width: 100%;
    position: relative;
    z-index: 1;
    padding: 20px;
}

.register-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(8px);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.left-panel {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.8) 0%, rgba(2, 132, 199, 0.8) 100%);
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
}

.right-panel {
    padding: 40px 50px;
    background: white;
}

.form-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 2px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 15px;
    background: #f8fafc;
    transition: all 0.3s;
}

.form-control:focus, .form-select:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
    background: white;
}

.btn-register {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    color: white;
    padding: 12px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
    margin-top: 10px;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(14, 165, 233, 0.3);
    color: white;
}

.text-info { color: #0ea5e9 !important; }

@media (max-width: 768px) {
    .left-panel { padding: 30px; }
    .right-panel { padding: 30px 20px; }
}
</style>
</head>

<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li><li></li><li></li>
    </ul>

<div class="container register-container">
  <div class="row register-card">
    
    <div class="col-md-4 left-panel">
      <div class="pulse-animation">
        <i class="bi bi-journal-text" style="font-size: 4rem;"></i>
      </div>
      <h3 class="fw-bold mt-3">NotulenKita</h3>
      <p class="small opacity-75">Gabung bersama kami untuk pengelolaan notulen yang lebih profesional.</p>
    </div>

    <div class="col-md-8 right-panel">
      <div class="mb-4">
        <h4 class="fw-bold text-dark">Buat Akun Baru</h4>
        <p class="text-muted small">Silakan lengkapi data di bawah ini</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small text-center"><?= $error ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success py-2 small text-center"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" name="confirm" class="form-control" placeholder="******" required>
            </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Daftar Sebagai</label>
          <select name="role" class="form-select" required>
            <option value="user">User (Pembuat Notulen)</option>
            <option value="admin">Admin (Pengelola Sistem)</option>
          </select>
        </div>

        <button type="submit" name="register" class="btn btn-register w-100">
          Daftar Sekarang
        </button>
      </form>

      <p class="text-center mt-4 small text-muted">
        Sudah punya akun? 
        <a href="login.php" class="fw-bold text-info text-decoration-none">Masuk di sini</a>
      </p>
    </div>

  </div>
</div>

</body>
</html>