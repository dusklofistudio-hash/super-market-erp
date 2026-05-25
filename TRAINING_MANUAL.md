# មេរៀនប្រើប្រាស់ប្រព័ន្គ — Student Profile Management System
# (Training Manual)

> **ជំពូកទី ១ — ទិដ្ឋភាពទូទៅ**

---

## ១.១ អ្វីជា Project នេះ?

**Student Profile Management System** គឺជា Web Application ប្រើប្រាស់ Laravel Framework សម្រាប់គ្រប់គ្រងព័ត៌មានសិស្សពេញលេញ ចាប់ពីការចុះឈ្មោះ (Enrollment) រហូតដល់ការបោះពុម្ពកាត (Student Card) វិញ្ញាបនបត្រ (Certificate) និងសញ្ញាបត្រ (Diploma)។ ប្រព័ន្ធនេះគាំទ្រពហុសាខា (Multi-Branch) និងមានការគ្រប់គ្រសិទ្ធិ (Role-Based Access Control)។

## ១.២ បច្ចេកវិទ្យាដែលប្រើ (Tech Stack)

| ផ្នែក | បច្ចេកវិទ្យា |
|---|---|
| Backend Framework | **Laravel 12** (PHP 8.2+) |
| Frontend CSS | **TailwindCSS 3** |
| Frontend JS | **AlpineJS**, **Vue 3** |
| Build Tool | **Vite** |
| Database | **SQLite** (default) ឬ **MySQL** |
| DataTables | **Yajra DataTables** |
| Notifications | **PHP-Flasher**, **SweetAlert2** |
| Date Picker | **Flatpickr** |
| Select Box | **Tom Select** |
| Auth | **Laravel Breeze** |

## ១.៣ រចនាសម្ព័ន្ធ Project (Folder Structure)

```
student-profile-management/
├── app/
│   ├── Helpers/helpers.php          # Helper functions (current_branch_id, slug, etc.)
│   ├── Http/Controllers/           # 35+ Controllers for all modules
│   ├── Models/                       # 42 Eloquent Models
│   ├── Policies/                     # Authorization policies
│   └── Providers/                    # Service providers
├── config/
│   └── app.php                       # APP_NAME, APP_NAME_KH, APP_NAME_EN
├── database/
│   ├── migrations/
│   │   └── 2026_04_29_000000_create_student_profile_system_all_tables.php
│   └── seeders/                      # 25+ seeders with demo data
├── resources/
│   └── views/admin/                  # All admin Blade views
├── public/
│   └── images/logo.png               # School logo for cards
├── routes/
│   └── web.php                       # All application routes
└── .env                              # Environment configuration
```

---

> **ជំពូកទី ២ — ការដំឡើង (Installation & Setup)**

---

## ២.១ តម្រូវការប្រព័ន្ធ (System Requirements)

- **PHP**: 8.2 ឬខ្ពស់ជាង
- **Composer**: ជំនាន់ចុងក្រោយ
- **Node.js & NPM**: សម្រាប់ compile frontend assets
- **Database**: SQLite (default) ឬ MySQL 8.0+

## ២.២ ជំហានដំឡើង (Step-by-Step)

### ជំហាន ១ — Clone និង Install Dependencies

```bash
# 1. Clone project (ឬ extract zip)
cd c:\laragon\www\student-profile-management

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install
```

### ជំហាន ២ — កំណត់ Environment

```bash
# Copy file .env.example ទៅ .env
copy .env.example .env

# Generate application key
php artisan key:generate
```

បើក `.env` ហើយកែតម្រូវ:

```env
APP_NAME="SITS Information Technology School"
APP_NAME_KH="សាលាបច្ចេកវិទ្យាព័ត៌មាន អេស អាយ ធី អេស"
APP_NAME_EN="SITS INFORMATION TECHNOLOGY SCHOOL"

# For SQLite (default)
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# For MySQL (optional)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=student_profile_db
# DB_USERNAME=root
# DB_PASSWORD=
```

