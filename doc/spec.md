# Product Requirements Document (PRD)
# ระบบจัดการคำสั่งซื้อ Order Management System (OMS)

---

## 1. ข้อมูลโครงการ (Project Information)

| รายการ | รายละเอียด |
|---|---|
| ชื่อระบบ | Order Management System (OMS) |
| ประเภท | Web Application (Server-Side Rendering) |
| เทคโนโลยี | PHP 7.x, MySQL, HTML/CSS |
| สถาปัตยกรรม | Monolithic — PHP รวม Logic + View ในไฟล์เดียวกัน ไม่แยก Frontend/Backend |
| ข้อจำกัด | ไม่ใช้ Framework (Laravel/Symfony), ไม่ใช้ ORM, ไม่ใช้ Parameterized Query |
| การจัดการ Session | PHP Native Session (`$_SESSION`) |
| Deployment | Docker (php:7.4-apache + MySQL) |

---

## 2. Business Requirement

บริษัทต้องการพัฒนาระบบ Web Application สำหรับจัดการคลังสินค้าและคำสั่งซื้อ (Order Management System) สำหรับร้านค้า E-commerce ขนาดเล็ก ระบบต้องรองรับ:

- การจัดการบัญชีผู้ใช้ (Register / Login)
- การแสดงรายการสินค้าพร้อมเลือกใส่ตะกร้า
- การสร้าง/แก้ไข/ยืนยันคำสั่งซื้อ
- หน้า Admin สำหรับจัดการและอัปเดตสถานะคำสั่งซื้อแบบ Bulk

---

## 3. User Roles

| Role | สิทธิ์การใช้งาน |
|---|---|
| **user** (ผู้ใช้ทั่วไป) | Register, Login, ดูสินค้า, สร้าง Order, แก้ไข Order, ยืนยัน Order, ดูคำสั่งซื้อของตัวเอง |
| **admin** (ผู้ดูแลระบบ) | Login, ค้นหาคำสั่งซื้อ, ดูรายละเอียด Order, Bulk Update สถานะคำสั่งซื้อ |

---

## 4. Database Schema

### 4.1 ตาราง `Users`

| Column | Type | Constraints | Description |
|---|---|---|---|
| user_id | INT | PRIMARY KEY, AUTO_INCREMENT | รหัสผู้ใช้ |
| email | VARCHAR(255) | UNIQUE, NOT NULL | อีเมล (ใช้เป็น User Account — ห้ามซ้ำ แต่ไม่ใช่ PK) |
| first_name | VARCHAR(100) | NOT NULL | ชื่อ |
| last_name | VARCHAR(100) | NOT NULL | นามสกุล |
| phone | VARCHAR(20) | NOT NULL | เบอร์โทร |
| password | VARCHAR(255) | NOT NULL | รหัสผ่าน (เก็บเป็น Plain Text) |
| role | VARCHAR(20) | DEFAULT 'user' | บทบาท ('user' หรือ 'admin') |

### 4.2 ตาราง `Products`

| Column | Type | Constraints | Description |
|---|---|---|---|
| product_number | VARCHAR(50) | PRIMARY KEY | รหัส SKU (เช่น SKU-001) |
| name | VARCHAR(255) | NOT NULL | ชื่อสินค้า |
| price | DECIMAL(10,2) | NOT NULL | ราคา (บาท) |
| stock_quantity | INT | NOT NULL | จำนวนคงเหลือ |

### 4.3 ตาราง `Status_Reference`

| Column | Type | Constraints | Description |
|---|---|---|---|
| status_id | INT | PRIMARY KEY, AUTO_INCREMENT | รหัสสถานะ |
| status_name | VARCHAR(100) | UNIQUE, NOT NULL | ชื่อสถานะ |

**ข้อมูลอ้างอิง:**

| status_id | status_name |
|---|---|
| 1 | รอยืนยันคำสั่งซื้อ |
| 2 | ยืนยันคำสั่งซื้อ |

### 4.4 ตาราง `Orders`

