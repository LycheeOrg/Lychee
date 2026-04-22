# Feature 037 Tasks – Admin Dashboard & `/admin/` URL Reorganisation

_Status: Draft_  
_Last updated: 2026-04-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-037-`), NFR IDs (`NFR-037-`), and scenario IDs (`S-037-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Config migration for `use_admin_dashboard`

- [ ] T-037-01 – Create config migration for `use_admin_dashboard` (FR-037-03, Q-037-06).  
  _Intent:_ New `database/migrations/<date>_add_use_admin_dashboard_config.php` extending `BaseConfigMigration`, `public const CAT = 'config'`, inserting the key `use_admin_dashboard` with `type_range: self::BOOL`, `value: '1'`, `is_secret: false`, `is_expert: false`, `level: 0`, description "Use new admin dashboard and collapsed admin menu". `down()` removes the row.  
  _Verification commands:_
  - `php artisan migrate:fresh --env=testing`
  - `php artisan test --filter=ConfigTogglePresentTest` (added in T-037-02)
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Mirrors pattern from [2026_04_12_000001_add_chunked_download_configs.php](../../../../database/migrations/2026_04_12_000001_add_chunked_download_configs.php).

- [ ] T-037-02 – Write `tests/Feature_v2/Admin/ConfigTogglePresentTest.php` (RED) then run migration to green (FR-037-03, S-037-04, S-037-05).  
  _Intent:_ Feature test seeds the fresh DB and asserts (a) row exists for `key = 'use_admin_dashboard'`, (b) default value is the string `'1'` (truthy boolean), (c) category is `'config'`, (d) `Configs::getValueAsBool('use_admin_dashboard')` returns `true`.  
  _Verification commands:_
  - `php artisan test --filter=ConfigTogglePresentTest`

- [ ] T-037-03 – Surface `use_admin_dashboard` through `LycheeStateStore` resource (FR-037-03, S-037-04, S-037-05).  
  _Intent:_ Locate the backend resource feeding `LycheeStateStore` (likely an `InitResource` or similar — trace via `LycheeState.ts`). Add `use_admin_dashboard: bool` to its public Data fields, sourced from `Configs::getValueAsBool('use_admin_dashboard')`. Extend `resources/js/stores/LycheeState.ts` state type with the new key and initialise it from the init payload. No new REST endpoint — reuse the existing one.  
  _Verification commands:_
  - `php artisan test --filter=Init` (or whichever test covers the state resource)
  - `make phpstan`
  - `npm run check`  
  _Notes:_ Keep the field optional in TS defaults so older cached stores fall back to legacy behaviour if the key is missing.

### I2 – `AdminStatsOverview` DTO + `AdminStatsService` + cache

- [ ] T-037-04 – Create `app/DTO/AdminStatsOverview.php` (DO-037-01).  
  _Intent:_ Immutable DTO with readonly public constructor properties: `photos_count: int`, `albums_count: int`, `users_count: int`, `storage_bytes: int`, `queued_jobs: int`, `failed_jobs_24h: int`, `last_successful_job_at: ?string` (ISO-8601), `cached_at: string` (ISO-8601), `errors: array` (list of strings). Pure PHP DTO — no Spatie Data extension.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-037-05 – Write `tests/Unit/Services/AdminStatsServiceTest.php` (RED) covering all cache + error paths (API-037-01, NFR-037-01, NFR-037-02, S-037-09, S-037-10, S-037-11, Q-037-02).  
  _Intent:_ Cases:
  1. Cache miss → computes all seven metrics, stores under `admin.stats` with 300s TTL, `cached_at` is current time.
  2. Cache hit → returns stored overview without re-running queries (assert via `Cache::shouldReceive('remember')` spy or by seeding cache and mutating underlying models).
  3. `$force = true` → calls `Cache::forget('admin.stats')` before recompute.
  4. Partial aggregation failure (e.g., simulate `Photo::count()` throw) → overview returned with matching entry in `errors[]`, result **not** stored in cache (next call re-runs).
  5. Metric correctness: seed 3 photos, 2 albums, 2 users, 1 queued + 1 failed-24h job → assert exact counts; `storage_bytes` computed from `size_variants` summed bytes.
  6. Latency budget: assert `microtime(true)` delta < 1500 ms on cache-miss path (NFR-037-01).  
  _Verification commands:_
  - `php artisan test --filter=AdminStatsServiceTest`

