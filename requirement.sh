# Update apt repository dan install dependencies PHP
sudo apt-get update
sudo apt-get install -y php php-cli php-fpm php-mbstring php-xml php-curl php-zip php-mysql unzip curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Pastikan Composer telah terinstal dengan benar
composer --version

# Install dependencies Laravel (jika belum ada file vendor/)
cd /path/to/your/laravel/project
composer install --no-dev --optimize-autoloader

# Set up Laravel
php artisan key:generate
php artisan migrate:fresh --seed
php artisan config:cache
php artisan route:cache

# Jika menggunakan queue worker atau scheduler, bisa diatur di sini (optional)
# php artisan queue:work
