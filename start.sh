set -e  # langsung exit kalau ada error

echo "Testing nginx config..."
nginx -t

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Starting PHP-FPM..."
php-fpm -F &

echo "Starting nginx..."
exec nginx -g "daemon off;"
