# Feature 002 – Worker Mode Support

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-01-21 |
| Owners | Lychee Team |
| Linked plan | `docs/specs/4-architecture/features/002-worker-mode/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/002-worker-mode/tasks.md` |
| Roadmap entry | #002 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Enable Lychee containers to operate in two distinct modes using the same Docker image: **web mode** (default, running FrankenPHP/Octane) and **worker mode** (processing Laravel queue jobs). Mode selection is controlled via an environment variable at container startup. This addresses the application layer (Laravel queue processing), deployment infrastructure (Docker), and operational concerns (horizontal scaling of queue workers). The feature enables standard Laravel queue-based architectures for background processing of photo uploads, image processing, and other asynchronous tasks.

## Goals
- Support running Lychee as a dedicated queue worker using the same Docker image
- Enable horizontal scaling by running multiple worker containers independently from web containers
- Maintain backward compatibility: default behavior remains web mode (Octane start)
- Ensure workers can process all Laravel queue jobs (photo processing, notifications, etc.)
- Provide clear operational guidance for deploying and monitoring worker containers

## Non-Goals
- Custom queue implementations beyond Laravel's built-in queue system
- Real-time job monitoring UI (may be addressed in future features)
- Automatic scaling/orchestration logic (handled by container orchestrators like Kubernetes)
- Changes to existing queue job implementations (focus is on runtime mode selection)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-002-01 | Container must detect `LYCHEE_MODE` environment variable and branch execution accordingly | When `LYCHEE_MODE=worker`, container starts `php artisan queue:work` instead of Octane. When unset or `LYCHEE_MODE=web`, container starts Octane (default). | Container must validate `LYCHEE_MODE` is either `worker`, `web`, or unset. Log validation result to stdout. | If `LYCHEE_MODE` contains invalid value, log error and exit with non-zero status code. | Log startup mode selection: `Starting Lychee in [worker\|web] mode`. No PII. | Docker deployment requirements, Laravel queue system documentation |
| FR-002-02 | Worker mode must execute Laravel queue:work with configurable queue priority and max-time | Execute `php artisan queue:work --queue=$QUEUE_NAMES --tries=3 --timeout=3600 --max-time=$WORKER_MAX_TIME` in infinite loop. `QUEUE_NAMES` defaults to "default" but supports priority queues (e.g., "high,default,low"). `WORKER_MAX_TIME` defaults to 3600 seconds. If worker exits (crash, max-time reached), entrypoint automatically restarts after 5-second delay. | Worker respects `QUEUE_CONNECTION`, `QUEUE_NAMES` (comma-separated), `WORKER_MAX_TIME` from `.env`. Auto-restart loop logs exit codes and restart events. | If queue connection fails, log error (redact credentials), wait 5 seconds, retry. Container restart policy (`restart: unless-stopped`) provides outer restart layer. | Log worker lifecycle: `Starting queue worker on queues: $QUEUE_NAMES (max-time: $WORKER_MAX_TIME)`, `Queue worker exited with code N`, `Waiting 5 seconds before restart`. | Q-002-01 (queue priority), Q-002-02 (max-time configurability), Laravel queue:work documentation |
| FR-002-03 | Web mode must remain default behavior when `LYCHEE_MODE` is unset | Container starts Octane with FrankenPHP exactly as before this feature. No behavioral changes. | Verify entrypoint script defaults to web mode when `LYCHEE_MODE` is not present. Test with existing docker compose configurations. | N/A (default path) | Existing Octane startup logs unchanged. | Backward compatibility requirement |
| FR-002-04 | Both modes must share identical Laravel environment setup | Database migrations, config caching, and permission checks run identically in both modes before mode-specific command execution. | Entrypoint script must complete common setup (migrations, config:cache, permissions-check.sh) before branching to mode-specific commands. | If common setup fails, both modes must exit with error (existing behavior). | Existing migration/cache logs apply to both modes. | Shared Laravel application state requirement |
| FR-002-05 | Worker healthcheck must track restart failures and trigger container restart after threshold | Healthcheck writes restart count to `/tmp/worker-restarts` with timestamp. If >10 restarts occur within 5 minutes, healthcheck exits 1 to trigger container restart. Reset counter after 5 minutes of stable operation. | Healthcheck script: `pgrep -f 'queue:work' && check_restart_count || exit 1`. Restart count increments on each worker restart detected in logs. | If worker crash-loops (>10 restarts in 5min), container becomes unhealthy, orchestrator restarts container, logs cleared for fresh debugging. | Log healthcheck failures: `Worker crash-looping detected: N restarts in 5 minutes, failing healthcheck`. | Q-002-04 (healthcheck failure behavior Option B) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-002-01 | Worker containers must gracefully handle shutdown signals | Queue workers need to finish in-flight jobs during rolling updates to prevent data corruption | Worker must trap SIGTERM and allow current job to complete (up to timeout) before exiting | Docker signal handling, Laravel queue:work graceful shutdown | Container orchestration best practices |
| NFR-002-02 | Entrypoint script must remain shell-portable (POSIX sh) | Minimal base image (Alpine) uses busybox sh, not bash | Script must use only POSIX-compliant syntax (no bashisms) | Existing entrypoint.sh standards | Current Dockerfile uses Alpine base image |
| NFR-002-03 | Documentation must include docker compose examples for multi-container deployment | Operators need clear guidance on deploying separate web and worker services | Provide example docker compose.yaml showing web + worker services with shared configuration | None | Operational clarity |
| NFR-002-04 | Worker mode must log to stdout/stderr for container log aggregation | Container orchestrators collect logs from stdout/stderr | All worker output (job processing, errors) must go to stdout/stderr, not files | Laravel logging configuration | Twelve-factor app logging principles |
| NFR-002-05 | Queue jobs must support deduplication for concurrent mutations | Album stats recomputation jobs (Feature 003) must not queue duplicate jobs when multiple concurrent photo uploads occur | Jobs use Laravel's WithoutOverlapping middleware with resource ID as lock key | Redis for lock storage (if CACHE_STORE=redis), otherwise database | Q-002-03 (job deduplication Option A) |

