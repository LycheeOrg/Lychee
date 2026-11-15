# Feature Plan 112 – Fix Album Delete Timeout

Linked specification: [spec.md](spec.md). Tasks: [tasks.md](tasks.md). Status: planned, analysis gate passed. Updated: 2026-11-15.

## Vision & Success Criteria
Deleting a 4000-photo album or a 4000-photo selection returns once the DB transaction commits. File deletion runs in bounded jobs (≤500 paths). On `sync`, the jobs the delete code starts (files, person stats, face embeddings, thumbs) and the delete webhooks run after the response. A multi-photo delete dispatches each thumb-refresh job once and fires `PhotoWillBeDeleted` only for `photo.delete` webhooks. No query of a delete request, permission checks included, binds more than 1000 parameters. Sold size variants are kept with NULL-safe SQL and detached in every delete path, so rotating a photo with a sale works. A migration re-links the sold variants the old update detached from photos that still exist.

## Scope Alignment
- Owner chose Q-112-01 A and Q-112-02 A (2026-11-08), amended: thumb jobs are kept and computed once per delete request (FR-112-07/08).
- Q-112-03 A: a `whereIn` over 1000 parameters fails on MySQL (owner), so every delete-flow query binds ≤1000 (FR-112-09).
- Q-112-04 A (owner 2026-11-15): fix the NULL-unsafe `order_items` exclusion and the instance-wide detach in this feature (FR-112-10).
- Q-112-05 A (owner 2026-11-15): the delete permission checks are bounded too (FR-112-11).
- Q-112-06 A (owner 2026-11-15): one helper, `DispatchAfterDelete`, defers every job the delete actions dispatch after the commit (files, person stats, face embeddings, thumbs) on `sync` (FR-112-04, FR-112-07). Jobs dispatched by other event listeners are unchanged.
- Q-112-07 A (owner 2026-11-15): `WebhookListener` hands its delete webhook jobs to `DispatchAfterDelete` (FR-112-12).
- Q-112-08 A (owner 2026-11-15): `SizeVariant\Delete` detaches sold variants like the force delete does, so `Rotate` works for photos with sales (FR-112-10).
- Q-112-09 A (owner 2026-11-15): a data migration re-links sold variants detached from photos that still exist (FR-112-13).
- Non-goals per spec: no queued DB delete, no `photo.delete` webhooks on album delete, no payload change, no protection for free-text `download_link` targets, no fix for files shared by same-checksum duplicates (docblock corrected only), no change to FK re-enabling in `AlbumsToBeDeletedDTO`.

## Dependencies & Interfaces
- `App\Jobs\FileDeleterJob` and its producers: `App\Actions\Album\Delete`, `App\DTO\Delete\PhotosToBeDeletedDTO`, `App\Actions\SizeVariant\Delete`.
- `App\Actions\Photo\Delete`, `App\DTO\Delete\AlbumsToBeDeletedDTO`, `App\Actions\Shop\PurchasableService`.
- `App\Policies\PhotoPolicy::canDeleteById()`, `App\Policies\AlbumPolicy` grant helpers (`hasGrantOnParentsById()`, `hasGrantOnAllById()`, `countGrantedById()`, shared with the move-by-id checks).
- `App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange`, `App\Listeners\ManagedCacheAlbumListingInvalidator`, `App\Listeners\WebhookListener`, `App\Providers\EventServiceProvider`.
- `Webhook::enabled()->forEvent(PhotoWebhookEvent::DELETE)` scopes (existing), `App\Jobs\WebhookDispatchJob` (HTTP call, 10 s timeout, `tries = 1`).
- `App\Actions\Photo\Rotate` (caller of `SizeVariants::deleteAll()` then `SizeVariant::create()`; no code change) and `size_variants` `unique(photo_id, type)`.
- Other entry points listed in the spec's API Routes / Services (admin `BulkAlbumEdit`, import pipes, NSFW block action, `Photo::delete()`, `CleanupOrphanedPersonAlbumsJob`).
- `order_items.size_variant_id` semantics: set only by `OrderCompletedListener::fullfillOrder()` for MEDIUM / MEDIUM2X / ORIGINAL photo items; NULL for URL-delivered, album and unfulfilled items (Q-112-04). `order_items.photo_id` is set to NULL by the FK when the photo is deleted (used by FR-112-13).
- Laravel `PendingDispatch::afterResponse()` and `Bus::assertDispatchedAfterResponse()` (framework, no new dependency).

