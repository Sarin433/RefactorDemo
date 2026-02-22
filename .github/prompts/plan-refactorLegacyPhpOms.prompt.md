# Plan: Refactor Legacy PHP OMS → Laravel 11 / PHP 8.3

**TL;DR** — สร้าง Laravel 11 app ใหม่ (greenfield) แทน legacy PHP 7.4 ทั้งหมด ใช้ Eloquent ORM, Blade + Tailwind CSS, Laravel Breeze (auth), Form Request Validation, middleware-based RBAC และ Pest tests โดยแก้ OWASP Top 10 ทุกข้อ **Order confirm flow คง logic เดิม**: user save address เท่านั้น, admin เป็นผู้เปลี่ยน status ผ่าน bulk update

---

### Phase 0 — Project Bootstrap & Infrastructure

1. สร้าง Laravel 11 project ใหม่ใน `refactored/` ด้วย `composer create-project laravel/laravel refactored` (PHP 8.3)
2. แทนที่ `Dockerfile` — base image `php:8.3-apache`, ติดตั้ง extension ที่จำเป็น (`pdo_mysql`, `mbstring`, `xml`, `zip`)
3. แทนที่ `docker-compose.yml` — service `app` (port `8080:80`), `db` (`mysql:8.0` พร้อม `--character-set-server=utf8mb4`), network `omsnet`
4. ตั้งค่า `.env` — ย้าย hardcoded credentials จาก `public/config.php` ทั้งหมด; สร้าง `.env.example`; เพิ่ม `.env` ใน `.gitignore`
5. ตั้ง session cookie ใน `config/session.php` — `secure=true`, `http_only=true`, `same_site=strict`, `lifetime=120`
6. สร้าง custom error views `resources/views/errors/404.blade.php` และ `500.blade.php`; ตั้ง `APP_DEBUG=false` สำหรับ production

---

### Phase 1 — Database Migration & Seeder (แทน `db/init.sql` + `public/seed.php`)

7. **Migration files:**
   - `create_users_table` — คอลัมน์ครบ, `password` VARCHAR(255) สำหรับ bcrypt, `role` enum(`user`,`admin`) DEFAULT `user`
   - `create_products_table` — `product_number` string PK, `name`, `price`, `stock_quantity`
   - `create_status_references_table` — `status_id`, `status_name` unique
   - `create_orders_table` — `order_number` string unique PK, **`user_id` INT FK → `users.id`** (เปลี่ยนจาก email FK เพื่อความถูกต้อง), `status_id` FK, `shipping_address` nullable; FK ทุกตัวมี `ON DELETE RESTRICT ON UPDATE CASCADE`
   - `create_order_details_table` — `detail_id`, `order_number` FK, `product_number` FK, `quantity`, **`unit_price DECIMAL(10,2)`** (snapshot ราคา ณ เวลาสั่งซื้อ)
8. **Seeders:**
   - `StatusReferenceSeeder` — INSERT status 1 (`รอยืนยันคำสั่งซื้อ`), 2 (`ยืนยันคำสั่งซื้อ`)
   - `ProductSeeder` — สินค้าตัวอย่างจาก legacy seed
   - `AdminUserSeeder` — สร้าง admin account ด้วย `Hash::make('password')`

---

### Phase 2 — Eloquent Models

9. **สร้าง Models พร้อม type hints, return types, relationships:**
   - `User extends Authenticatable` — `hasMany(Order::class)`, method `isAdmin(): bool`
   - `Product` — `hasMany(OrderDetail::class)`
   - `StatusReference` — `hasMany(Order::class)`
   - `Order` — `belongsTo(User::class)`, `belongsTo(StatusReference::class)`, `hasMany(OrderDetail::class)`
   - `OrderDetail` — `belongsTo(Order::class)`, `belongsTo(Product::class)`

---

### Phase 3 — Authentication (OWASP A02, A04, A07)

10. ติดตั้ง **Laravel Breeze** (`php artisan breeze:install blade`) — ได้ login/register views พร้อม `@csrf`
11. ปรับ `AuthenticatedSessionController` (Breeze) — เพิ่ม **`session()->regenerate()`** ทันทีหลัง login สำเร็จ (ป้องกัน Session Fixation)
12. ตรวจสอบ `RegisteredUserController` — ใช้ `Hash::make()` สำหรับ password (แทน plain-text ใน `public/actions/register_action.php`)
13. ตั้ง **`throttle:6,1` middleware** บน login route ใน `routes/web.php` — หลัง 6 ครั้ง → 429 (OWASP A04)
14. Redirect หลัง login ตาม role: admin → `/admin`, user → `/products`

