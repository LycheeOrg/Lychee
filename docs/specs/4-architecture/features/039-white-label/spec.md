# Feature 039 – Lychee White Label

| Field | Value |
|-------|-------|
| Status | Draft (questions resolved, ready for implementation) |
| Last updated | 2026-05-04 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/039-white-label/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/039-white-label/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Operators who use Lychee as an embedded or branded gallery application need a way to hide all visible references to the Lychee project (name, links, and generator metadata) from end-users, so the product appears as their own. This feature introduces a single boolean setting `white_label_enabled` in the `lychee SE` config category that, when enabled, suppresses the "Lychee" section in the left menu, the "Lychee / SE" branding line in the login form, the "Powered by Lychee" footer link, the `<meta name="generator">` tag, and Lychee branding inside the misconfiguration warning blade component.

Affected modules: `application` (new config migration), `REST` (`InitConfig` resource), `UI` (left-menu composable, `LoginForm.vue`, `GalleryFooter.vue`), `blade` (`meta.blade.php`, `warning-misconfiguration.blade.php`).

## Goals

1. A new Lychee SE config key `white_label_enabled` controls all white-labelling behaviour from a single toggle.
2. When enabled, the "Lychee" submenu section is hidden in the left navigation drawer.
3. When enabled, the `Lychee / Lychee SE` branding line at the bottom of the login form (`LoginForm.vue`) is hidden.
4. When enabled, the "Powered by Lychee" paragraph is hidden in `GalleryFooter.vue` and `footer.blade.php`.
5. When enabled, the `<meta name="generator" content="Lychee v7">` tag is omitted from the page `<head>`.
6. When enabled, the text "Lychee" and the example URL `lychee.example.com` inside the misconfiguration warning blade component are replaced by generic placeholders (`your-application` / `your-application.example.com`).
7. The setting is forwarded to the Vue front-end through the existing `InitConfig` resource so UI components can react without additional HTTP requests.

## Non-Goals

