<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// Validasi session dan role
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Cek apakah data ada
$check_query = mysqli_query($conn, "SELECT * FROM rapat WHERE id='$id'");
if (mysqli_num_rows($check_query) == 0) {
    header("Location: index.php?error=Data tidak ditemukan");
    exit;
}

$rapat = mysqli_fetch_assoc($check_query);

// Jika sudah dikonfirmasi hapus
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    mysqli_query($conn, "DELETE FROM rapat WHERE id='$id'");
    header("Location: index.php?success=Rapat berhasil dihapus");
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
        }

        .btn-confirm {
            background: #ef4444;
            color: white;
            border: none;
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
                        <div class="preview-value text-primary"><?= $rapat['status'] ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <a href="hapus.php?id=<?= $id ?>&confirm=yes" class="btn-action btn-confirm" id="btnDel">
                        <i class="bi bi-trash3-fill"></i> Ya, Hapus Sekarang
                    </a>
                </div>
                <div class="col-12">
                    <a href="index.php" class="btn-action btn-cancel">
                        Batal, Simpan Data
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnDel').addEventListener('click', function(e) {
        if(!confirm('Konfirmasi terakhir: Yakin hapus?')) {
            e.preventDefault();
        } else {
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghapus...';
            this.style.pointerEvents = 'none';
        }
    });
</script>

</body>
</html>