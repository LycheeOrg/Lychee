# Feature Plan 055 – Multi-Track Albums

_Linked specification:_ `docs/specs/4-architecture/features/055-multi-track-albums/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-08-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant. All thirteen Q-055-* clarifications are resolved and folded into the spec's normative sections (Q-055-01..05 from the initial draft review; Q-055-06..13 from a subsequent codebase-verification pass); no ADR was required (none rose to "cross-feature/module boundary" or "security/telemetry strategy" significance — they are within-feature API/UX/scope/data-model decisions).

## Vision & Success Criteria
An album can hold any number of GPS tracks instead of exactly one. v7 users notice nothing — their existing upload/delete button keeps working against a transparent "primary" track. v8 users get track management (batch add, rename, per-track delete) folded into the existing Album Settings modal, and a Map view that shows every track at once with a togglable legend. Success = all FR-055-01..13 implemented and tested (S-055-01..15 green), `resources/js/v7/` diff empty (NFR-055-01), existing single-track data preserved through the migration (S-055-13), and the pre-existing `Delete.php` hardcoded-`LOCAL` bug fixed (FR-055-12).

## Scope Alignment
- **In scope:** `tracks` table + migration/backfill; `Track` model; `Album` relations; legacy endpoint refactor (v7 compat); new v8 multi-track endpoints; `HeadAlbumResource`/`PositionDataResource` extension; `Actions\Album\Delete` disk-aware cleanup fix; `UploadTrackToS3Job` + `lychee:track_s3_migrate` command; a new `tracks` section (`AlbumTracks.vue`) inside v8's existing `AlbumEdit.vue` Album Settings modal (no new dialog, Q-055-05) + rewritten `Map.vue`; v8-only forked service module; removal of v8's now-redundant "+" add-menu track entries; docs updates (database-schema.md, albums.md, knowledge-map.md).
- **Out of scope:** Any v7 file changes (NFR-055-01); track content re-upload/replace; persisted map visibility/color prefs; GPX content validation beyond MIME/extension; per-track user-facing disk selection; JS automated test coverage (no test runner exists in this repo, per Q-051-05 precedent — manual verification only).

## Dependencies & Interfaces
- `App\Enum\StorageDiskType` (existing, reused as-is).
- `App\Jobs\FileDeleterJob`, `App\Jobs\UploadSizeVariantToS3Job` (pattern reference for `UploadTrackToS3Job`).
- `App\Console\Commands\ImageProcessing\MoveToS3` (pattern reference for `lychee:track_s3_migrate`).
- `App\Actions\Album\Delete` / `App\DTO\Delete\AlbumsToBeDeletedDTO` (modified, not replaced).
- `App\Assets\Features::active('use-s3')` feature-flag gate (existing).
- v8 Leaflet/`L.GPX` integration already present in `resources/js/v8/views/gallery-panels/Map.vue` (rewritten, not replaced wholesale).
- v8's existing `resources/js/v8/components/drawers/AlbumEdit.vue` Album Settings modal and its `SectionId`/`sections` registration pattern (extended with a new `tracks` section, not replaced — Q-055-05).
- typescript-transformer build step (`php artisan typescript:transform` or equivalent existing pipeline) to regenerate `resources/js/lychee.d.ts` for the new `TrackResource`/updated `HeadAlbumResource`/`PositionDataResource` shapes.
- Existing `AbstractTestCase`/`BaseApiWithDataTest` test base classes (AGENTS.md coding conventions).

## Assumptions & Risks
- **Assumptions:**
  - `AlbumTracksController` follows an explicit `store`/`update`/`destroy` convention specified directly in the spec (Q-055-08) — verified during review that no existing controller in this codebase (including `AlbumTagsController`, which is read-only) is an actual precedent to mirror; this is treated as a new, spec-defined convention rather than an inherited pattern.
- **Risks / Mitigations:**
  - *Risk:* `Schema::disableForeignKeyConstraints()` in `AlbumsToBeDeletedDTO::executeDelete()` means the `tracks.album_id` FK's `onDelete('cascade')` never fires during bulk album deletion — rows must be explicitly deleted in the dependents-cleanup block (FR-055-12). *Mitigation:* mirror the existing `album_size_statistics` explicit-delete line exactly, inside `AlbumsToBeDeletedDTO::executeDelete()`'s chunked closure specifically (not `Delete.php` — Q-055-11 corrected this file attribution); add a regression test asserting `tracks` rows are gone post-delete even though cascade is disabled. The disk-grouped `FileDeleterJob` dispatch change is a separate edit, in `Actions/Album/Delete.php:96`'s call site.
  - *Risk:* `AlbumsToBeDeletedDTO::$tracks` currently types as `Collection<string>` (paths only); widening it to disk-aware objects/rows is a breaking change to that DTO's constructor signature. *Mitigation:* since the DTO is `final` and constructed in exactly one place (`Delete::findAllAlbumsToDelete()`), this is a contained, mechanical change — covered by NFR-055-03's test.
  - *Risk:* Batch upload (FR-055-06) "all-or-nothing" validation needs the FormRequest to validate every file in the array before any DB write — must not rely on a naive foreach-and-save loop that could partially commit. *Mitigation:* validate the full `files[]` array via Laravel's array validation rules (`files.*` => same rule as the legacy single file) before entering the Action's persistence step; add NFR-055-02's explicit regression test.
  - *Risk:* v8's `Map.vue` rewrite touches a working feature (existing single-track map rendering); regressions would be silent since there's no JS test runner. *Mitigation:* manual verification pass against S-055-14/15 documented in this plan's Implementation Drift Gate before marking the increment done.
  - *Risk (Q-055-10):* Dropping `oldestOfMany` for an explicit `tracks.is_primary` boolean column means "primary" is no longer self-maintaining — every create path must decide whether the new row is the album's first (and thus primary), and every delete path must explicitly promote the next-oldest remaining track when the primary is removed. *Mitigation:* both bookkeeping steps are spelled out per-FR (FR-055-04/05/06/08) and covered by a dedicated invariant test ("at most one `is_primary = true` row per album, always non-null while tracks exist") run through S-055-04/S-055-08's delete-the-primary scenarios.
  - *Risk (Q-055-09):* No pre-existing Feature/HTTP-level test or `.gpx` fixture exists for the legacy track endpoints — I1/I2's test-first steps require authoring both from scratch, not extending anything. *Mitigation:* new fixture at `tests/Fixtures/tracks/sample.gpx` (a repurposed non-GPX binary, not real GPX content — acceptable per the GPX-content-validation Non-Goal) and a new `tests/Feature_v2/AlbumTrackControllerTest.php`.

## Implementation Drift Gate
**Executed:** 2026-08-18, per `docs/specs/5-operations/analysis-gate-checklist.md`, after all 30 tasks (T-055-01..30) implemented and checked off in `tasks.md`.

1. **Preconditions** — ✅ All tasks `[x]`. Quality gate run (see below); one narrower-documented-suite substitution noted.
2. **Cross-artifact validation** — ✅ Every FR/NFR traces to code+tests:
   - FR-055-01/02/03 → `database/migrations/2026_08_17_000001_create_tracks_table.php`, `app/Models/Track.php`, `Album::tracks()`/`primaryTrack()` — `TrackMigrationTest`, `TrackTest`, `AlbumTrackRelationsTest`.
   - FR-055-04/05 → `Album::setTrack()`/`deleteTrack()` refactor — `AlbumTrackControllerTest`.
   - FR-055-06/07/08 → `AlbumTracksController`, three new Requests, routes — `AlbumTracksControllerTest`.
   - FR-055-09 → `TrackResource`, `HeadAlbumResource`/`PositionDataResource` `tracks[]` — covered indirectly via `AlbumHeadEndpointTest`/`MapTest` (no regression) and `AlbumTracksControllerTest`'s response-shape assertions.
   - FR-055-10/11 → `UploadTrackToS3Job`, `lychee:track_s3_migrate`, auto-dispatch hooks — `UploadTrackToS3JobTest`, `TrackS3MigrateCommandTest`.
   - FR-055-12 → `Delete`/`AlbumsToBeDeletedDTO` disk-grouped cleanup — `AlbumDeleteTrackTest`; full `--filter=Delete` suite (129 tests) green, no regression.
   - FR-055-13 → `AlbumTracks.vue`, `AlbumEdit.vue` section registration, `contextMenuAlbumAdd.ts` cleanup — `npm run check`/`npm run format` clean; manual code-path review (no JS test runner, per Q-051-05 precedent).
   - No implementation or test lacks an originating task; no undocumented work.
3. **Divergence handling** — Two low-impact, in-scope corrections made and documented here (not escalated to open-questions.md):
   - Fixed a **pre-existing** gap in v8's `Map.vue`: `addContentsToMap()` returned early whenever an album had zero geotagged photos, which would have silently prevented any track (single or multi) from ever rendering on a photo-less album — undermining this feature's own success criteria (S-055-14). Rewired the guard and moved the track-rendering block ahead of the photo-bounds-only early return.
   - `SetAlbumTracksRequest`'s `$files` property was renamed to `$uploaded_files` (collides with `Symfony\Request::$files`, caught by phpstan `property.nativeType`).
4. **Coverage confirmation** — ✅ Success/validation/failure branches per S-055-01..15 all covered by the new test files (see task verification commands in `tasks.md`), all green on latest run. `S-055-14`/`UI-055-*` (client-only Map legend/rename-validation state) manually reviewed in code, not automated (no JS test runner in this repo).
5. **Report & retrospective:**
   - **Quality gate results:** `vendor/bin/php-cs-fixer fix` clean (3 files auto-fixed); `make phpstan` — **0 errors across all 2811 analysed files**; `npm run check`/`npm run format` clean; `git diff --stat -- resources/js/v7/` empty (NFR-055-01/S-055-15).
   - **Test suite substitution (documented, matches Feature 052's prior precedent):** an unfiltered `php artisan test` run cannot complete in this sandbox — it hits a **pre-existing, environment-level** cumulative `set_time_limit(600)` process fatal (`Maximum execution time of 600 seconds exceeded` in `Container.php`), unrelated to this feature and previously documented in Feature 052's roadmap entry. Verified this feature's changes via targeted `--filter` runs instead: all 9 new Track-specific test files (34 tests), the full `--filter=Album` suite (**812 tests passed**, covers every album/track consumer), and the full `--filter=Delete` suite (**129 tests passed**, covers the disk-aware cleanup change) — zero failures, zero regressions.
   - **Lesson for future features:** the photo-less-album early-return bug in `Map.vue` was only caught by relating the code to this feature's own acceptance scenario (S-055-14) during implementation, not by any existing test — no coverage exists for empty-album Map rendering either before or after this fix (no JS test runner). Flagging this gap for whoever next touches `Map.vue`.

**Outcome:** Gate passed. No high/medium-impact divergences outstanding.

## Increment Map

1. **I1 – Schema: `tracks` table, backfill migration, `Track` model**
   - _Goal:_ Land FR-055-01/02/03 — the new table, data migration, and Eloquent model/relations, with zero behavioural change yet (nothing reads/writes it besides the migration itself).
   - _Preconditions:_ Spec merged.
   - _Steps:_ Write migration test asserting backfill correctness (S-055-13) first (staged failing); create `tracks` migration (`create_tracks_table` — `id`, `album_id`, `name`, `file_name`, `disk` default `'images'` literal at schema level, `is_primary` boolean not-null default `false`, timestamps — + backfill setting `is_primary = true` for every backfilled row + drop `track_short_path`, with a reversible `down()` per NFR-055-04's accepted-risk caveat, Q-055-13); create `Track` model (fillable, `disk` cast to `StorageDiskType` defaulting to `StorageDiskType::LOCAL->value` at creation — Q-055-06, `url` accessor per DO-055-01); add `Album::tracks()`/`primaryTrack()` relations — `primaryTrack()` is `hasOne(Track::class, 'album_id', 'id')->where('is_primary', true)`, no `ofMany` construct (Q-055-10); create the `tests/Fixtures/tracks/sample.gpx` fixture (repurposed binary, not real GPX content — Q-055-09).
   - _Commands:_ `php artisan test --filter=TrackMigration`, `make phpstan`.
   - _Exit:_ Migration up/down clean on a scratch SQLite DB; `Track` model unit tests green; S-055-13 passes.

2. **I2 – Legacy v7 compatibility: primary-track refactor**
   - _Goal:_ FR-055-04/05 — `Album::setTrack()/deleteTrack()` delegate to the primary `Track` row; `SetAlbumTrackRequest`/`DeleteTrackRequest`/routes/controller methods keep their exact existing signatures.
   - _Preconditions:_ I1 complete.
   - _Steps:_ Write new Feature-level HTTP tests for `POST/DELETE Album::track` against the new schema (S-055-02/03) — no pre-existing Feature test to extend, only `Unit` FormRequest tests exist today (Q-055-09) — staged failing first; refactor `Album::setTrack()` to find-or-update the primary track: on update, reset `file_name`/`name` (extension-stripped filename, Q-055-07)/`disk` (`StorageDiskType::LOCAL->value`), delete old file via disk-aware `Track::url`/delete helper; on create (no primary exists yet), set `is_primary = true` (Q-055-10, it's necessarily the album's first track) with the same extension-stripped `name` convention; refactor `Album::deleteTrack()` to delete the primary track only, then — in the same transaction — promote the next-oldest remaining track (`ORDER BY id ASC LIMIT 1`) to `is_primary = true` if any remain (Q-055-10, explicit bookkeeping, not automatic); update `HeadAlbumResource`/`PositionDataResource`/`PositionData` actions' `track_url` computation to read from `primaryTrack` instead of the dropped column (FR-055-09, back-compat half only in this increment).
   - _Commands:_ `php artisan test --filter=AlbumTrack`, `make phpstan`.
   - _Exit:_ New Feature tests green; S-055-02/03 green; `resources/js/v7/` untouched (no reason to touch it — verify via `git status`).

3. **I3 – v8 multi-track REST surface**
   - _Goal:_ FR-055-06/07/08 — new `POST/PATCH/DELETE Album::tracks` endpoints, batch upload, rename, per-track delete; FR-055-09's `tracks` array addition to both resources.
   - _Preconditions:_ I2 complete.
   - _Steps:_ Write failing feature tests for S-055-01/04/05/06/07 first; add `SetAlbumTracksRequest`/`RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest`; add `Gallery\AlbumTracksController` (its own explicit `store`/`update`/`destroy` convention, spec-defined directly — Q-055-08, no existing controller in this codebase was found to actually mirror, `AlbumTagsController` is read-only) with `store`/`update`/`destroy`; `store()` marks the first created row `is_primary = true` only if the album had zero tracks before the request (Q-055-10); `destroy()` promotes the next-oldest remaining track to `is_primary = true` if the deleted track was primary and others remain (Q-055-10); register routes in `routes/api_v2.php`; add `TrackResource` (DO-055-02); extend `HeadAlbumResource`/`PositionDataResource` with `tracks`.
   - _Commands:_ `php artisan test --filter=AlbumTracks`, `make phpstan`.
   - _Exit:_ S-055-01/04/05/06/07 green; NFR-055-02 (all-or-nothing batch) regression test green; invariant test ("at most one `is_primary` row per album") green across delete-the-primary scenarios.

4. **I4 – Delete cleanup: disk-aware track collection**
   - _Goal:_ FR-055-12 — fix `Actions\Album\Delete`'s hardcoded-`LOCAL` gap; collect all tracks recursively, group by disk, dispatch one `FileDeleterJob` per disk; explicitly delete `tracks` rows in the dependents-cleanup block.
   - _Preconditions:_ I1–I3 complete (needs the `tracks` table and model in place).
   - _Steps:_ Write failing tests for S-055-11/S-055-12 first; widen `AlbumsToBeDeletedDTO::$tracks` from `Collection<string>` to a disk-grouped structure (e.g. `Collection<object{file_name:string,disk:string}>` or two separate collections); update `Delete::findAllAlbumsToDelete()`'s recursive collection query to select `tracks` rows (not `albums.track_short_path`, which no longer exists) across the album subtree; update `Delete::do()` (`Actions/Album/Delete.php:96`'s call site) to dispatch one `FileDeleterJob` per distinct `disk` value present; add `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` to `AlbumsToBeDeletedDTO::executeDelete()`'s dependents block (alongside the existing `album_size_statistics` line — this and the `FileDeleterJob` dispatch change are two separate edits in two separate classes, Q-055-11 corrected the plan's earlier single-file attribution).
   - _Commands:_ `php artisan test --filter=AlbumDelete`, `make phpstan`.
   - _Exit:_ S-055-11/S-055-12 green; existing `Delete.php` test suite still green (no regression on photo/tag/person album deletion paths).

5. **I5 – S3 offload: job + console command**
   - _Goal:_ FR-055-10/11 — disk-aware resolution everywhere, `UploadTrackToS3Job`, auto-dispatch on upload, `lychee:track_s3_migrate` command.
   - _Preconditions:_ I3 complete (needs upload endpoints to hook the auto-dispatch into).
   - _Steps:_ Write failing tests for S-055-08/09/10 first; add `UploadTrackToS3Job` (mirrors `UploadSizeVariantToS3Job`); hook auto-dispatch into both `POST /Album::track` (legacy) and `POST /Album::tracks` (v8) upload paths, gated by `Features::active('use-s3')`; add `lychee:track_s3_migrate` console command (mirrors `MoveToS3`).
   - _Commands:_ `php artisan test --filter=TrackS3`, `make phpstan`.
   - _Exit:_ S-055-08/09/10 green.

6. **I6 – v8 frontend: `tracks` section inside `AlbumEdit.vue`**
   - _Goal:_ FR-055-06/07/08's UI half + FR-055-13 — a new `AlbumTracks.vue` section registered inside the existing Album Settings modal (Q-055-05, no new dialog), forked v8 service, removal of the now-redundant "+" add-menu entries.
   - _Preconditions:_ I3 complete (endpoints must exist).
   - _Steps:_ Create new `resources/js/v8/services/track-service.ts` (forked, not editing shared `album-service.ts`) with `uploadTracks`/`renameTrack`/`deleteTrack`; create `AlbumTracks.vue` under `resources/js/v8/components/forms/album/` per the spec's ASCII mock-up (list, inline rename, per-row delete with confirm, multi-file "Add tracks" input), following the exact registration pattern of `AlbumShare.vue`/`AlbumMove.vue` (add a `"tracks"` `SectionId`, an entry in `AlbumEdit.vue`'s `sections` computed gated on `albumStore.config?.is_model_album && albumStore.rights?.can_edit` — exactly `Move`'s condition, Q-055-12 — and the matching `<section id="album-settings-tracks">` block); remove the now-redundant `gallery.menus.upload_track`/`delete_track` entries from v8's `contextMenuAlbumAdd.ts` (track management now lives in Album Settings); regenerate TS types (`php artisan typescript:transform` or repo's existing equivalent) and confirm `Track`/`TrackResource` types land in `lychee.d.ts`.
   - _Commands:_ `npm run check`, `npm run format`; manual verification in a running dev instance (no JS test runner in this repo).
   - _Exit:_ Manual walk-through of UI-055-01/02/03 and S-055-01/04/05 documented in Drift Gate; `resources/js/v7/` diff still empty.

7. **I7 – v8 frontend: Map.vue multi-track rendering**
   - _Goal:_ FR-055-09's UI half (Q-055-02) — Map view renders all tracks simultaneously with a colored legend/toggle.
   - _Preconditions:_ I3, I6 complete.
   - _Steps:_ Rewrite v8's `Map.vue` to iterate `tracks` (not the singular `track_url`), instantiate one `L.GPX` layer per track with a color cycled from a fixed palette, wire them into Leaflet's native `L.control.layers` overlay control for the legend/checkboxes (Q-055-02, no bespoke legend component).
   - _Commands:_ `npm run check`, `npm run format`; manual verification against S-055-14.
   - _Exit:_ Manual walk-through documented; v7's `Map.vue` untouched (verify via `git status`).

8. **I8 – Documentation sync**
   - _Goal:_ Close the Documentation Deliverables section of the spec.
   - _Preconditions:_ I1–I7 complete.
   - _Steps:_ Update `docs/specs/3-reference/database-schema.md` (new `tracks` table entry); correct `docs/specs/1-concepts/albums.md:75,303`'s stale audio-track description; add a `knowledge-map.md` entry for the `tracks` child-table + v7/v8 compatibility mechanism; move Feature 055 from Active to Completed in `roadmap.md` with an implementation summary (per Features 053/054 convention).
   - _Commands:_ None (docs-only).
   - _Exit:_ All three doc files updated; roadmap entry finalized.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-055-01 | I3 | Batch upload creates N rows |
| S-055-02 | I2 | v7 upload replaces primary only |
| S-055-03 | I2 | v7 delete removes primary only |
| S-055-04 | I3, I4 | v8 delete of primary re-resolves `primaryTrack()` |
| S-055-05 | I3 | Rename non-primary track |
| S-055-06 | I3 | Batch upload all-or-nothing on invalid file |
| S-055-07 | I3 | Cross-album track_id rejected |
| S-055-08 | I5 | Auto S3 offload on upload |
| S-055-09 | I5 | Bulk migrate command respects limit |
| S-055-10 | I5 | Command no-ops when `use-s3` inactive |
| S-055-11 | I4 | Disk-grouped `FileDeleterJob` dispatch |
| S-055-12 | I4 | Subtree delete generalisation of S-055-11 |
| S-055-13 | I1 | Backfill migration correctness |
| S-055-14 | I7 | Map legend toggle hides one layer |
| S-055-15 | I2, I6, I7 | v7 diff empty throughout |

## Analysis Gate
**Executed:** 2026-08-17, by the planning agent (self-review), per `docs/specs/5-operations/analysis-gate-checklist.md`.

1. **Specification completeness** — ✅ Objectives/FR/NFR populated (FR-055-01..13, NFR-055-01..05); all thirteen Q-055-* resolutions folded into FR-055-01..13, DO-055-01/03, NFR-055-04, Non-Goals, and the Spec DSL; three ASCII mock-ups present (Album Settings nav entry, `tracks` section, Map legend).
2. **Open questions review** — ✅ No blocking `Open` rows remain for Feature 055 (all thirteen struck through/Resolved). No ADR created: none of the thirteen clarifications reached "cross-feature/module boundary" or "security/telemetry strategy" significance — they're within-feature API-shape, UX, scope, and data-model decisions, consistent with how similarly-scoped questions in other features (e.g. Q-050-*, Q-046-02/03) were resolved without an ADR.
3. **Plan alignment** — ✅ Plan cites `spec.md`/`tasks.md` at the correct paths; Dependencies & Interfaces and Exit Criteria wording matches spec's FR/NFR language.
4. **Tasks coverage** — ✅ Every FR-055-01..13 maps to ≥1 task (traced: 01→T02/03, 02→T02, 03→T04, 04→T06, 05→T07, 06/07/08→T09-11/T20, 09→T08/T12/T24, 10→T18, 11→T16-19, 12→T13-15, 13→T21/T22). Every increment stages a failing-test task before its implementation tasks. Success/validation/failure branches enumerated via S-055-01..15 (validation: S-055-06/07/10; failure/edge: S-055-04/12).
5. **Constitution compliance** — ✅ No planned work violates spec-first/test-first/documentation-sync principles. Increments delegate disk-grouping and primary-track resolution to small model-level helpers (`Track::url`, `Album::primaryTrack()`) rather than inline branching in controllers/actions. No existing ADR under `docs/specs/6-decisions/` references album/track concerns (confirmed via research), so none required review.
6. **Tooling readiness** — ✅ Verification commands documented per task in `tasks.md`; this section records the analysis outcome.

**Findings:** None blocking. A follow-up codebase-verification pass (2026-08-17, same day) found the initial draft's `oldestOfMany`, `AlbumTagsController`, GPX-fixture, and disk-default assumptions didn't hold up against the actual code — resolved as Q-055-06..13, folded into spec.md/plan.md/tasks.md (most substantially: `oldestOfMany` replaced by an explicit `is_primary` boolean column with transactional bookkeeping). Remaining implementation-time confirmation: exact typescript-transformer command (`tasks.md` Notes) — does not block starting I1.

**Outcome:** Gate passed. Implementation may proceed from I1.

## Exit Criteria
- All FR-055-01..13 and NFR-055-01..05 implemented and traced to passing tests.
- All S-055-01..15 scenarios green (or, for JS-only scenarios without a test runner, manually verified and documented).
- `resources/js/v7/` diff empty (NFR-055-01).
- `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan` clean (PHP changes); `npm run format`, `npm run check` clean (frontend changes) — full quality gate per AGENTS.md since both PHP and frontend are touched.
- Documentation deliverables (I8) complete.
- Implementation Drift Gate section above completed and signed off.
- `tasks.md` fully checked off.

## Follow-ups / Backlog
- Track re-upload/replace-in-place (keeping the same `id` while swapping file content) — deferred, not requested.
- Persisted per-track map visibility/color preferences — deferred (Q-055-02 explicitly scoped this out).
- JS automated test coverage for `AlbumTracks.vue`/`Map.vue` — blocked on this repo adopting a JS test runner at all (tracked generally under Q-051-05's prior finding, not specific to this feature).
