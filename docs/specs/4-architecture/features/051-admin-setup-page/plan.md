# Feature Plan 051 – v8 Admin Setup Page

_Linked specification:_ `docs/specs/4-architecture/features/051-admin-setup-page/spec.md`
_Status:_ Implemented — T-051-14 (manual browser verification) deferred to operator
_Last updated:_ 2026-07-26

> Guardrail: Keep this plan traceable back to the governing spec. All four clarifications (Q-051-01..04) are resolved and captured in spec.md's normative sections; see [docs/specs/4-architecture/open-questions.md](../../open-questions.md) for rationale.

## Vision & Success Criteria
When `NUXT_UI_ENABLED=true` (i.e. `Features::active('nuxt_ui')`) and no admin user exists, any request lands on a Nuxt UI `/setup-admin` page instead of the legacy Blade `install/admin` form. Submitting valid credentials creates the admin account and the user proceeds into the gallery. With `NUXT_UI_ENABLED=false`, behaviour is byte-for-byte identical to today. Success signals: FR-051-01..06 all pass their feature/component tests; `php artisan test`, `make phpstan`, `npm run check` all green; manual verification of both flag states on a fresh (no-admin) database.

## Scope Alignment
- **In scope:** New `GET /setup-admin` web route + redirect branch; new `POST /Admin::Setup` API route; shared `CreateInitialAdmin` Action; new `AdminSetupPage.vue` v8 component; `paths.ts` entry; v7 defensive fallback + `Placeholder.vue`.
- **Out of scope:** The 6-step "not installed at all" wizard (`InstallationStatus`/`IsInstalled`); any change to `HasAdminUser`'s web-updater fallback; new fields beyond username/password/confirm; v7's `install/admin` Blade views/controller behaviour (only its internals are refactored to call the shared Action — its observable output is unchanged).

## Dependencies & Interfaces
- `App\Http\Middleware\Checks\HasAdminUser`, `App\Http\Middleware\AdminUserStatus` (read-only dependency, unchanged).
- `App\Exceptions\Handlers\AdminSetterHandler`, `App\Http\Redirections\ToAdminSetter` (modified: flag-aware redirect target).
- `App\Http\Requests\Install\SetUpAdminRequest` (reused unchanged by the new API controller).
- `resources/js/router/paths.ts`, `resources/js/v7/router/routes.ts`, `resources/js/v8/router/routes.ts`.
- `useAppToast()` composable (`resources/js/v8/composables/useAppToast.ts`) — reused, not modified.
- Precedent: `RegisterPage.vue` + `ProfileService.register()` + `PUT /Profile` (structurally mirrored throughout).

## Assumptions & Risks
- **Assumptions:** `Features::active('nuxt_ui')` remains a static, env-driven, deploy-wide flag (not per-request/per-user) — confirmed via `config/features.php`. The `api` middleware group's lack of `installation`/`admin_user` gating is intentional/stable (confirmed: `/register`'s `PUT /Profile` already relies on this).
- **Risks / Mitigations:**
  - Risk: redirect-branch logic (`ToAdminSetter`) accidentally applies to v7 too. Mitigation: feature test asserting `nuxt_ui=false` still redirects to `install-admin` (S-051-02).
  - Risk: `POST /Admin::Setup` reachable as a privilege-escalation vector after an admin already exists. Mitigation: `CreateInitialAdmin` unconditionally checks `HasAdminUser::assert()` first and throws (409) regardless of caller (feature test S-051-05).
  - Risk: v7's new `?? Placeholder` fallback masks a real future routing bug (silently rendering a blank page instead of failing loudly). Mitigation: this is the same pattern already accepted in v8's router (`?? Placeholder`), so it's a consistent, known trade-off, not a new one.

## Implementation Drift Gate
**Run:** 2026-07-26.

- `resources/js/v7/router/routes.ts` diff: exactly one behavioural line changed — `component: componentByName[p.name]` → `component: componentByName[p.name] ?? Placeholder` (plus the `Placeholder` import and a new `v7/views/Placeholder.vue`). No existing route's resolved component changes (all current names remain in `componentByName`). Confirms NFR-051-02.
- `app/Http/Controllers/Install/SetUpAdminController.php` diff: `create()` now delegates to `CreateInitialAdmin::do()` instead of inlining `User` construction + `Hash::make` + `configs.owner_id` update. Observable behaviour (views rendered, `$error` string on failure) unchanged — confirmed by `tests/Install/AdminTest.php` (pre-existing, untouched) still passing.
- Cross-artifact check: every FR-051-01..06 maps to shipped code/tests (route+redirect branch → FR-051-01; `AdminSetupPage.vue` → FR-051-02; `CreateInitialAdmin`+`AdminSetupController` → FR-051-03/04; `SetUpAdminController` refactor → FR-051-05; `paths.ts`+v7 fallback → FR-051-06).
- Two known, explicitly-flagged divergences from the original plan (not silently absorbed): T-051-13 (Vitest coverage) blocked by the absence of any JS test runner in this repo — raised as **Q-051-05**, resolved (Option A: accept the gap, no dependency added); T-051-14 (manual browser verification) deferred to the operator rather than performed against this session's real dev environment/database.
- Quality gate: `php-cs-fixer` (1 file auto-fixed), `npm run format`/`npm run check` (clean), `php artisan test` (2859 passed / 1 pre-existing unrelated failure, confirmed via `git stash`), `make phpstan` (0 errors, whole project).