| Column | Type | Constraints | Description |
|---|---|---|---|
| order_number | VARCHAR(50) | PRIMARY KEY | เลขคำสั่งซื้อ (เช่น ORD-20260329-XXXX) |
| user_email | VARCHAR(255) | FK → Users.email, NOT NULL | อีเมลผู้สั่งซื้อ |
| status_id | INT | FK → Status_Reference.status_id, NOT NULL | สถานะคำสั่งซื้อ |
| shipping_address | TEXT | NULLABLE | ที่อยู่จัดส่ง (กรอกตอนยืนยัน) |
| order_date | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | วันที่สร้าง Order |

### 4.5 ตาราง `Order_Details`

| Column | Type | Constraints | Description |
|---|---|---|---|
| detail_id | INT | PRIMARY KEY, AUTO_INCREMENT | รหัสรายละเอียด |
| order_number | VARCHAR(50) | FK → Orders.order_number, NOT NULL | เลขคำสั่งซื้อ |
| product_number | VARCHAR(50) | FK → Products.product_number, NOT NULL | รหัสสินค้า |
| quantity | INT | NOT NULL | จำนวนที่สั่ง |

### ER Diagram (Text)

```
Users (1) ──────< Orders (1) ──────< Order_Details >────── Products
                     │
                     └── FK ──> Status_Reference
```

---

## 5. Functional Specifications

---

### F1: Seed Data (Auto)

**วัตถุประสงค์:** สร้างข้อมูลเริ่มต้นอัตโนมัติเมื่อ Collection ว่าง

**ไฟล์:** `public/seed.php`

**Logic:**
1. เชื่อมต่อ DB
2. Query `SELECT COUNT(*) FROM Products` — ถ้าเป็น 0 ให้ INSERT ข้อมูลสินค้า
3. Query `SELECT COUNT(*) FROM Status_Reference` — ถ้าเป็น 0 ให้ INSERT สถานะ

**ข้อมูล Seed — Products:**

| product_number | name | price | stock_quantity |
|---|---|---|---|
| SKU-001 | แล็ปท็อป Asus VivoBook 15 | 18900.00 | 13 |
| SKU-002 | เมาส์ Logitech MX Master 3 | 3200.00 | 48 |
| SKU-003 | คีย์บอร์ด Keychron K2 Wireless | 2800.00 | 30 |
| SKU-004 | จอมอนิเตอร์ Dell 24" FHD | 5500.00 | 12 |
| SKU-005 | หูฟัง Sony WH-1000XM5 | 9800.00 | 20 |
| SKU-006 | เว็บแคม Logitech C920 | 2100.00 | 0 |
| SKU-007 | SSD Samsung 1TB NVMe | 2900.00 | 45 |
| SKU-008 | กระเป๋าโน้ตบุ๊ค 15.6" | 850.00 | 60 |

**ข้อมูล Seed — Status_Reference:**

| status_id | status_name |
|---|---|
| 1 | รอยืนยันคำสั่งซื้อ |
| 2 | ยืนยันคำสั่งซื้อ |

---

### F2: Create User Account (Register)

**วัตถุประสงค์:** ลงทะเบียนผู้ใช้ใหม่

**ไฟล์:** `public/register.php` (แสดงฟอร์ม), `public/actions/register_action.php` (ประมวลผล)

**Input Fields:**

| Field | Type | Validation |
|---|---|---|
| Email | text/email | Required, ต้องไม่ซ้ำกับที่มีใน DB |
| ชื่อ (first_name) | text | Required |
| นามสกุล (last_name) | text | Required |
| เบอร์โทร (phone) | text | Required |
| Password | password | Required |
| ConfirmPassword | password | Required, ต้องตรงกับ Password |

**Processing Flow:**
1. รับข้อมูลจาก `$_POST`
2. ตรวจสอบว่า Password === ConfirmPassword
3. Query ตรวจสอบ Email ซ้ำ: `SELECT email FROM Users WHERE email = '$email'`
4. ถ้าไม่ซ้ำ: `INSERT INTO Users (email, first_name, last_name, phone, password, role) VALUES ('$email', '$first_name', '$last_name', '$phone', '$password', 'user')`
5. สำเร็จ → Redirect ไปหน้า Login
6. ล้มเหลว → แสดง error message กลับหน้า Register

**หมายเหตุ:** Password เก็บเป็น Plain Text, ไม่ใช้ Prepared Statement

---

### F3: User Login

**วัตถุประสงค์:** เข้าสู่ระบบด้วย Email + Password

