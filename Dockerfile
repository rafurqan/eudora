FROM php:8.2-apache

# Install system dependencies (termasuk PostgreSQL)
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath gd

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Apache config (asumsikan file ini ada di ./docker/apache.conf)
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Laravel-specific: generate key if needed (opsional di Cloud Run)
# RUN php artisan key:generate

# Expose port and ENV for Cloud Run
ENV PORT=8080
EXPOSE 8080

# Start Apache in foreground
CMD ["apache2-foreground"]