## UI / Interaction Mock-ups
Not applicable – this feature has no user-facing UI changes. Configuration is purely operational (environment variables, container startup).

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-002-01 | Start container with `LYCHEE_MODE=worker`: Entrypoint completes common setup, logs "Starting Lychee in worker mode", executes `php artisan queue:work`, processes jobs continuously |
| S-002-02 | Start container with `LYCHEE_MODE=web`: Entrypoint completes common setup, logs "Starting Lychee in web mode", executes `php artisan octane:start` (existing behavior) |
| S-002-03 | Start container with no `LYCHEE_MODE` set: Defaults to web mode (backward compatible) |
| S-002-04 | Start container with `LYCHEE_MODE=invalid`: Entrypoint logs error "Invalid LYCHEE_MODE: invalid. Must be 'web' or 'worker'.", exits with status 1 |
| S-002-05 | Worker receives SIGTERM during job processing: Worker completes current job (if within timeout), then exits cleanly |
| S-002-06 | Worker fails to connect to queue (Redis/database down): Logs connection error, exits with non-zero status for restart |
| S-002-07 | Multi-container deployment (docker compose): One service runs web mode, another runs worker mode, both share database and storage volumes |

## Test Strategy
- **Infrastructure:** Shell script tests for entrypoint.sh mode detection logic (validate branching, error handling)
- **Integration:** Docker container tests for each scenario (S-002-01 through S-002-06)
  - Build image, run with various `LYCHEE_MODE` values, assert correct process starts
  - Verify signal handling by sending SIGTERM to worker container
- **Application:** Existing queue job tests remain unchanged (jobs must work in both modes)
- **Docs:** Validate docker compose examples start successfully

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-002-01 | LYCHEE_MODE environment variable: optional, values: "web" (default), "worker", or unset | Docker entrypoint, infrastructure |
| DO-002-02 | QUEUE_NAMES environment variable: optional, comma-separated queue names for priority processing (e.g., "high,default,low"), default: "default" | Docker entrypoint, queue worker |
| DO-002-03 | WORKER_MAX_TIME environment variable: optional, worker restart interval in seconds for memory leak mitigation, default: 3600 | Docker entrypoint, queue worker |

