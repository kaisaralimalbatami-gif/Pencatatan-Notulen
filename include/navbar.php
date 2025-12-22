<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current = basename($_SERVER['PHP_SELF']);
$path = $_SERVER['PHP_SELF'];
?>

<style>
.navbar-light .nav-link {
  color: #333;
  padding: 8px 14px;
  border-bottom: 3px solid transparent;
}
.navbar-light .nav-link.active {
  color: #0d6efd;
  font-weight: 600;
  border-bottom: 3px solid #0d6efd;
}
</style>

<!-- TOP BAR -->
<nav class="navbar navbar-dark bg-info">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="../dashboard/<?= $_SESSION['role']=='admin'?'admin':'user' ?>.php">
      NotulenKita
    </a>
    <a href="../auth/logout.php" class="btn btn-light btn-sm">Logout</a>
  </div>
</nav>

<!-- MENU -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid px-4">
    <ul class="navbar-nav me-auto">

      <li class="nav-item">
        <a class="nav-link <?= $current=='admin.php'||$current=='user.php'?'active':'' ?>"
           href="../dashboard/<?= $_SESSION['role']=='admin'?'admin':'user' ?>.php">
          Dashboard
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= strpos($path,'/rapat/')!==false?'active':'' ?>"
           href="../rapat/index.php">
          Daftar Rapat
        </a>
      </li>

      <?php if($_SESSION['role']=='admin'): ?>
      <li class="nav-item">
        <a class="nav-link <?= strpos($path,'notulen/tambah')!==false?'active':'' ?>"
           href="../notulen/tambah.php">
          Input Notulen
        </a>
      </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link <?= ($current=='index.php' && strpos($path,'/notulen/')!==false)?'active':'' ?>"
           href="../notulen/index.php">
          Daftar Notulen
        </a>
      </li>

      <?php if($_SESSION['role']=='admin'): ?>
      <li class="nav-item">
        <a class="nav-link <?= strpos($_SERVER['PHP_SELF'],'user_manage')!==false?'active':'' ?>"
            href="../dashboard/user_manage.php">
           Manajemen User
        </a>
      </li>
      <?php endif; ?>

    </ul>
  </div>
</nav>
