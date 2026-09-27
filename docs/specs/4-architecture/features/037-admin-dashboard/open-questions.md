# Open Questions – Feature 037

Open questions for [Feature 037](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-037-08: Partial-Admin Users — Dashboard Behaviour & Stats Visibility~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — the collapsed "Admin" menu entry appears whenever the existing `canSeeAdmin` composite is true. The dashboard tile grid renders per-capability (tiles for tools the operator cannot access are hidden). The stats overview block and `GET /api/v2/Admin/Stats` endpoint are gated on `settings.can_edit`; partial-admins receive 403 on the stats call and do not see the stats section. This keeps the fine-grained capability model intact and prevents leaking global telemetry to limited roles.

**Spec Impact:** Updated FR-037-02 (stats endpoint auth = `settings.can_edit`), FR-037-04 (tile gating detail), FR-037-05 (menu collapse honours existing `canSeeAdmin`), added NFR-037-05 (capability gating), UI-037-01a variant (no-stats view), and scenarios S-037-16 … S-037-18.

**Resolved:** 2026-04-22

---

Lychee treats the "admin" area as a union of five fine-grained capabilities (see [`SettingsRightsResource`](../../../../app/Http/Resources/Rights/SettingsRightsResource.php) + [`UserManagementRightsResource`](../../../../app/Http/Resources/Rights/UserManagementRightsResource.php)):

1. `settings.can_edit` — full config editor (typical super-admin).
2. `user_management.can_edit` — can manage users.
3. `settings.can_see_diagnostics` — can read diagnostics.
4. `settings.can_see_logs` — can read logs.
5. `settings.can_acess_user_groups` — can manage user groups (e.g., a team lead who is **not** a full admin).

Today's left-menu `canSeeAdmin` composite is a permissive OR of those five, but each submenu entry has its own `access` flag so a user only sees items their capability permits (e.g., a User-Groups-only operator sees just the "User Groups" entry nested under "Admin"). When we collapse the menu to a single link → `/admin`, that user lands on a dashboard that needs to:
- **Only** expose tiles they are authorised to reach, and
- Decide whether the stats overview (which exposes global photo/album/user counts and storage/job telemetry) is visible to them.

Today's `GET /api/v2/Admin/Stats` spec line says "Auth: admin (existing `AdminMiddleware`)", which is ambiguous: does "admin" mean full `settings.can_edit`, or the union that `canSeeAdmin` represents?

**Options (ordered by preference):**

- **Option A (Recommended) — Dashboard always available to anyone passing `canSeeAdmin`; tiles + stats are each permission-gated.**
  - Menu: collapsed "Admin" link appears whenever `canSeeAdmin` is true (unchanged semantics).
  - Dashboard tiles: rendered per existing per-tool flags (User Groups only → single User Groups tile; Diagnostics-only → single Diagnostics tile; etc.).
  - Stats block: rendered only when `settings.can_edit` is true. Partial-admins (groups-only, logs-only, diagnostics-only) see the dashboard header + tile grid but no stats section, and the `GET /api/v2/Admin/Stats` endpoint requires `settings.can_edit` (returns 403 otherwise).
  - Pros: preserves the fine-grained capability model; one menu entry for all admin flavours; avoids leaking global telemetry to limited roles.
  - Cons: a partial-admin may land on a dashboard with just one tile — simple but minimal.

- **Option B — Skip the dashboard for single-capability users and deep-link the menu entry.**
  - Menu: if only one of the five capabilities is present, the "Admin" link is rewritten to target that specific page (e.g., `/admin/user-groups`) instead of `/admin`. If two or more are present, use `/admin` (dashboard).
  - Dashboard: still tile-filtered + stats-gated on `settings.can_edit` (same gating as Option A for the multi-capability case).
  - Pros: one-click access for limited operators; feels "smart".
  - Cons: extra branching in the composable; users who gain a second capability suddenly see a different destination; harder to localise the single link label (does it still say "Admin" or "User Groups"?).

- **Option C — Restrict dashboard to full admins (`settings.can_edit` only).**
  - Menu: collapsed "Admin" link only appears for full admins. Partial-admins keep seeing the legacy nested submenu regardless of the toggle.
  - Dashboard: single render path; stats always visible.
  - Pros: simplest dashboard implementation.
  - Cons: two menu styles coexist indefinitely, breaks the "single toggle" UX, contradicts the user's clean-replacement intent.

**Spec Impact (after resolution):** Updates FR-037-02 (stats auth), FR-037-04 (tile gating), FR-037-05 (menu behaviour for partial-admins), a new NFR on capability gating, UI-037-01 variant for the no-stats view, and new scenarios S-037-16 … S-037-18 covering partial-admin paths.

---

### ~~Q-037-01: Scope of Admin Pages to Move Under `/admin/...`~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — Move every admin-only page except Diagnostics, Logs and Clockwork. The nine pages migrated to `/admin/<slug>` are: Settings, Users, User Groups, Purchasables, Contact Messages, Webhooks, Moderation, Maintenance, Jobs.

**Spec Impact:** Populated FR-037-01, the API/UI route catalogue (nine new `/admin/...` route IDs), and the router + views reorganisation tasks. Left-menu composable updated to target the new paths.

**Resolved:** 2026-04-22

---

### ~~Q-037-02: Statistics Overview Content and Caching Strategy~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — Curated v1 metrics with a 5-minute `Cache::remember('admin.stats', 300, …)` layer plus a dashboard-side "Refresh" action that busts the cache. First-release metrics: photo count, album count, user count, total storage size, queued job count, failed job count (last 24 h), last successful job timestamp.

**Spec Impact:** Defined FR-037-02 (stats endpoint), FR-037-03 (refresh action), NFR-037-01 (latency budget), DO-037-01 (AdminStatsOverview DTO), API-037-01 (REST route), TE-037-01 (telemetry event), and the dashboard mock-up.

**Resolved:** 2026-04-22

---

### ~~Q-037-03: Admin-Menu Toggle Default and Collapsed-Menu Behaviour~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — New config key `use_admin_dashboard` (boolean, default `1`). When ON the left-menu "Admin" submenu is replaced by a single "Admin" entry pointing to `/admin`. When OFF the current nested submenu renders unchanged.

**Spec Impact:** Defined FR-037-05 (config toggle + menu behaviour), config migration entry (category decided in Q-037-06), and left-menu composable branch.

**Resolved:** 2026-04-22

---

### ~~Q-037-04: View Folder Reorganisation (`resources/js/views/admin/`)~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — Mirror the URL scope from Q-037-01. Only the nine views whose URL moves to `/admin/<slug>` relocate into `resources/js/views/admin/`; `Diagnostics.vue` stays at top level because `/diagnostics` stays. A new `resources/js/views/admin/AdminDashboard.vue` is added as the `/admin` landing view.

**Spec Impact:** Defined Task-037 view-move entries, router import paths, and the knowledge-map entry for `resources/js/views/admin/`.

**Resolved:** 2026-04-22

---

### ~~Q-037-05: Dashboard Page Label/Name~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — "Admin Dashboard". i18n key `admin-dashboard.title`, route name `admin-dashboard`, view filename `AdminDashboard.vue`.

**Spec Impact:** Locked naming across locale files, router entry, and view module.

**Resolved:** 2026-04-22

---

### ~~Q-037-06: Settings Category for the New Toggle~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** Operator override — place the config row under `cat = 'config'` (rather than the recommended `access_permissions`). Config migration will use `'cat' => 'config'`.

**Spec Impact:** Config-migration entry uses `'cat' => 'config'`. Settings page tab visibility: the toggle surfaces under the `config` category tab.

**Resolved:** 2026-04-22

---

### ~~Q-037-07: Keep Old URLs as Redirects or Greenfield?~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — Greenfield. No redirects from old paths; the URLs move outright. Aligns with AGENTS.md "Guardrails & Governance" greenfield stance.

**Spec Impact:** Route catalogue confirms no redirect routes are added.

**Resolved:** 2026-04-22

---

### ~~Q-037-08: Partial-Admin Users — Dashboard Behaviour & Stats Visibility~~ ✅ RESOLVED

**Feature:** 037 – Admin Dashboard & `/admin/` URL Reorganisation
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-22

**Resolution:** **Option A** — the collapsed "Admin" menu entry appears whenever the existing `canSeeAdmin` composite is true. The dashboard tile grid renders per-capability (tiles for tools the operator cannot access are hidden). The stats overview block and `GET /api/v2/Admin/Stats` endpoint are gated on `settings.can_edit`; partial-admins receive 403 on the stats call and do not see the stats section. This keeps the fine-grained capability model intact and prevents leaking global telemetry to limited roles.

**Spec Impact:** Updated FR-037-02 (stats endpoint auth = `settings.can_edit`), FR-037-04 (tile gating detail), FR-037-05 (menu collapse honours existing `canSeeAdmin`), added NFR-037-05 (capability gating), UI-037-01a variant (no-stats view), and scenarios S-037-16 … S-037-18.

**Resolved:** 2026-04-22

---

Lychee treats the "admin" area as a union of five fine-grained capabilities (see [`SettingsRightsResource`](../../../../app/Http/Resources/Rights/SettingsRightsResource.php) + [`UserManagementRightsResource`](../../../../app/Http/Resources/Rights/UserManagementRightsResource.php)):

1. `settings.can_edit` — full config editor (typical super-admin).
2. `user_management.can_edit` — can manage users.
3. `settings.can_see_diagnostics` — can read diagnostics.
4. `settings.can_see_logs` — can read logs.
5. `settings.can_acess_user_groups` — can manage user groups (e.g., a team lead who is **not** a full admin).

Today's left-menu `canSeeAdmin` composite is a permissive OR of those five, but each submenu entry has its own `access` flag so a user only sees items their capability permits (e.g., a User-Groups-only operator sees just the "User Groups" entry nested under "Admin"). When we collapse the menu to a single link → `/admin`, that user lands on a dashboard that needs to:
- **Only** expose tiles they are authorised to reach, and
- Decide whether the stats overview (which exposes global photo/album/user counts and storage/job telemetry) is visible to them.

Today's `GET /api/v2/Admin/Stats` spec line says "Auth: admin (existing `AdminMiddleware`)", which is ambiguous: does "admin" mean full `settings.can_edit`, or the union that `canSeeAdmin` represents?

**Options (ordered by preference):**

- **Option A (Recommended) — Dashboard always available to anyone passing `canSeeAdmin`; tiles + stats are each permission-gated.**
  - Menu: collapsed "Admin" link appears whenever `canSeeAdmin` is true (unchanged semantics).
  - Dashboard tiles: rendered per existing per-tool flags (User Groups only → single User Groups tile; Diagnostics-only → single Diagnostics tile; etc.).
  - Stats block: rendered only when `settings.can_edit` is true. Partial-admins (groups-only, logs-only, diagnostics-only) see the dashboard header + tile grid but no stats section, and the `GET /api/v2/Admin/Stats` endpoint requires `settings.can_edit` (returns 403 otherwise).
  - Pros: preserves the fine-grained capability model; one menu entry for all admin flavours; avoids leaking global telemetry to limited roles.
  - Cons: a partial-admin may land on a dashboard with just one tile — simple but minimal.

- **Option B — Skip the dashboard for single-capability users and deep-link the menu entry.**
  - Menu: if only one of the five capabilities is present, the "Admin" link is rewritten to target that specific page (e.g., `/admin/user-groups`) instead of `/admin`. If two or more are present, use `/admin` (dashboard).
  - Dashboard: still tile-filtered + stats-gated on `settings.can_edit` (same gating as Option A for the multi-capability case).
  - Pros: one-click access for limited operators; feels "smart".
  - Cons: extra branching in the composable; users who gain a second capability suddenly see a different destination; harder to localise the single link label (does it still say "Admin" or "User Groups"?).

- **Option C — Restrict dashboard to full admins (`settings.can_edit` only).**
  - Menu: collapsed "Admin" link only appears for full admins. Partial-admins keep seeing the legacy nested submenu regardless of the toggle.
  - Dashboard: single render path; stats always visible.
  - Pros: simplest dashboard implementation.
  - Cons: two menu styles coexist indefinitely, breaks the "single toggle" UX, contradicts the user's clean-replacement intent.

**Spec Impact (after resolution):** Updates FR-037-02 (stats auth), FR-037-04 (tile gating), FR-037-05 (menu behaviour for partial-admins), a new NFR on capability gating, UI-037-01 variant for the no-stats view, and new scenarios S-037-16 … S-037-18 covering partial-admin paths.
