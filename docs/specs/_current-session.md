# Current Session

_Last updated: 2026-04-22_

## Active Features

**Feature 037 – Admin Dashboard & `/admin/` URL Reorganisation**
- Status: Ready for Implementation (spec + plan + tasks complete)
- Priority: P2
- License: Open
- Started: 2026-04-22
- Dependencies: None

## Session Summary

User requested a new admin dashboard page listing admin tools with a cacheable stats overview, a toggleable admin setting that replaces the left-menu "Admin" submenu, and migration of the major admin/maintenance pages (plus matching Vue views) under `/admin/<slug>`. Diagnostics and Logs keep their existing URLs.

### Feature 037: Admin Dashboard & `/admin/` URL Reorg

**Status:** spec.md + plan.md + tasks.md complete; ready to begin T-037-01.

**Locked decisions (via Q-037-01 … Q-037-08):**
- **Scope (Q-037-01):** 9 admin-only pages move under `/admin/<slug>`: Settings, Users, User Groups, Purchasables, Contact Messages, Webhooks, Moderation, Maintenance, Jobs. Diagnostics (`/diagnostics`) and Logs (`/Logs`) unchanged; Clockwork stays external.
- **Stats overview (Q-037-02):** 7 metrics (photos, albums, users, storage bytes, queued jobs, failed-24h jobs, last-successful-job timestamp) via `GET /api/v2/Admin/Stats`, cached with `Cache::remember('admin.stats', 300, …)`. Dashboard "Refresh" action busts cache (`?force=1`).
- **Toggle (Q-037-03):** Config key `use_admin_dashboard`, boolean, default `1`. When ON: left-menu "Admin" collapses to one link → `/admin`; when OFF: legacy nested submenu.
- **Views (Q-037-04):** Mirror URL scope. Nine moved views live under `resources/js/views/admin/`; Diagnostics.vue stays at top level. New `AdminDashboard.vue` added.
- **Label (Q-037-05):** "Admin Dashboard" — i18n key `admin-dashboard.title`, route name `admin-dashboard`.
- **Settings category (Q-037-06):** Config row uses `cat = 'config'` (operator override of the recommended `access_permissions`).
- **URL policy (Q-037-07):** Greenfield — no redirects from old paths (aligns with AGENTS.md).
- **Partial-admin handling (Q-037-08):** Collapsed "Admin" link appears for anyone with `canSeeAdmin` (union of 5 capability flags). Dashboard tiles are gated per individual capability; the stats block and `GET /api/v2/Admin/Stats` are gated on `settings.can_edit` (full admin only). Partial-admins (e.g., User-Groups-only, logs-only) land on a dashboard showing only their authorised tiles and no stats section; calling the stats endpoint returns 403.

**Plan increments (7 × ≤90 min, 30 tasks total):**
- **I1 – Config migration for `use_admin_dashboard`** (T-037-01..03): new migration row `cat = config`, default `1`; surface through `LycheeStateStore`.
- **I2 – `AdminStatsOverview` DTO + `AdminStatsService` + cache** (T-037-04..06): pure PHP DTO, 7 metrics, 5-min TTL, `force` bypass, partial-error path does **not** cache.
- **I3 – REST endpoint + Feature_v2 tests** (T-037-07..10): `GET /api/v2/Admin/Stats` gated on `SettingsPolicy::CAN_EDIT`, telemetry `admin.stats.fetch` + `admin.stats.refresh`.
- **I4 – Router + view relocation** (T-037-11..15): router Vitest (RED) → move 9 views to `resources/js/views/admin/`, rewrite 9 paths under `/admin/<slug>`, old paths 404.
- **I5 – `AdminDashboard.vue`** (T-037-16..20): `useAdminTiles` composable, PrimeVue tile grid + stats block (full admin only) + Refresh, Vitest covers full admin / partial admin / refresh / errors / keyboard nav.
- **I6 – Left-menu composable branch + 22-locale parity** (T-037-21..24): toggle branch in `useLeftMenu`, new i18n keys authored in `en.json` then propagated to 21 other locales.
- **I7 – Quality gates + docs + roadmap** (T-037-25..30): OpenAPI snapshot, knowledge-map, operator how-to, full pipeline, roadmap closeout.

**Key artefacts produced:**
- Spec: [docs/specs/4-architecture/features/037-admin-dashboard/spec.md](docs/specs/4-architecture/features/037-admin-dashboard/spec.md)
- Plan: [docs/specs/4-architecture/features/037-admin-dashboard/plan.md](docs/specs/4-architecture/features/037-admin-dashboard/plan.md)
- Tasks: [docs/specs/4-architecture/features/037-admin-dashboard/tasks.md](docs/specs/4-architecture/features/037-admin-dashboard/tasks.md)
- Open-questions log updated (Q-037-01 … Q-037-08 all marked resolved)
- Roadmap row: Ready for Implementation

**Key scenarios (S-037-01 … S-037-18):** see spec Branch & Scenario Matrix; the plan's Scenario Tracking table maps each to the owning increment/test. S-037-16..18 cover partial-admin paths (collapsed menu visibility, 403 on stats endpoint, legacy submenu with single capability).

## Next Steps

1. Run the analysis-gate checklist on the agreed spec/plan/tasks bundle before coding.
2. Start implementation at **T-037-01** (config migration) following tests-before-code ordering.
3. After each task passes verification, tick the box in `tasks.md` immediately (do not batch completions).
4. On completion of I7, move the roadmap row from "Active" to "Completed" and archive the session block.

## Open Questions

None for Feature 037 (Q-037-01 … Q-037-08 all resolved).

## References

**Feature 037:**
- Spec: [037-admin-dashboard/spec.md](docs/specs/4-architecture/features/037-admin-dashboard/spec.md)
- Plan: [037-admin-dashboard/plan.md](docs/specs/4-architecture/features/037-admin-dashboard/plan.md)
- Tasks: [037-admin-dashboard/tasks.md](docs/specs/4-architecture/features/037-admin-dashboard/tasks.md)
- Open questions (resolved): [open-questions.md](docs/specs/4-architecture/open-questions.md)
- Admin controllers: [app/Http/Controllers/Admin/](app/Http/Controllers/Admin/)
- Left-menu composable: [resources/js/composables/contextMenus/leftMenu.ts](resources/js/composables/contextMenus/leftMenu.ts)
- Vue router: [resources/js/router/routes.ts](resources/js/router/routes.ts)

**Common:**
- Roadmap: [roadmap.md](docs/specs/4-architecture/roadmap.md)
- Open questions: [open-questions.md](docs/specs/4-architecture/open-questions.md)

---

**Session Context for Handoff:**

Feature 037 spec, plan, and tasks are complete (30 tasks across 7 increments, all ≤90 min, tests-before-code). All 8 open questions resolved. Next author to pick up: run the analysis gate, then begin T-037-01 (config migration for `use_admin_dashboard`). I4 (router/views) can begin in parallel with I2/I3 once I1 is merged.
