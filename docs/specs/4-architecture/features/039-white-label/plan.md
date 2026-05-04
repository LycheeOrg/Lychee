# Feature Plan 039 – Lychee White Label

_Linked specification:_ `docs/specs/4-architecture/features/039-white-label/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-05-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Operators who white-label Lychee expect zero user-visible references to the Lychee brand when `white_label_enabled = 1`. Success is measured by:

1. All eight UI suppression points (FR-039-03 – FR-039-08, FR-039-05, FR-039-06, FR-039-07) behave correctly for both `0` and `1` config values.
2. `is_white_label_enabled` is present in the `InitConfig` JSON payload and in the generated TypeScript type.
3. `white_label_enabled` is absent from the public settings API response.
4. All existing Feature_v2 tests continue to pass after the migration.
5. `npm run check` and `php artisan test` pass.

## Scope Alignment

- **In scope:**
  - Database migration for `white_label_enabled` config key.
  - `InitConfig` PHP resource — new `is_white_label_enabled` field.
  - Left-menu composable — gate "Lychee" section.
  - `LoginForm.vue` — gate branding `<div>`.
  - `GalleryFooter.vue` — gate "Powered by Lychee" paragraph.
  - `footer.blade.php` — gate "Powered by Lychee" server-side.
  - `meta.blade.php` — gate `<meta name="generator">`.
  - `warning-misconfiguration.blade.php` — swap "Lychee" / "lychee.example.com" with generic placeholders.
  - `lang/en/settings.php` (and all 21 locales) — add translation key.
  - Feature_v2 test for `InitConfig` and `Settings` public response.
  - Vue component unit tests for login form and footer.

- **Out of scope:**
  - Logo/favicon replacement (see Non-Goals in spec).
  - Custom brand name field (generic placeholders only — pending Q-039-01).
  - API response header changes.

## Dependencies & Interfaces

- `BaseConfigMigration` pattern (see `database/migrations/2024_09_27_144741_add_supporter_fields.php`).
- `InitConfig` Spatie Data class and `#[TypeScript()]` attribute — TypeScript transformer must run after the property is added.
- `LycheeState` Pinia store — `is_white_label_enabled` must be added to the store's reactive properties.
- `request()->configs()->getValueAsBool()` — existing config read pipeline.
- `is_secret = 1` filtering in the settings query pipeline (NFR-039-01).

## Assumptions & Risks

- **Assumptions:**
  - The TypeScript transformer (`spatie/typescript-transformer`) regenerates types as part of `npm run check` or a separate artisan command; no manual type file editing is needed.
  - Blade views read configs through the standard `DB::table('configs')` pipeline used by all other white-label-adjacent blade checks (e.g., `@if(config_value('white_label_enabled'))` pattern used elsewhere, or via a view composer). **Clarification Q-039-02 is open** — the exact mechanism to pass the config to Blade views needs confirmation.
  - The `vite/index.html` file is the dev-only entry point; the production warning blade is `resources/views/components/warning-misconfiguration.blade.php`. Both contain the same warning text; only the blade file is within scope per FR-039-07.

- **Risks / Mitigations:**
  - **Risk:** `LycheeState` store may not expose `is_white_label_enabled` to `LoginForm.vue` if the store is initialised lazily. **Mitigation:** `LoginForm.vue` already accesses `lycheeStore` via `storeToRefs` for `is_se_enabled` and `is_basic_auth_enabled`; adding `is_white_label_enabled` follows the same pattern.
  - **Risk:** Translation files for all 22 locales must be updated consistently. **Mitigation:** Add the English key first; mirror to other locales using the existing pattern (other locales can default to the English description until translated).
  - **Risk:** Open question Q-039-03 (whether SE being inactive should silently disable white-label) could require spec revision. **Mitigation:** Proceed with the spec-stated behaviour (SE level=1 gates visibility in the admin UI, but the config value is read and applied regardless); revisit if Q-039-03 is resolved differently.

## Implementation Drift Gate

After each increment: run `php artisan test` (must be green) and `make phpstan` (zero new errors). After UI increments: run `npm run check`. Record results in tasks.md.

## Increment Map

### I1 — Database Migration (≤30 min)
- _Goal:_ Insert `white_label_enabled` config row. FR-039-01.
- _Preconditions:_ None.
- _Steps:_
  1. Create `database/migrations/<date>_add_white_label_config.php` extending `BaseConfigMigration`.
  2. Insert row: `key='white_label_enabled'`, `value='0'`, `type_range='BOOL'`, `cat='lychee SE'`, `is_secret=1`, `level=1`, `order=3`.
  3. `down()` deletes the row by key.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ Migration runs cleanly; all existing tests pass; row absent from public settings response.

### I2 — InitConfig PHP Resource (≤30 min)
- _Goal:_ Expose `is_white_label_enabled` in `InitConfig`. FR-039-02, NFR-039-04.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `public bool $is_white_label_enabled;` property to `InitConfig`.
  2. Populate in constructor: `$this->is_white_label_enabled = request()->configs()->getValueAsBool('white_label_enabled');`
  3. Add Feature_v2 test asserting field is present in `GET /api/v2/Gallery/Init` response with value `false` by default.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ Test green; TypeScript type regenerated (run transformer if needed).

