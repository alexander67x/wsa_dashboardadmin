# syntax=docker/dockerfile:1

############################################
# FRONTEND BUILD (Vite)
############################################
FROM node:20-bullseye AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


############################################
# PHP-FPM BUILD
############################################
FROM php:8.2-fpm-bullseye AS php

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libxml2-dev libssl-dev libxslt1-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl zip pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage/bootstrap/cache


############################################
# FINAL IMAGE WITH PHP-FPM + NGINX + SUPERVISOR
############################################
FROM debian:12-slim AS production

RUN apt-get update && apt-get install -y \
    nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-redis \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copia código y vendor desde build PHP
COPY --from=php /var/www/html /var/www/html

# Copia configuración nginx y supervisor
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Copia entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
