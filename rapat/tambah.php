<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

if(isset($_POST['simpan'])){
    mysqli_query($conn,"
        INSERT INTO rapat (judul,tanggal,waktu,tempat,peserta,status)
        VALUES (
          '$_POST[judul]',
          '$_POST[tanggal]',
          '$_POST[waktu]',
          '$_POST[tempat]',
          '$_POST[peserta]',
          'Terjadwal'
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
<title>Tambah Rapat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Tambah Rapat</strong></div>
  <div class="card-body">

    <form method="post">
      <div class="mb-3">
        <label>Judul Rapat</label>
        <input type="text" name="judul" class="form-control" required>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label>Waktu</label>
          <input type="time" name="waktu" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label>Tempat</label>
        <input type="text" name="tempat" class="form-control">
      </div>

      <div class="mb-3">
        <label>Peserta</label>
        <textarea name="peserta" class="form-control"></textarea>
      </div>

      <button name="simpan" class="btn btn-info text-white">Simpan</button>
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>

  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
