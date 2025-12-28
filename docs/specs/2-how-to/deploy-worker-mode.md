# How-To: Deploy Lychee Worker Mode

**Author:** Lychee Team
**Last Updated:** 2025-12-28
**Feature:** 002-worker-mode
**Related:** [Feature 002 Spec](../4-architecture/features/002-worker-mode/spec.md)

## Overview

Lychee supports running separate worker containers for background job processing, enabling horizontal scaling of queue workers independently from web servers. This guide covers deploying and configuring worker mode for production use.

## Why Use Worker Mode?

Worker mode addresses background processing bottlenecks without impacting web server capacity:

- **Horizontal Scaling**: Run multiple worker containers processing jobs in parallel
- **Resource Isolation**: Dedicate resources to background tasks (photo processing, notifications, etc.)
- **Memory Leak Mitigation**: Workers automatically restart after configured intervals
- **Failure Recovery**: Auto-restart loop ensures continuous operation after crashes
- **Queue Priority**: Process high-priority jobs before low-priority ones

## Prerequisites

- Docker and Docker Compose v2 installed
- Existing Lychee deployment with database and (optionally) Redis
- Lychee Docker image with worker mode support (version >= TBD)

## Environment Variables

### LYCHEE_MODE (Required for Worker)

Controls container startup mode:

```bash
LYCHEE_MODE=web     # Default: Run FrankenPHP/Octane web server
LYCHEE_MODE=worker  # Run Laravel queue worker
```

**Web mode** (default) is unchanged from previous Lychee versions. Omitting `LYCHEE_MODE` defaults to web mode for backward compatibility.

**Worker mode** starts `php artisan queue:work` in an auto-restart loop, processing background jobs continuously.

### QUEUE_CONNECTION (Critical for Worker)

Specifies the queue backend driver:

```bash
QUEUE_CONNECTION=database  # Use database for queue storage (no Redis dependency)
QUEUE_CONNECTION=redis     # Use Redis for queue storage (recommended for production)
QUEUE_CONNECTION=sync      # Synchronous processing (NOT recommended for worker mode)
```

**Recommendation**: Use `redis` for production (faster, better concurrency). Use `database` if Redis is unavailable. **Never use `sync` in worker mode** - jobs will run synchronously, defeating the purpose of a queue worker.

### QUEUE_NAMES (Optional)

Comma-separated queue names for priority processing:

```bash
QUEUE_NAMES=default           # Default: process only 'default' queue
QUEUE_NAMES=high,default,low  # Process high-priority jobs first, then default, then low
```

Example use cases:
- `high`: User-initiated photo uploads/processing
- `default`: General background tasks
- `low`: Cleanup, maintenance, non-urgent operations

### WORKER_MAX_TIME (Optional)

Worker restart interval in seconds for memory leak mitigation:

```bash
WORKER_MAX_TIME=3600  # Default: restart after 1 hour (3600 seconds)
WORKER_MAX_TIME=7200  # Restart after 2 hours
```

Workers automatically exit and restart after this interval to prevent memory leaks from accumulating. The auto-restart loop ensures continuous operation.

## Deployment: Docker Compose

### Single Worker Container

Edit your existing `docker-compose.yaml` to uncomment the `lychee_worker` service:

```yaml
services:
  # Existing lychee_api service...

  lychee_worker:
    image: lychee-frankenphp:latest
    container_name: lychee-worker
    restart: unless-stopped

    environment:
      LYCHEE_MODE: worker  # Enable worker mode
      QUEUE_CONNECTION: "${QUEUE_CONNECTION:-database}"
      QUEUE_NAMES: "${QUEUE_NAMES:-default}"
      WORKER_MAX_TIME: "${WORKER_MAX_TIME:-3600}"

      # Database (same as lychee_api)
      DB_CONNECTION: "${DB_CONNECTION:-mysql}"
      DB_HOST: "${DB_HOST:-lychee_db}"
      DB_PORT: "${DB_PORT:-3306}"
      DB_DATABASE: "${DB_DATABASE:-lychee}"
      DB_USERNAME: "${DB_USERNAME:-lychee}"

      # Redis (if using QUEUE_CONNECTION=redis)
      REDIS_HOST: "lychee_cache"
      REDIS_PASSWORD: "null"
      REDIS_PORT: "6379"

    volumes:
      # CRITICAL: Share storage with web service
      - ./lychee/uploads:/app/public/uploads
      - ./lychee/storage/app:/app/storage/app
      - ./lychee/logs:/app/storage/logs
      - ./lychee/tmp:/app/storage/tmp
      - .env:/app/.env:ro

    depends_on:
      lychee_db:
        condition: service_healthy
      lychee_cache:
        condition: service_started  # If using Redis

    healthcheck:
      test: ["CMD-SHELL", "pgrep -f 'queue:work' || exit 1"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 60s

    networks:
      - lychee
```

