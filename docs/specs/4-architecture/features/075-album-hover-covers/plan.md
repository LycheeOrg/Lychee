# Feature Plan 075 – Album Hover Side Covers

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented 2026-09-30 — manual browser check T-075-22 pending (no browser environment in this session)  
_Last updated:_ 2026-09-30

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Hovering an album tile in the v8 struct-of-arrays grids shows two different photos fanning out behind the cover, for regular, tag, person and smart albums, with a gallery setting to switch it off. Success: every v3 listing carries `cover_ids_2` / `cover_ids_3` with no extra per-row query (NFR-075-01), the recompute job runs no extra query (NFR-075-02), least-privilege sides never leak (NFR-075-03), the asset endpoint serves the sides with no per-request permission re-check (NFR-075-04), and v2/v7 diffs are empty (NFR-075-05).

## Scope Alignment
- **In scope:** FR-075-01..13: two schema migrations, one config migration, `RecomputeAlbumStatsJob` ranks 1–3, `Thumb::createManyFromQueryable()`, `CachesAlbumUserThumb` and `RecomputeAlbumUserThumbsJob` three-column writes, the `SideCoverIds` helper, additive arrays on `AlbumDataResource` / `AlbumCategoryResource` and their seven producers, the asset-endpoint cover exception, `PurgeAlbumUserThumbs`, the v8 tile and adapters, regenerated types.
- **Out of scope:** NG1–NG8 in the spec (v7, v8 flag-off tile, list rows, Flow, more than two sides, random smart thumbs, live computation in listings, fan CSS, frontend test runner).

