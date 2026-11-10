# Feature Plan 107 – WebAuthn Library Replacement

_Linked specification:_ [spec.md](spec.md)
_Linked tasks:_ [tasks.md](tasks.md)
_Status:_ Implemented (CI on MySQL/PostgreSQL pending)
_Last updated:_ 2026-11-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Security keys keep working exactly as today, but every WebAuthn check runs in a maintained library. Success:
- `composer audit` lists no abandoned package; no `laragear/*` in `composer.lock` (FR-107-11, NFR-107-02).
- Every scenario of today's `WebAuthTest` keeps its outcome (NFR-107-03), plus the new scenarios S-107-04 (replay), S-107-15/16 (user handle, allowed list).
- A key registered on `master` signs in after the upgrade (S-107-22, S-107-27).

## Scope Alignment
- **In scope:** FR-107-01 … 12: dependencies, migrations, model, ceremony services, the four ceremony endpoints, management endpoints on the new model, front-end encoding, Laragear removal, diagnostics.
- **Out of scope:** N-107-01 … 06 (policy change, attestation trust, new UI, `laravel/passkeys`, registration rights, native JSON helpers).

## Dependencies & Interfaces
- New (approved 2026-11-04, Q-107-01 A): `web-auth/webauthn-lib` ^5.3, which brings `web-auth/cose-lib`, `spomky-labs/cbor-php`, `spomky-labs/pki-framework`, `symfony/serializer`, `symfony/property-info`, `symfony/property-access`. `composer require` (T-107-01, 2026-11-04) added exactly: `web-auth/webauthn-lib` 5.3.12, `web-auth/cose-lib` 4.8.2, `spomky-labs/cbor-php` 3.4.2, `spomky-labs/pki-framework` 1.6.3, `symfony/serializer` v8.1.10, `symfony/property-info` v8.1.10, `symfony/property-access` v8.1.10, and `symfony/type-info` v8.1.10 (required by `symfony/property-info` 8.x, the only package beyond the approved list). No existing package changed version.
- Removed: `laragear/webauthn`, `laragear/meta-model`.
- Feature 091 (`WebAuthnLoginController::resolveIdentifier()`, FR-091-14) keeps its behaviour.
- Feature 074 (`gallery_password`): the login routes stay outside the middleware.
- Feature 102 (`useClearViewerState` after WebAuthn login in `WebauthnModal`): unchanged.
- `ext-sodium` decides whether EdDSA is offered (FR-107-01).

## Assumptions & Risks
- **Assumptions:** The recorded fixtures in `WebAuthTest` (origin `https://localhost`, `none` attestation, ES256 key) are valid WebAuthn data and verify under `web-auth/webauthn-lib` once re-encoded as base64url.
- **Risks / Mitigations:**
  - *Wrong user handle after conversion → discoverable logins fail.* S-107-23 unit test, S-107-27 browser upgrade test with a resident key.
  - *PEM ↔ COSE bug locks users out.* S-107-20 signature test per key type and round trip, S-107-22 end-to-end on the recorded fixture.
  - *Browser sends an attestation format other than `none`.* Six formats accepted (FR-107-03), checked in S-107-25/26.
  - *Migration on PostgreSQL/MySQL (column change, `uuid` → string).* Laravel's native `change()`, rows copied in PHP; CI runs all three drivers.
  - *Rotated `APP_KEY`.* Row left untouched, warning, diagnostics (FR-107-09, FR-107-12).
  - *The table switch cannot be split.* Once the schema migration exists, Laragear code no longer works, so I2–I5 form one commit slice; the build is only green again at the end of I5.

## Implementation Drift Gate
Run once T-107-01 … T-107-14 are checked: map each FR/NFR to its class and test in a table below, rerun the quality gate (php-cs-fixer, scoped tests, phpstan, scoped `vp check`, `vp run typecheck`), and record the browser results of S-107-25 … 27 with the script paths in the scratchpad.

