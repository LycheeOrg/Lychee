# Feature 021 Tasks – Album Variant ZIP Download

_Status: Complete_  
_Last updated: 2026-02-28_

## Checklist

### I1 – Backend: Thread variant through album archive pipeline

- [x] T-021-01 – Update `BaseArchive::do()` to accept optional `DownloadVariantType` (FR-021-01, FR-021-02, S-021-01, S-021-02).  
  _Intent:_ Add `?DownloadVariantType $variant = null` parameter to `do()`, thread through `compressAlbum()`, `compressPhotosFromPaginator()`, and `compressPhotosFromCollection()`. Replace hardcoded `getOriginal()->getFile()` with variant-aware lookup with ORIGINAL fallback.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Default to `DownloadVariantType::ORIGINAL` when null. Fallback to ORIGINAL when requested variant missing for a photo.

### I2 – Backend: Update controller & request validation

- [x] T-021-02 – Update `ZipRequest` rules to accept optional variant for album downloads (FR-021-01, S-021-03, S-021-04).  
  _Intent:_ Change `variant` rule from `required_if_accepted:photos_ids` to `sometimes` + enum validation so it works for both album and photo requests.  
  _Verification commands:_  
  - `php artisan test --filter=ZipRequestTest`  
  - `make phpstan`  

- [x] T-021-03 – Update `AlbumController::getArchive()` to pass variant to album archive (FR-021-01, S-021-05).  
  _Intent:_ Pass `$request->sizeVariant()` to `AlbumBaseArchive::resolve()->do()`.  
  _Verification commands:_  
  - `make phpstan`  

### I3 – Frontend: AlbumService & DownloadAlbum modal

- [x] T-021-04 – Update `AlbumService.download()` to accept variant parameter (FR-021-01).  
  _Intent:_ Add optional `variant` parameter to `download()` and include it as query string in URL.  
  _Verification commands:_  
  - `npm run check`  

- [x] T-021-05 – Create `DownloadAlbum.vue` modal (FR-021-05, S-021-06).  
  _Intent:_ New modal component listing available download variants, filtered by LycheeState config flags. Clicking a variant triggers `AlbumService.download()` with chosen variant.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  

### I4 – Frontend: Wire download buttons to modal

- [x] T-021-06 – Wire `AlbumHero.vue` download button to open modal (FR-021-05, FR-021-07, S-021-07).  
  _Intent:_ Replace direct `AlbumService.download()` call with modal trigger.  
  _Verification commands:_  
  - `npm run check`  

- [x] T-021-07 – Wire `AlbumPanel.vue` context menu download to open modal (FR-021-06, FR-021-07).  
  _Intent:_ Context menu download callbacks open the download modal for album downloads.  
  _Verification commands:_  
  - `npm run check`  

### I5 – Tests & quality gate

- [x] T-021-08 – Update `ZipRequestTest` for new validation rules (FR-021-01).  
  _Intent:_ Update `testRules()` to expect `sometimes` instead of `required_if_accepted:photos_ids` for variant.  
  _Verification commands:_  
  - `php artisan test --filter=ZipRequestTest`  

- [x] T-021-09 – Run full quality gate.  
  _Intent:_ All checks pass.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test`  
  - `make phpstan`  

## Notes / TODOs

- The `compressPhotosFromPaginator()` delegates to `compressPhotosFromCollection()`, so fixing the latter automatically covers paginated smart album downloads.
- `LIVEPHOTOVIDEO` is not meaningful for album ZIP downloads (maps to `null` SizeVariantType) — the modal should not offer it.
- Eager loading optimisation for album ZIP with specific variant is a follow-up (not blocking).
