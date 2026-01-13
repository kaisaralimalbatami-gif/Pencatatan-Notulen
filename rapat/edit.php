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
 * Fungsi Modular: Ambil Data Rapat Berdasarkan ID
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
 * Fungsi Modular: Update Data Rapat
 */
function updateRapat($conn, $id, $data) {
    try {
        $sql = "UPDATE rapat SET 
                judul = ?, 
                tanggal = ?, 
                waktu = ?, 
                tempat = ?, 
                peserta = ?, 
                status = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", 
            $data['judul'], 
            $data['tanggal'], 
            $data['waktu'], 
            $data['tempat'], 
            $data['peserta'], 
            $data['status'], 
            $id
        );

        if ($stmt->execute()) {
            // --- LOG AKTIVITAS ---
            $logPath = __DIR__ . '/../app/helpers/log.php';
            if (file_exists($logPath)) {
                require_once $logPath;
                if (function_exists('catat_log')) {
                    catat_log($conn, "Mengedit agenda rapat: " . $data['judul']);
                }
            }
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error Update Rapat: " . $e->getMessage());
        return false;
    }
}

// 3. VALIDASI ID
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

// 4. PROSES UPDATE
if (isset($_POST['update'])) {
    $dataUpdate = [
        'judul'   => $_POST['judul'],
        'tanggal' => $_POST['tanggal'],
        'waktu'   => $_POST['waktu'],
        'tempat'  => $_POST['tempat'],
        'peserta' => $_POST['peserta'],
        'status'  => $_POST['status']
    ];

    if (updateRapat($conn, $id, $dataUpdate)) {
        $_SESSION['success_message'] = "Rapat berhasil diperbarui!";
        header("Location: index.php");
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Rapat | NotulenKita</title>
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
            padding: 30px;
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
            text-decoration: none;
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
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-3px);
        }

        .info-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: inline-block;
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
                <h3><i class="bi bi-pencil-square me-2"></i> EDIT JADWAL</h3>
                <p>Perbarui informasi rapat dengan teliti</p>
            </div>

            <div class="form-body">
                <div class="text-center">
                    <div class="info-badge">ID RAPAT: #RPT-<?= str_pad($rapat['id'], 4, '0', STR_PAD_LEFT) ?></div>
                </div>

                <form method="post" id="editForm">
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-bookmark-fill"></i> Judul Agenda</label>
                        <input type="text" name="judul" class="form-control-custom" 
                               value="<?= htmlspecialchars($rapat['judul']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="bi bi-calendar3"></i> Tanggal</label>
                            <input type="date" name="tanggal" class="form-control-custom" 
                                   value="<?= $rapat['tanggal'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="bi bi-clock"></i> Waktu</label>
                            <input type="time" name="waktu" class="form-control-custom" 
                                   value="<?= $rapat['waktu'] ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-geo-alt-fill"></i> Lokasi / Link Meeting</label>
                        <input type="text" name="tempat" class="form-control-custom" 
                               value="<?= htmlspecialchars($rapat['tempat']) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-people-fill"></i> Jumlah Peserta</label>
                        <input type="text" name="peserta" class="form-control-custom" 
                               value="<?= htmlspecialchars($rapat['peserta']) ?>" placeholder="Contoh: 15 Orang">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-arrow-repeat"></i> Status Rapat</label>
                        <select name="status" class="form-control-custom">
                            <option value="Terjadwal" <?= $rapat['status']=='Terjadwal'?'selected':'' ?>>Terjadwal</option>
                            <option value="Berlangsung" <?= $rapat['status']=='Berlangsung'?'selected':'' ?>>Berlangsung</option>
                            <option value="Selesai" <?= $rapat['status']=='Selesai'?'selected':'' ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <a href="index.php" class="btn-pill btn-back">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" name="update" class="btn-pill btn-save" id="submitBtn">
                            <i class="bi bi-check-lg"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('editForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
            btn.classList.add('opacity-75');
        });
    </script>
</body>
</html>