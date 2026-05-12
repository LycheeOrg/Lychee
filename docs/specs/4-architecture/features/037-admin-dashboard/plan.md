# Feature Plan 037 – Admin Dashboard & `/admin/` URL Reorganisation

_Linked specification:_ [`docs/specs/4-architecture/features/037-admin-dashboard/spec.md`](spec.md)
_Status:_ Draft
_Last updated:_ 2026-04-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) have been updated.

## Vision & Success Criteria

An admin lands on a single `/admin` page that shows an at-a-glance health overview and a localised tile grid of every tool they are authorised to reach. The admin submenu collapses to one link when the new `use_admin_dashboard` setting is enabled (default ON), while partial-admins still reach the single tool(s) they are allowed to use. The nine admin-only screens live under `/admin/<slug>` URLs and matching `resources/js/views/admin/` files. Diagnostics (`/diagnostics`) and Logs (`/Logs`) are untouched.

**Success signals:**
- New config migration seeds `use_admin_dashboard = 1` under `cat = 'config'`; booleans enforced via `BaseConfigMigration::BOOL`.
- `AdminStatsService` fetches seven metrics, caches them via `Cache::remember('admin.stats', 300, …)`, and supports force-refresh; unit tests cover hit/miss/partial-error paths.
- `GET /api/v2/Admin/Stats` returns a cache-served `AdminStatsResource` payload for full admins; returns 403 for partial-admins; Feature_v2 tests green.
- Router + views reorganised: `/admin/{settings,users,user-groups,purchasables,contact-messages,webhooks,moderation,maintenance,jobs}` resolve to views under `resources/js/views/admin/`; old top-level paths return 404; `/diagnostics` and `/Logs` are unchanged.
- `AdminDashboard.vue` renders tile grid gated per capability plus (for full admins only) the stats block with Refresh; Vitest covers default, partial-admin no-stats, refresh, and error-toast states.
- `useLeftMenu` composable branches on `lycheeStore.use_admin_dashboard`; when true, a single "Admin" entry appears for any `canSeeAdmin` user.
- Locale parity: `admin-dashboard.*`, toggle label, and tile labels present in all 22 `lang/*.json`.
- PHPStan 0 errors; php-cs-fixer clean; `npm run check` + `npm run format` clean; OpenAPI snapshot updated.

## Scope Alignment

- **In scope:**
  - Config migration adding `use_admin_dashboard` (bool, default `1`, cat `config`).
  - `AdminStatsOverview` DTO + `AdminStatsResource` (Spatie Data).
  - `AdminStatsService` + cache layer (`Cache::remember('admin.stats', 300)` + `Cache::forget` via `force=1`).
  - `Admin\AdminDashboardController::stats` route `GET /api/v2/Admin/Stats` gated on `settings.can_edit`.
  - Telemetry events `admin.stats.fetch` and `admin.stats.refresh` emitted from the controller/service.
  - Router reorganisation in `resources/js/router/routes.ts` — nine moved paths + new `/admin` entry.
  - Nine Vue views relocated into `resources/js/views/admin/`; new `AdminDashboard.vue`.
  - Left-menu composable branching on the new config flag; `LycheeStateStore` exposes `use_admin_dashboard`.
  - Translation keys across 22 `lang/*.json` files.
  - OpenAPI entry for the stats route.
  - Knowledge-map + roadmap updates.

- **Out of scope:**
  - Diagnostics and Logs page changes (URLs, views, routes).
  - Redesigning the nine moved pages beyond path/view location.
  - Event-driven stats invalidation (deferred — see Q-037-02 Option C follow-up).
  - Backwards-compatibility redirects from old top-level URLs (Q-037-07 resolved as greenfield).
  - Per-user dashboard preferences (tile reordering, hiding tiles).
  - Any new capability flags (we reuse the existing five).

## Dependencies & Interfaces

