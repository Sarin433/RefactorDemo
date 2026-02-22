<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../config.php';

$email = $_SESSION['user_email'];
$ord_no = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
$pnums = $_POST['product_number'];
$qtys  = $_POST['quantity'];

$sql = "INSERT INTO Orders (order_number, user_email, status_id) VALUES ('" . $ord_no . "', '" . $email . "', 1)";
mysqli_query($conn, $sql);

for ($i = 0; $i < count($pnums); $i++) {
    $p = $pnums[$i];
    $q = (int)$qtys[$i];
    $dsql = "INSERT INTO Order_Details (order_number, product_number, quantity) VALUES ('" . $ord_no . "', '" . $p . "', " . $q . ")";
    mysqli_query($conn, $dsql);
}

header('Location: ../my_orders.php?created=' . $ord_no);
exit;