### API Routes / Services
Not applicable – no API changes.

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-002-01 | `php artisan queue:work --queue=$QUEUE_NAMES --tries=3 --timeout=3600 --max-time=$WORKER_MAX_TIME` | Executed in worker mode. Processes jobs from configured queue(s) with priority ordering. Auto-restarts after max-time or on exit. |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-002-01 | Container startup mode log | `mode` (web\|worker), timestamp. No redaction needed (public info). |
| TE-002-02 | Queue job processing log | `job_class` (class name), `attempt` (1-3), `queue` (queue name). No sensitive data. |
| TE-002-03 | Queue connection failure log | `connection_type` (redis\|database), `error_message` (sanitize credentials). |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-002-01 | `docker compose.yaml` (lychee_worker service) | Worker service configuration extending existing compose file |

### UI States
Not applicable – no UI changes.

## Telemetry & Observability
- **Startup mode detection:** Log line distinguishing web vs worker mode for operational visibility
- **Queue job lifecycle:** Leverage Laravel's existing queue logging (job started, completed, failed)
- **Error conditions:** Log queue connection failures, invalid mode values with clear error messages
- **Graceful shutdown:** Log when worker receives SIGTERM and begins shutdown sequence

All logs output to stdout/stderr for aggregation by container orchestrators.

## Documentation Deliverables
1. Update [knowledge-map.md](../../knowledge-map.md) with worker mode architecture entry
2. Create how-to guide: `docs/specs/2-how-to/deploy-worker-mode.md` covering:
   - Environment variable configuration (`LYCHEE_MODE`, `QUEUE_NAMES`, `WORKER_MAX_TIME`)
   - Queue priority setup (high, default, low queues)
   - Docker Compose multi-container setup
   - Queue connection configuration (Redis vs database)
   - Monitoring worker health and restart tracking
   - Job deduplication configuration (WithoutOverlapping middleware)
3. Update [roadmap.md](../../roadmap.md) with Feature 002 entry
4. Update Dockerfile comments to document `LYCHEE_MODE` behavior

## Fixtures & Sample Data
- `docker compose.yaml` (lychee_worker service): Worker service extending existing compose configuration
- Shell script test fixtures for entrypoint.sh validation
- Auto-restart loop test: shell script to verify worker restarts on process exit

## Spec DSL

```yaml
domain_objects:
  - id: DO-002-01
    name: LYCHEE_MODE
    type: environment_variable
    values:
      - web
      - worker
      - null
    default: web
  - id: DO-002-02
    name: QUEUE_NAMES
    type: environment_variable
    default: "default"
    example: "high,default,low"
    description: Comma-separated queue names for priority processing
  - id: DO-002-03
    name: WORKER_MAX_TIME
    type: environment_variable
    default: 3600
    unit: seconds
    description: Worker restart interval for memory leak mitigation

cli_commands:
  - id: CLI-002-01
    command: php artisan queue:work --queue=$QUEUE_NAMES --tries=3 --timeout=3600 --max-time=$WORKER_MAX_TIME
    context: worker_mode

telemetry_events:
  - id: TE-002-01
    event: container.startup.mode
    fields:
      - mode: string
      - timestamp: datetime
  - id: TE-002-02
    event: queue.job.processing
    fields:
      - job_class: string
      - attempt: integer
      - queue: string
  - id: TE-002-03
    event: queue.connection.failure
    fields:
      - connection_type: string
      - error_message: string
    redaction:
      - error_message: sanitize_credentials

fixtures:
  - id: FX-002-01
    path: docker compose.yaml
    service: lychee_worker
    description: Worker service added to existing compose file
```

## Appendix

### Current Dockerfile CMD
```dockerfile
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
```

This will be replaced with logic in entrypoint.sh to conditionally execute either:
- **Web mode:** `php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000`
- **Worker mode:** `php artisan queue:work --queue=$QUEUE_NAMES --tries=3 --timeout=3600 --max-time=$WORKER_MAX_TIME`

### Queue Connection Configuration
Worker mode respects standard Laravel queue configuration in `.env`:
- `QUEUE_CONNECTION`: redis, database, sync, etc.
- `QUEUE_NAMES`: comma-separated queue names for priority processing (default: "default")
  - Example: `QUEUE_NAMES=high,default,low` processes high-priority jobs first
- `WORKER_MAX_TIME`: seconds before worker gracefully restarts (default: 3600)
  - Mitigates memory leaks by periodically restarting the worker process
  - Auto-restart loop ensures continuous operation
- Redis connection settings (`REDIS_HOST`, `REDIS_PASSWORD`, etc.) if using Redis queue driver

No Lychee-specific queue configuration is introduced; workers use Laravel's existing infrastructure with standard queue:work options.
