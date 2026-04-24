# Feature 037 – Admin Dashboard & `/admin/` URL Reorganisation

| Field | Value |
|-------|-------|
| Status | Ready for Planning |
| Last updated | 2026-04-22 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/037-admin-dashboard/plan.md` (TBD) |
| Linked tasks | `docs/specs/4-architecture/features/037-admin-dashboard/tasks.md` (TBD) |
| Roadmap entry | Active Features |

> Guardrail: Open questions Q-037-01 … Q-037-08 are all resolved (see [docs/specs/4-architecture/open-questions.md](../../open-questions.md)). Resolutions are folded into the normative sections below. Per governance there is no `## Clarifications` section.

## Overview
Administrators currently reach every admin screen through a long "Admin" submenu in the left drawer. The submenu has outgrown the drawer and offers no at-a-glance overview of system state. This feature introduces a single **Admin Dashboard** page at `/admin` that lists the admin tools and surfaces a cacheable statistics overview, moves the nine admin-only screens under a `/admin/<slug>` URL namespace (with a matching `resources/js/views/admin/` folder reorganisation), and adds an admin-category toggle that replaces the long submenu with a single "Admin" link when enabled (default ON).

Affected modules: REST (new stats controller + updated admin route files), UI (new view, router reorg, left-menu composable, config migration), core/application (stats aggregation + cache layer), documentation (roadmap, knowledge map). Diagnostics (`/diagnostics`) and Logs (`/Logs`) remain on their existing URLs. Clockwork stays as an external link.

Constitutional constraints honoured: spec-first cadence; greenfield interfaces (no redirects from old URLs); telemetry parity for the new stats endpoint.

## Goals
- Deliver an Admin Dashboard view at `/admin` that lists every admin tool as an accessible, localised tile.
- Expose an authenticated REST endpoint that returns a cached 7-metric `AdminStatsOverview` payload feeding the dashboard.
- Add a config toggle `use_admin_dashboard` (default `1`) that collapses the left-menu "Admin" submenu into a single link to `/admin`.
- Move nine admin-only pages under `/admin/<slug>` URLs and mirror the relocation in `resources/js/views/admin/`.
- Preserve `/diagnostics` and `/Logs` URLs unchanged.
- Localise every new string in the 22 supported languages.