### ជំហាន ៣ — បង្កើត Database និង Migrate

```bash
# Create SQLite file (if using SQLite)
touch database/database.sqlite

# Run all migrations (creates 24 table groups)
php artisan migrate

# Seed demo data (25+ seeders with sample data)
php artisan db:seed
```

> **ចំណាំ**: Seeder រួមមានទិន្នន័យគំរូសម្រាប់រាជធានី-ខេត្ត ស្រុក ឃុំ ភូមិ ទាំងអស់នៅកម្ពុជា។

### ជំហាន ៤ — Compile Frontend Assets

```bash
# Development mode
npm run dev

# OR Production build
npm run build
```

### ជំហាន ៥ — ចាប់ផ្ដើម Server

```bash
php artisan serve
```

បើក Browser ទៅ `http://localhost:8000`

## ២.៣ គណនី Default (Login Credentials)

បន្ទាប់ពី `php artisan db:seed` គណនី Admin ដំបូងមាន:

| ប្រភេទ | Email | Password |
|---|---|---|
| Admin | `admin@sits.edu.kh` | `password` |

> ប្ដូរពាក្យសម្ងាត់ភ្លាមបន្ទាប់ពី Login លើកដំបូង!

---

> **ជំពូកទី ៣ — រចនាសម្ព័ន្ធទិន្នន័យ (Database Architecture)**

---

## ៣.១ តារាងទាំងអស់ (All 24 Table Groups)

តារាងត្រូវបានបង្កើតក្នុង file `database/migrations/2026_04_29_000000_create_student_profile_system_all_tables.php`:

| # | Group | Tables | គោលបំណង |
|---|---|---|---|
| 0 | **Branches** | `branches` | សាខាសាលា (multi-campus) |
| 1 | **Auth** | `roles`, `users`, `permissions`, `role_user`, `permission_role`, `permission_user`, `branch_user` | ប្រព័ន្ធសិទ្ធិ និងអ្នកប្រើ |
| 2 | **Locations** | `provinces`, `districts`, `communes`, `villages`, `addresses` | ទីតាំងភូមិសាស្ត្រកម្ពុជា |
| 3 | **Basic Data** | `genders` | ភេទ |
| 4 | **Staff** | `staff` | បុគ្គលិក និងគ្រូបង្រៀន |
| 5 | **Students** | `students` | ព័ត៌មានសិស្ស |
| 6 | **Guardians** | `guardians`, `student_guardians` | ព័ត៌មានឪពុកម្ដាយ/អាណាព្យាបាល |
| 7 | **Academic** | `courses`, `levels`, `academic_years`, `shifts` | វគ្គសិក្សា ថ្នាក់ ឆ្នាំសិក្សា វេន |
| 8 | **Buildings** | `buildings`, `rooms` | អគារ និងបន្ទប់ (រួមទាំង dormitory) |
| 9 | **Classes** | `classes`, `class_schedules`, `enrollments` | ថ្នាក់រៀន កាលវិភាគ ការចុះឈ្មោះ |
| 10 | **Room Assignments** | `student_room_assignments` | ការចាត់បន្ទប់ស្នាក់នៅ |
| 11 | **Files** | `student_files` | ឯកសារ និងរូបភាពសិស្ស |
| 12 | **Print Templates** | `print_templates` | ពុម្ពកាត វិញ្ញាបនបត្រ សញ្ញាបត្រ |
| 13 | **Student Cards** | `student_cards` | កាតសិស្ស |
| 14 | **Certificates** | `student_certificates` | វិញ្ញាបនបត្រ |
| 15 | **Diplomas** | `student_diplomas` | សញ្ញាបត្រ |
| 16 | **Fees** | `fee_types`, `student_invoices`, `student_invoice_items`, `payments` | ប្រភេទថ្លៃ វិក្កយបត្រ ការទូទាត់ |
| 17 | **Update Requests** | `student_update_requests` | សំណើកែប្រែព័ត៌មានសិស្ស |
| 18 | **Print Logs** | `print_logs` | កំណត់ត្រាការបោះពុម្ព |
| 19 | **Report Logs** | `report_logs` | កំណត់ត្រារបាយការណ៍ |
| 20 | **Export Logs** | `export_logs` | កំណត់ត្រានាំចេញទិន្នន័យ |
| 21 | **Audit Logs** | `audit_logs` | កំណត់ត្រាសកម្មភាពអ្នកប្រើ |
| 22 | **File Protection** | `file_protection_rules`, `file_access_logs` | ការការពារឯកសារ |
| 23 | **Branch Settings** | `branch_settings` | ការកំណត់សាខា (ឈ្មោះសាលា ឡូហ្គូ ហត្ថលេខា) |
| 24 | **Attendances** | `attendances` | វត្តមានសិស្ស និងបុគ្គលិក |

