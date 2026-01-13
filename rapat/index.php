<?php
session_start();
require_once __DIR__.'/../app/config/database.php';
require_once __DIR__.'/../include/navbar.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$conn = getDBConnection();

/**
 * Fungsi Modular: Mengambil semua data rapat
 * Menggunakan Try-Catch untuk menangani error database
 */
function ambilDaftarRapat($conn) {
    try {
        // Query ambil data urut dari yang terbaru
        $sql = "SELECT * FROM rapat ORDER BY tanggal DESC";
        $result = mysqli_query($conn, $sql);
        
        // Kembalikan sebagai array associative biar gampang di-loop
        return mysqli_fetch_all($result, MYSQLI_ASSOC);

    } catch (Exception $e) {
        // Catat error ke log server
        error_log("Error Ambil Rapat: " . $e->getMessage());
        return []; // Kembalikan array kosong agar web tidak crash
    }
}

// 2. Eksekusi Logika
$daftarRapat = ambilDaftarRapat($conn);

// 3. Handle Flash Message (Pesan Sukses)
$successMessage = '';
if(isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Rapat | NotulenKita</title>
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
            position: relative;
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
        .bg-bubbles li:nth-child(2) { left: 20%; width: 80px; height: 80px; animation-delay: 2s; animation-duration: 17s; }
        .bg-bubbles li:nth-child(3) { left: 25%; animation-delay: 4s; }
        .bg-bubbles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-duration: 22s; background-color: rgba(255, 255, 255, 0.25); }
        .bg-bubbles li:nth-child(5) { left: 70%; }
        .bg-bubbles li:nth-child(6) { left: 80%; width: 120px; height: 120px; animation-delay: 3s; }
        @keyframes bubble { 0% { transform: translateY(0); } 100% { transform: translateY(-1200px) rotate(600deg); opacity: 0; } }

        .container-main { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 3rem 1rem; }

        .page-title { font-weight: 800; font-size: 2.2rem; color: #ffffff; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        
        .search-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .form-control-search { background: white; border: none; border-radius: 10px; padding: 12px 20px; }

        /* TABLE SOLID */
        .main-card {
            background: #ffffff; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden; border: none;
        }

        .table thead th {
            background: #1e293b; color: #ffffff; border: none;
            text-transform: uppercase; font-size: 0.75rem; font-weight: 700;
            letter-spacing: 1px; padding: 1.2rem;
        }

        .table tbody td { padding: 1.2rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; }

        /* DESIGN TOMBOL BERBENTUK (PILL ACTION) */
        .btn-action-pill {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .btn-view { background: #e0f2fe; color: #0ea5e9; }
        .btn-view:hover { background: #0ea5e9; color: white; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(14, 165, 233, 0.3); }

        .btn-edit { background: #fef3c7; color: #d97706; }
        .btn-edit:hover { background: #f59e0b; color: white; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(245, 158, 11, 0.3); }

        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #ef4444; color: white; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(239, 68, 68, 0.3); }

        /* LAIN-LAIN */
        .btn-add {
            background: #0ea5e9; border: none; padding: 0.8rem 1.5rem; border-radius: 12px;
            font-weight: 700; color: white; box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
        }
        .badge-status { padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.7rem; font-weight: 800; }
        .st-selesai { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .st-jadwal { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li><li></li>
    </ul>

    <?php if (!empty($successMessage)): ?>
    <div class="container mt-3" style="position: relative; z-index: 10;">
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-lg" role="alert" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4 text-success"></i>
                <div><strong>Berhasil!</strong> <?= $successMessage ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title"> Daftar Rapat</h1>
                <p class="text-white-50 m-0">Gunakan kolom cari untuk menemukan data dengan cepat</p>
            </div>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="tambah.php" class="btn btn-add">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Agenda
                </a>
            <?php endif; ?>
        </div>

        <div class="search-container shadow-sm">
            <div class="input-group">
                <span class="input-group-text bg-white border-0" style="border-radius: 12px 0 0 12px;">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="searchInput" class="form-control form-control-search" placeholder="Cari agenda atau lokasi rapat..." style="border-radius: 0 12px 12px 0;">
            </div>
        </div>

        <div class="main-card">
            <div class="table-responsive">
                <table class="table mb-0" id="rapatTable">
                    <thead>
                        <tr>
                            <th style="width: 35%">Nama Agenda Rapat</th>
                            <th>Waktu & Tempat</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($daftarRapat)): ?>
                            <?php foreach($daftarRapat as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['judul']) ?></div>
                                    <small class="text-muted">Ref: #<?= $row['id'] ?></small>
                                </td>
                                <td>
                                    <div class="small fw-bold mb-1"><i class="bi bi-calendar3 me-2 text-primary"></i><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                    <div class="small text-muted"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?= htmlspecialchars($row['tempat']) ?></div>
                                </td>
                                <td>
                                    <span class="badge-status <?= $row['status'] == 'Selesai' ? 'st-selesai' : 'st-jadwal' ?>">
                                        <i class="bi bi-dot"></i> <?= strtoupper($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button onclick='showDetail(<?= json_encode($row) ?>)' class="btn-action-pill btn-view">
                                            <i class="bi bi-eye"></i> Lihat
                                        </button>
                                        
                                        <?php if($_SESSION['role'] === 'admin'): ?>
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn-action-pill btn-edit">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="hapus.php?id=<?= $row['id'] ?>" class="btn-action-pill btn-delete" onclick="return confirm('Hapus rapat ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-5">Belum ada data rapat tersedia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-800">Detail Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-light p-4 rounded-4 mb-4">
                        <label class="text-muted small fw-bold text-uppercase mb-2 d-block">Judul Pertemuan</label>
                        <h4 id="det-judul" class="fw-bold text-dark mb-0"></h4>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small fw-bold text-uppercase mb-2 d-block">Peserta</label>
                        <div id="det-peserta" class="p-3 border rounded-4 bg-white" style="min-height: 150px; max-height: 300px; overflow-y: auto; white-space: pre-wrap; font-size: 0.95rem;"></div>
                    </div>
                </div>
                <div class="p-4 pt-0">
                    <button type="button" class="btn btn-dark w-100 py-3 rounded-4 fw-bold shadow-sm" data-bs-dismiss="modal">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#rapatTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        const m = new bootstrap.Modal(document.getElementById('modalDetail'));
        function showDetail(data) {
            document.getElementById('det-judul').innerText = data.judul;
            document.getElementById('det-peserta').innerHTML = data.peserta || 'Data peserta belum diisi.';
            m.show();
        }
    </script>
</body>
</html>