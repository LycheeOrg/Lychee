# Feature Plan 002 – Worker Mode Support

_Linked specification:_ `docs/specs/4-architecture/features/002-worker-mode/spec.md`
_Status:_ Draft
_Last updated:_ 2025-12-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Enable Lychee operators to horizontally scale queue workers independently from web servers, addressing background processing bottlenecks (photo uploads, image processing, notifications) without impacting request-handling capacity.

**Success Signals:**
- Container starts in worker mode when `LYCHEE_MODE=worker` is set (FR-002-01)
- Worker processes jobs from configured queue connection continuously (FR-002-02)
- Default web mode remains unchanged for backward compatibility (FR-002-03)
- Multi-container docker-compose deployment works with shared database and storage (S-002-07)
- Worker gracefully handles SIGTERM, completing in-flight jobs (NFR-002-01)

**Quality Bars:**
- All 7 scenarios (S-002-01 through S-002-07) pass integration tests
- Entrypoint script remains POSIX sh compliant (NFR-002-02)
- Documentation includes working docker-compose examples (NFR-002-03)
- Logs output to stdout/stderr for container orchestration (NFR-002-04)

## Scope Alignment

**In scope:**
- Entrypoint script modification to detect `LYCHEE_MODE` and branch execution (FR-002-01)
- Worker mode command execution: `php artisan queue:work --tries=3 --timeout=3600` (FR-002-02)
- Environment variable validation and error handling (S-002-04)
- SIGTERM signal handling for graceful shutdown (NFR-002-01)
- Docker Compose example configuration (FX-002-01)
- Documentation: how-to guide for deployment, knowledge map updates

**Out of scope:**
- Queue job implementation changes (existing jobs work as-is)
- Custom queue drivers or job monitoring UI
- Automatic scaling logic (handled by orchestrators like Kubernetes)
- Redis/database configuration (operators configure via existing `.env`)
- Performance tuning of specific jobs (separate optimization work)

## Dependencies & Interfaces

**Modules:**
- `docker/scripts/entrypoint.sh`: Shell script modification for mode detection
- `config/queue.php`: Existing Laravel queue configuration (no changes required)
- Dockerfile: CMD remains unchanged (entrypoint handles branching)
- Existing queue jobs: `app/Jobs/*.php` (ExtractColoursJob, ImportImageJob, ProcessImageJob, etc.)

**External Dependencies:**
- Laravel queue system (built-in, no additional packages)
- Queue backend: Redis (preferred) or database driver (configured via `QUEUE_CONNECTION`)
- Docker/Podman container runtime
- Shell utilities: `nc` (netcat) for database readiness checks (already in Dockerfile)

**Telemetry:**
- Stdout/stderr logs for container log aggregation
- Laravel's built-in queue logging (job processing, failures)

**Fixtures:**
- `docker-compose.yaml` (lychee_worker service): Worker service added to existing compose file

## Assumptions & Risks

**Assumptions:**
1. Operators using worker mode will configure `QUEUE_CONNECTION` to a persistent backend (Redis/database, not `sync`)
2. Queue backend (Redis/database) is available and reachable from worker containers
3. Workers share the same `.env` configuration and storage volumes as web containers
4. Container orchestrators (Docker Compose, Kubernetes) handle worker restart on failure

**Risks & Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|------------|
| **R1:** Entrypoint script shell portability issues (bashisms) | Worker mode fails on Alpine/busybox sh | Use shellcheck during development (I2), test in Docker Alpine environment before commit |
| **R2:** Invalid `LYCHEE_MODE` values cause silent failures | Container starts in wrong mode or crashes | Validate mode value explicitly, log error, exit with non-zero status (S-002-04) |
| **R3:** SIGTERM handling fails, leaving orphaned jobs | Jobs incomplete or duplicated on worker restart | Test signal handling explicitly (I4), verify Laravel's graceful shutdown works |
| **R4:** Documentation examples don't match production needs | Operators struggle with deployment | Include realistic docker-compose example with Redis, MySQL, shared volumes (I5) |
| **R5:** Queue connection configuration unclear | Workers can't connect to Redis/database | Document required `.env` variables clearly, validate connection before starting worker (FR-002-02) |

