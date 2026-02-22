<?php
session_start();
include __DIR__ . '/config.php';
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

$search = isset($_GET['s']) ? $_GET['s'] : '';
$sql = "SELECT * FROM Products";
$r = mysqli_query($conn, $sql);
$products = array();
while ($p = mysqli_fetch_assoc($r)) {
    $products[] = $p;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายการสินค้า - OMS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-left">
    <div class="nav-logo"><span class="logo-icon">OMS</span></div>
    <a href="index.php" class="nav-tab active">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      สินค้า
    </a>
    <a href="my_orders.php" class="nav-tab">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
      คำสั่งซื้อของฉัน
    </a>
  </div>
  <div class="nav-right">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span class="nav-username"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
    <a href="actions/logout_action.php" class="btn-logout">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      ออกจากระบบ
    </a>
  </div>
</nav>

<!-- Content -->
<div class="container">
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
        <span class="cart-badge" id="cartBadge">0</span>
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

  <!-- Product Grid -->
  <div class="product-grid" id="productGrid">
    <?php foreach ($products as $p): ?>
    <div class="product-card <?php echo $p['stock_quantity'] == 0 ? 'out-of-stock' : ''; ?>"
         data-sku="<?php echo $p['product_number']; ?>"
         data-name="<?php echo htmlspecialchars($p['name']); ?>"
         data-price="<?php echo $p['price']; ?>"
         data-stock="<?php echo $p['stock_quantity']; ?>">

      <div class="card-top">
        <span class="sku-badge"><?php echo $p['product_number']; ?></span>
        <?php if ($p['stock_quantity'] == 0): ?>
          <span class="out-badge">สินค้าหมด</span>
        <?php endif; ?>
      </div>

      <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
      <div class="product-price">฿<?php echo number_format($p['price'], 0); ?></div>
      <div class="product-stock">คงเหลือ <?php echo $p['stock_quantity']; ?> ชิ้น</div>

      <?php if ($p['stock_quantity'] == 0): ?>
        <button class="btn-add disabled" disabled>+ เพิ่มในตะกร้า</button>
      <?php else: ?>
        <button class="btn-add" onclick="addToCart('<?php echo $p['product_number']; ?>', this)">+ เพิ่มในตะกร้า</button>
        <div class="stepper" style="display:none;">
          <button class="step-btn" onclick="changeQty('<?php echo $p['product_number']; ?>', -1, this)">−</button>
          <span class="step-qty" id="qty-<?php echo $p['product_number']; ?>">1</span>
          <button class="step-btn" onclick="changeQty('<?php echo $p['product_number']; ?>', 1, this)">+</button>
        </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Hidden Order Form -->
<form method="post" action="actions/add_order_action.php" id="orderForm">
  <div id="orderItems"></div>
</form>

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
</body>
</html>
