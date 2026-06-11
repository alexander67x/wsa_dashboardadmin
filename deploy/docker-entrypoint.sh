#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        cat > .env <<'EOF'
APP_NAME=WSA_DashboardAdmin
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8081
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=wsa_dashboardadmin
DB_USERNAME=wsa
DB_PASSWORD=wsa_secret
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
MEMCACHED_HOST=127.0.0.1
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=${APP_NAME}
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
VITE_APP_NAME=${APP_NAME}
EOF
    fi
fi

php <<'PHP'
<?php
$envPath = '/var/www/html/.env';
$lines = file_exists($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
$values = [
    'APP_NAME' => getenv('APP_NAME') ?: 'WSA_DashboardAdmin',
    'APP_ENV' => getenv('APP_ENV') ?: 'local',
    'APP_DEBUG' => getenv('APP_DEBUG') ?: 'true',
    'APP_URL' => getenv('APP_URL') ?: 'http://localhost:8081',
    'LOG_CHANNEL' => getenv('LOG_CHANNEL') ?: 'stack',
    'LOG_LEVEL' => getenv('LOG_LEVEL') ?: 'debug',
    'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'mysql',
    'DB_HOST' => getenv('DB_HOST') ?: 'mysql',
    'DB_PORT' => getenv('DB_PORT') ?: '3306',
    'DB_DATABASE' => getenv('DB_DATABASE') ?: 'wsa_dashboardadmin',
    'DB_USERNAME' => getenv('DB_USERNAME') ?: 'wsa',
    'DB_PASSWORD' => getenv('DB_PASSWORD') ?: 'wsa_secret',
    'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: 'database',
    'CACHE_STORE' => getenv('CACHE_STORE') ?: 'database',
    'QUEUE_CONNECTION' => getenv('QUEUE_CONNECTION') ?: 'database',
    'MAIL_MAILER' => getenv('MAIL_MAILER') ?: 'log',
    'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS') ?: 'hello@example.com',
    'MAIL_FROM_NAME' => getenv('MAIL_FROM_NAME') ?: 'WSA_DashboardAdmin',
    'RESEND_ENABLED' => getenv('RESEND_ENABLED') ?: 'false',
];

$lineMap = [];
foreach ($lines as $index => $line) {
    if (preg_match('/^\s*([A-Z0-9_]+)\s*=/', $line, $matches)) {
        $lineMap[$matches[1]] = $index;
    }
}

foreach ($values as $key => $value) {
    $encoded = str_contains($value, ' ') ? "\"{$value}\"" : $value;
    $entry = "{$key}={$encoded}";
    if (array_key_exists($key, $lineMap)) {
        $lines[$lineMap[$key]] = $entry;
    } else {
        $lines[] = $entry;
    }
}

file_put_contents($envPath, implode(PHP_EOL, $lines) . PHP_EOL);
PHP

until php -r '
try {
    new PDO(
        sprintf(
            "mysql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "mysql",
            getenv("DB_PORT") ?: "3306",
            getenv("DB_DATABASE") ?: "wsa_dashboardadmin"
        ),
        getenv("DB_USERNAME") ?: "wsa",
        getenv("DB_PASSWORD") ?: "wsa_secret",
        [PDO::ATTR_TIMEOUT => 3]
    );
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Waiting for database...\n");
    exit(1);
}
'; do
    sleep 3
done

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

php artisan storage:link --no-interaction || true
php artisan migrate --force --no-interaction

if [ "${APP_RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction
php artisan optimize:clear --no-interaction

exec /usr/bin/supervisord -c /etc/supervisord.conf
