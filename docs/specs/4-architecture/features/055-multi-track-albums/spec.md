# Feature 055 – Multi-Track Albums

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-08-18 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/055-multi-track-albums/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/055-multi-track-albums/tasks.md` |
| Roadmap entry | Completed Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview
Today an album can carry **at most one** GPS/GPX track, stored as a single nullable column (`albums.track_short_path`) with no disk awareness — every track is written to whatever the default `Storage` facade resolves to. This feature replaces that single-column model with a proper `tracks` child table (`id`, `album_id`, `name`, `file_name`, `disk`) supporting **multiple tracks per album**. Legacy v7 (PrimeVue) keeps its existing single-track UI unchanged, operating transparently against a "primary" track in the new table. v8 (Nuxt UI) gets a full multi-track management surface: batch upload, rename, per-track delete, and a Map view that renders every track simultaneously with a legend. The `disk` column also becomes operationally meaningful: new tracks can be offloaded to S3 the same way size variants already are.

Affected modules: **core** (new `Track` model, `Album` relations), **application** (new Actions, updated `Album::Delete`), **REST** (new/updated Album resources and routes), **UI** (v8 only — v7 untouched), **CLI** (new S3-migration command).

## Goals
- Support an arbitrary number of GPS tracks per album, each with an independent `name`, storage path, and storage disk.
- Preserve v7's existing single-track upload/delete UX with zero code changes, by having legacy endpoints operate on a well-defined "primary" track.
- Give v8 full track management — batch upload (multiple GPX files at once), inline rename, per-track delete — as a new section inside the existing Album Settings modal (`AlbumEdit.vue`), reusing the same modal already used for sharing/transfer/move/etc. rather than introducing a new dialog (Q-055-05); plus a Map view showing every track simultaneously with a per-track legend/visibility toggle.
- Make `disk` (local vs. S3) a first-class, per-track attribute, consistent with the existing `size_variants.storage_disk` + `StorageDiskType` pattern, including an S3-offload job and migration command mirroring `UploadSizeVariantToS3Job`/`lychee:s3_migrate`.
- Correctly clean up all of an album's track files (across both disks) when the album (or an ancestor) is deleted, fixing the existing hardcoded-`LOCAL` gap in `Actions\Album\Delete`.
- Migrate existing single-track data (`albums.track_short_path`) into the new table with no data loss, then remove the old column.

## Non-Goals
- No changes to v7's `AlbumHeader.vue`, `contextMenuAlbumAdd.ts`, `Map.vue`, or `album-service.ts`'s existing `uploadTrack`/`deleteTrack` methods (Q-055-01, Option A).
- No new standalone modal/dialog for v8 track management — it is a section inside the existing `AlbumEdit.vue` Album Settings modal, not a bespoke component (Q-055-05). The v8 album header's "+" add-menu no longer offers track upload/delete shortcuts once this feature ships (moved into Album Settings).
- No re-upload/replace-file capability for an existing track row (replacing content requires delete + re-add); rename only changes `name`.
- No server-side persistence of per-track map visibility/color preferences — toggle state in v8's Map view is client-side/session-only (Q-055-02).
- No GPX content parsing/validation beyond file extension/MIME type (same level of validation as today).
- No per-track user-facing disk selector — `disk` is assigned programmatically (default local, optionally offloaded to S3 by the new job/command), never chosen at upload time (mirrors `size_variants`).
- No change to `StorageDiskType` (already has `LOCAL`/`S3`); reused as-is.
- No limit configuration UI for batch-upload count/size — governed by PHP's existing `upload_max_filesize`/`post_max_size`, same as today's single-file upload.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-055-01 | A new `tracks` table stores one row per track: `id` (auto-increment PK), `album_id` (FK → `albums.id`, `onDelete('cascade')` at the DB level as a safety net), `name` (string, required), `file_name` (string, required — the storage-relative path, e.g. `tracks/<random>.gpx`, same semantics as `size_variants.short_path`), `disk` (string, default `images`, cast to `StorageDiskType`), `is_primary` (boolean, required, default `false`), `created_at`/`updated_at`. At most one row per `album_id` has `is_primary = true`, maintained transactionally by application code (Q-055-10) — not a DB-level constraint. | Row created on upload; `Track` model exposes a `url` accessor via `Storage::disk($this->disk->value)->url($this->file_name)`; `disk` defaults at creation time to `StorageDiskType::LOCAL->value` (Q-055-06), not a bare string literal. | Migration runs cleanly on both fresh installs and upgrades. | N/A (schema-only). | — | User request |
| FR-055-02 | A data migration backfills existing `albums.track_short_path` values: for every album where it is non-null, create one `tracks` row (`name` = filename without extension, `file_name` = existing path, `disk` = `StorageDiskType::LOCAL`, `is_primary` = `true` — it is, by construction, the album's only track). After backfill, `track_short_path` is dropped from `albums`. | All pre-existing tracks survive as the album's sole (and therefore primary) track; v7 continues to display/manage them via FR-055-04. | Migration is idempotent-safe (only acts on non-null values) and reversible (`down()` recreates the column and reverse-backfills from the primary track) — see NFR-055-04 for the accepted data-loss caveat on a post-adoption rollback (Q-055-13). | If backfill fails mid-way, migration transaction rolls back (Laravel default). | — | Derived — no-data-loss requirement |
| FR-055-03 | `Album` gains `tracks(): HasMany` (`hasMany(Track::class, 'album_id', 'id')`, ordered by `id` ascending) and `primaryTrack(): HasOne` (`hasOne(Track::class, 'album_id', 'id')->where('is_primary', true)`). No `ofMany`/`oldestOfMany` construct is used (Q-055-10 — dropped in favour of the explicit `is_primary` flag). | `$album->tracks` returns all tracks in upload order; `$album->primaryTrack` returns the single row with `is_primary = true` (or `null` if none). | — | — | — | Derived — relation convention (§ Album Model, `AlbumSizeStatistics`); Q-055-10 |
| FR-055-04 | Legacy `POST /Album::track` (`AlbumController::setTrack`) keeps its exact current request shape (`SetAlbumTrackRequest`: `album_id` + single `file`) and behaviour: if a primary track exists, its file is deleted and its row is updated in place with the new upload (`file_name` set to the new storage path, `name` reset to the new filename with its extension stripped — same convention as FR-055-02/FR-055-06, Q-055-07 — `disk` reset to `StorageDiskType::LOCAL->value`, `is_primary` unchanged at `true`); otherwise a new track row is created with `name` = the uploaded filename with its extension stripped and `is_primary = true` (this is necessarily the album's first track, mirroring FR-055-06's is-this-the-first-track bookkeeping, Q-055-10). Tracks added via v8 (i.e., not the primary) are untouched. | v7's existing "Upload track" action keeps working with no v7-side changes. | Same file validation as today (`mimetypes:application/gpx+xml`, extension `gpx`). | Same failure responses as today. | — | Q-055-01 (Option A), Q-055-07, Q-055-10 |
| FR-055-05 | Legacy `DELETE /Album::track` (`AlbumController::deleteTrack`) keeps its exact current request shape (`DeleteTrackRequest`: `album_id`) and deletes only the primary track (file + row). In the same transaction, if any other tracks remain for the album, the next-oldest one (`ORDER BY id ASC LIMIT 1`) is promoted to `is_primary = true` (Q-055-10 — explicit bookkeeping, since no `ofMany` relation resolves this automatically). Other tracks are otherwise untouched. If no primary track exists, behaves exactly as today's no-op-safe delete. | v7's existing "Delete track" action keeps working with no v7-side changes. | — | — | — | Q-055-01 (Option A), Q-055-10 |
| FR-055-06 | New `POST /Album::tracks` (v8-only) accepts `album_id` + an array of files (`files[]`, 1..N, each validated identically to the legacy single-file rule). One `Track` row is created per file, in submission order; `name` defaults to the uploaded filename with its extension stripped (Q-055-07). If the album has zero tracks immediately before this request, the first row created in submission order is marked `is_primary = true`; otherwise none of the newly created rows are primary (Q-055-10 — preserves v7 `track_url` compatibility even for albums managed only via v8). Response returns the album's full updated track list. | The "Add tracks..." control in `AlbumEdit.vue`'s new `tracks` section (Q-055-05) can add one or many tracks in a single request without disturbing existing tracks (including the primary). | Each file validated individually; a request with zero valid files is rejected before any row is created (all-or-nothing per request). | If one file in the batch fails validation, the entire request is rejected (no partial writes) — standard Laravel FormRequest behaviour. | — | Q-055-03 (Option A), Q-055-05, Q-055-07, Q-055-10 |
| FR-055-07 | New `PATCH /Album::tracks` (v8-only) accepts `album_id` + `track_id` + `name` and renames exactly that track. | Inline rename in the `tracks` section's track list updates `name` only; `file_name`/`disk`/`is_primary` untouched. | `track_id` must belong to `album_id`; `name` required, string, max 255. | 404/422 if the track does not belong to the album. | — | Q-055-03 (Option A), Q-055-05 |
| FR-055-08 | New `DELETE /Album::tracks` (v8-only) accepts `album_id` + `track_id` and deletes exactly that track (file + row), regardless of whether it is the primary. If the deleted track had `is_primary = true` and other tracks remain for the album, the next-oldest remaining track (`ORDER BY id ASC LIMIT 1`) is promoted to `is_primary = true` in the same transaction (Q-055-10 — explicit bookkeeping is required here; it is not automatic). | The per-track "Delete" action in the `tracks` section removes exactly one track; if the deleted track was the primary, `primaryTrack()` resolves to the next-oldest remaining track (or `null` if none remain), via the explicit promotion above. | `track_id` must belong to `album_id`. | 404/422 if the track does not belong to the album. | — | Q-055-01, Q-055-03, Q-055-05, Q-055-10 |
| FR-055-13 | v8's Album Settings modal (`AlbumEdit.vue`) gains a new `tracks` section: a new `SectionId` value, an entry in the `sections` computed, a `<section id="album-settings-tracks">` block, and a new `AlbumTracks.vue` sub-component under `resources/js/v8/components/forms/album/` — following the exact registration pattern already used by `AlbumShare.vue`/`AlbumMove.vue`/etc. The pre-existing "+" add-menu entries for track upload/delete (`gallery.menus.upload_track`/`delete_track` in v8's `contextMenuAlbumAdd.ts` only) are removed, since track management now lives in Album Settings like every other album-configuration concern. | Opening Album Settings (gear icon) shows a "Tracks" nav entry (badge = current count) alongside About/Visibility/Move/Share/Shop/Danger zone; no separate dialog exists. | Section gated exactly like `Move`: `albumStore.config?.is_model_album && albumStore.rights?.can_edit` (Q-055-12 — hidden for smart/tag/person albums, not a bare `can_edit` check). | — | — | Q-055-05, Q-055-12 |
| FR-055-09 | `HeadAlbumResource` and `PositionDataResource` (and the `PositionData`/`Actions\Albums\PositionData` actions that build them) keep the existing `track_url` field (nullable string, computed from the primary track only — unchanged shape for v7 back-compat) and gain a new `tracks` field: an array of `TrackResource` (`id`, `name`, `url`), always present (empty array when no tracks), ordered by `id` ascending. | v7 reads `track_url` exactly as before. v8 reads `tracks` to render its Map legend and track-management UI. | — | — | — | Q-055-01 |
| FR-055-10 | `Track.disk` defaults to `StorageDiskType::LOCAL->value` at creation time — a direct enum reference, not a bare `'images'` string literal or `config('filesystems.default')` (Q-055-06, resolved in favour of a single source of truth over the `size_variants.storage_disk` precedent's duplicated-literal approach); all file reads/writes/deletes for a track resolve the disk via `Storage::disk($track->disk->value)` (never the bare `Storage` facade). | Every track file operation is disk-aware from day one, closing the existing gap where tracks always implicitly used the default disk regardless of `StorageDiskType` configuration. | — | — | — | Q-055-04, Q-055-06 |
| FR-055-11 | A new `App\Jobs\UploadTrackToS3Job` (mirrors `UploadSizeVariantToS3Job`) moves one track's file from the local disk to S3 and updates `disk = StorageDiskType::S3`. It is auto-dispatched immediately after a track row is created (both via `POST /Album::track` and `POST /Album::tracks`) when `Features::active('use-s3')`. A new console command (`lychee:track_s3_migrate {limit=5} {tm=600}`, mirroring `lychee:s3_migrate`/`MoveToS3`) bulk-migrates existing local tracks, selecting `Track::query()->where('disk', '=', StorageDiskType::LOCAL->value)->limit($limit)`. | When S3 support is enabled, new tracks are offloaded automatically; existing local tracks can be migrated in batches via the console command, exactly like size variants today. | Command exits early with an error message if `Features::inactive('use-s3')`, matching `MoveToS3`'s guard. | Job failure is recorded via the existing `JobHistory`/`JobStatus` mechanism, matching `UploadSizeVariantToS3Job::failed()`. | — | Q-055-04 (Option B) |
| FR-055-12 | `Actions\Album\Delete` (and `AlbumsToBeDeletedDTO`) recursively collect **all** tracks (not just one per album) belonging to the albums being deleted, grouped by `disk`, and dispatch one `FileDeleterJob` per distinct disk group (instead of today's single hardcoded `FileDeleterJob(StorageDiskType::LOCAL, ...)`). The dependents-cleanup block also deletes the corresponding `tracks` rows (`DB::table('tracks')->whereIn('album_id', $chunk)->delete()`), matching the existing `album_size_statistics` cleanup pattern. | Deleting an album (or a subtree) removes every track file on every disk it was stored on, with no orphaned files or rows. | — | — | — | Derived — closes existing `Delete.php:96` hardcoded-`LOCAL` gap |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-055-01 | v7's `resources/js/v7/` tree must have zero diff from this feature (no `.vue`/`.ts` changes under that path). | v7 keeps single-track support unchanged, per user instruction and the existing v8-migration convention of forking rather than editing shared modules in place. | `git diff` on `resources/js/v7/` is empty after implementation. | — | Q-055-01 |
| NFR-055-02 | Batch track upload (`POST /Album::tracks`) must not partially commit — either all submitted files become tracks, or none do. | Prevents inconsistent album state from a mid-batch validation failure. | Feature test: submit one valid + one invalid file, assert zero rows created. | Laravel FormRequest validation (fails before controller logic runs). | Derived |
| NFR-055-03 | Track file deletion on album delete must correctly route local vs. S3 tracks to their respective disks; no S3 track's deletion may be attempted against the local disk or vice versa. | Prevents silent no-op deletions (a `FileDeleterJob` given the wrong `StorageDiskType` will look for the file on the wrong disk and simply not find it, leaking storage). | Feature test: album with one local + one S3 track (mocked), deleting the album dispatches two `FileDeleterJob`s with correct disk/file-list pairing. | `FileDeleterJob`, `StorageDiskType` | FR-055-12 |
| NFR-055-04 | The `tracks` DB migration must be reversible: `down()` restores `albums.track_short_path` from each album's primary track and drops the `tracks` table. **Accepted risk (Q-055-13):** `down()` is only intended/safe to run shortly after `up()`, before albums have accumulated multiple tracks via v8. A rollback performed after the feature has been live in production silently discards every non-primary track's file and row with no warning — consistent with this repo's existing migrations, none of which guard `down()` against post-adoption data loss, so this is not a special-cased regression. | Repo convention — all migrations in this codebase implement `down()`. | Migration test / manual `migrate:rollback` on a scratch DB (not the shared dev DB, per `AGENTS.md` database rules). | — | AGENTS.md coding conventions; Q-055-13 |
| NFR-055-05 | New PHP files carry the standard SPDX/copyright license header; PHP code follows PSR-4, `snake_case` variables, `===`/no `empty()`/`in_array(..., true)` per `docs/specs/3-reference/coding-conventions.md`. | Repo-wide coding standard. | `php-cs-fixer fix` clean; `make phpstan` (level 6) clean. | — | AGENTS.md |

## UI / Interaction Mock-ups (required for UI-facing work)

### v8 — "Tracks" section inside the existing Album Settings modal (`AlbumEdit.vue`)
No new dialog is introduced. Track management is a new scroll-spy section — alongside the existing `About`/`Visibility`/`Move`/`Share`/`Shop`/`Danger zone` sections — inside the same "Album Settings" modal already opened via the gear icon in `AlbumHeader.vue` (`resources/js/v8/components/drawers/AlbumEdit.vue`). The side nav gains one more entry; the section itself is a new `AlbumTracks.vue` under `resources/js/v8/components/forms/album/`, following the same pattern as `AlbumShare.vue`/`AlbumMove.vue` (Q-055-05).

```
+----------------------------------------------------------------------+
| Album Settings — "Alps Roadtrip 2026"                          [ x ] |
|------------------------+-----------------------------------------------|
| ○ About                |  Tracks                                       |
| ○ Visibility           |  ────────────────────────────────────────    |
| ○ Move                 |  [ + Add tracks... ]  (multi-file, accept=.gpx)|
| ○ Share                |                                                |
| ○ Shop                 |  ┌──────────────────────────────────────┐     |
| ● Tracks        (3)    |  │ ● Day 1 — Chamonix loop   [✎] [🗑]  │     |
| ○ Danger zone           |  │ ● Day 2 — Col du Galibier [✎] [🗑]  │     |
|                          |  │ ● gpx_track_003            [✎] [🗑]  │     |
|                          |  └──────────────────────────────────────┘     |
|                          |  ⚠ Validation: {invalid_file_type | upload_failed} |
+----------------------------------------------------------------------+
```
Rename switches a row into an inline text field (`[ Day 1 — Chamonix loop___] [✓][✗]`); delete asks for confirmation via the existing shared confirm-dialog pattern before calling `DELETE Album::tracks`. The side-nav label shows the current track count as a badge, matching this spec's earlier menu-badge intent but now inside the settings nav instead of a separate context-menu entry.

### v8 — Map view with multiple tracks
```
+----------------------------------------------------------------+
|  Map                                                            |
|  ┌────────────────────────────────────────────────────────┐    |
|  │                                                          │    |
|  │        ╱‾‾╲___                    ┌ Tracks ────────┐     │    |
|  │      ╱       ╲   ⋯⋯⋯⋯⋯            │ [✓] ── Day 1    │     │    |
|  │    ╱   ●cover  ╲                  │ [✓] ┄┄ Day 2    │     │    |
|  │   ╱______________╲                │ [ ] ·· gpx_...  │     │    |
|  │                                     └─────────────────┘    │    |
|  └────────────────────────────────────────────────────────┘    |
+----------------------------------------------------------------+
```
Each checked track renders as a distinctly colored/dashed `L.GPX` polyline (Leaflet's native `L.control.layers` overlay control supplies the legend + checkboxes — no bespoke component). Unchecking a box hides that track's layer only; state is not persisted.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-055-01 | Fresh album, v8 uploads 3 GPX files in one batch → 3 `tracks` rows created, all with sequential `id`s, `name`s default to filenames. |
| S-055-02 | Album already has a v8-added track; v7 uploads a track via legacy `POST Album::track` → primary track's file/row is replaced; the v8-added track is untouched; `tracks` array on the resource still has 2 entries. |
| S-055-03 | Album has 2 tracks; v7 calls legacy `DELETE Album::track` → only the primary (oldest) track is removed; the other remains and `track_url` now reflects the (new) primary. |
| S-055-04 | Album has 3 tracks; v8 deletes the primary one via `DELETE Album::tracks` with its `track_id` → that row+file is removed; `primaryTrack()` now resolves to the next-oldest remaining track; v7's `track_url` reflects it without any v7 code change. |
| S-055-05 | v8 renames a non-primary track via `PATCH Album::tracks` → `name` updates; `file_name`/`disk`/`id` unchanged. |
| S-055-06 | Batch upload with one valid + one invalid (non-GPX) file → request rejected 422, zero rows created (NFR-055-02). |
| S-055-07 | Attempt to rename/delete a `track_id` that belongs to a different album → 404/422, no mutation. |
| S-055-08 | `Features::active('use-s3')` is true; a new track is uploaded → `UploadTrackToS3Job` dispatched, `disk` becomes `S3` once the job completes; the track's `url` now resolves against the S3 disk. |
| S-055-09 | `lychee:track_s3_migrate 5` run with 8 local tracks existing → 5 are migrated in this invocation (limit respected), `disk` updated per migrated row. |
| S-055-10 | `lychee:track_s3_migrate` run while `use-s3` is inactive → command exits with an error message, no jobs dispatched. |
| S-055-11 | Album with one local-disk track and one S3-disk track is deleted → two `FileDeleterJob`s dispatched, each with the correct `StorageDiskType` and only its own disk's file(s) (NFR-055-03). |
| S-055-12 | Deleting a parent album cascades through descendant sub-albums, each having its own tracks across both disks → all descendant tracks are collected and grouped correctly (S-055-11 generalised to a subtree). |
| S-055-13 | Pre-upgrade DB has 40 albums with a non-null `track_short_path` and 10 with null → migration creates exactly 40 `tracks` rows, one per non-null album, and `track_short_path` column is dropped afterward. |
| S-055-14 | v8 Map view with 3 tracks, user unchecks one in the legend → only that track's polyline disappears; reload resets all to visible (client-only state). |
| S-055-15 | `resources/js/v7/` diff is empty after the full feature is implemented (NFR-055-01). |

## Test Strategy
- **Core:** `Track` model factory + unit tests for `url` accessor across both disk types; `Album::tracks()`/`primaryTrack()` relation tests (ordering; `is_primary` invariant — at most one `true` row per album, correct promotion of the next-oldest track after the primary is deleted — S-055-04, Q-055-10).
- **Application:** Feature tests for `Actions\Album\Delete` covering S-055-11/S-055-12 (disk-grouped `FileDeleterJob` dispatch, correct `tracks` row cleanup); `UploadTrackToS3Job` unit test mirroring the existing `UploadSizeVariantToS3Job` test if one exists (check `tests/` for its counterpart pattern first).
- **REST:** `BaseApiWithDataTest`-based feature tests for all 4 routes (`POST/DELETE Album::track` legacy — S-055-02/03; `POST/PATCH/DELETE Album::tracks` — S-055-01/04/05/06/07), covering success/validation/failure branches per FR-055-04..08.
- **CLI:** Feature/console test for `lychee:track_s3_migrate` covering S-055-09/S-055-10, mirroring any existing `MoveToS3` test.
- **UI (JS):** No JS test runner exists in this repo (per Q-051-05's prior finding) — manual verification of `AlbumEdit.vue`'s new `tracks` section (`AlbumTracks.vue`) and the rewritten `Map.vue` against S-055-01/04/05/14/15, documented in the plan's implementation notes instead of automated coverage.
- **Docs/Contracts:** `docs/specs/3-reference/database-schema.md` gains a `tracks` table entry (currently missing `track_short_path` entirely — pre-existing gap, fixed here); `docs/specs/1-concepts/albums.md`'s incorrect "Track: Optional audio track for slideshow" description corrected to describe GPS/GPX tracks and multiplicity.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-055-01 | `Track` Eloquent model — `id` (int PK), `album_id` (string FK), `name` (string), `file_name` (string), `disk` (`StorageDiskType`, cast, defaults to `StorageDiskType::LOCAL->value` at creation — Q-055-06), `is_primary` (bool, default `false`, transactionally maintained on create/delete — Q-055-10), timestamps. Computed `url` accessor via `Storage::disk($this->disk->value)->url($this->file_name)`. | core |
| DO-055-02 | `TrackResource` (API resource) — `id`, `name`, `url`. `file_name`/`disk` never serialized (internal storage details, mirrors `SizeVariant`'s `$hidden` convention). | REST |
| DO-055-03 | `Album::tracks(): HasMany<Track>` / `Album::primaryTrack(): HasOne<Track>` (`where('is_primary', true)` — Q-055-10; no `ofMany` construct used). | core |
| DO-055-04 | `App\Jobs\UploadTrackToS3Job(Track $track, int $owner_id)` — mirrors `UploadSizeVariantToS3Job`; streams `file_name` from local to S3 disk, deletes local copy, sets `disk = StorageDiskType::S3`, records `JobHistory`. | application |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-055-01 | REST `POST /api/v2/Album::track` | Legacy single-file upload (unchanged request shape) — now writes to the primary `Track` row. | v7-compatible; `SetAlbumTrackRequest` unchanged |
| API-055-02 | REST `DELETE /api/v2/Album::track` | Legacy single-track delete (unchanged request shape) — now deletes the primary `Track` row. | v7-compatible; `DeleteTrackRequest` unchanged |
| API-055-03 | REST `POST /api/v2/Album::tracks` | v8 batch upload — `album_id` + `files[]` (multipart, 1..N). Returns updated `tracks` array. | New `SetAlbumTracksRequest` |
| API-055-04 | REST `PATCH /api/v2/Album::tracks` | v8 rename — `album_id` + `track_id` + `name`. | New `RenameAlbumTrackRequest` |
| API-055-05 | REST `DELETE /api/v2/Album::tracks` | v8 single-track delete — `album_id` + `track_id`. | New `DeleteAlbumTrackRequest` |
| API-055-06 | REST — `HeadAlbumResource`/`PositionDataResource` | Both gain `tracks: TrackResource[]`; `track_url` unchanged in shape. | Consumed by `Album::head`, `/Map` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-055-01 | `php artisan lychee:track_s3_migrate {limit=5} {tm=600}` | Migrates up to `limit` local-disk tracks to S3, mirroring `lychee:s3_migrate`; no-ops with an error if `use-s3` is inactive. |

### Telemetry Events
_None — this feature does not introduce new telemetry events; existing `JobHistory`/`JobStatus` bookkeeping is reused for `UploadTrackToS3Job` (DO-055-04), consistent with `UploadSizeVariantToS3Job`._

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-055-01 | `tests/Fixtures/tracks/sample.gpx` — a repurposed non-GPX binary renamed to a `.gpx` extension, not authored GPX-formatted content (Q-055-09; acceptable since GPX content parsing/validation is a Non-Goal). Created fresh — no pre-existing GPX fixture or Feature-level test exists for the legacy track endpoints (verified against `tests/`). | Sample `.gpx` files for batch-upload feature tests (S-055-01/06). |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-055-01 | `AlbumEdit.vue` `tracks` section, empty state | Album has zero tracks → the section shows only the "Add tracks..." control, no list. |
| UI-055-02 | Map legend, per-track checkbox | Unchecking a track's box hides its `L.GPX` layer only (client state, not persisted) — Q-055-02. |
| UI-055-03 | Inline rename, validation error | Empty name submitted → inline field shows validation message, save disabled. |

## Telemetry & Observability
No new telemetry events. `UploadTrackToS3Job` failures are recorded via the existing `JobHistory`/`JobStatus` mechanism and logged to the `jobs` log channel, identical to `UploadSizeVariantToS3Job`.

## Documentation Deliverables
- Update `docs/specs/3-reference/database-schema.md` to document the new `tracks` table (and remove/replace the now-absent `albums.track_short_path` reference if one existed — it did not, this is a net addition).
- Correct `docs/specs/1-concepts/albums.md:75,303`'s stale "Track: Optional audio track for slideshow" description to accurately describe multiple GPS/GPX tracks.
- Add an entry to `docs/specs/4-architecture/knowledge-map.md` documenting the `tracks` child-table pattern alongside the existing `AlbumSizeStatistics` entry, and note the v7/v8 primary-track compatibility mechanism (Q-055-01) so future agents don't reintroduce a shared-module edit.
- Roadmap entry added to Active Features, then moved to Completed Features with an implementation summary on completion (per repo convention observed in Features 053/054).

## Fixtures & Sample Data
See FX-055-01 above — confirmed during review (Q-055-09) that no existing `.gpx` test fixture or Feature-level HTTP test exists in `tests/` (only `Unit`-level `SetAlbumTrackRequestTest`/`DeleteTrackRequestTest` against mocked Gates); both a new fixture and new Feature tests are created from scratch.

## Spec DSL
```
domain_objects:
  - id: DO-055-01
    name: Track
    fields:
      - name: id
        type: integer
        constraints: "primary key, auto-increment"
      - name: album_id
        type: string
        constraints: "FK -> albums.id, char(24)"
      - name: name
        type: string
        constraints: "required"
      - name: file_name
        type: string
        constraints: "required, storage-relative path"
      - name: disk
        type: StorageDiskType
        constraints: "default StorageDiskType::LOCAL->value (Q-055-06)"
      - name: is_primary
        type: boolean
        constraints: "default false, at most one true per album_id, maintained transactionally on create/delete (Q-055-10)"
  - id: DO-055-02
    name: TrackResource
    fields:
      - name: id
        type: integer
      - name: name
        type: string
      - name: url
        type: string
  - id: DO-055-03
    name: Album track relations
    fields:
      - name: tracks
        type: "HasMany<Track>"
      - name: primaryTrack
        type: "HasOne<Track> (where is_primary = true, Q-055-10)"
  - id: DO-055-04
    name: UploadTrackToS3Job
    fields:
      - name: track
        type: Track
      - name: owner_id
        type: integer
