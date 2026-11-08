# Feature Plan 112 – Fix Album Delete Timeout

Linked specification: [spec.md](spec.md). Tasks: [tasks.md](tasks.md). Status: planned. Updated: 2026-11-08.

## Vision & Success Criteria
Deleting a 4000-photo album or a 4000-photo selection returns once the DB transaction commits. File deletion runs in bounded jobs (≤500 paths), after the response on `sync`. A multi-photo delete dispatches each thumb-refresh job once and fires `PhotoWillBeDeleted` only for `photo.delete` webhooks.

## Scope Alignment
Owner chose Q-112-01 A and Q-112-02 A (2026-11-08), amended: thumb jobs are kept and computed once per delete request (FR-112-07/08). Q-112-03 A: a `whereIn` over 1000 parameters fails on MySQL (owner), so every delete-flow query binds ≤1000 (FR-112-09). Non-goals per spec: no queued DB delete, no `photo.delete` webhooks on album delete, no payload change.

## Dependencies & Interfaces
- `App\Jobs\FileDeleterJob` and its producers: `App\Actions\Album\Delete`, `App\DTO\Delete\PhotosToBeDeletedDTO`, `App\Actions\SizeVariant\Delete`.
- `App\Actions\Photo\Delete`, `App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange`, `App\Listeners\ManagedCacheAlbumListingInvalidator`, `App\Listeners\WebhookListener`, `App\Providers\EventServiceProvider`.
- `Webhook::enabled()->forEvent(PhotoWebhookEvent::DELETE)` scopes (existing).
- Laravel `PendingDispatch::afterResponse()` and `Bus::assertDispatchedAfterResponse()` (framework, no new dependency).

## Assumptions & Risks
- The affected-album set must be resolved before the DB delete: `photos_tags`, `faces` and `album_user_thumbs` rows of the deleted photos are gone afterwards (cascade). Jobs are dispatched after it, so on `sync` they cannot re-pick a photo being deleted.
- `afterResponse()` in console context runs on application terminate. Artisan callers (none delete photos today) still complete.
- With `sync`, file I/O after the response still counts against FPM's `request_terminate_timeout`. Accepted (Q-112-01 A cons).
- `album_user_thumbs` rows pointing at a deleted photo are cascaded away; that viewer's thumb is recomputed lazily on next read, as today.

## Increment Map
1. **I1 – `FileDeleterJob` cheap and bounded** (FR-112-01..03, S-112-01/02). Failing unit tests, then summary log, S3 without `exists()`, `batches()`. Producers switched to `batches()`.
2. **I2 – File deletion after response on `sync`** (FR-112-04, S-112-03/04). Failing feature tests, then `DispatchFileDeletion` helper used by the three producers.
3. **I3 – Shared thumb-target resolver** (FR-112-08). Failing `AlbumUserThumbTargetsTest`, extract the resolver from the listener with unchanged behaviour.
4. **I4 – Remove the per-photo fan-out** (FR-112-05..07, S-112-05..09). Failing `PhotoDeleteFanOutTest` and wiring test, then unsubscribe listeners, early-return on no `photo.delete` webhook, once-per-request thumb refresh in `Photo\Delete`.
5. **I5 – Bind-parameter bound** (FR-112-09, NFR-112-04, S-112-10/11). Failing `DeleteBindLimitTest`, then shared `QueryLimits::ID_CHUNK_SIZE = 500`, chunk every listed site, `order_items` exclusion as subquery. The resolver of I3 is written chunked from the start.
6. **I6 – Docs and quality gate**: knowledge map, roadmap, drift gate, php-cs-fixer, phpstan, scoped tests, commit.

## Scenario Tracking
S-112-01/02 → T-112-01/02. S-112-03/04 → T-112-03/04. S-112-05..09 → T-112-07/08. Resolver → T-112-05/06. S-112-10/11 → T-112-09/10.

## Analysis Gate
2026-11-08: PASS.
1. Spec complete; Q-112-01/02/03 folded into FR-112-01..09, NFR-112-04. No UI impact, no mock-up needed.
2. No open entries in open-questions.md. No ADR: local performance fix, no cross-module boundary or contract change.
3. Plan references spec and tasks; success criteria match.
4. Every FR maps to a task; tests precede code in each increment; each increment ≤90 min.
5. Control flow kept flat: batching, dispatch mode and thumb targets live in small helpers. No ADR lists Feature 112.
6. Commands listed below and in tasks.

## Implementation Drift Gate
Pending.

## Commands
- `php artisan test --filter=DeleteBindLimitTest`, `--filter=FileDeleterJobTest`, `--filter=AlbumUserThumbTargetsTest`, `--filter=PhotoDeleteFanOutTest`, `--filter=AlbumDeleteFileDispatchTest`, `--filter=AlbumDeleteTrackTest`, `--filter=ManagedCacheAlbumListingInvalidatorTest`, `--filter=WebhookListenerTest`, plus the existing photo/album delete suites (run one at a time).
- `vendor/bin/php-cs-fixer fix`, `make phpstan`.

## Exit Criteria
All tasks `[x]`, scoped tests green, php-cs-fixer and phpstan clean, roadmap and knowledge map updated, commit made.

## Intent & Tool Usage
Owner asked to read the pulled open questions, then answered "1: A 2: A, however RecomputeAlbumUserThumbsJobs should only be figured once". Owner then asked whether any `whereIn` takes an uncontrolled number of ids and stated that more than 1000 parameters fails on MySQL (Q-112-03). Read `Photo\Delete`, `PhotosToBeDeletedDTO`, `Album\Delete`, `SizeVariant\Delete`, `FileDeleterJob`, `RecomputeAlbumUserThumbsJob`, the three `PhotoWillBeDeleted` listeners and `EventServiceProvider` to place the change.