## ៣.២ Relationship Diagram (រូបភាពទំនាក់ទំនង)

```
Branch (1)
├── Users (N)
├── Staff (N)
├── Students (N)
│   ├── Guardians (M via student_guardians)
│   ├── Enrollments (N)
│   │   └── Classes (N)
│   ├── StudentCards (N)
│   ├── StudentCertificates (N)
│   ├── StudentDiplomas (N)
│   ├── StudentFiles (N)
│   ├── StudentInvoices (N)
│   │   ├── StudentInvoiceItems (N)
│   │   └── Payments (N)
│   └── StudentRoomAssignments (N)
│       └── Rooms (N)
│           └── Buildings (N)
├── AcademicYears (N)
├── Classes (N)
│   ├── Courses (N)
│   │   └── Levels (N)
│   ├── Shifts (N)
│   └── ClassSchedules (N)
└── BranchSettings (1)
```

---

> **ជំពូកទី ៤ — ម៉ូឌុល និងមុខងារទាំងអស់**

---

## ៤.១ Dashboard (ផ្ទាំងគ្រប់គ្រង)

**URL**: `/dashboard`

បង្ហាញស្ថិតិសង្ខេប:
- ចំនួនសិស្សសរុប / សិស្សសកម្ម
- ការចុះឈ្មោះដែលកំពុងសិក្សា
- វគ្គសិក្សា និងថ្នាក់សកម្ម
- វិក្កយបត្រដែលមិនទាន់បង់
- ការទូទាត់សរុប

> **ចំណាំ**: Dashboard បង្ហាញតែទិន្នន័យសាខាដែលបានជ្រើសរើស (branch-scoped)។

## ៤.២ Branch Management (គ្រប់គ្រងសាខា)

**URL**: `/admin/branches`

- **Create**: បន្ថែមសាខាថ្មី (កូដ ឈ្មោះខ្មែរ/អង់គ្លេស អាសយដ្ឋាន ទូរសព្ទ ឡូហ្គូ)
- **Switch**: ផ្លាស់ប្ដូរសាខាកំពុងប្រើ (session-based)
- **Settings**: កំណត់ឈ្មោះសាលា ឡូហ្គូ ហត្ថលេខា ស្តាប់សាលា លេខទូរសព្ទ អ៊ីមែល

## ៤.៣ User Management (គ្រប់គ្រងអ្នកប្រើ)

**URL**: `/admin/users`

- បង្កើត / កែ / លុបអ្នកប្រើ
- កំណត់ Role និង Permission
- កំណត់សាខា (branch assignment)

## ៤.៤ Roles & Permissions (សិទ្ធិ)

**URLs**: `/admin/roles`, `/admin/permissions`

ប្រព័ន្ធសិទ្ធិមាន ៣ កម្រិត:
1. **Role** (ឧ. Admin, Manager, Receptionist, Teacher, Accountant)
2. **Permission** (ឧ. `students.view`, `students.create`, `students.edit`, `students.delete`)
3. **Permission per User** (override ជារបស់គណនី)

## ៤.៥ Student Management (គ្រប់គ្រងសិស្ស)

**URL**: `/admin/students`