routes:
  - id: API-055-01
    method: POST
    path: /api/v2/Album::track
  - id: API-055-02
    method: DELETE
    path: /api/v2/Album::track
  - id: API-055-03
    method: POST
    path: /api/v2/Album::tracks
  - id: API-055-04
    method: PATCH
    path: /api/v2/Album::tracks
  - id: API-055-05
    method: DELETE
    path: /api/v2/Album::tracks
cli_commands:
  - id: CLI-055-01
    command: php artisan lychee:track_s3_migrate {limit=5} {tm=600}
fixtures:
  - id: FX-055-01
    path: tests/Fixtures/tracks/sample.gpx
ui_states:
  - id: UI-055-01
    description: AlbumEdit.vue tracks section empty state
  - id: UI-055-02
    description: Map legend per-track visibility checkbox
  - id: UI-055-03
    description: Inline rename validation error
```

## Appendix (Optional)

### Current-state reference (pre-feature)
- `albums.track_short_path` (nullable string), migration `2022_04_13_094611_add_track_short_path_to_album_table.php`.
- `Album::getTrackUrlAttribute()`/`setTrack()`/`deleteTrack()` — `app/Models/Album.php:525,541,566`.
- `SetAlbumTrackRequest`/`DeleteTrackRequest` — `app/Http/Requests/Album/`.
- `AlbumController::setTrack()/deleteTrack()` — `app/Http/Controllers/Gallery/AlbumController.php:404-415`.
- Routes — `routes/api_v2.php:79-82`.
- `track_url` consumers — `HeadAlbumResource`, `PositionDataResource`, `Actions/Album/PositionData.php`, `Actions/Albums/PositionData.php`.
- Delete cleanup (hardcoded `StorageDiskType::LOCAL`) — `Actions/Album/Delete.php:96,253-293`, `AlbumsToBeDeletedDTO`.
- `size_variants.storage_disk` + `StorageDiskType` pattern — `app/Enum/StorageDiskType.php`; `app/Models/SizeVariant.php:99,112,129,207`; migration `2024_04_26_201931_add_storate_disk_to_size_variants.php`.
- S3 offload precedent — `app/Jobs/UploadSizeVariantToS3Job.php`, `app/Actions/Photo/Pipes/Shared/UploadSizeVariantsToS3.php` (gated by `Features::active('use-s3')`), `app/Console/Commands/ImageProcessing/MoveToS3.php` (`lychee:s3_migrate`).
- Frontend single-track surfaces (v7 and v8, currently near-identical) — `resources/js/services/album-service.ts:290-306`, `resources/js/{v7,v8}/components/headers/AlbumHeader.vue`, `resources/js/{v7,v8}/composables/contextMenus/contextMenuAlbumAdd.ts`, `resources/js/{v7,v8}/views/gallery-panels/Map.vue`.
- v8's existing Album Settings modal (reused by this feature per Q-055-05, not replaced) — `resources/js/v8/components/drawers/AlbumEdit.vue` (opened via the gear icon in `AlbumHeader.vue`, toggled through the shared `useTogglablesStateStore().is_album_edit_open`); its existing sections live under `resources/js/v8/components/forms/album/` (`AlbumProperties.vue`, `AlbumVisibility.vue`, `AlbumMove.vue`, `AlbumShare.vue`, `AlbumPurchasable.vue`, `AlbumTransfer.vue`, `AlbumDelete.vue`), registered via a local `SectionId` union type and `sections` computed inside `AlbumEdit.vue`. v7 has a separate, independent `AlbumEdit.vue` (older index-based tab UI) — untouched by this feature (NFR-055-01).

### Naming decision (low-impact, decided without a logged open question)
Table named `tracks` (not `album_tracks`), matching the unprefixed-noun convention used by `photos`, `tags`, `size_variants` — all of which are child tables of a single parent identified by an `_id` FK, exactly like this feature's `album_id`.
