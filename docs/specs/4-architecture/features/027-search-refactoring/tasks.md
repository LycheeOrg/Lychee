# Feature 027 Tasks – Search Refactoring

_Status: Complete_  
_Last updated: 2026-03-12 (all tasks implemented; PHPStan clean, 27 unit tests passing)_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes.

---

## Checklist

### I1 – SearchToken DTO and SearchTokenParser

- [x] T-027-01 – Create `SearchToken` DTO (FR-027-13, DO-027-01).  
  _Intent:_ Readonly DTO class `app/DTO/Search/SearchToken.php` with fields: `modifier: ?string`, `sub_modifier: ?string` (`avg`|`own`, used only with `rating`), `operator: ?string`, `value: string`, `is_prefix: bool`.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ No DB access. Must include license header and blank line after `<?php`.

- [x] T-027-02 – Write `SearchTokenParserTest` unit tests (FR-027-13, S-027-02 to S-027-24 parser surface).  
  _Intent:_ Tests RED before parser exists. Cover: plain text, `tag:`, `tag:prefix*`, `tag:*` (→ 400), `date:2024-05-01`, `date:>2024-01-01`, `date:<=2024-12-31`, `type:jpeg`, `ratio:landscape`, `ratio:portrait`, `ratio:square`, `ratio:>1.5`, `color:#ff0000`, `color:red`, EXIF modifiers, `title:foo*`, `rating:avg:>=4`, `rating:own:>=3`, `rating:bad:>=3` (→ 400), `rating:own:>=3` unauthenticated (→ 400), unknown modifier fallback.  
  _Verification commands:_  
  - `php artisan test --filter=SearchTokenParserTest` (expect RED at this stage)

- [x] T-027-03 – Implement `SearchTokenParser` service (FR-027-13, DO-027-02).  
  _Intent:_ `app/Actions/Search/SearchTokenParser.php` — stateless, no DB calls. Implements grammar from spec including `rating:sub:op:value` syntax; populates `SearchToken::sub_modifier` for `rating` tokens. Throws `InvalidTokenException` for invalid token values (`tag:*`, blank values, invalid dates, invalid colours, invalid rating sub-modifiers).  
  _Verification commands:_  
  - `php artisan test --filter=SearchTokenParserTest` (→ GREEN)  
  - `make phpstan`  
  _Notes:_ Named colour words are passed through as `value`; resolution to hex happens in `ColourStrategy` at query time via `ColourNameMap::NAMES` (Q-027-04 resolved: hardcoded PHP const array, no DB lookup in parser).

---

### I2 – Wire SearchTokenParser into HTTP layer

- [x] T-027-04 – Refactor `GetSearchRequest` to use `SearchTokenParser` (FR-027-13, NFR-027-01).  
  _Intent:_ Replace the `preg_match_all` + `$this->terms` array with `SearchTokenParser::parse()`. Introduce `HasSearchTokens` contract and `HasSearchTokensTrait`, or extend existing `HasTerms` to carry `array<SearchToken>`. Update `SearchController::search()` to call `$request->tokens()` and pass to `PhotoSearch::sqlQuery(array<SearchToken>)` signature.  
  _Verification commands:_  
  - `php artisan test --filter=Search` (existing tests must stay GREEN)  
  - `make phpstan`  
  _Notes:_ `PhotoSearch::sqlQuery()` signature changes from `array<int,string>` to `array<int,SearchToken>`. Update `AlbumSearch` if it shares the same interface.

---

### I3 – Plain-text and Tag strategies

- [x] T-027-05 – Write feature tests for plain-text backward compat and tag search (S-027-01, S-027-02, S-027-03, S-027-16).  
  _Intent:_ Tests in `tests/Feature_v2/Actions/Search/PhotoSearchTest.php` (or extend existing). Tests RED until strategies implemented.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (expect partial RED)