## Non-Goals
- No visual redesign of the individual admin pages being moved; only paths + view locations change.
- No redirects from old URLs to new `/admin/...` URLs (greenfield per Q-037-07).
- No new permissions layer; continue to reuse the existing capability flags (`settings.can_edit`, `user_management.can_edit`, `settings.can_see_diagnostics`, `settings.can_see_logs`, `settings.can_acess_user_groups`) and the `canSeeAdmin` OR-composite in the left-menu composable.
- No changes to the Diagnostics or Logs pages beyond tile links on the dashboard.
- No event-driven cache invalidation in v1 (a 5-minute TTL + manual refresh covers the initial scope per Q-037-02).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-037-01 | The nine admin-only pages shall be reachable under `/admin/<slug>`: Settings → `/admin/settings`, Users → `/admin/users`, User Groups → `/admin/user-groups`, Purchasables → `/admin/purchasables`, Contact Messages → `/admin/contact-messages`, Webhooks → `/admin/webhooks`, Moderation → `/admin/moderation`, Maintenance → `/admin/maintenance`, Jobs → `/admin/jobs`. Diagnostics keeps `/diagnostics`; Logs keeps `/Logs`. | Router resolves each `/admin/<slug>` to the correct Vue view; existing admin navigation links point to the new paths. | Router returns 404 for unknown `/admin/...` slugs. | Unauthenticated or non-admin user is redirected/denied per existing guards. | Reuses existing page-view telemetry; no new events. | User request 2026-04-22; Q-037-01. |
| FR-037-02 | A REST endpoint `GET /api/v2/Admin/Stats` shall return an `AdminStatsOverview` payload: `photos_count`, `albums_count`, `users_count`, `storage_bytes`, `queued_jobs`, `failed_jobs_24h`, `last_successful_job_at`, `cached_at`. Results are served from `Cache::remember('admin.stats', 300, …)` (5-minute TTL). Access is gated on `settings.can_edit` (full admin); partial-admin capabilities are **not** sufficient. | Full admin request returns cached payload within TTL; stale cache repopulated on expiry. | Request schema validation rejects unknown query params. | Users lacking `settings.can_edit` (including partial-admins with only `can_acess_user_groups`, `can_see_logs`, `can_see_diagnostics`, or `user_management.can_edit`) return 403; downstream aggregation failure returns partial payload with an `errors` array and 200 (cache is not stored on partial failure). | `admin.stats.fetch` with fields `cache_hit`, `duration_ms`. | User request; Q-037-02, Q-037-08. |
| FR-037-03 | The dashboard shall offer a Refresh control that busts the `admin.stats` cache before re-fetching. | Click clears cache via `GET /api/v2/Admin/Stats?force=1` (or server-side forget), endpoint recomputes, stats re-render. | Button is disabled while a fetch is in flight. | On error the stale values remain visible and a non-blocking toast surfaces the error message. | `admin.stats.refresh` with fields `user_id_hash`, `duration_ms`. | User request; Q-037-02. |
| FR-037-04 | The dashboard shall render a localised, keyboard-navigable tile grid for every admin tool the current user is authorised to see. Per-tile visibility uses the existing fine-grained capability flags: Settings → `settings.can_edit`; Users → `user_management.can_edit`; User Groups → `settings.can_acess_user_groups`; Purchasables → `is_mod_webshop_enabled && settings.can_edit`; Contact Messages → `is_contact_enabled && canSeeAdmin`; Webhooks → `is_mod_webhook_enabled`; Moderation → `canSeeAdmin`; Diagnostics → `settings.can_see_diagnostics`; Logs → `settings.can_see_logs`; Maintenance → `settings.can_edit`; Jobs → `settings.can_see_logs`; Clockwork → `settings.can_access_dev_tools && clockwork_url !== null`. A partial-admin who holds only one capability sees only the corresponding tile. | Each visible tile shows icon + label + deep link; clicking navigates to the corresponding route/URL. | Hidden tiles for tools the user lacks permission for. | Dashboard with zero tiles is unreachable — if a user reaches `/admin` without any tile-eligible capability the router denies access (aligned with `canSeeAdmin` === false). | No new events. | User request; Q-037-01/04/08. |
| FR-037-05 | New config key `use_admin_dashboard` (bool, default `1`, `cat = 'config'`) shall toggle left-menu collapse. When enabled (default): the left-menu "Admin" submenu collapses to a single "Admin" entry → `/admin` for **every** user where `canSeeAdmin` is true (including partial-admins). When disabled: current nested "Admin" submenu renders unchanged for every user. | Setting change applies immediately (config reload) and reflects in the next menu render. | Config migration enforces boolean via `type_range` `BOOL`. | Invalid stored value falls back to default `1` (consistent with existing config handler). | No new events. | User request; Q-037-03/06/08. |
| FR-037-06 | Nine Vue views move into `resources/js/views/admin/`: `AdminDashboard.vue` (new), `Settings.vue`, `Users.vue`, `UserGroups.vue`, `Purchasables.vue`, `ContactMessages.vue`, `Webhooks.vue`, `Moderation.vue`, `Maintenance.vue`, `Jobs.vue`. `Diagnostics.vue` stays at top level. | Vue router imports resolve; `npm run check` passes. | Build fails if an import path is stale. | — | — | User request; Q-037-04. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-037-01 | `GET /api/v2/Admin/Stats` p95 latency ≤ 200 ms on cache hit; ≤ 1500 ms on cache miss (Feature_v2 reference dataset). | Dashboard responsiveness. | Feature_v2 test measures both paths against budget. | Laravel Cache facade, default store. | Q-037-02 |
| NFR-037-02 | All new labels (dashboard title, toggle label, tool tile labels, refresh button) shall have entries in every locale JSON in `lang/` (22 languages). | Existing i18n policy. | `npm run check` + translation-parity lint. | — | Standard project policy. |
| NFR-037-03 | Dashboard shall be keyboard-navigable (Tab focusable tiles, Enter/Space activates) with screen-reader labels matching visible text. | WCAG 2.1 AA (existing project target). | Accessibility assertions per existing pattern (Feature 006 precedent). | — | Standard project policy. |
| NFR-037-04 | The greenfield URL switch shall be atomic: old routes are removed in the same commit that introduces `/admin/<slug>` routes; no dual-serve period. | AGENTS.md "Guardrails & Governance" greenfield stance. | Feature_v2 regression asserts old routes return 404 and new routes return 200 for admins. | — | AGENTS.md; Q-037-07 |
| NFR-037-05 | Capability gating shall preserve today's fine-grained permission model: tile visibility per Q-037-08 Option A; stats endpoint restricted to `settings.can_edit`; dashboard view-level guard equivalent to `canSeeAdmin` (union). | Prevent telemetry leakage to partial-admin roles while keeping the collapsed menu usable by everyone who had access before. | Feature_v2 + component tests cover each of the five partial-admin capability combinations; assertions on tiles rendered and 403 on stats call. | Existing `SettingsPolicy`, `UserGroupPolicy`, `UserPolicy`. | Q-037-08 |

