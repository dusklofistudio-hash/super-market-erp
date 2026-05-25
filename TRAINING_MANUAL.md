# មេរៀនប្រើប្រាស់ប្រព័ន្ធ — Super Market ERP Management System
# (Training Manual)

> **ជំពូកទី ១ — ទិដ្ឋភាពទូទៅ**

---

## ១.១ អ្វីជា Project នេះ?

**Super Market ERP Management System** គឺជា Web Application ប្រើប្រាស់ Laravel Framework រួមជាមួយ ReactJS តាមរយៈ Inertia.js សម្រាប់គ្រប់គ្រងផ្សារទំនើបពហុសាខា (Multi-Branch Supermarket) ឲ្យដំណើរការទាំងផ្នែក POS (Point of Sale), គ្រប់គ្រងស្តុក (Inventory), ការទិញពីអ្នកផ្គត់ផ្គង់ (Purchases), ការផ្ទេរទំនិញរវាងសាខា (Stock Transfer), ការគ្រប់គ្រងចំណាយ (Expenses), និងរបាយការណ៍ (Reports)។ ប្រព័ន្ធគាំទ្រការប្តូរភាសា ខ្មែរ ↔ អង់គ្លេស **ដោយមិនបាច់ Refresh ទំព័រ** និងមានការគ្រប់គ្រងសិទ្ធិ (Role-Based Access Control) **ដោយមិនប្រើ Package ខាងក្រៅ** (manual)។

## ១.២ បច្ចេកវិទ្យាដែលប្រើ (Tech Stack)

| ផ្នែក | បច្ចេកវិទ្យា |
|---|---|
| Backend Framework | **Laravel 12** (PHP 8.2+) |
| Frontend Framework | **ReactJS 19** + **Inertia.js 3** |
| Frontend CSS | **Bootstrap 5.3** + Sass |
| Build Tool | **Vite 7** |
| Database | **SQLite** (default) ឬ **MySQL 8.0+** |
| DataTables | **Yajra DataTables** (Server-Side) |
| Notifications | **PHP-Flasher** (toast) + **SweetAlert2** (confirm/alert) |
| Date Picker | **Flatpickr** |
| Select Box | **Tom Select** (searchable + multi) |
| Routes in JS | **Ziggy** (`route()` helper in React) |
| Auth | **Manual** (custom controllers, no Breeze/Jetstream) |
| RBAC | **Manual** (Roles ↔ Permissions, no Spatie package) |
| i18n | **DB-backed** (Languages + Translations tables) + KH/EN no-refresh switching |
| Audit | **ActivityLogger Service** → `activity_logs` table |

## ១.៣ រចនាសម្ព័ន្ធ Project (Folder Structure)

```
super-market-erp/
├── app/
│   ├── Console/Commands/
│   │   └── AuditE2E.php               # `php artisan audit:e2e` — 51 assertions
│   ├── Http/Controllers/Admin/        # 28 admin controllers
│   │   ├── BranchController.php
│   │   ├── ProductController.php
│   │   ├── PosController.php          # POS cart + checkout
│   │   ├── SaleController.php
│   │   ├── PurchaseController.php
│   │   ├── StockTransferController.php
│   │   ├── StockAdjustmentController.php
│   │   ├── ReportController.php
│   │   ├── ActivityLogController.php
│   │   ├── LocaleController.php       # KH/EN no-refresh switcher endpoint
│   │   └── Concerns/RendersDataTable.php  # Yajra action/status cell helpers
│   ├── Http/Middleware/
│   │   ├── EnsurePermission.php       # `permission:slug` route guard
│   │   ├── SetLocale.php
│   │   └── HandleInertiaRequests.php
│   ├── Http/Requests/Admin/           # FormRequest validation per module
│   ├── Models/                        # 31 Eloquent models
│   │   └── Concerns/HasRolesAndPermissions.php  # Manual RBAC mixin
│   └── Services/
│       ├── StockService.php           # Centralized stock delta writer
│       ├── ActivityLogger.php         # Audit log writer
│       └── TranslationService.php
├── config/
│   └── app.php
├── database/
│   ├── migrations/
│   │   └── 2026_05_13_000000_create_super_market_pos_all_tables.php
│   │                                  # ALL 30 business tables in one file
│   └── seeders/                       # 41 seeders — one per table
│       ├── DatabaseSeeder.php         # Orchestrator with FK-safe order
│       ├── BranchSeeder.php
│       ├── UserSeeder.php
│       ├── ProductSeeder.php
│       └── ... (38 more)
├── resources/
│   ├── views/admin/layouts/           # Blade chrome
│   │   ├── admin_layout.blade.php     # Root view wrapping every Inertia page
│   │   ├── head.blade.php
│   │   ├── header.blade.php           # Brand + KH/EN pill + user dropdown
│   │   ├── left_sidebar.blade.php     # RBAC-gated navigation
│   │   └── scripts.blade.php
│   ├── js/
│   │   ├── app.jsx                    # Inertia + React bootstrap
│   │   ├── Pages/                     # 28 React page directories
│   │   │   ├── Branches/  Products/  Pos/  Sales/  Reports/  ...
│   │   ├── Components/
│   │   │   ├── ServerDataTable.jsx
│   │   │   ├── LineItemsEditor.jsx
│   │   │   └── RowActions.jsx         # Global SweetAlert delete handler
│   │   └── lib/
│   │       ├── I18nProvider.jsx       # KH/EN React listener
│   │       ├── PermissionProvider.jsx # `Can` + `usePermission()`
│   │       └── flatpickr-init.js, tom-init.js, flasher.js
│   └── sass/app.scss
├── lang/
│   ├── en/messages.php                # English translations
│   └── kh/messages.php                # Khmer translations
├── routes/
│   └── web.php                        # 93+ admin routes
├── tests/
│   ├── audit_smoke.sh                 # 93 GET routes smoke test
│   └── audit_datatables.sh            # 23 DataTable JSON endpoint test
├── docs/
│   └── tutorial/                      # 18-chapter developer curriculum (PR #9)
└── .agents/
    └── skills/testing-super-market-erp/SKILL.md
```

---

> **ជំពូកទី ២ — ការដំឡើង (Installation & Setup)**

---

## ២.១ តម្រូវការប្រព័ន្ធ (System Requirements)