- **Laravel `Cache` facade** — backs `admin.stats` TTL cache.
- **Laravel `Log` facade** — used by the service when partial aggregation errors occur.
- **`App\Models\Configs`** — reads/writes the new `use_admin_dashboard` boolean.
- **`BaseConfigMigration::BOOL` / `cat`** — config migration convention (see [2026_04_12_000001_add_chunked_download_configs.php](../../../../database/migrations/2026_04_12_000001_add_chunked_download_configs.php)).
- **`App\Models\Photo`, `App\Models\Album`, `App\Models\User`** — `count()` aggregates.
- **`jobs` + `failed_jobs` tables** — queue stats via existing job tables (consistent with the current Jobs admin page).
- **`SettingsPolicy::CAN_EDIT`** — authorisation gate for `GET /api/v2/Admin/Stats`.
- **Left-menu composable** — [resources/js/composables/contextMenus/leftMenu.ts](../../../../resources/js/composables/contextMenus/leftMenu.ts) — already reads `clockwork_url` from `LycheeStateStore`; we add `use_admin_dashboard` through the same store.
- **`LycheeStateStore`** — [resources/js/stores/LycheeState.ts](../../../../resources/js/stores/LycheeState.ts) — needs a new boolean surfaced through the existing init/state service.
- **Init service** — whichever REST endpoint currently populates `LycheeStateStore` must include `use_admin_dashboard` in its payload.
- **Spatie Laravel Data** — `AdminStatsResource` extends `Data`.
- **PrimeVue Dialog / Tag / Button** — dashboard tiles + refresh button consistent with existing admin screens.
- **Vue Router** — `resources/js/router/routes.ts` imports to update.

## Assumptions & Risks

- **Assumptions:**
  - Existing `settings.can_edit` policy is authoritative for "full admin" — no new permission flag required.
  - Counting `Photo`, `Album`, `User` rows directly on a well-maintained instance stays under the 1500 ms cache-miss budget (NFR-037-01). If not, I2 will be re-evaluated to use `album_size_statistics` or batched counts.
  - `LycheeStateStore` can surface one additional boolean through the existing state endpoint without a schema-breaking change.
  - The `jobs` and `failed_jobs` tables are reachable in every supported deployment (SQLite, MySQL, PostgreSQL). Feature 002 (Worker Mode) requires this already.
  - No existing route paths already live under `/admin/*` in the UI (confirmed by current `routes.ts`).

- **Risks / Mitigations:**
  - *Row counts are slow on very large libraries:* Cache layer mitigates repeated cost; if the initial miss exceeds budget, fall back to `COUNT(*)` on indexed `id` columns (tests assert latency budget in NFR-037-01).
  - *Router move breaks deep links in user bookmarks:* Accepted per Q-037-07 (greenfield). Release notes should call it out explicitly — captured as a doc follow-up in I7.
  - *Dashboard tile gating diverges from left-menu gating:* Mitigated by centralising capability→tile mapping in a single TypeScript composable (`useAdminDashboard`) that both `AdminDashboard.vue` and any future consumer can share.
  - *Partial-admin reaching the dashboard with zero tiles:* Guarded by the existing `canSeeAdmin` composite; a user reaches `canSeeAdmin === true` iff at least one of the five flags is true, so they always see at least one tile. Feature_v2 regression asserts this invariant.
  - *Stats endpoint leaks counts under slow responses (timing channel):* Not in scope — no secret data in aggregate counts.

## Implementation Drift Gate

After each increment, run the narrowest applicable gate first, then the full pipeline before committing the slice:

1. `php artisan test --filter=Admin` — admin-area tests.
2. `make phpstan` — 0 errors.
3. `vendor/bin/php-cs-fixer fix --dry-run` — no fixable issues.
4. `npm run check` — Vue component + type + eslint suite.
5. `npm run format --check` — Prettier clean.

Record any drift (scope change, unresolved failure, deferred test) in the plan's Follow-ups section and, if it changes observable behaviour, log a new open question and pause until the spec is updated.

## Increment Map

1. **I1 – Config migration for `use_admin_dashboard`**
   - _Goal:_ Add the admin toggle to the `configs` table with default `1`.
   - _Preconditions:_ None.
   - _Steps:_
     - Create `database/migrations/<date>_add_use_admin_dashboard_config.php` extending `BaseConfigMigration`, `public const CAT = 'config'`, inserts key `use_admin_dashboard`, `type_range: BOOL`, `value: '1'`, description "Use new admin dashboard and collapsed admin menu", details per spec appendix.
     - Surface the new boolean through `LycheeStateStore` (add key to the resource feeding the store; reuse existing init/state pattern).
     - Write a small config-presence feature test (`tests/Feature_v2/Admin/ConfigTogglePresentTest.php`) asserting the key exists and default is `true`.
   - _Commands:_ `php artisan test --filter=ConfigTogglePresentTest && make phpstan && vendor/bin/php-cs-fixer fix --dry-run`
   - _Exit:_ Migration runs on fresh SQLite; config key readable; PHPStan clean.

