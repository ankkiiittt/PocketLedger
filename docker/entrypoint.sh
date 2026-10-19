#!/bin/bash
set -e

# Default port to 8080 if not provided by Railway
export PORT=${PORT:-8080}

echo "=== Starting PocketLedger on Port ${PORT} ==="

# Ensure required runtime directories exist
mkdir -p /run/nginx /run/supervisord /var/log/supervisor /var/log/nginx /var/cache/nginx /var/lib/nginx
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/database

# Ensure fallback SQLite database file exists
touch /var/www/html/database/database.sqlite

# Ensure permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generating application key..."
    export APP_KEY=$(php artisan key:generate --show)
fi

# Ensure .env exists with APP_KEY
if [ ! -f /var/www/html/.env ]; then
    touch /var/www/html/.env
fi
if ! grep -q "APP_KEY=" /var/www/html/.env; then
    echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
fi

# Map Railway MySQL environment variables if present
export DB_HOST=${DB_HOST:-$MYSQLHOST}
export DB_PORT=${DB_PORT:-$MYSQLPORT}
export DB_DATABASE=${DB_DATABASE:-$MYSQLDATABASE}
export DB_USERNAME=${DB_USERNAME:-$MYSQLUSER}
export DB_PASSWORD=${DB_PASSWORD:-$MYSQLPASSWORD}

if [ -n "$MYSQLHOST" ] || [ -n "$MYSQL_URL" ] || [ -n "$DB_HOST" ]; then
    export DB_CONNECTION=${DB_CONNECTION:-mysql}
else
    export DB_CONNECTION=${DB_CONNECTION:-sqlite}
fi

echo "Database connection mode: ${DB_CONNECTION}"

# Configure Nginx with multi-port listener (Railway dynamic PORT, 8080, 10000, 80, 3000)
echo "Configuring Nginx with multi-port listener..."
mkdir -p /etc/nginx/http.d

php -r '
$template = file_get_contents("/var/www/html/docker/nginx.conf.template");
$port = (int) (getenv("PORT") ?: 8080);
$ports = array_values(array_unique(array_filter([$port, 8080, 10000, 80, 3000])));
$directives = implode("\n", array_map(fn($p) => "    listen {$p};", $ports));
$conf = str_replace("__LISTEN_DIRECTIVES__", $directives, $template);
file_put_contents("/etc/nginx/http.d/default.conf", $conf);
echo "Nginx configured to listen on ports: " . implode(", ", $ports) . "\n";
'

# Test Nginx configuration and send output to stdout so it appears normal in Railway logs
nginx -t 2>&1

# Run database migrations and seeding
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Running MySQL database migrations..."
    php artisan migrate --force || echo "Warning: MySQL migration failed or database not ready yet. Starting server anyway."
    php artisan db:seed --force || echo "Seeding skipped or already run."
else
    echo "Running SQLite database migrations..."
    php artisan migrate --force || echo "Warning: SQLite migration failed."
    php artisan db:seed --force || echo "Seeding skipped or already run."
fi

# Cache configurations in production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching Laravel configuration, routes and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "=== Starting services via Supervisor ==="
exec /usr/bin/supervisord -c /etc/supervisord.conf
