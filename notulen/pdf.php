<?php
ob_start();
session_start();
require_once __DIR__ . '/../app/config/database.php';

// 1. PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 2. KONEKSI DATABASE
$conn = getDBConnection();

/**
 * Fungsi Modular: Ambil Data Notulen Lengkap untuk Cetak
 */
function getDataCetakNotulen($conn, $id) {
    try {
        $sql = "SELECT 
                    n.*,
                    COALESCE(r.judul, 'Rapat tidak ditemukan') as judul_rapat,
                    r.tanggal, r.waktu, r.tempat, r.peserta, r.status,
                    COALESCE(u.nama, u.email, 'Administrator') as pembuat_notulen
                FROM notulen n
                LEFT JOIN rapat r ON r.id = n.rapat_id
                LEFT JOIN users u ON u.id = n.created_by
                WHERE n.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

// 3. VALIDASI ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    header("Location: index.php");
    exit;
}

// 4. AMBIL DATA
$data = getDataCetakNotulen($conn, $id);

if (!$data) {
    echo "<script>alert('Data notulen tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// --- LOG AKTIVITAS ---
$logPath = __DIR__ . '/../app/helpers/log.php';
if (file_exists($logPath)) {
    require_once $logPath;
    if (function_exists('catat_log')) {
        catat_log($conn, "Mengunduh PDF notulen: " . $data['judul_rapat']);
    }
}
// ---------------------

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notulen_<?= str_replace(' ', '_', $data['judul_rapat']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #1e3a8a;
            --secondary-gray: #64748b;
            --border-light: #e2e8f0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            padding: 40px 0;
            color: #1e293b;
        }

        /* --- PRINT AREA --- */
        .print-area {
            background: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto;
            padding: 25mm;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
            box-sizing: border-box;
        }

        /* --- HEADER / KOP SURAT --- */
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 4px solid var(--primary-dark);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo-placeholder {
            width: 70px;
            height: 70px;
            background: var(--primary-dark);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-right: 20px;
        }

        .company-info h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--primary-dark);
        }

        .company-info p {
            margin: 2px 0 0;
            color: var(--secondary-gray);
            font-size: 13px;
        }

        /* --- JUDUL DOKUMEN --- */
        .doc-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .doc-title h2 {
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 800;
            text-decoration: underline;
        }

        .doc-title p {
            color: var(--secondary-gray);
            font-size: 14px;
        }

        /* --- GRID INFO RAPAT --- */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--secondary-gray);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
        }

        /* --- CONTENT SECTIONS --- */
        .content-section {
            margin-bottom: 30px;
        }

        .section-header {
            background: #f1f5f9;
            padding: 8px 15px;
            border-left: 5px solid var(--primary-dark);
            font-weight: 800;
            font-size: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .text-block {
            padding: 0 15px;
            font-size: 14px;
            line-height: 1.8;
            text-align: justify;
            white-space: pre-line;
        }

        .participant-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 15px;
        }

        .tag {
            background: white;
            border: 1px solid var(--border-light);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
        }

        /* --- SIGNATURE --- */
        .signature-wrapper {
            margin-top: 60px;
            display: flex;
            justify-content: flex-end;
        }

        .sig-box {
            text-align: center;
            width: 250px;
        }

        .sig-space {
            height: 80px;
        }

        .sig-name {
            font-weight: 800;
            text-decoration: underline;
            margin-bottom: 2px;
        }

        /* --- BUTTONS --- */
        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }

        .btn-custom {
            padding: 12px 25px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-print { background: #1e293b; color: white; }
        .btn-back { background: white; color: #1e293b; }
        .btn-custom:hover { transform: translateY(-3px); }

        /* --- PRINT OVERRIDES --- */
        @media print {
            body { background: white; padding: 0; }
            .print-area { box-shadow: none; margin: 0; width: 100%; padding: 15mm; }
            .floating-action, .instructions { display: none !important; }
            .info-grid { background: transparent !important; border: 1px solid #000; }
            .section-header { background: #eee !important; -webkit-print-color-adjust: exact; border-bottom: 1px solid #000; }
            @page { size: A4; margin: 10mm; }
        }

        .instructions {
            max-width: 210mm;
            margin: 0 auto 20px;
            background: #dcfce7;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="instructions">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Tips Formal:</strong> Gunakan opsi <b>"Save as PDF"</b> pada printer destination. Pastikan <b>"Background Graphics"</b> dicentang di pengaturan print agar warna header muncul.
    </div>

    <div class="floating-action">
        <a href="index.php" class="btn-custom btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="btn-custom btn-print">
            <i class="bi bi-printer"></i> Cetak Dokumen
        </button>
    </div>

    <div class="print-area">
        <div class="kop-surat">
            <div class="logo-placeholder">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="company-info">
                <h1>NOTULENKITA DIGITAL</h1>
                <p>Gedung Pusat Dokumentasi Lt. 4, Jakarta Pusat</p>
                <p>Email: support@notulenkita.id | Website: www.notulenkita.id</p>
            </div>
        </div>

        <div class="doc-title">
            <h2>BERITA ACARA & NOTULEN RAPAT</h2>
            <p>Nomor Dokumen: NOT/<?= date('Y/m', strtotime($data['tanggal'])) ?>/00<?= $data['id'] ?></p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Judul Rapat</span>
                <span class="info-value"><?= htmlspecialchars($data['judul_rapat']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tempat & Lokasi</span>
                <span class="info-value"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($data['tempat']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Hari / Tanggal</span>
                <span class="info-value"><i class="bi bi-calendar3 me-1"></i> <?= date('l, d F Y', strtotime($data['tanggal'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Waktu Rapat</span>
                <span class="info-value"><i class="bi bi-clock me-1"></i> <?= $data['waktu'] ?> WIB s/d Selesai</span>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header"><i class="bi bi-people"></i> Daftar Hadir Peserta</div>
            <div class="participant-tags">
                <?php 
                $list = preg_split('/[\n,]+/', $data['peserta']);
                foreach($list as $p): if(trim($p)):
                ?>
                    <span class="tag"><?= htmlspecialchars(trim($p)) ?></span>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header"><i class="bi bi-list-check"></i> Agenda Rapat</div>
            <div class="text-block"><?= !empty($data['agenda']) ? $data['agenda'] : 'Belum ada agenda yang dicatat.' ?></div>
        </div>

        <div class="content-section">
            <div class="section-header"><i class="bi bi-chat-left-dots"></i> Ringkasan Pembahasan</div>
            <div class="text-block"><?= !empty($data['pembahasan']) ? $data['pembahasan'] : '-' ?></div>
        </div>

        <div class="content-section">
            <div class="section-header"><i class="bi bi-check2-circle"></i> Hasil Keputusan / Mufakat</div>
            <div class="text-block" style="background: #fdfaf0; padding: 15px; border-radius: 8px; border: 1px solid #fae8ff;">
                <strong>Kesimpulan:</strong><br>
                <?= !empty($data['keputusan']) ? $data['keputusan'] : 'Tidak ada keputusan spesifik.' ?>
            </div>
        </div>

        <div class="signature-wrapper">
            <div class="sig-box">
                <p style="font-size: 13px;">Jakarta, <?= date('d F Y') ?></p>
                <p style="font-size: 13px; margin-top: -10px;">Dibuat Oleh,</p>
                <div class="sig-space"></div>
                <p class="sig-name"><?= htmlspecialchars($data['pembuat_notulen']) ?></p>
                <p style="font-size: 12px; color: var(--secondary-gray);">Sekretaris Rapat</p>
            </div>
        </div>

        <div style="position: absolute; bottom: 20mm; left: 25mm; right: 25mm; border-top: 1px solid #eee; padding-top: 10px; font-size: 10px; color: #999; text-align: center;">
            Dokumen ini dihasilkan secara otomatis oleh Sistem NotulenKita Digital pada <?= date('d/m/Y H:i') ?>.
        </div>
    </div>

</body>
</html>