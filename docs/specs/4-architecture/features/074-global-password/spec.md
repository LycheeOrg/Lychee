# Feature 074 – Global Gallery Password

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-09-27 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #074 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
An administrator can set one shared **gallery password**. While it is set, an anonymous visitor cannot see any gallery content, whether pages or API responses, until they enter it. RSS feeds and album embeds are disabled entirely while a password is set. Account holders keep signing in normally and are never asked for it. The unlock is stored in a dedicated encrypted cookie, not in the server-side session, because server-side sessions for anonymous visitors are unreliable ([ADR-074-02](../../../6-decisions/ADR-074-02-gallery-unlock-encrypted-cookie.md)). The visitor stays `Auth::guest()`, so every existing permission rule keeps its meaning ([ADR-074-01](../../../6-decisions/ADR-074-01-gallery-password-visitor-unlock.md)). The change touches config and migrations, a new middleware, the REST API v2/v3 route groups, `VueController`, the RSS feed routes, `InitConfig`, and both frontends, v7 (PrimeVue) and v8 (Nuxt UI), each with its own unlock screen and admin setting. The shared axios config, stores and services are changed once.

## Goals
- G1. When a gallery password is set, every anonymous request for gallery data is refused until the visitor's browser holds a valid unlock cookie.
- G2. A visitor can unlock the gallery in their browser by entering the password on a dedicated screen, in both v7 and v8.
- G3. Logged-in users are never asked for the gallery password, and the login flow stays reachable while the gallery is locked.
- G4. Admins can set, change or clear the password from settings, in both v7 and v8. The password is stored only as a hash and is never returned by any endpoint.
- G5. Changing or clearing the password invalidates every existing unlock cookie.
- G6. The gallery password's help text tells admins that (a) media files are only protected when secure image links are enabled, and (b) setting a gallery password disables RSS feeds and album embeds, for privacy.

