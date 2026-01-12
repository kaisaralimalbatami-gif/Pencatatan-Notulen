<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();

session_start();
require_once __DIR__ . '/../app/config/database.php';

// PROTEKSI ADMIN ONLY
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard/user.php");
    exit;
}

// 1. PANGGIL KONEKSI (SOLUSI ERROR)
$conn = getDBConnection();

/**
 * Fungsi Modular: Ambil Statistik User
 */
function ambilStatistikUser($conn) {
    $stats = ['total' => 0, 'admin' => 0, 'biasa' => 0];
    
    try {
        // Total User
        $q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
        if($q1) $stats['total'] = mysqli_fetch_assoc($q1)['total'];

        // Total Admin
        $q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
        if($q2) $stats['admin'] = mysqli_fetch_assoc($q2)['total'];

        // Hitung user biasa
        $stats['biasa'] = $stats['total'] - $stats['admin'];

    } catch (Exception $e) {
        error_log("Error Stat User: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Fungsi Modular: Ambil Semua Data User
 */
function ambilDaftarUser($conn) {
    try {
        $q = mysqli_query($conn, "SELECT id, email, role FROM users ORDER BY role ASC, email ASC");
        return mysqli_fetch_all($q, MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error Daftar User: " . $e->getMessage());
        return [];
    }
}

// 2. EKSEKUSI LOGIKA
$statistik = ambilStatistikUser($conn);
$daftarUser = ambilDaftarUser($conn);

// INCLUDE NAVBAR
require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User | NotulenKita</title>
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

        .main-container {
            max-width: 1200px;
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
            border-left: 8px solid #6366f1; /* Indigo accent for User Management */
        }

        /* Mini Stats */
        .stat-badge {
            background: white;
            padding: 20px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s ease;
            height: 100%;
        }
        .stat-badge:hover { transform: translateY(-5px); }
        .icon-circle {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: white;
        }

        /* Table Styling */
        .custom-table thead th {
            background: #f8fafc;
            border: none;
            padding: 15px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }
        .custom-table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        /* Role Badge */
        .badge-pill {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .bg-admin { background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
        .bg-user { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

        /* Search Bar */
        .search-group {
            position: relative;
            max-width: 400px;
        }
        .search-group i {
            position: absolute;
            left: 15px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-input {
            padding-left: 45px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            height: 48px;
            background: #fff;
        }
        .search-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Buttons Action (With Text) */
        .btn-add {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white; border: none; padding: 12px 25px;
            border-radius: 12px; font-weight: 600; transition: 0.3s;
            text-decoration: none; display: inline-block;
        }
        .btn-add:hover { color: white; transform: scale(1.03); box-shadow: 0 8px 15px rgba(99, 102, 241, 0.3); }

        .btn-action {
            padding: 8px 16px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-edit { background: #fef3c7; color: #d97706; }
        .btn-edit:hover { background: #d97706; color: white; transform: translateY(-2px); }
        
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #dc2626; color: white; transform: translateY(-2px); }

        .btn-disabled { background: #f1f5f9; color: #cbd5e1; cursor: not-allowed; border: none; }

        .current-tag {
            font-size: 0.65rem;
            background: #6366f1;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            margin-left: 8px;
        }
    </style>
</head>
<body>

<div class="main-container">

    <div class="glass-card header-section">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="fw-bold mb-1 text-dark">Manajemen User</h1>
                <p class="text-muted mb-0">Kelola dan pantau seluruh akun pengguna dalam sistem.</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <a href="user_tambah.php" class="btn-add">
                    <i class="bi bi-person-plus-fill me-2"></i> Tambah Akun
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-badge">
                <div class="icon-circle" style="background: #6366f1;"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="fw-bold text-dark h4 mb-0"><?= $statistik['total'] ?></div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Total User</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-badge">
                <div class="icon-circle" style="background: #f59e0b;"><i class="bi bi-shield-lock-fill"></i></div>
                <div>
                    <div class="fw-bold text-dark h4 mb-0"><?= $statistik['admin'] ?></div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Admin</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-badge">
                <div class="icon-circle" style="background: #10b981;"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="fw-bold text-dark h4 mb-0"><?= $statistik['biasa'] ?></div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">User Biasa</small>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3" style="background: rgba(248, 250, 252, 0.5);">
            <div class="search-group">
                <i class="bi bi-search"></i>
                <input type="text" id="liveSearch" class="form-control search-input" placeholder="Cari email atau role...">
            </div>
            <div class="text-muted small fw-bold">
                <i class="bi bi-filter-right me-1"></i> AKTIF: <?= count($daftarUser) ?> ENTRI
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table" id="userTable">
                <thead>
                    <tr>
                        <th class="text-center" width="80">No</th>
                        <th>Info Pengguna</th>
                        <th width="180">Status Role</th>
                        <th class="text-center" width="250">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    // Menggunakan FOREACH untuk looping data array dari fungsi
                    if (!empty($daftarUser)):
                        foreach($daftarUser as $d): 
                            $is_me = ($d['id'] == $_SESSION['id']);
                    ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 45px; height: 45px; background: #eef2ff; color: #6366f1; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-envelope-fill" style="font-size: 1.2rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($d['email']) ?>
                                        <?php if($is_me): ?><span class="current-tag">Saya</span><?php endif; ?>
                                    </div>
                                    <small class="text-muted">UID-<?= str_pad($d['id'], 4, '0', STR_PAD_LEFT) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-pill <?= $d['role'] == 'admin' ? 'bg-admin' : 'bg-user' ?>">
                                <i class="bi <?= $d['role'] == 'admin' ? 'bi-shield-check' : 'bi-person' ?>"></i>
                                <?= strtoupper($d['role']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="user_edit.php?id=<?= $d['id'] ?>" class="btn-action btn-edit">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Edit</span>
                                </a>

                                <?php if(!$is_me): ?>
                                    <a href="user_hapus.php?id=<?= $d['id'] ?>" class="btn-action btn-delete" 
                                       onclick="return confirm('Hapus user ini?')">
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </a>
                                <?php else: ?>
                                    <button class="btn-action btn-disabled" title="Tidak bisa hapus diri sendiri" disabled>
                                        <i class="bi bi-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5">Belum ada data user.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center py-3">
        <p class="text-white-50 small">&copy; <?= date('Y') ?> NotulenKita Team • Secure Admin Panel</p>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Live Search Logic
    document.getElementById('liveSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#userTable tbody tr');
        
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>

</body>
</html>