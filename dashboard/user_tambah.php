<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();

session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI ADMIN ONLY
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. KONEKSI DATABASE
$conn = getDBConnection();

/**
 * Fungsi Modular: Cek Email Terdaftar
 */
function isEmailExist($conn, $email) {
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Fungsi Modular: Tambah User Baru
 */
function tambahUser($conn, $nama, $email, $password, $role) {
    try {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Query Insert (Tambahkan kolom 'nama' agar sesuai database)
        $sql = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nama, $email, $hash, $role);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Gagal eksekusi query.");
        }
    } catch (Exception $e) {
        error_log("Error Tambah User: " . $e->getMessage());
        return false;
    }
}

// 3. PROSES SIMPAN DATA
$error = "";
$success = "";

if (isset($_POST['simpan'])) {
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Validasi dasar
    if (isEmailExist($conn, $email)) {
        $error = "Email sudah terdaftar! Gunakan email lain.";
    } else {
        if (tambahUser($conn, $nama, $email, $password, $role)) {
            // Redirect sukses
            echo "<script>alert('User berhasil ditambahkan!'); window.location='user_manage.php';</script>";
            exit;
        } else {
            $error = "Terjadi kesalahan sistem saat menyimpan data.";
        }
    }
}

// INCLUDE NAVBAR
require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User | NotulenKita</title>
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
            color: #333;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .main-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            padding: 25px;
            color: white;
            text-align: center;
        }

        .form-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .card-body {
            padding: 40px !important;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Strength Meter */
        .strength-meter {
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }

        .strength-meter-fill {
            height: 100%;
            width: 0;
            transition: width 0.5s ease, background-color 0.5s ease;
        }

        /* Button Styling */
        .btn-simpan {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white; border: none; padding: 12px 25px;
            border-radius: 12px; font-weight: 600; transition: 0.3s;
            width: 100%;
        }

        .btn-simpan:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .btn-back {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .tips-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border-left: 5px solid #6366f1;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="glass-card">
        <div class="form-header">
            <h2><i class="bi bi-person-plus-fill"></i> Tambah User Baru</h2>
        </div>

        <div class="card-body">
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger rounded-4 border-0 mb-4"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" id="registrationForm">
                
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-person me-2"></i>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap User" required>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-envelope me-2"></i>Alamat Email</label>
                    <input type="email" name="email" class="form-control" placeholder="user@notulenkita.com" required>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-shield-lock me-2"></i>Password</label>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Minimal 8 karakter" required>
                    <div class="strength-meter">
                        <div id="strengthFill" class="strength-meter-fill"></div>
                    </div>
                    <small id="strengthText" class="text-muted mt-2 d-block">Kekuatan password: -</small>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-person-badge me-2"></i>Role Akses</label>
                    <select name="role" class="form-select" required>
                        <option value="" disabled selected>Pilih Level Akses</option>
                        <option value="user">USER (Akses Terbatas)</option>
                        <option value="admin">ADMIN (Akses Penuh)</option>
                    </select>
                </div>

                <div class="tips-box">
                    <h6 class="fw-bold text-dark"><i class="bi bi-lightbulb me-2 text-warning"></i>Tips Keamanan</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Password akan otomatis di-hash demi keamanan.</li>
                        <li>Pastikan email user aktif untuk keperluan koordinasi.</li>
                        <li>Admin memiliki wewenang untuk menghapus semua notulen.</li>
                    </ul>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-8">
                        <button type="submit" name="simpan" class="btn-simpan">
                            <i class="bi bi-check-circle-fill me-2"></i> Daftarkan User Sekarang
                        </button>
                    </div>
                    <div class="col-md-4">
                        <a href="user_manage.php" class="btn-back">
                            <i class="bi bi-arrow-left me-2"></i> Batal
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password Strength Checker
    const passwordInput = document.getElementById('passwordInput');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let strength = 0;
        
        if (val.length >= 8) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        let width = "0%";
        let color = "#e2e8f0";
        let label = "Terlalu Pendek";

        if (val.length > 0) {
            switch(strength) {
                case 0: width = "25%"; color = "#ef4444"; label = "Sangat Lemah"; break;
                case 1: width = "50%"; color = "#f59e0b"; label = "Lemah"; break;
                case 2: width = "75%"; color = "#3b82f6"; label = "Kuat"; break;
                case 3: 
                case 4: width = "100%"; color = "#10b981"; label = "Sangat Aman"; break;
            }
        }

        strengthFill.style.width = width;
        strengthFill.style.backgroundColor = color;
        strengthText.innerText = "Kekuatan password: " + label;
        strengthText.style.color = color;
    });

    // Form Dirty Confirmation
    let isDirty = false;
    document.getElementById('registrationForm').addEventListener('input', () => isDirty = true);
    
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.getElementById('registrationForm').addEventListener('submit', () => isDirty = false);
</script>

</body>
</html>