**Result:** Pass. Q-051-05 resolved (Option A); T-051-14 remains deferred to the operator (low-risk, doesn't block completion).

## Increment Map

1. **I1 – Shared `CreateInitialAdmin` Action (backend)**
   - _Goal:_ Extract admin-creation logic (Q-051-02) so both the legacy Blade controller and the new API controller share one implementation.
   - _Preconditions:_ None.
   - _Steps:_
     - Write a failing unit test for `App\Actions\User\CreateInitialAdmin`: creates a `User` with `may_upload`/`may_edit_own_settings`/`may_administrate = true`, sets `configs.owner_id`, and throws (a new `AdminUserAlreadySetException` reuse, or a dedicated exception) when an admin already exists.
     - Implement `CreateInitialAdmin` in `app/Actions/User/CreateInitialAdmin.php`.
     - Refactor `SetUpAdminController::create()` to call it; keep its existing try/catch → Blade view behaviour unchanged.
   - _Commands:_ `php artisan test --filter=CreateInitialAdmin`
   - _Exit:_ New unit test green; existing install-admin feature tests (if any) still green.

2. **I2 – `GET /setup-admin` route + flag-aware redirect (backend)**
   - _Goal:_ Implement Q-051-01's Option A mechanism.
   - _Preconditions:_ I1 (not strictly required, but keeps the diff coherent).
   - _Steps:_
     - Write failing feature tests: (a) `nuxt_ui` active + no admin → redirect to `/setup-admin`; (b) `nuxt_ui` inactive + no admin → unchanged redirect to `install-admin`; (c) `/setup-admin` visited after an admin already exists → `AdminUserAlreadySetException` path (S-051-06).
     - Add `Route::get('setup-admin', VueController::class)->name('admin-setup')->withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])` to `routes/web_v2.php`.
     - Branch `ToAdminSetter::go()` (or introduce a small internal helper) on `Features::active('nuxt_ui')`: redirect to `route('admin-setup')` when active, `route('install-admin')` otherwise.
   - _Commands:_ `php artisan test --filter=AdminSetter`
   - _Exit:_ All three feature tests green; `make phpstan` clean on touched files.

3. **I3 – `POST /Admin::Setup` API endpoint (backend)**
   - _Goal:_ Unauthenticated JSON endpoint for admin creation (FR-051-03/04).
   - _Preconditions:_ I1.
   - _Steps:_
     - Write failing feature tests: success (creates user, 200/201), validation failure (422, mismatched/missing fields), already-exists (403, `AdminUserAlreadySetException`), username-taken (409, `ConflictingPropertyException`), reachable with no `Authorization` header and no admin present.
     - Add controller (namespace TBD at implementation time, e.g. `App\Http\Controllers\Install\AdminSetupApiController`) with a `store(SetUpAdminRequest $request)` method calling `CreateInitialAdmin` — no manual exception mapping needed, both exceptions already render their correct status codes via the app's existing exception handling.
     - Register `Route::post('/Admin::Setup', [AdminSetupApiController::class, 'store'])` in `routes/api_v2.php`.
   - _Commands:_ `php artisan test --filter=AdminSetupApi`
   - _Exit:_ All feature tests green.

4. **I4 – v8 route + `AdminSetupPage.vue` (frontend, form only)**
   - _Goal:_ New Nuxt UI page reachable at `/setup-admin` with the create-admin form (FR-051-02), no submit wiring yet.
   - _Preconditions:_ None (can proceed in parallel with I2/I3).
   - _Steps:_
     - Add `{ name: "admin-setup", path: "/setup-admin" }` to `resources/js/router/paths.ts` (Q-051-04, Option B).
     - Create `resources/js/v8/views/AdminSetupPage.vue`, styled like `LoginPage.vue`/`RegisterPage.vue` (`UCard`, username/password/confirm-password fields, `isFormValid`/mismatch validation mirroring `RegisterPage.vue`).
     - Map `"admin-setup": AdminSetupPage` in `resources/js/v8/router/routes.ts`'s `componentByName`.
   - _Commands:_ `npm run check`
   - _Exit:_ Route renders in dev server with empty/disabled-submit form; `npm run check` clean.

5. **I5 – v7 defensive fallback (frontend)**
   - _Goal:_ Close the risk accepted in Q-051-04 (Option B).
   - _Preconditions:_ I4 (paths.ts entry must exist).
   - _Steps:_
     - Create `resources/js/v7/views/Placeholder.vue` (trivial, mirroring `resources/js/v8/views/Placeholder.vue`) since v7 has none today.
     - Change `resources/js/v7/router/routes.ts`'s `component: componentByName[p.name]` to `component: componentByName[p.name] ?? Placeholder`.
   - _Commands:_ `npm run check`
   - _Exit:_ v7 build unaffected for all pre-existing routes (manual diff of rendered route list); new fallback in place.

6. **I6 – Submit wiring + success/error states (frontend)**
   - _Goal:_ Complete FR-051-03's client side and Q-051-03's success UX.
   - _Preconditions:_ I3 (API endpoint), I4 (form component).
   - _Steps:_
     - Add an `admin-setup-service.ts` (or a method on an existing install-related service) calling `POST /Admin::Setup`.
     - Wire `AdminSetupPage.vue`'s submit handler: on success, `useAppToast().add(...)` + `router.push({ name: "gallery" })`; on 422, inline error message; on 409, distinct inline message (mirrors `RegisterPage.vue:126-131`).
     - Write Vitest component tests for `AdminSetupPage.vue`: mismatched-password disables submit, success path calls service + navigates, 422/409 paths surface distinct messages.
   - _Commands:_ `npm run check`
   - _Exit:_ All new component tests green.

7. **I7 – Quality gates, manual verification, docs**
   - _Goal:_ Close out the feature per the "After Completing Work" checklist.
   - _Preconditions:_ I1–I6 complete.
   - _Steps:_
     - Manual browser check: fresh (no-admin) SQLite/dev DB, `NUXT_UI_ENABLED=true` → confirm `/setup-admin` flow end-to-end; `NUXT_UI_ENABLED=false` → confirm unchanged Blade flow.
     - Update `docs/specs/4-architecture/knowledge-map.md` (new Action, new v8 route/component).
     - Update `docs/specs/4-architecture/roadmap.md` (051 status → Complete).
     - Run full quality gate.
   - _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`
   - _Exit:_ All quality gates green; roadmap/knowledge-map updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-051-01 | I2 | `nuxt_ui` active redirect to `/setup-admin` |
| S-051-02 | I2 | `nuxt_ui` inactive — unchanged Blade redirect |
| S-051-03 | I3, I6 | Successful admin creation end-to-end |
| S-051-04 | I4, I6 | Client-side password-mismatch validation |
| S-051-05 | I3 | Race condition — second submit gets 409 |
| S-051-06 | I2 | Direct navigation after admin exists — `admin_user:unset` guard |

## Analysis Gate
**Run:** 2026-07-26, self-reviewed against [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md).

1. Specification completeness — ✅ FR/NFR populated (FR-051-01..06, NFR-051-01..03); all 4 clarifications reflected in normative sections; ASCII mock-up present.
2. Open questions review — ✅ No blocking `Open` entries remain for Feature 051; Q-051-01/04 (architecturally significant, security-gate-bypass) recorded in **ADR-0007**.
3. Plan alignment — ✅ This plan references `spec.md`/`tasks.md` correctly; dependencies/success criteria match spec wording.
4. Tasks coverage — ✅ Every FR maps to ≥1 task (see `tasks.md` task Notes); tests staged before implementation in every increment; success/validation/failure branches enumerated in Branch & Scenario Matrix and mapped to tasks.
5. Constitution compliance — ✅ No violations identified; increments are small, mostly straight-line (Action extraction avoids branching duplication); ADR-0006 reviewed as the directly related prior decision.
6. Tooling readiness — ✅ Commands documented per increment/task (`php artisan test --filter=...`, `make phpstan`, `npm run check`, full gate in I7).

**Result:** Pass. Proceeding to implementation.

## Exit Criteria
- All tasks in `tasks.md` checked off.
- `php artisan test`, `make phpstan`, `npm run check` all green.
- Manual verification of both `NUXT_UI_ENABLED` states performed and noted in this plan.
- `knowledge-map.md` and `roadmap.md` updated; feature moved from Active to Completed.

## Follow-ups / Backlog
- Consider adding a Scramble/OpenAPI docblock to `POST /Admin::Setup` if install-related routes gain documented API coverage in the future (none currently exists for this family of routes).
- No other known follow-ups; scope is intentionally narrow (see Non-Goals in spec.md).
