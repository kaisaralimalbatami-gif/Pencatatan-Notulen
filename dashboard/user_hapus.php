<?php
ob_start();
session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI ADMIN
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. VALIDASI ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_manage.php");
    exit;
}

$id = (int)$_GET['id'];
$current_admin_id = $_SESSION['user_id'] ?? 0;

// 3. AMBIL DATA USER
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    header("Location: user_manage.php");
    exit;
}

// 4. LOGIKA PROTEKSI (Anti-hapus diri sendiri)
if ($id == $current_admin_id) {
    $_SESSION['error'] = 'Gagal: Anda tidak bisa menghapus akun sendiri!';
    header("Location: user_manage.php");
    exit;
}

// 5. PROSES HAPUS
if (isset($_POST['execute_delete'])) {
    $target = $user['email'];
    if (mysqli_query($conn, "DELETE FROM users WHERE id = '$id'")) {
        $_SESSION['success'] = "User $target berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus user.";
    }
    header("Location: user_manage.php");
    exit;
}

// INCLUDE NAVBAR
require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus User | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(-45deg, #0ea5e9, #0284c7, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* WRAPPER KHUSUS AGAR NAVBAR TIDAK TERGANGGU */
        .main-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px); /* Kurangi tinggi navbar sekitar 80px */
            padding: 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            text-align: center;
        }

        .header-danger {
            border-left: 6px solid #ef4444;
            padding: 10px 15px;
            background: rgba(239, 68, 68, 0.05);
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .stat-icon-danger {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: white;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 15px;
            box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);
        }

        .user-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            font-weight: 700;
        }

        .btn-confirm {
            background: #ef4444;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-confirm:hover:not(:disabled) {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-confirm:disabled { background: #cbd5e1; }

        .cancel-link {
            display: inline-block;
            margin-top: 15px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="glass-card">
        <div class="stat-icon-danger">
            <i class="bi bi-person-x-fill"></i>
        </div>

        <div class="header-danger">
            <h5 class="fw-bold text-dark mb-1">Hapus Pengguna</h5>
            <p class="text-muted small mb-0">Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <div class="user-info-box">
            <span class="text-uppercase x-small fw-bold text-muted d-block mb-1" style="font-size: 0.7rem;">Target User:</span>
            <span class="fw-bold text-dark"><?= htmlspecialchars($user['email']) ?></span>
            <div class="mt-2">
                <span class="badge bg-secondary opacity-75">ID: #<?= $user['id'] ?></span>
                <span class="badge bg-info text-white ms-1"><?= strtoupper($user['role']) ?></span>
            </div>
        </div>

        <form method="post" id="deleteForm">
            <div class="mb-3 text-start">
                <label class="small fw-bold text-muted mb-2">Ketik "HAPUS" untuk konfirmasi:</label>
                <input type="text" id="confirmInput" class="form-control form-control-custom" placeholder="..." autocomplete="off">
            </div>

            <button type="submit" name="execute_delete" id="deleteBtn" class="btn-confirm" disabled>
                <i class="bi bi-trash3 me-2"></i> Eksekusi Hapus
            </button>
        </form>

        <a href="user_manage.php" class="cancel-link">
            <i class="bi bi-arrow-left me-1"></i> Batal dan Kembali
        </a>
    </div>
</div>

<script>
    const confirmInput = document.getElementById('confirmInput');
    const deleteBtn = document.getElementById('deleteBtn');

    confirmInput.addEventListener('input', function() {
        if (this.value.trim().toUpperCase() === 'HAPUS') {
            deleteBtn.disabled = false;
        } else {
            deleteBtn.disabled = true;
        }
    });
</script>

</body>
</html>