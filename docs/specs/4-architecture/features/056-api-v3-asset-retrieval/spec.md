# Feature 056 – API v3 Asset Retrieval

| Field | Value |
|-------|-------|
| Status | Completed |
| Last updated | 2026-09-02 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/tasks.md` |
| Roadmap entry | #56 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Lychee's current REST surface is `/api/v2/...`, an Array-of-Structs (AoS) API where collection responses are arrays of self-contained objects (see `PaginatedPhotosResource`/`PaginatedAlbumsResource` in [docs/specs/3-reference/api-design.md](../../../3-reference/api-design.md)). This feature starts **API v3**, a new, greenfield `/api/v3/...` surface whose base response convention is Struct-of-Arrays (SoA) for *collection* endpoints — a convention this feature establishes precedent for but does not itself need, since its endpoint is single-item (ADR-0009). v3 coexists with v2; nothing in v2 is deprecated or changed by this feature.

The first v3 endpoint retrieves a single photo asset: given a `photo_id` and a `size_variant` type, it returns the associated file (thumbnail through original), watermark-aware. It supports two access modes: (1) an authenticated (session) request, and (2) an unauthenticated **temporary link** request carrying a `timestamp` and a MAC of that timestamp via request headers. `PhotoPolicy` authorization is enforced in both modes — a deliberate strengthening over v2's `SecurePathController`, which enforces no application-level policy at all (ADR-0008).

## Goals
- Establish the v3 routing/versioning convention (`routes/api_v3.php`, `/api/v3` prefix) that later v3 endpoints will follow.
- Serve a single photo size-variant's binary file given `photo_id` + `size_variant` type, watermark-aware.
- Support unauthenticated retrieval via a signed temporary link (`X-Timestamp` + `X-Mac` headers), and authenticated (session) retrieval otherwise, per the config-driven model in ADR-0008.
- Enforce `PhotoPolicy` authorization on every request, regardless of access mode.
- Keep v2 entirely unaffected (no shared route/controller edits beyond read-only reuse of existing models/enums).

## Non-Goals
- Any v3 endpoint other than this single asset-retrieval endpoint (e.g. no v3 album/photo listing, no v3 write endpoints) — out of scope for this feature.
- Migrating or deprecating the existing v2 `image/{path}` route, `SecurePathController`, or `UrlGenerator` — they remain unchanged and continue serving v2/legacy consumers.
- Defining the general SoA response convention for v3 *collection* endpoints (ADR-0009) — a future v3 spec defines that when the first list endpoint is built.
- Proxying S3-backed size variants through Lychee — this endpoint redirects to a native S3 temporary URL instead (see FR-056-05).
- A per-resource-scoped signature (the MAC authenticates the timestamp only, not `photo_id`/`size_variant` — accepted trade-off, see ADR-0008 Security/Privacy Impact).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-056-01 | New `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}` route, registered via a new `routes/api_v3.php` under the `api` middleware group with prefix `api/v3` (mirrors v2's `RouteServiceProvider` registration pattern), but with the `api` group's `accept_content_type:json`/`content_type:json` middleware opted out of on this route specifically (via `Route::withoutMiddleware(...)`) since this endpoint is a binary passthrough, not a JSON endpoint (Q-056-07). | Route resolves to a new controller action; `photo_id` and `size_variant` are route parameters. | `photo_id` validated as an existing photo ID (`RandomIDRule`, `findOrFail` semantics → 404 if absent); `size_variant` validated against `SizeVariantType`'s `name()` values (`raw`, `original`, `medium2x`, `medium`, `small2x`, `small`, `thumb2x`, `thumb`, `placeholder` — already lowercase, `app/Enum/SizeVariantType.php`), matched case-insensitively → 422 on an unrecognized value, via the new reusable `App\Rules\SizeVariantTypeNameRule`. | Unknown route → 404 (standard Laravel routing). Unknown `photo_id` → 404. Invalid `size_variant` token → 422. | Standard Laravel request logging; no new telemetry event. | Owner instruction (2026-08-20); Q-056-06; Q-056-07 |
| FR-056-02 | The endpoint returns the requested `SizeVariant`'s file as a raw binary passthrough (no JSON envelope) — `Content-Type` set from the resolved file, HTTP 200. | Streams file bytes for local-disk variants (mirrors `SecurePathController`'s `response()->file()` pattern, via `SizeVariant::getFile()`/`FlysystemFile`). | The requested `(photo_id, size_variant)` pair must have a corresponding `SizeVariant` row → 404 if the photo has no variant of that type (e.g. no `RAW` stored). | 404 Not Found (no such variant), with a JSON error body per Lychee's standard exception-handler convention (not this endpoint's own envelope — errors are not "success" responses). | Standard Laravel request logging. | Q-056-02; ADR-0009 |
| FR-056-03 | Authorization order: (1) FR-056-05's `signatureRequired()` check and, if required, FR-056-04's signature validity check run **first**; only once that step passes (or is not required) does `PhotoPolicy` run. `PhotoPolicy::CAN_SEE` gates thumbnail-class variants (`THUMB`, `THUMB2X`, `SMALL`, `SMALL2X`, `PLACEHOLDER`); `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` gates full-resolution variants (`MEDIUM`, `MEDIUM2X`, `ORIGINAL`, `RAW`) — evaluated against `Auth::user()` (nullable) in **every** request that clears step (1). | Policy grants access → proceed to FR-056-02/06/07. | N/A (this is itself the validation gate). | Policy denies access → 403 Forbidden (`App\Exceptions\UnauthorizedException`, thrown via `GetPhotoAssetRequest`'s overridden `failedAuthorization()` — see FR-056-05). | Standard Laravel request logging. | Q-056-05; ADR-0008 |
| FR-056-04 | Temporary-link mode: request carries `X-Timestamp` (Unix seconds, integer) and `X-Mac` (hex HMAC-SHA256 of the timestamp) headers. `mac` is verified as `hash_equals($expected, $provided)` where `$expected = hash_hmac('sha256', (string) $timestamp, config('app.key'))`, computed by a new `App\Services\TemporaryLinkSigner::verify(int $timestamp, string $mac): bool` (MAC check only — TTL/expiry is checked separately in `GetPhotoAssetRequest`, not inside `TemporaryLinkSigner`, since the signer has no config/`$ttl` input; see DO-056-02). Timestamp must satisfy `now()->timestamp - $timestamp <= temporary_image_link_life_in_seconds` and `$timestamp <= now()->timestamp`. This check runs before FR-056-03's `PhotoPolicy` check (see FR-056-03/05 ordering), and only when FR-056-05's `signatureRequired()` is `true` for the caller — when it's `false`, header validation is skipped entirely and the request proceeds straight to FR-056-03. | Valid, unexpired signature + `signatureRequired()` true → request proceeds to FR-056-03's `PhotoPolicy` check as a guest. | Missing one of `X-Timestamp`/`X-Mac` when the other is present → 422 (both-or-neither). | Invalid MAC, expired timestamp, or future timestamp, while `signatureRequired()` is `true` for the caller → 401 Unauthorized (`App\Exceptions\UnauthenticatedException` — see FR-056-05 for the exact mechanism). | Standard Laravel request logging. | Q-056-01; Q-056-06 (headers, amended); ADR-0008 |
| FR-056-05 | Whether `X-Timestamp`/`X-Mac` are *required* (vs. session being sufficient alone) is determined per-request by `signatureRequired(?User $user, ConfigManager $cfg)` (ADR-0008), reusing `temporary_image_link_enabled`/`temporary_image_link_when_logged_in`/`temporary_image_link_when_admin`. `signatureRequired()` is `false` for **every** caller, guests included, whenever `temporary_image_link_enabled` is `false` (Q-056-05): disabling the feature removes the extra signature requirement outright rather than locking guests out, so a disabled-feature guest relies solely on FR-056-03's `PhotoPolicy`/`AlbumPolicy` check, exactly like any other caller. When `temporary_image_link_enabled` is `true`, guests (`Auth::user() === null`) are only ever authorized via a valid temporary link, there being no session to fall back on. **401-vs-403 mechanism:** the inherited `BaseApiRequest::failedAuthorization()` picks its exception solely from `Auth::check()` (session state), which cannot express this endpoint's required mapping — e.g. S-056-03 (guest, valid signature, policy denies) needs 403 despite no session, and S-056-10 (logged-in user, signature required by config but missing) needs 401 despite a session. `GetPhotoAssetRequest` therefore overrides `failedAuthorization()`, keyed on a `private bool $signature_check_failed` flag set during `authorize()` (true only when `signatureRequired()` is true for this caller and the signature is absent/malformed/invalid/expired/future — i.e. FR-056-04's failure path): `throw $this->signature_check_failed ? new UnauthenticatedException() : new UnauthorizedException();`. | Authenticated session present and `signatureRequired()` is false for that user → session + `PhotoPolicy` (FR-056-03) suffices, no headers needed, `$signature_check_failed` stays `false`. A guest for whom `signatureRequired()` is false (`temporary_image_link_enabled=false`) is treated the same way — no signature required, `PhotoPolicy`/`AlbumPolicy` alone gates access. | Authenticated session present but `signatureRequired()` is true for that user (e.g. `temporary_image_link_when_logged_in=true`) → headers additionally required per FR-056-04; missing/invalid → `$signature_check_failed = true` → 401. | No session, `signatureRequired()` true (i.e. `temporary_image_link_enabled=true`), and no valid temporary link → `$signature_check_failed = true` → 401 Unauthorized. | Standard Laravel request logging. | Q-056-05; ADR-0008 |
| FR-056-06 | Watermarking: the file resolved and served is always watermark-aware, reusing `Watermarker::get_path($size_variant)` — the same resolution `SizeVariant::getUrlAttribute()` already performs for v2 display. | Watermarked path served when `should_use_watermarked_path()` (existing `Watermarker` logic) applies for the requesting viewer; plain path otherwise. | N/A (delegates entirely to existing `Watermarker` logic, unchanged by this feature). | N/A — `Watermarker::get_path()` already throws for `PLACEHOLDER`/`RAW`; this endpoint lets that exception surface as a 4xx per Lychee's standard exception-handler convention. | Standard Laravel request logging. | Q-056-04 |
| FR-056-07 | Disk handling: for a `SizeVariant` stored on a non-local (S3) disk, the endpoint responds with an HTTP 302 redirect to a freshly generated native S3 temporary URL (same `AwsS3V3Adapter` detection and `temporaryUrl()` call as `UrlGenerator::getAwsUrl()`), evaluated **after** FR-056-03's `PhotoPolicy` check passes. For a local-disk `SizeVariant`, the endpoint streams bytes directly (FR-056-02). | S3-backed variant + authorized request → 302 redirect to a time-limited S3 URL. | N/A. | N/A (disk resolution cannot itself fail independently of FR-056-02's 404). | Standard Laravel request logging. | Q-056-03 |
| FR-056-08 *(2026-09-02 amendment, Feature 063 Q-063-15)* | `GetPhotoAssetRequest::isPhotoOfAlbum()` gains a `BaseSmartAlbum` branch: `$album instanceof BaseSmartAlbum && $this->isComputedAlbumThumb($album->get_id())` returns `true`, mirroring the pre-existing (previously undocumented in this spec — a gap this amendment also closes) `TagAlbum`/`PersonAlbum` cache-exception branch immediately above it, which lets a viewer's cached `album_user_thumbs` cover photo through even when it no longer satisfies that album's own live membership query (the same reason the `TagAlbum`/`PersonAlbum` branch exists — `AlbumUserThumb` rows go stale between writes and the next `RecomputeAlbumUserThumbsJob` run). Without this branch, a smart album's cover_id sourced from Feature 062's new `AlbumUserThumb` lookup (FR-062-16) would 403 through this endpoint the moment the cached photo fell out of the smart album's live `smart_photo_condition` (e.g. a photo was unstarred) — the exact class of staleness `isComputedAlbumThumb()` already exists to paper over for tag/person albums, just never extended to the third `CachesAlbumUserThumb` consumer. A second, corollary bug was found and fixed while implementing this: `rules()` validated `album_id` via `RandomIDRule` (`RandomID::ID_LENGTH`-character, fixed-charset only) rather than `AlbumIDRule` (the rule `GetAlbumRequest`/`GetAlbumHeadRequest` already use, which additionally accepts any `SmartAlbumType` value) — every smart-album request 422'd at validation, before this new branch (or any album-type-specific logic) was ever reached. `TagAlbum`/`PersonAlbum` ids happen to share `Album`'s id length/charset, so this pre-existing mismatch went unnoticed until a genuinely differently-shaped id (a smart album's) was actually exercised against it. Swapped to `AlbumIDRule`. | A smart album's cached cover photo resolves through the Asset endpoint even during the staleness window before the next `RecomputeAlbumUserThumbsJob` run, matching `TagAlbum`/`PersonAlbum` parity. | N/A. | `photo_id` matching neither the live `smart_photo_condition` nor the cached `album_user_thumbs` row (e.g. the photo itself was deleted) → falls through to the existing generic `$album->photos()->whereKey($this->photo_id)->exists()` check, which correctly returns `false` → 403, unchanged. | None. | `App\Models\Extensions\CachesAlbumUserThumb`; the pre-existing, sibling `TagAlbum`/`PersonAlbum` branch (`GetPhotoAssetRequest::isPhotoOfAlbum()`, undocumented by any prior feature spec — this amendment is also this behavior's first spec record). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-056-01 | The MAC secret is `config('app.key')` — no new secret storage or config key introduced for signing. Comparison uses `hash_equals()` (timing-safe). | Security — avoid a new key-management surface; avoid timing side-channels. | Code review confirms `hash_equals()` usage; no new `.env`/config key added for the secret itself. | `App\Services\TemporaryLinkSigner` | ADR-0008 |
| NFR-056-02 | This feature adds no new database migrations, config keys, or columns — it reuses `temporary_image_link_enabled`/`temporary_image_link_when_logged_in`/`temporary_image_link_when_admin`/`temporary_image_link_life_in_seconds` verbatim. | Minimal footprint; avoid duplicating v2's existing config surface. | Diff review: no new migration file in this feature's task list. | Existing `configs` table rows (`database/migrations/2025_04_05_153533_add_secure_link_options.php`) | Q-056-01/05 |
| NFR-056-03 | v2 routes, controllers, and `UrlGenerator`/`SecurePathController` are untouched by this feature — verified by an empty diff on `routes/api_v2.php`, `routes/web_v2.php`, `app/Http/Controllers/SecurePathController.php`, `app/Services/UrlGenerator.php`. | Backward-compat stance (AGENTS.md): v3 is additive, not a v2 migration. | `git diff` review on those four files shows no changes. | — | AGENTS.md guardrail |
| NFR-056-04 | Every Branch & Scenario Matrix entry (S-056-*) has a corresponding Feature-level HTTP test, written and confirmed failing before implementation (test-first cadence), living under a new `tests/Feature_v3/` tree (own PHPUnit testsuite) rather than `tests/Feature_v2/` — v3 tests are kept in their own version-scoped directory from this first feature onward. | AGENTS.md SDD Feedback Loops — branch coverage upfront; owner instruction to scope v3 tests under `Feature_v3`. | `php artisan test --testsuite=Feature_v3` passes post-implementation; test file diff shows tests added before controller code in commit history. | New `Tests\Feature_v3\Base\BaseApiWithDataTest` (extends `Tests\Feature_v2\Base\BaseApiWithDataTest` to reuse its fixture graph, see plan.md); new `Feature_v3` `phpunit.xml` testsuite entry | AGENTS.md; owner instruction 2026-08-20 |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-056-01 | Authenticated owner requests own photo's `THUMB` variant, no signature headers, `signatureRequired()` false → 200, correct bytes streamed, watermark resolution applied. |
| S-056-02 | Guest requests a public album's photo `THUMB` variant with valid `X-Timestamp`/`X-Mac` within TTL, `temporary_image_link_enabled=true` → 200. |
| S-056-03 | Guest requests the same as S-056-02 but the album is **not** public (private/protected) → 403, despite a validly-signed link (`PhotoPolicy` still denies). |
| S-056-04 | Guest requests with no `X-Timestamp`/`X-Mac` at all, `temporary_image_link_enabled=true` → 401 (no session, no valid signature). |
| S-056-05 | Guest requests a public album's photo with `temporary_image_link_enabled=false` (feature off instance-wide) → `signatureRequired()` is `false`, so the request falls through to `PhotoPolicy`/`AlbumPolicy` exactly like any other unauthenticated request → 200, regardless of any headers present. |
| S-056-06 | Request with `X-Mac` that doesn't match `hash_hmac('sha256', X-Timestamp, app.key)` → 401. |
| S-056-07 | Request with a `X-Timestamp` older than `now() - temporary_image_link_life_in_seconds` → 401 (expired). |
| S-056-08 | Request with a `X-Timestamp` in the future (`> now()`) → 401. |
| S-056-09 | Request with only `X-Timestamp` present, `X-Mac` missing (or vice versa) → 422. |
| S-056-10 | Authenticated non-admin user, `temporary_image_link_when_logged_in=true` (feature configured to still require signatures for logged-in users) and no headers supplied → 401, even though a session exists. |
| S-056-11 | Authenticated admin, `temporary_image_link_when_admin=false`, no headers → 200 (admin session alone suffices per config). |
| S-056-12 | Request for `size_variant=RAW` on a photo with no stored `RAW` variant → 404. |
| S-056-13 | Request with an unrecognized `size_variant` path segment (e.g. `/Asset/huge`) → 422. |
| S-056-14 | Request with a non-existent `photo_id` → 404. |
| S-056-15 | Authorized request for a `size_variant` stored on the S3 disk → 302 redirect to a native S3 temporary URL, no bytes proxied through Lychee. |
| S-056-16 | Authorized request for `size_variant=MEDIUM` where the requesting viewer meets watermark conditions (`Watermarker::should_use_watermarked_path()` true) → served file is the watermarked path, not the plain stored path. |
| S-056-17 | `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` denies (e.g. album disables full-resolution access) for `size_variant=ORIGINAL` even though `CAN_SEE` would allow `THUMB` → 403 for `ORIGINAL`, 200 for `THUMB`, same photo/session. |

## Test Strategy
- **REST:** Feature-level HTTP tests (new `tests/Feature_v3/Photo/PhotoAssetV3Test.php`, extending a new `Tests\Feature_v3\Base\BaseApiWithDataTest`) cover every S-056-* scenario above — written and confirmed failing before controller implementation, per AGENTS.md's test-first cadence. v3 tests are scoped to their own `Feature_v3` directory/testsuite, not mixed into `Feature_v2` (owner instruction).
- **Unit:** `App\Services\TemporaryLinkSigner` gets isolated unit tests for `sign()`/`verify()` (valid MAC, tampered MAC) independent of HTTP plumbing — `verify()` has no TTL/config input (DO-056-02), so expiry/future-timestamp cases (S-056-07/08) are Feature-level tests against `GetPhotoAssetRequest`, not `TemporaryLinkSigner` unit tests.
- **Application:** `signatureRequired()`'s boolean composition (ADR-0008) is covered by a dedicated unit/Feature test matrix across all 2×2×2 combinations of the three `temporary_image_link_*` config booleans crossed with (guest / logged-in / admin) caller state — NFR-056-04.
- **Core:** No core/domain-layer changes — this feature reuses existing `SizeVariant`, `Photo`, `PhotoPolicy`, `Watermarker`, `ConfigManager` unchanged.
- **CLI:** N/A — no CLI surface in this feature.
- **UI (JS/Selenium):** N/A — no frontend change; this is a backend-only API endpoint (a future v3 client feature would consume it).
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` gets a new "API v3" section (Documentation Deliverables) documenting the route, headers, and response codes as the OpenAPI-equivalent contract reference for this endpoint.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-056-01 | `GetPhotoAssetRequest` — route params `photo_id` (string, `RandomIDRule`), `size_variant` (string, matched against `SizeVariantType` case names); headers `X-Timestamp` (nullable int), `X-Mac` (nullable string, required together with `X-Timestamp`). | REST |
| DO-056-02 | `TemporaryLinkSigner` — `sign(int $timestamp): string`, `verify(int $timestamp, string $mac): bool`. Stateless service, no persistence. | Application |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-056-01 | REST GET `/api/v3/Photo/{photo_id}/Asset/{size_variant}` | Retrieve a photo's size-variant asset. Headers: `X-Timestamp` (optional), `X-Mac` (optional, required together with `X-Timestamp`). Responses: 200 (binary, local disk), 302 (redirect, S3 disk), 401 (auth/signature failure), 403 (`PhotoPolicy` denial), 404 (photo or variant not found), 422 (invalid `size_variant` token or malformed header pair). | New `routes/api_v3.php`; new controller `App\Http\Controllers\Gallery\PhotoAssetController`. |