ព័ត៌មានសិស្សរួមមាន:
- **បញ្ជីសិស្ស**: DataTable មាន Search, Filter, Export
- **បង្កើតសិស្ស**: បំពេញកូដសិស្ស ឈ្មោះខ្មែរ/ឡាតាំង ភេទ ថ្ងៃកំណើត ទីកន្លែងកំណើត អាសយដ្ឋានបច្ចុប្បន្ន ទូរសព្ទ អ៊ីមែល រូបថត
- **មើលលំអិត**: Profile សិស្សពេញលេញ
- **កែសម្រួល**: Update ព័ត៌មានណាមួយ
- **Soft Delete**: លុបបណ្ដោះអាសន្ន (recoverable)

### Sub-features per Student:

| Feature | URL Pattern | គោលបំណង |
|---|---|---|
| Files | `/admin/students/{id}/files` | Upload រូបភាព ឯកសារ (ប្រភេទ: photo, birth_certificate, id_card, certificate, diploma, document) |
| Room Assignments | `/admin/students/{id}/room-assignments` | ចាត់បន្ទប់ស្នាក់នៅ និង Check-in/Check-out |
| Cards | `/admin/students/{id}/cards` | បង្កើត/កែ/បោះពុម្ពកាតសិស្ស |
| Certificates | `/admin/students/{id}/certificates` | បង្កើត/កែ/បោះពុម្ពវិញ្ញាបនបត្រ |
| Diplomas | `/admin/students/{id}/diplomas` | បង្កើត/កែ/បោះពុម្ពសញ្ញាបត្រ |
| Update Requests | `/admin/students/{id}/update-requests` | សំណើកែប្រែព័ត៌មាន |

## ៤.៦ Guardian Management (គ្រប់គ្រងឪពុកម្ដាយ)

**URL**: `/admin/guardians`

- បង្កើតអាណាព្យាបាលថ្មី
- ភ្ជាប់ទៅនឹងសិស្ស (relationship: father, mother, guardian, etc.)
- កំណត់ **Primary Guardian** (is_primary = true) — នឹងបង្ហាញលើកាតសិស្ស

## ៤.៧ Academic Setup (ការកំណត់វិទ្យាស្ថាន)

### Courses (វគ្គសិក្សា)
**URL**: `/admin/courses`
- ឈ្មោះវគ្គ ការពិពណ៌នា ស្ថានភាព
- **Levels** (ថ្នាក់): ភ្ជាប់ទៅ Course (ឧ. វគ្គ IT → Level 1, Level 2, Level 3)

### Academic Years (ឆ្នាំសិក្សា)
**URL**: `/admin/academic-years`
- ឈ្មោះឆ្នាំ ថ្ងៃចាប់ផ្ដើម ថ្ងៃបញ្ចប់
- កំណត់ **Current Year** (is_current = true)

### Shifts (វេន)
**URL**: `/admin/shifts`
- ឈ្មោះវេន ម៉ោងចាប់ផ្ដើម ម៉ោងបញ្ចប់ (ឧ. Morning 8:00-11:00, Afternoon 13:00-16:00)

## ៤.៨ Class Management (គ្រប់គ្រងថ្នាក់)

**URL**: `/admin/classes`

- **Class Code**: កូដថ្នាក់ (ឧ. IT-L1-MOR-2026)
- **Course & Level**: ភ្ជាប់ទៅវគ្គ និងថ្នាក់
- **Academic Year & Shift**: ភ្ជាប់ឆ្នាំសិក្សា និងវេន
- **Teacher**: ជ្រើសរើសពី Staff
- **Room**: បន្ទប់រៀន
- **Schedules**: កាលវិភាគប្រចាំសប្ដាហ៍ (ថ្ងៃ ម៉ោងចាប់ផ្ដើម-បញ្ចប់)

## ៤.៩ Enrollment (ការចុះឈ្មោះ)

**URL**: `/admin/enrollments`

- ចុះឈ្មោះសិស្សចូលថ្នាក់
- **Status**: studying, completed, dropped, transferred
- **Study Time Label**: សម្គាល់ពេលវេលាសិក្សា
- បង្ហាញប្រវត្តិចុះឈ្មោះទាំងអស់របស់សិស្ស

