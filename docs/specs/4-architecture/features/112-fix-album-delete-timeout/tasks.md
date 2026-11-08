# Feature 112 Tasks – Fix Album Delete Timeout

_Status: Planned_  
_Last updated: 2026-11-08_

## Checklist
- [ ] T-112-01 – Failing `FileDeleterJobTest` cases for summary log and `batches()` (FR-112-01, FR-112-03, S-112-01, S-112-02).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest` (new cases red).

- [ ] T-112-02 – Summary log, S3 delete without `exists()`, `FileDeleterJob::batches()`; switch the three producers to it (FR-112-01..03).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest`, `--filter=AlbumDeleteTrackTest`.

- [ ] T-112-03 – Failing `AlbumDeleteFileDispatchTest`: `sync` → after response, `database` → queued (FR-112-04, S-112-03, S-112-04).  
  _Verification:_ `php artisan test --filter=AlbumDeleteFileDispatchTest` (red).

- [ ] T-112-04 – `DispatchFileDeletion` helper used by `Album\Delete`, `Photo\Delete`, `SizeVariant\Delete` (FR-112-04).  
  _Verification:_ `php artisan test --filter=AlbumDeleteFileDispatchTest`, `--filter=AlbumDeleteTrackTest`.

- [ ] T-112-05 – Failing `AlbumUserThumbTargetsTest`: smart, tag, person, backward lookup, dedup, empty input (FR-112-08).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest` (red).

- [ ] T-112-06 – Extract `AlbumUserThumbTargets` from `RecomputeAlbumUserThumbsOnPhotoChange` with unchanged behaviour (FR-112-08).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest`, `--filter=RecomputeAlbumUserThumbs`.

- [ ] T-112-07 – Failing `PhotoDeleteFanOutTest` and wiring assertion (FR-112-05..07, S-112-05..09).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest` (red).

- [ ] T-112-08 – Unsubscribe the two listeners, early return without `photo.delete` webhook, once-per-request thumb refresh in `Photo\Delete::do()` and `forceDeletePhoto()`; update `ManagedCacheAlbumListingInvalidatorTest` (FR-112-05..07).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest`, `--filter=ManagedCacheAlbumListingInvalidatorTest`, `--filter=WebhookListenerTest`, existing photo delete tests.

- [ ] T-112-09 – Failing `DeleteBindLimitTest`: 1200 selected photos with purchasables and 1200 `order_items`, album with >1000 photos and sub-albums; `DB::listen` asserts ≤1000 bindings per query (FR-112-09, NFR-112-04, S-112-10, S-112-11).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (red).

- [ ] T-112-10 – `QueryLimits::ID_CHUNK_SIZE`, chunk every FR-112-09 site, `order_items` exclusion as subquery (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest`, `--filter=AlbumDeleteTrackTest`, existing photo/album/size-variant delete and shop tests.

- [ ] T-112-11 – Knowledge map, roadmap, drift gate; `vendor/bin/php-cs-fixer fix`, `make phpstan`; commit.

## Notes / TODOs
Run test classes one at a time (shared SQLite DB).
