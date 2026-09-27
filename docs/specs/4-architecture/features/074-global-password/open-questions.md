# Open Questions – Feature 074

Open questions for [Feature 074](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-074-01~~ | 074 | High | Implementation path: per-visitor gallery unlock vs. a dedicated "guest" user account | Resolved (Option A, spec FR-074-01..13, ADR-074-01) | 2026-09-27 | 2026-09-27 |
| ~~Q-074-02~~ | 074 | High | Scope of protection: API/pages only, or also directly-served media files | Resolved (Option A, spec NG1, FR-074-11/12) | 2026-09-27 | 2026-09-27 |
| ~~Q-074-03~~ | 074 | Medium | Interaction with `login_required` and with per-album passwords / shared links | Resolved (Option A, spec FR-074-03..05, FR-074-13, NG5) | 2026-09-27 | 2026-09-27 |
| ~~Q-074-04~~ | 074 | Medium | Lifetime of the `lychee_gallery_unlock` cookie | Resolved (Option A as an expert setting, spec FR-074-14, FR-074-15) | 2026-09-27 | 2026-09-27 |
| ~~Q-074-05~~ | 074 | Medium | New write-only config type (hashed / encrypted) instead of a dedicated gallery-password endpoint and widget | Resolved (Option A, spec FR-074-10..12, NFR-074-06, ADR-074-03) | 2026-09-27 | 2026-09-27 |

## Question Details

### ~~Q-074-01~~ – Implementation path

**Resolution:** Option A. See spec FR-074-01..13 and [ADR-074-01](../../../6-decisions/ADR-074-01-gallery-password-visitor-unlock.md).

**Context.** A global password must be entered before a visitor can open the gallery at all, in the same way `login_required` (`App\Http\Middleware\LoginRequired`, applied as `login_required:root|album|always` on `routes/api_v2.php` and `routes/web_v2.php`) forces a login. Two paths are possible: (1) a dedicated feature, or (2) a special "guest" user plus an extra guest login page.

Key facts from the codebase:
- Around 65 call sites branch on `Auth::check()`, `Auth::guest()` or `Auth::user() !== null` (policies, `InitConfig`, `LoginRequired`, the rating, upload and profile paths, and so on). Each one would treat a logged-in guest account as a full registered user.

**Option A (recommended): a dedicated per-visitor unlock.**
Add a hashed `gallery_password` config and a `GalleryPasswordRequired` middleware that passes through when the user is logged in, when no password is set, or when the visitor holds a valid unlock token. Otherwise it throws a dedicated `GalleryPasswordRequiredException`. Add a `POST /Gallery::unlock` endpoint that checks the password (throttled) and issues the unlock token. On the frontend, add a small unlock screen handled the same way as `"Login required."` in `axios-config.ts`.
- Pros: small and self-contained. It reuses the proven album-unlock pattern. The visitor stays `Auth::guest()`, so every existing permission rule (public, shared-with-users, ownership) keeps its meaning with no special cases. There is no fake account to hide from user lists, statistics, sharing dialogs or OAuth/WebAuthn. Changing the password invalidates every unlock.
- Cons: new UI and endpoint code instead of reusing the login dialog.

**Option B: a guest user plus a guest login page.**
Create a flagged `User` (for example `is_guest_account`). The guest page logs in as that user with the shared password.
- Pros: reuses the existing login flow, session handling and `login_required` for free.
- Cons: the visitor becomes a registered user, so all ~65 guest/auth checks change behaviour. The visitor would see albums shared with "all users", could get rating, upload or profile rights, would appear in user lists, sharing pickers and statistics, and could change "their" password. Every one of those needs a special case, now and in every future feature. That is a larger and ongoing security surface. It also blurs audit and ownership semantics.

**Option C: have `login_required` accept an anonymous password.**
Extend `LoginRequired` so that a matching password unlocks the visitor, and reuse the login modal with a password-only mode.
- Pros: the smallest diff. It sits next to the existing mechanism.
- Cons: it mixes two concepts in one middleware and one config category. The login modal's username/OAuth/WebAuthn UX does not fit a password-only prompt. It is harder to explain in settings.

### ~~Q-074-02~~ – Scope of protection (media files)

**Resolution:** Option A. See spec NG1, FR-074-11 and FR-074-12 (warning in the admin widget).

**Context.** Pages and API calls go through Laravel middleware. Original and size-variant files under `uploads/` are served directly by the web server unless secure or temporary image links are enabled (`SecurePathController`, `PhotoAssetController` with `X-Mac`). With Option A alone, anyone who knows or guesses a media URL bypasses the gallery password.

**Option A (recommended): protect pages and API, and document that media is only protected when secure image links are enabled.** Also show a warning in the setting's description or admin page when the gallery password is set but secure links are off.
- Pros: honest and cheap. It matches how `login_required` already behaves.
- Cons: the default install leaves media URLs reachable.

**Option B: require (force-enable) secure image links while a gallery password is set, and have `SecurePathController` check the unlock flag.**
- Pros: real end-to-end protection.
- Cons: couples two features. It has a performance cost on media serving, and web server configs that serve `uploads/` directly still bypass it.

**Option C: pages and API only, with no warning.**
- Pros: least work.
- Cons: gives a false sense of security.

### ~~Q-074-03~~ – Interaction with `login_required`, album passwords and shared links

**Resolution:** Option A. See spec FR-074-03..05, FR-074-13 and NG5.

**Context.** A gallery can have `login_required`, `login_required_root_only`, per-album passwords and direct album links (`unlock_password_photos_with_url_param`).

**Option A (recommended):** The gallery password is checked first and applies to every anonymous request, including direct album links. Logged-in users always bypass it. The login page and endpoints stay reachable without the gallery password, so account holders can still sign in. If `login_required` is also on, it still applies after the unlock.
- Pros: simple and predictable. It is always a strict extra gate.
- Cons: a direct album link to a password-protected album needs two passwords.

**Option B:** Same as Option A, but a direct link to a specific album skips the gallery password. This mirrors `login_required_root_only`.
- Pros: sharing single albums stays frictionless.
- Cons: weakens the "can't open the gallery at all" goal. It adds an extra config and more branches.

### ~~Q-074-04~~ – Lifetime of the unlock cookie

**Resolution:** Option A, with the config marked expert (`is_expert = true`). See spec FR-074-14 and FR-074-15.

**Context.** The gallery unlock is stored in an encrypted cookie ([ADR-074-02](../../../6-decisions/ADR-074-02-gallery-unlock-encrypted-cookie.md), spec FR-074-14). The cookie needs an expiry. It is also invalidated for everyone whenever the password changes.

**Option A (recommended): configurable in days, default 30.**
New config `gallery_password_cookie_lifetime` (integer days, `0` = expires when the browser closes), placed next to `gallery_password` in the Permissions category.
- Pros: admins choose between convenience (long) and strictness (`0`). One small config, read only when the cookie is set.
- Cons: one more setting.

**Option B: fixed 30 days.**
- Pros: simplest, with no new setting.
- Cons: admins who want visitors to re-enter the password more often can only do so by changing the password.

**Option C: browser-session cookie (expires when the browser closes).**
- Pros: strictest, with no new setting.
- Cons: visitors re-enter the password on every visit. Browsers that restore sessions keep the cookie anyway, so the behaviour is inconsistent.

### ~~Q-074-05~~ – A secure config type instead of a dedicated endpoint

**Resolution:** Option A, with bcrypt verification running only in `Gallery::unlock` and never per request. See spec FR-074-10..12, NFR-074-06 and [ADR-074-03](../../../6-decisions/ADR-074-03-password-config-type.md). An `encrypted` type is out of scope.

**Context.** The gallery password must be stored hashed and never returned to clients. This can be done with a dedicated endpoint and custom widgets, or with a new kind of setting whose value is hashed when saved.

Facts from the codebase:
- `ConfigType` (`app/Enum/ConfigType.php`) has no secret type. `SettingsController::setConfigs` writes `$config->value` verbatim, and `ConfigResource` returns every value verbatim.
- `is_secret` only hides a value from diagnostics (`App\Actions\Diagnostics\Configuration`). It has no effect on the settings API.
- Among the existing settings, only `license_key` (type `license`) looks like a real secret. Payment and AI keys live in `.env`. So a reversible-encryption type would have no consumer today.
- There are two different needs. A value that is only ever checked (like the gallery password) can be one-way hashed. A secret the server must read back (an API key) needs reversible encryption with `APP_KEY`.

**Option A (recommended): add a `password` config type (hashed, write-only) now, and defer an `encrypted` type.**
- Add `ConfigType::PASSWORD = 'password'`, and set `gallery_password` to `type_range = 'password'`.
- On write, `setConfigs` hashes the value with `Hash::make()`, explicitly at the write site with no model mutator. An empty string clears it, and a config that is not sent stays unchanged.
- On read, `ConfigResource` never returns the value. It returns `''` plus `is_set: bool` for this type. Diagnostics already hide it because `is_secret` is true.
- In `ConfigGroup` (v7 and v8), render a password field with a Set / Not set badge and a Clear button. The secure-links warning (G6) goes in the setting's `details` text.
- Pros: less code overall, and it can be reused by any future "check-only" secret. It fits the existing settings pipeline, and it fixes the "the hash is sent to the admin UI" problem generically.
- Cons: it touches the generic settings path, which every setting goes through, so it needs careful tests. The G6 warning becomes static text instead of showing only when secure links are off.

**Option B: add both `password` (hashed) and `encrypted` (reversible, `Crypt::encryptString`, read through a `ConfigManager::getValueAsDecrypted()`).**
- Pros: covers future secrets, such as moving `license_key` or SMTP credentials into settings.
- Cons: the `encrypted` half has no consumer in this feature, so it is untested by real use. Rotating `APP_KEY` would make stored values unreadable, which needs a recovery story. It is scope creep.

**Option C: keep the current design (dedicated endpoint and custom widget).**
- Pros: no change to the generic settings path, and the warning can react to the secure-links setting.
- Cons: one-off code in the backend and in two frontends. The next secret would need the same work again.

