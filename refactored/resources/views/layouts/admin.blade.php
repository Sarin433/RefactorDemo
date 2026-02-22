<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin - OMS')</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
@stack('styles')
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-left">
    <div class="nav-logo"><span class="logo-icon">OMS</span></div>
    <span class="nav-title">Admin Dashboard</span>
  </div>
  <div class="nav-right">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span class="nav-username">{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}</span>
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
      @csrf
      <button type="submit" class="btn-logout">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        ออกจากระบบ
      </button>
    </form>
  </div>
</nav>

<!-- Content -->
<div class="container">
  @yield('content')
</div>

@stack('scripts')
</body>
</html>