- Replacing or customising the Lychee logo or favicon — branding asset replacement is out of scope.
- Hiding the admin-panel settings section labelled "Lychee SE" from the settings page — this setting is secret (`is_secret = 1`) and only visible to admins.
- Removing Lychee branding from API response headers or error pages.
- Per-user or per-album granularity — the setting is global.
- Providing a custom brand name field for operators (replacement text is a generic placeholder, not a configurable string).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-039-01 | A new config row `white_label_enabled` is inserted via a database migration with: `cat = 'lychee SE'`, `value = '0'`, `type_range = 'BOOL'`, `is_secret = 1`, `level = 1`, `order = 3`. | Row present in `configs` table after running the migration; default value `0` leaves behaviour unchanged for existing installations. | Migration is reversible; rolling back removes the row. | If the row already exists the migration must be idempotent (use `insertOrIgnore`). | No telemetry. | Problem statement |
| FR-039-02 | `InitConfig` exposes a new boolean property `is_white_label_enabled` that is `true` only when both Lychee SE is active for the current request **and** `white_label_enabled` config is `'1'`. This mirrors the pattern used by `is_live_metrics_enabled`: `$this->is_se_enabled && request()->configs()->getValueAsBool('white_label_enabled')`. SE-inactive installations always receive `false`, ensuring the feature is exclusive to Lychee Supporters. | Property present in JSON payload from `GET /api/v2/Gallery/Init`; evaluates to `false` when SE is inactive regardless of config value. | TypeScript transformer generates the corresponding field in the `InitConfig` TypeScript type. | If the config key is missing, `getValueAsBool` returns `false`; if SE is inactive, short-circuits to `false` (fail-safe). | No telemetry (config read). | Problem statement; Q-039-03 → Option B |
| FR-039-03 | When `is_white_label_enabled` is `true`, the "Lychee" submenu section (containing the "About", "Changelog", "API", "Source Code", and "Support" items) is hidden entirely in the left navigation drawer. | Section not rendered in DOM; the filter in `useLeftMenu` excludes the section because all its items report `access = false`. | When `is_white_label_enabled` is `false`, the section renders as before (S-039-01 vs S-039-02). | No regression for installations with white label disabled. | No telemetry. | Problem statement |
| FR-039-04 | When `is_white_label_enabled` is `true`, the `<p class="hosted_by">` paragraph containing the "Powered by Lychee" link is hidden in `GalleryFooter.vue`. | Paragraph absent from rendered DOM when white label is active. | Paragraph present when `is_white_label_enabled` is `false`. | No regression for installations with white label disabled. | No telemetry. | Problem statement |
| FR-039-05 | When `is_white_label_enabled` is `true`, the "Powered by Lychee" link is hidden in `resources/views/includes/footer.blade.php`. The `<p class="hosted_by">` element is wrapped in a Blade `@unless` directive using `resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled')` — the same inline `resolve()` pattern used in `vueapp.blade.php` (Q-039-02 → Option A). | Element absent from rendered HTML when `white_label_enabled` is `1`. | Element present when `white_label_enabled` is `0`. | No regression for default installs. | No telemetry. | Problem statement; Q-039-02 → Option A |
| FR-039-06 | When `is_white_label_enabled` is `true`, `<meta name="generator" content="Lychee v7">` is omitted from the page `<head>` rendered by `resources/views/components/meta.blade.php`. The tag is wrapped with `@unless(resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled'))` (Q-039-02 → Option A). | Meta tag absent from page source when white label is active. | Meta tag present when `white_label_enabled` is `0`. | No regression for default installs. | No telemetry. | Problem statement; Q-039-02 → Option A |
| FR-039-07 | When `is_white_label_enabled` is `true`, the misconfiguration warning blade component (`resources/views/components/warning-misconfiguration.blade.php`) replaces: (a) "Lychee" in the `<h1>` text with "your-application"; (b) `lychee.example.com` in the `APP_URL` example `<pre>` block with `your-application.example.com`. The conditional uses the same `resolve(\App\Repositories\ConfigManager::class)` inline pattern (Q-039-01 → Option A: hardcoded generic placeholder; Q-039-02 → Option A). | Page source shows generic placeholders instead of "Lychee" and "lychee.example.com" when white label is active. | Original strings present when `white_label_enabled` is `0`. | No regression for default installs. | No telemetry. | Problem statement; Q-039-01 → Option A; Q-039-02 → Option A |
| FR-039-08 | When `is_white_label_enabled` is `true`, the `Lychee` / `Lychee SE` branding `<div>` at the bottom of the basic-auth section in `LoginForm.vue` (line reading `Lychee <span v-if="is_se_enabled" class="text-primary-500">SE</span>`) is hidden. The entire `<div class="text-muted-color text-right ...">` element must not render when white label is active, regardless of whether SE is enabled or not. | Branding `<div>` absent from rendered DOM when white label is active. | Element present when `is_white_label_enabled` is `false` (S-039-09 vs S-039-10). | No regression for installations with white label disabled. | No telemetry. | Problem statement (2026-05-04 update) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-039-01 | The `white_label_enabled` setting is stored with `is_secret = 1` and must not appear in any public-facing settings response. The `GET /api/v2/Settings` endpoint returns 403 for non-admin users, so no dedicated absence test is required; the `is_secret` filtering ensures the key is never returned to any caller regardless. | Privacy — operators must not expose the white-label toggle to end-users. | The `GET /api/v2/Settings` endpoint returns 403 for non-admin callers (enforced by existing admin middleware); no additional test is needed (I7 removed). | Existing admin-route middleware; `is_secret = 1` DB flag. | Problem statement |
| NFR-039-02 | Enabling or disabling `white_label_enabled` takes effect on the next page load without requiring a server restart or cache flush (config is read per-request). | Operational simplicity — operators need immediate feedback when toggling. | Manual verification: toggle setting, reload page, observe UI change. | Config read pipeline (`request()->configs()`). | Implementation requirement |
| NFR-039-03 | The migration must be backward-compatible: `value = '0'` (disabled by default) so no existing gallery changes behaviour after the upgrade. | Backward compatibility — existing installations must not be affected. | All existing Feature_v2 tests continue to pass after the migration. | Migration default value. | Implementation requirement |
| NFR-039-04 | The `is_white_label_enabled` field in `InitConfig` must be included in the TypeScript type generated by `spatie/typescript-transformer` so the Vue layer can consume it without manual type casting. | Type safety — TypeScript compilation (`npm run check`) must pass. | `npm run check` succeeds after the field is added. | `spatie/typescript-transformer`, `#[TypeScript()]` attribute on `InitConfig`. | Implementation requirement |

## UI / Interaction Mock-ups

### Login Form — White Label OFF (default)

```
┌────────────────────────────────────┐
│  [Username          ]              │
│  [Password          ]              │
│  [ ] Remember me                   │
│                    Lychee SE  ←    │
│  [  Cancel  ]  [  Sign in  ]       │
└────────────────────────────────────┘
```

### Login Form — White Label ON

```
┌────────────────────────────────────┐
│  [Username          ]              │
│  [Password          ]              │
│  [ ] Remember me                   │
│  (Lychee SE line hidden)           │
│  [  Cancel  ]  [  Sign in  ]       │
└────────────────────────────────────┘
```

### Left Menu — White Label OFF (default)

