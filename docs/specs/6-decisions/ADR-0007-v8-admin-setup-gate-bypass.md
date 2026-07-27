# ADR-0007: Exempting a dedicated route from the `admin_user:set` gate to serve v8's admin-setup page

- **Status:** Accepted
- **Date:** 2026-07-26
- **Related features/specs:** Feature 051 (docs/specs/4-architecture/features/051-admin-setup-page/spec.md)
- **Related open questions:** Q-051-01, Q-051-04
- **Relationship to ADR-0006:** Extends ADR-0006's shared `router/paths.ts` manifest with one deliberate exception (a v8-only route with no v7 twin); does not amend ADR-0006's decision itself.

## Context

Every route in the `web` middleware group (`app/Http/Kernel.php:41-53`) is gated by `admin_user:set` (`App\Http\Middleware\AdminUserStatus`). When no admin user exists, `HasAdminUser::assert()` returns false, the middleware throws `AdminUserRequiredException`, and `AdminSetterHandler` (`app/Exceptions/Handlers/AdminSetterHandler.php`) 307-redirects to `route('install-admin')` — a standalone Blade form (`resources/views/install/setup-admin.blade.php`) served entirely outside the Vue app. This happens before `vueapp.blade.php` is ever rendered, so today neither the v7 nor v8 bundle ever mounts in this state.

The user asked for v8 deployments (`Features::active('nuxt_ui')`) to show a proper Nuxt UI page for first-admin creation instead of falling through to this Blade form, while v7 keeps its exact current behaviour. This requires deciding how a request that would otherwise be blocked by this security-relevant gate is allowed to reach a page that renders the SPA at all — a trust-boundary decision, not just a UI change, so it is recorded here rather than left implicit in spec.md.

A directly analogous precedent already exists in this codebase: `GET /register` (`routes/web_v2.php:75`) is an ordinary gated `web`-group route, but `PUT /Profile` (`routes/api_v2.php:193`, `ProfileController::register`) is a plain `api`-group JSON endpoint — and the `api` middleware group (`app/Http/Kernel.php:72-86`) has no `installation`/`admin_user` gating at all, so it is already reachable regardless of whether an admin exists. Individual route-level exemption from a group-level middleware is also already an established pattern: `GET /up` (`routes/web_v2.php:38`) uses `->withoutMiddleware(['admin_user:set'])` for the health check, and `install/admin` itself (`routes/web-install.php:29-35`) uses `admin_user:unset` to guard against being reachable once an admin already exists.

## Decision

1. **New web route, individually exempted:** `Route::get('setup-admin', VueController::class)->name('admin-setup')->withoutMiddleware(['admin_user:set'])->middleware(['admin_user:unset'])` in `routes/web_v2.php`. This mirrors `/up`'s exemption pattern and `install/admin`'s `admin_user:unset` self-guard (so directly navigating there once an admin exists correctly falls through to the existing `AdminUserAlreadySetException` handling, unchanged).
2. **Flag-aware redirect target:** `ToAdminSetter::go()` (or an equivalent small branch) checks `Features::active('nuxt_ui')` and redirects to `route('admin-setup')` when active, `route('install-admin')` otherwise. `HasAdminUser`, `AdminUserStatus`, and `AdminUserRequiredException` themselves are **not** modified — only the redirect *target* changes, and only when the flag is on.
3. **New unauthenticated JSON endpoint:** `POST /Admin::Setup` (`routes/api_v2.php`), living in the `api` middleware group (already ungated for installation/admin-user status, per the `/register`+`PUT /Profile` precedent), validated by the existing `SetUpAdminRequest`, and — critically — re-checking `HasAdminUser::assert()` inside a shared `App\Actions\User\CreateInitialAdmin` Action before creating anything, throwing the existing `AdminUserAlreadySetException` (403) if an admin already exists. `CreateInitialAdmin` itself wraps the existing general-purpose `App\Actions\User\Create` action (already used elsewhere for user creation, including its own `ConflictingPropertyException` → 409 on username collision) plus the `configs.owner_id` update, rather than reimplementing user creation. This closes the obvious risk of this being a standing, ungated privilege-escalation endpoint: it is only ever a no-op once the precondition it exists for (no admin) is no longer true.
4. **Route manifest placement (Q-051-04):** the `admin-setup` route/name is added to the shared, cross-bundle `resources/js/router/paths.ts` manifest introduced by ADR-0006 — the user chose consistency (every route lives in one manifest) over keeping this v8-exclusive route out of the file whose stated purpose is cross-bundle URL parity. Because v7's `componentByName` lookup (`resources/js/v7/router/routes.ts`) has no fallback for unmapped names (unlike v8's, which already falls back to `Placeholder`), this decision is paired with adding the same `?? Placeholder` fallback to v7's lookup — a one-line, behaviour-preserving safety net (a new `Placeholder.vue` is added to v7, mirroring v8's) rather than a reintroduction of v7 functionality.

