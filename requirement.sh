# Update apt repository dan install dependencies PHP
apt-get update
apt-get install -y php php-cli php-fpm php-mbstring php-xml php-curl php-zip php-mysql unzip curl

npm install

# Set up Laravel
php artisan key:generate
php artisan migrate:fresh --seed
php artisan config:cache
php artisan route:cache

# Jika menggunakan queue worker atau scheduler, bisa diatur di sini (optional)
# php artisan queue:work
