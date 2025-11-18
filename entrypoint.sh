#!/bin/sh
set -e

echo "⏳ Esperando a que la base de datos esté lista..."

max_retries=30
counter=0

until php -r "try { new PDO(getenv('DB_CONNECTION').':host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (Exception \$e) { exit(1); }"; do
    counter=$((counter+1))
    if [ $counter -gt $max_retries ]; then
        echo "❌ No se pudo conectar a MySQL después de $max_retries intentos."
        exit 1
    fi
    echo "⏳ MySQL no está listo aún... ($counter/$max_retries)"
    sleep 2
done

echo "✅ MySQL está listo."

echo "📌 Ejecutando tareas iniciales..."

php artisan storage:link || true
php artisan migrate --force || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "----------- LARAVEL LOG ------------"
cat storage/logs/laravel.log || true
echo "------------------------------------"


echo "🚀 Iniciando supervisord (php-fpm + nginx)..."
exec /usr/bin/supervisord -n
