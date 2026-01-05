#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

echo "🚀 Starting Lychee entrypoint..."

# Run environment validation
/usr/local/bin/validate-env.sh

# This is commended for now as FrankenPHP uses native env vars
# And we are double checking that php also has access to them
# If php is complaining, then we can re-enable this.

# Dump environment variables to .env file for Laravel (only if not using FrankenPHP)
# if [ ! -f "/app/frankenphp_target" ]; then
#     /usr/local/bin/dump-env.sh
# else
#     echo "ℹ️  Skipping .env dump (FrankenPHP uses native environment variables)"
# fi

# Wait for database to be ready
if [ "${DB_CONNECTION:-}" = "mysql" ] || [ "${DB_CONNECTION:-}" = "pgsql" ]; then
  echo "⏳ Waiting for database to be ready..."

  max_attempts=30
  attempt=0

  while [ "$attempt" -lt "$max_attempts" ]; do
    if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
      echo "✅ Database port is open!"
      sleep 2 # Give it a moment to fully initialize
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

if pgrep -u www-data >/dev/null; then
  echo "www-data has running processes; skipping usermod"
else
  if command -v usermod >/dev/null 2>&1; then
    # Only modify user/group if shadow package is available
    if [ "$(id -u www-data)" -ne "$PUID" ]; then
      usermod -o -u "$PUID" www-data
    fi
    if [ "$(id -g www-data)" -ne "$PGID" ]; then
      groupmod -o -g "$PGID" www-data
    fi
  fi
fi
echo "  User UID: $(id -u www-data)"
echo "  User GID: $(id -g www-data)"

/usr/local/bin/permissions-check.sh

# Clear any cached config from development
echo "🧹 Clearing bootstrap cache..."
rm -rf bootstrap/cache/*.php

# Check for /conf/.env file - this indicates misconfiguration
if [ -f "/conf/.env" ]; then
  echo "❌ ERROR: /conf/.env file detected"
  echo "   Containers should not have mounted .env files at /conf/.env"
  echo "   Please check your docker-compose.yml configuration"
  echo "   See https://lycheeorg.github.io/docs/upgrade.html"
  exit 1
fi

# Detect LYCHEE_MODE and execute appropriate command
LYCHEE_MODE=${LYCHEE_MODE:-web}

case "$LYCHEE_MODE" in
web)
  echo "🌐 Starting Lychee in web mode..."

  # Run database migrations (only in web mode to avoid race conditions)
  echo "🔄 Running database migrations..."
  php artisan migrate --force

  # Clear and cache configuration
  echo "🧹 Optimizing application..."
  php artisan config:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  echo "✅ Application ready!"

  if [ ! -f "/app/frankenphp_target" ]; then
    # Start PHP-FPM and Nginx for traditional Docker setup
    echo "🚀 Starting PHP-FPM..."
    php-fpm8.5
  fi

  # Execute the main command (from Dockerfile CMD: octane:start)
  exec "$@"
  ;;
worker)
  echo "⚙️  Starting Lychee in worker mode..."

  # Check for pending migrations (wait for web container to complete them)
  max_migration_attempts=720 # 1h max (720*5s)
  migration_attempt=0

  while [ "$migration_attempt" -lt "$max_migration_attempts" ]; do
    # Check if there are pending migrations
    # php artisan migrate:status returns exit code 0 if all migrations are run
    # We check for "Pending" in the output to detect pending migrations
    if php artisan migrate:status 2>/dev/null | grep -q "Pending"; then
      migration_attempt=$((migration_attempt + 1))
      echo "⏳ Pending migrations detected (attempt $migration_attempt/$max_migration_attempts)"
      echo "   Waiting 5 seconds for web container to complete migrations..."
      sleep 5
    else
      echo "✅ All migrations are up to date"
      break
    fi
  done

  if [ "$migration_attempt" -eq "$max_migration_attempts" ]; then
    echo "⚠️  WARNING: Migrations still pending after ${max_migration_attempts} attempts (1 hour)"
    echo "   Starting worker anyway - this may cause issues if migrations are required"
  fi

  echo "🔄 Auto-restart enabled: worker will restart if it exits"

  # Get queue configuration from environment
  QUEUE_NAMES=${QUEUE_NAMES:-default}
  WORKER_MAX_TIME=${WORKER_MAX_TIME:-3600}
  QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

  echo "📋 Queue names: $QUEUE_NAMES"
  echo "⏱️  Max time: ${WORKER_MAX_TIME}s"
  echo "📡 Queue connection: $QUEUE_CONNECTION"

  # Warn if using sync driver (not recommended for worker mode)
  if [ "$QUEUE_CONNECTION" = "sync" ]; then
    echo "⚠️  WARNING: QUEUE_CONNECTION=sync is not recommended for worker mode."
    echo "   Jobs will run synchronously, defeating the purpose of a queue worker."
    echo "   Consider using 'redis' or 'database' for persistent asynchronous queues."
  fi

  # Track if we should keep running
  KEEP_RUNNING=true

  # Handle graceful shutdown
  trap 'echo "🛑 Received shutdown signal, stopping..."; KEEP_RUNNING=false' TERM INT

  # Auto-restart loop: if queue:work exits, restart it
  # This handles memory leak mitigation (max-time) and crash recovery
  while $KEEP_RUNNING; do
    echo "🚀 Starting queue worker ($(date '+%Y-%m-%d %H:%M:%S'))"

    # Default exit code to 0
    EXIT_CODE=0

    # Run queue worker with standard options
    # --tries=3: retry failed jobs up to 3 times
    # --timeout=3600: kill job if it runs longer than 1 hour
    # --sleep=3: sleep 3 seconds when queue is empty
    # --max-time=$WORKER_MAX_TIME: restart worker after N seconds (memory leak mitigation)
    php artisan queue:work \
      --queue="$QUEUE_NAMES" \
      --tries=3 \
      --timeout=3600 \
      --sleep=3 \
      --max-time="$WORKER_MAX_TIME" || EXIT_CODE=$?

    if [ $EXIT_CODE -eq 0 ]; then
      echo "✅ Queue worker exited cleanly (exit code 0)"
    else
      echo "⚠️  Queue worker exited with code $EXIT_CODE"
    fi

    # Exit if we received shutdown signal
    if ! $KEEP_RUNNING; then
      echo "👋 Shutting down worker..."
      exit $EXIT_CODE
    fi

    echo "⏳ Waiting 5 seconds before restart..."
    sleep 5
  done
  ;;
*)
  echo "❌ ERROR: Invalid LYCHEE_MODE: $LYCHEE_MODE. Must be 'web' or 'worker'."
  exit 1
  ;;
esac
