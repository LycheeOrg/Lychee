#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

echo "🚀 Starting Lychee entrypoint..."

# Run configuration file check
/usr/local/bin/00-conf-check.sh

# Run environment validation
/usr/local/bin/01-validate-env.sh

# This is commended for now as FrankenPHP uses native env vars
# And we are double checking that php also has access to them
# If php is complaining, then we can re-enable this.

# Dump environment variables to .env file for Laravel (only if not using FrankenPHP)
# if [ ! -f "/app/frankenphp_target" ]; then
#     /usr/local/bin/02-dump-env.sh
# else
#     echo "ℹ️  Skipping .env dump (FrankenPHP uses native environment variables)"
# fi

# Wait for database to be ready
/usr/local/bin/03-db-check.sh

# Setup user permissions
/usr/local/bin/04-user-setup.sh

# Check and set permissions
/usr/local/bin/05-permissions-check.sh

echo "Checking RUN_AS_ROOT setting"
RUN_AS_ROOT=${RUN_AS_ROOT:-no}
if [ "$RUN_AS_ROOT" = "yes" ]; then
  echo "⚠️  WARNING: Running as root (RUN_AS_ROOT=yes)"
  echo "   This is not recommended for production environments"
else
  echo "✅ Will drop privileges to www-data user"
fi

# Helper function to run commands as www-data
run_as_www() {
  # If RUN_AS_ROOT is set to yes, run directly without switching user
  if [ "$RUN_AS_ROOT" = "yes" ]; then
    "$@"
    return
  fi

  # Use gosu if available (Debian)
  if command -v gosu >/dev/null 2>&1; then
    gosu www-data "$@"
  # Use su-exec if available (Alpine)
  elif command -v su-exec >/dev/null 2>&1; then
    su-exec www-data "$@"
  else
    # We die
    echo "❌ ERROR: Neither gosu nor su-exec found to switch user"
    exit 1
  fi
}

# Clear any cached config from development
echo "🧹 Clearing bootstrap cache..."
rm -rf bootstrap/cache/*.php

# Detect LYCHEE_MODE and execute appropriate command
LYCHEE_MODE=${LYCHEE_MODE:-web}

case "$LYCHEE_MODE" in
web)
  echo "🌐 Starting Lychee in web mode..."

  # Run database migrations (only in web mode to avoid race conditions)
  echo "🔄 Running database migrations..."
  run_as_www php artisan migrate --force

  # Clear and cache configuration
  echo "🧹 Optimizing application..."
  run_as_www php artisan config:clear
  run_as_www php artisan config:cache
  run_as_www php artisan route:clear
  run_as_www php artisan route:cache
  run_as_www php artisan view:clear
  run_as_www php artisan view:cache

  echo "✅ Application ready!"

  if [ ! -f "/app/frankenphp_target" ]; then
    # Just to make sure.
    composer dump-autoload --optimize --no-scripts --no-dev

    # Ajust permissions for Nginx
    chown -R www-data:www-data /var/lib/nginx /var/log/nginx
    # yeah it is disgusting but nginx needs it.
	  chmod 666 /dev/stdout /dev/stderr

    # Start PHP-FPM and Nginx for traditional Docker setup
    echo "🚀 Starting PHP-FPM..."
    php-fpm8.5
  fi

  # Execute the main command (from Dockerfile CMD: octane:start) as www-data
  if [ "$RUN_AS_ROOT" = "yes" ]; then
    exec "$@"
  elif command -v gosu >/dev/null 2>&1; then
    exec gosu www-data "$@"
  elif command -v su-exec >/dev/null 2>&1; then
    exec su-exec www-data "$@"
  else
    # We die
    echo "❌ ERROR: Neither gosu nor su-exec found to switch user"
    exit 1
  fi
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
    if run_as_www php artisan migrate:status 2>/dev/null | grep -q "Pending"; then
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
    run_as_www php artisan queue:work \
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
