# Open Questions – Feature 056

Open questions for [Feature 056](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-056-01~~ | 056 – API v3 Asset Retrieval | High | MAC/signature mechanism for temporary-link `timestamp`+`mac` params — bespoke HMAC vs. reuse of Laravel's `URL::temporarySignedRoute()` | Resolved (custom — HMAC-SHA256 of the timestamp only, not photo/size-variant-scoped) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-02~~ | 056 – API v3 Asset Retrieval | High | Response shape for this endpoint — raw binary passthrough vs. JSON envelope; does the "Struct of Arrays" v3 principle apply to a single-item retrieval endpoint at all | Resolved (A — binary passthrough; SoA scoped to future collection endpoints) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-03~~ | 056 – API v3 Asset Retrieval | Medium | S3-backed size variants — proxy/stream through Lychee vs. redirect to a native S3 temporary URL (today's asymmetric behaviour) | Resolved (B — redirect to native S3 temporary URL, same asymmetry as v2) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-04~~ | 056 – API v3 Asset Retrieval | Medium | Watermark behaviour — always watermarked (view semantics), always raw (download semantics), or caller-selectable | Resolved (A — always watermark-aware, mirrors `getUrlAttribute()`) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-05~~ | 056 – API v3 Asset Retrieval | Medium | Authorization when not using a temporary link — must the caller be authenticated + PhotoPolicy-checked, or is it public like today's `image/{path}` route | Resolved (custom — depends on `temporary_image_link_*` configs; PhotoPolicy always checked regardless of mode) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-06~~ | 056 – API v3 Asset Retrieval | Medium | URL/parameter contract shape — path segments vs. query string for `photo_id`/`size_variant`/`timestamp`/`mac` | Resolved (A — path segments for resource identifiers, query string for signature params) | 2026-08-20 | 2026-08-20 |
| ~~Q-056-07~~ | 056 – API v3 Asset Retrieval | Medium | Discovered during I1 implementation: the shared `api` middleware group (`app/Http/Kernel.php`) applies `accept_content_type:json`/`content_type:json`, which throw `UnexpectedContentType` for any request/response that isn't JSON-negotiated — incompatible with this endpoint's binary passthrough response (FR-056-02) and any real client's GET request without an `Accept: application/json` header. Opting the route out of both middleware (only) then surfaced a second-order issue: Laravel's default exception rendering also keys off the client's `Accept` header, so error responses (422/404/etc.) would silently degrade to Lychee's HTML error page instead of the JSON body FR-056-02 requires, for any real client that (correctly) never sends `Accept: application/json` to a binary-asset endpoint. | Resolved (A — the v3 route opts out of `accept_content_type:json`/`content_type:json` via `Route::withoutMiddleware(...)` in `routes/api_v3.php`, scoped to this one route, and adds a new small `json_errors` middleware (`App\Http\Middleware\EnsureJsonErrorResponses`, aliased in `app/Http/Kernel.php`) that unconditionally sets the request's `Accept` header to `application/json` before the controller runs — success responses are unaffected since `response()->file()` doesn't consult `Accept`, but every exception path now renders through Lychee's standard JSON error convention regardless of the caller's real `Accept` header. The shared `api` middleware group definition and all v2 routes are untouched) | 2026-08-20 | 2026-08-20 |

## Question Details

### ~~Q-056-01~~: MAC/signature mechanism for temporary asset links ✅ RESOLVED

**Status:** Resolved — **custom** (HMAC-SHA256 of the timestamp only)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** High
**Opened:** 2026-08-20
**Resolved:** 2026-08-20

**Resolution:** `mac = hash_hmac('sha256', (string) $timestamp, config('app.key'))`, hex-encoded, verified with `hash_equals()`. Deliberately scoped to the timestamp alone — **not** `photo_id`/`size_variant` — per explicit owner instruction ("Hmac of timestamp only"). A new `App\Services\TemporaryLinkSigner` (`sign()`/`verify()`) owns this; no new secret storage (reuses `APP_KEY`). Expiry reuses the existing `temporary_image_link_life_in_seconds` config. See ADR-0008 for the full security rationale, including the accepted trade-off that a leaked `(timestamp, mac)` pair is valid for any photo/size-variant within the TTL window (bounded by `PhotoPolicy`, which is always separately enforced — Q-056-05).

**Spec impact:** FR-056-03; NFR-056-01; DO-056-01. ADR-0008.

