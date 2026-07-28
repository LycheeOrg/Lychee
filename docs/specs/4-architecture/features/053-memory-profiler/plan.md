# Feature Plan 053 – Memory Profiler

_Linked specification:_ `docs/specs/4-architecture/features/053-memory-profiler/spec.md`
_Status:_ Draft
_Last updated:_ 2026-07-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant. **Q-053-01 through Q-053-04 were resolved on 2026-07-28** (all Option A; Q-053-02 modified to bundle `pprof`/`google-pprof` + Graphviz into the production `Dockerfile` — see [open-questions.md](../../open-questions.md) and [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md)). This plan is unblocked for execution; run the analysis gate (below) before starting I1.

## Vision & Success Criteria

An operator can flip `MEMORY_PROFILER_ENABLED=true`, restart Lychee, and immediately start seeing per-request memory-allocation traces accumulate under `storage/profiling`. As the site owner (matching `config('owner_id')`), they can browse those traces at `/admin/profiler` and open any one as an SVG call-graph to diagnose a memory leak or unexpectedly heavy request — without touching Vue, without a database migration, and with zero measurable cost to every installation that leaves the flag off (the overwhelming majority).

Success bars:
- Middleware overhead when disabled: unmeasurable (single branch).
- No 500s: every failure mode (missing extension, missing `pprof`/Graphviz, disk-full, path traversal attempt) degrades to a clear UI/log message instead of a crash.
- `storage/profiling` never grows unbounded.

## Scope Alignment

- **In scope:**
  - Global request-scoped memory-profiling middleware (`memprof_enable()`/`memprof_disable()` + pprof dump), gated by `features.memory-profiler`.
  - `storage/profiling` as a new local Laravel disk.
  - Owner-only Blade admin surface: trace listing, SVG rendering (via external `pprof` CLI), manual + scheduled pruning.
  - New `owner` route middleware (reusable beyond this feature).
  - `google-perftools` (`google-pprof`) + `graphviz` added to the production `Dockerfile`'s `apt-get install` step (Q-053-02).
  - New how-to guide documenting the remaining manually-installed dependency (`memprof`) and the bundled ones (`pprof`/`google-pprof`, Graphviz) for non-Docker installs.
- **Out of scope (this feature):**
  - Bundling the `memprof` PECL extension itself into the default Docker image (remains manual/opt-in — see Follow-ups).
  - Any Vue/Nuxt/API surface.
  - Sampling / per-request opt-in triggers, aggregate reporting across multiple traces.
  - Formal Octane compatibility guarantee (documented risk only — see NFR-053-06, ADR-0008).

## Dependencies & Interfaces

