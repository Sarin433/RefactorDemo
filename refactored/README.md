# OMS — Refactored (Laravel 12 / PHP 8.3)

Refactored version of the legacy PHP 7.4 Order Management System.  
All OWASP Top 10 2025 issues present in the original codebase have been resolved.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 |
| Framework | Laravel 12 |
| ORM | Eloquent ORM |
| Database | MySQL 8.0 |
| Authentication | Laravel built-in (session-based) |
| Frontend | Blade Templates + vanilla JS (UI identical to legacy) |
| Validation | Laravel Form Request Validation |
| Password Hashing | `bcrypt` via `Hash::make()` |
| Container | Docker + Docker Compose |
| Testing | Pest 3 (PHPUnit 12) |

---

## Requirements

- Docker Desktop (v24+)
- Docker Compose v2

No local PHP or Composer installation required.

---

## Quick Start

```bash
# 1. Build and start containers
docker-compose up --build -d

# 2. Run database migrations and seeders (first time only)
docker-compose exec app php artisan migrate --seed

# 3. Open the app
# http://localhost:8080
```

### Default credentials (seeded)

| Role | Email | Password |
|---|---|---|
| Admin | admin@oms.local | password |

Register a new user account via `/register`.

---

## Running Tests

Tests run against an in-memory SQLite database — no running containers required:

```bash
# Linux / macOS
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  -e APP_ENV=testing \
  composer:latest \
  ./vendor/bin/pest
```

```powershell
# Windows (PowerShell)
docker run --rm `
  -v "${PWD}:/app" `
  -w /app `
  -e APP_ENV=testing `
  composer:latest `
  ./vendor/bin/pest
```

Expected output: **30 passed (67 assertions)**

---

## OWASP Top 10 2025 Fixes

| OWASP | Legacy issue | Fix |
|---|---|---|
| **A01 – Broken Access Control** | Each page checked session independently | `auth` + `admin` middleware on all routes; `OrderPolicy` prevents IDOR |
| **A02 – Cryptographic Failures** | Passwords stored as plain text | `Hash::make()` (bcrypt) in `RegisteredUserController` |
| **A03 – Injection** | Every query used string interpolation | Eloquent ORM + Query Builder with parameter binding throughout |
| **A04 – Insecure Design** | No rate limiting on login | `RateLimiter` in `LoginRequest` — 6 attempts per minute |
| **A05 – Security Misconfiguration** | Hardcoded DB credentials in `config.php` | All credentials in `.env`; `SecurityHeaders` middleware adds security headers |
| **A07 – Auth Failures** | No session regeneration after login | `session()->regenerate()` in `AuthenticatedSessionController` |
| **A08 – Integrity Failures** | No CSRF tokens on any form | `@csrf` on every Blade form; Laravel CSRF middleware enabled |
| **A09 – Logging Failures** | No logging anywhere | `Log::info/warning` for login events, order creation, bulk updates |

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/           ← Login, Register, Logout
│   │   ├── AdminOrderController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   ├── Middleware/
│   │   ├── EnsureIsAdmin.php
│   │   └── SecurityHeaders.php
│   └── Requests/           ← Form Request validation (4 requests + LoginRequest)
├── Models/                 ← User, Product, Order, OrderDetail, StatusReference
├── Policies/
│   └── OrderPolicy.php     ← IDOR protection for order edit/address
└── Services/
    └── OrderService.php    ← Business logic: createOrder, saveShippingAddress, bulkApprove

database/
├── migrations/             ← 5 migration files replacing init.sql
└── seeders/                ← StatusReferenceSeeder, ProductSeeder, AdminUserSeeder

resources/views/
├── layouts/
│   ├── app.blade.php       ← User layout (navbar: สินค้า / คำสั่งซื้อของฉัน)
│   └── admin.blade.php     ← Admin layout (Admin Dashboard navbar)
├── products/index.blade.php
├── orders/index.blade.php
├── admin/orders/index.blade.php
├── auth/login.blade.php
├── auth/register.blade.php
└── errors/                 ← Custom 404, 500 pages

tests/
├── Feature/                ← LoginTest, RegisterTest, OrderCreationTest,
│                              OrderEditTest, OrderAddressTest, AdminBulkUpdateTest
└── Unit/                   ← OrderServiceTest, OrderPolicyTest
```

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