- **PHP**: 8.2 ឬខ្ពស់ជាង (ត្រូវការ extensions: `mbstring`, `xml`, `bcmath`, `curl`, `zip`, `gd`, `intl`)
- **Composer**: 2.6 ឬខ្ពស់ជាង
- **Node.js**: 20 LTS (ឬខ្ពស់ជាង) + NPM 10
- **Database**: SQLite (default) ឬ MySQL 8.0+ ឬ MariaDB 10.6+
- **Web Server**: PHP built-in server (dev) ឬ nginx + PHP-FPM (production)

## ២.២ ជំហានដំឡើង (Step-by-Step)

### ជំហាន ១ — Clone និង Install Dependencies

```bash
# 1. Clone project
git clone https://github.com/dusklofistudio-hash/super-market-erp.git
cd super-market-erp

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install
```

### ជំហាន ២ — កំណត់ Environment

```bash
# Copy file .env.example ទៅ .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

បើក `.env` ហើយកែតម្រូវ:

```env
APP_NAME="Super Market ERP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# For SQLite (default — easiest for development)
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# For MySQL (recommended for staging/production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=super_market_erp
# DB_USERNAME=root
# DB_PASSWORD=

# Mail (dev — log channel writes reset URLs to laravel.log)
MAIL_MAILER=log

# Session + cache (database driver works out of the box)
SESSION_DRIVER=database
CACHE_STORE=database
```

### ជំហាន ៣ — បង្កើត Database និង Migrate

```bash
# Create SQLite file (if using SQLite)
touch database/database.sqlite

# Run all migrations (creates 30 business + 11 framework tables)
php artisan migrate

# Seed all 41 seeders (idempotent — safe to re-run)
php artisan db:seed

# OR do both in one shot (recommended for clean dev DB)
php artisan migrate:fresh --seed
```

> **ចំណាំ**: Seeder រួមមាន 3 roles, 60+ permissions, 2 branches (HQ + BR01), 3 users (admin/manager/cashier), 9 sample products, 12 stock rows, sample purchases + sales + activity logs។

### ជំហាន ៤ — Compile Frontend Assets

```bash
# Development mode (hot reload via Vite)
npm run dev

# OR Production build (output to public/build/)
npm run build
```

### ជំហាន ៥ — Storage Link (សម្រាប់ Avatar និងរូបភាព)

```bash
php artisan storage:link
```

### ជំហាន ៦ — ចាប់ផ្ដើម Server

```bash
# Terminal 1
php artisan serve

# Terminal 2 (only for dev mode)
npm run dev
```

បើក Browser ទៅ `http://127.0.0.1:8000`

## ២.៣ គណនី Default (Login Credentials)

បន្ទាប់ពី `php artisan db:seed` មានគណនី ៣ ដែលត្រូវ Seed រួចជាស្រេច:

| ប្រភេទ | Username | Password | សិទ្ធិ |
|---|---|---|---|
| **Super Admin** | `admin` | `password` | គ្រប់សិទ្ធិទាំងអស់ (bypass via `is_super_admin` flag) |
| **Branch Manager** | `manager` | `password` | គ្រប់គ្រងសាខា ផលិតផល លក់ ទិញ ស្តុក របាយការណ៍ (មិនមាន Users/Roles) |
| **Cashier** | `cashier` | `password` | POS, លក់, ផលិតផល (read-only), Customers |

> **ប្ដូរពាក្យសម្ងាត់ភ្លាមបន្ទាប់ពី Login លើកដំបូងនៅ Production!**

ការ Login៖ ប្រើ **Username** (មិនមែន Email)។ ឧ. `admin` + `password`។

---

> **ជំពូកទី ៣ — រចនាសម្ព័ន្ធទិន្នន័យ (Database Architecture)**

---

## ៣.១ តារាងទាំងអស់ (All 30 Business Tables + 11 Framework Tables)

តារាងទាំងអស់ត្រូវបានបង្កើតក្នុង file មួយ:
`database/migrations/2026_05_13_000000_create_super_market_pos_all_tables.php`

| # | Group | Tables | គោលបំណង |
|---|---|---|---|
| 1 | **Tenancy** | `branches` | សាខាផ្សារ (HQ + BR01 + ...) |
| 2 | **Auth** | `users`, `password_reset_tokens` | គណនី + Token reset password |
| 3 | **RBAC** | `roles`, `permissions`, `role_permission`, `user_role`, `user_branch` | Manual RBAC + multi-branch scoping |
| 4 | **i18n** | `languages`, `translations` | KH/EN language + DB-stored overrides |
| 5 | **Settings** | `settings` | Store name, currency, receipt footer (key/value) |
| 6 | **Catalog** | `categories`, `brands`, `units`, `tax_rates`, `products` | Master data ផលិតផល |
| 7 | **Stock** | `product_branch_stock` | Per-branch on-hand quantity (single source of truth) |
| 8 | **Parties** | `suppliers`, `customers`, `customer_groups` | អ្នកផ្គត់ផ្គង់ + អតិថិជន + ប្រភេទតម្លៃ |
| 9 | **Procurement** | `purchases`, `purchase_items`, `purchase_payments` | ការទិញ + ការទូទាត់ |
| 10 | **POS Infrastructure** | `pos_registers`, `pos_sessions` | តូទីលក់ + វេនអ្នកគិតលុយ |
| 11 | **Sales** | `sales`, `sale_items`, `sale_payments` | វិក្កយបត្រលក់ + payments ledger |
| 12 | **Stock Operations** | `stock_adjustments`, `stock_adjustment_items` | ការកែសម្រួលស្តុក (loss/damage/addition) |
| 13 | **Stock Transfers** | `stock_transfers`, `stock_transfer_items` | ការផ្ទេររវាងសាខា (sent → received) |
| 14 | **Expenses** | `expense_categories`, `expenses` | ប្រភេទចំណាយ + ចំណាយ |
| 15 | **Audit** | `activity_logs` | កំណត់ត្រាសកម្មភាពទាំងអស់ (append-only) |
| 16 | **Framework** | `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`, `personal_access_tokens` | Laravel runtime (no app logic) |

## ៣.២ Relationship Diagram (រូបភាពទំនាក់ទំនង)

