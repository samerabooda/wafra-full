# 🌊 Wafra Gulf — Commission Cards System
## Complete Laravel 11 + MySQL Backend

---

## 📁 Project Structure

```
wafra-gulf/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php           ← Login, Register, Logout, Me
│   │   │   ├── CommissionCardController.php  ← Cards CRUD + Tree + Report
│   │   │   ├── EmployeeController.php        ← Employees + Approval workflow
│   │   │   ├── ManagerController.php         ← Create/manage branch managers
│   │   │   ├── ImportController.php          ← Excel import
│   │   │   └── BranchController.php          ← Branches + Settings
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php
│   │       ├── PermissionMiddleware.php
│   │       ├── ActiveUserMiddleware.php
│   │       └── ForcePasswordChangeMiddleware.php
│   ├── Models/
│   │   ├── CommissionCard.php   ← Core model
│   │   ├── User.php
│   │   ├── Branch.php
│   │   ├── Employee.php
│   │   ├── UserPermission.php
│   │   ├── CardModification.php
│   │   ├── ImportBatch.php
│   │   ├── ActivityLog.php
│   │   ├── AccountType.php
│   │   ├── AccountStatus.php
│   │   └── TradingType.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/app.php
├── config/                      ← app, auth, cors, database, sanctum, session…
├── database/
│   ├── migrations/              ← 9 migration files
│   └── seeders/DatabaseSeeder.php
├── public/index.php + .htaccess
├── routes/api.php               ← All API endpoints
├── composer.json
└── .env.example
```

---

## ⚡ Setup on Local Server

### Requirements
- PHP >= 8.2
- MySQL >= 8.0
- Composer >= 2.x

---

### Step 1 — Create Laravel project + copy files
```bash
# Create a new Laravel project
composer create-project laravel/laravel wafra-gulf
cd wafra-gulf

# Install required packages
composer require laravel/sanctum maatwebsite/excel barryvdh/laravel-dompdf

# Publish Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Now **replace** the generated files with the ones from this package:
```
Copy everything from this zip → into your wafra-gulf/ folder
(replace composer.json, app/, config/, database/, routes/, bootstrap/app.php)
```

Then run:
```bash
composer install
```

---

### Step 2 — Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wafra_gulf
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

### Step 3 — Create MySQL database
```sql
CREATE DATABASE wafra_gulf
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Or via command line:
```bash
mysql -u root -p -e "CREATE DATABASE wafra_gulf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

### Step 4 — Run migrations
```bash
php artisan migrate
```

Expected output:
```
  2024_01_01_000001 .... branches ✓
  2024_01_01_000002 .... users + password_reset_tokens ✓
  2024_01_01_000003 .... user_permissions ✓
  2024_01_01_000004 .... account_types + account_statuses + trading_types ✓
  2024_01_01_000005 .... employees ✓
  2024_01_01_000006 .... import_batches ✓
  2024_01_01_000007 .... commission_cards ✓
  2024_01_01_000008 .... card_modifications ✓
  2024_01_01_000009 .... activity_logs + system tables ✓
