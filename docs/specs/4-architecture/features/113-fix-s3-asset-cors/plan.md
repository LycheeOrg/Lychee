# Feature Plan 113 – Fix S3 Asset CORS

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Complete (operator check pending)  
_Last updated:_ 2026-11-10

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
S3-backed images load in v8 on any bucket, with no CORS rule and no Lychee header sent to the bucket host (NFR-113-01), and without proxying bytes (NFR-113-02). Success: the scoped PHP and Vitest suites cover S-113-01 … 10 and pass; phpstan, php-cs-fixer, `vp check`, typecheck green.

## Scope Alignment
- **In scope:** `ServesSizeVariantAssetTrait` S3 branch (FR-113-01), `AssetUrlResource`, `UrlGenerator::getAwsUrl()` made public, `ThumbAssetService` + `FaceQuickNamingService` JSON handling (FR-113-02), `Thumb` retry (FR-113-03), docs listed in the spec.
- **Out of scope:** local-disk serving, authorization, v2/v7, map and face retry (spec Non-Goals).

## Dependencies & Interfaces
- `App\Services\UrlGenerator::getAwsUrl()` (private today, `@codeCoverageIgnore`), reused for the S3 URL.
- `App\Http\Controllers\Traits\ServesSizeVariantAssetTrait` (shared by `PhotoAssetController` and `FacePhotoAssetController`).
- `resources/js/services/thumb-asset-service.ts`, `face-quick-naming-service.ts`, `resources/js/v8/components/thumbs/Thumb.vue`.
- `php artisan typescript:transform` for `App.Http.Resources.V3.AssetUrlResource`.

## Assumptions & Risks
- **Assumptions:** axios with `responseType: "blob"` gives a `Blob` whose `type` is the response `Content-Type` (`application/json`).
- **Risks / Mitigations:**
  - Some other client consumes `/Asset` expecting a `302` → greenfield stance (AGENTS.md); only Lychee's own front end uses it (grep of `resources/js`).
  - axios-cache-interceptor caching the JSON with an expiring URL → `Cache-Control: no-store` on the response; the interceptor does not store `no-store`.

## Implementation Drift Gate
After I3: re-read FR-113-01 … 03 and NFR-113-01/02 against the diff, rerun the commands below, record the result in this section.

**2026-11-10 (agent) — passed.**

| Requirement | Evidence |
|-------------|----------|
| FR-113-01 | `ServesSizeVariantAssetTrait::responseS3Url()` → `AssetUrlResource` via `UrlGenerator::getAwsUrl()`, `no-store, private`; `PhotoAssetV3Test` S-113-01/02/04 + the two fallback-chain S3 tests, `FacePhotoAssetTest` S-113-03. |
| FR-113-02 | `services/asset-url.ts` used by `ThumbAssetService` and `FaceQuickNamingService`; `revoke()` and `useQuickNamingSession.releasePhoto()` skip non-`blob:` URLs; S-113-06/07/08. |
| FR-113-03 | `Thumb.errorAction()`; S-113-09 (three cases), S-113-10. |
| NFR-113-01 | No `3xx` left on either route; browser check on a real bucket pending (operator). |
| NFR-113-02 | S3 mocks expose no read method; tests green. |

Drift found and fixed during the gate: on a public bucket `/Asset` hands back the same URL as the failed direct URL, so the tile stuck as a broken image (or, with a reset, looped on the fallback). FR-113-03 now matches the `/Asset` URL first and shows the placeholder while asking again; test added before the fix.

Commands rerun: `php artisan test --filter=PhotoAssetV3Test` (52), `FacePhotoAssetTest` (13), `PhotoUrlsV3Test` (23), `AssetMiddlewareV3Test` (18); `vp test run` (36 files, 281 + 1 tests); `vp check --fix` on the changed files; `vp run typecheck`; `make phpstan`; `vendor/bin/php-cs-fixer fix` per changed PHP file.

Lessons: the S3 path was only covered by mocked-disk tests asserting the redirect; nothing exercised the browser side of a cross-origin redirect. An XHR-fetched asset endpoint must never redirect to another origin.

## Increment Map