- [x] T-027-06 – Implement `PlainTextStrategy` (FR-027-01, S-027-01, S-027-16).  
  _Intent:_ `app/Actions/Search/Strategies/PlainTextStrategy.php`. Matches `title`, `description`, `location`, `model`, `taken_at`, and `tags.name` via `whereHas('tags', fn($q) => $q->where('name', 'like', '%'.$value.'%'))`. Implements `PhotoSearchTokenStrategy` interface.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-01, S-027-16 → GREEN)  
  - `make phpstan`

- [x] T-027-07 – Implement `TagStrategy` (FR-027-02, FR-027-03, S-027-02, S-027-03, S-027-20).  
  _Intent:_ `app/Actions/Search/Strategies/TagStrategy.php`. Exact: `whereHas('tags', fn($q) => $q->whereRaw('LOWER(name) = LOWER(?)', [$value]))`. Prefix (`is_prefix=true`): `whereHas('tags', fn($q) => $q->whereRaw('LOWER(name) LIKE LOWER(?)', [$value.'%']))`. Uses `whereHas` to avoid duplicate rows (NFR-027-02).  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-02, S-027-03 → GREEN)  
  - `make phpstan`

- [x] T-027-08 – Refactor `PhotoSearch::sqlQuery()` strategy dispatch loop (FR-027-12).  
  _Intent:_ Replace `foreach ($terms as $term)` with `foreach ($tokens as $token)` dispatching to the correct strategy via a strategy registry (simple `match` or map array). Register `PlainTextStrategy` (modifier = null) and `TagStrategy` (modifier = 'tag').  
  _Verification commands:_  
  - `php artisan test --filter=Search` (all existing + S-027-01 to S-027-03 GREEN)  
  - `make phpstan`

---

### I4 – Date and Type strategies

- [x] T-027-09 – Write feature tests for date and type filters (S-027-04 to S-027-07, S-027-17).  
  _Intent:_ Tests RED. Include: exact date, after, range (two tokens), type:jpeg, invalid date → validation error.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (expect RED for new tests)

- [x] T-027-10 – Implement `DateStrategy` (FR-027-04, FR-027-05, S-027-04, S-027-05, S-027-06, S-027-17).  
  _Intent:_ `app/Actions/Search/Strategies/DateStrategy.php`. Parse `operator` + `value`. Exact (`operator=null`): `whereDate('taken_at', '=', $value)`. Range: `where('taken_at', $operator, Carbon::parse($value))`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-04, S-027-05, S-027-06, S-027-17 → GREEN)  
  - `make phpstan`

- [x] T-027-11 – Implement `TypeStrategy` (FR-027-06, S-027-07).  
  _Intent:_ `app/Actions/Search/Strategies/TypeStrategy.php`. `where('type', 'like', '%'.$value.'%')`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-07 → GREEN)  
  - `make phpstan`

- [x] T-027-12 – Register DateStrategy and TypeStrategy in dispatch (FR-027-12).  
  _Intent:_ Add `'date' => DateStrategy::class` and `'type' => TypeStrategy::class` to the strategy registry in `PhotoSearch`.  
  _Verification commands:_  
  - `php artisan test --filter=Search` (all passing so far remain GREEN)

---

### I5 – Ratio strategy

- [x] T-027-13 – Write feature tests for ratio named buckets (S-027-08, S-027-09, S-027-10).  
  _Intent:_ Tests RED. Seed photos with known size_variants.ratio values via factory.

- [x] T-027-14 – Write feature tests for ratio numeric comparison (S-027-11).  
  _Intent:_ Tests RED.

- [x] T-027-15 – Implement `RatioStrategy` (FR-027-07, FR-027-08, S-027-08 to S-027-11).  
  _Intent:_ `app/Actions/Search/Strategies/RatioStrategy.php`. Uses `whereHas('size_variants', fn($q) => $q->where('type', 0)->where('ratio', $op, $val))`. Named buckets: `landscape` → `>`, 1.05; `portrait` → `<`, 0.95; `square` → `between` 0.95 and 1.05 (two conditions). Numeric: operator + float value. Validates bucket name (NFR-027-03).  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-08 to S-027-11 → GREEN)  
  - `make phpstan`

