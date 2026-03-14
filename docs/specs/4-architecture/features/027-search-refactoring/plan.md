# Feature Plan 027 – Search Refactoring

_Linked specification:_ `docs/specs/4-architecture/features/027-search-refactoring/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-03-12 (Q-027-01/02/03/04/05 resolved; scenario tracking cross-references corrected)

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs have been updated.

---

## Vision & Success Criteria

Users can express structured search filters directly inside the existing search bar using a `modifier:value` grammar alongside free-text terms. The backend parses tokens, applies per-type query strategies, and returns correctly filtered, paginated results through the unchanged `GET /Search` endpoint.

**Success signals:**
- All 24 scenario tests (S-027-01 … S-027-24) passing.
- Plain-text backward-compat regression suite green (`php artisan test --filter=Search`).
- PHPStan level 6 clean on all new/modified files.
- `php-cs-fixer fix` produces no diffs.

---

## Scope Alignment

**In scope:**
- `SearchToken` DTO (with `sub_modifier` field) and `SearchTokenParser` service.
- Per-modifier query strategies for photo search: plain-text, tag (exact + prefix), date (exact + range), type, ratio (named buckets + numeric), colour palette similarity (via `colours` table JOIN), EXIF field modifiers (`make`, `lens`, `aperture`, `iso`, `shutter`, `focal`), `title`, `description`, `location`, `rating` (sub-modifiers: `avg`, `own`).
- `AlbumSearchTokenStrategy` interface + strategies for album `title:`, `description:`, `date:` modifiers wired into `AlbumSearch`.
- `PhotoSearch::sqlQuery()` refactored to delegate to strategies.
- `AlbumSearch` dispatch loop added.
- `GetSearchRequest::processValidatedValues()` updated to use `SearchTokenParser`.
- Migration adding `search_colour_distance` config key (default 30).
- Unit and Feature tests for all new code.

**Out of scope:**
- Frontend UI changes to render token badges.
- Full-text search engine integration.
- Album `tag:` modifier (tag albums exist but photo tags are not linked to album metadata).

---

## Dependencies & Interfaces

| Dependency | Notes |
|------------|-------|
| `photos_tags` pivot + `tags` table | Already exists; no schema change needed for FR-027-02/03. |
| `size_variants` table, `ratio` column | Already exists; JOIN added in `PhotoSearch` (FR-027-07/08). |
| `palette` table, `colour_1`…`colour_5` (INT = `colours.id`) | `palette.colour_N` stores the packed RGB int which is the PK of the `colours` table. JOIN `palette → colours ON colours.id IN (p.colour_1,…,p.colour_5)` gives direct access to `colours.R/G/B`; no bit-shift needed (FR-027-09). |
| `Colour` model / colours table | Used for named-colour resolution (CSS name → RGB hex). |
| `ConfigManager` / `Configs` table | New `search_colour_distance` key via migration. |

---

## Assumptions & Risks

**Assumptions:**
- The `palette` table is populated for photos that have been processed; photos without a palette row are simply excluded from `color:` results (FR-027-09, S-027-19).
- The `size_variants` table always has at least an ORIGINAL variant (type = 0) for photos that appear in search results.
- SQLite (used in tests) supports the arithmetic expressions needed for RGB distance; if not, the distance strategy will use a subquery compatible with both SQLite and MySQL/PostgreSQL.

**Risks / Mitigations:**
- **Double-counting with tag JOINs:** A photo with multiple matching tags could appear multiple times. Mitigation: use `whereHas('tags', ...)` Eloquent relationship clause (EXISTS subquery) instead of a raw JOIN, satisfying NFR-027-02.
- **Ratio JOIN on multiple size variants:** Using `whereHas` on `size_variants` filtered to the ORIGINAL type avoids row multiplication (NFR-027-03).
- **`rating:own:` for unauthenticated users:** Strategy must guard `Auth::check()` and return a 400 via `InvalidTokenException` if the user is not authenticated.

---

## Implementation Drift Gate

After all increments pass, run:
```bash
vendor/bin/php-cs-fixer fix
php artisan test --filter=Search
make phpstan
```
Record results in the `## Analysis Gate` section below before declaring the feature complete.

