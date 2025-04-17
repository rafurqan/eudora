# Install PHP dependencies
apt-get update
apt-get install -y php php-cli php-fpm php-mbstring php-xml php-curl php-zip php-mysql

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Laravel dependencies
composer install --no-dev --optimize-autoloader

# Set up Laravel
php artisan key:generate
php artisan migrate:fresh --seed
php artisan config:cache
php artisan route:cache