## Documentation Deliverables
- Update [docs/specs/3-reference/api-design.md](../../../3-reference/api-design.md) with a new "API v3" section documenting `API-056-01`'s route, headers, and response codes, and noting the SoA-for-collections / binary-passthrough-for-single-item precedent (ADR-0009).
- Update [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md) with the new `routes/api_v3.php` convention, `PhotoAssetController`, and `TemporaryLinkSigner` once implemented.
- Roadmap entry added (this feature) to the Active Features table in [docs/specs/4-architecture/roadmap.md](../../roadmap.md).

## Spec DSL

```
domain_objects:
  - id: DO-056-01
    name: GetPhotoAssetRequest
    fields:
      - name: photo_id
        type: string
        constraints: "route param, RandomIDRule, must resolve to an existing Photo"
      - name: size_variant
        type: string
        constraints: "route param, must match a SizeVariantType case name"
      - name: X-Timestamp
        type: integer
        constraints: "header, optional, required together with X-Mac, Unix seconds"
      - name: X-Mac
        type: string
        constraints: "header, optional, required together with X-Timestamp, hex HMAC-SHA256"
  - id: DO-056-02
    name: TemporaryLinkSigner
    fields:
      - name: sign
        type: "(int $timestamp) -> string"
      - name: verify
        type: "(int $timestamp, string $mac) -> bool"
routes:
  - id: API-056-01
    method: GET
    path: /api/v3/Photo/{photo_id}/Asset/{size_variant}
fixtures: []
ui_states: []
```

