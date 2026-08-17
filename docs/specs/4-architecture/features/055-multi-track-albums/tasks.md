# Feature 055 Tasks – Multi-Track Albums

_Status: Draft_
_Last updated: 2026-08-17_

> Keep this checklist aligned with `plan.md`'s increments (I1–I8). Tests are staged before implementation in every increment. IDs use this repo's established `FR-055-`/`NFR-055-`/`S-055-` prefixes (not the template's generic `F-`/`N-`).

## Checklist

### I1 – Schema: `tracks` table, backfill migration, `Track` model

- [ ] T-055-01 – Write failing backfill/migration test (FR-055-01, FR-055-02, S-055-13).
  _Intent:_ Seed a scratch SQLite DB with albums having/lacking `track_short_path`, run the migration, assert exactly one `tracks` row per non-null album with correct `name`/`file_name`/`disk`, and that `track_short_path` no longer exists on `albums`.
  _Verification commands:_
  - `php artisan test --filter=TrackMigrationTest`
  _Notes:_ Test must fail first (migration doesn't exist yet).

- [ ] T-055-02 – Create `tracks` table + backfill + drop-column migration (FR-055-01, FR-055-02).
  _Intent:_ New migration: `Schema::create('tracks', ...)` (`id` bigIncrements, `char('album_id', 24)` FK → `albums.id` `onDelete('cascade')`, `name` string, `file_name` string, `disk` string default `images`, timestamps); backfill loop over `albums` where `track_short_path IS NOT NULL`; `Schema::table('albums', fn ($t) => $t->dropColumn('track_short_path'))`. Reversible `down()` per NFR-055-04.
  _Verification commands:_
  - `php artisan test --filter=TrackMigrationTest`
  - `make phpstan`
  _Notes:_ Mirror `size_variants`' original create-table shape (`2021_12_04_181200_refactor_models.php:797-811`) and `2024_04_26_201931_add_storate_disk_to_size_variants.php` for the `disk` column default.

- [ ] T-055-03 – Create `Track` Eloquent model (FR-055-01, DO-055-01).
  _Intent:_ `app/Models/Track.php` — `$fillable`, `disk` cast to `StorageDiskType`, `url` computed accessor via `Storage::disk($this->disk->value)->url($this->file_name)`, factory for tests.
  _Verification commands:_
  - `php artisan test --filter=TrackTest`
  - `make phpstan`
  _Notes:_ Mirror `SizeVariant`'s `storage_disk` cast pattern (`app/Models/SizeVariant.php:99,207`).

- [ ] T-055-04 – Add `Album::tracks()`/`primaryTrack()` relations (FR-055-03, DO-055-03).
  _Intent:_ `hasMany(Track::class, 'album_id', 'id')` ordered by `id`; `hasOne(Track::class, 'album_id', 'id')->oldestOfMany('id')`. Confirm `oldestOfMany` is available in the installed Laravel version before writing.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackRelationsTest`
  - `make phpstan`
  _Notes:_ If `oldestOfMany` is unavailable, fall back to `hasOne(...)->oldest('id')` and log a note here.

### I2 – Legacy v7 compatibility: primary-track refactor

- [ ] T-055-05 – Write failing feature tests for legacy endpoints against the new schema (FR-055-04, FR-055-05, S-055-02, S-055-03).
  _Intent:_ Extend/replace the existing `POST/DELETE Album::track` feature tests to assert behaviour against `tracks` rows: uploading when a non-primary track already exists only touches the primary; deleting only removes the primary.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  _Notes:_ Reuse `BaseApiWithDataTest`.

- [ ] T-055-06 – Refactor `Album::setTrack()` to delegate to the primary track (FR-055-04).
  _Intent:_ Find-or-create the primary `Track` row; delete old file via disk-aware deletion before saving; keep the exact same validation/route/controller signature.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  - `make phpstan`

- [ ] T-055-07 – Refactor `Album::deleteTrack()` to delegate to the primary track (FR-055-05).
  _Intent:_ Delete the primary track's file (disk-aware) and row only; no-op-safe when none exists.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  - `make phpstan`

- [ ] T-055-08 – Update `track_url` computation to read `primaryTrack` (FR-055-09, back-compat half).
  _Intent:_ `HeadAlbumResource`, `PositionDataResource`, `Actions/Album/PositionData.php`, `Actions/Albums/PositionData.php` — replace the dropped `track_short_path`-based accessor with `$album->primaryTrack?->url`.
  _Verification commands:_
  - `php artisan test --filter=HeadAlbumResourceTest`
  - `php artisan test --filter=PositionDataTest`
  - `make phpstan`

### I3 – v8 multi-track REST surface

- [ ] T-055-09 – Write failing feature tests for batch upload/rename/delete (FR-055-06, FR-055-07, FR-055-08, S-055-01, S-055-04, S-055-05, S-055-06, S-055-07).
  _Intent:_ New `AlbumTracksControllerTest` covering: batch upload creates N rows with filename-derived names; all-or-nothing on one invalid file in the batch (NFR-055-02); rename updates `name` only; delete removes exactly the targeted track and re-resolves `primaryTrack()` when the primary was deleted; cross-album `track_id` rejected (404/422).
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`

- [ ] T-055-10 – Add `SetAlbumTracksRequest`/`RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest` (FR-055-06, FR-055-07, FR-055-08).
  _Intent:_ `SetAlbumTracksRequest`: `album_id` + `files.*` (same file rule as legacy, array 1..N). `RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest`: `album_id` + `track_id` (must belong to `album_id`) [+ `name` for rename, max 255].
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`
  - `make phpstan`

- [ ] T-055-11 – Add `AlbumTracksController` + routes (FR-055-06, FR-055-07, FR-055-08; API-055-03, API-055-04, API-055-05).
  _Intent:_ New `Gallery\AlbumTracksController` (mirrors `AlbumTagsController`'s sub-resource-controller convention) with `store`/`update`/`destroy`; register `POST/PATCH/DELETE /Album::tracks` in `routes/api_v2.php` next to the existing `Album::track` routes.
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`
  - `make phpstan`

- [ ] T-055-12 – Add `TrackResource`; extend `HeadAlbumResource`/`PositionDataResource` with `tracks[]` (FR-055-09; DO-055-02).
  _Intent:_ `TrackResource` exposes `id`, `name`, `url` only (`file_name`/`disk` hidden, mirrors `SizeVariant`). Both album resources gain `tracks: TrackResource[]` (always an array, ordered by `id`).
  _Verification commands:_
  - `php artisan test --filter=HeadAlbumResourceTest`
  - `php artisan test --filter=PositionDataTest`
  - `make phpstan`

### I4 – Delete cleanup: disk-aware track collection

- [ ] T-055-13 – Write failing tests for disk-grouped deletion (FR-055-12, S-055-11, S-055-12).
  _Intent:_ Feature test: album with one local + one S3 track (and a descendant sub-album with its own tracks on both disks) is deleted; assert two `FileDeleterJob`s dispatched with correct `StorageDiskType`/file-list pairing, and `tracks` rows are gone from the DB.
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`

- [ ] T-055-14 – Widen `AlbumsToBeDeletedDTO::$tracks` and update the recursive collection query (FR-055-12).
  _Intent:_ Replace the `track_short_path`-based `Collection<string>` with a disk-grouped structure sourced from `tracks` rows across the recursive album-id set (`Delete::findAllAlbumsToDelete()` no longer selects `albums.track_short_path`, which is dropped).
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`
  - `make phpstan`

- [ ] T-055-15 – Dispatch per-disk `FileDeleterJob`s and delete `tracks` rows in `executeDelete()` (FR-055-12).
  _Intent:_ `Delete::do()` dispatches one `FileDeleterJob` per distinct `disk` present (replacing the single hardcoded `StorageDiskType::LOCAL` call); `AlbumsToBeDeletedDTO::executeDelete()`'s dependents-cleanup block gains `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` alongside the existing `album_size_statistics` line.
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`
  - `php artisan test --filter=Delete` (full existing album-delete suite — no regression)
  - `make phpstan`

### I5 – S3 offload: job + console command

- [ ] T-055-16 – Write failing tests for S3 offload (FR-055-10, FR-055-11, S-055-08, S-055-09, S-055-10).
  _Intent:_ Test that uploading a track with `use-s3` active dispatches `UploadTrackToS3Job` and results in `disk = S3`; test `lychee:track_s3_migrate` respects `{limit}` and no-ops with an error when `use-s3` is inactive.
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `php artisan test --filter=TrackS3MigrateCommandTest`

- [ ] T-055-17 – Add `UploadTrackToS3Job` (FR-055-11; DO-055-04).
  _Intent:_ Mirror `UploadSizeVariantToS3Job`: stream `file_name` from local to S3 disk, delete local copy, set `disk = StorageDiskType::S3`, record `JobHistory`/`JobStatus`.
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `make phpstan`

- [ ] T-055-18 – Hook auto-dispatch into both upload paths, gated by `Features::active('use-s3')` (FR-055-10, FR-055-11).
  _Intent:_ After a `Track` row is created via `POST /Album::track` (legacy) or `POST /Album::tracks` (v8), dispatch `UploadTrackToS3Job` when the feature flag is active. Also ensure `Track.disk` always defaults to `StorageDiskType::LOCAL` at creation regardless of the flag (FR-055-10).
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `make phpstan`

- [ ] T-055-19 – Add `lychee:track_s3_migrate` console command (FR-055-11; CLI-055-01).
  _Intent:_ Mirror `MoveToS3`: `{limit=5} {tm=600}` signature, guards on `Features::inactive('use-s3')`, selects `Track::query()->where('disk', '=', StorageDiskType::LOCAL->value)->limit($limit)->get()`, dispatches `UploadTrackToS3Job` per row.
  _Verification commands:_
  - `php artisan test --filter=TrackS3MigrateCommandTest`
  - `make phpstan`

### I6 – v8 frontend: track manager UI

- [ ] T-055-20 – Create forked v8 `track-service.ts` (FR-055-06, FR-055-07, FR-055-08).
  _Intent:_ New `resources/js/v8/services/track-service.ts` with `uploadTracks(album_id, files: File[])`, `renameTrack(album_id, track_id, name)`, `deleteTrack(album_id, track_id)`. Do **not** edit the shared `resources/js/services/album-service.ts` (NFR-055-01).
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-055-21 – Create `TrackManager.vue` (UI-055-01, UI-055-03).
  _Intent:_ Dialog per the spec's ASCII mock-up: multi-file "Add tracks" input, track list with inline rename and per-row delete (confirm via existing shared confirm-dialog pattern), empty state when zero tracks.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual walk-through in a running dev instance (no JS test runner in this repo).

- [ ] T-055-22 – Replace v8's binary upload/delete menu entries with a single "Manage tracks" entry (FR-055-06, FR-055-07, FR-055-08).
  _Intent:_ Update `resources/js/v8/composables/contextMenus/contextMenuAlbumAdd.ts` only (not the v7 equivalent) to show one entry (badge = track count) opening `TrackManager.vue`, replacing the current `track_url`-based binary logic.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-055-23 – Regenerate TS types and confirm `Track`/`TrackResource` land in `lychee.d.ts`.
  _Intent:_ Run the repo's existing PHP→TS type-generation step; confirm the new resource shapes appear and `resources/js/v7/lychee.d.ts` usage (if the type file is shared) doesn't break v7's existing `track_url` consumers.
  _Verification commands:_
  - Repo's typescript-transformer command (confirm exact command from `composer.json`/`package.json` scripts during execution)
  - `npm run check`

### I7 – v8 frontend: `Map.vue` multi-track rendering

- [ ] T-055-24 – Rewrite v8's `Map.vue` for multi-track rendering + legend (FR-055-09 UI half; UI-055-02; S-055-14).
  _Intent:_ Iterate `tracks` (not singular `track_url`); one `L.GPX` layer per track, color cycled from a fixed palette; wire into Leaflet's native `L.control.layers` overlay control for the legend/visibility checkboxes (Q-055-02 — no bespoke legend component). v7's `Map.vue` untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual walk-through against S-055-14; confirm `git status` shows no changes under `resources/js/v7/`.

### I8 – Documentation sync

- [ ] T-055-25 – Add `tracks` table to `docs/specs/3-reference/database-schema.md`.
  _Intent:_ Document columns, FK, and the `disk` cast, closing the pre-existing gap where `track_short_path` was never documented either.
  _Verification commands:_ None (docs-only).

- [ ] T-055-26 – Correct `docs/specs/1-concepts/albums.md`'s stale track description.
  _Intent:_ Replace "Track: Optional audio track for slideshow" (lines 75, 303) with an accurate description of multiple GPS/GPX tracks per album.
  _Verification commands:_ None (docs-only).

- [ ] T-055-27 – Add a `tracks` entry to `docs/specs/4-architecture/knowledge-map.md`.
  _Intent:_ Document the child-table pattern (alongside the existing `AlbumSizeStatistics` entry) and the v7/v8 primary-track compatibility mechanism, so future agents don't reintroduce a shared-module edit.
  _Verification commands:_ None (docs-only).

- [ ] T-055-28 – Update `docs/specs/4-architecture/roadmap.md`.
  _Intent:_ Add Feature 055 to Active Features at plan start; move to Completed Features with an implementation summary once all tasks above are `[x]` and the quality gate is green.
  _Verification commands:_ None (docs-only).

### Final verification

- [ ] T-055-29 – Confirm `resources/js/v7/` diff is empty (NFR-055-01, S-055-15).
  _Intent:_ Final guard that no v7 file was touched across I1–I8.
  _Verification commands:_
  - `git diff --stat -- resources/js/v7/`

- [ ] T-055-30 – Run the full quality gate (AGENTS.md "Full quality gate" — both PHP and frontend touched).
  _Intent:_ Final pre-completion sweep.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ Then execute the Implementation Drift Gate section of `docs/specs/5-operations/analysis-gate-checklist.md` and record results in `plan.md`.

## Notes / TODOs
- Confirm the exact `.gpx` test fixture path used by today's single-track tests before T-055-01/T-055-09 (FX-055-01 in spec.md is a placeholder pending this check).
- Confirm `oldestOfMany` availability (T-055-04) against this repo's installed Laravel version before implementation; note the fallback if unavailable.
- Confirm the exact typescript-transformer command (T-055-23) from this repo's `composer.json`/`package.json` before running it.