---

## Increment Map

### I1 – SearchToken DTO and SearchTokenParser (tests first)
- _Goal:_ Introduce the pure-PHP parsing layer with no DB dependency.
- _Preconditions:_ Spec sections FR-027-13, DO-027-01, DO-027-02 finalised.
- _Steps:_
  1. Write `tests/Unit/Actions/Search/SearchTokenParserTest.php` covering all grammar rules (S-027-02 to S-027-20 parser surface). **Confirm tests RED.**
  2. Create `app/DTO/Search/SearchToken.php` — readonly DTO with fields `modifier`, `sub_modifier`, `operator`, `value`, `is_prefix`.
  3. Create `app/Actions/Search/SearchTokenParser.php` — stateless service implementing the grammar from spec (including `rating:avg:>=4` sub-modifier syntax).
  4. Make parser tests GREEN.
- _Commands:_ `php artisan test --filter=SearchTokenParser`, `make phpstan`
- _Exit:_ Parser unit tests pass; PHPStan clean on new files.

### I2 – Refactor GetSearchRequest to use SearchTokenParser
- _Goal:_ Wire the parser into the HTTP layer, replacing the current split-by-space logic.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Extend `GetSearchRequest::processValidatedValues()` to call `SearchTokenParser::parse()` and store `array<SearchToken>` in `$this->terms` (or a dedicated `$this->tokens` property with new interface contract).
  2. Update `HasTermsTrait` / `HasTerms` contract if needed, or introduce `HasSearchTokens` contract.
  3. Adjust `SearchController::search()` to pass `$request->tokens()` to `PhotoSearch::sqlQuery()`.
  4. Run existing search feature tests — should still pass (backward compat, S-027-01).
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ All existing search tests green; no plain-text regression.

### I3 – Plain-text and tag strategies in PhotoSearch
- _Goal:_ Replace raw `->where(...LIKE...)` loop with strategy-based dispatch; add tag search.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Write failing feature tests for S-027-02, S-027-03 (tag exact/prefix) and verify S-027-01 (plain text now includes tags).
  2. Create `app/Actions/Search/Strategies/PlainTextStrategy.php` (matches title, description, location, model, taken_at, tags.name).
  3. Create `app/Actions/Search/Strategies/TagStrategy.php` (uses `whereHas('tags', ...)` for exact and prefix).
  4. Refactor `PhotoSearch::sqlQuery()`: replace `foreach ($terms as $term)` loop with a strategy dispatch loop over `array<SearchToken>`.
  5. Make failing tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-01, S-027-02, S-027-03, S-027-16 green.

### I4 – Date and Type strategies
- _Goal:_ Add date exact/range and MIME type filters.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Write failing tests for S-027-04, S-027-05, S-027-06, S-027-07, S-027-17.
  2. Create `app/Actions/Search/Strategies/DateStrategy.php`.
  3. Create `app/Actions/Search/Strategies/TypeStrategy.php`.
  4. Register strategies in `PhotoSearch`.
  5. Make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-04 – S-027-07, S-027-17 green.

### I5 – Ratio strategies
- _Goal:_ Add named-bucket and numeric ratio filtering.
- _Preconditions:_ I4 complete.
- _Steps:_
  1. Write failing tests for S-027-08, S-027-09, S-027-10, S-027-11.
  2. Create `app/Actions/Search/Strategies/RatioStrategy.php` (uses `whereHas('size_variants', ...)` filtered to ORIGINAL type).
  3. Register strategy; make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-08 – S-027-11 green.