## ៤.១០ Attendance (វត្តមាន)

**URL**: `/admin/attendances`

- បំពេញវត្តមានប្រចាំថ្ងៃ (បុគ្គលិក ឬសិស្ស)
- **Status**: present, absent, late, excused
- **Check-in / Check-out time**
- Bulk Entry: បំពេញវត្តមានច្រើននាក់ក្នុងថ្នាក់តែម្ដង

## ៤.១១ Fee Management (គ្រប់គ្រងថ្លៃ)

### Fee Types (ប្រភេទថ្លៃ)
**URL**: `/admin/fees/types`
- ឈ្មោះប្រភេទថ្លៃ (ឧ. ថ្លៃសិក្សា, ថ្លៃសៀវភៅ, ថ្លៃប្រឡង) និងចំនួនទឹកប្រាក់

### Invoices (វិក្កយបត្រ)
**URL**: `/admin/fees/invoices`
- បង្កើតវិក្កយបត្រសម្រាប់សិស្ស
- បន្ថែមធាតុ (items) ពី Fee Types
- **Status**: unpaid, partial, paid, cancelled
- គណនាតុល្យភាព (Balance) ដោយស្វ័យប្រវត្តិ

### Payments (ការទូទាត់)
**URL**: `/admin/fees/payments`
- ទទួលការទូទាត់ពីសិស្ស
- **Method**: cash, bank, ABA, Wing, other
- ភ្ជាប់ទៅ Invoice (optional)
- គណនាតុល្យភាពវិក្កយបត្រដោយស្វ័យប្រវត្តិ

## ៤.១២ Room & Building (អគារ និងបន្ទប់)

**URL**: `/admin/rooms`

- **Buildings**: អគារថ្មី (ឈ្មោះ អាសយដ្ឋាន)
- **Rooms**: បន្ទប់ (លេខ ប្រភេទ: single/double/shared/classroom, ចំណុះ, តម្លៃប្រចាំខែ)
- **Status**: available, full, maintenance, inactive

## ៤.១៣ Print System (ប្រព័ន្ធបោះពុម្ព)

### Print Templates (ពុម្ពបោះពុម្ព)
**URL**: `/admin/print-templates`

ប្រព័ន្ធពុម្ពអាចកែប្រែបានទាំងស្រុង:
- **Template Types**: student_card, certificate, diploma
- **HTML Template**: កែ HTML structure
- **CSS Template**: កែ styling
- **Settings**: JSON config បន្ថែម
- **Default Template**: កំណត់ពុម្ពដើម្បីប្រើដោយស្វ័យប្រវត្តិ

### Student Cards (កាតសិស្ស)
**URL**: `/admin/student-cards`

- បង្ហាញកាតទាំងអស់ក្នុងប្រព័ន្ធ
- **Bulk Print**: ជ្រើសរើសច្រើនកាត → Print ព្រមគ្នា (4 កាត/ទំព័រ A4)
- **Single Print**: Print កាតតែមួយ (ទំហំពេញ A4 landscape)
- **Card Layout** (បច្ចុប្បន្ន): Header ពណ៌ខៀវ `#0000ff` + ឈ្មោះសាលា រាងកោងក្រហមតាមរយៈ SVG រូបថត និងព័ត៌មានសិស្ស

### Certificates & Diplomas
**URL**: `/admin/student-certificates`, `/admin/student-diplomas`

- **Workflow**: Draft → Approved → Printed → Cancelled
- **Approval**: អ្នកមានសិទ្ធិ approve ទើបបោះពុម្ពបាន
- **Print Log**: កត់ត្រាចំនួនការបោះពុម្ព

## ៤.១៤ Reports (របាយការណ៍)

**URL**: `/admin/reports`

