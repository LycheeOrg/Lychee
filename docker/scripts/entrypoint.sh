#!/bin/sh
# shellcheck disable=SC3040
set -euo pipefail

echo "🚀 Starting Lychee entrypoint..."

# Run environment validation
/usr/local/bin/validate-env.sh

# Wait for database to be ready
if [ "${DB_CONNECTION:-}" = "mysql" ] || [ "${DB_CONNECTION:-}" = "pgsql" ]; then
    echo "⏳ Waiting for database to be ready..."

    max_attempts=30
    attempt=0

    while [ "$attempt" -lt "$max_attempts" ]; do
        if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
            echo "✅ Database port is open!"
            sleep 2  # Give it a moment to fully initialize
            break
        fi

        attempt=$((attempt + 1))
        echo "   Attempt $attempt/$max_attempts... (waiting 2s)"
        sleep 2
    done

    if [ "$attempt" -eq "$max_attempts" ]; then
        echo "❌ ERROR: Database connection timeout"
        exit 1
    fi
fi

echo "Validating and setting PUID/PGID"
PUID=${PUID:-33}
PGID=${PGID:-33}

# Validate PUID/PGID are within safe ranges (no root, within system limits)
if [ "$PUID" -lt 33 ] || [ "$PUID" -gt 65534 ]; then
    echo "❌ ERROR: PUID must be between 33 and 65534 (got: $PUID)"
    exit 1
fi
if [ "$PGID" -lt 33 ] || [ "$PGID" -gt 65534 ]; then
    echo "❌ ERROR: PGID must be between 33 and 65534 (got: $PGID)"
    exit 1
fi

# Only modify user/group if shadow package is available
if command -v usermod >/dev/null 2>&1; then
    if [ "$(id -u www-data)" -ne "$PUID" ]; then
        usermod -o -u "$PUID" www-data
    fi
    if [ "$(id -g www-data)" -ne "$PGID" ]; then
        groupmod -o -g "$PGID" www-data
    fi
fi
echo "  User UID: $(id -u www-data)"
echo "  User GID: $(id -g www-data)"

/usr/local/bin/permissions-check.sh

# Clear any cached config from development
echo "🧹 Clearing bootstrap cache..."
rm -rf bootstrap/cache/*.php

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
echo "🧹 Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Application ready!"

# Execute the main command
exec "$@"