### I6 – EXIF field strategies (make, lens, aperture, iso, shutter, focal, title, description, location, model)
- _Goal:_ Allow explicit field-specific LIKE queries with optional prefix mode.
- _Preconditions:_ I4 complete (can be done in parallel with I5).
- _Steps:_
  1. Write failing tests for S-027-14, S-027-15.
  2. Create `app/Actions/Search/Strategies/FieldLikeStrategy.php` — generic strategy parameterised by column name.
  3. Register for all EXIF modifier keywords.
  4. Make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-14, S-027-15 green; S-027-18 (unknown modifier fallback) green.

### I7 – Colour palette similarity strategy
- _Goal:_ Add `color:`/`colour:` filter using Manhattan RGB distance via `colours` table JOIN.
- _Preconditions:_ I3 complete; NFR-027-04 respected (Q-027-04/05 resolved).
- _Steps:_
  1. Add migration for `search_colour_distance` config key (default 30).
  2. Write failing tests for S-027-12, S-027-13, S-027-19.
  3. Create `app/Actions/Search/ColourNameMap.php` — `const` array of the 16 CSS Level 1 colour names → `#rrggbb` hex strings (Q-027-04).
  4. Create `app/Actions/Search/Strategies/ColourStrategy.php`:
     - Hex input (`#rrggbb`): parse R, G, B directly; call `Colour::fromHex($hex)` to ensure the row exists and get the `Colour` instance.
     - Named input: look up in `ColourNameMap::NAMES`; if not found throw `InvalidTokenException` (→ 400). Then call `Colour::fromHex($resolvedHex)`.
     - SQL EXISTS subquery (Q-027-05): `EXISTS (SELECT 1 FROM palette p JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR c.id = p.colour_3 OR c.id = p.colour_4 OR c.id = p.colour_5) WHERE p.photo_id = photos.id AND ABS(c.R-:R)+ABS(c.G-:G)+ABS(c.B-:B) <= :dist)`.
     - Threshold sourced from `ConfigManager::getValueAsInt('search_colour_distance')`.
  5. Register strategy; make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-12, S-027-13, S-027-19 green.

### I8 – Rating sub-modifier strategy
- _Goal:_ Add `rating:avg:` and `rating:own:` filters (Q-027-02, FR-027-14).
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Write failing feature tests for S-027-21 (`rating:avg:>=4`) and S-027-22 (`rating:own:>=3`, unauthenticated 400).
  2. Create `app/Actions/Search/Strategies/RatingStrategy.php`:
     - `sub_modifier = 'avg'`: `where('photos.rating_avg', $operator, $value)`.
     - `sub_modifier = 'own'`: guard `Auth::check()` (throw `InvalidTokenException` if unauthenticated); `whereHas('rating', fn($q) => $q->where('user_id', Auth::id())->where('rating', $operator, $value))`.
     - Operator must be `<`, `<=`, `>`, `>=`, `=`; value must be integer 0–5.
  3. Register strategy; make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-21, S-027-22 green.

### I9 – Album search modifier support
- _Goal:_ Wire `SearchTokenParser` into `AlbumSearch`; add album-level `title:`, `description:`, `date:` strategies (Q-027-03, FR-027-15).
- _Preconditions:_ I2 complete (parser available); I4 complete (DateStrategy reusable pattern).
- _Steps:_
  1. Write failing feature tests for S-027-23 (`title:summer` on albums) and S-027-24 (`date:>2024-01-01` on albums).
  2. Create `app/Contracts/Search/AlbumSearchTokenStrategy.php` interface (mirrors `PhotoSearchTokenStrategy`).
  3. Create `app/Actions/Search/Strategies/Album/AlbumFieldLikeStrategy.php` and `app/Actions/Search/Strategies/Album/AlbumDateStrategy.php`.
  4. Refactor `AlbumSearch::addSearchCondition()` (and `queryAlbums`/`queryTagAlbums`) to accept `array<SearchToken>` and dispatch to album strategies.
  5. Update `SearchController::search()` to pass `$request->tokens()` to `AlbumSearch` methods.
  6. Make tests GREEN.