---

### Phase 4 — Middleware & RBAC (OWASP A01)

15. สร้าง **`EnsureIsAdmin` middleware** ใน `app/Http/Middleware/EnsureIsAdmin.php` — ตรวจ `Auth::user()->isAdmin()`, ถ้าไม่ใช่ → `abort(403)`; register ใน `bootstrap/app.php`
16. ตั้ง **Route groups** ใน `routes/web.php`:
    - Guest only: `/login`, `/register`
    - Auth (`middleware: auth`): `/products`, `/my-orders`, `/orders/*`
    - Admin (`middleware: ['auth', 'admin']`): `/admin/*`
17. ลบ inline session check `if (!isset($_SESSION[...]))` ออกจาก `public/admin.php`, `public/my_orders.php`, `public/index.php`, และทุก `public/actions/` — แทนด้วย middleware กลาง

---

### Phase 5 — Controllers & Service Layer

18. **Controllers:**
    - `ProductController@index` — แทน `public/index.php`; query `Product::all()`; pass ไป view
    - `OrderController`:
      - `index()` — แทน `public/my_orders.php`; `Order::with('orderDetails.product', 'status')->where('user_id', Auth::id())->get()` (แก้ N+1)
      - `store(CreateOrderRequest $request)` — delegate ไป `OrderService::createOrder()`
      - `updateDetail(UpdateOrderDetailRequest $request, Order $order)` — Route Model Binding + Policy; แทน `public/actions/edit_order_action.php`
      - `saveAddress(ConfirmOrderRequest $request, Order $order)` — save `shipping_address` เท่านั้น **ไม่เปลี่ยน status** (ตาม decision); แทน `public/actions/confirm_order_action.php`
    - `AdminOrderController`:
      - `index(Request $request)` — search ด้วย Query Builder parameter binding (`LIKE ?`); eager load `with('user', 'orderDetails.product', 'status')`; แทน `public/admin.php`
      - `bulkApprove(BulkUpdateRequest $request)` — แทน `public/actions/bulk_update_action.php`

19. **`OrderService`** ใน `app/Services/OrderService.php`:
    - `createOrder(User $user, array $items): Order` — ตรวจ stock server-side ภายใน `DB::transaction()` + `lockForUpdate()`, decrement `stock_quantity`, snapshot `unit_price`, generate `order_number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8))` (แทน `rand()` — แก้ collision risk)
    - `saveShippingAddress(Order $order, string $address): void` — update `shipping_address` เท่านั้น
    - `bulkApprove(array $orderNumbers): int` — `Order::whereIn('order_number', $orderNumbers)->update(['status_id' => 2])`

---

### Phase 6 — Form Request Validation & Authorization (OWASP A03, A08)

20. **Form Requests:**
    - `CreateOrderRequest` — `product_number.*: required|exists:products,product_number`, `quantity.*: required|integer|min:1`; `authorize()` → `Auth::check()`
    - `UpdateOrderDetailRequest` — `quantity: required|integer|min:0`; `authorize()` → ตรวจ Policy
    - `ConfirmOrderRequest` — `shipping_address: required|string|max:500`; `authorize()` → ตรวจ Policy
    - `BulkUpdateRequest` — `selected_orders: required|array|min:1`, `selected_orders.*: string|exists:orders,order_number`; `authorize()` → `Auth::user()->isAdmin()`

21. **`OrderPolicy`** ใน `app/Policies/OrderPolicy.php`:
    - `update(User $user, Order $order): bool` — `$user->id === $order->user_id` (แก้ IDOR ใน `public/actions/edit_order_action.php`)
    - `saveAddress(User $user, Order $order): bool` — `$user->id === $order->user_id` (แก้ IDOR ใน `public/actions/confirm_order_action.php`)

---

### Phase 7 — Blade Views + Tailwind CSS (OWASP A08)

