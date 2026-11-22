# Feature 112 Tasks – Fix Album Delete Timeout

_Status: Planned (analysis gate passed 2026-11-22)_  
_Last updated: 2026-11-22_

## Checklist

### I1 – `FileDeleterJob` cheap and bounded
- [ ] T-112-01 – Failing `FileDeleterJobTest` cases for summary log, `batches()`, and the S3 branch through a mocked non-local disk (one `delete()` per path, no `exists()`, `delete()` false → all paths tried then `MediaFileOperationException`) (FR-112-01..03, S-112-01, S-112-02, S-112-19).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest` (new cases red).

- [ ] T-112-02 – Summary log `Processed {n} paths on {disk}.`, S3 delete without `exists()` (drop `@codeCoverageIgnore`), `FileDeleterJob::batches()`; switch the three producers to it, all kinds of one disk (variant, watermarked, live-photo, track files) merged before batching (FR-112-01..03).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest`, `--filter=AlbumDeleteTrackTest`.

### I2 – Deferred dispatch on `sync`
- [ ] T-112-03 – Failing `DeleteAfterResponseTest` (Feature_v2): album and photo delete on `sync` → file, person-stats and face-embedding jobs dispatched after response, none inline; on `database` → all queued, none after response (FR-112-04, S-112-03, S-112-04, S-112-18 without the thumb part).  
  _Verification:_ `php artisan test --filter=DeleteAfterResponseTest` (red).

- [ ] T-112-04 – `App\Actions\Shared\DispatchAfterDelete` (DO-112-03) used by `Album\Delete::do()`, `Photo\Delete::do()` and `forceDeletePhoto()`, `SizeVariant\Delete::do()` for every post-commit job (FR-112-04).  
  _Verification:_ `php artisan test --filter=DeleteAfterResponseTest`, `--filter=AlbumDeleteTrackTest`, existing photo/album delete tests.

### I3 – `QueryLimits` and shared thumb-target resolver
- [ ] T-112-05 – Add `App\Constants\QueryLimits::ID_CHUNK_SIZE = 500` (DO-112-04). Failing `AlbumUserThumbTargetsTest`: smart, tag, person, backward lookup, dedup, empty input, 1200 photo ids with ≤1000 bindings per query (FR-112-08, FR-112-09, S-112-17).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest` (red).

- [ ] T-112-06 – Extract `AlbumUserThumbTargets` (`resolve()`, `jobs()`, `dispatch()`) from `RecomputeAlbumUserThumbsOnPhotoChange`: same targets, photo ids chunked, tag/person albums through subqueries, backward lookup as a join from `album_user_thumbs` (photo chunk) to `tag_albums` / `person_albums` (FR-112-08, FR-112-09).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest`, `--filter=RecomputeAlbumUserThumbsJobTest`, `--filter=RecomputeJobDispatchDedupTest`.

### I4 – Remove the per-photo fan-out
- [ ] T-112-07 – Failing `PhotoDeleteFanOutTest`: S-112-05..08, wiring assertion on the `PhotoWillBeDeleted` listeners (S-112-09), thumb jobs dispatched after response on `sync` (thumb part of S-112-18), and the `Queue::before` check that they start after the photos are gone (FR-112-05..07).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest` (red).

- [ ] T-112-08 – Unsubscribe the two listeners, early return without `photo.delete` webhook, once-per-request thumb refresh in `Photo\Delete::do()` and `forceDeletePhoto()` through `AlbumUserThumbTargets::jobs()` and `DispatchAfterDelete`; update `ManagedCacheAlbumListingInvalidatorTest` (FR-112-05..08).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest`, `--filter=ManagedCacheAlbumListingInvalidatorTest`, `--filter=WebhookListenerTest`, existing photo delete tests, the `album_user_thumbs` suites (`--filter=SharingTest`, `--filter=PeopleControllerTest`, `--filter=FaceDismissTest`, `--filter=AlbumCategoryV3Test`, `--filter=UserGroupTest`).