របាយការណ៍ដែលមាន:
1. **Student Report** — បញ្ជីសិស្សតាមលក្ខខណ្ឌ
2. **New Admissions** — សិស្សចុះឈ្មោះថ្មី
3. **Class Roster** — បញ្ជីសិស្សតាមថ្នាក់
4. **Monthly Attendance** — វត្តមានប្រចាំខែ
5. **Daily Cash Receipts** — ទទួលលុយប្រចាំថ្ងៃ
6. **AR Aging** — វិក្កយបត្រដែលនៅជំពាក់
7. **Revenue Report** — ចំណូលសរុប
8. **Fee Statement** — របាយការណ៍ថ្លៃសិក្សា

**Export Formats**: PDF, Excel, CSV, Print, View

## ៤.១៥ Logs & Auditing (កំណត់ត្រា)

| Log Type | URL | គោលបំណង |
|---|---|---|
| Audit Logs | `/admin/audit-logs` | កត់ត្រាសកម្មភាពទាំងអស់ (create, update, delete) |
| Report Logs | `/admin/report-logs` | កត់ត្រាការបង្កើតរបាយការណ៍ |
| Export Logs | `/admin/export-logs` | កត់ត្រាការនាំចេញទិន្នន័យ |
| Print Logs | `/admin/print-logs` | កត់ត្រាការបោះពុម្ព |
| File Access Logs | `/admin/file-access-logs` | កត់ត្រាការចូលមើលឯកសារ |

## ៤.១៦ File Protection (ការពារឯកសារ)

**URL**: `/admin/file-protection-rules`

កំណត់តាម Role:
- Allow Download? (បើក/បិទ)
- Allow Print? (បើក/បិទ)
- Allow Export? (បើក/បិទ)
- Watermark? (បើក/បិទ)

---

> **ជំពូកទី ៥ — ការប្រើប្រាស់ប្រចាំថ្ងៃ (Daily Workflow)**

---

## ៥.១ វិធីប្រើប្រាស់មូលដ្ឋាន

### ជំហានដំបូងប្រចាំថ្ងៃ

1. **Login** ចូប្រព័ន្ធ
2. **ជ្រើសរើសសាខា** (Branch Switcher នៅខាងលើអេក្រង់)
3. **ពិនិត្យ Dashboard** សម្រាប់ស្ថិតិសង្ខេប

### ការចុះឈ្មោះសិស្សថ្មី

```
Students → Create New
  ├─ បំពេញព័ត៌មានផ្ទាល់ខ្លួន
  ├─ បំពេញអាសយដ្ឋាន (ខេត្ត → ស្រុក → ឃុំ → ភូមិ)
  ├─ Upload រូបថត
  ├─ ភ្ជាប់ Guardian (father/mother/guardian)
  └─ បង្កើត Card (បើាត្រូវការ)
      └─ Print Card
```

### ការចុះឈ្មោះចូលថ្នាក់

```
Enrollments → Create
  ├─ ជ្រើសរើសសិស្ស
  ├─ ជ្រើសរើសថ្នាក់ (Class)
  ├─ ជ្រើសរើសឆ្នាំសិក្សា
  ├─ ជ្រើសរើសវេន (Shift)
  └─ កំណត់ស្ថានភាព: studying
```

### ការបង់ថ្លៃសិក្សា

```
Fees → Invoices → Create
  ├─ ជ្រើសរើសសិស្ស
  ├─ បន្ថែម Items (ប្រភេទថ្លៃ + ចំនួន)
  ├─ រក្សាទុក → Status: unpaid
  │
  └─ Payments → Create
      ├─ ជ្រើសរើស Invoice
      ├─ បំពេញចំនួនលុយ
      ├─ ជ្រើស Method (cash/bank/ABA/Wing)
      └─ Save → តុល្យភាពវិក្កយបត្រត្រូវបាន update ដោយស្វ័យប្រវត្តិ
```

### ការបោះពុម្ពកាតសិស្ស

