# Feature Plan 055 – Multi-Track Albums

_Linked specification:_ `docs/specs/4-architecture/features/055-multi-track-albums/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant. All four Q-055-* clarifications are resolved and folded into the spec's normative sections; no ADR was required (none of the four rose to "cross-feature/module boundary" or "security/telemetry strategy" significance — they are within-feature API/UX/scope decisions).

## Vision & Success Criteria
An album can hold any number of GPS tracks instead of exactly one. v7 users notice nothing — their existing upload/delete button keeps working against a transparent "primary" track. v8 users get a track manager (batch add, rename, per-track delete) and a Map view that shows every track at once with a togglable legend. Success = all FR-055-01..12 implemented and tested (S-055-01..15 green), `resources/js/v7/` diff empty (NFR-055-01), existing single-track data preserved through the migration (S-055-13), and the pre-existing `Delete.php` hardcoded-`LOCAL` bug fixed (FR-055-12).

## Scope Alignment
- **In scope:** `tracks` table + migration/backfill; `Track` model; `Album` relations; legacy endpoint refactor (v7 compat); new v8 multi-track endpoints; `HeadAlbumResource`/`PositionDataResource` extension; `Actions\Album\Delete` disk-aware cleanup fix; `UploadTrackToS3Job` + `lychee:track_s3_migrate` command; v8 `TrackManager.vue` + rewritten `Map.vue`; v8-only forked service module; docs updates (database-schema.md, albums.md, knowledge-map.md).
- **Out of scope:** Any v7 file changes (NFR-055-01); track content re-upload/replace; persisted map visibility/color prefs; GPX content validation beyond MIME/extension; per-track user-facing disk selection; JS automated test coverage (no test runner exists in this repo, per Q-051-05 precedent — manual verification only).

## Dependencies & Interfaces
- `App\Enum\StorageDiskType` (existing, reused as-is).
- `App\Jobs\FileDeleterJob`, `App\Jobs\UploadSizeVariantToS3Job` (pattern reference for `UploadTrackToS3Job`).
- `App\Console\Commands\ImageProcessing\MoveToS3` (pattern reference for `lychee:track_s3_migrate`).
- `App\Actions\Album\Delete` / `App\DTO\Delete\AlbumsToBeDeletedDTO` (modified, not replaced).
- `App\Assets\Features::active('use-s3')` feature-flag gate (existing).
- v8 Leaflet/`L.GPX` integration already present in `resources/js/v8/views/gallery-panels/Map.vue` (rewritten, not replaced wholesale).
- typescript-transformer build step (`php artisan typescript:transform` or equivalent existing pipeline) to regenerate `resources/js/lychee.d.ts` for the new `TrackResource`/updated `HeadAlbumResource`/`PositionDataResource` shapes.
- Existing `AbstractTestCase`/`BaseApiWithDataTest` test base classes (AGENTS.md coding conventions).

## Assumptions & Risks
- **Assumptions:**
  - The exact GPX test fixture(s) used by the current single-track feature tests exist in `tests/` and can be reused/duplicated for batch-upload tests (FX-055-01) — to be confirmed in I1.
  - `AlbumTagsController`/`AlbumPhotosController` (`Route::get('/Album::tags', ...)`) represent the intended controller-per-sub-resource convention; the new `AlbumTracksController` follows the same pattern.
  - Laravel's `HasOne::ofMany('id', 'min')` (`oldestOfMany`) is available in the Laravel version this repo runs (standard since Laravel 8.42+); to be confirmed against `composer.json` in I2.
- **Risks / Mitigations:**
  - *Risk:* `Schema::disableForeignKeyConstraints()` in `AlbumsToBeDeletedDTO::executeDelete()` means the `tracks.album_id` FK's `onDelete('cascade')` never fires during bulk album deletion — rows must be explicitly deleted in the dependents-cleanup block (FR-055-12). *Mitigation:* mirror the existing `album_size_statistics` explicit-delete line exactly; add a regression test asserting `tracks` rows are gone post-delete even though cascade is disabled.
  - *Risk:* `AlbumsToBeDeletedDTO::$tracks` currently types as `Collection<string>` (paths only); widening it to disk-aware objects/rows is a breaking change to that DTO's constructor signature. *Mitigation:* since the DTO is `final` and constructed in exactly one place (`Delete::findAllAlbumsToDelete()`), this is a contained, mechanical change — covered by NFR-055-03's test.
  - *Risk:* Batch upload (FR-055-06) "all-or-nothing" validation needs the FormRequest to validate every file in the array before any DB write — must not rely on a naive foreach-and-save loop that could partially commit. *Mitigation:* validate the full `files[]` array via Laravel's array validation rules (`files.*` => same rule as the legacy single file) before entering the Action's persistence step; add NFR-055-02's explicit regression test.
  - *Risk:* v8's `Map.vue` rewrite touches a working feature (existing single-track map rendering); regressions would be silent since there's no JS test runner. *Mitigation:* manual verification pass against S-055-14/15 documented in this plan's Implementation Drift Gate before marking the increment done.

## Implementation Drift Gate
Executed per `docs/specs/5-operations/analysis-gate-checklist.md`. Findings, evidence, and sign-off recorded in this section once implementation of all increments below is complete and the quality gate is green. Placeholder — populated at feature completion.

## Increment Map

1. **I1 – Schema: `tracks` table, backfill migration, `Track` model**
   - _Goal:_ Land FR-055-01/02/03 — the new table, data migration, and Eloquent model/relations, with zero behavioural change yet (nothing reads/writes it besides the migration itself).
   - _Preconditions:_ Spec merged; confirm existing GPX fixture path for later increments.
   - _Steps:_ Write migration test asserting backfill correctness (S-055-13) first (staged failing); create `tracks` migration (`create_tracks_table` + backfill + drop `track_short_path`, with a reversible `down()`); create `Track` model (fillable, casts, `url` accessor per DO-055-01); add `Album::tracks()`/`primaryTrack()` relations (DO-055-03); confirm `oldestOfMany` availability.
   - _Commands:_ `php artisan test --filter=TrackMigration`, `make phpstan`.
   - _Exit:_ Migration up/down clean on a scratch SQLite DB; `Track` model unit tests green; S-055-13 passes.

2. **I2 – Legacy v7 compatibility: primary-track refactor**
   - _Goal:_ FR-055-04/05 — `Album::setTrack()/deleteTrack()` delegate to the primary `Track` row; `SetAlbumTrackRequest`/`DeleteTrackRequest`/routes/controller methods keep their exact existing signatures.
   - _Preconditions:_ I1 complete.
   - _Steps:_ Write/extend existing feature tests for `POST/DELETE Album::track` to assert they now operate against `tracks` rows (S-055-02/03) — staged failing first; refactor `Album::setTrack()` to find-or-update the primary track (delete old file via disk-aware `Track::url`/delete helper, create/update row); refactor `Album::deleteTrack()` to delete the primary track only; update `HeadAlbumResource`/`PositionDataResource`/`PositionData` actions' `track_url` computation to read from `primaryTrack` instead of the dropped column (FR-055-09, back-compat half only in this increment).
   - _Commands:_ `php artisan test --filter=AlbumTrack`, `make phpstan`.
   - _Exit:_ All pre-existing single-track tests still pass unmodified in intent (same assertions, now against the new schema); S-055-02/03 green; `resources/js/v7/` untouched (no reason to touch it — verify via `git status`).

3. **I3 – v8 multi-track REST surface**
   - _Goal:_ FR-055-06/07/08 — new `POST/PATCH/DELETE Album::tracks` endpoints, batch upload, rename, per-track delete; FR-055-09's `tracks` array addition to both resources.
   - _Preconditions:_ I2 complete.
   - _Steps:_ Write failing feature tests for S-055-01/04/05/06/07 first; add `SetAlbumTracksRequest`/`RenameAlbumTrackRequest`/`DeleteAlbumTrackRequest`; add `Gallery\AlbumTracksController` (mirrors `AlbumTagsController`'s sub-resource-controller convention) with `store`/`update`/`destroy`; register routes in `routes/api_v2.php`; add `TrackResource` (DO-055-02); extend `HeadAlbumResource`/`PositionDataResource` with `tracks`.
   - _Commands:_ `php artisan test --filter=AlbumTracks`, `make phpstan`.
   - _Exit:_ S-055-01/04/05/06/07 green; NFR-055-02 (all-or-nothing batch) regression test green.

4. **I4 – Delete cleanup: disk-aware track collection**
   - _Goal:_ FR-055-12 — fix `Actions\Album\Delete`'s hardcoded-`LOCAL` gap; collect all tracks recursively, group by disk, dispatch one `FileDeleterJob` per disk; explicitly delete `tracks` rows in the dependents-cleanup block.
   - _Preconditions:_ I1–I3 complete (needs the `tracks` table and model in place).
   - _Steps:_ Write failing tests for S-055-11/S-055-12 first; widen `AlbumsToBeDeletedDTO::$tracks` from `Collection<string>` to a disk-grouped structure (e.g. `Collection<object{file_name:string,disk:string}>` or two separate collections); update `Delete::findAllAlbumsToDelete()`'s recursive collection query to select `tracks` rows (not `albums.track_short_path`, which no longer exists) across the album subtree; update `Delete::do()` to dispatch one `FileDeleterJob` per distinct `disk` value present; add `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` to `AlbumsToBeDeletedDTO::executeDelete()`'s dependents block (alongside the existing `album_size_statistics` line).
   - _Commands:_ `php artisan test --filter=AlbumDelete`, `make phpstan`.
   - _Exit:_ S-055-11/S-055-12 green; existing `Delete.php` test suite still green (no regression on photo/tag/person album deletion paths).

5. **I5 – S3 offload: job + console command**
   - _Goal:_ FR-055-10/11 — disk-aware resolution everywhere, `UploadTrackToS3Job`, auto-dispatch on upload, `lychee:track_s3_migrate` command.
   - _Preconditions:_ I3 complete (needs upload endpoints to hook the auto-dispatch into).
   - _Steps:_ Write failing tests for S-055-08/09/10 first; add `UploadTrackToS3Job` (mirrors `UploadSizeVariantToS3Job`); hook auto-dispatch into both `POST /Album::track` (legacy) and `POST /Album::tracks` (v8) upload paths, gated by `Features::active('use-s3')`; add `lychee:track_s3_migrate` console command (mirrors `MoveToS3`).
   - _Commands:_ `php artisan test --filter=TrackS3`, `make phpstan`.
   - _Exit:_ S-055-08/09/10 green.

6. **I6 – v8 frontend: track manager UI**
   - _Goal:_ FR-055-06/07/08's UI half — `TrackManager.vue`, forked v8 service, updated context-menu entry.
   - _Preconditions:_ I3 complete (endpoints must exist).
   - _Steps:_ Create new `resources/js/v8/services/track-service.ts` (forked, not editing shared `album-service.ts`) with `uploadTracks`/`renameTrack`/`deleteTrack`; create `TrackManager.vue` per the spec's ASCII mock-up (list, inline rename, per-row delete with confirm, multi-file "Add tracks" input); update v8's `contextMenuAlbumAdd.ts` to replace the binary upload/delete entries with a single "Manage tracks" entry (badge = count) opening the dialog; regenerate TS types (`php artisan typescript:transform` or repo's existing equivalent) and confirm `Track`/`TrackResource` types land in `lychee.d.ts`.
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

1. **Specification completeness** — ✅ Objectives/FR/NFR populated (FR-055-01..12, NFR-055-01..05); all four Q-055-* resolutions folded into FR-055-04/05/06/07/08/10/11, Non-Goals, and NFR-055-01; three ASCII mock-ups present (track manager menu entry, dialog, Map legend).
2. **Open questions review** — ✅ No blocking `Open` rows remain for Feature 055 (all four struck through/Resolved). No ADR created: none of the four clarifications reached "cross-feature/module boundary" or "security/telemetry strategy" significance — they're within-feature API-shape, UX, and scope decisions, consistent with how similarly-scoped questions in other features (e.g. Q-050-*, Q-046-02/03) were resolved without an ADR.
3. **Plan alignment** — ✅ Plan cites `spec.md`/`tasks.md` at the correct paths; Dependencies & Interfaces and Exit Criteria wording matches spec's FR/NFR language.
4. **Tasks coverage** — ✅ Every FR-055-01..12 maps to ≥1 task (traced: 01→T02/03, 02→T02, 03→T04, 04→T06, 05→T07, 06/07/08→T09-11/T20/T22, 09→T08/T12/T24, 10→T18, 11→T16-19, 12→T13-15). Every increment stages a failing-test task before its implementation tasks. Success/validation/failure branches enumerated via S-055-01..15 (validation: S-055-06/07/10; failure/edge: S-055-04/12).
5. **Constitution compliance** — ✅ No planned work violates spec-first/test-first/documentation-sync principles. Increments delegate disk-grouping and primary-track resolution to small model-level helpers (`Track::url`, `Album::primaryTrack()`) rather than inline branching in controllers/actions. No existing ADR under `docs/specs/6-decisions/` references album/track concerns (confirmed via research), so none required review.
6. **Tooling readiness** — ✅ Verification commands documented per task in `tasks.md`; this section records the analysis outcome.

**Findings:** None blocking. Two implementation-time confirmations are flagged as TODOs in `tasks.md`'s Notes section (exact `.gpx` fixture path; `oldestOfMany` availability; exact typescript-transformer command) — none block starting I1.

**Outcome:** Gate passed. Implementation may proceed from I1.

## Exit Criteria
- All FR-055-01..12 and NFR-055-01..05 implemented and traced to passing tests.
- All S-055-01..15 scenarios green (or, for JS-only scenarios without a test runner, manually verified and documented).
- `resources/js/v7/` diff empty (NFR-055-01).
- `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan` clean (PHP changes); `npm run format`, `npm run check` clean (frontend changes) — full quality gate per AGENTS.md since both PHP and frontend are touched.
- Documentation deliverables (I8) complete.
- Implementation Drift Gate section above completed and signed off.
- `tasks.md` fully checked off.

## Follow-ups / Backlog
- Track re-upload/replace-in-place (keeping the same `id` while swapping file content) — deferred, not requested.
- Persisted per-track map visibility/color preferences — deferred (Q-055-02 explicitly scoped this out).
- JS automated test coverage for `TrackManager.vue`/`Map.vue` — blocked on this repo adopting a JS test runner at all (tracked generally under Q-051-05's prior finding, not specific to this feature).
