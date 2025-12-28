# Feature 002 Tasks – Worker Mode Support

_Status: Draft_
_Last updated: 2025-12-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1: Entrypoint Script Mode Detection with Auto-Restart

- [ ] T-002-01 – Create shell script unit test fixture for mode detection (FR-002-01, S-002-01, S-002-02, S-002-03, S-002-04).
  _Intent:_ Test entrypoint.sh mode detection logic before implementation. Validates branching for web/worker/invalid modes.
  _Verification commands:_
  - `./tests/docker/test-entrypoint-mode-detection.sh`
  - All test cases pass (web mode, worker mode, unset mode, invalid mode)
  _Notes:_ Create mock environment variables, test each mode branch, verify error exit codes.

- [ ] T-002-02 – Implement LYCHEE_MODE detection in entrypoint.sh with auto-restart loop (FR-002-01, FR-002-02, NFR-002-02).
  _Intent:_ Add mode detection case statement and worker auto-restart infinite loop to entrypoint.sh after common setup.
  _Verification commands:_
  - `shellcheck docker/scripts/entrypoint.sh` (zero warnings)
  - `./tests/docker/test-entrypoint-mode-detection.sh` (all tests pass)
  _Notes:_ Insert after line 78 (before `exec "$@"`), use POSIX sh syntax only. Worker loop includes --queue=$QUEUE_NAMES, --max-time=$WORKER_MAX_TIME.

- [ ] T-002-03 – Manual test: Verify web mode starts Octane (S-002-02, S-002-03).
  _Intent:_ Build Docker image and confirm web mode executes Octane correctly.
  _Verification commands:_
  - `docker build -t lychee-test .`
  - `docker run -e LYCHEE_MODE=web lychee-test`
  - Logs show "Starting Lychee in web mode"
  - Octane process starts successfully
  _Notes:_ Default behavior (no LYCHEE_MODE set) should also start web mode.

- [ ] T-002-04 – Manual test: Verify worker mode starts queue:work with auto-restart (S-002-01, FR-002-02).
  _Intent:_ Confirm worker mode executes queue:work in infinite loop and restarts on exit.
  _Verification commands:_
  - `docker run -e LYCHEE_MODE=worker -e DB_CONNECTION=sqlite lychee-test &`
  - Logs show "Starting Lychee in worker mode" and "Auto-restart enabled"
  - `docker exec <container> pkill -f queue:work`
  - Logs show multiple "Starting queue worker" entries (restart after kill)
  _Notes:_ Worker should restart after 5-second delay. Verify --max-time=3600 parameter present.

- [ ] T-002-05 – Manual test: Verify invalid mode exits with error (S-002-04).
  _Intent:_ Ensure invalid LYCHEE_MODE values are rejected with clear error message.
  _Verification commands:_
  - `docker run -e LYCHEE_MODE=invalid lychee-test`
  - Container exits with status 1
  - Logs show "ERROR: Invalid LYCHEE_MODE: invalid. Must be 'web' or 'worker'."
  _Notes:_ Container should not start any service, immediate exit.

### Increment I2: Queue Connection Validation

- [ ] T-002-06 – Add queue connection pre-flight check to entrypoint.sh (FR-002-02, S-002-06).
  _Intent:_ Validate queue connection is reachable before starting worker, exit gracefully if unreachable.
  _Verification commands:_
  - `shellcheck docker/scripts/entrypoint.sh`
  - Manual test: `docker run -e LYCHEE_MODE=worker -e QUEUE_CONNECTION=redis -e REDIS_HOST=invalid lychee-test`
  - Logs show connection error with sanitized credentials
  - Container exits with non-zero status
  _Notes:_ Check QUEUE_CONNECTION type (redis/database), attempt connection, log error with credential redaction (TE-002-03).

