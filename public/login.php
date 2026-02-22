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
<title>เข้าสู่ระบบ - OMS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<div class="auth-box">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2>เข้าสู่ระบบ</h2>
  <?php if ($err == 'invalid'): ?>
    <p class="err-msg">อีเมลหรือรหัสผ่านไม่ถูกต้อง</p>
  <?php endif; ?>
  <form method="post" action="actions/login_action.php">
    <label>อีเมล</label>
    <input type="text" name="email" placeholder="email@example.com" required>
    <label>รหัสผ่าน</label>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit" class="btn-primary full">เข้าสู่ระบบ</button>
  </form>
  <p class="auth-link">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
</div>
</body>
</html>
