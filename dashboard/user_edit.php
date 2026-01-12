<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();

session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI ADMIN
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. KONEKSI DATABASE
$conn = getDBConnection();

/**
 * Fungsi Modular: Ambil Data User berdasarkan ID
 */
function getUserById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error Get User: " . $e->getMessage());
        return null;
    }
}

/**
 * Fungsi Modular: Update Data User
 */
function updateUser($conn, $id, $nama, $role) {
    try {
        // Gunakan Prepared Statement untuk keamanan maksimal
        $stmt = $conn->prepare("UPDATE users SET nama = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $role, $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log("Error Update User: " . $e->getMessage());
        return false;
    }
}

// 3. VALIDASI ID & AMBIL DATA
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_manage.php");
    exit;
}

$id_user = (int)$_GET['id'];
$user = getUserById($conn, $id_user);

// Jika user tidak ditemukan
if (!$user) {
    header("Location: user_manage.php");
    exit;
}

// 4. PROSES UPDATE FORM
$error = "";
if (isset($_POST['update_user'])) {
    $nama = $_POST['nama']; // Sesuai kolom database 'nama'
    $role = $_POST['role'];
    
    // Validasi sederhana
    if (empty($nama)) {
        $error = "Nama tidak boleh kosong.";
    } else {
        if (updateUser($conn, $id_user, $nama, $role)) {
            // Redirect sukses
            echo "<script>alert('Data berhasil diperbarui!'); window.location='user_manage.php';</script>";
            exit;
        } else {
            $error = "Terjadi kesalahan saat menyimpan data.";
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
    <title>Edit User | NotulenKita</title>
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
            margin: 0;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .main-wrapper {
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 70px);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
            padding: 35px;
        }

        .header-section {
            border-left: 8px solid #6366f1;
            padding-left: 20px;
            margin-bottom: 30px;
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
        }

        .btn-update {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            transition: 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(99, 102, 241, 0.3);
        }

        .user-badge-info {
            background: #eef2ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="glass-card">
        <div class="stat-icon">
            <i class="bi bi-person-gear"></i>
        </div>

        <div class="header-section">
            <h3 class="fw-bold mb-1">Edit Data User</h3>
            <p class="text-muted mb-0 small">ID Pengguna: <span class="user-badge-info">#<?= $user['id'] ?></span></p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger rounded-4 border-0"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Email / Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Role Akses</label>
                <select name="role" class="form-select" required>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>PENGGUNA (USER)</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>ADMINISTRATOR</option>
                </select>
            </div>

            <button type="submit" name="update_user" class="btn-update">
                <i class="bi bi-save2 me-2"></i> Simpan Perubahan
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="user_manage.php" class="text-decoration-none text-muted small fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Manajemen User
            </a>
        </div>
    </div>
</div>

</body>
</html>