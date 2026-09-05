# Feature 056 Tasks – API v3 Asset Retrieval

_Status: Completed — T-056-19 (2026-09-02 amendment, `BaseSmartAlbum` cache-exception branch + `AlbumIDRule` validation fix, FR-056-08) implemented, `PhotoAssetV3Test` 29/29 green._
_Last updated: 2026-09-02_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Route, request validation, and controller skeleton

- [x] T-056-01a – Scaffold the `Feature_v3` test tree (F-056-01).
  _Intent:_ Add a `Feature_v3` testsuite entry to `phpunit.xml` (mirrors the existing `Feature_v2` block: `<directory suffix="Test.php">./tests/Feature_v3</directory>`, excluding `./tests/Feature_v3/Base/BaseApiWithDataTest.php`). Create `tests/Feature_v3/Base/BaseApiWithDataTest.php` (`namespace Tests\Feature_v3\Base`, `extends \Tests\Feature_v2\Base\BaseApiWithDataTest` — reuses the existing user/album/photo fixture graph by inheritance, zero duplication, zero edits to the v2 file), adding a `getV3(string $uri, array $headers = []): TestResponse` helper that issues `$this->withCredentials()->get('/api/v3/' . ltrim($uri, '/'), $headers)` (native Laravel `get()`, not `getJson()`, since this endpoint's success response is binary). Doc comment on the class warns not to use the inherited v2-prefixed `getJson()`/`postJson()`/etc. helpers.
  _Verification commands:_
  - `php artisan test --testsuite=Feature_v3` (should report "No tests executed" — tree is empty until T-056-01b)
  _Notes:_ v2's `tests/Feature_v2/Base/BaseApiWithDataTest.php` is not modified. See plan.md Assumptions for the full rationale (why inheritance over duplication or editing v2's `self::API_PREFIX`).

