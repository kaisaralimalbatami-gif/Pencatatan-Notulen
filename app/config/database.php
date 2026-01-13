<?php
// app/config/database.php

/**
 * Mengatur zona waktu agar sesuai dengan lokasi server/pengguna
 */
date_default_timezone_set('Asia/Jakarta');

/**
 * Mengaktifkan pelaporan error mysqli agar bisa ditangkap oleh Try-Catch
 * Ini penting untuk poin no. 2
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * Fungsi untuk membuat koneksi ke database
 * Memenuhi poin no. 1: Logika pemecahan program dengan fungsi
 * * @return mysqli|false Mengembalikan objek koneksi atau false jika gagal
 */
function getDBConnection() {
    $host   = 'localhost';
    $user   = 'root'; 
    $pass   = ''; 
    $dbname = 'notulenkita';

    try {
        // Memenuhi poin no. 2: Penerapan error handling try catch
        $conn = mysqli_connect($host, $user, $pass, $dbname);
        
        // Set charset untuk menangani karakter khusus
        mysqli_set_charset($conn, "utf8mb4");

        return $conn;

    } catch (mysqli_sql_exception $e) {
        // Log error asli untuk developer (jangan tampilkan ke user)
        error_log("[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage());
        
        // Return false atau handle error secara graceful
        // Di sini kita bisa matikan proses jika database vital
        die("❌ Maaf, terjadi kesalahan pada sistem (Database Connection). Silakan coba beberapa saat lagi.");
    }
}
?>