## Appendix
Research grounding this spec (from codebase exploration, 2026-08-20):
- v2 asset serving today has no dedicated `photo_id`+`size_variant` endpoint; `SizeVariant::getUrlAttribute()`/`getDownloadUrlAttribute()` compute a URL to a generic `image/{path}` route (`routes/web_v2.php`), served by `SecurePathController` (local disk only, `response()->file()` streaming, no `PhotoPolicy` check).
- Signed-link precedent: `UrlGenerator::pathToUrl()` uses Laravel's `URL::temporarySignedRoute()` (HMAC-SHA256 over the full canonical URL, `expires`+`signature` query params, `APP_KEY`-derived), gated by `temporary_image_link_enabled`/`secure_image_link_enabled` configs; `UrlGenerator::shouldNotUseSignedUrl()` (`app/Services/UrlGenerator.php:82-88`) is the existing generation-time predicate this feature's `signatureRequired()` re-purposes for validation-time (ADR-0008).
- `SizeVariantType` enum (`app/Enum/SizeVariantType.php`): `RAW=0, ORIGINAL=1, MEDIUM2X=2, MEDIUM=3, SMALL2X=4, SMALL=5, THUMB2X=6, THUMB=7, PLACEHOLDER=8`.
- `PhotoPolicy` (`app/Policies/PhotoPolicy.php`): `canSee()` (general visibility, album-access-reduced), `canAccessFullPhoto()` (full-resolution gate, requires `canSee()` first plus per-album full-photo access) — both accept a nullable `User` (guest-aware).
- `Watermarker::get_path(SizeVariant $size_variant)` (`app/Image/Watermarker.php:140-170`) — existing watermark-path resolution, reused unchanged by FR-056-06.
- `StorageDiskType` (`app/Enum/StorageDiskType.php`): `LOCAL = 'images'`, `S3 = 's3'`; `UrlGenerator::pathToUrl()` branches on `getAdapter() instanceof AwsS3V3Adapter`.
- `routes/api_v2.php` is registered via `Route::middleware('api')->prefix('api/v2')->group(...)` in `app/Providers/RouteServiceProvider.php:50`; the `api` middleware group (`app/Http/Kernel.php:68-79`) includes `StartSession`/`AuthenticateSession` (session auth available with no extra guard) and `VerifyCsrfToken` (a no-op for GET requests, so no CSRF exception entry is needed for this endpoint).
- No prior "API v3" or per-version controller-namespace convention exists anywhere in this codebase — this feature is the first. v2 organizes controllers by domain (e.g. `Gallery`), not by version; this feature follows the same domain-organization convention (`App\Http\Controllers\Gallery\PhotoAssetController`) rather than introducing a version-namespaced controller tree.
- No prior "Struct of Arrays" implementation exists; only a forward-looking mention in `virtual-scrolling-study.md` (an unrelated design study of Immich's approach) — this feature does not implement SoA itself (ADR-0009).