## Consequences

### Positive
- Reuses two patterns already proven in this exact codebase (`/up`'s middleware exemption, `/register`+`PUT /Profile`'s web-page/JSON-API split) rather than inventing a new bypass mechanism.
- v7 is unaffected in every observable respect except the one-line defensive fallback (item 4), which changes nothing for any route v7 currently serves.
- The new API endpoint's attack surface is bounded by construction: `CreateInitialAdmin` is the single place that decides whether creation is allowed, and it always re-checks the admin-exists precondition regardless of which caller (Blade or API) invokes it.
- Small, reviewable diff: one new route, one redirect branch, one API endpoint + shared Action, one Vue view, one manifest entry, one defensive fallback.

### Negative
- The `admin-setup` route/name in `paths.ts` has no corresponding v7 component — a deliberate, documented exception to that manifest's "both bundles serve this" invariant established by ADR-0006, relying on the new fallback (not a structural impossibility) to stay safe if v7 ever resolved that name.
- `POST /Admin::Setup` is technically reachable (though inert once an admin exists) by anyone, including on v7-only deployments where `nuxt_ui` is off — this is an accepted trade-off identical in shape to `PUT /Profile` already being reachable regardless of authentication state; both are guarded by their own internal precondition checks rather than middleware.

## Alternatives Considered

- **A (chosen) — Dedicated exempted route + flag-aware redirect + new ungated API endpoint.** Described above.
- **B — Skip `admin_user:set` broadly for `nuxt_ui`-flagged `VueController` routes; expose `has_admin` via `GET /Gallery::Init`; client-side boot-time self-redirect.** Rejected: larger blast radius (a middleware-group-wide change rather than one route exemption), introduces a new pattern (no `router.beforeEach` guard exists anywhere in this codebase today) for no material benefit over Option A, and adds `has_admin` as new always-present public API surface.
- **C — Branch `install/admin`'s own route registration in `routes/web-install.php` on the flag, keeping one URL for both bundles.** Rejected: couples a file that today has zero Vue/`nuxt_ui` awareness to that concern, contrary to the v7-isolation principle established by ADR-0006 (v7's install path should stay untouched by v8 work).

## Security / Privacy Impact

- The new `/setup-admin` web route only ever serves an empty admin-creation form; it carries the same `admin_user:unset` guard as today's `install/admin`, so it is not reachable once an admin exists.
- The new `POST /Admin::Setup` API endpoint is unauthenticated by necessity (there is no admin to authenticate as), exactly like `install/admin`'s existing POST handler today — this is not a new class of exposure, only a new transport (JSON vs. HTML form) for the same operation, and it is closed off (409) the instant an admin exists, checked server-side on every call via `CreateInitialAdmin`, not cached or client-trusted.
- No change to `HasAdminUser`'s web-updater column-fallback behaviour, and no change to session/auth handling.

## Operational Impact

- No new environment variables or config keys — reuses the existing `nuxt_ui` flag (`NUXT_UI_ENABLED`).
- No new telemetry/logging surface (consistent with the existing, untelemetered admin-creation flow).
- Deployments already building `app-v8.ts` (per ADR-0006) require no additional build step; the new Vue view/component is part of the existing `v8/` tree.

## Links

- Related spec sections: `docs/specs/4-architecture/features/051-admin-setup-page/spec.md` (FR-051-01..06, NFR-051-02/03, API-051-01/02)
- Related plan: `docs/specs/4-architecture/features/051-admin-setup-page/plan.md` (Increment Map I2, I3, I5)
- Related open questions: Q-051-01, Q-051-04 (docs/specs/4-architecture/open-questions.md)
- Related ADRs: ADR-0006 (shared `router/paths.ts` manifest this ADR adds one exception to)
