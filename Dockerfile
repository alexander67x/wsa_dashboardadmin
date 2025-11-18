# syntax=docker/dockerfile:1

FROM php:8.2-fpm-bullseye AS base

# Sistema y extensiones PHP requeridas por Filament 4 (intl, zip, gd, pdo_mysql)
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) intl zip gd pdo_mysql && \
    pecl install redis && docker-php-ext-enable redis && \
    rm -rf /var/lib/apt/lists/*


FROM node:20-bullseye AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


FROM base AS app
WORKDIR /var/www/html

# Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar TODO el código antes de instalar dependencias
COPY . .

# Instalar dependencias PHP (usa artisan ahora sí existe)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar assets compilados
COPY --from=frontend /app/public/build ./public/build

# Asegurar permisos de runtime
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 8000
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
