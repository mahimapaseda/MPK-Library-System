# Free Deployment Guide

This project can be deployed on a 100% free PHP host, but the practical path is an upload-based shared host that supports PHP 8.3 and lets you set the document root to the `public` directory.

## Recommended Free Hosting Type

Use a free PHP shared host with:

- PHP 8.3 support
- SQLite or MySQL support
- FTP or file manager upload access
- Writable `storage` and `bootstrap/cache` directories
- Ability to point the web root to `public`

Examples change often, so verify the host still offers a free tier before signing up.

## Why This Repo Can Work On A Free Host

- The app already defaults to SQLite.
- Frontend assets can now be built locally with `npm run build`.
- The default environment template is now free-host friendly:
  - `SESSION_DRIVER=file`
  - `CACHE_STORE=file`
  - `QUEUE_CONNECTION=sync`
- No queued jobs were found in the application code, so `sync` is safe.

## Best Zero-Cost Deployment Path

The most reliable fully free setup for this project is:

1. Build everything locally.
2. Run migrations locally.
3. Seed the database locally.
4. Upload the whole Laravel app, including `vendor` and `public/build`.
5. Use SQLite so you do not depend on a paid managed database.

This avoids needing SSH access on the host.

## Local Preparation

Run these commands on your machine from the project root:

```powershell
composer install --no-dev --optimize-autoloader
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed --force
npm install
npm run build
```

Then edit `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_LEVEL=error
```

Make sure `database/database.sqlite` exists before migrating.

## Default Seeded Admin Account

If you use `php artisan migrate --seed`, the default admin account is:

- Email: `admin@school.lk`
- Password: `password`

Change that password immediately after first login.

## Files To Upload

Upload the full project, including these important paths:

- `app`
- `bootstrap`
- `config`
- `database`
- `public`
- `resources`
- `routes`
- `storage`
- `vendor`
- `artisan`
- `composer.json`
- `composer.lock` if present

Do not rely on the host to run Composer or Node unless the host explicitly supports that.

## Document Root

Preferred setup:

- Upload the project to a folder such as `library-system`
- Set the domain or subdomain document root to `library-system/public`

If the host does not let you change the document root:

1. Put the Laravel project outside the public web folder if the host allows it.
2. Copy the contents of `public` into the host web root.
3. Update the copied `index.php` so the `vendor/autoload.php` and `bootstrap/app.php` paths point to the real project location.

Example pattern:

```php
require __DIR__.'/../library-system/vendor/autoload.php';

$app = require_once __DIR__.'/../library-system/bootstrap/app.php';
```

Adjust the folder name to match where you uploaded the app.

## Required Writable Directories

Your host must allow write access to:

- `storage`
- `bootstrap/cache`
- `database/database.sqlite`

If sessions stop working or the site throws 500 errors after upload, permissions are the first thing to check.

## SQLite Notes

SQLite is the easiest free option for this project because you can migrate locally and upload the database file.

Use SQLite when:

- traffic is low to moderate
- you do not need external DB management
- the host allows writes to the uploaded database file

If the host blocks SQLite writes, switch to MySQL in `.env` and create a free MySQL database from the hosting panel.

## What Not To Do On A Free Host

- Do not enable database queue workers unless the host provides cron or workers.
- Do not depend on local dev settings like `APP_DEBUG=true`.
- Do not upload without `vendor` and `public/build` if the host cannot run Composer or Node.
- Do not upload cached config built on a different machine path unless you regenerate it on the server.

## Verification Checklist

After upload, verify:

1. The homepage redirects to login.
2. Admin login works.
3. Books, members, and issue pages load.
4. Member portal login works.
5. The `storage` and `bootstrap/cache` folders are writable.
6. The background images load from `/bg-light.webp` and `/bg.jpg`.

## Current Repo Status

Verified locally during analysis:

- `php artisan about` boots successfully.
- `npm run build` completes successfully.
- The app uses Laravel 13, PHP 8.3, Inertia, Vue, and SQLite by default.

## Practical Limitation

There is no serious fully managed Laravel platform in common use that stays free forever without tradeoffs. Free shared hosting is the realistic route for this repo if the requirement is strictly zero cost.

If you choose a specific host, the next step is to tailor the folder layout and `.env` exactly to that provider.