- [x] T-027-16 – Register RatioStrategy in dispatch.  
  _Verification commands:_  
  - `php artisan test --filter=Search`

---

### I6 – EXIF field and explicit field strategies

- [x] T-027-17 – Write feature tests for EXIF modifiers and title prefix (S-027-14, S-027-15, S-027-18).  
  _Intent:_ Tests RED.

- [x] T-027-18 – Implement `FieldLikeStrategy` (FR-027-10, FR-027-11, S-027-14, S-027-15, S-027-18).  
  _Intent:_ `app/Actions/Search/Strategies/FieldLikeStrategy.php` — generic strategy parameterised by `column: string`. LIKE `%value%`; if `is_prefix=true`, LIKE `value%`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-14, S-027-15, S-027-18 → GREEN)  
  - `make phpstan`

- [x] T-027-19 – Register FieldLikeStrategy instances for all EXIF and text modifiers.  
  _Intent:_ Register for: `make`, `lens`, `aperture`, `iso`, `shutter`, `focal`, `title`, `description`, `location`, `model`. Map unknown modifiers to `PlainTextStrategy` fallback (S-027-18).  
  _Verification commands:_  
  - `php artisan test --filter=Search`

---

### I7 – Colour palette similarity strategy

- [x] T-027-20 – Add `search_colour_distance` config migration (NFR-027-05).  
  _Intent:_ New database migration adding a row in `configs` table for `search_colour_distance` with default value `30`, type `integer`, category `search`.  
  _Verification commands:_  
  - `php artisan test` (migrations applied to SQLite test DB)

- [x] T-027-21 – Write feature tests for colour search (S-027-12, S-027-13, S-027-19).  
  _Intent:_ Tests RED. Seed a Photo with a Palette row (known colours) and assert match/no-match based on distance.

- [x] T-027-22 – Implement `ColourStrategy` (FR-027-09, S-027-12, S-027-13, S-027-19, NFR-027-04, NFR-027-05).  
  _Intent:_ `app/Actions/Search/Strategies/ColourStrategy.php`.  
  - Hex input (`#rrggbb`): parse R, G, B directly; call `Colour::fromHex($hex)` to ensure the `colours` row exists and to obtain the `Colour` instance.  
  - Named input (e.g. `red`): look up `strtolower($value)` in `ColourNameMap::NAMES`; throw `InvalidTokenException` (→ 400) if not found. Then call `Colour::fromHex($resolvedHex)` (Q-027-04 resolved: no DB lookup for names).  
  - SQL EXISTS subquery (Q-027-05 resolved — OR expansion, valid on SQLite/MySQL/PostgreSQL):  
    ```sql
    EXISTS (
      SELECT 1 FROM palette p
      JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR c.id = p.colour_3
                         OR c.id = p.colour_4 OR c.id = p.colour_5)
      WHERE p.photo_id = photos.id
        AND ABS(c.R - :R) + ABS(c.G - :G) + ABS(c.B - :B) <= :dist
    )
    ```  
  - No bit-shift needed; `colours.R/G/B` are pre-decomposed integer columns (NFR-027-04).  
  - Threshold from `ConfigManager::getValueAsInt('search_colour_distance')`.  
  - Photos with no `palette` row excluded (EXISTS fails → no match).  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-12, S-027-13, S-027-19 → GREEN)  
  - `make phpstan`

- [x] T-027-23 – Register ColourStrategy for `color` and `colour` modifier aliases.  
  _Verification commands:_  
  - `php artisan test --filter=Search`

---

### I8 – Rating sub-modifier strategy

- [x] T-027-27 – Write feature tests for rating filter (S-027-21, S-027-22; FR-027-14).  
  _Intent:_ Tests RED. One test for `rating:avg:>=4` (authenticated and unauthenticated both allowed). One test for `rating:own:>=3` authenticated (returns photos). One test for `rating:own:>=3` unauthenticated (expects 400).

