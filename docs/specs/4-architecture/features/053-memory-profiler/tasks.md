# Feature 053 Tasks – Memory Profiler

_Status: Draft_
_Last updated: 2026-07-28_

> Keep this checklist aligned with `plan.md`'s increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> Q-053-01 through Q-053-04 were resolved on 2026-07-28 (all Option A; Q-053-02 modified — see `open-questions.md` and `ADR-0008`). This checklist is unblocked; run the analysis gate before starting T-053-01.

## Checklist

- [ ] T-053-01 – Add `memory-profiler` flag to `config/features.php` + `.env.example` entries (F-053-06).
  _Intent:_ Wire the env-backed feature flag, no behaviour yet.
  _Verification commands:_
  - `php artisan test --filter=FeaturesConfigTest`
  - `make phpstan`
  _Notes:_ Mirror `log-viewer`/`use-s3` style exactly (docblock + `env()` default `false`).

- [ ] T-053-02 – Add failing test asserting `config('features.memory-profiler')` defaults to `false` (F-053-06).
  _Intent:_ Test-first for I1.
  _Verification commands:_ `php artisan test --filter=FeaturesConfigTest`

- [ ] T-053-03 – Add `profiling` disk to `config/filesystems.php` + `storage/profiling/.gitignore` (F-053-01).
  _Intent:_ Stable storage location for trace pairs.
  _Verification commands:_
  - `php artisan test --filter=ProfilingDiskTest`
  _Notes:_ `driver: local`, `root: storage_path('profiling')`, `visibility: private`, matches existing `tmp-uploads`/`image-jobs` disk shape.

- [ ] T-053-04 – Failing feature tests for `owner` middleware: unauthenticated, authenticated-non-owner, authenticated-owner (F-053-05, S-053-09, S-053-10).
  _Intent:_ Test-first for I3.
  _Verification commands:_ `php artisan test --filter=OwnerOnlyMiddlewareTest`

- [ ] T-053-05 – Implement `App\Http\Middleware\OwnerOnly` + register `owner` alias in `app/Http/Kernel.php` (F-053-05).
  _Intent:_ Reusable owner-only route guard, matching `App\Rules\OwnerIdRule`'s check (`Auth::id() === $config_manager->getValueAsInt('owner_id')`), throwing `UnauthorizedException` on mismatch.
  _Verification commands:_
  - `php artisan test --filter=OwnerOnlyMiddlewareTest`
  - `make phpstan`

- [ ] T-053-06 – Failing test: middleware no-ops when feature flag is off (F-053-02, S-053-01).
  _Intent:_ Test-first for I4.
  _Verification commands:_ `php artisan test --filter=MemoryProfilerMiddlewareTest`

- [ ] T-053-07 – Failing test: middleware no-ops when `memprof_enable` doesn't exist / recorder double reports unavailable (F-053-02, S-053-02).
  _Intent:_ Test-first for I4; real extension is never required in CI.
  _Verification commands:_ `php artisan test --filter=MemoryProfilerMiddlewareTest`

- [ ] T-053-08 – Implement `App\Services\Profiling\MemprofRecorder` (thin wrapper around `memprof_enable/disable/dump_pprof`, `function_exists`-guarded) + `App\Http\Middleware\MemoryProfiler` (`handle()`/`terminate()`), register in `app/Http/Kernel.php`'s global `$middleware` (F-053-01, F-053-02, F-053-06).
  _Intent:_ Core capture path; writes `.pprof` + JSON sidecar (DO-053-01) to the `profiling` disk in `terminate()`.
  _Verification commands:_
  - `php artisan test --filter=MemoryProfilerMiddlewareTest`
  - `make phpstan`

- [ ] T-053-09 – Failing + passing test: dump/write failure is logged, original response unaffected (F-053-01, S-053-04).
  _Intent:_ Resilience of the `terminate()` path.
  _Verification commands:_ `php artisan test --filter=MemoryProfilerMiddlewareTest`

- [ ] T-053-10 – Add fixture `tests/Fixtures/Profiling/sample.pprof` + a sample JSON sidecar (FX-053-01).
  _Intent:_ Deterministic fixture for listing/SVG tests that don't require the real extension.
  _Verification commands:_ n/a (fixture only, used by later tasks)

- [ ] T-053-11 – Failing test: `GET /admin/profiler` empty state (F-053-03, S-053-05).
  _Intent:_ Test-first for I5.
  _Verification commands:_ `php artisan test --filter=ProfilerControllerTest`

- [ ] T-053-12 – Failing test: `GET /admin/profiler` populated listing, newest first, correct columns (F-053-03, S-053-06).
  _Intent:_ Test-first for I5, uses FX-053-01.
  _Verification commands:_ `php artisan test --filter=ProfilerControllerTest`

- [ ] T-053-13 – Failing test: unauthenticated → redirect on `/admin/profiler*` (F-053-05, S-053-09).
  _Intent:_ Confirms `login_required:always` + `owner` stack ordering.
  _Verification commands:_ `php artisan test --filter=ProfilerControllerTest`

- [ ] T-053-14 – Failing test: authenticated non-owner → 403 on `/admin/profiler*` (F-053-05, S-053-10).
  _Intent:_ Confirms `owner` middleware wired on the route group, not just unit-tested in isolation.
  _Verification commands:_ `php artisan test --filter=ProfilerControllerTest`

