<?php
session_start();
include __DIR__ . '/../config.php';

$email = $_POST['email'];
$pw    = $_POST['password'];

$sql = "SELECT * FROM Users WHERE email='" . $email . "' AND password='" . $pw . "'";
$r   = mysqli_query($conn, $sql);
$u   = mysqli_fetch_assoc($r);

if ($u) {
    $_SESSION['user_email']  = $u['email'];
    $_SESSION['role']        = $u['role'];
    $_SESSION['first_name']  = $u['first_name'];
    $_SESSION['last_name']   = $u['last_name'];
    if ($u['role'] == 'admin') {
        header('Location: ../admin.php');
    } else {
        header('Location: ../index.php');
    }
} else {
    header('Location: ../login.php?err=invalid');
}
exit;
