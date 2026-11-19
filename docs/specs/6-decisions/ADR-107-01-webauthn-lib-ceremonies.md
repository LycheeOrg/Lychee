# ADR-107-01: WebAuthn ceremonies on `web-auth/webauthn-lib` behind Lychee code

- **Status:** Accepted
- **Date:** 2026-11-04
- **Related features/specs:** Feature 107 (docs/specs/4-architecture/features/107-webauthn-replacement/spec.md)
- **Related open questions:** Q-107-01, Q-107-03

## Context

Lychee's security key login and registration ran on `laragear/webauthn` (v5.0.2). The repository is archived and read-only, and Packagist marks the package abandoned in favour of `laravel/passkeys`. Laragear implements WebAuthn itself: CBOR decoding, COSE key parsing, authenticator data parsing and signature verification. With no maintainer left, a flaw in any of these stays unfixed. That is a risk for an authentication path that can be the only way into an instance (`DISABLE_BASIC_AUTH`).

Constraints:
- Lychee exposes its own routes (`/api/v2/WebAuthn::register/options`, `::register`, `::login/options`, `::login`, `GET|PATCH|DELETE /WebAuthn`) used by the v7 and v8 front ends.
- Login may be username-first, by email when `email_login_enabled` is on (Feature 091), or usernameless.
- Existing users include U2F-only keys (no PIN, no resident key), so the authenticator policy must not change (Q-107-03 A).

## Decision

Require `web-auth/webauthn-lib` ^5.3 and keep the ceremony glue in Lychee:
- `App\Services\WebAuthn\WebAuthnFactory` builds the serializer, the attestation statement support manager (`none`, `fido-u2f`, `packed`, `tpm`, `android-key`, `apple`), the algorithm manager (ES256, RS256, EdDSA) and the two response validators. The origin rule is the library's allowed-origins check with an empty list (HTTPS on the relying party id or a subdomain, as Laragear did); on `localhost` the list holds the `APP_URL` origin so local installs work over HTTP.
- `App\Services\WebAuthn\CeremonyStore` keeps the serialised options and an expiry in the session, single use.
- The existing controllers keep their routes and response shapes and call these services. `App\Models\WebAuthnCredential` replaces Laragear's model on the same table (ADR-107-02).
- Policy (Q-107-03 A): registration `userVerification: discouraged`, `residentKey: preferred`, `attestation: none`; login `userVerification: preferred`; `allowCredentials` when an identifier resolves to a user.
- `laragear/webauthn` and `laragear/meta-model` are removed.

## Consequences

### Positive
- All parsing and signature checks run in a maintained library that implements the W3C WebAuthn Level 3 verification steps.
- Lychee keeps control over the API, the identifier resolution of Feature 091 and the policy, so the front end only changes its outgoing encoding.
- One library underneath, the same one `laravel/passkeys` uses, so moving to that package later stays possible.

### Negative
- Lychee owns about 300–400 lines of ceremony code and their tests.
- Seven new packages: `web-auth/webauthn-lib`, `web-auth/cose-lib`, `spomky-labs/cbor-php`, `spomky-labs/pki-framework`, `symfony/serializer`, `symfony/property-info`, `symfony/property-access`.

## Alternatives Considered

- **B – `laravel/passkeys` (v0.2.1):** official, but pre-1.0. Its actions hard-code required user verification and discoverable credentials, and it has no username-first options, so Lychee would override most of it. Same packages plus itself.
- **C – fork Laragear:** no code change, but LycheeOrg would maintain a hand-written WebAuthn implementation, which is the risk this decision removes.

## Security / Privacy Impact

- Signature, challenge, origin, relying party, user handle, allowed-credential and counter checks move to the library. Behaviour is kept, except that every check now follows the specification's ordering.
- The user handle stays opaque (random bytes, or the converted Laragear handle), with no personal data in it.
- Challenges are single use and expire after `webauthn.challenge.timeout` seconds.

## Operational Impact

- No new configuration. `WEBAUTHN_ID` and `WEBAUTHN_NAME` keep their meaning, and `APP_URL` still decides the relying party when `WEBAUTHN_ID` is empty.
- Dependabot now tracks `web-auth/*` and `spomky-labs/*`.

## Links

- Related spec sections: `docs/specs/4-architecture/features/107-webauthn-replacement/spec.md` FR-107-01 … 07, FR-107-10, FR-107-11, NFR-107-01 … 03
- Related ADRs: ADR-107-02
- Upstream: https://github.com/web-auth/webauthn-lib, https://github.com/Laragear/WebAuthn (archived)
