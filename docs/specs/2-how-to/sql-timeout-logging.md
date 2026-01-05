# SQL Timeout Logging

This document explains how to log SQL requests that are timing out or approaching timeout limits in Lychee.

## Overview

When running with Laravel Octane (FrankenPHP/Swoole/RoadRunner), requests have a maximum execution time (default: 30 seconds as configured in `config/octane.php`). SQL queries that take too long can cause the entire request to timeout.

The logging system tracks:
1. **Slow queries** - Queries taking longer than configured threshold
2. **Queries approaching timeout** - Queries using >70% of max execution time
3. **Critical queries** - Queries using >90% of max execution time
4. **PHP timeouts** - When the entire request times out

## Configuration

### Enable SQL Logging

Set the following in your `.env` file:

```env
# Enable SQL query logging
DB_LOG_SQL=true

# Optional: Minimum execution time to log (in milliseconds, default: 100)
DB_LOG_SQL_MIN_TIME=100

# Optional: Enable EXPLAIN for MySQL SELECT queries
DB_LOG_SQL_EXPLAIN=true
```

### Timeout Settings

The max execution time is configured in `config/octane.php`:

```php
'max_execution_time' => 30, // seconds
```

## How It Works

### 1. Query Execution Logging

**Location**: [app/Providers/AppServiceProvider.php:238-300](app/Providers/AppServiceProvider.php#L238-L300)

The `logSQL()` method logs queries after they complete with severity based on execution time:
- **Debug**: Normal slow queries (>100ms)
- **Warning**: Queries taking >1 second
- **Error**: Queries approaching timeout (>90% of max_execution_time)

### 2. Timeout Detection Listener

**Location**: [app/Listeners/LogQueryTimeout.php](app/Listeners/LogQueryTimeout.php)

Registered in [app/Providers/EventServiceProvider.php:97-99](app/Providers/EventServiceProvider.php#L97-L99)

This listener provides detailed logging for queries that exceed warning/critical thresholds:
- **70% threshold**: WARNING level log
- **90% threshold**: CRITICAL/ERROR level log

### 3. PHP Timeout Handler

**Location**: [app/Providers/AppServiceProvider.php:200-216](app/Providers/AppServiceProvider.php#L200-L216)

A shutdown function that catches when PHP times out entirely, logging:
- Error message
- File and line where timeout occurred
- Request URL and method

## Log Files

Logs are written to different files based on severity:

- `storage/logs/errors.log` - Critical/slow queries (>90% timeout)
- `storage/logs/warning.log` - Slow queries (>80% timeout or >1s)
- `storage/logs/daily.log` - All SQL queries (when DB_LOG_SQL=true)

## Example Log Entries

### Slow Query Warning
```
[2026-01-04 10:15:32] warning.WARNING: ⚠️ WARNING: Slow query detected {"execution_time_ms":21000,"execution_time_s":21,"timeout_limit_s":30,"percentage":"70%","sql":"SELECT * FROM photos WHERE album_id = ?","bindings":[123],"connection":"mysql","url":"https://lychee.local/api/albums/123/photos"}
```

### Critical Query Near Timeout
```
[2026-01-04 10:20:45] error.ERROR: 🚨 CRITICAL: Query approaching timeout {"execution_time_ms":27500,"execution_time_s":27.5,"timeout_limit_s":30,"percentage":"91.7%","sql":"SELECT * FROM photos WHERE...","bindings":[...],"connection":"mysql","url":"https://lychee.local/api/..."}
```

### PHP Timeout Detected
```
[2026-01-04 10:25:12] error.ERROR: 🔥 PHP TIMEOUT DETECTED {"error":"Maximum execution time of 30 seconds exceeded","file":"/app/vendor/laravel/framework/src/Illuminate/Database/Connection.php","line":742,"url":"https://lychee.local/api/albums/delete","method":"DELETE"}
```

## Troubleshooting Timeouts

When you see timeout logs:

1. **Check the SQL query** - Look for missing indexes, inefficient joins, or full table scans
2. **Use EXPLAIN** - Enable `DB_LOG_SQL_EXPLAIN=true` to see query execution plans
3. **Add indexes** - Common fixes involve adding database indexes
4. **Optimize queries** - Rewrite queries to be more efficient
5. **Increase timeout** - As a last resort, increase `max_execution_time` in `config/octane.php`
6. **Use queues** - Move long-running operations to background jobs

## Performance Impact

SQL logging has minimal performance impact when disabled. When enabled:
- Each query execution triggers event listeners
- EXPLAIN queries add overhead for SELECT statements (MySQL only)
- Logs are written asynchronously via Monolog

**Recommendation**: Only enable in development or temporarily in production for debugging.

## Related Configuration

### Database Connection Timeouts

MySQL connection settings in [config/database.php:111-113](config/database.php#L111-L113):

```php
PDO::ATTR_TIMEOUT => 5, // Connection timeout (5 seconds)
PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION wait_timeout=28800', // 8 hours
```

### Octane Database Ping

The AppServiceProvider pings database connections every 30 seconds to prevent timeouts: [app/Providers/AppServiceProvider.php:340-341](app/Providers/AppServiceProvider.php#L340-L341)

## Viewing Logs

Logs can be viewed via:
1. **Log Viewer** - Built-in at `/log-viewer` (requires admin access)
2. **Command line**: `tail -f storage/logs/errors.log`
3. **Docker**: `docker logs <container_name>`

## Additional Notes

- Timeout detection works best with FrankenPHP, Swoole, or RoadRunner
- Traditional PHP-FPM may not trigger all timeout handlers consistently