## Assumptions & Risks
- The affected-album set must be resolved before the DB delete: `photos_tags`, `faces` and `album_user_thumbs` rows of the deleted photos are gone afterwards (cascade). Jobs are dispatched after it, so on `sync` they cannot re-pick a photo being deleted.
- `lychee:sync` deletes photos and albums from the console (Import `DeleteMissingPhotos` / `DeleteMissingAlbums`). `afterResponse()` there runs on `$app->terminate()` when the command exits, so deferred jobs pile up in memory for the whole run (bounded by paths) and run at the end. Accepted.
- Each after-response job runs inside `rescue(report: true)`: a failing batch is reported and the others still run. This is framework behaviour (`Dispatcher::dispatchAfterResponse()`), read in the vendor code and not re-tested here.
- On `sync`, person counts and smart/tag/person thumbs are refreshed a moment after the response; a read issued right after it can still see the old values (accepted, Q-112-06 A).
- HTTP feature tests call the kernel's `terminate()` inside the request helper, so after-response jobs run before the test's assertions. Unit tests that call an action directly do not terminate the app; they observe deferred jobs only through `Bus::fake` (`assertDispatched` also counts after-response dispatches). No existing direct-call test asserts deleted files or these jobs.
- With `sync`, file I/O after the response still counts against FPM's `request_terminate_timeout`. Accepted (Q-112-01 A cons).
- `album_user_thumbs` rows pointing at a deleted photo are cascaded away; that viewer's thumb is recomputed lazily on next read, as today.
- FR-112-11 changes the `AlbumPolicy` helpers that the move-by-id checks share. Their results must stay identical; existing move and delete request tests guard it.
- FR-112-10 changes shop data behaviour: sold variants of photos that are not deleted keep their `photo_id` (today they lose it), and `SizeVariant\Delete` detaches sold variants instead of keeping them on the photo (Q-112-08 A). After a rotate, the buyer downloads the pre-rotation file.
- FR-112-13 is a data repair on shop data. No test (owner rule): it is run on the dev DB before the commit (S-112-22). Lychee runs migrations on update, so the repair happens on upgrade. Rows blocked by `unique(photo_id, type)` stay detached, with row and file kept.
- FR-112-12: a delete webhook is still sent when the DB delete then fails, as today (the event fires before the delete).

## Increment Map
1. **I1 – `FileDeleterJob` cheap and bounded** (FR-112-01..03, S-112-01/02/19). Failing unit tests (S3 through a mocked disk), then summary log, S3 without `exists()`, `batches()`. Producers switched to `batches()`.
2. **I2 – Deferred dispatch on `sync`** (FR-112-04, S-112-03/04/18). Failing `DeleteAfterResponseTest`, then `DispatchAfterDelete` used by the three producers for every post-commit job.
3. **I3 – `QueryLimits` and shared thumb-target resolver** (DO-112-04, FR-112-08, FR-112-09 resolver part, S-112-17). Failing `AlbumUserThumbTargetsTest`, then `QueryLimits::ID_CHUNK_SIZE` and the resolver extracted from the listener: same targets, chunked, tag/person through subqueries, backward lookup as a join filtered on the photo chunk.
4. **I4 – Remove the per-photo fan-out** (FR-112-05..07, FR-112-12, S-112-05..09, S-112-21). Failing `PhotoDeleteFanOutTest`, then unsubscribe listeners, early return without a `photo.delete` webhook, once-per-request thumb refresh in `Photo\Delete`, its jobs handed to `DispatchAfterDelete` (`AlbumUserThumbTargets::jobs()`). Then `WebhookListener::handlePhotoWillBeDeleted()` hands its jobs to the same helper.
5. **I5 – NULL-safe sold-variant exclusion and detach** (FR-112-10, S-112-13..15, S-112-20). Failing tests in the photo and size-variant `DeleteTest`s and `PhotoRotateTest`, then `NOT EXISTS` in `PhotosToBeDeletedDTO` and `SizeVariant\Delete`, sold variants detached in both (force-deleted chunk, given variant ids), `collectSizeVariantPathsByPhotoID()` docblock corrected.
6. **I5b – Repair migration** (FR-112-13, S-112-22). Migration written, run on the dev DB, re-linked and skipped counts checked. No test (owner rule).
7. **I6 – Bounded permission checks** (FR-112-11, S-112-16). Failing `DeletePolicyBindLimitTest`, then chunked `PhotoPolicy::canDeleteById()` and `AlbumPolicy` grant helpers.
8. **I7 – Bounded photo-side delete** (FR-112-09, S-112-10). Failing `DeleteBindLimitTest` (all three cases), then chunk `Photo\Delete`, `PurchasableService`, the `PhotosToBeDeletedDTO` re-reads, and switch both DTOs to `QueryLimits`.
9. **I8 – Bounded album-side delete** (FR-112-09, S-112-11/12, through `DELETE /api/v2/Album` and admin `DELETE /api/v2/BulkAlbumEdit`). `Album\Delete` request-id sites, then `findAllPhotosToDelete()` with keyed, summed counts.
10. **I9 – Listener bound** (FR-112-09, S-112-17). `evictPersonTagsForPhotos()` chunked.
11. **I10 – Docs and quality gate**: knowledge map, roadmap, drift gate, php-cs-fixer, phpstan, scoped tests, commit.