## Non-Goals
- NG1. Protecting media files that the web server serves directly from `uploads/`. That protection comes only from secure or temporary image links (Q-074-02, Option A). This feature does not change `SecurePathController` or `PhotoAssetController`.
- NG2. A guest user account or any change to `users` ([ADR-074-01](../../../6-decisions/ADR-074-01-gallery-password-visitor-unlock.md)).
- NG3. Any frontend other than v7 and v8 (for example, a third-party client). The backend gate still applies to it.
- NG4. Per-user or multiple gallery passwords, password expiry, and storing the unlock in the server-side session.
- NG5. Exempting direct album links from the gallery password (Q-074-03, Option A: the gate is strict).
- NG6. Changing the existing `login_required` or `login_required_root_only` behaviour.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-074-01 | New config `gallery_password` (category `access_permissions`, order 13, `type_range = 'password'` (FR-074-10), `is_secret = true`, default `''`). Empty means the feature is off. A non-empty value is a `Hash::make()` hash. | Migration adds the row. With the empty default, the gate is a no-op. | Written only through the `password` config type's save path (FR-074-10). | — | — | Q-074-01, Q-074-05 |
| FR-074-02 | Gate middleware `GalleryPasswordRequired` (alias `gallery_password`) lets a request through when (a) `gallery_password` is empty, (b) `Auth::user() !== null`, or (c) the request carries the unlock cookie (FR-074-14), its fingerprint matches the current one, and its embedded expiry (if any) is in the future. Otherwise it throws `GalleryPasswordRequiredException`. | Request continues unchanged. | Fingerprint = `hash_hmac('sha3-256', <stored hash>, APP_KEY)`, compared with `hash_equals`. A stale fingerprint (password since changed or cleared and set again) does not match. | 401 JSON `{"message": "Gallery password required"}`. | The exception is in the handler's `$dontReport` list: a locked gallery is the expected state for anonymous visitors, so it is never logged. | Q-074-01, Q-074-03 |
| FR-074-03 | The gate is appended to the `api` middleware group, so it covers every `api/v2` and `api/v3` route by default. Only the routes listed in FR-074-04 opt out, each with `->withoutMiddleware('gallery_password')`. | New API routes are protected automatically. | — | — | — | Q-074-03 |
| FR-074-04 | Routes that stay reachable while locked: `Gallery::Init`, `Gallery::unlock`, `Auth::login`, `Auth::logout`, `Auth::user`, `Auth::config`, `WebAuthn::login/options`, `WebAuthn::login`, `Oauth::providers`, and the machine callbacks `FaceDetection/results`, `FaceDetection/cluster-results` and `NsfwDetection/results`. | Login, passkey and OAuth sign-in still work. AI services can still post results. | — | — | — | Q-074-03, G3 |
| FR-074-05 | While `gallery_password` is set, RSS feeds and album embeds are **disabled for everyone**, whatever the unlock cookie or login state. The existing on/off checks go through `GalleryLockState::isRssEnabled()` (`rss_enable` and no gallery password) in `RSSController::getRSS` and the `Meta` view component, and `GalleryLockState::isEmbedEnabled()` (`is_embed_enabled` and no gallery password) in `EmbedController::getAlbum`, `getPublicStream` and `InitConfig::is_embed_enabled`. The v7 and v8 embed menu entries and `AlbumHero` already hide themselves from `is_embed_enabled`, so no frontend change is needed. | RSS and embeds behave exactly as when their own config is off. | — | RSS: the existing `ConfigurationException` ("RSS is disabled by configuration"). Embed: the existing 404. No RSS `<link>` in the page head. | — | G6 |
| FR-074-06 | `VueController::gallery()` does not resolve the album or photo, and does not emit album or photo Open Graph metadata, when the request would fail the gate. It serves the plain SPA shell instead. | A locked visitor gets no album title, description or thumbnail in the HTML. | Gate check through the same pure predicate as FR-074-02. | — | — | G1 |
| FR-074-07 | `POST /api/v2/Gallery::unlock` `{password: string}` (throttled `10,1`, like `Album::unlock`). If `Hash::check()` succeeds, it queues the unlock cookie (FR-074-14) holding the fingerprint. Nothing is written to the server-side session. | 204. Later requests pass the gate. | `password` required, string. | Wrong password: 403 `{"message": "Password is invalid"}`. Feature off: 204 (no-op). Throttle: 429. | — | G2 |
| FR-074-08 | `InitConfig` gains `is_gallery_locked: bool`, which is true exactly when the gate would refuse this request. | Frontend decides at boot whether to show the unlock screen. | — | — | — | G2 |
| FR-074-09 | Unlock screen (UI-074-01), implemented in both v7 (`resources/js/v7`) and v8 (`resources/js/v8`): full-page password form with a "Sign in instead" link to `/login`. It is shown at boot when `is_gallery_locked`, and whenever any API response is 401 with message `Gallery password required` (cookie expired or password changed mid-browse). On success it clears the axios response cache and reloads the page, so the visitor lands on the requested route with the unlock cookie in place. Detecting the 401 (shared `resources/js/config/axios-config.ts`) and holding the locked state (shared store) are implemented once. Only the screen and the startup check exist per frontend. | — | Empty submit is blocked client-side. | Wrong password: inline error (UI-074-02). 429: inline "Too many attempts" error. | — | G2 |
| FR-074-10 | New config type `ConfigType::PASSWORD = 'password'` (hashed and never returned). In `Settings::setConfigs`, a config of this type is saved explicitly at the write site, with no model mutator or event: a non-empty value is stored as `Hash::make(value)`, an empty string clears it to `''`, and a config not sent is left unchanged. | Setting or changing the gallery password changes the hash and so invalidates every unlock cookie (G5). | Non-empty values shorter than 4 characters are rejected (`SetConfigsRequest`). Admin only, as with every setting. | 422 on validation failure. | — | Q-074-05, G4, G5 |
| FR-074-11 | `ConfigResource` never returns the stored value of a `password` config: `value` is always `''`, and a new `is_set: bool` tells whether a value is stored. This applies to `Settings::getAll`, the `setConfigs` response, and every other place that serialises configs. | The admin UI knows the state without ever receiving the hash. | — | — | — | Q-074-05, NFR-074-01 |
| FR-074-12 | Generic `password` setting input in the settings `ConfigGroup` of both v7 and v8 (UI-074-03): Set / Not set badge from `is_set`, a password field that saves when changed, and a Clear button that sends `''`. The field is never pre-filled. Both caveats (G6: media protection, and RSS and embeds disabled) are part of the `gallery_password` help text (`details`), shown statically. | — | Min length 4, mirroring FR-074-10. | API error shown the same way as for any setting. | — | Q-074-05, G4, G6, Q-074-02 |
| FR-074-13 | Order relative to `login_required`: the gallery gate runs first. After unlock, `login_required` still applies unchanged. The unlock cookie is independent of the login session, so logging in or out does not change it. | — | — | — | — | Q-074-03 |
| FR-074-14 | Unlock cookie `lychee_gallery_unlock`: value = JSON `{"f": <fingerprint>, "exp": <unix timestamp or null>}` (FR-074-02), encrypted and signed by Laravel's `EncryptCookies` with `APP_KEY`, `HttpOnly`, `SameSite=Lax`, `Secure` when the request is HTTPS, path `/`. Lifetime: `gallery_password_cookie_lifetime` days (FR-074-15). `0` makes it a browser-session cookie that expires when the browser closes (`exp = null`). For a positive lifetime, the server enforces `exp` itself, so a replayed cookie stops working at expiry even if the client ignores `Max-Age`. | Survives server-side session expiry and garbage collection. | A cookie that cannot be decrypted (tampered, or `APP_KEY` rotated), is malformed, or is past `exp` counts as absent. | Absent or stale cookie: locked. | — | ADR-074-02 |
| FR-074-15 | New config `gallery_password_cookie_lifetime` (category `access_permissions`, `type_range` `int:0:3650`, default `30`, `is_expert = true`, `is_secret = false`), placed right after `gallery_password`. It is edited through the standard settings screen in expert mode (v7 and v8, both rendering `int:min:max` types with a bounded `NumberField`), and the admin widget (FR-074-12) does not show it. It is read only when `Gallery::unlock` sets the cookie, so changing it affects only cookies set afterwards. | Cookie `Max-Age` = days × 86400, or no expiry when `0`. | Values outside 0–3650 are rejected by the standard config validation. | — | — | Q-074-04 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-074-01 | The password is never stored or returned in clear text, and the hash is never sent to any client. The unlock cookie holds only an encrypted HMAC fingerprint of the hash (keyed with `APP_KEY`), so neither the stored hash nor a database dump is enough to forge it. | Security | Tests assert that `Settings::getAll`, the `setConfigs` response and `Gallery::Init` contain no `gallery_password` value. | `Hash`, `EncryptCookies` | G4 |
| NFR-074-02 | The gate adds no database query per request beyond the already-cached `ConfigManager` lookup. | Performance | Code review. The middleware reads only `request()->configs()` and the request cookie. | `ConfigManager` | — |
| NFR-074-06 | bcrypt (`Hash::check`) runs **only** in `Gallery::unlock`, never in the gate, `VueController` or `InitConfig`. The per-request check is `hash_equals(hash_hmac('sha3-256', <stored hash>, APP_KEY), <cookie fingerprint>)` plus an integer expiry comparison, which takes microseconds, instead of about 100 ms for bcrypt. | Performance | `GalleryLockStateTest` and `GalleryPasswordTest` fail if `Hash::check` is called on a gated request (`Hash::spy()` / `shouldNotReceive('check')`). | `Hash` | Q-074-05 |
| NFR-074-03 | Unlock attempts are rate limited to 10 per minute per client. | Brute force | Feature test gets 429 on the 11th attempt. | `throttle` | FR-074-07 |
| NFR-074-04 | The gate logic is one pure predicate (`GalleryLockState::isLocked()`) shared by the middleware, `VueController` and `InitConfig`, so the three cannot drift apart. | Straight-line increments (AGENTS.md) | Unit test on the predicate. | — | — |
| NFR-074-05 | Works fully offline. No external dependency. | Offline-only requirement | — | — | — |

