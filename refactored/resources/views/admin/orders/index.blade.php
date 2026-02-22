@extends('layouts.admin')

@section('title', 'Admin Dashboard - OMS')

@section('content')
  <div class="page-header">
    <div>
      <h1>จัดการคำสั่งซื้อ</h1>
      <p class="subtitle">ค้นหาและอัปเดตสถานะคำสั่งซื้อของลูกค้า</p>
    </div>
  </div>

  <!-- Search -->
  <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-search-form">
    <div class="search-box">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="{{ $kw }}" placeholder="ค้นหาด้วยเลข Order หรือชื่อลูกค้า...">
    </div>
    <button type="submit" class="btn-primary">ค้นหา</button>
    @if ($kw)
      <a href="{{ route('admin.orders.index') }}" class="btn-secondary">ล้าง</a>
    @endif
  </form>

  @if ($errors->any())
  <div class="alert-error" style="margin-bottom:12px;">
    @foreach ($errors->all() as $error)
      <div>{{ $error }}</div>
    @endforeach
  </div>
  @endif

  <!-- Bulk Update Form -->
  <form method="POST" action="{{ route('admin.orders.bulk') }}" id="bulkForm">
    @csrf
    <div class="admin-toolbar">
      <span class="selected-count" id="selectedCount">เลือก 0 รายการ</span>
      <button type="submit" class="btn-primary" onclick="return confirm('ยืนยันการเปลี่ยนสถานะที่เลือกทั้งหมด?')">
        ✓ ยืนยันที่เลือก
      </button>
    </div>

    <table class="admin-table">
      <thead>
        <tr>
          <th><input type="checkbox" id="masterCheck" onclick="toggleAll(this)"></th>
          <th>เลขคำสั่งซื้อ</th>
          <th>ลูกค้า</th>
          <th>วันที่</th>
          <th>สถานะ</th>
          <th>รายการสินค้า</th>
          <th>ที่อยู่จัดส่ง</th>
        </tr>
      </thead>
      <tbody>
        @if ($orders->isEmpty())
        <tr><td colspan="7" class="text-center text-gray">ไม่พบคำสั่งซื้อ</td></tr>
        @endif
        @foreach ($orders as $o)
        <tr class="order-row" data-order="{{ $o->order_number }}">
          <td>
            <input type="checkbox" name="selected_orders[]"
                   value="{{ $o->order_number }}"
                   class="row-check"
                   onchange="updateCount()">
          </td>
          <td><strong>{{ $o->order_number }}</strong></td>
          <td>
            {{ $o->user->first_name . ' ' . $o->user->last_name }}
            <br><small class="text-gray">{{ $o->user->email }}</small>
          </td>
          <td>{{ $o->order_date->format('Y-m-d H:i') }}</td>
          <td><span class="status-badge status-{{ $o->status_id }}">{{ $o->status->status_name }}</span></td>
          <td>
            @foreach ($o->orderDetails as $d)
            <div class="detail-row">
              <span class="sku-badge">{{ $d->product_number }}</span>
              {{ $d->product->name }}
              × {{ $d->quantity }}
              (฿{{ number_format($d->unit_price * $d->quantity, 0) }})
            </div>
            @endforeach
          </td>
          <td>
            @if ($o->shipping_address)
              {{ $o->shipping_address }}
            @else
              <span class="text-gray">-</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </form>
@endsection

@push('scripts')
<script>
function toggleAll(master) {
  var checks = document.querySelectorAll('.row-check');
  checks.forEach(function(c) {
    c.checked = master.checked;
    highlightRow(c);
  });
  updateCount();
}

function updateCount() {
  var checks = document.querySelectorAll('.row-check:checked');
  document.getElementById('selectedCount').innerText = 'เลือก ' + checks.length + ' รายการ';
  document.querySelectorAll('.row-check').forEach(function(c) { highlightRow(c); });
}

function highlightRow(checkbox) {
  var row = checkbox.closest('tr');
  if (checkbox.checked) {
    row.classList.add('row-selected');
  } else {
    row.classList.remove('row-selected');
  }
}
</script>
@endpush
