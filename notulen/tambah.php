<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

// Proteksi Halaman
if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

// ambil rapat
$rapat = mysqli_query($conn, "SELECT * FROM rapat ORDER BY tanggal DESC");

if(isset($_POST['simpan'])){
    // Gunakan mysqli_real_escape_string untuk keamanan
    $rapat_id = mysqli_real_escape_string($conn, $_POST['rapat_id']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul_notulen']);
    $agenda = mysqli_real_escape_string($conn, $_POST['agenda']);
    $pembahasan = mysqli_real_escape_string($conn, $_POST['pembahasan']);
    $keputusan = mysqli_real_escape_string($conn, $_POST['keputusan']);
    $tindak_lanjut = mysqli_real_escape_string($conn, $_POST['tindak_lanjut']);
    $pj = mysqli_real_escape_string($conn, $_POST['penanggung_jawab']);

    $simpan = mysqli_query($conn,"
        INSERT INTO notulen
        (rapat_id, judul_notulen, agenda, pembahasan, keputusan, tindak_lanjut, penanggung_jawab)
        VALUES ('$rapat_id', '$judul', '$agenda', '$pembahasan', '$keputusan', '$tindak_lanjut', '$pj')
    ");

    // --- MULAI KODE CCTV (LOG AKTIVITAS) ---
    if ($simpan) {
        // Panggil Helper Log
        require_once __DIR__ . '/../helpers/log.php';
        
        // Catat ke database aktivitas
        $log_pesan = "Menambahkan notulen baru: " . $judul;
        catat_log($conn, $log_pesan);
    }
    // --- SELESAI KODE CCTV ---

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Notulen | NotulenKita</title>
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

        .container-fluid { position: relative; z-index: 1; max-width: 1000px; padding-bottom: 50px; }

        .card-custom {
            border: none; border-radius: 20px; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.98);
            margin-top: 30px;
        }

        .card-header-custom {
            background: #1e293b; color: white; padding: 25px; border-bottom: none;
        }

        .card-header-custom h2 { margin: 0; font-weight: 800; font-size: 1.4rem; letter-spacing: 0.5px; }

        .section-header {
            color: #1e293b; font-weight: 700; font-size: 1.1rem; margin: 30px 0 15px 0;
            padding-bottom: 10px; border-bottom: 2px solid #0ea5e9; display: flex; align-items: center; gap: 10px;
        }

        .form-label-custom { font-weight: 600; color: #334155; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .form-label-custom i { color: #0ea5e9; }

        .form-control-custom {
            border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 15px;
            background: #f8fafc; transition: 0.3s;
        }
        .form-control-custom:focus {
            border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); outline: none;
        }

        .rapat-info-display {
            background: #f0f9ff; border-radius: 15px; padding: 20px;
            margin-bottom: 25px; border: 1px solid #bae6fd; display: none;
        }
        .rapat-info-display.active { display: block; animation: fadeIn 0.5s; }

        .btn-save {
            background: #0ea5e9; color: white; border: none; padding: 12px 30px;
            border-radius: 12px; font-weight: 700; transition: 0.3s;
        }
        .btn-save:hover { background: #0284c7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4); }

        .btn-cancel {
            background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 12px 30px;
            border-radius: 12px; font-weight: 700; text-decoration: none; transition: 0.3s;
        }
        .btn-cancel:hover { background: #e2e8f0; color: #1e293b; }

        .char-counter { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 4px; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <?php require_once __DIR__.'/../include/navbar.php'; ?>

    <div class="container-fluid px-3 px-md-4">
        <div class="card card-custom">
            <div class="card-header card-header-custom text-center">
                <h2><i class="bi bi-pencil-square me-2"></i> BUAT NOTULEN BARU</h2>
            </div>

            <div class="card-body p-4 p-md-5">
                <form method="post" id="notulenForm">
                    
                    <div class="section-header">
                        <i class="bi bi-info-circle-fill"></i> Informasi Rapat
                    </div>

                    <div id="rapatInfoDisplay" class="rapat-info-display">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">Tanggal</small>
                                <strong id="displayTanggal" class="text-primary">-</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <small class="text-muted d-block">Waktu</small>
                                <strong id="displayWaktu" class="text-primary">-</strong>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Lokasi/Tempat</small>
                                <strong id="displayTempat" class="text-primary">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-custom"><i class="bi bi-calendar-check"></i> Pilih Rapat Terjadwal</label>
                            <select name="rapat_id" class="form-control-custom" id="rapatSelect" required>
                                <option value="">-- Pilih Rapat --</option>
                                <?php while($r=mysqli_fetch_assoc($rapat)): ?>
                                    <option value="<?= $r['id'] ?>" 
                                            data-judul="<?= htmlspecialchars($r['judul']) ?>"
                                            data-tanggal="<?= date('d M Y', strtotime($r['tanggal'])) ?>"
                                            data-waktu="<?= $r['waktu'] ?>"
                                            data-tempat="<?= htmlspecialchars($r['tempat']) ?>">
                                        <?= htmlspecialchars($r['judul']) ?> (<?= date('d/m/y', strtotime($r['tanggal'])) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom"><i class="bi bi-type"></i> Judul Notulen</label>
                            <input type="text" name="judul_notulen" id="judulNotulen" class="form-control-custom" placeholder="Masukkan judul..." required>
                            <div class="char-counter" id="judulCounter">0/200</div>
                        </div>
                    </div>

                    <div class="section-header">
                        <i class="bi bi-journal-text"></i> Isi Konten Notulen
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom"><i class="bi bi-list-ol"></i> Agenda Rapat</label>
                        <textarea name="agenda" id="agendaInput" class="form-control-custom" rows="3" placeholder="Tulis poin-poin agenda..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom"><i class="bi bi-chat-dots"></i> Pembahasan Lengkap</label>
                        <textarea name="pembahasan" id="pembahasanInput" class="form-control-custom" rows="5" placeholder="Tulis detail pembahasan rapat..."></textarea>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-custom"><i class="bi bi-check2-circle"></i> Keputusan Akhir</label>
                            <textarea name="keputusan" id="keputusanInput" class="form-control-custom" rows="4" placeholder="Hasil keputusan rapat..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom"><i class="bi bi-arrow-right-circle"></i> Tindak Lanjut</label>
                            <textarea name="tindak_lanjut" id="tindakLanjutInput" class="form-control-custom" rows="4" placeholder="Langkah setelah rapat..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 mb-4">
                        <label class="form-label-custom"><i class="bi bi-person-badge"></i> Penanggung Jawab (PJ)</label>
                        <input type="text" name="penanggung_jawab" class="form-control-custom" placeholder="Nama PJ atau Divisi terkait...">
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-5 pt-4 border-top">
                        <button type="submit" name="simpan" class="btn-save">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> SIMPAN NOTULEN
                        </button>
                        <button type="button" class="btn-cancel" id="previewBtn" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                            <i class="bi bi-eye-fill me-2"></i> PREVIEW
                        </div>
                        <a href="index.php" class="btn-cancel">
                            <i class="bi bi-x-circle me-2"></i> BATAL
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const rapatSelect = document.getElementById('rapatSelect');
        const rapatInfoDisplay = document.getElementById('rapatInfoDisplay');
        const judulNotulen = document.getElementById('judulNotulen');

        // Logic pilih rapat
        rapatSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                rapatInfoDisplay.classList.add('active');
                document.getElementById('displayTanggal').textContent = opt.getAttribute('data-tanggal');
                document.getElementById('displayWaktu').textContent = opt.getAttribute('data-waktu');
                document.getElementById('displayTempat').textContent = opt.getAttribute('data-tempat');
                
                if (!judulNotulen.value) {
                    judulNotulen.value = "Notulen: " + opt.getAttribute('data-judul');
                }
            } else {
                rapatInfoDisplay.classList.remove('active');
            }
        });

        // Simple Character Counter logic
        const inputs = ['judulNotulen', 'agendaInput', 'pembahasanInput'];
        inputs.forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener('input', function() {
                    const counter = document.getElementById(id.replace('Input', '').replace('Notulen', '') + 'Counter');
                    if(counter) counter.textContent = this.value.length + (id === 'judulNotulen' ? '/200' : '');
                });
            }
        });
    });
    </script>
</body>
</html>