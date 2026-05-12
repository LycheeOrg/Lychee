# Feature Plan 039 – Lychee White Label

_Linked specification:_ `docs/specs/4-architecture/features/039-white-label/spec.md`  
_Status:_ Implemented  
_Last updated:_ 2026-05-04 (rev 3 — moved storage from DB to `.env`/`features.php`)

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Operators who white-label Lychee expect zero user-visible references to the Lychee brand when `WHITE_LABEL_ENABLED=true`. Success is measured by:

1. All eight UI suppression points (FR-039-03 – FR-039-08, FR-039-05, FR-039-06, FR-039-07) behave correctly for both `0` and `1` config values.
2. `is_white_label_enabled` is present in the `InitConfig` JSON payload and in the generated TypeScript type.
3. The `GET /api/v2/Settings` endpoint returns 403 for non-admin users; no dedicated absence test is required (NFR-039-01; I7 removed).
4. All existing Feature_v2 tests continue to pass after the migration.
5. `npm run check` and `php artisan test` pass.

## Scope Alignment

- **In scope:**
  - `config/features.php` entry for `white_label_enabled` (env `WHITE_LABEL_ENABLED`, default `false`).
  - `.env.example` entry with SE disclaimer.
  - `InitConfig` PHP resource — new `is_white_label_enabled` field (SE-gated: `$is_se_enabled && Features::active('white_label_enabled')`).
  - Left-menu composable — gate "Lychee" section.
  - `LoginForm.vue` — gate branding `<div>`.
  - `GalleryFooter.vue` — gate "Powered by Lychee" paragraph.
  - `footer.blade.php` — gate "Powered by Lychee" server-side using `Features::inactive('white_label_enabled')`.
  - `meta.blade.php` — gate `<meta name="generator">` using `Features::inactive('white_label_enabled')`.
  - `warning-misconfiguration.blade.php` — swap "Lychee" / "lychee.example.com" with hardcoded generic placeholders using `Features::active('white_label_enabled')`.
  - Feature_v2 test for `InitConfig` response.

- **Out of scope:**
  - Database migration (replaced by env variable).
  - Translation keys (no settings UI entry required).
  - Logo/favicon replacement (see Non-Goals in spec).
  - Custom brand name field for operators.
  - API response header changes.
  - Dedicated test for white-label absence from Settings API.

## Dependencies & Interfaces

- `config/features.php` and the `Features` helper (`app/Assets/Features.php`) — `Features::active('white_label_enabled')` returns `true`/`false` and is usable in both PHP and Blade (registered as global alias `Features`).
- `InitConfig` Spatie Data class and `#[TypeScript()]` attribute — TypeScript transformer must run after the property is added.
- `LycheeState` Pinia store — `is_white_label_enabled` must be added to the store's reactive properties.

## Assumptions & Risks

- **Assumptions:**
  - The TypeScript transformer (`spatie/typescript-transformer`) regenerates types as part of `npm run check` or a separate artisan command; no manual type file editing is needed.
  - Blade views use `Features::active('white_label_enabled')` / `Features::inactive('white_label_enabled')` directly; the `Features` alias is registered globally in `config/app.php`.
  - The `vite/index.html` file is the dev-only entry point; the production warning blade is `resources/views/components/warning-misconfiguration.blade.php`. Only the blade file is within scope per FR-039-07.

- **Risks / Mitigations:**
  - **Risk:** `LycheeState` store may not expose `is_white_label_enabled` to `LoginForm.vue` if the store is initialised lazily. **Mitigation:** `LoginForm.vue` already accesses `lycheeStore` via `storeToRefs` for `is_se_enabled` and `is_basic_auth_enabled`; adding `is_white_label_enabled` follows the same pattern.
  - **Risk (resolved — Q-039-03 Option B):** When SE licence expires, `is_white_label_enabled` evaluates to `false` and Lychee branding reappears in Vue components. Blade templates check `Features::active()` directly and are not SE-gated. This is intentional: white-label is a Lychee Supporter exclusive. No mitigation needed; operators who stop their support accept this behaviour.

## Implementation Drift Gate

After each increment: run `php artisan test` (must be green) and `make phpstan` (zero new errors). After UI increments: run `npm run check`. Record results in tasks.md.

## Increment Map