_Report (2026-11-04, agent self-review):_

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-107-01, FR-107-02 | `Actions\WebAuthn\GenerateRegistrationOptions`, `User::webAuthnUserName()`, `RegisterOptionsRequest`, `WebAuthnRegisterController::options()` | `WebAuthTest::testWebAuthnRegisterOptions`, `…ReuseUserHandle`, `…OptionsUnauthorized`; `UserTest` (2 `webAuthnUserName` cases) |
| FR-107-03 | `Actions\WebAuthn\StoreCredential`, `RegisterCredentialRequest`, `WebAuthnFactory::attestationValidator()` | `WebAuthTest` register execution / expired / without challenge / replay / duplicate / wrong challenge / validation / unauthorized; `WebAuthnFactoryTest::testAcceptedAttestationFormats` |
| FR-107-04 | `Actions\WebAuthn\GenerateLoginOptions`, `LoginOptionsRequest`, `WebAuthnLoginController::options()` (+ `resolveIdentifier()`) | `WebAuthTest` login options ×7, `EmailLoginTest` |
| FR-107-05 | `Actions\WebAuthn\VerifyLogin`, `LoginCredentialRequest`, `WebAuthnLoginController::login()` | `WebAuthTest` login ×17 (recorded U2F-style, empty handle, malformed / foreign signature, challenge, replay, expiry, counter, discoverable right/wrong handle, not allowed, disabled, unknown, validation, after migration, unconverted) |
| FR-107-06 | `WebAuthnFactory::relyingPartyId()`, `allowedOrigins()` | `WebAuthnFactoryTest` (relying party ×2, allowed origins ×2, origin rule ×9) |
| FR-107-07 | `WebAuthnManageController`, `EditCredentialRequest`, `WebAuthnResource` on `App\Models\WebAuthnCredential` | `WebAuthTest` management ×7 |
| FR-107-08 | `2026_11_04_000001_webauthn_credentials_user_handle`, `App\Models\WebAuthnCredential` | `WebAuthTest::testWebAuthnRegisterExecution` (stored columns), `WebAuthnCredentialMigrationTest` |
| FR-107-09 | `2026_11_04_000002_convert_webauthn_public_keys` | `WebAuthnKeyConversionTest` (10), `WebAuthnCredentialMigrationTest` (4), `WebAuthTest::testWebAuthLoginAfterMigration`, `…UnconvertedCredential`; browser S-107-27 |
| FR-107-10 | `resources/js/vendor/webauthn/base64url.ts`, `webauthn.ts` | `base64url.test.ts` (5); browser payload checks |
| FR-107-11 | `composer.json`/`composer.lock` (no `laragear/*`), `config/auth.php` `eloquent`, `User` | `grep -rn "Laragear\\" app config routes database tests` empty, `composer audit` without abandoned packages |
| FR-107-12 | `Actions\Diagnostics\Pipes\Checks\WebAuthnCredentialsCheck` (Errors pipe) | `WebAuthnCredentialsCheckTest` (4), `DiagnosticsTest`, `ErrorsTest` |
| NFR-107-01 | No `openssl_verify`/`sodium_crypto_sign_verify` in `app/`; PEM ↔ COSE only in the data migration | `WebAuthnKeyConversionTest` signature + round-trip cases |
| NFR-107-02 | Lock diff recorded under Dependencies (8 packages, `symfony/type-info` the only one beyond the approved list) | `composer audit` |
| NFR-107-03 | Same outcomes as Laragear's tests except the documented guest `401` | `WebAuthTest` (41) |
| NFR-107-04 | No logger set on the validators; migration warning lists credential ids only; `CeremonyStore` single use + expiry | `CeremonyStoreTest` (6), `WebAuthnCredentialMigrationTest::testRowUnderAnotherKeyIsLeftAndReported` |
| NFR-107-05 | Laravel schema builder only, rows copied in PHP | SQLite locally (tests + scratch upgrade/downgrade); MySQL/PostgreSQL through CI (pending) |

Quality gate (2026-11-04): `vendor/bin/php-cs-fixer fix` (0 left), `make phpstan` (no errors), scoped `vp check --fix` on the three webauthn files, `vp run typecheck`, `vp test run resources/js/vendor/webauthn` (5), `php artisan typescript:transform` (no change to `lychee.d.ts`). PHP test classes, run one at a time: WebAuthTest 41, WebAuthnCredentialMigrationTest 4, WebAuthnKeyConversionTest 10, CeremonyStoreTest 6, WebAuthnFactoryTest 17, WebAuthnCredentialsCheckTest 4, UserTest 40, AuthDisabledCheckTest 11, EmailLoginTest 21, AuthTest 52, LdapAuthTest 7, AuthControllerTest 15, GalleryPasswordTest 22, DiagnosticsTest 1, ErrorsTest 1, RegisterTest 9, ProfileTest 10, Create/Delete/Edit/Invite/ListUserTest 11, UsersTest 4: all green.