```
Branch (1)
├── User (M via user_branch)
├── PosRegister (N)
│   └── PosSession (N)
│       └── Sale (N)
│           ├── SaleItem (N) → Product
│           └── SalePayment (N)
├── ProductBranchStock (N) → Product
├── Purchase (N) → Supplier
│   ├── PurchaseItem (N) → Product
│   └── PurchasePayment (N)
├── StockAdjustment (N)
│   └── StockAdjustmentItem (N) → Product
├── StockTransfer (from + to)
│   └── StockTransferItem (N) → Product
└── Expense (N) → ExpenseCategory

User
├── Role (M via user_role) → Permission (M via role_permission)
├── Branch (M via user_branch)
└── ActivityLog (N)   # auto-written on auth/sale/purchase/stock writes

Product
├── Category, Brand, Unit, TaxRate   (single FKs)
├── ProductBranchStock (N)            # qty per branch
└── SaleItem, PurchaseItem, StockAdjustmentItem, StockTransferItem
```

## ៣.៣ Single Source of Truth សម្រាប់ Stock

រាល់ការផ្លាស់ប្តូរស្តុក **ត្រូវឆ្លងកាត់** តែ Service មួយ:

```php
App\Services\StockService::applyDelta(int $branchId, int $productId, float $delta)
```

កន្លែងដែលហៅ:
1. **PurchaseController::store** — `+qty` (បន្ថែមស្តុក)
2. **PosController::checkout** — `-qty` (កាត់ស្តុក)
3. **StockAdjustmentController::store** — `+qty` ឬ `-qty` (តាម reason)
4. **StockTransferController::store** — `-qty` នៅ from_branch
5. **StockTransferController::receive** — `+qty` នៅ to_branch

ទាំងអស់ដំណើរការក្នុង `DB::transaction()` ដូច្នេះប្រសិនបើមាន Error អ្វីមួយ, ស្តុកនឹង Rollback ស្អាត។

---

> **ជំពូកទី ៤ — ម៉ូឌុល និងមុខងារទាំងអស់**

---

## ៤.១ Dashboard (ផ្ទាំងគ្រប់គ្រង)

**URL**: `/admin`

បង្ហាញស្ថិតិសង្ខេប:
- ចំនួនផលិតផល
- ចំនួនសាខា
- ការលក់ថ្ងៃនេះ (សរុបជាប្រាក់)
- ចំនួនវិក្កយបត្រលក់ថ្ងៃនេះ
- Latest activity log entries

## ៤.២ Branch Management (គ្រប់គ្រងសាខា)

**URL**: `/admin/branches`

- **Create**: បន្ថែមសាខាថ្មី (កូដ ឈ្មោះ ទូរសព្ទ អាសយដ្ឋាន Manager ស្ថានភាព)
- **Edit / Delete**: កែ ឬលុបសាខា (SweetAlert2 confirm)
- **DataTable**: Search + Sort + Pagination ដោយ Server-Side (Yajra)

## ៤.៣ User & Access Control

### Users (អ្នកប្រើ)
**URL**: `/admin/users`

- បង្កើត / កែ / លុបអ្នកប្រើ
- កំណត់ Username, Email, Phone, Password
- **Avatar Upload**: អនុញ្ញាតបង្ហោះរូបភាព Profile (saved to `storage/avatars/`)
- **Locale**: ជ្រើស `en` ឬ `kh` ជា default locale របស់អ្នកប្រើ
- **Default Branch**: សាខាដែលអ្នកប្រើនឹង Login ចូលដោយស្វ័យប្រវត្តិ
- កំណត់ Role(s) + Branch(es)

### Roles (តួនាទី)
**URL**: `/admin/roles`

- បង្កើត Role ថ្មី (ឧ. `inventory-clerk`, `accountant`)
- ភ្ជាប់ Permissions ទៅ Role
- Roles ដែល Seed រួច: `super-admin`, `branch-manager`, `cashier`

### Permissions (សិទ្ធិ)
**URL**: `/admin/permissions`

ប្រព័ន្ធមាន Permission 60+ ផ្តើមដោយ Module:
- `branches.{view,create,edit,delete}`
- `products.{view,create,edit,delete}`
- `pos.access`, `sales.{view,create}`
- `purchases.{view,create,edit,delete}`
- `stock_transfers.{view,create,receive}`
- `reports.view`
- `activity_logs.view`
- `users.*`, `roles.*`, `permissions.*`

> **ចំណាំ**: Super Admin មាន flag `is_super_admin=true` ដែល bypass គ្រប់ Permission checks — សុវត្ថិភាពពេលបន្ថែម Permission ថ្មី។

## ៤.៤ Catalog (ផលិតផល និងគ្រឿងបន្សំ)

### Categories (ប្រភេទផលិតផល)
**URL**: `/admin/categories`
- បង្កើតប្រភេទ (ឧ. Beverages, Snacks, Dairy, Bakery)
- Parent ID សម្រាប់ Sub-category (hierarchical)

### Brands (ម៉ាក)
**URL**: `/admin/brands`
- បន្ថែម brand + logo (optional)

### Units (ឯកតា)
**URL**: `/admin/units`
- កំណត់ឯកតារាប់ (piece, kg, liter, box, pack)

### Tax Rates (អត្រាពន្ធ)
**URL**: `/admin/tax-rates`
- បន្ថែមអត្រាពន្ធ (ឧ. VAT 10%)
- **Inclusive vs Exclusive**: ពន្ធដែលរួមក្នុងតម្លៃ ឬបន្ថែមលើតម្លៃ

### Products (ផលិតផល)
**URL**: `/admin/products`
- **SKU / Barcode**: លេខសម្គាល់
- **Name (KH / EN)**: ឈ្មោះពីរភាសា
- **Cost / Sale Price**: តម្លៃទុន និងតម្លៃលក់
- ភ្ជាប់ទៅ Category, Brand, Unit, Tax Rate
- **Active flag**: បិទផលិតផលដែលលែងលក់ (មិនលុបចេញ)

## ៤.៥ Parties (អ្នកផ្គត់ផ្គង់ និងអតិថិជន)

### Suppliers (អ្នកផ្គត់ផ្គង់)
**URL**: `/admin/suppliers`
- កូដ ឈ្មោះ ទូរសព្ទ អ៊ីមែល អាសយដ្ឋាន ស្ថានភាព

### Customers (អតិថិជន)
**URL**: `/admin/customers`
- ភ្ជាប់ទៅ Customer Group (សម្រាប់តម្លៃពិសេស)
- ស្ថានភាព Active

