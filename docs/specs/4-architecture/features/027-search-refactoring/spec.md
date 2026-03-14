# Feature 027 – Search Refactoring

| Field        | Value                                                                      |
|--------------|----------------------------------------------------------------------------|
| Status       | Draft                                                                      |
| Last updated | 2026-03-12 (Q-027-01/02/03/04/05 resolved) |
| Owners       | LycheeOrg                                                                  |
| Linked plan  | `docs/specs/4-architecture/features/027-search-refactoring/plan.md`        |
| Linked tasks | `docs/specs/4-architecture/features/027-search-refactoring/tasks.md`       |
| Roadmap entry | #27                                                                       |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

---

## Overview

The current photo search is a flat `LIKE '%term%'` clause evaluated across a handful of text columns (`title`, `description`, `location`, `model`, `taken_at`). Tags are not searched at all, and there is no way to express structured filters such as date ranges, MIME type, aspect ratio, or colour similarity. This feature introduces a **token-based search grammar** parsed from the existing free-text query string, delegates each token to a dedicated query builder strategy, and exposes the new capabilities through the same `GET /Search` REST endpoint while remaining fully backward-compatible with plain-text queries.

Affected modules: `app/Actions/Search`, `app/Http/Requests/Search`, `app/Http/Controllers/Gallery/SearchController`, and the frontend search bar component.

---

## Goals

