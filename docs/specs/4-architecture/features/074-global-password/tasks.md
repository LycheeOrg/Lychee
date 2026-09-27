# Feature 074 Tasks – Global Gallery Password

_Status: Draft_  
_Last updated: 2026-09-27_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md).

## Checklist

### I1 – Config and predicate
- [ ] T-074-01 – Failing `tests/Unit/.../GalleryLockStateTest` (FR-074-02, NFR-074-04).  
  _Intent:_ Cases: password empty → unlocked; logged-in user → unlocked; matching fingerprint → unlocked; stale fingerprint → locked; `exp` in the past → locked; `exp = null` → unlocked; malformed payload → locked; no cookie → locked. Every case asserts `Hash::shouldNotReceive('check')` (NFR-074-06).  
  _Verification commands:_ `php artisan test --filter=GalleryLockStateTest` (expected red).
- [ ] T-074-02 – Migration `add_gallery_password_config` (FR-074-01, FR-074-15).  
  _Intent:_ `BaseConfigMigration`, key `gallery_password`, value `''`, cat `access_permissions`, `is_secret` true, order after `login_required_root_only`. Also `gallery_password_cookie_lifetime`: `int:0:3650`, default `30`, `is_expert` true, next in order.  
  _Verification commands:_ covered by T-074-01 and T-074-04.
- [ ] T-074-03 – `GalleryLockState` predicate, `GalleryPasswordRequiredException` (401, `Gallery password required`) the cookie name constant, and HMAC fingerprint and `{f, exp}` payload helpers (FR-074-02, FR-074-14).  
  _Verification commands:_ `php artisan test --filter=GalleryLockStateTest`, `make phpstan`.

### I2 – Gate middleware and wiring
- [ ] T-074-04 – Failing `tests/Feature_v3/GalleryPassword/GalleryPasswordTest` gate cases on v2 endpoints (S-074-01, 02, 07, 08, 11, 13, 16, 21).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest` (expected red).
- [ ] T-074-05 – Failing v3 listing cases in the same `GalleryPasswordTest`, using `getJsonV3()` (S-074-02).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest` (expected red).
- [ ] T-074-06 – `GalleryPasswordRequired` middleware, `gallery_password` alias, appended at the end of the `api` group, after `EncryptCookies` and `AuthenticateSession` (FR-074-02, FR-074-03).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest`.
- [ ] T-074-07 – `withoutMiddleware('gallery_password')` on the FR-074-04 routes (FR-074-04). Add `GalleryLockState::isPasswordSet()` to the existing `rss_enable` checks (`RSSController`, `Meta`) and `is_embed_enabled` checks (`EmbedController::getAlbum`/`getPublicStream`, `InitConfig`) (FR-074-05, S-074-11).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest`, `--filter=AuthTest`, `--filter=Embed` (regressions).

### I3 – Unlock endpoint and Init flag
- [ ] T-074-08 – Failing unlock cases in `GalleryPasswordTest` (S-074-03, 04, 05, 06, 09, 10, 17, 20).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest` (expected red).
- [ ] T-074-09 – `UnlockGalleryRequest`, controller action, `POST Gallery::unlock` (throttle `10,1`, exempt from the gate) queueing the unlock cookie with the configured lifetime (FR-074-14, FR-074-15), `InitConfig::is_gallery_locked` (FR-074-07, FR-074-08, NFR-074-03).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordTest`, `make phpstan`.

### I4 – `password` config type
- [ ] T-074-10 – Failing `tests/Feature_v3/GalleryPassword/PasswordConfigTypeTest` (S-074-14, 15).  
  _Intent:_ Cases: an admin write stores a hash, not the clear text; a 3-character value returns 422; `''` clears it; leaving the key out leaves it unchanged; a non-admin gets 403; `getAll` and the `setConfigs` response return `value = ''` with the correct `is_set`; `Gallery::Init` does not leak the hash.  
  _Verification commands:_ `php artisan test --filter=PasswordConfigTypeTest` (expected red).
