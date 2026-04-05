# MPK Library System

Build a library desk that actually feels alive.

MPK Library System is a Laravel + Inertia + Vue platform for schools and institutions that need real circulation workflows: accession-level copies, fast issue/return operations, incident handling, fines, role-aware access, member self-service, analytics, and scheduled reminders.

## Why This Project Exists

Most library apps are either too basic for day-to-day desk reality or too heavy for small teams.

This project aims for the middle path:

- operationally practical for librarians
- technically maintainable for developers
- fast enough for live counters and busy school periods

## At A Glance

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Vue 3, Inertia.js, Vite 6, Tailwind CSS 4 |
| Database | SQLite default (MySQL/PostgreSQL supported by configuration) |
| Reports | barryvdh/laravel-dompdf |
| Notifications | Laravel Notifications (mail channel) |

## Core Capabilities

- Dashboard with cached KPIs, trend charts, and AI insight cards
- Book catalog + accession-level copy tracking
- Member management for student, teacher, and staff types
- Issue desk and POS issue flow with search endpoints
- Return handling with overdue fine calculation
- Lost and damaged incident workflows with optional charges
- Fine administration with paid and waived states + resolver audit trail
- Member portal with separate guard for personal loan history and fines
- PDF exports: overdue, inventory summary, incidents, AI strategy
- Daily reminder command: library:send-notifications

## Role Map

Authorization is handled through user.role and custom middleware.

- librarian: full access to books, categories, members, issues, fines, settings, and reports
- teacher: issue and report access
- principal: report access
- member guard: separate login context for the member portal

## Architecture Snapshot

This is a server-driven SPA using Inertia, so you get modern frontend UX without introducing a separate public API surface.

Business logic is organized in service classes:

- BookInventoryService: copy lifecycle, stock alignment, and accession handling
- LibraryNotificationService: issue/return receipts and reminder automation
- AiInsightsService: dashboard strategy insights and alert heuristics

Key domain entities:

- Book
- BookCopy
- BookIssue
- Fine
- Member
- Setting

Audit trail support:

- LogsActivity trait
- ActivityLog records (model changes and action metadata)

## Data Model Highlights

- books: title metadata + aggregate stock fields
- book_copies: accession_number, sequence_number, status, rack location
- book_issues: links member/book/copy with issue lifecycle fields
- fines: unpaid/paid/waived with resolution metadata
- members: member identity + portal credentials
- users: staff login + role
- settings: runtime policy values, cached through SettingCache
- activity_logs: action history for key model operations

## Circulation Logic (Simplified)

Issue flow

1. Validate member and policy constraints.
2. Reserve the next available copy with row lock.
3. Create the issue record.
4. Send issue receipt after transaction commit.

Return flow

1. Mark issue returned and release copy.
2. Compute overdue days with grace-period settings.
3. Create or update fine when chargeable.
4. Send return receipt after commit.

Lost or damaged flow

1. Close issue with incident status.
2. Mark copy as lost or damaged.
3. Create or update charge when needed.

## Quick Start

### 1) Project setup

```bash
composer run setup
```

What this does:

- installs Composer dependencies
- creates .env from .env.example if missing
- generates APP_KEY
- runs migrations
- installs npm dependencies
- builds frontend assets

### 2) Seed sample data

```bash
php artisan db:seed
```

### 3) Run in development

Recommended:

```bash
composer run dev
```

Manual option (useful on some Windows shells):

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev -- --host 127.0.0.1 --port 5173
```

Optional Windows helper:

```powershell
.\Run-Site.ps1 -Setup -Seed
```

## Default Admin Login

After seeding:

- Email: admin@school.lk
- Password: password

## Scheduler and Notifications

Notification types:

- issue receipt emails
- return receipt emails
- due reminder emails
- overdue alerts

Scheduled command:

- library:send-notifications
- scheduled daily at 08:00

Production cron:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Testing

Current feature test focus areas:

- issue condition workflow (lost and damaged)
- fine administration (paid and waived)
- reminder and overdue notification behavior

Run tests:

```bash
php artisan test
```

## Technical Review (April 2026)

Strengths

- service-oriented domain logic keeps controllers focused
- accession-level copy tracking matches real library operations
- transactional and lock-based issue flow improves consistency
- clear role segmentation including separate member guard
- useful operational reporting and scheduled automations
- integrated activity logging for traceability

Known gaps

- monthly dashboard grouping uses SQLite-specific strftime semantics
- spatie/laravel-permission is installed but custom role middleware is used for route control
- seeded member data does not consistently include portal credentials
- cache invalidation relies on manual controller-level forget calls
- notifications run synchronously unless queue infrastructure is enabled

Suggested next upgrades

1. make date aggregation database-agnostic
2. consolidate RBAC strategy around one approach
3. expand authorization tests per role and route group
4. move notifications to queued delivery for larger deployments
5. centralize cache invalidation via domain events/listeners

## License

MIT
