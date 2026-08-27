# Feature 060 – Database-Driven Title Sorting

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-27 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/060-database-title-sorting/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/060-database-title-sorting/tasks.md` |
| Roadmap entry | #060 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Today, sorting photos/albums by Title or Description offers a "natural" variant (`title`/`description`, PHP-level `SORT_NATURAL | SORT_FLAG_CASE` applied via `Collection::sortBy()` *after* the DB query — and, critically, after pagination has already sliced the result set, which the codebase's own migration comment (`database/migrations/2026_08_05_000000_add_sorting_description.php:22`) flags as producing incorrect page-to-page ordering) and a "strict" variant (`title_strict`/`description_strict`, pure SQL byte-order `ORDER BY`). This feature eliminates that PHP-level sorting path entirely for both `photos` and `base_albums`: Description stops being a sort criterion altogether (existing Description-based sort configs fall back to Title), and Title/Title-strict collapse into a single DB-driven "Title" order that still behaves like natural sort (`test_0`, `test_1`, `test_2`, `test_10`, …) by splitting the existing `title` column into two new derived columns — `title_base` and `title_index` — computed in PHP at write time and ordered purely in SQL (`ORDER BY title_base, title_index`) at read time. `title` itself is untouched and keeps its current display/search/filter role.

**Affected modules:** Core (`Photo`, `BaseAlbumImpl` models), Application (`SortingDecorator`, `ColumnSortingType`/`ColumnSortingAlbumType`/`ColumnSortingPhotoType`/`SearchSortingType` enums, several `HasMany*` relation classes), Database (migrations for both tables, all 3 supported drivers), REST (config validation for `sorting_*_col`), UI (v7 and v8 sort dropdowns, 23-locale translations).

## Goals

- Remove all PHP-level (`SORT_NATURAL | SORT_FLAG_CASE` / `Collection::sortBy()`) sorting for photos and albums — every sort criterion resolves to a single SQL `ORDER BY` clause.
- Remove Description as a sort criterion entirely (both its natural and strict variants); existing configs/album overrides pointing at Description are migrated to Title.
- Collapse Title / Title-strict into one "Title" sort criterion, still natural-sort-like in behaviour, driven purely by the database.
- Split `title` into two new derived, indexed columns (`title_base`, `title_index`) on both `photos` and `base_albums`, kept in sync automatically on every write, computed via a hardcoded trailing-digits heuristic with one additional fallback pattern (parenthesised numbering, e.g. `Photo (2)`).
- Keep the original `title` column completely unchanged (display, search, filtering, exports all keep reading it as today).
- Preserve behaviour parity where it matters: case-insensitive comparison (today's `SORT_FLAG_CASE`), and correct pagination (no more "natural sort re-shuffles once you turn the page").

## Non-Goals

- Description remains a normal column: still displayed, still searchable/filterable (`FieldLikeStrategy`/`AlbumFieldLikeStrategy` in `PhotoSearch`/`AlbumSearch` are untouched). Only its use as a **sort** criterion is removed — per explicit user direction ("Description ordering is removed completely and replaced by title").
- No admin-configurable/pluggable splitter-pattern system. The splitting heuristic is a hardcoded, ordered 2-rule chain (trailing digits, then parenthesised number) baked into one PHP function; adding a third pattern later is a code change, not a settings toggle — per explicit user direction.
- No change to any other sort column (`created_at`, `taken_at`, `min_taken_at`/`max_taken_at`, `is_highlighted`, `type`, `rating_avg` / Feature 009).
- No change to search's title/description **filtering** semantics (substring match), only to result **ordering**.
- No UI redesign beyond removing the now-dead dropdown entries and label wording.
- No change to how `title` itself is validated, displayed, or renamed (Feature 017 Apply Renamer Rules keeps writing to `title` exactly as today; this feature only adds the derived-column recompute as a side effect of that write).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-060-01 | `photos` and `base_albums` each gain two new columns: `title_base` (string, same length/nullability as `title` on that table) and `title_index` (nullable unsigned big integer). | Both columns exist after migration, indexed together as `(title_base, title_index)`. | Migration runs cleanly on MySQL/MariaDB, PostgreSQL, and SQLite. | Migration `down()` drops both columns and the composite index. | No telemetry. | User direction |
| FR-060-02 | A single pure PHP function (`TitleSplitter`, e.g. `app/Actions/Utility/TitleSplitter.php` or `app/Services/TitleSplitter.php`) computes `{base, index}` from a raw title string using an ordered rule chain: (1) trailing digit run — `^(.*?)(\d+)$`, e.g. `test10` → `base="test"`, `index=10`; (2) if rule 1 finds no match, a trailing parenthesised number — `^(.*?)\s*\((\d+)\)$`, e.g. `Photo (2)` → `base="Photo "`, `index=2`; (3) fallback — no digits found by either rule, `base=title`, `index=null`. | Base is stored lower-cased (case-folded) so plain byte-order `ORDER BY` on it reproduces today's case-insensitive comparison across all 3 DB drivers/collations without relying on driver-specific collation config. Index is cast to unsigned big integer; a digit run longer than 19 characters (overflowing 64-bit unsigned range) is truncated to its **last** 19 digits, consistent with the heuristic's "always take the last/trailing digits" rule. | Empty-string title → `base=""`, `index=null`. | N/A (pure function, no failure mode). | No telemetry. | User direction (heuristic), Q-060-02 |
| FR-060-03 | `title_base`/`title_index` are recomputed **explicitly, at every individual write site**, immediately after `title` is assigned and before `save()`/batch-update — no Eloquent model events (`saving`/`creating` hooks), no mutators, no other implicit/framework-driven mechanism. Each site calls the same pure `TitleSplitter::split(string $title): TitleSplitResult` (FR-060-02) and assigns `title_base`/`title_index` inline, mirroring Feature 009's explicit `$photo->rating_avg = ...;` sync-at-the-point-of-change convention. | All 12 identified write sites are updated: **Photos** — `Pipes/Shared/Save.php` (single choke-point for the whole upload/import pipeline, covers title set earlier by `Pipes/Init/LoadFileMetadata.php`, `Pipes/Shared/HydrateMetadata.php`, `Pipes/Standalone/ApplyUserProvidedMetadata.php`, `Pipes/Standalone/AutoRenamer.php`), `PhotoController::update()`, `PhotoController::rename()`, `Actions/Renamer/RenamePhotos.php` (bulk Renamer Rules — inline in the `batch()->update()` value map, since that call bypasses `save()`/model events entirely). **Albums** — `Actions/Album/Create.php`, `Actions/Album/CreateTagAlbum.php`, `Actions/Album/CreatePersonAlbum.php`, `Actions/Album/SetHeader.php` (regular-album title edit path), `AlbumController::updateTagAlbum()`, `AlbumController::updatePersonAlbum()`, `AlbumController::rename()`, `Actions/Renamer/RenameAlbums.php` (bulk, same `batch()->update()` bypass as photos). | A new data-integrity test iterates all `photos`/`base_albums` rows and asserts `title_base`/`title_index` always equal `TitleSplitter::split(title)` — this is the safety net for a missed call site (in place of relying on a hook to make omission impossible). | A write site that is missed leaves stale derived columns for that row until the next title change through a covered site — this is a known, accepted trade-off of the explicit-call-site approach (NFR-060-07) and is why the integrity test above exists, plus a `phpstan`-visible convention comment at every site. | No telemetry. | User direction — explicit, direct implementation only; no Eloquent lifecycle hooks. Write-path list from repo-wide `grep` enumeration (`grep -rn "->title\s*=" app/`, `grep -rln "'title'\s*=>" app/Actions/ app/Http/Requests/`). |
| FR-060-04 | A backfill data migration computes `title_base`/`title_index` for every existing row in `photos` and `base_albums`, chunked, using the same `TitleSplitter` function as FR-060-02 (PHP-side, since the split cannot be expressed as a single portable SQL expression — see NFR-060-01). | All pre-existing rows have correct, non-stale derived columns immediately after migration. | Chunked (e.g. 500-1000 rows/batch) to bound memory on large installs. | Migration is transactional per chunk; a failure mid-run can be safely re-run (idempotent — recomputing from `title` always yields the same result). | No telemetry. | User direction |
| FR-060-05 | `ColumnSortingType::TITLE_STRICT` is removed. `ColumnSortingType::TITLE` becomes the sole title-ordering entry point, resolved via `requiresRawOrdering() === true` and a `getRawOrderExpression()` extended to accept the sort direction, returning `{prefix}title_base {dir}, COALESCE({prefix}title_index, -1) {dir}` (mirrors Feature 009's `COALESCE(rating_avg, -1)` sentinel pattern — a title with no digit suffix sorts as if `index = -1`, i.e. immediately before any digit-suffixed sibling with the same base, matching today's natural-sort behaviour where `"test"` sorts before `"test0"`). | `ORDER BY title ASC/DESC` in `SortingDecorator::applySqlSorting()` becomes `ORDER BY title_base ASC/DESC, title_index ASC/DESC` for both directions symmetrically (not a fixed "NULLs always last" rule — direction-symmetric, since an absent index isn't conceptually "worse" the way an unrated photo is in Feature 009). | Both `photos` and `base_albums` (and the `photos.` relation-prefixed path via `orderPhotosBy()`) use the identical expression shape. | N/A — pure SQL, no runtime failure mode beyond the existing `InvalidOrderDirectionException` handling already in `SortingDecorator`. | No telemetry. | Research: `app/Enum/ColumnSortingType.php`, `app/Models/Extensions/SortingDecorator.php` |
| FR-060-06 | `ColumnSortingType::DESCRIPTION` and `ColumnSortingType::DESCRIPTION_STRICT` are removed (and their mirrors in `ColumnSortingAlbumType`/`ColumnSortingPhotoType`). Description is no longer a selectable sort criterion anywhere. | Sort dropdowns (`resources/js/config/constants.ts`) no longer offer "Description (Nat)" / "Description (Lexico)". | `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` configs' `type_range` no longer accepts `description`/`description_strict`. | Attempting to set a config value outside the new `type_range` fails existing config-sanity validation (no new failure path introduced). | No telemetry. | User direction |
| FR-060-07 | `SortingDecorator::POSTPONE_COLUMNS` and the PHP-fallback machinery (`applyPhpSorting()`, `$pivot_idx` bookkeeping) are deleted. `orderBy()`/`orderPhotosBy()`/`get()`/`paginate()` become pure SQL pass-throughs. | 100% of sorting happens in the database query; pagination is always correct (no post-fetch re-ordering after a `LIMIT`/`OFFSET`). | N/A. | N/A. | No telemetry. | User direction ("no more php ordering") |
| FR-060-08 | The 5 relation `match()` methods that independently re-implement `SORT_NATURAL \| SORT_FLAG_CASE` for eager-loading (`HasManyPhotosByPerson.php:91-95`, `HasManyChildPhotos.php:157-163`, `HasManyPhotosRecursively.php:139-144`, `HasManyPhotosByTag.php:197-202`, `HasManyChildAlbums.php:128`) are simplified to preserve the DB-provided order instead of re-sorting in PHP. | Eager-loaded collections keep the same order the underlying query already produced. | N/A. | N/A. | No telemetry. | Research (duplicated PHP-sort logic outside `SortingDecorator`) |
| FR-060-09 | `SearchSortingType::toPhotoColumn()`/`toAlbumColumn()` map `TITLE` directly to `ColumnSortingPhotoType::TITLE`/`ColumnSortingAlbumType::TITLE` (no longer redirecting to a now-nonexistent `_STRICT` case). | Search's title-sorted results use the same natural DB order as gallery/album listing everywhere else. | N/A. | N/A. | No telemetry. | Research: `app/Enum/SearchSortingType.php:25-34,44-53` — documented **intentional behaviour change**: search's title sort was previously forced byte-exact lexicographic; it now matches the unified natural-DB order like every other Title sort, consistent with removing the Title/Title-strict distinction everywhere. |
| FR-060-10 | Existing installs' `configs` rows for `sorting_photos_col`, `sorting_albums_col`, `sorting_pinned_albums_col` are rewritten: any value of `title_strict`, `description`, or `description_strict` becomes `title`; any other value (`created_at`, `taken_at`, etc.) is untouched. `type_range` for all 3 keys drops the removed tokens. | Every upgraded install keeps working with no manual admin intervention and no validation errors post-upgrade. | Migration is idempotent (re-running produces the same end state). | N/A. | No telemetry. | User direction, mirrors the predecessor migration `database/migrations/2026_01_10_212900_add_strict_ordering.php` (which this feature's migration effectively reverses/supersedes for `type_range`) |
| FR-060-11 | Frontend sort dropdowns (`resources/js/config/constants.ts` — shared by both v7 and v8) drop the `description`/`description_strict`/`title_strict` entries from `photoSortingColumnsOptions`/`albumSortingColumnsOptions`. The remaining `title` entry's label drops the "(Nat)" qualifier (no longer a meaningful distinction). | v7 and v8 sort-order dropdowns (Settings → General, per-album `AlbumProperties.vue`, Bulk Album Edit) show only "Title" where they used to show 4 title/description-related options. | `App.Enum.ColumnSortingPhotoType`/`ColumnSortingAlbumType`/`ColumnSortingType` TypeScript types in `resources/js/lychee.d.ts` are regenerated (build step) to match the narrowed PHP enums — never hand-edited. | N/A. | No telemetry. | Research: `resources/js/config/constants.ts:11-35` |
| FR-060-12 | All 23 locales' **`lang/<locale>/gallery.php`** files (the real source of truth — a PHP array under the `'sort'` key, e.g. `lang/en/gallery.php:386-404`) have the 6 orphaned keys removed: `photo_select_4`, `photo_select_3_strict`, `photo_select_4_strict`, `album_select_3`, `album_select_2_strict`, `album_select_3_strict`. The retained `photo_select_3`/`album_select_2` ("Title (Nat)") text is updated to plain "Title" in `lang/en/gallery.php` (other locales keep their existing translated string as a starting point, per this repo's English-placeholder convention for translation drift — Feature 054 precedent). `lang/<locale>.json` (e.g. `lang/en.json`) is a **generated artifact** — produced from the `.php` files by the `php artisan lang:json` command (`App\Console\Commands\Laravel\LangFilesToJson`) — and is regenerated, never hand-edited. | `tests/Unit/LangTest.php`'s `testLanguageConsistency()` (which diffs every locale's `.php` files against `lang/en`'s) stays green — no dangling/unused/inconsistent keys remain after this feature. | N/A. | N/A. | No telemetry. | Research corrected by user: `lang/<locale>/*.php` are hand-edited source files; `lang/<locale>.json` is generated by `php artisan lang:json` — confirmed via `app/Console/Commands/Laravel/LangFilesToJson.php` and `tests/Unit/LangTest.php`. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-060-01 | The entire feature must work identically on all 3 supported DB drivers (MySQL/MariaDB, PostgreSQL, SQLite) without driver-specific ordering behaviour. | SQLite has no built-in `REGEXP` support (no loadable extension registered by this app), which rules out expressing the trailing-digit-split as a native SQL generated/virtual column (unlike the `COALESCE`-only precedent in `database/migrations/2026_07_01_120000_deduplicate_and_constrain_access_permissions.php`, which needed no regex). | `title_base`/`title_index` are plain, ordinary (non-generated) columns populated entirely in PHP (FR-060-02/03/04); the `ORDER BY` clause itself (FR-060-05) is portable standard SQL with no driver-specific functions. | None — deliberately avoids `storedAs()`/`virtualAs()` and the FK-drop dance they require on MySQL. | Research (SQLite regex gap), architecture decision |
| NFR-060-02 | No PHP-level post-fetch sorting remains for any in-scope column; pagination is always correct. | Fixes the documented bug where natural sort + pagination showed pages out of order (`database/migrations/2026_08_05_000000_add_sorting_description.php:22`). | Code review confirms `SortingDecorator::POSTPONE_COLUMNS` is empty/removed and `applyPhpSorting()` is deleted; a feature test pages through a natural-sort-ordered result set and asserts stable, correct cross-page order. | FR-060-07 | User direction |
| NFR-060-03 | Case-insensitive title ordering parity with today's `SORT_FLAG_CASE` behaviour, independent of each DB driver's default column/table collation. | SQLite defaults to `BINARY` (case-sensitive) comparison, PostgreSQL is case-sensitive by default, MySQL/MariaDB's default collation is usually (but not guaranteed to be) case-insensitive — relying on collation would be driver/config-dependent. | `title_base` is stored lower-cased at compute time (FR-060-02), so a plain byte-order `ORDER BY` is case-insensitive by construction on every driver. | FR-060-02 | Research (case-sensitivity risk flagged) |
| NFR-060-04 | Query performance for title-sorted listings is comparable to or better than today. | Avoids the previous approach's full-table-into-PHP-memory sort for any page beyond the first. | New composite index `(title_base, title_index)` on both tables (FR-060-01); `EXPLAIN`/query-plan spot check during implementation. | FR-060-01 | Performance standard |
| NFR-060-05 | Upgrading an existing install requires no manual admin action. | Configs may currently hold `title_strict`/`description`/`description_strict` values that would otherwise become invalid after this feature ships. | FR-060-10's migration is idempotent and covers all 3 affected config keys. | FR-060-10 | User direction |
| NFR-060-06 | `lychee.d.ts` TypeScript enum types stay in sync with the narrowed PHP enums. | `Spatie\TypeScriptTransformer`-generated file must never silently drift from the backing PHP `#[TypeScript()]` enums. | Regenerate via the existing project build/artisan step as part of this feature's implementation; never hand-edited. | FR-060-11 | Repo convention (`config/app.php:241`) |
| NFR-060-07 | The explicit-call-site approach (FR-060-03) has no automatic enforcement — a future write path that sets `title` without also calling `TitleSplitter::split()` leaves that row's derived columns stale until its next covered-site edit. This is an accepted, documented trade-off of avoiding model-event "magic," not silently patched by this feature. | User explicitly rejected an Eloquent `saving`-hook design in favour of direct, visible calls at each write site. | A repo-wide grep sweep (tasks.md, T-060-08) confirms all 12 write sites are covered at implementation time; a permanent data-integrity test (FR-060-03) catches drift after the fact by comparing every row's stored `title_base`/`title_index` against a fresh `TitleSplitter::split(title)` computation. | FR-060-03 | User direction (no hooks/magic) |
| NFR-060-08 | Code follows Lychee PHP conventions. | Maintainability. | License headers, snake_case, strict comparison, PSR-4, no `empty()`; `make phpstan` 0 errors; `php-cs-fixer` clean. | Coding conventions | Repo convention |

## UI / Interaction Mock-ups

Only the sort-order dropdown changes (identical shape in v7's `resources/js/v7/components/settings/General.vue` and v8's `resources/js/v8/components/settings/General.vue`, plus `AlbumProperties.vue` and `BulkEditFieldsDialog.vue` in both trees — all consume the same shared `resources/js/config/constants.ts`).

**Before:**
```
Sort photos by: [Title (Nat)        ▼]
                ┌──────────────────────┐
                │ Created Date         │
                │ Taken Date           │
                │ Title (Nat)          │
                │ Description (Nat)    │
                │ Title (Lexico)       │
                │ Description (Lexico) │
                │ Highlighted          │
                │ Type                 │
                └──────────────────────┘
```

**After:**
```
Sort photos by: [Title               ▼]
                ┌──────────────────────┐
                │ Created Date         │
                │ Taken Date           │
                │ Title                │
                │ Highlighted          │
                │ Type                 │
                └──────────────────────┘
```

Same before/after shape applies to the album sort dropdown (`album_select_2`/`album_select_3` + their `_strict` siblings collapse to a single "Title").

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-060-01 | Photos titled `test_0`, `test_1`, `test_2`, `test_10` sorted ascending by Title → appear in that exact numeric order, not lexicographic (`test_1, test_10, test_2, test_0`). |
| S-060-02 | Same set sorted descending → `test_10, test_2, test_1, test_0`. |
| S-060-03 | Title `Photo (2)` and `Photo (10)` (no trailing bare digits, parenthesised instead) sort numerically via the rule-2 fallback. |
| S-060-04 | Title with no digits at all (`Vacation`) and title with digits (`Vacation5`) — `Vacation` (index NULL→sentinel -1) sorts immediately before `Vacation5` ascending, matching prior natural-sort behaviour. |
| S-060-05 | Mixed-case titles (`Apple`, `banana`, `Cherry`) sort case-insensitively, identically across MySQL, PostgreSQL, and SQLite. |
| S-060-06 | Paginated title-sorted listing (e.g. 3 pages of 20) — page 2's first item strictly follows page 1's last item in sort order (no PHP-level re-shuffle after the fact). |
| S-060-07 | Upgrading an install with `sorting_photos_col = 'title_strict'` → config auto-migrates to `title`, sort still natural-DB-ordered, no error. |
| S-060-08 | Upgrading an install with `sorting_albums_col = 'description'` or `'description_strict'` → config auto-migrates to `title`. |
| S-060-09 | Same split/order behaviour verified independently for `base_albums` (albums list), not just `photos`. |
| S-060-10 | Search results sorted by title (`SearchSortingType::TITLE`) use the same natural DB order as gallery listing (documented behaviour change from prior byte-exact order). |
| S-060-11 | Backfill migration re-run on an already-migrated install is a no-op (idempotent — same `title` always yields the same `title_base`/`title_index`). |
| S-060-12 | Renaming a photo/album through every one of the 12 explicit write sites (upload/import, admin edit, rename endpoint, bulk Renamer Rules — Feature 017) correctly updates `title_base`/`title_index`. |
| S-060-13 | Eager-loaded photo relations (person/tag/child/recursive) preserve DB-provided title order without re-sorting in PHP. |
| S-060-14 | Description is no longer offered in either sort dropdown, but remains fully functional as a search filter field and as displayed text. |
| S-060-15 | Data-integrity test: every `photos`/`base_albums` row's stored `title_base`/`title_index` matches a fresh `TitleSplitter::split(title)` recomputation (catches any write site missed during implementation or added later without the explicit call). |

## Test Strategy

- **Core (Unit tests):** `TitleSplitter` pure-function tests — trailing digits, parenthesised fallback, no-digit fallback, case-folding, digit runs >19 characters (truncation), empty string, unicode titles. `ColumnSortingType::getRawOrderExpression()` direction-aware output for `TITLE`.
- **Application (Feature tests):** New `tests/Feature_v2/Photo/PhotoTitleSortingTest.php` and `tests/Feature_v2/Album/AlbumTitleSortingTest.php` — covers S-060-01..06, S-060-09, S-060-13. New `tests/Feature_v2/Photo/PhotoTitleSyncTest.php`/`AlbumTitleSyncTest.php` — one assertion per write site (all 12, S-060-12), each performing the real write (upload, rename endpoint, admin edit, bulk Renamer Rules) and asserting correct `title_base`/`title_index` afterward — no shortcutting via direct model manipulation, since the point is to catch a site that forgot the explicit call. New `tests/Feature_v2/TitleSplitIntegrityTest.php` (S-060-15) — seeds rows via every write path, then asserts `title_base`/`title_index` match a fresh `TitleSplitter::split(title)` for 100% of rows. Migration test asserts config auto-rewrite (S-060-07/08) and backfill idempotency (S-060-11).
- **REST (API contract):** `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` config validation rejects the now-removed tokens (`title_strict`, `description`, `description_strict`) for **new** writes, while the migration silently repairs **existing** stored values.
- **CLI:** N/A (no CLI surface for this feature).
- **UI (JS/vue-tsc):** Sort dropdown component renders only the reduced option list; `App.Enum.*` type regeneration verified via `vue-tsc`/`npm run check` passing with no stale `_strict`/`description` references.
- **Docs/Contracts:** `lychee.d.ts` regenerated; `docs/specs/3-reference/api-design.md` / `docs/specs/4-architecture/knowledge-map.md` updated if they reference the removed sort enum values.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-060-01 | `photos.title_base` (string, nullable — mirrors `photos.title`'s current nullability, max length 300) | core (Models), database |
| DO-060-02 | `photos.title_index` (unsigned big integer, nullable) | core (Models), database |
| DO-060-03 | `base_albums.title_base` (string, NOT NULL — mirrors `base_albums.title`'s current non-nullability, max length 100) | core (Models), database |
| DO-060-04 | `base_albums.title_index` (unsigned big integer, nullable) | core (Models), database |
| DO-060-05 | `TitleSplitter` — stateless pure-function service, `split(string $title): array{base: string, index: ?int}`. Called **explicitly at each of the 12 write sites** (FR-060-03) — never registered as a model event/observer. | core (Services/Actions) |
| DO-060-06 | `ColumnSortingType` enum: removes `TITLE_STRICT`, `DESCRIPTION`, `DESCRIPTION_STRICT` cases; `getRawOrderExpression()` signature gains a `$direction` parameter | core (Enum) |
| DO-060-07 | `ColumnSortingAlbumType`/`ColumnSortingPhotoType` enums: same 3 cases removed (mirrored) | core (Enum) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-060-01 | Existing config-write endpoints (Settings) | `sorting_photos_col`/`sorting_albums_col`/`sorting_pinned_albums_col` reject the removed tokens via narrowed `type_range` | No new routes; existing config-sanity validation, narrower allow-list |

### CLI Commands / Flags

Not applicable — no CLI surface.

### Telemetry Events

Not applicable — no telemetry for this feature.

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-060-01 | Feature test factories | Photos/albums titled `test_0`/`test_1`/`test_2`/`test_10`, `Photo (2)`/`Photo (10)`, `Vacation`/`Vacation5`, mixed-case, and a >19-digit-suffix title, for S-060-01..05 |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-060-01 | Sort dropdown (photos) | Shows "Title" once, no "(Nat)"/"(Lexico)" suffix, no "Description" entries |
| UI-060-02 | Sort dropdown (albums) | Same reduction, mirrored |

## Telemetry & Observability

Not applicable — no telemetry for this feature.

## Documentation Deliverables

- **Roadmap update:** Add Feature 060 to `docs/specs/4-architecture/roadmap.md`'s Active Features table.
- **Open questions:** Log Q-060-01 (Description scope) and Q-060-02 (splitter design) as Resolved in `docs/specs/4-architecture/open-questions.md`.
- **Knowledge map update:** Add `title_base`/`title_index` columns, `TitleSplitter`, and the narrowed sorting enums to `docs/specs/4-architecture/knowledge-map.md` if it documents sort infrastructure.
- **API reference:** Update `docs/specs/3-reference/api-design.md` if it enumerates the `sorting_*_col` allowed values.

## Fixtures & Sample Data

See FX-060-01 above — no external fixture files needed, factory-generated data is sufficient.

## Spec DSL

```yaml
domain_objects:
  - id: DO-060-01
    name: Photo
    table: photos
    new_fields:
      - name: title_base
        type: string(300)
        nullable: true
        indexed: true
        description: Case-folded non-digit prefix of title, derived at write time
      - name: title_index
        type: unsignedBigInteger
        nullable: true
        indexed: true
        description: Trailing numeric suffix of title (or parenthesised fallback), derived at write time
  - id: DO-060-03
    name: BaseAlbumImpl
    table: base_albums
    new_fields:
      - name: title_base
        type: string(100)
        nullable: false
        indexed: true
      - name: title_index
        type: unsignedBigInteger
        nullable: true
        indexed: true
  - id: DO-060-06
    name: ColumnSortingType
    type: enum
    removed_values: [TITLE_STRICT, DESCRIPTION, DESCRIPTION_STRICT]
    changed_values:
      - name: TITLE
        requires_raw_ordering: true
        raw_expression: "{prefix}title_base {dir}, COALESCE({prefix}title_index, -1) {dir}"

splitter_rules:
  - priority: 1
    name: trailing_digits
    pattern: '^(.*?)(\d+)$'
  - priority: 2
    name: parenthesised_number
    pattern: '^(.*?)\s*\((\d+)\)$'
  - priority: 3
    name: fallback
    pattern: null
    result: {base: "<whole title, lowercased>", index: null}

sorting:
  - column: title
    order: "title_base {dir}, COALESCE(title_index, -1) {dir}"
    description: Direction-symmetric DB-only natural-like order, replaces title/title_strict/description/description_strict

configs_migrated:
  - key: sorting_photos_col
    rewrite: {title_strict: title, description: title, description_strict: title}
  - key: sorting_albums_col
    rewrite: {title_strict: title, description: title, description_strict: title}
  - key: sorting_pinned_albums_col
    rewrite: {title_strict: title, description: title, description_strict: title}
```

## Appendix

### TitleSplitter reference implementation (illustrative, not final code)

```php
final class TitleSplitter
{
    private const MAX_INDEX_DIGITS = 19;

    public static function split(string $title): array
    {
        if (preg_match('/^(.*?)(\d+)$/u', $title, $m) === 1) {
            return self::toResult($m[1], $m[2]);
        }
        if (preg_match('/^(.*?)\s*\((\d+)\)$/u', $title, $m) === 1) {
            return self::toResult($m[1], $m[2]);
        }

        return ['base' => mb_strtolower($title), 'index' => null];
    }

    private static function toResult(string $base, string $digits): array
    {
        if (strlen($digits) > self::MAX_INDEX_DIGITS) {
            $digits = substr($digits, -self::MAX_INDEX_DIGITS);
        }

        return ['base' => mb_strtolower($base), 'index' => (int) $digits];
    }
}
```

### Why not native DB generated columns?

The obvious alternative — `storedAs()`/`virtualAs()` generated columns computed by the database itself (the pattern used for `user_id_unique_key`/`user_group_id_unique_key` in `database/migrations/2026_07_01_120000_deduplicate_and_constrain_access_permissions.php`) — is not portable here: that precedent's expression is a simple `COALESCE`, but extracting a trailing digit run requires regex, and **SQLite has no built-in `REGEXP` support** (it requires an app-registered extension function that this codebase does not provide). Rather than special-casing SQLite with a different, weaker column-definition strategy than MySQL/PostgreSQL, this feature computes `title_base`/`title_index` identically in PHP for all 3 drivers, via explicit calls at each of the 12 write sites (FR-060-03 — deliberately not an Eloquent model event/hook) — trading "the DB recomputes it for free on every write" for "one consistent, visible code path across every supported database."

---

*Last updated: 2026-08-27*
