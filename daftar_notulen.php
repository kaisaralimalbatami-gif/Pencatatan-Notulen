<?php
// koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "project";
$koneksi = mysqli_connect($host, $user, $pass, $db);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Rapat | NotulenKita</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f6f6f6; }
        .header {
            background-color: #00c7f2;
            padding: 15px 30px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h4 { margin: 0; font-weight: bold; }
        .nav-custom {
            background: #fff;
            border-bottom: 3px solid #00c7f2;
            justify-content: center;
            border-radius: 0;
        }
        .nav-custom .nav-link { color: #000; }
        .nav-custom .active { color: #00c7f2 !important; font-weight: 600; }
        .card-custom {
            border-radius: 10px;
        }
        .table thead {
            background-color: #7fd7e5;
        }
        .btn-add {
            background-color: #2ec4b6;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <h4>NotulenKita</h4>
    <span>Logout</span>
</div>

<!-- NAVBAR -->
<ul class="nav nav-custom px-4 py-2 justify-content-center">
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-grid"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-calendar"></i> Daftar Rapat</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-plus-circle"></i> Input Notulen</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-text"></i> Daftar Notulen</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-people"></i> Manajemen User</a></li>
</ul>

<!-- CONTENT -->
<div class="container my-4">
    <div class="card card-custom shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Daftar Jadwal Rapat</h5>
                <a href="#" class="btn btn-add btn-sm"><i class="bi bi-plus"></i> Tambah Rapat</a>
            </div>

            <input type="text" class="form-control mb-3" placeholder="cari rapat berdasarkan judul atau tempat...">

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead>
                        <tr>
                            <th>Judul Rapat</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Tempat</th>
                            <th>Peserta</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7">Belum ada data rapat</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
