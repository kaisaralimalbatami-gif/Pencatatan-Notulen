<?php
    // dummy data bisa diganti database
    $rapat = 24;
    $stat_rapat = "+12% dari bulan lalu";

    $notulen = 18;
    $review = 7;

    $user = 18;
    $login_hari_ini = 8;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NotulenKita - Dashboard</title>

    <!-- Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f4f4;
        }

        /* HEADER */
        .header {
            width: 100%;
            background: #00B5E2;
            padding: 25px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .logout {
            font-size: 18px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* NAVIGATION */
        .nav {
            display: flex;
            gap: 40px;
            padding: 15px 50px;
            background: white;
            border-bottom: 1px solid #ddd;
        }

        .nav a {
            text-decoration: none;
            color: #444;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav .active {
            color: #00AEEF;
            font-weight: 600;
        }

        /* CARD CONTAINER */
        .container {
            padding: 30px 50px;
            display: flex;
            gap: 25px;
        }

        .card {
            background: white;
            width: 330px;
            padding: 22px;
            border-radius: 12px;
            border: 2px solid transparent;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            position: relative;
        }

        .card.blue-border {
            border-color: #0da2ff;
        }

        .card .icon-small {
            position: absolute;
            top: 18px;
            right: 18px;
            color: #7A4AFF;
        }

        .card .icon-green {
            color: #00C2A8;
        }

        .card .icon-purple {
            color: #9A4BFF;
        }

        .card h1 {
            font-size: 40px;
            margin-top: 10px;
        }

        .card p {
            margin-top: 8px;
            font-size: 15px;
            color: #555;
        }

        .card span {
            color: #00AEEF;
            font-size: 14px;
            font-weight: 500;
        }

        /* BOX AKTIVITAS */
        .box-container {
            margin: 20px 50px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .box-container h2 {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .line {
            width: 100%;
            height: 1px;
            background: #e0e0e0;
            margin: 22px 0;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div>NotulenKita</div>
        <a href="#" class="logout"><i data-feather="log-out"></i> Logout</a>
    </div>

    <!-- MENU -->
    <div class="nav">
        <a class="active"><i data-feather="grid"></i> Dashboard</a>
        <a><i data-feather="calendar"></i> Daftar Rapat</a>
        <a><i data-feather="plus-circle"></i> Input Notulen</a>
        <a><i data-feather="file-text"></i> Daftar Notulen</a>
        <a><i data-feather="users"></i> Manajemen User</a>
    </div>

    <!-- CARDS -->
    <div class="container">

        <div class="card blue-border">
            <i class="icon-small" data-feather="calendar"></i>
            <h3>Statistik Rapat</h3>
            <h1><?= $rapat ?></h1>
            <p>Rapat bulan ini</p>
            <span><?= $stat_rapat ?></span>
        </div>

        <div class="card">
            <i class="icon-small icon-green" data-feather="file-text"></i>
            <h3>Statistik Notulen</h3>
            <h1><?= $notulen ?></h1>
            <p>Notulen terdokumentasi</p>
            <span><?= $review ?> menunggu review</span>
        </div>

        <div class="card">
            <i class="icon-small icon-purple" data-feather="user"></i>
            <h3>User Aktif</h3>
            <h1><?= $user ?></h1>
            <p>User terdaftar</p>
            <span><?= $login_hari_ini ?> login hari ini</span>
        </div>

    </div>

    <!-- AKTIVITAS TERBARU -->
    <div class="box-container">
        <h2>Aktivitas Terbaru</h2>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>

    <script>
        feather.replace();
    </script>

</body>
</html>