2. **I2 – `AdminStatsOverview` DTO + `AdminStatsService` + cache**
   - _Goal:_ Deliver the aggregation + caching layer powering the stats endpoint.
   - _Preconditions:_ I1 complete.
   - _Steps:_
     - Create `app/DTO/AdminStatsOverview.php` (plain PHP DTO, all fields readonly) per DO-037-01: `photos_count`, `albums_count`, `users_count`, `storage_bytes`, `queued_jobs`, `failed_jobs_24h`, `last_successful_job_at` (nullable), `cached_at`, `errors` (array).
     - Write `tests/Unit/Services/AdminStatsServiceTest.php` covering: cache hit (no requery), cache miss (requery + store), `force=true` bypass + forget, partial aggregation failure populates `errors[]` and is **not** stored in cache, all counts match seeded fixtures.
     - Implement `app/Services/AdminStatsService.php::getOverview(bool $force = false): AdminStatsOverview`. Force path calls `Cache::forget('admin.stats')` before `Cache::remember('admin.stats', 300, …)`. Each metric is computed in its own `try/catch`; on throw, it is pushed to `$errors[]` and the closure returns a non-stored payload (`Cache::remember` short-circuits storage via a second path — or equivalently, compute first and `Cache::put` only when `$errors` empty).
   - _Commands:_ `php artisan test --filter=AdminStatsServiceTest && make phpstan`
   - _Exit:_ Service returns correct values for seeded fixtures; cache semantics verified; PHPStan clean.

3. **I3 – REST endpoint `GET /api/v2/Admin/Stats` + Feature_v2 tests**
   - _Goal:_ Ship API-037-01 with the right auth gate and telemetry.
   - _Preconditions:_ I2 complete.
   - _Steps:_
     - Create `app/Http/Resources/Models/AdminStatsResource.php` (Spatie Data) mirroring `AdminStatsOverview` fields.
     - Create `app/Http/Controllers/Admin/AdminDashboardController.php::stats(AdminStatsRequest $request)`. Request class applies `SettingsPolicy::CAN_EDIT`. Emits `admin.stats.fetch` with `cache_hit`, `duration_ms`, `errors_count`; emits `admin.stats.refresh` with `user_id_hash`, `duration_ms` when `force=1`.
     - Register route `GET /api/v2/Admin/Stats` in `routes/api_v2.php` alongside the existing admin routes (line ~200 area).
     - Write `tests/Feature_v2/Admin/AdminStatsControllerTest.php` covering S-037-01 (full admin 200), S-037-02 (non-admin 403), S-037-09 (cache hit emits `cache_hit=true`), S-037-10 (force=1 recomputes, emits `cache_hit=false` + `admin.stats.refresh`), S-037-11 (partial error → 200 with `errors[]`, cache not stored), S-037-17 (partial-admin 403).
     - Assert latency budget per NFR-037-01.
   - _Commands:_ `php artisan test --filter=AdminStatsControllerTest && make phpstan && vendor/bin/php-cs-fixer fix --dry-run`
   - _Exit:_ All six scenarios green; PHPStan 0; route visible via `php artisan route:list | grep Admin/Stats`.