## Scenario Tracking
S-112-01/02/19 → T-112-01/02. S-112-03/04/18 → T-112-03/04 (thumb part of S-112-18 → T-112-07/08). S-112-17 (resolver) → T-112-05/06. S-112-05..09 → T-112-07/08. S-112-13..15, S-112-20 → T-112-09/10. S-112-22 → T-112-20. S-112-16 → T-112-11/12. S-112-10..12 → T-112-13..16. S-112-17 (listener) → T-112-17. S-112-21 → T-112-19.

## Analysis Gate
2026-11-08: PASS.
1. Spec complete; Q-112-01/02/03 folded into FR-112-01..09, NFR-112-04. No UI impact, no mock-up needed.
2. No open entries in open-questions.md. No ADR: local performance fix, no cross-module boundary or contract change.
3. Plan references spec and tasks; success criteria match.
4. Every FR maps to a task; tests precede code in each increment; each increment ≤90 min.
5. Control flow kept flat: batching, dispatch mode and thumb targets live in small helpers. No ADR lists Feature 112.
6. Commands listed below and in tasks.

2026-11-15: REOPENED, not passed. The review logged Q-112-04..06. Q-112-04 A and Q-112-05 A are folded into FR-112-09..11, NFR-112-04, S-112-10..17. Review corrections that needed no decision are also folded in: summed counts in `findAllPhotosToDelete()`, subqueries in the resolver, console assumption, `QueryLimits` moved to I3, T-112-10 split. No ADR for Q-112-04/05: Q-112-04 is a local bug fix on lines already rewritten, and Q-112-05 keeps the permission semantics and only changes the query shape. Gate stays blocked until Q-112-06 is answered.

2026-11-15 (after Q-112-06 A): PASS.
1. Spec completeness: PASS. Goals, FR-112-01..11, NFR-112-01..04 populated; Q-112-01..06 folded into normative sections (FR-112-04/07/08 and DO-112-02/03 for Q-112-06). No UI impact, no mock-up needed.
2. Open questions: PASS. No `Open` row in open-questions.md. No ADR needed (local performance and bug fixes, permission semantics and contracts unchanged); no ADR lists Feature 112.
3. Plan alignment: PASS. Plan links spec and tasks; dependencies and success criteria match the spec wording.
4. Tasks coverage: PASS after one fix. FR-112-02 had no test: added S-112-19 (mocked S3 disk) to T-112-01, and the S3 branch drops `@codeCoverageIgnore`. Every FR now maps to tasks (FR-01..03 → T-01/02, FR-04 → T-03/04/08, FR-05..07 → T-07/08, FR-08 → T-05/06, FR-09 → T-05/06/13..17, FR-10 → T-09/10, FR-11 → T-11/12). Each code task follows its failing test. Failure branches covered: denied permission (S-112-16), S3 `delete()` false (S-112-19), sold variants kept (S-112-14/15). FR-112-04's failure path is framework behaviour (see Assumptions), not re-tested. T-112-13 is the largest (three bulk fixtures); fixtures use bulk inserts to stay within 90 min.
5. Working agreements: PASS. Spec first, tests first, no new dependency, no fallback or shim. Branching lives in small helpers (`batches()`, `DispatchAfterDelete`, `AlbumUserThumbTargets`, `QueryLimits`).
6. Tooling: PASS. Commands below; results recorded here.

2026-11-15 (second review): REOPENED. Q-112-07 (delete webhooks still inline on `sync`), Q-112-08 (sold variants kept attached in `SizeVariant\Delete` vs `unique(photo_id, type)` on rotate), Q-112-09 (repair rows detached by the old update) are open. Lightweight corrections queued with them: Goal 2 wording (listener jobs), FR-112-03 batching per disk, resolver backward lookup as a join, other delete entry points in the spec (admin `BulkAlbumEdit`, import, NSFW, `Photo::delete()`), extra verification suites (rotate, `album_user_thumbs` tests). Gate blocked until the three are answered.