Browser (I7, Chromium with the CDP virtual authenticator; scripts in the scratchpad `pw/webauthn.cjs`, `env.sh`, `serve.sh`; SQLite DB in the scratchpad, never the dev DB):
- `master` worktree (Laragear) on :8124: admin registers a CTAP2 resident key with user verification, bob a CTAP1/U2F key; both log in (baseline). The stored `user_id` is the plain hex UUID and the authenticator holds 24 bytes whose base64url is that string: ADR-107-02's assumption checked on real data.
- This branch's migrations on the same DB: both rows converted (COSE, `user_handle` = hex). `down()` of both migrations (called directly; `migrate:rollback` is prohibited by Lychee) restores `user_id` and the decrypted PEM byte for byte, and `master` logs in again with all three cases. Migrations re-applied.
- S-107-27 on :8123 (v8): admin without username (discoverable, converted handle checked) and with username, bob username-first (U2F): 204 each, payloads unpadded base64url.
- S-107-25/26 on :8123 (v8) and :8125 (v7): new registrations (resident and U2F), logins with and without username: all 204, registration and login payloads base64url.

Drift: guest `401` instead of `403` on the two registration endpoints and `requireResidentKey` left out (means false) are written into FR-107-01/03, S-107-02/05 and NFR-107-03. `setSecuredRelyingPartyId()` is deprecated in the library, so the `localhost` HTTP rule goes through an allowed origin (FR-107-06 updated). User/auth-config cleanup moved from T-107-13 to T-107-05 (Laragear's provider needs its interface). Diagnostics test lives in `tests/Feature_v2/Diagnostics/` (needs the database). No high- or medium-impact divergence; no new open question.

Seen, not changed: v8 calls `GET /api/v3/Albums/root/rights?scope=own` as a guest after logout and gets `422` ("The selected scope is invalid."), identical on `master`.

## Increment Map

1. **I1 – Library and ceremony services** (commit slice 1)
   - _Goal:_ `web-auth/webauthn-lib` installed; `WebAuthnFactory`, `CeremonyStore` and the test helper `SoftAuthenticator` exist and are unit-tested. Nothing is wired in yet, so the build stays green.
   - _Preconditions:_ Spec, ADR-107-01/02, analysis gate.
   - _Steps:_ `composer require web-auth/webauthn-lib:^5.3` and record the lock diff; tests first: `CeremonyStoreTest` (put/pull, single use, expiry, separate attestation/assertion slots), `WebAuthnFactoryTest` (relying party id from `WEBAUTHN_ID` / `APP_URL`, EdDSA present with sodium, attestation formats); then implement.
   - _Commands:_ `php artisan test --filter=CeremonyStoreTest`, `php artisan test --filter=WebAuthnFactoryTest`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ Tests green, phpstan clean.
2. **I2 – Migrations and model** (slice 2 begins)
   - _Goal:_ FR-107-08/09: schema migration, data migration with PEM ↔ COSE, `App\Models\WebAuthnCredential`, `User::webAuthnCredentials()` on it.
   - _Steps:_ Tests first: `WebAuthnKeyConversionTest` (Unit, S-107-20, S-107-23), `WebAuthnCredentialMigrationTest` (Feature_v2, S-107-21, S-107-22 storage part); then the two migrations and the model.
   - _Commands:_ `php artisan test --filter=WebAuthnKeyConversionTest`, `php artisan test --filter=WebAuthnCredentialMigrationTest`.
   - _Exit:_ Conversion tests green. `WebAuthTest` is red until I4 (expected, see risks).
3. **I3 – Registration on the new library**
   - _Goal:_ FR-107-01/02/03/06.
   - _Steps:_ Rewrite the registration cases of `WebAuthTest` (S-107-01 … 06) on `CeremonyStore` and base64url fixtures; `RegisterOptionsRequest`, `RegisterCredentialRequest`; `App\Actions\WebAuthn\GenerateRegistrationOptions`, `StoreCredential`; `WebAuthnRegisterController` on them; `User::webAuthnUserName()`.
   - _Commands:_ `php artisan test --filter=WebAuthTest`.
   - _Exit:_ Registration cases green.
4. **I4 – Login and management on the new library**
   - _Goal:_ FR-107-04/05/07.
   - _Steps:_ Rewrite the login cases (S-107-07 … 14, 17, 18, 22) and add S-107-15/16 with `SoftAuthenticator`; `LoginOptionsRequest`, `LoginCredentialRequest`; `GenerateLoginOptions`, `VerifyLogin`; `WebAuthnLoginController` on them (keeps `resolveIdentifier()`); `EditCredentialRequest`, `WebAuthnResource` on the new model (S-107-19).
   - _Commands:_ `php artisan test --filter=WebAuthTest`, `php artisan test --filter=EmailLoginTest`.
   - _Exit:_ `WebAuthTest` fully green.
5. **I5 – Front-end encoding** (slice 2 ends: commit)
   - _Goal:_ FR-107-10.
   - _Steps:_ Vitest for `bufferToBase64Url` (S-107-24) first; then `resources/js/vendor/webauthn/base64url.ts` and the helper's outgoing encoding.
   - _Commands:_ `vp test run resources/js/vendor/webauthn`, scoped `vp check --fix <files>` (not repo-wide: it breaks `EmbedCodeDialog.vue`), `vp run typecheck`.
   - _Exit:_ Vitest green, typecheck clean, the full slice-2 quality gate green.
6. **I6 – Remove Laragear, diagnostics** (commit slice 3)
   - _Goal:_ FR-107-11/12.
   - _Steps:_ `WebAuthnCredentialsCheckTest` first; `WebAuthnCredentialsCheck`; `composer remove laragear/webauthn`; `config/auth.php` → `eloquent`; `User` cleanup (`WebAuthnAuthentication`, `webAuthnData()`), `UserTest` on `webAuthnUserName()`; `grep -rn Laragear app config database tests` empty; `php artisan typescript:transform` unchanged.
   - _Commands:_ `php artisan test --filter=WebAuthnCredentialsCheckTest`, `--filter=UserTest`, `--filter=AuthDisabledCheckTest`, `--filter=WebAuthTest`, `--filter=DiagnosticsTest`, `composer audit`, `make phpstan`.
   - _Exit:_ No Laragear left; tests green.
7. **I7 – Browser verification**
   - _Goal:_ S-107-25 … 27 on a scratch instance with Playwright and Chrome's CDP virtual authenticator (`WebAuthn.addVirtualAuthenticator`, CTAP2 internal with resident key and user verification; CTAP1/U2F without either).
   - _Steps:_ v8 and v7: profile → add key → logout → login modal with and without username; upgrade path: key registered on a `master` worktree build, credential exported with `WebAuthn.getCredentials`, migrations run on this branch, credential re-added with `WebAuthn.addCredential`, login.
   - _Exit:_ All three scenarios pass, script paths recorded below.
8. **I8 – Documentation and drift gate** (commit slice 4)
   - _Goal:_ Knowledge map, roadmap, drift gate report, `_current-session.md`.

9. **I9 – PR #4957 review** (commit slice 5)
   - _Goal:_ Fixes for the accepted CodeRabbit findings (see "PR #4957 review" below): registration options tied to the requesting user, expiry to the microsecond, login field length limits, "delete, then register again" wording, resumable schema migration with batched reads, full-output encoder test, ADR-107-02 wording.
   - _Steps:_ Tests first per task (T-107-16 … 21), then code; spec FR-107-01/03/05/09/12, NFR-107-04, S-107-23/28/29 updated before the code.
   - _Commands:_ `php artisan test --filter=` for `CeremonyStoreTest`, `WebAuthTest`, `WebAuthnCredentialsCheckTest`, `WebAuthnCredentialMigrationTest`, `WebAuthnKeyConversionTest`; `vp test run resources/js/vendor/webauthn/base64url.test.ts`; `make phpstan`; `vendor/bin/php-cs-fixer fix`; scoped `vp check --fix`; `vp run typecheck`.
   - _Exit:_ Tests green, phpstan clean.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-107-01 … 06 | I3 / T-107-06, T-107-07 | `WebAuthTest` |
| S-107-07 … 14, 17, 18 | I4 / T-107-08, T-107-09 | `WebAuthTest` |
| S-107-15, 16 | I4 / T-107-08 | `WebAuthTest` with `SoftAuthenticator` |
| S-107-19 | I4 / T-107-09 | `WebAuthTest` manage cases |
| S-107-20, 23 | I2 / T-107-04 | `WebAuthnKeyConversionTest` |
| S-107-21 | I2 / T-107-04, I6 / T-107-12 | `WebAuthnCredentialMigrationTest`, `WebAuthnCredentialsCheckTest` |
| S-107-22 | I2 / T-107-04, I4 / T-107-08 | migration + login |
| S-107-24 | I5 / T-107-10 | Vitest |
| S-107-25 … 27 | I7 / T-107-14 | Playwright, scratchpad |
| S-107-23 (migration run) | I9 / T-107-20 | `WebAuthnCredentialMigrationTest` (`down()`/`up()` on the table, resumed run) |
| S-107-28 | I9 / T-107-16 | `CeremonyStoreTest`, `WebAuthTest` |
| S-107-29 | I9 / T-107-18 | `WebAuthTest` |

## Analysis Gate
2026-11-04, agent self-review against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md):