- [ ] T-002-07 – Log warning for QUEUE_CONNECTION=sync in worker mode.
  _Intent:_ Operator guidance: sync driver processes jobs synchronously (defeats worker purpose).
  _Verification commands:_
  - `docker run -e LYCHEE_MODE=worker -e QUEUE_CONNECTION=sync lychee-test`
  - Logs show warning: "WARNING: QUEUE_CONNECTION=sync in worker mode. Jobs will run synchronously. Use redis or database for async processing."
  _Notes:_ Worker still starts, but logs prominent warning for operator visibility.

### Increment I3: Docker Compose Worker Service Extension

- [ ] T-002-08 – Extend docker-compose.yaml with lychee_worker service (FR-002-04, NFR-002-03, S-002-07).
  _Intent:_ Add worker service configuration to existing docker-compose.yaml with shared volumes and environment.
  _Verification commands:_
  - `docker-compose config` (validate syntax)
  - `docker-compose up -d lychee_worker`
  - `docker-compose ps` (lychee_worker shows "Up")
  _Notes:_ Include security hardening (cap_drop/cap_add), resource limits, healthcheck, LYCHEE_MODE=worker env var. Add inline comments explaining configuration.

- [ ] T-002-09 – Test multi-container deployment: web + worker in parallel (S-002-07).
  _Intent:_ Verify web and worker services run simultaneously sharing database and storage.
  _Verification commands:_
  - `docker-compose up -d` (starts both lychee_api and lychee_worker)
  - `docker-compose logs lychee_api | grep "Starting Lychee in web mode"`
  - `docker-compose logs lychee_worker | grep "Starting Lychee in worker mode"`
  - Both services show healthy status
  _Notes:_ Shared volumes (./lychee/uploads, ./lychee/storage/app) must be accessible from both containers.

- [ ] T-002-10 – Test job dispatch from web to worker processing.
  _Intent:_ End-to-end validation: dispatch job via web service, verify worker processes it.
  _Verification commands:_
  - `docker-compose exec lychee_api php artisan tinker`
  - `>>> dispatch(new \App\Jobs\ExtractColoursJob(1));`
  - `docker-compose logs lychee_worker | grep ExtractColoursJob`
  - Worker logs show job processing (started, completed)
  _Notes:_ Use existing Lychee queue job (photo processing). If no jobs exist yet, create dummy test job.

- [ ] T-002-11 – Test worker auto-restart at container level.
  _Intent:_ Verify dual-layer restart: internal loop + Docker restart policy.
  _Verification commands:_
  - `docker-compose exec lychee_worker pkill -f queue:work`
  - Wait 10 seconds
  - `docker-compose ps lychee_worker` (status shows "Up", not "Restarting")
  - `docker-compose logs lychee_worker | grep "Starting queue worker"` (multiple entries)
  _Notes:_ Internal loop should restart process within container. Container itself should remain running (restart: unless-stopped).

### Increment I4: Queue Priority Configuration (QUEUE_NAMES)

- [ ] T-002-12 – Update entrypoint.sh to use QUEUE_NAMES environment variable (FR-002-02, DO-002-02).
  _Intent:_ Replace hardcoded --queue=default with configurable --queue=$QUEUE_NAMES for priority processing.
  _Verification commands:_
  - `shellcheck docker/scripts/entrypoint.sh`
  - `grep 'QUEUE_NAMES' docker/scripts/entrypoint.sh` (variable used in queue:work command)
  _Notes:_ Default to "default" if QUEUE_NAMES unset: `QUEUE_NAMES=${QUEUE_NAMES:-default}`.

- [ ] T-002-13 – Test priority queue processing: high, default, low.
  _Intent:_ Verify worker processes high-priority jobs before default and low queues.
  _Verification commands:_
  - Set `QUEUE_NAMES=high,default,low` in worker service
  - Dispatch jobs to different queues: `dispatch(new TestJob())->onQueue('high')`
  - `docker-compose logs lychee_worker | grep "Processing"`
  - High-priority jobs processed first
  _Notes:_ Create test jobs for each queue. Verify order: high jobs complete before default/low jobs start.

