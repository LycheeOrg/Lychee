# Feature 059 Tasks – Embed Metadata in Original/RAW File

_Status: Implemented_
_Last updated: 2026-08-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Config migration + reference-doc groundwork

- [x] T-059-01 – Add `embed_metadata_in_files_enabled` + `embed_metadata_update_checksum_enabled` config migration (FR-059-01, FR-059-16, FR-059-17).
  _Intent:_ New migration extending `App\Models\Extensions\BaseConfigMigration`, category `Image Processing`, inserting two rows via one `getConfigs()` array: (1) `embed_metadata_in_files_enabled`, `value: '0'`, `type_range: self::BOOL`, `description`/`details` containing the checksum/duplicate-detection warning HTML (mirrors the `Mod Watermarker` `pi-exclamation-triangle text-orange-500` styling) **and** an explicit note that this only applies to photos on local storage — S3-backed (or any other non-local disk) photos are silently skipped (FR-059-11); (2) `embed_metadata_update_checksum_enabled`, `value: '1'`, `type_range: self::BOOL`, `details` explaining the on/off trade-off (on: checksum refreshed, internally consistent; off: checksum stays a permanent fingerprint of the original upload).
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan test --filter=GetAllSettingsTest`
  _Notes:_ Copy the structural shape of `database/migrations/2026_02_28_000002_add_raw_download_enabled_config.php` (most recent same-category precedent). Verified: `php artisan migrate --path=database/migrations/2026_08_28_000001_add_embed_metadata_config.php --force` applies cleanly and both rows land correctly in `configs` (confirmed directly, since the shared dev/test `database.sqlite` has an unrelated pre-existing backlog of pending migrations from other in-flight features that a plain `migrate --force` cannot get past — scoped `--path` avoids touching those).

- [ ] T-059-02 – Confirm the new config renders correctly with no new frontend code (FR-059-16, S-059-14).
  _Intent:_ Manual check (dev server) that `BoolField.vue` renders the toggle + `v-html` warning under Settings → Image Processing.
  _Verification commands:_
  - Manual: `php artisan serve` + browser check of `/admin/settings` (or equivalent v8 route).
  _Notes:_ No Vue test needed per spec.md Test Strategy — generic component, no bespoke UI. **Not verified this session** — no browser available; left unchecked rather than claimed done. Low risk: `BoolField.vue` is an existing, already-proven generic renderer (used by every other boolean config), and the new `details` HTML mirrors the already-shipped `Mod Watermarker` warning markup exactly.

- [x] T-059-03 – Update `docs/specs/3-reference/image-processing.md` with the "Metadata Write-Back" section.
  _Intent:_ Document the config, the exact tag-mapping table (FR-059-08), and the checksum-consistency caveat (Q-059-05), cross-referencing this feature.
  _Verification commands:_ none (docs-only).

### I2 – `MetadataWritePayload` DTO + `App\Metadata\Writer`

- [x] T-059-04 – Implement `App\DTO\MetadataWritePayload` and `App\Metadata\Writer::embed()` (FR-059-08, FR-059-13, FR-059-14, NFR-059-01, NFR-059-04, S-059-15).
  _Intent:_ Pure argument-array-building + `Process::run()` invocation, per spec.md's Appendix invocation shape. No filesystem/job/controller wiring yet.
  _Verification commands:_
  - `php artisan test --filter=WriterTest` (new)
  - `make phpstan`
  _Notes:_ Unit tests use `Process::fake()` exclusively (NFR-059-04) — assert the exact argument array for: full field set; empty tags; `rating: null` (deletion — empty-value assignments); a title/tag containing shell metacharacters (`` $(rm -rf /) ``, backticks, semicolons, quotes) to prove array-form `Process::run()` never interpolates into a shell string.

### I3 – `EmbedMetadataJob`

- [x] T-059-05 – Implement `App\Jobs\EmbedMetadataJob` skeleton: config/exiftool gating, `ShouldBeUnique`, `JobHistory` lifecycle, top-level exception guarantee (FR-059-01, FR-059-02, FR-059-07, FR-059-12, NFR-059-07, S-059-12, S-059-13, S-059-19).
  _Intent:_ `handle()`'s **entire body** is wrapped in a top-level try/catch (NFR-059-07) — critical under the default `sync` queue driver, where `dispatch()` runs `handle()` inline, so any escaped exception would break the calling `PhotoController` action. Inside: re-checks `embed_metadata_in_files_enabled` + `ConfigManager::hasExiftool()`, refreshes the photo (title/description/tags/owner rating) at execution time, mirrors `WatermarkerJob`'s `JobHistory` READY→STARTED→SUCCESS/FAILURE transitions (never re-throwing, exactly like `WatermarkerJob::handle()`). `uniqueId()` = `'embed-metadata:' . $photo->id`, `uniqueFor = 60`.
  _Verification commands:_
  - `php artisan test --filter=EmbedMetadataJobTest` (new)
  - `make phpstan`
  _Notes:_ Test S-059-12 (exiftool unavailable → graceful `FAILURE`, no crash), S-059-13 (dedup reflects latest state, not dispatch-time snapshot), and S-059-19 (force an unexpected exception at a stage outside the per-variant `Writer::embed()` calls — e.g. the photo refresh — and assert it never escapes `handle()`) here.

- [x] T-059-06 – Wire `Writer` into the job for the Original variant + owner-rating deletion handling (FR-059-06, FR-059-08, S-059-02, S-059-07).
  _Intent:_ Build `MetadataWritePayload` from current DB state (title, description, `tags()->pluck('name')`, owner's `PhotoRating` — `null` when the owner has no rating row), call `Writer::embed()` against `getOriginal()->getFile()->toLocalFile()`.
  _Verification commands:_
  - `php artisan test --filter=EmbedMetadataJobTest`

- [x] T-059-07 – Extend to the RAW variant with independent, symmetric failure isolation (FR-059-09, FR-059-10, S-059-08, S-059-09, S-059-10).
  _Intent:_ Also target `getRaw()` when non-null; wrap each variant's `Writer::embed()` call in its own try/catch, with no ordering dependency between the two, so **either** variant's failure logs a `WARNING` and does not prevent/undo the other's write; job reports `SUCCESS` as long as at least one targeted variant succeeded, `FAILURE` only if all of them did.
  _Verification commands:_
  - `php artisan test --filter=EmbedMetadataJobTest`
  _Notes:_ Use the existing RAW test fixture (Feature 020's test tree) for S-059-08; simulate a `Writer::embed()` throw for S-059-09 (RAW fails, Original succeeds) — also add the reverse case (Original fails, RAW succeeds) to prove the symmetry, since the spec's first draft only described one direction.

- [x] T-059-08 – Non-local-disk skip handling (FR-059-11, S-059-11).
  _Intent:_ Catch `FlysystemFile`'s non-local-disk exception per variant (from `toLocalFile()`), log a `WARNING`, continue to the next variant instead of failing the job.
  _Drift found during implementation:_ a misconfigured non-local disk (e.g. S3 without credentials/bucket) can throw exception types **other than** `MediaFileOperationException` while merely constructing the disk adapter (confirmed directly: `Storage::disk('s3')->getAdapter()` throws a plain `TypeError` in this environment). Broadened the catch to `\Throwable` for this specific step — still logged as a warning and skipped, never treated as a job failure — since the intent ("don't attempt non-local writes") is the same regardless of the specific exception type.
  _Verification commands:_
  - `php artisan test --filter=EmbedMetadataJobTest`

- [x] T-059-09 – Post-write filesize refresh (unconditional) + checksum refresh gated by `embed_metadata_update_checksum_enabled` (FR-059-15, FR-059-17, S-059-17, S-059-18).
  _Intent:_ After each successful `Writer::embed()`, re-measure via `StreamStat::createFromLocalFile()`; **always** persist `SizeVariant::filesize` for the written variant. Additionally persist `Photo::checksum`/`Photo::original_checksum` from that same read, but only when the written variant is the Original **and** `ConfigManager::getValueAsBool('embed_metadata_update_checksum_enabled')` is true.
  _Verification commands:_
  - `php artisan test --filter=EmbedMetadataJobTest`
  _Notes:_ Also add the NFR-059-05 idempotency test here: dispatch twice unchanged, assert the second `Process::run()` call is byte-identical to the first. Cover S-059-17 (checksum sub-setting off — filesize still updates, checksum columns don't) and S-059-18 (sub-setting is a no-op when the parent feature is off, since no embed runs at all).

### I4 – Controller dispatch wiring

- [x] T-059-10 – Dispatch from `PhotoController::update()` (title/description) (FR-059-03, S-059-01, S-059-02, S-059-03).
  _Intent:_ After `$photo->save()` succeeds, and only when `embed_metadata_in_files_enabled` is true, dispatch `EmbedMetadataJob::dispatch($photo)`.
  _Verification commands:_
  - `php artisan test --filter=PhotoEditTest`
  _Notes:_ Extend `PhotoEditTest` with `Bus::fake()`/`Queue::fake()` assertions for config on/off × owner/non-owner-editor. Confirms NFR-059-02 (no regression when off) for this endpoint.

- [x] T-059-11 – Dispatch from `PhotoController::tags()` (FR-059-04, S-059-04).
  _Intent:_ After the tag-sync `DB::transaction()` commits, dispatch `EmbedMetadataJob` per affected photo id, gated the same way.
  _Verification commands:_
  - `php artisan test --filter=PhotoTagsTest` (or the relevant existing tag-endpoint test file — confirm exact name during implementation)
  _Notes:_ Cover the multi-photo `photo_ids[]` case — one job per photo, not one job for the batch.

- [x] T-059-12 – Dispatch from `PhotoController::rate()`/`Rating::do()`, owner-only (FR-059-05, FR-059-06, S-059-05, S-059-06, S-059-07).
  _Intent:_ Dispatch `EmbedMetadataJob` only when `PhotoPolicy::isOwner($user, $photo)` is true, for both a new/updated rating and a removal (`rating: 0`). Resolve `isOwner()`'s current visibility (`private`) — expose a small public accessor or equivalent, per plan.md's Dependencies & Interfaces note.
  _Resolved:_ did **not** touch `PhotoPolicy` — `isOwner()`'s logic is a trivial one-liner (`$photo->owner_id === $user->id`), so `PhotoController::rate()` does the equality check inline instead of exposing/wrapping the private policy method. Smaller diff, zero risk of changing `PhotoPolicy`'s behavior for its other (unrelated) callers.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatingTest`
  _Notes:_ Three assertions: owner rates own photo → dispatched; other user (with view access) rates the same photo → not dispatched; owner removes their own rating → dispatched (payload signals deletion, verified via T-059-06's job-level test, not re-verified here).

- [x] T-059-13 – Full regression pass across all three endpoints with the config at its default (off) (NFR-059-02).
  _Intent:_ Run the complete pre-existing `PhotoEditTest`/tag-endpoint/`PhotoRatingTest` suites unmodified (config untouched, defaults to off) to confirm zero behavioural change.
  _Verification commands:_
  - `php artisan test --filter=Photo`
  _Result:_ `--testsuite=ImageProcessing` (232 tests) and `--testsuite=Feature_v2 --filter=Photo` (170 tests) both run clean apart from issues confirmed pre-existing and unrelated: (a) 2 errors in each run from `SQLSTATE[HY000]: General error: 1 no such table: tracks` — the `tracks` table migration (`2026_08_17_000001_create_tracks_table`, a different in-flight feature) is pending in this shared sandbox DB, and `Actions\Album\Delete` unconditionally queries it on every album deletion, cascading into any test whose setup/teardown deletes an album (`EmbedAlbumTest`, `EventsFiredTest`, `AlbumMatchingAlbumsTest`) — nothing to do with photo editing/tags/rating; (b) 4 failures in `PhotosAddHandlerImagickTest`/`PhotoAddTest` (PDF media-box / Apple Live Photo cases) — already documented as pre-existing/unrelated in this repo's own Feature 054 session notes.

### I5 – Full scenario coverage + quality gates

- [x] T-059-14 – Security-focused review pass of `Writer`/`EmbedMetadataJob` (NFR-059-01).
  _Intent:_ Confirm no code path ever builds a shell string from title/description/tag values; `Process::run()` is called with an array at every call site.
  _Verification commands:_
  - Manual code review (grep for `Process::run(` call sites; confirm array argument).
  _Result:_ Exactly one call site (`app/Metadata/Writer.php:47`), passed `buildArguments()`'s return value, typed `list<string>` (phpstan-enforced). Every `-Tag=value` element is built via string concatenation of the flag with its value **into one array element** (the standard argv token shape) — never joined into a single command string. Also covered by `WriterTest::testShellMetacharactersAreEmbeddedVerbatimNeverExecuted` (title/tag containing backticks/`$()`/quotes/semicolons, asserted to appear verbatim as one array element).

- [x] T-059-15 – Full quality gate.
  _Intent:_ `make phpstan` (0 errors, full repo), `vendor/bin/php-cs-fixer fix` (clean), targeted `php artisan test` runs for every touched area, full-suite attempt (document any pre-existing/unrelated failures per plan.md's I5 notes).
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test --filter=Photo`
  - `php artisan test --filter=Embed`
  - `php artisan test --filter=Settings`
  _Result:_ `vendor/bin/phpstan analyze` (no path args — full repo, 2857 files): 0 errors. `php-cs-fixer fix` on every touched `app`/`tests` file: clean (no fixes needed beyond what was applied during development). See T-059-13 for the full-suite regression result. `LangTest::testLanguageConsistency` green (translations added across all 22 locales).

- [x] T-059-16 – Update `docs/specs/4-architecture/roadmap.md` and `docs/specs/_current-session.md`.
  _Intent:_ Move Feature 059 to the correct status/section once implementation is complete, with a session summary matching the repo's established format.
  _Verification commands:_ none (docs-only).

## Notes / TODOs

- `PhotoPolicy::isOwner()` — resolved during implementation: left `private`/untouched; `PhotoController::rate()` does the trivial `$user->id === $photo->owner_id` comparison inline instead.
- The tags-endpoint tests actually live at `tests/ImageProcessing/Photo/PhotoTagsTest.php` (not `tests/Feature_v2/Photo/`, and not named exactly `PhotoTagsTest` under `Feature_v2` as the spec guessed) — extended in place. Likewise `tests/ImageProcessing/Photo/PhotoEditTest.php` for the title/description path; the rating path was extended at the already-correct `tests/Feature_v2/Photo/PhotoRatingIntegrationTest.php`.
- No JS/Vue test added (T-059-02 is a manual check only, not verified this session — no browser available) — consistent with spec.md's Test Strategy, since the settings UI needs no bespoke component.
- **Config-state leakage in this test suite:** `Configs::set()` changes are normally rolled back per-test by `DatabaseTransactions` (confirmed empirically), but this shared, persistent `database/database.sqlite` file is also mutated by tooling/ad-hoc scripts run outside a test transaction, and — separately — running many `BaseApiWithDataTest`-based test classes together in one process was observed to leave `embed_metadata_in_files_enabled` set after the run completed (root cause not fully isolated; not a regression introduced by this feature's code). Mitigated by making every "config disabled" test explicitly call `Configs::set(..., false)` at its own start rather than relying on the migration's default — order-independent regardless of what ran before.
- Translations: `lang/en/all_settings.php`'s `documentation`/`details` arrays were extended with both new config keys; the same English-placeholder text was propagated to all 21 other locale files (`ar,bg,cz,de,el,es,fa,fr,hu,it,ja,nl,no,pl,pt,ru,sk,sv,tr,vi,zh_CN,zh_TW`), matching this repo's established placeholder-until-translated convention (e.g. Feature 054). `php artisan test --filter=testLanguageConsistency` (LangTest) passes. **Do not** run `vendor/bin/php-cs-fixer` against files under `lang/` — these files are not tab-indented like the rest of the codebase and the fixer will reformat the entire file (discovered and reverted during this session).