1. **I1 – Backend S3 URL response (FR-113-01, NFR-113-02)**
   - _Preconditions:_ spec, ADR-113-01.
   - _Steps:_ rewrite S-056-15 test as S-113-01 and add S-113-02, S-113-04 in `PhotoAssetV3Test`, S-113-03 in `FacePhotoAssetTest`; see them fail. Add `AssetUrlResource`; make `UrlGenerator::getAwsUrl()` public; S3 branch of `tryServe()` returns the resource with `no-store`. Run `typescript:transform`.
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `php artisan test --filter=FacePhotoAssetTest`, `php artisan test --filter=PhotoUrlsV3Test`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ tests green, phpstan clean.
2. **I2 – Client JSON handling and Thumb retry (FR-113-02, FR-113-03)**
   - _Preconditions:_ I1 (type generated).
   - _Steps:_ add failing Vitest cases S-113-06 … 10. Add `resources/js/services/asset-url.ts` (`assetResponseToUrl()`, `isObjectUrl()`), use it in both services, guard `revoke()`, add `ThumbAssetService.invalidate()`, add the retry to `Thumb`.
   - _Commands:_ `vp test run resources/js/services resources/js/v8/components/thumbs`, `vp check --fix <changed files>`, `vp run typecheck`.
   - _Exit:_ Vitest green, lint and typecheck clean.
3. **I3 – Docs (Documentation Deliverables)**
   - _Steps:_ Feature 056 amendments, `api-design.md`, knowledge map, roadmap.
   - _Exit:_ docs match the behaviour.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-113-01 | I1 / T-113-01, T-113-02 | `PhotoAssetV3Test` |
| S-113-02 | I1 / T-113-01, T-113-02 | `PhotoAssetV3Test` |
| S-113-03 | I1 / T-113-01, T-113-02 | `FacePhotoAssetTest` |
| S-113-04 | I1 / T-113-01, T-113-02 | `PhotoAssetV3Test` |
| S-113-05 | I1 / T-113-02 | existing local-disk tests |
| S-113-06 | I2 / T-113-03, T-113-04 | `thumb-asset-service.test.ts` |
| S-113-07 | I2 / T-113-03, T-113-04 | `thumb-asset-service.test.ts` |
| S-113-08 | I2 / T-113-03, T-113-04 | `face-quick-naming-service.test.ts` |
| S-113-09 | I2 / T-113-03, T-113-05 | `Thumb.test.ts` |
| S-113-10 | I2 / T-113-03, T-113-05 | `Thumb.test.ts` |

## Analysis Gate
2026-11-10 (agent): spec, plan and tasks agree; Q-113-01 resolved (A) and folded into FR-113-01 … 03 and ADR-113-01; every FR/NFR maps to a scenario and a task; tests precede code in tasks. No open questions.

## Exit Criteria
- Scoped PHP tests and Vitest green; `vendor/bin/php-cs-fixer fix`, `make phpstan`, `vp check --fix` (changed files), `vp run typecheck` clean.
- `php artisan typescript:transform` rerun.
- Docs updated; roadmap row 113 reflects progress.
- Operator check on a real S3/R2 instance (not possible from this environment).

## Follow-ups / Backlog
- Operator browser check on an S3/R2 bucket without CORS.

## Intent Log
- 2026-11-10: user report (R2 CORS error on `/Asset` redirect). Diagnosis from `ServesSizeVariantAssetTrait`, `thumb-asset-service.ts`, `axios-config.ts`, `client-cache.ts`. Q-113-01 logged, owner chose A.
- 2026-11-10: I1 tests first (3 red, denial case green as guard), then `AssetUrlResource` + `responseS3Url()`; two older S3 fallback tests rewritten from `assertRedirect` to the JSON body (behaviour replaced by FR-113-01). `php artisan typescript:transform` reformats `lychee.d.ts` with a narrower width: `vp check --fix resources/js/lychee.d.ts` restores it, leaving only the new type. phpstan rejected `Data::toResponse()` (typed `Response`): built with `response()->json()` instead.
- 2026-11-10: I2 tests first (6 red), then `asset-url.ts`, services, `invalidate()`, `Thumb` retry; drift gate found the public-bucket loop (see gate).
- 2026-11-10: renumbered 112 → 113 (folder, ADR, all IDs): Feature 112 is `112-fix-album-delete-timeout` on branch `delete-albums-timeout` (also on origin). 113 checked free on every local and remote ref.
