#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

# Check if admin user creation is requested
if [ -n "${ADMIN_USER:-}" ]; then
  value=""

  # Prefer reading from file (more secure than env var)
  if [ -n "${ADMIN_PASSWORD_FILE:-}" ] && [ -f "${ADMIN_PASSWORD_FILE}" ]; then
    # Securely read password file (no command substitution vulnerabilities)
    value=$(cat "${ADMIN_PASSWORD_FILE}")
  elif [ -n "${ADMIN_PASSWORD:-}" ]; then
    value="${ADMIN_PASSWORD}"
    echo "⚠️  WARNING: Using ADMIN_PASSWORD from environment - prefer ADMIN_PASSWORD_FILE for security"
  fi

  # Validate password meets minimum requirements
  if [ -n "$value" ]; then
    password_length=${#value}
    if [ "$password_length" -lt 8 ]; then
      echo "❌ ERROR: Admin password must be at least 8 characters long"
      exit 1
    fi

    echo "🚀 Creating admin account for user: ${ADMIN_USER}"
    php artisan lychee:create_user "${ADMIN_USER}" "${value}"

    # Clear sensitive variables
    unset value
    unset ADMIN_PASSWORD
  else
    echo "⚠️  WARNING: ADMIN_USER set but no password provided (need ADMIN_PASSWORD or ADMIN_PASSWORD_FILE)"
  fi
fi
