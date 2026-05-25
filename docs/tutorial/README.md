# Super Market ERP — Developer Tutorial

A step-by-step curriculum that walks you through building this multi-branch
Super Market ERP system **from an empty directory all the way to a
production deployment that end users can log into**.

This tutorial is written for the developer who clones this repo and wants
to understand **why** every file is shaped the way it is, then be able to
rebuild the same architecture for a different domain.

## Audience and prerequisites

- **You should be comfortable with:** PHP 8.2+, Laravel basics (routing,
  Eloquent, migrations), JavaScript / React, the command line.
- **You do NOT need prior experience with:** Inertia.js, Yajra DataTables,
  SweetAlert2, PHPFlasher, manual RBAC, or multi-tenant patterns. The
  tutorial introduces each piece in isolation before composing them.
- **OS:** Linux, macOS, or WSL2. Native Windows works but command examples
  assume a POSIX shell.

## What you will build

A Laravel 12 + Inertia/React 19 admin app that ships with:

- 30 database tables modeling branches, catalog, parties, purchases,
  sales/POS, stock, expenses, activity logs, RBAC, and i18n.
- 28 admin controllers backing 17 master-data CRUD modules + POS + Sales
  + Purchases + Stock ops + Expenses + Reports + Activity logs.
- A Blade chrome (`admin_layout.blade.php` + 4 partials) that wraps every
  Inertia/React page so the sidebar, header, language pill, and shared
  scripts (Bootstrap 5, DataTables, SweetAlert2, PHPFlasher, flatpickr,
  Tom Select) only render once per session.
- A manual RBAC system (no Spatie, no third-party package) with roles,
  permissions, per-user branch scoping, and middleware that returns
  HTTP 403 on any unauthorized action.
- No-refresh Khmer ↔ English switching driven by a custom DOM event,
  fully covering Blade-rendered nav and React-rendered content.
- Server-side Yajra DataTables on every list page with Bootstrap 5
  pagination, search, and column sort.
- A canonical `StockService` that owns every inventory write so purchase
  receive, POS checkout, stock adjustment, and branch transfer all share
  the same atomic, audit-logged code path.
- An activity log viewer fed by an `ActivityLogger` service hooked into
  authentication, sales, purchases, stock writes, and password resets.

## How the curriculum is organized

Each chapter is a self-contained markdown file. Read them **in order** the
first time. Later, you can re-open any single chapter when you need to
refresh one specific piece.

| #  | Chapter                                          | What you'll learn                                  |
|----|--------------------------------------------------|----------------------------------------------------|
| 00 | [Prerequisites](./00-prerequisites.md)           | Install PHP/Composer/Node, pick a database, prep env |
| 01 | [Project bootstrap](./01-project-bootstrap.md)   | `laravel new`, install Inertia + React + Vite       |
| 02 | [Database schema](./02-database-schema.md)       | Read the consolidated migration table-by-table     |
| 03 | [Blade layouts](./03-blade-layouts.md)           | Build `admin_layout` + head/header/sidebar/scripts |
| 04 | [Manual RBAC](./04-manual-rbac.md)               | Roles, permissions, middleware, sidebar gating     |
| 05 | [i18n no-refresh](./05-i18n-no-refresh.md)       | KH/EN switcher + `smk:locale-changed` DOM event    |
| 06 | [Yajra DataTables](./06-yajra-datatables.md)     | Server-side tables with the `RendersDataTable` trait |
| 07 | [Frontend libraries](./07-frontend-libraries.md) | SweetAlert2, PHPFlasher, flatpickr, Tom Select     |
| 08 | [CRUD modules pattern](./08-crud-modules.md)     | The shared shape of all 17 master-data screens     |
| 09 | [POS and sales](./09-pos-and-sales.md)           | Registers, sessions, cart, receipt                 |
| 10 | [Stock operations](./10-stock-operations.md)     | Adjustments, transfers, `StockService::applyDelta` |
| 11 | [Purchases and expenses](./11-purchases-expenses.md) | Purchase orders, supplier payments, expense entry |
| 12 | [Reports](./12-reports.md)                       | Sales summary, profit, stock by branch, expenses   |
| 13 | [Activity logs](./13-activity-logs.md)           | `ActivityLogger` service + viewer                  |
| 14 | [Forgot password](./14-forgot-password.md)       | Public reset flow + tokens table                   |
| 15 | [Seeders](./15-seeders.md)                       | One seeder per table, idempotent, FK-safe          |
| 16 | [Testing and audit](./16-testing-audit.md)       | `audit:e2e`, `audit_smoke.sh`, browser tests       |
| 17 | [Deployment](./17-deployment.md)                 | Production server setup, queue, supervisor, HTTPS  |

## Conventions used in this tutorial

- Code blocks tagged ` ```php `, ` ```bash `, ` ```jsx ` are meant to be
  typed as shown.
- Output blocks are tagged ` ```text ` — do not type them.
- File paths are repo-relative (e.g. `app/Services/StockService.php`).
- "HQ" and "BR01" refer to the two seeded branches.
- "admin/manager/cashier" with password `password` are the three seeded
  test accounts.

## When something doesn't work

Each chapter ends with a **Verify** section that tells you the exact
command to run and the exact output to expect. If the output doesn't
match, the most common causes are:

1. Forgot to run `php artisan migrate:fresh --seed` after editing a
   migration or seeder.
2. Forgot to run `npm run build` after editing a React or JS file.
3. Forgot to clear `php artisan config:cache` after editing `.env`.
4. Created a new Inertia page but didn't add it to the controller's
   `Inertia::render(...)`.

Chapter 16 documents the three audit suites (`audit:e2e`,
`audit_smoke.sh`, `audit_datatables.sh`) that you can run after **any**
change to catch regressions.

Happy building.
