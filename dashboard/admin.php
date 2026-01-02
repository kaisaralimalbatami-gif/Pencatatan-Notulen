<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();
session_start();

// Pastikan path ini benar
require_once __DIR__ . '/../app/config/database.php';

// proteksi login hanya untuk admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

/* ================== STATISTIK ================== */

// TOTAL RAPAT
$qRapatAll = mysqli_query($conn, "SELECT COUNT(*) AS total FROM rapat");
$totalRapatAll = $qRapatAll ? mysqli_fetch_assoc($qRapatAll)['total'] : 0;

// TOTAL USER
$qUserAll = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$totalUserAll = $qUserAll ? mysqli_fetch_assoc($qUserAll)['total'] : 0;

// TOTAL RAPAT BULAN INI
$bulan = date('m');
$tahun = date('Y');
$qRapat = mysqli_query($conn, "SELECT COUNT(*) AS total FROM rapat WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'");
$totalRapat = $qRapat ? mysqli_fetch_assoc($qRapat)['total'] : 0;

// TOTAL NOTULEN (Cek dulu tabelnya ada gak)
$totalNotulen = 0;
$qCheckNotulen = mysqli_query($conn, "SHOW TABLES LIKE 'notulen'");
if ($qCheckNotulen && mysqli_num_rows($qCheckNotulen) > 0) {
    $qNotulen = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notulen");
    $totalNotulen = $qNotulen ? mysqli_fetch_assoc($qNotulen)['total'] : 0;
}

// USER AKTIF HARI INI (Menggunakan tabel 'aktivitas')
// Kalau tabel belum ada, kita kasih nilai 0 biar ga fatal error
$userAktif = 0;
$cekTabelLog = mysqli_query($conn, "SHOW TABLES LIKE 'aktivitas'");
if(mysqli_num_rows($cekTabelLog) > 0){
    $qUserAktif = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) AS total FROM aktivitas WHERE DATE(created_at)=CURDATE()");
    if($qUserAktif){
        $userAktif = mysqli_fetch_assoc($qUserAktif)['total'];
    }
}

// SEKARANG INCLUDE NAVBAR
require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | NotulenKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95); /* Sedikit lebih solid biar teks jelas */
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .header-section {
            padding: 30px;
            margin-bottom: 30px;
            border-left: 8px solid #0ea5e9;
        }

        .stat-card {
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            border-bottom: 5px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            background: #fff;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 15px;
            box-shadow: 0 8px 15px rgba(14, 165, 233, 0.3);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        /* Border colors for cards */
        .card-meetings { border-bottom-color: #0ea5e9; }
        .card-notulen { border-bottom-color: #10b981; }
        .card-active { border-bottom-color: #f59e0b; }
        .card-total { border-bottom-color: #6366f1; }

        .activity-section {
            margin-top: 30px;
            overflow: hidden;
        }

        .section-header {
            background: rgba(248, 250, 252, 0.8);
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-content {
            padding: 0; /* Ubah padding jadi 0 buat tabel */
        }

        .refresh-btn {
            background: white;
            color: #0ea5e9;
            border: 2px solid #0ea5e9;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .refresh-btn:hover {
            background: #0ea5e9;
            color: white;
        }

        .date-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .footer-note {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

<div class="dashboard-container">

    <div class="glass-card header-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-1 text-dark">Dashboard Admin</h1>
                <p class="text-muted mb-3">Ringkasan aktivitas sistem dan statistik NotulenKita.</p>
                <span class="date-badge">
                    <i class="bi bi-calendar3 me-2"></i><?= date('d F Y') ?>
                </span>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="refresh-btn" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="glass-card stat-card card-meetings">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-number"><?= $totalRapat ?></div>
                <div class="stat-label">Rapat Bulan Ini</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card card-notulen">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"><i class="bi bi-file-earmark-text"></i></div>
                <div class="stat-number"><?= $totalNotulen ?></div>
                <div class="stat-label">Total Notulen</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card card-active">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"><i class="bi bi-person-check"></i></div>
                <div class="stat-number"><?= $userAktif ?></div>
                <div class="stat-label">User Aktif Hari Ini</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card card-total">
                <div class="stat-icon" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);"><i class="bi bi-people"></i></div>
                <div class="stat-number"><?= $totalUserAll ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
        </div>
    </div>

    <div class="glass-card activity-section">
        <div class="section-header">
            <span><i class="bi bi-activity me-2 text-primary"></i> Aktivitas Terbaru</span>
            <span class="badge bg-primary opacity-75">Update: <?= date('H:i') ?> WIB</span>
        </div>
        <div class="activity-content">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="20%" class="ps-4">Waktu</th>
                            <th width="25%">User</th>
                            <th class="pe-4">Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Cek tabel aktivitas ada apa nggak
                        if(mysqli_num_rows($cekTabelLog) > 0){
                            $qLog = mysqli_query($conn, "SELECT * FROM aktivitas ORDER BY created_at DESC LIMIT 5");
                            
                            if (mysqli_num_rows($qLog) > 0) {
                                while($row = mysqli_fetch_assoc($qLog)):
                        ?>
                        <tr>
                            <td class="ps-4 text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('d M Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <i class="bi bi-person-circle me-1 text-secondary"></i> 
                                <?= htmlspecialchars($row['nama_user']); ?>
                            </td>
                            <td class="pe-4">
                                <?= htmlspecialchars($row['aksi']); ?>
                            </td>
                        </tr>
                        <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-5 text-muted">Belum ada aktivitas tercatat</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center py-5 text-danger">Tabel "aktivitas" belum dibuat di database!</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer-note">
        <p>&copy; <?= date('Y') ?> NotulenKita Team • Secure Admin Panel</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>