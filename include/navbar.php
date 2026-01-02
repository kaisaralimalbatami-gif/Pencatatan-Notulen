<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER['PHP_SELF']);
$path = $_SERVER['PHP_SELF'];
$user_role = $_SESSION['role'] ?? 'user';
$user_name = $_SESSION['nama'] ?? 'User';
?>

<style>
/* NAVBAR THEME MATCHING */
:root {
    --nav-gradient: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}

/* TOP BAR - Glassmorphism Style */
.navbar-top {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-top .navbar-brand {
    font-size: 1.6rem;
    font-weight: 800;
    color: white !important;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.navbar-top .navbar-brand i {
    background: white;
    color: #0ea5e9;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 1.4rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* USER INFO AREA */
.user-badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 6px 15px;
    border-radius: 12px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.user-badge .role-tag {
    background: white;
    color: #0284c7;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
}

.btn-logout {
    background: #ef4444;
    color: white;
    padding: 8px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
}

.btn-logout:hover {
    background: #dc2626;
    transform: translateY(-2px);
    color: white;
    box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
}

/* MENU NAVIGATION */
.navbar-menu {
    background: rgba(255, 255, 255, 0.95);
    padding: 5px 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.navbar-menu .nav-link {
    color: #475569;
    font-weight: 600;
    padding: 10px 20px !important;
    border-radius: 12px;
    margin: 5px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.navbar-menu .nav-link i {
    font-size: 1.2rem;
}

.navbar-menu .nav-link:hover {
    background: #f1f5f9;
    color: #0ea5e9;
}

.navbar-menu .nav-link.active {
    background: #e0f2fe;
    color: #0ea5e9 !important;
    font-weight: 700;
    position: relative;
}

.navbar-menu .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 5px;
    left: 20px;
    right: 20px;
    height: 3px;
    background: #0ea5e9;
    border-radius: 10px;
}

/* RESPONSIVE */
@media (max-width: 991px) {
    .navbar-top .navbar-brand span { display: none; }
    .user-badge span { display: none; }
    .navbar-menu .nav-link.active::after { display: none; }
}
</style>

<nav class="navbar navbar-top">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="../dashboard/<?= $user_role == 'admin' ? 'admin' : 'user' ?>.php">
      <i class="bi bi-journal-text"></i>
      <span>NotulenKita</span>
    </a>
    
    <div class="d-flex align-items-center gap-3">
      <div class="user-badge d-none d-sm-flex">
        <i class="bi bi-person-circle"></i>
        <span><?= htmlspecialchars($user_name) ?></span>
        <span class="role-tag"><?= $user_role ?></span>
      </div>
      <a href="../auth/logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">
        <i class="bi bi-power"></i> <span class="d-none d-md-inline">Logout</span>
      </a>
    </div>
  </div>
</nav>

<nav class="navbar navbar-expand-lg navbar-menu">
  <div class="container">
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="bi bi-list fs-1 text-primary"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current == 'admin.php' || $current == 'user.php' ? 'active' : '' ?>"
             href="../dashboard/<?= $user_role == 'admin' ? 'admin' : 'user' ?>.php">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= strpos($path, '/rapat/') !== false ? 'active' : '' ?>"
             href="../rapat/index.php">
            <i class="bi bi-calendar3"></i>
            <span>Daftar Rapat</span>
          </a>
        </li>

        <?php if($user_role == 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link <?= strpos($path, 'notulen/tambah') !== false ? 'active' : '' ?>"
             href="../notulen/tambah.php">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Input Notulen</span>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
          <a class="nav-link <?= ($current == 'index.php' && strpos($path, '/notulen/') !== false) ? 'active' : '' ?>"
             href="../notulen/index.php">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Daftar Notulen</span>
          </a>
        </li>

        <?php if($user_role == 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link <?= strpos($path, 'user_manage') !== false ? 'active' : '' ?>"
             href="../dashboard/user_manage.php">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Manajemen User</span>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">