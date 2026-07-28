# Feature 053 Tasks – Memory Profiler

_Status: Implemented_
_Last updated: 2026-07-28_

> Keep this checklist aligned with `plan.md`'s increments. This reflects the final `spx`-based implementation — the earlier `memprof`-based tasks (T-053-01..25 in a prior revision of this file) were superseded mid-implementation after `memprof` was confirmed impossible to bundle (ZTS incompatibility). See [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md) and [open-questions.md](../../open-questions.md) for the full trail.

## Checklist

- [x] T-053-01 – Add `memory-profiler`, `memory-profiler-max-traces`, `memory-profiler-spx-key` to `config/features.php` + `.env.example` entries (F-053-06).
  _Verification commands:_ `php artisan test --filter=MemoryProfilerConfigTest`, `make phpstan`

- [x] T-053-02 – Add `profiling` disk to `config/filesystems.php` + `storage/profiling/.gitignore` (F-053-01).
  _Verification commands:_ `php artisan test --filter=ProfilingDiskTest`

- [x] T-053-03 – Implement `App\Http\Middleware\OwnerOnly` + register `owner` alias (F-053-05).
  _Verification commands:_ `php artisan test --filter=OwnerOnlyTest`, `make phpstan`

- [x] T-053-04 – Implement `App\Services\Profiling\SpxRecorder` (thin `function_exists`-guarded wrapper around `spx_profiler_start()`/`spx_profiler_stop()`) (F-053-01, F-053-02).
  _Verification commands:_ covered via T-053-05's tests; `make phpstan` (with `phpstan/stubs/spx.stub` registered as a `bootstrapFiles` entry so the stub functions are genuinely callable during analysis).

- [x] T-053-05 – Implement `App\Http\Middleware\MemoryProfiler` (`handle()`/`terminate()`, state carried on `$request->attributes` since Laravel resolves a fresh instance for `terminate()`), registered in `app/Http/Kernel.php`'s global `$middleware` (F-053-01, F-053-02, F-053-06, S-053-01..04).
  _Verification commands:_ `php artisan test --filter=MemoryProfilerTest`, `make phpstan`
  _Notes:_ Tested via `FakeSpxRecorder` (a `SpxRecorder` subclass); the real extension is never required in CI.

- [x] T-053-06 – Implement `App\Http\Controllers\Admin\ProfilerController::index()` + `GET admin/profiler` route + `resources/views/admin/profiler/index.blade.php` (F-053-03, F-053-04, F-053-05, S-053-05..10).
  _Verification commands:_ `php artisan test --filter=ProfilerControllerTest`, `make phpstan`
  _Notes:_ Lists `lychee-*.json` sidecars; builds the SPX analysis-screen URL (`?SPX_UI_URI=/report.html&SPX_KEY=...&key=...`) per row when `spx_report_key` + `MEMORY_PROFILER_SPX_KEY` are both available.

- [x] T-053-07 – Implement `App\Services\Profiling\TracePruner` (deletes each pruned trace's `lychee-*.json` sidecar *and* its `spx_report_key`-derived `.json`/`.txt.gz` pair together) + `App\Console\Commands\Profiling\PruneTraces` (`lychee:profiler:prune`) + schedule entry + `ProfilerController::prune()` (F-053-07, CLI-053-01, S-053-11).
  _Verification commands:_ `php artisan test --filter=TracePrunerTest`, `php artisan test --filter=PruneTracesTest`, `make phpstan`

- [x] T-053-08 – Bundle `spx` (+ `zlib1g-dev` build dependency) in `Dockerfile`'s existing `apt-get install`/`install-php-extensions` invocations (NFR-053-05).
  _Verification commands:_
  - `docker build .` — succeeded.
  - `docker run --rm --entrypoint sh <image> -c "php -m | grep spx"` — reports `SPX` loaded.
  _Notes:_ `memprof` + `libjudy-dev` were attempted here first and reverted after `docker build` failed deterministically with `#error "ZTS build not supported (yet)"` — see ADR-0008.

- [x] T-053-09 – Add `docker/scripts/06-configure-profiler.sh` (writes `spx`'s `PHP_INI_SYSTEM` settings — `data_dir`, `http_profiling_enabled`, `http_profiling_auto_start=0`, `http_profiling_metrics`, and conditionally `http_enabled`/`http_key`/`http_ip_whitelist` — from `MEMORY_PROFILER_*` env vars); source it from `entrypoint.sh` (F-053-06).
  _Verification commands:_
  - `docker run ... -e MEMORY_PROFILER_ENABLED=true -e MEMORY_PROFILER_SPX_KEY=... -c "/usr/local/bin/06-configure-profiler.sh && cat .../zz-memory-profiler.ini && php -i | grep spx"` — ini file and `php -i` output both show the expected settings.
  - Same command with `MEMORY_PROFILER_ENABLED` unset — ini file empty, `php -i` shows `spx.http_enabled => 0`, `spx.http_profiling_enabled => no value`.

- [x] T-053-10 – Empirically verify per-request memory isolation under FrankenPHP's worker model (NFR-053-06, S-053-12).
  _Verification commands:_ `frankenphp php-server` serving a minimal script via the built image, two consecutive requests (1MB / 9MB allocations) compared by `process_pid`/`process_tid`/`peak_memory_usage` in the two resulting SPX reports.
  _Notes:_ Confirmed: same worker thread (`process_pid`/`process_tid` identical), independently-correct `peak_memory_usage` per request (not cumulative) — manual start/stop spans are correct under Octane/FrankenPHP. This closes the loop opened by the (superseded) `memprof`-era Q-053-01.

- [x] T-053-11 – Write `docs/specs/2-how-to/enable-memory-profiler.md` for the `spx`-based design (dependencies, install, enable, SPX analysis-screen access model, troubleshooting).
  _Verification commands:_ n/a (docs)

- [x] T-053-12 – Update `docs/specs/4-architecture/roadmap.md` and `docs/specs/4-architecture/knowledge-map.md`.
  _Verification commands:_ n/a (docs)

- [ ] T-053-13 – Full quality gate.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test`
  - `make phpstan`

## Notes / TODOs

- Every task that calls `spx_*` functions directly is wrapped behind `App\Services\Profiling\SpxRecorder`, specifically so the automated suite never requires the real PECL extension to be installed in CI/sandbox — only T-053-08/09/10 (Docker builds + manual runtime checks) touch the real extension.
- `phpstan/stubs/spx.stub` is registered under `bootstrapFiles` (not `stubFiles`) in `phpstan.neon` — `stubFiles` alone did not make PHPStan recognize genuinely new global functions in this project's PHPStan version; `bootstrapFiles` (which actually `require`s the stub, defining real no-op functions during the analysis process) was needed instead.
- If `MEMORY_PROFILER_SPX_KEY` is ever made mandatory-with-validation (rather than just a documented recommendation), add a startup check in `06-configure-profiler.sh` that fails loudly (not just warns) when `MEMORY_PROFILER_ENABLED=true` and the key is unset.
