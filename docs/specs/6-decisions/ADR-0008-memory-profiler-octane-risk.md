# ADR-0008: Ship the Memory Profiler under Octane/FrankenPHP with a documented risk banner, not a hard block

- **Status:** Accepted
- **Date:** 2026-07-28
- **Related features/specs:** Feature 053 (docs/specs/4-architecture/features/053-memory-profiler/spec.md)
- **Related open questions:** Q-053-01

## Context

Feature 053 adds an optional, request-scoped memory profiler backed by the `memprof` PHP extension (`arnaud-lb/php-memory-profiler`). Its public API (`memprof_enable()`, `memprof_disable()`, `memprof_dump_pprof()`) is documented upstream against a traditional per-request PHP-FPM/CLI-script lifecycle, where the PHP process is torn down (or at least fully reset) between requests.

Lychee's **default** production runtime is Laravel Octane running on FrankenPHP — confirmed by the project's `Dockerfile` (`CMD ["php", "artisan", "octane:start", "--server=frankenphp", ...]`) and `docs/specs/2-how-to/deploy-worker-mode.md` ("Web mode (default): Run FrankenPHP/Octane web server"). Under Octane, a single PHP process (worker) stays alive and serves many requests in sequence, keeping the application booted and any process-global extension state intact between them.

`memprof`'s upstream documentation and issue tracker do not address this execution model at all. It is therefore unverified whether calling `memprof_enable()` at the start of request N and dumping+`memprof_disable()`-ing at the end of request N produces a profile scoped to *only* request N's allocations, or whether it also includes residual/leaked memory from requests 1..N-1 handled by the same worker — which would make every trace potentially misleading on the platform's own default runtime.

Affected modules: application (new global `MemoryProfiler` middleware), infra/runtime (interaction with Octane's persistent-worker model), and the new owner-only admin UI that presents the resulting traces as authoritative.

## Decision

Ship Feature 053 as specified, without blocking on, or attempting to programmatically detect and disable under, Octane. Instead:

1. Add a persistent, visible warning banner on the `/admin/profiler` listing page when the app is detected running under Octane, stating that trace accuracy under this runtime is unverified.
2. Document the same caveat prominently in the new how-to guide (`docs/specs/2-how-to/enable-memory-profiler.md`).
3. Add a one-off, documented (not automated) manual verification task (T-053-24): run the same route twice inside a single Octane worker with profiling enabled, and diff the two resulting trace dumps for cross-contamination. Record the outcome in the how-to guide.
4. If that manual check later shows cross-request contamination, revisit this ADR (supersede or amend) to either hard-disable the feature under Octane or fix the middleware's interaction with the worker lifecycle (e.g. resetting/forking state per request, if `memprof` supports it).

## Consequences

### Positive
- The feature ships now, providing real diagnostic value for every non-Octane deployment (local `php artisan serve`, classic PHP-FPM, `php artisan test`, CI) immediately.
- Even under Octane, the feature may still work correctly — the risk is unverified, not confirmed broken — so blocking it outright would potentially withhold a working tool for no benefit.
- Produces concrete, recorded evidence (T-053-24) instead of leaving the question open indefinitely.

### Negative
- On Lychee's own default production runtime, traces could be misleading (over-reporting cumulative worker memory instead of per-request memory) until the manual check is run and, if needed, acted on.
- An operator who doesn't read the banner/how-to guide could draw incorrect conclusions from a trace captured under Octane.

## Alternatives Considered

- **Option B — Hard-disable under Octane:** Detect Octane at runtime (e.g. `app()->bound('octane')`) and make the middleware/admin page unconditionally unavailable in that mode. Rejected as the primary path because Octane is the *default* runtime — this would make the feature unusable out-of-the-box for most installs unless they specifically switch runtimes to debug, which is a heavy ask for a lightweight diagnostic tool, and the underlying risk is unverified rather than confirmed.
- **Option C — Spike/validate before shipping:** Run an empirical Octane test before writing any feature code, to decide between "ship" and "block" up front. Rejected as the primary path because it requires the `memprof` extension to already be installed in the environment doing the spike (it is an opt-in system extension, not present by default anywhere), which would have blocked spec/plan finalization on an environment change; the same empirical check is instead folded into the feature's own task list (T-053-24) as a documented, non-blocking follow-up.

## Security / Privacy Impact

None beyond what Feature 053's spec already covers (owner-only access, no PII beyond `user_id` in log-channel telemetry). This decision only concerns data *accuracy*, not data exposure.

## Operational Impact

- Operators running the default Octane/FrankenPHP image should treat traces as provisional until T-053-24's observation is published in the how-to guide.
- No monitoring/runbook changes; this is a diagnostic tool, not a production dependency — leaving it disabled (`MEMORY_PROFILER_ENABLED=false`, the default) has zero operational impact.

## Links

- Related spec sections: `docs/specs/4-architecture/features/053-memory-profiler/spec.md#non-functional-requirements` (NFR-053-06), `#non-goals`
- Related open question: Q-053-01 (`docs/specs/4-architecture/open-questions.md`)
- Related tasks: T-053-24 (`docs/specs/4-architecture/features/053-memory-profiler/tasks.md`)
