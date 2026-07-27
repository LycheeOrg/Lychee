# Feature 051 – v8 Admin Setup Page

| Field | Value |
|-------|-------|
| Status | Spec approved — plan/tasks in progress |
| Last updated | 2026-07-26 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/051-admin-setup-page/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/051-admin-setup-page/tasks.md` |
| Roadmap entry | Active Features #051 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview
Today, when no admin user exists yet (fresh install, tables migrated but no `may_administrate = true` user created), every request under the `web` middleware group is gated by `admin_user:set` (`app/Http/Kernel.php:43`). `HasAdminUser::assert()` (`app/Http/Middleware/Checks/HasAdminUser.php`) fails, `AdminUserStatus` throws `AdminUserRequiredException`, and `AdminSetterHandler` (`app/Exceptions/Handlers/AdminSetterHandler.php`) 307-redirects the browser to `route('install-admin')` — a standalone Blade form (`resources/views/install/setup-admin.blade.php`) styled with the installer's own static CSS, completely outside the Vue app. This happens **before** `vueapp.blade.php` is ever served, so neither the v7 (`app.ts`/PrimeVue) nor v8 (`app-v8.ts`/Nuxt UI) bundle ever mounts in this state.

This feature makes v8 deployments (`Features::active('nuxt_ui')` on) show a proper Nuxt UI page — a new Vue route + component — for first-admin creation, instead of falling through to the legacy Blade installer page. v7 deployments (`nuxt_ui` off) are explicitly out of scope and must keep their exact current Blade-redirect behaviour, consistent with Feature 049's v7-isolation principle (v7's bundle/behaviour stays byte-for-byte unchanged regardless of v8 work).

Affected modules: backend routing (`routes/web_v2.php`), exception/redirect handling (`App\Exceptions\Handlers\AdminSetterHandler`, `App\Http\Redirections\ToAdminSetter`), a new JSON API endpoint for admin creation, and the v8 frontend (`resources/js/v8/router/`, `resources/js/v8/views/`).

## Goals
- When `nuxt_ui` is active and no admin user exists, the browser lands on a v8 Nuxt UI page (not the Blade installer) offering a form to create the first admin account.
- Submitting valid credentials creates the admin user (same effective result as today's Blade flow: `may_upload`/`may_edit_own_settings`/`may_administrate = true`, `configs.owner_id` set) and the user proceeds into the app.
- v7 (`nuxt_ui` inactive) behaviour is completely unchanged — same redirect, same Blade page.
- No new runtime dependency is introduced (offline-only requirement).

## Non-Goals
- The broader 6-step "not installed at all" wizard (`welcome` → `requirements` → `permissions` → `env` → `migrate`, governed by `InstallationStatus`/`IsInstalled`) is untouched. This feature only addresses the narrower "tables exist, but no admin user yet" case (`AdminUserStatus`/`HasAdminUser`).
- No change to `HasAdminUser`'s web-updater column-fallback behaviour (`app/Http/Middleware/Checks/HasAdminUser.php:23-32`).
- No redesign of the admin-creation fields themselves (username/password/confirm password) beyond adapting them to Nuxt UI form components — no new fields (e.g. email) are added to admin creation, unlike `/register`'s user-facing signup form which does collect an email.
- v7's `install/admin` Blade route, `SetUpAdminController`, and `install.setup-admin`/`install.setup-success` views are not removed or modified for this feature (they remain v7's exclusive path).

## Resolved Clarifications
All four clarifications raised while drafting this feature are resolved; full rationale lives in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and the security-relevant gate-bypass mechanism is additionally recorded in **[ADR-0007](../../../6-decisions/ADR-0007-v8-admin-setup-gate-bypass.md)**:
- **Q-051-01** (High, Option A) — new route `GET /setup-admin`, exempted from `admin_user:set`, redirect target branched on `Features::active('nuxt_ui')`. See ADR-0007.
- **Q-051-02** (Medium, Option A) — shared `App\Actions\User\CreateInitialAdmin` used by both the legacy Blade controller and the new API endpoint.
- **Q-051-03** (Medium, Option A) — toast + immediate `router.push` to gallery on success, mirroring `RegisterPage.vue`.
- **Q-051-04** (Medium, **Option B**, overriding the recommendation) — the `admin-setup` route is added to the shared `resources/js/router/paths.ts` manifest for consistency with every other route, rather than kept v8-local. To close the resulting risk (v7's `componentByName` lookup has no fallback and would resolve to `component: undefined` if this name were ever reached in a v7 context), the plan adds a one-line defensive fallback to v7's lookup (`?? Placeholder`, matching v8's existing pattern) — a behaviour-preserving safety net, not a new v7 feature. See ADR-0007.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-051-01 | When `Features::active('nuxt_ui')` is true and no admin user exists, any request that would otherwise hit the `admin_user:set` gate is redirected to `route('admin-setup')` (`GET /setup-admin`) instead of `install-admin`. | 307 redirect to `/setup-admin`; `vueapp.blade.php` renders with `app-v8.ts`. | N/A (server-side gate, no user input). | If `nuxt_ui` is false, behaviour is unchanged (redirect to `install-admin`, Blade form). | None (mirrors existing redirect, no new event). | Q-051-01 |
| FR-051-02 | `/setup-admin` renders a new Nuxt UI component (`AdminSetupPage.vue`) with username/password/confirm-password fields, styled consistently with `LoginPage.vue`/`RegisterPage.vue` (`UCard`, no left-menu/gallery chrome). | Form renders with empty fields, submit disabled until valid. | Client-side: passwords must match, all fields non-empty (mirrors `RegisterPage.vue`'s `isFormValid`/`confirmationError` pattern). | N/A | None | User request; UI precedent `RegisterPage.vue` |
| FR-051-03 | Submitting the form calls `POST /Admin::Setup`, validated by the existing `SetUpAdminRequest` (`app/Http/Requests/Install/SetUpAdminRequest.php`, reused as-is), which creates the admin user via the new shared `App\Actions\User\CreateInitialAdmin` (Q-051-02) — itself a thin wrapper around the existing general-purpose `App\Actions\User\Create` action plus the `configs.owner_id` update. | User created (`may_upload`/`may_edit_own_settings`/`may_administrate = true`), `configs.owner_id` set to new user's id; toast shown; client immediately navigates to the `gallery` route (Q-051-03). | Standard Laravel form-request validation errors (422) surfaced inline, mirroring `RegisterPage.vue`'s `errorMessage` handling. | Two distinct failure cases, both already handled by existing exception classes: (a) an admin already exists at submit time (race condition) — `CreateInitialAdmin` throws `AdminUserAlreadySetException`, rendered as its existing 403; (b) the chosen username collides with an existing (non-admin) user row — the underlying `Create` action throws `ConflictingPropertyException`, rendered as its existing 409, mirroring `ProfileService.register()`'s 409-handling pattern in `RegisterPage.vue:126-128`. | None (mirrors existing, untelemetered admin-creation flow). | Q-051-01, Q-051-02 |
| FR-051-04 | `POST /Admin::Setup` is reachable regardless of admin existence (it lives in the `api` middleware group, which has no `installation`/`admin_user` gate, per `app/Http/Kernel.php:72-86`), but `CreateInitialAdmin` rejects creation if an admin already exists. | N/A | N/A | 403 response (`AdminUserAlreadySetException`, reused as-is) if `HasAdminUser::assert()` is already true. | None | `app/Http/Middleware/Checks/HasAdminUser.php`, `app/Exceptions/AdminUserAlreadySetException.php` |
| FR-051-05 | v7 (`nuxt_ui` inactive) is unaffected: the redirect target, Blade views, and `SetUpAdminController` behave exactly as before this feature. `SetUpAdminController::create()` is refactored to call `CreateInitialAdmin` internally, but its observable behaviour (views rendered, error format) is unchanged. | Unchanged. | Unchanged. | Unchanged. | None | Feature 049 v7-isolation principle |
| FR-051-06 | The `admin-setup` route/name is added to the shared `resources/js/router/paths.ts` manifest (Q-051-04, Option B); v7's `componentByName` lookup in `resources/js/v7/router/routes.ts` gains a defensive `?? Placeholder` fallback (matching v8's existing pattern) so an unmapped name never resolves to `component: undefined`. | v7 build/behaviour unchanged for every existing route; the new name resolves to `Placeholder` if ever reached in a v7 context (never in practice, since v7 always redirects to `install-admin`). | N/A | N/A | None | Q-051-04 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-051-01 | No new runtime dependency on an external host (CDN, font, telemetry). | Offline-only requirement (project-wide constraint). | Manual review of new frontend/backend code — no new `<script src="https://...">`, no external fetch. | — | Project offline-only guardrail |
| NFR-051-02 | v7's *observable behaviour* is unchanged by this feature for every route it already serves. The one accepted exception (Q-051-04, Option B) is the addition of a defensive `?? Placeholder` fallback in v7's `componentByName` lookup, which changes nothing for any currently-reachable route and only prevents a hypothetical future `undefined`-component crash. | Feature 049 isolation principle, balanced against Q-051-04's consistency choice. | Manual diff of `resources/js/v7/router/routes.ts` — confirm the only change is the one-line fallback; `npm run build` diff on `app.ts` output before/after for all existing routes. | Feature 049 (`resources/js/router/paths.ts` handling, Q-051-04) | ADR-0006 |
| NFR-051-03 | The new admin-creation Action produces an identical `User`/`configs.owner_id` result to the existing Blade flow. | Consistency — both v7 and v8 must create equivalent admin accounts. | Feature test asserting same fields set (`may_upload`, `may_edit_own_settings`, `may_administrate`, `configs.owner_id`) as the existing `SetUpAdminControllerTest` (if one exists) or `SetUpAdminController::create()` logic. | Q-051-02 | `app/Http/Controllers/Install/SetUpAdminController.php:43-74` |

## UI / Interaction Mock-ups

```
┌──────────────────────────────────────────────┐
│  ←                                             │   (back chevron, top-left, like LoginPage.vue)
│                                                │
│                 [ Lychee logo ]                │
│                                                │
│   ┌────────────────────────────────────────┐  │
│   │   Set up your admin account            │  │
│   │                                         │  │
│   │   Username                              │  │
│   │   [_____________________________]       │  │
│   │                                         │  │
│   │   Password                              │  │
│   │   [_____________________________]       │  │
│   │                                         │  │
│   │   Confirm password                      │  │
│   │   [_____________________________]       │  │
│   │                                         │  │
│   │   ⚠ Passwords do not match (if any)     │  │
│   │                                         │  │
│   │   [        Create admin account       ] │  │
│   └────────────────────────────────────────┘  │
│                                                │
└──────────────────────────────────────────────┘

