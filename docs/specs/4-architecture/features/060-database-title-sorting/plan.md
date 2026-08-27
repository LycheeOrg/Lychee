# Feature Plan 060 – Database-Driven Title Sorting

_Linked specification:_ `docs/specs/4-architecture/features/060-database-title-sorting/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-27

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Every title-based sort (`photos` and `base_albums`) resolves in a single SQL `ORDER BY`, with no PHP-level `Collection::sortBy()` fallback anywhere in the codebase for these two models. Natural-sort-like behaviour (`test_0, test_1, test_2, test_10`) is preserved via two new derived columns computed identically across MySQL/MariaDB, PostgreSQL, and SQLite. Description disappears as a sort option; all 3 sort-related config keys auto-migrate cleanly on upgrade. `make phpstan` 0 errors, `php-cs-fixer` clean, `npm run check` clean, full relevant test suites green.

## Scope Alignment

- **In scope:**
  - New `title_base`/`title_index` columns + composite index on `photos` and `base_albums`.
  - `TitleSplitter` pure-function service + centralized `saving`-hook wiring on `Photo`/`BaseAlbumImpl`.
  - Backfill migration for existing rows.
  - `ColumnSortingType`/`ColumnSortingAlbumType`/`ColumnSortingPhotoType`/`SearchSortingType` enum changes.
  - `SortingDecorator` simplification (delete `POSTPONE_COLUMNS`/`applyPhpSorting()`/pivot-index machinery).
  - The 5 relation `match()` methods with duplicated PHP natural-sort logic.
  - `configs` data migration for `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` (value rewrite + `type_range` narrowing).
  - Frontend: `resources/js/config/constants.ts` dropdown options, `lychee.d.ts` regeneration, 23-locale translation key cleanup.
- **Out of scope:** Everything listed under spec.md's Non-Goals — description filtering/search, other sort columns, a pluggable pattern system, `title` column semantics/validation/display.

## Dependencies & Interfaces

- Feature 009 (Rating Ordering) — `requiresRawOrdering()`/`getRawOrderExpression()` extension point on `ColumnSortingType` is reused/extended, not replaced; `RATING_AVG`'s existing behaviour must remain unaffected (regression risk to watch).
- Feature 017 (Apply Renamer Rules) — writes to `title` via `RenamePhotos`/`RenameAlbums`; must keep working unchanged (verified via the centralized hook, not touched directly).
- Feature 028 (Search UI Refactor) / `PhotoSearch`/`AlbumSearch` — description remains a search filter field; only `SearchSortingType`'s title-sort mapping changes (FR-060-09).
- `database/migrations/TemporaryModels/OptimizeTables.php` — reuse its idempotent `dropIndexIfExists` helper for the migration's `down()`/re-run safety.
- `Spatie\TypeScriptTransformer` build step — regenerates `resources/js/lychee.d.ts` after PHP enum changes.

## Assumptions & Risks

- **Assumptions:**
  - No existing code path performs a raw/mass `Builder::update(['title' => ...])` on `Photo`/`BaseAlbumImpl` that would bypass the `saving` hook (NFR-060-07) — confirmed by a grep sweep in I2, not merely assumed.
  - `base_albums.title` is genuinely NOT NULL in every supported install (per research); the `title_base` column can safely mirror that non-nullability.
  - 19 digits is a safe, generous ceiling for `title_index` (unsigned 64-bit max is ~1.8×10^19, i.e. 20 digits, but we deliberately cap at 19 to always stay within range even for the maximum unsigned representable magnitude).
- **Risks / Mitigations:**
  - *Risk:* MySQL/MariaDB's FK constraints could complicate the composite index creation the same way `storedAs()` needed an FK-drop dance in the Feature 009-adjacent precedent. *Mitigation:* `title_base`/`title_index` are ordinary columns (not generated), so no FK-drop dance is needed — confirm during I1 that no FK references `photos.title`/`base_albums.title` (research found none).
  - *Risk:* The explicit-call-site design (12 sites, no hook) means a future write path added after this feature ships could forget the `TitleSplitter::split()` call, leaving that row's derived columns silently stale. *Mitigation:* deliberate, accepted trade-off per explicit user direction ("we do not trust the hooks, we implement things directly without magic") — I2b's permanent `TitleSplitIntegrityTest` (S-060-15) is the regression guard instead of a hook.
  - *Risk:* The two `batch()->update()` bulk-rename call sites (`RenamePhotos.php`/`RenameAlbums.php`, `mavinoo/laravelBatch`) bypass Eloquent `save()`/events entirely — the split **must** be computed inline in the closure that builds the batch `$values` array, not deferred anywhere else, or those two paths silently drift. *Mitigation:* called out explicitly in I2's steps; covered by I2b's integrity test.
  - *Risk:* Removing `POSTPONE_COLUMNS ` entirely could silently break the 2 call sites (`Thumb.php:76-81`, `RecomputeAlbumStatsJob.php:235-250`) that already bypass `SortingDecorator` — these use `toColumn()` directly and are unaffected by this change, but must be spot-checked in I3 since `TITLE_STRICT`'s removal changes what `toColumn()` returns for a `title_strict`-valued config that might still be read there before the config migration runs.
  - *Risk:* Backfilling large installs (100k+ photos) chunked in PHP could be slow. *Mitigation:* chunk size tunable (500-1000), migration wrapped per-chunk transaction, documented as a one-time cost in tasks.md.
  - *Risk:* `getRawOrderExpression()`'s new `$direction` parameter changes its call signature — must audit every existing caller (currently only `RATING_AVG` via `SortingDecorator::applySqlSorting()`) so Feature 009's rating sort doesn't regress.

## Implementation Drift Gate

Record here, once implementation starts: (1) whether the grep sweep in I2 found any raw-update write path on `title` that needed extra handling, (2) actual chunk size/timing observed backfilling the dev DB, (3) any `EXPLAIN` findings from NFR-060-04's performance check, (4) any locale files found missing the 6 orphaned keys (i.e., already inconsistent before this feature).

## Increment Map

1. **I1 – Schema migration (both tables, all 3 drivers)**
   - _Goal:_ Add `title_base`/`title_index` + composite index to `photos` and `base_albums`; write `down()`.
   - _Preconditions:_ Spec approved (this document).
   - _Steps:_ Confirm no FK references `title` on either table (`Schema::hasColumn`/introspection or a direct grep of migration history for `foreign('title')` — expected: none). Write migration using plain (non-generated) column definitions per FR-060-01/DO-060-01..04. Add composite index `(title_base, title_index)` per table. Write reversible `down()`.
   - _Commands:_ `php artisan migrate`, `php artisan migrate:rollback --step=1` (dev DB), repeat against a local SQLite DB to confirm driver parity.
   - _Exit:_ Migration applies/rolls back cleanly on MySQL/MariaDB, PostgreSQL, and SQLite dev instances.

2. **I2 – `TitleSplitter` service + explicit write-site wiring**
   - _Goal:_ Implement FR-060-02/03. **No Eloquent model events/hooks/mutators** — every call is explicit and visible at the point `title` changes, per direct user instruction ("we do not trust the hooks, we implement things directly without magic").
   - _Preconditions:_ I1 merged.
   - _Steps:_ Write `TitleSplitter::split()` (unit-tested first — trailing digits, parenthesised fallback, no-digit fallback, case-fold, >19-digit truncation, empty string, unicode). Then add an explicit `TitleSplitter::split()` call + `title_base`/`title_index` assignment at each of the 12 identified write sites, immediately after `title` is set and before `save()`/batch-update:
     - Photos: `app/Actions/Photo/Pipes/Shared/Save.php`, `app/Http/Controllers/Gallery/PhotoController.php::update()`, `::rename()`, `app/Actions/Renamer/RenamePhotos.php` (inline in the `batch()->update()` value map — this call bypasses `save()` entirely, so it needs the split computed inline in the closure, not deferred to a shared save point).
     - Albums: `app/Actions/Album/Create.php`, `CreateTagAlbum.php`, `CreatePersonAlbum.php`, `SetHeader.php`, `app/Http/Controllers/Gallery/AlbumController.php::updateTagAlbum()`, `::updatePersonAlbum()`, `::rename()`, `app/Actions/Renamer/RenameAlbums.php` (same inline `batch()->update()` treatment as photos).
     - Grep sweep re-confirming these 12 are the complete list (NFR-060-07) — document result in the Implementation Drift Gate.
   - _Commands:_ `php artisan test --filter=TitleSplitter`, `make phpstan`.
   - _Exit:_ Unit tests green; a manual `tinker` check confirms creating/renaming a photo and an album (via each of the 12 sites) all populate the derived columns correctly.

3. **I2b – Data-integrity safety net**
   - _Goal:_ FR-060-03's accepted trade-off (no hook ⇒ no automatic enforcement) needs a permanent regression guard, per NFR-060-07.
   - _Preconditions:_ I2 merged.
   - _Steps:_ Write `tests/Feature_v2/TitleSplitIntegrityTest.php` (S-060-15) — exercises all 12 write sites, then asserts every resulting row's `title_base`/`title_index` equals a fresh `TitleSplitter::split(title)` recomputation. This is the mechanism that would catch a 13th write site added later (e.g. a future feature) that forgets the explicit call — deliberately a test, not a hook.
   - _Commands:_ `php artisan test --filter=TitleSplitIntegrity`.
   - _Exit:_ Test green against all 12 current write sites.

3. **I3 – Backfill migration**
   - _Goal:_ FR-060-04 — populate derived columns for pre-existing rows.
   - _Preconditions:_ I2 merged (needs `TitleSplitter`).
   - _Steps:_ Chunked update migration (500-1000 rows/batch) over `photos` then `base_albums`, using `TitleSplitter::split()`. Verify idempotency (S-060-11) by re-running against an already-backfilled dataset and asserting no changes.
   - _Commands:_ `php artisan migrate`, targeted feature test asserting backfilled values match `TitleSplitter::split()` output for a seeded dataset.
   - _Exit:_ All existing dev-DB rows have correct, non-null `title_base` (and correct `title_index` where applicable).

4. **I4 – Enum & `SortingDecorator` changes**
   - _Goal:_ FR-060-05/06/07/09 — collapse Title/Title-strict, remove Description entirely, delete PHP-fallback machinery, extend `getRawOrderExpression()` with direction.
   - _Preconditions:_ I1-I3 merged (columns must exist and be populated before sort queries reference them).
   - _Steps:_ Remove `TITLE_STRICT`/`DESCRIPTION`/`DESCRIPTION_STRICT` from all 3 sorting enums. Update `ColumnSortingType::requiresRawOrdering()`/`getRawOrderExpression($prefix, $direction)` for `TITLE` (verify `RATING_AVG`'s call site in `applySqlSorting()` still passes correctly with the new signature — regression risk noted in plan Risks). Delete `SortingDecorator::POSTPONE_COLUMNS`, `applyPhpSorting()`, `$pivot_idx` bookkeeping; simplify `orderBy()`/`orderPhotosBy()`/`get()`/`paginate()` accordingly. Update `SearchSortingType::toPhotoColumn()`/`toAlbumColumn()` (FR-060-09).
   - _Commands:_ `php artisan test --filter=Sorting`, `php artisan test --filter=SmartAlbum` (Feature 009 regression check), `make phpstan`.
   - _Exit:_ All existing sort-related tests pass or are updated to match new expected values; Feature 009 rating-sort tests unaffected.

5. **I5 – Relation `match()` cleanup**
   - _Goal:_ FR-060-08 — remove duplicated PHP natural-sort logic in eager-load paths.
   - _Preconditions:_ I4 merged.
   - _Steps:_ Simplify `match()` in `HasManyPhotosByPerson.php`, `HasManyChildPhotos.php`, `HasManyPhotosRecursively.php`, `HasManyPhotosByTag.php`, `HasManyChildAlbums.php` to preserve DB-provided order.
   - _Commands:_ `php artisan test --filter=Person`, `--filter=Tag`, `--filter=Album` (targeted, these relations are exercised broadly).
   - _Exit:_ Eager-loaded ordering matches direct-query ordering in tests (S-060-13).

6. **I6 – Config migration (existing installs)**
   - _Goal:_ FR-060-10 — rewrite `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` values and narrow `type_range`.
   - _Preconditions:_ I4 merged (target enum values must already be valid).
   - _Steps:_ Data migration rewriting `title_strict`/`description`/`description_strict` → `title` for all 3 keys; narrow `type_range`. Idempotency test (S-060-07/08).
   - _Commands:_ `php artisan migrate`, targeted config migration test.
   - _Exit:_ Seeded configs with old values end up correctly migrated; re-running is a no-op.

7. **I7 – Frontend (v7 + v8 shared)**
   - _Goal:_ FR-060-11 — dropdown option cleanup; TS type regeneration.
   - _Preconditions:_ I4 merged (PHP enums must be final).
   - _Steps:_ Remove the 3 orphaned options from `resources/js/config/constants.ts`'s `photoSortingColumnsOptions`/`albumSortingColumnsOptions`; update `title`/`album_select_2`'s label. Regenerate `lychee.d.ts` via the project's TypeScript-transformer build step. Manual browser check: Settings → General sort dropdown (both v7 and v8), per-album `AlbumProperties.vue`, Bulk Album Edit.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ `vue-tsc` clean; dropdowns show only "Title" (no Description, no "(Nat)"/"(Lexico)" suffixes) in both frontends.

8. **I8 – Locale cleanup**
   - _Goal:_ FR-060-12 — remove 6 orphaned keys across 23 locales' `gallery.php` source files, update `en`'s 2 retained labels, regenerate the derived JSON.
   - _Preconditions:_ I7 merged.
   - _Steps:_ Sweep all `lang/<locale>/gallery.php` files (23 locales) removing `photo_select_4`, `photo_select_3_strict`, `photo_select_4_strict`, `album_select_3`, `album_select_2_strict`, `album_select_3_strict` from the `'sort'` array. Update `lang/en/gallery.php`'s `photo_select_3`/`album_select_2` text to drop "(Nat)". Run `php artisan lang:json` to regenerate every `lang/<locale>.json` from the updated `.php` sources — never hand-edit the `.json` files.
   - _Commands:_ `php artisan lang:json`, then existing `LangTest` (mirrors Feature 054's translation-sweep precedent).
   - _Exit:_ `LangTest` green; regenerated `.json` files match the edited `.php` sources; no locale file references a removed key.

9. **I9 – Quality gate & documentation**
   - _Goal:_ Full-suite verification + doc updates.
   - _Preconditions:_ I1-I8 merged.
   - _Steps:_ Full `php artisan test` run (or targeted `--filter` runs per this repo's documented process-timeout precedent from Features 052/055/056), `make phpstan`, `php-cs-fixer`, `npm run check`/`npm run format`. Update `docs/specs/4-architecture/roadmap.md` (move to Completed) and `docs/specs/4-architecture/knowledge-map.md` if it documents sorting infrastructure.
   - _Commands:_ `php artisan test`, `make phpstan`, `npm run check`, `npm run format`.
   - _Exit:_ All quality gates green (or documented pre-existing/unrelated failures, per repo precedent).

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-060-01/02 | I4 | Core ordering behaviour |
| S-060-03 | I2/I4 | Parenthesised fallback rule |
| S-060-04 | I2/I4 | NULL-index sentinel behaviour |
| S-060-05 | I2/I4 | Case-insensitive parity |
| S-060-06 | I4 | Pagination correctness |
| S-060-07/08 | I6 | Config auto-migration |
| S-060-09 | I4 | Albums parity |
| S-060-10 | I4 | Search behaviour change |
| S-060-11 | I3 | Backfill idempotency |
| S-060-12 | I2 | Write-path coverage (incl. Renamer Rules), all 12 sites explicit |
| S-060-13 | I5 | Eager-load order preservation |
| S-060-14 | I7/I8 | Description remains searchable, not sortable |
| S-060-15 | I2b | Data-integrity safety net (no-hook trade-off) |

## Analysis Gate

Not yet run. Record date/reviewer/findings here once executed.

## Exit Criteria

- All tasks in tasks.md marked `[x]`.
- `make phpstan`: 0 errors.
- `php-cs-fixer`: clean.
- `npm run check`/`npm run format`: clean.
- `php artisan test` (or documented targeted-filter equivalent per repo precedent): green for all touched suites.
- `LangTest`: green (23-locale sweep confirmed clean).
- roadmap.md and knowledge-map.md updated.

## Follow-ups / Backlog

- A future feature could generalize `TitleSplitter` into a genuinely pluggable/admin-configurable pattern system (explicitly deferred, Q-060-02).
- A future feature could apply the same DB-driven approach to any other still-PHP-sorted column, if one is ever introduced.
