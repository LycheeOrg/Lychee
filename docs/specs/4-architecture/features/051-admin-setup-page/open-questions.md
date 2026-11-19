# Open Questions – Feature 051

Open questions for [Feature 051](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-051-01~~ | 051 – v8 Admin Setup Page | High | Architectural mechanism for letting v8 show its own "no admin" page instead of the Blade redirect | Resolved (A – new route exempted from `admin_user:set`) | 2026-07-26 | 2026-07-26 |
| ~~Q-051-02~~ | 051 – v8 Admin Setup Page | Medium | Should admin-creation logic be extracted into a shared Action reused by the legacy Blade controller and the new API endpoint? | Resolved (A – shared Action) | 2026-07-26 | 2026-07-26 |
| ~~Q-051-03~~ | 051 – v8 Admin Setup Page | Medium | Post-success navigation — auto-redirect with toast vs. a distinct success screen | Resolved (A – toast + auto-redirect) | 2026-07-26 | 2026-07-26 |
| ~~Q-051-04~~ | 051 – v8 Admin Setup Page | Medium | Route manifest placement — v8-local router only, or added to the shared `paths.ts` manifest? | Resolved (B – added to shared `paths.ts`) | 2026-07-26 | 2026-07-26 |
| ~~Q-051-05~~ | 051 – v8 Admin Setup Page | Medium | No JS test runner (Vitest or otherwise) exists in this repo — planned component tests for `AdminSetupPage.vue` cannot be written without adding a new dev dependency | Resolved (A – accept the gap, no dependency added) | 2026-07-26 | 2026-07-26 |

## Question Details

### ~~Q-051-01~~ · Architectural mechanism for the v8 "no admin" page ✅ RESOLVED

**Status:** Resolved — **Option A** (redirect to a new v8-only route, exempted from `admin_user:set`)
**Feature:** 051 – v8 Admin Setup Page
**Priority:** High
**Opened:** 2026-07-26
**Resolved:** 2026-07-26

**Resolution:** Add `GET /setup-admin` (`routes/web_v2.php`) → `VueController`, with `->withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])`, mirroring `/up`'s exemption and `install/admin`'s `admin_user:unset` guard. Branch `ToAdminSetter::go()` on `Features::active('nuxt_ui')`: redirect to the new route/name when active, `install-admin` otherwise. `vueapp.blade.php` renders normally (with `app-v8.ts` per the existing flag check). v7 is untouched. Recorded in **ADR-0007** (security-relevant gate-bypass decision).

**Spec impact:** Captured in FR-051-01, API-051-01; ADR-0007.

**Question**
Today, `AdminUserStatus` (`admin_user:set`, applied group-wide to every `web` route in `app/Http/Kernel.php:43`) throws `AdminUserRequiredException` whenever `HasAdminUser::assert()` is false. `AdminSetterHandler` catches it and 307-redirects to `route('install-admin')` (`ToAdminSetter::go()`), which serves the static Blade form `resources/views/install/setup-admin.blade.php` — the browser never receives `vueapp.blade.php`, so neither `app.ts` (v7) nor `app-v8.ts` (v8) ever mounts. Making v8 show its own Vue page instead requires deciding how this server-side gate is bypassed for v8 specifically, without affecting v7 (which must keep its exact current Blade-redirect behaviour, per the user's "on version 8" scoping and Feature 049's v7-isolation principle).

A directly analogous pattern already exists for the sibling `/register` flow: `GET /register` (`routes/web_v2.php:75`) is a normal `web`-group `VueController` route (gated like everything else) but `PUT /Profile` (`routes/api_v2.php:193`, `ProfileController::register`) is a plain `api`-group JSON endpoint — and the `api` middleware group (`app/Http/Kernel.php:72-86`) **does not include `installation:complete`/`admin_user:set` at all**, so it's already reachable regardless of admin existence. `RegisterPage.vue` is a full working precedent of a routed, unauthenticated, no-shell v8 page (`UCard` form + `ProfileService.register()` + `useAppToast()` + `router.push({name:'gallery'})` on success).

---

#### 🅰️ (**recommended**) Option A – Redirect to a new v8-only route, exempted from `admin_user:set`

- **Idea:** Add a new route (e.g. `GET /setup-admin` in `routes/web_v2.php`) mapped to `VueController`, with `->withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])` — mirroring the exact pattern already used by `/up` (`routes/web_v2.php:38`, health check exemption) and by `install/admin` itself (`routes/web-install.php:29-35`, `admin_user:unset`). Branch `ToAdminSetter::go()` (or a new flag-aware redirection) on `Features::active('nuxt_ui')`: if active, redirect to the new route/name instead of `install-admin`. `vueapp.blade.php` renders normally there (with `app-v8.ts` per the existing `nuxt_ui` flag check), and the v8 router gets a new `AdminSetupPage.vue` at that route, submitting to a new unauthenticated JSON API endpoint (Q-051-02) already reachable since the `api` group has no admin gate.
- **Spec impact:** New route + `withoutMiddleware`/`admin_user:unset` guard; `ToAdminSetter` (or a new redirection class) branches on `Features::active('nuxt_ui')`; new v8 route + component; new API endpoint.
- **Pros:**
  - ✅ Reuses two patterns already proven in this exact codebase (`/up`'s exemption, `/register` + `PUT /Profile`'s web-page/JSON-API split) — minimal new architectural surface.
  - ✅ v7 is untouched: the branch only changes where the redirect points when `nuxt_ui` is active; `HasAdminUser`/`AdminUserStatus`/`AdminUserRequiredException` stay exactly as they are.
  - ✅ Self-guarding: `admin_user:unset` on the new route means directly navigating to it after an admin already exists correctly falls through to the existing `AdminUserAlreadySetException` handling, same as `install/admin` today.
  - ✅ Small diff: one new route, one redirect branch, one API endpoint, one Vue view — no middleware-group-wide changes.
- **Cons:**
  - ❌ Still a full HTTP redirect/page load rather than an in-SPA client-side transition (acceptable since the SPA isn't mounted yet at that point anyway).

---

#### 🅱️ Option B – Remove the gate for v8 entirely; client-side self-redirect

- **Idea:** Skip `admin_user:set` for `nuxt_ui`-flagged requests on `VueController` routes; expose a new `has_admin` boolean via `GET /Gallery::Init` (`InitConfig`, already reachable pre-admin since it's under the ungated `api` group); v8's `App.vue`/router checks the flag on boot and internally navigates to the setup page regardless of which URL was originally requested.
- **Spec impact:** Broader middleware-group change (all `VueController` routes, not just one), new public field on `InitConfig`, boot-time client-side redirect logic (no `router.beforeEach` guard exists anywhere in the codebase today — this would be the first).
- **Pros:**
  - ✅ Works from any URL the user happens to land on, not just the one redirect target.
- **Cons:**
  - ❌ Larger blast radius — touches the shared `InitConfig` DTO and the `VueController` route group broadly rather than one exemption.
  - ❌ Introduces a new pattern (client-side boot guard) with no existing precedent to follow.
  - ❌ `has_admin` becomes public API surface exposed even when true (minor information-shape change to an always-loaded resource).

---

#### 🅲 Option C – Branch `install/admin`'s own route registration on the flag

- **Idea:** In `routes/web-install.php`, conditionally register `install/admin` to `VueController` (serving `app-v8.ts`) instead of `SetUpAdminController` when `Features::active('nuxt_ui')` is true, keeping the exact same URL for both v7 and v8.
- **Spec impact:** Entangles the legacy install-wizard route file (`web-install.php`, otherwise pure Blade/no-Vue concerns) with `nuxt_ui`-flag branching and `VueController`.
- **Pros:**
  - ✅ Preserves the exact `install/admin` URL across both v7 and v8.
- **Cons:**
  - ❌ Couples a file that today has zero Vue/`nuxt_ui` awareness to that concern, contrary to Feature 049's isolation principle (v7's install path should stay untouched).
  - ❌ No real benefit over Option A's new dedicated route, since nothing outside this redirect ever links to `install/admin` directly.

---

**Next action**
Decide before drafting `plan.md`. If 🅰️: finalize the new route name/path and the redirection branch point. If 🅱️ or 🅲: expand scope/risk notes accordingly.

---

### ~~Q-051-02~~ · Shared Action for admin creation, or duplicate the logic? ✅ RESOLVED

**Status:** Resolved — **Option A** (extract a shared Action)
**Feature:** 051 – v8 Admin Setup Page
**Priority:** Medium
**Opened:** 2026-07-26
**Resolved:** 2026-07-26

**Resolution:** Add `App\Actions\User\CreateInitialAdmin`, encapsulating user creation (`may_upload`/`may_edit_own_settings`/`may_administrate = true`) + `configs.owner_id` update + an "admin already exists" guard. Both `SetUpAdminController::create()` and the new API controller call it.

**Spec impact:** Captured in FR-051-03, NFR-051-03.

**Question**
`SetUpAdminController::create()` (`app/Http/Controllers/Install/SetUpAdminController.php:43-74`) currently creates the `User` (`may_upload`/`may_edit_own_settings`/`may_administrate = true`) and updates the `configs.owner_id` row inline in the controller. The new v8 JSON API endpoint (Q-051-01) needs the same creation logic. Should this be extracted into a reusable `App\Actions\...` class (the project's established pattern for single-responsibility command objects, per `docs/specs/4-architecture/knowledge-map.md`'s "Actions" section), or should the new API controller just duplicate the ~10 lines directly?

---

#### 🅰️ (**recommended**) Option A – Extract a shared Action

- **Idea:** Add `App\Actions\User\CreateInitialAdmin` (or similar) encapsulating user creation + `owner_id` config update + the "does an admin already exist" guard. Both `SetUpAdminController::create()` (Blade path, v7-reachable) and the new API controller (v8 path) call it.
- **Spec impact:** New Action class + unit test; both controllers become thin wrappers.
- **Pros:**
  - ✅ Matches existing `app/Actions/` conventions (e.g. `ProvisionLdapUser`, `Delete`) — one source of truth for "what does creating the initial admin mean."
  - ✅ Avoids the two code paths silently drifting (e.g. one forgets the `owner_id` config update after a future change).
- **Cons:**
  - ❌ One more class/file than strictly necessary for ~10 lines of logic.

---

#### 🅱️ Option B – Duplicate the logic in the new API controller

- **Idea:** Write the new API controller's creation logic independently, leaving `SetUpAdminController` untouched.
- **Spec impact:** No change to existing Blade controller; new controller is self-contained.
- **Pros:**
  - ✅ Zero risk of accidentally changing legacy Blade-path behaviour.
- **Cons:**
  - ❌ Two copies of "create the first admin user" logic that must be kept in sync manually (e.g. if a future change adds an audit log entry or additional default permission, only one path gets updated unless both are remembered).

---

### ~~Q-051-03~~ · Post-success navigation ✅ RESOLVED

**Status:** Resolved — **Option A** (toast + auto-redirect, mirroring `RegisterPage.vue`)
**Feature:** 051 – v8 Admin Setup Page
**Priority:** Medium
**Opened:** 2026-07-26
**Resolved:** 2026-07-26

**Resolution:** On success, show a toast via `useAppToast()` and immediately `router.push({ name: "gallery" })`. No dedicated success screen.

**Spec impact:** Captured in FR-051-03, UI-051-05.

**Question**
The Blade flow shows a distinct `install.setup-success` page ("Admin account has been created", with a manual link to `home`) after `SetUpAdminController::create()` succeeds. `RegisterPage.vue` (`resources/js/v8/views/RegisterPage.vue:107-133`) — the closest existing v8 precedent for "create an account, then what" — instead fires a success toast via `useAppToast()` and immediately calls `router.push({ name: "gallery" })`. Which pattern should the new admin-setup page follow?

---

#### 🅰️ (**recommended**) Option A – Toast + auto-redirect (mirror `RegisterPage.vue`)

- **Idea:** On successful admin creation, show a success toast (`useAppToast().add(...)`) and immediately `router.push` to the gallery/home route. No intermediate success screen.
- **Spec impact:** One UI state (the form) plus a toast; matches `RegisterPage.vue` exactly.
- **Pros:**
  - ✅ Consistent with the one existing v8 "create an account" precedent — no new UX pattern to design/review.
  - ✅ Fewer states to build/test (no separate success view).
- **Cons:**
  - ❌ Less explicit than a dedicated confirmation screen; a user reading quickly might not register that the account was created before landing on the gallery.

---

#### 🅱️ Option B – Distinct success screen with manual continue

- **Idea:** Mirror the Blade `install.setup-success` page: after creation, replace the form with a "Your admin account has been created" message and a button to continue to the gallery/login.
- **Spec impact:** New UI state within the same component (or a second route), extra translation strings.
- **Pros:**
  - ✅ Explicit confirmation, closest to current (legacy) behaviour.
- **Cons:**
  - ❌ New UX pattern not used anywhere else in v8 for "action succeeded" — every other success case in v8 uses a toast, not a dedicated screen.

---

### ~~Q-051-04~~ · Route manifest placement — shared `paths.ts` or v8-local only? ✅ RESOLVED

**Status:** Resolved — **Option B** (added to shared `paths.ts`, for consistency)
**Feature:** 051 – v8 Admin Setup Page
**Priority:** Medium
**Opened:** 2026-07-26
**Resolved:** 2026-07-26

**Resolution:** The `admin-setup` route/name is added to `resources/js/router/paths.ts` alongside every other route, overriding the Option A recommendation, for manifest consistency. To close the accepted risk (v7's `componentByName[name]` has no fallback and would resolve to `component: undefined` if this name were ever reached by v7), the plan adds a defensive one-line fallback to v7's `routes.ts` lookup (`componentByName[p.name] ?? Placeholder`, matching v8's existing fallback pattern) as a small, low-risk companion change — this does not reintroduce a v7 feature, it only prevents an `undefined`-component crash if the route name is ever hit in a v7 context (e.g. a future misconfiguration). Recorded in **ADR-0007** alongside Q-051-01.

**Spec impact:** Captured in NFR-051-02, FR-051-06, and the plan's increment map (defensive v7 fallback task); ADR-0007.

**Question**
Every existing v8 route is declared once in the shared, component-free manifest `resources/js/router/paths.ts` and consumed by both `resources/js/v7/router/routes.ts` and `resources/js/v8/router/routes.ts` (Feature 049 / ADR-0006), so both bundles serve identical URLs. The new admin-setup route has, by design, **no v7 equivalent** (v7 keeps the Blade redirect unconditionally). v8's `componentByName` lookup falls back to `Placeholder` for unmapped names (`resources/js/v8/router/routes.ts:112`), but v7's equivalent lookup has **no fallback** — an unmapped name resolves to `component: undefined`. Should the new route be added to the shared manifest anyway (for consistency), or kept purely in v8's own router?

---

#### 🅰️ (**recommended**) Option A – v8-local router only

- **Idea:** Declare the new route directly in `resources/js/v8/router/routes.ts` (or a small v8-only routes array merged into it), outside `paths.ts`. It is a genuinely v8-exclusive route (unlike every other `paths.ts` entry, which represents a URL both bundles must serve identically per ADR-0006), so it doesn't belong in a manifest whose entire purpose is cross-bundle URL parity.
- **Spec impact:** No change to `paths.ts`; v7 is never at risk of resolving `component: undefined` for this name.
- **Pros:**
  - ✅ Zero risk to v7 (the file `paths.ts` feeds is v7's router too) — sidesteps the `undefined`-component landmine entirely rather than relying on v7 never being redirected there in practice.
  - ✅ Semantically correct: `paths.ts`'s stated purpose (per its own header comment) is routes both bundles serve; this route is the one deliberate exception.
- **Cons:**
  - ❌ Slightly inconsistent with "all routes live in one manifest" — a future reader must know this one route is declared differently.

---

#### 🅱️ Option B – Add to shared `paths.ts` for consistency

- **Idea:** Add the new route/name to `paths.ts` like every other route, accepting that v7's `componentByName[name]` resolves to `undefined` if ever reached (mitigated since v7's redirect target never changes — the flag branch in Q-051-01 only redirects here when `nuxt_ui` is active).
- **Spec impact:** One new entry in `paths.ts`; v7's router gains a route whose component is `undefined`.
- **Pros:**
  - ✅ Uniform pattern — every route lives in the same place.
- **Cons:**
  - ❌ Plants a latent landmine in v7's router (an `undefined` component) that depends entirely on "no code path ever navigates there" holding true forever, rather than being structurally impossible.

---

### ~~Q-051-05~~ · No JS test runner exists in this repo — planned Vitest coverage is blocked ✅ RESOLVED

**Status:** Resolved — **Option A** (accept the gap; no dependency added)
**Feature:** 051 – v8 Admin Setup Page
**Priority:** Medium
**Opened:** 2026-07-26
**Resolved:** 2026-07-26

**Resolution:** `AdminSetupPage.vue` is covered by `npm run check` (type-check, passing) and the backend feature tests (which exercise the full HTTP flow) only, consistent with every other v7/v8 view in this codebase. No new dev dependency added. Adding a JS test runner project-wide remains a separate, future decision if ever wanted.

**Spec impact:** `plan.md`'s Implementation Drift Gate and `tasks.md` T-051-13 updated to record this as accepted, not outstanding.

**Question**
`plan.md`/`tasks.md` (T-051-13) called for Vitest component tests on `AdminSetupPage.vue`. During implementation, no JS unit-test runner (Vitest or otherwise) was found anywhere in this repository — `npm run check` is `vue-tsc --noEmit` only (type-checking, not tests), and no existing v7 or v8 view has component-level tests. Adding Vitest would mean introducing a new dev dependency, which requires explicit user approval per this project's guardrails ("Never add or upgrade libraries without explicit user approval"). How should this gap be handled?

---

#### 🅰️ (**recommended**) Option A – Accept the gap for now; rely on `npm run check` + manual verification

- **Idea:** Leave `AdminSetupPage.vue` covered by TypeScript type-checking (`npm run check`, passing) and manual browser verification (T-051-14) only, consistent with every other v7/v8 view in this codebase today. No dependency added.
- **Pros:** Zero new dependencies; consistent with the project's current (repo-wide) testing posture; unblocks completing this feature now.
- **Cons:** No automated regression protection for the form's validation/success/error-branching logic specifically.

#### 🅱️ Option B – Add Vitest + `@vue/test-utils` as new dev dependencies, write the planned component tests

- **Idea:** Introduce a JS unit-test runner for the whole project (not just this feature), then write the planned `AdminSetupPage.vue` tests.
- **Pros:** Establishes real component-test coverage for `AdminSetupPage.vue` and opens the door to testing other v8 views going forward.
- **Cons:** New dependency + config surface affecting the whole frontend, decided in the middle of an unrelated feature — a larger, precedent-setting decision that deserves its own explicit go-ahead rather than being bundled into Feature 051.

---

**Next action:** Ask the user directly; do not add a dependency without their explicit approval.
