<?php
session_start();
require_once __DIR__ . '/../app/config/database.php';

$id = $_GET['id'];
mysqli_query($conn,"DELETE FROM notulen WHERE id='$id'");

header("Location: index.php");
exit;
