#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

echo "🔍 Validating permissions..."

# Ensure critical directories have correct rights
chown -R www-data:www-data /app/storage/bootstrap /app/storage/debugbar /app/storage/framework
chown -R www-data:www-data /app/bootstrap/cache
chown www-data:www-data /app/storage
chown www-data:www-data /app/public

# echo "who am i"
# id
# chown -R www-data:www-data /data /config
# chmod -R 775 /data /config

echo "⏰ Set Permissions for Lychee folders..."
# Ensure www-data owns necessary directories
find /app/storage -type d \( ! -user "www-data" -o ! -group "www-data" \) -exec chown "www-data":"www-data" {} +
find /app/bootstrap/cache -type d \( ! -user "www-data" -o ! -group "www-data" \) -exec chown "www-data":"www-data" {} +

# Set restrictive permissions: 750 for directories (owner+group only, no world access)
find /app/storage -type d \( ! -perm 750 \) -exec chmod 750 {} + 2>/dev/null || true
find /app/bootstrap/cache -type d \( ! -perm 750 \) -exec chmod 750 {} + 2>/dev/null || true

# Files: 640 for sensitive, 644 for public
find /app/storage -type f \( ! -perm 640 \) -exec chmod 640 {} + 2>/dev/null || true
find /app/bootstrap/cache -type f \( ! -perm 640 \) -exec chmod 640 {} + 2>/dev/null || true
echo "✅ Permissions set securely"

# Safely check SKIP_PERMISSIONS_CHECKS
skip_check="${SKIP_PERMISSIONS_CHECKS:-no}"

if [ "$skip_check" = "yes" ] || [ "$skip_check" = "YES" ]; then
  echo "⚠️ WARNING: Skipping upload permissions check"
else
  echo "⏰ Set Permissions for Upload/dist folders (this may take a while)..."

  # More restrictive permissions - no world-readable for sensitive directories
  # Only set permissions on writable directories that need it

  # Ensure www-data owns necessary directories
  find /app/public/uploads -type d \( ! -user "www-data" -o ! -group "www-data" \) -exec chown "www-data":"www-data" {} + 2>/dev/null || true
  find /app/public/dist -type d \( ! -user "www-data" -o ! -group "www-data" \) -exec chown "www-data":"www-data" {} + 2>/dev/null || true

  # Upload directories need 755 for web serving
  find /app/public/uploads -type d \( ! -perm 755 \) -exec chmod 755 {} + 2>/dev/null || true
  find /app/public/dist -type d \( ! -perm 755 \) -exec chmod 755 {} + 2>/dev/null || true

  # Files: 640 for sensitive, 644 for public
  find /app/public/uploads -type f \( ! -perm 644 \) -exec chmod 644 {} + 2>/dev/null || true
  find /app/public/dist -type f \( ! -perm 644 \) -exec chmod 644 {} + 2>/dev/null || true

  echo "✅ Permissions set securely"
fi
