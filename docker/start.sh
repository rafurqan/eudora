#!/bin/sh
set -e

echo "Preparing Laravel..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache

echo "Starting PHP-FPM..."
php-fpm -y /usr/local/etc/php-fpm.conf -R &

echo "Waiting PHP-FPM..."
sleep 2

echo "Starting Nginx..."
exec nginx -c /etc/nginx/nginx.conf -g "daemon off;"
