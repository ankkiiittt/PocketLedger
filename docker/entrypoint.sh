#!/bin/bash
set -e

# Default port to 10000 if not provided by Render
export PORT=${PORT:-10000}

echo "Configuring Nginx on port ${PORT}..."
envsubst '${PORT}' < /var/www/html/docker/nginx.conf.template > /etc/nginx/http.d/default.conf

# Ensure permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration failed or skipped."
fi

# Cache configurations in production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching Laravel configuration, routes and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting services via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
