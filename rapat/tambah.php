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
 * Fungsi Modular: Tambah Rapat Baru
 */
function tambahRapat($conn, $data) {
    try {
        $sql = "INSERT INTO rapat (judul, tanggal, waktu, tempat, peserta, status) 
                VALUES (?, ?, ?, ?, ?, 'Terjadwal')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $data['judul'], 
            $data['tanggal'], 
            $data['waktu'], 
            $data['tempat'], 
            $data['peserta']
        );

        if ($stmt->execute()) {
            // --- LOG AKTIVITAS ---
            $logPath = __DIR__ . '/../app/helpers/log.php';
            if (file_exists($logPath)) {
                require_once $logPath;
                if (function_exists('catat_log')) {
                    catat_log($conn, "Menjadwalkan rapat baru: " . $data['judul']);
                }
            }
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error Tambah Rapat: " . $e->getMessage());
        return false;
    }
}

// 3. PROSES SIMPAN
$error = '';
if(isset($_POST['simpan'])){
    $judul   = trim($_POST['judul'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $waktu   = $_POST['waktu'] ?? '';
    $tempat  = trim($_POST['tempat'] ?? '');
    $peserta = trim($_POST['peserta'] ?? '');
    
    if(empty($judul) || empty($tanggal) || empty($waktu)) {
        $error = "Judul, tanggal, dan waktu wajib diisi!";
    } else {
        $dataInput = [
            'judul'   => $judul,
            'tanggal' => $tanggal,
            'waktu'   => $waktu,
            'tempat'  => $tempat,
            'peserta' => $peserta
        ];

        if(tambahRapat($conn, $dataInput)) {
            $_SESSION['success_message'] = "Rapat '$judul' berhasil dijadwalkan!";
            header("Location: index.php");
            exit();
        } else {
            $error = "Gagal menyimpan data rapat.";
        }
    }
}

require_once __DIR__ . '/../include/navbar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Rapat | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            background: linear-gradient(-45deg, #0ea5e9, #0284c7, #1e293b, #0f172a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bg-bubbles {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; overflow: hidden; pointer-events: none;
        }
        .bg-bubbles li {
            position: absolute; list-style: none; display: block;
            width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.1);
            bottom: -160px; animation: bubble 25s infinite linear;
        }
        .bg-bubbles li:nth-child(1) { left: 10%; }
        .bg-bubbles li:nth-child(2) { left: 20%; width: 80px; height: 80px; animation-delay: 2s; }
        .bg-bubbles li:nth-child(3) { left: 70%; width: 120px; height: 120px; animation-delay: 4s; }
        @keyframes bubble { 0% { transform: translateY(0); } 100% { transform: translateY(-1200px) rotate(600deg); opacity: 0; } }

        .container-main {
            position: relative;
            z-index: 1;
            max-width: 850px;
            margin: auto;
            padding: 40px 20px;
        }

        .form-card {
            background: #ffffff;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: none;
        }

        .form-header {
            background: #1e293b;
            padding: 25px;
            color: white;
            text-align: center;
        }

        .form-header h3 { font-weight: 800; margin: 0; letter-spacing: 1px; }
        .form-header p { opacity: 0.7; margin: 5px 0 0; font-size: 0.9rem; }

        .form-body { padding: 40px; }

        .form-label {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-label i { color: #0ea5e9; }

        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
            width: 100%;
        }

        .form-control-custom:focus {
            border-color: #0ea5e9;
            background: white;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            outline: none;
        }

        .btn-pill {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
        }

        .btn-save {
            background: #0ea5e9;
            color: white;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }
        .btn-save:hover {
            background: #0284c7;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(14, 165, 233, 0.4);
            color: white;
        }

        .btn-back {
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-3px);
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
            padding: 15px 20px;
        }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="container-main">
        <div class="form-card">
            <div class="form-header">
                <h3><i class="bi bi-calendar-plus-fill me-2"></i> BUAT JADWAL</h3>
                <p>Silakan lengkapi detail pertemuan di bawah ini</p>
            </div>

            <div class="form-body">
                <?php if($error): ?>
                    <div class="alert alert-custom mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="rapatForm">
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-pencil-square"></i> Judul Agenda</label>
                        <input type="text" name="judul" class="form-control-custom" 
                               placeholder="Masukkan judul rapat..." required
                               value="<?= isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : '' ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="bi bi-calendar3"></i> Tanggal</label>
                            <input type="date" name="tanggal" id="tanggalInput" class="form-control-custom" required
                                   value="<?= isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="bi bi-clock"></i> Waktu</label>
                            <input type="time" name="waktu" id="waktuInput" class="form-control-custom" required
                                   value="<?= isset($_POST['waktu']) ? htmlspecialchars($_POST['waktu']) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Lokasi / Link Meeting</label>
                        <input type="text" name="tempat" class="form-control-custom" 
                               placeholder="Gedung A, Ruang 302, atau Zoom Link"
                               value="<?= isset($_POST['tempat']) ? htmlspecialchars($_POST['tempat']) : '' ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-people-fill"></i> Peserta / Catatan Awal</label>
                        <textarea name="peserta" class="form-control-custom" rows="4" 
                                  placeholder="Daftar peserta atau agenda ringkas..."><?= isset($_POST['peserta']) ? htmlspecialchars($_POST['peserta']) : '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <a href="index.php" class="btn-pill btn-back">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" name="simpan" class="btn-pill btn-save" id="submitBtn">
                            <i class="bi bi-check-lg"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tanggalInput = document.getElementById('tanggalInput');
        const waktuInput = document.getElementById('waktuInput');
        
        // Set min date ke hari ini
        const today = new Date().toISOString().split('T')[0];
        if (!tanggalInput.value) tanggalInput.value = today;
        tanggalInput.min = today;

        // Default waktu
        if (!waktuInput.value) {
            const now = new Date();
            now.setHours(now.getHours() + 1);
            waktuInput.value = now.getHours().toString().padStart(2, '0') + ":00";
        }

        // Animasi loading saat submit
        document.getElementById('rapatForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
            btn.classList.add('opacity-75');
        });
    });
    </script>
</body>
</html>
<?php 
// 4. TUTUP BUFFERING
ob_end_flush();
?>