## UI / Interaction Mock-ups

Admin Dashboard (default view):

```
┌──────────────────────────────────────────────────────────────────────┐
│  Admin Dashboard                                         [↻ Refresh] │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  Overview                                                            │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │ Photos   │ │ Albums   │ │ Users    │ │ Storage  │                 │
│  │  12 345  │ │     789  │ │     42   │ │  1.24 TB │                 │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────────────────────┐              │
│  │ Queued   │ │ Failed   │ │ Last successful job      │              │
│  │    3     │ │   (24h)0 │ │ 2026-04-22 13:42 UTC     │              │
│  └──────────┘ └──────────┘ └──────────────────────────┘              │
│  Cached at 2026-04-22 13:47 UTC · refreshes every 5 min              │
│                                                                      │
│  Tools                                                               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │ Settings │ │  Users   │ │UserGroups│ │Purchsbls │                 │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │ Messages │ │ Webhooks │ │Moderation│ │Maintenance│                │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │   Jobs   │ │Diagnostic│ │   Logs   │ │Clockwork │                 │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                 │
└──────────────────────────────────────────────────────────────────────┘
```

Left-menu behaviour driven by `use_admin_dashboard`:

```
 use_admin_dashboard = 1 (default)        use_admin_dashboard = 0
 ┌──────────────────────────┐             ┌──────────────────────────┐
 │ Gallery                  │             │ Gallery                  │
 │ Timeline                 │             │ Timeline                 │
 │ Tags                     │             │ Tags                     │
 │ …                        │             │ …                        │
 │ Admin   →  /admin        │             │ Admin ▾                  │
 │ Lychee                   │             │   Settings               │
 │                          │             │   Users                  │
 │                          │             │   User Groups            │
 │                          │             │   Purchasables           │
 │                          │             │   Messages               │
 │                          │             │   Webhooks               │
 │                          │             │   Moderation             │
 │                          │             │   Diagnostics            │
 │                          │             │   Maintenance            │
 │                          │             │   Logs                   │
 │                          │             │   Jobs                   │
 │                          │             │   Clockwork              │
 │                          │             │ Lychee                   │
 └──────────────────────────┘             └──────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-037-01 | Admin navigates to `/admin` → dashboard renders stats block + tool grid. |
| S-037-02 | Non-admin navigates to `/admin` → denied per existing admin route guard. |
| S-037-03 | Admin clicks Refresh → cache busted, endpoint recomputes, UI updates; telemetry `admin.stats.refresh` emitted. |
| S-037-04 | Toggle ON (default): left menu renders a single "Admin" link → `/admin`. |
| S-037-05 | Toggle OFF: left menu renders the legacy nested "Admin" submenu unchanged. |
| S-037-06 | `/admin/settings` loads the Settings view; same for the eight other `/admin/<slug>` routes. |
| S-037-07 | `/diagnostics` still loads Diagnostics (URL unchanged). |
| S-037-08 | `/Logs` still loads Logs (URL unchanged). |
| S-037-09 | Stats endpoint returns cached payload within 5-minute TTL (`cache_hit=true`). |
| S-037-10 | Stats endpoint recomputes after TTL expiry or `force=1` (`cache_hit=false`). |
| S-037-11 | Stats endpoint returns partial payload with `errors[]` when one aggregator fails; cache is not stored. |
| S-037-12 | Every dashboard label exists in all 22 locale JSON files. |
| S-037-13 | Keyboard user can Tab through every tile and activate with Enter/Space. |
| S-037-14 | Old top-level routes for the nine moved pages return 404 after deployment (greenfield). |
| S-037-15 | Tile visibility: a user lacking `is_mod_webshop_enabled` does not see the Purchasables tile. |
| S-037-16 | Partial-admin (only `settings.can_acess_user_groups`) with toggle ON: left menu shows the single "Admin" link; `/admin` renders dashboard with the User Groups tile only and no stats section. |
| S-037-17 | Partial-admin calling `GET /api/v2/Admin/Stats` directly receives HTTP 403 regardless of the `force` query parameter. |
| S-037-18 | Partial-admin (only `settings.can_see_logs`) with toggle OFF: legacy nested submenu still renders only the Logs entry (unchanged from today's behaviour). |

## Test Strategy
- **Core / Application:** Unit tests for the stats aggregator service and its cache layer (cache hit vs. miss, partial-failure path). Base class: `AbstractTestCase`.
- **REST:** Feature tests under `tests/Feature_v2` using `BaseApiWithDataTest` for `GET /api/v2/Admin/Stats`: authorised, forbidden, cache hit/miss, `force=1`. Latency assertions for NFR-037-01.
- **UI (Vue):** Component tests for `AdminDashboard.vue` covering default render, refresh action, error toast, and tile visibility gating. Composable test for `useLeftMenu` covering toggle-on and toggle-off branches.
- **Routing:** Vue router assertions that each `/admin/<slug>` resolves to the correct view and that `/diagnostics`, `/Logs` are unchanged; regression assertions that the old top-level paths 404 (NFR-037-04).
- **i18n:** Translation-parity check for the new keys across all 22 languages (NFR-037-02).
- **Docs/Contracts:** OpenAPI entry for `GET /api/v2/Admin/Stats`; knowledge-map entry for the new dashboard and `resources/js/views/admin/` folder.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-037-01 | `AdminStatsOverview` DTO (Spatie Data). Fields: `photos_count:int≥0`, `albums_count:int≥0`, `users_count:int≥0`, `storage_bytes:int≥0`, `queued_jobs:int≥0`, `failed_jobs_24h:int≥0`, `last_successful_job_at:string(ISO8601)\|null`, `cached_at:string(ISO8601)`, `errors:string[]` (only on partial failure). | core, application, REST |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-037-01 | REST `GET /api/v2/Admin/Stats` | Returns `AdminStatsOverview`. Query param `force=1` bypasses cache. | Auth: `settings.can_edit` (full admin only). Partial-admins receive 403. |
| API-037-02 | UI `GET /admin` | Renders `AdminDashboard.vue`. | Auth: `canSeeAdmin` (any of the five admin capabilities). Tile + stats visibility further gated per FR-037-04 / FR-037-02. |
| API-037-03 | UI `GET /admin/settings` | Renders `Settings.vue` (moved). | — |
| API-037-04 | UI `GET /admin/users` | Renders `Users.vue` (moved). | — |
| API-037-05 | UI `GET /admin/user-groups` | Renders `UserGroups.vue` (moved). | — |
| API-037-06 | UI `GET /admin/purchasables` | Renders `Purchasables.vue` (moved). | Module-gated: webshop. |
| API-037-07 | UI `GET /admin/contact-messages` | Renders `ContactMessages.vue` (moved). | Module-gated: contact. |
| API-037-08 | UI `GET /admin/webhooks` | Renders `Webhooks.vue` (moved). | Module-gated: webhook. |
| API-037-09 | UI `GET /admin/moderation` | Renders `Moderation.vue` (moved). | — |
| API-037-10 | UI `GET /admin/maintenance` | Renders `Maintenance.vue` (moved). | — |
| API-037-11 | UI `GET /admin/jobs` | Renders `Jobs.vue` (moved). | — |

### CLI Commands / Flags
None.

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-037-01 | `admin.stats.fetch` | `cache_hit:bool`, `duration_ms:int`, `errors_count:int`. No PII. |
| TE-037-02 | `admin.stats.refresh` | `user_id_hash:string`, `duration_ms:int`. `user_id` is hashed; username not emitted. |

### Fixtures & Sample Data
None planned; existing seeder data suffices.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-037-01  | Admin Dashboard — default (full admin) | `settings.can_edit` user; stats block + tile grid rendered. |
| UI-037-01a | Admin Dashboard — partial-admin (no-stats variant) | User lacks `settings.can_edit`; stats block is omitted, header + tile grid rendered with only the tiles the user is authorised to reach. |
| UI-037-02  | Admin Dashboard — refreshing | Refresh clicked; button disabled with spinner; stats fade-to-update when resolved. Control is hidden in UI-037-01a. |
| UI-037-03  | Admin Dashboard — partial error | `errors[]` present; stats rendered where available; toast surfaces error list. Only reachable from UI-037-01. |
| UI-037-04  | Left menu — collapsed (toggle ON) | Single "Admin" entry pointing to `/admin`, visible to any `canSeeAdmin` user (including partial-admins). |
| UI-037-05  | Left menu — expanded (toggle OFF) | Legacy nested submenu preserved; entries still individually gated per today's flags. |

## Telemetry & Observability
- `admin.stats.fetch` emitted on every endpoint call (cached or not). `cache_hit=true` on TTL-served responses.
- `admin.stats.refresh` emitted only when the Refresh control triggers a forced fetch.
- Both events redacted of user identifiers beyond the hashed user id.

## Documentation Deliverables
- [roadmap.md](../../roadmap.md): move row to Ready for Implementation on plan/tasks delivery; Completed on merge.
- [knowledge-map.md](../../knowledge-map.md): add entries for (a) `AdminDashboard.vue` landing view, (b) `resources/js/views/admin/` subfolder, (c) `Admin\StatsController` (or equivalent), (d) `admin.stats` cache key.
- Optional how-to: short operator note under `docs/specs/2-how-to/admin-dashboard.md` describing the toggle.
- OpenAPI: add `GET /api/v2/Admin/Stats` entry.

## Fixtures & Sample Data
None planned.

## Spec DSL

```yaml
feature_id: 037
name: admin-dashboard
status: ready-for-planning

