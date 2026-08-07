# Feature Plan 053 – Memory Profiler

_Linked specification:_ `docs/specs/4-architecture/features/053-memory-profiler/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-07-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant.

> **Engine history.** This plan originally targeted `memprof`, then pivoted to `spx` after `memprof` was confirmed impossible to bundle (ZTS incompatibility with the official Docker image's PHP build). The increment map below reflects what was actually built (the `spx`-based design); see [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md) and [open-questions.md](../../open-questions.md) (Q-053-01, Q-053-02, Q-053-05..08) for the full trail, including the abandoned `memprof` work.

## Vision & Success Criteria

An operator can set `MEMORY_PROFILER_ENABLED=true` and `MEMORY_PROFILER_SPX_KEY=<secret>`, restart Lychee, and immediately start seeing per-request memory traces accumulate under `storage/profiling`. As the site owner (matching `config('owner_id')`), they can browse those traces at `/admin/profiler` and open any one in SPX's own analysis screen to diagnose a memory leak or unexpectedly heavy request — without touching Vue, without a database migration, with zero measurable cost to every installation that leaves the flag off, and **correctly under Lychee's default Octane/FrankenPHP runtime** (verified empirically, not merely hoped for).

Success bars (all met):
- Middleware overhead when disabled: unmeasurable (single branch).
- No 500s: every failure mode (missing extension, disk-full, missing SPX key) degrades to a clear UI/log message instead of a crash.
- `storage/profiling` never grows unbounded.
- Per-request memory isolation holds even when the same OS thread serves multiple requests (Octane/FrankenPHP worker model) — verified via a live `frankenphp php-server` test during implementation.

## Scope Alignment

- **In scope:**
  - Global request-scoped memory-profiling middleware (`spx_profiler_start()`/`spx_profiler_stop()` manual spans), gated by `features.memory-profiler`.
  - `storage/profiling` as a local Laravel disk, shared by Lychee's own sidecar metadata and SPX's own report files.
  - Owner-only Blade admin surface: trace listing, manual + scheduled pruning, external link to SPX's own analysis screen.
  - New `owner` route middleware (reusable beyond this feature).
  - `spx` PECL extension (+ its `zlib1g-dev` build dependency) bundled in the production `Dockerfile` — confirmed to compile/load on the ZTS base image, unlike `memprof`.
  - Container-start script (`docker/scripts/06-configure-profiler.sh`) writing `spx`'s `PHP_INI_SYSTEM` settings from `MEMORY_PROFILER_*` env vars.
  - How-to guide covering the bundled extension, the env vars, and SPX's own access-control model for its analysis screen.
- **Out of scope:**
  - Bundling `memprof` — confirmed impossible (ZTS incompatibility), not revisited without an upstream fix.
  - Any Vue/Nuxt/API surface.
  - Rendering SPX's call-graph/flame-graph inside a Lychee-owned Blade page — an external link to SPX's own bundled viewer is used instead (Q-053-07).
  - Sampling / per-request opt-in triggers, aggregate reporting across multiple traces.

## Dependencies & Interfaces

- `App\Repositories\ConfigManager` (reused, read-only, for the `owner_id` check — same dependency `App\Rules\OwnerIdRule` already has).
- `App\Exceptions\UnauthorizedException`, `App\Exceptions\FeatureDisabledException` (existing exception classes, reused as-is).
- `config/features.php` + `App\Http\Middleware\FeatureEnabled` (existing pattern, reused).
- `config/filesystems.php` (new `profiling` disk entry, same shape as the existing `tmp-uploads`/`image-jobs` local disks).
- `app/Http/Kernel.php` (new global middleware entry + new `owner` alias).
- `routes/web-admin-v2.php` (new `/admin/profiler*` route group — this file is registered **before** `routes/web_v2.php` in `RouteServiceProvider::boot()`, so these explicit routes are matched ahead of the Vue SPA's `/admin` catch-all; this ordering is load-bearing and must not be disturbed).
- `Dockerfile` (new `spx` extension + `zlib1g-dev` build dependency) + `docker/scripts/06-configure-profiler.sh` (new, sourced from `entrypoint.sh`).
- External, non-Composer runtime dependency: `spx` PHP extension (bundled in the official image; manual install for bare-metal/custom images).

## Assumptions & Risks

- **Resolved decisions (all Option A unless noted):**
  - Engine: `spx`, not `memprof` (Q-053-02, forced by empirical ZTS failure) or an XHProf-family tool (Q-053-06, usage-only metrics insufficient for leak-hunting).
  - Capture model: manual `spx_profiler_start()`/`spx_profiler_stop()` spans, not SPX's ini-only always-on mode (Q-053-05), specifically for Octane/FrankenPHP correctness.
  - Viewing model: external link to SPX's own bundled analysis screen, not a Lychee-rendered SVG/JSON→pprof conversion (Q-053-07).
  - Access control for that external link: SPX's own `spx.http_key`/`spx.http_ip_whitelist`, accepted as "secure enough" despite bypassing Lychee's `owner_id` gate (Q-053-08).
  - Profiling is always-on for every request while the flag is enabled (no sampling) — Q-053-04.
  - Trace retention is count-based (`MEMORY_PROFILER_MAX_TRACES`, default 200) — Q-053-03.
- **Risks / Mitigations:**
  - **SPX's analysis screen bypasses Lychee's owner-only gate** (NFR-053-04): accepted trade-off (Q-053-08); mitigated by requiring a long random `spx.http_key` (no default shipped) and documenting the IP-whitelist option prominently in the how-to guide.
  - **CI/analysis sandbox has no `spx` extension installed**: all extension-dependent code paths are wrapped behind `function_exists()` and unit-tested via a fake/no-op double (`FakeSpxRecorder`); the *real* extension behaviour (including the Octane correctness claim, NFR-053-06) was validated manually during implementation via a live `frankenphp php-server` run, not by the automated suite.
  - **`spx`'s own report format is opaque** (JSON `full` report + `.txt.gz`): Lychee's code never parses it — it only stores the returned report key and links out, so there is no coupling to SPX's internal format beyond the key itself.

## Implementation Drift Gate

This plan reflects the shipped implementation as of 2026-07-28. Any future change to the capture model (e.g. switching back to ini-only auto-profiling, or re-attempting `memprof` if it ever gains ZTS support) must update NFR-053-06's empirical claim and re-verify via the same `frankenphp php-server` two-request test before merging.

## Increment Map

1. **I1 – Config, feature flag & `profiling` disk**
   - _Goal:_ Wire up `MEMORY_PROFILER_ENABLED`, `MEMORY_PROFILER_MAX_TRACES`, `MEMORY_PROFILER_SPX_KEY` at the config layer; add the `profiling` local disk.
   - _Steps:_ `config/features.php` entries; `.env.example` entries; `config/filesystems.php` `profiling` disk (`storage_path('profiling')`); `storage/profiling/.gitignore`.
   - _Commands:_ `php artisan test --filter=MemoryProfilerConfigTest`, `php artisan test --filter=ProfilingDiskTest`, `make phpstan`.
   - _Exit:_ Config resolves correctly; disk resolvable in tests without touching real `storage/app`.

2. **I2 – `owner` middleware**
   - _Goal:_ Reusable owner-only route guard.
   - _Steps:_ `App\Http\Middleware\OwnerOnly` (mirrors `App\Rules\OwnerIdRule`'s check), registered as the `owner` alias in `app/Http/Kernel.php`.
   - _Commands:_ `php artisan test --filter=OwnerOnlyTest`, `make phpstan`.
   - _Exit:_ Unauthenticated/non-owner/owner cases all correctly handled in isolation.

3. **I3 – `SpxRecorder` + `MemoryProfiler` middleware (core capture)**
   - _Goal:_ Implement FR-053-01/02/06.
   - _Steps:_ `App\Services\Profiling\SpxRecorder` (thin `function_exists`-guarded wrapper around `spx_profiler_start()`/`spx_profiler_stop()`); `App\Http\Middleware\MemoryProfiler` (terminable middleware: `handle()` starts the span via a request attribute for cross-instance state, `terminate()` stops it and writes the `lychee-*.json` sidecar, including the returned `spx_report_key`); registered in `app/Http/Kernel.php`'s global `$middleware`. PHPStan stub (`phpstan/stubs/spx.stub`, loaded via `bootstrapFiles` so the stub functions are genuinely callable during static analysis) since the extension isn't installed on the analysis machine.
   - _Commands:_ `php artisan test --filter=MemoryProfilerTest`, `make phpstan`.
   - _Exit:_ No-op paths (flag off, extension absent) and the happy path (sidecar written with correct metadata + report key) all covered with a fake recorder; dump failures logged without throwing.

4. **I4 – `ProfilerController` + routes + Blade listing page**
   - _Goal:_ FR-053-03/04/05.
   - _Steps:_ `Route::prefix('admin')->middleware(['login_required:always', 'feature:memory-profiler', 'owner'])` group in `routes/web-admin-v2.php` (`GET profiler`, `POST profiler/prune`); `ProfilerController::index()` lists `lychee-*.json` sidecars and builds the SPX analysis-screen URL per row when a key is available; `resources/views/admin/profiler/index.blade.php`.
   - _Commands:_ `php artisan test --filter=ProfilerControllerTest`, `make phpstan`.
   - _Exit:_ Empty/populated listing, auth/feature-flag gating, and SPX-link presence/absence all covered.

5. **I5 – Pruning**
   - _Goal:_ FR-053-07.
   - _Steps:_ `App\Services\Profiling\TracePruner` (keeps newest `MEMORY_PROFILER_MAX_TRACES`, deletes each pruned trace's sidecar *and* its SPX report pair together); `App\Console\Commands\Profiling\PruneTraces` (`lychee:profiler:prune`); scheduled daily in `app/Console/Kernel.php`; `ProfilerController::prune()` reuses the same service.
   - _Commands:_ `php artisan test --filter=TracePrunerTest`, `php artisan test --filter=PruneTracesTest`, `make phpstan`.
   - _Exit:_ Oldest traces pruned beyond the cap; no orphaned SPX report files.

6. **I6 – Dockerfile + container-start ini configuration**
   - _Goal:_ Bundle `spx` and wire its `PHP_INI_SYSTEM` settings from env vars, since they can't be toggled from Laravel at request time.
   - _Steps:_ Add `zlib1g-dev` (build dep) to the existing `apt-get install` list and `spx` to the existing `install-php-extensions` invocation in `Dockerfile`. New `docker/scripts/06-configure-profiler.sh`, sourced from `entrypoint.sh`, writes `spx.data_dir`, `spx.http_profiling_enabled`, `spx.http_profiling_auto_start=0`, `spx.http_profiling_metrics`, and (when `MEMORY_PROFILER_SPX_KEY` is set) `spx.http_enabled`/`spx.http_key`/`spx.http_ip_whitelist` to a conf.d ini file, from the `MEMORY_PROFILER_*` env vars, at every container start.
   - _Commands:_ `docker build .`; `docker run --rm --entrypoint sh <image> -c "php -m | grep spx"`; `docker run ... -c "/usr/local/bin/06-configure-profiler.sh && cat .../zz-memory-profiler.ini && php -i | grep spx"`.
   - _Exit:_ Extension loads; ini settings correctly reflect env vars in both the enabled and disabled cases (both verified empirically).

7. **I7 – Octane/FrankenPHP correctness verification (NFR-053-06)**
   - _Goal:_ Confirm manual start/stop spans correctly isolate memory per request even when the same worker thread serves multiple requests — the core risk this design exists to address.
   - _Steps:_ Ran the built image via `frankenphp php-server` (FrankenPHP's own HTTP server) with a minimal script that allocates a request-sized buffer and calls `spx_profiler_start()`/`stop()`; sent two consecutive requests (1MB and 9MB allocations) and compared the resulting SPX reports.
   - _Commands:_ `docker run ... frankenphp php-server ...`; `curl` x2; inspect the two `spx-full-*.json` report files' `process_pid`/`process_tid`/`peak_memory_usage` fields.
   - _Exit:_ Both requests reported the **same** `process_pid`/`process_tid` (proving they shared a worker thread) but **independently correct** `peak_memory_usage` (1.4MB vs 9.4MB, not cumulative) — confirming no cross-request contamination. Documented in ADR-0008 and the how-to guide.

8. **I8 – Documentation**
   - _Goal:_ Satisfy the Documentation Deliverables section of the spec.
   - _Steps:_ `docs/specs/2-how-to/enable-memory-profiler.md` (rewritten for `spx`); `roadmap.md`; `knowledge-map.md`; ADR-0008 (amended with the full `memprof`→`spx` history and the I7 verification).
   - _Commands:_ none (docs only).
   - _Exit:_ All Documentation Deliverables checked off; no stale `memprof`/pprof references remain in shipped docs.

9. **I9 – Quality gate**
   - _Goal:_ Final sign-off.
   - _Steps:_ Full `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Commands:_ same.
   - _Exit:_ Green quality gate.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-053-01 | I3 | Flag off → no-op. |
