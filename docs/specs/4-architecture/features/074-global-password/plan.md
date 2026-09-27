# Feature Plan 074 – Global Gallery Password

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-09-27

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
An admin can lock the whole gallery behind one shared password. Success means:
- every anonymous content request (v2, v3, Open Graph HTML) is refused until the browser holds a valid unlock cookie (S-074-02, 12), and RSS and embeds are disabled while a password is set (S-074-11);
- login is never blocked (S-074-07, 08);
- the hash never leaves the server (NFR-074-01);
- changing the password relocks everyone (S-074-09).

## Scope Alignment
- **In scope:** config migration, `GalleryLockState` predicate, `GalleryPasswordRequired` middleware in the `api` group with an explicit opt-out list, the `Gallery::unlock` endpoint, a reusable `password` config type (hashed on write, masked on read), the `InitConfig` flag, gating `VueController`, disabling RSS and embeds while a password is set, the shared frontend plumbing (axios, store, service), and the unlock screen and `password` setting input in both v7 and v8.
- **Out of scope:** media file protection (NG1), guest accounts (NG2), non-v7/v8 clients (NG3), any `login_required` change (NG6).

## Dependencies & Interfaces
- `App\Http\Kernel` (`api` group, aliases), `routes/api_v2.php`, `routes/api_v3.php`, `App\Http\Controllers\VueController`, `RSSController`, `App\View\Components\Meta`, `EmbedController`, `App\Http\Resources\GalleryConfigs\InitConfig`, `App\Http\Controllers\Admin\SettingsController::setConfigs`, `SetConfigsRequest`, `ConfigResource`, `App\Enum\ConfigType`.
- Existing pattern: `App\Actions\Album\Unlock` and `AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY`.
- Frontend, shared: `resources/js/config/axios-config.ts`, `resources/js/stores`, `resources/js/services` (`InitService`). Per tree: `v7/views/App.vue` and `v8/views/App.vue`, `v7/components/settings` and the v8 settings components. Editing the shared modules in place is intended here, because the behaviour is wanted in both trees.
- [ADR-074-01](../../../6-decisions/ADR-074-01-gallery-password-visitor-unlock.md), [ADR-074-02](../../../6-decisions/ADR-074-02-gallery-unlock-encrypted-cookie.md).

## Assumptions & Risks
- **Assumption:** the gate runs after `EncryptCookies` (so the cookie is already decrypted) and after `AuthenticateSession` (so `Auth::user()` is known). Mitigation: append the gate at the end of the `api` group.
- **Risk:** existing Feature_v2 and Feature_v3 tests assume an ungated API. Mitigation: the config defaults to `''`, so the gate is a no-op and the existing suites are unaffected.
- **Risk:** a public-by-design route is missed in the opt-out list. It then fails closed (locked). Mitigation: S-074-08 and S-074-13 tests, plus an audit of `withoutMiddleware(['auth'])` routes during I2.
- **Risk:** the `password` type changes the generic settings save and read path that every setting goes through. Mitigation: the type-specific branch sits at the single write site and in `ConfigResource`, `PasswordConfigTypeTest` covers it, and the existing `UpdateSettingsTest` is rerun for regressions.
- **Risk:** bcrypt on every request (about 100 ms). Mitigation: `Hash::check` runs only in `Gallery::unlock`, and the gate compares an HMAC-SHA3-256 fingerprint with `hash_equals` (NFR-074-06). S-074-21 asserts it with `Hash::spy()`.

## Implementation Drift Gate
After all tasks are `[x]`: rerun `php artisan test --filter=GalleryLockStateTest`, `--filter=GalleryPasswordTest`, `--filter=PasswordConfigTypeTest`, `--filter=GalleryPasswordWebTest`, then `make phpstan` and `npm run check`. Map FR-074-01..15 and NFR-074-01..06 to code and tests in a table appended here, and record manual S-074-18 and S-074-19 results for both v7 and v8.

## Increment Map

1. **I1 – Config and predicate** (FR-074-01, FR-074-02, NFR-074-04)
   - _Steps:_ failing `GalleryLockStateTest`, then the `gallery_password` migration (`BaseConfigMigration`, category `access_permissions`, `is_secret` true), then `GalleryLockState` (pure: off, logged in, fingerprint match), then `GalleryPasswordRequiredException` (401).
   - _Commands:_ `php artisan test --filter=GalleryLockStateTest`, `make phpstan`.
2. **I2 – Gate middleware and route wiring** (FR-074-02..05, FR-074-13)
   - _Steps:_ failing `GalleryPasswordTest` cases S-074-01, 02, 07, 08, 11, 13, 16 and v3 listing cases in the same class (`tests/Feature_v3/GalleryPassword/`), then the middleware plus the `gallery_password` alias appended to the `api` group, then `withoutMiddleware('gallery_password')` on the FR-074-04 routes, then `isPasswordSet()` added to the existing RSS and embed on/off checks.
   - _Commands:_ `php artisan test --filter=GalleryPasswordTest`, and existing `--filter=AuthTest` and `--filter=Embed` for regressions.
3. **I3 – Unlock endpoint and Init flag** (FR-074-07, FR-074-08, NFR-074-03)
   - _Steps:_ failing S-074-03..06, 09, 10, 17, then `UnlockGalleryRequest` plus `GalleryUnlockController` (or a `ConfigController::unlock`) and the route (throttle `10,1`), then `InitConfig::is_gallery_locked`.
   - _Commands:_ `php artisan test --filter=GalleryPasswordTest`.