4. **I4 – Router + view file relocation**
   - _Goal:_ Move the nine admin-only views into `resources/js/views/admin/`, wire new `/admin/<slug>` paths, and add the dashboard shell.
   - _Preconditions:_ Independent of I1–I3 (can run in parallel with I2/I3 once I1 is merged).
   - _Steps:_
     - Create `resources/js/views/admin/` folder.
     - Move nine files into `admin/`: `Settings.vue`, `Users.vue`, `UserGroups.vue`, `Purchasables.vue` (currently under `views/webshop/PurchasablesList.vue` — confirm path and rename accordingly), `ContactMessages.vue`, `Webhooks.vue`, `Moderation.vue`, `Maintenance.vue`, `Jobs.vue`. Keep original Vue component names unchanged to minimise diff; only the import paths move.
     - Create a placeholder `resources/js/views/admin/AdminDashboard.vue` (real implementation in I5) — enough to make the router test pass.
     - Update `resources/js/router/routes.ts`: rewrite the nine paths to `/admin/<slug>`, add `name: "admin-dashboard", path: "/admin"`, update dynamic import paths to `@/views/admin/…`.
     - Grep-replace `route: "/settings"`, `route: "/users"`, etc. in left-menu composable to the new paths (the deeper refactor happens in I6; here we just keep the existing menu compilable).
     - Write a router-level Vitest `resources/js/router/__tests__/admin-routes.test.ts` asserting: `/admin` resolves to `AdminDashboard`; each `/admin/<slug>` resolves to its component; `/diagnostics` and `/Logs` unchanged; old top-level paths (`/settings`, `/users`, …) no longer match any route (return to `NoMatch`/404).
   - _Commands:_ `npm run check`
   - _Exit:_ Build passes; router assertions green; existing Vue components render with new import paths.

5. **I5 – `AdminDashboard.vue` (tile grid + stats block + Refresh)**
   - _Goal:_ Build the dashboard view honouring capability gating (UI-037-01, UI-037-01a, UI-037-02, UI-037-03).
   - _Preconditions:_ I3 + I4 complete.
   - _Steps:_
     - Create `resources/js/services/admin-stats-service.ts` exposing `getStats(force: boolean = false): Promise<AxiosResponse<AdminStatsResource>>`.
     - Update `resources/js/lychee.d.ts` to include `App.Http.Resources.Models.AdminStatsResource`.
     - Create a shared composable `resources/js/composables/useAdminTiles.ts` returning the ordered list of admin tiles with `{ label, icon, to, visible }` computed from `initData` + `lycheeStore`. Capability mapping from spec FR-037-04.
     - Write Vitest tests `resources/js/views/admin/__tests__/AdminDashboard.test.ts`:
       - UI-037-01: full-admin render → stats block visible, 12 tiles visible.
       - UI-037-01a: partial-admin (e.g., only `can_acess_user_groups`) → no stats block, only User Groups tile.
       - UI-037-02: Refresh click disables the button and calls service with `force=true`.
       - UI-037-03: service returns `errors[]` → toast surfaces the error messages; stats values shown where present.
       - S-037-13: first tile focusable via Tab, Enter triggers navigation.
     - Implement `AdminDashboard.vue`: PrimeVue grid of tiles, `<Button>` Refresh gated on `settings.can_edit`, stats block gated on `settings.can_edit`, `Tag` components showing each metric.
   - _Commands:_ `npm run check`
   - _Exit:_ All Vitest cases green; TypeScript 0 errors; ESLint clean.

6. **I6 – Left-menu composable branch + 22-locale parity**
   - _Goal:_ Wire the toggle into the left menu and localise every new string.
   - _Preconditions:_ I1 + I4 + I5 complete.
   - _Steps:_
     - Extend `LycheeStateStore` with `use_admin_dashboard: boolean`; thread it through the existing state init service resource.
     - Update `resources/js/composables/contextMenus/leftMenu.ts`: when `use_admin_dashboard` is true, replace the `items` entry for `"left-menu.admin"` with a flat entry `{ label: "left-menu.admin", icon: "cog", route: "/admin", access: canSeeAdmin.value }`. When false, keep the current nested submenu but update each `route` to its new `/admin/<slug>` path.
     - Add `admin-dashboard.title`, `admin-dashboard.overview`, `admin-dashboard.tools`, `admin-dashboard.refresh`, `admin-dashboard.metrics.<key>` keys (one per metric) and `settings.config.use_admin_dashboard.*` label/help to **every** `lang/*.json` file (22 languages). English is authoritative; other locales may use the English string as a placeholder translation that translators can refine later (match existing project convention for new keys).
     - Write Vitest test `resources/js/composables/__tests__/leftMenu.admin.test.ts` covering S-037-04 (toggle ON → single link), S-037-05 (toggle OFF → nested submenu with new paths), S-037-16 (partial-admin toggle ON → still sees collapsed link), S-037-18 (partial-admin toggle OFF → only their authorised nested entry).
   - _Commands:_ `npm run check && npm run format`
   - _Exit:_ Composable tests green; locale JSON parity script (`npm run check`) passes; PHPStan-free (no backend changes here beyond state resource).

