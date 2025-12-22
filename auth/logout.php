<?php
session_start();

// hapus semua session
session_unset();
session_destroy();

// balik ke login
header("Location: ../auth/login.php");
exit;
