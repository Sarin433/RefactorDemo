# Plan: PHP 7 Legacy OMS Web Application (UI Updated)

ระบบ OMS Monolithic PHP 7 + MySQL รันผ่าน Docker Compose เขียนแบบ legacy/bad code ตาม copilot-instructions — UI ต้องตรงกับภาพตัวอย่างทุกรายละเอียด

---

## โครงสร้างไฟล์

```
RefactorDemo/
├── Dockerfile
├── docker-compose.yml
├── db/init.sql
└── public/
    ├── config.php
    ├── seed.php
    ├── index.php
    ├── login.php
    ├── register.php
    ├── my_orders.php
    ├── admin.php
    ├── actions/
    │   ├── login_action.php
    │   ├── logout_action.php
    │   ├── register_action.php
    │   ├── add_order_action.php
    │   ├── edit_order_action.php
    │   ├── confirm_order_action.php
    │   └── bulk_update_action.php
    └── assets/style.css
```

---

## Steps

### Step 1 — Docker Setup
- `Dockerfile` ใช้ `php:7.4-apache` + ติดตั้ง `mysqli` extension
- `docker-compose.yml`:
  - service `app`: port 8080:80, build จาก Dockerfile, mount `./public:/var/www/html`
  - service `db`: mysql:5.7, env vars (`MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`), mount `./db/init.sql:/docker-entrypoint-initdb.d/init.sql`
  - shared network ให้ `app` connect ไปที่ host `db`

### Step 2 — Database Init
- `db/init.sql` CREATE TABLE ทั้ง 5 ตาราง:
  - `Users` (user_id PK, email UNIQUE, first_name, last_name, phone, password, role)
  - `Products` (product_number PK VARCHAR(50), name, price DECIMAL(10,2), stock_quantity)
  - `Status_Reference` (status_id PK, status_name UNIQUE)
  - `Orders` (order_number PK VARCHAR(50), user_email FK, status_id FK, shipping_address TEXT NULLABLE, order_date TIMESTAMP)
  - `Order_Details` (detail_id PK AUTO_INCREMENT, order_number FK, product_number FK, quantity INT)

### Step 3 — config.php + seed.php
- `config.php`: `mysqli_connect('db', ...)` แบบตรงๆ + `include 'seed.php'`
- `seed.php`: `SELECT COUNT(*)` ตรวจสอบ Products และ Status_Reference — หากว่างให้ INSERT ด้วย SQL string concatenation ตรงๆ:
  - Status: `{1, 'รอยืนยันคำสั่งซื้อ'}`, `{2, 'ยืนยันคำสั่งซื้อ'}`
  - Products 8 รายการ: SKU-001..SKU-008 ตาม requirements

### Step 4 — Auth Pages (Functions 2–3)
- `register.php`: ฟอร์ม 6 ฟิลด์ (Email, First Name, Last Name, Phone, Password, ConfirmPassword)
- `actions/register_action.php`: รับ `$_POST` → validate password match → INSERT ด้วย string concat (plain text password, ไม่มี prepared statement)
- `login.php`: ฟอร์ม email + password
- `actions/login_action.php`: `SELECT * FROM Users WHERE email='$email' AND password='$password'` → `session_start()` → set `$_SESSION['user_email']`, `$_SESSION['role']`, `$_SESSION['first_name']`, `$_SESSION['last_name']`
- `actions/logout_action.php`: `session_destroy()` + redirect login.php

### Step 5 — หน้าหลัก index.php — UI ตามภาพ (ฟังก์ชันที่ 5)

**Navbar** (bg ขาว, border-bottom เทาอ่อน):
- ซ้าย: icon สี่เหลี่ยมน้ำเงิน + "OMS" → แท็บ **"สินค้า"** (active: bg `#EEF2FF`, text `#3730A3`, icon ถุงช้อปปิ้ง) → แท็บ **"คำสั่งซื้อของฉัน"** (inactive: text เทา, icon list)
- ขวา: icon คน + `$_SESSION['first_name'] $_SESSION['last_name']` → ปุ่ม "→ ออกจากระบบ"

**Content Header**:
- ซ้าย: `<h1>` "รายการสินค้า" + `<p>` "เลือกสินค้าแล้วกด 'สร้าง Order'"
- ขวา: ปุ่มน้ำเงิน `🛒 สร้าง Order` + badge วงกลมขาวแสดงจำนวน SKU + Search input "ค้นหาสินค้า..."

**Cart Banner** (แสดงเมื่อ cart ไม่ว่าง, bg `#EEF2FF`, border `#C7D2FE`, rounded):
- ซ้าย: "เลือกไว้ **N ชิ้น** · ยอดรวม ฿XX,XXX"
- ขวา: ปุ่มน้ำเงินเข้ม "ดำเนินการสั่งซื้อ →"

**Product Grid** (`display: grid`, `grid-template-columns: repeat(4, 1fr)`, gap 16px):

แต่ละ card (bg ขาว, `border: 1px solid #E5E7EB`, `border-radius: 8px`, padding 16px):
- SKU badge: bg `#F3F4F6`, text เทา, font-size 12px, border-radius 4px
- Product name: font-weight 600, สีเทาเข้ม
- ราคา: `฿XX,XXX` สี `#1D4ED8`, font-size ใหญ่สุด, font-weight bold
- Stock: "คงเหลือ X ชิ้น" สี `#6B7280`, font-size 13px

**3 สถานะของปุ่มล่างการ์ด:**
1. **Default**: ปุ่ม `+ เพิ่มในตะกร้า` bg `#2563EB`, text ขาว, เต็มความกว้าง, border-radius 6px
2. **Out-of-stock** (stock=0): badge "สินค้าหมด" สี `#EF4444` มุมขวาบน + ปุ่มสีจาง `#93C5FD`, disabled, cursor not-allowed
3. **Selected** (JS toggle): Quantity Stepper `[ − ][ qty ][ + ]` — bg ขาว, border เทา, border-radius 6px