- [x] T-027-28 – Implement `RatingStrategy` (FR-027-14, S-027-21, S-027-22).  
  _Intent:_ `app/Actions/Search/Strategies/RatingStrategy.php`.  
  - `sub_modifier = 'avg'`: `where('photos.rating_avg', $operator, (int) $value)`.  
  - `sub_modifier = 'own'`: guard `Auth::check()` — throw `InvalidTokenException` if unauthenticated. Then `whereHas('rating', fn($q) => $q->where('user_id', Auth::id())->where('rating', $operator, (int) $value))`.  
  - Validate: `sub_modifier` must be `avg` or `own`; `operator` required; `value` must be integer 0–5.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoSearchTest` (S-027-21, S-027-22 → GREEN)  
  - `make phpstan`

- [x] T-027-29 – Register RatingStrategy in PhotoSearch dispatch.  
  _Verification commands:_  
  - `php artisan test --filter=Search`

---

### I9 – Album search modifier support

- [x] T-027-30 – Create `AlbumSearchTokenStrategy` interface (FR-027-15, DO-027-04).  
  _Intent:_ `app/Contracts/Search/AlbumSearchTokenStrategy.php`:  
  `public function apply(Builder $query, SearchToken $token): void;`  
  _Verification commands:_  
  - `make phpstan`

- [x] T-027-31 – Write feature tests for album modifier search (S-027-23, S-027-24).  
  _Intent:_ Tests RED. Seed albums with known titles and `created_at` dates. Assert `title:summer` returns only matching albums; assert date range excludes older albums.

- [x] T-027-32 – Implement album strategies and wire into `AlbumSearch` (FR-027-15, S-027-23, S-027-24).  
  _Intent:_  
  - `app/Actions/Search/Strategies/Album/AlbumFieldLikeStrategy.php`: LIKE `%value%` on `base_albums.title` / `base_albums.description`; `is_prefix` → `value%`.  
  - `app/Actions/Search/Strategies/Album/AlbumDateStrategy.php`: date exact/range on `base_albums.created_at` (reuse Carbon parsing from DateStrategy).  
  - Refactor `AlbumSearch::addSearchCondition()` to accept `array<SearchToken>` and dispatch to these strategies (fallback to LIKE on title + description for plain-text tokens).  
  - Update `SearchController::search()` to pass `$request->tokens()` to `AlbumSearch::queryAlbums()` and `queryTagAlbums()`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSearch` (S-027-23, S-027-24 → GREEN; existing album tests remain GREEN)  
  - `make phpstan`

---

### I10 – Final validation and documentation

- [x] T-027-24 – Full quality gate (all tests, phpstan, php-cs-fixer).  
  _Intent:_ Ensure zero regressions and clean style.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix` (no diff)  
  - `php artisan test` (all green)  
  - `make phpstan` (exit 0)

- [x] T-027-33 – Update `knowledge-map.md` (DO-027-01 to DO-027-04).  
  _Intent:_ Add `SearchToken` (with `sub_modifier`), `SearchTokenParser`, `PhotoSearchTokenStrategy` (interface), `AlbumSearchTokenStrategy` (interface), and per-strategy classes to the Search module section.

- [x] T-027-34 – Update `roadmap.md` Feature 027 status to complete.

---

## Notes / TODOs

- Q-027-01 resolved: use `colours` table R/G/B columns via JOIN on `palette.colour_N = colours.id`. No bit-shift in SQL, no schema migration needed.
- Q-027-02 resolved: both `rating:avg:` and `rating:own:` sub-modifiers supported (I8).
- Q-027-03 resolved: album modifier support included in I9.
- Q-027-04 resolved: named CSS colours resolved via hardcoded `ColourNameMap::NAMES` PHP const array in `ColourStrategy`. No DB lookup, no schema migration. Unknown names → 400.
- Q-027-05 resolved: colour EXISTS subquery uses `JOIN colours c ON (c.id = p.colour_1 OR … OR c.id = p.colour_5)` — valid standard SQL on all three target engines.
- Ensure `rating:own:` guards `Auth::check()` in the strategy before building the query (not just in the parser) since the auth state may differ between parse time and query time.
