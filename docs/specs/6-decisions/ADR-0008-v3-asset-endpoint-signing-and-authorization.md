# ADR-0008: Temporary-link signing and authorization model for the v3 asset endpoint

- **Status:** Accepted
- **Date:** 2026-08-20
- **Related features/specs:** Feature 056 (docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md)
- **Related open questions:** Q-056-01, Q-056-05

## Context

Lychee's only existing signed-link mechanism is Laravel's native `URL::temporarySignedRoute()` (`app/Services/UrlGenerator.php:71`), which HMAC-SHA256-signs the *entire canonical URL* and emits `expires`+`signature` query params, verified via `Illuminate\Http\Request::hasValidSignature()` (`app/Http/Controllers/SecurePathController.php`). That controller never checks `PhotoPolicy` at all — the opaque, optionally `Crypt`-encrypted path token *is* the access control; anyone holding a valid link URL can fetch the file, logged in or not.

Feature 056 introduces a v3 endpoint addressed by a plain `photo_id` + `size_variant` (not an opaque path), which is directly guessable/enumerable. The owner asked for this endpoint's temporary-link mode to carry a `timestamp` and "the MAC of the timestamp" specifically — not a MAC of the full request/resource, and confirmed the endpoint must always additionally check `PhotoPolicy`, with whether a signature is required at all depending on the same three config keys that already gate v2's signed-URL *generation*: `temporary_image_link_enabled`, `temporary_image_link_when_logged_in`, `temporary_image_link_when_admin` (`database/migrations/2025_04_05_153533_add_secure_link_options.php`).

## Decision