- `App\Repositories\ConfigManager` (reused, read-only, for the `owner_id` check — same dependency `App\Rules\OwnerIdRule` already has).
- `App\Exceptions\UnauthorizedException`, `App\Exceptions\FeatureDisabledException` (existing exception classes, reused as-is).
- `config/features.php` + `App\Http\Middleware\FeatureEnabled` (existing pattern, reused).
- `config/filesystems.php` (new `profiling` disk entry, same shape as the existing `tmp-uploads`/`image-jobs` local disks).
- `app/Http/Kernel.php` (new global middleware entry + new `owner` alias).
- `routes/web-admin-v2.php` (new `/admin/profiler*` route group — this file is registered **before** `routes/web_v2.php` in `RouteServiceProvider::boot()`, so these explicit routes are matched ahead of the Vue SPA's `/admin` catch-all; this ordering is load-bearing and must not be disturbed).
- `Dockerfile` (new `google-perftools` + `graphviz` packages in the existing `apt-get install` block, ~line 77).
- External, non-Composer runtime dependencies: `memprof` PHP extension (manual/opt-in), `pprof`/`google-pprof` CLI + Graphviz `dot` (bundled in the Docker image; invoked internally by `pprof`, not called directly by our code).

## Assumptions & Risks

- **Resolved decisions (Q-053-01..04, resolved 2026-07-28 — all Option A):**
  - Profiling is always-on for every request while the flag is enabled (no sampling) — Q-053-04.
  - Trace retention is count-based (`MEMORY_PROFILER_MAX_TRACES`, default 200), not time-based — Q-053-03.
  - The feature ships without empirical Octane validation; a risk banner + how-to caveat is the mitigation for v1, recorded in ADR-0008 — Q-053-01.
  - `pprof`/`google-pprof` + Graphviz are bundled into the production `Dockerfile`; `memprof` itself remains documented/manual-install-only — Q-053-02 (modified).
- **Risks / Mitigations:**
  - **Octane process-global state contamination** (NFR-053-06, ADR-0008): mitigated by a visible admin-page warning + explicit "unverified under Octane" documentation, plus a manual cross-request diff test (T-053-24) that will surface the problem concretely if it exists, feeding a potential follow-up ADR revision.
  - **CI has no `memprof` extension installed**: all extension-dependent code paths are wrapped behind `function_exists()` and unit-tested via a fake/no-op double; the *real* extension behaviour is validated manually (documented, not automated) per T-053-24.
  - **`pprof` CLI naming varies by distro** (`pprof` vs `google-pprof`): the Debian/Ubuntu package (`google-perftools`, used in our Debian-trixie-based `Dockerfile`) installs the binary as `google-pprof`, so `MEMORY_PROFILER_PPROF_BIN` defaults to `google-pprof`; still operator-configurable for other distros/manual installs.
  - **Path traversal via `{trace}`**: mitigated by NFR-053-04's allow-list resolution, covered by a dedicated feature test (T-053-18).

## Implementation Drift Gate

Before starting I4 (middleware implementation), re-confirm the spec's FR-053-01/02/06 still match the increments described here (the resolved-answers-driven design shouldn't have drifted since 2026-07-28's resolution). Record the re-confirmation date and any resulting spec edits directly in `spec.md`'s "Last updated" + the relevant FR rows; do not silently diverge plan from spec.

## Increment Map

1. **I1 – Config & feature flag**
   - _Goal:_ Wire up `MEMORY_PROFILER_ENABLED` end-to-end at the config layer, with no behaviour yet.
   - _Preconditions:_ None (first increment; Q-053-01..04 already resolved).
   - _Steps:_ Add `'memory-profiler' => (bool) env('MEMORY_PROFILER_ENABLED', false)` to `config/features.php`; add `MEMORY_PROFILER_ENABLED`, `MEMORY_PROFILER_MAX_TRACES`, `MEMORY_PROFILER_PPROF_BIN` to `.env.example` (commented/off, matching `LOG_VIEWER_ENABLED` style); add failing config test asserting the key resolves to `false` by default.
   - _Commands:_ `php artisan test --filter=FeaturesConfigTest`, `make phpstan`.
   - _Exit:_ `config('features.memory-profiler')` resolves correctly from env in both directions; no runtime behaviour changed yet.

2. **I2 – `profiling` filesystem disk**
   - _Goal:_ Give the middleware/controller a stable, testable place to read/write trace files.
   - _Steps:_ Add `'profiling'` disk to `config/filesystems.php` (`driver: local`, `root: storage_path('profiling')`, `visibility: private`); add `storage/profiling/.gitignore` (ignore `*`, keep `.gitignore` itself) mirroring existing `storage/tmp` handling; add a feature test asserting `Storage::disk('profiling')` resolves to the expected path.
   - _Commands:_ `php artisan test --filter=ProfilingDiskTest`.
   - _Exit:_ Disk resolvable in tests without touching real `storage/app`.

3. **I3 – `owner` middleware**
   - _Goal:_ Extract the existing `OwnerIdRule` check into a reusable route middleware, independent of this feature (usable by future admin-only routes too).
   - _Steps:_ Write failing feature tests first: unauthenticated → redirect/401 per existing `login_required` behaviour when stacked; authenticated non-owner → 403 `UnauthorizedException`; authenticated owner → passes through. Implement `App\Http\Middleware\OwnerOnly`, register alias `'owner' => \App\Http\Middleware\OwnerOnly::class` in `app/Http/Kernel.php`.
   - _Commands:_ `php artisan test --filter=OwnerOnlyMiddlewareTest`, `make phpstan`.
   - _Exit:_ Middleware green in isolation; not yet attached to any route.

4. **I4 – `MemoryProfiler` middleware (core capture)**
   - _Goal:_ Implement FR-053-01/02/06 behind the flag from I1, using the disk from I2.
   - _Preconditions:_ I1–I2 done.
   - _Steps:_ Write failing tests for S-053-01/02 (flag off / extension absent → no-op, asserted via a fake `function_exists` seam or a dedicated `MemprofRecorder` interface with a null-object test double — real `memprof_*` calls are never invoked in CI). Implement `App\Http\Middleware\MemoryProfiler` implementing Laravel's terminable-middleware pattern (`handle()` + `terminate()`); extract the `memprof_*` calls behind a small `App\Services\Profiling\MemprofRecorder` class so tests can substitute a fake. Register in `app/Http/Kernel.php`'s global `$middleware` array (runs for every group: `web`, `web-admin`, `web-install`, `api` — matches "beginning of the request... end" literally). Write the JSON sidecar (DO-053-01) alongside the `.pprof` dump.
   - _Commands:_ `php artisan test --filter=MemoryProfilerMiddlewareTest`, `make phpstan`.
   - _Exit:_ S-053-01..04 covered and green; when the flag is on and a fake recorder is bound in tests, a trace pair appears on disk with correct metadata.

5. **I5 – `ProfilerController` + routes (listing)**
   - _Goal:_ FR-053-03, S-053-05/06/09/10.
   - _Steps:_ Add `Route::prefix('admin')->middleware(['login_required:always', 'feature:memory-profiler', 'owner'])->group(...)` to `routes/web-admin-v2.php` for `GET profiler` → `ProfilerController::index`. Write failing feature tests for empty/populated listing and the two auth-failure scenarios before implementing. Implement controller + `resources/views/admin/profiler/index.blade.php` (mock-up in spec.md).
   - _Commands:_ `php artisan test --filter=ProfilerControllerTest`, `make phpstan`.
   - _Exit:_ Listing page works end-to-end against fixture trace files (FX-053-01 + a generated sidecar).

6. **I6 – SVG rendering (FR-053-04, S-053-07/08/11)**
   - _Goal:_ Render a trace as SVG via `pprof`/Graphviz, safely.
   - _Steps:_ Write failing tests first for: safe-path resolution / traversal rejection (S-053-11), successful render (mocking the shell-out via a `Symfony\Process`-wrapping `PprofRenderer` service, test double returns canned SVG bytes), and missing-binary error state (double throws/reports "not found"). Implement `GET admin/profiler/{trace}/svg` route + controller method + `PprofRenderer` (shells out to `MEMORY_PROFILER_PPROF_BIN`, caches result as `{trace}.svg` next to the dump).
   - _Commands:_ `php artisan test --filter=ProfilerSvgTest`, `make phpstan`.
   - _Exit:_ S-053-07/08/11 all green; no real `pprof` binary required in CI (fully mocked).

6b. **I6b – Bundle `pprof`/`google-pprof` + Graphviz into the production Dockerfile (Q-053-02)**
   - _Goal:_ Make FR-053-04's SVG rendering work out of the box on the official Docker image, without requiring a manual post-install step.
   - _Preconditions:_ None (independent of I6's mocked tests, but naturally reviewed alongside it since both touch `PprofRenderer`'s default binary name).
   - _Steps:_ Add `google-perftools` and `graphviz` to the existing `apt-get install -y --no-install-recommends` list in `Dockerfile` (~line 77-89, alongside `ffmpeg`, `imagemagick`, etc.); confirm the installed binary is named `google-pprof` on the `dunglas/frankenphp:...-trixie` base image (Debian trixie); set `MEMORY_PROFILER_PPROF_BIN` default to `google-pprof` in `config/features.php`/`.env.example` to match. Build the image locally and manually verify `google-pprof --version` and `dot -V` both succeed inside the container.
   - _Commands:_ `docker build .` (or the project's existing Makefile/Docker Compose target), then `docker run --rm <image> google-pprof --version && dot -V`.
   - _Exit:_ Official image has both binaries available; `PprofRenderer`'s default configuration matches.

7. **I7 – Pruning (FR-053-07, S-053-12, CLI-053-01)**
   - _Goal:_ Bound `storage/profiling` growth.
   - _Steps:_ Failing test: seed N+k trace pairs, run command, assert only newest N remain. Implement `php artisan lychee:profiler:prune` (`App\Console\Commands\Profiling\PruneTraces`), wire `POST admin/profiler/prune` in the controller to invoke the same underlying action, add to `app/Console/Kernel.php`'s `$schedule` (e.g. daily).
   - _Commands:_ `php artisan test --filter=PruneTracesTest`, `make phpstan`.
   - _Exit:_ S-053-12 green; "Prune old traces" button on the admin page works.

8. **I8 – Documentation**
   - _Goal:_ Satisfy the Documentation Deliverables section of the spec.
   - _Steps:_ Write `docs/specs/2-how-to/enable-memory-profiler.md` (mirrors `enable-hprof.md` structure: dependencies, install, enable, usage, troubleshooting, Octane caveat). Update `roadmap.md` Active Features table (already has a placeholder row added at spec-drafting time — refresh its Progress column). Update `knowledge-map.md` with the new middleware/admin surface. Confirm `.env.example` entries from I1 have explanatory comments.
   - _Commands:_ none (docs only); spell-check pass.
   - _Exit:_ All Documentation Deliverables checked off.

9. **I9 – Quality gate & manual Octane check**
   - _Goal:_ Final sign-off.
   - _Steps:_ Full `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`. Manual (non-automated, documented in tasks.md Notes) check: run the app under `octane:start --server=frankenphp` locally with the flag on, hit the same route twice, diff the two dumps — record the observed result in the how-to guide, as evidence for ADR-0008 (Q-053-01 was already resolved to "ship anyway"; this observation may prompt an ADR revision if contamination is confirmed).
   - _Commands:_ `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ Green quality gate; Octane observation documented (even if the answer is "still needs more investigation" — that's a valid, recorded outcome).

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-053-01 | I4 / T-053-06 | Flag off → no-op. |
| S-053-02 | I4 / T-053-07 | Extension absent → no-op. |
| S-053-03 | I4 / T-053-08 | Normal capture path. |
| S-053-04 | I4 / T-053-09 | Write failure logged, response unaffected. |
| S-053-05 | I5 / T-053-11 | Empty listing. |
| S-053-06 | I5 / T-053-12 | Populated listing. |
| S-053-07 | I6 / T-053-15 | SVG success (mocked). |
| S-053-08 | I6 / T-053-16 | SVG missing-binary error. |
| S-053-09 | I5 / T-053-13 | Unauthenticated → redirect. |
| S-053-10 | I5 / T-053-14 | Non-owner → 403. |
| S-053-11 | I6 / T-053-17 | Path traversal → 404. |
| S-053-12 | I7 / T-053-19 | Pruning caps count. |

## Analysis Gate

Ready to run — Q-053-01..04 resolved 2026-07-28, spec/plan/tasks agree. Execute the walkthrough per `docs/specs/5-operations/analysis-gate-checklist.md` before starting I1 and record the outcome here.

## Exit Criteria

- All tasks in `tasks.md` checked `[x]`.
- `php artisan test`, `make phpstan` (0 errors), `vendor/bin/php-cs-fixer fix` all green.
- `docs/specs/2-how-to/enable-memory-profiler.md` published.
- `roadmap.md` and `knowledge-map.md` updated.
- `Dockerfile` updated with `google-perftools` + `graphviz`; verified inside a built image.
- Manual Octane cross-request observation recorded (I9), even if inconclusive, as evidence toward ADR-0008.

## Follow-ups / Backlog

- Bundle the `memprof` PECL extension itself into the default Docker image (needs explicit dependency approval — AGENTS "Dependencies" guardrail; `pprof`/`google-pprof` + Graphviz are already bundled per Q-053-02).
- Per-request opt-in trigger / sampling rate, if always-on proves too noisy in practice (deferred from Q-053-04).
- Aggregate/cross-request reporting (e.g. "top 10 heaviest routes this week").
- Revisit ADR-0008 (hard-disable under Octane, or a fix) if T-053-24's empirical observation shows cross-request contamination.
