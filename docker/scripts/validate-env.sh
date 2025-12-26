#!/bin/sh
# shellcheck disable=SC3040
set -euo pipefail

echo "🔍 Validating environment variables..."

# Required variables
REQUIRED_VARS="APP_KEY DB_CONNECTION"

# Check required variables
for var in $REQUIRED_VARS; do
    val=$(eval echo "\${$var:-}")
    if [ -z "$val" ]; then
        echo "❌ ERROR: Required environment variable $var is not set"
        exit 1
    fi
done

# Validate APP_KEY format (should be base64:... for Laravel)
if ! echo "${APP_KEY}" | grep -qE '^base64:.{32,}'; then
    echo "❌ ERROR: APP_KEY must be in format 'base64:...' with sufficient length"
    echo "   Generate one with: php artisan key:generate --show"
    exit 1
fi

# Validate DB_CONNECTION value
case "${DB_CONNECTION}" in
    mysql|pgsql|sqlite)
        echo "✅ Valid database connection type: ${DB_CONNECTION}"
        ;;
    *)
        echo "❌ ERROR: DB_CONNECTION must be mysql, pgsql, or sqlite (got: ${DB_CONNECTION})"
        exit 1
        ;;
esac

# Validate database credentials are set for mysql/pgsql
if [ "${DB_CONNECTION}" = "mysql" ] || [ "${DB_CONNECTION}" = "pgsql" ]; then
    if [ -z "${DB_HOST:-}" ]; then
        echo "❌ ERROR: DB_HOST is required for ${DB_CONNECTION}"
        exit 1
    fi
    if [ -z "${DB_DATABASE:-}" ]; then
        echo "❌ ERROR: DB_DATABASE is required for ${DB_CONNECTION}"
        exit 1
    fi
    if [ -z "${DB_USERNAME:-}" ]; then
        echo "❌ ERROR: DB_USERNAME is required for ${DB_CONNECTION}"
        exit 1
    fi
    if [ -z "${DB_PASSWORD:-}" ]; then
        echo "⚠️  WARNING: DB_PASSWORD is empty - this may cause authentication issues"
    fi
fi

# Validate APP_ENV
if [ -n "${APP_ENV:-}" ]; then
    case "${APP_ENV}" in
        production|staging|development|local|testing)
            echo "✅ Valid environment: ${APP_ENV}"
            ;;
        *)
            echo "⚠️  WARNING: Unusual APP_ENV value: ${APP_ENV}"
            ;;
    esac
fi

# Security checks
if [ "${APP_ENV:-production}" = "production" ]; then
    if [ "${APP_DEBUG:-false}" = "true" ]; then
        echo "⚠️  WARNING: APP_DEBUG is enabled in production - this exposes sensitive information!"
    fi
fi

echo "✅ Core variables validated"
echo "🎉 Environment validation complete!"