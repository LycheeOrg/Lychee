# Feature 102 Tasks – Asset Endpoint Speedup

_Status: Draft_  
_Last updated: 2026-10-21_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Lean `/Asset` authorization
- [x] T-102-01 – Query-count and status tests for `/Asset` (F-102-02, S-102-01, S-102-02, S-102-03).  
  _Intent:_ Failing tests: ≤6 queries (config query excluded) for owner, shared user and public guest on a regular album; admin ≤3 queries, 200 for an unknown album id and for a photo outside the album, 401 when `temporary_image_link_when_admin` requires a signature; photo not in album stays 403 (non-admin) and a missing size variant stays 404 under the new query shape.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoAssetV3Test`

- [x] T-102-02 – Lean album resolution in `GetPhotoAssetRequest` (F-102-02, S-102-01, S-102-02, S-102-03).  
  _Intent:_ Album with `base_class` + `access_permissions` only; membership as an `exists` column of the size-variant query (regular albums); admins skip the album read and the membership check.  
  _Verification commands:_  
  - `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test'`  
  - `make phpstan`

### I2 – Route-group middleware and partial configs
- [ ] T-102-03 – Tests for middleware placement and partial configs (F-102-01, F-102-03, F-102-07, S-102-04, S-102-05).  
  _Intent:_ Failing tests: `/Asset` and the faces photo route read no `license_key`/`email` and run one key-restricted `configs` query; a v2 and a v3 JSON endpoint still resolve configs, verify and locale.  
  _Verification commands:_  
  - `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`

- [ ] T-102-04 – Record the config keys read by both binary routes (F-102-03).  
  _Intent:_ Temporarily record `ConfigManager` reads across `PhotoAssetV3Test`, `AssetMiddlewareV3Test` and the faces photo tests; write the list into `ResolveAssetConfigs`; remove the recorder.  
  _Verification commands:_  
  - `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`

- [ ] T-102-05 – Move `ResolveConfigs`/`ResolveVerify`/`SetLocale` into the route groups (F-102-07, S-102-04).  
  _Intent:_ First entries of `web`, `web-admin`, `api`; `ResolveConfigs` in `web-install` and log-viewer's API middleware; `image/{path}` keeps `ResolveConfigs`.  
  _Verification commands:_  
  - `php artisan test --filter='ResolveConfigsTest|ResolveVerifyTest|SetLocaleTest|AssetMiddlewareV3Test'`  
  - `make phpstan`

- [ ] T-102-06 – `ConfigManager::loadOnly()` and `ResolveAssetConfigs` on both binary routes (F-102-01, F-102-03, F-102-07, S-102-04, S-102-05).  
  _Intent:_ Partial load, scoped `ConfigManager` + `Watermarker` bindings, route `withoutMiddleware()` lists.  
  _Verification commands:_  
  - `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`  
  - `make phpstan`

### I3 – HTTP caching
- [ ] T-102-07 – HTTP caching tests (F-102-04, S-102-06, S-102-07).  
  _Intent:_ Failing tests: `Cache-Control: private, max-age=3600` and `Last-Modified` on 200; 304 with empty body when `If-Modified-Since` ≥ mtime; 200 when older; 401/403/404 unchanged.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoAssetV3Test`

- [ ] T-102-08 – Cache headers and 304 in `ServesSizeVariantAssetTrait` (F-102-04, S-102-06, S-102-07).  
  _Verification commands:_  
  - `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`  
  - `make phpstan`

### I4 – URL tier backend
- [ ] T-102-09 – `PhotoUrlsV3Test` (F-102-05, S-102-08 … S-102-12).  
  _Intent:_ Failing tests: direct URLs; fallback chain; `null` without rows; temporary link required → `null`; secure links → `null`; watermark path; S3 URL; ids outside the album dropped; 0 or >300 ids → 422; struct-of-arrays off, guest private → 401, other user → 403; unknown album → 404; smart album and `timeline`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoUrlsV3Test`

- [ ] T-102-10 – `GetPhotoUrlsRequest`, route and `PhotoChildrenController::urls()` (F-102-05, S-102-11).  
  _Verification commands:_  
  - `php artisan test --filter=PhotoUrlsV3Test`  
  - `make phpstan`

- [ ] T-102-11 – `QueryPhotoUrls`, the direct-or-null helper and `PhotoUrlResource` (F-102-05, S-102-08, S-102-09, S-102-10, S-102-12).  
  _Verification commands:_  
  - `php artisan test --filter=PhotoUrlsV3Test`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`

- [ ] T-102-12 – Regenerate TypeScript types (F-102-05).  
  _Verification commands:_  
  - `php artisan typescript:transform`  
  - `vp run typecheck`

### I5 – Front-end URL loader
- [ ] T-102-13 – Vitest for the tile URL loader (F-102-06, S-102-13).  
  _Intent:_ Failing tests: dedup of resolved and in-flight ids, ≤300 ids per request, stale-album guard, non-empty all-`null` response disables, error → `null`, reset on reload.  
  _Verification commands:_  
  - `vp test` (scoped to the loader test)

- [ ] T-102-14 – `PhotoChildrenV3Service.getUrls()` and the loader, provided from `AlbumPanel.vue` and `Timeline.vue` (F-102-06, S-102-13).  
  _Verification commands:_  
  - `vp test` (scoped)  
  - `vp check --fix`  
  - `vp run typecheck`

### I6 – Grid wiring and `Thumb`
- [ ] T-102-15 – Vitest for the visible-id composable (F-102-06, S-102-13).  
  _Intent:_ Failing tests: ids of rendered chunks, stable key, loader called once per new id set.  
  _Verification commands:_  
  - `vp test` (scoped)

- [ ] T-102-16 – Visible-id composable in `PhotoGridVirtual.vue`; tiles pass their URL to `Thumb` (F-102-06, S-102-13, S-102-14).  
  _Verification commands:_  
  - `vp test` (scoped)  
  - `vp check --fix`  
  - `vp run typecheck`

- [ ] T-102-17 – Vitest for `Thumb` (F-102-06, S-102-14).  
  _Intent:_ Failing tests: string → direct `src` and no `ThumbAssetService` call; `undefined` → placeholder; `null` → `ThumbAssetService`; image `error` → `ThumbAssetService`.  
  _Verification commands:_  
  - `vp test` (scoped)

- [ ] T-102-18 – `Thumb` direct `src` with `/Asset` fallback (F-102-06, S-102-14).  
  _Verification commands:_  
  - `vp test` (scoped)  
  - `vp check --fix`  
  - `vp run typecheck`

### I7 – Verification and docs
- [ ] T-102-19 – Browser check, harness rerun, docs (NFR-102-01, S-102-14).  
  _Intent:_ Scratch instance: album, timeline, smart album; unprotected and temporary-link instance; network panel. Record numbers in the plan; update knowledge map, roadmap, Feature 056 cross-references.  
  _Verification commands:_  
  - `.claude/skills/run-lychee/scratch.sh serve` + driver script  
  - harness: `loop.php`, `run.sh`, `payload.php`

## Notes / TODOs
- None.