**Start the worker:**

```bash
docker compose up -d lychee_worker
docker compose logs -f lychee_worker  # Monitor logs
```

### Multiple Worker Containers (Horizontal Scaling)

To run multiple workers processing jobs in parallel:

```bash
docker compose up -d --scale lychee_worker=3
```

This creates 3 worker containers. Each processes jobs independently, improving throughput for background tasks.

**Note**: When scaling, remove `container_name: lychee-worker` from the compose file (Docker Compose generates unique names when scaling).

## Queue Configuration

### Redis Queue Driver (Recommended)

**Advantages:**
- Faster job processing
- Better concurrency handling
- Lower database load

**Setup:**

1. Ensure `lychee_cache` (Redis) service is running:

```yaml
lychee_cache:
  image: redis:alpine
  # ... (existing configuration)
```

2. Set `QUEUE_CONNECTION=redis` in both `lychee_api` and `lychee_worker`:

```bash
QUEUE_CONNECTION=redis
REDIS_HOST=lychee_cache
REDIS_PORT=6379
```

3. Restart containers:

```bash
docker compose restart lychee_api lychee_worker
```

### Database Queue Driver

**Advantages:**
- No Redis dependency
- Simpler setup for small deployments

**Setup:**

1. Set `QUEUE_CONNECTION=database`:

```bash
QUEUE_CONNECTION=database
```

2. Ensure the `jobs` table exists (Laravel migration):

```bash
docker compose exec lychee_api php artisan queue:table
docker compose exec lychee_api php artisan migrate
```

3. Restart worker:

```bash
docker compose restart lychee_worker
```

## Monitoring

### Check Worker Status

```bash
# View worker logs
docker compose logs -f lychee_worker

# Check if queue:work process is running
docker compose exec lychee_worker pgrep -f 'queue:work'

# View container health status
docker compose ps lychee_worker
```

### Expected Log Output

**Successful startup:**

```
🚀 Starting Lychee entrypoint...
✅ Application ready!
⚙️  Starting Lychee in worker mode...
🔄 Auto-restart enabled: worker will restart if it exits
📋 Queue names: high,default,low
⏱️  Max time: 3600s
📡 Queue connection: redis
🚀 Starting queue worker (2025-12-28 10:00:00)
```

**Job processing:**

```
[2025-12-28 10:01:00] Processing: App\Jobs\ExtractColoursJob
[2025-12-28 10:01:05] Processed:  App\Jobs\ExtractColoursJob
```

**Auto-restart (after WORKER_MAX_TIME):**

```
✅ Queue worker exited cleanly (exit code 0)
⏳ Waiting 5 seconds before restart...
🚀 Starting queue worker (2025-12-28 11:00:00)
```

### Monitor Queue Depth

Check pending jobs in the queue:

**Redis:**

```bash
docker compose exec lychee_cache redis-cli
> LLEN queues:default
```

**Database:**

```bash
docker compose exec lychee_db mysql -u lychee -p lychee -e "SELECT COUNT(*) FROM jobs;"
```

## Job Deduplication

To prevent duplicate jobs from being queued (e.g., multiple concurrent photo uploads triggering the same background task), use Laravel's `WithoutOverlapping` middleware:

```php
use Illuminate\Queue\Middleware\WithoutOverlapping;

class RecomputeAlbumStatsJob implements ShouldQueue
{
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->albumId))
                ->releaseAfter(60)  // Release lock after 60 seconds
                ->expireAfter(120), // Expire lock after 120 seconds
        ];
    }
}
```

