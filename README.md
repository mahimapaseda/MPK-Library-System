# MPK Library System

MPK Library System is a Laravel + Inertia + Vue application for school and institutional library operations. It supports catalog management, accession-level copy tracking, issuing/returns, incident handling (lost/damaged), fines, role-based access control, member self-service, analytics, and scheduled reminder emails.

## Current Stack

- Backend: Laravel 13, PHP 8.3+
- Frontend: Vue 3, Inertia.js, Vite 6, Tailwind CSS 4
- Database: SQLite by default (works with MySQL/PostgreSQL if configured)
- Reporting: barryvdh/laravel-dompdf (PDF exports)
- Notifications: Laravel Notifications (mail channel)

## Feature Overview

- Dashboard with cached KPI cards, trends, category distribution, and AI insight cards
- Books module with copy-level accession tracking
- Member management (students/teachers/staff)
- Issue desk + POS issue flow with search endpoints
- Return flow with overdue fine generation
- Lost/Damaged incident workflow with optional charges
- Fine administration (paid/waived with resolver audit trail)
- Member portal (separate guard) for loans/history/fines
- PDF reports: overdue, inventory summary, incidents, AI strategy
- Scheduled reminders via custom command `library:send-notifications`

## Roles and Access

Role checks are implemented through `user.role` and custom middleware.

- `librarian`: full access (books, categories, members, issues, fines, settings, reports)
- `teacher`: issue and report access
- `principal`: report access
- `member` guard: separate login for member portal

## Architecture Summary

- Server-rendered SPA architecture with Inertia (no separate public REST API layer)
- Domain logic is concentrated in services:
	- `BookInventoryService` for copy lifecycle and quantity sync
	- `LibraryNotificationService` for receipts/reminders/overdue alerts
	- `AiInsightsService` for dashboard/report recommendations and alert heuristics
- Core business state is modeled by `Book`, `BookCopy`, `BookIssue`, `Fine`, `Member`, `Setting`
- Activity auditing is handled by `LogsActivity` trait + `ActivityLog` model

## Data Model (High Level)

- `books` (title-level metadata + aggregate quantities)
- `book_copies` (accession_number, sequence_number, status)
- `book_issues` (book/member/copy link + due/return/resolution state)
- `fines` (amount + unpaid/paid/waived + resolver metadata)
- `members` (portal-capable auth entity under `member` guard)
- `users` (staff auth + role)
- `settings` (runtime policy knobs, cached via `SettingCache`)
- `activity_logs` (CRUD and manual action trail)

## Request Flows

- Issue:
	1. Validate member and policy limits.
	2. Reserve next available copy with row lock.
	3. Create `book_issues` record.
	4. Dispatch issue receipt after commit.

- Return:
	1. Mark issue returned and release copy.
	2. Compute overdue days using configured grace period.
	3. Create/update fine if chargeable.
	4. Dispatch return receipt after commit.

- Lost/Damaged:
	1. Close issue with incident status.
	2. Mark copy incident state.
	3. Optionally create/update fine.

## Quick Start

### 1. Install dependencies and initialize

```bash
composer run setup
```

This command installs Composer + npm packages, creates `.env` if missing, generates `APP_KEY`, runs migrations, and builds frontend assets.

### 2. Seed demo data

```bash
php artisan db:seed
```

### 3. Run development servers

Option A (recommended):

```bash
composer run dev
```

Option B (manual, useful if shell quoting differs on Windows):

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev -- --host 127.0.0.1 --port 5173
```

### 4. Optional Windows helper

```powershell
.\Run-Site.ps1 -Setup -Seed
```

## Default Credentials

After `php artisan db:seed`:

- Admin email: `admin@school.lk`
- Admin password: `password`

## Notifications and Scheduler

- Receipts: issue and return emails (when member has email)
- Reminders: due-soon and overdue alerts
- Scheduled command: `library:send-notifications`
- Schedule time: daily at `08:00` (see `routes/console.php`)

Production cron example:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Tests

Feature coverage includes:

- issue condition workflow (lost/damaged)
- fine administration (paid/waived)
- notification behavior and overdue auto-marking

Run all tests:

```bash
php artisan test
```

## System Analysis (April 2026)

### Strengths

- Good domain separation with dedicated services for inventory, notifications, and analytics.
- Copy-level inventory modeling supports realistic circulation and incident tracking.
- Transaction + lock usage in issue/return paths improves consistency under concurrency.
- Practical role segmentation and separate member guard.
- Useful PDF reporting and scheduled operational automation.
- Activity logging integrated into key models.

### Observed Risks and Gaps

- Dashboard month aggregation currently uses SQLite-specific `strftime`, which can require adaptation for MySQL/PostgreSQL.
- `spatie/laravel-permission` is installed but not wired into route authorization (custom role middleware is used instead).
- Some seeded members do not include portal credentials by default; member portal login is email/password based.
- Cache invalidation is manual in multiple controllers; missing invalidation points can produce stale analytics.
- Notification delivery is synchronous unless queue driver/worker is configured for async handling.

### Recommended Next Improvements

1. Make reporting date queries database-agnostic for full multi-DB portability.
2. Choose one RBAC strategy: either remove unused permission package or fully integrate it.
3. Add policy/authorization tests per role and per route group.
4. Add queue-backed notifications for high-volume reminder runs.
5. Centralize cache invalidation into domain events/listeners to reduce drift.

## License

MIT