## Implementation Drift Gate

**Evidence Collection:**
- After each increment: run integration tests for affected scenarios
- Before final merge: execute full docker-compose example, verify both modes start and process work
- Shellcheck output for entrypoint.sh (zero warnings)
- Docker build logs (successful multi-stage build)

**Recording Results:**
- Update `tasks.md` with test outcomes after each increment
- Log any deviations from spec in this plan's "Follow-ups / Backlog" section
- If drift occurs (e.g., additional `.env` variables needed), update spec first, then code

**Commands to Rerun:**
```bash
# Shellcheck validation
shellcheck docker/scripts/entrypoint.sh

# Docker build test
docker build -t lychee-worker-test .

# Worker mode integration test
docker run -e LYCHEE_MODE=worker -e QUEUE_CONNECTION=database lychee-worker-test

# Multi-container deployment test
docker-compose up -d
docker-compose logs -f lychee_worker
```

## Increment Map

### **I1 – Entrypoint Script Mode Detection with Auto-Restart**
**Goal:** Add `LYCHEE_MODE` environment variable detection and worker auto-restart loop to entrypoint.sh.

**Preconditions:**
- Current [entrypoint.sh](docker/scripts/entrypoint.sh) executes common setup then `exec "$@"`
- Dockerfile CMD is `php artisan octane:start ...` (remains unchanged)

**Steps:**
1. **Test first:** Create shell script unit test fixture `tests/docker/test-entrypoint-mode-detection.sh`
   - Mock environment variables
   - Test valid modes: `LYCHEE_MODE=web`, `LYCHEE_MODE=worker`, unset
   - Test invalid mode: `LYCHEE_MODE=invalid` (must exit 1)
   - Test worker restart: simulate queue:work exit, verify loop restarts it
2. **Implement:** Modify [entrypoint.sh](docker/scripts/entrypoint.sh) after line 78 (before `exec "$@"`):
   ```sh
   # Detect LYCHEE_MODE and set command accordingly
   LYCHEE_MODE=${LYCHEE_MODE:-web}

   case "$LYCHEE_MODE" in
       web)
           echo "🌐 Starting Lychee in web mode..."
           # Use provided CMD from Dockerfile (octane:start)
           ;;
       worker)
           echo "⚙️  Starting Lychee in worker mode..."
           echo "🔄 Auto-restart enabled: worker will restart if it exits"

           # Auto-restart loop: if queue:work exits, restart it
           # This handles memory leak mitigation and crash recovery
           while true; do
               echo "🚀 Starting queue worker ($(date))"
               php artisan queue:work --tries=3 --timeout=3600 --sleep=3 --max-time=3600
               EXIT_CODE=$?

               if [ $EXIT_CODE -eq 0 ]; then
                   echo "✅ Queue worker exited cleanly (exit code 0)"
               else
                   echo "⚠️  Queue worker exited with code $EXIT_CODE"
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
   ```

   **Worker Options Explained:**
   - `--tries=3`: Retry failed jobs up to 3 times
   - `--timeout=3600`: Kill job if it runs longer than 1 hour
   - `--sleep=3`: Sleep 3 seconds when queue is empty
   - `--max-time=3600`: Restart worker after 1 hour (memory leak mitigation)

3. **Shellcheck:** Run `shellcheck docker/scripts/entrypoint.sh` (zero warnings)
4. **Manual test:** Run container with `LYCHEE_MODE=worker`, verify log output shows restart loop

**Commands:**
```bash
# Unit test
./tests/docker/test-entrypoint-mode-detection.sh

# Shellcheck
shellcheck docker/scripts/entrypoint.sh

# Manual test - web mode
docker build -t lychee-test .
docker run -e LYCHEE_MODE=web lychee-test

# Manual test - worker mode with auto-restart
docker run -e LYCHEE_MODE=worker -e DB_CONNECTION=sqlite lychee-test &
WORKER_PID=$!
sleep 10

# Kill queue:work process inside container (should auto-restart)
docker exec $(docker ps -q -f ancestor=lychee-test) pkill -f queue:work
sleep 10
docker logs $(docker ps -q -f ancestor=lychee-test) | grep "Starting queue worker"
# Should see multiple "Starting queue worker" entries

# Cleanup
docker kill $WORKER_PID
```

