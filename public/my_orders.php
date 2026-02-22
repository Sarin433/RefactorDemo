<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}
include __DIR__ . '/config.php';

$email = $_SESSION['user_email'];
$created = isset($_GET['created']) ? $_GET['created'] : '';

$sql = "SELECT o.order_number, o.order_date, o.shipping_address, s.status_name, s.status_id
        FROM Orders o
        JOIN Status_Reference s ON o.status_id = s.status_id
        WHERE o.user_email='" . $email . "'
        ORDER BY o.order_date DESC";
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
<title>คำสั่งซื้อของฉัน - OMS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="nav-left">
    <div class="nav-logo"><span class="logo-icon">OMS</span></div>
    <a href="index.php" class="nav-tab">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      สินค้า
    </a>
    <a href="my_orders.php" class="nav-tab active">
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

<div class="container">
  <div class="page-header">
    <div>
      <h1>คำสั่งซื้อของฉัน</h1>
      <p class="subtitle">รายการคำสั่งซื้อทั้งหมดของคุณ</p>
    </div>
  </div>

  <?php if ($created): ?>
  <div class="alert-success">
    สร้างคำสั่งซื้อ <strong><?php echo htmlspecialchars($created); ?></strong> เรียบร้อยแล้ว!
  </div>
  <?php endif; ?>

  <?php if (count($orders) == 0): ?>
    <div class="empty-state">ยังไม่มีคำสั่งซื้อ <a href="index.php">เลือกสินค้า</a></div>
  <?php endif; ?>

  <?php foreach ($orders as $o): ?>
  <?php
    $dq = "SELECT od.product_number, od.quantity, p.name, p.price
           FROM Order_Details od
           JOIN Products p ON od.product_number = p.product_number
           WHERE od.order_number='" . $o['order_number'] . "'";
    $dr = mysqli_query($conn, $dq);
    $details = array();
    $grand = 0;
    while ($dd = mysqli_fetch_assoc($dr)) {
        $dd['subtotal'] = $dd['price'] * $dd['quantity'];
        $grand += $dd['subtotal'];
        $details[] = $dd;
    }
  ?>
  <div class="order-card">
    <div class="order-header">
      <div>
        <span class="order-number"><?php echo htmlspecialchars($o['order_number']); ?></span>
        <span class="order-date"><?php echo $o['order_date']; ?></span>
      </div>
      <span class="status-badge status-<?php echo $o['status_id']; ?>">
        <?php echo htmlspecialchars($o['status_name']); ?>
      </span>
    </div>

    <table class="order-table">
      <thead>
        <tr><th>รหัสสินค้า</th><th>ชื่อสินค้า</th><th>ราคา/ชิ้น</th><th>จำนวน</th><th>ราคารวม</th><th>แก้ไข</th></tr>
      </thead>
      <tbody>
        <?php foreach ($details as $d): ?>
        <tr>
          <td><?php echo htmlspecialchars($d['product_number']); ?></td>
          <td><?php echo htmlspecialchars($d['name']); ?></td>
          <td>฿<?php echo number_format($d['price'], 0); ?></td>
          <td><?php echo $d['quantity']; ?></td>
          <td>฿<?php echo number_format($d['subtotal'], 0); ?></td>
          <td>
            <?php if ($o['status_id'] == 1): ?>
            <form method="post" action="actions/edit_order_action.php" class="inline-form">
              <input type="hidden" name="order_number" value="<?php echo $o['order_number']; ?>">
              <input type="hidden" name="product_number" value="<?php echo $d['product_number']; ?>">
              <input type="number" name="quantity" value="<?php echo $d['quantity']; ?>" min="0" class="qty-input">
              <button type="submit" class="btn-sm">บันทึก</button>
            </form>
            <?php else: ?>
            <span class="text-gray">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="4" class="text-right"><strong>ยอดรวม</strong></td><td colspan="2"><strong class="price-blue">฿<?php echo number_format($grand, 0); ?></strong></td></tr>
      </tfoot>
    </table>

    <?php if ($o['status_id'] == 1): ?>
    <div class="confirm-section">
      <form method="post" action="actions/confirm_order_action.php">
        <input type="hidden" name="order_number" value="<?php echo $o['order_number']; ?>">
        <label>ที่อยู่จัดส่ง</label>
        <textarea name="shipping_address" rows="2" placeholder="กรอกที่อยู่สำหรับจัดส่ง..."><?php echo htmlspecialchars($o['shipping_address'] ?? ''); ?></textarea>
        <button type="submit" class="btn-primary">ยืนยันคำสั่งซื้อ</button>
      </form>
    </div>
    <?php else: ?>
      <?php if ($o['shipping_address']): ?>
      <div class="shipping-info"><strong>ที่อยู่:</strong> <?php echo htmlspecialchars($o['shipping_address']); ?></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>
