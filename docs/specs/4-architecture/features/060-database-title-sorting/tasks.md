# Feature 060 Tasks – Database-Driven Title Sorting

_Status: Draft_
_Last updated: 2026-08-27_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.
>
> **No Eloquent model events/hooks/mutators for `title_base`/`title_index`.** Per explicit user direction, every task in I2/I2b wires an *explicit* call at the point `title` is set — never a `saving`/`creating` hook. See spec.md FR-060-03/NFR-060-07.

## Checklist

### I1 – Schema migration

- [ ] T-060-01 – Confirm no foreign key references `photos.title` or `base_albums.title` (F-060-01).
  _Intent:_ De-risk I1 before writing the migration; grep migration history / introspect dev DB schema.
  _Verification commands:_
  - `grep -rn "foreign.*title" database/migrations/`
  _Notes:_ Expected result: no matches. Record in plan.md's Implementation Drift Gate.

- [ ] T-060-02 – Write migration adding `title_base`/`title_index` + composite index to `photos` (F-060-01, S-060-01).
  _Intent:_ New non-generated columns, nullable per FR-060-01/DO-060-01/02.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback --step=1`
  _Notes:_ Test against SQLite dev DB too, not just the primary driver.

- [ ] T-060-03 – Write migration adding `title_base`/`title_index` + composite index to `base_albums` (F-060-01, S-060-09).
  _Intent:_ Mirrors T-060-02; `title_base` NOT NULL per DO-060-03 (unlike photos).
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback --step=1`

### I2 – TitleSplitter + explicit write-site wiring (no hooks)

- [ ] T-060-04 – Unit tests for `TitleSplitter::split()` (F-060-02, S-060-16..21).
  _Intent:_ Write tests first: trailing digits, parenthesised fallback, no-digit fallback, case-fold, >19-digit truncation, empty string, unicode title, and the extension-suffix cases found via user review — `xxx_123.jpg`→(`xxx_`,123), `xxx (123)`→(`xxx `,123), `xxx (123).xts`→(`xxx `,123), `xxx.2`→(`xxx.`,2), `Vol.II`→(`vol.ii`,null) false-positive-extension fallback, and a mixed-extension pair (`photo_5.jpg`/`photo_5.heic`) confirming they do NOT tie (NFR-060-09).
  _Verification commands:_
  - `php artisan test --filter=TitleSplitterTest`
  _Notes:_ Expect red until T-060-05 lands.

- [ ] T-060-05 – Implement `TitleSplitter::split()` (F-060-02).
  _Intent:_ Ordered rule chain per spec Appendix reference implementation.
  _Verification commands:_
  - `php artisan test --filter=TitleSplitterTest`
  - `make phpstan`

- [ ] T-060-06 – Wire explicit `TitleSplitter::split()` call + `title_base`/`title_index` assignment at the 3 non-bulk photo write sites: `app/Actions/Photo/Pipes/Shared/Save.php`, `PhotoController::update()`, `PhotoController::rename()` (F-060-03, S-060-12).
  _Intent:_ One explicit call right after `title` is set / right before `save()` at each site — no hook.
  _Verification commands:_
  - `php artisan test --filter=PhotoTitleSyncTest`

- [ ] T-060-07 – Wire the split inline into `app/Actions/Renamer/RenamePhotos.php`'s `batch()->update()` value-map closure (F-060-03, S-060-12).
  _Intent:_ This call bypasses `save()`/model events entirely (`mavinoo/laravelBatch`) — `title_base`/`title_index` must be added directly to the `$values` array built in the closure, not deferred anywhere else.
  _Verification commands:_
  - `php artisan test --filter=PhotoTitleSyncTest`

- [ ] T-060-08 – Wire explicit `TitleSplitter::split()` calls at the 4 album-creation/regular-edit write sites: `app/Actions/Album/Create.php`, `CreateTagAlbum.php`, `CreatePersonAlbum.php`, `SetHeader.php` (F-060-03, S-060-09, S-060-12).
  _Verification commands:_
  - `php artisan test --filter=AlbumTitleSyncTest`

- [ ] T-060-09 – Wire explicit `TitleSplitter::split()` calls at the 3 remaining `AlbumController` write sites: `updateTagAlbum()`, `updatePersonAlbum()`, `rename()` (F-060-03, S-060-09, S-060-12).
  _Verification commands:_
  - `php artisan test --filter=AlbumTitleSyncTest`