**Exit Criteria:**
- Shellcheck passes (NFR-002-02)
- Mode detection logic tested with valid/invalid values (S-002-01, S-002-02, S-002-03, S-002-04)
- Log messages clearly indicate selected mode (TE-002-01)
- Worker auto-restart loop functions correctly (manual kill test)
- Worker respects `--max-time=3600` and restarts after 1 hour

**Covers Scenarios:** S-002-01, S-002-02, S-002-03, S-002-04, S-002-05 (partial - auto-restart)

---

### **I2 – Queue Connection Validation**
**Goal:** Add pre-flight check to ensure queue connection is reachable before starting worker.

**Preconditions:**
- I1 completed (mode detection works)
- Laravel queue configuration exists in [config/queue.php](config/queue.php)

**Steps:**
1. **Test first:** Create PHP test `tests/Feature/Queue/QueueConnectionTest.php`
   - Test: `QUEUE_CONNECTION=redis` with Redis unavailable → command exits with error
   - Test: `QUEUE_CONNECTION=database` with database available → worker starts
   - Test: `QUEUE_CONNECTION=sync` (not recommended for worker mode but valid)
2. **Implement:** Add validation in entrypoint.sh before worker mode starts:
   ```sh
   worker)
       echo "⚙️  Starting Lychee in worker mode..."

       # Validate queue connection is configured
       QUEUE_CONN="${QUEUE_CONNECTION:-sync}"
       echo "📡 Queue connection: $QUEUE_CONN"

       if [ "$QUEUE_CONN" = "sync" ]; then
           echo "⚠️  WARNING: QUEUE_CONNECTION=sync is not recommended for worker mode."
           echo "   Consider using 'redis' or 'database' for persistent queues."
       fi

       set -- php artisan queue:work --tries=3 --timeout=3600
       ;;
   ```
3. **Note:** Full connection testing (Redis/database reachability) is handled by Laravel's queue:work command itself (it will fail fast if connection unavailable per FR-002-02)

**Commands:**
```bash
# Feature test
php artisan test --filter=QueueConnectionTest

# Manual test with Redis
docker run -e LYCHEE_MODE=worker -e QUEUE_CONNECTION=redis \
  -e REDIS_HOST=localhost lychee-test
```

**Exit Criteria:**
- Warning logged when `QUEUE_CONNECTION=sync` (operator guidance)
- Queue connection type logged for visibility (TE-002-03)
- Worker exits gracefully if connection fails (FR-002-02 failure path)

**Covers Scenarios:** S-002-06

---

### **I3 – Docker Compose Worker Service Extension**
**Goal:** Extend existing docker-compose.yaml with optional worker service.

**Preconditions:**
- I1 completed (mode detection works)
- Existing [docker-compose.yaml](docker-compose.yaml) has lychee_api service configured

