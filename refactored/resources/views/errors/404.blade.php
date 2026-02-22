<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>404 - ไม่พบหน้าที่ต้องการ</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-page">
<div class="auth-box" style="text-align:center;">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2 style="font-size:48px;color:#2563EB;margin:16px 0;">404</h2>
  <p style="color:#6B7280;margin-bottom:24px;">ไม่พบหน้าที่คุณต้องการ</p>
  <a href="{{ auth()->check() ? route('products.index') : route('login') }}" class="btn-primary" style="display:inline-flex;">กลับหน้าหลัก</a>
</div>
</body>
</html>
