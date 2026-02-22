<?php
session_start();
include __DIR__ . '/../config.php';

$email = $_POST['email'];
$fn    = $_POST['first_name'];
$ln    = $_POST['last_name'];
$ph    = $_POST['phone'];
$pw    = $_POST['password'];
$cpw   = $_POST['confirm_password'];

if ($pw != $cpw) {
    header('Location: ../register.php?err=pw');
    exit;
}

$chk = "SELECT email FROM Users WHERE email='" . $email . "'";
$r   = mysqli_query($conn, $chk);
if (mysqli_num_rows($r) > 0) {
    header('Location: ../register.php?err=dup');
    exit;
}

$sql = "INSERT INTO Users (email, first_name, last_name, phone, password, role) VALUES ('" . $email . "', '" . $fn . "', '" . $ln . "', '" . $ph . "', '" . $pw . "', 'user')";
mysqli_query($conn, $sql);

header('Location: ../login.php');
exit;
