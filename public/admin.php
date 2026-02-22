<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/config.php';

$kw = isset($_GET['q']) ? $_GET['q'] : '';

if ($kw != '') {
    $sql = "SELECT DISTINCT o.order_number, o.order_date, o.shipping_address, s.status_name, s.status_id, u.first_name, u.last_name, u.email
            FROM Orders o
            JOIN Users u ON o.user_email = u.email
            JOIN Status_Reference s ON o.status_id = s.status_id
            WHERE o.order_number LIKE '%" . $kw . "%'
               OR u.first_name LIKE '%" . $kw . "%'
               OR u.last_name LIKE '%" . $kw . "%'
            ORDER BY o.order_date DESC";
} else {
    $sql = "SELECT o.order_number, o.order_date, o.shipping_address, s.status_name, s.status_id, u.first_name, u.last_name, u.email
            FROM Orders o
            JOIN Users u ON o.user_email = u.email
            JOIN Status_Reference s ON o.status_id = s.status_id
            ORDER BY o.order_date DESC";
}
$r = mysqli_query($conn, $sql);
$orders = array();
while ($row = mysqli_fetch_assoc($r)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - OMS</title>
<link rel="stylesheet" href="assets/style.css">
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
    <span class="nav-username"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
    <a href="actions/logout_action.php" class="btn-logout">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      ออกจากระบบ
    </a>
  </div>
</nav>

<div class="container">
  <div class="page-header">
    <div>
      <h1>จัดการคำสั่งซื้อ</h1>
      <p class="subtitle">ค้นหาและอัปเดตสถานะคำสั่งซื้อของลูกค้า</p>
    </div>
  </div>

  <!-- Search -->
  <form method="get" action="admin.php" class="admin-search-form">
    <div class="search-box">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?php echo htmlspecialchars($kw); ?>" placeholder="ค้นหาด้วยเลข Order หรือชื่อลูกค้า...">
    </div>
    <button type="submit" class="btn-primary">ค้นหา</button>
    <?php if ($kw): ?><a href="admin.php" class="btn-secondary">ล้าง</a><?php endif; ?>
  </form>

  <!-- Bulk Update Form -->
  <form method="post" action="actions/bulk_update_action.php" id="bulkForm">
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
        <?php if (count($orders) == 0): ?>
        <tr><td colspan="7" class="text-center text-gray">ไม่พบคำสั่งซื้อ</td></tr>
        <?php endif; ?>
        <?php foreach ($orders as $o): ?>
        <?php
          $dq = "SELECT od.product_number, od.quantity, p.name, p.price
                 FROM Order_Details od
                 JOIN Products p ON od.product_number = p.product_number
                 WHERE od.order_number='" . $o['order_number'] . "'";
          $dr = mysqli_query($conn, $dq);
          $details = array();
          while ($dd = mysqli_fetch_assoc($dr)) {
              $details[] = $dd;
          }
        ?>
        <tr class="order-row" data-order="<?php echo $o['order_number']; ?>">
          <td>
            <input type="checkbox" name="selected_orders[]"
                   value="<?php echo $o['order_number']; ?>"
                   class="row-check"
                   onchange="updateCount()">
          </td>
          <td><strong><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
          <td><?php echo htmlspecialchars($o['first_name'] . ' ' . $o['last_name']); ?><br><small class="text-gray"><?php echo htmlspecialchars($o['email']); ?></small></td>
          <td><?php echo $o['order_date']; ?></td>
          <td><span class="status-badge status-<?php echo $o['status_id']; ?>"><?php echo htmlspecialchars($o['status_name']); ?></span></td>
          <td>
            <?php foreach ($details as $d): ?>
            <div class="detail-row">
              <span class="sku-badge"><?php echo $d['product_number']; ?></span>
              <?php echo htmlspecialchars($d['name']); ?>
              × <?php echo $d['quantity']; ?>
              (฿<?php echo number_format($d['price'] * $d['quantity'], 0); ?>)
            </div>
            <?php endforeach; ?>
          </td>
          <td><?php echo $o['shipping_address'] ? htmlspecialchars($o['shipping_address']) : '<span class="text-gray">-</span>'; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </form>
</div>

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
</body>
</html>
