<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครสมาชิก - OMS</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="auth-page">
<div class="auth-box">
  <div class="auth-logo"><span class="logo-icon">OMS</span></div>
  <h2>สมัครสมาชิก</h2>

  @if ($errors->any())
    <p class="err-msg">
      @foreach ($errors->all() as $error)
        {{ $error }}<br>
      @endforeach
    </p>
  @endif

  <form method="POST" action="{{ route('register') }}">
    @csrf
    <label>อีเมล</label>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required autocomplete="email">
    <label>ชื่อจริง</label>
    <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="ชื่อจริง" required>
    <label>นามสกุล</label>
    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="นามสกุล" required>
    <label>เบอร์โทรศัพท์</label>
    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0XX-XXX-XXXX" required>
    <label>รหัสผ่าน</label>
    <input type="password" name="password" placeholder="รหัสผ่าน" required autocomplete="new-password">
    <label>ยืนยันรหัสผ่าน</label>
    <input type="password" name="password_confirmation" placeholder="ยืนยันรหัสผ่าน" required autocomplete="new-password">
    <button type="submit" class="btn-primary full">สมัครสมาชิก</button>
  </form>
  <p class="auth-link">มีบัญชีแล้ว? <a href="{{ route('login') }}">เข้าสู่ระบบ</a></p>
</div>
</body>
</html>
