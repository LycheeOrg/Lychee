# Feature 116 Tasks – Upload Status Query

_Status: Complete_  
_Last updated: 2026-11-18_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-116-01 – Failing unit tests for the entry DTO and the store choice (FR-116-04, FR-116-05, FR-116-06, NFR-116-02, NFR-116-05, S-116-19).  
  _Intent:_ `tests/Unit/DTO/UploadStatusEntryTest.php`: `processing`, `ofPhoto` (same ID → `completed`, other ID → `completed`, duplicate → `duplicate`), `ofThrowable` (skip, resync, moderation, reject → `skipped` + photo ID + message; Lychee exception → `failed` + message; other → `failed` + `"Processing failed"`), `isReadableBy` matrix, array round trip, malformed array → `null`. `tests/Unit/Services/Cache/SharedCacheStoreTest.php`: `storeName()` over (sync/async × file/array/apc/redis/database), `isPerContainer()`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusEntryTest` (red)  
  - `php artisan test --filter=SharedCacheStoreTest` (red)

- [x] T-116-02 – Enum, entry DTO, shared driver list (FR-116-04, FR-116-05, NFR-116-02, NFR-116-05).  
  _Intent:_ `App\Enum\UploadStatus`, `App\DTO\UploadStatusEntry`, `App\Services\Cache\SharedCacheStore`; `WorkerCacheCheck` reads `SharedCacheStore::isPerContainer()`. `photo_id` constructor parameter on `PhotoSkippedException` / `PhotoResyncedException` / `PhotoRejectedException` (the DTO maps it; moved here from T-116-06).  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusEntryTest`, `SharedCacheStoreTest`, `WorkerCacheCheckTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-116-03 – Failing store tests (FR-116-08, FR-116-10, NFR-116-03, S-116-17, S-116-20).  
  _Intent:_ `tests/Unit/Services/UploadStatusStoreTest.php`: record/read round trip under `upload-status:<id>`, TTL 86 400 s, `many()` keeps request order and gives `null` for missing or garbage, throwing store → no exception + one `warning`; on the `database` store with `Lottery::alwaysWin()` a `processing` write deletes expired `upload-status:` rows and keeps live rows and other expired keys; `alwaysLose()` deletes nothing.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusStoreTest` (red)

- [x] T-116-04 – `UploadStatusStore` (FR-116-08, FR-116-10, NFR-116-03).  
  _Intent:_ `App\Services\UploadStatusStore` with `record()`, `many()`, `prune()` on `SharedCacheStore::repository()`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusStoreTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-116-05 – Failing job tests (FR-116-01 … 05, FR-116-09, N1, S-116-02 … 11, S-116-14, S-116-15, S-116-17).  
  _Intent:_ `tests/ImageProcessing/Jobs/UploadStatusJobTest.php` (`BaseApiWithDataTest`): constructor writes `processing` with uploader; async new photo → `completed` with provisional ID; duplicate from another album and in the same album → `duplicate`; `skip_duplicates` → `skipped` + ID + message; resync; moderation copy; `skip_duplicates_not_owned` → `skipped`; `failed(RuntimeException)` → `failed` generic, `failed(BaseLycheeException)` → its message; job without `expected_id` writes nothing; video joined to its still → `completed` with the still's ID (ffmpeg); sync upload → outcome stored when the upload answers; job without provisional ID (ZIP, imports) → no `record()` call. Resync (S-116-06) and throwing store (S-116-17) are covered by the unit tests.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusJobTest` (red)

- [x] T-116-06 – Exceptions, `Create::isDuplicate()`, `ProcessImageJob` recording (FR-116-01 … 05, FR-116-09, DO-116-04).  
  _Intent:_ the three `Duplicate\*` throw pipes pass `photo_id` to their exceptions; `Create::isDuplicate()`; `ProcessImageJob::$uploader_id`, `recordUploadStatus()`, writes in constructor, `handle()`, skip catch, `failed()`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusJobTest`, `SkippedDuplicateJobTest`, `PhotoAddTest`, `UploadWithMetadataTest`, `PhotoUnownedDuplicateTest`, `ProcessImageJobTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-116-07 – Failing endpoint tests (FR-116-06, FR-116-07, NFR-116-01, NFR-116-04, S-116-01, S-116-12, S-116-13, S-116-16, S-116-18).  
  _Intent:_ `tests/Feature_v2/Photo/UploadStatusTest.php`: queued upload (`Queue::fake()`) → `processing`; unknown ID → `unknown`; other user and guest on an authenticated entry → `unknown`; guest entry readable by anyone; duplicate photo not visible to the caller → `photo_id` `null`; batch of three in request order; `422` on malformed, empty, 101 IDs.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusTest` (red)

- [x] T-116-08 – Endpoint (FR-116-06, FR-116-07, API-116-01).  
  _Intent:_ `GetUploadStatusRequest`, `UploadStatusResource`, `ResolveUploadStatuses`, `PhotoController::uploadStatus()`, route; `php artisan typescript:transform`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusTest`, `OpenApiTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `vp check --fix resources/js/lychee.d.ts`  
  - `vp run typecheck`

- [x] T-116-09 – Docs and drift gate.  
  _Intent:_ `docs/specs/3-reference/api-design.md` (route, vocabulary, access, store), knowledge map, roadmap, drift gate in the plan.  
  _Verification commands:_ —

- [x] T-116-10 – Failing tests for the ID without visibility check (FR-116-07, NFR-116-01, S-116-08).  
  _Intent:_ `UploadStatusTest`: other users' photos (shared, public, inaccessible) → IDs given, deleted photo → `null`, total query count of a four-photo batch equal to a one-photo batch; `testInvisibleDuplicateIdIsWithheld` → `testDuplicateIdOfAnotherUsersPhotoIsGiven`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusTest` (red)

- [x] T-116-11 – Primary-key lookup instead of `CAN_SEE` (FR-116-07, NFR-116-01).  
  _Intent:_ `ResolveUploadStatuses::existingPhotoIds()`: `whereIn('id')` plucked through the base query, no hydration, no `Gate`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadStatusTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

## Notes / TODOs
- `php-cs-fixer` on several paths needs `--config=.php-cs-fixer.php --path-mode=intersection`.
- Scope `vp check --fix` to changed files.
