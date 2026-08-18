# Feature 055 Tasks – Multi-Track Albums

_Status: Implemented_
_Last updated: 2026-08-18_

> Keep this checklist aligned with `plan.md`'s increments (I1–I8). Tests are staged before implementation in every increment. IDs use this repo's established `FR-055-`/`NFR-055-`/`S-055-` prefixes (not the template's generic `F-`/`N-`).

## Checklist

### I1 – Schema: `tracks` table, backfill migration, `Track` model

- [x] T-055-01 – Write failing backfill/migration test (FR-055-01, FR-055-02, S-055-13).
  _Intent:_ Seed a scratch SQLite DB with albums having/lacking `track_short_path`, run the migration, assert exactly one `tracks` row per non-null album with correct `name`/`file_name`/`disk`/`is_primary = true`, and that `track_short_path` no longer exists on `albums`.
  _Verification commands:_
  - `php artisan test --filter=TrackMigrationTest`
  _Notes:_ Test must fail first (migration doesn't exist yet).

- [x] T-055-02 – Create `tracks` table + backfill + drop-column migration (FR-055-01, FR-055-02).
  _Intent:_ New migration: `Schema::create('tracks', ...)` (`id` bigIncrements, `char('album_id', 24)` FK → `albums.id` `onDelete('cascade')`, `name` string, `file_name` string, `disk` string default `images`, `is_primary` boolean not-null default `false` — Q-055-10, timestamps); backfill loop over `albums` where `track_short_path IS NOT NULL`, setting `is_primary = true` on every backfilled row (each is its album's sole track); `Schema::table('albums', fn ($t) => $t->dropColumn('track_short_path'))`. Reversible `down()` per NFR-055-04 — note its documented accepted-risk caveat (Q-055-13): only safe to run shortly after `up()`, before multi-track data accumulates; no code guard is added.
  _Verification commands:_
  - `php artisan test --filter=TrackMigrationTest`
  - `make phpstan`
  _Notes:_ Mirror `size_variants`' original create-table shape (`2021_12_04_181200_refactor_models.php:797-811`) and `2024_04_26_201931_add_storate_disk_to_size_variants.php` for the `disk` column's schema-level default (still a literal string — schema defaults can't reference PHP enums).

- [x] T-055-03 – Create `Track` Eloquent model (FR-055-01, DO-055-01).
  _Intent:_ `app/Models/Track.php` — `$fillable`, `disk` cast to `StorageDiskType` and defaulted at creation time to `StorageDiskType::LOCAL->value` (Q-055-06 — an explicit enum reference, not a bare `'images'` literal or `config('filesystems.default')`), `is_primary` cast to boolean, `url` computed accessor via `Storage::disk($this->disk->value)->url($this->file_name)`, factory for tests.
  _Verification commands:_
  - `php artisan test --filter=TrackTest`
  - `make phpstan`
  _Notes:_ Mirror `SizeVariant`'s `storage_disk` cast pattern (`app/Models/SizeVariant.php:99,207`) for the cast mechanism only — the *default value* deliberately does not mirror `SizeVariant`'s hardcoded-literal `DEFAULT` constant (Q-055-06).

- [x] T-055-04 – Add `Album::tracks()`/`primaryTrack()` relations (FR-055-03, DO-055-03).
  _Intent:_ `hasMany(Track::class, 'album_id', 'id')` ordered by `id`; `hasOne(Track::class, 'album_id', 'id')->where('is_primary', true)` — no `ofMany`/`oldestOfMany` construct (Q-055-10, dropped in favor of the explicit `is_primary` column since `ofMany` has zero prior usage in this codebase).
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackRelationsTest`
  - `make phpstan`
  _Notes:_ Test the `is_primary` invariant explicitly: at most one `true` row per album; `primaryTrack()` resolves to `null` when no track has `is_primary = true` (e.g. all deleted).

### I2 – Legacy v7 compatibility: primary-track refactor

- [x] T-055-05 – Write new Feature-level HTTP tests for legacy endpoints against the new schema (FR-055-04, FR-055-05, S-055-02, S-055-03).
  _Intent:_ Create `tests/Feature_v2/AlbumTrackControllerTest.php` from scratch — no pre-existing Feature/HTTP test exists to extend (Q-055-09; only `Unit`-level `SetAlbumTrackRequestTest`/`DeleteTrackRequestTest` against mocked Gates exist today) — asserting behaviour against `tracks` rows: uploading when a non-primary track already exists only touches the primary and sets its `name` via the extension-stripped convention (Q-055-07); deleting only removes the primary and promotes the next-oldest remaining track to `is_primary = true` if any remain (Q-055-10).
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  _Notes:_ Reuse `BaseApiWithDataTest`; use the new `tests/Fixtures/tracks/sample.gpx` fixture (Q-055-09).

- [x] T-055-06 – Refactor `Album::setTrack()` to delegate to the primary track (FR-055-04).
  _Intent:_ Find-or-update the primary `Track` row: if one exists, delete the old file via disk-aware deletion, then update `file_name`/`name` (extension-stripped filename, Q-055-07)/`disk` (`StorageDiskType::LOCAL->value`) in place, `is_primary` unchanged; if none exists, create a new row with `is_primary = true` (Q-055-10 — it's necessarily the album's first track) and the same extension-stripped `name`. Keep the exact same validation/route/controller signature.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  - `make phpstan`

- [x] T-055-07 – Refactor `Album::deleteTrack()` to delegate to the primary track (FR-055-05).
  _Intent:_ Delete the primary track's file (disk-aware) and row; in the same transaction, if any other tracks remain for the album, promote the next-oldest one (`ORDER BY id ASC LIMIT 1`) to `is_primary = true` (Q-055-10 — explicit bookkeeping, not automatic); no-op-safe when no primary exists.
  _Verification commands:_
  - `php artisan test --filter=AlbumTrackControllerTest`
  - `make phpstan`

- [x] T-055-08 – Update `track_url` computation to read `primaryTrack` (FR-055-09, back-compat half).
  _Intent:_ `HeadAlbumResource`, `PositionDataResource`, `Actions/Album/PositionData.php`, `Actions/Albums/PositionData.php` — replace the dropped `track_short_path`-based accessor with `$album->primaryTrack?->url`.
  _Verification commands:_
  - `php artisan test --filter=HeadAlbumResourceTest`
  - `php artisan test --filter=PositionDataTest`
  - `make phpstan`

### I3 – v8 multi-track REST surface

- [x] T-055-09 – Write failing feature tests for batch upload/rename/delete (FR-055-06, FR-055-07, FR-055-08, S-055-01, S-055-04, S-055-05, S-055-06, S-055-07).
  _Intent:_ New `AlbumTracksControllerTest` covering: batch upload creates N rows with extension-stripped filename-derived names (Q-055-07), marking the first created row `is_primary = true` only when the album had zero tracks beforehand (Q-055-10); all-or-nothing on one invalid file in the batch (NFR-055-02); rename updates `name` only; delete removes exactly the targeted track and promotes the next-oldest remaining track to `is_primary = true` when the deleted track was primary (Q-055-10); cross-album `track_id` rejected (404/422).
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`
  _Notes:_ Use the `tests/Fixtures/tracks/sample.gpx` fixture created in I1 (Q-055-09).

- [x] T-055-10 – Add `SetAlbumTracksRequest`/`RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest` (FR-055-06, FR-055-07, FR-055-08).
  _Intent:_ `SetAlbumTracksRequest`: `album_id` + `files.*` (same file rule as legacy, array 1..N). `RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest`: `album_id` + `track_id` (must belong to `album_id`) [+ `name` for rename, max 255].
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`
  - `make phpstan`

- [x] T-055-11 – Add `AlbumTracksController` + routes (FR-055-06, FR-055-07, FR-055-08; API-055-03, API-055-04, API-055-05).
  _Intent:_ New `Gallery\AlbumTracksController` — its own explicit `store`/`update`/`destroy` convention, spec-defined directly (Q-055-08; no existing controller in this codebase was found to actually mirror — `AlbumTagsController` is read-only with a single `get()` action, not a CRUD sub-resource controller). `store()`: create one row per uploaded file; if the album had zero tracks before the request, mark the first created row (in submission order) `is_primary = true` (Q-055-10). `update()`: rename only. `destroy()`: delete the targeted row; if it had `is_primary = true` and other tracks remain, promote the next-oldest one to `is_primary = true` in the same transaction (Q-055-10). Register `POST/PATCH/DELETE /Album::tracks` in `routes/api_v2.php` next to the existing `Album::track` routes.
  _Verification commands:_
  - `php artisan test --filter=AlbumTracksControllerTest`
  - `make phpstan`

- [x] T-055-12 – Add `TrackResource`; extend `HeadAlbumResource`/`PositionDataResource` with `tracks[]` (FR-055-09; DO-055-02).
  _Intent:_ `TrackResource` exposes `id`, `name`, `url` only (`file_name`/`disk`/`is_primary` hidden, mirrors `SizeVariant`). Both album resources gain `tracks: TrackResource[]` (always an array, ordered by `id`).
  _Verification commands:_
  - `php artisan test --filter=HeadAlbumResourceTest`
  - `php artisan test --filter=PositionDataTest`
  - `make phpstan`

### I4 – Delete cleanup: disk-aware track collection

- [x] T-055-13 – Write failing tests for disk-grouped deletion (FR-055-12, S-055-11, S-055-12).
  _Intent:_ Feature test: album with one local + one S3 track (and a descendant sub-album with its own tracks on both disks) is deleted; assert two `FileDeleterJob`s dispatched with correct `StorageDiskType`/file-list pairing, and `tracks` rows are gone from the DB.
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`

- [x] T-055-14 – Widen `AlbumsToBeDeletedDTO::$tracks` and update the recursive collection query (FR-055-12).
  _Intent:_ Replace the `track_short_path`-based `Collection<string>` with a disk-grouped structure sourced from `tracks` rows across the recursive album-id set (`Delete::findAllAlbumsToDelete()` no longer selects `albums.track_short_path`, which is dropped).
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`
  - `make phpstan`

- [x] T-055-15 – Dispatch per-disk `FileDeleterJob`s and delete `tracks` rows in `executeDelete()` (FR-055-12).
  _Intent:_ This task spans two classes (Q-055-11 corrected an earlier single-file attribution): (1) `Delete::do()` in `Actions/Album/Delete.php:96`'s call site dispatches one `FileDeleterJob` per distinct `disk` present (replacing the single hardcoded `StorageDiskType::LOCAL` call); (2) `AlbumsToBeDeletedDTO::executeDelete()` (`app/DTO/Delete/AlbumsToBeDeletedDTO.php`) — where `Schema::disableForeignKeyConstraints()` actually lives — gains `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` inside its existing chunked closure, alongside the existing `live_metrics`/`access_permissions`/`statistics`/`album_size_statistics` lines.
  _Verification commands:_
  - `php artisan test --filter=AlbumDeleteTrackTest`
  - `php artisan test --filter=Delete` (full existing album-delete suite — no regression)
  - `make phpstan`

### I5 – S3 offload: job + console command

- [x] T-055-16 – Write failing tests for S3 offload (FR-055-10, FR-055-11, S-055-08, S-055-09, S-055-10).
  _Intent:_ Test that uploading a track with `use-s3` active dispatches `UploadTrackToS3Job` and results in `disk = S3`; test `lychee:track_s3_migrate` respects `{limit}` and no-ops with an error when `use-s3` is inactive.
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `php artisan test --filter=TrackS3MigrateCommandTest`

- [x] T-055-17 – Add `UploadTrackToS3Job` (FR-055-11; DO-055-04).
  _Intent:_ Mirror `UploadSizeVariantToS3Job`: stream `file_name` from local to S3 disk, delete local copy, set `disk = StorageDiskType::S3`, record `JobHistory`/`JobStatus`.
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `make phpstan`

- [x] T-055-18 – Hook auto-dispatch into both upload paths, gated by `Features::active('use-s3')` (FR-055-10, FR-055-11).
  _Intent:_ After a `Track` row is created via `POST /Album::track` (legacy) or `POST /Album::tracks` (v8), dispatch `UploadTrackToS3Job` when the feature flag is active. Also ensure `Track.disk` always defaults to `StorageDiskType::LOCAL` at creation regardless of the flag (FR-055-10).
  _Verification commands:_
  - `php artisan test --filter=UploadTrackToS3JobTest`
  - `make phpstan`

- [x] T-055-19 – Add `lychee:track_s3_migrate` console command (FR-055-11; CLI-055-01).
  _Intent:_ Mirror `MoveToS3`: `{limit=5} {tm=600}` signature, guards on `Features::inactive('use-s3')`, selects `Track::query()->where('disk', '=', StorageDiskType::LOCAL->value)->limit($limit)->get()`, dispatches `UploadTrackToS3Job` per row.
  _Verification commands:_
  - `php artisan test --filter=TrackS3MigrateCommandTest`
  - `make phpstan`

### I6 – v8 frontend: `tracks` section inside `AlbumEdit.vue`

- [x] T-055-20 – Create forked v8 `track-service.ts` (FR-055-06, FR-055-07, FR-055-08).
  _Intent:_ New `resources/js/v8/services/track-service.ts` with `uploadTracks(album_id, files: File[])`, `renameTrack(album_id, track_id, name)`, `deleteTrack(album_id, track_id)`. Do **not** edit the shared `resources/js/services/album-service.ts` (NFR-055-01).
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [x] T-055-21 – Create `AlbumTracks.vue` section and register it in `AlbumEdit.vue` (FR-055-13, UI-055-01, UI-055-03).
  _Intent:_ New `resources/js/v8/components/forms/album/AlbumTracks.vue` per the spec's ASCII mock-up: multi-file "Add tracks" input, track list with inline rename and per-row delete (confirm via existing shared confirm-dialog pattern), empty state when zero tracks. Register it in `resources/js/v8/components/drawers/AlbumEdit.vue` by adding a `"tracks"` value to the local `SectionId` type, an entry in the `sections` computed gated on `albumStore.config?.is_model_album && albumStore.rights?.can_edit` (exactly `Move`'s condition, Q-055-12 — hidden for smart/tag/person albums), and a matching `<section id="album-settings-tracks">` block — following the exact pattern already used for `share`/`move`/etc. **No new modal/dialog is created** (Q-055-05) — this reuses the existing Album Settings modal.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual walk-through in a running dev instance (no JS test runner in this repo).

- [x] T-055-22 – Remove v8's now-redundant upload/delete track menu entries (FR-055-13).
  _Intent:_ Update `resources/js/v8/composables/contextMenus/contextMenuAlbumAdd.ts` only (not the v7 equivalent) to remove the `gallery.menus.upload_track`/`delete_track` entries entirely, since track management now lives in the Album Settings modal's `tracks` section (T-055-21) — consistent with sharing/transfer/move not being in this menu either.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [x] T-055-23 – Regenerate TS types and confirm `Track`/`TrackResource` land in `lychee.d.ts`.
  _Intent:_ Run the repo's existing PHP→TS type-generation step; confirm the new resource shapes appear and `resources/js/v7/lychee.d.ts` usage (if the type file is shared) doesn't break v7's existing `track_url` consumers.
  _Verification commands:_
  - Repo's typescript-transformer command (confirm exact command from `composer.json`/`package.json` scripts during execution)
  - `npm run check`

### I7 – v8 frontend: `Map.vue` multi-track rendering

- [x] T-055-24 – Rewrite v8's `Map.vue` for multi-track rendering + legend (FR-055-09 UI half; UI-055-02; S-055-14).
  _Intent:_ Iterate `tracks` (not singular `track_url`); one `L.GPX` layer per track, color cycled from a fixed palette; wire into Leaflet's native `L.control.layers` overlay control for the legend/visibility checkboxes (Q-055-02 — no bespoke legend component). v7's `Map.vue` untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual walk-through against S-055-14; confirm `git status` shows no changes under `resources/js/v7/`.

### I8 – Documentation sync

- [x] T-055-25 – Add `tracks` table to `docs/specs/3-reference/database-schema.md`.
  _Intent:_ Document columns, FK, and the `disk` cast, closing the pre-existing gap where `track_short_path` was never documented either.
  _Verification commands:_ None (docs-only).

- [x] T-055-26 – Correct `docs/specs/1-concepts/albums.md`'s stale track description.
  _Intent:_ Replace "Track: Optional audio track for slideshow" (lines 75, 303) with an accurate description of multiple GPS/GPX tracks per album.
  _Verification commands:_ None (docs-only).

- [x] T-055-27 – Add a `tracks` entry to `docs/specs/4-architecture/knowledge-map.md`.
  _Intent:_ Document the child-table pattern (alongside the existing `AlbumSizeStatistics` entry) and the v7/v8 primary-track compatibility mechanism, so future agents don't reintroduce a shared-module edit.
  _Verification commands:_ None (docs-only).

- [x] T-055-28 – Update `docs/specs/4-architecture/roadmap.md`.
  _Intent:_ Add Feature 055 to Active Features at plan start; move to Completed Features with an implementation summary once all tasks above are `[x]` and the quality gate is green.
  _Verification commands:_ None (docs-only).

### Final verification

- [x] T-055-29 – Confirm `resources/js/v7/` diff is empty (NFR-055-01, S-055-15).
  _Intent:_ Final guard that no v7 file was touched across I1–I8.
  _Verification commands:_
  - `git diff --stat -- resources/js/v7/`

- [x] T-055-30 – Run the full quality gate (AGENTS.md "Full quality gate" — both PHP and frontend touched).
  _Intent:_ Final pre-completion sweep.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ Then execute the Implementation Drift Gate section of `docs/specs/5-operations/analysis-gate-checklist.md` and record results in `plan.md`.

## Notes / TODOs
- Confirm the exact typescript-transformer command (T-055-23) from this repo's `composer.json`/`package.json` before running it.

Resolved during a codebase-verification pass on 2026-08-17 (Q-055-06..13, see `open-questions.md`) — kept here for history:
- ~~Confirm the exact `.gpx` test fixture path...~~ — none exists; a new fixture is created at `tests/Fixtures/tracks/sample.gpx` (Q-055-09).
- ~~Confirm `oldestOfMany` availability...~~ — dropped entirely in favor of an explicit `tracks.is_primary` boolean column (Q-055-10); `oldestOfMany` has zero prior usage anywhere in this codebase.
