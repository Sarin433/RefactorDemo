@extends('layouts.app')

@section('title', 'คำสั่งซื้อของฉัน - OMS')

@section('content')
  <div class="page-header">
    <div>
      <h1>คำสั่งซื้อของฉัน</h1>
      <p class="subtitle">รายการคำสั่งซื้อทั้งหมดของคุณ</p>
    </div>
  </div>

  @if ($created)
  <div class="alert-success">
    สร้างคำสั่งซื้อ <strong>{{ $created }}</strong> เรียบร้อยแล้ว!
  </div>
  @endif

  @if ($errors->any())
  <div class="alert-error">
    @foreach ($errors->all() as $error)
      <div>{{ $error }}</div>
    @endforeach
  </div>
  @endif

  @if ($orders->isEmpty())
    <div class="empty-state">ยังไม่มีคำสั่งซื้อ <a href="{{ route('products.index') }}">เลือกสินค้า</a></div>
  @endif

  @foreach ($orders as $o)
  @php
    $grand = $o->orderDetails->sum('subtotal');
  @endphp
  <div class="order-card">
    <div class="order-header">
      <div>
        <span class="order-number">{{ $o->order_number }}</span>
        <span class="order-date">{{ $o->order_date->format('Y-m-d H:i:s') }}</span>
      </div>
      <span class="status-badge status-{{ $o->status_id }}">
        {{ $o->status->status_name }}
      </span>
    </div>

    <table class="order-table">
      <thead>
        <tr><th>รหัสสินค้า</th><th>ชื่อสินค้า</th><th>ราคา/ชิ้น</th><th>จำนวน</th><th>ราคารวม</th><th>แก้ไข</th></tr>
      </thead>
      <tbody>
        @foreach ($o->orderDetails as $d)
        <tr>
          <td>{{ $d->product_number }}</td>
          <td>{{ $d->product->name }}</td>
          <td>฿{{ number_format($d->unit_price, 0) }}</td>
          <td>{{ $d->quantity }}</td>
          <td>฿{{ number_format($d->subtotal, 0) }}</td>
          <td>
            @if ($o->isPending())
            <form method="POST" action="{{ route('orders.updateDetail', $o->order_number) }}" class="inline-form">
              @csrf
              <input type="hidden" name="product_number" value="{{ $d->product_number }}">
              <input type="number" name="quantity" value="{{ $d->quantity }}" min="0" class="qty-input">
              <button type="submit" class="btn-sm">บันทึก</button>
            </form>
            @else
            <span class="text-gray">-</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr><td colspan="4" class="text-right"><strong>ยอดรวม</strong></td><td colspan="2"><strong class="price-blue">฿{{ number_format($grand, 0) }}</strong></td></tr>
      </tfoot>
    </table>

    @if ($o->isPending())
    <div class="confirm-section">
      <form method="POST" action="{{ route('orders.saveAddress', $o->order_number) }}">
        @csrf
        <label>ที่อยู่จัดส่ง</label>
        <textarea name="shipping_address" rows="2" placeholder="กรอกที่อยู่สำหรับจัดส่ง...">{{ $o->shipping_address }}</textarea>
        <button type="submit" class="btn-primary">บันทึกที่อยู่</button>
      </form>
    </div>
    @else
      @if ($o->shipping_address)
      <div class="shipping-info"><strong>ที่อยู่:</strong> {{ $o->shipping_address }}</div>
      @endif
    @endif
  </div>
  @endforeach
@endsection