- [ ] T-002-14 – Document QUEUE_NAMES configuration in docker-compose.yaml comments.
  _Intent:_ Operator guidance for queue priority setup.
  _Verification commands:_
  - Review docker-compose.yaml worker service environment section
  - Comment explains QUEUE_NAMES usage with example: "high,default,low"
  _Notes:_ Add inline comment above QUEUE_NAMES env var in lychee_worker service.

### Increment I5: Configurable Max-Time (WORKER_MAX_TIME)

- [ ] T-002-15 – Update entrypoint.sh to use WORKER_MAX_TIME environment variable (FR-002-02, DO-002-03).
  _Intent:_ Replace hardcoded --max-time=3600 with configurable --max-time=$WORKER_MAX_TIME.
  _Verification commands:_
  - `shellcheck docker/scripts/entrypoint.sh`
  - `grep 'WORKER_MAX_TIME' docker/scripts/entrypoint.sh` (variable used in queue:work command)
  _Notes:_ Default to 3600 if unset: `WORKER_MAX_TIME=${WORKER_MAX_TIME:-3600}`.

- [ ] T-002-16 – Test worker restarts after max-time expires.
  _Intent:_ Verify worker gracefully exits and restarts after WORKER_MAX_TIME seconds.
  _Verification commands:_
  - Set `WORKER_MAX_TIME=60` in worker service (1 minute for faster testing)
  - `docker-compose up -d lychee_worker`
  - Wait 70 seconds
  - `docker-compose logs lychee_worker | grep "Queue worker exited cleanly"`
  - `docker-compose logs lychee_worker | grep "Starting queue worker"` (multiple entries)
  _Notes:_ Worker should exit cleanly after 60 seconds, auto-restart loop should start new worker process.

- [ ] T-002-17 – Document WORKER_MAX_TIME configuration in docker-compose.yaml comments.
  _Intent:_ Operator guidance for memory leak mitigation tuning.
  _Verification commands:_
  - Review docker-compose.yaml worker service environment section
  - Comment explains WORKER_MAX_TIME purpose (memory leak mitigation) and example values
  _Notes:_ Add inline comment above WORKER_MAX_TIME env var: "Restart worker after N seconds (default: 3600)".

### Increment I6: Worker Healthcheck with Restart Tracking

- [ ] T-002-18 – Create healthcheck script with restart count tracking (FR-002-05).
  _Intent:_ Implement healthcheck that monitors queue:work process and tracks restart failures.
  _Verification commands:_
  - `shellcheck docker/scripts/healthcheck-worker.sh`
  - Script checks `pgrep -f 'queue:work'`
  - Script tracks restart count in /tmp/worker-restarts
  - Script exits 1 if >10 restarts in 5 minutes
  _Notes:_ Create /usr/local/bin/healthcheck-worker.sh, make executable in Dockerfile.

- [ ] T-002-19 – Update docker-compose.yaml healthcheck to use restart tracking script.
  _Intent:_ Replace simple pgrep healthcheck with restart-aware script.
  _Verification commands:_
  - `docker-compose config` (validate healthcheck syntax)
  - Healthcheck test command: `["CMD-SHELL", "/usr/local/bin/healthcheck-worker.sh"]`
  _Notes:_ Healthcheck interval: 30s, timeout: 10s, retries: 3, start_period: 60s.

- [ ] T-002-20 – Test healthcheck fails after crash-loop threshold.
  _Intent:_ Verify healthcheck marks container unhealthy after 10 restarts in 5 minutes.
  _Verification commands:_
  - Simulate crash loop: create job that always fails immediately
  - Dispatch 15+ failing jobs rapidly
  - `docker-compose ps lychee_worker` (status shows "unhealthy" after threshold)
  - `docker-compose logs lychee_worker | grep "Worker crash-looping detected"`
  _Notes:_ Healthcheck should fail, orchestrator restarts container, logs cleared for fresh debugging.