**ไฟล์:** `public/login.php` (แสดงฟอร์ม), `public/actions/login_action.php` (ประมวลผล)

**Input Fields:**

| Field | Type | Validation |
|---|---|---|
| Email | text/email | Required |
| Password | password | Required |

**Processing Flow:**
1. รับ `$_POST['email']` และ `$_POST['password']`
2. Query: `SELECT * FROM Users WHERE email = '$email' AND password = '$password'`
3. ถ้าพบ record:
   - `session_start()`
   - ตั้งค่า `$_SESSION['user_email']`, `$_SESSION['user_name']` (ชื่อ + นามสกุล), `$_SESSION['role']`
   - ถ้า role = 'admin' → Redirect ไป `admin.php`
   - ถ้า role = 'user' → Redirect ไป `index.php`
4. ถ้าไม่พบ → แสดง error "อีเมลหรือรหัสผ่านไม่ถูกต้อง"

---

### F4: Admin — จัดการสินค้า / ค้นหาคำสั่งซื้อ

**วัตถุประสงค์:** Admin สามารถค้นหาและดูรายการคำสั่งซื้อทั้งหมดพร้อมรายละเอียด

**ไฟล์:** `public/admin.php`

**เงื่อนไขเข้าถึง:** ต้อง Login แล้ว + `$_SESSION['role'] === 'admin'`

**ความสามารถการค้นหา:**
- ค้นหาด้วย: เลขคำสั่งซื้อ (order_number) หรือ ชื่อ-นามสกุลผู้สั่ง
- ใช้ `LIKE '%keyword%'` สำหรับ wildcard search
- SQL Query (JOIN):
  ```sql
  SELECT o.order_number, o.order_date, o.shipping_address,
         u.first_name, u.last_name, u.email,
         s.status_name, s.status_id
  FROM Orders o
  JOIN Users u ON o.user_email = u.email
  JOIN Status_Reference s ON o.status_id = s.status_id
  WHERE o.order_number LIKE '%$search%'
     OR u.first_name LIKE '%$search%'
     OR u.last_name LIKE '%$search%'
  ORDER BY o.order_date DESC
  ```

**Data Return — Order List:**

แต่ละ Order แสดง:
- order_number
- ชื่อ-นามสกุลผู้สั่ง
- วันที่สั่ง
- สถานะ (รอยืนยันคำสั่งซื้อ / ยืนยันคำสั่งซื้อ)
- ที่อยู่จัดส่ง (ถ้ามี)

**Data Return — Order Detail (ภายในแต่ละ Order):**

| รายการ | แหล่งข้อมูล |
|---|---|
| Product Number | Order_Details.product_number |
| ชื่อสินค้า | Products.name |
| จำนวน | Order_Details.quantity |
| ราคาต่อชิ้น | Products.price |
| ราคารวม | Products.price × Order_Details.quantity |

---

### F5: เพิ่มคำสั่งซื้อสินค้า (Add Order)

**วัตถุประสงค์:** User สร้าง Order ใหม่จากสินค้าที่เลือกไว้ในหน้า Product List

**ไฟล์:** `public/actions/add_order_action.php`

**เงื่อนไข:** ต้อง Login แล้ว (role = user)

**Input:** Array ของ items (products ที่เลือก + จำนวน)
- ส่งผ่าน `$_POST` ในรูปแบบ:
  ```
  items[0][product_number] = SKU-001
  items[0][quantity] = 2
  items[1][product_number] = SKU-003
  items[1][quantity] = 1
  ```

**Processing Flow:**
1. สร้าง OrderNumber: `'ORD-' . date('Ymd') . '-' . rand(1000, 9999)`
2. ดึง user_email จาก `$_SESSION['user_email']`
3. INSERT ลง Orders:
   ```sql
   INSERT INTO Orders (order_number, user_email, status_id)
   VALUES ('$order_number', '$user_email', 1)
   ```
4. Loop ทุก item → INSERT ลง Order_Details:
   ```sql
   INSERT INTO Order_Details (order_number, product_number, quantity)
   VALUES ('$order_number', '$product_number', $quantity)
   ```
5. Return: แสดง OrderNumber ที่สร้างสำเร็จ → Redirect กลับหน้า index.php หรือ my_orders.php พร้อมแสดง success message

