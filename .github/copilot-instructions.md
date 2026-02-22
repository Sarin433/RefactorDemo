# Copilot Instructions — RefactorDemo (OMS Legacy PHP)

โปรเจกต์นี้ตั้งใจทำเป็นตัวอย่าง **"เว็บแอป PHP แบบเก่า/โค้ดไม่ดี" (legacy PHP 7)** เพื่อใช้เป็นเดโมและชุดตัวอย่างสำหรับการรีวิว/รีแฟกเตอร์ภายหลัง

ข้อสำคัญ: "โค้ดไม่ดี" ในที่นี้หมายถึงโครงสร้าง/สไตล์/การออกแบบที่ไม่ดี (spaghetti, coupling สูง, แยกชั้นไม่ชัด, naming แย่, duplication) ไม่ใช่การจงใจใส่ช่องโหว่เพื่อการโจมตี

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 7.4 (ห้ามอัปเกรดเป็น 8+) |
| Web Server | Apache (`php:7.4-apache` Docker image) |
| Database | MySQL 5.7 |
| DB Extension | `mysqli` — ไม่ใช้ PDO, ไม่ใช้ prepared statements |
| Container | Docker + Docker Compose v3.7 |
| Frontend | Vanilla HTML/CSS + plain JavaScript (ไม่มี framework) |
| Charset | utf8mb4 ทุกตาราง |

**Ports:** App → `8080:80` | DB → internal บน network `omsnet`  
**DB Credentials:** host=`db`, db=`omsdb`, user=`omsuser`, pass=`omspass`

---

## โครงสร้าง Directory

```
RefactorDemo/
├── docker-compose.yml          ← services: app (port 8080:80) + db; network omsnet
├── Dockerfile                  ← php:7.4-apache + mysqli extension
├── db/
│   └── init.sql                ← DDL สร้าง 5 ตาราง (ไม่มี seed data)
├── doc/
│   ├── requirements.md
│   └── TOR/
│       └── TOR.md
└── public/                     ← Apache document root (mount + copy เข้า image)
    ├── config.php              ← เปิด mysqli connection + include seed.php
    ├── seed.php                ← auto-seed Status_Reference & Products ทุก request
    ├── index.php               ← หน้าแสดงสินค้า + JS cart (user)
    ├── login.php               ← ฟอร์ม login
    ├── register.php            ← ฟอร์มสมัครสมาชิก
    ├── my_orders.php           ← ประวัติคำสั่งซื้อของ user
    ├── admin.php               ← แดชบอร์ด admin + bulk update status
    ├── assets/
    │   └── style.css
    └── actions/                ← POST-only handlers (ไม่มี HTML output, redirect อย่างเดียว)
        ├── login_action.php
        ├── logout_action.php
        ├── register_action.php
        ├── add_order_action.php
        ├── edit_order_action.php
        ├── confirm_order_action.php
        └── bulk_update_action.php
```

---

## บทบาทของแต่ละไฟล์

### Infrastructure

| ไฟล์ | หน้าที่ |
|---|---|
| `docker-compose.yml` | กำหนด service `app` (PHP+Apache) และ `db` (MySQL 5.7) บน network `omsnet` |
| `Dockerfile` | ต่อยอดจาก `php:7.4-apache`, ติดตั้ง mysqli extension, copy `public/` เข้า image |
| `db/init.sql` | สร้าง 5 ตาราง — ไม่มี seed data (seed ทำโดย PHP) |

### PHP Pages (มี HTML output)

| ไฟล์ | หน้าที่ |
|---|---|
| `config.php` | เปิด mysqli connection ด้วย hardcoded credentials แล้ว `include` seed.php — ทุกหน้า include ไฟล์นี้ |
| `seed.php` | ตรวจ `COUNT(*)` ของ `Status_Reference` และ `Products`; ถ้าว่างให้ INSERT ข้อมูลเริ่มต้น — ทำงานทุก request |
| `login.php` | HTML form → POST ไป `actions/login_action.php`; แสดง error จาก `?err=invalid` |
| `register.php` | HTML form → POST ไป `actions/register_action.php`; แสดง error จาก `?err=dup` / `?err=pw` |
| `index.php` | แสดง product grid (user ต้อง login); JS cart ใน memory + quantity stepper; checkout → POST ไป `add_order_action.php` |
| `my_orders.php` | ประวัติคำสั่งซื้อของ user; แสดง form แก้ไข quantity (status=1) และ confirm+shipping address (status=1) |
| `admin.php` | รายการ order ทั้งหมด; ค้นหาด้วย `?q=` (SQL injection point); checkbox bulk select → POST ไป `bulk_update_action.php` |

### Action Scripts (POST handlers — redirect only)