```
Students → [សិស្ស] → Cards
  ├─ Create Card → បំពេញថ្ងៃចេញ និងថ្ងៃផុតកំណត់
  └─ Print → បោះពុម្ពកាតតែមួយ (A4 landscape)

ឬ

Student Cards → ជ្រើសរើសច្រើនកាត → Bulk Print → Print ព្រមគ្នា (4 កាត/ទំព័រ)
```

---

> **ជំពូកទី ៦ — ការគ្រប់គ្រងប្រព័ន្ធ (System Administration)**

---

## ៦.១ ការប្ដូរឈ្មោះសាលា និងឡូហ្គូ (Brand Configuration)

មានពីររបៀប:

### របៀបទី ១ — Branch Settings (សម្រាប់គ្រប់សាខា)

```
Branches → [សាខា] → Settings
```

បំពេញ:
- School Name (Khmer / English)
- Logo path
- Stamp path
- Signature path
- Address, Phone, Email, Website

### របៀបទី ២ — Environment File (Global)

```env
# .env
APP_NAME="SITS Information Technology School"
APP_NAME_KH="សាលាបច្ចេកវិទ្យាព័ត៌មាន អេស អាយ ធី អេស"
APP_NAME_EN="SITS INFORMATION TECHNOLOGY SCHOOL"
```

## ៦.២ ការប្ដូរពុម្ពកាត (Customizing Card Template)

```
Print Templates → Create / Edit
  ├─ Name: Default Student Card
  ├─ Type: student_card
  ├─ HTML Template: កែ HTML structure
  ├─ CSS Template: កែ styling
  └─ Is Default: បើក → ពុម្ពនេះនឹងត្រូវប្រើដោយស្វ័យប្រវត្តិ
```

> **ចំណាំ**: HTML template ប្រើ `{{ $variable }}` syntax ដូច Blade template។

## ៦.៣ ការបង្កើត Role ថ្មី

```
Roles → Create
  ├─ Name: accountant
  ├─ Display Name: Accountant
  └─ Permissions: ជ្រើសសិទ្ធដែលត្រូវការ
      (ឧ. invoices.view, invoices.create, payments.view, payments.create)
```

## ៦.៤ ការបង្កើត Permission ថ្មី

```
Permissions → Create
  ├─ Name: reports.export
  ├─ Module: reports
  ├─ Display Name: Export Reports
  └─ Description: អនុញ្ញាតឲ្យនាំចេញរបាយការណ៍
```

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
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed       # Reset DB + seed
php artisan db:seed --class=UsersSeeder # Seed specific seeder