1. Specification completeness — pass. FR-107-01 … 12, NFR-107-01 … 05; Q-107-01/02/03 folded into FR-107-01/03/04/05/08/09/11 and NFR-107-01/03; no UI change (stated in the mock-up section).
2. Open questions — pass. Q-107-01 … 03 resolved (A, A, A); ADR-107-01 (Q-107-01, Q-107-03) and ADR-107-02 (Q-107-02) written and linked.
3. Plan alignment — pass. The plan links spec and tasks; the dependency list matches NFR-107-02.
4. Tasks coverage — pass. Every FR maps to a task (FR-107-01/02/03/06 → T-107-06/07, FR-107-04/05 → T-107-08/09, FR-107-07 → T-107-09, FR-107-08/09 → T-107-04/05, FR-107-10 → T-107-10/11, FR-107-11 → T-107-13, FR-107-12 → T-107-12); each implementation task follows its test task; failure branches listed (S-107-04, 12 … 18, 21).
5. Working agreements — pass. Spec first, dependency approval recorded, tests before code. Validation lives in request classes and pure helpers (`toUserHandle`, `pemToCose`, `coseToPem`). Only ADR-107-01/02 reference Feature 107.
6. Tooling — pass. Commands listed per increment; the scoped `vp check` constraint is noted (memory: repo-wide autofix breaks `EmbedCodeDialog.vue`).