**Cart Logic (JavaScript inline ใน index.php)**:
- `cart = {}` เก็บ `{sku: {qty, price, name}}`
- กด "+ เพิ่มในตะกร้า" → สลับเป็น stepper + อัปเดต badge count + แสดง/อัปเดต banner
- กด [-] จนถึง 0 → ลบออกจาก cart + สลับกลับเป็นปุ่มเพิ่ม
- กด "ดำเนินการสั่งซื้อ" → populate hidden form (`product_number[]`, `quantity[]`) + submit ไป `actions/add_order_action.php`
- Search input ทำ JS filter กรอง card ตาม name/sku แบบ real-time

**actions/add_order_action.php**:
1. Generate `ORD-YYYYMMDD-XXXX` (timestamp + rand)
2. INSERT into Orders (order_number, user_email จาก Session, status_id=1)
3. Loop INSERT into Order_Details แต่ละ product_number + quantity
4. Redirect หรือแสดง order number

### Step 6 — my_orders.php (Functions 6–7)
- ดึง Orders JOIN Order_Details JOIN Products JOIN Status_Reference ของ `$_SESSION['user_email']`
- แสดงตาราง: order_number, order_date, status_name, รายการสินค้า (แบบ nested loop)
- ฟอร์มแก้ไขจำนวน → `actions/edit_order_action.php`: `UPDATE Order_Details SET quantity=$q WHERE order_number='$o' AND product_number='$p'` (ถ้า qty=0 ให้ DELETE)
- ฟอร์มยืนยัน shipping address → `actions/confirm_order_action.php`: `UPDATE Orders SET shipping_address='$addr' WHERE order_number='$o'`

### Step 7 — admin.php (Functions 4 & 8)
- ตรวจ `$_SESSION['role'] == 'admin'` ถ้าไม่ใช่ redirect
- Search bar: `SELECT ... FROM Orders o JOIN Users u ON o.user_email=u.email LEFT JOIN Order_Details od ... WHERE o.order_number LIKE '%$kw%' OR u.first_name LIKE '%$kw%' OR u.last_name LIKE '%$kw%'` (string concat ตรงๆ)
- แสดงตาราง: คอลัมน์แรก checkbox `<input type="checkbox" name="selected_orders[]" value="OrderNumber">`, order_number, ชื่อลูกค้า, สถานะ, รายการสินค้า (nested loop)
- Master checkbox Select All ด้วย JS
- แถวที่ checkbox ถูกเลือก → highlight bg อ่อน (JS)
- ปุ่ม "ยืนยันที่เลือก" → submit ไป `actions/bulk_update_action.php`

**actions/bulk_update_action.php**:
```php
$ids = implode("','", $_POST['selected_orders']);
$sql = "UPDATE Orders SET status_id = 2 WHERE order_number IN ('$ids')";
mysqli_query($conn, $sql);
```

### Step 8 — style.css
กำหนด CSS:
- Color tokens: `--blue-primary: #2563EB`, `--blue-light: #EEF2FF`, `--blue-dark: #1D4ED8`
- Navbar: flex, justify-between, align-center, border-bottom
- Tab active/inactive state
- Card grid: 4 columns responsive
- Card states: default / out-of-stock (badge + disabled btn) / selected (stepper)
- Cart banner: flex, bg `#EEF2FF`, border `#C7D2FE`
- Quantity stepper: flex, border เทา, rounded
- Admin table: border-collapse, checkbox column, row highlight on check

---

## ลักษณะ "bad code" ที่พึงประสงค์ตาม spec

- SQL queries ต่อ string ตรงๆ ทุกที่ (เปิด SQL Injection)
- Password เก็บเป็น plain text
- Business logic, HTML output, และ DB query อยู่ในไฟล์เดียวกัน
- ไม่มี input validation ที่จริงจัง
- `include 'config.php'` global ทุกไฟล์
- ตัวแปรชื่อสั้น/ไม่สื่อความหมาย เช่น `$r`, `$q`, `$d`, `$conn`

---

## Verification

- `docker-compose up --build` → http://localhost:8080 → redirect ไป login.php
- Register user → Login → หน้า Product Grid ตรงตามภาพ (8 สินค้า, SKU-006 แสดง "สินค้าหมด")
- กด "+ เพิ่มในตะกร้า" SKU-001 → stepper โผล่ + banner อัปเดตเป็น "เลือกไว้ 1 ชิ้น · ยอดรวม ฿18,900"
- กด "ดำเนินการสั่งซื้อ" → สร้าง order สำเร็จ
- ไปที่ "คำสั่งซื้อของฉัน" → เห็น order + แก้ไข/ยืนยัน
- Login admin → admin.php → ค้นหา order → เลือก checkbox → bulk update status

## Decisions

- Cart state: JavaScript object ใน `<script>` inline ใน index.php (ไม่ใช้ Session/AJAX — ตามสไตล์ legacy)
- Raw CSS ใน style.css — ไม่ใช้ CSS framework เพื่อให้ควบคุม UI ตามภาพได้ตรง
- Search สินค้าใน index.php: JS filter ตาม DOM (ไม่ reload หน้า)
- Admin account: INSERT ตรง DB หรือ register แล้วเปลี่ยน role ใน MySQL
- ใช้ `mysqli` ไม่ใช้ PDO (ตาม legacy style)
- init.sql mount เข้า MySQL container สำหรับ table structure เท่านั้น — seed data ใช้ seed.php ใน PHP runtime
