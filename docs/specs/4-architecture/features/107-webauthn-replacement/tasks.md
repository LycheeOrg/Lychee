# Feature 107 Tasks – WebAuthn Library Replacement

_Status: Complete_
_Last updated: 2026-11-04_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Library and ceremony services
- [x] T-107-01 – Require `web-auth/webauthn-lib` ^5.3 (NFR-107-02).
  _Intent:_ `composer require web-auth/webauthn-lib:^5.3`; list every package added to `composer.lock` in the plan's dependency section.
  _Verification commands:_
  - `composer show --locked | grep -E "web-auth|spomky|symfony/(serializer|property)"`
  - `composer audit` (only the Laragear abandonment left)

- [x] T-107-02 – Tests for `CeremonyStore` and `WebAuthnFactory` (FR-107-01, FR-107-03, FR-107-04, FR-107-06, S-107-04, S-107-14).
  _Intent:_ `tests/Unit/Services/WebAuthn/CeremonyStoreTest.php` (put → pull returns options, second pull null, expired → null, attestation and assertion slots separate); `WebAuthnFactoryTest` (relying party id from `webauthn.relying_party.id`, else `APP_URL` host; algorithm list −7, −257, −8; attestation formats). Red first.
  _Verification commands:_
  - `php artisan test --filter=CeremonyStoreTest`
  - `php artisan test --filter=WebAuthnFactoryTest`

- [x] T-107-03 – Implement `CeremonyStore`, `WebAuthnFactory`, test helper `SoftAuthenticator` (FR-107-01, FR-107-03, FR-107-06).
  _Intent:_ `app/Services/WebAuthn/CeremonyStore.php`, `app/Services/WebAuthn/WebAuthnFactory.php`, `tests/Support/WebAuthn/SoftAuthenticator.php` (EC P-256 key pair, COSE public key, signed assertions for given challenge, origin, user handle, counter, flags); `config/webauthn.php` challenge bytes 32.
  _Verification commands:_
  - `php artisan test --filter=CeremonyStoreTest`
  - `php artisan test --filter=WebAuthnFactoryTest`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

### I2 – Migrations and model
- [x] T-107-04 – Tests for the conversion (FR-107-08, FR-107-09, S-107-20, S-107-21, S-107-22, S-107-23).
  _Intent:_ `tests/Unit/Migrations/WebAuthnKeyConversionTest.php` (requires the data migration file: EC/RSA/Ed25519 PEM → COSE, a signature by the original private key verifies with the converted key through `web-auth/cose-lib`, COSE → PEM equals the input; requires the schema migration file: `toUserHandle()` with and without dashes, upper case). `tests/Feature_v2/WebAuthnCredentialMigrationTest.php` (Laragear-shaped row with encrypted PEM → encrypted COSE, second run unchanged, row encrypted with another key untouched, warning logged without key material). Red first.
  _Verification commands:_
  - `php artisan test --filter=WebAuthnKeyConversionTest`
  - `php artisan test --filter=WebAuthnCredentialMigrationTest`

