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
 * Fungsi Modular: Mengambil semua data notulen beserta info rapatnya
 */
function ambilDaftarNotulen($conn) {
    try {
        // Cek dulu tabelnya ada atau nggak biar safe
        $cekTabel = mysqli_query($conn, "SHOW TABLES LIKE 'notulen'");
        if (mysqli_num_rows($cekTabel) == 0) return [];

        $sql = "SELECT n.id, n.judul_notulen, n.dibuat_pada, r.judul as judul_rapat, r.status, r.tanggal, r.waktu
                FROM notulen n
                JOIN rapat r ON r.id = n.rapat_id
                ORDER BY n.dibuat_pada DESC";
        
        $result = mysqli_query($conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);

    } catch (Exception $e) {
        error_log("Error Ambil Notulen: " . $e->getMessage());
        return [];
    }
}

/**
 * Fungsi Modular: Menghitung statistik notulen (Total, Mingguan, Harian)
 */
function ambilStatistikNotulen($conn) {
    $stats = [
        'total' => 0,
        'weekly' => 0,
        'today' => 0
    ];

    try {
        $cekTabel = mysqli_query($conn, "SHOW TABLES LIKE 'notulen'");
        if (mysqli_num_rows($cekTabel) == 0) return $stats;

        // A. Total Notulen
        $q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM notulen");
        if($q1) $stats['total'] = mysqli_fetch_assoc($q1)['total'];

        // B. Minggu Ini
        $q2 = mysqli_query($conn, "SELECT COUNT(*) as weekly FROM notulen WHERE YEARWEEK(dibuat_pada, 1) = YEARWEEK(CURDATE(), 1)");
        if($q2) $stats['weekly'] = mysqli_fetch_assoc($q2)['weekly'];

        // C. Hari Ini
        $q3 = mysqli_query($conn, "SELECT COUNT(*) as today FROM notulen WHERE DATE(dibuat_pada) = CURDATE()");
        if($q3) $stats['today'] = mysqli_fetch_assoc($q3)['today'];

    } catch (Exception $e) {
        error_log("Error Statistik Notulen: " . $e->getMessage());
    }

    return $stats;
}

