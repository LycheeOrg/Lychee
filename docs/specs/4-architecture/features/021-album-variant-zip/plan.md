# Feature Plan 021 – Album Variant ZIP Download

_Linked specification:_ `docs/specs/4-architecture/features/021-album-variant-zip/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-28

## Vision & Success Criteria

Allow users to download album ZIPs containing any supported size variant (MEDIUM, SMALL, THUMB, etc.) instead of only ORIGINALs. Success is measured by:
- Album ZIP downloads accept and honour an optional `variant` parameter.
- Frontend provides a modal for variant selection before album download.
- Backward compatibility: omitting `variant` produces the same result as before.
- All existing tests continue to pass; new tests cover variant threading.

## Scope Alignment

- **In scope:**
  - Backend: Thread `DownloadVariantType` through `AlbumController::getArchive()` → `BaseArchive::do()` → `compressAlbum()` → `compressPhotosFromCollection()`.
  - Backend: Update `ZipRequest` validation to accept `variant` for album downloads.
  - Frontend: New `DownloadAlbum.vue` modal with variant selection.
  - Frontend: Update `AlbumService.download()` to accept and pass variant.
  - Frontend: Wire album download buttons (hero, panel, context menu) to open modal.
  - Tests: Unit test for updated `ZipRequest` rules; feature test for album ZIP with variant.

- **Out of scope:**
  - Photo-level ZIP changes (already supports variant).
  - Mixed-variant ZIPs.
  - Download progress reporting.

## Dependencies & Interfaces

- `DownloadVariantType` enum — existing, no changes needed.
- `SizeVariantType` enum — existing, used via `getSizeVariantType()`.
- `ZipRequest` — existing request class, minor rule change.
- `BaseArchive` / `Archive64` / `Archive32` — existing archive classes, signature change.
- `AlbumService.download()` — existing frontend method, parameter addition.
- `DownloadPhoto.vue` — reference for building `DownloadAlbum.vue`.
- LycheeState store — existing download-gating flags.

## Assumptions & Risks

- **Assumptions:**
  - Photos always have an ORIGINAL size variant (safe fallback target).
  - Eager loading of a specific size variant type is efficient (already done for photo ZIP in `ZipRequest::processPhotos()`).
- **Risks / Mitigations:**
  - Smart albums paginate photos → variant must be threaded through paginator path too. Mitigation: `compressPhotosFromPaginator` delegates to `compressPhotosFromCollection`, so fixing the latter covers both.

## Increment Map

### I1 – Backend: Thread variant through album archive pipeline (~45 min)

_Goal:_ `BaseArchive::do()` accepts an optional `DownloadVariantType` and uses it when compressing photos.

_Steps:_
1. Update `BaseArchive::do()` signature to accept `?DownloadVariantType $variant = null`.
2. Pass `$variant` through `compressAlbum()` → `compressPhotosFromPaginator()` → `compressPhotosFromCollection()`.
3. In `compressPhotosFromCollection()`, replace hardcoded `getOriginal()->getFile()` with variant-aware lookup: attempt `getSizeVariant($variant->getSizeVariantType())`, fall back to `getOriginal()` if null.
4. Default to `DownloadVariantType::ORIGINAL` when `$variant` is null.

_Commands:_ `php artisan test --filter=ZipRequestTest`, `make phpstan`  
_Exit:_ `BaseArchive` accepts and honours variant parameter; PHPStan clean.

### I2 – Backend: Update controller & request validation (~20 min)

_Goal:_ `AlbumController::getArchive()` passes the variant to album archive; `ZipRequest` accepts optional variant for album downloads.

_Steps:_
1. In `ZipRequest::rules()`, change `variant` rule from `required_if_accepted:photos_ids` to `sometimes` so it's accepted for both album and photo requests.
2. In `AlbumController::getArchive()`, pass `$request->sizeVariant()` to `AlbumBaseArchive::resolve()->do()`.
3. Update `ZipRequestTest::testRules()` to reflect the changed rule.

_Commands:_ `php artisan test --filter=ZipRequestTest`, `make phpstan`  
_Exit:_ Controller threads variant; validation allows optional variant for albums; test green.

### I3 – Frontend: Update AlbumService & create DownloadAlbum modal (~40 min)

_Goal:_ Frontend provides variant selection for album downloads.

_Steps:_
1. Update `AlbumService.download()` to accept an optional `variant` parameter and include it in the URL.
2. Create `DownloadAlbum.vue` modal (modeled after `DownloadPhoto.vue`) that lists downloadable variant types based on LycheeState config flags.
3. Wire modal: clicking a variant calls `AlbumService.download(album_ids, variant)` and closes the modal.

_Commands:_ `npm run check`, `npm run format`  
_Exit:_ Modal renders with variant buttons; download URL includes variant parameter.

### I4 – Frontend: Wire download buttons to modal (~30 min)

_Goal:_ Album download entry points open the new modal instead of immediately downloading.

_Steps:_
1. Update `AlbumHero.vue` download button to open `DownloadAlbum.vue` modal.
2. Update `AlbumPanel.vue` context menu download callback to open the modal.
3. Update context menu composable if needed to support modal trigger.
4. Handle multi-album selection: pass selected album IDs to modal.

_Commands:_ `npm run check`, `npm run format`  
_Exit:_ All album download entry points route through the modal.

### I5 – Tests & quality gate (~30 min)

_Goal:_ All tests pass, static analysis clean, formatting clean.

_Steps:_
1. Update `ZipRequestTest` for new validation rule.
2. Run full quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`.

_Commands:_ Full quality gate commands.  
_Exit:_ All quality gates pass.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-021-01 | I1 / T-021-01 | Default variant = ORIGINAL |
| S-021-02 | I1 / T-021-01 | Variant with fallback |
| S-021-03 | I2 / T-021-02 | RAW gating (existing authorize()) |
| S-021-04 | I2 / T-021-02 | RAW variant allowed |
| S-021-05 | I1 / T-021-01 | Multi-album variant threading |
| S-021-06 | I3 / T-021-04 | Frontend config-gated variant list |
| S-021-07 | I4 / T-021-05 | Context menu → modal |

## Exit Criteria

- [ ] Album ZIP downloads honour optional `variant` parameter.
- [ ] Omitting `variant` produces identical results to pre-feature behaviour.
- [ ] Frontend modal shows only enabled variants.
- [ ] All existing tests pass; new/updated tests cover variant threading.
- [ ] PHPStan 0 errors, php-cs-fixer clean, npm check/format clean.
- [ ] Roadmap and knowledge map updated.

## Follow-ups / Backlog

- Eager loading optimisation: add size-variant-specific eager loading in `ZipRequest::processAlbums()` when variant is specified.
- Consider adding download size estimates to the modal (requires pre-computation).