- [ ] T-037-06 – Implement `app/Services/AdminStatsService.php::getOverview(bool $force = false): AdminStatsOverview` (API-037-01, NFR-037-01, NFR-037-02).  
  _Intent:_ On `$force === true`, `Cache::forget('admin.stats')`. Compute each metric in its own `try/catch`, capture `$errors[]` on throw and log via `Log::warning`. If `$errors` is empty → `Cache::put('admin.stats', $overview, 300)` and return. If `$errors` non-empty → return overview **without** caching. Use `storage_bytes = SizeVariant::sum('filesize')` (or closest equivalent in current schema). `last_successful_job_at` = `Jobs::where('status','success')->max('finished_at')` with null-safety.  
  _Verification commands:_
  - `php artisan test --filter=AdminStatsServiceTest`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Keep the closure passed to `Cache::remember` free of telemetry so the controller owns `admin.stats.fetch` emission.

### I3 – REST endpoint `GET /api/v2/Admin/Stats` + Feature_v2 tests

- [ ] T-037-07 – Create `app/Http/Resources/Models/AdminStatsResource.php` (API-037-01, DO-037-01).  
  _Intent:_ Spatie `Data` resource mirroring `AdminStatsOverview` fields 1:1. Static factory `fromOverview(AdminStatsOverview $o): self`.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-037-08 – Create `app/Http/Requests/Admin/AdminStatsRequest.php` with `SettingsPolicy::CAN_EDIT` authorisation (API-037-01, NFR-037-03, S-037-02, S-037-17).  
  _Intent:_ Extends `BaseApiRequest`; overrides `authorize()` to call `Gate::authorize(SettingsPolicy::CAN_EDIT)`. Validates optional `force` query parameter (`sometimes|boolean`).  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-037-09 – Write `tests/Feature_v2/Admin/AdminStatsControllerTest.php` (RED) covering six scenarios (S-037-01, S-037-02, S-037-09, S-037-10, S-037-11, S-037-17, NFR-037-01).  
  _Intent:_ Cases:
  1. S-037-01 – Full admin (`settings.can_edit = true`) → 200 with all seven metric fields present.
  2. S-037-02 – Unauthenticated → 401; authenticated non-admin (no `settings.can_edit`) → 403.
  3. S-037-09 – Two calls within TTL → second returns cached payload; telemetry `cache_hit=true` on second.
  4. S-037-10 – `?force=1` → forces recompute; emits `admin.stats.refresh` with `duration_ms`, `user_id_hash`.
  5. S-037-11 – Simulate partial error (mock service to inject `errors[]`) → 200 with `errors` array non-empty; cache not populated (assert by calling again without `force` → recomputes).
  6. S-037-17 – Partial-admin holding only `can_acess_user_groups` → 403 (endpoint gated on full admin only).
  7. NFR-037-01 – Assert response latency < 1500 ms on cache-miss path.  
  _Verification commands:_
  - `php artisan test --filter=AdminStatsControllerTest`

- [ ] T-037-10 – Implement `app/Http/Controllers/Admin/AdminDashboardController.php::stats` and register the route (FR-037-02, API-037-01, NFR-037-03).  
  _Intent:_ Controller method signature: `stats(AdminStatsRequest $request): AdminStatsResource`. Reads `$force = $request->boolean('force')`. Records `$start = microtime(true)`, calls `$this->service->getOverview($force)`, wraps in `AdminStatsResource::fromOverview($overview)`. Emits telemetry events:
  - `admin.stats.fetch` → `{ cache_hit: bool, duration_ms: int, errors_count: int }`
  - `admin.stats.refresh` → `{ user_id_hash: string, duration_ms: int }` (only when `$force === true`)
  Register `Route::get('/Admin/Stats', [AdminDashboardController::class, 'stats'])->middleware('auth')` in `routes/api_v2.php` alongside other admin routes.  
  _Verification commands:_
  - `php artisan test --filter=AdminStatsControllerTest`
  - `php artisan route:list | grep 'Admin/Stats'` (manual check)
  - `make phpstan`

### I4 – Router + view file relocation