On success (Q-051-03, Option A):
  toast: "Admin account created" → router.push({ name: "gallery" })
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-051-01 | `nuxt_ui` active, no admin exists, user requests any gated URL → 307 redirect to `/setup-admin`, v8 `AdminSetupPage.vue` renders. |
| S-051-02 | `nuxt_ui` inactive, no admin exists → unchanged: 307 redirect to `install-admin`, Blade form renders. |
| S-051-03 | User submits valid username/password/confirm on `/setup-admin` → admin user created, `configs.owner_id` set, toast shown, client navigates to gallery. |
| S-051-04 | User submits mismatched passwords → client-side validation blocks submit (mirrors `RegisterPage.vue`). |
| S-051-05 | Two browser tabs both open `/setup-admin`; one submits successfully, the second submits after an admin already exists → second request receives 403 (`AdminUserAlreadySetException`), error surfaced inline. |
| S-051-07 | User submits a username that collides with an existing (non-admin) user row → 409 (`ConflictingPropertyException`, via the underlying `Create` action), error surfaced inline. |
| S-051-06 | User manually navigates to `/setup-admin` after an admin already exists → `admin_user:unset` guard throws `AdminUserAlreadySetException`, handled exactly as `install/admin` handles the same case today. |

## Test Strategy
- **Application/REST:** Feature test for the new API endpoint — success (201/200 + user fields), validation failure (400), already-exists (409), and unauthenticated reachability (no `Authorization` header required). Feature test for the new web route's `withoutMiddleware`/`admin_user:unset` behaviour (redirect-when-no-admin vs. 403/redirect-when-admin-exists).
- **UI (component-level):** No JS test runner (Vitest or otherwise) exists anywhere in this repo — resolved via **Q-051-05** (Option A: accept the gap, no dependency added). `AdminSetupPage.vue` is covered by `npm run check` (type-check, passing) and the backend feature tests (which exercise the full HTTP request/response cycle), consistent with every other v7/v8 view in this codebase.
- **Manual/browser verification:** With `NUXT_UI_ENABLED=true` and a fresh (no-admin) database, confirm `/` redirects to `/setup-admin` and the flow completes; with `NUXT_UI_ENABLED=false`, confirm the Blade redirect is unchanged.
- **Docs/Contracts:** No OpenAPI/Scramble doc currently exists for install-related routes (they're outside `api_v2.php`'s documented surface per a quick check) — new endpoint should still get Scramble-visible docblocks per existing API controller conventions if it lands under `routes/api_v2.php`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-051-01 | Admin-creation request payload: `username` (string, required), `password`/`password_confirmation` (required, confirmed) — same shape as `SetUpAdminRequest`. | REST, UI |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-051-01 | REST GET `/setup-admin` (name `admin-setup`) | Serves `vueapp.blade.php` (v8) for first-admin creation; `withoutMiddleware(['admin_user:set'])`, `middleware(['admin_user:unset'])`. Registered in `routes/web_v2.php`. | Q-051-01 |
| API-051-02 | REST POST `/Admin::Setup` | Creates the first admin user via `CreateInitialAdmin`; validated by `SetUpAdminRequest`; `api` middleware group (no admin gate); returns 409 if an admin already exists. Registered in `routes/api_v2.php`. | Q-051-01, Q-051-02 |

