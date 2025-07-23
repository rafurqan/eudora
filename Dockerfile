FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath gd

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Copy production environment
COPY .env.production .env

# Set file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Apache config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Laravel optimize
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Cloud Run port
ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]
