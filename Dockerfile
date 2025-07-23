# --- Stage 1: Composer Build ---
FROM php:8.2-fpm-alpine AS composer_build

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql mbstring exif pcntl bcmath gd zip intl

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Copy project files
COPY . .

# --- Stage 2: Runtime ---
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Install runtime dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    postgresql-libs \
    icu \
    nginx \
    gettext \
    bash

# Reinstall PHP extensions (runtime only)
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    oniguruma-dev && \
    docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql mbstring exif pcntl bcmath gd zip intl && \
    apk del .build-deps

# Copy built app from build stage
COPY --from=composer_build /app /var/www/html

# Set permissions
RUN mkdir -p /run/nginx /var/log/nginx && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cleanup default NGINX config
RUN rm -rf /etc/nginx/conf.d/* /etc/nginx/http.d/*

# Copy configuration files
COPY nginx/nginx.conf /etc/nginx/nginx.conf
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY start.sh /start.sh

RUN chmod +x /start.sh

# Set default port (Cloud Run uses PORT)
ENV PORT=8080

# Expose the port
EXPOSE 8080

# Start nginx + php-fpm
CMD ["/start.sh"]
