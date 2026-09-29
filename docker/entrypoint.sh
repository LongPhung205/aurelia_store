#!/bin/sh
set -e

echo "=========================================="
echo " Starting Aurelia Store Web Service"
echo "=========================================="

export PORT="${PORT:-8080}"
echo "[ENTRYPOINT] Binding service to port: ${PORT}"

# 1. Setup SSL CA Certificate for Aiven MySQL
mkdir -p /run/app-certificates
CA_FILE="/run/app-certificates/mysql-ca.pem"

if [ -f "/etc/secrets/ca.pem" ]; then
    echo "[ENTRYPOINT] Detected Secret File at /etc/secrets/ca.pem. Installing..."
    cp /etc/secrets/ca.pem "$CA_FILE"
elif [ -n "${MYSQL_CA_CONTENT:-}" ]; then
    echo "[ENTRYPOINT] Detected MYSQL_CA_CONTENT environment variable. Writing certificate..."
    printf '%s\n' "$MYSQL_CA_CONTENT" > "$CA_FILE"
elif [ -n "${AIVEN_CA_CERT:-}" ]; then
    echo "[ENTRYPOINT] Detected AIVEN_CA_CERT environment variable. Writing certificate..."
    printf '%s\n' "$AIVEN_CA_CERT" > "$CA_FILE"
fi

if [ -f "$CA_FILE" ]; then
    chmod 644 "$CA_FILE"
    chown www-data:www-data "$CA_FILE"
    export MYSQL_ATTR_SSL_CA="$CA_FILE"
    if [ -f "/var/www/html/docker/check-ca.php" ]; then
        php /var/www/html/docker/check-ca.php "$CA_FILE" || echo "[ENTRYPOINT] Warning: CA validation encountered an issue."
    fi
else
    echo "[ENTRYPOINT] No custom MySQL CA certificate provided. Proceeding with system default certificates."
fi

# 2. Render Nginx Configuration with actual PORT
if [ -f "/etc/nginx/templates/nginx.conf.template" ]; then
    echo "[ENTRYPOINT] Generating /etc/nginx/nginx.conf with PORT=${PORT}..."
    envsubst '${PORT}' < /etc/nginx/templates/nginx.conf.template > /etc/nginx/nginx.conf
elif [ -f "/var/www/html/docker/nginx.conf" ]; then
    echo "[ENTRYPOINT] Generating /etc/nginx/nginx.conf from application template with PORT=${PORT}..."
    envsubst '${PORT}' < /var/www/html/docker/nginx.conf > /etc/nginx/nginx.conf
fi

# 3. Ensure required directories and permissions
echo "[ENTRYPOINT] Ensuring directory permissions for storage and bootstrap/cache..."
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Storage Link
echo "[ENTRYPOINT] Linking storage directory..."
php artisan storage:link || true

# 5. Laravel Caching
echo "[ENTRYPOINT] Optimizing Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Database Migrations & Seeders
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[ENTRYPOINT] RUN_MIGRATIONS is true. Executing migrations..."
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "[ENTRYPOINT] RUN_SEEDERS is true. Executing seeders..."
    php artisan db:seed --force
fi

# 7. Validate Nginx and PHP-FPM syntax
echo "[ENTRYPOINT] Checking Nginx configuration..."
nginx -t

echo "[ENTRYPOINT] Checking PHP-FPM configuration..."
php-fpm -t

# 8. Start Services
echo "[ENTRYPOINT] Starting PHP-FPM..."
php-fpm -y /usr/local/etc/php-fpm.conf -R &
PHP_FPM_PID=$!

echo "[ENTRYPOINT] Starting Nginx on port ${PORT}..."
nginx -g 'daemon off;' &
NGINX_PID=$!

terminate() {
    echo "[ENTRYPOINT] Termination signal received. Shutting down gracefully..."
    kill -TERM "$NGINX_PID" 2>/dev/null || true
    kill -TERM "$PHP_FPM_PID" 2>/dev/null || true
    wait "$NGINX_PID" 2>/dev/null || true
    wait "$PHP_FPM_PID" 2>/dev/null || true
    echo "[ENTRYPOINT] Shutdown complete."
    exit 0
}

trap terminate TERM INT QUIT

echo "[ENTRYPOINT] Aurelia Store is ready and listening on port ${PORT}."

while kill -0 "$PHP_FPM_PID" 2>/dev/null && kill -0 "$NGINX_PID" 2>/dev/null; do
    sleep 2
done

echo "[ENTRYPOINT] One of the background processes terminated. Exiting container..."
terminate
