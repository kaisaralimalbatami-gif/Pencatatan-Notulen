<?php
function catat_log($conn, $aksi) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['id']) && isset($_SESSION['nama'])) {
        $user_id = $_SESSION['id'];
        $nama_user = $_SESSION['nama'];
        
        $aksi_safe = mysqli_real_escape_string($conn, $aksi);
        $nama_safe = mysqli_real_escape_string($conn, $nama_user);
        $id_safe   = (int)$user_id;

        // Perhatikan nama tabel: 'aktivitas'
        $query = "INSERT INTO aktivitas (user_id, nama_user, aksi) VALUES ('$id_safe', '$nama_safe', '$aksi_safe')";
        mysqli_query($conn, $query);
    }
}
?>