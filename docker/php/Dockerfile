# =========================
# STAGE 1 — COMPOSER
# =========================
FROM composer:2 AS composer

WORKDIR /app

# Copia apenas o composer.json
COPY laravel-api/composer.json ./

# Gera composer.lock e vendor
RUN composer update \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# =========================
# STAGE 2 — PHP FINAL
# =========================
FROM php:8.4-fpm

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif

# Copia composer e vendor
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=composer /app/vendor /var/www/vendor
COPY --from=composer /app/composer.lock /var/www/composer.lock
COPY laravel-api /var/www

WORKDIR /var/www

RUN chown -R www-data:www-data storage bootstrap/cache
