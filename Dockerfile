# syntax=docker/dockerfile:1

# ==============================================================================
# Stage 1: Build Frontend Assets (Vite + Tailwind CSS)
# ==============================================================================
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --prefer-offline

COPY vite.config.js tailwind.config.js* postcss.config.js* ./
COPY resources resources
COPY public public

RUN npm run build

# ==============================================================================
# Stage 2: Install Composer Dependencies (Production only)
# ==============================================================================
FROM composer:2 AS composer-build

WORKDIR /app

COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==============================================================================
# Stage 3: Final Production Runtime (PHP-FPM 8.2 + Nginx on Alpine)
# ==============================================================================
FROM php:8.2-fpm-alpine AS production

# Install system runtime libraries, Nginx, gettext (for envsubst), tini, ca-certificates
RUN apk add --no-cache \
    nginx \
    curl \
    gettext \
    tini \
    ca-certificates \
    tzdata \
    freetype \
    libpng \
    libjpeg-turbo \
    libzip \
    icu-libs \
    oniguruma

# Install build dependencies and PHP extensions
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        opcache \
        intl \
        pcntl \
    && apk del .build-deps

# Copy custom configurations
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/nginx.conf /etc/nginx/templates/nginx.conf.template
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy production vendor from composer-build stage
COPY --from=composer-build /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from node-build stage
COPY --from=node-build /app/public/build /var/www/html/public/build

# Set permissions and prepare runtime folders
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && mkdir -p /var/log/nginx /var/lib/nginx/tmp /run/app-certificates \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /run/app-certificates

# Expose default HTTP ports
EXPOSE 8080 10000

# Health check endpoint provided by Laravel /up route
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://127.0.0.1:${PORT:-8080}/up || exit 1

ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/app-entrypoint"]