- [ ] T-074-11 – `ConfigType::PASSWORD`. In `SetConfigsRequest`, validate min 4 for non-empty values. In `SettingsController::setConfigs`, hash with `Hash::make()` at the write site (no mutator), `''` clears (FR-074-10).  
  _Verification commands:_ `php artisan test --filter=PasswordConfigTypeTest`, and existing `--filter=UpdateSettingsTest` for regressions.
- [ ] T-074-12 – `ConfigResource` masks `password` values (`value = ''`) and adds `is_set: bool`. Check that no other config serialiser (diagnostics, `InitConfig`) exposes the raw value (FR-074-11, NFR-074-01).  
  _Verification commands:_ `php artisan test --filter=PasswordConfigTypeTest`, `make phpstan`.

### I5 – Web Open Graph gate
- [ ] T-074-13 – Failing `tests/Feature_v3/GalleryPassword/GalleryPasswordWebTest` (S-074-12).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordWebTest` (expected red).
- [ ] T-074-14 – `VueController::gallery()` skips album and photo resolution when locked (FR-074-06).  
  _Verification commands:_ `php artisan test --filter=GalleryPasswordWebTest`.

### I6 – Contracts and lang
- [ ] T-074-15 – `php artisan typescript:transform` (`InitConfig.is_gallery_locked`, `ConfigResource.is_set`, `ConfigType` `password`).  
  _Verification commands:_ `npm run check`.
- [ ] T-074-16 – `lang/en` keys for the unlock screen, the password field (Set / Not set / Clear), and the `gallery_password` and `gallery_password_cookie_lifetime` documentation and details, including both caveats (G6): media protection, and RSS and embeds being disabled. Edit the PHP source, then run `php artisan lang:json`. Other locales follow the repo convention.  
  _Verification commands:_ `npm run check`.

### I7 – Shared frontend plumbing
- [ ] T-074-17 – Shared layer used by both trees: gallery service `unlock()` call, a `is_gallery_locked` flag in the shared store, and an axios interceptor branch in `resources/js/config/axios-config.ts` that sets the flag on 401 `Gallery password required` (added to the message allowlist so no generic error toast is shown) (FR-074-09).  
  _Verification commands:_ `npm run format`, `npm run check`.

### I8 – Unlock screen (v8 and v7)
- [ ] T-074-18 – v8: `resources/js/v8/views/GalleryUnlock.vue` and the startup check in `v8/views/App.vue` (FR-074-09, UI-074-01, UI-074-02, S-074-18).  
  _Verification commands:_ `npm run format`, `npm run check`.
- [ ] T-074-19 – v7: `resources/js/v7/views/GalleryUnlock.vue` (PrimeVue) and the startup check in `v7/views/App.vue` (FR-074-09, UI-074-01, UI-074-02, S-074-18).  
  _Verification commands:_ `npm run format`, `npm run check`.

### I9 – `password` setting input (v8 and v7)
- [ ] T-074-20 – v8: `PasswordField.vue` plus a `config.type === 'password'` branch in `v8/components/settings/ConfigGroup.vue`. It has a badge from `is_set`, is never pre-filled, and has a Clear button that sends `''` (FR-074-12, UI-074-03, S-074-19).  
  _Verification commands:_ `npm run format`, `npm run check`.
- [ ] T-074-21 – v7: the same in `v7/components/settings/ConfigGroup.vue` with a PrimeVue `PasswordField.vue` (FR-074-12, UI-074-03, S-074-19).  
  _Verification commands:_ `npm run format`, `npm run check`.

### I10 – Docs and drift gate
- [ ] T-074-22 – Knowledge map entry for the gate and its opt-out list. Update the roadmap row.
- [ ] T-074-23 – Manual browser check of S-074-18 and S-074-19 in both trees (`features.v8` off for v7, on for v8). Record the drift gate report in plan.md.  
  _Verification commands:_ `vendor/bin/php-cs-fixer fix`, the scoped tests above (run one at a time), `make phpstan`, `npm run check`.

## Notes / TODOs
- Never run two test commands concurrently (shared SQLite).
- No new tests go in Feature_v2. All feature tests for this feature live in `tests/Feature_v3/GalleryPassword/`.
- The default config value `''` keeps every existing suite ungated.