```

---

### Step 5 — Seed default data
```bash
php artisan db:seed
```

This creates:
- 9 branches (HQ, Beirut, Damascus, Cairo, Riyadh, Dubai, Amman, Kuwait, Baghdad)
- Finance Admin user
- 7 base employees (Samer Obeid, Reyad Sabobah, etc.)
- Account Types: ECN, STP, Cent
- Account Statuses: NEW, Sub, Sub account, Transfer Broker, IB account
- Trading Types: ECN, Forex, Futures

---

### Step 6 — Start server
```bash
php artisan serve
# → http://127.0.0.1:8000
```

---

### Step 7 — Test the API

**Login:**
```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"finance@wafragulf.com","password":"Wafra@2026!"}'
```

**Response:**
```json
{
  "success": true,
  "token": "1|xxxxxxxxxxxxxxxx",
  "user": { "id": 1, "name": "محمد الشعلة", "role": "finance_admin" },
  "permissions": { "dashboard": true, "cards": true, ... }
}
```

**Use the token:**
```bash
curl http://127.0.0.1:8000/api/cards \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxx"
```

---

## 🔑 Default Login

| Field | Value |
|-------|-------|
| Email | `finance@wafragulf.com` |
| Password | `Wafra@2026!` |
| Role | `finance_admin` |

---

## 🗄️ Database Tables

| Table | Description |
|-------|-------------|
| `branches` | Company branches (HQ, Beirut, etc.) |
| `users` | System users (FA, Branch Managers) |
| `user_permissions` | Per-user permission grants |
| `account_types` | ECN, STP, Cent |
| `account_statuses` | NEW, Sub, IB account, etc. |
| `trading_types` | Forex, Futures, ECN |
| `employees` | Brokers + Marketers (internal & external) |
| `import_batches` | Excel import tracking |
| `commission_cards` | **CORE** — Commission records |
| `card_modifications` | Full audit trail for every edit |
| `activity_logs` | All user actions |
| `personal_access_tokens` | Sanctum API tokens |

---

## 📡 API Endpoints

### Authentication
```
POST   /api/auth/register          → FA first-time registration
POST   /api/auth/login             → Login (returns Bearer token)
POST   /api/auth/logout            → Revoke token
GET    /api/auth/me                → Current user + permissions
POST   /api/auth/change-password   → Change password
```

### Commission Cards
```
GET    /api/cards                  → List (paginated, with filters)
GET    /api/cards/{id}             → Single card
POST   /api/cards                  → Create new card
PUT    /api/cards/{id}             → Edit + audit trail (requires reason)
DELETE /api/cards/{id}             → Soft delete (FA only)
GET    /api/cards/tree             → Tree view (grouped)
GET    /api/cards/report           → Dynamic report
GET    /api/cards/modifications    → All modification history
```

### Query Filters (GET /api/cards)
```
?month=Jan+2025
?broker_id=1
?branch_id=2            (FA only)
?status=modified        (active|modified|new_added|inactive)
?kind=new               (new|sub)
?search=719750
?modified_only=1
?min_deposit=5000
?per_page=50
```

### Tree Groups (GET /api/cards/tree)
```
?group_by=broker        (default)
?group_by=branch
?group_by=month
?group_by=ext_marketer
```

### Employees
```
GET    /api/employees              → List all
GET    /api/employees/pending      → Pending approvals (FA only)
GET    /api/employees/{id}         → Single
POST   /api/employees              → Add (pending if branch manager)
PUT    /api/employees/{id}         → Update
PUT    /api/employees/{id}/approve → Approve (FA only)
PUT    /api/employees/{id}/reject  → Reject (FA only)
DELETE /api/employees/{id}         → Soft delete
```

### Managers (FA only)
```
GET    /api/managers               → List managers
POST   /api/managers               → Create + set permissions
PUT    /api/managers/{id}          → Update + change permissions
DELETE /api/managers/{id}          → Deactivate
POST   /api/managers/{id}/reset-password → Reset password
```

### Branches
```
GET    /api/branches               → List all
GET    /api/branches/{id}          → Single
POST   /api/branches               → Create (FA only)
PUT    /api/branches/{id}          → Update (FA only)
```

### Import
```
POST   /api/import                 → Import JSON rows
GET    /api/import/batches         → Import history
```

### Settings
```
GET    /api/settings               → All lookup data
POST   /api/settings/account-types         → Add type (FA only)
DELETE /api/settings/account-types/{id}    → Delete type (FA only)
POST   /api/settings/account-statuses      → Add status (FA only)
DELETE /api/settings/account-statuses/{id} → Delete status (FA only)
POST   /api/settings/trading-types         → Add trading type (FA only)
DELETE /api/settings/trading-types/{id}    → Delete trading type (FA only)
```

---

## 📤 Create Card Payload

```json
POST /api/cards
{
    "account_number":    "719750",
    "month":             "Jan 2025",
    "month_date":        "2025-01-01",
    "branch_id":         1,
    "account_type_id":   1,
    "account_status_id": 1,
    "trading_type_id":   1,
    "account_kind":      "new",
    "broker_id":         1,
    "broker_commission": 4.00,
    "marketer_id":       2,
    "marketer_commission": 3.00,
    "ext_marketer1_id":  3,
    "ext_commission1":   2.00,
    "ext_marketer2_id":  null,
    "ext_commission2":   0,
    "initial_deposit":   5000.00,
    "monthly_deposit":   12000.00,
    "notes":             "Optional notes"
}
```

## 📤 Edit Card Payload (requires reason)
```json
PUT /api/cards/1
{
    "broker_commission":  5.00,
    "monthly_deposit":    15000.00,
    "reason":             "تعديل عمولة",
    "notes":              "تم التعديل بناء على طلب الإدارة"
}
```

## 📤 Create Manager Payload
```json
POST /api/managers
{
    "name":      "Ahmed Al-Salem",
    "email":     "ahmed@wafragulf.com",
    "branch_id": 2,
    "role":      "branch_manager",
    "permissions": [
        "dashboard", "cards", "modified", "reports",
        "create_card", "edit_card", "employees", "import", "export"
    ]
}
```

---

## 🔗 Connect HTML Frontend to API

Add this to your `wafra_gulf_v2.html` JavaScript:

```javascript
const API = 'http://127.0.0.1:8000/api';
let TOKEN = localStorage.getItem('wg_api_token');