**Steps:**
1. **Research:** Review existing docker-compose.yaml structure (lychee_api, lychee_db, lychee_cache)
2. **Implement:** Add worker service to existing `docker-compose.yaml`:
   ```yaml
   # Add this service to the existing docker-compose.yaml
   # after the lychee_api service definition

   lychee_worker:
     image: lychee-frankenphp:latest
     # Uncomment to build locally:
     # build:
     #   context: ./app
     #   dockerfile: Dockerfile
     #   args:
     #     NODE_ENV: "${NODE_ENV:-production}"
     container_name: lychee-worker
     restart: unless-stopped  # Auto-restart at container level

     # Security hardening (match lychee_api)
     security_opt:
       - no-new-privileges:true
       - seccomp:unconfined
     cap_drop:
       - ALL
     cap_add:
       - CHOWN
       - SETGID
       - SETUID
       - DAC_OVERRIDE

     # Resource limits (adjust based on workload)
     deploy:
       resources:
         limits:
           cpus: '2'
           memory: 2G
         reservations:
           cpus: '0.5'
           memory: 512M

     env_file:
       - path: ./.env
         required: true
     environment:
       PUID: "${PUID:-1000}"
       PGID: "${PGID:-1000}"
       LYCHEE_MODE: worker  # Worker mode

       # Application (inherit from lychee_api)
       APP_NAME: "${APP_NAME:-Lychee}"
       APP_ENV: "${APP_ENV:-production}"
       APP_DEBUG: "${APP_DEBUG:-false}"

       # Database (same as lychee_api)
       DB_CONNECTION: "${DB_CONNECTION:-mysql}"
       DB_HOST: "${DB_HOST:-lychee_db}"
       DB_PORT: "${DB_PORT:-3306}"
       DB_DATABASE: "${DB_DATABASE:-lychee}"
       DB_USERNAME: "${DB_USERNAME:-lychee}"

       # Queue configuration (CRITICAL for worker mode)
       QUEUE_CONNECTION: "${QUEUE_CONNECTION:-database}"

       # Redis (if using redis queue driver)
       REDIS_HOST: "lychee_cache"
       REDIS_PASSWORD: "null"
       REDIS_PORT: "6379"

       # Logging
       LOG_CHANNEL: "${LOG_CHANNEL:-stack}"
       LOG_STDOUT: "${LOG_STDOUT:-true}"

     # Shared volumes with lychee_api (critical for photo processing)
     volumes:
       - ./lychee/uploads:/app/public/uploads
       - ./lychee/storage/app:/app/storage/app
       - ./lychee/logs:/app/storage/logs
       - ./lychee/tmp:/app/storage/tmp
       - .env:/app/.env:ro

     depends_on:
       lychee_db:
         condition: service_healthy
       # If using redis for queue:
       lychee_cache:
         condition: service_started

     # Worker health check (verify process is running)
     healthcheck:
       test: ["CMD-SHELL", "pgrep -f 'queue:work' || exit 1"]
       interval: 30s
       timeout: 10s
       retries: 3
       start_period: 60s

     networks:
       - lychee
   ```

   **Important:** To use Redis for queue (recommended for production):
   - Set `QUEUE_CONNECTION=redis` in `.env`
   - Ensure `lychee_cache` service is running
3. **Test:** Run `docker-compose up -d lychee_worker`
4. **Verify:**
   - Web service (lychee_api) runs normally with Octane (check logs: "Starting Lychee in web mode")
   - Worker service (lychee_worker) starts queue:work (check logs: "Starting Lychee in worker mode")
   - Dispatch test job via web, verify worker processes it
   - Test auto-restart: `docker-compose exec lychee_worker pkill -f queue:work` → verify container restarts process
5. **Document:** Add inline comments explaining worker configuration in docker-compose.yaml

**Commands:**
```bash
# Start worker (lychee_api already running)
docker-compose up -d lychee_worker

# Check logs
docker-compose logs -f lychee_api   # Web mode
docker-compose logs -f lychee_worker # Worker mode

# Dispatch test job (via web service)
docker-compose exec lychee_api php artisan tinker
# >>> dispatch(new \App\Jobs\ExtractColoursJob(1));

# Verify worker processed it
docker-compose logs lychee_worker | grep ExtractColoursJob

# Test auto-restart (kill worker process inside container)
docker-compose exec lychee_worker pkill -f queue:work
sleep 5
docker-compose ps lychee_worker  # Should show "Up" (restarted)

# Scale workers (run 3 worker containers)
docker-compose up -d --scale lychee_worker=3

# Stop worker
docker-compose stop lychee_worker
```

**Exit Criteria:**
- Docker Compose stack starts successfully
- Web and worker services run in parallel
- Test job dispatched by web is processed by worker
- Shared volumes work (uploads accessible from both services)

**Covers Scenarios:** S-002-07

---

### **I4 – SIGTERM Graceful Shutdown Test**
**Goal:** Verify worker gracefully handles SIGTERM during job processing.

