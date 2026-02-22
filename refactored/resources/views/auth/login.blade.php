<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ - OMS</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-page">
<div class="auth-box">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2>เข้าสู่ระบบ</h2>

  @if ($errors->has('email'))
    <p class="err-msg">{{ $errors->first('email') }}</p>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <label>อีเมล</label>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required autocomplete="email">
    <label>รหัสผ่าน</label>
    <input type="password" name="password" placeholder="รหัสผ่าน" required autocomplete="current-password">
    <button type="submit" class="btn-primary full">เข้าสู่ระบบ</button>
  </form>
  <p class="auth-link">ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a></p>
</div>
</body>
</html>
