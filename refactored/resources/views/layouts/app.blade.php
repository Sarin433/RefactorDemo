<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'OMS')</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
@stack('styles')
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-left">
    <div class="nav-logo"><span class="logo-icon">OMS</span></div>
    <a href="{{ route('products.index') }}" class="nav-tab {{ Request::routeIs('products.*') ? 'active' : '' }}">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      สินค้า
    </a>
    <a href="{{ route('orders.index') }}" class="nav-tab {{ Request::routeIs('orders.*') ? 'active' : '' }}">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
      คำสั่งซื้อของฉัน
    </a>
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
