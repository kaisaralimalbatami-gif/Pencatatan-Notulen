<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();

session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 2. KONEKSI DATABASE
$conn = getDBConnection();

/**
 * Fungsi Modular: Ambil Data Rapat
 */
function getRapatById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM rapat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Fungsi Modular: Hapus Rapat
 */
function deleteRapat($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM rapat WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Log Aktivitas
            $logPath = __DIR__ . '/../app/helpers/log.php';
            if (file_exists($logPath)) {
                require_once $logPath;
                if (function_exists('catat_log')) {
                    catat_log($conn, "Menghapus rapat ID: " . $id);
                }
            }
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error Hapus Rapat: " . $e->getMessage());
        return false;
    }
}

// 3. PROSES EKSEKUSI HAPUS (VIA POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $id_to_delete = (int)$_POST['id'];
    
    if (deleteRapat($conn, $id_to_delete)) {
        $_SESSION['success_message'] = "Rapat berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus rapat.";
    }
    
    header("Location: index.php");
    exit;
}

// 4. TAMPILAN KONFIRMASI (VIA GET)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$rapat = getRapatById($conn, $id);

if (!$rapat) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Navbar di-include di sini biar rapi
// (Opsional, kalau desainnya full page warning biasanya gak pake navbar, tapi gw ikutin style lu)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Rapat | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            background: linear-gradient(-45deg, #ef4444, #dc2626, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .nav-wrapper { position: relative; z-index: 1000; margin-bottom: 1rem; }

        .content-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            min-height: 80vh;
        }

        .delete-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border: none;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .delete-header {
            background: #fee2e2;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #fecaca;
        }

        .delete-header i { 
            font-size: 3.5rem; 
            color: #ef4444;
            display: block;
            margin-bottom: 10px;
        }

        .delete-header h4 { 
            font-weight: 800; 
            color: #991b1b; 
            margin: 0;
        }

        .delete-body { padding: 30px; }

        .rapat-preview {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .preview-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .preview-value {
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
            width: 100%;
            border: none;
            cursor: pointer;
        }

        .btn-confirm {
            background: #ef4444;
            color: white;
        }

        .btn-confirm:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            text-align: center;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
    </style>
</head>
<body>

<div class="nav-wrapper">
    <?php require_once __DIR__ . '/../include/navbar.php'; ?>
</div>

<div class="content-wrapper">
    <div class="delete-card">
        <div class="delete-header">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <h4>Hapus Rapat?</h4>
        </div>

        <div class="delete-body">
            <p class="text-center text-muted mb-4">
                Apakah Anda yakin ingin menghapus agenda ini? Data yang sudah dihapus <b>tidak dapat dikembalikan</b>.
            </p>

            <div class="rapat-preview">
                <div class="preview-label">Judul Agenda</div>
                <div class="preview-value"><?= htmlspecialchars($rapat['judul']) ?></div>

                <div class="row">
                    <div class="col-6">
                        <div class="preview-label">Tanggal</div>
                        <div class="preview-value"><?= date('d M Y', strtotime($rapat['tanggal'])) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="preview-label">Status</div>
                        <div class="preview-value text-primary"><?= htmlspecialchars($rapat['status']) ?></div>
                    </div>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="confirm_delete" value="1">
                
                <div class="row g-3">
                    <div class="col-12">
                        <button type="submit" class="btn-action btn-confirm" id="btnDel">
                            <i class="bi bi-trash3-fill"></i> Ya, Hapus Sekarang
                        </button>
                    </div>
                    <div class="col-12">
                        <a href="index.php" class="btn-action btn-cancel">
                            Batal, Simpan Data
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    document.getElementById('btnDel').addEventListener('click', function(e) {
        // Konfirmasi di sisi client juga biar double check
        if(!confirm('Konfirmasi terakhir: Yakin hapus?')) {
            e.preventDefault();
        } else {
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghapus...';
            this.style.pointerEvents = 'none';
            this.closest('form').submit();
        }
    });
</script>

</body>
</html>