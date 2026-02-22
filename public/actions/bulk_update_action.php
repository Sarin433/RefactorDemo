<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../config.php';

if (isset($_POST['selected_orders']) && count($_POST['selected_orders']) > 0) {
    $ids = implode("','", $_POST['selected_orders']);
    $sql = "UPDATE Orders SET status_id = 2 WHERE order_number IN ('" . $ids . "')";
    mysqli_query($conn, $sql);
}

header('Location: ../admin.php');
exit;
