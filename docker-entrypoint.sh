#!/bin/bash
set -e

# Check if APP_KEY is set in .env, generate if missing
APP_KEY_VALUE=$(grep '^APP_KEY=' /var/www/html/.env | cut -d '=' -f2-)
if [ -z "$APP_KEY_VALUE" ] || [ "$APP_KEY_VALUE" = "" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration (after key is set)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations
echo "Running database migrations..."
php artisan migrate --force --seed 2>/dev/null || {
    echo "Migration failed (DB may not be ready yet), will retry in 5 seconds..."
    sleep 5
    php artisan migrate --force --seed 2>/dev/null || {
        echo "Migration retry failed, will retry once more in 10 seconds..."
        sleep 10
        php artisan migrate --force --seed || echo "Migration failed after retries. Please run manually."
    }
}

# Ensure storage link exists
php artisan storage:link 2>/dev/null || true

# Start Apache in foreground
exec apache2-foreground