1. Support structured search tokens (modifier:value format, e.g. `tag:sunset`) while keeping plain-text terms backward compatible.
2. Enable tag search (exact and prefix).
3. Enable date-based filtering (exact, before, after, range) for both photos and albums.
4. Enable MIME / media-type filtering.
5. Enable aspect-ratio filtering (named buckets and numeric comparisons).
6. Enable colour-palette similarity search against stored palette data (using `colours` table R/G/B decomposition).
7. Enable explicit field prefix search for additional EXIF fields (`make`, `lens`, `aperture`, `iso`, `shutter`, `focal`, `location`, `title`, `description`).
8. Enable rating filter via sub-modifiers `rating:avg:` (global average) and `rating:own:` (user's own rating).
9. Enable album title / description / date modifier search in `AlbumSearch` using the same token infrastructure.
10. Ensure the new parser and token strategies are independently unit-testable.

---

## Non-Goals

- Full-text search engine integration (Elasticsearch, MeiliSearch, etc.).
- Frontend UI redesign for the search bar (out of scope; the bar stays as a plain text input—modifiers are typed manually).
- Saved/pinned searches.
- Sorting/ranking by relevance score.
- Colour search on externally-hosted photos (palette must be pre-computed).

---

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path |
|----|-------------|--------------|-----------------|--------------|
| FR-027-01 | **Plain-text backward-compat.** A term with no modifier prefix must match `title`, `description`, `location`, `model`, `taken_at`, and `tags.name` (LIKE `%term%`). | Query returns all photos matching any of those fields. | If no modifier detected, full-text LIKE applied. | No failure—no results is a valid empty set. |
| FR-027-02 | **Tag exact search.** `tag:sunset` matches photos whose tag list contains a tag with `name = 'sunset'` (case-insensitive). | Join on `photos_tags`/`tags`; `LOWER(tags.name) = LOWER('sunset')`. | Token is recognised by `tag:` prefix; value must be non-empty. | `400` if value is blank; silently skipped if tag does not exist. |
| FR-027-03 | **Tag prefix search.** `tag:sun*` matches photos where at least one tag `name` starts with `sun` (case-insensitive). | Trailing `*` triggers `LIKE 'sun%'` on `tags.name`. | Wildcard detected; prefix must be ≥1 character. | `400` if prefix is empty (i.e. `tag:*` alone). |
| FR-027-04 | **Date exact.** `date:2024-05-01` matches photos where `DATE(taken_at) = '2024-05-01'`. | SQL: `WHERE DATE(photos.taken_at) = ?`. | Value must parse to a valid date (`Y-m-d`). | `400` if value is not a valid `Y-m-d` date. |
| FR-027-05 | **Date range.** Operators `<`, `<=`, `>`, `>=` are supported: `date:>2024-01-01`, `date:<=2024-12-31`. Multiple `date:` tokens combine as AND. | SQL range clauses on `taken_at`. | Operator + date validated. | `400` on invalid operator or date value. |
| FR-027-06 | **Type filter.** `type:jpeg` or `type:image/jpeg` restricts results to photos where `photos.type LIKE '%jpeg%'`. | SQL: `WHERE photos.type LIKE '%jpeg%'`. | Value normalised to lowercase; empty value rejected. | `400` if value is blank. |
| FR-027-07 | **Ratio named buckets.** `ratio:landscape` returns photos with `ratio > 1.05`; `ratio:portrait` → `ratio < 0.95`; `ratio:square` → `0.95 ≤ ratio ≤ 1.05`. Ratio is sourced from `size_variants` where `type = ORIGINAL` (type 0). | JOIN on `size_variants`; WHERE clause on `ratio`. | Bucket name must be one of `landscape`, `portrait`, `square`. | `400` for unrecognised bucket name. |
| FR-027-08 | **Ratio numeric comparison.** `ratio:>1.5`, `ratio:<=0.75` etc. using the same comparison operators as date. | SQL: `WHERE size_variants.ratio > 1.5`. | Numeric value must be a positive float; operator must be `<`, `<=`, `>`, `>=`, or `=`. | `400` on non-numeric value or invalid operator. |
| FR-027-09 | **Colour similarity.** `color:#rrggbb` (or `colour:#rrggbb`) returns photos whose `palette` rows contain at least one colour whose `colours` table entry satisfies Manhattan distance `ABS(c.R-R) + ABS(c.G-G) + ABS(c.B-B) \<= threshold`. Named CSS colour words (e.g. `color:red`) are resolved via a hardcoded `ColourNameMap` (PHP `const` array of the 16 CSS Level 1 names → hex strings) inside `ColourStrategy`; the resolved hex is then passed to `Colour::fromHex()`. | SQL: `EXISTS (SELECT 1 FROM palette p JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR c.id = p.colour_3 OR c.id = p.colour_4 OR c.id = p.colour_5) WHERE p.photo_id = photos.id AND ABS(c.R-:R)+ABS(c.G-:G)+ABS(c.B-:B) <= :dist)`. Threshold sourced from config key `search_colour_distance` (default 30). | Value must be a valid 6-digit hex `#rrggbb` or a name present in `ColourNameMap`. | `400` on invalid hex or unrecognised colour name. Photos without a `palette` row are excluded (EXISTS fails). |
| FR-027-10 | **EXIF field prefix.** Modifiers `make:`, `lens:`, `aperture:`, `iso:`, `shutter:`, `focal:` each match LIKE `%value%` on the corresponding `photos` column. Trailing `*` switches to LIKE `value%`. | SQL: `WHERE photos.<field> LIKE '%value%'`. | Value must be non-empty. | `400` if value is blank. |
| FR-027-11 | **Title / description explicit.** `title:foo` and `description:bar` allow explicit field-only matching (also support trailing `*` for prefix). | SQL: `WHERE photos.title LIKE '%foo%'`. | Non-empty value required. | `400` if blank. |
| FR-027-12 | **Multiple tokens AND semantics.** Each `$query->where(...)` wrapping remains: all tokens are ANDed together. Within a single plain-text term the OR across fields is unchanged. | Compound query: each token adds an AND WHERE group. | N/A—parser picks up all tokens. | Empty result set is valid. |
| FR-027-13 | **Token parser exposed via service.** A new `SearchTokenParser` service class parses the raw decoded query string into an `array<SearchToken>` DTO array. `GetSearchRequest` calls the parser; the result replaces the current `$this->terms` array approach. | `SearchTokenParser::parse('tag:sun date:>2024')` returns two `SearchToken` objects. | Service is injected/resolved; can be unit-tested in isolation. | Parser throws `InvalidTokenException` (caught, returns 400). |
| FR-027-14 | **Rating sub-modifier filter.** `rating:avg:>=4` filters photos where `photos.rating_avg >= 4`. `rating:own:>=4` filters photos where `photo_ratings.rating >= 4` for `user_id = Auth::id()`. The `own:` sub-modifier requires authentication; unauthenticated users receive a `400` if they use `rating:own:`. | `rating:avg:>=4` → `WHERE photos.rating_avg >= 4`. `rating:own:>=4` → `WHERE EXISTS (SELECT 1 FROM photo_ratings WHERE photo_id = photos.id AND user_id = :uid AND rating >= 4)`. | Sub-modifier must be `avg` or `own`; operator must be one of `<`, `<=`, `>`, `>=`, `=`; value must be an integer 0–5. | `400` if sub-modifier is unrecognised, operator invalid, or value out of range. `400` if `rating:own:` used without authentication. |
| FR-027-15 | **Album modifier search.** `AlbumSearch` is wired to the same `SearchTokenParser`. Supported album modifiers: `title:` (LIKE `%value%`, prefix with `*`), `description:` (same), `date:` (exact or range on `base_albums.created_at`). Unrecognised modifiers and plain-text tokens fall back to existing LIKE on `base_albums.title` and `base_albums.description`. | Plain and modifier tokens each add AND WHERE clauses to the album query. | Same validation rules as photo equivalents. | `400` on invalid date or blank value. |

---

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies |
|----|-------------|--------|-------------|--------------|
| NFR-027-01 | Backward compatibility: existing clients sending plain-text queries must receive equivalent or better results. | No breaking API change. | Existing Feature tests pass without modification. | FR-027-01 |
| NFR-027-02 | Tag JOIN must not double-count photos (DISTINCT or EXISTS subquery). | Correctness: a photo with 3 matching tags must appear once. | Verified by unit test asserting result count. | FR-027-02, FR-027-03 |
| NFR-027-03 | Ratio JOIN (`size_variants`) must filter to `type = 0` (ORIGINAL) only. | Correctness: thumbnail ratios may differ from the original. | Unit test with photos having multiple size variants. | FR-027-07, FR-027-08 |
| NFR-027-04 | Colour distance computation must be done in SQL (no PHP post-filtering) to respect the pagination applied in the controller. The query uses `JOIN colours c ON (c.id = p.colour_1 OR … OR c.id = p.colour_5)` and compares `colours.R/G/B` directly—no bit-shift; portable across SQLite, MySQL, and PostgreSQL. | Correctness with paginated results and DB portability. | Query inspection test: assert no lazy-loading post-filter; test must pass on SQLite. | FR-027-09 |
| NFR-027-05 | The RGB distance threshold for colour search must be stored as a Lychee config key `search_colour_distance` (integer, default 30). | Configurability. | Config key present after migration; documented in admin panel description. | FR-027-09 |
| NFR-027-06 | All new token-handling code must be covered by PHPUnit unit tests (Unit layer) and at least one feature-level integration test per token type. | Quality. | `php artisan test` green; coverage report shows token strategies covered. | All FRs. |
| NFR-027-07 | `SearchTokenParser` must be free of side effects (no DB calls); all query building happens in `PhotoSearch`. | Testability / SRP. | Unit test for parser uses no database. | FR-027-13 |

---

## UI / Interaction Mock-ups

The search bar is unchanged. Users type modifiers directly as text. Example query typed into the search bar:

```
tag:landscape date:>2024-01-01 ratio:landscape
```

The existing search bar renders tokens as plain text. A future enhancement (out of scope) could parse tokens client-side and render badges.

---

## Search Grammar Reference (normative)

```
query       = token ( WS token )*
token       = modifier ":" sub_token | quoted_term | plain_term
sub_token   = sub_modifier ":" op value_body   # rating only
            | op value_body
            | value_body
modifier    = "tag" | "date" | "type" | "ratio" | "color" | "colour"
            | "make" | "lens" | "aperture" | "iso" | "shutter" | "focal"
            | "title" | "description" | "location" | "model"
            | "rating"
sub_modifier= "avg" | "own"   # used only with "rating"
op          = ">" | ">=" | "<" | "<="
value_body  = WORD | QUOTED_STRING
plain_term  = WORD | QUOTED_STRING   # no ":" inside
```

- Tokens are separated by spaces.  
- Quoted strings (`"hello world"`) preserve spaces within a single token.  
- Unrecognised modifiers fall back to plain-text matching (i.e. the raw `modifier:value` string is treated as a plain-text term against all default fields).  
- All matching is case-insensitive.

---

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-027-01 | Plain-text term returns photos matching title, description, location, model, taken_at, or tags (backward compat). |
| S-027-02 | `tag:sunset` returns only photos tagged "Sunset" (case-insensitive), no duplicates. |
| S-027-03 | `tag:sun*` returns photos with any tag beginning "sun". |
| S-027-04 | `date:2024-05-01` returns photos taken on that exact calendar date. |
| S-027-05 | `date:>2024-01-01` returns photos taken after that date. |
| S-027-06 | `date:>2024-01-01 date:<=2024-12-31` returns photos taken within the year 2024. |
| S-027-07 | `type:jpeg` returns only JPEG photos. |
| S-027-08 | `ratio:landscape` returns only landscape-oriented photos (ratio > 1.05). |
| S-027-09 | `ratio:portrait` returns only portrait-oriented photos (ratio < 0.95). |
| S-027-10 | `ratio:square` returns photos with ratio in [0.95, 1.05]. |
| S-027-11 | `ratio:>1.5` returns photos with ratio strictly greater than 1.5. |
| S-027-12 | `color:#ff0000` returns photos with at least one palette colour within distance 30 of pure red. |
| S-027-13 | `color:red` (named colour) resolves to `#ff0000` and behaves like S-027-12. |
| S-027-14 | `make:Canon lens:50mm` returns Canon photos with a 50mm lens. |
| S-027-15 | `title:holiday*` returns photos with titles starting with "holiday". |
| S-027-16 | Two plain-text terms `cat dog` return photos matching BOTH terms (existing AND semantics preserved). |
| S-027-17 | Query with an invalid date `date:notadate` returns HTTP 400. |
| S-027-18 | Query with unknown modifier `foo:bar` treats `foo:bar` as a plain-text term. |
| S-027-19 | Photo without a palette row is excluded from `color:` search results. |
| S-027-20 | `tag:*` (wildcard only, no prefix) returns HTTP 400. |
| S-027-21 | `rating:avg:>=4` returns photos with average rating ≥ 4; unauthenticated access permitted. |
| S-027-22 | `rating:own:>=3` returns photos rated ≥ 3 by the authenticated user; returns HTTP 400 for unauthenticated. |
| S-027-23 | `title:summer` on album search returns albums whose title contains "summer". |
| S-027-24 | `date:>2024-01-01` on album search returns albums created after that date. |

---

## Test Strategy

- **Unit (tests/Unit):** `SearchTokenParserTest` — one test per grammar rule; no DB. `SearchTokenTest` — DTO value validation. One test class per token strategy (`TagTokenStrategyTest`, `DateTokenStrategyTest`, `RatingTokenStrategyTest`, etc.).
- **Feature (tests/Feature_v2):** One `PhotoSearchTest` class with one test method per photo scenario; one `AlbumSearchTest` class for album scenarios (S-027-23/S-027-24), using the in-memory SQLite database and `BaseApiWithDataTest`.
- **REST:** `GET /Search?term=<base64>` integration assertions inside existing `SearchController` feature tests.
- **Docs / Contracts:** OpenAPI scramble snapshot updated to reflect no new query parameters (term encoding unchanged).

---

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-027-01 | `SearchToken` DTO: `modifier: ?string`, `sub_modifier: ?string` (`avg`\|`own`, used only for `rating`), `operator: ?string` (`<`, `<=`, `>`, `>=`, `=`), `value: string`, `is_prefix: bool`. | Actions/Search, Http/Requests/Search |
| DO-027-02 | `SearchTokenParser` service: `parse(string $raw): array<SearchToken>`. No DB access. | Actions/Search |
| DO-027-03 | `PhotoSearchTokenStrategy` interface: `apply(Builder $query, SearchToken $token): void`. | Actions/Search |
| DO-027-04 | `AlbumSearchTokenStrategy` interface: `apply(Builder $query, SearchToken $token): void`. Mirrors DO-027-03 for album queries. | Actions/Search |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-027-01 | `GET /Search` | Unchanged endpoint; term encoding (base64) and `album_id` remain. | FR-027-01 backward compat. |

### Config Keys

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `search_colour_distance` | integer | 30 | Maximum Manhattan RGB distance (`ABS(c.R-R0)+ABS(c.G-G0)+ABS(c.B-B0)`) for palette colour matching. Uses `colours` table R/G/B columns via `JOIN colours c ON (c.id = p.colour_1 OR … OR c.id = p.colour_5)`. |

---

## Telemetry & Observability

No new telemetry events. Existing query-log behaviour (Laravel query logging in debug mode) covers all SQL produced.

---

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md`: add `SearchToken` (with `sub_modifier` field), `SearchTokenParser`, `PhotoSearchTokenStrategy`, `AlbumSearchTokenStrategy`, and per-strategy classes to the Search module section.
- Update `docs/specs/4-architecture/roadmap.md`: add Feature 027 entry.
- Admin config panel: add description for `search_colour_distance` key.

---

*Last updated: 2026-03-12 (Q-027-01/02/03 resolved)*