- [ ] T-037-11 – Write router Vitest `resources/js/router/__tests__/admin-routes.test.ts` (RED) (FR-037-05, S-037-06, S-037-07, S-037-08, S-037-14).  
  _Intent:_ Uses `createRouter({ history: createMemoryHistory(), routes })`. Asserts:
  - `/admin` resolves to route `name: 'admin-dashboard'`.
  - Each `/admin/<slug>` resolves to the correct component for the nine moved pages.
  - `/diagnostics` still resolves to `Diagnostics` (unchanged — S-037-07).
  - `/Logs` still resolves to `Logs` (unchanged — S-037-08).
  - Old top-level paths `/settings`, `/users`, `/user-groups`, `/purchasables`, `/contact-messages`, `/webhooks`, `/moderation`, `/maintenance`, `/jobs` no longer match any route (resolve to 404 / NoMatch — S-037-14).  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-12 – Create `resources/js/views/admin/` and relocate the nine admin views (FR-037-05, UI-037-04).  
  _Intent:_ Move the following files into `resources/js/views/admin/` (adjust import paths inside each moved file if they break):
  - `Settings.vue`
  - `Users.vue`
  - `UserGroups.vue`
  - `Purchasables.vue` (currently `views/webshop/PurchasablesList.vue` — verify current location and rename consistently)
  - `ContactMessages.vue`
  - `Webhooks.vue`
  - `Moderation.vue`
  - `Maintenance.vue`
  - `Jobs.vue`
  
  Leave `Diagnostics.vue` and `Logs.vue` at their current top-level paths.  
  _Verification commands:_
  - `npm run check`
  - `git mv` used where applicable (preserve history).  
  _Notes:_ Do not rename the Vue components themselves — only the file location. Downstream imports will be fixed in T-037-14.

- [ ] T-037-13 – Create placeholder `resources/js/views/admin/AdminDashboard.vue` (UI-037-01).  
  _Intent:_ Minimal component shell exporting `<template><div class="admin-dashboard-shell"/></template>` so the router test can mount the route. Full UI lands in I5.  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-14 – Update `resources/js/router/routes.ts` with new paths + imports (FR-037-05, UI-037-04, S-037-06, S-037-14).  
  _Intent:_ Replace the nine flat paths with:
  - `path: '/admin'`, `name: 'admin-dashboard'`, component `AdminDashboard`
  - `path: '/admin/settings'` → `views/admin/Settings.vue`
  - `path: '/admin/users'` → `views/admin/Users.vue`
  - `path: '/admin/user-groups'` → `views/admin/UserGroups.vue`
  - `path: '/admin/purchasables'` → `views/admin/Purchasables.vue`
  - `path: '/admin/contact-messages'` → `views/admin/ContactMessages.vue`
  - `path: '/admin/webhooks'` → `views/admin/Webhooks.vue`
  - `path: '/admin/moderation'` → `views/admin/Moderation.vue`
  - `path: '/admin/maintenance'` → `views/admin/Maintenance.vue`
  - `path: '/admin/jobs'` → `views/admin/Jobs.vue`
  
  Leave `/diagnostics` and `/Logs` as-is.  
  _Verification commands:_
  - `npm run check`
  - Router Vitest from T-037-11 passes.

- [ ] T-037-15 – Fix any broken `@/views/*` imports after the move (supporting task for T-037-12).  
  _Intent:_ Global grep-replace of `@/views/Settings` → `@/views/admin/Settings` and the other eight moved components across the entire `resources/js/` tree (excluding the views themselves, which may need relative path tweaks).  
  _Verification commands:_
  - `npm run check`  
  _Notes:_ Do **not** change `@/views/Diagnostics` or `@/views/Logs`.

### I5 – `AdminDashboard.vue` (tile grid + stats block + Refresh)

- [ ] T-037-16 – Update `resources/js/lychee.d.ts` with `AdminStatsResource` namespace (API-037-01).  
  _Intent:_ Add `namespace App.Http.Resources.Models { interface AdminStatsResource { photos_count: number; albums_count: number; users_count: number; storage_bytes: number; queued_jobs: number; failed_jobs_24h: number; last_successful_job_at: string | null; cached_at: string; errors: string[]; } }`.  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-17 – Create `resources/js/services/admin-stats-service.ts` (API-037-01).  
  _Intent:_ Exports `AdminStatsService` with `getStats(force: boolean = false): Promise<AxiosResponse<App.Http.Resources.Models.AdminStatsResource>>` calling `GET ${Constants.getApiUrl()}Admin/Stats` with `{ params: { force: force ? 1 : undefined } }`.  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-18 – Create `resources/js/composables/useAdminTiles.ts` (FR-037-04, UI-037-01, UI-037-01a, S-037-15, S-037-16, Q-037-08).  
  _Intent:_ Export a composable returning `AdminTile[]` where each tile is `{ key: string; label: string (i18n key); icon: string; to: string; visible: ComputedRef<boolean> }`. Visibility mapping per FR-037-04:
  - `settings` → `settings.can_edit`
  - `users` → `user_management.can_edit`
  - `user-groups` → `settings.can_acess_user_groups`
  - `purchasables` → `settings.can_edit && initStore.modules.webshop` (S-037-15)
  - `contact-messages` → `settings.can_edit`
  - `webhooks` → `settings.can_edit`
  - `moderation` → `settings.can_edit`
  - `maintenance` → `settings.can_edit`
  - `jobs` → `settings.can_edit`
  - `diagnostics` → `settings.can_see_diagnostics`
  - `logs` → `settings.can_see_logs`
  - `clockwork` → `initStore.config.clockwork_url !== null && settings.can_edit`