### CLI Commands / Flags
_None — this feature has no CLI surface._

### Telemetry Events
_None planned — mirrors the existing untelemetered admin-creation flow._

### Fixtures & Sample Data
_None required — covered by feature tests seeding/clearing the `users` table._

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-051-01 | Form (default) | Route loads with empty username/password/confirm fields. |
| UI-051-02 | Validation error | Passwords mismatch → inline alert, submit disabled. |
| UI-051-03 | Submit error | Server validation failure (400) → inline alert with server message. |
| UI-051-04 | Already-exists error | 403 (admin already set) or 409 (username taken) response → inline alert (distinct message per case, mirrors `RegisterPage.vue`'s username-exists handling). |
| UI-051-05 | Success | Toast shown, client navigates to gallery route (Q-051-03). |

## Telemetry & Observability
No telemetry events are introduced by this feature (consistent with the existing, untelemetered `SetUpAdminController` flow).

## Documentation Deliverables
- Update `docs/specs/4-architecture/knowledge-map.md`: note the new `admin-setup`/`AdminSetupPage.vue` route under the v8 Frontend/Routing section, and the new Action under `app/Actions/` (if Q-051-02 resolves to Option A).
- Update `docs/specs/4-architecture/roadmap.md` Active Features entry as the feature progresses.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-051-01
    name: CreateAdminRequest
    fields:
      - name: username
        type: string
        constraints: "required"
      - name: password
        type: string
        constraints: "required, confirmed"
routes:
  - id: API-051-01
    method: GET
    path: /setup-admin
    name: admin-setup
  - id: API-051-02
    method: POST
    path: /Admin::Setup
ui_states:
  - id: UI-051-01
    description: Admin setup form, default state
  - id: UI-051-05
    description: Success toast + redirect to gallery
```

## Appendix

### Current (pre-feature) flow, for reference
1. `HasAdminUser::assert()` (`app/Http/Middleware/Checks/HasAdminUser.php:21-33`) — false when no `may_administrate = true` user exists.
2. `AdminUserStatus::handle()` (`app/Http/Middleware/AdminUserStatus.php:51-67`) — throws `AdminUserRequiredException` for `required_status = set`.
3. `AdminSetterHandler` (`app/Exceptions/Handlers/AdminSetterHandler.php:27-75`) — catches it, calls `ToAdminSetter::go()`.
4. `ToAdminSetter::go()` (`app/Http/Redirections/ToAdminSetter.php:27-32`) — 307 redirect to `route('install-admin')`.
5. `routes/web-install.php:27-35` — `install/admin` GET/POST → `SetUpAdminController`, gated by `admin_user:unset` (so it 404s/errors if an admin already exists).
6. `SetUpAdminController::create()` (`app/Http/Controllers/Install/SetUpAdminController.php:43-74`) — creates `User`, sets `configs.owner_id`, renders `install.setup-success` or re-renders the form with `$error`.

### Precedent this feature follows
`/register` (web, gated, `VueController`) + `PUT /Profile` (`ProfileController::register`, `api` group, ungated) + `RegisterPage.vue` (routed, unauthenticated, no-shell `UCard` form, `useAppToast()` + `router.push` on success) is structurally the same shape as what this feature needs for admin creation, and is used throughout this spec as the reference pattern.

### Finalized naming (post-clarification)
- Web route: `GET /setup-admin`, Laravel route name `admin-setup`, registered in `routes/web_v2.php`, `withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])`.
- API route: `POST /Admin::Setup`, registered in `routes/api_v2.php`, controller TBD in plan (e.g. `App\Http\Controllers\Api\V2\AdminSetupController@store`), validated by the existing `App\Http\Requests\Install\SetUpAdminRequest` (reused unchanged — it already has `authorize() => true` and no auth dependency).
- Shared Action: `App\Actions\User\CreateInitialAdmin`, called by both `SetUpAdminController::create()` (v7/Blade) and the new API controller (v8).
- v8 component: `resources/js/v8/views/AdminSetupPage.vue`, route name `admin-setup` mapped in both `resources/js/router/paths.ts` (path `/setup-admin`) and `resources/js/v8/router/routes.ts`'s `componentByName`.
- v7 companion change: `resources/js/v7/router/routes.ts`'s `componentByName[p.name]` lookup gains a `?? Placeholder` fallback (v7 has no component for `admin-setup`, matching v8's existing fallback pattern).
