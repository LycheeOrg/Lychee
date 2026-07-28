#!/usr/bin/env bash
# shellcheck disable=SC3040
set -euo pipefail

# Configures the `spx` PHP extension (Feature 053 - Memory Profiler) at
# container start, since all of its ini settings are PHP_INI_SYSTEM and
# cannot be toggled at request time from Laravel. Driven by the same
# MEMORY_PROFILER_* env vars used by the Laravel-side feature flag
# (config/features.php).
#
# Deliberately uses manual start/stop spans (spx.http_profiling_auto_start=0)
# rather than SPX's own "always profiling" ini-only mode: this guarantees a
# correct per-request span regardless of whether the host runtime
# (Octane/FrankenPHP's persistent worker model) fires fresh Zend
# request-lifecycle hooks per HTTP request. See
# docs/specs/6-decisions/ADR-0008-memory-profiler-octane-risk.md.

PROFILER_INI="${PHP_INI_DIR:-/usr/local/etc/php}/conf.d/zz-memory-profiler.ini"

MEMORY_PROFILER_ENABLED="${MEMORY_PROFILER_ENABLED:-false}"

is_truthy() {
  case "$(echo "$1" | tr '[:upper:]' '[:lower:]')" in
    1|true|yes|on) return 0 ;;
    *) return 1 ;;
  esac
}

if is_truthy "$MEMORY_PROFILER_ENABLED"; then
  echo "🧠 Enabling Memory Profiler (spx extension)..."

  if [ -z "${MEMORY_PROFILER_SPX_KEY:-}" ]; then
    echo "⚠️  WARNING: MEMORY_PROFILER_ENABLED=true but MEMORY_PROFILER_SPX_KEY is not set."
    echo "   Traces will still be captured, but the analysis-screen link cannot be secured."
    echo "   See docs/specs/2-how-to/enable-memory-profiler.md."
  fi

  {
    echo "spx.data_dir = /app/storage/profiling"
    echo "spx.http_profiling_enabled = 1"
    echo "spx.http_profiling_auto_start = 0"
    echo "spx.http_profiling_metrics = wt,zm,zmab,zmfb,zmac,zmfc,mor"
    if [ -n "${MEMORY_PROFILER_SPX_KEY:-}" ]; then
      echo "spx.http_enabled = 1"
      echo "spx.http_key = ${MEMORY_PROFILER_SPX_KEY}"
    fi
    if [ -n "${MEMORY_PROFILER_SPX_IP_WHITELIST:-}" ]; then
      echo "spx.http_ip_whitelist = ${MEMORY_PROFILER_SPX_IP_WHITELIST}"
    fi
  } > "$PROFILER_INI"
else
  # Extension stays loaded but fully inert (no ini settings enabled): no
  # measurable overhead beyond the disabled extension's own baseline cost.
  : > "$PROFILER_INI"
fi
