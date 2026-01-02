<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID notulen tidak valid!';
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data notulen
$query = mysqli_query($conn, "
    SELECT n.*, r.judul as judul_rapat, r.tanggal, r.waktu, r.tempat, r.peserta, r.status
    FROM notulen n 
    JOIN rapat r ON r.id = n.rapat_id 
    WHERE n.id = '$id'
");

if (mysqli_num_rows($query) == 0) {
    $_SESSION['error'] = 'Notulen tidak ditemukan!';
    header("Location: index.php");
    exit;
}

$notulen = mysqli_fetch_assoc($query);
$tanggal_rapat = date('d F Y', strtotime($notulen['tanggal']));
$waktu_rapat = date('H:i', strtotime($notulen['waktu']));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Notulen | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* MENYAMAKAN DENGAN DASHBOARD ADMIN */
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

        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .header-section {
            padding: 30px;
            border-left: 8px solid #0ea5e9;
        }

        .section-header {
            background: rgba(248, 250, 252, 0.8);
            padding: 15px 25px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-body {
            padding: 25px;
        }

        /* Styling Detail Item */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #0f172a;
        }

        /* Text Content Styling */
        .notulen-text {
            white-space: pre-wrap;
            line-height: 1.7;
            color: #334155;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #0ea5e9;
        }

        .date-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Action Buttons */
        .btn-action {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @media print {
            body { background: white !important; animation: none !important; }
            .btn-action, .no-print { display: none !important; }
            .glass-card { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="glass-card header-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-1 text-dark">Detail Notulen</h1>
                <p class="text-muted mb-3">Arsip resmi hasil keputusan rapat.</p>
                <span class="date-badge">
                    <i class="bi bi-calendar3 me-2"></i><?= $tanggal_rapat ?>
                </span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0 no-print">
                <a href="index.php" class="btn btn-outline-secondary btn-action bg-white">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="section-header">
            <i class="bi bi-info-circle text-primary"></i> Informasi Rapat
        </div>
        <div class="content-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Judul Rapat</div>
                    <div class="info-value"><?= htmlspecialchars($notulen['judul_rapat']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Lokasi / Tempat</div>
                    <div class="info-value"><?= htmlspecialchars($notulen['tempat']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Waktu</div>
                    <div class="info-value"><?= $waktu_rapat ?> WIB</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="badge bg-primary"><?= strtoupper($notulen['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="section-header">
            <i class="bi bi-journal-text text-success"></i> Isi & Pembahasan
        </div>
        <div class="content-body">
            <label class="info-label">Agenda</label>
            <div class="notulen-text mb-4"><?= htmlspecialchars($notulen['agenda']) ?></div>

            <label class="info-label">Pembahasan</label>
            <div class="notulen-text mb-4"><?= htmlspecialchars($notulen['pembahasan']) ?></div>
            
            <label class="info-label">Keputusan</label>
            <div class="notulen-text" style="border-left-color: #10b981; background: #f0fdf4;"><?= htmlspecialchars($notulen['keputusan']) ?></div>
        </div>
    </div>

    <?php if($notulen['tindak_lanjut']): ?>
    <div class="glass-card">
        <div class="section-header">
            <i class="bi bi-arrow-repeat text-warning"></i> Tindak Lanjut & PIC
        </div>
        <div class="content-body">
            <div class="notulen-text"><?= htmlspecialchars($notulen['tindak_lanjut']) ?></div>
            <div class="mt-3 p-3 bg-light rounded-3 border">
                <i class="bi bi-person-badge me-2 text-primary"></i>
                <strong>Penanggung Jawab:</strong> <?= htmlspecialchars($notulen['penanggung_jawab']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2 justify-content-center no-print mb-5">
        <button onclick="window.print()" class="btn btn-primary btn-action">
            <i class="bi bi-printer"></i> Cetak Dokumen
        </button>
        <a href="pdf.php?id=<?= $id ?>" class="btn btn-danger btn-action">
            <i class="bi bi-file-pdf"></i> Download PDF
        </a>
        <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="edit.php?id=<?= $id ?>" class="btn btn-warning btn-action text-white">
            <i class="bi bi-pencil-square"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>