## Dependencies & Interfaces
- ADR-0003 (precomputed covers, `lychee:recompute-album-stats` backfill), ADR-0009 (SoA shape), ADR-0010 (cache purge invariant), ADR-075-01 (this feature's storage decision).
- Feature 053 managed listing cache: `SettingsController::ALBUM_LISTING_COARSE_FLUSH_CONFIGS` for the new setting.
- Feature 061/062/069 producers: `BuildAlbumDataResource`, `AlbumRootController::toResource()`, `AlbumPinnedController`, `AlbumSmartController`, `AlbumTagController`, `AlbumPersonController`, `QuerySearchAlbums` (via the builder).
- `php artisan typescript:transform` for `resources/js/lychee.d.ts`.
- Test bases: `AbstractTestCase` (Unit, Precomputing), `BaseApiWithDataTest` (Feature_v3). No new Feature_v2 tests.

## Assumptions & Risks
- **Assumptions:** the SQLite test database applies the new migrations automatically; `AlbumUserThumb` seeding in tests goes through the model (no factory exists).
- **Risks / Mitigations:**
  - `AlbumCategoryV3Test` may assert `null` person covers today; FR-075-09 changes that once a cache row exists. Update the assertion together with the new expectations, do not weaken it.
  - Managed listing cache keys carry no side-cover dimension; the flush-config entry (FR-075-04) is the mitigation, covered by S-075-18.
  - `limit(3)` on MySQL: the query keeps its existing join and ordering, only the row count changes; the MIN/MAX date query is untouched.
  - The asset exception list grows to seven ids per album; still an in-memory `in_array` on an already-loaded model.

## Implementation Drift Gate
After all tasks are `[x]`: rerun `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`, `npm run check`, and every test class named in tasks.md; confirm `git diff --stat -- resources/js/v7 app/Http/Resources/Models app/Relations/HasAlbumThumb.php` is empty (NFR-075-05); walk FR-075-01..13 against the diff and record the traceability table and any lessons below in `## Drift Gate Record`.

## Increment Map

1. **I1 – Schema and setting**
   - _Goal:_ FR-075-01, FR-075-04, FR-075-08 columns; DO-075-01/02/05.
   - _Preconditions:_ none.
   - _Steps:_ migration adding the four `albums` columns + FKs (down drops FKs then columns); migration adding `photo_id_2`/`photo_id_3` to `album_user_thumbs` + FKs SET NULL; `BaseConfigMigration` for `album_hover_side_covers_enabled`; `Album` and `AlbumUserThumb` attribute docblocks/`$fillable`; add the key to `V8_CONFIGS` and `ALBUM_LISTING_COARSE_FLUSH_CONFIGS`. Failing test first: `AlbumSideCoversV3Test::testSettingChangeFlushesListingCache` (S-075-18).
   - _Commands:_ `php artisan test --filter=AlbumSideCoversV3Test`, `make phpstan`.
   - _Exit:_ migrations apply on SQLite, S-075-18 green.
2. **I2 – Recompute job ranks 1–3**
   - _Goal:_ FR-075-02, FR-075-03, NFR-075-02, NFR-075-03.
   - _Preconditions:_ I1.
   - _Steps:_ failing tests in `RecomputeAlbumStatsJobTest` (S-075-01, S-075-02, query count) and `AlbumCoverSecurityTest` (S-075-03); rename `getPhotoIdForUser()` → `getPhotoIdsForUser(): array<int,string>` with `limit(3)`; assign the six columns; extend the debug log line.
   - _Commands:_ `php artisan test --filter=RecomputeAlbumStatsJobTest`, `php artisan test --filter=AlbumCoverSecurityTest`, `make phpstan`.
   - _Exit:_ all green, no new query in the job.
3. **I3 – `SideCoverIds` helper**
   - _Goal:_ FR-075-05, FR-075-09 pure rule, DO-075-03.
   - _Preconditions:_ I1.
   - _Steps:_ failing `tests/Unit/Actions/SideCoverIdsTest` covering: disabled, null primary, owner vs guest triple, drop nulls, drop primary (ranks 1/2/3 cases), locked gate with both config flags, `fromCacheRow` with/without row; implement `App\Actions\Album\StructOfArrays\SideCoverIds` as two static methods with straight-line filtering.
   - _Commands:_ `php artisan test --filter=SideCoverIdsTest`, `make phpstan`.
   - _Exit:_ green.
4. **I4 – Regular-album listings**
   - _Goal:_ FR-075-06, FR-075-07 (pinned half), NFR-075-01.
   - _Preconditions:_ I2, I3.
   - _Steps:_ failing tests in `AlbumSideCoversV3Test` (S-075-04..07, S-075-16 children/root/pinned/search, query count); add the four columns to the three row selects and the `TRow` phpstan type; add `cover_ids_2`/`cover_ids_3` to `AlbumDataResource` and `AlbumCategoryResource` and fill them in `BuildAlbumDataResource`, `AlbumRootController::toResource()`, `BuildsAlbumCategoryResource` (pinned). Smart/tag/person pass `[null, null]` arrays in this increment so the resource stays constructible; I6 fills them.
   - _Commands:_ `php artisan test --filter=AlbumSideCoversV3Test`, `php artisan test --filter=AlbumRootV3Test`, `php artisan test --filter=AlbumCategoryV3Test`, `php artisan test --filter=QuerySearchAlbumsTest`, `make phpstan`.
   - _Exit:_ green.
5. **I5 – Asset authorization for album side columns**
   - _Goal:_ FR-075-10 (album half), NFR-075-04.
   - _Preconditions:_ I2.
   - _Steps:_ failing tests in `PhotoAssetV3Test` (S-075-08, S-075-09); extend the `in_array` list in `isPhotoOfAlbum()`.
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`.
   - _Exit:_ green.
6. **I6 – Cache store: three ids per viewer row**
   - _Goal:_ FR-075-08 (behaviour), FR-075-09, FR-075-10 (cache half), FR-075-11, DO-075-04.
   - _Preconditions:_ I1, I3, I4.
   - _Steps:_ failing tests: `RecomputeAlbumUserThumbsJobTest` (S-075-15), `TagAlbumTest`/`PersonAlbumTest` (three-column seeding), `AlbumCategoryV3Test` (S-075-10..12), `PhotoAssetV3Test` (S-075-10 asset half), new `PurgeSideCoversV3Test` (S-075-13, S-075-14). Implement `Thumb::createManyFromQueryable()`; `getCachedOrLiveThumb()` closure returns the collection and seeds three columns; job writes three; `isComputedAlbumThumb()` matches three columns; `forBaseAlbums()` matches three columns; smart/tag/person controllers batch-read the cache rows and fill primary + sides.
   - _Commands:_ `php artisan test --filter=RecomputeAlbumUserThumbsJobTest`, `php artisan test --filter=TagAlbumTest`, `php artisan test --filter=PersonAlbumTest`, `php artisan test --filter=AlbumCategoryV3Test`, `php artisan test --filter=PhotoAssetV3Test`, `php artisan test --filter=PurgeSideCoversV3Test`, `php artisan test --filter=SharingTest` (existing v2 purge coverage), `make phpstan`.
   - _Exit:_ green. Split into I6a (Thumb helper + cache trait + job) and I6b (controllers + asset + purge) if either half nears 90 minutes.
7. **I7 – Frontend**
   - _Goal:_ FR-075-12, FR-075-13, S-075-17.
   - _Preconditions:_ I4, I6.
   - _Steps:_ `php artisan typescript:transform`; `AdaptedAlbumTile` + two fields; fill in `adaptAlbumChildTile.ts`, `adaptCategoryTile.ts`, and the root store's tile assembly (`AlbumsState.ts`); `AlbumThumbVirtual.vue` back layers use `cover_id_2 ?? cover_id` / `cover_id_3 ?? cover_id`. `npm run format`, `npm run check`. Manual browser check when an environment is available.
   - _Commands:_ `php artisan typescript:transform`, `npm run format`, `npm run check`.
   - _Exit:_ type-check green; S-075-17 recorded as done or as pending manual check.
8. **I8 – Docs and drift gate**
   - _Goal:_ knowledge map, roadmap, ADR status, drift gate record, commit preparation.
   - _Preconditions:_ I1–I7.
   - _Steps:_ knowledge-map entries (spec Documentation Deliverables); roadmap row → Implemented; run the Implementation Drift Gate and record it below; stage files and prepare the Conventional Commit per AGENTS.md.
   - _Commands:_ full quality gate (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every touched test class, `make phpstan`).
   - _Exit:_ commit command handed to the operator.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-075-01 | I2 / T-075-04 | `RecomputeAlbumStatsJobTest` |
| S-075-02 | I2 / T-075-04 | `RecomputeAlbumStatsJobTest` |
| S-075-03 | I2 / T-075-05 | `AlbumCoverSecurityTest` |
| S-075-04 | I4 / T-075-09 | `AlbumSideCoversV3Test` |
| S-075-05 | I3, I4 / T-075-07, T-075-09 | helper unit + listing |
| S-075-06 | I3, I4 / T-075-07, T-075-09 | helper unit + listing |
| S-075-07 | I3, I4 / T-075-07, T-075-09 | helper unit + listing |
| S-075-08 | I5 / T-075-11 | `PhotoAssetV3Test` |
| S-075-09 | I5 / T-075-11 | `PhotoAssetV3Test` |
| S-075-10 | I6 / T-075-14, T-075-16, T-075-17 | seeding, listing, asset |
| S-075-11 | I6 / T-075-16 | `AlbumCategoryV3Test` |
| S-075-12 | I6 / T-075-16 | `AlbumCategoryV3Test` |
| S-075-13 | I6 / T-075-18 | `PurgeSideCoversV3Test` |
| S-075-14 | I6 / T-075-18 | `PurgeSideCoversV3Test` |
| S-075-15 | I6 / T-075-13 | `RecomputeAlbumUserThumbsJobTest` |
| S-075-16 | I4 / T-075-09 | `AlbumSideCoversV3Test` |
| S-075-17 | I7 / T-075-22 | manual browser check |
| S-075-18 | I1 / T-075-01 | `AlbumSideCoversV3Test` |

## Analysis Gate
Run 2026-09-30 (agent, owner ildyria) against spec/plan/tasks as of this date.

1. **Specification completeness** — PASS. Goals G1–G5, FR-075-01..13, NFR-075-01..06 populated. Q-075-01..05 resolutions folded into FR/NFR/NG sections and ADR-075-01. ASCII mock-up present (tile states and the settings row).
2. **Open questions review** — PASS. No `Open` rows remain in open-questions.md. ADR-075-01 covers the schema and cache-shape decision (Q-075-01, Q-075-02).
3. **Plan alignment** — PASS. Plan links spec.md and tasks.md; dependencies name ADR-0003/0009/0010/075-01 and the seven listing producers; success criteria quote NFR-075-01..05.
4. **Tasks coverage** — PASS. FR-075-01 → T-02; FR-02 → T-04/06; FR-03 → T-06; FR-04 → T-01/03; FR-05 → T-07/08; FR-06 → T-09/10; FR-07 → T-10/16; FR-08 → T-02/13/14/15; FR-09 → T-16; FR-10 → T-11/12/17; FR-11 → T-18; FR-12 → T-20/21; FR-13 → T-19. Every increment stages a failing test before its implementation task. I6 carries an explicit split rule (I6a/I6b) if it nears 90 minutes.
5. **Working-agreement compliance** — PASS. No new dependency. Branching is pushed into `SideCoverIds` (two pure static methods) and `Thumb::createManyFromQueryable()`; listing producers gain one call each. ADR-0003, ADR-0009, ADR-0010 reviewed. No fallback/compat behaviour beyond the `null` → cover fallback the spec requires (FR-075-12).
6. **Tooling readiness** — PASS. Commands listed per task; `php artisan typescript:transform` in I7; drift-gate commands in the Implementation Drift Gate section.

Findings: none blocking. Note for I4: the pinned listing's `BuildsAlbumCategoryResource` trait is shared with tags; the side-cover call must stay behind the same `$resolve_cover` opt-in so the tag path keeps its own rule (FR-075-09).

## Exit Criteria
- All tasks `[x]`, full quality gate green, `php artisan typescript:transform` rerun.
- Drift gate recorded below, NFR-075-05 empty-diff check passed.
- Roadmap row 075 → Implemented, knowledge map updated, ADR-075-01 Accepted.
- Commit prepared for the operator.

## Upgrade Note
After deploying, run `php artisan lychee:recompute-album-stats` once to fill side covers for existing albums (FR-075-03). Tag/person/smart rows fill on their next recompute or first view.

## Prompt & Tool Usage Notes
Started from GitHub discussion #4742 on branch `tripple`. Discovery established that the v7/v8 tiles already render the three-layer fan with one `cover_id`. Five decision cards (Q-075-01..05) were answered A, B, B, A, A; ADR-075-01 fixes the column-based storage on both cover stores. Increments I1–I8 ran test-first in order, with `php artisan test --filter=<Class>` per class (never concurrent), `make phpstan` after each backend increment, `php artisan typescript:transform` + `npm run format` + `npm run check` for I7, `vendor/bin/php-cs-fixer fix` before the drift gate.

## Follow-ups / Backlog
- Side covers for the v8 flag-off tile and v7 (NG1) if the v2 path is ever kept long-term.
- A `FulfillPreCompute` condition for rows with a primary cover but `NULL` sides, if operators prefer the maintenance page over the bulk command.

## Drift Gate Record
Run 2026-09-30 after T-075-01..21 and T-075-23.

- **Preconditions:** all code tasks `[x]`; T-075-22 (manual browser check of the fan, S-075-17) is the only open task — no browser environment was available. Spec status says so.
- **Commands (all green):** `vendor/bin/php-cs-fixer fix` (6 files restyled), `make phpstan` (0 errors), `php artisan typescript:transform`, `npm run format`, `npm run check` (exit 0), and each of: `RecomputeAlbumStatsJobTest` (17), `AlbumCoverSecurityTest` (5), `RecomputeAlbumStatsCommandTest` (13), `SideCoverIdsTest` (10), `AlbumSideCoversV3Test` (7), `AlbumRootV3Test` (30), `AlbumCategoryV3Test` (20), `QuerySearchAlbumsTest` (9), `AlbumChildrenDataV3Test` (24), `PhotoAssetV3Test` (34), `RecomputeAlbumUserThumbsJobTest` (11), `TagAlbumTest` (6), `PersonAlbumTest` (15), `PurgeSideCoversV3Test` (2), `SharingTest` (30), `FulfillPreComputeTest` (8).
- **NFR-075-05 empty-diff check:** `git diff --stat -- resources/js/v7 app/Http/Resources/Models app/Relations/HasAlbumThumb.php` is empty.
- **Query-count evidence:** `RecomputeAlbumStatsJob::handle()` stays at 35 queries for the 3-photo fixture (NFR-075-02); `GET /Albums/{id}` stays at 21 queries for the 1-child fixture (NFR-075-01); the tags listing runs exactly one `album_user_thumbs` query and no `photos`/`size_variants` query.

| FR | Evidence |
|----|----------|
| FR-075-01 | `2026_09_30_000001_add_side_covers_to_albums.php`, `Album` attributes/casts; S-075-14 in `PurgeSideCoversV3Test` |
| FR-075-02 | `RecomputeAlbumStatsJob::getPhotoIdsForUser()` (`limit(3)`); S-075-01/02 in `RecomputeAlbumStatsJobTest`, S-075-03 in `AlbumCoverSecurityTest` |
| FR-075-03 | `FulfillPreCompute` untouched (`FulfillPreComputeTest` green); bulk command covered by `RecomputeAlbumStatsCommandTest` |
| FR-075-04 | `2026_09_30_000003_add_album_hover_side_covers_config.php`; `SettingsController::V8_CONFIGS` / `ALBUM_LISTING_COARSE_FLUSH_CONFIGS`; S-075-18 |
| FR-075-05 | `SideCoverIds::forAlbumRow()`/`isHiddenByLock()`; `SideCoverIdsTest`, S-075-04..07 in `AlbumSideCoversV3Test` |
| FR-075-06 | `AlbumDataResource`, `BuildAlbumDataResource`, `AlbumRootController`; S-075-16 |
| FR-075-07 | `AlbumCategoryResource`, `BuildsAlbumCategoryResource` (pinned), `AlbumPinnedController` select; S-075-16 |
| FR-075-08 | `2026_09_30_000002_add_side_photos_to_album_user_thumbs.php`, `Thumb::createManyFromQueryable()`, `CachesAlbumUserThumb`, `RecomputeAlbumUserThumbsJob`; S-075-10/15 |
| FR-075-09 | `AlbumUserThumb::rowsForViewer()`, smart/tag/person controllers, `SideCoverIds::fromCacheRow()`; S-075-10..12 in `AlbumCategoryV3Test` |
| FR-075-10 | `GetPhotoAssetRequest::isPhotoOfAlbum()`/`isComputedAlbumThumb()`; S-075-08/09/10 in `PhotoAssetV3Test` |
| FR-075-11 | `PurgeAlbumUserThumbs::forBaseAlbums()`; S-075-13 |
| FR-075-12 | `AdaptedAlbumTile`, `adaptAlbumChildTile.ts`, `adaptCategoryTile.ts`, `AlbumThumbVirtual.vue`; `npm run check`; S-075-17 pending manual |
| FR-075-13 | `resources/js/lychee.d.ts` regenerated (`cover_ids_2`/`cover_ids_3` typed `(string \| null)[]` on both resources) |

Lessons: (1) a `//` comment between a Data class's opening brace and its constructor docblock makes `typescript:transform` lose the `@param` types (`Array<any>`); keep the explanation in the class docblock. (2) In the SQLite test run, `PhotoDeleted` triggers a synchronous `RecomputeAlbumStatsJob`, so an FK SET NULL assertion needs `Queue::fake()` to observe the null before the recompute refills it.