### Increment I7: Graceful Shutdown (SIGTERM Handling)

- [ ] T-002-21 – Verify Laravel queue:work handles SIGTERM gracefully (NFR-002-01, S-002-05).
  _Intent:_ Confirm queue:work completes in-flight jobs before exiting on SIGTERM.
  _Verification commands:_
  - Dispatch long-running job (30-second sleep)
  - `docker-compose stop lychee_worker` (sends SIGTERM)
  - `docker-compose logs lychee_worker` (job completes before exit)
  - No "Job failed" errors in logs
  _Notes:_ Laravel queue:work has built-in SIGTERM handling. Test confirms it works in containerized environment.

- [ ] T-002-22 – Document graceful shutdown behavior in docker-compose.yaml comments.
  _Intent:_ Operator guidance for rolling updates and maintenance.
  _Verification commands:_
  - Review docker-compose.yaml worker service comments
  - Comment explains SIGTERM handling and timeout behavior
  _Notes:_ Add note: "Worker completes in-flight jobs (up to --timeout=3600) before shutdown."

### Increment I8: Documentation

- [ ] T-002-23 – Update knowledge-map.md with worker mode architecture entry.
  _Intent:_ Document new worker mode capability in architecture knowledge map.
  _Verification commands:_
  - `grep 'Worker Mode' docs/specs/4-architecture/knowledge-map.md`
  - Entry describes LYCHEE_MODE, queue processing, auto-restart
  _Notes:_ Add entry under Infrastructure/Docker section.

- [ ] T-002-24 – Create how-to guide: deploy-worker-mode.md (NFR-002-03).
  _Intent:_ Comprehensive operator guide for deploying and configuring worker mode.
  _Verification commands:_
  - File created at `docs/specs/2-how-to/deploy-worker-mode.md`
  - Covers: environment variable configuration, docker-compose setup, queue connection, monitoring, job deduplication
  - Includes examples for QUEUE_NAMES and WORKER_MAX_TIME
  _Notes:_ Reference from main documentation. Include troubleshooting section (common errors, healthcheck failures).

- [ ] T-002-25 – Update Dockerfile comments to document LYCHEE_MODE behavior.
  _Intent:_ Inline documentation for image builders.
  _Verification commands:_
  - Review Dockerfile comments
  - Comment above CMD explains mode detection and branching
  _Notes:_ Add comment: "Container mode controlled by LYCHEE_MODE env var (web|worker). See entrypoint.sh for logic."

- [ ] T-002-26 – Update roadmap.md with Feature 002 completion status.
  _Intent:_ Mark feature as "Implemented" when all tasks complete.
  _Verification commands:_
  - `grep 'Feature 002' docs/specs/4-architecture/roadmap.md`
  - Status shows "Implemented", completion date added
  _Notes:_ Only update after ALL tasks marked [x] and verified.

## Notes / TODOs

- **Redis vs Database Queue Driver:** If QUEUE_CONNECTION=redis, ensure lychee_cache service is running and accessible. If using database driver, ensure jobs table exists (Laravel default migration).
- **Healthcheck Script Location:** Create docker/scripts/healthcheck-worker.sh and COPY to /usr/local/bin/ in Dockerfile.
- **Test Job Creation:** If no existing queue jobs in Lychee codebase, create `tests/Jobs/TestJob.php` for integration testing.
- **Scaling Workers:** Document how to run multiple worker containers: `docker-compose up -d --scale lychee_worker=3`
- **Queue Naming Convention:** Recommend queue names: `high` (urgent jobs like user-initiated photo processing), `default` (general background tasks), `low` (cleanup, maintenance).
- **Job Deduplication (NFR-002-05):** Implementation deferred to Feature 003 (Album Computed Fields) - jobs will use WithoutOverlapping middleware as needed.
- **Environment Variable Validation:** Consider adding validation script that checks for required env vars (DB_CONNECTION, QUEUE_CONNECTION) before starting services.