- [ ] T-060-10 – Wire the split inline into `app/Actions/Renamer/RenameAlbums.php`'s `batch()->update()` value-map closure (F-060-03, S-060-09, S-060-12).
  _Intent:_ Same bypass concern as T-060-07.
  _Verification commands:_
  - `php artisan test --filter=AlbumTitleSyncTest`

- [ ] T-060-11 – Grep sweep re-confirming the 12 write sites above are the complete list; document result in plan.md's Implementation Drift Gate (N-060-07).
  _Verification commands:_
  - `grep -rn "->title\s*=" app/ --include="*.php"`
  - `grep -rln "'title'\s*=>" app/Actions/ app/Http/Requests/ app/Models/`

### I2b – Data-integrity safety net (replaces a hook)

- [ ] T-060-12 – Write `tests/Feature_v2/TitleSplitIntegrityTest.php`: exercise all 12 write sites, then assert every resulting `photos`/`base_albums` row's `title_base`/`title_index` equals a fresh `TitleSplitter::split(title)` recomputation (F-060-03, S-060-15).
  _Intent:_ The regression guard for the "no hook" trade-off (NFR-060-07) — catches a future write site that forgets the explicit call.
  _Verification commands:_
  - `php artisan test --filter=TitleSplitIntegrity`

### I3 – Backfill migration

- [ ] T-060-13 – Chunked backfill migration for `photos.title_base`/`title_index` (F-060-04).
  _Intent:_ 500-1000 row batches, uses `TitleSplitter::split()`.
  _Verification commands:_
  - `php artisan migrate`
  - Feature test asserting seeded dataset backfills correctly.

- [ ] T-060-14 – Chunked backfill migration for `base_albums.title_base`/`title_index` (F-060-04, S-060-09).
  _Intent:_ Mirrors T-060-13.
  _Verification commands:_
  - `php artisan migrate`

- [ ] T-060-15 – Idempotency test: re-running the backfill on an already-migrated dataset is a no-op (S-060-11).
  _Intent:_ Confirms safe re-run semantics.
  _Verification commands:_
  - `php artisan test --filter=TitleBackfillTest`

### I4 – Enum & SortingDecorator changes

- [ ] T-060-16 – Remove `TITLE_STRICT`/`DESCRIPTION`/`DESCRIPTION_STRICT` from `ColumnSortingType` (F-060-05, F-060-06).
  _Intent:_ Update `toColumn()` accordingly.
  _Verification commands:_
  - `make phpstan`

- [ ] T-060-17 – Mirror removal in `ColumnSortingAlbumType`/`ColumnSortingPhotoType` (F-060-05, F-060-06).
  _Verification commands:_
  - `make phpstan`

- [ ] T-060-18 – Extend `ColumnSortingType::getRawOrderExpression()` to accept `$direction`; update `TITLE` case per FR-060-05 (F-060-05, S-060-01, S-060-02, S-060-04).
  _Intent:_ `{prefix}title_base {dir}, COALESCE({prefix}title_index, -1) {dir}`.
  _Verification commands:_
  - `php artisan test --filter=ColumnSortingTypeTest`

- [ ] T-060-19 – Update `SortingDecorator::applySqlSorting()` call site for the new `getRawOrderExpression()` signature; verify `RATING_AVG` (Feature 009) unaffected (F-060-05).
  _Intent:_ Regression guard called out in plan.md Risks.
  _Verification commands:_
  - `php artisan test --filter=SmartAlbum`
  - `php artisan test --filter=Rating`

- [ ] T-060-20 – Delete `SortingDecorator::POSTPONE_COLUMNS`, `applyPhpSorting()`, `$pivot_idx` bookkeeping; simplify `orderBy()`/`orderPhotosBy()`/`get()`/`paginate()` (F-060-07, N-060-02).
  _Verification commands:_
  - `php artisan test --filter=Sorting`
  - `make phpstan`

- [ ] T-060-21 – Update `SearchSortingType::toPhotoColumn()`/`toAlbumColumn()` to map `TITLE` directly, no `_STRICT` redirect (F-060-09, S-060-10).
  _Verification commands:_
  - `php artisan test --filter=Search`

- [ ] T-060-22 – New feature tests: `PhotoTitleSortingTest`, `AlbumTitleSortingTest` covering S-060-01..06, S-060-09.
  _Verification commands:_
  - `php artisan test --filter=TitleSorting`

### I5 – Relation match() cleanup

