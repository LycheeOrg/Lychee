#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

# Check if migrations folder exists and is not empty
# This is a sanity check to ensure that the user did not just mount
#   - ./database:/app/database
if [ ! -d "database/migrations" ]; then
  echo "❌ ERROR: Migrations folder does not exist at database/migrations"
  echo "   Cannot start container without migrations."
  exit 1
elif [ -z "$(ls -A database/migrations 2>/dev/null)" ]; then
  echo "❌ ERROR: Migrations folder is empty at database/migrations"
  echo "   Cannot start container without migrations."
  exit 1
fi

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
