# Open Questions – Feature 107

Open questions for [Feature 107](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

Context (2026-11-04): `laragear/webauthn` (v5.0.2 in `composer.lock`) is archived and read-only; Packagist marks it abandoned with `laravel/passkeys` as replacement (`composer audit`: no advisory, 1 abandoned package). Lychee uses it in `WebAuthnLoginController`, `WebAuthnRegisterController`, `WebAuthnManageController`, `EditCredentialRequest`, `WebAuthnResource`, `User` (`WebAuthnAuthenticatable`, `WebAuthnAuthentication`, `webAuthnData()`), the `eloquent-webauthn` auth driver (`config/auth.php`), `config/webauthn.php`, the `webauthn_credentials` table, `tests/Feature_v2/WebAuthTest.php` and the vendored front-end helper `resources/js/vendor/webauthn/webauthn.ts`.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-107-01~~ | 107 | High | Replacement library: A `web-auth/webauthn-lib` with a Lychee-owned ceremony service, B `laravel/passkeys` (v0.2.x) behind Lychee controllers, C fork `laragear/webauthn` into LycheeOrg | Resolved (A, FR-107-01 … 07, FR-107-11, NFR-107-01/02, ADR-107-01) | 2026-11-04 | 2026-11-04 |
| ~~Q-107-02~~ | 107 | High | Existing credentials: A convert in a migration (decrypt PEM → COSE credential record), B drop them and ask users to register again | Resolved (A, FR-107-08/09/12, ADR-107-02) | 2026-11-04 | 2026-11-04 |
| ~~Q-107-03~~ | 107 | Medium | Authenticator policy: A keep today's (user verification discouraged at registration, preferred at login, discoverable preferred), B passkey policy (verification and discoverable required) | Resolved (A, FR-107-01/04/05, NFR-107-03, ADR-107-01) | 2026-11-04 | 2026-11-04 |

## Question Details

### ~~Q-107-01~~ · Which library replaces `laragear/webauthn`

**Status:** Resolved (A, owner, 2026-11-04): `web-auth/webauthn-lib` ^5.3 behind Lychee-owned services; the owner's answer also approves the packages listed under Option A (FR-107-01 … 07, FR-107-11, NFR-107-01/02, ADR-107-01).
**Preferred option:** A – `web-auth/webauthn-lib` with a Lychee-owned ceremony service

**Question**
Laragear implements WebAuthn itself (its own CBOR, COSE and signature checks), so nobody fixes its cryptographic code from now on. Which maintained code takes over the registration and login ceremonies?

**Option A (recommended) – `web-auth/webauthn-lib` directly**
- Idea: require `web-auth/webauthn-lib` ^5.3 (Spomky-Labs, FIDO2 library, 5.3.12 released 2026-11-03). A small `App\Services\WebAuthn` builds the creation/request options, keeps them in the session and validates responses with `AuthenticatorAttestationResponseValidator` / `AuthenticatorAssertionResponseValidator`. Lychee keeps its routes (`WebAuthn::register/options`, `WebAuthn::register`, `WebAuthn::login/options`, `WebAuthn::login`, `GET|PATCH|DELETE /WebAuthn`), its own credential model and table.
- Pros: the maintained, widely used PHP implementation (it is what `laravel/passkeys` wraps); stable 5.x API; Lychee keeps control over username/email login (Feature 091), the verification policy (Q-107-03) and the API shape, so the v7/v8 front end barely changes.
- Cons: Lychee owns the ceremony glue (≈300–400 lines plus tests); new packages: `web-auth/webauthn-lib`, `web-auth/cose-lib`, `spomky-labs/cbor-php`, `spomky-labs/pki-framework`, `symfony/serializer`, `symfony/property-info`, `symfony/property-access` (`symfony/uid`, `symfony/clock`, `psr/event-dispatcher` already present).

**Option B – `laravel/passkeys` behind Lychee controllers**
- Idea: require `laravel/passkeys` (v0.2.1, 2026-05-18, pinned to `web-auth/webauthn-lib` 5.3.x), call `Passkeys::ignoreRoutes()` and call its actions (`GenerateRegistrationOptions`, `StorePasskey`, `GenerateVerificationOptions`, `VerifyPasskey`, `DeletePasskey`) from the existing Lychee controllers; its `passkeys` table replaces `webauthn_credentials`.
- Pros: official Laravel package, the replacement Laragear points to; authenticator names from AAGUIDs; same underlying library as A.
- Cons: pre-1.0 (API may change between minors); its actions hard-code user verification and discoverable credentials as required (Q-107-03 B unless Lychee subclasses them); `GenerateVerificationOptions` only narrows by a logged-in user, so username-first login needs our own code anyway; same new packages as A plus `laravel/passkeys`, and an `@laravel/passkeys` npm package if its front end is used.

**Option C – fork `laragear/webauthn` into LycheeOrg**
- Idea: copy the package into a LycheeOrg repository (or `app/`) and maintain it.
- Pros: no code change in Lychee, no new dependency, no data migration.
- Cons: LycheeOrg then owns a hand-written WebAuthn/CBOR/COSE implementation, which is the risk raised in the first place; no upstream security fixes.

### ~~Q-107-02~~ · What happens to the credentials users already registered

**Status:** Resolved (A, owner, 2026-11-04): converted in place by migration (FR-107-08/09/12, ADR-107-02).
**Preferred option:** A – convert them in a migration

**Question**
`webauthn_credentials` stores each public key as PEM, encrypted with `APP_KEY` (`'public_key' => 'encrypted'` cast), plus counter, transports, AAGUID and the user handle (`user_id` UUID). `web-auth/webauthn-lib` needs a COSE-encoded key in a `CredentialRecord`. Do registered keys survive the switch?

**Option A (recommended) – convert in a migration**
- Idea: a migration decrypts each `public_key`, turns the PEM into a COSE key (Laragear only ever accepted ES256 on P-256, RS256 and Ed25519 with `attestation: none`, so key type → algorithm is unambiguous), and writes a credential record with the same credential id, user handle, counter, transports, AAGUID and alias. Rows that fail to decrypt (rotated `APP_KEY`) or parse are skipped, logged and reported by a diagnostics check.
- Pros: nobody re-registers; instances running with `DISABLE_BASIC_AUTH` and WebAuthn-only admins are not locked out (`AuthDisabledCheck` scenario).
- Cons: conversion code for three key types with fixtures for each; depends on the current `APP_KEY` to decrypt.

**Option B – drop them and ask users to register again**
- Idea: drop the table, show a notice in the profile that keys must be added again.
- Pros: no conversion code.
- Cons: locks out every user whose only sign-in is a security key, including admins on instances with basic auth disabled; recovery then needs shell access (`lychee:reset_admin`).

### ~~Q-107-03~~ · Which authenticator policy the new code applies

**Status:** Resolved (A, owner, 2026-11-04): today's policy is kept (FR-107-01/04/05, NFR-107-03, ADR-107-01).
**Preferred option:** A – keep today's policy

**Question**
Today registration uses `fastRegistration()` (user verification `discouraged`, discoverable credential `preferred`) and login leaves user verification unset (browser default `preferred`), so U2F-only keys (YubiKey 4, keys without a PIN) work and login can be username-first or usernameless. `laravel/passkeys` requires user verification and discoverable credentials. Does the new code change this?

**Option A (recommended) – keep today's policy**
- Idea: registration `userVerification: discouraged`, `residentKey: preferred`; login `userVerification: preferred`; `allowCredentials` filled when a username/email or `user_id` is given, empty otherwise.
- Pros: the switch is behaviour-neutral and testable against today's `WebAuthTest`; existing keys keep working; a stricter policy can follow as its own change.
- Cons: a stolen security key without a PIN still signs in as its owner (same as today).

**Option B – passkey policy**
- Idea: user verification `required` at registration and login, discoverable credential `required`.
- Pros: every sign-in proves PIN or biometrics; matches `laravel/passkeys` defaults.
- Cons: U2F-only keys and keys without a PIN stop working, including converted ones (Q-107-02); behaviour change bundled into a dependency swap.
