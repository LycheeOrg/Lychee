# Feature 039 – Lychee White Label

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-05-04 (rev 3 — moved to `.env`/`features.php`) |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/039-white-label/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/039-white-label/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Operators who use Lychee as an embedded or branded gallery application need a way to hide all visible references to the Lychee project (name, links, and generator metadata) from end-users, so the product appears as their own. This feature introduces a `.env` flag `WHITE_LABEL_ENABLED` (surfaced in `config/features.php` as `features.white_label_enabled`, default `false`) that, when enabled, suppresses the "Lychee" section in the left menu, the "Lychee / SE" branding line in the login form, the "Powered by Lychee" footer link, the `<meta name="generator">` tag, and Lychee branding inside the misconfiguration warning blade component.

Affected modules: `application` (`config/features.php`, `.env.example`), `REST` (`InitConfig` resource), `UI` (left-menu composable, `LoginForm.vue`, `GalleryFooter.vue`), `blade` (`meta.blade.php`, `warning-misconfiguration.blade.php`).

## Goals

1. A `.env` flag `WHITE_LABEL_ENABLED` (in `config/features.php` as `features.white_label_enabled`, default `false`) controls all white-labelling behaviour from a single toggle.
2. When enabled, the "Lychee" submenu section is hidden in the left navigation drawer.
3. When enabled, the `Lychee / Lychee SE` branding line at the bottom of the login form (`LoginForm.vue`) is hidden.
4. When enabled, the "Powered by Lychee" paragraph is hidden in `GalleryFooter.vue` and `footer.blade.php`.
5. When enabled, the `<meta name="generator" content="Lychee v7">` tag is omitted from the page `<head>`.
6. When enabled, the text "Lychee" and the example URL `lychee.example.com` inside the misconfiguration warning blade component are replaced by generic placeholders (`your-application` / `your-application.example.com`).
7. The setting is forwarded to the Vue front-end through the existing `InitConfig` resource so UI components can react without additional HTTP requests.

## Non-Goals