async function api(method, url, body = null) {
    const res = await fetch(API + url, {
        method,
        headers: {
            'Content-Type':  'application/json',
            'Accept':        'application/json',
            'Authorization': TOKEN ? `Bearer ${TOKEN}` : '',
        },
        body: body ? JSON.stringify(body) : null,
    });
    const data = await res.json();
    if (!data.success && res.status === 401) {
        TOKEN = null;
        localStorage.removeItem('wg_api_token');
        showScr('sl'); // redirect to login
    }
    return data;
}

// Override doLogin to use API
async function doLoginApi(email, password) {
    const data = await api('POST', '/auth/login', { email, password });
    if (data.success) {
        TOKEN = data.token;
        localStorage.setItem('wg_api_token', TOKEN);
        // Store user info
        curUser = data.user;
        curUser.perms = data.permissions;
        goApp();
    } else {
        document.getElementById('lerr').textContent = data.message;
        document.getElementById('lerr').style.display = 'block';
    }
}

// Get cards from API
async function loadCardsFromApi(filters = {}) {
    const params = new URLSearchParams(filters);
    const data = await api('GET', '/cards?' + params);
    if (data.success) {
        FD = data.data.data; // paginated
        renderCards(FD);
    }
}
```

---

## 🚀 Production Deployment

```bash
# 1. Set environment
APP_ENV=production
APP_DEBUG=false

# 2. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 3. Set storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 4. Restrict CORS
# In config/cors.php → change allowed_origins to your domain only
```

---

## 🧪 Quick API Test (using curl)

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"finance@wafragulf.com","password":"Wafra@2026!"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])")

echo "Token: $TOKEN"

# 2. Get branches
curl http://127.0.0.1:8000/api/branches \
  -H "Authorization: Bearer $TOKEN" | python3 -m json.tool

# 3. Get employees
curl http://127.0.0.1:8000/api/employees \
  -H "Authorization: Bearer $TOKEN" | python3 -m json.tool

# 4. Get settings
curl http://127.0.0.1:8000/api/settings \
  -H "Authorization: Bearer $TOKEN" | python3 -m json.tool
```

---

*Wafra Gulf Financial Services · Commission Cards System · Laravel 11 / MySQL 8*
