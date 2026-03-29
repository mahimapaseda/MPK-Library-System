#!/bin/sh
set -e

# Run database migrations
php artisan migrate --force

# Cache config, routes, and views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create public/storage symlink (safe to ignore if already exists)
php artisan storage:link || true

# Start the built-in PHP server
# Render injects $PORT; fallback to 10000 locally
php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