- [x] T-056-01b – Failing Feature test for S-056-01 (F-056-01, F-056-02, F-056-03, S-056-01).
  _Intent:_ Add `tests/Feature_v3/Photo/PhotoAssetV3Test.php` (extends `Tests\Feature_v3\Base\BaseApiWithDataTest`) with a test asserting `GET /api/v3/Photo/{id}/Asset/thumb` returns 200 with correct bytes for an authenticated owner. Confirm it fails (route/controller don't exist yet).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  _Notes:_ Establishes the test file and base fixture data (owned photo with a `THUMB` size variant) reused by later tasks in this increment.

- [x] T-056-02 – `routes/api_v3.php` + `RouteServiceProvider` registration (F-056-01).
  _Intent:_ New route file with `GET /Photo/{photo_id}/Asset/{size_variant}`; register `Route::middleware('api')->prefix('api/v3')->group(base_path('routes/api_v3.php'));` in `app/Providers/RouteServiceProvider.php`.
  _Verification commands:_
  - `php artisan route:list --path=api/v3`
  _Notes:_ No controller yet — route resolves to a 500/404 until T-056-04.

- [x] T-056-03 – `GetPhotoAssetRequest` (F-056-01, F-056-03).
  _Intent:_ `app/Http/Requests/Photo/GetPhotoAssetRequest.php extends BaseApiRequest`. `rules()`: `photo_id` via `RandomIDRule`, `size_variant` matched against `SizeVariantType` case names (case-insensitive). `authorize()`: `Gate::check(PhotoPolicy::CAN_SEE or CAN_ACCESS_FULL_PHOTO, $photo)` depending on resolved `size_variant` class. `processValidatedValues()` resolves `Photo::findOrFail()` and the matching `SizeVariant` row (404 if either absent).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`
  _Notes:_ Covers FR-056-01's validation path (422 on bad `size_variant`, 404 on unknown `photo_id`/missing variant) — add assertions for S-056-12/13/14 in the same test file.

- [x] T-056-04 – `PhotoAssetController::show()` — local-disk streaming, no watermark/S3/temp-link yet (F-056-02).
  _Intent:_ `app/Http/Controllers/Gallery/PhotoAssetController.php`, single `show(GetPhotoAssetRequest $request)` action streaming the plain (unwatermarked) local file via `response()->file(...)`, mirroring `SecurePathController`'s pattern.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`
  _Notes:_ S-056-01 should now pass.

- [x] T-056-05 – Tests for S-056-12/13/14 (validation/404 paths) (F-056-01, S-056-12, S-056-13, S-056-14).
  _Intent:_ Add explicit test cases: missing `SizeVariant` row (e.g. request `RAW` on a photo with none) → 404; invalid `size_variant` token (e.g. `huge`) → 422; unknown `photo_id` → 404.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  _Notes:_ Closes out I1's exit criteria.

### I2 – Temporary-link signing and `signatureRequired()`

- [x] T-056-06 – `TemporaryLinkSigner` unit tests + implementation (F-056-04).
  _Intent:_ Failing unit test first (`tests/Unit/Services/TemporaryLinkSignerTest.php`) covering: valid mac verifies, tampered mac fails, then implement `app/Services/TemporaryLinkSigner.php` (`sign()`/`verify()`, HMAC-SHA256 of the timestamp only, keyed by `config('app.key')`, `hash_equals()` comparison).
  _Verification commands:_
  - `php artisan test --filter=TemporaryLinkSignerTest`
  - `make phpstan`
  _Notes:_ No HTTP/Laravel request plumbing in this class — pure unit-testable.

- [x] T-056-07 – Failing Feature tests for S-056-02..09 (F-056-04, S-056-02..09).
  _Intent:_ Extend `PhotoAssetV3Test` with cases: guest + valid signed headers → 200; guest + valid signature but non-public album → 403; guest + no headers → 401; `temporary_image_link_enabled=false` → 401; tampered mac → 401; expired timestamp → 401; future timestamp → 401; only one of the two headers present → 422. Confirm all fail (headers not yet read by `authorize()`).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`

- [x] T-056-08 – Wire `X-Timestamp`/`X-Mac` header validation into `GetPhotoAssetRequest::authorize()`, with correct 401-vs-403 (F-056-04, F-056-05).
  _Intent:_ Read both headers; both-or-neither validation (422 via `rules()` if only one present); call `TemporaryLinkSigner::verify()`; expiry/future-timestamp checks against `temporary_image_link_life_in_seconds`. Add `private bool $signature_check_failed = false;` on `GetPhotoAssetRequest`; set it `true` and `return false` immediately from `authorize()` on any signature failure (skipping the `PhotoPolicy` check). Override `failedAuthorization()`: `throw $this->signature_check_failed ? new UnauthenticatedException() : new UnauthorizedException();` — the inherited `BaseApiRequest::failedAuthorization()` keys off `Auth::check()` (session state), which gives the *wrong* status code for this endpoint (see spec.md FR-056-05); this override is required, not optional.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`
  _Notes:_ S-056-02, 04, 05, 06, 07, 08, 09 should now pass. S-056-03 needs T-056-09 too (guest + valid signature still gated by `PhotoPolicy`).

- [x] T-056-09 – Implement `signatureRequired()` predicate + tests for S-056-03, S-056-10, S-056-11 (F-056-05, S-056-03, S-056-10, S-056-11).
  _Intent:_ Implement the config-driven predicate from ADR-0008 (reusing `temporary_image_link_enabled`/`_when_logged_in`/`_when_admin` via `ConfigManager`), wire into `authorize()` so authenticated sessions can bypass the header requirement per config, then add/pass tests: guest with valid signature but private album → 403 (`$signature_check_failed` stays `false`, `PhotoPolicy` denial drives the 403 via T-056-08's override); logged-in user required to still sign per config, no headers → 401 (`$signature_check_failed = true`); admin exempted per config → 200 without headers.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`
  _Notes:_ Completes NFR-056-04's config/caller-state coverage (finished fully in T-056-13).

### I3 – Watermark and S3-redirect branches

- [x] T-056-10 – Failing tests for S-056-15/16 (F-056-06, F-056-07, S-056-15, S-056-16).
  _Intent:_ Add test cases: S3-backed `SizeVariant` → expect 302 with a `Location` header pointing at a temporary S3 URL (mock/fake the S3 disk per this repo's existing S3 test-fixture convention — check `UploadSizeVariantToS3Job`'s test for the fake-disk pattern to reuse); viewer meeting watermark conditions → served bytes match the watermarked file, not the plain one.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`

- [x] T-056-11 – Watermark-aware file resolution (F-056-06).
  _Intent:_ `PhotoAssetController::show()` calls `Watermarker::get_path($size_variant)` instead of the plain `short_path` before resolving the file to stream/redirect.
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`

- [x] T-056-12 – S3 redirect branch (F-056-07).
  _Intent:_ Branch on `getAdapter() instanceof AwsS3V3Adapter` (mirrors `UrlGenerator::getAwsUrl()`); return `redirect()->away($disk->temporaryUrl(...))` for S3-backed variants, evaluated after the `PhotoPolicy` check passes; local-disk path unchanged (streams as before).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  - `make phpstan`
  _Notes:_ S-056-15/16 should now pass.

### I4 – Full scenario-matrix sweep and edge-case hardening

- [x] T-056-13 – Full `signatureRequired()` config/caller-state matrix test (NFR-056-04).
  _Intent:_ Dedicated test enumerating all 2×2×2 combinations of `temporary_image_link_enabled`/`_when_logged_in`/`_when_admin` crossed with (guest / logged-in-non-admin / admin) caller state; document the collapsed expected-outcome truth table in the test file's comments (several combinations collapse to the same result).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`

- [x] T-056-14 – `CAN_SEE` vs `CAN_ACCESS_FULL_PHOTO` split test (F-056-03, S-056-17).
  _Intent:_ Test asserting the same photo/session returns 200 for `THUMB` but 403 for `ORIGINAL` when the album disables full-resolution access (`AlbumPolicy::canAccessFullPhoto()` denies while `canAccess()` allows).
  _Verification commands:_
  - `php artisan test --filter=PhotoAssetV3Test`
  _Notes:_ Full scenario matrix (S-056-01..17) is green after this task.

### I5 – Documentation and quality gate

- [x] T-056-15 – `docs/specs/3-reference/api-design.md` "API v3" section.
  _Intent:_ Document `API-056-01`'s route, `X-Timestamp`/`X-Mac` headers, and response codes (200/302/401/403/404/422); reference ADR-0009's SoA/binary-passthrough precedent.
  _Verification commands:_ N/A (docs-only)

- [x] T-056-16 – `docs/specs/4-architecture/knowledge-map.md` update.
  _Intent:_ Add entries for `routes/api_v3.php`, `PhotoAssetController`, `TemporaryLinkSigner` under the appropriate Backend Application/Domain Layer subsections.
  _Verification commands:_ N/A (docs-only)

- [x] T-056-17 – Implementation Drift Gate diff check (NFR-056-03).
  _Intent:_ Confirm `git diff master -- routes/api_v2.php routes/web_v2.php app/Http/Controllers/SecurePathController.php app/Services/UrlGenerator.php` is empty; record result in plan.md's Implementation Drift Gate section.
  _Verification commands:_
  - `git diff master -- routes/api_v2.php routes/web_v2.php app/Http/Controllers/SecurePathController.php app/Services/UrlGenerator.php`

- [x] T-056-18 – Full quality gate + roadmap update.
  _Intent:_ Run the full PHP quality gate; move Feature 056's roadmap row from Active to Completed.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ `make phpstan` (0 errors/2822 files) and `php-cs-fixer` (clean) both ran full-project and are unqualified passes. The unfiltered `php artisan test` run hits the same pre-existing environment-level timeout/SQLite-lock-contention issue documented for Features 052/055 (confirmed unrelated to this feature: an untouched existing test, `SecureImageLinksTest`, passes cleanly in isolation; a clean low-contention `--testsuite=Unit` run shows exactly one pre-existing unrelated failure class, missing php-ldap extension constants in `LdapServiceTest`). Verified instead via the full `Feature_v3` suite (18/18), `TemporaryLinkSignerTest` (3/3), and `LoginRequiredTest` (touches the same `app/Http/Kernel.php` this feature added one middleware alias to) — all green. Roadmap row moved to Completed.

- [x] T-056-19 *(2026-09-02 amendment, precondition for Feature 063's I14)* – `GetPhotoAssetRequest::isPhotoOfAlbum()`: add a `BaseSmartAlbum` branch (`$album instanceof BaseSmartAlbum && $this->isComputedAlbumThumb($album->get_id())`) mirroring the existing `TagAlbum`/`PersonAlbum` branch immediately above it (FR-056-08).
  _Intent:_ Feature 062's new `/Albums/smart` cover-photo resolution (FR-062-16) depends on this — without it, a cached-but-since-stale smart-album cover would 403 through this endpoint. Feature 063's own I14 tracks it only as a precondition (T-063-42), this is the actual implementation task.
  _Note:_ A second, corollary fix was required to make this branch reachable at all: `rules()` validated `album_id` via `RandomIDRule` instead of `AlbumIDRule` (the rule `GetAlbumRequest`/`GetAlbumHeadRequest` already use for the same purpose), so every smart-album-shaped `album_id` 422'd before ever reaching `isPhotoOfAlbum()` — a pre-existing bug, invisible until now because `TagAlbum`/`PersonAlbum` ids happen to satisfy `RandomIDRule`'s length/charset check too. Swapped to `AlbumIDRule`. Three tests added to `PhotoAssetV3Test`: cached cover still live-matching (200), cached cover no longer live-matching — the actual staleness scenario this branch exists for (200, would have been 403 without it), and an uncached non-matching photo (still 403, proving this isn't a blanket bypass).
  _Verification commands:_ `make phpstan` (0 errors); `vendor/bin/php-cs-fixer fix --dry-run` (clean); `php artisan test --filter=PhotoAssetV3Test` (29/29 green).

## Notes / TODOs
- T-056-10's S3 test-fixture pattern (fake disk) should be reused from an existing S3-aware test (e.g. around `UploadSizeVariantToS3Job`) rather than reinvented — confirm the exact fixture during implementation.
- **(2026-09-02 amendment)** T-056-19 (`isPhotoOfAlbum()`'s new `BaseSmartAlbum` branch) is a small, deliberate exception to this feature's "Completed" status — driven by a dependency from Feature 063's smart-album-root-tiles addendum, not a bug found in this feature's original scope.