- _Commands:_ `php artisan test --filter=Search`, `make phpstan`
- _Exit:_ S-027-23, S-027-24 green; all existing album search tests remain green.

### I10 – Final validation gate and documentation
- _Goal:_ Full quality gate pass and documentation updates.
- _Steps:_
  1. Run full suite: `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`.
  2. Fix any remaining issues.
  3. Update `docs/specs/4-architecture/knowledge-map.md`.
  4. Update `docs/specs/4-architecture/roadmap.md` (Feature 027 status → complete).
  5. Mark all tasks `[x]` in `tasks.md`.

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-027-01 | I3 / T-027-04 | Plain-text + tags; backward compat |
| S-027-02 | I3 / T-027-05 | tag exact |
| S-027-03 | I3 / T-027-06 | tag prefix |
| S-027-04 | I4 / T-027-08 | date exact |
| S-027-05 | I4 / T-027-09 | date after |
| S-027-06 | I4 / T-027-10 | date range (two tokens) |
| S-027-07 | I4 / T-027-11 | type filter |
| S-027-08 | I5 / T-027-13 | ratio landscape |
| S-027-09 | I5 / T-027-13 | ratio portrait |
| S-027-10 | I5 / T-027-13 | ratio square |
| S-027-11 | I5 / T-027-14 | ratio numeric |
| S-027-12 | I7 / T-027-22 | colour hex |
| S-027-13 | I7 / T-027-22 | colour named |
| S-027-14 | I6 / T-027-18 | EXIF make + lens combo |
| S-027-15 | I6 / T-027-18 | title prefix |
| S-027-16 | I3 / T-027-04 | AND semantics for plain terms |
| S-027-17 | I4 / T-027-12 | invalid date → 400 |
| S-027-18 | I6 / T-027-19 | unknown modifier fallback |
| S-027-19 | I7 / T-027-22 | photo without palette excluded |
| S-027-20 | I1 / T-027-02 | tag:* alone → 400 |
| S-027-21 | I8 / T-027-27 | rating:avg:>=4 |
| S-027-22 | I8 / T-027-28 | rating:own:>=3 + unauthenticated 400 |
| S-027-23 | I9 / T-027-31 | album title: modifier |
| S-027-24 | I9 / T-027-32 | album date: modifier |

---

## Analysis Gate

_Not yet run. To be completed before declaring the feature done._

| Check | Result | Date |
|-------|--------|------|
| `vendor/bin/php-cs-fixer fix` (no diff) | — | — |
| `php artisan test` (all green) | — | — |
| `make phpstan` (level 6, zero errors) | — | — |

---

## Exit Criteria

- [ ] All 24 scenario tests (S-027-01 … S-027-24) pass.
- [ ] `php artisan test` suite green (no regressions).
- [ ] `make phpstan` exits 0.
- [ ] `php-cs-fixer fix` produces no changes.
- [ ] `search_colour_distance` config key exists with default 30.
- [ ] `knowledge-map.md` updated.
- [ ] `roadmap.md` updated.
- [ ] All tasks in `tasks.md` marked `[x]`.

---

## Follow-ups / Backlog

- **Album `tag:` modifier**: tagging is a photo-level concept; album-level tag filters would require a different JOIN (photos within album that have the tag). Scope separately.
- **Frontend token badge UI**: separate feature, to be scoped after user feedback.
- **Performance (colour):** if the `palette → colours` JOIN proves slow at large scale, consider adding a composite index on `(palette.photo_id, colour_1, …, colour_5)` or caching the R/G/B values in the palette table directly.
- **Full-text search engine integration**: long-term architectural consideration for large deployments.

---

*Last updated: 2026-03-12 (Q-027-01/02/03 resolved)*