## UI / Interaction Mock-ups

UI-074-01: unlock screen (v7 and v8, full page, replaces the gallery while locked). Each tree uses its own component library, and the layout is the same.
```
+--------------------------------------------------------------+
|                                                              |
|                        [ site logo ]                         |
|                        <site title>                          |
|                                                              |
|          This gallery is protected by a password.            |
|                                                              |
|          ┌──────────────────────────────────────┐            |
|          │ Password                    ••••••   │            |
|          └──────────────────────────────────────┘            |
|                                                              |
|                    [   Enter gallery   ]                     |
|                                                              |
|                 Have an account? Sign in →                   |
|                                                              |
+--------------------------------------------------------------+
```

UI-074-02: wrong password or throttled
```
|          ┌──────────────────────────────────────┐            |
|          │ Password                    ••••••   │  (red)     |
|          └──────────────────────────────────────┘            |
|          ⚠ Password is invalid.                              |
|             (or: ⚠ Too many attempts, try again later.)      |
```

UI-074-03: generic `password` setting row (settings `ConfigGroup`, v7 and v8; shown here for `gallery_password` in the Permissions category)
```
+--------------------------------------------------------------+
| Gallery password                               [ ✓ Set ]     |
| <documentation>                                              |
| <details: Visitors must enter this password before seeing    |
|  anything. Signed-in users are never asked. Photo files are  |
|  only protected when secure image links are enabled.         |
|  ⚠ For privacy, a gallery password disables RSS feeds and    |
|  album embeds.>                                              |
|                                                              |
|  ┌────────────────────────────────┐                          |
|  │ New password                   │            [ Clear ]     |
|  └────────────────────────────────┘                          |
|  (saved with the rest of the settings, like any other field) |
+--------------------------------------------------------------+
```
When not set, the badge reads `[ Not set ]` and Clear is disabled. The field is never pre-filled.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-074-01 | Password not set: anonymous `GET /api/v2/Albums` behaves exactly as before. |
| S-074-02 | Password set, anonymous, not unlocked: `GET /api/v2/Albums`, a v3 listing, `Album::head` and `Timeline` return 401 `Gallery password required`. |
| S-074-03 | Password set, anonymous: `Gallery::Init` returns 200 with `is_gallery_locked = true`. |
| S-074-04 | Correct `Gallery::unlock`: 204, then `GET /api/v2/Albums` returns 200 and `is_gallery_locked = false`. |
| S-074-05 | Wrong `Gallery::unlock`: 403 `Password is invalid`, and the gallery stays locked. |
| S-074-06 | The 11th unlock attempt within a minute returns 429. |
| S-074-07 | Password set, logged-in user (no unlock): `GET /api/v2/Albums` returns 200 and `is_gallery_locked = false`. |
| S-074-08 | Password set, anonymous: `Auth::login` with valid credentials succeeds, and later calls return 200. |
| S-074-09 | Unlocked visitor (valid cookie), admin changes the password: the next anonymous request returns 401. |
| S-074-10 | Unlocked visitor (valid cookie), admin clears the password: requests return 200 without unlock (feature off). |
| S-074-11 | Password set: the RSS feed and `Embed/{album_id}` / `Embed/stream` are refused (RSS disabled error, embed 404) for an anonymous visitor, for a visitor with a valid unlock cookie, and for a logged-in user. `Gallery::Init` returns `is_embed_enabled = false`, and the page HTML has no RSS `<link>`. With the password cleared, both work again according to their own configs. |
| S-074-12 | Locked: `GET /gallery/{albumId}` HTML contains no album Open Graph title or description. |
| S-074-13 | Locked: AI callback routes (`FaceDetection/results`) are not refused by the gate. |
| S-074-14 | `setConfigs` with `gallery_password = 'secret'` by an admin stores a hash (`Hash::check('secret', stored)` is true), not the clear text. A value of 3 characters returns 422. `''` clears it. Leaving it out of the request leaves it unchanged. A non-admin gets 403, as for every setting. |
| S-074-15 | `Settings::getAll`, the `setConfigs` response and `Gallery::Init` never contain the hash. The `gallery_password` entry has `value = ''` and `is_set` matching the stored state. |
| S-074-16 | Password set and `login_required` on: after unlock, anonymous `GET /api/v2/Albums` still returns 401 `Login required.`. |
| S-074-17 | Password set, unlock with `Gallery::unlock`, then the server-side session is flushed (simulating expiry or garbage collection): the next request with the cookie still returns 200. A tampered cookie value returns 401. A cookie whose embedded `exp` is in the past returns 401 even though the client still sends it. |
| S-074-20 | `gallery_password_cookie_lifetime = 30`: the unlock response's cookie expires about 30 days out. With `0`, the cookie has no expiry (browser-session cookie). |
| S-074-21 | Password set, valid unlock cookie: 20 gated API requests make no `Hash::check` call (`Hash::spy()`). Only `Gallery::unlock` calls it (NFR-074-06). |
| S-074-18 | (Manual, v7 and v8) Boot while locked shows UI-074-01. Unlock continues to the deep-linked album. A 401 mid-browse brings the screen back. |
| S-074-19 | (Manual, v7 and v8) The `password` setting row sets and clears the gallery password. The badge follows `is_set`, and the field is never pre-filled. |

