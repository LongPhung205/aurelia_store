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
elif [ -f "/var/www/html/docker/aiven_ca.pem" ]; then
    echo "[ENTRYPOINT] Detected built-in Aiven CA certificate. Installing..."
    cp /var/www/html/docker/aiven_ca.pem "$CA_FILE"
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
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

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
    if ! php artisan migrate --force --verbose; then
        echo "[ENTRYPOINT] ERROR: Migration failed! Please verify DB_HOST, DB_PASSWORD, and Aiven MySQL status."
        exit 1
    fi
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "[ENTRYPOINT] RUN_SEEDERS is true. Executing seeders..."
    if ! php artisan db:seed --force --verbose; then
        echo "[ENTRYPOINT] ERROR: Seeding failed! Please verify SEED_ADMIN_* variables."
        exit 1
    fi
fi

# Ensure all existing orders have inventory export histories
php artisan app:sync-order-inventory-history || true

# 7. Validate Nginx and PHP-FPM syntax
echo "[ENTRYPOINT] Checking Nginx configuration..."
nginx -t

echo "[ENTRYPOINT] Checking PHP-FPM configuration..."
php-fpm -t

# Re-ensure ownership and permissions after root artisan tasks
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Start Services
echo "[ENTRYPOINT] Starting PHP-FPM..."
php-fpm -y /usr/local/etc/php-fpm.conf -R &
PHP_FPM_PID=$!

echo "[ENTRYPOINT] Starting Nginx on port ${PORT}..."
nginx -g 'daemon off;' &
NGINX_PID=$!

echo "[ENTRYPOINT] Starting Laravel Queue Worker (OTP mails, order notifications)..."
php artisan queue:restart || true
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=128 &
QUEUE_PID=$!

echo "[ENTRYPOINT] Starting Laravel Reverb WebSocket Server on port 8080..."
php artisan reverb:start --host=127.0.0.1 --port=8080 &
REVERB_PID=$!

echo "[ENTRYPOINT] Starting Scheduler loop (unpaid order auto-cancellation)..."
(
    while true; do
        php artisan schedule:run --no-interaction --quiet || true
        sleep 60
    done
) &
SCHEDULER_PID=$!

terminate() {
    echo "[ENTRYPOINT] Termination signal received. Shutting down gracefully..."
    kill -TERM "$NGINX_PID" 2>/dev/null || true
    kill -TERM "$PHP_FPM_PID" 2>/dev/null || true
    kill -TERM "$QUEUE_PID" 2>/dev/null || true
    kill -TERM "$REVERB_PID" 2>/dev/null || true
    kill -TERM "$SCHEDULER_PID" 2>/dev/null || true
    wait "$NGINX_PID" 2>/dev/null || true
    wait "$PHP_FPM_PID" 2>/dev/null || true
    echo "[ENTRYPOINT] Shutdown complete."
    exit 0
}

trap terminate TERM INT QUIT

echo "[ENTRYPOINT] Aurelia Store is ready and listening on port ${PORT}."

while kill -0 "$PHP_FPM_PID" 2>/dev/null && kill -0 "$NGINX_PID" 2>/dev/null; do
    if ! kill -0 "$QUEUE_PID" 2>/dev/null; then
        echo "[ENTRYPOINT] Warning: Queue worker exited, restarting..."
        php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=128 &
        QUEUE_PID=$!
    fi
    if ! kill -0 "$REVERB_PID" 2>/dev/null; then
        echo "[ENTRYPOINT] Warning: Reverb server exited, restarting..."
        php artisan reverb:start --host=127.0.0.1 --port=8080 &
        REVERB_PID=$!
    fi
    sleep 3
done

echo "[ENTRYPOINT] Core web processes terminated. Exiting container..."
terminate