- Replacing or customising the Lychee logo or favicon — branding asset replacement is out of scope.
- Hiding the admin-panel settings section labelled "Lychee SE" from the settings page.
- Removing Lychee branding from API response headers or error pages.
- Per-user or per-album granularity — the setting is global.
- Providing a custom brand name field for operators (replacement text is a generic placeholder, not a configurable string).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-039-01 | `WHITE_LABEL_ENABLED` is read from the environment into `config/features.php` as `features.white_label_enabled` (boolean, default `false`). This replaces the earlier database-migration design. | `Features::active('white_label_enabled')` returns `true` when the env var is set to `true`; returns `false` (default) for all existing installations. | `php artisan config:show features` lists the key; the `Features::active()` helper throws `FeaturesDoesNotExistsException` if the key is missing from `features.php`. | No migration, no DB row — the flag is env-only and requires a process restart (or `php artisan config:clear`) to take effect. | No telemetry. | Problem statement (rev 3) |
| FR-039-02 | `InitConfig` exposes a new boolean property `is_white_label_enabled` that is `true` only when both Lychee SE is active for the current request **and** `Features::active('white_label_enabled')` returns `true`. SE-inactive installations always receive `false`, ensuring the feature is exclusive to Lychee Supporters. | Property present in JSON payload from `GET /api/v2/Gallery/Init`; evaluates to `false` when SE is inactive regardless of env flag. | TypeScript transformer generates the corresponding field in the `InitConfig` TypeScript type. | If SE is inactive the expression short-circuits to `false` (fail-safe). | No telemetry (config read). | Problem statement; Q-039-03 → Option B |
| FR-039-03 | When `is_white_label_enabled` is `true`, the "Lychee" submenu section (containing the "About", "Changelog", "API", "Source Code", and "Support" items) is hidden entirely in the left navigation drawer. | Section not rendered in DOM; the filter in `useLeftMenu` excludes the section because all its items report `access = false`. | When `is_white_label_enabled` is `false`, the section renders as before (S-039-01 vs S-039-02). | No regression for installations with white label disabled. | No telemetry. | Problem statement |
| FR-039-04 | When `is_white_label_enabled` is `true`, the `<p class="hosted_by">` paragraph containing the "Powered by Lychee" link is hidden in `GalleryFooter.vue`. | Paragraph absent from rendered DOM when white label is active. | Paragraph present when `is_white_label_enabled` is `false`. | No regression for installations with white label disabled. | No telemetry. | Problem statement |
| FR-039-05 | When `Features::active('white_label_enabled')` is `true`, the "Powered by Lychee" link is hidden in `resources/views/includes/footer.blade.php`. The `<p class="hosted_by">` element is wrapped with `Features::inactive('white_label_enabled')`. | Element absent from rendered HTML when the env flag is active. | Element present when the env flag is `false`. | No regression for default installs. | No telemetry. | Problem statement (rev 3) |
| FR-039-06 | When `Features::active('white_label_enabled')` is `true`, `<meta name="generator" content="Lychee v7">` is omitted from the page `<head>` rendered by `resources/views/components/meta.blade.php`. The tag is wrapped with `@if(Features::inactive('white_label_enabled'))`. | Meta tag absent from page source when env flag is active. | Meta tag present when env flag is `false`. | No regression for default installs. | No telemetry. | Problem statement (rev 3) |
| FR-039-07 | When `Features::active('white_label_enabled')` is `true`, the misconfiguration warning blade component (`resources/views/components/warning-misconfiguration.blade.php`) replaces: (a) "Lychee" in the `<h1>` text with "your-application"; (b) `lychee.example.com` in the `APP_URL` example `<pre>` block with `your-application.example.com`. The conditional uses `@if(Features::active('white_label_enabled'))`. | Page source shows generic placeholders instead of "Lychee" and "lychee.example.com" when env flag is active. | Original strings present when env flag is `false`. | No regression for default installs. | No telemetry. | Problem statement (rev 3) |
| FR-039-08 | When `is_white_label_enabled` is `true`, the `Lychee` / `Lychee SE` branding `<div>` at the bottom of the basic-auth section in `LoginForm.vue` (line reading `Lychee <span v-if="is_se_enabled" class="text-primary-500">SE</span>`) is hidden. The entire `<div class="text-muted-color text-right ...">` element must not render when white label is active, regardless of whether SE is enabled or not. | Branding `<div>` absent from rendered DOM when white label is active. | Element present when `is_white_label_enabled` is `false` (S-039-09 vs S-039-10). | No regression for installations with white label disabled. | No telemetry. | Problem statement (2026-05-04 update) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-039-01 | The `WHITE_LABEL_ENABLED` flag is an operator-level env variable and is never exposed to end-users through any API response. | Privacy — operators must not expose the white-label toggle to end-users. | The flag is only readable from server config; no API endpoint surfaces it. | `config/features.php`; `Features::active()` helper. | Problem statement (rev 3) |
| NFR-039-02 | Changing `WHITE_LABEL_ENABLED` takes effect after the next `php artisan config:clear` and process restart (or on the next request in environments without config caching). | Operational simplicity — standard Laravel config caching rules apply. | Manual verification: change env var, clear config cache, reload page, observe UI change. | Laravel config caching (`php artisan config:cache`). | Implementation requirement (rev 3) |
| NFR-039-03 | The default value of `WHITE_LABEL_ENABLED` is `false`, so no existing installation changes behaviour after upgrading. | Backward compatibility — existing installations must not be affected. | All existing Feature_v2 tests continue to pass with the default. | `features.php` default value. | Implementation requirement |
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
| S-039-01 | `WHITE_LABEL_ENABLED=false` (default) — Left menu shows "Lychee" submenu; login form shows "Lychee SE" branding line; footer shows "Powered by Lychee"; `<meta name="generator">` present; warning blade shows "Lychee" and "lychee.example.com". |
| S-039-02 | `WHITE_LABEL_ENABLED=true`, SE active — `is_white_label_enabled` evaluates to `true`; left menu omits "Lychee" submenu; login form hides "Lychee SE" branding line; footer hides "Powered by Lychee"; `<meta name="generator">` absent; warning blade shows "your-application" and "your-application.example.com". |
| S-039-03 | ~~Migration rollback~~ — N/A; no database migration. Reverting to `WHITE_LABEL_ENABLED=false` (or removing the env var) restores S-039-01 behaviour after config cache is cleared. |
| S-039-04 | `WHITE_LABEL_ENABLED` absent from env — `features.white_label_enabled` defaults to `false`; behaviour equivalent to S-039-01. |
| S-039-05 | `WHITE_LABEL_ENABLED=true`, SE **not** active — `is_white_label_enabled` short-circuits to `false` (SE gate in `InitConfig`); all Lychee branding remains visible; equivalent to S-039-01. This is intentional: white-label is a Lychee Supporter benefit; operators who let their SE licence expire lose the suppression. Blade templates check `Features::active()` directly and are not SE-gated — they respond to the env flag alone. |
| S-039-06 | `WHITE_LABEL_ENABLED=true`, login accessed via modal (`LoginModal.vue`) — branding line hidden identically to the full-page `LoginPage` (same `LoginForm` component, same flag). |
| S-039-07 | Login form opened with `is_basic_auth_enabled = false` — "Lychee SE" `<div>` is inside the `<template v-if="is_basic_auth_enabled">` block; it never renders anyway; no conflict with white-label flag. |
| S-039-08 | `WHITE_LABEL_ENABLED` toggled — change reflected after `php artisan config:clear` and process restart (or next request in non-cached environments). |
| S-039-09 | `WHITE_LABEL_ENABLED=false` — Login form branding `<div>` visible; shows "Lychee" when SE is off, "Lychee SE" (with coloured span) when SE is on. |
| S-039-10 | `WHITE_LABEL_ENABLED=true` — Login form branding `<div>` absent regardless of SE status. |

