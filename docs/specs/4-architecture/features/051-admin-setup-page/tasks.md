# Feature 051 Tasks – v8 Admin Setup Page

_Status: Implemented — T-051-14 (manual browser verification) deferred to operator_
_Last updated: 2026-07-26_

> Keep this checklist aligned with `plan.md`'s increments. Tests are staged before implementation in every increment. Mark tasks `[x]` immediately after each one passes verification.

## Checklist

- [x] T-051-01 – Write failing unit test for `CreateInitialAdmin` Action (F-051-03, F-051-04, F-051-05).
  _Intent:_ Assert it creates a `User` with `may_upload`/`may_edit_own_settings`/`may_administrate = true`, sets `configs.owner_id`, and throws when an admin already exists.
  _Verification commands:_
  - `php artisan test --filter=CreateInitialAdmin`
  _Notes:_ New test file under `tests/Unit/Actions/User/`. Spec: FR-051-03/04/05.

- [x] T-051-02 – Implement `App\Actions\User\CreateInitialAdmin` (F-051-03, F-051-04, F-051-05).
  _Intent:_ Extract creation logic from `SetUpAdminController::create()`.
  _Verification commands:_
  - `php artisan test --filter=CreateInitialAdmin`
  - `make phpstan`
  _Notes:_ Spec: FR-051-03, NFR-051-03.

- [x] T-051-03 – Refactor `SetUpAdminController::create()` to call `CreateInitialAdmin` (F-051-05).
  _Intent:_ Legacy Blade path keeps identical observable behaviour (views rendered, `$error` format) while delegating creation to the shared Action.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ No test file changes expected if pre-existing coverage exists; add one if none does. Spec: FR-051-05, NFR-051-03.

- [x] T-051-04 – Write failing feature tests for `GET /setup-admin` redirect branching (F-051-01, S-051-01, S-051-02, S-051-06).
  _Intent:_ Cover: `nuxt_ui` active + no admin → redirect to `/setup-admin`; `nuxt_ui` inactive + no admin → unchanged redirect to `install-admin`; visiting `/setup-admin` after an admin exists → `AdminUserAlreadySetException` path.
  _Verification commands:_
  - `php artisan test --filter=AdminSetter`
  _Notes:_ Spec: FR-051-01, Branch Matrix S-051-01/02/06.

- [x] T-051-05 – Implement `GET /setup-admin` route + flag-aware `ToAdminSetter` branch (F-051-01).
  _Intent:_ Register the route in `routes/web_v2.php` with `withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])`; branch the redirect target on `Features::active('nuxt_ui')`.
  _Verification commands:_
  - `php artisan test --filter=AdminSetter`
  - `make phpstan`
  _Notes:_ Spec: FR-051-01, API-051-01.

- [x] T-051-06 – Write failing feature tests for `POST /Admin::Setup` (F-051-03, F-051-04, S-051-03, S-051-05, S-051-07).
  _Intent:_ Cover success, 422 validation failure, 403 already-exists (`AdminUserAlreadySetException`), 409 username-taken (`ConflictingPropertyException`), and reachability without an `Authorization` header/admin present.
  _Verification commands:_
  - `php artisan test --filter=AdminSetupApi`
  _Notes:_ Spec: FR-051-03/04, Branch Matrix S-051-03/05/07.

- [x] T-051-07 – Implement `POST /Admin::Setup` controller + route (F-051-03, F-051-04).
  _Intent:_ New controller calling `CreateInitialAdmin`, validated by the existing `SetUpAdminRequest`; register in `routes/api_v2.php`. No manual try/catch needed for status codes — `AdminUserAlreadySetException` (403) and `ConflictingPropertyException` (409) already render correctly via the app's existing exception handling.
  _Verification commands:_
  - `php artisan test --filter=AdminSetupApi`
  - `make phpstan`
  _Notes:_ Spec: FR-051-03/04, API-051-02.

- [x] T-051-08 – Add `admin-setup` entry to `resources/js/router/paths.ts` (F-051-06).
  _Intent:_ `{ name: "admin-setup", path: "/setup-admin" }`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Spec: FR-051-06 (Q-051-04, Option B).

