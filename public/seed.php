<?php
$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM Status_Reference");
$d = mysqli_fetch_assoc($r);
if ($d['c'] == 0) {
    mysqli_query($conn, "INSERT INTO Status_Reference (status_name) VALUES ('รอยืนยันคำสั่งซื้อ')");
    mysqli_query($conn, "INSERT INTO Status_Reference (status_name) VALUES ('ยืนยันคำสั่งซื้อ')");
}

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM Products");
$d = mysqli_fetch_assoc($r);
if ($d['c'] == 0) {
    $products = array(
        array('SKU-001', 'แล็บท็อป Asus VivoBook 15', 18900, 13),
        array('SKU-002', 'เมาส์ Logitech MX Master 3', 3200, 48),
        array('SKU-003', 'คีย์บอร์ด Keychron K2 Wireless', 2800, 30),
        array('SKU-004', 'จอมอนิเตอร์ Dell 24 นิ้ว FHD', 5500, 12),
        array('SKU-005', 'หูฟัง Sony WH-1000XM5', 9800, 20),
        array('SKU-006', 'เว็บแคม Logitech C920', 2100, 0),
        array('SKU-007', 'SSD Samsung 1TB NVMe', 2900, 45),
        array('SKU-008', 'กระเป๋าโน้ตบุ๊ค 15.6 นิ้ว', 850, 60),
    );
    foreach ($products as $p) {
        $sql = "INSERT INTO Products (product_number, name, price, stock_quantity) VALUES ('" . $p[0] . "', '" . $p[1] . "', " . $p[2] . ", " . $p[3] . ")";
        mysqli_query($conn, $sql);
    }
}