domain_objects:
  - id: DO-037-01
    name: AdminStatsOverview
    fields:
      - { name: photos_count,            type: integer, constraints: ">= 0" }
      - { name: albums_count,            type: integer, constraints: ">= 0" }
      - { name: users_count,             type: integer, constraints: ">= 0" }
      - { name: storage_bytes,           type: integer, constraints: ">= 0" }
      - { name: queued_jobs,             type: integer, constraints: ">= 0" }
      - { name: failed_jobs_24h,         type: integer, constraints: ">= 0" }
      - { name: last_successful_job_at,  type: "string|null", constraints: "ISO8601" }
      - { name: cached_at,               type: string,  constraints: "ISO8601" }
      - { name: errors,                  type: "string[]", constraints: "optional; present on partial failure" }

routes:
  - { id: API-037-01,  method: GET, path: "/api/v2/Admin/Stats",       auth: "settings.can_edit" }
  - { id: API-037-02,  method: GET, path: "/admin",                    auth: canSeeAdmin }
  - { id: API-037-03,  method: GET, path: "/admin/settings",           auth: admin }
  - { id: API-037-04,  method: GET, path: "/admin/users",              auth: admin }
  - { id: API-037-05,  method: GET, path: "/admin/user-groups",        auth: admin }
  - { id: API-037-06,  method: GET, path: "/admin/purchasables",       auth: admin, module: webshop }
  - { id: API-037-07,  method: GET, path: "/admin/contact-messages",   auth: admin, module: contact }
  - { id: API-037-08,  method: GET, path: "/admin/webhooks",           auth: admin, module: webhook }
  - { id: API-037-09,  method: GET, path: "/admin/moderation",         auth: admin }
  - { id: API-037-10,  method: GET, path: "/admin/maintenance",        auth: admin }
  - { id: API-037-11,  method: GET, path: "/admin/jobs",               auth: admin }