### I3 — Vue Front-End: Left Menu & Login Form (≤45 min)
- _Goal:_ Gate the "Lychee" submenu section and login branding. FR-039-03, FR-039-08, S-039-02, S-039-09, S-039-10.
- _Preconditions:_ I2 complete; `is_white_label_enabled` available in `LycheeState` store.
- _Steps:_
  1. Add `is_white_label_enabled` to `LycheeState` store reactive properties (if not already via TypeScript transformer).
  2. In `leftMenu.ts`: destructure `is_white_label_enabled` from `lycheeStore`; set `access: !is_white_label_enabled.value` (or equivalent) on each item in the "Lychee" section.
  3. In `LoginForm.vue`: add `is_white_label_enabled` to destructuring; wrap the branding `<div>` with `v-if="!is_white_label_enabled"`.
  4. Write/update Vitest unit test for `useLeftMenu` (S-039-02).
  5. Write/update Vitest unit test for `LoginForm.vue` (S-039-09, S-039-10).
- _Commands:_ `npm run check`
- _Exit:_ `npm run check` passes; both component tests green.

### I4 — Vue Front-End: Gallery Footer (≤20 min)
- _Goal:_ Gate "Powered by Lychee" in `GalleryFooter.vue`. FR-039-04, S-039-02.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Import/reference `lycheeStore` (or pass as prop) in `GalleryFooter.vue`; destructure `is_white_label_enabled`.
  2. Wrap `<p class="hosted_by ...">` with `v-if="!is_white_label_enabled"`.
  3. Write/update Vitest unit test for `GalleryFooter.vue` (S-039-02 footer path).
- _Commands:_ `npm run check`
- _Exit:_ `npm run check` passes; test green.

### I5 — Blade: Footer, Meta, Warning (≤45 min)
- _Goal:_ Gate all three blade branding points. FR-039-05, FR-039-06, FR-039-07. Resolve Q-039-02 mechanism.
- _Preconditions:_ I1 complete; config mechanism for Blade confirmed (Q-039-02).
- _Steps:_
  1. In `footer.blade.php`: wrap "Powered by Lychee" `<p>` with an `@if` / `@unless` Blade directive reading `white_label_enabled` from the config.
  2. In `meta.blade.php`: wrap `<meta name="generator">` with same conditional.
  3. In `warning-misconfiguration.blade.php`: use `@if` to swap "Lychee" → "your-application" and "lychee.example.com" → "your-application.example.com".
  4. Write PHPUnit blade tests for all three files.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ All blade tests green.

### I6 — Translation Keys (≤30 min)
- _Goal:_ Add `white_label_enabled` translation key to all locale files.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `'white_label_enabled' => 'Hide Lychee branding (white label)'` (and description) to `lang/en/settings.php` under the `lychee_se` group.
  2. Mirror the English string to the remaining 21 locale `settings.php` files.
- _Commands:_ `php artisan test`
- _Exit:_ No missing-key warnings; existing tests green.

### I7 — NFR-039-01 Test: Secret Key Not Exposed (≤20 min)
- _Goal:_ Verify `white_label_enabled` is absent from the public settings API. NFR-039-01.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add Feature_v2 test: call `GET /api/v2/Settings` as non-admin; assert `white_label_enabled` key absent.
- _Commands:_ `php artisan test`
- _Exit:_ Test green.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-039-01 | I1, I2 (default state) | Baseline — no change for existing installations |
| S-039-02 | I3, I4, I5 | All UI suppression points active |
| S-039-03 | I1 (down()) | Migration rollback |
| S-039-04 | I2 | `getValueAsBool` fail-safe |
| S-039-05 | I3 | SE inactive + white label on |
| S-039-06 | I3 | Modal path via `LoginModal.vue` → same `LoginForm` |
| S-039-07 | I3 | `is_basic_auth_enabled = false` — branding `<div>` inside `v-if` block |
| S-039-08 | I1, I2 | Runtime toggle; no restart needed |
| S-039-09 | I3 | Login form branding visible (white label OFF) |
| S-039-10 | I3 | Login form branding hidden (white label ON) |

## Analysis Gate

Pending resolution of Q-039-01 (custom vs generic placeholder) and Q-039-02 (Blade config access mechanism). Q-039-03 (SE-inactive gating) is a low-priority edge case and should not block implementation. Gate should be run once Q-039-01 and Q-039-02 are resolved before starting I5.

## Exit Criteria

- [ ] `php artisan test` green (no regressions).
- [ ] `make phpstan` zero new errors.
- [ ] `npm run check` passes (TypeScript types correct).
- [ ] `white_label_enabled` absent from public settings API response (NFR-039-01).
- [ ] All 10 scenarios (S-039-01 – S-039-10) covered by automated tests.
- [ ] Translation key present in all 22 locale files.
- [ ] Roadmap and knowledge-map updated.

## Follow-ups / Backlog

- Resolve Q-039-01: whether operators want a configurable custom brand name rather than the hardcoded "your-application" placeholder.
- Consider Q-039-03: gating `is_white_label_enabled` on SE being active at runtime (not just `level=1` in the admin UI).
- `vite/index.html` contains the same warning text as the blade component — if that file is ever served in production it should also be patched (deferred; currently dev-only).

---
*Last updated: 2026-05-04*
