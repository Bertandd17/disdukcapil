#!/bin/bash
set -e

php artisan optimize:clear
php artisan storage:link || true
php artisan config:cache
php artisan event:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