| ไฟล์ | หน้าที่ |
|---|---|
| `login_action.php` | ตรวจ email+password ตรงตรงใน DB; set session → redirect `admin.php` หรือ `index.php` |
| `logout_action.php` | `session_destroy()` → redirect `login.php` |
| `register_action.php` | ตรวจ password match + duplicate email; INSERT user (plain-text password) → redirect `login.php` |
| `add_order_action.php` | สร้าง `order_number` = `ORD-YYYYMMDD-rand(1000,9999)`; INSERT `Orders` + `Order_Details` |
| `edit_order_action.php` | UPDATE quantity ใน `Order_Details`; ถ้า qty ≤ 0 ให้ DELETE แถว (ไม่ตรวจ status ฝั่ง server) |
| `confirm_order_action.php` | UPDATE `shipping_address` ใน `Orders` เท่านั้น — **ไม่** เปลี่ยน `status_id` |
| `bulk_update_action.php` | Admin: UPDATE `status_id = 2` สำหรับ order ที่เลือก โดยใช้ `IN (...)` clause แบบ string concat |

---

## Database Schema

```
Users
  user_id       INT PK AUTO_INCREMENT
  email         VARCHAR(255) UNIQUE NOT NULL
  first_name    VARCHAR(100) NOT NULL
  last_name     VARCHAR(100) NOT NULL
  phone         VARCHAR(20)  NOT NULL
  password      VARCHAR(255) NOT NULL   ← เก็บเป็น plain text
  role          VARCHAR(20)  DEFAULT 'user'

Products
  product_number  VARCHAR(50) PK
  name            VARCHAR(255) NOT NULL
  price           DECIMAL(10,2) NOT NULL
  stock_quantity  INT NOT NULL

Status_Reference
  status_id    INT PK AUTO_INCREMENT
  status_name  VARCHAR(100) UNIQUE NOT NULL
  ── seed: 1 = 'รอยืนยันคำสั่งซื้อ', 2 = 'ยืนยันคำสั่งซื้อ'

Orders
  order_number      VARCHAR(50) PK
  user_email        VARCHAR(255) FK → Users.email
  status_id         INT FK → Status_Reference.status_id
  shipping_address  TEXT NULL
  order_date        TIMESTAMP DEFAULT CURRENT_TIMESTAMP

Order_Details
  detail_id      INT PK AUTO_INCREMENT
  order_number   VARCHAR(50) FK → Orders.order_number
  product_number VARCHAR(50) FK → Products.product_number
  quantity       INT NOT NULL
```

---

## Navigation / Routing Flow

ไม่มี router — ใช้ file-based routing ทั้งหมด:

```
[ยังไม่ login]
  /login.php ──POST──► /actions/login_action.php
                            ├─(role=admin)──► /admin.php
                            ├─(role=user) ──► /index.php
                            └─(fail)      ──► /login.php?err=invalid

  /register.php ──POST──► /actions/register_action.php
                               ├─(สำเร็จ)    ──► /login.php
                               ├─(email ซ้ำ)──► /register.php?err=dup
                               └─(pw ไม่ตรง)──► /register.php?err=pw

[User ที่ login แล้ว]
  /index.php   ──(JS cart checkout)──► POST /actions/add_order_action.php ──► /my_orders.php?created=ORD-...
  /my_orders.php ──POST (edit)    ──► /actions/edit_order_action.php    ──► /my_orders.php
  /my_orders.php ──POST (confirm) ──► /actions/confirm_order_action.php ──► /my_orders.php

[Admin ที่ login แล้ว]
  /admin.php?q= ──GET──► /admin.php   (ค้นหา order)
  /admin.php    ──POST──► /actions/bulk_update_action.php ──► /admin.php

[ทุกหน้า]
  ──► /actions/logout_action.php ──► /login.php
```

**Session variables** (set ที่ `login_action.php`):
- `$_SESSION['user_email']` — ใช้ตรวจว่า login อยู่ + FK ใน Orders
- `$_SESSION['role']` — `'user'` หรือ `'admin'`
- `$_SESSION['first_name']`, `$_SESSION['last_name']` — แสดงใน nav bar

**Access control:** แต่ละหน้าตรวจ session เองด้วย `if (!isset(...)) header('Location: login.php')` — ไม่มี middleware กลาง

---

## Code Patterns (สิ่งที่ตั้งใจให้มีในโค้ด)

