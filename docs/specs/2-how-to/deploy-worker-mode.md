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

### CACHE_STORE (Critical for Worker)

The web and the worker containers must share their cache:

```bash
CACHE_STORE=database  # Cache in the Lychee database (no extra service)
CACHE_STORE=redis     # Cache in Redis (configure REDIS_HOST/REDIS_PASSWORD/REDIS_PORT)
CACHE_STORE=file      # One cache per container: NOT suitable for worker mode
```

Set it in `.env` so both containers read it. `CACHE_DRIVER` is read when `CACHE_STORE` is unset. With `file`, the caches evicted by jobs run in the worker stay stale in the web container; the Diagnostics page warns about it. The unique locks of the recompute jobs live in the database on any asynchronous queue, whatever the cache driver.

### QUEUE_NAMES (Optional)

Comma-separated queue names, processed in order (a queue is only read once the previous ones are empty):

```bash
QUEUE_NAMES=high,default,low  # Default: high, then uploads and imports, then recomputations
```

No Lychee job is pushed on `high` yet: the worker reads it first, so a job moved there later runs before everything else. Lychee pushes its recompute jobs (album statistics, covers, sizes, buckets) on the `low` queue, so they run after the waiting uploads and imports. **Keep `low` in the list**: a worker which does not process it never updates album statistics, covers, sizes or buckets.

Set `QUEUE_NAMES` in `.env` (loaded by both the web and the worker container) rather than on the worker service alone: the Diagnostics page runs in the web container and warns when the list misses `low`.

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
      QUEUE_NAMES: "${QUEUE_NAMES:-high,default,low}"
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

The recompute jobs (`RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob`, `RecomputeAlbumUserThumbsJob` and the four bucket jobs) implement `ShouldBeUniqueUntilProcessing`: a dispatch made while a copy for the same album or photo waits in the queue is dropped, so a batch of uploads ends with one recompute per album. The unique lock is taken on dispatch and released when a worker starts the job; it expires after one hour.

```php
use App\Jobs\Traits\HasSharedUniqueLockTrait;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class RecomputeAlbumStatsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use HasSharedUniqueLockTrait; // lock in the `database` cache store on asynchronous queues

    public int $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return $this->album_id . ':' . ($this->propagate_to_parent ? 'propagate' : 'local');
    }
}
```

**Requirements:**
- The `cache_locks` table (created by the migrations): on an asynchronous queue the web and worker containers share these locks through the database, whatever `CACHE_STORE` is.

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

The worker reads `high` first; no Lychee job is pushed on it yet. Every job runs on the connection's default queue (`default`), except the recompute jobs (`RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob`, `RecomputeAlbumUserThumbsJob` and the four bucket jobs), which declare `#[Queue(JobQueue::LOW)]`. A recompute job for a given album is queued once while it waits (`ShouldBeUniqueUntilProcessing`): an upload of many photos ends with one recompute per album, after the uploads.

Outside the LycheeOrg Docker images (bare metal, linuxserver.io, custom images), start the worker with the three queues:

```bash
php artisan queue:work --queue=high,default,low
```

Use the connection's default queue name instead of `default` if you changed it (`REDIS_QUEUE`, `SQS_QUEUE`). The Diagnostics page reminds you of this command when the queue connection is not `sync` outside a LycheeOrg image, and warns inside a LycheeOrg image when `QUEUE_NAMES` is set without `low`.

## References

- [Feature 002 Specification](../4-architecture/features/002-worker-mode/spec.md)
- [Feature 002 Implementation Plan](../4-architecture/features/002-worker-mode/plan.md)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Docker Compose Scaling](https://docs.docker.com/compose/reference/up/)
