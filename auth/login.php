<?php
session_start();
require_once "../app/config/database.php";
// Pastikan path helper log benar, kita load di awal biar rapi
require_once '../app/helpers/log.php'; 

$error = null;

/**
 * Fungsi Modular: Mencari user berdasarkan email
 * Memenuhi poin no. 1 (Logika fungsi) dan no. 3 (Clean Code - Prepared Statement)
 */
function cariUserBerdasarkanEmail($conn, $email) {
    try {
        // Memenuhi poin no. 2 (Try Catch pada kueri DB)
        // Menggunakan Prepared Statement agar lebih aman dan clean
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Kembalikan data user sebagai assoc array, atau null jika tidak ada
        return $result->fetch_assoc();

    } catch (Exception $e) {
        // Catat error ke log server, jangan tampilkan detail teknis ke user
        error_log("Error Login Query: " . $e->getMessage());
        return null;
    }
}

// Proses Login Utama
if (isset($_POST['login'])) {
    // Panggil fungsi koneksi baru kita
    $conn = getDBConnection();

    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Panggil fungsi pencarian user
    $user = cariUserBerdasarkanEmail($conn, $email);

    if ($user) {
        // Verifikasi Password
        if (password_verify($password, $user['password'])) {
            
            // 1. SET SESSION
            $_SESSION['login'] = true;
            $_SESSION['id']    = $user['id'];
            $_SESSION['nama']  = $user['nama'] ?? $user['email'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role']  = $user['role'];

            // 2. CATAT LOG (Menggunakan try-catch juga biar aman)
            try {
                if (function_exists('catat_log')) {
                    catat_log($conn, "Login berhasil ke sistem");
                }
            } catch (Exception $logError) {
                error_log("Gagal catat log: " . $logError->getMessage());
            }

            // 3. REDIRECT CLEAN CODE
            $redirectUrl = ($user['role'] === 'admin') ? "../dashboard/admin.php" : "../dashboard/user.php";
            header("Location: " . $redirectUrl);
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

/* Partikel Melayang */
.bg-bubbles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
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
.bg-bubbles li:nth-child(8) { left: 55%; width: 20px; height: 20px; animation-delay: 15s; animation-duration: 40s; }
.bg-bubbles li:nth-child(9) { left: 25%; width: 10px; height: 10px; animation-delay: 2s; animation-duration: 40s; background-color: rgba(255, 255, 255, 0.3); }
.bg-bubbles li:nth-child(10) { left: 90%; width: 160px; height: 160px; animation-delay: 11s; }

@keyframes bubble {
    0% { transform: translateY(0); }
    100% { transform: translateY(-1000px) rotate(600deg); opacity: 0; }
}

.login-container {
    max-width: 1000px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.login-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    height: 500px;
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
    position: relative;
    overflow: hidden;
}

.logo-container {
    position: relative;
    z-index: 2;
    text-align: center;
}

.logo-circle {
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.logo-circle i {
    font-size: 45px;
}

.app-name {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: 1px;
}

.app-tagline {
    font-size: 0.9rem;
    opacity: 0.9;
    max-width: 250px;
    line-height: 1.5;
}

.right-panel {
    padding: 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: white;
}

.welcome-text h3 {
    color: #0f172a;
    font-weight: 700;
    margin-bottom: 8px;
}

.welcome-text p {
    color: #475569;
    margin-bottom: 30px;
}

.form-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control-custom {
    border: 2px solid #cbd5e1;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 1rem;
    transition: all 0.3s;
    width: 100%;
    background: #f8fafc;
}

.form-control-custom:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
    outline: none;
    background: white;
}

.form-group {
    margin-bottom: 25px;
}

.btn-login {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    color: white;
    padding: 14px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-login:hover {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(14, 165, 233, 0.3);
    color: white;
}

.register-link {
    text-align: center;
    margin-top: 25px;
    color: #64748b;
}

.register-link a {
    color: #0ea5e9;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s;
}

.alert-custom {
    border-radius: 10px;
    border: none;
    padding: 15px;
    margin-bottom: 25px;
    text-align: center;
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    z-index: 3;
}

.input-wrapper {
    position: relative;
}

.input-wrapper .form-control-custom {
    padding-left: 45px;
}

@media (max-width: 768px) {
    .login-card {
        height: auto;
        margin: 20px;
    }
    .left-panel {
        padding: 30px 20px;
        height: 250px;
    }
    .right-panel {
        padding: 30px 25px;
    }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.feature-list {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    opacity: 0.9;
}

.form-control-custom.is-invalid {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}
</style>
</head>

<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

<div class="container login-container">
  <div class="row login-card">
    
    <div class="col-md-5 left-panel">
      <div class="logo-container">
        <div class="logo-circle pulse-animation">
          <i class="bi bi-journal-text"></i>
        </div>
        <h1 class="app-name">NotulenKita</h1>
        <p class="app-tagline">Kelola notulen rapat dengan mudah, cepat, dan terorganisir</p>
      </div>
      
      <div class="feature-list">
        <div class="feature-item">
          <i class="bi bi-check-circle-fill"></i>
          <span>Buat notulen dengan cepat</span>
        </div>
        <div class="feature-item">
          <i class="bi bi-check-circle-fill"></i>
          <span>Kelola agenda rapat</span>
        </div>
        <div class="feature-item">
          <i class="bi bi-check-circle-fill"></i>
          <span>Export ke PDF</span>
        </div>
        <div class="feature-item">
          <i class="bi bi-check-circle-fill"></i>
          <span>Akses multi-user</span>
        </div>
      </div>
    </div>

    <div class="col-md-7 right-panel">
      <div class="welcome-text">
        <h3>Selamat Datang Kembali</h3>
        <p>Masuk ke akun Anda untuk melanjutkan</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-custom">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-envelope"></i>Email
          </label>
          <div class="input-wrapper">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" name="email" class="form-control-custom" 
                   placeholder="nama@email.com" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="bi bi-lock"></i>Password
          </label>
          <div class="input-wrapper">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" class="form-control-custom" 
                   placeholder="Masukkan password" required>
          </div>
        </div>

        <button type="submit" name="login" class="btn btn-login">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Masuk ke Akun</span>
        </button>

        <div class="register-link">
          Belum punya akun?
          <a href="register.php" class="ms-1">Daftar sekarang</a>
        </div>
      </form>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control-custom');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.querySelector('.input-icon').style.color = '#0ea5e9';
        });
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.parentElement.querySelector('.input-icon').style.color = '#94a3b8';
            }
        });
    });
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const email = form.querySelector('input[name="email"]');
        const password = form.querySelector('input[name="password"]');
        let valid = true;
        
        if (!email.value.trim()) {
            email.classList.add('is-invalid');
            valid = false;
        }
        if (!password.value.trim()) {
            password.classList.add('is-invalid');
            valid = false;
        }
    });
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});
</script>
</body>
</html>