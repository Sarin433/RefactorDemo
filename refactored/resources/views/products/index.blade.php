@extends('layouts.app')

@section('title', 'รายการสินค้า - OMS')

@section('content')
  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1>รายการสินค้า</h1>
      <p class="subtitle">เลือกสินค้าแล้วกด 'สร้าง Order'</p>
    </div>
    <div class="header-actions">
      <button class="btn-primary" id="btnCreateOrder" onclick="submitOrder()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/></svg>
        สร้าง Order
        <span class="cart-badge" id="cartBadge" style="display:none;">0</span>
      </button>
      <div class="search-box">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="ค้นหาสินค้า..." oninput="filterProducts()">
      </div>
    </div>
  </div>

  <!-- Cart Banner -->
  <div class="cart-banner" id="cartBanner" style="display:none;">
    <span id="bannerText">เลือกไว้ 0 ชิ้น · ยอดรวม ฿0</span>
    <button class="btn-checkout" onclick="submitOrder()">ดำเนินการสั่งซื้อ &#x2192;</button>
  </div>

  @if ($errors->any())
  <div class="alert-error" style="margin-bottom:16px;">
    @foreach ($errors->all() as $error)
      <div>{{ $error }}</div>
    @endforeach
  </div>
  @endif

  <!-- Product Grid -->
  <div class="product-grid" id="productGrid">
    @foreach ($products as $p)
    <div class="product-card {{ $p->stock_quantity == 0 ? 'out-of-stock' : '' }}"
         data-sku="{{ $p->product_number }}"
         data-name="{{ $p->name }}"
         data-price="{{ $p->price }}"
         data-stock="{{ $p->stock_quantity }}">

      <div class="card-top">
        <span class="sku-badge">{{ $p->product_number }}</span>
        @if ($p->stock_quantity == 0)
          <span class="out-badge">สินค้าหมด</span>
        @endif
      </div>

      <div class="product-name">{{ $p->name }}</div>
      <div class="product-price">฿{{ number_format($p->price, 0) }}</div>
      <div class="product-stock">คงเหลือ {{ $p->stock_quantity }} ชิ้น</div>

      @if ($p->stock_quantity == 0)
        <button class="btn-add disabled" disabled>+ เพิ่มในตะกร้า</button>
      @else
        <button class="btn-add" onclick="addToCart('{{ $p->product_number }}', this)">+ เพิ่มในตะกร้า</button>
        <div class="stepper" style="display:none;">
          <button class="step-btn" onclick="changeQty('{{ $p->product_number }}', -1, this)">−</button>
          <span class="step-qty" id="qty-{{ $p->product_number }}">1</span>
          <button class="step-btn" onclick="changeQty('{{ $p->product_number }}', 1, this)">+</button>
        </div>
      @endif
    </div>
    @endforeach
  </div>

  <!-- Hidden Order Form with CSRF -->
  <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
    @csrf
    <div id="orderItems"></div>
  </form>
@endsection

@push('scripts')
<script>
var cart = {};

function addToCart(sku, btn) {
  var card = btn.closest('.product-card');
  var price = parseFloat(card.dataset.price);
  var name  = card.dataset.name;
  cart[sku] = { qty: 1, price: price, name: name };
  btn.style.display = 'none';
  card.querySelector('.stepper').style.display = 'flex';
  document.getElementById('qty-' + sku).innerText = 1;
  updateBanner();
}

function changeQty(sku, delta, btn) {
  if (!cart[sku]) return;
  cart[sku].qty += delta;
  if (cart[sku].qty <= 0) {
    delete cart[sku];
    var card = btn.closest('.product-card');
    card.querySelector('.stepper').style.display = 'none';
    card.querySelector('.btn-add').style.display = 'block';
  } else {
    document.getElementById('qty-' + sku).innerText = cart[sku].qty;
  }
  updateBanner();
}

function updateBanner() {
  var total = 0, count = 0;
  for (var k in cart) {
    total += cart[k].price * cart[k].qty;
    count++;
  }
  var badge = document.getElementById('cartBadge');
  badge.innerText = count;
  badge.style.display = count > 0 ? 'inline-flex' : 'none';

  var banner = document.getElementById('cartBanner');
  if (count > 0) {
    banner.style.display = 'flex';
    var totalQty = 0;
    for (var k in cart) totalQty += cart[k].qty;
    document.getElementById('bannerText').innerHTML =
      'เลือกไว้ <strong>' + totalQty + ' ชิ้น</strong> · ยอดรวม <strong class="price-blue">฿' + total.toLocaleString() + '</strong>';
  } else {
    banner.style.display = 'none';
  }
}

function submitOrder() {
  if (Object.keys(cart).length === 0) {
    alert('กรุณาเลือกสินค้าก่อน');
    return;
  }
  var form = document.getElementById('orderForm');
  var items = document.getElementById('orderItems');
  items.innerHTML = '';
  for (var k in cart) {
    items.innerHTML += '<input type="hidden" name="product_number[]" value="' + k + '">';
    items.innerHTML += '<input type="hidden" name="quantity[]" value="' + cart[k].qty + '">';
  }
  form.submit();
}

function filterProducts() {
  var q = document.getElementById('searchInput').value.toLowerCase();
  var cards = document.querySelectorAll('.product-card');
  cards.forEach(function(c) {
    var name = c.dataset.name.toLowerCase();
    var sku  = c.dataset.sku.toLowerCase();
    c.style.display = (name.includes(q) || sku.includes(q)) ? 'flex' : 'none';
  });
}
</script>
@endpush