- [ ] T-060-23 – Simplify `HasManyPhotosByPerson::match()` (F-060-08, S-060-13).
  _Verification commands:_
  - `php artisan test --filter=Person`

- [ ] T-060-24 – Simplify `HasManyChildPhotos::match()` (F-060-08, S-060-13).
  _Verification commands:_
  - `php artisan test --filter=Album`

- [ ] T-060-25 – Simplify `HasManyPhotosRecursively::match()` (F-060-08, S-060-13).
  _Verification commands:_
  - `php artisan test --filter=Album`

- [ ] T-060-26 – Simplify `HasManyPhotosByTag::match()` (F-060-08, S-060-13).
  _Verification commands:_
  - `php artisan test --filter=Tag`

- [ ] T-060-27 – Simplify `HasManyChildAlbums::match()` (F-060-08, S-060-13).
  _Verification commands:_
  - `php artisan test --filter=Album`

### I6 – Config migration

- [ ] T-060-28 – Data migration rewriting `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` values (`title_strict`/`description`/`description_strict` → `title`) and narrowing `type_range` (F-060-10, S-060-07, S-060-08).
  _Verification commands:_
  - `php artisan migrate`
  - New config-migration feature test with seeded pre-upgrade values.

- [ ] T-060-29 – Idempotency check: re-running the config migration is a no-op (S-060-07, S-060-08).
  _Verification commands:_
  - Same test as T-060-28, run migration twice.

### I7 – Frontend

- [ ] T-060-30 – Remove `description`/`description_strict`/`title_strict` entries from `photoSortingColumnsOptions`/`albumSortingColumnsOptions` in `resources/js/config/constants.ts`; update retained `title` labels (F-060-11).
  _Verification commands:_
  - `npm run check`

- [ ] T-060-31 – Regenerate `resources/js/lychee.d.ts` from the updated PHP enums (F-060-11, N-060-06).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Never hand-edit; use the project's existing TypeScript-transformer build step.

- [ ] T-060-32 – Manual browser verification: v7 and v8 Settings → General sort dropdowns, `AlbumProperties.vue`, Bulk Album Edit (S-060-14, UI-060-01, UI-060-02).
  _Verification commands:_
  - Manual (dev server) — screenshot both frontends' dropdown state.

### I8 – Locale cleanup

- [ ] T-060-33 – Sweep all `lang/<locale>/gallery.php` (23 locales) removing the 6 orphaned `sort.*` keys (`photo_select_4`, `photo_select_3_strict`, `photo_select_4_strict`, `album_select_3`, `album_select_2_strict`, `album_select_3_strict`); update `lang/en/gallery.php`'s retained `photo_select_3`/`album_select_2` text; regenerate every `lang/<locale>.json` via `php artisan lang:json` (F-060-12).
  _Verification commands:_
  - `php artisan lang:json`
  - `php artisan test --filter=LangTest`
  _Notes:_ Edit only the `.php` sources — `lang/<locale>.json` is a generated artifact (`App\Console\Commands\Laravel\LangFilesToJson`), never hand-edited.

### I9 – Quality gate & documentation

- [ ] T-060-34 – Full quality gate: `php artisan test` (or documented targeted-filter equivalent), `make phpstan`, `php-cs-fixer`, `npm run check`, `npm run format`.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `npm run check`
  - `npm run format`

- [ ] T-060-35 – Update `docs/specs/4-architecture/roadmap.md` (move Feature 060 to Completed) and `docs/specs/4-architecture/knowledge-map.md` if it documents sorting infrastructure.
  _Verification commands:_
  - N/A (docs only).

## Notes / TODOs

- Q-060-01 (Description scope) and Q-060-02 (splitter design) were resolved during spec drafting (2026-08-27) — see `docs/specs/4-architecture/open-questions.md`.
- Design correction (2026-08-27, same session): the original draft of I2 specified an Eloquent `saving` hook; the user explicitly rejected this ("we do not trust the hooks, we implement things directly without magic") — I2 was rewritten as 12 explicit write-site calls, backstopped by I2b's `TitleSplitIntegrityTest` instead of hook-based enforcement.
- Design correction (2026-08-27, same session): I8 originally targeted `lang/*.json` directly; the user corrected that these are generated artifacts — the real edit surface is `lang/<locale>/*.php`, regenerated via `php artisan lang:json`.
- If the repo's full `php artisan test` run hits the documented pre-existing process-timeout issue (Features 052/055/056 precedent), fall back to targeted `--filter` runs per touched area and note it here.
