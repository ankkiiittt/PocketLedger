# PocketLedger

> A calm, precise personal finance web application to record income and expenses, set monthly category budgets, automate recurring transactions, and clearly see where the money goes.

---

## Table of Contents
- [Overview](#overview)
- [Screenshots](#screenshots)
- [Feature List](#feature-list)
- [Technology Stack](#technology-stack)
- [System Architecture & Principles](#system-architecture--principles)
- [Local Setup & Installation](#local-setup--installation)
- [Demo Account & Credentials](#demo-account--credentials)
- [Test & Verification Commands](#test--verification-commands)
- [Scheduler Note](#scheduler-note)
- [Production Deployment Steps](#production-deployment-steps)
- [Database Requirements](#database-requirements)
- [Documentation & Architectural Decisions](#documentation--architectural-decisions)

---

## Overview

PocketLedger provides envelope-style budgeting and disciplined ledger tracking without the visual noise, neon gradients, or gamification common to modern fintech apps. Designed like a well-kept paper ledger, it offers calm control, high contrast, predictable server rendering, and exact financial calculations using `DECIMAL(12,2)` and `bcmath`.

---

## Screenshots

<!-- Screenshots Placeholder -->
| Dashboard | Monthly Budgets | Transactions Ledger |
|:---:|:---:|:---:|
| *(Summary tiles, category donut, top budgets, recent entries)* | *(Ruled rows, limit inputs, progress bars & status)* | *(Filter bar, totals strip, ruled rows, pagination)* |

---

## Feature List

- **F1: Authentication and Account Settings**:
  - Secure registration, login with optional remember-me, single-use signed password resets (60 min expiry).
  - Rate limiting (5 failed logins/min per IP & email; 3 password reset requests/min).
  - Account settings: update name, email, preferred display currency (INR, USD, EUR, GBP, AED, SGD, AUD, CAD).
  - Change password and permanent account deletion (removing all owned data in safe foreign key sequence).
- **F2: Categories Management**:
  - Income and expense categories with 8 curated palette colours.
  - Unique name per user and type (case-insensitive).
  - Archive/restore capability (archived categories remain in historical ledger and reports, but disappear from entry pickers).
  - Type-lock and delete guard (categories with transactions or recurring rules cannot change type or be deleted).
- **F3: Transactions (The Ledger)**:
  - Add, edit, and delete transactions with instant budget feedback when adding an expense in a budgeted category.
  - Date filtering (default current month), type toggle, category filter, text search with wildcard escaping.
  - Comprehensive totals strip (Income, Expense, Net) calculated across the entire filtered dataset.
  - Safe pagination (20 per page) keeping all query parameters.
- **F4: Monthly Budgets**:
  - Monthly spending limits per expense category with live total recalculation while typing.
  - Progress bar with explicit status text: **On track** (<80%), **Nearing limit** (80%–100%), **Over budget** (>100%).
  - One-click "Copy from previous month" that fills unbudgeted categories without overwriting existing limits.
- **F5: Dashboard**:
  - Month switcher (previous, current label, next, "This month").
  - Summary tiles: Total Income, Total Expenses, Net Balance, Budget Used percentage.
  - Interactive expense category breakdown donut chart with accessible value legend.
  - Top 5 budgets by percentage used and 8 most recent transactions.
- **F6: Reports and CSV Export**:
  - Monthly financial breakdown with savings rate percentage.
  - Detailed expense category breakdown table with budget variance.
  - 6-month income versus expense trend chart with data table text alternative.
  - Top 5 largest expenses of the month.
  - Streamed CSV export with UTF-8 BOM and formula injection protection (neutralising `=`, `+`, `-`, `@`, `\t`, `\r`).
- **F7: Recurring Transactions**:
  - Automated rules on daily, weekly, monthly, and yearly schedules.
  - Month-end clamping (e.g., rules on day 31 automatically land on day 28/30 on short months and restore day 31 on subsequent months).
  - Idempotent execution (prevents duplicates via unique constraints and transaction boundaries).
  - Catch-up execution for missed dates up to 400 iterations per rule.
  - "Process due now" button for instantaneous user-scoped processing.
- **F8: Data Privacy and Ownership**:
  - Strict multi-tenant isolation where every business record is scoped to the authenticated user.
  - Unauthorized access attempts to other users' records return HTTP 404 (never revealing record existence).

---

## Technology Stack

- **Backend**: PHP 8.3+, Laravel 12 Monolith (`declare(strict_types=1);` across all files).
- **Database**: MySQL 8.0+ (InnoDB, `utf8mb4_unicode_ci`, strict `ONLY_FULL_GROUP_BY` compliance, native `CHECK` constraints).
- **Frontend Templating**: Laravel Blade with clean reusable UI components (`x-button`, `x-field`, `x-flash`, `x-stat`, `x-budget-bar`, `x-empty-state`, `x-month-switcher`).
- **Styling**: Hand-written modern Vanilla CSS with custom properties (`tokens.css`, `base.css`, `layout.css`, `components/`, `pages/`). No Tailwind, no Bootstrap.
- **Scripting**: TypeScript 5 in strict mode bundled with Vite and `laravel-vite-plugin`.
- **Charts**: Tree-shaken Chart.js 4 (accessible canvas labels, reduced-motion awareness, fallback retry states).
- **Self-Hosted Typography**: Variable fonts `@fontsource-variable/inter` and `@fontsource-variable/fraunces` (zero external font requests).
- **Quality & Testing**: Pest PHP (unit and feature tests), PHPStan / Larastan at Level 6, Laravel Pint, Vitest (jsdom), ESLint, TypeScript compiler (`tsc --noEmit`).

---

## System Architecture & Principles

- **Thin Controllers**: All business rules reside in Actions (`RegisterUser`, `SeedDefaultCategories`, `DeleteUserAccount`) and Services (`BudgetStatusService`, `ReportService`, `RecurringTransactionProcessor`, `RecurrenceCalculator`, `CurrencyFormatter`, `MonthRange`).
- **Form Requests & Policies**: Every input is validated by dedicated Form Requests; every model access is authorized by Eloquent Policies (`CategoryPolicy`, `TransactionPolicy`, `RecurringTransactionPolicy`).
- **Exact Monetary Precision**: Amounts are stored as `DECIMAL(12,2)` in MySQL and manipulated in PHP using `bcmath` and string representations. Floats are never used for currency.
- **SQL Aggregations**: Server-rendered responses use database-level SQL aggregations (`SUM()`, `GROUP BY`) ensuring fast sub-second responses even with 10,000+ transaction rows per user.

---

## Local Setup & Installation

### Prerequisites
- **PHP 8.3 or newer** with extensions: `mbstring`, `intl`, `bcmath`, `pdo_mysql`, `xml`, `curl`, `zip`.
- **Composer 2**
- **Node.js 20 or 22 LTS** and **npm**
- **MySQL 8.0.16 or newer** running on `127.0.0.1:3306`

### Setup Steps

1. **Clone the repository and install dependencies**:
   ```bash
   git clone <repository-url>
   cd pocketledger
   composer install
   npm ci
   ```

2. **Environment configuration**:
   Copy `.env.example` to `.env` and generate the application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Ensure your MySQL credentials in `.env` match your local setup:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pocketledger
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

3. **Create the databases**:
   Create the primary and test databases in MySQL:
   ```sql
   CREATE DATABASE IF NOT EXISTS pocketledger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE IF NOT EXISTS pocketledger_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run migrations and seed demo data**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(In local environment, this automatically seeds the realistic 12-month demo dataset).*

5. **Build frontend assets or start development server**:
   ```bash
   npm run build
   # Or for active development:
   # npm run dev
   ```

6. **Serve the application**:
   ```bash
   php artisan serve
   ```
   Open `http://localhost:8000` in your web browser.

---

## Demo Account & Credentials

The database seeder prepares a fully populated demo account with 12 months of realistic transactions, active category budgets, and recurring rules:

- **Email**: `demo@pocketledger.test`
- **Password**: `password`
- **Currency**: `INR` (₹)

---

## Test & Verification Commands

Run the complete test suite, static analysis, and code quality checkers:

```bash
# 1. Run all Pest unit and feature tests
php ./vendor/bin/pest

# 2. Run PHPStan / Larastan static analysis (Level 6)
php ./vendor/bin/phpstan analyse --memory-limit=1G

# 3. Check code formatting with Laravel Pint
php ./vendor/bin/pint --test

# 4. Frontend TypeScript type check
npm run typecheck

# 5. Frontend ESLint linting
npm run lint

# 6. Frontend Vitest unit tests
npm run test:ts

# 7. Production asset build
npm run build
```

---

## Scheduler Note

PocketLedger automates recurring transactions through the daily scheduled command:
```bash
php artisan recurring:process
```

To run the scheduler in production, register the following cron job on your host:
```crontab
* * * * * cd /path/to/pocketledger && php artisan schedule:run >> /dev/null 2>&1
```
The command is configured in `routes/console.php` to run daily at `00:10` with `withoutOverlapping()`. For hosts without cron access, users can also trigger processing on demand via the **"Process due now"** action on the Recurring screen.

---

## Production Deployment Steps

Follow these standard steps to deploy PocketLedger on any VPS or hosting environment (e.g. Render, Railway, Forge, Ubuntu/Nginx):

1. **Install production PHP dependencies without development packages**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. **Build compiled assets**:
   ```bash
   npm ci && npm run build
   ```
3. **Run database migrations**:
   ```bash
   php artisan migrate --force
   ```
4. **Cache configuration, routes, views, and events for maximum performance**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
5. **Ensure web server points document root strictly to the `public/` directory**:
   - Serve only `/public/index.php`.
   - Never expose root or `.env` files.
   - Configure HTTPS and set `SESSION_SECURE_COOKIE=true` in `.env`.

---

## Database Requirements

> [!IMPORTANT]
> **MySQL 8.0.16 or newer** is strictly required. PocketLedger utilizes native database `CHECK` constraints (e.g., `amount > 0` on transactions and recurring rules), which were ignored in earlier MySQL releases. Do not substitute SQLite or PostgreSQL, as test assertions and SQL queries specifically leverage MySQL collation and date functions (`DATE_FORMAT()`, `ONLY_FULL_GROUP_BY`).

---

## Documentation & Architectural Decisions

- **Full Specification**: Complete requirements specification available in [`docs/SPEC.pdf`](file:///C:/Users/Asus/OneDrive/Desktop/PocketLedger/docs/SPEC.pdf).
- **Decisions Log**: Detailed record of all implementation choices and architectural resolutions in [`docs/DECISIONS.md`](file:///C:/Users/Asus/OneDrive/Desktop/PocketLedger/docs/DECISIONS.md).
