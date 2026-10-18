# Architectural & Implementation Decisions

Record of technical and product decisions, conflicts resolved, and additions made during the build of PocketLedger.

## Decision Log

### D-001: Toolchain and Runtime Environment
- **Context**: The host machine did not have PHP 8.3 in global PATH.
- **Decision**: Configured official portable PHP 8.3 (x64) with extensions: `mbstring`, `intl`, `bcmath`, `pdo_mysql`, `xml`, `curl`, `zip`, `fileinfo`, `openssl`, and installed Composer 2.
- **Status**: Accepted.

### D-002: MySQL 8.0 Databases
- **Context**: Local MySQL 8.0 service `MYSQL80` is running on port 3306.
- **Decision**: Created `pocketledger` and `pocketledger_test` databases with `utf8mb4` charset and `utf8mb4_unicode_ci` collation. Configured credentials in `.env` and `phpunit.xml`.
- **Status**: Accepted.

### D-003: Front-end Tooling & Strict TypeScript
- **Context**: Removed default Tailwind and Axios from Laravel skeleton as required by TRD section 2.
- **Decision**: Installed `typescript`, `vitest`, `jsdom`, `eslint`, `@eslint/js`, `typescript-eslint`, `chart.js`, and self-hosted fonts `@fontsource-variable/inter` and `@fontsource-variable/fraunces`. Configured `tsconfig.json`, `vite.config.ts`, `vitest.config.ts`, and `eslint.config.js`.
- **Status**: Accepted.

### D-004: Pest and Larastan
- **Context**: Testing with Pest and static analysis with Larastan level 6.
- **Decision**: Configured `tests/Pest.php`, `phpunit.xml`, and `phpstan.neon` at level 6.
- **Status**: Accepted.

### D-005: Design System Tokens and Custom CSS Architecture
- **Context**: TRD requires pure hand-written modern CSS without external CSS frameworks or Tailwind, providing a warm, paper-ledger aesthetic.
- **Decision**: Established a modular CSS token system (`resources/css/tokens.css`, `base.css`, `layout.css`, `components/`, and `pages/`). All font metrics, spacing tokens, and color roles adhere strictly to the UI/UX Design Brief.
- **Status**: Accepted.

### D-006: Database Schema & Exact Monetary Precision
- **Context**: PRD and TRD require zero floating-point arithmetic for monetary values to eliminate rounding errors.
- **Decision**: Stored all monetary fields as `DECIMAL(12,2)` in MySQL with native `CHECK (amount > 0)` constraints. Cast as `decimal:2` in Eloquent models and manipulated using `bcmath` and string representations in PHP services.
- **Status**: Accepted.

### D-007: Strict Multi-Tenant Data Isolation & 404 Responses
- **Context**: PRD F8 requires that a user attempting to access or modify another user's financial record receives HTTP 404 (Not Found) rather than HTTP 403 (Forbidden), ensuring no private record existence is leaked.
- **Decision**: Implemented `failedAuthorization(): void { abort(404); }` across all Form Requests and utilized `Response::denyAsNotFound()` in Eloquent policies (`CategoryPolicy`, `TransactionPolicy`, `RecurringTransactionPolicy`).
- **Status**: Accepted.

### D-008: Recurrence Month-End Clamping and Restoration
- **Context**: Recurring transactions configured on month-end dates (e.g. 29th, 30th, 31st) must clamp to the last day of shorter months (e.g. Feb 28), but restore to day 31 on subsequent 31-day months.
- **Decision**: `RecurrenceCalculator` computes each occurrence index $n$ relative to the immutable original `start_date` (`$start->copy()->addMonthsNoOverflow($n)`), preventing cumulative date drift across shorter months.
- **Status**: Accepted.

### D-009: Streamed CSV Export & Formula Injection Neutralization
- **Context**: TRD F6 requires memory-efficient CSV streaming and mitigation against formula injection attacks when opened in spreadsheets.
- **Decision**: Used `StreamedResponse` in `ReportService` with UTF-8 BOM (`\xEF\xBB\xBF`). Cells whose values begin with `=`, `+`, `-`, `@`, `\t`, or `\r` are prefixed with a single quote (`'`).
- **Status**: Accepted.

### D-010: Deterministic Demo Dataset & Production Safeguard
- **Context**: Reviewers and recruiters need an instant, rich 12-month demo dataset without compromising production safety.
- **Decision**: Created `DemoSeeder` with a fixed pseudo-random seed (`mt_srand(42)`), generating realistic monthly salaries, rent, utilities, weekly groceries, dining out, transport, shopping, and healthcare. Automatically called by `DatabaseSeeder` in `local` environment only.
- **Status**: Accepted.
