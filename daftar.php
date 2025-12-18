<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>NotulenKita - Daftar Rapat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Georgia', serif;
            background: #f2f2f2;
        }
        .navbar-custom {
            background: #1BC3E5;
        }
        .menu a {
            text-decoration: none;
            color: #000;
            font-size: 18px;
        }
        .menu a:hover {
            color: #1BC3E5;
        }
        .table thead {
            background: #8CD5E7;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom px-4 py-3">
    <span class="navbar-brand text-white fw-bold fs-3">NotulenKita</span>
    <div class="ms-auto">
        <a href="#" class="text-white text-decoration-none fs-5">Logout</a>
    </div>
</nav>

<!-- Menu -->
<div class="bg-white px-4 py-3 d-flex gap-4 menu shadow-sm">
    <a href="#"><i class="fa fa-home"></i> Dashboard</a>
    <a href="#"><i class="fa fa-calendar"></i> Daftar Rapat</a>
    <a href="#"><i class="fa fa-edit"></i> Input Notulen</a>
    <a href="#"><i class="fa fa-file"></i> Daftar Notulen</a>
    <a href="#"><i class="fa fa-users"></i> Manajemen User</a>
</div>

<!-- Content -->
<div class="container my-4">
    <div class="bg-white p-4 rounded-4 border border-2" style="border-color:#a66bff!important;">
        <h2 class="mb-3">Daftar Jadwal Rapat</h2>

        <!-- Button -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn text-white" style="background:#21D4B4;">
                <i class="fa fa-plus"></i> Tambah Rapat
            </button>
        </div>

        <!-- Search -->
        <div class="input-group mb-4">
            <span class="input-group-text bg-light">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" class="form-control" placeholder="Cari rapat berdasarkan judul atau tempat...">
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
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
                        <td colspan="7" class="text-center py-5">
                            Belum ada data rapat.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