---

### F6: แก้ไขคำสั่งซื้อ (Edit Order)

**วัตถุประสงค์:** แก้ไขจำนวนสินค้าภายใน Order ที่ยังอยู่ในสถานะ "รอยืนยัน"

**ไฟล์:** `public/actions/edit_order_action.php`

**Input:**

| Field | Description |
|---|---|
| order_number | เลขคำสั่งซื้อ |
| product_number | รหัสสินค้าที่ต้องการแก้ไข |
| quantity | จำนวนใหม่ |

**Processing Flow:**
1. ถ้า quantity > 0:
   ```sql
   UPDATE Order_Details SET quantity = $quantity
   WHERE order_number = '$order_number' AND product_number = '$product_number'
   ```
2. ถ้า quantity = 0 (ลบรายการ):
   ```sql
   DELETE FROM Order_Details
   WHERE order_number = '$order_number' AND product_number = '$product_number'
   ```
3. Redirect กลับหน้ารายละเอียด Order

---

### F7: ยืนยันคำสั่งซื้อ (User Confirm Order)

**วัตถุประสงค์:** User กรอกที่อยู่จัดส่งเพื่อยืนยัน Order

**ไฟล์:** `public/actions/confirm_order_action.php`

**Input:**

| Field | Description |
|---|---|
| order_number | เลขคำสั่งซื้อ |
| shipping_address | ที่อยู่จัดส่ง (Text) |

**Processing Flow:**
1. อัปเดตที่อยู่จัดส่ง:
   ```sql
   UPDATE Orders SET shipping_address = '$shipping_address'
   WHERE order_number = '$order_number'
   ```
2. Redirect กลับหน้า my_orders.php พร้อมแจ้ง success

**หมายเหตุ:** ฟังก์ชันนี้ไม่ได้เปลี่ยนสถานะ Order — สถานะจะเปลี่ยนได้โดย Admin เท่านั้น (F8)

---

### F8: Admin Bulk Update สถานะคำสั่งซื้อ

**วัตถุประสงค์:** Admin เลือกหลาย Order พร้อมกัน แล้วเปลี่ยนสถานะเป็น "ยืนยันคำสั่งซื้อ"

**ไฟล์:** `public/actions/bulk_update_action.php`

**Input:**
- Array ของ order_number ที่ถูก select ผ่าน Checkbox
- ส่งผ่าน `$_POST['selected_orders']` (array)

**Processing Flow:**
1. รับ array `$selected_orders` จาก `$_POST`
2. สร้าง SQL:
   ```php
   $in = implode("','", $selected_orders);
   $sql = "UPDATE Orders SET status_id = 2 WHERE order_number IN ('$in')";
   ```
3. Execute query
4. Redirect กลับหน้า admin.php พร้อมแจ้งจำนวน Order ที่อัปเดตสำเร็จ

---

## 6. Page Specifications & UI/UX Design

---

### 6.1 โครงสร้างไฟล์ (File Structure)

```
public/
├── config.php              # DB connection (mysqli)
├── seed.php                # Auto seed data
├── index.php               # หน้ารายการสินค้า (Product Listing) — สำหรับ User
├── login.php               # หน้า Login
├── register.php            # หน้า Register
├── my_orders.php           # หน้ารายการคำสั่งซื้อของ User
├── admin.php               # หน้า Admin Dashboard
├── actions/
│   ├── login_action.php
│   ├── logout_action.php
│   ├── register_action.php
│   ├── add_order_action.php
│   ├── edit_order_action.php
│   ├── confirm_order_action.php
│   └── bulk_update_action.php
└── assets/
    └── style.css
```

---

### 6.2 Global Layout (ทุกหน้าที่ Login แล้ว)

#### Top Navigation Bar

```
┌─────────────────────────────────────────────────────────────────────┐
│ [■ OMS]   📦 สินค้า  |  📋 คำสั่งซื้อของฉัน       👤 ชื่อ นามสกุล  ↪ ออกจากระบบ │
└─────────────────────────────────────────────────────────────────────┘
```

