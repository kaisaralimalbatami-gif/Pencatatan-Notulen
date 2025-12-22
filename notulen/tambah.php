<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// ambil rapat
$rapat = mysqli_query($conn,"SELECT * FROM rapat ORDER BY tanggal DESC");

if(isset($_POST['simpan'])){
    mysqli_query($conn,"
        INSERT INTO notulen
        (rapat_id, judul_notulen, agenda, pembahasan, keputusan, tindak_lanjut, penanggung_jawab)
        VALUES (
            '$_POST[rapat_id]',
            '$_POST[judul_notulen]',
            '$_POST[agenda]',
            '$_POST[pembahasan]',
            '$_POST[keputusan]',
            '$_POST[tindak_lanjut]',
            '$_POST[penanggung_jawab]'
        )
    ");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Notulen</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white fw-semibold">
    Form Input Notulen Rapat
  </div>

  <div class="card-body">
    <form method="post">

    <h6 class="fw-bold">Informasi Rapat</h6>
    <hr>

    <div class="row mb-3">
      <div class="col-md-6">
        <label>Pilih Rapat</label>
        <select name="rapat_id" class="form-control" required>
          <option value="">-- Pilih Rapat --</option>
          <?php while($r=mysqli_fetch_assoc($rapat)): ?>
            <option value="<?= $r['id'] ?>">
              <?= $r['judul'] ?> (<?= $r['tanggal'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label>Judul Notulen</label>
        <input type="text" name="judul_notulen" class="form-control">
      </div>
    </div>

    <h6 class="fw-bold mt-4">Isi Notulen</h6>
    <hr>

    <div class="mb-3">
      <label>Agenda Rapat</label>
      <textarea name="agenda" class="form-control" rows="2"></textarea>
    </div>

    <div class="mb-3">
      <label>Pembahasan</label>
      <textarea name="pembahasan" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label>Keputusan</label>
      <textarea name="keputusan" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label>Tindak Lanjut</label>
      <textarea name="tindak_lanjut" class="form-control" rows="2"></textarea>
    </div>

    <div class="mb-3">
      <label>Penanggung Jawab</label>
      <input type="text" name="penanggung_jawab" class="form-control">
    </div>

    <button name="simpan" class="btn btn-info text-white">
      Simpan Notulen
    </button>
    <a href="index.php" class="btn btn-secondary">
      Batal
    </a>

    </form>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
