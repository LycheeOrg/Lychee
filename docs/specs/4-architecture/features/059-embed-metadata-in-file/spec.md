# Feature 059 – Embed Metadata in Original/RAW File

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-27 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/059-embed-metadata-in-file/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/059-embed-metadata-in-file/tasks.md` |
| Roadmap entry | #059 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Today, editing a photo's title, description, or tags, or rating a photo, only ever updates Lychee's database — the on-disk file is never touched. This feature adds an **opt-in, admin-gated** write-back path: whenever a photo's title, description, or tags are edited, or the photo's **owner** rates their own photo, Lychee embeds the equivalent EXIF/IPTC/XMP metadata directly into the photo's on-disk **Original** file and, when present (Feature 020's dual-variant RAW pipeline), its preserved **RAW** camera file — so the metadata survives if the file is ever exported, downloaded, or read by external tools.

Because rewriting a file's bytes changes its SHA-1 checksum (`Photo::checksum`/`Photo::original_checksum`, used for import-time duplicate detection — see `App\Actions\Photo\Pipes\Init\FindDuplicate`), this behaviour is **disabled by default** and carries an explicit admin-facing warning: re-importing/rescanning a separate untouched copy of the same source file will no longer be recognized as a duplicate of the (now-modified) photo already in the library.

**Affected modules:** Core (new `App\Metadata\Writer`, new `App\Jobs\EmbedMetadataJob`), Application (`PhotoController::update()`/`tags()`/`rate()` dispatch points), Config (new boolean config in the *Image Processing* category), CLI (none — no new command).

## Goals

1. When a photo's title or description is edited (`PATCH /Photo`) by anyone with edit rights, embed the new values into the Original (and RAW, if present) file, when the feature is enabled.
2. When a photo's tags are edited (`PATCH /Photo::tags`) by anyone with edit rights, embed the new tag list into the same file(s), when enabled.
3. When the photo's **owner** rates their own photo (`POST /Photo::setRating`), embed the owner's rating into the same file(s), when enabled. Ratings set by any other user (today's multi-user average-rating system, Feature 001) never touch the file — EXIF/XMP has room for exactly one "Rating" value, and only the owner's opinion is authoritative for what gets embedded.
4. Add one new boolean admin config, `embed_metadata_in_files_enabled` (category **Image Processing**, default `off`), gating the entire feature.
5. Surface a clear, visible warning in the admin Settings UI explaining the checksum/duplicate-detection consequence of enabling this config, following the existing `Mod Watermarker`/`raw_download_enabled` warning-styling precedent.
6. Never let a metadata-embedding failure (missing `exiftool`, unsupported RAW format, non-local storage disk) break or delay the photo-edit request itself — the edit is already complete and successful in the database before any file write is attempted.
7. Add a **second, independent** boolean admin config, `embed_metadata_update_checksum_enabled` (default `on`), controlling whether a successful embed also refreshes `Photo::checksum`/`Photo::original_checksum` to keep Lychee's own duplicate-finder/integrity tooling internally consistent with the file's new bytes — separate from the affected `SizeVariant::filesize`, which is always refreshed regardless of this setting (a byte-count is a fact, not a policy choice).

## Non-Goals

- **Reading metadata back out of the file into the database.** This is a one-way DB → file sync. If a user later edits the file externally (e.g. with exiftool directly), Lychee's database is not updated to match — that is the existing, unrelated `lychee:exif_lens`/re-import metadata-extraction machinery.
- **Non-local storage disks** (S3, etc.). `exiftool` requires a real filesystem path; downloading a remote-only file, mutating it, and re-uploading is real, non-trivial scope (mirrors the existing `FlysystemFile::toLocalFile()` limitation, which already throws for non-local disks — see e.g. `app/Console/Commands/ImageProcessing/ExifLens.php`). Deferred to Follow-ups.
- **Video files** (MP4/MOV, live-photo companions). Embedding metadata into video containers is format-dependent and out of scope for v1 — this feature only touches `SizeVariantType::ORIGINAL`/`RAW` variants of **image** photos (`FileExtensionService`'s supported still-image types).
- **Retroactive backfill.** Enabling the config does not touch any already-existing photo; only edits made *after* enablement trigger an embed. A bulk backfill command is a candidate Follow-up, not part of this feature.
- **A manual "resync now" action.** No new button/endpoint to force a resync without editing a field. Follow-up candidate.
- **GPS coordinates, `taken_at`, camera make/model, or any other EXIF-sourced field.** Those are read *from* the file today (`app/Metadata/Extractor.php`) and are never edited by users in Lychee; this feature is strictly about the four user-editable fields named in Goals 1–3.
- **Changing today's rating permission model.** Feature 001's multi-user average-rating system (any viewer with `CAN_SEE` may rate) is unchanged; this feature only adds a side-effect when the acting rater happens to be the owner.
- **A dedicated admin config page.** Unlike some other features in this codebase (`NsfwConfig.vue`, `WatermarkPreview.vue`, `LandingConfig.vue`), both new configs are plain boolean entries in the existing flat Settings list under the *Image Processing* category — no new Vue route, page, or component. The settings are directly accessible via the global Settings page today; nothing about this feature needs its own page.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-059-01 | New boolean config `embed_metadata_in_files_enabled` (category `Image Processing`, default `'0'`) gates the entire feature. | `ConfigManager::getValueAsBool('embed_metadata_in_files_enabled')` read at dispatch time in each of the three controller actions and again defensively inside `EmbedMetadataJob::handle()`. | Config row inserted via a `BaseConfigMigration`-style migration, `type_range = self::BOOL`. | When `false` (default), no job is ever dispatched — zero behavioural change from today. | — | User requirement |
| FR-059-02 | Feature additionally requires `exiftool` to be available, reusing the existing detection (`ConfigManager::hasExiftool()` / `has_exiftool` / `exiftool_path` configs, `app/Repositories/ConfigManager.php:190-213`). | Job proceeds only when `hasExiftool()` is `true`. | No new detection logic — reuses existing config. | If `embed_metadata_in_files_enabled` is `true` but `hasExiftool()` is `false` (binary missing/removed after being detected), the job logs a warning and records `JobHistory::FAILURE` without touching any file or DB row. | Log channel `jobs`, `WARNING`. | Technical constraint — no EXIF-write capability exists anywhere else in this codebase (`lychee-org/php-exif` is read-only). |
| FR-059-03 | Editing title and/or description (`PATCH /Photo` → `PhotoController::update()`) dispatches `EmbedMetadataJob` for the photo, when FR-059-01/02 hold. | After `$photo->save()` succeeds, `EmbedMetadataJob::dispatch($photo)` is queued. Any user who passes `PhotoPolicy::CAN_EDIT` (owner, admin, or an album editor via `EditPhotoRequest::authorize()`) can trigger this — same permission the endpoint already requires today. | Existing `EditPhotoRequest` validation is unchanged. | If dispatch itself throws (queue connection issue), it is caught and logged the same way any other post-save side effect in this action would be — the HTTP response (`PhotoResource`) is unaffected. | — | User requirement |
| FR-059-04 | Editing tags (`PATCH /Photo::tags` → `PhotoController::tags()`) dispatches `EmbedMetadataJob` for every affected photo, when FR-059-01/02 hold. | After the `DB::transaction()` commits and `PhotoTagsChanged::dispatch($photo_ids)` fires, one `EmbedMetadataJob` per photo id is queued. Same `CAN_EDIT`-based permission as today (`SetPhotosTagsRequest`/`AuthorizeCanEditPhotosTrait`). | Existing tag validation unchanged. | Same failure handling as FR-059-03. | — | User requirement |
| FR-059-05 | Rating a photo (`POST /Photo::setRating` → `Rating::do()`) dispatches `EmbedMetadataJob` **only when the acting user is the photo's owner** (`PhotoPolicy::isOwner($user, $photo)`, `app/Policies/PhotoPolicy.php:57-59`), when FR-059-01/02 hold. | After `Rating::do()` persists the owner's own `PhotoRating` row and recomputes statistics, `EmbedMetadataJob::dispatch($photo)` is queued. | `SetPhotoRatingRequest::authorize()` is unchanged (still `CAN_SEE`-gated for the rating action itself); the owner check is an *additional* gate purely for the file-write side effect. | Any rating action by a user who is not that photo's owner never dispatches the job — no file write, no error, no difference from today's behaviour. | — | User requirement — "when the owner of a photo adds the rating" |
| FR-059-06 | Removing the owner's rating (API request `rating: 0`, which `Rating::do()` treats as a deletion signal per Feature 001's FR-001-02 and never persists — `photo_ratings.rating` has a `CHECK (rating BETWEEN 1 AND 5)` constraint, so `0` is never actually stored) clears the embedded rating tags rather than leaving a stale value. | `EmbedMetadataJob` re-reads the owner's current `PhotoRating` (via `Photo::ratings()->where('user_id', $photo->owner_id)`) at execution time; since the row was deleted, this always resolves to **absent** (never a literal `0`), which the job maps to `MetadataWritePayload::$rating = null`. `Writer` then emits empty-value assignments (`-XMP-xmp:Rating= -EXIF:Rating= -EXIF:RatingPercent=`), which `exiftool` treats as tag deletion. | — | If the tag was already absent, deletion is a no-op (exiftool does not error on deleting an absent tag). | — | Consistency with FR-059-05 |
| FR-059-07 | The job always re-reads the photo's **current** DB state at execution time (title, description, tag names, owner's rating) rather than a value captured at dispatch time. | Because `ShouldBeUnique` (FR-059-12) can collapse several rapid edits into one queued execution, `EmbedMetadataJob::handle()` calls `$photo->refresh()` (and reloads `tags`/owner rating) before building the write payload, so a de-duplicated run still reflects the final, latest field values. | — | — | — | Correctness — avoids writing a stale intermediate value from a superseded dispatch |
| FR-059-08 | Metadata is written to standard, cross-application-compatible EXIF/IPTC/XMP tag triads per field, so the embedded values are readable by common external tools (Adobe products, Windows Explorer, other DAM software), not just Lychee. | `App\Metadata\Writer` invokes `exiftool` with: **Title** → `XMP-dc:Title`, `IPTC:ObjectName`, `EXIF:XPTitle`. **Description** → `EXIF:ImageDescription`, `IPTC:Caption-Abstract`, `XMP-dc:Description`. **Tags** → `IPTC:Keywords` (list, cleared then re-appended), `XMP-dc:Subject` (bag, cleared then re-appended), `EXIF:XPKeywords` (`;`-joined). **Rating** (owner's, 1–5) → `XMP-xmp:Rating`, `EXIF:Rating`, `EXIF:RatingPercent` (`rating * 20`). | Every field is fully replaced (clear-then-set for list tags) with the current DB value on every run — never a partial/incremental diff. | If `exiftool` rejects a specific tag for the file's format (rare, format-dependent), that single field's write fails; see FR-059-09 for how format-scoped write failures are handled. | — | Cross-tool compatibility; mirrors the well-established multi-standard convention other DAM tools use. |
| FR-059-09 | A single variant's embed failure is logged as a **warning** and does not fail the overall job as long as **at least one** targeted variant (Original or RAW) still succeeds. Only when **every** targeted variant fails (or the sole present one does) does the job report failure. This is symmetric — it applies whether it's the RAW variant that fails while Original succeeds (the more likely case in practice, since RAW-format write support varies widely by camera vendor/model), or vice versa. | `EmbedMetadataJob` writes to `getOriginal()` and `getRaw()` (`App\Models\Extensions\SizeVariants::getOriginal()`/`getRaw()`) independently, each wrapped in its own try/catch, with no ordering dependency between the two. | — | Per-variant failure: `Log::channel('jobs')->warning(...)`, job still records `JobHistory::SUCCESS` if at least one variant succeeded. All targeted variants fail: `JobHistory::FAILURE`. | Log channel `jobs`, `WARNING`/`ERROR`. | Robustness — RAW format write support varies widely by camera vendor/model; Original-variant failures (e.g. a permissions/I/O error) are rarer but handled identically, not as a special case. |
| FR-059-10 | Only the `ORIGINAL` and `RAW` (`SizeVariantType::RAW`/`::ORIGINAL`) size variants are ever written to; `MEDIUM2X`/`MEDIUM`/`SMALL2X`/`SMALL`/`THUMB2X`/`THUMB`/`PLACEHOLDER` are never touched. | `EmbedMetadataJob` only resolves `$photo->size_variants->getOriginal()` and `->getRaw()`. | — | — | — | Scope guardrail (Non-Goals) |
| FR-059-11 | A size variant whose storage disk is not local (`FlysystemFile::isLocalFile()` returns `false`) is skipped with a warning, not an error. | `App\Metadata\Writer` requires a `NativeLocalFile` (obtained via `FlysystemFile::toLocalFile()`, which itself throws `MediaFileOperationException` for non-local disks); the job catches this specific exception per-variant and logs a warning instead of propagating it. | — | `Log::channel('jobs')->warning('... non-local disk, skipped ...')`. Job still reports `SUCCESS` if at least the local variant(s) succeeded, or a no-op `SUCCESS` if none were local (nothing to do, not a failure). | Log channel `jobs`, `WARNING`. | Non-Goals — no S3 download/upload plumbing built for v1. |
| FR-059-12 | `EmbedMetadataJob implements ShouldQueue, ShouldBeUnique`, keyed per photo, to collapse rapid consecutive edits (e.g. typing a title then immediately adding tags) into a single `exiftool` invocation. | `uniqueId(): string` returns `'embed-metadata:' . $this->photo->id`; `uniqueFor = 60` (seconds) — short window, distinct from `WatermarkerJob`'s 3600s (which dedupes a much slower, one-shot operation). | — | — | — | Mirrors `App\Jobs\WatermarkerJob`'s `ShouldBeUnique` pattern (`app/Jobs/WatermarkerJob.php:25-49`). |
| FR-059-13 | `exiftool` is invoked without a shell — via `Illuminate\Support\Facades\Process::run(array $command)` (array-form command, Laravel's bundled Process component; no new composer dependency) — so user-controlled title/description/tag text is never interpolated into a shell string. | `App\Metadata\Writer` builds the argument array (binary path, `-overwrite_original`, `-P`, `-charset iptc=UTF8`, `-charset exif=UTF8`, one array element per `-Tag=value` pair, file path) and passes it directly to `Process::run()`. | — | A non-zero exit code from `exiftool` is treated as a failure for that variant (FR-059-09's failure path). | — | **Security (NFR-059-01)** — user-controlled text (title/description/tags) must never reach a shell interpreter; this rules out the codebase's existing `exec()`/`App\Assets\CommandExecutor` string-command pattern for this specific use, which is only safe for the fixed, non-user-controlled commands it's used for elsewhere (`command -v exiftool`, `composer install`). |
| FR-059-14 | `exiftool` is invoked with `-overwrite_original` so no `<file>_original` backup copy is left behind cluttering storage, and `-P`/`-preserve` so the file's modification timestamp is unchanged. | Fixed flags, always present. | — | — | — | Operational hygiene — avoids silently doubling on-disk storage usage (the same concern `Mod Watermarker`'s own admin warning already calls out for a different mechanism). |
| FR-059-15 | After a successful write to a variant (Original or RAW), its file is re-measured via `App\Image\StreamStat::createFromLocalFile()` (single read, yields both byte size and SHA-1 checksum) and the corresponding `SizeVariant::filesize` is refreshed to the new byte count — **unconditionally**, for either variant. | `EmbedMetadataJob` calls `StreamStat::createFromLocalFile($local_file)` after each successful `Writer::embed()` call and persists the refreshed `filesize`. | — | If re-measuring fails (I/O error reading the just-written file), logged as an error; the embed itself is not rolled back (the file was already written). | — | Data integrity — a stale `filesize` is a plain factual bug (the byte count genuinely changed), not a policy trade-off, so this is never gated behind a config (contrast FR-059-17, which is). |
| FR-059-16 | The Settings UI shows the new boolean with an explicit warning about the checksum/duplicate-detection consequence **and** the local-storage-only limitation, using the same inline-HTML warning convention as `Mod Watermarker`. | Migration's `details` column contains HTML: a `pi-exclamation-triangle text-orange-500` icon plus bold text stating that enabling this **changes the file's contents and checksum**, that re-importing/rescanning an untouched copy of the same original file elsewhere **will no longer be recognized as a duplicate** of the already-imported (now-modified) photo, and that this **only works for photos stored on local disk** — photos on S3 (or any other non-local storage disk) are silently skipped and never have metadata embedded (FR-059-11), so admins running S3-backed installs should not expect this setting to have any effect for those photos. Rendered automatically by the existing generic `BoolField.vue` (`v-html` on `config.details`) — no new frontend component needed. | — | — | — | User requirement — "explains the risk of creating duplicates"; user follow-up — "add extra details that this is only for local storage and does not work on s3" |
| FR-059-17 | A **second, independent** boolean config, `embed_metadata_update_checksum_enabled` (category `Image Processing`, default `'1'`), controls whether a successful **Original**-variant write also updates `Photo::checksum`/`Photo::original_checksum` to match the file's new SHA-1 (from the same FR-059-15 `StreamStat` read). | When `true` (default): the Original variant's refreshed checksum is persisted to `Photo::checksum` and `Photo::original_checksum`, exactly as previously specified — keeps Lychee's own duplicate-finder/integrity tooling internally consistent with the now-mutated file. When `false`: the checksum columns are left untouched at their pre-edit values — `original_checksum` then permanently represents the exact bytes that were originally uploaded, at the cost of the DB record no longer matching the file's actual current bytes (a deliberate, admin-accepted inconsistency). Only meaningful when `embed_metadata_in_files_enabled` (FR-059-01) is also `true`; read via `ConfigManager::getValueAsBool('embed_metadata_update_checksum_enabled')` inside `EmbedMetadataJob`, independently of `filesize`'s unconditional refresh (FR-059-15). | Config row inserted via the same migration as FR-059-01, `type_range = self::BOOL`. | — | — | User requirement — "make this an additional setting" (checksum-refresh behaviour split out from the main embed toggle) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-059-01 | No shell-string interpolation of user-controlled text anywhere in the write path. | Security (command injection) — title/description/tags are arbitrary user-supplied text. | Code review + a dedicated test asserting a title/tag containing shell metacharacters (`` $(rm -rf /) ``, backticks, `;`, quotes) is embedded verbatim and never executed. `Process::run()` called with an array, never a string. | `Illuminate\Support\Facades\Process` | OWASP Top 10 — command injection |
| NFR-059-02 | Zero behavioural change when the config is at its default (`off`). | Backwards compatibility — this must be a pure opt-in addition. | Full existing `PhotoEditTest`/`PhotoRatingTest`/tag-endpoint test suites pass unmodified; a new assertion confirms no job is queued when the config is `false`. | — | Safety |
| NFR-059-03 | Coding conventions: PSR-4, strict comparisons, snake_case variables, license headers, no `empty()`, `in_array(..., true)`. | Maintainability | `vendor/bin/php-cs-fixer fix` + `make phpstan` both clean on new/touched files. | — | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-059-04 | Tests never require a real `exiftool` binary in CI. | CI portability — the test environment may not have `exiftool` installed. | All new tests fake the write path via `Illuminate\Support\Facades\Process::fake()`; no test asserts against real file bytes produced by a real `exiftool` invocation. | Laravel's `Process::fake()` testing helper | Test reliability |
| NFR-059-05 | Idempotent: running the job twice back-to-back with unchanged DB field values produces the same embedded tag values both times. | Safety for retries (`ShouldBeUnique` re-queues, manual re-run) | A test dispatches the job twice for the same unchanged photo and asserts the second `Process::run()` call receives an identical argument array to the first. | — | Correctness |
| NFR-059-06 | Under the default `QUEUE_CONNECTION=sync`, `EmbedMetadataJob` **does** add the `exiftool` invocation's latency to the triggering photo-edit request (Laravel's sync driver runs `handle()` inline from `dispatch()`) — this is a known, accepted cost, not a regression specific to this feature, since every other queued job in this codebase (e.g. `WatermarkerJob`) has the identical characteristic under `sync`. Installs that configure a real async queue driver pay none of this cost. | UX — full zero-latency is not achievable under `sync` without inventing new async infrastructure this codebase doesn't have elsewhere; the goal is parity with existing job-dispatch precedent, not a stronger guarantee this feature alone can't actually make. | Manual/documented, not asserted by an automated timing test (flaky by nature) — verified structurally instead: `EmbedMetadataJob` is dispatched the same way (`::dispatch()`, no `dispatchSync()`/`dispatchNow()`) as `WatermarkerJob`. | Laravel queue configuration (existing, unchanged) | Consistency with existing job dispatch precedent — corrects an earlier draft's overclaim ("not measurably affected"), which contradicted the sync-driver behaviour this same NFR's own dependency line already described. |
| NFR-059-07 | `EmbedMetadataJob::handle()` never lets an exception propagate to its caller — every failure path (missing `exiftool`, both variants failing, a re-hash I/O error, an unexpected error building the payload) is caught internally and resolved to a logged warning/error plus a `JobHistory::FAILURE` row, exactly like the already-specified per-variant failures (FR-059-09). | **Critical**, not merely tidy: per NFR-059-06, `dispatch()` runs `handle()` inline under the default `sync` queue driver, so an exception that escapes `handle()` would propagate directly into `PhotoController::update()`/`tags()`/`rate()` and break the photo-edit HTTP response — directly violating Goal 6 ("never let a metadata-embedding failure break or delay the photo-edit request itself"). A per-variant try/catch (FR-059-09) alone is not sufficient, since it doesn't cover failures in the surrounding code (e.g. the initial config/`exiftool` check, the photo/tag/rating refresh, or the post-write checksum persistence). | A test that forces an unexpected exception at each stage of `handle()` (not just inside a per-variant `Writer::embed()` call) and asserts the calling code (a direct `EmbedMetadataJob::dispatchSync()`/`handle()` call in the test) never receives it. | — | Correctness — found during a post-draft ambiguity review; mirrors `App\Jobs\WatermarkerJob::handle()`, which never throws either (its own error paths return after setting `JobHistory::FAILURE`, matching this exact pattern). |

## UI / Interaction Mock-ups

Reference: [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](../../spec-guidelines/ui-ascii-mockups.md).

### Image Processing settings — new toggle

```
┌────────────────────────────────────────────────────────────────────┐
│ Image Processing                                                    │
├────────────────────────────────────────────────────────────────────┤
│  Allow RAW file download                              [ ] Off      │
│  ...                                                                 │
│                                                                       │
│  Embed metadata (title, description, tags, rating)     [ ] Off      │
│  in original/RAW file                                                │
│  ⚠ Enabling this rewrites the Original file (and RAW file,          │
│     if present) whenever a photo's title, description, tags, or     │
│     the owner's rating changes. This changes the file's contents    │
│     and checksum — if you later re-import or rescan an untouched    │
│     copy of the same source file, it will no longer be recognized   │
│     as a duplicate of the photo already in your library.            │
│     Local storage only — photos stored on S3 (or any other          │
│     non-local disk) are skipped and never have metadata embedded.   │
│                                                                       │
│  Update stored checksum after embedding metadata       [x] On       │
│  ⚠ When on (default), the photo's recorded checksum is updated to  │
│     match the rewritten file, keeping Lychee's own duplicate        │
│     finder consistent. When off, the recorded checksum keeps        │
│     pointing at the original pristine upload even after the file    │
│     has changed — useful if you need `original_checksum` to stay    │
│     a permanent fingerprint of what was first uploaded.              │
└────────────────────────────────────────────────────────────────────┘
```

No other UI surface changes — the photo-edit dialogs (title/description/tags/rating) are entirely unchanged; the embedding happens silently in the background.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-059-01 | Config at default (`off`) — editing title/description/tags/rating never dispatches `EmbedMetadataJob`; zero behavioural change from today. |
| S-059-02 | Config `on`, `exiftool` available, owner edits title → Original file's `Title`/`ObjectName`/`XPTitle` updated; with `embed_metadata_update_checksum_enabled` at its default (`on`), `Photo::checksum`/`original_checksum` refreshed to match. |
| S-059-03 | Config `on`, editor (album-share edit rights, not owner) edits description → Original file's `ImageDescription`/`Caption-Abstract`/`Description` updated (description/title/tags are `CAN_EDIT`-scoped, not owner-restricted). |
| S-059-04 | Config `on`, any `CAN_EDIT` user edits tags → Original file's `Keywords`/`Subject`/`XPKeywords` fully replaced with the new tag list. |
| S-059-05 | Config `on`, photo owner rates their own photo 4 stars → `Rating`/`RatingPercent` set to `4`/`80` in the file. |
| S-059-06 | Config `on`, a different user (not the owner, but has view access) rates the photo → no job dispatched, file untouched. |
| S-059-07 | Config `on`, owner removes their rating (sets to `0`) → `Rating`/`RatingPercent`/`XMP:Rating` tags deleted from the file. |
| S-059-08 | Photo has both a `RAW` and an `ORIGINAL` size variant (Feature 020 dual-variant upload) → both files are updated. |
| S-059-09 | RAW variant's format is one `exiftool` cannot write (rare/unsupported RAW dialect) → Original write still succeeds; RAW failure logged as a warning; job still `SUCCESS`. |
| S-059-10 | Photo has no `RAW` variant (ordinary JPEG upload) → only the Original file is updated; no error/warning about a "missing RAW". |
| S-059-11 | Original variant's storage disk is non-local (e.g. S3) → embed skipped for that variant, warning logged, no error surfaced anywhere; the edit request itself already succeeded before the job ran. |
| S-059-12 | `embed_metadata_in_files_enabled` is `true` but `has_exiftool` is `false` (binary missing) → job fails gracefully (`JobHistory::FAILURE`, one warning log line), no file/DB mutation, no retry storm. |
| S-059-13 | User edits title, then within the same minute edits tags on the same photo → `ShouldBeUnique` collapses this into effectively one `exiftool` invocation reflecting both the new title and the new tags (job re-reads current DB state at execution time, not dispatch time). |
| S-059-14 | Admin opens the Image Processing settings page → sees the new toggle with the warning text rendered (icon + bold), consistent with the `Mod Watermarker` warning styling. |
| S-059-15 | A title/tag value containing shell metacharacters (e.g. `` My `photo`; rm -rf / `` ) is embedded into the file verbatim as literal text — never executed as a shell command. |
| S-059-16 | Video photo (MP4 live-photo companion or standalone video) is edited → no embed attempted (Non-Goals); only the image-type Original/RAW variants of still photos are ever targeted. |
| S-059-17 | Config `on`, `embed_metadata_update_checksum_enabled` set to `off` → title edit still rewrites the Original file (and `SizeVariant::filesize` is still refreshed, FR-059-15), but `Photo::checksum`/`original_checksum` are left at their pre-edit values — deliberate, admin-accepted inconsistency between the DB and the file's actual current bytes. |
| S-059-18 | `embed_metadata_update_checksum_enabled` toggled independently of `embed_metadata_in_files_enabled` (e.g. main feature off, checksum sub-setting on) → has no observable effect, since no embed ever happens to refresh a checksum from (FR-059-17's "only meaningful when the parent is also on"). |

## Test Strategy

- **Core (Unit):** `App\Metadata\Writer` argument-building logic (title/description/tag/rating → exact `exiftool` argument array, including clear-then-append list handling and empty-value deletion for a removed rating) — tested against `Process::fake()`, asserting the exact array passed, never a string. `EmbedMetadataJob::uniqueId()`/dedup key. Checksum/filesize refresh logic in isolation.
- **Application (Feature, `tests/Feature_v2/Photo/`):**
  - `PhotoEditTest` extended: config on/off × title/description change → job dispatched/not dispatched (`Queue::fake()` / `Bus::fake()`).
  - `PhotoTagsTest` extended similarly for the tags endpoint.
  - `PhotoRatingTest` extended: owner rates → job dispatched; non-owner rates → job not dispatched; owner clears rating → job dispatched with a payload that deletes the rating tags.
  - New `tests/Feature_v2/Photo/EmbedMetadataJobTest.php`: end-to-end job execution against a local fixture image, using `Process::fake()` to assert the exact command array per scenario (S-059-02 through S-059-13), including the RAW-present/absent, non-local-disk-skip, and exiftool-unavailable branches.
- **REST:** No new endpoints — no new OpenAPI schema. Existing `PATCH /Photo`, `PATCH /Photo::tags`, `POST /Photo::setRating` schemas are unchanged (the job is a fire-and-forget side effect, invisible in the response body).
- **CLI:** Not applicable (no new command).
- **UI (JS):** No new Vue component (BoolField.vue renders the config generically) — a lightweight snapshot/manual check that `config.details` HTML renders as expected is sufficient; no dedicated component test required.
- **Docs/Contracts:** Update `docs/specs/3-reference/image-processing.md` with a new "Metadata Write-Back" section documenting the config, the tag mapping table, and the checksum-consistency caveat.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-059-01 | `App\DTO\MetadataWritePayload` — `?string $title`, `?string $description`, `string[] $tags`, `?int $rating` (`1`–`5`, or `null` when the owner has no rating row — whether because they never rated the photo, or because they just removed their rating; `photo_ratings.rating` is DB-constrained to `1`–`5` so a literal `0` is never a real value here, unlike the transient `rating: 0` **API request** signal in FR-059-06). Built fresh from the current `Photo` state at job-execution time (FR-059-07). | core (DTO) |
| DO-059-02 | `App\Metadata\Writer` — stateless service, single public method `embed(NativeLocalFile $file, MetadataWritePayload $payload, string $exiftool_path): void`, throws on a non-zero `exiftool` exit code. Mirrors the naming/placement of the existing read-side `App\Metadata\Extractor`. | core (Metadata) |
| DO-059-03 | `App\Jobs\EmbedMetadataJob` — `ShouldQueue`, `ShouldBeUnique` (`uniqueId = 'embed-metadata:' . $photo->id`, `uniqueFor = 60`), constructed with a `Photo $photo`. Tracked via a `JobHistory` row (`READY` → `STARTED` → `SUCCESS`/`FAILURE`), mirroring `App\Jobs\WatermarkerJob`. | core (Jobs) |

### API Routes / Services

No new routes. Existing routes gain a side effect only:

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-059-01 | PATCH `/Photo` (existing) | `PhotoController::update()` additionally dispatches `EmbedMetadataJob` after a successful title/description save, when FR-059-01/02 hold. | No response-shape change. |
| API-059-02 | PATCH `/Photo::tags` (existing) | `PhotoController::tags()` additionally dispatches `EmbedMetadataJob` per affected photo after a successful tag sync, when FR-059-01/02 hold. | No response-shape change. |
| API-059-03 | POST `/Photo::setRating` (existing) | `PhotoController::rate()`/`Rating::do()` additionally dispatches `EmbedMetadataJob` only when the acting user is the photo owner, when FR-059-01/02 hold. | No response-shape change. |

### CLI Commands / Flags

Not applicable — no new CLI command in this feature (a bulk-backfill command is a Follow-up candidate, see Appendix).

### Telemetry Events

No structured telemetry events (consistent with Feature 001's rating feature having none). Standard application logs only, channel `jobs`:
- `INFO`: successful embed (variant(s) written, checksum refreshed).
- `WARNING`: RAW-variant write skipped/failed while Original succeeded; non-local-disk variant skipped.
- `ERROR`: total job failure (`exiftool` unavailable, both variants failed, or a fatal I/O error).

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-059-01 | `tests/Feature_v2/Photo/fixtures/` (reuse existing small local JPEG fixture, e.g. the one `PhotoEditTest`/`PhotoAddTest` already use) | Local file for `EmbedMetadataJobTest` to point `Writer`/`Process::fake()` assertions at. |
| FX-059-02 | Existing RAW fixture (e.g. a `.dng`/`.cr2` sample already used by `RAW Upload Pipeline` tests, Feature 020) | Exercises the dual-variant (Original + RAW) embed path, S-059-08/09. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-059-01 | Settings toggle — off (default) | `BoolField.vue` renders unchecked; warning text still visible underneath (informational, not conditional on the toggle's own state). |
| UI-059-02 | Settings toggle — on | `BoolField.vue` renders checked; no additional UI change elsewhere (no confirmation dialog — a single settings-page toggle + save, same as every other boolean config). |
| UI-059-03 | Checksum-update sub-setting toggle | Renders as its own independent `BoolField.vue` row (default checked), visible regardless of the main toggle's state — same generic-config rendering, no conditional show/hide logic. |

## Telemetry & Observability

Covered under "Telemetry Events" above — log-only, no metrics/events pipeline changes. No verbose-trace additions.

## Documentation Deliverables

- Update `docs/specs/3-reference/image-processing.md` — new "Metadata Write-Back" section (config, tag-mapping table, checksum caveat), cross-referencing this feature.
- Update `docs/specs/4-architecture/roadmap.md` — add Feature 059 to the Active Features table.
- Update `docs/specs/4-architecture/open-questions.md` — log Q-059-01..07 (see Appendix) as resolved.
- Update `docs/specs/_current-session.md` with a session summary entry once drafting is complete.

## Fixtures & Sample Data

See "Fixtures & Sample Data" under the Interface & Contract Catalogue above — no new fixture files need to be authored; existing small local-JPEG and RAW test fixtures are reused.

## Spec DSL

```yaml
domain_objects:
  - id: DO-059-01
    name: MetadataWritePayload
    fields:
      - name: title
        type: string
        constraints: "nullable"
      - name: description
        type: string
        constraints: "nullable"
      - name: tags
        type: string[]
      - name: rating
        type: integer
        constraints: "1-5, or null when the owner has no rating row (never a literal 0 — see DO-059-01)"
  - id: DO-059-02
    name: Writer
    namespace: App\Metadata
    methods:
      - name: embed
        args: [NativeLocalFile, MetadataWritePayload, exiftool_path]
  - id: DO-059-03
    name: EmbedMetadataJob
    namespace: App\Jobs
    interfaces: [ShouldQueue, ShouldBeUnique]
    unique_id_template: "embed-metadata:{photo_id}"
    unique_for_seconds: 60

