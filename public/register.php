<?php
session_start();
if (isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}
$err = isset($_GET['err']) ? $_GET['err'] : '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>สมัครสมาชิก - OMS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<div class="auth-box">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2>สมัครสมาชิก</h2>
  <?php if ($err == 'dup'): ?>
    <p class="err-msg">อีเมลนี้ถูกใช้งานแล้ว</p>
  <?php elseif ($err == 'pw'): ?>
    <p class="err-msg">รหัสผ่านไม่ตรงกัน</p>
  <?php endif; ?>
  <form method="post" action="actions/register_action.php">
    <label>อีเมล</label>
    <input type="text" name="email" placeholder="email@example.com" required>
    <label>ชื่อจริง</label>
    <input type="text" name="first_name" placeholder="ชื่อจริง" required>
    <label>นามสกุล</label>
    <input type="text" name="last_name" placeholder="นามสกุล" required>
    <label>เบอร์โทรศัพท์</label>
    <input type="text" name="phone" placeholder="0XX-XXX-XXXX" required>
    <label>รหัสผ่าน</label>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <label>ยืนยันรหัสผ่าน</label>
    <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
    <button type="submit" class="btn-primary full">สมัครสมาชิก</button>
  </form>
  <p class="auth-link">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
</div>
</body>
</html>