```
┌─────────────────────────────────┐
│ Left drawer                     │
│  ...                            │
│  ─── Lychee ───                 │
│    ⓘ  About                     │
│    📋 Changelog                 │
│    📖 API                       │
│    </> Source Code              │
│    ♥  Support                   │
└─────────────────────────────────┘
```

### Left Menu — White Label ON

```
┌─────────────────────────────────┐
│ Left drawer                     │
│  ...                            │
│  (Lychee section hidden)        │
└─────────────────────────────────┘
```

### Footer — White Label OFF (default)

```
┌─────────────────────────────────────────────┐
│  © Copyright …                              │
│  Additional text …                          │
│  Powered by Lychee   ← always visible       │
│  Contact ↗                                  │
└─────────────────────────────────────────────┘
```

### Footer — White Label ON

```
┌─────────────────────────────────────────────┐
│  © Copyright …                              │
│  Additional text …                          │
│  (Powered by Lychee hidden)                 │
│  Contact ↗                                  │
└─────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-039-01 | `white_label_enabled = 0` (default) — Left menu shows "Lychee" submenu; login form shows "Lychee SE" branding line; footer shows "Powered by Lychee"; `<meta name="generator">` present; warning blade shows "Lychee" and "lychee.example.com". |
| S-039-02 | `white_label_enabled = 1`, SE active — `is_white_label_enabled` evaluates to `true`; left menu omits "Lychee" submenu; login form hides "Lychee SE" branding line; footer hides "Powered by Lychee"; `<meta name="generator">` absent; warning blade shows "your-application" and "your-application.example.com". |
| S-039-03 | Migration rollback — `white_label_enabled` row removed; behaviour reverts to S-039-01. |
| S-039-04 | `white_label_enabled` key absent from `configs` table (edge case) — `getValueAsBool` returns `false`; behaviour equivalent to S-039-01. |
| S-039-05 | `white_label_enabled = 1`, SE **not** active — `is_white_label_enabled` short-circuits to `false` (SE gate in `InitConfig`); all Lychee branding remains visible; equivalent to S-039-01. This is intentional: white-label is a Lychee Supporter benefit; operators who let their SE licence expire lose the suppression. |
| S-039-06 | `white_label_enabled = 1`, login accessed via modal (`LoginModal.vue`) — branding line hidden identically to the full-page `LoginPage` (same `LoginForm` component, same flag). |
| S-039-07 | Login form opened with `is_basic_auth_enabled = false` — "Lychee SE" `<div>` is inside the `<template v-if="is_basic_auth_enabled">` block; it never renders anyway; no conflict with white-label flag. |
| S-039-08 | `white_label_enabled = 1` toggled at runtime — change reflected on next page load without server restart (per NFR-039-02). |
| S-039-09 | `white_label_enabled = 0` — Login form branding `<div>` visible; shows "Lychee" when SE is off, "Lychee SE" (with coloured span) when SE is on. |
| S-039-10 | `white_label_enabled = 1` — Login form branding `<div>` absent regardless of SE status. |

## Test Strategy

- **REST:** Feature_v2 test for `GET /api/v2/Gallery/Init` asserts `is_white_label_enabled` is present with value `false` when the config is `0` or SE is inactive.
- **UI (component):** Jest/Vitest unit test for `useLeftMenu` confirms the "Lychee" section items all have `access = false` when `is_white_label_enabled = true`.
- **UI (component):** Jest/Vitest unit test for `LoginForm.vue` confirms the branding `<div>` is absent from the rendered output when `is_white_label_enabled = true`, and present when `false` (covers S-039-09, S-039-10).
- **UI (component):** Jest/Vitest unit test for `GalleryFooter.vue` confirms the "Powered by Lychee" paragraph is absent from the rendered output when `is_white_label_enabled = true`.
- **Blade:** PHPUnit test or snapshot assertion for `meta.blade.php` confirms `<meta name="generator">` tag is absent when `white_label_enabled` config is `1`.
- **Blade:** PHPUnit test or snapshot assertion for `warning-misconfiguration.blade.php` confirms "your-application" / "your-application.example.com" appear when `white_label_enabled` is `1`.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-039-01 | `white_label_enabled` config key — boolean stored in `configs` table, `cat = 'lychee SE'`, `is_secret = 1`, `level = 1`, `order = 3`, default `'0'`. | application (migration), REST (InitConfig) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-039-01 | REST GET /api/v2/Gallery/Init | `InitConfig` payload gains `is_white_label_enabled: boolean` field. | Populated from `white_label_enabled` config key; default `false`. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-039-01 | Left menu — Lychee section visible | `is_white_label_enabled = false` → "Lychee" submenu items rendered. |
| UI-039-02 | Left menu — Lychee section hidden | `is_white_label_enabled = true` → "Lychee" submenu section absent from DOM. |
| UI-039-03 | Footer — Powered by Lychee visible | `is_white_label_enabled = false` → `<p class="hosted_by">` rendered. |
| UI-039-04 | Footer — Powered by Lychee hidden | `is_white_label_enabled = true` → `<p class="hosted_by">` absent from DOM. |
| UI-039-05 | Meta generator present | `white_label_enabled = 0` → `<meta name="generator" content="Lychee v7">` in page `<head>`. |
| UI-039-06 | Meta generator absent | `white_label_enabled = 1` → `<meta name="generator">` omitted from page `<head>`. |
| UI-039-07 | Warning blade — Lychee branding | `white_label_enabled = 0` → text reads "…misconfigured Lychee" and "lychee.example.com". |
| UI-039-08 | Warning blade — generic branding | `white_label_enabled = 1` → text reads "…misconfigured your-application" and "your-application.example.com". |
| UI-039-09 | Login form — Lychee branding visible | `is_white_label_enabled = false` → branding `<div>` rendered (shows "Lychee" or "Lychee SE"). |
| UI-039-10 | Login form — Lychee branding hidden | `is_white_label_enabled = true` → branding `<div>` absent from DOM (regardless of SE status). |

## Telemetry & Observability

No new telemetry events are introduced. This feature only gates rendering of static content.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` to add Feature 039.
- Update `docs/specs/4-architecture/knowledge-map.md` to record the new `white_label_enabled` config key under the SE config group.