4. **I4 – `password` config type** (FR-074-10, FR-074-11, NFR-074-01)
   - _Steps:_ failing `PasswordConfigTypeTest` (S-074-14, 15), then `ConfigType::PASSWORD`, min-4 validation in `SetConfigsRequest`, `Hash::make()` at the `setConfigs` write site (`''` clears it, with no model mutator), then `ConfigResource` masking (`value = ''`, `is_set`).
   - _Commands:_ `php artisan test --filter=PasswordConfigTypeTest`, `--filter=UpdateSettingsTest`, `make phpstan`.
5. **I5 – Web Open Graph gate** (FR-074-06)
   - _Steps:_ failing `GalleryPasswordWebTest` (S-074-12), then `VueController::gallery()` skips album and photo resolution when `GalleryLockState::isLocked()`.
   - _Commands:_ `php artisan test --filter=GalleryPasswordWebTest`.
6. **I6 – TypeScript contracts and lang** (FR-074-08, FR-074-11)
   - _Steps:_ `php artisan typescript:transform`, add `lang/en` keys (PHP source, then `php artisan lang:json`), with placeholders copied to other locales following the repo convention.
   - _Commands:_ `npm run check`.
7. **I7 – Shared frontend plumbing** (FR-074-09)
   - _Steps:_ service `unlock()`, a shared store flag, and the axios interceptor branch for `Gallery password required` (added to the existing message allowlist so no generic error toast is shown).
   - _Commands:_ `npm run format`, `npm run check`.
8. **I8 – Unlock screen, v8 and v7** (FR-074-09, UI-074-01, UI-074-02)
   - _Steps:_ `GalleryUnlock.vue` plus the startup check on `is_gallery_locked` in each tree's `App.vue` (Nuxt UI in v8, PrimeVue in v7).
   - _Commands:_ `npm run format`, `npm run check`.
9. **I9 – `password` setting input, v8 and v7** (FR-074-12, UI-074-03)
   - _Steps:_ `PasswordField.vue` plus a `config.type === 'password'` branch in each tree's `components/settings/ConfigGroup.vue` (badge from `is_set`, never pre-filled, Clear sends `''`).
   - _Commands:_ `npm run format`, `npm run check`.
10. **I10 – Docs and drift gate**
    - _Steps:_ knowledge map, roadmap, drift report, manual S-074-18 and S-074-19 in both trees.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-074-01 | I2 / T-074-04 | `GalleryPasswordTest` |
| S-074-02 | I2 / T-074-04, T-074-05 | v2 and v3 endpoints in `GalleryPasswordTest` (Feature_v3) |
| S-074-03 | I3 / T-074-08 | |
| S-074-04 | I3 / T-074-08 | |
| S-074-05 | I3 / T-074-08 | |
| S-074-06 | I3 / T-074-08 | |
| S-074-07 | I2 / T-074-04 | |
| S-074-08 | I2 / T-074-04 | |
| S-074-09 | I3 / T-074-08 | |
| S-074-10 | I3 / T-074-08 | |
| S-074-11 | I2 / T-074-04, T-074-07 | RSS and embeds disabled |
| S-074-12 | I5 / T-074-13 | `GalleryPasswordWebTest` |
| S-074-13 | I2 / T-074-04 | |
| S-074-14 | I4 / T-074-10 | `PasswordConfigTypeTest` |
| S-074-15 | I4 / T-074-10 | |
| S-074-16 | I2 / T-074-04 | |
| S-074-17 | I3 / T-074-08 | |
| S-074-20 | I3 / T-074-08 | cookie lifetime |
| S-074-21 | I2 / T-074-04 | no bcrypt on gated requests (NFR-074-06) |
| S-074-18 | I7, I8, I10 / T-074-17, T-074-18, T-074-19, T-074-23 | manual, v7 and v8 |
| S-074-19 | I9, I10 / T-074-20, T-074-21, T-074-23 | manual, v7 and v8 |

## Analysis Gate
Result: **pass**.
1. Spec completeness: pass. FR, NFR and UI mock-ups (UI-074-01..03) are present, and Q-074-01..03 are encoded in the normative sections.
2. Open questions: pass. No `Open` rows. ADR-074-01 and ADR-074-02 are recorded and linked.
3. Plan alignment: pass.
4. Task coverage: pass. FR-074-01→T-02, 02→T-01/03/06, 03→T-06, 04/05→T-07, 06→T-14, 07/08→T-09, 14/15→T-02/09, 09→T-17/18/19, 10→T-11, 11→T-12, 12→T-20/21, NFR-06→T-01/04, 13→T-04 (S-074-16/17). Each increment stages failing tests first.
5. Working agreements: pass. The predicate is extracted (NFR-074-04). No new dependencies. v7 and v8 are both in scope, so the shared frontend modules are edited in place.
6. Tooling: pass. Commands are listed per increment.

## Exit Criteria
- All tasks `[x]`, and the scoped tests listed above are green.
- `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format` and `npm run check` are clean.
- `php artisan typescript:transform` has been rerun.
- Roadmap and knowledge map are updated, and the drift gate report is recorded here.

## Follow-ups / Backlog
- Possible later feature: let `SecurePathController` honour the gallery lock (Q-074-02, Option B, not chosen).
- `login_required` covers only a subset of API routes (v3 listings are not gated by it). This was noticed during planning and is out of scope here (NG6), but is worth its own feature.