## Test Strategy
- **Unit:** `GalleryLockStateTest` (predicate: off, logged-in, matching cookie fingerprint, stale fingerprint, expired `exp`, `exp = null`, no cookie, with a `Hash::shouldNotReceive('check')` assertion).
- **Feature tests (all in `tests/Feature_v3/GalleryPassword/`, extending `Tests\Feature_v3\Base\BaseApiWithDataTest`; no new tests in Feature_v2):** `GalleryPasswordTest` covers v2 and v3 endpoints (S-074-01..11, 13, 16, 17, 20, 21). v2 calls use the inherited `getJson()`/`postJson()` and v3 calls use `getJsonV3()`. `PasswordConfigTypeTest` covers S-074-14 and 15, and `GalleryPasswordWebTest` covers S-074-12 (HTML contains no Open Graph metadata). Failing tests are staged first.
- **Frontend (v7 and v8):** no automated suite. `npm run check` plus manual S-074-18 and S-074-19 in each tree (v7 runs with `features.v8` off, v8 with it on).
- **Contracts:** regenerate TypeScript types for `InitConfig`, `ConfigResource` (`is_set`) and `ConfigType` (`password`).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-074-01 | `gallery_password` config (hash or `''`). | configs, migration |
| DO-074-02 | Encrypted cookie `lychee_gallery_unlock` holding `{f: HMAC fingerprint of the hash, exp}` (FR-074-14). | cookies, middleware |
| DO-074-03 | `App\Services\GalleryLockState`: pure predicate `isLocked(string $stored_hash, bool $is_logged_in, ?string $cookie_value, int $now): bool`, request wrapper `isLockedForRequest(Request)`, `isPasswordSet()`, `isRssEnabled()`, `isEmbedEnabled()`, and the cookie helpers `fingerprint()`, `makeCookieValue()`, `makeUnlockCookie()`. | `app/Services` |
| DO-074-05 | `gallery_password_cookie_lifetime` config (int days, 0–3650, default 30, expert). | configs, migration |
| DO-074-06 | `ConfigType::PASSWORD` (`'password'`): hashed on write, masked on read (`value = ''`, `is_set`). | enums, configs, settings, v7/v8 settings UI |
| DO-074-04 | `GalleryPasswordRequiredException` (401, message `Gallery password required`). | exceptions |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-------------|-------------|-------|
| API-074-01 | REST POST /api/v2/Gallery::unlock | Unlocks the gallery by setting the unlock cookie. | `UnlockGalleryRequest`, throttle `10,1`, exempt from the gate. |
| API-074-02 | REST POST /api/v2/Settings::setConfigs | Hashes `password`-type values before saving (FR-074-10). | Existing `SetConfigsRequest`, extended. |
| API-074-03 | REST GET /api/v2/Gallery::Init | Adds `is_gallery_locked`. | `InitConfig`. |
| API-074-04 | REST GET /api/v2/Settings | `ConfigResource` masks `password`-type values and adds `is_set` (FR-074-11). | Existing endpoint. |

