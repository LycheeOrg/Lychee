# Feature 069 – Search Struct-of-Arrays

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-09-22 |
| Owners | ildyria |
| Linked plan | [docs/specs/4-architecture/features/069-search-struct-of-arrays/plan.md](plan.md) |
| Linked tasks | [docs/specs/4-architecture/features/069-search-struct-of-arrays/tasks.md](tasks.md) |
| Roadmap entry | Feature 069 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview

Search is the last v8 gallery view still served entirely by API v2. Features 062, 064/065, 066, 067 and 068 moved root album listing, per-album photo listing, Timeline, Map and Flow onto the v3 Struct-of-Arrays convention (ADR-0009); `GET /api/v2/Search` remains an Array-of-Structs endpoint that paginates photos at 300/page through the full `PhotoResource`, and returns album hits unpaginated through the heavy `ThumbAlbumResource`.

This feature adds a dedicated `GET /api/v3/Search/*` family (albums + a `/rights` tier, photos + an on-demand `/details` tier), and migrates the v8 Search page onto it behind the existing `features.struct-of-array` flag. Affected modules: REST (new v3 controller, request classes, resources, query actions), core (a config rename migration), and UI (v8 Search view, its store, and its service client). The v2 route and the v7 frontend are untouched.

Two decisions shape the whole design and are recorded in full in [open-questions.md](../../open-questions.md): photo results are delivered **whole-scope and unpaginated** rather than bucket-windowed (Q-069-02, Option B), bounded instead by a repurposed hard result cap with an explicit truncation signal (Q-069-10, Option A). Consequently this feature introduces **no bucket tier** — the only SoA photo consumer without one.

## Goals

1. Serve search results over API v3 in Struct-of-Arrays form, for both the album half and the photo half.
2. Remove the per-photo serialization cost of v2's `PhotoResource` (two `Gate` checks plus several config reads per photo, ×300 per page) by resolving every gate once per request.
3. Remove the album half's unbounded, per-album policy/thumbnail/`owner` N+1 cost by reusing the already-shipped `BuildAlbumDataResource` + `AlbumRightsResource` split.
4. Replace page-jump pagination with the same virtualized rendering every other v8 gallery view already uses, reusing `PhotoGridVirtual.vue` via its existing `source` prop seam.
5. Return a correct distinct-photo `total`, fixing v2's missing `distinct()` (Q-069-08).
6. Bound the unpaginated response with an admin-tunable result cap and report truncation honestly to the user.
7. Keep the v2 `GET /Search` endpoint fully functional for the v7 frontend and for Spotlight.

## Non-Goals

