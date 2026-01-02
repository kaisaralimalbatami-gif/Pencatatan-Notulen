<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// proteksi login
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$email = htmlspecialchars($_SESSION['email'] ?? 'User');
$nama = htmlspecialchars($_SESSION['nama'] ?? 'User');
$role = $_SESSION['role'] ?? 'user';

// Hitung jumlah rapat yang diikuti user
$query_rapat_user = mysqli_query($conn, 
    "SELECT COUNT(*) as total FROM rapat 
     WHERE peserta LIKE '%$email%' OR 
           peserta LIKE '%$nama%'");
$row_rapat = mysqli_fetch_assoc($query_rapat_user);
$total_rapat_ikuti = $row_rapat['total'];

// Rapat terbaru
$query_rapat_terbaru = mysqli_query($conn, 
    "SELECT * FROM rapat 
     WHERE peserta LIKE '%$email%' OR 
           peserta LIKE '%$nama%'
     ORDER BY tanggal DESC 
     LIMIT 3");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            background-attachment: fixed;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #f8fafc;
            min-height: 100vh;
        }

        /* WELCOME SECTION */
        .welcome-banner {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -10%;
            right: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        /* STAT CARDS */
        .stat-card {
            background: var(--glass-bg);
            border: none;
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            background: #ffffff;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-1 { background: #e0f2fe; color: #0ea5e9; }
        .stat-2 { background: #dcfce7; color: #22c55e; }
        .stat-3 { background: #fef3c7; color: #f59e0b; }

        .stat-val {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0;
        }

        .stat-label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* CONTENT CARDS */
        .main-card {
            background: var(--glass-bg);
            border-radius: 24px;
            border: none;
            padding: 1.5rem;
            color: #1e293b;
            height: 100%;
        }

        .meeting-item {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .meeting-item:hover {
            border-color: #0ea5e9;
            background: #f0f9ff;
        }

        .status-pill {
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* PERMISSION LIST */
        .perm-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .perm-icon {
            font-size: 1.2rem;
        }

        .perm-allowed { color: #22c55e; }
        .perm-denied { color: #ef4444; }

        /* QUICK ACTION BUTTONS */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 20px;
        }

        .btn-action {
            padding: 15px;
            border-radius: 16px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: 0.3s;
            border: 1px solid #e2e8f0;
            background: white;
            color: #1e293b;
        }

        .btn-action:hover {
            background: #0ea5e9;
            color: white;
            transform: scale(1.05);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="welcome-banner animate-up">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">Halo, <?= explode(' ', $nama)[0]; ?>! 👋</h1>
                <p class="lead opacity-75 mb-4">Senang melihatmu kembali. Hari ini ada <?= $total_rapat_ikuti; ?> agenda rapat yang tercatat atas namamu.</p>
                <div class="d-flex gap-2">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> User Terverifikasi
                    </span>
                </div>
            </div>
            <div class="col-md-4 d-none d-md-block text-end">
                <i class="bi bi-person-workspace" style="font-size: 8rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5 animate-up" style="animation-delay: 0.1s;">
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="stat-icon stat-1"><i class="bi bi-calendar-event"></i></div>
                <h3 class="stat-val"><?= $total_rapat_ikuti; ?></h3>
                <p class="stat-label">Total Rapat Diikuti</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="stat-icon stat-2"><i class="bi bi-file-earmark-text"></i></div>
                <h3 class="stat-val">0</h3>
                <p class="stat-label">Notulen Baru</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <div class="stat-icon stat-3"><i class="bi bi-clock-history"></i></div>
                <h3 class="stat-val"><?= date('H:i'); ?></h3>
                <p class="stat-label">Waktu Sistem (WIB)</p>
            </div>
        </div>
    </div>

    <div class="row g-4 animate-up" style="animation-delay: 0.2s;">
        <div class="col-lg-8">
            <div class="main-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0"><i class="bi bi-list-stars me-2 text-primary"></i>Agenda Terbaru</h5>
                    <a href="../rapat/index.php" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
                </div>

                <?php if(mysqli_num_rows($query_rapat_terbaru) > 0): ?>
                    <?php while($rapat = mysqli_fetch_assoc($query_rapat_terbaru)): ?>
                        <div class="meeting-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?= $rapat['judul']; ?></h6>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($rapat['tanggal'])); ?> 
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-geo-alt me-1"></i> <?= $rapat['tempat'] ?: 'Online'; ?>
                                </div>
                            </div>
                            <?php 
                                $s = $rapat['status'];
                                $cls = ($s == 'Selesai') ? 'bg-success-subtle text-success' : (($s == 'Berlangsung') ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary');
                            ?>
                            <span class="status-pill <?= $cls; ?>"><?= $s; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://illustrations.popsy.co/white/waiting.svg" alt="empty" style="width: 150px; opacity: 0.5;">
                        <p class="text-muted mt-3">Belum ada jadwal rapat untukmu.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="main-card shadow-sm">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i>Hak Akses</h5>
                <div class="mb-4">
                    <div class="perm-item">
                        <i class="bi bi-check-circle-fill perm-icon perm-allowed"></i>
                        <span class="small fw-semibold">Membaca Notulen</span>
                    </div>
                    <div class="perm-item">
                        <i class="bi bi-check-circle-fill perm-icon perm-allowed"></i>
                        <span class="small fw-semibold">Download PDF</span>
                    </div>
                    <div class="perm-item">
                        <i class="bi bi-x-circle-fill perm-icon perm-denied"></i>
                        <span class="small fw-semibold text-muted">Edit Data (Admin Only)</span>
                    </div>
                </div>

                <h5 class="fw-bold mb-3">Tindakan Cepat</h5>
                <div class="action-grid">
                    <a href="../rapat/index.php" class="btn-action">
                        <i class="bi bi-search d-block mb-1 fs-4"></i> Cari Rapat
                    </a>
                    <a href="../notulen/index.php" class="btn-action">
                        <i class="bi bi-file-text d-block mb-1 fs-4"></i> Notulen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>