1. **MAC scope — timestamp only, not resource-scoped.** `mac = hash_hmac('sha256', (string) $timestamp, config('app.key'))`, hex-encoded, verified with `hash_equals()`. It authenticates *that a timestamp was minted by the server* (anti-tampering/anti-fabrication of the clock value), not a capability grant for a specific `photo_id`/`size_variant`. A new `App\Services\TemporaryLinkSigner` class owns `sign(int $timestamp): string` / `verify(int $timestamp, string $mac): bool`, keyed off `config('app.key')` — no new secret storage.
1a. **Transport — request headers, not query string.** `timestamp` and `mac` are carried as request headers, `X-Timestamp` and `X-Mac`, matching this codebase's existing single-word `X-<Name>` custom-header convention (`X-API-Key`, `app/Http/Requests/Face/FaceDetectionResultsRequest.php`). Amended same-day from an initial query-string design (Q-056-06) — keeping signature material out of the URL avoids it landing in server access-log request lines and `Referer` headers if this endpoint's URL is ever linked from another page.
2. **Expiry.** Reuses the existing `temporary_image_link_life_in_seconds` config (same TTL knob v2 already exposes): request rejected if `now() - $timestamp > $ttl`, or if `$timestamp` is in the future.
3. **PhotoPolicy is always evaluated, regardless of access mode.** This is the key security delta versus `SecurePathController`: the v3 endpoint never treats "the link is well-formed" as sufficient authorization on its own. `PhotoPolicy::CAN_SEE` gates thumbnail-class variants (`THUMB`, `THUMB2X`, `SMALL`, `SMALL2X`, `PLACEHOLDER`); `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` gates full-resolution variants (`MEDIUM`, `MEDIUM2X`, `ORIGINAL`, `RAW`) — evaluated against `Auth::user()` (nullable, i.e. guest-aware) exactly as `PhotoPolicy::canSee()`/`canAccessFullPhoto()` already support. Signature validation (steps 1/1a/2) runs **strictly before** `PhotoPolicy`; a request that fails the signature step never reaches the policy check.
3a. **401 vs. 403 cannot be derived from session state.** `BaseApiRequest::failedAuthorization()` (the inherited default every other `FormRequest` in this codebase relies on) throws `UnauthorizedException` (403) or `UnauthenticatedException` (401) based solely on `Auth::check()`. That default is wrong here: a guest with a *valid* signature but a policy-denied photo must get 403 (not 401 — they proved timing legitimacy, they're just not allowed to see this), while a *logged-in* caller whose config still requires a signature they didn't supply must get 401 (not 403 — they never cleared the access-proof step at all). `GetPhotoAssetRequest` therefore overrides `failedAuthorization()`, keyed on a `$signature_check_failed` flag set during `authorize()` (true iff step 1/1a/2 failed) rather than on `Auth::check()`.
4. **Whether a signature is *required* is derived from the existing three config keys, re-purposed from generation-time to validation-time**, via a new predicate that mirrors `UrlGenerator::shouldNotUseSignedUrl()`'s existing boolean composition (just evaluated against the incoming request's auth state instead of the outgoing link's target viewer):
   ```php
   function signatureRequired(?User $user, ConfigManager $cfg): bool {
       if (!$cfg->getValueAsBool('temporary_image_link_enabled')) {
           return false; // feature off entirely — session + PhotoPolicy is the only path
       }
       if ($user !== null && !$cfg->getValueAsBool('temporary_image_link_when_logged_in')) {
           return false; // logged-in caller's session is sufficient
       }
       if ($user?->may_administrate === true && !$cfg->getValueAsBool('temporary_image_link_when_admin')) {
           return false; // admin caller's session is sufficient
       }
       return true;
   }
   ```
   A **guest** request (`$user === null`) is therefore only ever admitted via a valid `timestamp`+`mac` when `temporary_image_link_enabled` is true (the `when_logged_in`/`when_admin` exemptions only ever apply to authenticated callers) — followed, unconditionally, by the same `PhotoPolicy` check, so a valid signature never grants access beyond what a guest could already see (e.g. a public album).
5. **Authenticated (session) requests never need `timestamp`/`mac` unless `signatureRequired()` says so for that caller** — the `api` middleware group's `StartSession`/`AuthenticateSession` (`app/Http/Kernel.php:68-79`) already makes `Auth::user()` available with no extra guard wiring.

## Consequences

### Positive
- Closes a real gap versus the v2 mechanism: `PhotoPolicy` is now always enforced for this endpoint, not implicitly delegated to "does the caller possess an opaque token."
- The MAC scheme is trivially reproducible by any client (server-language-agnostic `hash_hmac('sha256', timestamp, shared_secret)`), unlike Laravel's canonical-URL signature, which depends on exact query-param ordering/host reconstruction.
- Reuses the exact existing config keys and TTL — no new settings-page surface, no new migration.
- No new secret storage — reuses `APP_KEY`.

### Negative
- Because the MAC is timestamp-only (not resource-scoped), one valid `(timestamp, mac)` pair is valid for *any* `photo_id`/`size_variant` within the TTL window, not just the one it was originally minted for. This is an accepted trade-off (explicit owner instruction) — it is not a privilege-escalation risk on its own because `PhotoPolicy` is still evaluated per-request as a guest, so it only ever unlocks what an anonymous visitor could already see (e.g., a public album's photos); it does **not** unlock private/password-protected content. Documented here so the narrower scope is visible to reviewers, since it is a deliberate divergence from a per-resource capability token.
- `signatureRequired()`'s re-purposing of generation-time config semantics as validation-time semantics is a new, not-previously-existing code path — worth explicit test coverage per config combination (see spec Branch & Scenario Matrix).

## Alternatives Considered

- **A (chosen) — Bespoke timestamp-only HMAC + PhotoPolicy always enforced + config-driven signature requirement.** Described above.
- **B — Reuse `URL::temporarySignedRoute()`/`hasValidSignature()` verbatim.** Rejected: ties the v3 endpoint to Laravel's own canonical-URL reconstruction, harder for non-Laravel/external clients to reproduce, and does not naturally extend to a `photo_id`+`size_variant` path shape without also carrying `expires`/`signature` naming inconsistent with the owner's explicit `timestamp`/`mac` request.
- **C — No new mechanism; redirect to the existing `image/{path}` signed-URL flow.** Rejected: doesn't establish a real v3-native contract (defeats the point of a v3 endpoint), and inherits `SecurePathController`'s lack of `PhotoPolicy` enforcement.

## Security / Privacy Impact

- MAC secret is `config('app.key')` — the same key that already backs Laravel's session/cookie encryption and the v2 signed-route mechanism; no new key management surface.
- `hash_equals()` used for the MAC comparison (timing-safe), consistent with cryptographic best practice already implicit in Laravel's own `hasValidSignature()`.
- Because the MAC is not resource-scoped (see Negative, above), a leaked `(timestamp, mac)` pair is a time-boxed, but not photo-scoped, guest-equivalent access token. `PhotoPolicy` remains the actual access-control boundary in every case; the signature only ever proves "requested within a legitimate time window."

## Operational Impact

- No new config keys — reuses `temporary_image_link_enabled`/`temporary_image_link_when_logged_in`/`temporary_image_link_when_admin`/`temporary_image_link_life_in_seconds` verbatim.
- No new telemetry surface introduced by this ADR itself; standard Laravel request logging applies.

## Links

- Related spec sections: `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md` (FR-056-02/03, NFR-056-01)
- Related open questions: Q-056-01, Q-056-05 (docs/specs/4-architecture/open-questions.md)
- Related ADRs: none (first v3-specific ADR)