- **NG1 — No bucket tier** (ADR-0010 strategy 3, not strategy 2)**.** Per Q-069-02 (Option B) search photos are returned whole-scope in one request. No `/Search/Photos/buckets` route, no sticky bucket headers, no scrubber. The frontend renders one flat chunk via `PhotoGridVirtual.vue`'s already-supported `bucketable: false` path.
- **NG2 — `SpotlightSearch.vue` is not migrated** (Q-069-04). The global quick-search palette keeps calling `GET /api/v2/Search`; it renders a short capped list that gains nothing from tiering, and v2 survives for v7 regardless.
- **NG3 — `date:` semantics are unchanged** (Q-069-05). `AlbumDateStrategy` keeps matching `base_albums.created_at` while `DateStrategy` matches `photos.taken_at`. Carried into v3 verbatim so that any result difference during migration is a real regression, not an intended one.
- **NG4 — No change to the token grammar.** `SearchTokenParser`, every `PhotoSearchTokenStrategy`/`AlbumSearchTokenStrategy`, `ColourNameMap` and the advanced-search panel are reused exactly as they are. This feature changes transport and shape, not what matches.
- **NG5 — No change to the v2 endpoint.** `GET /api/v2/Search` and `GET /api/v2/Search::init` keep their current behaviour, including v2's own duplicate-row counting.
- **NG6 — `Search::init` is not ported.** It returns two scalar config values, not a collection; ADR-0009 scopes the SoA convention to collection endpoints. The v3 Search page keeps calling the existing v2 `Search::init`.
- **NG7 — No full-text/FTS index.** Plain-text matching stays `LIKE %term%`. Improving match quality is out of scope.
- **NG8 — No thumbnail bytes in any response.** Cover and photo imagery is fetched by the frontend from the existing v3 Asset endpoint (Feature 056) via `ThumbAssetService`/`<Thumb>`, exactly as Features 063/065/067/068 already do.
- **NG9 — `face_count` and `preformatted.filesize` stay unpopulated** on SoA-sourced tiles, inheriting Feature 065's documented regressions (Q-065-06) unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-069-01 | A new `GET /api/v3/Search/Photos` returns the photo half of a search as a Struct-of-Arrays body, whole-scope and never paginated. | Returns one index-aligned row per distinct matching photo, ordered by the effective sort. | `terms` required; `album_id` optional and must be a valid random ID; `sorting_column`/`sorting_order` validated against `SearchSortingType`/`OrderSortingType`. | 422 on invalid params; 403 when the feature flag or `search_public` gate denies. | Existing request logging only. | Q-069-01 (A), Q-069-02 (B) |
| FR-069-02 | The photo tier is bounded by the `search_result_limit` config (ADR-0010 strategy 3, "capped with truncation"). The query selects `limit + 1` rows; if the extra row exists, only the first `limit` rows are returned and `is_truncated` is `true`. | Under the cap: every match returned, `is_truncated: false`. | `limit` is server-side config, never a request parameter. | — | — | Q-069-10 (A) |
| FR-069-03 | The config key `search_pagination_limit` is renamed `search_result_limit` by migration, preserving its currently stored value, and its documented meaning changes from "photos per page" to "maximum photo hits returned per search". | Existing installs keep their tuned value (default 300). | — | — | — | Q-069-10 (A) |
| FR-069-04 | Each photo row carries an `album_ids[i]`: one concrete, viewer-accessible album id for that photo, resolved by a separate join-and-collapse pass to the lowest accessible `album_id`. | Feeds the `{album_id}` path segment of the v3 Asset endpoint so `<Thumb>` can render a cross-album result. | — | A photo the viewer owns but which belongs to no album (searchable in v2 via `owner_id`) reports the `unsorted` smart album id instead, so the field is never null (Q-069-11). | — | Q-069-07, Q-069-11, Feature 067 Q-067-11 |
| FR-069-05 | The photo tier returns exactly one row per distinct `photos.id`, regardless of how many albums the photo belongs to. | A photo in N albums appears once. | — | — | — | Q-069-08 |
| FR-069-06 | A new `GET /api/v3/Search/Photos/details` returns the bounded, richer per-photo payload for an explicit `photo_ids[]` set. | Combined with FR-069-01's tier, reconstructs every field of v2's `PhotoResource` for any photo the caller can see, matching Feature 064's own parity contract. | `photo_ids[]` required, max 300 entries; `terms` required (the details tier re-applies the search predicate as its authorization filter). | 422 above 300 ids; ids the caller cannot see are silently absent from the response, never an error. | — | Q-069-01 (A), Feature 064 Q-064-04/06 |
| FR-069-07 | A new `GET /api/v3/Search/albums` returns the album half as a Struct-of-Arrays body, whole-scope and never paginated, reusing `AlbumDataResource` unchanged. | One row per matching, browsable album, ordered by the effective album sort. | Same parameter rules as FR-069-01. | Same as FR-069-01. | — | Q-069-03 (A) |
| FR-069-08 | A new `GET /api/v3/Search/albums/rights` returns the per-album permission signals for the same matching set, reusing `AlbumRightsResource` unchanged. | `grants_edit`/`grants_download` index-aligned with `ids`. | Same parameter rules as FR-069-01. | Same as FR-069-01. | — | Q-069-03 (A) |
| FR-069-09 | Because a search result set has no single shared parent album, `AlbumRightsResource.owner_id` is omitted from the payload entirely (`Optional`), and `can_delete_children`/`can_move_children` are both `false`. | Mirrors `/Albums/root/rights`' own resolution of the identical heterogeneous-parent problem (Q-062-16). | — | — | — | Q-069-03 (A), Q-062-16 |
| FR-069-10 | All four routes are gated at `FormRequest::authorize()` by `features.struct-of-array`, then by the existing search gates: `search_public` for guests, and `AlbumPolicy::CAN_ACCESS` on the optional `album_id` origin. | An authorized caller receives results. | — | 403 when any gate denies, matching v2's `GetSearchRequest::authorize()` exactly. | — | Feature 064/066 gating convention |
| FR-069-11 | The `terms` parameter stays base64-encoded, decoded server-side via `base64_decode($value, true)` and parsed by the existing `SearchTokenParser`. | Token grammar (`:`, `>=`, `"`, `#`, `*`) survives any proxy intact. | Undecodable input is a 422 on `terms`. | — | — | Q-069-06 |
| FR-069-12 | Both halves honour the optional `album_id` origin: photo matching is restricted to that album's nested-set subtree, album matching to its descendants — reproducing `PhotoSearch::sqlQuery()`/`AlbumSearch::queryAlbums()`'s existing `_lft`/`_rgt` bounds. | Album-scoped search returns only in-subtree hits. | A smart/tag/person album passed as `album_id` is treated as no origin, exactly as `SearchController::search()` does today. | — | — | Parity with v2 |
| FR-069-13 | Photo visibility uses `PhotoQueryPolicy::applySearchabilityFilter()` and album visibility uses `AlbumQueryPolicy::applyBrowsabilityFilter()`, with NSFW gated by `hide_nsfw_in_search` — the same policy calls v2 makes. | Identical result membership to v2 (modulo FR-069-05's dedup). | — | — | — | Parity with v2 |
| FR-069-14 | Every conditional field gate (`rating_enabled`+`CAN_READ_RATINGS`, `display_thumb_photo_overlay`, `photo_thumb_info`, `photo_thumb_tags_enabled`, `display_exif_data`, `gps_coordinate_display`, `location_show`, `metrics_enabled`) is evaluated **once per request** and applied uniformly, never per photo. | Absent gates omit the key entirely via `Optional::create()`, never a null-filled array. | — | — | — | Feature 064 convention |
| FR-069-15 | All four routes are cached through `ManagedCacheService::rememberIf()` under `managed_cache_albums_enabled`/`managed_cache_ttl`, keyed on a digest of the parsed token list plus origin album, sort, user id and `unlockedAlbumsDigest()`. | Repeat identical searches hit cache. | — | Cache disabled ⇒ direct query, identical body. | Existing managed-cache event logging. | Feature 064/067 convention |
| FR-069-16 | v8's Search page consumes the v3 family when `is_struct_of_array_enabled` is true, via a new `isSearchSoaActive` getter on `SearchState.ts`; otherwise it keeps its current v2 path unchanged. | Flag on ⇒ virtualized SoA rendering; flag off ⇒ today's behaviour byte-for-byte. | — | — | — | Feature 065/066/067/068 convention |
| FR-069-17 | On the v3 path the photo grid renders through `PhotoGridVirtual.vue` with a new `source="search"` value, and the album grid through the existing `AlbumThumbGridVirtual.vue`. | All five photo layout modes work, as they already do for `source="album"`. | — | — | — | Feature 065 |
| FR-069-18 | On the v3 path the page-jump `UPagination` controls above and below the photo grid are removed, and `Search.vue`'s nested `overflow-y-auto` scroll container is dropped so `useWindowVirtualizer` tracks the real window scroll. | Smooth virtualized scrolling with no desync. | — | — | — | Timeline.vue:30-38, Flow.vue:30-39 |
| FR-069-19 | When `is_truncated` is true the UI shows a non-blocking hint above the photo grid stating that only the first N matches are shown and the search should be refined. | Hint visible, results still fully interactive. | — | — | — | Q-069-10 (A) |
| FR-069-20 | Search results are held in v3-specific store state rather than being written into the shared `AlbumsState.albums`/`PhotosState.photos`, which are compacted into only for lightbox compatibility — mirroring `TimelineState.ts`'s `tilesV3`/`_syncPhotosStoreV3()` split. | Navigating away from a search no longer leaves browsing data bleeding into the grid. | — | — | — | Feature 066 |
| FR-069-21 | Tier-3 `details` are fetched on demand only (lightbox open, neighbours prefetched), deduped by resolved id and chunked at 300 ids per request. | Grid scrolling never triggers a details fetch. | — | — | — | Feature 065 `loadPhotoDetails()` |
| FR-069-22 | A stale-response guard discards any in-flight v3 response whose generation counter no longer matches, reusing `SearchState.ts`'s existing `requestToken` mechanism. | Rapid re-searches never render stale results. | — | — | — | Existing `requestToken` |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-069-01 | No response may exceed `search_result_limit` photo rows. | The whole-scope choice removed the only existing ceiling. | Feature test asserting `limit + 1` matches yield `limit` rows and `is_truncated: true`. | FR-069-02 | Q-069-10, ADR-0010 |
| NFR-069-02 | The photo and album tiers build their bodies from flat `toBase()` queries with no Eloquent model hydration and no eager loading. | v2 eager-loads 6 relations per photo and hydrates every album. | Code review + a query-count assertion. | — | Feature 064/067 precedent |
| NFR-069-03 | No `Gate::check()` or `ConfigManager` read inside any per-row loop. | v2 performs 2 Gate checks per photo. | Code review; FR-069-14. | — | Feature 064 |
| NFR-069-04 | No `Carbon` instantiation in any request-path tier; date handling uses raw string slicing or native `date()`/`mktime()`. | Owner directive; `Carbon` is disproportionately costly per row. | Code review. | — | Owner directive, Feature 066 precedent |
| NFR-069-05 | The `album_ids[]` resolution pass must not hydrate `Album` models or re-check access per photo. | Would reintroduce the N+1 this feature exists to remove. | Code review against `QueryMapPhotos`'s implementation. | FR-069-04 | Q-067-11 |
| NFR-069-06 | Parity: for any search where v2 returns ≤ one page of results and no photo belongs to more than one album, the v3 tiers must reconstruct v2's `ResultsResource` field-for-field, except: the fields NG9 documents; datetime **serialization** (v2 emits a Carbon-formatted ISO 8601 string, v3 the raw DB value — same instant, per NFR-069-04 and the Feature 064/066 convention); and `size_variants.original.url`, which v3 gates per photo rather than per request (Q-069-12). | Guards the migration. | Parity feature test comparing v2 and v3 responses for a fixture search, asserting dates as instants and excluding the one documented field. | — | Feature 064 Q-064-06, Q-069-12 |
| NFR-069-07 | The v2 endpoint's behaviour, and the v7 frontend, must be byte-for-byte unaffected. | v7 is still shipped; Spotlight still depends on v2. | Existing `SearchTest`/`PhotoSearchTest`/`AlbumSearchTest`/`SearchSortingTest` pass unmodified. | NG5 | Guardrails |
| NFR-069-08 | Every new file carries the project licence header; PHP uses `===`, no `empty()`, `in_array(..., true)`, snake_case locals, PSR-4. | Coding conventions. | `vendor/bin/php-cs-fixer fix`, `make phpstan` (level 6+, 0 errors). | — | [coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-069-09 | The config rename must update all 22 locale files' `all_settings.php` entries, regenerated via `php artisan lang:json`. | `lang/<locale>/*.php` is hand-edited source; `.json` is generated. | `LangTest::testLanguageConsistency` green. | FR-069-03 | Memory: lang files are PHP source |
| NFR-069-10 | No network dependency is introduced; all assets resolve locally. | Lychee must work fully offline. | Code review. | — | Owner directive |

## UI / Interaction Mock-ups

Current v2 path (unchanged when the flag is off) — page-jump pagination above and below the grid:

```
┌─ Search ──────────────────────────────────── [album chip ×] ─┐
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ 🔍 sunset beach                            [ Search ]    │ │
│ │    ▸ Advanced search                                     │ │
│ └──────────────────────────────────────────────────────────┘ │
│ Albums (3)                                                   │
│ ┌────────┐ ┌────────┐ ┌────────┐                             │
│ │  img   │ │  img   │ │  img   │                             │
│ └────────┘ └────────┘ └────────┘                             │
│                  ‹ 1  2  3  4 ›            ← page-jump       │
│ Photos (1204)                                                │
│ ┌────┐┌────┐┌────┐┌────┐┌────┐┌────┐                         │
│ └────┘└────┘└────┘└────┘└────┘└────┘                         │
│                  ‹ 1  2  3  4 ›            ← page-jump       │
└──────────────────────────────────────────────────────────────┘
```

New v3 path (flag on) — no pagination, window-virtualized, with the truncation hint:

```
┌─ Search ──────────────────────────────────── [album chip ×] ─┐
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ 🔍 sunset beach                            [ Search ]    │ │
│ │    ▸ Advanced search                                     │ │
│ └──────────────────────────────────────────────────────────┘ │
│ Albums (3)                                                   │
│ ┌────────┐ ┌────────┐ ┌────────┐     ← AlbumThumbGridVirtual │
│ │ <Thumb>│ │ <Thumb>│ │ <Thumb>│                             │
│ └────────┘ └────────┘ └────────┘                             │
│ Photos (300)                                                 │
│ ⚠ Showing the first 300 matches — refine your search.        │
│ ┌────┐┌────┐┌────┐┌────┐┌────┐┌────┐ ← PhotoGridVirtual      │
│ └────┘└────┘└────┘└────┘└────┘└────┘   source="search"       │
│ ┌────┐┌────┐┌────┐┌────┐┌────┐┌────┐                         │
│ └────┘└────┘└────┘└────┘└────┘└────┘                         │
│              ⋮ (window scroll, no inner scrollbar)           │
└──────────────────────────────────────────────────────────────┘
```

The truncation hint (UI-069-03) appears only when `is_truncated` is true; when false the row is absent entirely, not rendered empty.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-069-01 | Authenticated user searches a plain term ⇒ both tiers return index-aligned SoA bodies; photo count equals distinct matches. |
| S-069-02 | Guest searches with `search_public=true` ⇒ results returned, gated identically to v2. |
| S-069-03 | Guest searches with `search_public=false` ⇒ 403 on all four routes. |
| S-069-04 | `features.struct-of-array` disabled ⇒ 403 on all four routes; v2 endpoint unaffected. |
| S-069-05 | Missing `terms` ⇒ 422. |
| S-069-06 | `terms` that is not valid base64 ⇒ 422 on `terms`. |
| S-069-07 | Invalid `sorting_column` ⇒ 422; `sorting_column` without `sorting_order` ⇒ 422 (parity with `SearchSortingTest`). |
| S-069-08 | A photo belonging to 3 albums matches ⇒ appears exactly once; `total` counts it once (FR-069-05). |
| S-069-09 | Matches exceed `search_result_limit` ⇒ exactly `limit` rows returned, `is_truncated: true`. |
| S-069-10 | Matches equal `search_result_limit` exactly ⇒ all rows returned, `is_truncated: false` (off-by-one boundary). |
| S-069-11 | Each returned photo's `album_ids[i]` is an album the caller can actually access, and is the lowest accessible one for a multi-album photo. |
| S-069-12 | Album-scoped search (`album_id` = a real album) ⇒ only in-subtree photos and descendant albums returned. |
| S-069-13 | `album_id` = a smart/tag/person album ⇒ treated as no origin, matching v2. |
| S-069-14 | Album hits for a set spanning multiple parents ⇒ `owner_id` key absent, `can_delete_children`/`can_move_children` both false (FR-069-09). |
| S-069-15 | NSFW photos excluded when `hide_nsfw_in_search` is true, included when false. |
| S-069-16 | Rating fields omitted entirely when `rating_enabled` is false or `CAN_READ_RATINGS` denies. |
| S-069-17 | EXIF/GPS/location fields omitted from `details` when their config gates deny. |
| S-069-18 | `details` with 300 ids ⇒ 200; with 301 ⇒ 422. |
| S-069-19 | `details` requesting an id the caller cannot see ⇒ that id is absent from the response, not an error. |
| S-069-20 | A token that matches no album strategy at all (e.g. `rating:avg:>=3`) ⇒ album tier returns zero rows, never every album (parity with `addSearchCondition()`'s `1=0` guard). |
| S-069-21 | Sorting by `title` ⇒ natural sort via `title_base`/`title_index` (Feature 060), not PHP sorting. |
| S-069-22 | Tier-2 + tier-3 combined reconstruct v2's `PhotoResource` field-for-field, except NG9's documented gaps (NFR-069-06). |
| S-069-23 | Cache hit: identical repeat search returns the identical body without re-querying. |
| S-069-24 | Cache isolation: two different terms, or two different users, never share a cache entry. |
| S-069-25 | Frontend, flag off ⇒ v2 path renders exactly as today, pagination intact. |
| S-069-26 | Frontend, flag on ⇒ virtualized grids render, no `UPagination`, window scroll drives the virtualizer. |
| S-069-27 | Frontend, flag on, truncated result ⇒ hint row visible above the photo grid. |
| S-069-28 | Lightbox open on a v3-sourced tile ⇒ tier-3 details fetched for that photo and its two neighbours only. |
| S-069-29 | Rapid consecutive searches ⇒ only the latest result renders (`requestToken` guard). |
| S-069-30 | Navigating from a browsed album to Search ⇒ no leftover browsing tiles appear (FR-069-20). |
| S-069-31 | `SpotlightSearch.vue` continues to work against v2 with thumbnails intact (NG2). |
| S-069-32 | Existing v2 search tests pass unmodified (NFR-069-07). |

## Test Strategy

- **Core:** Unit tests for the truncation boundary arithmetic. Migrations are deliberately not unit-tested in this project (owner direction); the rename is covered indirectly by every v2 search suite, which reads the renamed key.
- **Application:** Unit tests for the new query actions — dedup (S-069-08), `album_ids` collapse (S-069-11), cap/truncation (S-069-09/10), and the once-per-request gate resolution (S-069-16/17).
- **REST:** `tests/Feature_v2/Search/` gains `SearchV3PhotosTest`, `SearchV3AlbumsTest`, `SearchV3DetailsTest`, `SearchV3GatingTest` and `SearchV3ParityTest` (NFR-069-06), all extending `BaseApiWithDataTest`. Existing v2 search tests must pass **unmodified** as the regression guard.
- **CLI:** Not applicable — no CLI surface.
- **UI (JS):** `npm run check` type-checking over the new service/store/component code. Manual browser verification of S-069-26/27/28 where an environment is available; recorded honestly as unverified otherwise.
- **Docs/Contracts:** `resources/js/lychee.d.ts` regenerated so the new resources appear under `App.Http.Resources.V3`; `LangTest::testLanguageConsistency` green after the config rename.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-069-01 | `App\Http\Resources\V3\SearchPhotoResource` — tier-2 body. `PhotoRatioResource`'s fields **minus** `bucket_ids` (NG1), **plus** `album_ids: string[]` (FR-069-04) and `is_truncated: bool` (FR-069-02). | REST |
| DO-069-02 | `App\Http\Resources\V3\PhotoDetailResource` — **reused unchanged** as the `/Search/Photos/details` body. | REST |
| DO-069-03 | `App\Http\Resources\V3\AlbumDataResource` — **reused unchanged** as the `/Search/albums` body, built by the existing `BuildAlbumDataResource::do()`, which already accepts an arbitrary pre-filtered album query. | REST |
| DO-069-04 | `App\Http\Resources\V3\AlbumRightsResource` — **reused unchanged** as the `/Search/albums/rights` body, with `owner_id` omitted per FR-069-09. | REST |
| DO-069-05 | `App\DTO\Search\SearchToken` and `App\Actions\Search\SearchTokenParser` — reused unchanged (NG4). | core |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-069-01 | REST GET `/api/v3/Search/Photos` | Tier-2 photo body, whole-scope, capped. | Params: `terms` (required, base64), `album_id?`, `sorting_column?`, `sorting_order?`. Returns DO-069-01. |
| API-069-02 | REST GET `/api/v3/Search/Photos/details` | Tier-3 on-demand payload. | Params: API-069-01's, plus `photo_ids[]` (required, ≤300). Returns DO-069-02. |
| API-069-03 | REST GET `/api/v3/Search/albums` | Album half, whole-scope. | Params as API-069-01. Returns DO-069-03. |
| API-069-04 | REST GET `/api/v3/Search/albums/rights` | Per-album permission signals. | Params as API-069-01. Returns DO-069-04. |

All four are served by `App\Http\Controllers\Gallery\SearchListingController`, registered in `routes/api_v3.php` after the `/Albums/...` family. Query actions live under `App\Actions\Search\StructOfArrays\`.

### CLI Commands / Flags

None — this feature adds no CLI surface.

### Telemetry Events

None new. The four routes emit the existing managed-cache hit/miss events via `ManagedCacheService` when `cache_event_logging` is on, exactly as Features 064/067 already do.

### Fixtures & Sample Data

No new fixture files. The new REST tests build their data through the existing `BaseApiWithDataTest` fixtures.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-069-01 | v2 path (flag off) | Today's rendering, `UPagination` intact. |
| UI-069-02 | v3 path (flag on) | Virtualized album + photo grids, no pagination, window scroll. |
| UI-069-03 | Truncation hint | `is_truncated: true` ⇒ a hint row above the photo grid; absent entirely when false. |
| UI-069-04 | Empty result | Existing `UEmpty` "no results" state, reused unchanged. |
| UI-069-05 | Below minimum term length | Existing min-chars hint, reused unchanged. |

## Telemetry & Observability

No new events, fields or redaction rules. Managed-cache instrumentation is inherited from `ManagedCacheService`; cache keys embed a digest of the parsed token list rather than the raw term, so no user-entered search text is written to cache-event logs.

## Documentation Deliverables

- [roadmap.md](../../roadmap.md) — add Feature 069 to Active during implementation, move to Completed at the end.
- [knowledge-map.md](../../knowledge-map.md) — new "Search Struct-of-Arrays (Feature 069)" section, following the Feature 066/067/068 entries' shape.
- [open-questions.md](../../open-questions.md) — Q-069-01..10 already logged and resolved.
- [_current-session.md](../../../_current-session.md) — refresh (currently stale at 2026-08-28).
- [ADR-0010](../../../6-decisions/ADR-0010-v3-collection-bounding-strategies.md) — **new**, created by this feature: names the four bounding strategies for v3 SoA collections and the rule for choosing among them. ADR-0009 governs response *shape*; ADR-0010 governs response *size*. Neither is amended by this feature.

## Fixtures & Sample Data

None.

## Spec DSL

```yaml
domain_objects:
  - id: DO-069-01
    name: SearchPhotoResource
    new: true
    fields:
      - name: ids
        type: string[]
      - name: album_ids
        type: string[]
        constraints: "one accessible album id per photo; never null"
      - name: is_truncated
        type: bool
      - name: titles|types|ratios|owner_ids|is_highlighteds|is_validateds|is_videos|is_raws|is_live_photos|taken_ats|created_ats|taken_at_orig_tzs
        type: array
        constraints: "index-aligned to ids; mirrors PhotoRatioResource minus bucket_ids"
      - name: rating_avgs|rating_users|thumb_infos|tags
        type: array|Optional
        constraints: "gated once per request; Optional omits the key entirely"
  - id: DO-069-02
    name: PhotoDetailResource
    new: false
  - id: DO-069-03
    name: AlbumDataResource
    new: false
  - id: DO-069-04
    name: AlbumRightsResource
    new: false
routes:
  - id: API-069-01
    method: GET
    path: /api/v3/Search/Photos
    returns: DO-069-01
  - id: API-069-02
    method: GET
    path: /api/v3/Search/Photos/details
    returns: DO-069-02
  - id: API-069-03
    method: GET
    path: /api/v3/Search/albums
    returns: DO-069-03
  - id: API-069-04
    method: GET
    path: /api/v3/Search/albums/rights
    returns: DO-069-04
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-069-01
    description: v2 path, pagination intact
  - id: UI-069-02
    description: v3 virtualized path
  - id: UI-069-03
    description: truncation hint
  - id: UI-069-04
    description: empty result
  - id: UI-069-05
    description: below minimum term length
```

## Appendix — Resolved Decisions

Full option analysis for each lives in [open-questions.md](../../open-questions.md); summarised here so this spec stays self-contained.

- **Q-069-01 → Option A.** Dedicated `/api/v3/Search/*` family. A "search album" would be parameterised by request state, which nothing in `AlbumFactory`/`AlbumPolicy` assumes, and would push `terms` onto request classes three shipped features already depend on — landing the blast radius on Features 064/066 rather than on new code. It would also still need a separate album route, so it never achieves "zero new routes".
- **Q-069-02 → Option B.** Whole-scope unpaginated, **no bucket tier**. Chosen over the recommended bucket-windowed option; the unbounded-response risk is accepted and mitigated by Q-069-10's cap.
- **Q-069-03 → Option A.** Flat SoA album listing plus a separate `/rights` tier. `BuildAlbumDataResource::do()` already accepts an arbitrary pre-filtered `Builder<Album>`, so `AlbumSearch::queryAlbums()`'s query feeds it directly with no new projection code.
- **Q-069-04 → Option A.** Spotlight stays on v2 (NG2).
- **Q-069-05 → Option A.** `date:` semantics unchanged (NG3).
- **Q-069-06.** `terms` stays base64 — the grammar embeds `:`/`>=`/`"`/`#`/`*`, the client helper already exists, and no proxy can mangle it.
- **Q-069-07.** `album_ids[]` resolved by Feature 067's Q-067-11 mechanism verbatim: join `photo_album` → `base_albums` → `computed_access_permissions`, apply `AlbumQueryPolicy::appendAccessibilityConditions()`, `GROUP BY` + `MIN()` to the lowest accessible id, no model hydration, no per-photo re-check. Note this needs a **per-row** album id where `adaptPhotoTile(i, ratios, album_id)` takes one id per call — satisfied without changing that function, since the caller simply passes `album_ids[i]`.
- **Q-069-08.** The missing `distinct()` is fixed via the `whereIn('photos.id', …)` id-subquery pattern `ResolvesPhotoSource` already documents. **Documented divergence:** v3's photo count will be lower than v2's for any library containing multi-album photos.
- **Q-069-09.** One full-stack Feature 069, matching Features 066/067/068's cadence.
- **Q-069-10 → Option A.** `search_pagination_limit` → `search_result_limit`, repurposed as a hard cap with an `is_truncated` signal. The key's size was actively tuned down (1000 → 300) by migration `2025_03_01_154728_search_pagination_limit_reduction.php`, so orphaning it would discard a deliberate operational choice.