| Pattern | รายละเอียด |
|---|---|
| **SQL Injection** | ทุก query ใช้ string interpolation ตรง ๆ: `"WHERE email='" . $email . "'"` — ไม่มี escaping |
| **Plain-text password** | เก็บและเปรียบเทียบ password เป็น text ตรง ๆ ใน `login_action.php` และ `register_action.php` |
| **N+1 Query** | `admin.php` และ `my_orders.php` รัน query `Order_Details` ใน `foreach` loop ทุก order |
| **Mixed concerns** | แต่ละ `.php` รวม auth check + DB query + HTML output ไว้ในไฟล์เดียว |
| **seed ทุก request** | `seed.php` ถูก include ใน `config.php` — รัน `COUNT(*)` ทุกครั้งที่โหลดหน้า |
| **No CSRF protection** | ทุก POST form ไม่มี token |
| **Stock ตรวจแค่ client** | การจำกัดจำนวนสินค้าทำใน JS เท่านั้น — server ไม่ตรวจ stock เมื่อรับ order |
| **order_number collision** | ใช้ `rand(1000,9999)` — ไม่การันตี uniqueness |
| **`?s=` ไม่ถูกใช้** | `index.php` รับ `$_GET['s']` แต่ไม่เคยนำไปใช้ใน SQL |
| **confirm ไม่เปลี่ยน status** | `confirm_order_action.php` save แค่ `shipping_address` — `status_id` ไม่เปลี่ยน |

---

## Tech Constraints (ต้องยึดตามนี้)

- **PHP:** 7.x (legacy) — หลีกเลี่ยง syntax/feature ที่ต้อง PHP 8+
- **DB:** MySQL — ใช้ `mysqli` แบบ raw string concat เท่านั้น (ห้ามใช้ prepared statements / PDO)
- **Password:** เก็บเป็น plain text เท่านั้น
- **Run/Build:** ต้องรันได้ด้วย `docker-compose up`
- **Style:** ไม่ใช้ framework สมัยใหม่ (Laravel/Symfony) และไม่เพิ่ม heavy dependency

---

## How Copilot Should Work in This Repo

### 1) Keep it intentionally "legacy/bad" (แต่ยังรันได้)

- ผูก logic ไว้กับ I/O, ใช้ `include` หลายไฟล์, ฟังก์ชันยาว, ทำหลายอย่างในที่เดียว
- ยอมรับการผสม PHP กับ output/HTTP handling แบบตรง (เช่น `header()/json` ในไฟล์เดียวกัน)
- หลีกเลี่ยง clean architecture, typing, DTO, DI ที่จะทำให้โค้ด "ดี" เกินเจตนา

### 2) สร้างช่องโหว่ด้าน structure/maintainability

- spaghetti code, coupling สูง, naming แย่, duplication ยอมรับได้
- SQL injection แบบ string concat, plain-text password เป็นสิ่งที่ตั้งใจ

### 3) What to avoid

- อย่าอัปเกรด PHP เป็น 8+
- อย่าเพิ่ม framework ใหญ่หรือ tooling ที่ทำให้โปรเจกต์ "ทันสมัย"
- อย่าใช้ ORM หรือ query builder
- ควรมีหน้า HTML อย่างง่าย — ไม่ใช่ pure API

---

## โจทย์ที่ต้องการให้ Refactor ปรับปรุง Codebase นี้คือ

เป้าหมายของการ Refactor คือ **ยกระดับ codebase นี้ให้เป็น PHP สมัยใหม่** โดยใช้ tech stack ที่เป็น best practice ปัจจุบัน และให้ความสำคัญกับ **ความปลอดภัยของโค้ดสูงสุด** โดยอ้างอิง OWASP Top 10 2025

---

### Target Tech Stack (หลัง Refactor)

| Layer | Technology |
|---|---|
| Language | PHP 8.3 (LTS) |
| Framework | Laravel (latest stable) |
| ORM | Eloquent ORM |
| Database | MySQL 8.x |
| Authentication | Laravel Sanctum / Laravel Breeze |
| Frontend | Blade Templates + Tailwind CSS (หรือ Bootstrap 5) |
| Validation | Laravel Form Request Validation |
| Password Hashing | `bcrypt` / `argon2id` ผ่าน `Hash::make()` |
| Container | Docker + Docker Compose |

---

### หลักการ Refactor

#### 1) โครงสร้างโค้ด (Architecture)

- แยก **Controller / Service / Model** ให้ชัดเจน ตาม MVC ของ Laravel
- ใช้ **Eloquent Model** แทน raw mysqli string concat
- ใช้ **Form Request** สำหรับ validation ทุก input ก่อนถึง controller
- ใช้ **Route Model Binding** แทนการ query ด้วยมือใน controller
- แยก business logic ออกไปใน **Service class** ไม่ให้อยู่ใน controller โดยตรง
- ใช้ **Migration** แทน init.sql และ **Seeder / Factory** แทน seed.php

#### 2) ความปลอดภัย — OWASP Top 10 2025

แก้ไขจุดอ่อนทุกข้อที่อยู่ใน legacy code ให้ผ่านมาตรฐาน OWASP Top 10 2025:

| OWASP 2025 | ช่องโหว่เดิมใน legacy code | วิธีแก้ใน Laravel |
|---|---|---|
| **A01 – Broken Access Control** | ทุกหน้าตรวจ session เอง ไม่มี middleware กลาง | ใช้ Laravel `auth` middleware + Gate / Policy |
| **A02 – Cryptographic Failures** | password เก็บเป็น plain text | ใช้ `Hash::make()` (bcrypt/argon2id) + HTTPS only |
| **A03 – Injection** | SQL injection ทุก query ด้วย string concat | ใช้ Eloquent ORM หรือ Query Builder พร้อม parameter binding เสมอ |
| **A04 – Insecure Design** | ไม่มี rate limiting, ไม่มี account lockout | ใช้ Laravel `throttle` middleware บน login route |
| **A05 – Security Misconfiguration** | hardcoded DB credentials ใน config.php | ใช้ `.env` + `config/database.php`; ห้าม commit `.env` |
| **A07 – Identification & Authentication Failures** | ไม่มี session regeneration หลัง login | ใช้ `session()->regenerate()` หลัง authenticate สำเร็จ |
| **A08 – Software & Data Integrity Failures** | ไม่มี CSRF token ใน form ใด ๆ | ใช้ `@csrf` Blade directive ทุก form (Laravel เปิดใช้อัตโนมัติ) |
| **A09 – Security Logging & Monitoring Failures** | ไม่มี log เลย | ใช้ Laravel `Log` facade บันทึก login attempt และ order action |
| **A10 – Server-Side Request Forgery** | ไม่มีการกรอง user-supplied URL | ตรวจสอบและ whitelist URL ก่อน HTTP request ทุกครั้ง |

#### 3) Validation & Input Handling

- **ทุก input** ต้องผ่าน Form Request validation ก่อนเสมอ — ห้ามอ่าน `$request->input()` ใน controller โดยไม่ validate
- ใช้ Laravel validation rule: `required`, `email`, `min`, `max`, `integer`, `exists`, `unique` ตามความเหมาะสม
- **ห้าม** แสดง stack trace หรือ DB error ต่อ user — ใช้ custom error page (`APP_DEBUG=false` ใน production)

#### 4) Authentication & Session

- ใช้ Laravel built-in authentication (Breeze หรือ Fortify)
- บังคับ `session()->regenerate()` ทุกครั้งที่ login สำเร็จ (ป้องกัน Session Fixation)
- ตั้ง session lifetime ให้เหมาะสม และใช้ `secure`, `httponly`, `samesite=strict` บน session cookie
- ใช้ **Role-based access control** ผ่าน Gate หรือ Policy — ไม่ตรวจ role ด้วย `if ($role == 'admin')` กระจัดกระจาย

#### 5) Database

- ใช้ **Eloquent ORM** หรือ **Query Builder** เท่านั้น — ห้าม raw SQL ที่มี string interpolation
- ถ้าจำเป็นต้องใช้ raw SQL ให้ใช้ `DB::select('... WHERE id = ?', [$id])` (parameter binding เสมอ)
- ใช้ **Migration** สร้าง schema และ **Seeder** สำหรับ seed data
- Foreign key constraint ต้องครบและเปิดใช้งาน

#### 6) Code Quality

- ทุก class/method มี type hint (PHP 8 union types, return types)
- ใช้ `readonly` property และ named argument ที่ PHP 8.1+ รองรับ ตามความเหมาะสม
- ทำ **unit test** และ **feature test** ด้วย PHPUnit / Pest สำหรับ critical path (login, order creation, bulk update)
- ใช้ **Laravel Pint** หรือ PHP-CS-Fixer สำหรับ code style

---

### Refactor Checklist

- [ ] ย้าย credentials ทั้งหมดออกจาก source code → `.env`
- [ ] เปลี่ยน plain-text password เป็น `Hash::make()` / `Hash::check()`
- [ ] เปลี่ยนทุก query เป็น Eloquent หรือ Query Builder (ไม่มี string concat SQL)
- [ ] เพิ่ม `@csrf` ทุก form
- [ ] เพิ่ม Form Request validation ทุก POST endpoint
- [ ] เพิ่ม `auth` middleware ทุก route ที่ต้อง login
- [ ] เพิ่ม `role` middleware แยก user / admin
- [ ] แก้ N+1 query ด้วย Eloquent `with()` (eager loading)
- [ ] เพิ่ม `throttle` middleware บน login route
- [ ] เพิ่ม `session()->regenerate()` หลัง login
- [ ] ลบ seed logic ออกจาก request cycle → ย้ายไป Laravel Seeder
- [ ] เพิ่ม logging สำหรับ auth event และ order action
- [ ] ตั้ง `APP_DEBUG=false` และ custom error page สำหรับ production
- [ ] ตรวจสอบ stock server-side ก่อน INSERT Order_Details
- [ ] ใช้ `Str::uuid()` หรือ database sequence แทน `rand()` สำหรับ order_number