<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI: Cek Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Hanya admin yang memiliki izin untuk menghapus notulen!';
    header("Location: index.php");
    exit;
}

// 2. KONEKSI DATABASE
$conn = getDBConnection();

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
 * Fungsi Modular: Eksekusi Hapus Notulen
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

// 3. PROSES HAPUS (Jika form disubmit via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] == '1') {
    $id = (int)$_POST['id'];

    // Ambil data dulu buat log & pesan sukses
    $data = getNotulenInfo($conn, $id);

    if ($data) {
        $judul = $data['judul_notulen'];
        
        // Eksekusi Hapus
        if (hapusNotulen($conn, $id)) {
            
            // --- KODE CCTV (LOG AKTIVITAS) ---
            $logPath = __DIR__ . '/../app/helpers/log.php';
            if (file_exists($logPath)) {
                require_once $logPath;
                if (function_exists('catat_log')) {
                    catat_log($conn, "Menghapus notulen: " . $judul);
                }
            }
            // --- SELESAI KODE CCTV ---

            $_SESSION['success'] = "Notulen <strong>'" . htmlspecialchars($judul) . "'</strong> berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan sistem saat menghapus data.";
        }
    } else {
        $_SESSION['error'] = "Data sudah tidak tersedia.";
    }

    header("Location: index.php");
    exit;
}

// 4. TAMPILAN KONFIRMASI (Jika diakses via GET)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$notulen = getNotulenInfo($conn, $id);

if (!$notulen) {
    $_SESSION['error'] = 'Notulen tidak ditemukan!';
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
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

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
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container { position: relative; z-index: 1; max-width: 500px; padding: 20px; }

        .card-custom {
            border: none; border-radius: 24px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            background: #ffffff;
        }

        .card-header-danger {
            background: #991b1b; color: white; padding: 30px; text-align: center;
        }

        .card-header-danger i { font-size: 3rem; animation: pulse 2s infinite; display: block; }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .details-box {
            background: #f8fafc; border-radius: 15px; padding: 15px; margin: 20px 0;
            border: 1px dashed #cbd5e1;
        }

        .detail-item { margin-bottom: 5px; font-size: 0.9rem; }
        .detail-label { font-weight: 700; color: #64748b; }
        .detail-value { color: #1e293b; font-weight: 600; }

        .btn-delete {
            background: #ef4444; color: white; border: none; padding: 14px;
            border-radius: 12px; font-weight: 700; width: 100%; transition: 0.3s;
        }
        .btn-delete:hover { background: #b91c1c; transform: translateY(-2px); }

        .btn-cancel {
            background: #f1f5f9; color: #475569; border: none; padding: 14px;
            border-radius: 12px; font-weight: 600; width: 100%; text-decoration: none; 
            display: inline-block; text-align: center; margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card card-custom">
        <div class="card-header card-header-danger">
            <i class="bi bi-trash3-fill mb-2"></i>
            <h4 class="fw-bold mb-0">Hapus Data?</h4>
        </div>
        <div class="card-body p-4 text-center">
            <p class="text-muted">Apakah Anda yakin ingin menghapus notulen ini secara permanen?</p>
            
            <div class="details-box text-start">
                <div class="detail-item">
                    <span class="detail-label">Judul:</span><br>
                    <span class="detail-value"><?= htmlspecialchars($notulen['judul_notulen']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Rapat:</span><br>
                    <span class="detail-value"><?= htmlspecialchars($notulen['judul_rapat']) ?></span>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="confirm" value="1">
                
                <button type="submit" class="btn-delete" id="delBtn">
                    YA, HAPUS SEKARANG
                </button>
                <a href="index.php" class="btn-cancel">BATALKAN</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('delBtn');
        btn.disabled = true;
        btn.innerHTML = 'MENGHAPUS...';
    });
</script>

</body>
</html>