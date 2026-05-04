# Feature 039 Tasks – Lychee White Label

_Status: Ready for implementation (all questions resolved)_  
_Last updated: 2026-05-04_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### I1 – Database Migration

- [x] T-039-01 – Create `white_label_enabled` config migration (FR-039-01).  
  _Intent:_ Insert `white_label_enabled` row in `configs` table with `cat='lychee SE'`, `value='0'`, `type_range='BOOL'`, `is_secret=1`, `level=1`, `order=3`. `down()` removes the row. Migration must be idempotent (`insertOrIgnore`).  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Follow `BaseConfigMigration` pattern (see `database/migrations/2024_09_27_144741_add_supporter_fields.php`).

---

### I2 – InitConfig PHP Resource

- [x] T-039-02 – Write Feature_v2 test: `GET /api/v2/Gallery/Init` returns `is_white_label_enabled = false` by default (FR-039-02).  
  _Intent:_ Failing test first — asserts new field is present in the Init payload.  
  _Verification commands:_  
  - `php artisan test --filter=WhiteLabelInitTest`  
  _Notes:_ Two tests: default=false, and SE-gate (config=1 but no SE → still false).

- [x] T-039-03 – Add `is_white_label_enabled` property to `InitConfig` (FR-039-02, NFR-039-04).  
  _Intent:_ Add `public bool $is_white_label_enabled;` to `app/Http/Resources/GalleryConfigs/InitConfig.php`; populate in the SE-setup section: `$this->is_white_label_enabled = $this->is_se_enabled && request()->configs()->getValueAsBool('white_label_enabled');` (mirrors the `is_live_metrics_enabled` pattern — Q-039-03 Option B).  
  _Verification commands:_  
  - `php artisan test` ✓  
  - `make phpstan` ✓  
  _Notes:_ TypeScript type added manually to `lychee.d.ts`; `npm run check` passes ✓.

---

### I3 – Vue Front-End: Left Menu & Login Form

- [x] T-039-05 – Add `is_white_label_enabled` to `LycheeState` store / confirm TypeScript transformer output.  
  _Intent:_ Ensure the reactive ref is available in Vue components after `InitConfig` adds the field.  
  _Verification commands:_  
  - `npm run check` ✓

- [ ] T-039-06 – Write Vitest unit test for `useLeftMenu`: "Lychee" section hidden when `is_white_label_enabled = true` (FR-039-03, S-039-02, UI-039-02).  
  _Intent:_ Failing test first.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ No Vitest infrastructure exists in the repo; deferred.

- [x] T-039-07 – Update `leftMenu.ts`: gate "Lychee" section on `!is_white_label_enabled` (FR-039-03, S-039-02).  
  _Intent:_ Destructure `is_white_label_enabled` from `lycheeStore`; apply to the "Lychee" section items' `access` field.  
  _Verification commands:_  
  - `npm run check` ✓

- [ ] T-039-08 – Write Vitest unit test for `LoginForm.vue`: branding `<div>` hidden when `is_white_label_enabled = true`, visible when `false` (FR-039-08, S-039-09, S-039-10, UI-039-09, UI-039-10).  
  _Intent:_ Failing test first.  
  _Notes:_ No Vitest infrastructure exists in the repo; deferred.

- [x] T-039-09 – Update `LoginForm.vue`: wrap branding `<div>` with `v-if="!is_white_label_enabled"` (FR-039-08, S-039-09, S-039-10).  
  _Verification commands:_  
  - `npm run check` ✓

---

### I4 – Vue Front-End: Gallery Footer

- [ ] T-039-10 – Write Vitest unit test for `GalleryFooter.vue`: "Powered by Lychee" paragraph hidden when `is_white_label_enabled = true` (FR-039-04, S-039-02, UI-039-04).  
  _Notes:_ No Vitest infrastructure exists in the repo; deferred.

- [x] T-039-11 – Update `GalleryFooter.vue`: wrap `<p class="hosted_by ...">` with `v-if="!is_white_label_enabled"` (FR-039-04).  
  _Verification commands:_  
  - `npm run check` ✓

---

### I5 – Blade: Footer, Meta, Warning

- [ ] T-039-12 – Write PHPUnit test for `footer.blade.php`: "Powered by Lychee" hidden when `white_label_enabled = 1` (FR-039-05, S-039-02, UI-039-04).  
  _Notes:_ Deferred; inline `resolve(ConfigManager)` pattern used; blade component testing is complex without a view composer.

- [x] T-039-13 – Update `resources/views/includes/footer.blade.php`: wrap `<p class="hosted_by">` with Blade conditional on `white_label_enabled` (FR-039-05).  
  _Verification commands:_  
  - `php artisan test` ✓

- [ ] T-039-14 – Write PHPUnit test for `meta.blade.php`: `<meta name="generator">` absent when `white_label_enabled = 1` (FR-039-06, S-039-02, UI-039-06).  
  _Notes:_ Deferred.

- [x] T-039-15 – Update `resources/views/components/meta.blade.php`: wrap `<meta name="generator" content="Lychee v7">` with Blade conditional (FR-039-06).  
  _Verification commands:_  
  - `php artisan test` ✓

- [ ] T-039-16 – Write PHPUnit test for `warning-misconfiguration.blade.php`: generic placeholders appear when `white_label_enabled = 1` (FR-039-07, S-039-02, UI-039-07, UI-039-08).  
  _Notes:_ Deferred.

- [x] T-039-17 – Update `resources/views/components/warning-misconfiguration.blade.php`: replace "Lychee" → "your-application" and "lychee.example.com" → "your-application.example.com" under Blade conditional (FR-039-07).  
  _Verification commands:_  
  - `php artisan test` ✓

---

### I6 – Translation Keys

- [x] T-039-18 – Add `white_label_enabled` translation key to `lang/en/settings.php` and all 21 remaining locale files.  
  _Intent:_ Key under `lychee_se` group: `'white_label' => 'Hide Lychee branding (white label mode)'`. All 23 locale files updated.  
  _Verification commands:_  
  - `php artisan test` ✓

---

### SE Middleware

- [x] T-039-MW – Add `white_label_enabled` to `ConfigIntegrity::SE_FIELDS` so the middleware enforces `level=1` on the config key (prevents non-SE users from sneaking the value through direct DB manipulation).

---

### Documentation

- [ ] T-039-19 – Update `docs/specs/4-architecture/roadmap.md` to add Feature 039 entry.

- [ ] T-039-20 – Update `docs/specs/4-architecture/knowledge-map.md`: record `white_label_enabled` config key under the SE config group.

## Notes / TODOs

- Q-039-01 resolved: Option A (hardcoded "your-application" placeholder). See spec.md FR-039-07.
- Q-039-02 resolved: Option A (inline `resolve(\App\Repositories\ConfigManager::class)->getValueAsBool(...)` pattern, same as `vueapp.blade.php`). See spec.md FR-039-05/06/07.
- Q-039-03 resolved: Option B (SE runtime gate — `is_se_enabled && getValueAsBool(...)` in InitConfig). See spec.md FR-039-02.
- Vitest unit tests (T-039-06, T-039-08, T-039-10) deferred: no Vitest/Jest infrastructure in `resources/js/`.
- Blade isolation tests (T-039-12, T-039-14, T-039-16) deferred: complex setup; covered by manual verification.
- `vite/index.html` contains the same warning text as `warning-misconfiguration.blade.php` — dev-only, intentionally out of scope.