Follow-ups from the gate: none.

## Exit Criteria
- T-107-01 … T-107-22 checked.
- Quality gate: `vendor/bin/php-cs-fixer fix`, scoped `vp check --fix`, `vp run typecheck`, `php artisan test --filter=` for `WebAuthTest`, `WebAuthnKeyConversionTest`, `WebAuthnCredentialMigrationTest`, `CeremonyStoreTest`, `WebAuthnFactoryTest`, `WebAuthnCredentialsCheckTest`, `UserTest`, `AuthDisabledCheckTest`, `EmailLoginTest`, `DiagnosticsTest`; `make phpstan`; `composer audit`.
- `php artisan typescript:transform` leaves `resources/js/lychee.d.ts` unchanged.
- Roadmap and knowledge map updated; drift gate report filled in.

## Follow-ups / Backlog
- Registration of a key by accounts without `may_edit_own_settings` (N-107-05): today any signed-in user may add a key. Decide separately whether `RegisterOptionsRequest` should check `UserPolicy::CAN_EDIT` like edit/delete do.
- Stricter policy (Q-107-03 B) as its own feature once key-only users are known to have PIN-capable keys.
- Native `PublicKeyCredential.parseCreationOptionsFromJSON()` / `toJSON()` instead of the vendored helper once Safari support is broad enough (N-107-06).