**Requirements:**
- Redis cache driver (`CACHE_STORE=redis`) for distributed locking
- Or database cache driver for single-server deployments

## Graceful Shutdown

Workers handle `SIGTERM` gracefully, completing in-flight jobs before exiting:

```bash
# Send SIGTERM (stop command)
docker compose stop lychee_worker

# Worker completes current job (up to --timeout=3600), then exits
```

**Rolling updates:**
1. Start new worker containers
2. Stop old workers (graceful SIGTERM)
3. Old workers finish current jobs, new workers take over

## Troubleshooting

### Worker Exits Immediately

**Symptom:** Worker container exits with "Queue connection failed"

**Solution:** Verify queue backend is reachable:

```bash
# Test Redis connection
docker compose exec lychee_worker nc -z lychee_cache 6379

# Test database connection
docker compose exec lychee_worker nc -z lychee_db 3306
```

### Jobs Not Processing

**Symptom:** Jobs queued but not processed

**Checklist:**
1. Worker is running: `docker compose ps lychee_worker` shows "Up"
2. Queue connection matches web service: `QUEUE_CONNECTION` same in both
3. Jobs table exists (if using database driver): `php artisan queue:table && php artisan migrate`
4. Logs show "Starting queue worker": `docker compose logs lychee_worker`

### Worker Crash-Looping

**Symptom:** Worker repeatedly crashes and restarts

**Solution:** Check healthcheck threshold (default: 3 retries in 30 seconds)

```yaml
healthcheck:
  test: ["CMD-SHELL", "pgrep -f 'queue:work' || exit 1"]
  interval: 30s
  retries: 3
```

Increase `retries` or `interval` if workers need more startup time.

### Memory Leaks

**Symptom:** Worker memory usage grows over time

**Solution:** Reduce `WORKER_MAX_TIME` to restart workers more frequently:

```bash
WORKER_MAX_TIME=1800  # Restart every 30 minutes
```

Or allocate more memory in docker compose:

```yaml
deploy:
  resources:
    limits:
      memory: 4G  # Increase from default 2G
```

## Production Best Practices

### Security

1. **Use secrets for credentials**: Store `DB_PASSWORD`, `REDIS_PASSWORD` in `.env` or Docker secrets
2. **Restrict worker capabilities**: Workers don't need network binding, reduce attack surface:

```yaml
cap_drop:
  - ALL
cap_add:
  - CHOWN
  - SETGID
  - SETUID
  - DAC_OVERRIDE
```

3. **Read-only volumes**: Mount `.env` as read-only:

```yaml
volumes:
  - .env:/app/.env:ro
```

### Resource Limits

Tune worker resource limits based on workload:

```yaml
deploy:
  resources:
    limits:
      cpus: '2'
      memory: 2G
    reservations:
      cpus: '0.5'
      memory: 512M
```

**Guidance:**
- Photo processing jobs: Higher memory (2-4GB per worker)
- Notification jobs: Lower memory (512MB-1GB per worker)
- CPU: 1-2 cores per worker typically sufficient

### Scaling Strategy

1. **Start with 1 worker**, monitor queue depth
2. **Scale up** if queue depth consistently > 100 jobs:

```bash
docker compose up -d --scale lychee_worker=2
```

3. **Monitor metrics**: Use Prometheus/Grafana to track queue depth, job processing time
4. **Scale down** if queue depth stays near 0

## Advanced: Priority Queues

Dispatch jobs to specific queues:

```php
// High-priority (user-initiated)
ExtractColoursJob::dispatch($photoId)->onQueue('high');

// Default priority
ProcessImageJob::dispatch($photoId);  // Defaults to 'default' queue

// Low-priority (cleanup)
PruneOldLogsJob::dispatch()->onQueue('low');
```

Configure worker to process high-priority first:

```bash
QUEUE_NAMES=high,default,low
```

Worker processes all `high` jobs before moving to `default`, then `low`.

## References

- [Feature 002 Specification](../4-architecture/features/002-worker-mode/spec.md)
- [Feature 002 Implementation Plan](../4-architecture/features/002-worker-mode/plan.md)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Docker Compose Scaling](https://docs.docker.com/compose/reference/up/)
