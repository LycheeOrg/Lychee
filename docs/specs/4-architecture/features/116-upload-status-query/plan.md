# Feature Plan 116 – Upload Status Query

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Complete (pending CI and operator check)  
_Last updated:_ 2026-11-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
An API client uploads a set of files on a worker-backed instance, then polls `GET /api/v2/Photo::uploadStatus` with the provisional IDs and gets, for each, `processing`, `completed`, `duplicate`, `skipped`, `failed` or `unknown`, with the photo ID to record. Works with the default compose file (`file` cache, worker container) and on `sync`.

## Scope Alignment
- **In scope:** `UploadStatus` enum, `UploadStatusEntry` DTO, shared per-container driver list + store choice, `UploadStatusStore` (record, read, prune), photo ID on skip/reject exceptions, duplicate flag on `Create`, `ProcessImageJob` recording, request/resource/action/route, generated TS types, docs.
- **Out of scope:** ZIP, `fromUrl`, server import, `lychee:sync` (N1, N2), front end (N6), `JobHistory` for rejected photos (N5).

## Dependencies & Interfaces
- `App\Actions\Photo\Create` and `Pipes\Duplicate\*` (throw sites of the three exceptions).
- `ProcessImageJob`, `HasFailedTrait`, `HandlesSkippedPhotoTrait` (ADR-114-01 flow kept).
- Laravel cache: `Cache::store()`, `Illuminate\Cache\DatabaseStore` (`getConnection()`, `getPrefix()`), `cache` table (ADR-090-01), `Illuminate\Support\Lottery`.
- `App\Actions\Diagnostics\Pipes\Checks\WorkerCacheCheck` (driver list moves out).
- `RandomIDRule`, `spatie/laravel-typescript-transformer`.

## Assumptions & Risks
- **Assumptions:** the job constructor runs in the upload request on every queue (it already saves the `JobHistory` row there), so writing `processing` there precedes any outcome write, including on `sync`. On `sync`, a rethrown skip reaches `failed()` (Laravel `SyncQueue::handleException`), which maps the same exception to the same entry. Job payloads queued before the deploy lack `uploader_id`: a defaulted property (`= null`) keeps them loadable.
- **Risks / Mitigations:** the prune query relies on the `database` driver's column names → covered by a test on the real `cache` table. `Lottery` makes pruning random → tests force it with `Lottery::alwaysWin()` / `alwaysLose()`. `typescript:transform` reformats `lychee.d.ts` → run `vp check --fix resources/js/lychee.d.ts` and keep only the new types (Feature 114 lesson). `BaseApiWithDataTest` seeds photos → select uploaded photos by ID.

## Design Notes
- `App\Enum\UploadStatus` (string-backed, `#[TypeScript]`).
- `App\DTO\UploadStatusEntry` (readonly): `processing(?int)`, `ofPhoto(?int, string $photo_id, bool $is_duplicate)`, `ofThrowable(?int, \Throwable)`, `isReadableBy(?int)`, `toArray()` / `fromArray(mixed): ?self`. Pure, no I/O (NFR-116-05).
- `App\Services\Cache\SharedCacheStore`: `PER_CONTAINER_DRIVERS`, `isPerContainer(?string)`, `storeName(string $queue, string $default_store, ?string $driver): string`, `repository(): Repository`. `WorkerCacheCheck` reads `isPerContainer()` (DO-116-05).
- `App\Services\UploadStatusStore`: `KEY_PREFIX = 'upload-status:'`, `TTL = 86400`; `record(string $id, UploadStatusEntry)`, `many(string[] $ids): array<string, ?UploadStatusEntry>`, `prune()` (only on a `DatabaseStore`, `Lottery::odds(1, 50)`, called from `record()` for `processing`). Errors caught, one `warning` each (NFR-116-03).
- Exceptions: `PhotoSkippedException`, `PhotoResyncedException`, `PhotoRejectedException` get a third constructor parameter `?string $photo_id = null` exposed as `public readonly`. Pipes pass `photo_id: $state->photo->id`.
- `Create`: `private bool $is_duplicate`, set in `handleDuplicate()`; `isDuplicate(): bool`.
- `ProcessImageJob`: `public ?int $uploader_id = null` (= `Auth::id()` in the constructor); `recordUploadStatus(UploadStatusEntry)` (no-op without `expected_id`); constructor → `processing`; `handle()` success → `ofPhoto`; skip catch → `ofThrowable` before `skipped()`; `failed()` wraps `HasFailedTrait::failed()` then `ofThrowable`.
- `App\Actions\Photo\ResolveUploadStatuses::do(string[] $ids): Collection<UploadStatusResource>`: one `many()`, one primary-key lookup (`whereIn('id')`, plucked IDs, no model hydrated) for readable entries with a `photo_id`, to drop deleted photos; no visibility check (NFR-116-01, FR-116-07; replaced `Gate::check(CAN_SEE)` per photo on 2026-11-18, T-116-10/11).
- `App\Http\Requests\Photo\GetUploadStatusRequest` (`photo_ids` 1…100, `RandomIDRule(false)` each, `authorize()` true), `App\Http\Resources\Models\UploadStatusResource`, `PhotoController::uploadStatus()`, route `GET /Photo::uploadStatus`.

