# ADR-0008: Memory Profiler engine selection and Octane/FrankenPHP correctness

- **Status:** Accepted
- **Date:** 2026-07-28
- **Related features/specs:** Feature 053 (docs/specs/4-architecture/features/053-memory-profiler/spec.md)
- **Related open questions:** Q-053-01, Q-053-02, Q-053-05, Q-053-06, Q-053-07, Q-053-08

## Context

Feature 053 adds an optional, request-scoped memory profiler. The initial brief requested the `memprof` PHP extension (`arnaud-lb/php-memory-profiler`). Its public API (`memprof_enable()`, `memprof_disable()`, `memprof_dump_pprof()`) is documented upstream against a traditional per-request PHP-FPM/CLI-script lifecycle.

Lychee's **default** production runtime is Laravel Octane running on FrankenPHP — confirmed by the project's `Dockerfile` (`CMD ["php", "artisan", "octane:start", "--server=frankenphp", ...]`) and `docs/specs/2-how-to/deploy-worker-mode.md` ("Web mode (default): Run FrankenPHP/Octane web server"). Under Octane, a single PHP process/worker thread stays alive and serves many requests in sequence.

Two separate problems emerged during implementation, in this order:

1. **`memprof` cannot be installed on the official image at all.** Adding it to the `Dockerfile`'s `install-php-extensions` step failed the build with `memprof.c:49:9: error: #error "ZTS build not supported (yet)"`. Root cause, confirmed: the base image (`dunglas/frankenphp:...-trixie`) ships `PHP 8.5.8 (ZTS)` (verified via `docker run dunglas/frankenphp:1.12.4-php8.5-trixie php -v`) — FrankenPHP requires a ZTS (Zend Thread Safety) PHP build for its worker model. `memprof`'s mainline release explicitly refuses to compile against ZTS builds at all; this is a long-standing, unresolved upstream limitation ([arnaud-lb/php-memory-profiler#24](https://github.com/arnaud-lb/php-memory-profiler/issues/24), open since the extension's early history — community pull requests #7, #25, #26 attempted ZTS support over the years but none were merged). This is a **hard compile-time blocker**, not merely an unverified runtime risk.

2. **Whatever replaces it must still behave correctly under Octane's persistent-worker model.** Even with a ZTS-compatible engine, it remained an open question whether "start profiling at the beginning of a request, stop at the end" correctly isolates memory per request when the *same OS thread* serves many logical requests in sequence, versus leaking/accumulating state across requests handled by that worker.

Affected modules: application (new global `MemoryProfiler` middleware), infra/runtime (interaction with Octane's persistent-worker model, Docker image, container-start ini configuration), and the new owner-only admin UI that presents the resulting traces.

## Decision

**Engine: replace `memprof` with [`spx`](https://github.com/NoiseByNorthwest/php-spx).** Verified empirically (not merely read from documentation) that `spx` compiles and loads successfully against the exact ZTS base image (`docker build` + `php -m | grep spx`). `spx` also exposes allocation/free byte and count metrics (`zmac`/`zmab`/`zmfc`/`zmfb`) rather than only usage deltas, which is necessary for leak-hunting (usage-only tools like `tideways_xhprof`/the modern `xhprof` PECL fork were considered and rejected for this reason — see Q-053-06).

**Capture model: manual `spx_profiler_start()`/`spx_profiler_stop()` spans, not SPX's own ini-only "always profiling" mode.** SPX's own documentation ("Handle long-living / daemon processes") explicitly recommends disabling automatic start (`spx.http_profiling_auto_start=0`) and manually bounding each unit of work for exactly this class of runtime (a single process/thread serving many logical units of work) — the same problem shape as an Octane worker. Lychee's `MemoryProfiler` middleware calls `spx_profiler_start()` in `handle()` and `spx_profiler_stop()` in `terminate()`.

**This was verified empirically, not just asserted:** during implementation, the built image was run via FrankenPHP's own `frankenphp php-server` command with a minimal script, and sent two consecutive HTTP requests (allocating 1MB and 9MB respectively). Both requests were served by the **same** OS thread (`process_pid`/`process_tid` identical in both of SPX's resulting reports), yet each produced an **independently correct** `peak_memory_usage` (≈1.4MB and ≈9.4MB respectively — not cumulative, which would have shown ≈10.8MB on the second request). This directly confirms the manual-span approach isolates memory correctly per request even under Octane/FrankenPHP's worker model.

**Viewing model: link out to SPX's own bundled analysis screen, rather than rendering a call-graph inside a Lychee-owned page.** SPX ships a pre-built vanilla-JS flame-graph/timeline viewer, reached via a documented URL pattern (`/?SPX_UI_URI=/report.html&SPX_KEY=<key>&key=<report key>`) that the extension intercepts directly (see Security below). Two alternatives were considered and rejected — see Alternatives Considered.

**Access control for that link is SPX's own (`spx.http_key` + optional `spx.http_ip_whitelist`), not Lychee's `owner_id` gate — an explicit, accepted trade-off**, because SPX intercepts the matching request at PHP's earliest hook (`PHP_RINIT_FUNCTION`), before Laravel's kernel or router ever runs; Laravel middleware (including `OwnerOnly`) cannot see or gate that specific request at all. The user was informed of this precisely and accepted it (Q-053-08), on the condition that `MEMORY_PROFILER_SPX_KEY` ships with no default value (must be explicitly set to a long random secret) and that the IP-whitelist option is documented prominently.

## Consequences

### Positive
- The feature ships with real diagnostic value, correctly, under Lychee's actual default production runtime — not merely for non-Octane deployments.
- The Octane-correctness claim is backed by a concrete, reproduced empirical test, not an assumption.
- `spx`'s allocation/free metrics are a better fit for leak-hunting than the usage-only alternatives that were considered.

### Negative
- SPX's own analysis screen is reachable by anyone who knows (or brute-forces) `spx.http_key` and satisfies the IP whitelist, bypassing Lychee's own owner-only session-based gate entirely. This is a different, and arguably weaker (for a targeted attacker who already has network access), security model than the rest of Lychee's admin surface. Mitigated by requiring a long random key (no shipped default) and documenting the IP-whitelist option, but not eliminated.
- Two extension pivots occurred during implementation (`memprof` → attempted `spx` ini-only mode → `spx` manual-span mode), each discovered via empirical testing rather than upfront research; this cost implementation time but produced a materially more correct and better-verified result than shipping on the first (undocumented-behavior) assumption would have.

## Alternatives Considered

### Engine choice
- **Keep `memprof`, document as broken on the official image:** Rejected — this would ship a feature that can never work on Lychee's actual production runtime, defeating the point of "bundle it so it works out of the box."
- **`php-meminfo`:** Conceptually the best fit for leak-hunting in a persistent worker (dumps the live object graph for diffing over time), but official PHP version support tops out at 8.0; PHP 8.1+ support is only an open, unmerged upstream PR. Not viable for Lychee's PHP 8.4/8.5 requirement.
- **`tideways_xhprof` / modern `xhprof` PECL fork:** ZTS-compatible, but only expose usage deltas (`mu`/`pmu`) per function, not allocation-vs-free tracking — insufficient to distinguish "allocated and freed" from "leaked" (the user's explicit correction, Q-053-06).
- **Blackfire:** Real memory profiling, but SaaS by default (self-hosted is enterprise-only) — conflicts with Lychee's offline-only requirement.

### Capture model
- **SPX's ini-only "always profiling" mode** (`spx.http_profiling_enabled=1`, default `auto_start=1`): Simpler (zero Laravel-side code), but relies on SPX's own automatic per-request start/stop, whose correctness under Octane's worker model was exactly the open question — rejected in favour of the manual-span pattern SPX's own docs recommend for this runtime shape, which was then empirically verified.

### Viewing model
- **Write a JSON→pprof converter, reuse the original `PprofRenderer`/SVG pipeline:** Was the first choice after ruling out embedding SPX's UI (over the access-control concern, see below) — reconsidered and ultimately dropped a second time in favour of embedding, once the user judged SPX's own key+IP-whitelist protection "secure enough."
- **Write a from-scratch SVG flame-graph renderer:** Largest implementation effort (a real layout/rendering algorithm), rejected in favour of reusing SPX's own, already-built viewer.
- **Hard-disable SPX's own UI-interception mechanism entirely, keep captured data Laravel-only:** Would have required the JSON→pprof conversion (or equivalent) to have any viewer at all; not pursued once the user accepted SPX's own access-control model.

## Security / Privacy Impact

- Trace **capture** (FR-053-01) has no security impact beyond what the original design had: owner-only listing page, log-channel-only telemetry, no PII beyond `user_id`.
- Trace **viewing** (FR-053-04) has a materially different security model than the rest of Lychee's admin surface: SPX's own analysis screen is protected by `spx.http_key` (a shared secret, not a per-user session) and optionally `spx.http_ip_whitelist`, evaluated by the extension itself before Laravel ever sees the request. This is an explicit, user-accepted trade-off (Q-053-08), not an oversight — see NFR-053-04. `.env.example` ships no default `MEMORY_PROFILER_SPX_KEY`, forcing operators to set one explicitly.

## Operational Impact

- Operators must set both `MEMORY_PROFILER_ENABLED=true` and `MEMORY_PROFILER_SPX_KEY=<secret>` (a container restart is required either way, since `spx`'s ini settings are `PHP_INI_SYSTEM` and are written by `docker/scripts/06-configure-profiler.sh` at container start, not toggled by Laravel at request time).
- No monitoring/runbook changes; this is a diagnostic tool, not a production dependency — leaving it disabled (the default) has zero operational impact.

## Links

- Related spec sections: `docs/specs/4-architecture/features/053-memory-profiler/spec.md` (FR-053-01/04, NFR-053-04/05/06, Non-Goals)
- Related open questions: Q-053-01, Q-053-02, Q-053-05, Q-053-06, Q-053-07, Q-053-08 (`docs/specs/4-architecture/open-questions.md`)
- Related tasks: T-053-04..10 (`docs/specs/4-architecture/features/053-memory-profiler/tasks.md`)
- Upstream issues/docs: [arnaud-lb/php-memory-profiler#24](https://github.com/arnaud-lb/php-memory-profiler/issues/24) (ZTS build not supported); [NoiseByNorthwest/php-spx README](https://github.com/NoiseByNorthwest/php-spx#handle-long-living--daemon-processes) ("Handle long-living / daemon processes")
