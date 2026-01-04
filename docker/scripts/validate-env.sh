#!/bin/sh
# shellcheck disable=SC3040
set -euo

echo "🔍 Validating environment variables..."

###########################
#   VALIDATE APP KEY      #
###########################

# Check if APP_KEY exists, with fallback mechanisms
if [ -z "${APP_KEY:-}" ]; then
    # Check if APP_KEY_FILE is set and load from file
    if [ -n "${APP_KEY_FILE:-}" ] && [ -f "$APP_KEY_FILE" ]; then
        APP_KEY=$(cat "$APP_KEY_FILE")
        export APP_KEY
    # Fallback to /app/.env if it exists
    elif [ -f "/app/.env" ]; then
        APP_KEY=$(grep "^APP_KEY=" /app/.env | cut -d= -f2- | tr -d '"' | tr -d "'")
        export APP_KEY
    fi
fi

# Error out if APP_KEY is still empty
if [ -z "${APP_KEY:-}" ]; then
    echo "❌ ERROR: APP_KEY is not set"
    echo "   Set it via APP_KEY environment variable, APP_KEY_FILE, or /app/.env"
    exit 1
fi

# Validate APP_KEY format (should be base64:... for Laravel)
if ! echo "${APP_KEY}" | grep -qE '^base64:.{32,}'; then
    echo "❌ ERROR: APP_KEY must be in format 'base64:...' with sufficient length"
    echo "   Generate one with: php artisan key:generate --show"
    exit 1
fi

###########################
#    VALIDATE DATABASE    #
###########################

if [ -z "${DB_CONNECTION:-}" ]; then
	echo "❌ ERROR: DB_CONNECTION is not set"
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

    # Check if DB_PASSWORD exists, with fallback mechanisms
    if [ -z "${DB_PASSWORD:-}" ]; then
        # Check if DB_PASSWORD_FILE is set and load from file
        if [ -n "${DB_PASSWORD_FILE:-}" ] && [ -f "$DB_PASSWORD_FILE" ]; then
            DB_PASSWORD=$(cat "$DB_PASSWORD_FILE")
            export DB_PASSWORD
        # Fallback to /app/.env if it exists
        elif [ -f "/app/.env" ]; then
            DB_PASSWORD=$(grep "^DB_PASSWORD=" /app/.env | cut -d= -f2- | tr -d '"' | tr -d "'")
            export DB_PASSWORD
        fi
    fi

    # Error out if DB_PASSWORD is still empty
    if [ -z "${DB_PASSWORD:-}" ]; then
        echo "❌ ERROR: DB_PASSWORD is not set"
        echo "   Set it via DB_PASSWORD environment variable, DB_PASSWORD_FILE, or /app/.env"
        exit 1
    fi
fi

###########################
#     VALIDATE REDIS      #
###########################

# Check if REDIS_PASSWORD exists, with fallback mechanisms (only if Redis is being used)
if [ -n "${REDIS_HOST:-}" ] || [ -n "${REDIS_PASSWORD:-}" ] || [ -n "${REDIS_PASSWORD_FILE:-}" ]; then
    if [ -z "${REDIS_PASSWORD:-}" ]; then
        # Check if REDIS_PASSWORD_FILE is set and load from file
        if [ -n "${REDIS_PASSWORD_FILE:-}" ] && [ -f "$REDIS_PASSWORD_FILE" ]; then
            REDIS_PASSWORD=$(cat "$REDIS_PASSWORD_FILE")
            export REDIS_PASSWORD
        # Fallback to /app/.env if it exists
        elif [ -f "/app/.env" ]; then
            REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" /app/.env | cut -d= -f2- | tr -d '"' | tr -d "'")
            export REDIS_PASSWORD
        fi
    fi
fi

###########################
#     ADDITIONAL ENV      #
###########################


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