## Follow-ups from implementation
- PostgreSQL/MySQL run of `2026_11_04_000001` (`uuid` → string `change()`, `dropColumn`) through CI. First CI run (1a00d4601): PostgreSQL failed only in `testUserHandleBecomesUserIdOnRollback`, which compared the raw `user_id`. PostgreSQL returns its native `uuid` dashed, so the test now compares without dashes. MariaDB was cancelled by fail-fast at 1159/1288 with no failure.
- Checked locally against throwaway containers (`postgres:18` on :55432, `mariadb:11` with the `mysql` driver on :53306, `vendor/bin/phpunit --configuration phpunit.ci.xml --no-coverage`): `WebAuthnCredentialMigrationTest` green on PostgreSQL, and `WebAuthnCredentialMigrationTest`, `WebAuthTest`, `WebAuthnCredentialsCheckTest` (54 tests) green on MariaDB. On MySQL/MariaDB the schema steps commit the test transaction early. The base class empties its fixture tables in `tearDown`, and `tearDown` re-runs `up()` if a test stopped after `down()`. The `mariadb` driver is not supported by Lychee (`2021_06_01_181900` throws), so MariaDB keeps `uuid` as `char(36)`.
- Owner check on a real security key and a platform passkey (Touch ID / Windows Hello / Android) after deploy.

## PR #4957 review

CodeRabbit review of 2026-11-04 (one review, 9 inline comments, 4 follow-ups, 2 nitpicks).

| Finding | Outcome | Where |
|---------|---------|-------|
| Bind pending registration options to the requesting account | Fixed | `CeremonyStore` stores the user id; T-107-16, S-107-28 |
| Whole-second challenge deadline | Fixed: integer microseconds from Carbon's clock | T-107-17 |
| Migration `2026_11_04_000001` not safe to rerun on MySQL | Fixed for `up()` and `down()` | T-107-20 |
| Both migrations load every row with `get()` | Fixed: `lazyById()` | T-107-20 |
| Diagnostics load every credential | Fixed: `lazyById()` | T-107-19 |
| No length limits on the guest login request | Fixed: 1364 / 86 / 16384 characters | T-107-18, S-107-29 |
| "Register again" omits the required deletion (registration excludes the stored id) | Fixed: login and diagnostics messages | T-107-19 |
| `tasks.md` T-107-06 said guest `403` | Fixed: `401` | – |
| ADR-107-02 "every row has the new format" | Fixed: only convertible rows | T-107-22 |
| Encoder test only checks length and alphabet | Fixed: full output against a `btoa` reference | T-107-21 |
| No test runs `2026_11_04_000001` `up()`/`down()` | Fixed | T-107-20, S-107-23 |
| Atomic challenge consumption (concurrent requests on one session) | Declined | Both racing requests must carry the same session cookie and the same signed assertion, and they only sign in that same session again. Whoever holds the cookie already holds the session, so the race grants nothing. Laragear used the same `Session::pull()` design. |
| Relying party id pinned to the `APP_URL` host when `WEBAUTHN_ID` is unset | Declined | Laragear left `rp.id` out of the options but checked the authenticator's RP id hash and the origin against the `APP_URL` host (`CheckRelyingPartyHashSame`, `CheckRelyingPartyIdContained`), so only that host ever worked. The new code fails the same requests earlier, in the browser instead of with a `422`. |
| `hasReadablePublicKey()` accepts a decryptable empty CBOR map | Declined | Only someone holding `APP_KEY` and write access to the table can store such a row, and its login still fails in `validate()`. A full COSE parse per row is not worth it for the diagnostics count. |

## Intent Log
- 2026-11-04: owner reported that `Laragear/WebAuthn` is deprecated and read-only. Findings: `composer audit` (abandoned, no advisory), `composer show laravel/passkeys` (v0.2.1, `web-auth/webauthn-lib` 5.3.x), sources of `laravel/passkeys-server`, `web-auth/webauthn-lib` 5.3.12 and `web-auth/cose-lib` cloned to the scratchpad for reading. Q-107-01 … 03 logged and answered "A A A" by the owner, which also approves the packages in NFR-107-02.
- 2026-11-04: implementation I1 … I8 in one session. `composer require web-auth/webauthn-lib:^5.3`, `composer remove laragear/webauthn`; tests written red first per task. Browser check against a `git worktree add --detach` of `master` in the scratchpad (`composer install --no-scripts`, `node_modules` symlinked, `vp build`), served with `php -S` on :8124 and this branch on :8123 (v8) / :8125 (v7).
- 2026-11-04: owner asked to check the CodeRabbit comments on PR #4957 (`gh pr view 4957`, `gh api repos/LycheeOrg/Lychee/pulls/4957/comments`). Laragear's relying-party handling read from a `master` worktree (`vendor/laragear/webauthn/src`). Outcomes in "PR #4957 review"; T-107-16 … 22 test-first.
