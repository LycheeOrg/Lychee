# Feature 112 Tasks – Fix Album Delete Timeout

_Status: Implemented (analysis and drift gates passed 2026-11-22)_  
_Last updated: 2026-11-23_

## Checklist

### I1 – `FileDeleterJob` cheap and bounded
- [x] T-112-01 – Failing `FileDeleterJobTest` cases for summary log, `batches()`, and the S3 branch through a mocked non-local disk (one `delete()` per path, no `exists()`, `delete()` false → all paths tried then `MediaFileOperationException`) (FR-112-01..03, S-112-01, S-112-02, S-112-19).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest` (new cases red).

- [x] T-112-02 – Summary log `Processed {n} paths on {disk}.`, S3 delete without `exists()` (drop `@codeCoverageIgnore`), `FileDeleterJob::batches()`; switch the three producers to it, all kinds a producer holds for one disk merged before batching (tracks batched in `Album\Delete`, apart from the photo files of `PhotosToBeDeletedDTO`) (FR-112-01..03).  
  _Verification:_ `php artisan test --filter=FileDeleterJobTest`, `--filter=AlbumDeleteTrackTest`.

### I2 – Deferred dispatch on `sync`
- [x] T-112-03 – Failing `DeleteAfterResponseTest` (Feature_v2): album and photo delete on `sync` → file, person-stats and face-embedding jobs dispatched after response, none inline; on `database` → all queued, none after response (FR-112-04, S-112-03, S-112-04, S-112-18 without the thumb part).  
  _Verification:_ `php artisan test --filter=DeleteAfterResponseTest` (red).

- [x] T-112-04 – `App\Actions\Shared\DispatchAfterDelete` (DO-112-03) used by `Album\Delete::do()`, `Photo\Delete::do()` and `forceDeletePhoto()`, `SizeVariant\Delete::do()` for every post-commit job (FR-112-04).  
  _Verification:_ `php artisan test --filter=DeleteAfterResponseTest`, `--filter=AlbumDeleteTrackTest`, existing photo/album delete tests.

### I3 – `QueryLimits` and shared thumb-target resolver
- [x] T-112-05 – Add `App\Constants\QueryLimits::ID_CHUNK_SIZE = 500` (DO-112-04). Failing `AlbumUserThumbTargetsTest`: smart, tag, person, backward lookup, dedup, empty input, 1200 photo ids with ≤1000 bindings per query (FR-112-08, FR-112-09, S-112-17).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest` (red).

- [x] T-112-06 – Extract `AlbumUserThumbTargets` (`resolve()`, `jobs()`, `dispatch()`) from `RecomputeAlbumUserThumbsOnPhotoChange`: same targets, photo ids chunked, tag/person albums through subqueries, backward lookup as a join from `album_user_thumbs` (photo chunk) to `tag_albums` / `person_albums` (FR-112-08, FR-112-09).  
  _Verification:_ `php artisan test --filter=AlbumUserThumbTargetsTest`, `--filter=RecomputeAlbumUserThumbsJobTest`, `--filter=RecomputeJobDispatchDedupTest`.

### I4 – Remove the per-photo fan-out
- [x] T-112-07 – Failing `PhotoDeleteFanOutTest`: S-112-05..08, wiring assertion on the `PhotoWillBeDeleted` listeners (S-112-09), thumb jobs dispatched after response on `sync` (thumb part of S-112-18), and the `Queue::before` check that they start after the photos are gone (FR-112-05..07).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest` (red).

- [x] T-112-08 – Unsubscribe the two listeners, early return without `photo.delete` webhook, once-per-request thumb refresh in `Photo\Delete::do()` and `forceDeletePhoto()` through `AlbumUserThumbTargets::jobs()` and `DispatchAfterDelete`; update `ManagedCacheAlbumListingInvalidatorTest` (FR-112-05..08).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest`, `--filter=ManagedCacheAlbumListingInvalidatorTest`, `--filter=WebhookListenerTest`, existing photo delete tests, the `album_user_thumbs` suites (`--filter=SharingTest`, `--filter=PeopleControllerTest`, `--filter=FaceDismissTest`, `--filter=AlbumCategoryV3Test`, `--filter=UserGroupTest`).

- [x] T-112-19 – Failing `PhotoDeleteFanOutTest` case S-112-21 (enabled `photo.delete` webhook: one `WebhookDispatchJob` per (photo, webhook) after response on `sync`, queued on `database`, payload unchanged), then `WebhookListener::handlePhotoWillBeDeleted()` hands its jobs to `DispatchAfterDelete` (FR-112-12).  
  _Verification:_ `php artisan test --filter=PhotoDeleteFanOutTest`, `--filter=WebhookListenerTest`.

