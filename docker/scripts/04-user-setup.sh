#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

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
      echo "Updating www-data UID to $PUID"
      usermod -o -u "$PUID" www-data
    fi
    if [ "$(id -g www-data)" -ne "$PGID" ]; then
      echo "Updating www-data GID to $PGID"
      groupmod -o -g "$PGID" www-data
    fi
  fi
fi
echo "  User UID: $(id -u www-data)"
echo "  User GID: $(id -g www-data)"