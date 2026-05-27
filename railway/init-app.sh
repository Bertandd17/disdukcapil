#!/bin/bash
set -e

mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/testing storage/logs
chmod -R a+rw bootstrap/cache storage

php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan event:cache
php artisan view:cache