- [x] T-107-05 – Migrations and `App\Models\WebAuthnCredential` (FR-107-08, FR-107-09).
  _Intent:_ `database/migrations/2026_11_04_000001_webauthn_credentials_user_handle.php` (add `user_handle`, `backup_eligible`, `backup_status`, `uv_initialized`, copy, not null, drop `user_id`; `down()` reverses); `2026_11_04_000002_convert_webauthn_public_keys.php` (PEM ↔ COSE); `app/Models/WebAuthnCredential.php` (`toCredentialRecord()`, `fillFromCredentialRecord()`, `authenticatable()`); `User::webAuthnCredentials()` on it; `RequiresEmptyWebAuthnCredentials` unchanged. Moved here from T-107-13 (Laragear's auth provider expects its interface on `User`): `User` without `WebAuthnAuthenticatable`/`WebAuthnAuthentication`/`webAuthnData()`, `User::webAuthnUserName()`, `config/auth.php` `eloquent`, `UserTest` on `webAuthnUserName()` (email, fallback `<name>#<host>`).
  _Verification commands:_
  - `php artisan test --filter=WebAuthnKeyConversionTest`
  - `php artisan test --filter=WebAuthnCredentialMigrationTest`
  - `php artisan test --filter=UserTest`
  - `make phpstan`

### I3 – Registration
- [x] T-107-06 – Rewrite the registration tests (FR-107-01, FR-107-02, FR-107-03, FR-107-06, S-107-01 … S-107-06).
  _Intent:_ `WebAuthTest`: options JSON (`authenticatorSelection` discouraged/preferred, `pubKeyCredParams`, `excludeCredentials`, `user`), guest `401`, recorded attestation as base64url → `204` and listed, expired / missing / reused options `422`, duplicate id `422`, second key reuses the handle. Red first.
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest`

- [x] T-107-07 – Registration on `web-auth/webauthn-lib` (FR-107-01, FR-107-02, FR-107-03).
  _Intent:_ `RegisterOptionsRequest`, `RegisterCredentialRequest`, `App\Actions\WebAuthn\GenerateRegistrationOptions`, `StoreCredential`, `WebAuthnRegisterController`, `User::webAuthnUserName()`; verification failures → `WebAuthnVerificationException` (`422`).
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest` (registration cases)
  - `make phpstan`

### I4 – Login and management
- [x] T-107-08 – Rewrite and extend the login tests (FR-107-04, FR-107-05, S-107-07 … S-107-18, S-107-22).
  _Intent:_ `WebAuthTest`: options with `user_id`, `username`, email on/off, anonymous (`allowCredentials` left out); recorded assertion → `204`; wrong signature, wrong challenge, replay, expired, disabled, unknown credential → `422` guest; discoverable assertion with the right/wrong user handle (`SoftAuthenticator`); credential outside `allowCredentials`; `DISABLE_WEBAUTHN` → `403`; the recorded key stored as a Laragear row, converted, then login `204`. Red first.
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest`

- [x] T-107-09 – Login and management on the new model (FR-107-04, FR-107-05, FR-107-07, S-107-19).
  _Intent:_ `LoginOptionsRequest`, `LoginCredentialRequest`, `App\Actions\WebAuthn\GenerateLoginOptions`, `VerifyLogin`, `WebAuthnLoginController` (keeps `resolveIdentifier()`), `EditCredentialRequest` and `WebAuthnResource` on `App\Models\WebAuthnCredential`, unconverted credential → "register again" `422`.
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest`
  - `php artisan test --filter=EmailLoginTest`
  - `php artisan test --filter=AuthTest`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

### I5 – Front-end encoding
- [x] T-107-10 – Vitest for the base64url encoder (FR-107-10, S-107-24).
  _Intent:_ `resources/js/vendor/webauthn/base64url.test.ts`: bytes with `+`/`/` positions → `-`/`_`, no `=`; empty buffer → `""`. Red first.
  _Verification commands:_
  - `vp test run resources/js/vendor/webauthn`

- [x] T-107-11 – Outgoing encoding in the vendored helper (FR-107-10).
  _Intent:_ `resources/js/vendor/webauthn/base64url.ts` (`bufferToBase64Url`), `webauthn.ts` `#parseOutgoingCredentials` on it, `userHandle` `null` when absent.
  _Verification commands:_
  - `vp test run resources/js/vendor/webauthn`
  - `vp check --fix resources/js/vendor/webauthn/base64url.ts resources/js/vendor/webauthn/base64url.test.ts resources/js/vendor/webauthn/webauthn.ts`
  - `vp run typecheck`

### I6 – Remove Laragear, diagnostics
- [x] T-107-12 – Diagnostics check for unusable credentials (FR-107-12, S-107-21).
  _Intent:_ `WebAuthnCredentialsCheckTest` first (no table → nothing; WebAuthn disabled → nothing; all readable → nothing; one row under another key → one warning with the count, no ids), then `App\Actions\Diagnostics\Pipes\Checks\WebAuthnCredentialsCheck` in the Errors pipe.
  _Verification commands:_
  - `php artisan test --filter=WebAuthnCredentialsCheckTest`
  - `php artisan test --filter=DiagnosticsTest`

- [x] T-107-13 – Remove `laragear/webauthn` (FR-107-11, NFR-107-01, NFR-107-02).
  _Intent:_ `composer remove laragear/webauthn` (takes `laragear/meta-model`); `User`/`config/auth.php` already done in T-107-05; `php artisan typescript:transform` leaves `lychee.d.ts` unchanged.
  _Verification commands:_
  - `grep -rn "Laragear" app config database tests` (empty)
  - `composer audit`
  - `php artisan test --filter=UserTest`
  - `php artisan test --filter=AuthDisabledCheckTest`
  - `php artisan test --filter=WebAuthTest`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

### I7 – Browser verification
- [x] T-107-14 – Playwright with the Chrome virtual authenticator (S-107-25, S-107-26, S-107-27).
  _Intent:_ Scratch instance; v8 and v7 register + login with and without username (CTAP2 internal, resident key, UV); CTAP1/U2F authenticator username-first; key registered on a `master` build, migrations, login on this branch.
  _Verification commands:_
  - `node <scratchpad>/pw/webauthn.cjs master-register | upgrade-login | master-login | fresh 8123 | fresh 8125` (servers via `<scratchpad>/serve.sh`)

### I8 – Documentation
- [x] T-107-15 – Roadmap, knowledge map, drift gate report, session snapshot.
  _Intent:_ Roadmap row 107 status; knowledge-map entry for the WebAuthn module; drift gate in the plan; `_current-session.md`.
  _Verification commands:_
  - Review of the plan's drift gate table.

### I9 – PR #4957 review
- [x] T-107-16 – Registration options belong to the requesting user (FR-107-03, NFR-107-04, S-107-28).
  _Intent:_ `CeremonyStore::putCreationOptions()` / `pullCreationOptions()` take the user id; a different id → "No pending WebAuthn challenge". `CeremonyStoreTest` + `WebAuthTest` case red first.
  _Verification commands:_
  - `php artisan test --filter=CeremonyStoreTest`
  - `php artisan test --filter=WebAuthTest`
- [x] T-107-17 – Challenge expiry to the microsecond (NFR-107-04, S-107-04, S-107-14).
  _Intent:_ expiry stored as integer microseconds from the Carbon clock (`travel()` still works). `CeremonyStoreTest`: valid just before the deadline, expired just after.
  _Verification commands:_
  - `php artisan test --filter=CeremonyStoreTest`
- [x] T-107-18 – Length limits on the login request (FR-107-05, S-107-29).
  _Intent:_ `LoginCredentialRequest` `max` rules; `WebAuthTest` oversize cases red first.
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest`
- [x] T-107-19 – "Delete, then register again" wording (FR-107-05, FR-107-12).
  _Intent:_ `VerifyLogin` and `WebAuthnCredentialsCheck` messages; diagnostics reads rows with `lazyById`.
  _Verification commands:_
  - `php artisan test --filter=WebAuthnCredentialsCheckTest`
  - `php artisan test --filter=WebAuthTest`
- [x] T-107-20 – Rerunnable schema migration, batched reads in both migrations (FR-107-09, S-107-23).
  _Intent:_ `2026_11_04_000001` skips steps already done (`up()` and `down()`); both migrations read with `lazyById`. `WebAuthnCredentialMigrationTest` runs `down()`/`up()` on a Laragear-shaped table and resumes a run cut short, red first.
  _Verification commands:_
  - `php artisan test --filter=WebAuthnCredentialMigrationTest`
  - `php artisan test --filter=WebAuthnKeyConversionTest`
- [x] T-107-21 – Encoder test compares the full output (S-107-24).
  _Intent:_ `base64url.test.ts` large buffer with varied bytes against a byte-by-byte `btoa` reference.
  _Verification commands:_
  - `vp test run resources/js/vendor/webauthn/base64url.test.ts`
- [x] T-107-22 – ADR-107-02 wording, plan intent log, quality gate.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  - `vp check --fix <changed files>`, `vp run typecheck`

## Notes / TODOs
- I2 … I5 form one commit slice: after the schema migration exists, the Laragear code cannot run, so `WebAuthTest` is red until T-107-09.
- `vp check --fix` must be scoped to the changed files (repo-wide autofix breaks `EmbedCodeDialog.vue`).
