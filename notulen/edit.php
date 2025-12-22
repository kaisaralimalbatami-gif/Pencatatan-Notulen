<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../include/navbar.php';

$id = $_GET['id'];
$data = mysqli_query($conn,"SELECT * FROM notulen WHERE id='$id'");
$n = mysqli_fetch_assoc($data);

if(isset($_POST['update'])){
  mysqli_query($conn,"
    UPDATE notulen SET
      pembahasan='$_POST[pembahasan]',
      keputusan='$_POST[keputusan]',
      catatan='$_POST[catatan]'
    WHERE id='$id'
  ");
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Notulen</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid px-4 mt-4">
<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Edit Notulen</strong></div>
  <div class="card-body">

    <form method="post">
      <div class="mb-3">
        <label>Pembahasan</label>
        <textarea name="pembahasan" class="form-control" rows="4"><?= $n['pembahasan'] ?></textarea>
      </div>

      <div class="mb-3">
        <label>Keputusan</label>
        <textarea name="keputusan" class="form-control" rows="3"><?= $n['keputusan'] ?></textarea>
      </div>

      <div class="mb-3">
        <label>Catatan</label>
        <textarea name="catatan" class="form-control"><?= $n['catatan'] ?></textarea>
      </div>

      <button name="update" class="btn btn-warning">Update</button>
      <a href="index.php" class="btn btn-secondary">Kembali</a>
    </form>

  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