### I5 – NULL-safe sold-variant exclusion and detach
- [x] T-112-09 – Failing cases: `tests/Unit/Actions/Photo/DeleteTest.php` S-112-13 (URL-delivered order item present → rows gone, files queued) and S-112-14 (sold variant of a photo that is not deleted keeps its `photo_id`); `tests/Unit/Actions/SizeVariant/DeleteTest.php` S-112-15 (sold variant kept, `photo_id` NULL); `tests/ImageProcessing/Photo/PhotoRotateTest.php` S-112-20 (rotate a photo whose original is sold) (FR-112-10).  
  _Verification:_ `php artisan test --filter=DeleteTest`, `--filter=PhotoRotateTest` (new cases red).

- [x] T-112-10 – `NOT EXISTS` on `order_items` in `PhotosToBeDeletedDTO` (path collection) and `SizeVariant\Delete::do()` (path collection, row delete); sold variants detached (`photo_id = NULL`) in both: per force-deleted chunk before its `size_variants` delete, and for the given ids in `SizeVariant\Delete`; correct the `collectSizeVariantPathsByPhotoID()` docblock (FR-112-10).  
  _Verification:_ `php artisan test --filter=DeleteTest`, `--filter=PhotoRotateTest`, shop order tests (`OrderItemDisplayTest` and the `tests/Webshop` classes touching order items).

### I5b – Repair migration
- [x] T-112-20 – Migration `2026_11_22_000001_relink_detached_sold_size_variants.php` (DO-112-05; dated on the day it was written, `2026_11_15_000001` is taken by `add_server_path_to_users`): candidates `size_variants.photo_id IS NULL` referenced by an `order_items` row with `photo_id IS NOT NULL`; per (photo, type) without a current variant, re-link the highest id; others left detached; chunks of 500; counts logged; `down()` no-op. No test (owner rule). Check S-112-22 on the dev DB in two steps (FR-112-13):
  1. Idempotence dry run. A second `php artisan migrate` skips a recorded migration, so call `up()` directly. The migration is DML only, so one transaction holds it. `tinker` is not installed: use a scratch PHP script that boots the app (`vendor/autoload.php`, `bootstrap/app.php`, console kernel `bootstrap()`) and run `DB::beginTransaction()`. Build the fixture: an `order_items` row pointing at a variant of a surviving photo, with that variant set to `photo_id = NULL`; a second one the same way, then give its photo a new variant of that type. Run `$m = require base_path('database/migrations/<file>'); $m->up();`, record `id, photo_id` of both fixture variants, run `$m->up()` again, record them again, then `DB::rollBack()`. Expected: after run 1 the first fixture is re-linked and the second stays detached. Run 2 logs 0 re-linked and leaves both rows as they were. The rollback leaves the data and the `migrations` table untouched.
  2. Real run: `php artisan migrate --path=database/migrations/<file>` (only this migration, other pending migrations of the checkout are left alone), then check the logged counts. Never use `migrate:rollback`, `migrate:fresh`, `migrate:reset` or `db:wipe` to repeat it.  
  _Verification:_ both steps give the expected results; `make phpstan`.  
  _Result 2026-11-22 (dev DB, MySQL):_ dry run: fixture A (sv 16) re-linked, fixture B (sv 22) left detached, the one real candidate (sv 447420, type 1) re-linked; run 2 unchanged ("re-linked 0"); after rollback all rows as before, transaction level 0. Real run: `re-linked 1 sold size variants, 0 left detached.`; no detached sold variant left. The three master migrations pending on the dev DB (`2026_11_15_000001/000002`, `2026_11_19_210546`) were not run.

### I6 – Bounded permission checks
- [x] T-112-11 – Failing `DeletePolicyBindLimitTest` (Feature_v2): 1200 photo ids and 1200 album ids, owner → allowed, one foreign id in the last chunk → denied, delete grant on every parent → allowed, a non-owner in 600 groups (bulk-inserted) with the grant through one group → allowed, ≤1000 bindings per query (FR-112-11, S-112-16).  
  _Verification:_ `php artisan test --filter=DeletePolicyBindLimitTest` (red).

