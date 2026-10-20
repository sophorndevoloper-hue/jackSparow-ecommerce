#!/bin/sh
set -e

# Make sure directory and log permissions exist
mkdir -p /var/www/storage/logs \
         /var/www/storage/framework/views \
         /var/www/storage/framework/cache \
         /var/www/storage/framework/sessions \
         /var/www/bootstrap/cache \
         /run/nginx

chmod -R 775 /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 1. Run migrations FIRST so database tables exist
php artisan migrate --force || true
php artisan db:seed --force || true

# 2. Clear stale cache safely now that database tables are present
php artisan config:clear || true
php artisan cache:clear || true

# 3. Build optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link || true

# 4. Start PHP-FPM in background
php-fpm -D

# 5. Validate Nginx and start
nginx -t
exec nginx -g "daemon off;"