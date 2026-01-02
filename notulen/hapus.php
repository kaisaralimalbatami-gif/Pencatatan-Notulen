<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI: Cek Login & Role Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Hanya admin yang memiliki izin untuk menghapus notulen!';
    header("Location: index.php");
    exit;
}

// 2. PROSES HAPUS (Jika form disubmit via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] == '1') {
    $id = (int)$_POST['id'];

    // Cek data sebelum hapus untuk keperluan Log & Feedback
    $check = mysqli_query($conn, "
        SELECT n.judul_notulen, r.judul as judul_rapat 
        FROM notulen n 
        LEFT JOIN rapat r ON r.id = n.rapat_id 
        WHERE n.id = '$id'
    ");

    if (mysqli_num_rows($check) > 0) {
        $data = mysqli_fetch_assoc($check);
        $judul = $data['judul_notulen'];
        
        // Eksekusi Hapus
        $result = mysqli_query($conn, "DELETE FROM notulen WHERE id = '$id'");

        if ($result) {
            // --- MULAI KODE CCTV (LOG AKTIVITAS) ---
            require_once __DIR__ . '/../helpers/log.php';
            
            // Catat log dengan helper standar kita
            $log_pesan = "Menghapus notulen: " . $judul;
            catat_log($conn, $log_pesan);
            // --- SELESAI KODE CCTV ---

            $_SESSION['success'] = "Notulen <strong>'$judul'</strong> berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Data tidak ditemukan!";
    }

    header("Location: index.php");
    exit;
}

// 3. TAMPILAN KONFIRMASI (Jika diakses via GET)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$query = mysqli_query($conn, "
    SELECT n.*, r.judul as judul_rapat, r.tanggal 
    FROM notulen n 
    LEFT JOIN rapat r ON r.id = n.rapat_id 
    WHERE n.id = '$id'
");

if (mysqli_num_rows($query) == 0) {
    $_SESSION['error'] = 'Notulen tidak ditemukan!';
    header("Location: index.php");
    exit;
}

$notulen = mysqli_fetch_assoc($query);
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