- **ซ้าย:** โลโก้สี่เหลี่ยมสีน้ำเงิน + ข้อความ "OMS"
- **กลาง:** เมนู Tab 2 รายการ
  - "สินค้า" → link ไป `index.php` (ไอคอนร้านค้า)
  - "คำสั่งซื้อของฉัน" → link ไป `my_orders.php` (ไอคอนรายการ)
  - Tab ที่ active มีพื้นหลังสีฟ้าอ่อน + ขีดเส้นใต้
- **ขวา:** ไอคอนรูปคน + ชื่อผู้ใช้ (จาก `$_SESSION['user_name']`) + ปุ่ม "ออกจากระบบ" → link ไป `actions/logout_action.php`

**สี:** พื้น Navbar สีขาว, เส้นขอบล่างสีเทาอ่อน

---

### 6.3 หน้า Login (`login.php`)

**Layout:** ฟอร์มกลางหน้าจอ (Centered Card)

```
┌──────────────────────┐
│      เข้าสู่ระบบ        │
│                      │
│  Email:  [________]  │
│  Password: [______]  │
│                      │
│  [ เข้าสู่ระบบ ]       │
│                      │
│  ยังไม่มีบัญชี? สมัครสมาชิก │
└──────────────────────┘
```

- ปุ่ม "เข้าสู่ระบบ" สีน้ำเงิน เต็มความกว้างของฟอร์ม
- Link "สมัครสมาชิก" → ไป `register.php`
- แสดง error message (ถ้ามี) ด้วย banner สีแดงอ่อน

---

### 6.4 หน้า Register (`register.php`)

**Layout:** ฟอร์มกลางหน้าจอ (Centered Card)

```
┌──────────────────────────┐
│      สมัครสมาชิก            │
│                          │
│  Email:          [_____] │
│  ชื่อ:            [_____] │
│  นามสกุล:         [_____] │
│  เบอร์โทร:        [_____] │
│  Password:       [_____] │
│  Confirm Password:[____] │
│                          │
│  [ สมัครสมาชิก ]           │
│                          │
│  มีบัญชีแล้ว? เข้าสู่ระบบ     │
└──────────────────────────┘
```

- ปุ่ม "สมัครสมาชิก" สีน้ำเงิน เต็มความกว้าง
- Link "เข้าสู่ระบบ" → ไป `login.php`
- Validation error แสดงใต้ field ที่มีปัญหา

---

### 6.5 หน้ารายการสินค้า (`index.php`) — **หน้าหลัก**

**อ้างอิงตาม UI ในภาพตัวอย่าง**

#### 6.5.1 Content Header Area

```
┌──────────────────────────────────────────────────────────────┐
│  รายการสินค้า                        [📦 สร้าง Order (n)]  🔍 ค้นหาสินค้า... │
│  เลือกสินค้าแล้วกด "สร้าง Order"                                            │
└──────────────────────────────────────────────────────────────┘
```

- **H1:** "รายการสินค้า"
- **Subtitle:** "เลือกสินค้าแล้วกด 'สร้าง Order'"
- **ปุ่ม "สร้าง Order (n)":** สีน้ำเงิน มี badge วงกลมสีขาวแสดงจำนวนชนิดสินค้าที่เลือก — กดแล้ว Submit ฟอร์มสร้าง Order
- **Search Input:** ช่องค้นหาสินค้า มี placeholder "ค้นหาสินค้า..." — ค้นหาจาก product_number หรือ name

#### 6.5.2 Cart Summary Banner

ปรากฏเมื่อมีสินค้าถูกเลือกอย่างน้อย 1 รายการ:

```
┌──────────────────────────────────────────────────────────────┐
│  เลือกไว้ X ชิ้น · ยอดรวม ฿XX,XXX              [ ดำเนินการสั่งซื้อ → ] │
└──────────────────────────────────────────────────────────────┘
```

