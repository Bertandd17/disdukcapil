#!/bin/sh
set -e

mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/testing storage/logs public/storage
chmod -R a+rw bootstrap/cache storage

# Set Laravel storage path for SQLite fallback (if no DB configured)
if [ -n "$DATABASE_URL" ]; then
  echo "[start] DATABASE_URL detected, MySQL will be used by Laravel"
fi

# Run Laravel optimizations (use || true to not break deploy on optional steps)
php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link || true
php artisan route:cache || true
php artisan view:cache || true

# Start Laravel on Railway's assigned PORT
echo "[start] Starting Laravel on port ${PORT:-8000}"
exec php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