config_keys:
  - key: use_admin_dashboard
    cat: config
    type_range: BOOL
    default: "1"
    description: "Use new admin dashboard and collapsed admin menu"
    details: "When enabled, the 'Admin' submenu in the left drawer is replaced by a single link to the new Admin Dashboard page."

cache_keys:
  - key: admin.stats
    ttl_seconds: 300
    bust_triggers: ["manual refresh via API-037-01 ?force=1"]

telemetry_events:
  - { id: TE-037-01, event: admin.stats.fetch,   fields: [cache_hit, duration_ms, errors_count] }
  - { id: TE-037-02, event: admin.stats.refresh, fields: [user_id_hash, duration_ms] }

ui_states:
  - { id: UI-037-01,  description: "Admin Dashboard default (full admin)" }
  - { id: UI-037-01a, description: "Admin Dashboard no-stats variant (partial-admin)" }
  - { id: UI-037-02,  description: "Admin Dashboard refreshing" }
  - { id: UI-037-03,  description: "Admin Dashboard partial-error" }
  - { id: UI-037-04,  description: "Left menu collapsed (toggle ON)" }
  - { id: UI-037-05,  description: "Left menu expanded (toggle OFF)" }

capability_gating:
  tiles:
    settings:          "settings.can_edit"
    users:             "user_management.can_edit"
    user_groups:       "settings.can_acess_user_groups"
    purchasables:      "is_mod_webshop_enabled && settings.can_edit"
    contact_messages:  "is_contact_enabled && canSeeAdmin"
    webhooks:          "is_mod_webhook_enabled"
    moderation:        "canSeeAdmin"
    diagnostics:       "settings.can_see_diagnostics"
    logs:              "settings.can_see_logs"
    maintenance:       "settings.can_edit"
    jobs:              "settings.can_see_logs"
    clockwork:         "settings.can_access_dev_tools && clockwork_url != null"
  stats_block:         "settings.can_edit"
  page_guard:          "canSeeAdmin"

views_moved_to_admin_folder:
  - AdminDashboard.vue   # new
  - Settings.vue
  - Users.vue
  - UserGroups.vue
  - Purchasables.vue
  - ContactMessages.vue
  - Webhooks.vue
  - Moderation.vue
  - Maintenance.vue
  - Jobs.vue

views_unchanged:
  - Diagnostics.vue  # URL /diagnostics kept
```

## Appendix
- User request (2026-04-22, verbatim): "We want a new page with all the admin tools and links. We may need a controller endpoint for some nice statistics overview (maybe use caching to avoid computations). This should be toggable settings (in admin category) and should replace the admin section in the menu. We would like also to move the major admin/maintenance pages to sub address under /admin/ we would like also to do the same with the views files in resources/js/views. Diagnostics must stay on the same url, same for Logs."
- Resolutions recorded in [open-questions.md](../../open-questions.md) Q-037-01 through Q-037-07.
- Current admin submenu source: [resources/js/composables/contextMenus/leftMenu.ts](../../../../resources/js/composables/contextMenus/leftMenu.ts).
- Existing admin controllers: [app/Http/Controllers/Admin/](../../../../app/Http/Controllers/Admin/).