- [ ] T-037-19 – Write `resources/js/views/admin/__tests__/AdminDashboard.test.ts` (RED) (UI-037-01, UI-037-01a, UI-037-02, UI-037-03, S-037-01, S-037-03, S-037-13, S-037-16).  
  _Intent:_ Cases:
  1. UI-037-01 – Full admin context (`settings.can_edit = true`) → stats block visible, all 12 tiles rendered (assuming webshop + clockwork available).
  2. UI-037-01a / S-037-16 – Partial admin with only `can_acess_user_groups` → no stats block, only User Groups tile visible.
  3. UI-037-02 / S-037-03 – Click Refresh → button becomes disabled, service called with `force = true`, stats re-rendered with new `cached_at`.
  4. UI-037-03 – Service resolves with `errors = ['queued_jobs failure']` → toast emitted with error message; stats still rendered where values exist.
  5. S-037-13 – First tile focusable via Tab; Enter key triggers navigation to its `to` path.  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-20 – Implement `resources/js/views/admin/AdminDashboard.vue` to green the Vitest cases (UI-037-01, UI-037-01a, UI-037-02, UI-037-03).  
  _Intent:_ Layout: PrimeVue `<div class="admin-dashboard">` wrapper, `<h1>` with `t('admin-dashboard.title')`, stats `<section>` gated on `initStore.rights.settings.can_edit` showing one `<Tag>` per metric and a `<Button>` "Refresh" (disabled while pending), then a `<section>` with the tile grid driven by `useAdminTiles`. Each tile is a `<router-link>` styled as a card with icon + label. Error toast via existing PrimeVue `useToast()` when `errors[]` non-empty.  
  _Verification commands:_
  - `npm run check`

### I6 – Left-menu composable branch + 22-locale parity

- [ ] T-037-21 – Write `resources/js/composables/__tests__/leftMenu.admin.test.ts` (RED) (FR-037-03, FR-037-04, S-037-04, S-037-05, S-037-16, S-037-18, Q-037-08).  
  _Intent:_ Cases:
  1. S-037-04 – `lycheeStore.use_admin_dashboard = true` + `canSeeAdmin = true` → menu exposes one flat `{ route: '/admin', label: 'left-menu.admin', access: true }` entry; no nested submenu entries.
  2. S-037-05 – `use_admin_dashboard = false` + full admin → legacy nested submenu with each entry's route updated to `/admin/<slug>` (except Diagnostics `/diagnostics` and Logs `/Logs`).
  3. S-037-16 – `use_admin_dashboard = true` + partial admin (only `can_acess_user_groups`) → collapsed link still visible (`access: true` via `canSeeAdmin` composite).
  4. S-037-18 – `use_admin_dashboard = false` + partial admin (only `can_acess_user_groups`) → only User Groups entry in nested submenu, every other nested entry `access: false`.  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-22 – Update `resources/js/composables/contextMenus/leftMenu.ts` with the toggle branch (FR-037-03, FR-037-04, S-037-04, S-037-05, S-037-16, S-037-18).  
  _Intent:_ Add `use_admin_dashboard = computed(() => lycheeStore.use_admin_dashboard)` (with `??= true` default). In the admin section of `items`, branch: when `use_admin_dashboard.value === true`, emit a single flat entry `{ label: 'left-menu.admin', icon: 'cog', route: '/admin', access: canSeeAdmin.value }`; when `false`, keep the existing nested structure but rewrite each nested `route` from `/settings` → `/admin/settings` etc. (leave `/diagnostics` and `/Logs` untouched).  
  _Verification commands:_
  - `npm run check`

- [ ] T-037-23 – Add new i18n keys to English authoritative `lang/en.json` (FR-037-04, UI-037-04, UI-037-05, S-037-12).  
  _Intent:_ Add the following keys with authoritative English strings:
  - `admin-dashboard.title` = "Admin Dashboard"
  - `admin-dashboard.overview` = "Overview"
  - `admin-dashboard.tools` = "Tools"
  - `admin-dashboard.refresh` = "Refresh"
  - `admin-dashboard.metrics.photos_count` = "Photos"
  - `admin-dashboard.metrics.albums_count` = "Albums"
  - `admin-dashboard.metrics.users_count` = "Users"
  - `admin-dashboard.metrics.storage_bytes` = "Storage used"
  - `admin-dashboard.metrics.queued_jobs` = "Queued jobs"
  - `admin-dashboard.metrics.failed_jobs_24h` = "Failed jobs (24h)"
  - `admin-dashboard.metrics.last_successful_job_at` = "Last successful job"
  - `admin-dashboard.errors.partial` = "Some metrics could not be loaded."
  - `settings.config.use_admin_dashboard.label` = "Use admin dashboard"
  - `settings.config.use_admin_dashboard.help` = "Replace the nested admin submenu with a single link to the new admin dashboard page."  
  _Verification commands:_
  - `npm run check` (JSON parse)

