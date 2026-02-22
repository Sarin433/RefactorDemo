<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>500 - ข้อผิดพลาดของเซิร์ฟเวอร์</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-page">
<div class="auth-box" style="text-align:center;">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2 style="font-size:48px;color:#EF4444;margin:16px 0;">500</h2>
  <p style="color:#6B7280;margin-bottom:24px;">เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์ กรุณาลองใหม่อีกครั้ง</p>
  <a href="{{ auth()->check() ? route('products.index') : route('login') }}" class="btn-primary" style="display:inline-flex;">กลับหน้าหลัก</a>
</div>
</body>
</html>
