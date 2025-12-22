<?php
session_start();

// koneksi & navbar
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

// ambil id dari URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// ambil data rapat
$data = mysqli_query($conn, "SELECT * FROM rapat WHERE id='$id'");
$rapat = mysqli_fetch_assoc($data);

if (!$rapat) {
    header("Location: index.php");
    exit;
}

// proses update
if (isset($_POST['update'])) {
    $judul   = $_POST['judul'];
    $tanggal = $_POST['tanggal'];
    $waktu   = $_POST['waktu'];
    $tempat  = $_POST['tempat'];
    $peserta = $_POST['peserta'];
    $status  = $_POST['status'];

    mysqli_query($conn, "
        UPDATE rapat SET
            judul   = '$judul',
            tanggal = '$tanggal',
            waktu   = '$waktu',
            tempat  = '$tempat',
            peserta = '$peserta',
            status  = '$status'
        WHERE id = '$id'
    ");

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Rapat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<body>

<div class="container-fluid px-4 mt-4">

  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <strong>Edit Jadwal Rapat</strong>
    </div>

    <div class="card-body">

      <form method="post">

        <div class="mb-3">
          <label class="form-label">Judul Rapat</label>
          <input type="text" name="judul" class="form-control"
                 value="<?= htmlspecialchars($rapat['judul']) ?>" required>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control"
                   value="<?= $rapat['tanggal'] ?>" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Waktu</label>
            <input type="time" name="waktu" class="form-control"
                   value="<?= $rapat['waktu'] ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Tempat</label>
          <input type="text" name="tempat" class="form-control"
                 value="<?= htmlspecialchars($rapat['tempat']) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Peserta</label>
          <textarea name="peserta" class="form-control" rows="3"><?= htmlspecialchars($rapat['peserta']) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="Terjadwal" <?= $rapat['status']=='Terjadwal'?'selected':'' ?>>
              Terjadwal
            </option>
            <option value="Selesai" <?= $rapat['status']=='Selesai'?'selected':'' ?>>
              Selesai
            </option>
          </select>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" name="update" class="btn btn-warning">
            Update
          </button>
          <a href="index.php" class="btn btn-secondary">
            Kembali
          </a>
        </div>

      </form>

    </div>
  </div>

</div>

</body>
</html>
