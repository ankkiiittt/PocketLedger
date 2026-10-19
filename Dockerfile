# ------------------------------------------------------------------------------
# Stage 1: Build frontend assets (Vite)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package*.json tsconfig.json vite.config.ts ./
COPY resources ./resources

RUN npm ci && npm run build

# ------------------------------------------------------------------------------
# Stage 2: Production PHP 8.3 & Web Server (Nginx + PHP-FPM)
# ------------------------------------------------------------------------------
FROM php:8.3-fpm-alpine

# Install system utilities, web server, and PHP extension dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    gettext \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    intl \
    bcmath \
    zip \
    opcache

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create required runtime directories for Nginx, Supervisor, and logs
RUN mkdir -p /run/nginx /run/supervisord /var/log/supervisor /var/log/nginx /var/cache/nginx /var/lib/nginx \
    && echo -e "\n[www]\nclear_env = no\n" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy application files
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend-builder /app/public/build ./public/build

# Install production PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup supervisord configuration
COPY docker/supervisord.conf /etc/supervisord.conf

# Setup entrypoint script permissions
RUN chmod +x /var/www/html/docker/entrypoint.sh

# Expose web ports (supports dynamic $PORT, 8080, 10000, 80, 3000)
EXPOSE 8080 10000 80 3000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