- [ ] T-053-15 – Implement `App\Http\Controllers\Admin\ProfilerController::index` + route (`admin/profiler`) + `resources/views/admin/profiler/index.blade.php` (F-053-03, F-053-05, F-053-06).
  _Intent:_ Listing page per spec mock-up; gated by `login_required:always`, `feature:memory-profiler`, `owner`.
  _Verification commands:_
  - `php artisan test --filter=ProfilerControllerTest`
  - `make phpstan`

- [ ] T-053-16 – Failing test: SVG render success renders inline (mocked `PprofRenderer`) (F-053-04, S-053-07).
  _Intent:_ Test-first for I6.
  _Verification commands:_ `php artisan test --filter=ProfilerSvgTest`

- [ ] T-053-17 – Failing test: SVG render failure (binary missing/process error) shows error state, not 500 (F-053-04, S-053-08).
  _Intent:_ Test-first for I6.
  _Verification commands:_ `php artisan test --filter=ProfilerSvgTest`

- [ ] T-053-18 – Failing test: path-traversal payload as `{trace}` → 404 (NFR-053-04, S-053-11).
  _Intent:_ Test-first, security-critical.
  _Verification commands:_ `php artisan test --filter=ProfilerSvgTest`

- [ ] T-053-19 – Implement `App\Services\Profiling\PprofRenderer` (shells out to `MEMORY_PROFILER_PPROF_BIN`, caches `.svg` sidecar) + `GET admin/profiler/{trace}/svg` route/controller method + safe-basename resolution (F-053-04, NFR-053-04).
  _Intent:_ Core SVG rendering path.
  _Verification commands:_
  - `php artisan test --filter=ProfilerSvgTest`
  - `make phpstan`

- [ ] T-053-19a – Add `google-perftools` + `graphviz` to the `Dockerfile`'s `apt-get install` block; set `MEMORY_PROFILER_PPROF_BIN` default to `google-pprof` (F-053-04, NFR-053-05, Q-053-02).
  _Intent:_ Make SVG rendering work out of the box on the official Docker image, per the resolved Q-053-02 (Option A modified — bundle pprof/Graphviz, keep `memprof` itself manual).
  _Verification commands:_
  - `docker build .` (or project's existing Docker build target)
  - `docker run --rm <image> google-pprof --version && dot -V`
  _Notes:_ `memprof` (the PHP extension) is deliberately **not** added here — it stays a manually-installed, documented opt-in dependency (see Non-Goals in spec.md).

- [ ] T-053-20 – Failing test: pruning keeps only the newest `MEMORY_PROFILER_MAX_TRACES` trace pairs (F-053-07, S-053-12).
  _Intent:_ Test-first for I7.
  _Verification commands:_ `php artisan test --filter=PruneTracesTest`

- [ ] T-053-21 – Implement `App\Console\Commands\Profiling\PruneTraces` (`lychee:profiler:prune`), wire into `app/Console/Kernel.php` schedule, add `POST admin/profiler/prune` route/controller action reusing the same action class (F-053-07, CLI-053-01, S-053-12).
  _Intent:_ Bound `storage/profiling` growth; manual + scheduled trigger.
  _Verification commands:_
  - `php artisan test --filter=PruneTracesTest`
  - `make phpstan`

- [ ] T-053-22 – Write `docs/specs/2-how-to/enable-memory-profiler.md` (dependencies, install, enable, usage, troubleshooting, Octane caveat).
  _Intent:_ Documentation deliverable; mirrors `enable-hprof.md` structure.
  _Verification commands:_ n/a (docs)

- [ ] T-053-23 – Update `docs/specs/4-architecture/roadmap.md` (refresh 053 row) and `docs/specs/4-architecture/knowledge-map.md` (new middleware/admin surface entry).
  _Intent:_ Documentation deliverable.
  _Verification commands:_ n/a (docs)

- [ ] T-053-24 – Manual (documented, non-automated) Octane cross-request check: run `octane:start --server=frankenphp` locally with the flag on, hit the same route twice, diff the two dumps; record the observation in the how-to guide and as evidence toward resolving Q-053-01.
  _Intent:_ Close the loop on NFR-053-06's biggest open risk with real evidence.
  _Verification commands:_ n/a (manual)

- [ ] T-053-25 – Full quality gate.
  _Intent:_ Final sign-off per AGENTS.md "After Completing Work".
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test`
  - `make phpstan`

## Notes / TODOs

- Every task that mentions `memprof_*` functions directly is wrapped behind `App\Services\Profiling\MemprofRecorder` specifically so the automated suite never requires the real PECL extension to be installed in CI/sandbox — only T-053-24 (manual) touches the real extension.
- Q-053-01 resolved to Option A (ship with a risk banner; validate empirically, per ADR-0008) — T-053-24 is a one-off documented manual observation, not a permanent regression test. If that observation later shows cross-request contamination under Octane, revisit ADR-0008 and this file's T-053-08 for a hard-disable guard.
- Q-053-03 resolved to Option A (count-based retention, `MEMORY_PROFILER_MAX_TRACES`) — T-053-20/T-053-21 as written.
- Q-053-02 resolved to Option A modified (bundle `pprof`/`google-pprof` + Graphviz in the Dockerfile, `memprof` itself stays manual) — see new T-053-19a.