- [x] T-051-09 – Create `AdminSetupPage.vue` (form only) + map it in v8's `componentByName` (F-051-02).
  _Intent:_ `UCard` form (username/password/confirm), styled like `LoginPage.vue`/`RegisterPage.vue`; client-side mismatch validation disables submit. No submit wiring yet.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Spec: FR-051-02, UI-051-01/02.

- [x] T-051-10 – Create `Placeholder.vue` for v7 + add `?? Placeholder` fallback to v7's `componentByName` lookup (F-051-06).
  _Intent:_ Close the risk accepted in Q-051-04 (Option B) without changing v7's observable behaviour for any existing route.
  _Verification commands:_
  - `npm run check`
  - `npm run build` (manual diff — confirm no existing v7 route's resolved component changes)
  _Notes:_ Spec: FR-051-06, NFR-051-02.

- [x] T-051-11 – Implement `admin-setup-service.ts` calling `POST /Admin::Setup` (F-051-03).
  _Intent:_ Thin axios wrapper, mirroring `ProfileService.register()`'s shape.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Spec: FR-051-03.

- [x] T-051-12 – Wire `AdminSetupPage.vue` submit → service → success/error/409 states (F-051-03, S-051-03, S-051-04, S-051-05).
  _Intent:_ Success: `useAppToast()` + `router.push({ name: "gallery" })`. 422: inline error message. 409: distinct inline message (mirrors `RegisterPage.vue:126-131`).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Spec: FR-051-03, UI-051-03/04/05, Q-051-03.

- [x] T-051-13 – Write Vitest component tests for `AdminSetupPage.vue` (S-051-03, S-051-04, S-051-05). **BLOCKED — no JS test runner in this repo.**
  _Intent:_ Cover mismatched-password (submit disabled), success (service called + navigation), 422 and 409 paths (distinct inline messages).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Discovered during implementation: this repo has no Vitest (or any JS unit-test runner) configured at all. Flagged to the user as Q-051-05 and **resolved (Option A)** — accept the gap, no dependency added. Coverage is `npm run check` (passing) + backend feature tests only, consistent with every other v7/v8 view in this codebase.

- [ ] T-051-14 – Manual browser verification, both `NUXT_UI_ENABLED` states. **NOT PERFORMED — deferred to the operator.**
  _Intent:_ Fresh (no-admin) dev database: confirm `/setup-admin` end-to-end flow with `NUXT_UI_ENABLED=true`; confirm unchanged Blade redirect with `NUXT_UI_ENABLED=false`.
  _Verification commands:_ (manual — browser)
  _Notes:_ Not performed this session — would require mutating this environment's real `.env` (`NUXT_UI_ENABLED`) and dev database (removing the admin user) to reach the "no admin" state, which risked disrupting an actively-used dev checkout. The equivalent HTTP-level behaviour (redirect target under both flag states, `vueapp` rendering, `admin_user:unset` guard) is covered by `tests/Install/AdminSetupTest.php`, which passes. True visual/interactive confirmation is left to the user when convenient.

- [x] T-051-15 – Update `knowledge-map.md` and `roadmap.md`; run full quality gate.
  _Intent:_ Document the new Action/route/component; move Feature 051 to Completed once green.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix` — 1 file fixed (`CreateInitialAdmin.php`), clean thereafter.
  - `npm run format` — all files unchanged (already formatted).
  - `npm run check` — clean, exit 0.
  - `php artisan test` — 2859 passed, 1 failed (`GeodecodeLocationJobTest > middleware includes rate limiter`), confirmed pre-existing/unrelated via `git stash`.
  - `make phpstan` — 0 errors, full project.
  _Notes:_ Quality gate green modulo the one pre-existing unrelated failure.

## Notes / TODOs
- Final PHP namespace/class name for the new API controller (`App\Http\Controllers\Install\AdminSetupApiController` suggested in plan.md) may be adjusted during T-051-07 if a better-fitting location is found (e.g. under a dedicated `App\Http\Controllers\Api\V2` namespace) — no spec impact either way since only the route path/name (`POST /Admin::Setup`) is normative.
- The exact exception type `CreateInitialAdmin` throws when an admin already exists (new dedicated exception vs. reusing `AdminUserAlreadySetException`) is an implementation detail to resolve during T-051-02; either is acceptable as long as the API controller (T-051-07) maps it to HTTP 409.