## Implementation Drift Gate
2026-11-18, agent review after T-116-01 … 08, all listed commands green.

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-116-01 | `ProcessImageJob::__construct()` → `recordUploadStatus(processing)`, `$uploader_id` | `UploadStatusJobTest::testCreationRecordsProcessing`, `UploadStatusTest::testQueuedUploadIsProcessing` |
| FR-116-02 | `handle()` → `UploadStatusEntry::ofPhoto(..., $create->isDuplicate())` | `testNewPhotoIsCompleted`, `testVideoJoinedToItsStillIsCompletedWithTheStillId` |
| FR-116-03 | `Create::$is_duplicate` set in `handleDuplicate()` | `testDuplicateFromAnotherAlbumIsDuplicate`, `testDuplicateInTheSameAlbumIsDuplicate` |
| FR-116-04 | `photo_id` on the three exceptions, passed by `ThrowSkipDuplicate` / `ThrowUntrustedDuplicate` / `ThrowUnownedDuplicate`; skip catch; `ofThrowable()` | `testSkippedDuplicateIsSkipped`, `testDuplicateOfModerationCopyIsSkipped`, `testRejectedDuplicateOfAnotherUserIsSkippedAfterLastAttempt`, `UploadStatusEntryTest` skip provider |
| FR-116-05 | `ProcessImageJob::failed()` wraps `HasFailedTrait::failed()` | `testFinalFailureOnLycheeExceptionKeepsItsMessage`, `testFinalFailureOnOtherThrowableIsGeneric` |
| FR-116-06 | `GetUploadStatusRequest`, `ResolveUploadStatuses`, `UploadStatusResource`, `PhotoController::uploadStatus()`, route | `UploadStatusTest` (13) |
| FR-116-07 | `ResolveUploadStatuses::existingPhotoIds()` (primary-key lookup, no policy) | `testDuplicateIdOfAnotherUsersPhotoIsGiven`, `testVisibleDuplicateIdIsGiven`, `testDeletedPhotoIdIsWithheld`, `testPhotoIdsOfOtherUsersAreGivenInOneQuery` |
| FR-116-08 | `UploadStatusStore::TTL`, `put()` on every write | `UploadStatusStoreTest::testEntryExpiresAfterOneDay`, `testEachWriteResetsTheExpiry` |
| FR-116-09 | constructor runs before the inline job on `sync` | `testSyncUploadStoresTheOutcome` |
| FR-116-10 | `UploadStatusStore::mayPrune()`, `TransactionSafeDatabaseStore::forgetExpired()` | `testProcessingWriteOnDatabaseStorePrunesExpiredUploadStatuses`, `testLostLotteryDoesNotPrune`, `testOutcomeWriteDoesNotPrune` |
| NFR-116-01 | one `many()`, one primary-key lookup | `testBatchAnswersInRequestOrder`, `testPhotoIdsOfOtherUsersAreGivenInOneQuery` (query counts) |
| NFR-116-02 | `SharedCacheStore` (shared `PER_CONTAINER_DRIVERS`, `WorkerCacheCheck` reads it) | `SharedCacheStoreTest`, `testAsyncQueueOnArrayCacheWritesToTheDatabaseStore`, `WorkerCacheCheckTest` |
| NFR-116-03 | `record()` try/catch + `warning` | `testCacheErrorIsLoggedNotThrown` |
| NFR-116-04 | `UploadStatusEntry::isReadableBy()` | `testUserEntryIsHiddenFromOthers`, `testUserEntryIsHiddenFromGuests`, `testGuestEntryIsReadableByAnyone` |
| NFR-116-05 | pure `UploadStatusEntry`, `SharedCacheStore::storeName()` | `UploadStatusEntryTest`, `SharedCacheStoreTest` |