### I1 — Environment Variable & Features Config (≤15 min) ✓
- _Goal:_ Add `WHITE_LABEL_ENABLED` to `config/features.php` and `.env.example`. FR-039-01.
- _Preconditions:_ None.
- _Steps:_
  1. Add `'white_label_enabled' => (bool) env('WHITE_LABEL_ENABLED', false)` to `config/features.php`.
  2. Add commented entry to `.env.example` with SE disclaimer.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ `Features::active('white_label_enabled')` returns `false` by default; all existing tests pass.

### I2 — InitConfig PHP Resource (≤30 min) ✓
- _Goal:_ Expose `is_white_label_enabled` in `InitConfig`. FR-039-02, NFR-039-04.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `public bool $is_white_label_enabled;` property to `InitConfig`.
  2. Populate in the SE-setup section: `$this->is_white_label_enabled = $this->is_se_enabled && Features::active('white_label_enabled');`
  3. Add Feature_v2 test asserting field is present in `GET /api/v2/Gallery/Init` response with value `false` by default; and remains `false` when env flag is set but SE is inactive.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ Tests green; TypeScript type regenerated.

### I3 — Vue Front-End: Left Menu & Login Form (≤45 min) ✓
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

### I4 — Vue Front-End: Gallery Footer (≤20 min) ✓
- _Goal:_ Gate "Powered by Lychee" in `GalleryFooter.vue`. FR-039-04, S-039-02.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Import/reference `lycheeStore` (or pass as prop) in `GalleryFooter.vue`; destructure `is_white_label_enabled`.
  2. Wrap `<p class="hosted_by ...">` with `v-if="!is_white_label_enabled"`.
  3. Write/update Vitest unit test for `GalleryFooter.vue` (S-039-02 footer path).
- _Commands:_ `npm run check`
- _Exit:_ `npm run check` passes; test green.

### I5 — Blade: Footer, Meta, Warning (≤45 min) ✓
- _Goal:_ Gate all three blade branding points. FR-039-05, FR-039-06, FR-039-07.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. In `footer.blade.php`: wrap "Powered by Lychee" `<p>` with `@if(Features::inactive('white_label_enabled'))`.
  2. In `meta.blade.php`: wrap `<meta name="generator">` with `@if(Features::inactive('white_label_enabled'))`.
  3. In `warning-misconfiguration.blade.php`: use `@if(Features::active('white_label_enabled'))` to render generic text; `@else` renders original Lychee text.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ All blade changes verified; tests pass.

### ~~I6 — Translation Keys~~ (removed)

Translation keys are not needed: `white_label_enabled` is an env-only feature flag with no settings UI entry.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-039-01 | I1, I2 (default state) | Baseline — no change for existing installations |
| S-039-02 | I3, I4, I5 | All UI suppression points active (SE must be active for Vue; blade responds to env flag directly) |
| S-039-03 | N/A | No migration; reverting env var restores default behaviour |
| S-039-04 | I1 | Env var absent — `features.white_label_enabled` defaults to `false` |
| S-039-05 | I2 | SE inactive + `WHITE_LABEL_ENABLED=true` → `is_white_label_enabled` = false; branding visible (Q-039-03 Option B) |
| S-039-06 | I3 | Modal path via `LoginModal.vue` → same `LoginForm` |
| S-039-07 | I3 | `is_basic_auth_enabled = false` — branding `<div>` inside `v-if` block |
| S-039-08 | I1, I2 | Toggle env var + config cache clear; change reflected on next request |
| S-039-09 | I3 | Login form branding visible (white label OFF) |
| S-039-10 | I3 | Login form branding hidden (white label ON) |

## Analysis Gate

All open questions (Q-039-01, Q-039-02, Q-039-03) resolved. Implementation may proceed without further gate.

## Exit Criteria

- [x] `php artisan test` green (no regressions).
- [x] `make phpstan` zero new errors.
- [x] `npm run check` passes (TypeScript types correct).
- [ ] All 10 scenarios (S-039-01 – S-039-10) covered by automated tests.
- [x] `.env.example` updated with `WHITE_LABEL_ENABLED` entry and SE disclaimer.
- [ ] Roadmap and knowledge-map updated.

## Follow-ups / Backlog

- `vite/index.html` contains the same warning text as the blade component — if that file is ever served in production it should also be patched (deferred; currently dev-only).

---
*Last updated: 2026-05-04 (rev 3 — storage moved from DB migration to `.env`/`features.php`; translation keys and ConfigIntegrity middleware removed from scope; blade templates updated to use `Features::active()`/`Features::inactive()`)*
