# syntax=docker/dockerfile:1

#######################################
# BUILD FRONTEND (Vite)
#######################################
FROM node:20-bullseye AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


#######################################
# BUILD PHP-FPM + EXTENSIONS
#######################################
FROM php:8.2-fpm-bullseye AS php

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    git unzip curl libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libxml2-dev libssl-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl zip pdo_mysql && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    pecl install redis && docker-php-ext-enable redis && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

#######################################
# COPY SOURCE (including artisan)
#######################################
COPY . .

#######################################
# INSTALL COMPOSER DEPENDENCIES
#######################################
RUN composer install --no-dev --optimize-autoloader --no-interaction

#######################################
# COPY BUILT ASSETS
#######################################
COPY --from=frontend /app/public/build ./public/build

#######################################
# PERMISSIONS
#######################################
RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache


#######################################
# FINAL IMAGE WITH NGINX + PHP-FPM
#######################################
FROM nginx:1.25 AS production

WORKDIR /var/www/html

# Copia código y vendor desde build PHP
COPY --from=php /var/www/html /var/www/html

# Copia configuración nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copia entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