2026-11-15 (after Q-112-07..09 A): PASS.
1. Spec completeness: PASS. FR-112-01..13, NFR-112-01..04, S-112-01..22. Q-112-07 → FR-112-12, Q-112-08 → FR-112-10, Q-112-09 → FR-112-13, DO-112-05. The queued corrections are applied: Goal 2 wording, FR-112-03 merges kinds per disk before batching, resolver backward lookup as a join, all entry points listed, re-run suites named, new namespace noted. No UI impact.
2. Open questions: PASS. No `Open` row. No ADR: dispatch timing, a bug fix and a data repair inside one feature, no contract change; no ADR lists Feature 112.
3. Plan alignment: PASS. Vision, scope, dependencies and risks updated for Q-112-07..09.
4. Tasks coverage: PASS. FR-112-10 → T-112-09/10 (rotate case added), FR-112-12 → T-112-19, FR-112-13 → T-112-20 (manual check, no test per owner rule). Every other FR keeps its tasks. Tests precede code (T-112-19 writes its failing case first). T-112-20 is a single migration.
5. Working agreements: PASS. No dependency, no fallback. The migration follows the owner rule (no migration test, run on the dev DB, never `migrate:fresh`).
6. Tooling: PASS. Commands updated below.

## Implementation Drift Gate
Pending.

## Commands
- One at a time (shared SQLite DB): `php artisan test --filter=FileDeleterJobTest`, `--filter=AlbumDeleteTrackTest`, `--filter=DeleteAfterResponseTest`, `--filter=AlbumUserThumbTargetsTest`, `--filter=RecomputeAlbumUserThumbsJobTest`, `--filter=PhotoDeleteFanOutTest`, `--filter=ManagedCacheAlbumListingInvalidatorTest`, `--filter=WebhookListenerTest`, `--filter=DeleteTest` (photo, size-variant and album unit tests), `--filter=DeletePolicyBindLimitTest`, `--filter=DeleteAlbumsRequestTest`, `--filter=DeleteBindLimitTest`, `--filter=PhotoRotateTest`, the `album_user_thumbs` suites (`--filter=SharingTest`, `--filter=PeopleControllerTest`, `--filter=FaceDismissTest`, `--filter=AlbumCategoryV3Test`, `--filter=UserGroupTest`), plus the existing photo/album delete and move suites and the shop order tests.
- FR-112-13: `php artisan migrate` on the dev DB, then check the logged counts (never `migrate:fresh`, `migrate:reset` or `db:wipe`).
- `vendor/bin/php-cs-fixer fix`, `make phpstan`.

## Exit Criteria
All tasks `[x]`, scoped tests green, php-cs-fixer and phpstan clean, roadmap and knowledge map updated, commit made.

## Intent & Tool Usage
- 2026-11-08: Owner asked to read the pulled open questions, then answered "1: A 2: A, however RecomputeAlbumUserThumbsJobs should only be figured once". Owner then asked whether any `whereIn` takes an uncontrolled number of ids and stated that more than 1000 parameters fails on MySQL (Q-112-03). Read `Photo\Delete`, `PhotosToBeDeletedDTO`, `Album\Delete`, `SizeVariant\Delete`, `FileDeleterJob`, `RecomputeAlbumUserThumbsJob`, the three `PhotoWillBeDeleted` listeners and `EventServiceProvider` to place the change.
- 2026-11-15: Owner asked for a review of the feature (improvements, clarifications, ambiguities). Read the delete actions, both delete DTOs, `PurchasableService`, `PhotoPolicy`/`AlbumPolicy` delete checks, the delete requests, the shop fulfilment code (`OrderCompletedListener`, `OrderService`, `markAsDelivered`), the `order_items` / `size_variants` migrations, Laravel's `Dispatcher::dispatchAfterResponse()` and `Application::terminate()`. Checked on SQLite that `NOT IN (…, NULL)` and `NOT IN (SELECT …)` return no row while `NOT EXISTS` does. Owner asked whether URL-delivered order items affect `size_variant_id` (yes: permanently NULL, Q-112-04 context corrected), then answered "4: A 5: A", then "A" for Q-112-06. Checked in vendor code that `MakesHttpRequests::call()` terminates the kernel (after-response jobs run in HTTP tests) and that `BusFake::assertDispatched()` counts after-response dispatches. Analysis gate re-run: PASS after adding S-112-19. Owner asked to analyze again: found delete webhooks still inline, sold variants kept attached breaking `Rotate` on `unique(photo_id, type)`, and rows detached by the old update (Q-112-07..09), plus six small corrections. Checked that size-variant file names are random per photo and regeneration (no path reuse when deferring), that delete-flow listeners and jobs bind no id list, and that `DeleteBulkAlbumRequest` checks only the admin flag. Owner answered "7: A 8: A 9: A". Gate: PASS.