- [ ] T-037-24 – Propagate new i18n keys to the remaining 21 locale files (S-037-12, NFR-037-04).  
  _Intent:_ For each `lang/*.json` except `en.json`, add all keys introduced in T-037-23. Per existing project convention, copy the English string as a placeholder translation — native translators will refine later.  
  _Verification commands:_
  - `npm run check`
  - Confirm locale count: files should each contain `admin-dashboard.title`. Spot-check three non-English files manually.  
  _Notes:_ The 22 locales are `ar`, `cn`, `de`, `en`, `es`, `fr`, `hu`, `id`, `it`, `ja`, `ko`, `nl`, `no`, `pl`, `pt`, `ro`, `ru`, `sk`, `sv`, `tw`, `uk`, `vi` (verify against repo).

### I7 – Quality gates, OpenAPI, knowledge map, roadmap

- [ ] T-037-25 – Regenerate OpenAPI snapshot for `GET /api/v2/Admin/Stats` (API-037-01).  
  _Intent:_ Add the route to the OpenAPI source (controller annotations / spec file — follow existing convention for admin routes). Run the OpenAPI snapshot test and commit the updated JSON/YAML.  
  _Verification commands:_
  - `php artisan test --filter=OpenApi`

- [ ] T-037-26 – Update `docs/specs/4-architecture/knowledge-map.md` (NFR-037-05).  
  _Intent:_ Add entries under the appropriate sections:
  - Application Layer → `AdminStatsService` + cache key `admin.stats`.
  - Controllers → `AdminDashboardController`.
  - Frontend Views → `resources/js/views/admin/` (new folder).
  - Composables → `useAdminTiles`.
  - Frontend Services → `admin-stats-service.ts`.  
  _Verification commands:_ manual review.

- [ ] T-037-27 – Add operator how-to `docs/specs/2-how-to/admin-dashboard.md` (NFR-037-05).  
  _Intent:_ Short operator-facing page describing the toggle (`use_admin_dashboard`), what collapses/expands, the nine moved URLs (with old → new mapping for bookmarks), and a note that Diagnostics + Logs are unchanged.  
  _Verification commands:_ manual review.

- [ ] T-037-28 – Run full PHP quality gate (exit criterion).  
  _Intent:_ Ensure I1–I3 and any backend touches in I6 have not regressed the suite.  
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-037-29 – Run full frontend quality gate (exit criterion).  
  _Intent:_ Ensure I4–I6 changes are typecheck/eslint/prettier-clean and Vitest is green.  
  _Verification commands:_
  - `npm run check`
  - `npm run format --check`

- [ ] T-037-30 – Move Feature 037 from Active → Completed in `docs/specs/4-architecture/roadmap.md`, update `_current-session.md` (NFR-037-05).  
  _Intent:_ Roadmap row migrates with completion date and a one-line summary of delivered scope. Session snapshot archives the feature.  
  _Verification commands:_ manual review.

## Notes / TODOs

- T-037-03 assumes the existing state-init resource can accept a new boolean without a breaking change; if the resource is covered by an OpenAPI snapshot test, regenerate it in T-037-25 alongside the stats route.
- T-037-12 relies on confirming the current path of the Purchasables view (likely `views/webshop/PurchasablesList.vue`); if naming differs, adjust the destination file name inside `views/admin/` accordingly.
- T-037-15 is a supporting task for the rename in T-037-12: if the project uses an `@/views/...` alias consistently, a single codebase-wide grep-replace is the quickest path.
- T-037-24 touches every locale file; consider splitting into two commits (authoritative English + bulk placeholder copies) to keep diffs reviewable. Placeholder policy matches current practice established by Feature 019 (Friendly URLs).
- If row counts in `AdminStatsService` regress beyond the 1500 ms budget (NFR-037-01), pivot to pre-computed counters (see Feature 003/004) in a follow-up rather than inflating the current scope.
- All open questions resolved (2026-04-22): Q-037-01..Q-037-08. See [open-questions.md](../../open-questions.md) for the closed trail.