- [ ] T-112-19 – Failing `PhotoDeleteFanOutTest` case S-112-21 (enabled `photo.delete` webhook: one `WebhookDispatchJob` per (photo, webhook) after response on `sync`, queued on `database`, payload unchanged), then `WebhookListener::handlePhotoWillBeDeleted()` hands its jobs to `DispatchAfterDelete` (FR-112-12).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest`, `--filter=WebhookListenerTest`.

### I5 – NULL-safe sold-variant exclusion and detach
- [ ] T-112-09 – Failing cases: `tests/Unit/Actions/Photo/DeleteTest.php` S-112-13 (URL-delivered order item present → rows gone, files queued) and S-112-14 (sold variant of a photo that is not deleted keeps its `photo_id`); `tests/Unit/Actions/SizeVariant/DeleteTest.php` S-112-15 (sold variant kept, `photo_id` NULL); `tests/ImageProcessing/Photo/PhotoRotateTest.php` S-112-20 (rotate a photo whose original is sold) (FR-112-10).  
  _Verification:_ `php artisan test --filter=DeleteTest`, `--filter=PhotoRotateTest` (new cases red).

- [ ] T-112-10 – `NOT EXISTS` on `order_items` in `PhotosToBeDeletedDTO` (path collection) and `SizeVariant\Delete::do()` (path collection, row delete); sold variants detached (`photo_id = NULL`) in both: per force-deleted chunk before its `size_variants` delete, and for the given ids in `SizeVariant\Delete`; correct the `collectSizeVariantPathsByPhotoID()` docblock (FR-112-10).  
  _Verification:_ `php artisan test --filter=DeleteTest`, `--filter=PhotoRotateTest`, shop order tests (`OrderItemDisplayTest` and the `tests/Webshop` classes touching order items).

### I5b – Repair migration
- [ ] T-112-20 – Migration `2026_11_15_000001_relink_detached_sold_size_variants.php` (DO-112-05): candidates `size_variants.photo_id IS NULL` referenced by an `order_items` row with `photo_id IS NOT NULL`; per (photo, type) without a current variant, re-link the highest id; others left detached; chunks of 500; counts logged; `down()` no-op. No test (owner rule). Check S-112-22 on the dev DB in two steps (FR-112-13):
  1. Idempotence dry run. A second `php artisan migrate` skips a recorded migration, so call `up()` directly. The migration is DML only, so one transaction holds it. Open `php artisan tinker` and run `DB::beginTransaction()`. Build the fixture: an `order_items` row pointing at a variant of a surviving photo, with that variant set to `photo_id = NULL`; a second one the same way, then give its photo a new variant of that type. Run `$m = require base_path('database/migrations/<file>'); $m->up();`, record `id, photo_id` of both fixture variants, run `$m->up()` again, record them again, then `DB::rollBack()`. Expected: after run 1 the first fixture is re-linked and the second stays detached. Run 2 logs 0 re-linked and leaves both rows as they were. The rollback leaves the data and the `migrations` table untouched.
  2. Real run: `php artisan migrate`, then check the logged counts. Never use `migrate:rollback`, `migrate:fresh`, `migrate:reset` or `db:wipe` to repeat it.  
  _Verification:_ both steps give the expected results; `make phpstan`.

### I6 – Bounded permission checks
- [ ] T-112-11 – Failing `DeletePolicyBindLimitTest` (Feature_v2): 1200 photo ids and 1200 album ids, owner → allowed, one foreign id in the last chunk → denied, delete grant on every parent → allowed, a non-owner in 600 groups (bulk-inserted) with the grant through one group → allowed, ≤1000 bindings per query (FR-112-11, S-112-16).  
  _Verification:_ `php artisan test --filter=DeletePolicyBindLimitTest` (red).

- [ ] T-112-12 – Chunk `PhotoPolicy::canDeleteById()` and the `AlbumPolicy` grant helpers (`hasGrantOnParentsById()`, `hasGrantOnAllById()`, `countGrantedById()`): counts summed against the de-duplicated input. `countGrantedById()` matches groups through a `users_user_groups` subquery that binds the user id only (FR-112-11).  
  _Verification:_ `php artisan test --filter=DeletePolicyBindLimitTest`, `--filter=DeleteAlbumsRequestTest`, existing photo/album delete and move suites.

### I7 – Bounded photo-side delete
- [ ] T-112-13 – Failing `DeleteBindLimitTest` over HTTP with `DB::listen`: S-112-10 (1200 photos, purchasables, 1200 `order_items`), S-112-11 (subtree of more than 1000 albums and photos, one photo in two sub-albums in different chunks), S-112-12 (more than 1000 regular and tag album ids, through `DELETE /api/v2/Album` and admin `DELETE /api/v2/BulkAlbumEdit`). Fixtures built with bulk inserts (valid nested set) to keep the task within 90 min (FR-112-09, NFR-112-04).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (red).

- [ ] T-112-14 – Chunk `Photo\Delete::do()` (3 queries), `dispatchWillBeDeletedEvents()`, `PurchasableService::deleteMulitplePhotoPurchasables()`, the `PhotosToBeDeletedDTO` album/tag-album re-reads; switch both delete DTOs to `QueryLimits::ID_CHUNK_SIZE` and fix their 65 535 comments (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (S-112-10 green), existing photo delete tests.

### I8 – Bounded album-side delete
- [ ] T-112-15 – Chunk the `Album\Delete` request-id sites: person/tag album lookups in `do()`, `findAllAlbumsToDelete()`, `getGaps()`, `deleteTagAlbums()`, `deletePersonAlbums()`; de-duplicate album ids before chunking (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (S-112-12 green), `--filter=AlbumDeleteTrackTest`, existing album delete tests.

- [ ] T-112-16 – `findAllPhotosToDelete()`: per-photo link counts keyed by photo id and summed across album chunks, totals de-duplicated, sorted merge removed (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (all green), existing album delete tests.

### I9 – Listener bound
- [ ] T-112-17 – Failing 1200-id `PhotoSaved` case in `ManagedCacheAlbumListingInvalidatorTest`, then chunk `evictPersonTagsForPhotos()` (FR-112-09, S-112-17).  
  _Verification:_ `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest`.

### I10 – Docs and quality gate
- [ ] T-112-18 – Knowledge map, roadmap, drift gate; release-note lines from the spec's Documentation Deliverables in the PR description, including the best-effort caveat on `sync` (Q-112-10 A); `vendor/bin/php-cs-fixer fix`, `make phpstan`; commit.

## Notes / TODOs
Run test classes one at a time (shared SQLite DB).