**Preconditions:**
- I1, I2, I3 completed
- Worker mode functional

**Steps:**
1. **Test first:** Create integration test `tests/Integration/Docker/WorkerGracefulShutdownTest.php`
   - Start worker container with long-running test job (sleep 10 seconds)
   - Send SIGTERM to container after 2 seconds
   - Verify job completes before container exits
   - Verify job not marked as failed
2. **Verify Laravel behavior:** Laravel's `queue:work` already handles SIGTERM gracefully via [Illuminate\Queue\Worker](https://github.com/laravel/framework/blob/10.x/src/Illuminate/Queue/Worker.php) (PCNTL signal handling)
3. **Document:** Add note in how-to guide about graceful shutdown (30-60 second timeout recommended for orchestrators)

**Commands:**
```bash
# Integration test
docker-compose -f docker-compose.worker-example.yaml up -d worker

# Dispatch long job
docker-compose exec web php artisan tinker
# >>> dispatch(new \App\Jobs\TestLongRunningJob(10)); // 10 second job

# Send SIGTERM after 2 seconds
sleep 2
docker-compose kill -s SIGTERM worker

# Verify job completed
docker-compose logs worker | grep "Job completed"
```

**Exit Criteria:**
- Worker completes in-flight job before exiting (NFR-002-01)
- Job not marked as failed in queue
- Container exit code is 0 (clean shutdown)

**Covers Scenarios:** S-002-05

---

### **I5 – Documentation & Knowledge Map Updates**
**Goal:** Create deployment how-to guide and update knowledge map.

**Preconditions:**
- I1-I4 completed and tested

**Steps:**
1. **Create how-to guide:** `docs/specs/2-how-to/deploy-worker-mode.md`
   - Overview: why use worker mode
   - Environment variables: `LYCHEE_MODE`, `QUEUE_CONNECTION`, Redis/database config
   - Docker Compose deployment example
   - Scaling workers: `docker-compose up -d --scale worker=3`
   - Monitoring: checking logs, job queue depth
   - Troubleshooting: common issues (connection failures, permissions)
2. **Update knowledge map:** [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md)
   - Add section under "Infrastructure Layer": Worker Mode Architecture
   - Document mode detection flow, queue processing
   - Reference how-to guide
3. **Update Dockerfile comments:** Add comment documenting `LYCHEE_MODE` behavior
   ```dockerfile
   # LYCHEE_MODE environment variable controls container mode:
   # - "web" (default): Runs FrankenPHP/Octane web server
   # - "worker": Runs Laravel queue worker
   # See docs/specs/2-how-to/deploy-worker-mode.md
   ```

**Commands:**
```bash
# Validate markdown
npm run check  # If linter exists for docs

# Manual review
cat docs/specs/2-how-to/deploy-worker-mode.md
```

**Exit Criteria:**
- How-to guide covers all operator questions (environment setup, deployment, monitoring)
- Knowledge map updated with worker mode architecture
- Dockerfile comments explain LYCHEE_MODE

**Covers Requirements:** NFR-002-03 (documentation)

---

### **I6 – Integration Tests & Regression Check**
**Goal:** Ensure all scenarios pass and no regressions introduced.

**Preconditions:**
- I1-I5 completed

**Steps:**
1. **Run full test suite:**
   ```bash
   php artisan test
   npm run check
   ```
2. **Docker integration tests:** Test each scenario manually:
   - S-002-01: Worker mode starts correctly
   - S-002-02: Web mode starts correctly
   - S-002-03: Default mode (unset) is web
   - S-002-04: Invalid mode exits with error
   - S-002-05: SIGTERM graceful shutdown
   - S-002-06: Queue connection failure handling
   - S-002-07: Multi-container deployment
3. **Performance check:** Verify no slowdown in web mode (baseline comparison)
4. **Security check:** Ensure no credentials leaked in logs

**Commands:**
```bash
# Full test suite
php artisan test
npm run check

# Docker integration (each scenario)
./tests/docker/run-all-scenarios.sh

# Performance baseline
ab -n 1000 -c 10 http://localhost:8000/
```

