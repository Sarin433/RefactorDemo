<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../config.php';

$o = $_POST['order_number'];
$p = $_POST['product_number'];
$q = (int)$_POST['quantity'];

if ($q <= 0) {
    $sql = "DELETE FROM Order_Details WHERE order_number='" . $o . "' AND product_number='" . $p . "'";
} else {
    $sql = "UPDATE Order_Details SET quantity=" . $q . " WHERE order_number='" . $o . "' AND product_number='" . $p . "'";
}
mysqli_query($conn, $sql);

header('Location: ../my_orders.php');
exit;
