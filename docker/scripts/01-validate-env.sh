#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

echo "🔍 Validating environment variables..."

###########################
#   VALIDATE APP KEY      #
###########################

# Check if APP_KEY exists, with fallback mechanisms
if [ -z "${APP_KEY:-}" ]; then
  # Check if APP_KEY_FILE is set and load from file
  if [ -n "${APP_KEY_FILE:-}" ]; then
    if [ -f "$APP_KEY_FILE" ]; then
      APP_KEY=$(cat "$APP_KEY_FILE")
      export APP_KEY
      echo "✅ Loaded APP_KEY from file: ${APP_KEY_FILE}"
    else
      echo "❌ ERROR: APP_KEY_FILE is set but file does not exist: ${APP_KEY_FILE}"
      exit 1
    fi
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
  echo "   You can generate one with: 'echo \"APP_KEY=base64:\$(openssl rand -base64 32)\"' or 'php artisan key:generate --show'"
  exit 1
fi

# Validate APP_KEY is exactly 32 bytes when decoded
# Temporarily disable errexit to handle base64 failure gracefully
set +e
KEY_BYTE_COUNT=$(echo "${APP_KEY#base64:}" | base64 -d 2>/dev/null | wc -c)
BASE64_EXIT=$?
set -e

if [ "${BASE64_EXIT}" -ne 0 ] || [ "${KEY_BYTE_COUNT}" -eq 0 ]; then
  echo "❌ ERROR: APP_KEY contains invalid base64 data"
  echo "   Generate one with: 'echo \"APP_KEY=base64:\$(openssl rand -base64 32)\"' or 'php artisan key:generate --show'"
  exit 1
fi
if [ "${KEY_BYTE_COUNT}" -ne 32 ]; then
  echo "❌ ERROR: APP_KEY must be exactly 32 bytes when decoded (got: ${KEY_BYTE_COUNT} bytes)"
  echo "   Generate one with: 'echo \"APP_KEY=base64:\$(openssl rand -base64 32)\"' or 'php artisan key:generate --show'"
  exit 1
fi

###########################
#    VALIDATE DATABASE    #
###########################

if [ -z "${DB_CONNECTION:-}" ]; then
  echo "❌ ERROR: DB_CONNECTION is not set"
  sleep 10
  exit 1
fi

# Validate DB_CONNECTION value
case "${DB_CONNECTION}" in
mysql | pgsql | sqlite)
  echo "✅ Valid database connection type: ${DB_CONNECTION}"
  ;;
*)
  echo "❌ ERROR: DB_CONNECTION must be mysql, pgsql, or sqlite (got: ${DB_CONNECTION})"
  sleep 10
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
    if [ -n "${DB_PASSWORD_FILE:-}" ]; then
      if [ -f "$DB_PASSWORD_FILE" ]; then
        DB_PASSWORD=$(cat "$DB_PASSWORD_FILE")
        export DB_PASSWORD
        echo "✅ Loaded DB_PASSWORD from file: ${DB_PASSWORD_FILE}"
      else
        echo "❌ ERROR: DB_PASSWORD_FILE is set but file does not exist: ${DB_PASSWORD_FILE}"
        exit 1
      fi
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

##################################
#   OPTIONAL SERVICE SECRETS     #
##################################

# Generic helper: if <VAR> is empty and <VAR>_FILE is set, load the value from
# the file it points to into <VAR> (Docker/Kubernetes secrets convention).
# Unlike APP_KEY/DB_PASSWORD/REDIS_PASSWORD above, these are all optional
# credentials (config/services.php third-party integrations, plus
# KEYGEN_API_KEY), so an unset value is not an error - it just means the
# corresponding integration stays disabled.
load_secret_from_file() {
  local var_name="$1"
  local file_var_name="${var_name}_FILE"
  local current_value="${!var_name:-}"
  local file_path="${!file_var_name:-}"

  if [ -n "${current_value}" ] || [ -z "${file_path}" ]; then
    return
  fi

  if [ ! -f "${file_path}" ]; then
    echo "❌ ERROR: ${file_var_name} is set but file does not exist: ${file_path}"
    exit 1
  fi

  if [ ! -r "${file_path}" ]; then
    echo "❌ ERROR: ${file_var_name} points to a file that is not readable: ${file_path}"
    exit 1
  fi

  export "${var_name}=$(cat "${file_path}")"
  echo "✅ Loaded ${var_name} from file: ${file_path}"
}

# All confidential keys backing config/services.php, plus KEYGEN_API_KEY - each
# supports the matching "<VAR>_FILE" env var so secrets can be mounted as files
# instead of passed as plaintext environment variables.
SERVICES_SECRET_VARS=(
  AI_VISION_FACE_API_KEY
  AI_VISION_NSFW_API_KEY
  MAILGUN_SECRET
  POSTMARK_TOKEN
  AWS_ACCESS_KEY_ID
  AWS_SECRET_ACCESS_KEY
  AMAZON_SIGNIN_SECRET
  APPLE_CLIENT_SECRET
  AUTHELIA_CLIENT_SECRET
  AUTHENTIK_CLIENT_SECRET
  FACEBOOK_CLIENT_SECRET
  GITHUB_CLIENT_SECRET
  GOOGLE_CLIENT_SECRET
  MASTODON_SECRET
  MICROSOFT_CLIENT_SECRET
  NEXTCLOUD_CLIENT_SECRET
  KEYCLOAK_CLIENT_SECRET
  KEYGEN_API_KEY
)

for service_secret_var in "${SERVICES_SECRET_VARS[@]}"; do
  load_secret_from_file "${service_secret_var}"
done

###########################
#     ADDITIONAL ENV      #
###########################

# Validate APP_ENV
if [ -n "${APP_ENV:-}" ]; then
  case "${APP_ENV}" in
  production | staging | development | local | testing)
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