Findings: none high/medium. Low, fixed in place: S-116-06 (resync) cannot happen on upload, so it is tested at mapping level only; S-116-17 is covered by the store's unit test (the job only calls `record()`); the prune query lives in `TransactionSafeDatabaseStore::forgetExpired()` (Lychee's own subclass) because Laravel keeps the table name protected; `ResolveUploadStatuses` does not eager-load `albums`, since `Album::$with` pulls cover photos (5 photo queries instead of 1), and `PhotoPolicy::canSee()` loads albums only for photos the caller does not own. **Superseded 2026-11-18 (owner review):** that left a lazy `albums` load (with `Album::$with` covers and access permissions) per photo the caller does not own, the exact `duplicate`/`skipped` case, and the query-count test only used the caller's own photos. Fixed by T-116-10/11: no visibility check on `photo_id` (owner directive), one primary-key lookup for deleted photos. Lessons: `BaseApiTest::getJson()` already adds `/api/v2/`, so a prefixed URL falls through to the HTML catch-all and answers `406`.

Pending: CI on MySQL/PostgreSQL (prune `like` + `expiration` on the real `cache` table); operator check with a Docker worker on `CACHE_STORE=file`.

## Increment Map
1. **I1 – Pure helpers** (T-116-01, T-116-02)
   - _Steps:_ failing `UploadStatusEntryTest`, `SharedCacheStoreTest`; then enum, DTO, `SharedCacheStore`, `WorkerCacheCheck` on the shared list.
   - _Commands:_ `php artisan test --filter=UploadStatusEntryTest`, `--filter=SharedCacheStoreTest`, `--filter=WorkerCacheCheckTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
2. **I2 – Store** (T-116-03, T-116-04)
   - _Steps:_ failing `UploadStatusStoreTest` (round trip, TTL, `many()` order, throwing store logs, prune on `database` store keeps live rows and other keys, no prune on other stores); then `UploadStatusStore`.
   - _Commands:_ `php artisan test --filter=UploadStatusStoreTest`, php-cs-fixer, phpstan.
3. **I3 – Recording in the job** (T-116-05, T-116-06)
   - _Steps:_ failing `UploadStatusJobTest` (S-116-02 … 11, 14, 15, 17); then exceptions, pipes, `Create::isDuplicate()`, `ProcessImageJob`.
   - _Commands:_ `php artisan test --filter=UploadStatusJobTest`, `--filter=SkippedDuplicateJobTest`, `--filter=PhotoAddTest`, `--filter=UploadWithMetadataTest`, `--filter=PhotoUnownedDuplicateTest`, `--filter=ProcessImageJobTest`, php-cs-fixer, phpstan.
4. **I4 – Endpoint** (T-116-07, T-116-08)
   - _Steps:_ failing `UploadStatusTest` (Feature_v2: S-116-01, 12, 13, 16, 18, FR-116-07); then request, resource, action, controller method, route, `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=UploadStatusTest`, `--filter=OpenApiTest`, php-cs-fixer, phpstan, `vp check --fix resources/js/lychee.d.ts`, `vp run typecheck`.
5. **I5 – Docs** (T-116-09)
   - _Steps:_ `api-design.md`, knowledge map, roadmap, drift gate.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-116-01 | I4 / T-116-07, T-116-08 | `Queue::fake()` upload, then query |
| S-116-02 … 11 | I3 / T-116-05, T-116-06 | job with fake queue interactions; S-116-06 mapping only (`UploadStatusEntryTest`), uploads never resync |
| S-116-12, 13, 16, 18 | I4 / T-116-07, T-116-08 | `UploadStatusTest` |
| S-116-14, 15 | I3 / T-116-05, T-116-06 | sync upload, ZIP upload |
| S-116-17 | I2 / T-116-03, T-116-04 | throwing store in `UploadStatusStoreTest`; the job only calls `record()`, which catches |
| S-116-19 | I1 / T-116-01, T-116-02 | `SharedCacheStoreTest` |
| S-116-20 | I2 / T-116-03, T-116-04 | `UploadStatusStoreTest` |

## Analysis Gate
2026-11-18, agent review:
1. Spec completeness — pass: goals, FR-116-01 … 10, NFR-116-01 … 05, scenarios S-116-01 … 20; Q-116-01 … 05 folded into normative sections. No UI change (N6), so no mock-up.
2. Open questions — pass: none open; ADR-116-01 written (access, store, cleanup). ADR-090-01 and ADR-114-01 reviewed as related context.
3. Plan alignment — pass: links to spec and tasks; success criteria match G1 … G4.
4. Tasks coverage — pass: FR-116-01 … 05, 09 → T-116-05/06; FR-116-06, 07 → T-116-07/08; FR-116-08, 10 → T-116-03/04; NFR-116-02 → T-116-01/02. Failing tests precede each implementation task. Success (completed/duplicate), validation (`422`, unreadable entry) and failure (failed, throwing store) branches listed.
5. Working agreements — pass: no dependency change, no migration (the `cache` table exists). Branching kept in pure helpers (`UploadStatusEntry`, `SharedCacheStore::storeName()`); the job gets one call per outcome.
6. Tooling — pass: commands listed per increment.

## Exit Criteria
- All tasks `[x]`; listed test filters green; `php-cs-fixer`, `make phpstan`, `vp check` (changed files), `vp run typecheck` clean.
- `lychee.d.ts` regenerated; docs updated; drift gate recorded.

## Intent Log
- 2026-11-18: owner shared discussion #4991 (comment 19691947, six outcomes). Owner directives: cache provisional ID + status for one day, the job writes the outcome, an endpoint reads by provisional ID. Code read: `PhotoController::upload/process`, `ProcessImageJob`, `HandlesSkippedPhotoTrait`, `HasFailedTrait`, `Create::add/handleDuplicate`, `Duplicate\*` pipes, `FindDuplicate`, `WorkerCacheCheck`, `HasSharedUniqueLockTrait`, ADR-090-01. Owner chose Q-116-01 … 04 A and Q-116-05 C.

## Follow-ups / Backlog
- v8 upload panel showing final outcomes of queued files (N6).
- `PhotoRejectedException` on asynchronous queues still retries and records `FAILURE` (N5).

## Follow-up 2026-11-18 – no visibility check on `photo_id`
- Owner review: `Gate::check(CAN_SEE)` per photo is too heavy (`canSee()` lazy-loads `albums` with `Album::$with` covers and access permissions for every photo the caller does not own), and the code comment claimed the opposite.
- Owner directive: revealing the ID of a duplicate is fine. The uploader already has the file, the `duplicate`/`skipped` status already says it exists, and every photo route applies its own policy. FR-116-07 and NFR-116-01 reworded, S-116-08 now answers the other user's photo ID.
- Kept: a photo deleted since answers `null`, through one primary-key lookup for the batch (not ruled on, existing behaviour).
- First draft of the fix used `PhotoQueryPolicy::applyVisibilityFilter` (one query, also withholding photos awaiting moderation); dropped after the directive, before any commit.
- Found while writing T-116-10: `Model::shouldBeStrict()` is unconditional (`AppServiceProvider`), so `canSee()`'s lazy `albums` load threw `LazyLoadingViolationException` for any batch of two or more photos holding one the caller does not own: `500` in production, not only slow. The old tests used one such photo at a time, which Laravel allows.
- Tasks T-116-10 (failing tests) and T-116-11 (fix). Verified: `UploadStatusTest` 14/14, `make phpstan` clean, `php-cs-fixer` on the three touched files. `testInvisibleDuplicateIdIsWithheld` becomes `testDuplicateIdOfAnotherUsersPhotoIsGiven` (requirement changed, not a red test silenced).