### CLI Commands / Flags
None.

### Telemetry Events
None. Lychee has no telemetry pipeline, and failed attempts are bounded by the throttle.

### Fixtures & Sample Data
None.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-074-01 | Unlock screen | `is_gallery_locked` at boot, or a 401 `Gallery password required` response. |
| UI-074-02 | Unlock error | 403 or 429 from API-074-01. |
| UI-074-03 | Generic `password` setting row | Any config with `type_range = 'password'`, in v7 and v8 `ConfigGroup`. |

## Telemetry & Observability
None beyond Laravel's standard throttle responses.

## Documentation Deliverables
- Roadmap entry for Feature 074.
- Knowledge map: the `GalleryPasswordRequired` middleware in the `api` group and its opt-out list.
- [ADR-074-01](../../../6-decisions/ADR-074-01-gallery-password-visitor-unlock.md) and [ADR-074-02](../../../6-decisions/ADR-074-02-gallery-unlock-encrypted-cookie.md).
- Settings help text (lang `en`) covering the media-protection caveat (Q-074-02).

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-074-01
    name: gallery_password
    type: config
    fields:
      - name: value
        type: string
        constraints: "'' (off) or bcrypt hash"
  - id: DO-074-05
    name: gallery_password_cookie_lifetime
    type: config
    constraints: "int:0:3650, default 30, expert; 0 = browser-session cookie"
  - id: DO-074-06
    name: ConfigType::PASSWORD
    type: enum case
    constraints: "'password'; hashed on write, masked on read"
  - id: DO-074-02
    name: cookie.lychee_gallery_unlock
    type: string
    constraints: "encrypted {f: hmac_sha3_256(APP_KEY, gallery_password hash), exp}; HttpOnly; SameSite=Lax"
routes:
  - id: API-074-01
    method: POST
    path: /api/v2/Gallery::unlock
  - id: API-074-02
    method: POST
    path: /api/v2/Settings::setConfigs
  - id: API-074-03
    method: GET
    path: /api/v2/Gallery::Init
  - id: API-074-04
    method: GET
    path: /api/v2/Settings
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-074-01
    description: Gallery unlock screen
  - id: UI-074-02
    description: Unlock error (invalid / throttled)
  - id: UI-074-03
    description: Generic password setting row
```
