<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. KONEKSI DATABASE
$conn = getDBConnection();

// 2. PROTEKSI: Cek login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Anda tidak memiliki izin untuk menghapus data!';
    header("Location: index.php");
    exit;
}

/**
 * Fungsi Modular: Ambil Data Notulen untuk Konfirmasi
 */
function getNotulenInfo($conn, $id) {
    try {
        $sql = "SELECT n.id, n.judul_notulen, r.judul as judul_rapat, r.tanggal 
                FROM notulen n 
                LEFT JOIN rapat r ON r.id = n.rapat_id 
                WHERE n.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Fungsi Modular: Eksekusi Hapus
 */
function hapusNotulen($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM notulen WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error Hapus Notulen: " . $e->getMessage());
        return false;
    }
}

// 3. PROSES HAPUS (Jika form disubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $id_to_delete = (int)$_POST['id'];
    
    // Ambil data dulu untuk log
    $dataNotulen = getNotulenInfo($conn, $id_to_delete);
    
    if ($dataNotulen) {
        $judul = $dataNotulen['judul_notulen'];

        if (hapusNotulen($conn, $id_to_delete)) {
            // --- LOG AKTIVITAS ---
            $logPath = __DIR__ . '/../app/helpers/log.php';
            if (file_exists($logPath)) {
                require_once $logPath;
                if (function_exists('catat_log')) {
                    catat_log($conn, "Menghapus notulen: " . $judul);
                }
            }
            // ---------------------

            $_SESSION['success'] = 'Notulen berhasil dihapus secara permanen.';
        } else {
            $_SESSION['error'] = 'Gagal menghapus data. Silakan coba lagi.';
        }
    } else {
        $_SESSION['error'] = 'Data tidak ditemukan.';
    }
    
    header("Location: index.php");
    exit;
}

// 4. VALIDASI ID UNTUK TAMPILAN
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID tidak valid!';
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$notulen = getNotulenInfo($conn, $id);

if (!$notulen) {
    $_SESSION['error'] = 'Data tidak ditemukan!';
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Hapus | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            background: linear-gradient(-45deg, #ef4444, #991b1b, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Bubbles Background */
        .bg-bubbles {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; overflow: hidden; pointer-events: none;
        }
        .bg-bubbles li {
            position: absolute; list-style: none; display: block;
            width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.1);
            bottom: -160px; animation: bubble 25s infinite linear;
            border-radius: 50%;
        }
        .bg-bubbles li:nth-child(1) { left: 10%; width: 80px; height: 80px; animation-delay: 0s; }
        .bg-bubbles li:nth-child(2) { left: 20%; width: 30px; height: 30px; animation-delay: 2s; animation-duration: 17s; }
        .bg-bubbles li:nth-child(3) { left: 70%; width: 120px; height: 120px; animation-delay: 4s; }
        .bg-bubbles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-duration: 22s; }
        .bg-bubbles li:nth-child(5) { left: 85%; width: 40px; height: 40px; animation-delay: 1s; }

        @keyframes bubble { 
            0% { transform: translateY(0) rotate(0deg); opacity: 1; } 
            100% { transform: translateY(-1200px) rotate(600deg); opacity: 0; } 
        }

        .container { position: relative; z-index: 1; width: 100%; max-width: 550px; padding: 20px; }

        .card-custom {
            border: none; border-radius: 28px; overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .card-header-danger {
            background: #991b1b; color: white; padding: 40px 20px; border-bottom: none; text-align: center;
        }

        .card-header-danger i { font-size: 3.5rem; display: block; margin-bottom: 10px; animation: pulseRed 2s infinite; }

        @keyframes pulseRed {
            0% { transform: scale(1); text-shadow: 0 0 0 rgba(239, 68, 68, 0); }
            50% { transform: scale(1.1); text-shadow: 0 0 20px rgba(239, 68, 68, 0.5); }
            100% { transform: scale(1); text-shadow: 0 0 0 rgba(239, 68, 68, 0); }
        }

        .details-box {
            background: #f1f5f9; border-radius: 20px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0;
        }

        .detail-item { margin-bottom: 8px; display: flex; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .detail-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .detail-label { font-weight: 700; color: #64748b; width: 120px; font-size: 0.85rem; text-transform: uppercase; }
        .detail-value { color: #1e293b; font-weight: 600; flex: 1; }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; 
            padding: 16px; border-radius: 16px; font-weight: 800; width: 100%; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        .btn-delete:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5); filter: brightness(1.1); }

        .btn-cancel {
            background: #fff; color: #64748b; border: 2px solid #e2e8f0; padding: 14px;
            border-radius: 16px; font-weight: 700; width: 100%; text-decoration: none; 
            display: inline-block; text-align: center; transition: 0.3s;
        }
        .btn-cancel:hover { background: #f8fafc; color: #1e293b; border-color: #cbd5e1; }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="container">
        <div class="card card-custom">
            <div class="card-header card-header-danger">
                <i class="bi bi-trash3-fill"></i>
                <h2 class="h4 mb-0 fw-bold">Hapus Notulen?</h2>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <p class="text-muted">Data yang sudah dihapus <strong>tidak dapat dikembalikan</strong>. Apakah Anda yakin ingin melanjutkan?</p>
                </div>

                <div class="details-box">
                    <div class="detail-item">
                        <span class="detail-label">Notulen</span>
                        <span class="detail-value"><?= htmlspecialchars($notulen['judul_notulen']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Rapat</span>
                        <span class="detail-value"><?= htmlspecialchars($notulen['judul_rapat']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value"><?= date('d M Y', strtotime($notulen['tanggal'])) ?></span>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="confirm_delete" value="1">
                    
                    <div class="d-grid gap-3 mt-4">
                        <button type="submit" class="btn-delete" id="submitBtn">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> YA, HAPUS PERMANEN
                        </button>
                        <a href="index.php" class="btn-cancel">
                            <i class="bi bi-x-circle me-2"></i> BATALKAN
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Efek loading saat tombol hapus ditekan
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> MENGHAPUS...';
        });
    </script>
</body>
</html>