- [x] T-112-12 – Chunk `PhotoPolicy::canDeleteById()` and the `AlbumPolicy` grant helpers (`hasGrantOnParentsById()`, `hasGrantOnAllById()`, `countGrantedById()`): counts summed against the de-duplicated input. `countGrantedById()` matches groups through a `users_user_groups` subquery that binds the user id only (FR-112-11).  
  _Verification:_ `php artisan test --filter=DeletePolicyBindLimitTest`, `--filter=DeleteAlbumsRequestTest`, existing photo/album delete and move suites.

### I7 – Bounded photo-side delete
- [x] T-112-13 – Failing `DeleteBindLimitTest` over HTTP with `DB::listen`: S-112-10 (1200 photos, purchasables, 1200 `order_items`), S-112-11 (subtree of more than 1000 albums and photos, one photo in two sub-albums in different chunks), S-112-12 (more than 1000 regular and tag album ids, through `DELETE /api/v2/Album` and admin `DELETE /api/v2/BulkAlbumEdit`). Fixtures built with bulk inserts (valid nested set) to keep the task within 90 min (FR-112-09, NFR-112-04).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (red).

- [x] T-112-14 – Chunk `Photo\Delete::do()` (3 queries), `dispatchWillBeDeletedEvents()`, `PurchasableService::deleteMulitplePhotoPurchasables()`, the `PhotosToBeDeletedDTO` album/tag-album re-reads; switch both delete DTOs to `QueryLimits::ID_CHUNK_SIZE` and fix their 65 535 comments (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (S-112-10 green), existing photo delete tests.

### I8 – Bounded album-side delete
- [x] T-112-15 – Chunk the `Album\Delete` request-id sites: person/tag album lookups in `do()`, `findAllAlbumsToDelete()`, `getGaps()`, `deleteTagAlbums()`, `deletePersonAlbums()`; de-duplicate album ids before chunking (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (S-112-12 green), `--filter=AlbumDeleteTrackTest`, existing album delete tests.

- [x] T-112-16 – `findAllPhotosToDelete()`: per-photo link counts keyed by photo id and summed across album chunks, totals de-duplicated, sorted merge removed (FR-112-09).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (all green), existing album delete tests.

### I9 – Listener bound
- [x] T-112-17 – Failing 1200-id `PhotoSaved` case in `ManagedCacheAlbumListingInvalidatorTest`, then chunk `evictPersonTagsForPhotos()` (FR-112-09, S-112-17).  
  _Verification:_ `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest`.

### I10 – Docs and quality gate
- [x] T-112-18 – Knowledge map, roadmap, drift gate; release-note lines from the spec's Documentation Deliverables in the PR description, including the best-effort caveat on `sync` (Q-112-10 A); `vendor/bin/php-cs-fixer fix`, `make phpstan`; commit.  
  _Result:_ release-note lines drafted in the plan (Release Note); php-cs-fixer (3 files reformatted), phpstan clean; commit command handed to the owner.

### I11 – PR review round 2 (2026-11-23)
- [x] T-112-21 – `GhostbusterTest::testRemoveZombiePhotos` terminates the app after the command, as the `artisan` binary does, so the deferred `FileDeleterJob`s run (FR-112-04).  
  _Verification:_ `php artisan test --filter=GhostbusterTest`.

- [x] T-112-22 – Failing S-112-23 in `DeleteBindLimitTest` (second chunk's `size_variants` delete throws from `DB::listen`), then one `DB::transaction` around the chunks of `SizeVariant\Delete::do()`. `DeleteBindLimitTest` checks every requested id in chunks of 500 (FR-112-10, S-112-23).  
  _Verification:_ `php artisan test --filter=DeleteBindLimitTest` (red: 0 of 706 rows left, then green), `--filter='Tests\\Unit\\Actions\\SizeVariant\\DeleteTest'`, `--filter=PhotoRotateTest`, `--filter=DeleteAfterResponseTest`.

- [x] T-112-23 – Docs: an async queue does not retry a failed webhook call (open-questions Q-112-10, plan, spec FR-112-12 and release note), `lychee:ghostbuster` walks the active upload disk only (spec FR-112-04, plan, knowledge map), the FR-112-13 command uses `migrate --path` and a scratch script (plan Commands).  
  _Verification:_ re-read against the cited lines.

- [x] T-112-24 – Q-112-11 C: FR-112-10 failure path and Non-Goals state that a sale committed during the delete of the same variant can lose its file (no code change).  
  _Verification:_ re-read spec and open-questions.
- [x] T-112-25 – Q-112-12 A: FR-112-09 failure path and Non-Goals state that the classification is not atomic with a concurrent move (no code change).  
  _Verification:_ re-read spec and open-questions.

## Notes / TODOs
Run test classes one at a time (shared SQLite DB).
