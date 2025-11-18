# syntax=docker/dockerfile:1

###########################
# PHP-FPM + extensiones
###########################
FROM php:8.2-fpm-bullseye AS php

# Sistema y extensiones PHP requeridas por Filament 4 (intl, zip, gd, pdo_mysql) + nginx
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git unzip nginx \
    libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev libssl-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql && \
    pecl install redis && docker-php-ext-enable redis && \
    rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


###########################
# Build frontend
###########################
FROM node:20-bullseye AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


###########################
# Aplicación final
###########################
FROM php AS app
WORKDIR /var/www/html

# Copiar dependencias PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar código y assets compilados
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Permisos runtime
RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Script de arranque: corre artisan y luego levanta php-fpm + nginx
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