22. ติดตั้ง **Tailwind CSS** ผ่าน Vite (`npm install tailwindcss @tailwindcss/vite`)
23. สร้าง `resources/views/layouts/app.blade.php` (nav bar) และ `layouts/admin.blade.php`
24. **Migrate views:**
    - `products/index.blade.php` — grid สินค้า, JS in-memory cart (Vanilla JS, ไม่มี framework), quantity stepper; form checkout มี `@csrf`
    - `orders/index.blade.php` — ประวัติ order, form แก้ไข quantity, form save address; ทุก form มี `@csrf`
    - `admin/orders/index.blade.php` — search form (`GET`), order list พร้อม checkbox bulk select; form bulk update มี `@csrf`
    - `auth/login.blade.php`, `auth/register.blade.php` — จาก Breeze scaffold (มี `@csrf` อัตโนมัติ)

---

### Phase 8 — Logging & Security Hardening (OWASP A05, A09)

25. เพิ่ม **Logging** ด้วย `Log` facade:
    - `Log::info('auth.login', ['email' => ...])` ใน `AuthenticatedSessionController`
    - `Log::warning('auth.login.failed', ['email' => ...])` เมื่อ authenticate ล้มเหลว
    - `Log::info('order.created', ['order_number' => ..., 'user_id' => ...])` ใน `OrderService`
    - `Log::info('order.bulk_approved', ['orders' => ..., 'admin_id' => ...])` ใน `AdminOrderController`
26. ตั้งค่า `config/logging.php` — channel `daily` สำหรับ production
27. เพิ่ม **Security Headers** ผ่าน middleware หรือ Apache `.htaccess` — `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin`

---

### Phase 9 — Test Suite (Pest)

28. **Feature Tests** (`tests/Feature/`):
    - `LoginTest` — valid login → redirect by role; invalid → error; throttle หลัง 6 ครั้ง → 429
    - `RegisterTest` — duplicate email → validation error; password mismatch → error; success → `users` table มี hashed password
    - `OrderCreationTest` — place order → `orders` + `order_details` rows, `unit_price` snapshotted, `stock_quantity` decremented; out-of-stock → 422
    - `OrderEditTest` — owner edit → success; non-owner edit → 403
    - `OrderAddressTest` — owner save address → `shipping_address` updated, `status_id` **ไม่เปลี่ยน** (status คงเป็น 1); non-owner → 403
    - `AdminBulkUpdateTest` — admin bulk approve → status = 2; non-admin → 403; invalid order number → 422

29. **Unit Tests** (`tests/Unit/`):
    - `OrderServiceTest` — `createOrder()` (stock check, unit_price snapshot, order_number format); `bulkApprove()`
    - `OrderPolicyTest` — ownership check

30. ตั้งค่า `phpunit.xml` — DB connection `sqlite :memory:` + `RefreshDatabase` trait

---

### Phase 10 — Code Quality & Cleanup

31. รัน **Laravel Pint** — `./vendor/bin/pint` format code ทั้ง project
32. ย้าย legacy code ไปที่ `legacy/` folder (หรือ branch แยก) เพื่อเปรียบเทียบ
33. อัปเดต `README.md` — วิธีรัน `docker-compose up --build`, `php artisan migrate --seed`, `php artisan test`

---

### Verification

- `docker-compose up --build` → app พร้อมที่ `localhost:8080`
- `php artisan migrate --seed` → ตาราง + seed ข้อมูลครบ
- `php artisan test` → ทุก test pass (green)
- ทดสอบ CSRF: POST โดยไม่มี token → 419
- ทดสอบ SQLi: `' OR '1'='1` ใน login → ไม่ bypass ได้
- ทดสอบ IDOR: user A save address order ของ user B → 403
- ทดสอบ throttle: login ผิด 7 ครั้ง → 429
- ทดสอบ stock: สั่งเกิน stock → 422 พร้อม error message
- ทดสอบ plain-text password: ค่าใน `users.password` ต้องเป็น bcrypt hash (`$2y$...`)
- ตรวจสอบ order confirm: หลัง save address → `status_id` ยังคงเป็น 1

---

### Decisions

| ประเด็น | Decision |
|---|---|
| Confirm flow | คง logic เดิม: user save address only, admin bulk approve เปลี่ยน status |
| Frontend | Blade + Tailwind CSS, Vanilla JS สำหรับ cart |
| Stock | Server-side check + decrement ภายใน DB transaction |
| FK orders→users | เปลี่ยนจาก `user_email` FK → `user_id` INT FK |
| order_number | `Str::random(8)` แทน `rand(1000,9999)` |
| unit_price | เพิ่มคอลัมน์ใน `order_details` เพื่อ snapshot ราคา |