// 2. Eksekusi Logika
$daftarNotulen = ambilDaftarNotulen($conn);
$statistik = ambilStatistikNotulen($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Notulen | NotulenKita</title>
    <meta charset="UTF-8">
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

        .container-fluid { position: relative; z-index: 1; max-width: 1300px; padding-bottom: 50px; }

        .card-custom {
            border: none; border-radius: 20px; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.98);
        }

        .card-header-custom {
            background: #1e293b; color: white; padding: 20px 25px; border-bottom: none;
        }

        .card-header-custom h2 { margin: 0; font-weight: 800; font-size: 1.4rem; letter-spacing: 0.5px; }

        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; margin-top: 20px; }
        .stat-card {
            background: white; padding: 20px; border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #0ea5e9; display: flex; align-items: center; gap: 15px;
        }

        .search-create-section {
            background: #f8fafc; padding: 20px; border-radius: 15px; margin-bottom: 25px; border: 1px solid #e2e8f0;
        }

        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-custom thead { background: #f1f5f9; }
        .table-custom thead th { padding: 15px; font-weight: 700; color: #1e293b; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #e2e8f0; }
        .table-custom tbody td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

        /* ACTION BUTTONS */
        .btn-action {
            padding: 8px 15px; border-radius: 10px; font-size: 0.75rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: 0.2s;
            border: none; cursor: pointer;
        }
        .btn-det { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .btn-det:hover { background: #0ea5e9; color: white; }
        
        .btn-pdf { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-pdf:hover { background: #ef4444; color: white; }
        
        .btn-share { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .btn-share:hover { background: #4f46e5; color: white; transform: translateY(-2px); }

        .btn-edt { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
        .btn-edt:hover { background: #f59e0b; color: white; }
        
        .btn-hps { background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6; }
        .btn-hps:hover { background: #e11d48; color: white; }

        .btn-create {
            background: #0ea5e9; 
            color: white; 
            border-radius: 12px; 
            padding: 12px;
            font-weight: 800; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
        }
        .btn-create:hover { 
            background: #0284c7; 
            color: white; 
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
        }

        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; color: white; text-transform: uppercase; }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li>
    </ul>

    <div class="container-fluid px-3 px-md-4 mt-4">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h2><i class="bi bi-journal-text me-2"></i> DATA NOTULEN</h2>
            </div>

            <div class="card-body p-4">
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="bi bi-journals fs-2 text-primary"></i>
                        <div class="stat-info"><h3><?= $statistik['total'] ?></h3><p>TOTAL NOTULEN</p></div>
                    </div>
                    <div class="stat-card" style="border-left-color: #0ea5e9;">
                        <i class="bi bi-calendar-check fs-2 text-info"></i>
                        <div class="stat-info"><h3><?= $statistik['weekly'] ?></h3><p>MINGGU INI</p></div>
                    </div>
                    <div class="stat-card" style="border-left-color: #f59e0b;">
                        <i class="bi bi-clock-history fs-2 text-warning"></i>
                        <div class="stat-info"><h3><?= $statistik['today'] ?></h3><p>HARI INI</p></div>
                    </div>
                </div>

                <div class="search-create-section">
                    <div class="row g-3">
                        <div class="<?= ($_SESSION['role'] == 'admin') ? 'col-md-8' : 'col-md-12' ?>">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari notulen...">
                            </div>
                        </div>

                        <?php if ($_SESSION['role'] == 'admin'): ?>
                        <div class="col-md-4">
                            <a href="tambah.php" class="btn-create">
                                <i class="bi bi-plus-circle-fill"></i> INPUT NOTULEN BARU
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-custom" id="notulenTable">
                        <thead>
                            <tr>
                                <th>Agenda Rapat</th>
                                <th>Judul Notulen</th>
                                <th>Status</th>
                                <th>Waktu Input</th>
                                <th class="text-center">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($daftarNotulen)): ?>
                                <?php foreach($daftarNotulen as $d): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($d['judul_rapat']) ?></div>
                                        <small class="text-muted"><i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($d['tanggal'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($d['judul_notulen']) ?></td>
                                    <td>
                                        <?php 
                                            $st = strtolower($d['status'] ?? 'terjadwal');
                                            $bg_color = ($st == 'selesai') ? '#10b981' : (($st == 'berlangsung') ? '#f59e0b' : '#0ea5e9');
                                        ?>
                                        <span class="status-badge" style="background: <?= $bg_color ?>"><?= $d['status'] ?: 'Terjadwal' ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= date('d M Y', strtotime($d['dibuat_pada'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($d['dibuat_pada'])) ?> WIB</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            <a href="detail.php?id=<?= $d['id'] ?>" class="btn-action btn-det">
                                                <i class="bi bi-eye-fill"></i> DETAIL
                                            </a>
                                            
                                            <a href="pdf.php?id=<?= $d['id'] ?>" target="_blank" class="btn-action btn-pdf">
                                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                                            </a>

                                            <?php
                                            // Persiapan Data Share
                                            $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Pencatatan-Notulen"; 
                                            $link_share = $base_url . "/app/notulen/detail.php?id=" . $d['id'];
                                            $judul_share = "Notulen: " . htmlspecialchars($d['judul_notulen']);
                                            $text_share = "Berikut notulen rapat " . htmlspecialchars($d['judul_rapat']);
                                            ?>
                                            <button onclick="shareNative('<?= $judul_share ?>', '<?= $text_share ?>', '<?= $link_share ?>')" 
                                                    class="btn-action btn-share" 
                                                    title="Bagikan Notulen">
                                                <i class="bi bi-share-fill"></i> SHARE
                                            </button>

                                            <?php if($_SESSION['role'] == 'admin'): ?>
                                                <a href="edit.php?id=<?= $d['id'] ?>" class="btn-action btn-edt">
                                                    <i class="bi bi-pencil-fill"></i> EDIT
                                                </a>
                                                <a href="konfirmasi_hapus.php?id=<?= $d['id'] ?>" class="btn-action btn-hps">
                                                    <i class="bi bi-trash3-fill"></i> HAPUS
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada data notulen.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script Pencarian
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let val = this.value.toLowerCase();
            let rows = document.querySelectorAll('#notulenTable tbody tr');
            rows.forEach(r => {
                r.style.display = r.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        });

        // Script Smart Share
        async function shareNative(judul, teks, url) {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: judul,
                        text: teks,
                        url: url
                    });
                } catch (err) {
                    console.log('User membatalkan share');
                }
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Link notulen berhasil disalin ke clipboard! 📋');
                }, function(err) {
                    alert('Gagal menyalin link.');
                });
            }
        }
    </script>
</body>
</html>