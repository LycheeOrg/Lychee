# Feature 053 – Memory Profiler

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-07-28 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/053-memory-profiler/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/053-memory-profiler/tasks.md` |
| Roadmap entry | #053 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

> **Resolution log.** Q-053-01 through Q-053-04 were resolved on 2026-07-28 — all Option A, with Q-053-02 **modified**: `pprof`/`google-pprof` + Graphviz are bundled into the production `Dockerfile` rather than left as manual-install-only (the `memprof` PHP extension itself remains a separate, manually-installed opt-in dependency — see FR-053-04, NFR-053-05). Q-053-01 (Octane risk) is recorded in [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md). See [open-questions.md](../../open-questions.md) for full resolution details.

## Overview

Lychee currently has no built-in way to capture *memory* profiles of a request (only `itsgoingd/clockwork` for general request inspection and an optional, manually-installed XHProf setup documented in [enable-hprof.md](../../2-how-to/enable-hprof.md) for CPU/wall-time profiling). This feature adds an optional, request-scoped **memory** profiler backed by the [`memprof`](https://github.com/arnaud-lb/php-memory-profiler) PHP extension, plus an owner-only Blade admin page (`/admin/profiler`) to browse collected traces and render each one as an SVG call/flame graph.

**Correction to the initial brief.** The code sample supplied with this request (`new Arnaud\PhpMemoryProfiler\Profiler(); $profiler->start(); ... $profiler->dumpToFile(...)`) does not match the real library. `arnaud-lb/php-memory-profiler` ships a **PHP extension** (`memprof.so`, installed via PECL/PIE — it is *not* a Composer package and defines no `Profiler` class or `Arnaud\PhpMemoryProfiler` namespace). Its actual public API is a set of global functions (confirmed against `memprof.stub.php` in the upstream repo):

```php
memprof_enabled(): bool
memprof_enabled_flags(): array
memprof_enable(): bool
memprof_disable(): bool
memprof_dump_array(): array
memprof_dump_callgrind($handle): void   // for KCacheGrind/QCacheGrind
memprof_dump_pprof($handle): void       // for the `pprof`/`google-pprof` CLI (chosen here, see FR-053-04)
memprof_version(): string
```

There is no `dumpToDot()`. Turning a dump into an SVG flame/call-graph requires the external `pprof` (google-perftools) CLI, which itself shells out to Graphviz's `dot` — a new operational dependency beyond the extension itself (see FR-053-04, NFR-053-05, Q-053-02).

This feature touches: **application** (new middleware, config), **REST/web** (new Blade-only admin routes — no Vue/API surface), and **ops/docs** (new how-to guide, since the `memprof` extension, like XHProf, is not bundled by default and must be opted into).

## Goals

- Capture a memory-allocation profile for every HTTP request while the feature is enabled, from the start of the request to the end, without requiring code changes per-route.
- Persist each trace under `storage/profiling` so it survives across requests/deploys of the same container.
- Provide an owner-only web UI (`/admin/profiler`) to list collected traces and render any one of them as an SVG call/flame graph, with no Vue/JS SPA integration (Blade only, per explicit instruction).
- Degrade to a complete no-op with zero measurable overhead when the `memprof` extension is not loaded or the feature is disabled (mirrors the existing opt-in precedent set by XHProf/Clockwork/Log Viewer).
- Keep `storage/profiling` from growing without bound (count-based cap with auto-pruning, Q-053-03).

## Non-Goals

- No Vue/Nuxt UI, API resource, or SPA route — Blade views only, per explicit instruction.
- No CPU/wall-clock profiling (that's the existing XHProf how-to's job); this feature is memory-allocation only.
- No per-request opt-in trigger (query string / header), sampling rate, or percentage-based profiling in v1 — profiling is simply "on for every request" while the feature flag is enabled (resolved, Q-053-04, Option A).
- No bundling of the `memprof` PECL extension into the default Docker image in this feature — it remains a manually-installed, documented opt-in extension (mirrors the XHProf precedent), since installing a PHP extension is a heavier, build-time-only toggle unlike installing a CLI tool. **`pprof`/`google-pprof` and Graphviz *are* bundled into the production `Dockerfile`** (resolved, Q-053-02, Option A modified) — see FR-053-04 and the plan's Dockerfile increment.
- No aggregate/cross-request reporting (flame graphs are per single request/trace only).
- No guarantee of accurate results when running under Laravel Octane/FrankenPHP workers — shipped anyway with a documented risk banner rather than blocked or hard-disabled (resolved, Q-053-01, Option A; see NFR-053-06 and [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md)).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-053-01 | A global HTTP middleware calls `memprof_enable()` at the start of every request and, in its `terminate()` phase (after the response has been sent), calls `memprof_disable()`, dumps the profile in pprof format, and writes it to `storage/profiling` together with a JSON metadata sidecar (route name, method, path, status code, duration, peak memory, timestamp, user id). | Enabled + extension loaded: a `.pprof` + `.json` pair appears in `storage/profiling` after every request. | N/A (no user input). | If the dump/write fails (disk full, permissions), the error is logged via the standard Laravel logger; the response already sent to the client is unaffected (dump happens in `terminate()`). | Log entry `memory_profiler.dump_failed` with exception message on failure. | User request; upstream `memprof` API (`memprof.stub.php`). |
| FR-053-02 | The middleware is a complete no-op — no function calls, no I/O — when `function_exists('memprof_enable') === false` or the feature flag (FR-053-06) is off. | `handle()` returns `$next($request)` immediately; `terminate()` returns immediately. | N/A | N/A | None (intentionally silent — this is the default/common case). | Derived from XHProf precedent (`enable-hprof.md`) and AGENTS' "no surprises" guardrail. |
| FR-053-03 | `GET /admin/profiler` renders a Blade page listing every trace pair currently in `storage/profiling`, newest first, showing timestamp, route/method/path, HTTP status, duration, peak memory, and file size. | Page renders a table/list of traces; empty state shown when none exist. | N/A | If `storage/profiling` is unreadable, show an error banner instead of a 500. | None. | User request. |
| FR-053-04 | From the listing, `GET /admin/profiler/{trace}/svg` renders (or streams) an SVG call-graph for that trace by invoking the external `pprof`/`google-pprof` CLI (binary name configurable via `MEMORY_PROFILER_PPROF_BIN`, default `google-pprof` — the name of the binary provided by Debian/Ubuntu's `google-perftools` package, matching this project's Debian-trixie-based `Dockerfile`) against the stored `.pprof` dump, caching the generated `.svg` next to the dump so repeat views don't re-invoke the CLI. **`google-perftools` (providing `google-pprof`) and `graphviz` are installed by default in the production `Dockerfile`** (resolved, Q-053-02), so this works out of the box on the official image; non-Docker/bare-metal installs must install them manually per the how-to guide. | SVG displayed inline in the browser. | The `{trace}` route parameter is resolved against a strict filename allow-list (no path traversal — see NFR-053-04) before touching the filesystem. | If the `pprof`/Graphviz binaries are missing or the shell-out fails (e.g. a custom/non-Docker install that hasn't installed them), show a clear error state explaining the missing dependency and linking to the new how-to guide, instead of a 500. | Log entry `memory_profiler.svg_render_failed` on shell-out failure. | User request ("give the ability to open each trace as svg"); upstream `memprof_dump_pprof()` + `pprof` CLI; Q-053-02 resolution. |
| FR-053-05 | `/admin/profiler` and all its sub-routes require (a) an authenticated session and (b) that the authenticated user's id equals the configured `owner_id` (same check as the existing `OwnerIdRule`), enforced by a new `owner` route middleware. | Owner loads the page normally. | N/A | Unauthenticated → redirect to login (via existing `login_required` middleware, applied first); authenticated-but-not-owner → `403 Unauthorized` (`UnauthorizedException`, matches `OwnerIdRule`'s message/behaviour). | None (security-sensitive path; no need to log routine denials beyond the framework's own request log). | User request ("protected by owner_id check middleware"); existing `App\Rules\OwnerIdRule` pattern. |
| FR-053-06 | The entire feature (middleware + admin routes) is gated by a single flag: `config('features.memory-profiler')`, backed by `MEMORY_PROFILER_ENABLED` (env, default `false`), following the exact pattern already used for `log-viewer`, `use-s3`, etc. in `config/features.php`. When off, `/admin/profiler*` responds with the existing `FeatureDisabledException` (`feature:memory-profiler` middleware, 501). | Operator sets `MEMORY_PROFILER_ENABLED=true`, restarts, feature becomes active end-to-end. | N/A | Route access while flag is off → 501, consistent with every other `feature:` gated route in this codebase. | None. | Existing `FeatureEnabled` middleware convention (`app/Http/Middleware/FeatureEnabled.php`). |
| FR-053-07 | `storage/profiling` is kept bounded: a scheduled/console-driven pruning step deletes the oldest trace pairs once the count exceeds a configurable cap (`MEMORY_PROFILER_MAX_TRACES`, default 200) (resolved, Q-053-03, Option A). | Trace count in `storage/profiling` never exceeds the cap. | N/A | N/A | Log entry `memory_profiler.pruned` with count removed. | Derived necessity — every enabled request writes a new trace pair; unbounded growth is a real operational risk. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-053-01 | When disabled (flag off or extension absent), the middleware must add no more than a single `config()` read + one `function_exists()` check per request. | Global middleware runs on 100% of traffic; must not regress baseline latency for the overwhelming majority of installs that will never enable this. | Manual review of `handle()`/`terminate()` — both must short-circuit before any I/O or `memprof_*` call. | `App\Http\Middleware\MemoryProfiler`. | AGENTS "no surprises"; existing `FeatureEnabled`/XHProf precedent (opt-in only). |
| NFR-053-02 | No runtime dependency on any external host — extension, CLI tools, and trace files are all local to the server. | Lychee's offline-only requirement: it must work with zero network connection (no CDN, font, icon, or telemetry host dependency). | Code review: no HTTP client / CDN / telemetry call anywhere in the new middleware, controller, or views. | None. | Project convention (offline-only requirement). |
| NFR-053-03 | Admin UI is Blade-only; no new Vue component, Pinia store, or `resources/js` route is introduced. | Explicit instruction ("We do not need any vue integration for this. blade templates are fine."). | Code review of the diff — no changes under `resources/js/**`. | `resources/views/admin/profiler/*.blade.php`. | User request. |
| NFR-053-04 | The `{trace}` route parameter must never be used to build a filesystem path directly; it is resolved against an allow-list of basenames actually present in `storage/profiling` (e.g. via `Str::of($trace)->basename()` equality check against a directory listing, rejecting `..`, `/`, and non-matching entries with a 404). | Prevents path traversal / arbitrary file read through the owner-only admin page. | Feature test: request `/admin/profiler/..%2F..%2F.env/svg` (and similar) as owner → 404, not a leaked file. | `App\Http\Controllers\Admin\ProfilerController`. | OWASP Top 10 guardrail (AGENTS "Be careful not to introduce security vulnerabilities"). |
| NFR-053-05 | `memprof` (PHP extension) remains a manually-installed, documented opt-in dependency (never bundled), described in a dedicated how-to guide mirroring `enable-hprof.md`. `pprof`/`google-pprof` + Graphviz `dot` are bundled by default in the production `Dockerfile` (Q-053-02) but the code must still never assume they're present — every code path that shells out or calls a `memprof_*` function checks availability first and fails into a clear, non-500 UI/log message, covering custom/non-Docker/bare-metal installs. | Matches existing project convention for optional profiling tooling (the extension); avoids a broken admin page on any install — Docker or otherwise — that hasn't fully opted in. | New file `docs/specs/2-how-to/enable-memory-profiler.md` exists and is linked from the admin page's empty/error states; `Dockerfile` diff installs `google-perftools` + `graphviz`. | `docs/specs/2-how-to/enable-hprof.md` (structural precedent); `Dockerfile`. | AGENTS "Dependencies" guardrail + existing XHProf doc; Q-053-02 resolution. |
| NFR-053-06 | Behaviour under Laravel Octane/FrankenPHP (the **default** production runtime per `Dockerfile`/`deploy-worker-mode.md`, where the PHP process — and therefore any process-global `memprof` tracking state — persists across many requests) is explicitly called out as **unverified** in the how-to guide and as a visible banner on the admin page. The feature ships regardless (resolved, Q-053-01, Option A — see [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md)); T-053-24 provides the empirical follow-up evidence rather than blocking this feature. | `memprof_enable()`/`memprof_disable()` semantics are documented against a traditional per-request PHP-FPM/CLI lifecycle; Octane's persistent worker model is a materially different execution environment that the upstream project does not document. | Manual test task in `tasks.md` (T-053-24) that runs the same route twice in the same Octane worker and diffs the two trace dumps for cross-contamination. | `laravel/octane`, `dunglas/frankenphp` (already-installed default runtime). | Discovered during spec research (Dockerfile default `CMD` runs `octane:start --server=frankenphp`); ADR-0008. |

## UI / Interaction Mock-ups

```
GET /admin/profiler
+----------------------------------------------------------------------------------+
| Memory Profiler                                              [ Prune old traces ]|
|------------------------------------------------------------------------------------|
| ⚠ Traces captured while running under Octane/FrankenPHP may be unreliable — see    |
|   the how-to guide. (shown only when app is detected running under Octane)         |
|------------------------------------------------------------------------------------|
| Captured at         | Route                | Method | Status | Peak mem | Size | ⋮  |
|----------------------|-----------------------|--------|--------|----------|------|----|
| 2026-07-28 10:14:02  | gallery.index         | GET    | 200    | 42.1 MB  | 88KB |[view]|
| 2026-07-28 10:13:57  | api.v2.photo.upload   | POST   | 201    | 118.4 MB | 210KB|[view]|
| 2026-07-28 10:13:40  | gallery.album.show    | GET    | 200    | 39.8 MB  | 76KB |[view]|
|                                                                                      |
| (empty state, shown when storage/profiling has no traces yet)                      |
| "No traces collected yet. Make sure MEMORY_PROFILER_ENABLED=true and the memprof   |
|  extension is loaded — see the how-to guide."                                      |
+----------------------------------------------------------------------------------+

GET /admin/profiler/{trace}/svg  (opened via [view] above, in a new tab/section)
+----------------------------------------------------------------------------------+
| ← Back to trace list              gallery.index · 2026-07-28 10:14:02 · 42.1 MB    |
|------------------------------------------------------------------------------------|
|                                                                                      |
|         [ rendered SVG call-graph from `pprof --svg`, scrollable/zoomable ]         |
|                                                                                      |
|------------------------------------------------------------------------------------|
| (error state, shown when `pprof`/Graphviz are not installed)                        |
| "Could not render this trace: the `pprof` CLI (or Graphviz `dot`) is not installed  |
|  on this server. See the how-to guide to install it. Raw dump: [download .pprof]"   |
+----------------------------------------------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-053-01 | Feature flag off → middleware no-ops; `/admin/profiler*` returns 501 (`feature` middleware). |
| S-053-02 | Feature flag on, `memprof` extension not loaded → middleware no-ops (logs nothing, no error); admin page still reachable and shows the "no traces / check extension" empty state. |
| S-053-03 | Feature flag on, extension loaded, normal request → trace pair (`.pprof` + `.json`) written to `storage/profiling` after the response is sent. |
| S-053-04 | Trace write fails (disk full/permissions) → response to the original request is unaffected; failure logged. |
| S-053-05 | Owner visits `/admin/profiler` with zero traces present → empty state rendered, no error. |
| S-053-06 | Owner visits `/admin/profiler` with N traces present → table of N rows, newest first. |
| S-053-07 | Owner opens a trace's SVG view and `pprof`/Graphviz are installed → SVG renders inline. |
| S-053-08 | Owner opens a trace's SVG view and `pprof`/Graphviz are missing → clear error state, no 500. |
| S-053-09 | Unauthenticated visitor requests `/admin/profiler` → redirected to login. |
| S-053-10 | Authenticated non-owner requests `/admin/profiler` → `403 Unauthorized`. |
| S-053-11 | Owner requests `/admin/profiler/{trace}/svg` with a path-traversal payload as `{trace}` → `404`, no filesystem access outside `storage/profiling`. |
| S-053-12 | Trace count exceeds `MEMORY_PROFILER_MAX_TRACES` → oldest trace pair(s) pruned automatically (Q-053-03). |

## Test Strategy

- **Application/Middleware:** Feature tests for `MemoryProfiler` covering S-053-01..04, using a test double / conditional skip for the real `memprof_*` calls (see Q-053-01/plan for how extension-dependent assertions are isolated so the suite still runs green on CI images without the extension installed).
- **REST/Web (Blade routes):** Feature tests (`BaseApiWithDataTest`/`AbstractTestCase` equivalents for web routes) for S-053-05..11: empty listing, populated listing, SVG success (mocking the `pprof` shell-out), SVG missing-binary error state, unauthenticated redirect, non-owner 403, path-traversal 404.
- **CLI:** Feature/unit test for the pruning console command (S-053-12): seed N+k fake trace pairs, run the command, assert only the newest N remain.
- **Docs/Contracts:** New how-to guide (`enable-memory-profiler.md`) reviewed for accuracy against the real `memprof` API (already verified against upstream `memprof.stub.php` during spec research); roadmap and knowledge-map updated per Documentation Deliverables below.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-053-01 | `ProfilingTraceMeta` — JSON sidecar fields: `route_name` (string\|null), `method` (string), `path` (string), `status_code` (int), `duration_ms` (float), `peak_memory_bytes` (int), `user_id` (int\|null), `created_at` (ISO-8601 string). | application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-053-01 | Web GET `/admin/profiler` | Lists all traces. | `owner`, `feature:memory-profiler`, `login_required:always` middleware. Blade response, not JSON. |
| API-053-02 | Web GET `/admin/profiler/{trace}/svg` | Renders the SVG call-graph for one trace. | Same middleware stack; `{trace}` resolved via allow-list (NFR-053-04). |
| API-053-03 | Web POST `/admin/profiler/prune` | Manually triggers the pruning step from the admin page's "Prune old traces" button. | Same middleware stack. |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-053-01 | `php artisan lychee:profiler:prune` | Deletes the oldest trace pairs beyond `MEMORY_PROFILER_MAX_TRACES`, callable manually or from the schedule (`app/Console/Kernel.php`). |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-053-01 | `memory_profiler.dump_failed` | `route`, `exception_message` (log channel only, not persisted as a DB event). |
| TE-053-02 | `memory_profiler.svg_render_failed` | `trace_file`, `reason` (`binary_missing`\|`process_error`). |
| TE-053-03 | `memory_profiler.pruned` | `removed_count`, `remaining_count`. |

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-053-01 | `tests/Fixtures/Profiling/sample.pprof` | A small, static, valid pprof-format dump used to test the listing/SVG-rendering code paths without requiring the real `memprof` extension in CI. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-053-01 | Empty trace list | No files in `storage/profiling` → guidance banner (extension/flag check). |
| UI-053-02 | Populated trace list | ≥1 trace pair present → table, newest first. |
| UI-053-03 | SVG render success | `pprof`/Graphviz present → inline SVG. |
| UI-053-04 | SVG render failure | Missing binaries or process error → explanatory error state + raw-download link. |
| UI-053-05 | Octane risk banner | App detected running under Octane → persistent warning banner on the listing page (Q-053-01, ADR-0008). |

## Telemetry & Observability

All events in this feature are **log-channel only** (Laravel's standard logger), not the DB-backed telemetry/event pipeline used by other features (e.g. NSFW detections) — there is no user-facing analytics need here, only operator-facing diagnostics. No PII beyond `user_id` (already present in every other Lychee log line) is recorded.

## Documentation Deliverables

- New how-to guide: `docs/specs/2-how-to/enable-memory-profiler.md` (install the `memprof` PECL/PIE extension manually; `pprof`/`google-pprof` + Graphviz are already bundled in the official Docker image, with manual-install steps documented for bare-metal/custom installs; enable via `MEMORY_PROFILER_ENABLED`; Octane caveat).
- `docs/specs/4-architecture/roadmap.md`: new row under Active Features for 053.
- `docs/specs/4-architecture/knowledge-map.md`: new entry describing the global profiling middleware and the owner-only admin surface.
- `.env.example`: new `MEMORY_PROFILER_ENABLED`, `MEMORY_PROFILER_MAX_TRACES`, `MEMORY_PROFILER_PPROF_BIN` entries (commented out / defaulted off, matching the style of `LOG_VIEWER_ENABLED`, `WHITE_LABEL_ENABLED`, etc.).
- `Dockerfile`: add `google-perftools` + `graphviz` to the existing `apt-get install` block (around line 77) so `pprof --svg` works out of the box on the official image (Q-053-02).
- [ADR-0008](../../../6-decisions/ADR-0008-memory-profiler-octane-risk.md): records the decision to ship this feature under the default Octane/FrankenPHP runtime with a documented risk banner rather than blocking or hard-disabling it (Q-053-01).

## Fixtures & Sample Data

See FX-053-01 above.

## Spec DSL

```
domain_objects:
  - id: DO-053-01
    name: ProfilingTraceMeta
    fields:
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
    method: GET
    path: /admin/profiler/{trace}/svg
  - id: API-053-03
    method: POST
    path: /admin/profiler/prune
cli_commands:
  - id: CLI-053-01
    command: php artisan lychee:profiler:prune
telemetry_events:
  - id: TE-053-01
    event: memory_profiler.dump_failed
  - id: TE-053-02
    event: memory_profiler.svg_render_failed
  - id: TE-053-03
    event: memory_profiler.pruned
fixtures:
  - id: FX-053-01
    path: tests/Fixtures/Profiling/sample.pprof
ui_states:
  - id: UI-053-01
    description: Empty trace list guidance banner
  - id: UI-053-02
    description: Populated trace list table
  - id: UI-053-03
    description: SVG render success
  - id: UI-053-04
    description: SVG render failure with raw-download fallback
  - id: UI-053-05
    description: Octane risk banner
```

## Appendix

### Corrected reference snippet

The real, verified `memprof` usage pattern (procedural, not the OOP snippet originally supplied):

```php
if (!\function_exists('memprof_enable')) {
    return $next($request); // extension not loaded — no-op
}

memprof_enable();

/** @var \Illuminate\Http\Response $response */
$response = $next($request);

// ... later, in terminate($request, $response) ...
memprof_disable();
$handle = fopen(storage_path('profiling/' . $filename . '.pprof'), 'w');
memprof_dump_pprof($handle);
fclose($handle);
```

Rendering the SVG (shelled out, not a library call — `pprof` itself invokes Graphviz `dot`):

```bash
pprof --svg storage/profiling/2026-07-28_101402_ab12cd.pprof > storage/profiling/2026-07-28_101402_ab12cd.svg
```

### Why the trace format is pprof, not callgrind

`memprof_dump_callgrind()` targets KCacheGrind/QCacheGrind (desktop GUI apps) and cannot be rendered to SVG directly. `memprof_dump_pprof()` targets the `pprof`/`google-pprof` CLI, which supports `--svg` output — the only realistic path to "open each trace as svg" from a web admin page (see Q-053-02).