- **พื้นหลัง:** สีฟ้าอ่อน (#EBF5FF หรือใกล้เคียง)
- **ข้อความซ้าย:** "เลือกไว้ {จำนวนชิ้นรวมทุกสินค้า} ชิ้น · ยอดรวม ฿{ราคารวม}"
- **ปุ่มขวา:** "ดำเนินการสั่งซื้อ →" สีน้ำเงินเข้ม — กดแล้ว Submit ฟอร์มเพิ่ม Order (เรียก F5)
- ถ้ายังไม่เลือกสินค้า → ซ่อน Banner นี้

#### 6.5.3 Product Grid (4 Columns)

แสดงสินค้าทั้งหมดในรูปแบบ Grid Layout 4 คอลัมน์:

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│ SKU-001     │  │ SKU-002     │  │ SKU-003     │  │ SKU-004     │
│ ชื่อสินค้า    │  │ ชื่อสินค้า    │  │ ชื่อสินค้า    │  │ ชื่อสินค้า    │
│ ฿18,900     │  │ ฿3,200      │  │ ฿2,800      │  │ ฿5,500      │
│ คงเหลือ 13   │  │ คงเหลือ 48   │  │ คงเหลือ 30   │  │ คงเหลือ 12   │
│ [- 1 +]     │  │[+เพิ่มในตะกร้า]│  │[+เพิ่มในตะกร้า]│  │[+เพิ่มในตะกร้า]│
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
```

#### 6.5.4 Product Card — Anatomy

แต่ละการ์ดมีโครงสร้าง:

| ส่วน | รายละเอียด | Style |
|---|---|---|
| SKU Badge | มุมซ้ายบน เช่น "SKU-001" | กรอบสีเทา, ตัวอักษรเล็ก, สีน้ำเงิน |
| สินค้าหมด Badge | มุมขวาบน (เฉพาะสินค้า stock=0) "สินค้าหมด" | ข้อความสีแดง, พื้นเหลืองอ่อน |
| ชื่อสินค้า | ตัวหนา ขนาดกลาง | สีดำ |
| ราคา | ฿ + ตัวเลขจัดรูปแบบ (เช่น ฿18,900) | สีน้ำเงิน, ตัวใหญ่ |
| คงเหลือ | "คงเหลือ XX ชิ้น" | สีเทา, ตัวเล็ก |
| Action Area | ปุ่ม/ตัวควบคุม (ดูด้านล่าง) | เต็มความกว้างการ์ด |

#### 6.5.5 Product Card — 3 States

**State 1: Default (ยังไม่เลือก, มี stock)**
```
┌──────────────────────┐
│ [+ เพิ่มในตะกร้า]       │
└──────────────────────┘
```
- ปุ่มสีน้ำเงินทึบ, ข้อความสีขาว, เต็มความกว้าง, มีเครื่องหมาย +

**State 2: Out of Stock (stock_quantity = 0)**
```
┌──────────────────────┐
│ [+ เพิ่มในตะกร้า]       │  ← ปุ่มสีจาง (disabled)
└──────────────────────┘
```
- ปุ่มสีเทาอ่อน (disabled), กดไม่ได้
- มี Badge "สินค้าหมด" สีแดง ที่มุมขวาบนของการ์ด

**State 3: Selected (เลือกแล้ว, ปรับจำนวนได้)**
```
┌──────────────────────┐
│  [ - ]    1    [ + ]  │
└──────────────────────┘
```
- แทนที่ปุ่ม "เพิ่มในตะกร้า" ด้วย Quantity Stepper
- ปุ่ม `[-]` ลดจำนวน — ถ้าจำนวน = 1 แล้วกด `-` → นำออกจากตะกร้า (กลับไป State 1)
- ตัวเลขจำนวนตรงกลาง
- ปุ่ม `[+]` เพิ่มจำนวน — ไม่เกิน stock_quantity
- พื้นหลังสีขาว, กรอบสีเทา

#### 6.5.6 กลไกตะกร้า (Cart Mechanism)

เนื่องจากเป็น Server-Side Rendering ทั้งหมด — ใช้ `$_SESSION` เก็บข้อมูลตะกร้า:

```php
$_SESSION['cart'] = [
    'SKU-001' => 2,   // product_number => quantity
    'SKU-003' => 1,
];
```

**การทำงาน:**
- กด "เพิ่มในตะกร้า" → POST ไปหน้าเดิม (index.php) พร้อม product_number → เพิ่มลง `$_SESSION['cart']`
- กด `+` / `-` → POST ไปหน้าเดิมพร้อม action (increment/decrement)
- กด "สร้าง Order" หรือ "ดำเนินการสั่งซื้อ" → POST ไป `actions/add_order_action.php` พร้อมข้อมูลจาก `$_SESSION['cart']`

**การคำนวณ Summary:**
- จำนวนชิ้น = `array_sum($_SESSION['cart'])`
- ยอดรวม = sum of (quantity × price) สำหรับทุกสินค้าในตะกร้า
- จำนวนชนิดสินค้า (แสดงใน badge ปุ่ม "สร้าง Order") = `count($_SESSION['cart'])`

---

### 6.6 หน้าคำสั่งซื้อของฉัน (`my_orders.php`)

**เงื่อนไข:** ต้อง Login แล้ว (role = user)

**Layout:** ตารางแสดงรายการ Order ทั้งหมดของ User ที่ Login อยู่

```
┌────────────────────────────────────────────────────────────────────┐
│  คำสั่งซื้อของฉัน                                                    │
├──────────┬────────────┬───────────────────┬──────────┬────────────┤
│ เลขที่ Order │ วันที่สั่ง     │ สถานะ              │ ยอดรวม    │ จัดการ      │
├──────────┼────────────┼───────────────────┼──────────┼────────────┤
│ ORD-xxx  │ 29/03/2026 │ รอยืนยันคำสั่งซื้อ   │ ฿22,500  │ [ดู] [แก้ไข] │
│ ORD-xxx  │ 28/03/2026 │ ยืนยันคำสั่งซื้อ     │ ฿5,500   │ [ดู]        │
└──────────┴────────────┴───────────────────┴──────────┴────────────┘
```

**รายละเอียด Order (Expandable หรือ หน้าแยก):**

แสดงรายการสินค้าภายใน Order:

| Product Number | ชื่อสินค้า | จำนวน | ราคาต่อชิ้น | รวม |
|---|---|---|---|---|
| SKU-001 | แล็ปท็อป Asus VivoBook 15 | 1 | 18,900 | 18,900 |
| SKU-003 | คีย์บอร์ด Keychron K2 Wireless | 1 | 2,800 | 2,800 |

**ฟอร์มยืนยัน Order (F7):**
- แสดงเมื่อ Order มีสถานะ "รอยืนยันคำสั่งซื้อ" และยังไม่มี shipping_address
- มีช่อง Textarea สำหรับกรอก "ที่อยู่จัดส่ง"
- ปุ่ม "ยืนยันคำสั่งซื้อ" → POST ไป `actions/confirm_order_action.php`

**ฟอร์มแก้ไข Order (F6):**
- แสดงเมื่อ Order มีสถานะ "รอยืนยันคำสั่งซื้อ"
- แต่ละ item มี input แก้ไขจำนวน
- ปุ่ม "บันทึก" → POST ไป `actions/edit_order_action.php`

---

### 6.7 หน้า Admin Dashboard (`admin.php`)

**เงื่อนไข:** ต้อง Login แล้ว + `$_SESSION['role'] === 'admin'`

**Layout:**

```
┌─────────────────────────────────────────────────────────────────┐
│  จัดการคำสั่งซื้อ (Admin)                     🔍 [ค้นหา Order/ชื่อ...]  │
├─────────────────────────────────────────────────────────────────┤
│  [ ☑ ยืนยันคำสั่งซื้อที่เลือก ]                                       │
├────┬──────────┬──────────────┬──────────┬───────────────────┬───┤
│ ☐  │ เลขที่ Order │ ชื่อผู้สั่ง        │ วันที่สั่ง   │ สถานะ              │ ดู │
├────┼──────────┼──────────────┼──────────┼───────────────────┼───┤
│ ☐  │ ORD-xxx  │ สมชาย ใจดี    │ 29/03/26 │ รอยืนยันคำสั่งซื้อ   │ ▶ │
│ ☐  │ ORD-xxx  │ สมหญิง ใจงาม  │ 28/03/26 │ ยืนยันคำสั่งซื้อ     │ ▶ │
└────┴──────────┴──────────────┴──────────┴───────────────────┴───┘
```

**องค์ประกอบ:**

1. **Search Bar:** ค้นหาด้วย order_number, first_name, last_name
2. **ปุ่ม "ยืนยันคำสั่งซื้อที่เลือก":** สีน้ำเงิน — กดแล้ว Submit Checkbox ที่เลือกไว้ (F8)
3. **Checkbox คอลัมน์แรก:**
   - Master Checkbox ที่ header → Select All / Deselect All
   - Checkbox ขนาดอย่างน้อย 24×24 px (ตาม Fitts's Law)
   - แถวที่ถูกเลือก → พื้นหลังไฮไลท์สีฟ้าอ่อน
4. **คอลัมน์ "ดู":** ปุ่มขยายดูรายละเอียด Order Detail

**Order Detail (Expandable):**
- แสดงตาราง Order_Details ภายใน Order ที่เลือก
- แสดง: Product Number, ชื่อสินค้า, จำนวน, ราคา, ที่อยู่จัดส่ง

---

## 7. UI Style Guide

### สี (Color Palette)

| ชื่อ | Hex | ใช้สำหรับ |
|---|---|---|
| Primary Blue | #2563EB | ปุ่มหลัก, SKU badge, ราคา, Navbar active |
| Primary Blue Dark | #1D4ED8 | ปุ่ม hover, ปุ่ม "ดำเนินการสั่งซื้อ" |
| Light Blue BG | #EBF5FF | Cart summary banner, active tab bg, selected row |
| White | #FFFFFF | พื้นหลังหลัก, การ์ด, Navbar |
| Gray Border | #E5E7EB | กรอบการ์ด, เส้นแบ่งตาราง |
| Gray Text | #6B7280 | ข้อความรอง (คงเหลือ, subtitle) |
| Dark Text | #111827 | ข้อความหลัก, ชื่อสินค้า |
| Red | #EF4444 | "สินค้าหมด" badge |
| Disabled Gray | #D1D5DB | ปุ่ม disabled |

### Typography

| Element | Size | Weight | Color |
|---|---|---|---|
| Page Title (H1) | 24px | Bold | Dark Text |
| Subtitle | 14px | Normal | Gray Text |
| Product Name | 16px | Semi-bold | Dark Text |
| Price | 20px | Bold | Primary Blue |
| Stock text | 13px | Normal | Gray Text |
| SKU Badge | 12px | Normal | Primary Blue |
| Button text | 14px | Semi-bold | White |

### Component Specs

| Component | Border Radius | Padding | Border |
|---|---|---|---|
| Product Card | 12px | 16px | 1px solid #E5E7EB |
| Button Primary | 8px | 10px 16px | none |
| Button Disabled | 8px | 10px 16px | none, bg: #D1D5DB |
| Search Input | 8px | 8px 12px | 1px solid #E5E7EB |
| Navbar | 0 | 12px 24px | bottom: 1px solid #E5E7EB |
| Cart Banner | 8px | 12px 16px | none, bg: #EBF5FF |
| Quantity Stepper | 8px | 8px | 1px solid #E5E7EB |

---

## 8. Page Flow (Navigation Map)

```
login.php ──────────────────────────┐
    │                               │
    ├── (user) ──→ index.php ◄──────┤
    │                  │            │
    │                  ├── สร้าง Order → add_order_action.php → my_orders.php
    │                  │
    │              my_orders.php
    │                  ├── แก้ไข Order → edit_order_action.php → my_orders.php
    │                  ├── ยืนยัน Order → confirm_order_action.php → my_orders.php
    │                  └── ออกจากระบบ → logout_action.php → login.php
    │
    └── (admin) ──→ admin.php
                       ├── ค้นหา Order (GET/POST)
                       ├── Bulk Update → bulk_update_action.php → admin.php
                       └── ออกจากระบบ → logout_action.php → login.php

register.php ──→ register_action.php ──→ login.php
```

---

## 9. Non-Functional Notes

| หัวข้อ | รายละเอียด |
|---|---|
| Security | ไม่พิจารณาในระยะนี้ — ไม่ hash password, ไม่ใช้ prepared statement, ไม่ป้องกัน XSS/CSRF |
| Session | ใช้ PHP native session (`session_start()`) |
| Encoding | UTF-8 ทั้ง DB และ HTML (`<meta charset="UTF-8">`) |
| DB Connection | ใช้ `mysqli_connect()` ตรงๆ ใน `config.php` |
| Error Handling | แสดง error เป็น HTML message ธรรมดา ไม่มี structured error handling |
| Responsive | ไม่จำเป็นต้อง responsive — ออกแบบสำหรับ Desktop เป็นหลัก |
| Docker | php:7.4-apache + mysql:5.7, expose port 8080:80 |