## Fixtures & Sample Data

No new test-vector fixtures required. Existing Feature_v2 test infrastructure (SQLite in-memory database with seeded config rows) is sufficient.

## Spec DSL

```yaml
domain_objects:
  - id: DO-039-01
    name: white_label_enabled
    fields:
      - name: key
        type: string
        constraints: "= 'white_label_enabled'"
      - name: value
        type: string
        constraints: "'0' | '1'"
      - name: cat
        type: string
        constraints: "= 'lychee SE'"
      - name: is_secret
        type: bool
        constraints: "= true"
      - name: level
        type: int
        constraints: "= 1"
      - name: order
        type: int
        constraints: "= 3"
routes:
  - id: API-039-01
    method: GET
    path: /api/v2/Gallery/Init
    notes: gains is_white_label_enabled boolean field
ui_states:
  - id: UI-039-01
    description: Left menu Lychee section visible (white label OFF)
  - id: UI-039-02
    description: Left menu Lychee section hidden (white label ON)
  - id: UI-039-03
    description: Footer Powered by Lychee visible (white label OFF)
  - id: UI-039-04
    description: Footer Powered by Lychee hidden (white label ON)
  - id: UI-039-05
    description: Meta generator tag present (white label OFF)
  - id: UI-039-06
    description: Meta generator tag absent (white label ON)
  - id: UI-039-07
    description: Warning blade Lychee branding (white label OFF)
  - id: UI-039-08
    description: Warning blade generic branding (white label ON)
  - id: UI-039-09
    description: Login form Lychee/SE branding visible (white label OFF)
  - id: UI-039-10
    description: Login form Lychee/SE branding hidden (white label ON)
```

## Appendix

### Affected source files (reference for planning)

| File | Change |
|------|--------|
| `database/migrations/<date>_add_white_label_config.php` | New migration inserting `white_label_enabled` config row |
| `app/Http/Resources/GalleryConfigs/InitConfig.php` | Add `public bool $is_white_label_enabled` property; populate as `$this->is_se_enabled && request()->configs()->getValueAsBool('white_label_enabled')` |
| `resources/js/composables/contextMenus/leftMenu.ts` | Gate the "Lychee" submenu section on `!is_white_label_enabled` |
| `resources/js/components/forms/auth/LoginForm.vue` | Wrap the branding `<div>` in `v-if="!is_white_label_enabled"` |
| `resources/js/components/footers/GalleryFooter.vue` | Wrap `<p class="hosted_by">` in `v-if="!lycheeStore.is_white_label_enabled"` |
| `resources/views/includes/footer.blade.php` | Wrap "Powered by Lychee" `<p>` with `@unless(resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled'))` |
| `resources/views/components/meta.blade.php` | Wrap `<meta name="generator">` with `@unless(resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled'))` |
| `resources/views/components/warning-misconfiguration.blade.php` | Use `@if(resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled'))` to swap "Lychee" → "your-application" and "lychee.example.com" → "your-application.example.com" |
| `lang/en/settings.php` (and all locales) | Add translation key for `white_label_enabled` description under `lychee_se` group |

---
*Last updated: 2026-05-04 (rev 3 — resolved Q-039-01/02/03; removed I7; updated FR-039-02 SE gate; updated Blade mechanism)*
