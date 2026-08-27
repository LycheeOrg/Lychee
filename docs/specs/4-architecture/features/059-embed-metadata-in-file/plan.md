# Feature Plan 059 – Embed Metadata in Original/RAW File

_Linked specification:_ `docs/specs/4-architecture/features/059-embed-metadata-in-file/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-27

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

A photographer who edits a photo's title, description, or tags in Lychee — or rates their own upload — sees that same metadata reflected in the actual file on disk, so it survives export/download and is visible to any external tool that reads EXIF/IPTC/XMP. Success is: (1) all four field-edit paths reliably embed metadata when the feature is enabled, (2) the feature is fully invisible (zero behavioural/perf change) when disabled (the default), and (3) the admin is unambiguously warned about the checksum/duplicate-detection trade-off before turning it on.

## Scope Alignment

- **In scope:**
  - New `embed_metadata_in_files_enabled` boolean config (Image Processing category) + admin warning copy.
  - New `embed_metadata_update_checksum_enabled` boolean config (Image Processing category, default on) — independent sub-setting controlling whether a successful embed also refreshes `Photo::checksum`/`original_checksum` (FR-059-17).
  - New `App\Metadata\Writer` service wrapping `exiftool` via Laravel's `Process` facade (array-form, no shell).
  - New `App\DTO\MetadataWritePayload`.
  - New `App\Jobs\EmbedMetadataJob` (queued, deduped, tracked via `JobHistory`).
  - Dispatch wiring in `PhotoController::update()`, `PhotoController::tags()`, `PhotoController::rate()`/`Rating::do()`.
  - Writing to both `ORIGINAL` and `RAW` size variants when present.
  - Post-write checksum (`Photo::checksum`/`original_checksum`) and `SizeVariant::filesize` refresh for the Original variant.
  - Full test coverage per spec's Test Strategy, using `Process::fake()`.
  - Reference-doc update (`docs/specs/3-reference/image-processing.md`).
- **Out of scope:** everything listed under spec.md's Non-Goals (non-local disks, video files, retroactive backfill, manual resync action, reading metadata back into the DB, GPS/taken_at/camera fields).

## Dependencies & Interfaces

- `App\Repositories\ConfigManager` — `getValueAsBool()`, `hasExiftool()`, `getValueAsString('exiftool_path')` (all pre-existing).
- `App\Models\Extensions\SizeVariants::getOriginal()`/`getRaw()` (pre-existing, `app/Models/Extensions/SizeVariants.php:142,147`).
- `App\Image\Files\FlysystemFile::isLocalFile()`/`toLocalFile()` (pre-existing, `app/Image/Files/FlysystemFile.php`).
- `App\Image\StreamStat::createFromLocalFile()` (pre-existing, reused for post-write re-hashing).
- `App\Policies\PhotoPolicy::isOwner()` — currently `private`; needs a small visibility change (or a new small public wrapper) so the rating dispatch site can call it. Confirm exact approach at I2 (Analysis Gate item).
- `Illuminate\Support\Facades\Process` (bundled with `laravel/framework: ^12.0`, not currently used anywhere else in this codebase — first adopter).
- Existing `App\Jobs\WatermarkerJob` / `App\Models\JobHistory` as the structural template for the new job.
- `docs/specs/4-architecture/features/020-raw-upload-support/spec.md` — RAW dual-variant precedent (`SizeVariantType::RAW`).
- `docs/specs/4-architecture/features/001-photo-star-rating/spec.md` — existing multi-user rating model this feature layers an owner-only side effect onto.

## Assumptions & Risks

- **Assumptions:**
  - `exiftool` (when present) supports writing the specific tags listed in FR-059-08 for the vast majority of still-image formats Lychee accepts (JPEG, PNG, HEIC, common RAW dialects, PSD). Confirmed generically true of `exiftool`; format-by-format exhaustive verification is not attempted — RAW-format write failures are handled as an expected, non-fatal case (FR-059-09), not treated as a bug to chase per-format.
  - `PhotoPolicy::isOwner()` can be safely exposed (or wrapped) for use outside the policy class without behavioural change — it is a pure `$photo->owner_id === $user->id` check today (`app/Policies/PhotoPolicy.php:57-59`); re-verify at implementation time in case it has changed.
  - The default `QUEUE_CONNECTION=sync` (`.env.example:133`) means most installs execute `EmbedMetadataJob` inline within the triggering request — acceptable per NFR-059-06, since this matches every other queued job's behaviour in this codebase already.
- **Risks / Mitigations:**
  - **Risk:** `exiftool`'s exact CLI tag names/behaviour (e.g. `-charset` flags, list-tag clear-then-append semantics) could differ subtly from the illustrative invocation in spec.md's Appendix once tested against a real binary. **Mitigation:** I3 includes a manual smoke-test step against a real local `exiftool` install (if available in the dev sandbox) to validate the exact argument shape before locking down `App\Metadata\Writer`'s implementation; automated tests still use `Process::fake()` (NFR-059-04) so CI never depends on this.
  - **Risk:** Making `EmbedMetadataJob` re-read the photo's live state at execution time (FR-059-07) could race with a *concurrent, later* unrelated edit landing between dispatch and execution, embedding metadata that's already stale by the time the job actually runs. **Mitigation:** accepted as a known, low-probability edge case — `ShouldBeUnique`'s 60s window and the fact every edit re-dispatches the same deduped job means a fast-follow edit will simply trigger its own (or an already-pending) run that catches up; no additional locking is introduced. Documented here rather than in spec.md since it's an implementation-level accepted trade-off, not a new requirement.
  - **Risk:** `PhotoController::update()` currently calls `$photo->save()` unconditionally, even when title/description/etc. are unchanged from their current values — dispatching the job on every save (not just on actual change) means occasional no-op `exiftool` re-writes. **Mitigation:** accepted — NFR-059-05 (idempotency) makes a no-op re-write harmless (same bytes/checksum out), and diffing "did this field actually change" adds complexity for a purely cosmetic efficiency gain; not built for v1.

## Implementation Drift Gate

To be executed at the start of implementation (before I1): re-read `app/Http/Controllers/Gallery/PhotoController.php` (`update()`/`tags()`/`rate()`), `app/Actions/Photo/Rating.php`, `app/Policies/PhotoPolicy.php`, `app/Models/Extensions/SizeVariants.php`, and `app/Repositories/ConfigManager.php` in full to confirm none of the line numbers/method signatures cited in spec.md have shifted since this plan was drafted (2026-08-27). Record any drift found directly in this section (findings + fix), not as a silent edit elsewhere.

_Drift Gate status: not yet executed — implementation not started._

## Increment Map

1. **I1 – Config migration + reference-doc groundwork**
   - _Goal:_ Land the new config row and update the reference doc, independent of any code that reads it.
   - _Preconditions:_ none.
   - _Steps:_
     - New migration `database/migrations/2026_08_27_000001_add_embed_metadata_config.php` extending `App\Models\Extensions\BaseConfigMigration`, category `Image Processing`, inserting **two** config rows in one `getConfigs()` array: `embed_metadata_in_files_enabled` (default `'0'`, `details` containing the warning HTML, FR-059-16) and `embed_metadata_update_checksum_enabled` (default `'1'`, FR-059-17, its own short `details` explaining the on/off trade-off per spec.md's UI mock-up).
     - Update `docs/specs/3-reference/image-processing.md` with the new "Metadata Write-Back" section.
   - _Commands:_ `php artisan migrate`, `php artisan test --filter=GetAllSettingsTest` (confirm the new config surfaces in the generic settings list with no extra plumbing).
   - _Exit:_ Config row exists, visible in `AllSettings.vue` with rendered warning, defaults to off.

2. **I2 – `MetadataWritePayload` DTO + `App\Metadata\Writer` service**
   - _Goal:_ Build and unit-test the pure argument-building/`exiftool`-invocation logic in isolation, no job/controller wiring yet.
   - _Preconditions:_ I1 (for the `exiftool_path` config to exist, though Writer itself just accepts the path as a constructor/method argument).
   - _Steps:_
     - `App\DTO\MetadataWritePayload` (title, description, tags[], rating).
     - `App\Metadata\Writer::embed(NativeLocalFile $file, MetadataWritePayload $payload, string $exiftool_path): void`, building the exact argument array from spec.md's Appendix, invoking via `Process::run()`, throwing on non-zero exit.
     - Unit tests (`Process::fake()`): full field set, empty tags, rating deletion (payload rating `null`), a value containing shell metacharacters (NFR-059-01/S-059-15), verifying the array shape exactly.
   - _Commands:_ `php artisan test --filter=WriterTest`, `make phpstan`.
   - _Exit:_ `Writer` fully unit-tested; zero wiring into any controller/job yet.

3. **I3 – `EmbedMetadataJob`**
   - _Goal:_ Wire `Writer` into a queued, deduped, `JobHistory`-tracked job operating on a real `Photo`.
   - _Preconditions:_ I1, I2.
   - _Steps:_
     - `App\Jobs\EmbedMetadataJob` (`ShouldQueue`, `ShouldBeUnique`), constructor `Photo $photo`, `handle()`: config re-check (FR-059-01/02) → refresh photo (FR-059-07) → build `MetadataWritePayload` from current title/description/tags/owner-rating → for each of `getOriginal()`/`getRaw()` (skip null): local-disk check (FR-059-11) → `Writer::embed()` → on success, re-measure via `StreamStat` and always persist `filesize` (FR-059-15, both variants, unconditional), additionally persisting `Photo::checksum`/`original_checksum` from that same read **only** for the Original variant **and only if** `embed_metadata_update_checksum_enabled` is true (FR-059-17) → per-variant try/catch so a RAW failure doesn't abort the Original write (FR-059-09) → `JobHistory` status transitions mirroring `WatermarkerJob`.
     - Manual smoke test against a real local `exiftool` binary if available in the dev sandbox (see plan's Risks) to sanity-check the exact tag output with a real file — not part of the automated suite.
   - _Commands:_ `php artisan test --filter=EmbedMetadataJobTest`, `make phpstan`.
   - _Exit:_ Dispatching the job directly (not yet from a controller) against a fixture photo correctly embeds/clears/skips per S-059-02/06/07/08/09/10/11/12/13.

4. **I4 – Controller dispatch wiring**
   - _Goal:_ Trigger `EmbedMetadataJob` from the three real edit endpoints, with the correct gating per field.
   - _Preconditions:_ I3.
   - _Steps:_
     - `PhotoController::update()`: dispatch after `$photo->save()` succeeds (FR-059-03).
     - `PhotoController::tags()`: dispatch per photo id after the tag-sync transaction commits (FR-059-04).
     - `PhotoController::rate()`/`App\Actions\Photo\Rating::do()`: dispatch only when `PhotoPolicy::isOwner($user, $photo)` (FR-059-05) — resolve the `isOwner()` visibility question flagged in Dependencies & Interfaces here, first.
     - All three dispatch sites additionally short-circuit on `getValueAsBool('embed_metadata_in_files_enabled')` before even queuing (avoids needless queue churn when disabled, on top of the job's own defensive re-check).
   - _Commands:_ `php artisan test --filter=PhotoEditTest`, `--filter=PhotoTagsTest`, `--filter=PhotoRatingTest` (all extended with `Queue::fake()`/`Bus::fake()` assertions), full existing suites for these three areas to confirm NFR-059-02 (no regression when disabled).
   - _Exit:_ All of spec.md's S-059-01 through S-059-07 pass end-to-end through the real HTTP endpoints.

5. **I5 – Full scenario coverage + quality gates**
   - _Goal:_ Close out every remaining Branch & Scenario Matrix row and run the full quality gate.
   - _Preconditions:_ I1–I4.
   - _Steps:_
     - Remaining scenarios not yet covered by I3/I4's tests: S-059-14 (settings UI render — light manual/EditorConfig check, no new Vue test needed per Test Strategy).
     - Full `php artisan test` targeted runs (`--filter=Photo`, `--filter=Embed`, `--filter=Settings`) plus a full unfiltered run if the sandbox allows within its time budget (documented precedent: several prior features hit a pre-existing `set_time_limit(600)` full-suite issue unrelated to this feature — fall back to targeted runs if so, and document it here rather than treating it as a regression).
     - `make phpstan` (0 errors, full repo), `vendor/bin/php-cs-fixer fix` (clean).
   - _Commands:_ as above.
   - _Exit:_ All Exit Criteria below satisfied.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-059-01 | I4 / T-059-10 | Config-off regression guard |
| S-059-02 | I3, I4 / T-059-06, T-059-09 | Title embed |
| S-059-03 | I4 / T-059-09 | Description embed, non-owner editor |
| S-059-04 | I4 / T-059-11 | Tags embed |
| S-059-05 | I4 / T-059-12 | Owner rating embed |
| S-059-06 | I4 / T-059-12 | Non-owner rating — no embed |
| S-059-07 | I3, I4 / T-059-06, T-059-12 | Rating removal — tag deletion |
| S-059-08 | I3 / T-059-07 | RAW + Original both written |
| S-059-09 | I3 / T-059-07 | RAW write failure, Original still succeeds |
| S-059-10 | I3 / T-059-07 | No RAW variant present |
| S-059-11 | I3 / T-059-08 | Non-local disk skip |
| S-059-12 | I3 / T-059-05 | `exiftool` unavailable |
| S-059-13 | I3 / T-059-05 | Dedup collapses rapid edits |
| S-059-14 | I1 / T-059-02 | Settings UI warning render |
| S-059-15 | I2 / T-059-04 | Shell-injection safety |
| S-059-16 | Non-Goals, no task | Video files explicitly out of scope — documented, not tested |
| S-059-17 | I3 / T-059-09 | Checksum-update sub-setting off — filesize still refreshed, checksum columns left untouched |
| S-059-18 | I3 / T-059-09 | Checksum-update sub-setting has no effect when the parent feature is off |

## Analysis Gate

Not yet run — plan/spec just drafted. Must be executed at the start of implementation, using the Implementation Drift Gate re-read above as its evidence base, and recorded here (date, reviewer, findings) before any task is marked `[x]`.

## Exit Criteria

- All tasks in `tasks.md` marked `[x]`.
- All 16 Branch & Scenario Matrix rows (S-059-01..16) verified (either by an automated test or, for S-059-16, explicitly as an out-of-scope confirmation).
- `make phpstan`: 0 errors across the full repo (not just touched files).
- `vendor/bin/php-cs-fixer fix`: clean.
- Targeted `php artisan test` runs for every touched test file green; full-suite run attempted, any pre-existing/unrelated failures documented (not silently ignored).
- `docs/specs/3-reference/image-processing.md` updated.
- `docs/specs/4-architecture/roadmap.md` Active Features entry updated to reflect implementation status.
- `_current-session.md` updated with the implementation summary.

## Follow-ups / Backlog

- Bulk backfill CLI command for existing photos.
- Manual "resync now" per-photo action.
- Non-local storage disk support (S3 download/mutate/re-upload).
- Video-file metadata embedding.
- Consider surfacing `JobHistory` failures for this job somewhere admin-visible (today `JobHistory` has no dedicated UI anywhere in the codebase for any job — out of scope to build one just for this feature).