### Customer Groups (ប្រភេទអតិថិជន)
**URL**: `/admin/customer-groups`
- ឧ. Wholesale, VIP, Regular

## ៤.៦ Procurement (ការទិញពីអ្នកផ្គត់ផ្គង់)

### Purchases (ការទិញ)
**URL**: `/admin/purchases`

- **Create**: ជ្រើស Supplier + Branch → បន្ថែម Line Items (Product, Qty, Unit Cost, Tax) → Save
- **Status**: `draft`, `ordered`, `received`, `cancelled`
- ពេលរក្សាទុក → ស្តុកនៅ Branch កើនឡើងភ្លាមៗ (ឆ្លងកាត់ `StockService::applyDelta`)
- **Ref No** ត្រូវបង្កើតស្វ័យប្រវត្តិ: `PUR-YYYYMMDD-XXXXXX`

### Purchase Payments (ការទូទាត់ទៅអ្នកផ្គត់ផ្គង់)
- Add Payment លើ Purchase មួយ
- Method: cash / bank / credit / other
- Auto-update `purchases.paid` field

## ៤.៧ POS (Point of Sale)

### POS Registers (តូទីលក់)
**URL**: `/admin/pos-registers`
- បន្ថែមតូទីលក់ក្នុងសាខាមួយ (ឧ. "Counter 1", "Counter 2")

### POS Sessions (វេនអ្នកគិតលុយ)
**URL**: `/admin/pos-sessions`

- **Open Session**: Cashier ចូលវេន → បំពេញលុយចាប់ផ្តើម (opening cash)
- **Close Session**: ចុង​ថ្ងៃ → បំពេញលុយបិទ (closing cash)
- **Expected Cash**: ប្រព័ន្ធគណនាដោយស្វ័យប្រវត្តិ (opening + sum of cash sales)
- **Difference** = closing − expected (variance សម្រាប់ shift reconciliation)

### POS Register (Cart UI)
**URL**: `/admin/pos/register?session={id}`

Layout 3 ផ្នែក:
1. **ខាងឆ្វេង**: Product grid + Search (click to add to cart)
2. **កណ្តាល**: Cart line items (qty, price, tax, remove)
3. **ខាងស្តាំ**: Customer picker + totals + payment method + "Complete Sale" button

ពេលចុច **Complete Sale**:
- Sale row ត្រូវបង្កើតរួចមាន `ref_no` ដូច `SAL-YYYYMMDD-XXXXXX`
- Sale Items រួមជាមួយតម្លៃ + ពន្ធ
- Sale Payment ត្រូវកត់ត្រា (cash/card/credit/other)
- **Stock decrement** សម្រាប់រាល់ item → `StockService::applyDelta(branch, product, -qty)`
- POS Session `expected_cash` កើនឡើងតាមចំនួនលុយសុទ្ធ
- Activity log entry `sale.completed` ត្រូវសរសេរ

> **ចំណាំសំខាន់**: ត្រូវ Open Session មុនលក់។ បើ Session បិទរួច (closed_at != null) ការ Submit នឹង 422 error។

### Sales (វិក្កយបត្រលក់)
**URL**: `/admin/sales`

- បញ្ជីវិក្កយបត្រទាំងអស់
- **Show**: បង្ហាញរបាយការណ៍លំអិតរបស់ Sale (សម្រាប់ Print receipt)
- Filter តាមថ្ងៃ, branch, cashier, customer

## ៤.៨ Stock Operations (ការគ្រប់គ្រងស្តុក)

### Stock Adjustments (ការកែសម្រួលស្តុក)
**URL**: `/admin/stock-adjustments`

ប្រើនៅពេលចង់កែស្តុកដោយដៃ (មិនមែនពីការទិញ ឬលក់):
- **Reason**: `addition` (+), `subtraction` (-), `damage` (-), `loss` (-)
- បន្ថែម Line Items (Product, Qty)
- Sign នៃ delta ត្រូវយកពី reason ដោយស្វ័យប្រវត្តិ

### Stock Transfers (ការផ្ទេររវាងសាខា)
**URL**: `/admin/stock-transfers`

**Workflow:**
1. **Create + Send**: សាខាប្រភព (HQ) → ជ្រើស to_branch (BR01) → បន្ថែម items → Save
   - Status: `sent`
   - ស្តុក HQ ត្រូវ -qty ភ្លាមៗ
   - ស្តុក BR01 មិនទាន់កើនឡើង (នៅ in-transit)
2. **Mark as Received**: ពេលទំនិញដល់ → ចុចប៊ូតុង Receive នៅ BR01
   - Status: `received`
   - ស្តុក BR01 +qty
   - Activity log `transfer.received`

> **ចំណាំ**: ការ Receive ត្រូវឆ្លងកាត់ SweetAlert2 confirm ដើម្បីការពារកុំឱ្យចុចដោយចៃដន្យ។

## ៤.៩ Expenses (ការគ្រប់គ្រងចំណាយ)

### Expense Categories
**URL**: `/admin/expense-categories`
- ឧ. Rent, Utilities, Salaries, Transportation, Office Supplies

### Expenses
**URL**: `/admin/expenses`
- ជ្រើស Category + Branch + Date + Amount + Payee + Note
- Activity log entry `expense.recorded`

## ៤.១០ Reports (របាយការណ៍)

**URL**: `/admin/reports/*`

| # | Report | URL | គោលបំណង |
|---|---|---|---|
| 1 | **Sales Summary** | `/admin/reports/sales-summary` | ការលក់ប្រចាំថ្ងៃ/សប្តាហ៍/ខែ |
| 2 | **Stock by Branch** | `/admin/reports/stock-by-branch` | ស្តុកប្រចាំសាខា (រួម low-stock badge) |
| 3 | **Profit** | `/admin/reports/profit` | ចំណូលលុបចំណាយលុបការទិញ = ប្រាក់ចំណេញ |
| 4 | **Expenses by Category** | `/admin/reports/expenses` | ចំណាយតាមប្រភេទ |

ផ្ទាំងនីមួយៗមាន Date range filter (Flatpickr) + តារាងលទ្ធផល។

## ៤.១១ Activity Logs (កំណត់ត្រាសកម្មភាព)

**URL**: `/admin/activity-logs`