## Test Strategy

- **REST:** Feature_v2 test for `GET /api/v2/Gallery/Init` asserts `is_white_label_enabled` is present with value `false` when the env flag is `false` or SE is inactive.
- **REST:** Feature_v2 test asserts `is_white_label_enabled` remains `false` when `features.white_label_enabled` is set to `true` at runtime but SE is inactive (SE gate).
- **UI (component):** Jest/Vitest unit test for `useLeftMenu` confirms the "Lychee" section items all have `access = false` when `is_white_label_enabled = true`.
- **UI (component):** Jest/Vitest unit test for `LoginForm.vue` confirms the branding `<div>` is absent from the rendered output when `is_white_label_enabled = true`, and present when `false` (covers S-039-09, S-039-10).
- **UI (component):** Jest/Vitest unit test for `GalleryFooter.vue` confirms the "Powered by Lychee" paragraph is absent from the rendered output when `is_white_label_enabled = true`.
- **Blade:** PHPUnit test or snapshot assertion for `meta.blade.php` confirms `<meta name="generator">` tag is absent when `features.white_label_enabled` is `true`.
- **Blade:** PHPUnit test or snapshot assertion for `warning-misconfiguration.blade.php` confirms "your-application" / "your-application.example.com" appear when `features.white_label_enabled` is `true`.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-039-01 | `WHITE_LABEL_ENABLED` — boolean env variable, read into `config/features.php` as `features.white_label_enabled`, default `false`. Accessed via `Features::active('white_label_enabled')` / `Features::inactive('white_label_enabled')`. | application (`features.php`, `.env.example`), REST (InitConfig), blade templates |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-039-01 | REST GET /api/v2/Gallery/Init | `InitConfig` payload gains `is_white_label_enabled: boolean` field. | Populated from `Features::active('white_label_enabled')` AND SE active; default `false`. |

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