**Exit Criteria:**
- All tests pass (0 failures)
- All 7 scenarios verified manually
- No performance regression in web mode
- No security issues (credentials redacted in logs)

**Covers:** All scenarios S-002-01 through S-002-07

---

## Scenario Tracking

| Scenario ID | Increment / Task Reference | Notes |
|-------------|---------------------------|-------|
| S-002-01 | I1 / Mode detection with `LYCHEE_MODE=worker` | Entrypoint script branching |
| S-002-02 | I1 / Mode detection with `LYCHEE_MODE=web` | Explicit web mode |
| S-002-03 | I1 / Mode detection with unset variable | Default web mode (backward compat) |
| S-002-04 | I1 / Invalid mode validation | Error handling, exit code 1 |
| S-002-05 | I4 / SIGTERM graceful shutdown test | Integration test with long job |
| S-002-06 | I2 / Queue connection validation | Connection failure handling |
| S-002-07 | I3 / Docker Compose multi-container example | Web + worker deployment |

## Analysis Gate

**Status:** Pending (to be executed before I1 starts)

**Checklist:** Follow [docs/specs/5-operations/analysis-gate-checklist.md](../../5-operations/analysis-gate-checklist.md)

**Key Checks:**
- [ ] Spec FR/NFR IDs map to increments (verified in Scenario Tracking table above)
- [ ] All dependencies identified (shell, Docker, Laravel queue system)
- [ ] Test strategy covers all scenarios (unit, integration, manual)
- [ ] Rollback plan exists (revert entrypoint.sh, redeploy)
- [ ] Performance impact assessed (negligible - mode detection adds <10ms startup time)

**Findings:** (To be filled during execution)

## Exit Criteria

**Feature Complete Checklist:**
- [ ] Entrypoint script mode detection implemented and tested (I1)
- [ ] Queue connection validation and logging added (I2)
- [ ] Docker Compose example created and verified (I3)
- [ ] SIGTERM graceful shutdown tested (I4)
- [ ] Documentation complete: how-to guide, knowledge map, Dockerfile comments (I5)
- [ ] All integration tests pass (I6)
- [ ] Shellcheck passes with zero warnings (NFR-002-02)
- [ ] All 7 scenarios manually verified (S-002-01 through S-002-07)
- [ ] Roadmap updated to "In Progress" during implementation, "Complete" at finish
- [ ] No regressions in existing tests
- [ ] Docker image builds successfully
- [ ] Performance: mode detection overhead <10ms
- [ ] Security: no credentials in logs (TE-002-03 redaction)

**Quality Gates:**
```bash
# Code quality
shellcheck docker/scripts/entrypoint.sh
php artisan test
npm run check

# Docker build
docker build -t lychee-worker .

# Integration
docker-compose -f docker-compose.worker-example.yaml up -d
# Manual verification of web + worker services
docker-compose -f docker-compose.worker-example.yaml down -v
```

## Follow-ups / Backlog

**Deferred Optimizations:**
- [ ] **Job monitoring UI:** Dashboard showing queue depth, job status, worker health (separate feature)
- [ ] **Auto-scaling:** Kubernetes HPA configuration example (operational guide, not code)
- [ ] **Worker-specific health checks:** Endpoint to verify worker is processing jobs (may require separate feature for health check API)

**Monitoring Improvements:**
- [ ] CloudWatch/Prometheus metrics for job processing (integrate with existing observability stack)
- [ ] Alerting for queue lag exceeding threshold (operational tooling)

**Documentation Enhancements:**
- [ ] Video walkthrough of worker deployment (community contribution)
- [ ] Kubernetes deployment example (Helm chart) (separate PR)

**Potential Issues to Watch:**
- Memory leaks in long-running workers (Laravel's `queue:restart` recommendation)
- Job timeout configuration tuning for large photo processing (operator configuration, not code change)

---

**Plan Status:** Draft
**Next Steps:** Execute Analysis Gate, then proceed with I1 (Entrypoint Script Mode Detection)
