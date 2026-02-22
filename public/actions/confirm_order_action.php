<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../config.php';

$o    = $_POST['order_number'];
$addr = $_POST['shipping_address'];

$sql = "UPDATE Orders SET shipping_address='" . $addr . "' WHERE order_number='" . $o . "'";
mysqli_query($conn, $sql);

header('Location: ../my_orders.php');
exit;