7. **I7 – Quality gates, OpenAPI, knowledge map, roadmap**
   - _Goal:_ Close out with the full pipeline, contract snapshot, and docs.
   - _Preconditions:_ I1–I6 complete.
   - _Steps:_
     - Regenerate/edit OpenAPI snapshot for `GET /api/v2/Admin/Stats` and run the OpenAPI snapshot test (`tests/Feature_v2/OpenApiTest.php`).
     - Update `docs/specs/4-architecture/knowledge-map.md`: add (a) `AdminStatsService` + `admin.stats` cache key under Application Layer, (b) `AdminDashboardController` under Controllers, (c) `resources/js/views/admin/` under Views, (d) `useAdminTiles` composable.
     - Add a short operator note `docs/specs/2-how-to/admin-dashboard.md` describing the toggle and dashboard (reference from spec's Documentation Deliverables).
     - Run the full pipeline: `vendor/bin/php-cs-fixer fix && php artisan test && make phpstan && npm run format && npm run check`.
     - Move Feature 037 from "Active Features" to "Completed Features" in [docs/specs/4-architecture/roadmap.md](../../roadmap.md).
   - _Commands:_ see above.
   - _Exit:_ Full pipeline green; OpenAPI diff committed; knowledge-map + roadmap updated; ready for RCI self-review and commit handoff.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-037-01 | I3 (AdminStatsControllerTest — admin path), I5 (Vitest default render) | Full-admin dashboard + stats happy path. |
| S-037-02 | I3 (403 test), I5 (route guard test) | Non-admin denied. |
| S-037-03 | I5 (Refresh Vitest), I3 (force=1 telemetry test) | Refresh busts cache. |
| S-037-04 | I6 (leftMenu test — toggle ON) | Collapsed menu. |
| S-037-05 | I6 (leftMenu test — toggle OFF) | Legacy submenu. |
| S-037-06 | I4 (router test) | `/admin/<slug>` resolves. |
| S-037-07 | I4 (router test) | `/diagnostics` unchanged. |
| S-037-08 | I4 (router test) | `/Logs` unchanged. |
| S-037-09 | I3 (cache hit test) | Cache within TTL. |
| S-037-10 | I3 (force=1 test) | Cache busted / recomputed. |
| S-037-11 | I3 (partial error test) | `errors[]` path, cache not stored. |
| S-037-12 | I6 (locale parity check) | All 22 locales carry the new keys. |
| S-037-13 | I5 (keyboard-nav test) | Tab+Enter activates tiles. |
| S-037-14 | I4 (router test — old paths 404) | Greenfield switch. |
| S-037-15 | I5 (tile gating test — module flag) | Webshop-disabled hides Purchasables tile. |
| S-037-16 | I5 (partial-admin render), I6 (composable partial-admin test) | Groups-only operator. |
| S-037-17 | I3 (partial-admin 403 test) | Direct stats call denied. |
| S-037-18 | I6 (composable partial-admin OFF test) | Legacy submenu with single capability. |

## Analysis Gate

_Not yet completed — pending start of implementation. Run [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md) once this plan + tasks.md agree and the sibling spec is stable._

## Exit Criteria

- [`php artisan test`](../../../../tests) full suite green.
- `make phpstan` — 0 errors.
- `vendor/bin/php-cs-fixer fix --dry-run` — clean.
- `npm run check` — TypeScript, ESLint, and Vitest green.
- `npm run format --check` — Prettier clean.
- OpenAPI snapshot updated and committed.
- Knowledge-map updated.
- Roadmap row moved to Completed.
- Release notes flag the greenfield URL change for operator awareness.

## Follow-ups / Backlog

- (Q-037-02 follow-up) Revisit event-driven stats invalidation (Option C variant) if operators demand fresher numbers than 5 minutes.
- Investigate a per-user "pinned tools" preference for the dashboard.
- Consider a CLI command `php artisan lychee:admin-stats --refresh` for scripted cache warming.
- If row-count latency regresses on very large libraries (NFR-037-01 miss budget), switch counters to `album_size_statistics` / denormalised counters similar to Feature 003/004.
- Release-notes entry listing the nine moved URLs so operators can update bookmarks.
