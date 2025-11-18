#!/bin/sh
set -e

echo "📌 Ejecutando comandos de inicialización..."

php-fpm -D

# Crear enlace a /storage (ignora error si ya existe)
php artisan storage:link || true

# Migraciones (solo si la DB está accesible)
php artisan migrate --force || true

# Limpiar caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "🚀 Iniciando Nginx..."
exec nginx -g "daemon off;"
