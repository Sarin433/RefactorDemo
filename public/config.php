<?php
$host = 'db';
$db   = 'omsdb';
$user = 'omsuser';
$pass = 'omspass';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('DB connect fail: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

include __DIR__ . '/seed.php';
