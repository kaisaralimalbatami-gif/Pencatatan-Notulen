<?php
// AWALI DENGAN OUTPUT BUFFERING
ob_start();

session_start();
require_once __DIR__ . '/../app/config/database.php';

// Proteksi akses (Sesuaikan dengan session di dashboard lu)
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID notulen tidak valid!';
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data notulen dengan informasi rapat
$query = mysqli_query($conn, "
    SELECT n.*, r.judul as judul_rapat, r.tanggal, r.waktu, r.tempat, r.status
    FROM notulen n 
    JOIN rapat r ON r.id = n.rapat_id 
    WHERE n.id='$id'
");

if (mysqli_num_rows($query) == 0) {
    $_SESSION['error'] = 'Notulen tidak ditemukan!';
    header("Location: index.php");
    exit;
}

$n = mysqli_fetch_assoc($query);

// Proses update
if(isset($_POST['update'])){
    $pembahasan = mysqli_real_escape_string($conn, $_POST['pembahasan']);
    $keputusan = mysqli_real_escape_string($conn, $_POST['keputusan']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $tindak_lanjut = mysqli_real_escape_string($conn, $_POST['tindak_lanjut']);
    $penanggung_jawab = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);
    
    $q_update = mysqli_query($conn, "
        UPDATE notulen SET
            pembahasan = '$pembahasan',
            keputusan = '$keputusan',
            catatan = '$catatan',
            tindak_lanjut = '$tindak_lanjut',
            penanggung_jawab = '$penanggung_jawab'
        WHERE id = '$id'
    ");
    
    // --- MULAI KODE CCTV (LOG AKTIVITAS) ---
    if ($q_update) {
        // Panggil Helper Log
        require_once __DIR__ . '/../helpers/log.php';
        
        // Ambil judul rapat dari data $n yang udah di-select di atas
        $log_pesan = "Mengedit notulen rapat: " . $n['judul_rapat'];
        catat_log($conn, $log_pesan);
    }
    // --- SELESAI KODE CCTV ---

    $_SESSION['success'] = 'Notulen berhasil diperbarui!';
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../include/navbar.php';
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Notulen | NotulenKita</title>
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
        }

        /* Glassmorphism Effect Sesuai Dashboard Admin */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 30px;
        }

        .header-section {
            border-left: 8px solid #f59e0b;
            margin-bottom: 30px;
        }

        /* Form Styling */
        .form-label-custom {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .form-control-custom:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
            background: #fff;
        }

        /* Info Display */
        .info-box {
            background: #f1f5f9;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .status-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        /* Toolbar Editor */
        .editor-toolbar {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            padding: 8px;
            display: flex;
            gap: 5px;
        }

        .btn-tool {
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-tool:hover { background: #0ea5e9; color: white; }

        /* Buttons */
        .btn-update {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 8px 15px rgba(14, 165, 233, 0.3);
            transition: 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(14, 165, 233, 0.4);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .char-counter {
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="glass-card header-section mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-1 text-dark">Edit Notulen</h1>
                <p class="text-muted mb-0">Sesuaikan hasil rapat dengan format profesional.</p>
            </div>
            <span class="status-badge">
                <i class="bi bi-info-circle me-1"></i> Status Rapat: <?= $n['status'] ?>
            </span>
        </div>
    </div>

    <div class="glass-card">
        <form method="post">
            
            <div class="info-box">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Judul Rapat</small>
                        <strong class="text-dark"><?= htmlspecialchars($n['judul_rapat']) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Tanggal & Waktu</small>
                        <strong class="text-dark"><?= date('d M Y', strtotime($n['tanggal'])) ?> | <?= $n['waktu'] ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Tempat</small>
                        <strong class="text-dark"><?= htmlspecialchars($n['tempat']) ?></strong>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom"><i class="bi bi-list-stars text-primary"></i> Agenda Rapat</label>
                <div class="form-control-custom bg-light" style="white-space: pre-line; border-style: dashed;">
                    <?= htmlspecialchars($n['agenda']) ?>
                </div>
            </div>

            <hr class="my-4 opacity-50">

            <div class="mb-4">
                <label class="form-label-custom"><i class="bi bi-chat-right-text text-primary"></i> Isi Pembahasan</label>
                <div class="editor-toolbar">
                    <button type="button" class="btn-tool" onclick="formatText('pembahasan', 'bold')">B</button>
                    <button type="button" class="btn-tool" onclick="formatText('pembahasan', 'italic')">I</button>
                    <button type="button" class="btn-tool" onclick="formatText('pembahasan', 'list')">Bullet</button>
                </div>
                <textarea name="pembahasan" id="pembahasanInput" class="form-control form-control-custom w-100" style="border-radius: 0 0 12px 12px;" rows="8"><?= htmlspecialchars($n['pembahasan']) ?></textarea>
                <div class="char-counter" id="pembahasanCounter">0/5000</div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom"><i class="bi bi-check2-circle text-success"></i> Keputusan Akhir</label>
                <div class="editor-toolbar">
                    <button type="button" class="btn-tool" onclick="formatText('keputusan', 'bold')">B</button>
                    <button type="button" class="btn-tool" onclick="formatText('keputusan', 'list')">Bullet</button>
                </div>
                <textarea name="keputusan" id="keputusanInput" class="form-control form-control-custom w-100" style="border-radius: 0 0 12px 12px;" rows="4"><?= htmlspecialchars($n['keputusan']) ?></textarea>
                <div class="char-counter" id="keputusanCounter">0/3000</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label-custom"><i class="bi bi-arrow-right-short text-primary"></i> Tindak Lanjut</label>
                    <textarea name="tindak_lanjut" class="form-control-custom w-100" rows="3"><?= htmlspecialchars($n['tindak_lanjut']) ?></textarea>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label-custom"><i class="bi bi-person-badge text-primary"></i> Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" class="form-control-custom w-100" value="<?= htmlspecialchars($n['penanggung_jawab']) ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom"><i class="bi bi-sticky text-warning"></i> Catatan Tambahan</label>
                <textarea name="catatan" class="form-control-custom w-100" rows="2"><?= htmlspecialchars($n['catatan']) ?></textarea>
            </div>

            <div class="d-flex gap-3 mt-5 pt-4 border-top">
                <button type="submit" name="update" class="btn-update">
                    <i class="bi bi-save me-2"></i> Update Notulen
                </button>
                <a href="index.php" class="btn-cancel">
                    Kembali
                </a>
                <a href="pdf.php?id=<?= $id ?>" target="_blank" class="ms-auto btn btn-outline-danger px-4" style="border-radius:12px">
                    <i class="bi bi-file-pdf"></i> PDF
                </a>
            </div>

        </form>
    </div>
</div>

<script>
// Logic formatter sederhana
function formatText(field, type) {
    const textarea = document.getElementById(`${field}Input`);
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    let formattedText = '';
    
    switch(type) {
        case 'bold': formattedText = `**${selectedText}**`; break;
        case 'italic': formattedText = `*${selectedText}*`; break;
        case 'list': formattedText = `\n- ${selectedText}`; break;
    }
    textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    textarea.focus();
}

// Counter Logic
document.addEventListener('DOMContentLoaded', function() {
    ['pembahasan', 'keputusan'].forEach(f => {
        const el = document.getElementById(f + 'Input');
        const counter = document.getElementById(f + 'Counter');
        if(el && counter) {
            el.addEventListener('input', () => {
                counter.textContent = el.value.length + (f === 'pembahasan' ? '/5000' : '/3000');
            });
            el.dispatchEvent(new Event('input'));
        }
    });
});
</script>

</body>
</html>