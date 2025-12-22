<?php
function logAktivitas($conn, $user_id, $aksi) {
    mysqli_query($conn, "
        INSERT INTO aktivitas (user_id, aksi, created_at)
        VALUES ('$user_id', '$aksi', NOW())
    ");
}