- កត់ត្រាសកម្មភាពទាំងអស់:
  - `auth.login`, `auth.logout`
  - `sale.completed`
  - `purchase.received`
  - `transfer.sent`, `transfer.received`
  - `stock.adjusted`
  - `expense.recorded`
  - `password.reset`
- Append-only — មិនអាចលុប ឬកែ
- Display: Time, User, Action, Subject (Sale#42, etc.), IP, User-Agent, Payload (JSON)

## ៤.១២ Settings (ការកំណត់ប្រព័ន្ធ)

**URL**: `/admin/settings`

Key/value table — ប្តូរបាន:
- Store name
- Default currency
- Receipt footer
- Default branch
- Low-stock threshold

## ៤.១៣ Languages & Translations

### Languages
**URL**: `/admin/languages`
- បន្ថែម / Activate ភាសាថ្មី
- Default: `en` (English), `kh` (Khmer)

### Translations
**URL**: `/admin/translations`
- DB-stored translation overrides
- Override file-based `lang/<locale>/messages.php` entries
- មានឯកសារ + DB → merged at runtime (DB ឈ្នះ)

## ៤.១៤ Profile (Profile អ្នកប្រើ)

**URL**: `/admin/profile`

- កែ Name / Email / Phone / Avatar / Locale
- Change Password (require current password)

## ៤.១៥ Forgot Password (Public Flow)

**URL**: `/forgot-password` (unauthenticated)

- បំពេញ Email → Receive reset link (via Mail Notification)
- ចុច link → `/reset-password/{token}` → set new password
- Token saved in `password_reset_tokens` table
- Dev mode: link សរសេរនៅ `storage/logs/laravel.log` (MAIL_MAILER=log)

---

> **ជំពូកទី ៥ — ការប្រើប្រាស់ប្រចាំថ្ងៃ (Daily Workflow)**

---

## ៥.១ វិធីប្រើប្រាស់មូលដ្ឋាន

### ជំហានដំបូងប្រចាំថ្ងៃ (សម្រាប់ Cashier)

1. **Login** ចូលប្រព័ន្ធ (ឧ. `cashier` / `password`)
2. បង្កើត POS Session ថ្មី (Open) → បំពេញ Opening Cash
3. ត្រឡប់ទៅ Dashboard ឬ POS Register
4. ចាប់ផ្តើមលក់!

### ការលក់ផលិតផល (POS Workflow)

```
POS Sessions → Open New Session (បំពេញ opening_cash)
    │
    ▼
POS Register (?session={id})
    ├─ ស្វែងរក / ចុចលើ Product → បន្ថែមក្នុង Cart
    ├─ កែ Qty ឬលុបធាតុ
    ├─ ជ្រើស Customer (optional)
    ├─ បំពេញ Discount (បើមាន)
    ├─ ជ្រើស Payment Method (cash/card/credit/other)
    ├─ បំពេញលុយដែលអតិថិជនបង់ (paid)
    └─ Complete Sale
        ├─ Sale ត្រូវបង្កើត (ref_no = SAL-YYYYMMDD-XXXXXX)
        ├─ Stock decrement សម្រាប់រាល់ item
        ├─ Sale Payment ត្រូវកត់ត្រា
        └─ Redirect → Sale Show page (សម្រាប់ Print)

ចុង​ថ្ងៃ:
POS Sessions → Close Session → បំពេញ Closing Cash → Save
```

### ការទិញពីអ្នកផ្គត់ផ្គង់

```
Purchases → Create
    ├─ ជ្រើស Supplier
    ├─ ជ្រើស Branch (សាខាដែលទទួលទំនិញ)
    ├─ បន្ថែម Items (Product, Qty, Unit Cost, Tax)
    ├─ បំពេញ Paid (បើទូទាត់ជាមួយ)
    ├─ ជ្រើស Payment Method
    └─ Save
        ├─ Purchase ត្រូវបង្កើត (ref_no = PUR-YYYYMMDD-XXXXXX)
        ├─ Stock increment សម្រាប់រាល់ item
        └─ Activity log: purchase.received
```

### ការផ្ទេរទំនិញរវាងសាខា

```
Stock Transfers → Create
    ├─ From Branch: HQ
    ├─ To Branch: BR01
    ├─ បន្ថែម Items (Product, Qty)
    └─ Save (Status: sent)
        └─ HQ stock decremented

ពេលដែលទំនិញដល់ BR01:
Stock Transfers → Show → "Mark as Received"
    └─ SweetAlert2 confirm → POST
        ├─ BR01 stock incremented
        ├─ Status: received
        └─ Activity log: transfer.received
```

### ការបញ្ចូលចំណាយ (Daily Expense)

```
Expenses → Create
    ├─ ជ្រើស Category (ឧ. Utilities)
    ├─ ជ្រើស Branch
    ├─ បំពេញ Date, Amount, Payee, Note
    └─ Save
        └─ Activity log: expense.recorded
```

### ការមើលរបាយការណ៍ប្រចាំថ្ងៃ

```
Reports → Sales Summary → ជ្រើស date range → View
Reports → Stock by Branch → ពិនិត្យ low-stock items (badge ក្រហម)
Reports → Profit → ពិនិត្យចំណេញ/ខាត
```

---

> **ជំពូកទី ៦ — ការគ្រប់គ្រងប្រព័ន្ធ (System Administration)**

---

## ៦.១ ការប្តូរភាសា (KH ↔ EN)

ប្រព័ន្ធគាំទ្រការប្តូរភាសាដោយ **មិនបាច់ Refresh ទំព័រ**:

1. ចុច language pill នៅជ្រុងស្តាំខាងលើ
2. ជ្រើស `English` ឬ `ភាសាខ្មែរ`
3. AJAX request → `POST /admin/locale` → ត្រឡប់ JSON dictionary
4. Sidebar + Page content swap ភាសាភ្លាមៗ
5. Session updated → ការ navigate បន្ទាប់នឹង render ភាសាថ្មី

**Technical**: ឧបករណ៍ប្រើ custom DOM event `smk:locale-changed` ដែលទាំង Blade-rendered nav (jQuery listener) និង React tree (`I18nProvider`) Listen ជាមួយគ្នា។

## ៦.២ ការបន្ថែម Translation ថ្មី

មាន ២ វិធី:

### វិធីទី ១ — Edit `lang/*/messages.php` (recommended for new keys)

```php
// lang/en/messages.php
'nav' => [
    'my_new_module' => 'My New Module',
],

// lang/kh/messages.php
'nav' => [
    'my_new_module' => 'ម៉ូឌុលថ្មីរបស់ខ្ញុំ',
],
```

ប្រើនៅ React/Blade:
```jsx
const { t } = useI18n();
<h4>{t('nav.my_new_module')}</h4>
```

### វិធីទី ២ — Translations CRUD (សម្រាប់ override)

`/admin/translations` → Create
- Key: `messages.nav.my_new_module`
- Locale: `kh`
- Value: `ឈ្មោះកែរបស់ខ្ញុំ`

DB entries override file-based entries នៅពេល runtime។

## ៦.៣ ការបង្កើត Role ថ្មី

```
Roles → Create
    ├─ Name: "Inventory Clerk"
    ├─ Slug: "inventory-clerk"
    ├─ Description: "ទទួលខុសត្រូវលើស្តុក"
    └─ Permissions: ជ្រើសសិទ្ធដែលត្រូវការ
        (ឧ. products.view, stock_transfers.view, stock_adjustments.*, reports.view)
Save
```

បន្ទាប់មក ភ្ជាប់ Role ទៅ User៖ Users → Edit → Roles → ជ្រើស Inventory Clerk → Save។

## ៦.៤ ការបង្កើត Branch ថ្មី

```
Branches → Create
    ├─ Code: "BR02"
    ├─ Name (KH): "សាខាទី២"
    ├─ Name (EN): "Branch 02"
    ├─ Phone, Address
    ├─ Manager: ជ្រើស User ដែលជា manager
    └─ Active: true
Save

បន្ទាប់មក:
- បង្កើត POS Register សម្រាប់ BR02
- ផ្ទេរ Stock ពី HQ → BR02 (Stock Transfer)
- កំណត់ Users មួយចំនួនឱ្យចូល BR02 (User → Branches → ជ្រើស BR02)
```

## ៦.៥ ការផ្លាស់ប្តូរ Settings ប្រព័ន្ធ

```
Settings → ស្វែងរក Key (ឧ. "store_name")
Edit → ផ្លាស់ប្តូរ Value → Save
```

Settings Keys សំខាន់ៗ:
- `store_name` — ឈ្មោះផ្សារ (ប្រើនៅ Receipt header)
- `default_currency` — រូបិយប័ណ្ណដើម (ឧ. `USD`, `KHR`)
- `receipt_footer` — អត្ថបទនៅខាងក្រោម receipt
- `low_stock_threshold` — ចំនួនកាត់ low-stock alert

---

> **ជំពូកទី ៧ — ការថែទាំ និងការដោះស្រាយបញ្ហា**

---

## ៧.១ Command សំខាន់ៗ (Artisan Commands)

```bash
# Clear caches (ប្រសិនបើមានបញ្ហា)
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize (បន្ទាប់ពី deploy)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database
php artisan migrate                          # Run pending migrations
php artisan migrate:fresh --seed             # Reset DB + seed all 41 seeders
php artisan db:seed --class=ProductSeeder    # Seed specific table
php artisan db:seed                          # Re-seed all (idempotent)

# Storage link (សម្រាប់ avatar uploads)
php artisan storage:link

# Audit / regression test (run before merging code changes)
php artisan audit:e2e                        # 51 assertions
bash tests/audit_smoke.sh                    # 93 routes
bash tests/audit_datatables.sh               # 23 DataTable endpoints

# Tinker (interactive SQL/PHP console)
php artisan tinker
```

## ៧.២ បញ្ហាដែលជួបប្រទះញឹកញាប់

| បញ្ហា | មូលហេតុ | ដំណោះស្រាយ |
|---|---|---|
| **Avatar មិនបង្ហាញ** | `storage:link` មិនបានធ្វើ | `php artisan storage:link` |
| **DataTable error / empty** | Database គ្មានទិន្នន័យ ឬ migrations មិនច្រើនជាងគ្នា | `php artisan migrate:fresh --seed` |
| **POS cart "No active session"** | Session បិទរួច ឬមិនបាន pass `?session={id}` | បើក POS Session ថ្មី → click POS Register link |
| **Stock មិនកាត់ ឬមិនកើន** | Bug ក្នុង Service / Observer ហៅផងគ្នា | `php artisan audit:e2e --only=stock` → ពិនិត្យ assertion failures |
| **SweetAlert delete មិនដំណើរការ** | `RowActions.jsx` ត្រូវ tree-shaken | រត់ `npm run build` ឡើងវិញ + ផ្ទៀងផ្ទាត់ `app.jsx` มาន `import './Components/RowActions'` |
| **KH/EN switcher មិនប្តូរអ្វីសោះ** | `data-i18n` attribute បាត់ ឬ `I18nProvider` មិន listen `smk:locale-changed` | ពិនិត្យ console — ត្រូវឃើញ `smk:locale-changed` event |
| **Permission denied (403)** | Role មិនមាន permission | Users → Edit → Roles → ផ្ទៀងផ្ទាត់; ឬមើល EnsurePermission middleware |
| **CSS / JS old version** | Vite cache | `npm run build` ឡើងវិញ + Browser hard refresh (Ctrl+Shift+R) |
| **Login fails with valid credentials** | Session driver issue | `php artisan migrate` (បើ sessions table មិនទាន់បាន) + `php artisan config:clear` |
| **Forgot password — link មិនមកជា Email** | `MAIL_MAILER=log` នៅ dev | មើល `storage/logs/laravel.log` → grep "Reset Password" → copy URL |

## ៧.៣ Backup ទិន្នន័យ (Data Backup)

### SQLite
```bash
# Copy database file
cp database/database.sqlite database/backup_$(date +%Y%m%d).sqlite
```

### MySQL
```bash
mysqldump -u erp -p super_market_erp > backup_$(date +%Y%m%d).sql

# Restore
mysql -u erp -p super_market_erp < backup_20260513.sql
```

### Daily backup cron (production)
```cron
0 2 * * * cd /var/www/super-market-erp && mysqldump -u erp -p$DB_PASS super_market_erp | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```

## ៧.៤ Activity Log Retention

ដើម្បីកុំឱ្យ `activity_logs` ធំពេក:

```bash
# Add to routes/console.php
Schedule::command('activity-logs:prune --keep-days=180')->daily();

# Or run manually
php artisan activity-logs:prune --keep-days=180
```

---

> **ជំពូកទី ៨ — សង្ខេប URL Routes**

---

## តារាង URL សំខាន់ៗ

### Public (Unauthenticated)
| មុខងារ | URL | Method |
|---|---|---|
| Login form | `/login` | GET |
| Login submit | `/login` | POST |
| Forgot password form | `/forgot-password` | GET |
| Forgot password submit | `/forgot-password` | POST |
| Reset password form | `/reset-password/{token}` | GET |
| Reset password submit | `/reset-password` | POST |

### Authenticated (`/admin/*`)
| មុខងារ | URL | ទំព័រ |
|---|---|---|
| Dashboard | `/admin` | ផ្ទាំងស្ថិតិសង្ខេប |
| Branches | `/admin/branches` | គ្រប់គ្រងសាខា |
| Users | `/admin/users` | គ្រប់គ្រងអ្នកប្រើ |
| Roles | `/admin/roles` | តួនាទី |
| Permissions | `/admin/permissions` | សិទ្ធិ |
| Languages | `/admin/languages` | ភាសា |
| Translations | `/admin/translations` | បកប្រែ |
| Settings | `/admin/settings` | កំណត់ប្រព័ន្ធ |
| Profile | `/admin/profile` | Profile របស់ខ្ញុំ |
| Categories | `/admin/categories` | ប្រភេទផលិតផល |
| Brands | `/admin/brands` | ម៉ាក |
| Units | `/admin/units` | ឯកតា |
| Tax Rates | `/admin/tax-rates` | ពន្ធ |
| Products | `/admin/products` | ផលិតផល |
| Suppliers | `/admin/suppliers` | អ្នកផ្គត់ផ្គង់ |
| Customers | `/admin/customers` | អតិថិជន |
| Customer Groups | `/admin/customer-groups` | ប្រភេទអតិថិជន |
| Purchases | `/admin/purchases` | ការទិញ |
| POS Registers | `/admin/pos-registers` | តូទីលក់ |
| POS Sessions | `/admin/pos-sessions` | វេនអ្នកគិតលុយ |
| POS Cart | `/admin/pos/register?session={id}` | UI លក់ |
| Sales | `/admin/sales` | វិក្កយបត្រលក់ |
| Stock Adjustments | `/admin/stock-adjustments` | កែសម្រួលស្តុក |
| Stock Transfers | `/admin/stock-transfers` | ផ្ទេរស្តុក |
| Expense Categories | `/admin/expense-categories` | ប្រភេទចំណាយ |
| Expenses | `/admin/expenses` | ចំណាយ |
| Reports — Sales Summary | `/admin/reports/sales-summary` | របាយការណ៍លក់ |
| Reports — Stock by Branch | `/admin/reports/stock-by-branch` | ស្តុកសាខា |
| Reports — Profit | `/admin/reports/profit` | ចំណេញ |
| Reports — Expenses | `/admin/reports/expenses` | ចំណាយតាមប្រភេទ |
| Activity Logs | `/admin/activity-logs` | កំណត់ត្រាសកម្មភាព |
| Locale Switch | `/admin/locale` (POST) | API ប្តូរភាសា |

---

> **ជំពូកទី ៩ — ការអភិវឌ្ឍបន្ត (Development Guide)**

---

## ៩.១ របៀបបន្ថែម Module ថ្មី (CRUD Pattern)

ប្រសិនបើចង់បន្ថែម Module ថ្មី (ឧ. "Loyalty Points"), សូមបន្ត ៧ ជំហានដូចគ្នាទៅគ្រប់ Module:

1. **Migration** (បន្ថែម table ក្នុង migration file ដែលមានស្រាប់ ឬបង្កើតថ្មី):
   ```bash
   php artisan make:migration create_loyalty_points_table
   ```

2. **Eloquent Model**:
   ```bash
   php artisan make:model LoyaltyPoint
   ```

3. **FormRequest** (validation):
   ```bash
   php artisan make:request Admin/LoyaltyPointRequest
   ```

4. **Controller**:
   ```bash
   php artisan make:controller Admin/LoyaltyPointController
   ```
   ដាក់ ៧ methods: `index`, `data`, `create`, `store`, `edit`, `update`, `destroy`
   ហើយ `use RendersDataTable` trait

5. **Routes** (បន្ថែមក្នុង `routes/web.php`):
   ```php
   Route::middleware('permission:loyalty_points.view')->group(function () {
       Route::get('loyalty-points', [LoyaltyPointController::class, 'index'])->name('loyalty-points.index');
       Route::get('loyalty-points/data', [LoyaltyPointController::class, 'data'])->name('loyalty-points.data');
   });
   // ... + create/edit/delete groups
   ```

6. **React Pages**:
   ```
   resources/js/Pages/LoyaltyPoints/Index.jsx
   resources/js/Pages/LoyaltyPoints/Form.jsx
   ```

7. **Seeder + Permissions**:
   - Update `PermissionSeeder` to include `loyalty_points.{view,create,edit,delete}`
   - Update `RolePermissionSeeder` to attach to relevant roles
   - Update `lang/{en,kh}/messages.php` with new menu/field strings
   - Update sidebar (`resources/views/admin/layouts/left_sidebar.blade.php`) with `@can('loyalty_points.view')` block

> **សូមមើល `docs/tutorial/08-crud-modules.md` សម្រាប់ឧទាហរណ៍ពេញលេញ (Branches as template)។**

## ៩.២ របៀបបន្ថែម Stock Mutation ថ្មី

ប្រសិនបើបង្កើត Module ដែលប៉ះស្តុក (ឧ. Return / Refund):

```php
// ALWAYS ឆ្លងកាត់ StockService - DON'T touch product_branch_stock directly
DB::transaction(function () use ($request, $stock) {
    foreach ($items as $row) {
        $stock->applyDelta($branchId, $row['product_id'], +$row['qty']);  // OR -qty
    }
});

// Then log it
$logger->log('return.completed', $return, ['ref_no' => $return->ref_no]);
```

## ៩.៣ Conventions ចំបាច់

- **Permission slug**: `module.action` (ឧ. `products.view`)
- **Route name**: `admin.<module>.<action>` (ឧ. `admin.products.index`)
- **Page file**: `<Module>/Index.jsx`, `<Module>/Form.jsx`
- **Sidebar items**: ដាក់ `data-i18n="<key>"` attribute ដើម្បីឱ្យ KH/EN switch ដំណើរការ
- **Action slug for ActivityLogger**: `<noun>.<verb>` (ឧ. `sale.completed`)
- **Ref no prefix**: `SAL-`, `PUR-`, `TRN-`, `ADJ-` + `YYYYMMDD` + 6-char random

## ៩.៤ គោលការសុវត្ថិភាព (Security)

- **CSRF Protection**: Inertia auto-includes X-CSRF-TOKEN; Blade has `@csrf` directive
- **Authorization**: `EnsurePermission` middleware on every admin route group
- **Password Hashing**: `'password' => 'hashed'` cast in User model (bcrypt)
- **SQL Injection**: Eloquent + Query Builder bind params automatically
- **XSS**: Inertia + React escape by default; Yajra `rawColumns([...])` must be reviewed
- **Audit Trail**: Every create/update/delete on business tables ត្រូវ log
- **HTTPS only** in production (HSTS header)
- **Secrets**: `.env` files **never** commit to git

## ៩.៥ ការសាកល្បង (Testing & Audit)

មាន ៤ ឧបករណ៍ regression testing៖

```bash
# 1. Pint (code style)
vendor/bin/pint --test

# 2. Vite build (frontend syntax check)
npm run build

# 3. End-to-end functional audit (51 assertions)
php artisan audit:e2e

# 4. HTTP smoke test (93 admin GET routes)
bash tests/audit_smoke.sh

# 5. DataTable JSON validity (23 endpoints)
bash tests/audit_datatables.sh
```

រត់ទាំងអស់មុនពេល commit/push. ប្រសិនបើ pass ទាំងអស់ — code ត្រឹមត្រូវ។

---

> **ជំពូកទី ១០ — ព័ត៌មានទំនាក់ទំនង**

---

## ឯកសារយោង (References)

- **GitHub Repo**: https://github.com/dusklofistudio-hash/super-market-erp
- **Developer Tutorial**: [`docs/tutorial/`](./docs/tutorial/) — 18 chapters from zero to deploy
- **Testing Skill**: [`.agents/skills/testing-super-market-erp/SKILL.md`](./.agents/skills/testing-super-market-erp/SKILL.md)
- **Laravel 12 Docs**: https://laravel.com/docs/12.x
- **Inertia.js Docs**: https://inertiajs.com
- **ReactJS Docs**: https://react.dev
- **Bootstrap 5**: https://getbootstrap.com/docs/5.3
- **Yajra DataTables**: https://yajrabox.com/docs/laravel-datatables
- **PHP-Flasher**: https://php-flasher.io
- **SweetAlert2**: https://sweetalert2.github.io
- **Flatpickr**: https://flatpickr.js.org
- **Tom Select**: https://tom-select.js.org

## អ្នកអភិវឌ្ឍ (Developer Notes)

ប្រព័ន្ធ Super Market ERP នេះត្រូវបានបង្កើតឡើងសម្រាប់គ្រប់គ្រងផ្សារទំនើបពហុសាខា ដោយប្រើ Laravel 12 + ReactJS 19 + Inertia.js (hybrid rendering)។ ស្ថាបត្យកម្មមានគោលដៅៈ

- **Centralized Stock Service** — រាល់ការផ្លាស់ប្តូរស្តុកឆ្លងកាត់ `StockService::applyDelta` តែមួយ
- **Centralized Audit Logger** — រាល់សកម្មភាពកត់ត្រាដោយ `ActivityLogger`
- **Manual RBAC** — មិនអាស្រ័យលើ Package ខាងក្រៅ (Spatie/etc.)
- **No-Refresh i18n** — ឆ្លងកាត់ custom DOM event `smk:locale-changed`
- **Server-Side DataTables** — Yajra ដោះស្រាយ pagination/search/sort លើ Backend
- **One Seeder Per Table** — ៤១ seeders, idempotent, FK-safe order

ប្រសិនបើមានសំណួរ ឬបញ្ហាណាមួយ:
- ពិនិត្យ Log files ក្នុង `storage/logs/laravel.log`
- មើល Activity Logs នៅ `/admin/activity-logs`
- រត់ `php artisan audit:e2e` ដើម្បីផ្ទៀងផ្ទាត់ writes
- ផ្ទុក Developer Tutorial នៅ `docs/tutorial/`

---

**សង្ខេបការប្រើប្រាស់រហ័ស (Quick Start Checklist)**

- [ ] `git clone https://github.com/dusklofistudio-hash/super-market-erp.git`
- [ ] `cd super-market-erp`
- [ ] `composer install`
- [ ] `npm install && npm run build`
- [ ] `cp .env.example .env`
- [ ] `php artisan key:generate`
- [ ] `touch database/database.sqlite` (បើ SQLite)
- [ ] `php artisan migrate:fresh --seed` (creates 30 tables + seeds 41 seeders)
- [ ] `php artisan storage:link`
- [ ] `php artisan serve` + (in another terminal) `npm run dev`
- [ ] Open `http://127.0.0.1:8000/login`
- [ ] Login: `admin` / `password`
- [ ] ប្តូរ default password នៅ `/admin/profile` (production)
- [ ] បង្កើត POS Session ដំបូង → ចាប់ផ្តើមលក់!
- [ ] Ready to use!

---

**Production Deployment Checklist**

- [ ] `APP_ENV=production` + `APP_DEBUG=false` in `.env`
- [ ] `APP_KEY` rotated and stored in secrets vault
- [ ] MySQL/MariaDB configured (not SQLite)
- [ ] `php artisan migrate --force` (no `:fresh` in production!)
- [ ] `php artisan db:seed --force` (only on first deploy)
- [ ] `php artisan config:cache route:cache view:cache`
- [ ] `npm run build` (produces `public/build/`)
- [ ] Storage permissions: `chmod -R ug+rwx storage bootstrap/cache`
- [ ] nginx + PHP-FPM + Supervisor (queue worker)
- [ ] HTTPS only (Let's Encrypt certbot)
- [ ] HSTS header enabled
- [ ] Daily MySQL backup cron
- [ ] Activity log prune cron (180 days retention)
- [ ] Replace seeded admin password with strong password
- [ ] Monitor: `tail -f storage/logs/laravel.log`

---

> បាន​បង្កើត​ដោយ​ក្រុម Dusk Lofi Studio
> Repository: https://github.com/dusklofistudio-hash/super-market-erp