**Context:** Today's only signed-URL mechanism (`app/Services/UrlGenerator.php`, `app/Http/Controllers/SecurePathController.php`) uses Laravel's built-in `URL::temporarySignedRoute()`, which produces `expires` + `signature` query params — an HMAC-SHA256 over the *entire canonical URL* (host, path, all query params in Laravel's own ordering), keyed by `APP_KEY`, verified via `$request->hasValidSignature()`. The user's ask for v3 explicitly describes a `timestamp` + a MAC "of the timestamp" — a different shape than Laravel's `expires`/`signature` idiom, and a MAC computed over just the timestamp (not the whole URL) suggests a simpler, more portable scheme that a non-Laravel client could reproduce without depending on Laravel's URL-canonicalization internals. Since v3 is greenfield, nothing forces reuse of the v2 mechanism.

**Question:** What algorithm, inputs, and secret produce the `mac` value, and how does it relate to Laravel's existing signed-URL support?
- **Option A (Recommended):** New, bespoke endpoint-level HMAC: `mac = hash_hmac('sha256', "{photo_id}:{size_variant}:{timestamp}", $secret)`, hex-encoded, verified with `hash_equals()` and an expiry window read from config (mirroring `temporary_image_link_enabled`'s existing TTL knob). Secret is a server-side key (e.g. derived from `APP_KEY`, or a new dedicated config value). Decoupled from Laravel's `hasValidSignature()` internals — any client that knows the secret and the three inputs can (re)compute a valid link without touching Laravel's route/query-canonicalization rules.
- **Option B:** Reuse Laravel's `URL::temporarySignedRoute()`/`hasValidSignature()` exactly as `SecurePathController` does today, just renaming/aliasing `expires`→`timestamp` and `signature`→`mac` in the request contract while keeping Laravel's canonical-URL HMAC underneath.
- **Option C:** Don't build a new mechanism at all — v3's asset endpoint redirects to (or wraps) the existing `image/{path}` signed-URL flow via `UrlGenerator::pathToUrl()`, so `SecurePathController` remains the sole place that validates signatures.

**Impact:** Determines the new Request/validation class's shape, any new config keys (secret source, TTL), whether a new `Services/` class is needed to compute/verify the MAC, and whether this is a genuinely new REST contract or a thin wrapper around the existing v2 mechanism.

---

### ~~Q-056-02~~: Response shape — binary passthrough vs. JSON envelope ✅ RESOLVED

**Status:** Resolved — **Option A** (binary passthrough)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** High
**Opened:** 2026-08-20
**Resolved:** 2026-08-20

**Resolution:** Pure binary passthrough — raw file bytes with `Content-Type` set from the resolved file, no JSON envelope. The SoA-vs-AoS response-shape principle is scoped to future v3 endpoints that return collections; it does not apply to this single-item binary-retrieval endpoint. See ADR-0009.

**Spec impact:** FR-056-02; Non-Goals. ADR-0009.

**Context:** The user states v3's "base" will be Struct-of-Arrays (SoA) instead of Array-of-Structs (AoS) — a response-*shape* principle that applies naturally to *list/collection* endpoints (e.g., a future paginated endpoint returning `{ids: [...], titles: [...], ...}` instead of `[{id, title, ...}, ...]`). This first endpoint, though, returns exactly one binary file for one `photo_id` + one `size_variant` — there is no array of records to arrange as SoA vs. AoS in the response body itself, so it's unclear whether/how the SoA principle is meant to apply here at all.

**Question:** What does this endpoint actually return on success?
- **Option A (Recommended):** Pure binary passthrough — raw file bytes with appropriate `Content-Type`/`Content-Disposition` headers, HTTP 200 (or 302 redirect for the S3 case, see Q-056-03), no JSON envelope at all — same client contract as today's `image/{path}` route. The SoA principle is scoped to future v3 endpoints that return collections, not to this single-item binary-retrieval endpoint.
- **Option B:** Wrap the binary response in a JSON envelope (base64-encoded `data` field plus metadata fields), so that literally every v3 response — including this one — is JSON-shaped for client-side consistency.
- **Option C:** Return JSON metadata only (a resolved, possibly-signed URL to the actual bytes, mirroring `SizeVariant::getUrlAttribute()`) rather than streaming bytes directly from this endpoint — the client makes a second request to fetch the actual file.

**Impact:** Fundamentally different controller implementation, telemetry shape, and client integration pattern (`<img src="...">`/direct byte stream vs. `fetch()` + JSON decode + second request). Also determines whether this endpoint needs its own Spatie Data Resource class at all.

---

### ~~Q-056-03~~: S3-backed size variants — proxy vs. redirect ✅ RESOLVED

**Status:** Resolved — **Option B** (redirect to native S3 temporary URL)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** Medium
**Opened:** 2026-08-20
**Resolved:** 2026-08-20

**Resolution:** For S3-backed variants, the endpoint responds with an HTTP 302 redirect to a freshly generated native S3 temporary URL — same asymmetric behaviour as v2's `UrlGenerator::getAwsUrl()`, keeping the app server out of the byte-proxying path for cloud-stored files.

**Spec impact:** FR-056-05.

**Context:** Today, only local-disk size variants are streamed through Lychee (`SecurePathController::__invoke`, `response()->file($file)`); S3-backed variants bypass Lychee entirely — `UrlGenerator::getAwsUrl()` hands the client a native, S3-issued temporary URL instead, so S3 files are fetched directly from AWS, never proxied. v3's endpoint takes `photo_id` + `size_variant` directly (not an opaque path token), so it's a natural point to decide disk behaviour fresh rather than inheriting the existing asymmetry.

**Question:** How does this endpoint serve a size variant stored on a non-local disk?
- **Option A (Recommended):** Always proxy bytes through Lychee for every disk (local and S3 alike), via the existing `SizeVariant::getFile()`/`FlysystemFile` abstraction (streamed response) — consistent behaviour regardless of storage backend, fixes the existing local/S3 asymmetry, and keeps all v3 asset traffic authenticated at one chokepoint.
- **Option B:** For S3-backed variants, respond with an HTTP 302 redirect to a freshly generated native S3 temporary URL — mirrors today's asymmetric behaviour, avoids double-proxying bandwidth through the app server.
- **Option C:** Local disk only for this feature; requests for S3-backed variants return 501 Not Implemented, with S3 support deferred to a follow-up feature.

**Impact:** Bandwidth/infra cost trade-off, controller complexity (streaming vs. redirect branch), and whether this feature touches `UrlGenerator`/adds a new S3-streaming code path.

---

### ~~Q-056-04~~: Watermark behaviour ✅ RESOLVED

**Status:** Resolved — **Option A** (always watermark-aware)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** Medium
**Opened:** 2026-08-20
**Resolved:** 2026-08-20

**Resolution:** Always applies the same watermark resolution as the existing display path — `Watermarker::get_path($size_variant)` — before serving/redirecting. This is a "view" endpoint, matching gallery-display use, not a download endpoint.

**Spec impact:** FR-056-04.

**Context:** `SizeVariant::getUrlAttribute()` (today's main "display" accessor) resolves and returns a watermarked path when applicable; the separate `getDownloadUrlAttribute()` deliberately does not. The user's description ("return the associated file") doesn't say which semantic this v3 endpoint matches.

**Question:** Does this endpoint apply watermarking?
- **Option A (Recommended):** Always apply the same watermark resolution as the existing display path (`getUrlAttribute()`'s logic) — this is framed as a "view" endpoint (id + size-variant lookup), matching gallery-display use, not a download endpoint.
- **Option B:** Always return the raw/original stored file, never watermarked (download semantics) — a separate future v3 endpoint would be needed for watermark-aware display.
- **Option C:** Make it caller-selectable via a request parameter (e.g. `?download=true`) that toggles between watermarked-view and raw-download semantics within this one endpoint.

**Impact:** Determines whether this feature is purely a "view" replacement for `image/{path}` or also subsumes today's separate download flow; shapes FR wording and whether a second parameter/branch is in scope.

---

### ~~Q-056-05~~: Authorization when not using a temporary link ✅ RESOLVED

**Status:** Resolved — **custom** (config-dependent signature requirement; `PhotoPolicy` always checked)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** Medium
**Opened:** 2026-08-20
**Resolved:** 2026-08-20

**Resolution:** Whether a valid `timestamp`+`mac` is *required* at all depends on the existing `temporary_image_link_enabled`/`temporary_image_link_when_logged_in`/`temporary_image_link_when_admin` configs, re-purposed from v2's generation-time semantics (`UrlGenerator::shouldNotUseSignedUrl()`) into a validation-time predicate (`signatureRequired()`, see ADR-0008) evaluated against the current request's auth state. Guests are only ever admitted via a valid signature (when the feature is enabled); authenticated/admin callers may rely on their session alone if the corresponding `_when_*` config says so. In **every** case — session-authenticated or temporary-link — `PhotoPolicy::CAN_SEE`/`CAN_ACCESS_FULL_PHOTO` is evaluated against the resolved `Auth::user()` (nullable) before serving the file. This is the key security delta versus v2's `SecurePathController`, which never checks `PhotoPolicy` at all.

**Spec impact:** FR-056-02/03; NFR-056-01. ADR-0008.

**Context:** The existing `image/{path}` route has no `login_required` middleware — it relies entirely on the signed/encrypted opaque path token for access control (anyone holding a valid link URL can fetch the file; no session check happens at all). A v3 endpoint addressed by a plain `photo_id` + `size_variant` (not an opaque encrypted path) is directly guessable/enumerable, so its default (non-temporary-link) access path needs an explicit authorization story that the opaque-path design never had to solve.

**Question:** What authorizes a request that does *not* carry `timestamp`+`mac`?
- **Option A (Recommended):** Default (non-temporary-link) requests must be authenticated (Sanctum session or API token, same guard as other `/api/v2/...` endpoints) and are then subject to the same `PhotoPolicy`/`PhotoQueryPolicy` album-visibility checks as other authenticated photo endpoints. The `timestamp`+`mac` temporary-link path becomes the *only* way to fetch an asset unauthenticated — mirroring today's `temporary_image_link_enabled` config gate, just re-scoped to this new endpoint.
- **Option B:** Fully public/unauthenticated by default (like today's `image/{path}` route), with album-visibility rules (public/unlisted) enforced but no session required at all — `timestamp`/`mac` become optional and only matter for privately-shared/protected albums.
- **Option C:** Require `timestamp`+`mac` unconditionally on every request to this endpoint, even for already-logged-in users — no separate authenticated-session code path at all.

**Impact:** Determines whether new middleware/guard wiring is needed, whether this endpoint depends on `SecurePathRequest`-style config gating (`secure_image_link_enabled`/`temporary_image_link_enabled`), and the shape of 401 vs. 403 vs. 404 responses.

---

### ~~Q-056-06~~: URL/parameter contract shape ✅ RESOLVED

**Status:** Resolved — **Option A, amended** (path segments; signature via headers, not query string)
**Feature:** 056 – API v3 Asset Retrieval
**Priority:** Medium
**Opened:** 2026-08-20
**Resolved:** 2026-08-20 (amended same day — signature moved from query string to headers)

**Resolution:** `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}` — RESTful path segments for the resource identifiers. The signature is **not** carried in the query string: `timestamp` and `mac` are passed as request headers, `X-Timestamp` and `X-Mac` (matching this codebase's existing single-word `X-<Name>` custom-header convention, e.g. `X-API-Key` in `app/Http/Requests/Face/FaceDetectionResultsRequest.php`). Rationale for the amendment: keeps signature material out of server access logs' request-line/URL field (a real, if partial, improvement over query-string placement — headers can still be logged by some proxies, but are not part of the URL itself), and out of browser history / `Referer` leakage if this endpoint's URL is ever linked from another page.

**Spec impact:** API-056-01; DO-056-01 (request shape amended: `timestamp`/`mac` are headers, not query params).

**Context:**

**Context:** No v3 routing convention exists yet — this feature establishes the first one. v2's precedent is a single flat `routes/api_v2.php` with domain-organized (not version-namespaced) controllers, and action-style routes like `Album::head`. v3 needs its own decision for how `photo_id`, `size_variant`, `timestamp`, and `mac` are carried on the request.

**Question:** What does the request URL look like?
- **Option A (Recommended):** RESTful path segments for the resource identifiers, query string for the signature params: `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}?timestamp=...&mac=...` — resource path reads as cacheable/bookmarkable, signature params stay in the query string like today's `expires`/`signature`.
- **Option B:** Fully query-string based: `GET /api/v3/Asset?photo_id=...&size_variant=...&timestamp=...&mac=...` — flat and uniform, straightforward to generate programmatically from an SoA-style client (parallel arrays of ids/types) via simple param substitution.
- **Option C:** Fully path-segment based, including the signature: `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}/{timestamp}/{mac}` — maximally cacheable at a CDN/reverse-proxy layer, but puts signature material directly in the URL path rather than the query string.

**Impact:** Defines the concrete `API-056-01` contract entry, the new `routes/api_v3.php` additions, and the OpenAPI/documentation shape for this and future v3 endpoints.
