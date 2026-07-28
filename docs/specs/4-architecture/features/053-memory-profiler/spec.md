# Feature 053 – Memory Profiler

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-07-28 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/053-memory-profiler/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/053-memory-profiler/tasks.md` |
| Roadmap entry | #053 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

> **Revision history.** This spec went through two engine choices during implementation, both driven by hard technical findings rather than preference:
> 1. **`memprof`** (the originally requested extension) — confirmed **impossible** to bundle: Lychee's official Docker image's PHP build is ZTS (required by FrankenPHP), and `memprof`'s mainline release refuses to compile against ZTS builds at all (`docker build` failure, reproduced; tracked upstream at [arnaud-lb/php-memory-profiler#24](https://github.com/arnaud-lb/php-memory-profiler/issues/24), unresolved since ~2016).
> 2. **`spx`** ([NoiseByNorthwest/php-spx](https://github.com/NoiseByNorthwest/php-spx)) — the feature's final engine. Explicitly supports ZTS (verified empirically: compiles and loads on the exact base image), is self-hosted (no SaaS), and ships allocation/free byte metrics suitable for leak-hunting (not just usage deltas, which is why XHProf-family tools were ruled out — see Q-053-02/Q-053-06).
>
> See [open-questions.md](../../open-questions.md) (Q-053-01 through Q-053-08) and [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md) for the full decision trail.

## Overview

Lychee had no built-in way to capture *memory* profiles of a request (only `itsgoingd/clockwork` for general request inspection and an optional, manually-installed XHProf setup documented in [enable-hprof.md](../../2-how-to/enable-hprof.md) for CPU/wall-time profiling). This feature adds an optional, request-scoped **memory** profiler backed by the [`spx`](https://github.com/NoiseByNorthwest/php-spx) PHP extension, plus an owner-only Blade admin page (`/admin/profiler`) to browse captured traces and open each one in SPX's own analysis screen (a call-graph/timeline/flame-graph viewer bundled with the extension).

**Correction to the initial brief.** The code sample supplied with this request (`new Arnaud\PhpMemoryProfiler\Profiler(); $profiler->start(); ... $profiler->dumpToFile(...)`) matched neither `memprof` (see revision history above) nor any other real extension — it was a hallucinated OOP API. No PHP memory-profiling extension exposes a `Profiler` class; every real option (`spx`, `memprof`, `tideways_xhprof`) exposes a small set of global functions.

**Why not an XHProf-family tool for the "hunt leaks" requirement.** `tideways_xhprof` and the modern PECL `xhprof` fork both support ZTS and expose a `XHPROF_FLAGS_MEMORY` mode, but that mode only reports `mu`/`pmu` (memory used / peak memory) per function call — usage deltas, not leaks. That doesn't distinguish "allocated and properly freed" from "allocated and never freed," which is the actual signal needed to hunt leaks (Q-053-06). `spx` exposes allocation count/bytes *and* free count/bytes (`zmac`/`zmab`/`zmfc`/`zmfb`) per call node, which is the closer fit, plus a process-RSS metric (`mor`) its own docs describe as "useful to highlight a memory leak."

This feature touches: **application** (new global middleware, config), **infra** (Docker image, container-start ini configuration — `spx`'s settings are `PHP_INI_SYSTEM`, unlike a normal Laravel feature flag), **REST/web** (new Blade-only admin routes — no Vue/API surface), and **ops/docs** (new how-to guide).

## Goals

- Capture a memory-allocation profile for every HTTP request while the feature is enabled, from the start of the request to the end, without requiring code changes per-route.
- Do so **correctly under Laravel Octane/FrankenPHP** (Lychee's default production runtime, where a single PHP worker thread serves many logical requests) — verified empirically, not assumed (see NFR-053-06).
- Persist a metadata sidecar per request under `storage/profiling` so traces survive across requests/deploys of the same container.
- Provide an owner-only web UI (`/admin/profiler`) to list captured traces and open each one in SPX's own analysis screen, with no Vue/JS SPA integration on Lychee's side (Blade only, per explicit instruction).
- Degrade to a complete no-op with zero measurable overhead when the `spx` extension is not loaded or the feature is disabled.
- Keep `storage/profiling` from growing without bound (count-based cap with auto-pruning, Q-053-03).

## Non-Goals

- No Vue/Nuxt UI, API resource, or SPA route — Blade views only, per explicit instruction. (SPX's own analysis screen is a separate, pre-built vanilla-JS asset bundled with the extension, reached via an external link — it is not part of Lychee's own frontend.)
- No CPU/wall-clock profiling (that's the existing XHProf how-to's job); this feature is memory-allocation only.
- No per-request opt-in trigger (query string / header), sampling rate, or percentage-based profiling in v1 — profiling is simply "on for every request" while the feature flag is enabled (resolved, Q-053-04, Option A).
- No bundling of the `memprof` PECL extension into the default Docker image — confirmed **impossible** (ZTS incompatibility; superseded by the `spx`-based design, see revision history).
- No rendering of the call-graph/flame-graph inside Lychee's own Blade pages — SPX's own analysis screen is linked to externally instead (resolved, Q-053-07; see Security & Access Model below for why this doesn't use the owner-only gate the same way).
- No aggregate/cross-request reporting (each analysis-screen view is per single request/trace only).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-053-01 | A global HTTP middleware (`App\Http\Middleware\MemoryProfiler`) calls `spx_profiler_start()` at the start of every request and, in its `terminate()` phase (after the response has been sent), calls `spx_profiler_stop()` (which returns SPX's own report key) and writes a JSON metadata sidecar (route name, method, path, status code, duration, peak memory, timestamp, user id, `spx_report_key`) to `storage/profiling`. | Enabled + extension loaded: a `lychee-*.json` sidecar appears in `storage/profiling` after every request, and SPX itself writes its own `{spx_report_key}.json` + `{spx_report_key}.txt.gz` report files to the same directory. | N/A (no user input). | If the sidecar write fails (disk full, permissions), the error is logged via the standard Laravel logger; the response already sent to the client is unaffected (this happens in `terminate()`). | Log entry `memory_profiler.dump_failed` with exception message on failure. | User request; corrected against the real `spx` API (`spx_profiler_start()`/`spx_profiler_stop()`, confirmed via upstream source `src/php_spx.c`). |
| FR-053-02 | The middleware is a complete no-op — no function calls, no I/O — when `function_exists('spx_profiler_start') === false` or the feature flag (FR-053-06) is off. | `handle()` returns `$next($request)` immediately; `terminate()` returns immediately. | N/A | N/A | None (intentionally silent — this is the default/common case). | AGENTS' "no surprises" guardrail. |
| FR-053-03 | `GET /admin/profiler` renders a Blade page listing every trace currently in `storage/profiling` (i.e. every `lychee-*.json` sidecar), newest first, showing timestamp, route/method/path, HTTP status, duration, and peak memory. | Page renders a table of traces; empty state shown when none exist. | N/A | If `storage/profiling` is unreadable, show an error banner instead of a 500. | None. | User request. |
| FR-053-04 | From the listing, each row with a captured `spx_report_key` links to SPX's own analysis screen (`{app_url}/?SPX_UI_URI=/report.html&SPX_KEY=<key>&key=<spx_report_key>`, per SPX's documented URL pattern), opened in a new tab. Rows without a usable key (missing `spx_report_key`, or `MEMORY_PROFILER_SPX_KEY` not configured) show a placeholder instead of a broken link. | Clicking the link opens SPX's bundled analysis screen (flame-graph/timeline UI) for that specific trace. | N/A — this is an external link, not a Lychee route; SPX itself validates `SPX_KEY` (see NFR-053-04 / Security & Access Model). | Missing key/report → placeholder text, not a broken link or 500. | None (SPX's own request-interception is outside Lychee's request lifecycle and therefore outside this feature's logging). | User request ("give the ability to open each trace" — resolved to SPX's own bundled viewer rather than a locally-rendered SVG, Q-053-07); upstream `spx_profiler_stop()` return value + documented analysis-screen URL pattern. |
| FR-053-05 | `/admin/profiler` and its `prune` sub-route require (a) an authenticated session and (b) that the authenticated user's id equals the configured `owner_id` (same check as the existing `OwnerIdRule`), enforced by a new `owner` route middleware. | Owner loads the page normally. | N/A | Unauthenticated → redirect (via existing `login_required` middleware, applied first); authenticated-but-not-owner → `403 Unauthorized` (`UnauthorizedException`, matches `OwnerIdRule`'s message/behaviour). | None (security-sensitive path; no need to log routine denials beyond the framework's own request log). | User request ("protected by owner_id check middleware"); existing `App\Rules\OwnerIdRule` pattern. Note: this gate protects Lychee's *listing* page only — SPX's own analysis screen (FR-053-04) is protected separately, see NFR-053-04. |
| FR-053-06 | The Laravel-side of the feature (middleware + admin routes) is gated by `config('features.memory-profiler')`, backed by `MEMORY_PROFILER_ENABLED` (env, default `false`), following the exact pattern already used for `log-viewer`, `use-s3`, etc. in `config/features.php`. When off, `/admin/profiler*` responds with the existing `FeatureDisabledException` (`feature:memory-profiler` middleware, 501). The `spx` extension's own ini settings are configured independently by a container-start script from the *same* env var (NFR-053-07). | Operator sets `MEMORY_PROFILER_ENABLED=true` + `MEMORY_PROFILER_SPX_KEY=<secret>`, restarts, feature becomes active end-to-end. | N/A | Route access while flag is off → 501, consistent with every other `feature:` gated route in this codebase. | None. | Existing `FeatureEnabled` middleware convention (`app/Http/Middleware/FeatureEnabled.php`). |
| FR-053-07 | `storage/profiling` is kept bounded: `php artisan lychee:profiler:prune` (manual, scheduled daily, or triggered from the admin page's "Prune old traces" button) deletes the oldest traces once the count exceeds `MEMORY_PROFILER_MAX_TRACES` (default 200) — removing both our own `lychee-*.json` sidecar and the corresponding `spx_report_key.json`/`.txt.gz` pair together. | Trace count in `storage/profiling` never exceeds the cap; no orphaned SPX report files. | N/A | N/A | Log entry `memory_profiler.pruned` with count removed. | Derived necessity — every enabled request writes a new trace; unbounded growth is a real operational risk (Q-053-03). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-053-01 | When disabled (flag off or extension absent), the middleware must add no more than a single `config()` read + one `function_exists()` check per request. | Global middleware runs on 100% of traffic; must not regress baseline latency for the overwhelming majority of installs that will never enable this. | Manual review of `handle()`/`terminate()` — both must short-circuit before any I/O or `spx_*` call. | `App\Http\Middleware\MemoryProfiler`. | AGENTS "no surprises". |
| NFR-053-02 | No runtime dependency on any external host — extension, ini config, and trace files are all local to the server; SPX's analysis screen is served by the extension itself, not a SaaS. | Lychee's offline-only requirement: it must work with zero network connection. | Code review: no HTTP client / CDN / telemetry call anywhere in the new middleware, controller, or views. | None. | Project convention (offline-only requirement). |
| NFR-053-03 | Admin UI is Blade-only; no new Vue component, Pinia store, or `resources/js` route is introduced. | Explicit instruction ("We do not need any vue integration for this. blade templates are fine."). | Code review of the diff — no changes under `resources/js/**`. | `resources/views/admin/profiler/index.blade.php`. | User request. |
| NFR-053-04 | SPX's own analysis screen is protected by the extension's own access-control mechanism (`spx.http_key` — a long random secret, required — and optionally `spx.http_ip_whitelist`/`spx.http_trusted_proxies`), **not** by Lychee's `owner_id` gate, because SPX intercepts matching requests at PHP's earliest hook (`RINIT`), before Laravel's kernel or router ever runs — Laravel middleware cannot see or gate that request at all. This is a deliberate, accepted trade-off (Q-053-07/Q-053-08), not an oversight. | Discovered while implementing FR-053-04: SPX's `http_ui_handler` execution path is chosen in `PHP_RINIT_FUNCTION`, entirely outside userland/Laravel code. | Code review + manual verification: hitting `/?SPX_UI_URI=/report.html&...` with a wrong/missing `SPX_KEY` must be denied by the extension itself (`check_access()`), independent of any Laravel route. `MEMORY_PROFILER_SPX_KEY` must never default to a guessable value (`.env.example` ships no default). | `spx.http_key`, `spx.http_ip_whitelist`, `spx.http_trusted_proxies` ini settings. | Upstream `src/php_spx.c` (`PHP_RINIT_FUNCTION`, `check_access()`); user decision (Q-053-08). |
| NFR-053-05 | The `spx` extension is bundled in the production `Dockerfile` (`install-php-extensions spx`, plus its `zlib1g-dev` build dependency) — unlike `memprof`, this compiles and loads successfully on the image's ZTS PHP build (empirically verified). Non-Docker/bare-metal installs must install it manually per the how-to guide. | Matches existing project convention for profiling tooling where feasible; ZTS support is what makes bundling possible here (unlike `memprof`). | `docker build .` succeeds; `docker run --entrypoint sh <image> -c "php -m \| grep spx"` reports the extension loaded. | `docs/specs/2-how-to/enable-memory-profiler.md`; `Dockerfile`. | Empirical verification during implementation (Q-053-02/Q-053-05). |
| NFR-053-06 | Manual `spx_profiler_start()`/`spx_profiler_stop()` spans (with `spx.http_profiling_auto_start=0`) are used instead of SPX's own ini-only "always profiling" mode, specifically to guarantee correct per-request memory isolation under Octane/FrankenPHP's persistent-worker model, where a single OS thread serves many logical requests and Zend request-lifecycle hooks are not guaranteed to reset state the same way as classic per-request PHP-FPM. **Verified empirically, not merely asserted:** two consecutive HTTP requests (allocating 1MB and 9MB respectively) served by the *same* worker thread (`process_pid`/`process_tid` identical in both SPX reports) produced independently-correct `peak_memory_usage` values (not cumulative), confirming no cross-request contamination. | SPX's own documentation ("Handle long-living / daemon processes") recommends exactly this pattern for persistent-worker runtimes; this is a materially different execution model from the traditional per-request PHP-FPM lifecycle that most profiling tooling assumes. | Manual verification performed during implementation: `frankenphp php-server` (FrankenPHP's own HTTP server) serving two consecutive requests to the same worker process/thread, comparing the two SPX-produced reports' `peak_memory_usage` and `process_pid`/`process_tid` fields. | `laravel/octane`, `dunglas/frankenphp` (default runtime); `spx.http_profiling_auto_start` ini setting. | Discovered during spec research for the (superseded) `memprof` design; resolved for `spx` via the above empirical test (Q-053-01, amended). | 

## UI / Interaction Mock-ups

```
GET /admin/profiler
+----------------------------------------------------------------------------------+
| Memory Profiler                                              [ Prune old traces ]|
|------------------------------------------------------------------------------------|
| ⚠ This server is running under Octane/FrankenPHP. Capture uses SPX's manual       |
|   start/stop spans specifically to remain correct under this runtime.              |
| ⚠ MEMORY_PROFILER_SPX_KEY is not set — "view" links cannot be built.               |
|   (shown only when the key is missing)                                             |
|------------------------------------------------------------------------------------|
| Captured at         | Route                | Method | Status | Duration | Peak mem |    |
|----------------------|-----------------------|--------|--------|----------|----------|----|
| 2026-07-28 10:14:02  | gallery.index         | GET    | 200    | 42 ms    | 42.1 MB  |[open in SPX →]|
| 2026-07-28 10:13:57  | api.v2.photo.upload   | POST   | 201    | 812 ms   | 118.4 MB |[open in SPX →]|
| 2026-07-28 10:13:40  | gallery.album.show    | GET    | 200    | 31 ms    | 39.8 MB  |      —        |
|                                                                                      |
| (empty state, shown when storage/profiling has no traces yet)                      |
| "No traces collected yet. Make sure MEMORY_PROFILER_ENABLED=true and the spx       |
|  extension is loaded — see the how-to guide."                                      |
+----------------------------------------------------------------------------------+

"[open in SPX →]" opens, in a new tab, SPX's own bundled analysis screen — a
separate, pre-built UI (flame graph / timeline / call tree) shipped with the
extension itself, not a Lychee-rendered page. It is reached via an external
URL protected by SPX's own spx.http_key, not by Lychee's owner-only gate
(see NFR-053-04).
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-053-01 | Feature flag off → middleware no-ops; `/admin/profiler*` returns 501 (`feature` middleware). |
| S-053-02 | Feature flag on, `spx` extension not loaded → middleware no-ops (logs nothing, no error); admin page still reachable and shows the "no traces / check extension" empty state. |
| S-053-03 | Feature flag on, extension loaded, normal request → sidecar (`lychee-*.json`) + SPX's own report pair written to `storage/profiling` after the response is sent. |
| S-053-04 | Sidecar write fails (disk full/permissions) → response to the original request is unaffected; failure logged. |
| S-053-05 | Owner visits `/admin/profiler` with zero traces present → empty state rendered, no error. |
| S-053-06 | Owner visits `/admin/profiler` with N traces present → table of N rows, newest first. |
| S-053-07 | Trace has a `spx_report_key` and `MEMORY_PROFILER_SPX_KEY` is configured → row shows a working "open in SPX" link. |
| S-053-08 | Trace has no `spx_report_key`, or the key isn't configured → row shows a placeholder, no broken link. |
| S-053-09 | Unauthenticated visitor requests `/admin/profiler` → redirected. |
| S-053-10 | Authenticated non-owner requests `/admin/profiler` → `403 Unauthorized`. |
| S-053-11 | Trace count exceeds `MEMORY_PROFILER_MAX_TRACES` → oldest trace(s) — both our sidecar and SPX's own report pair — pruned automatically. |
| S-053-12 | Two consecutive requests served by the same Octane/FrankenPHP worker thread → each gets an independently-scoped SPX report (verified empirically, NFR-053-06). |

## Test Strategy

- **Application/Middleware:** Feature tests for `MemoryProfiler` covering S-053-01..04, using a test double (`FakeSpxRecorder`) for the real `spx_profiler_start()`/`stop()` calls so the suite runs green on CI images without the extension installed.
- **REST/Web (Blade routes):** Feature tests for S-053-05..10: empty listing, populated listing (with/without a usable SPX link), unauthenticated redirect, non-owner 403.
- **CLI:** Feature/unit test for the pruning console command (S-053-11): seed N+k fake traces (sidecar + SPX report pairs), run the command, assert only the newest N remain and no SPX report is orphaned.
- **Manual/empirical (not automated):** S-053-12 was verified via a real `frankenphp php-server` run during implementation (see NFR-053-06) rather than as part of the automated suite, since it requires the real extension and a live HTTP server.
- **Docs/Contracts:** How-to guide (`enable-memory-profiler.md`) reviewed for accuracy against the real `spx` API and the empirically-verified Docker build/runtime behaviour.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-053-01 | `ProfilingTraceMeta` — JSON sidecar fields: `spx_report_key` (string\|null), `route_name` (string\|null), `method` (string), `path` (string), `status_code` (int), `duration_ms` (float), `peak_memory_bytes` (int), `user_id` (int\|null), `created_at` (ISO-8601 string). | application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-053-01 | Web GET `/admin/profiler` | Lists all traces. | `owner`, `feature:memory-profiler`, `login_required:always` middleware. Blade response, not JSON. |
| API-053-02 | Web POST `/admin/profiler/prune` | Manually triggers the pruning step from the admin page's "Prune old traces" button. | Same middleware stack. |
| API-053-03 | External (not a Lychee route) `GET /?SPX_UI_URI=/report.html&SPX_KEY=<key>&key=<spx_report_key>` | SPX's own analysis screen, intercepted by the extension before Laravel's router runs. | Protected by `spx.http_key`/`spx.http_ip_whitelist`, not by Lychee's `owner` middleware (NFR-053-04). |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-053-01 | `php artisan lychee:profiler:prune` | Deletes the oldest traces (sidecar + SPX report pair) beyond `MEMORY_PROFILER_MAX_TRACES`, callable manually or from the schedule (`app/Console/Kernel.php`). |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-053-01 | `memory_profiler.dump_failed` | `route`, `exception_message` (log channel only, not persisted as a DB event). |
| TE-053-02 | `memory_profiler.pruned` | `removed_count`, `remaining_count`. |

### Fixtures & Sample Data

None required — all tests use inline fixture data (fake sidecars/report files written directly to the test's `profiling` disk), since the real `.txt.gz`/`.json` report format is opaque to Lychee's own code (we only read/write our own sidecar; SPX's own reports are opaque blobs we pass through by reference).

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-053-01 | Empty trace list | No files in `storage/profiling` → guidance banner (extension/flag check). |
| UI-053-02 | Populated trace list | ≥1 trace present → table, newest first. |
| UI-053-03 | Working SPX link | Trace has `spx_report_key` + `MEMORY_PROFILER_SPX_KEY` configured → "open in SPX" link. |
| UI-053-04 | No SPX link available | Missing key/report → placeholder, no broken link. |
| UI-053-05 | Octane info banner | App detected running under Octane → informational banner (not a warning — NFR-053-06 confirms correctness under this runtime). |
| UI-053-06 | Missing SPX key banner | `MEMORY_PROFILER_SPX_KEY` unset while feature enabled → warning banner. |

## Telemetry & Observability

All events in this feature are **log-channel only** (Laravel's standard logger), not the DB-backed telemetry/event pipeline used by other features — there is no user-facing analytics need here, only operator-facing diagnostics. No PII beyond `user_id` (already present in every other Lychee log line) is recorded. SPX's own reports (`http_request_uri`, `http_method`, etc.) live in SPX's own report files, outside Lychee's telemetry pipeline.

## Documentation Deliverables

- How-to guide: `docs/specs/2-how-to/enable-memory-profiler.md` (bundled `spx` extension; `MEMORY_PROFILER_ENABLED`/`MEMORY_PROFILER_SPX_KEY`/`MEMORY_PROFILER_SPX_IP_WHITELIST`/`MEMORY_PROFILER_MAX_TRACES`; security model for SPX's own analysis screen; Octane correctness note).
- `docs/specs/4-architecture/roadmap.md`: row for 053.
- `docs/specs/4-architecture/knowledge-map.md`: entry describing the middleware/admin surface and SPX integration.
- `.env.example`: `MEMORY_PROFILER_ENABLED`, `MEMORY_PROFILER_MAX_TRACES`, `MEMORY_PROFILER_SPX_KEY`, `MEMORY_PROFILER_SPX_IP_WHITELIST`.
- `Dockerfile` + `docker/scripts/06-configure-profiler.sh`: bundles `spx`, writes its `PHP_INI_SYSTEM` settings from env vars at container start.
- [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md): records the full engine-selection history (`memprof` → `spx`) and the Octane-correctness verification.

## Fixtures & Sample Data

None (see Interface & Contract Catalogue above).

## Spec DSL

```
domain_objects:
  - id: DO-053-01
    name: ProfilingTraceMeta
    fields:
      - name: spx_report_key
        type: string|null
      - name: route_name
        type: string|null
      - name: method
        type: string
      - name: path
        type: string
      - name: status_code
        type: integer
      - name: duration_ms
        type: float
      - name: peak_memory_bytes
        type: integer
      - name: user_id
        type: integer|null
      - name: created_at
        type: string (ISO-8601)
routes:
  - id: API-053-01
    method: GET
    path: /admin/profiler
  - id: API-053-02
    method: POST
    path: /admin/profiler/prune
  - id: API-053-03
    method: GET
    path: / (external, SPX-intercepted, not a Lychee route)
cli_commands:
  - id: CLI-053-01
    command: php artisan lychee:profiler:prune
telemetry_events:
  - id: TE-053-01
    event: memory_profiler.dump_failed
  - id: TE-053-02
    event: memory_profiler.pruned
ui_states:
  - id: UI-053-01
    description: Empty trace list guidance banner
  - id: UI-053-02
    description: Populated trace list table
  - id: UI-053-03
    description: Working SPX analysis-screen link
  - id: UI-053-04
    description: No SPX link available placeholder
  - id: UI-053-05
    description: Octane info banner
  - id: UI-053-06
    description: Missing SPX key warning banner
```

## Appendix

### Corrected reference snippet (final, `spx`-based)

```php
// App\Http\Middleware\MemoryProfiler::handle()
if (!Features::active('memory-profiler') || !$this->recorder->isAvailable()) {
    return $next($request); // extension not loaded or feature off — no-op
}
$request->attributes->set(self::ATTR_START_TIME, microtime(true));
$this->recorder->start(); // spx_profiler_start()

// ... later, in terminate($request, $response) ...
$spx_report_key = $this->recorder->stop(); // spx_profiler_stop(): ?string
// write our own JSON sidecar to storage/profiling, including $spx_report_key
```

Building the analysis-screen link (external, not a Lychee route):

```php
url('/') . '?' . http_build_query([
    'SPX_UI_URI' => '/report.html',
    'SPX_KEY' => config('features.memory-profiler-spx-key'),
    'key' => $spx_report_key,
]);
```

### Why manual start/stop instead of SPX's own "always profiling" ini mode

SPX supports an ini-only always-on mode (`spx.http_profiling_enabled=1` with default `auto_start=1`), which would need zero Laravel-side code. It was rejected in favour of manual `spx_profiler_start()`/`spx_profiler_stop()` spans (`spx.http_profiling_auto_start=0`) specifically because SPX's own documentation calls out persistent-worker runtimes (exactly Octane/FrankenPHP's model) as needing explicit span control for correctness — and this was independently confirmed empirically during implementation (NFR-053-06).