# Storage link (សម្រាប់ file uploads)
php artisan storage:link
```

## ៧.២ បញ្ហាដែលជួបប្រទះញឹកញាប់

| បញ្ហា | មូលហេតុ | ដំណោះស្រាយ |
|---|---|---|
| **រូបថតមិនបង្ហាញ** | `storage:link` មិនបានធ្វើ | `php artisan storage:link` |
| **ឡូហ្គូមិនបង្ហាញលើកាត** | គ្មាន `public/images/logo.png` | ដាក់រូបភាព logo ទៅ `public/images/logo.png` |
| **សិស្សមិនបង្ហាញ** | Branch filter មិនត្រឹមត្រូវ | ពិនិត្យ Branch Switcher នៅខាងលើ |
| **Permission denied** | Role មិនមានសិទ្ធិ | ពិនិត្យ Role និង Permission |
| **DataTable error** | Database គ្មានទិន្នន័យ | `php artisan db:seed` |
| **ទំព័រចុះពណ៌** | npm build មិនបានធ្វើ | `npm run build` |

## ៧.៣ Backup ទិន្នន័យ (Data Backup)

### SQLite
```bash
# Copy database file
copy database\database.sqlite database\database_backup_YYYYMMDD.sqlite
```

### MySQL
```bash
mysqldump -u root -p student_profile_db > backup_YYYYMMDD.sql
```

---

> **ជំពូកទី ៨ — សង្ខេប URL Routes**

---

## តារាង URL សំខាន់ៗ

| មុខងារ | URL | ទំព័រ |
|---|---|---|
| ផ្ទាំងគ្រប់គ្រង | `/dashboard` | Dashboard |
| សិស្ស | `/admin/students` | បញ្ជីសិស្ស |
| បង្កើតសិស្ស | `/admin/students/create` | Form បង្កើត |
| កាតសិស្សសកល | `/admin/student-cards` | Global card list |
| Bulk Print | `/admin/student-cards/bulk-print` | Print ច្រើនកាត |
| វិក្កយបត្រ | `/admin/fees/invoices` | បញ្ជីវិក្កយបត្រ |
| ទូទាត់ | `/admin/fees/payments` | បញ្ជីការទូទាត់ |
| របាយការណ៍ | `/admin/reports` | ផ្ទាំងរបាយការណ៍ |
| អ្នកប្រើ | `/admin/users` | គ្រប់គ្រងអ្នកប្រើ |
| សាខា | `/admin/branches` | គ្រប់គ្រងសាខា |

---

> **ជំពូកទី ៩ — ការអភិវឌ្ឍបន្ត (Development Guide)**

---

## ៩.១ របៀបបន្ថែម Module ថ្មី

ប្រសិនបើចង់បន្ថែមមុខងារថ្មី:

1. **Create Migration**: បន្ថែម table ទៅ `database/migrations/...`
2. **Create Model**: `php artisan make:model NewModel`
3. **Create Controller**: `php artisan make:controller NewModelController`
4. **Create Routes**: បន្ថែមទៅ `routes/web.php`
5. **Create Views**: បង្កើត folder ក្នុង `resources/views/admin/...`
6. **Add Permissions**: បន្ថែមទៅ `RolesPermissionsSeeder`
7. **Seed Data**: បង្កើត Seeder បើាត្រូវការទិន្នន័យគំរូ

## ៩.២ គោលការសុវត្ថិភាព (Security)

- **CSRF Protection**: Laravel Breeze មានរួចជាស្រេច
- **Authorization**: `can:module.action` middleware គ្រប់ routes
- **Password Hashing**: bcrypt hashing ដោយស្វ័យប្រវត្តិ
- **Soft Deletes**: មិនលុបពិតប្រាកដ — recoverable
- **Audit Trail**: គ្រប់ create/update/delete ត្រូវបានកត់ត្រា

---

> **ជំពូកទី ១០ — ព័ត៌មានទំនាក់ទំនង**

---

## ឯកសារយោង (References)

- **Laravel Docs**: https://laravel.com/docs/12.x
- **TailwindCSS**: https://tailwindcss.com/docs
- **AlpineJS**: https://alpinejs.dev
- **Yajra DataTables**: https://yajrabox.com/docs/laravel-datatables

## អ្នកអភិវឌ្ឍ (Developer Notes)

ប្រព័ន្ធនេះត្រូវបានបង្កើតឡើងសម្រាប់គ្រប់គ្រងព័ត៌មានសិស្សពេញលេញ ដោយប្រើ Laravel Framework ជំនាន់ចុងក្រោយ។ ប្រសិនបើមានសំណួរ ឬបញ្ហាណាមួយ សូមពិនិត្យ Log files ក្នុង `storage/logs/` ឬមើល Audit Logs នៅក្នុងប្រព័ន្ធ។

---

**សង្ខេបការប្រើប្រាស់រហ័ស (Quick Start Checklist)**

- [ ] Clone Project
- [ ] `composer install`
- [ ] `npm install && npm run build`
- [ ] Copy `.env.example` → `.env`
- [ ] `php artisan key:generate`
- [ ] Create database file
- [ ] `php artisan migrate`
- [ ] `php artisan db:seed`
- [ ] `php artisan storage:link`
- [ ] Login with `admin@sits.edu.kh` / `password`
- [ ] Switch to correct branch
- [ ] Upload logo to `public/images/logo.png`
- [ ] Ready to use!

---

*ឯកសារនេះត្រូវបានបង្កើតឡើងដោយស្វ័យប្រវត្តិពី Project Audit — ថ្ងៃទី ៥ ឧសភា ២០២៦*
