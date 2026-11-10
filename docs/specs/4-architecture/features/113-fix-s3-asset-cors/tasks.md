# Feature 113 Tasks – Fix S3 Asset CORS

_Status: Complete (operator check pending)_  
_Last updated: 2026-11-10_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-113-01 – Failing backend tests for the S3 URL response (FR-113-01, S-113-01, S-113-02, S-113-03, S-113-04).  
  _Intent:_ rewrite `testS3BackedVariantRedirectsToTemporaryUrl` as S-113-01, add public-bucket and denied cases to `PhotoAssetV3Test`, add the face-route case to `FacePhotoAssetTest`; confirm they fail on the `302`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoAssetV3Test`  
  - `php artisan test --filter=FacePhotoAssetTest`

- [x] T-113-02 – Backend S3 URL response (FR-113-01, NFR-113-02, S-113-01 … 05).  
  _Intent:_ `AssetUrlResource`, public `UrlGenerator::getAwsUrl()`, S3 branch of `ServesSizeVariantAssetTrait::tryServe()`, `typescript:transform`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoAssetV3Test`  
  - `php artisan test --filter=FacePhotoAssetTest`  
  - `php artisan test --filter=PhotoUrlsV3Test`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-113-03 – Failing Vitest cases (FR-113-02, FR-113-03, S-113-06 … 10).  
  _Intent:_ JSON body cases in `thumb-asset-service.test.ts`, new `face-quick-naming-service.test.ts`, retry cases in `Thumb.test.ts`; confirm they fail.  
  _Verification commands:_  
  - `vp test run resources/js/services resources/js/v8/components/thumbs`

- [x] T-113-04 – Client JSON handling (FR-113-02, S-113-06, S-113-07, S-113-08).  
  _Intent:_ `asset-url.ts` helpers, both services resolve the URL, `revoke()` only for `blob:` URLs, `ThumbAssetService.invalidate()`.  
  _Verification commands:_  
  - `vp test run resources/js/services`  
  - `vp run typecheck`

- [x] T-113-05 – Thumb retry on S3 load error (FR-113-03, S-113-09, S-113-10).  
  _Intent:_ one `/Asset` retry for a non-`blob:` URL, placeholder on a second error.  
  _Verification commands:_  
  - `vp test run resources/js/v8/components/thumbs`  
  - `vp check --fix <changed files>`  
  - `vp run typecheck`

- [x] T-113-06 – Docs (Documentation Deliverables).  
  _Intent:_ Feature 056 amendments (FR-056-07, S-056-15, API-056-01), `api-design.md`, knowledge map, roadmap row 113, plan drift gate.  
  _Verification commands:_ review only.

## Notes / TODOs
- Operator browser check on an S3/R2 bucket without CORS (cannot be done from this environment).