| S-053-02 | I3 | Extension absent → no-op. |
| S-053-03 | I3 | Normal capture path (sidecar + SPX report pair). |
| S-053-04 | I3 | Sidecar write failure logged, response unaffected. |
| S-053-05 | I4 | Empty listing. |
| S-053-06 | I4 | Populated listing. |
| S-053-07 | I4 | Working SPX link. |
| S-053-08 | I4 | No SPX link available. |
| S-053-09 | I4 | Unauthenticated → redirect. |
| S-053-10 | I4 | Non-owner → 403. |
| S-053-11 | I5 | Pruning caps count, no orphaned SPX files. |
| S-053-12 | I7 | Octane/FrankenPHP per-request isolation (manual verification). |

## Analysis Gate

Not formally re-run as a separate gate — this plan was implemented directly following the pivot from `memprof`, with each increment's tests passing before moving to the next (per the Specification Pipeline's spirit, if not its literal pre-implementation gate, since the pivot happened mid-implementation in response to empirical findings rather than at plan-drafting time).

## Exit Criteria

- All tasks in `tasks.md` checked `[x]`.
- `php artisan test`, `make phpstan` (0 errors), `vendor/bin/php-cs-fixer fix` all green.
- `docs/specs/2-how-to/enable-memory-profiler.md` published, reflecting the final `spx`-based design.
- `roadmap.md` and `knowledge-map.md` updated.
- `Dockerfile` bundles `spx` (not `memprof`); verified via `docker build` + runtime checks.
- NFR-053-06's Octane correctness claim backed by the I7 empirical test, documented in ADR-0008.

## Follow-ups / Backlog

- Per-request opt-in trigger / sampling rate, if always-on proves too noisy in practice (deferred from Q-053-04).
- Aggregate/cross-request reporting (e.g. "top 10 heaviest routes this week").
- Revisit bundling `memprof` if it ever gains ZTS support upstream ([arnaud-lb/php-memory-profiler#24](https://github.com/arnaud-lb/php-memory-profiler/issues/24)).
- Consider rendering SPX's report data inside a Lychee-owned page (rather than linking out) if the owner-only-gate gap (NFR-053-04) proves unacceptable in practice.