routes:
  - id: API-059-01
    method: PATCH
    path: /Photo
    side_effect: dispatch EmbedMetadataJob
  - id: API-059-02
    method: PATCH
    path: /Photo::tags
    side_effect: dispatch EmbedMetadataJob per photo
  - id: API-059-03
    method: POST
    path: /Photo::setRating
    side_effect: dispatch EmbedMetadataJob, owner-only

cli_commands: []

telemetry_events: []

fixtures:
  - id: FX-059-01
    path: tests/Feature_v2/Photo/fixtures/ (reused JPEG fixture)
  - id: FX-059-02
    path: existing RAW fixture (Feature 020 test tree)

ui_states:
  - id: UI-059-01
    description: Settings toggle off (default), warning text visible
  - id: UI-059-02
    description: Settings toggle on
  - id: UI-059-03
    description: Checksum-update sub-setting toggle (default on), independent of the main toggle
```

## Appendix

### Resolved Decisions (Q-059-01..07)

These are recorded in compressed form here and cross-referenced from `docs/specs/4-architecture/open-questions.md`; all were resolved directly from the user's own request phrasing plus grounded codebase precedent, without requiring a blocking clarification round.

- **Q-059-01 (trigger scope per field):** Title/description/tags sync trigger for **any** user who already has `CAN_EDIT` on the photo (owner, admin, or shared-album editor) — the same permission the three endpoints already require today. Rating sync is restricted to the **photo owner's own rating** only. *Rationale:* the user's own request explicitly distinguishes "a user" (tags) from "the owner" (rating); this also matches the underlying permission architecture exactly — title/description/tags are already `CAN_EDIT`-gated (an editor-not-owner scenario is a real, existing case for shared albums), while rating is `CAN_SEE`-gated and *not* owner-restricted today (Feature 001, Q001-05), so an explicit additional owner check is the only way to give the file a single, meaningful "whose rating is this" answer.
- **Q-059-02 (execution model):** Queued job (`EmbedMetadataJob`, `ShouldQueue`), not inline synchronous code in the controller. *Rationale:* mirrors the closest existing precedent (`WatermarkerJob`, also a single-size-variant file mutation dispatched from a controller action); with the default `QUEUE_CONNECTION=sync` it still runs within the request today, so there is no functional difference for the common case, but the door is open for async queues without a later refactor.
- **Q-059-03 (which file(s)):** Both the **Original** and, when present, the preserved **RAW** camera file (Feature 020's dual-variant pipeline) are written to. *Rationale:* explicit user follow-up mid-request ("We also may want to consider the RAW file too") — RAW files are the ones most likely to be exported/archived/reused outside Lychee, so skipping them would defeat much of the feature's purpose for RAW-shooting users.
- **Q-059-04 (tag mapping):** Write to the cross-application-compatible EXIF+IPTC+XMP triad per field (see FR-059-08), not just a single tag per field. *Rationale:* single-standard tags (e.g. EXIF-only `ImageDescription`) are not read by every consumer (e.g. Lightroom/Bridge favor XMP, older Windows tools favor the `XP*` EXIF tags, many archival/DAM tools favor IPTC) — writing all three maximizes the chance the embedded metadata round-trips correctly through whatever external tool the user later opens the file with.
- **Q-059-05 (checksum consistency):** After a successful embed, `Photo::checksum`/`Photo::original_checksum` (Original variant only — there is no RAW-checksum column in the schema) and both variants' `SizeVariant::filesize` are refreshed to match the new file bytes. *Rationale:* leaving Lychee's own stored checksum pointing at stale (pre-edit) bytes would make the library internally inconsistent with itself (e.g. `App\Actions\Photo\DuplicateFinder`/future integrity-check tooling would flag the record as corrupt) — this is separate from, and does not eliminate, the *external* re-scan/duplicate risk the admin warning (FR-059-16) exists to flag: a separate, untouched copy of the original source file elsewhere on disk will still have the old checksum and will no longer match this now-modified library record.
- **Q-059-06 (`exiftool` requirement):** The feature hard-requires `exiftool` (via the existing `has_exiftool`/`exiftool_path` detection) rather than attempting a native-PHP or Imagick-based write path. *Rationale:* confirmed by direct codebase/vendor inspection that `lychee-org/php-exif` (the only EXIF library currently in `composer.json`) is read-only (`Reader`/`Adapter`/`Mapper` only, no writer), and PHP has no built-in EXIF-write support; `exiftool` is the de facto standard for reliable, wide-format EXIF/IPTC/XMP writing (including most RAW formats) and is already an optional, auto-detected dependency in this codebase for reading.
- **Q-059-07 (non-local storage disks):** Explicitly out of scope for v1 (Non-Goals) — a non-local variant is skipped with a warning, never attempted. *Rationale:* `FlysystemFile::toLocalFile()` already throws for non-local disks everywhere else in this codebase (e.g. `ExifLens`/`Takedate` CLI commands accept this same limitation); building S3 download/mutate/re-upload plumbing is materially larger scope than the user's request and was not asked for.
- **Q-059-08 (checksum-refresh as its own setting):** Split out of the main `embed_metadata_in_files_enabled` toggle into a second, independent boolean, `embed_metadata_update_checksum_enabled` (default **on**, FR-059-17). *Rationale:* direct user instruction ("Make this an additional setting") after reviewing the original single-toggle design. Defaulting **on** preserves the originally-specified behaviour (internal consistency with the duplicate-finder) for anyone who doesn't change it; an admin who specifically needs `original_checksum` to remain a permanent, unchanging fingerprint of the exact bytes first uploaded (e.g. for provenance/audit purposes) can now turn checksum-refresh off independently of the embedding feature itself. `SizeVariant::filesize` is deliberately **not** gated the same way (FR-059-15) — its refresh is a factual correction (the byte count did change), not a policy trade-off like the checksum semantics are.

### Exact `exiftool` Invocation Shape (illustrative)

```
exiftool
  -overwrite_original -P
  -charset iptc=UTF8 -charset exif=UTF8
  -XMP-dc:Title=<title> -IPTC:ObjectName=<title> -EXIF:XPTitle=<title>
  -EXIF:ImageDescription=<description> -IPTC:Caption-Abstract=<description> -XMP-dc:Description=<description>
  -IPTC:Keywords= -XMP-dc:Subject=                      # clear existing list tags first
  -IPTC:Keywords+=<tag1> -IPTC:Keywords+=<tag2> ...      # then re-append current tags
  -XMP-dc:Subject+=<tag1> -XMP-dc:Subject+=<tag2> ...
  -EXIF:XPKeywords=<tag1;tag2;...>
  -XMP-xmp:Rating=<rating> -EXIF:Rating=<rating> -EXIF:RatingPercent=<rating*20>   # or all empty-assigned to delete
  <absolute local file path>
```

Passed to `Illuminate\Support\Facades\Process::run()` as an **array**, one element per line above (never concatenated into a shell string) — see NFR-059-01/FR-059-13.

### Follow-ups (not in this feature's scope)

- Bulk backfill CLI command (`lychee:embed_metadata`) to retroactively sync existing photos once the feature is enabled.
- Manual "resync now" action per photo, independent of editing a field.
- Support for non-local storage disks (download → mutate → re-upload).
- Video-file metadata embedding.
