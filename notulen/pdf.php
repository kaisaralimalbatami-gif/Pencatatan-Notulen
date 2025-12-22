<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

/*
|--------------------------------------------------------------------------
| DOMPDF AUTOLOAD (INI KUNCI)
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../vendor/dompdf/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| VALIDASI ID
|--------------------------------------------------------------------------
*/
if (!isset($_GET['id'])) {
    die('ID notulen tidak ditemukan');
}

$id = intval($_GET['id']);

/*
|--------------------------------------------------------------------------
| AMBIL DATA NOTULEN
|--------------------------------------------------------------------------
*/
$q = mysqli_query($conn, "
    SELECT 
        n.*,
        r.judul,
        r.tanggal,
        r.waktu,
        r.tempat,
        r.peserta
    FROM notulen n
    JOIN rapat r ON n.rapat_id = r.id
    WHERE n.id = $id
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    die('Data notulen tidak ditemukan');
}

/*
|--------------------------------------------------------------------------
| HTML PDF
|--------------------------------------------------------------------------
*/
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
}
h2 {
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
td, th {
    border: 1px solid #000;
    padding: 6px;
    vertical-align: top;
}
th {
    width: 25%;
    background: #f2f2f2;
}
</style>
</head>
<body>

<h2>NOTULEN RAPAT</h2>

<table>
<tr><th>Judul Rapat</th><td>'.$data['judul'].'</td></tr>
<tr><th>Tanggal</th><td>'.$data['tanggal'].'</td></tr>
<tr><th>Waktu</th><td>'.$data['waktu'].'</td></tr>
<tr><th>Tempat</th><td>'.$data['tempat'].'</td></tr>
<tr><th>Peserta</th><td>'.$data['peserta'].'</td></tr>
<tr><th>Pembahasan</th><td>'.$data['pembahasan'].'</td></tr>
<tr><th>Keputusan</th><td>'.$data['keputusan'].'</td></tr>
<tr><th>Catatan</th><td>'.$data['catatan'].'</td></tr>
</table>

</body>
</html>
';

/*
|--------------------------------------------------------------------------
| INIT DOMPDF (INI FIX ERROR LU)
|--------------------------------------------------------------------------
*/
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('notulen-'.$id.'.pdf', ['Attachment' => false]);
exit;
