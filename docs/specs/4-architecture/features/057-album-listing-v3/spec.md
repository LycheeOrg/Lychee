# Feature 057 – Album Listing v3

| Field | Value |
|-------|-------|
| Status | Completed |
| Last updated | 2026-08-22 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/057-album-listing-v3/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/057-album-listing-v3/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

A single new backend-only `GET /api/v3/Albums` endpoint that returns a lightweight, rights-curated flat listing of albums for three consumers that all currently either lack a shared data source or pay for far more than they need:

1. **Default mode** — the minimal shape (`id`, `title`, `_lft`, `_rgt`) needed to drive a "pick a target album" dropdown (e.g. the Move dialog). Today's closest v2 equivalent, `Actions\Album\ListAlbums` (`GET /api/v2/Album::getTargetListAlbums`), fully hydrates Eloquent models, builds a nested tree server-side (`Collection::toTree()`), computes breadcrumb path strings and truncates them, and resolves a thumbnail URL per album — all of which this feature deliberately does **not** do. This feature intentionally returns only the raw nested-set coordinates (`_lft`, `_rgt`) and leaves tree/breadcrumb construction to the client, per explicit instruction.
2. **`with_parent_id=true`** — adds `parent_id` per album, for the admin Fix Tree maintenance page (today served by `Admin\Maintenance\FullTree::check()`, `GET /Maintenance::fullTree`), which already uses the closest existing precedent for this feature's query shape (`Album::query()->join('base_albums', ...)->select([...])->toBase()->get()`, `app/Http/Controllers/Admin/Maintenance/FullTree.php:47`).
3. **`for_bulk_edit=true`** — adds the full column set the admin Bulk Album Edit listing page shows today (`Admin\BulkAlbumController::index()`, `GET /BulkAlbumEdit`), so a future (out-of-scope) frontend change can source that page's row data from this lighter, cacheable, unpaginated endpoint instead.

This is the first `/api/v3/...` collection endpoint (Feature 056 was a single-item binary passthrough) and is therefore the first to realize ADR-0009's Struct-of-Arrays (SoA) response-shape convention in practice. The endpoint is read-only, introduces no new database columns, and reuses `App\Policies\AlbumQueryPolicy` for rights curation and `App\Services\Cache\ManagedCacheService` (Feature 052/053) for caching. **Front-end integration (wiring any existing dropdown/page to this endpoint) is explicitly out of scope for this feature** — this spec governs the backend contract only.

**Amendment (Q-057-05, added while scoping Feature 058):** the default/base response also includes a resolved `cover_ids` array, so a move-target-picker consumer can render a thumbnail without any extra endpoint beyond the base mode. This costs zero extra joins/queries — `cover_id`, `auto_cover_id_max_privilege`, and `auto_cover_id_least_privilege` all already live on `albums`, and the resolution logic is the same pure priority rule `App\Relations\HasAlbumThumb::getCoverTypeForAlbum()`/`selectCoverIdForAlbum()` already implements (`app/Relations/HasAlbumThumb.php:71-109`): explicit `cover_id` first, else `auto_cover_id_max_privilege` for an admin/owner viewer, else `auto_cover_id_least_privilege`. This endpoint returns only the resolved photo **ID** — resolving it to actual thumbnail bytes is the caller's job, via the separate Feature 056 `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` endpoint.

## Goals

- Provide one cacheable, rights-curated, flat album listing endpoint under `/api/v3/...` that returns the complete result set in a single response (no pagination), so a client can reconstruct a valid tree from `_lft`/`_rgt` in one pass.
- Keep the default response the lightest possible shape (`id`, `title`, `_lft`, `_rgt`, `cover_id`), built via `toBase()` to avoid Eloquent hydration/casts/eager-loading overhead, mirroring `FullTree::check()`'s existing precedent.
- Expose a resolved cover-photo id per album (Q-057-05) so a thumbnail-needing consumer can pair this endpoint with the Feature 056 v3 Asset endpoint, without this endpoint itself resolving or serving any image bytes.
- Support two independent, admin-gated opt-in flags that extend the response without changing its cost for callers who don't need them: `with_parent_id` (adds `parent_id`) and `for_bulk_edit` (adds full `BulkAlbumResource`-parity fields).
- Curate every mode's result set by the requesting visitor's or user's actual access rights via `AlbumQueryPolicy::applyVisibilityFilter()` — including anonymous/guest visitors.
- Cache the query result via the existing `ManagedCacheService`, keyed per user and per flag combination, and correctly invalidate it by extending the existing Feature 053 listener rather than auditing every album-mutation call site again.
- Respond in Struct-of-Arrays (SoA) JSON shape per ADR-0009, as the first realized example of that convention.

## Non-Goals

- **No frontend work.** Wiring the Move dialog, the Fix Tree admin page, or the Bulk Album Edit admin page to consume this endpoint is explicitly out of scope for this feature. This spec defines the backend contract only.
- **No v2 changes.** `GET /api/v2/Album::getTargetListAlbums`, `GET /Maintenance::fullTree`, and `GET /BulkAlbumEdit` are not modified, deprecated, or removed. This endpoint coexists alongside them (same precedent as Feature 056, `routes/api_v3.php`'s header comment).
- **No tree/breadcrumb construction server-side.** Sorting into a hierarchy, computing indentation depth, and building breadcrumb path strings are explicitly left to the client, per instruction — the server only guarantees `_lft`/`_rgt` correctness and stable per-array index alignment.
- **No search/filter query parameter.** Because the endpoint always returns the complete curated set (never paginated), client-side filtering over the already-fetched data is sufficient; no `search` parameter is offered.
- **No pagination**, under any flag combination (Q-057-04).
- **No new database columns or migrations** — purely a read/query-shape feature over existing `albums`/`base_albums`/`access_permissions`/`users` columns.
- **No thumbnail resolution.** `cover_ids` (FR-057-09) exposes a photo **ID** only; resolving it to actual image bytes/a URL is the caller's responsibility via the separate Feature 056 v3 Asset endpoint. This endpoint never touches `SizeVariant`/`Watermarker`/file storage.
- **No new telemetry events.**
- **No reachability/password-unlock awareness** in the default mode (Q-057-01) — a password-protected-but-not-yet-unlocked album still appears if it is otherwise visible (public/shared/owned).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-057-01 | `GET /api/v3/Albums` returns every album visible to the requesting visitor/user per `AlbumQueryPolicy::applyVisibilityFilter()`, ordered by `albums._lft` ascending, as a Struct-of-Arrays JSON body containing at minimum `ids`, `titles`, `lft`, `rgt`, `cover_ids` (parallel arrays, index-aligned). | 200 with fully populated parallel arrays (empty arrays, not an error, when the visitor has zero visible albums). | Query params validated per FR-057-02/03. | N/A (no failure branch beyond validation/authorization). | None. | User instruction; precedent `FullTree::check()` (`app/Http/Controllers/Admin/Maintenance/FullTree.php:47`). |
| FR-057-09 | `cover_ids[i]` is resolved per the same priority rule as `HasAlbumThumb::getCoverTypeForAlbum()`: `albums.cover_id` if set, else `albums.auto_cover_id_max_privilege` when the requesting user is an admin or the album's owner, else `albums.auto_cover_id_least_privilege`; `null` when none of the three yields a value (no live legacy-fallback query, unlike `HasAlbumThumb::getResults()`). | `cover_ids` array present in every mode (not gated behind `with_parent_id`/`for_bulk_edit`). | N/A. | N/A. | None. | Q-057-05. |
| FR-057-02 | Optional boolean query param `with_parent_id` (default `false`). When `true`, response additionally includes `parent_ids` (index-aligned, `null` for a root album's entry — never omitted). Requires `may_administrate === true`. | 200 with `parent_ids` populated. | `sometimes\|boolean`. | Non-admin caller supplying `with_parent_id=true` → 403. | None. | Q-057-02; fixTree consumer (`FullTree::check()`). |
| FR-057-03 | Optional boolean query param `for_bulk_edit` (default `false`). When `true`, response additionally includes a nested SoA block with full `BulkAlbumResource` field parity (see DO-057-02). Requires `may_administrate === true`. | 200 with the bulk-edit block populated. | `sometimes\|boolean`. | Non-admin caller supplying `for_bulk_edit=true` → 403. | None. | Q-057-02/Q-057-03; bulk-edit consumer (`BulkAlbumController::index()`/`BulkAlbumResource`). |
| FR-057-04 | `with_parent_id=true` and `for_bulk_edit=true` may be combined in one request; both additive blocks are present simultaneously, admin-gated as one combined check. | 200 with both `parent_ids` and the bulk-edit block present. | Same as FR-057-02/03. | Non-admin with either flag `true` → 403 (same as above). | None. | Q-057-02. |
| FR-057-05 | The underlying query result is cached via `App\Services\Cache\ManagedCacheService::rememberIf()`, gated by the existing `managed_cache_enabled` AND `managed_cache_albums_enabled` config toggles (Feature 052/053, no new toggle), with TTL `managed_cache_ttl`. Cache key varies by user identity and by the exact flag combination requested. | Second identical request within TTL is served from cache (no repeat album/base_albums/access_permissions query). | N/A. | Either toggle `false` → endpoint behaves identically but is never cached (`rememberIf()`'s existing semantics). | None. | User instruction ("ideally cached with the cache manager"); Feature 052/053 precedent. |
| FR-057-06 | `App\Listeners\ManagedCacheAlbumListingInvalidator` (Feature 053) is extended so every event it already handles also evicts a new shared `album-listing-v3` cache tag (coarse: the entire v3 listing cache, across all users and flag combinations, is evicted together on any album/permission mutation the listener already reacts to). | An album edit/move/delete/permission change invalidates all cached v3 listing responses; the next request recomputes. | N/A. | N/A. | None. | Reuses Feature 053 infrastructure; avoids re-auditing every mutation call site. |
| FR-057-07 | Response body is JSON Struct-of-Arrays per ADR-0009 — no Array-of-Structs envelope. | Body matches DO-057-01's shape exactly. | N/A. | N/A. | None. | ADR-0009. |
| FR-057-08 | Route registered in `routes/api_v3.php` as `GET /api/v3/Albums`; no existing v2 route is modified, deprecated, or removed. | Route resolves independently of `routes/api_v2.php`. | N/A. | N/A. | None. | Feature 056 precedent (`routes/api_v3.php` header comment). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-057-01 | The base query must use `Illuminate\Database\Eloquent\Builder::toBase()` (raw `stdClass` rows, no Eloquent hydration/casts/model events/eager-loading) for every mode. | "Lightest possible way" instruction. | Code review confirms `toBase()` is called before `->get()`; no Eloquent model methods invoked on result rows. | `FullTree::check()` precedent. | User instruction. |
| NFR-057-02 | The endpoint never paginates, under any flag combination — it always returns the complete curated set in one response. | Client must have the whole set to build a valid tree from `_lft`/`_rgt`; an arbitrary page boundary can split a subtree. | Feature test asserts response array length equals the full curated count, with no `page`/`per_page`/`links`/`meta` pagination envelope. | Q-057-04. | Q-057-04 resolution. |
| NFR-057-03 | The default mode must function correctly for an unauthenticated visitor (`Auth::user() === null`), matching `AlbumQueryPolicy`'s existing null-user support. | User explicitly said "visitor/user." | Feature test covers an unauthenticated request against a public album. | `AlbumQueryPolicy::applyVisibilityFilter()`'s existing `?User $user` support. | Q-057-01 resolution; user instruction. |
| NFR-057-04 | Cache key must be a pure function of (user identity, `with_parent_id`, `for_bulk_edit`) — no two distinct combinations may collide on the same key, and no two different users may share a key. | Cache correctness; avoids serving one user's/mode's data to another. | Unit test asserts key uniqueness across a matrix of (guest, user A, user B) × (00, 10, 01, 11) flag combinations, mirroring NFR-053-08's key-uniqueness test pattern. | `CacheKeyProvider` (Feature 053 precedent). | Feature 053 (`CacheKeyProvider`, NFR-053-08). |
| NFR-057-05 | No new database columns or migrations. | Feature is read-only over existing schema. | `git diff -- database/migrations/` empty for this feature. | N/A. | Non-Goals. |
| NFR-057-06 | `make phpstan` (level 6 minimum) and `vendor/bin/php-cs-fixer fix` clean on every new/changed file. | Repo quality gate. | `make phpstan`; `php-cs-fixer fix --dry-run`. | AGENTS.md quality gate. | AGENTS.md. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-057-01 | Anonymous visitor requests default mode; response includes only public/shared-without-account albums, base 4 fields, no `parent_ids`/bulk-edit block. |
| S-057-02 | Authenticated non-admin user requests default mode; response includes owned + shared-with-them + public albums, excludes private others' albums. |
| S-057-03 | Non-admin user requests `with_parent_id=true` → 403. |
| S-057-04 | Non-admin user requests `for_bulk_edit=true` → 403. |
| S-057-05 | Admin requests `with_parent_id=true`; `parent_ids` present, correct per-album value, `null` for root albums (not omitted). |
| S-057-06 | Admin requests `for_bulk_edit=true`; bulk-edit block present with values matching the equivalent `BulkAlbumResource` fields for the same albums (owner name, license, sorting, grants, etc.). |
| S-057-07 | Admin requests both flags together; both `parent_ids` and the bulk-edit block are present simultaneously. |
| S-057-08 | Install with zero albums visible to the requester → 200 with all arrays empty (not 404/500). |
| S-057-09 | Two identical requests within `managed_cache_ttl`; the second is served from `ManagedCacheService` with no repeat album/base_albums/access_permissions query. |
| S-057-10 | An album is edited/moved/deleted, or its access permissions change, between two requests; the second request reflects the change (cache invalidated via the extended Feature 053 listener), not stale data. |
| S-057-11 | `managed_cache_enabled=false` or `managed_cache_albums_enabled=false`; endpoint still returns correct data, uncached (query re-executed every call). |
| S-057-12 | A root album (`parent_id IS NULL`) is present when `with_parent_id=true`; its `parent_ids` entry is `null`, and every parallel array stays index-aligned across all albums including this one. |
| S-057-13 | A password-protected album that the visitor has *not* unlocked, but which is otherwise public, still appears in the default listing (regression guard for the Q-057-01 "visibility only" resolution — explicitly not reachability-filtered). |
| S-057-14 | Two different users (or a guest and a user) each request default mode; each gets their own correctly-curated result — cache entries never leak across identities (NFR-057-04). |
| S-057-15 | An album has an explicit `cover_id` set → `cover_ids[i]` equals that value regardless of viewer privilege (FR-057-09). |
| S-057-16 | An album has no explicit cover but the requester is its owner (or an admin) → `cover_ids[i]` equals `auto_cover_id_max_privilege`. |
| S-057-17 | An album has no explicit cover and the requester is neither owner nor admin → `cover_ids[i]` equals `auto_cover_id_least_privilege`. |
| S-057-18 | An album has none of the three cover columns set → `cover_ids[i]` is `null` (no live fallback query). |

## Test Strategy

- **Core:** N/A (no core-library changes).
- **Application:** Unit tests for the new `AlbumQueryPolicy::joinBaseAlbumBulkEditFields()` join helper and the new `CacheKeyProvider` key/tag methods (mirrors existing `CacheKeyProvider` unit test coverage from Feature 053).
- **REST:** New `tests/Feature_v3/Album/AlbumListV3Test.php` extending `Tests\Feature_v3\Base\BaseApiWithDataTest`, covering S-057-01..14. Reuses the existing v2/v3 fixture graph (no new fixtures needed).
- **CLI:** N/A.
- **UI (JS/Selenium):** N/A — front-end is explicitly out of scope.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` gains an entry for `GET /api/v3/Albums`; `docs/specs/4-architecture/knowledge-map.md` updated to reference the new controller/resources.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-057-01 | `App\Http\Resources\V3\AlbumListResource` (Spatie `Data`, `#[TypeScript]`) — `ids: string[]`, `titles: string[]`, `lft: int[]`, `rgt: int[]`, `cover_ids: (string\|null)[]`, `parent_ids: (string\|null)[]\|null` (`null` when `with_parent_id=false`), `bulk_edit: AlbumListBulkEditFieldsResource\|null` (`null` when `for_bulk_edit=false`). | REST |
| DO-057-02 | `App\Http\Resources\V3\AlbumListBulkEditFieldsResource` (Spatie `Data`, `#[TypeScript]`) — full parity with `BulkAlbumResource`'s fields, SoA-shaped and index-aligned to DO-057-01's arrays: `owner_ids: int[]`, `owner_names: string[]`, `descriptions: (string\|null)[]`, `copyrights: (string\|null)[]`, `licenses: string[]`, `photo_layouts: (string\|null)[]`, `photo_sorting_cols: (string\|null)[]`, `photo_sorting_orders: (string\|null)[]`, `album_sorting_cols: (string\|null)[]`, `album_sorting_orders: (string\|null)[]`, `album_thumb_aspect_ratios: (string\|null)[]`, `album_timelines: (string\|null)[]`, `photo_timelines: (string\|null)[]`, `is_nsfws: bool[]`, `is_publics: bool[]`, `is_link_requireds: bool[]`, `grants_full_photo_accesses: bool[]`, `grants_downloads: bool[]`, `grants_uploads: bool[]`, `created_ats: string[]` (ISO 8601). | REST |
| DO-057-03 | `App\Http\Requests\Gallery\AlbumListV3Request` — validates `with_parent_id`/`for_bulk_edit` (`sometimes\|boolean`, default `false`), `authorize()` returns `true` unless either resolved flag is `true`, in which case it returns `Auth::user()?->may_administrate === true`. | REST |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-057-01 | REST `GET /api/v3/Albums` | Returns DO-057-01. Query params: `with_parent_id?: boolean = false`, `for_bulk_edit?: boolean = false`. | `App\Http\Controllers\Gallery\AlbumListController::index()`; registered in `routes/api_v3.php`. |

### CLI Commands / Flags

None.

### Telemetry Events

None.

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-057-01 | (reused) `tests/Feature_v3/Base/BaseApiWithDataTest.php`'s inherited v2 fixture graph | Provides the users/albums/permissions graph needed for S-057-01..14; no new fixture file required. |

### UI States

None — front-end integration is out of scope (see Non-Goals).

## Telemetry & Observability

No new telemetry events. Standard Laravel/Lychee exception logging applies to validation/authorization failures (403 via the FormRequest's `authorize()`).

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — add `GET /api/v3/Albums` alongside Feature 056's entry.
- `docs/specs/4-architecture/knowledge-map.md` — reference `AlbumListController`, `AlbumListResource`/`AlbumListBulkEditFieldsResource`, and the new `AlbumQueryPolicy`/`CacheKeyProvider` additions.
- `docs/specs/4-architecture/roadmap.md` — Feature 057 entry (Active Features while in progress; moved to Completed Features once all tasks are `[x]`).

## Fixtures & Sample Data

No new fixture files — reuses the existing `Tests\Feature_v3\Base\BaseApiWithDataTest` fixture graph (see FX-057-01).

## Spec DSL

```
domain_objects:
  - id: DO-057-01
    name: AlbumListResource
    fields:
      - name: ids
        type: string[]
      - name: titles
        type: string[]
      - name: lft
        type: int[]
      - name: rgt
        type: int[]
      - name: cover_ids
        type: (string|null)[]
      - name: parent_ids
        type: (string|null)[]|null
      - name: bulk_edit
        type: AlbumListBulkEditFieldsResource|null
  - id: DO-057-02
    name: AlbumListBulkEditFieldsResource
    fields:
      - name: owner_ids
        type: int[]
      - name: owner_names
        type: string[]
      - name: descriptions
        type: (string|null)[]
      - name: copyrights
        type: (string|null)[]
      - name: licenses
        type: string[]
      - name: photo_layouts
        type: (string|null)[]
      - name: photo_sorting_cols
        type: (string|null)[]
      - name: photo_sorting_orders
        type: (string|null)[]
      - name: album_sorting_cols
        type: (string|null)[]
      - name: album_sorting_orders
        type: (string|null)[]
      - name: album_thumb_aspect_ratios
        type: (string|null)[]
      - name: album_timelines
        type: (string|null)[]
      - name: photo_timelines
        type: (string|null)[]
      - name: is_nsfws
        type: bool[]
      - name: is_publics
        type: bool[]
      - name: is_link_requireds
        type: bool[]
      - name: grants_full_photo_accesses
        type: bool[]
      - name: grants_downloads
        type: bool[]
      - name: grants_uploads
        type: bool[]
      - name: created_ats
        type: string[]
routes:
  - id: API-057-01
    method: GET
    path: /api/v3/Albums
fixtures:
  - id: FX-057-01
    path: tests/Feature_v3/Base/BaseApiWithDataTest.php
ui_states: []
```

## Appendix

### Decision Cards (Q-057-01..04)

#### Q-057-01 — Rights-curation filter for the default listing mode

**Resolved: Option A — `AlbumQueryPolicy::applyVisibilityFilter()`.** Matches Feature 053's existing root-listing cache-key shape (keyed only by user identity, no session-scoped "unlocked album" digest needed). A password-protected-but-not-unlocked album may still appear in the default listing — accepted as correct behaviour (S-057-13), not a security gap, since visibility (not reachability/unlock state) is the intended curation semantics here. Rejected alternative: `applyReachabilityFilter()` (matches today's v2 `ListAlbums`/`getTargetListAlbums` exactly, but requires `AlbumPolicy::getUnlockedAlbumIDs()` and a session-scoped digest baked into the cache key, the same complexity Feature 053 had to solve separately for the Tag detail page — heavier, rejected per the "lightest possible way" instruction).

#### Q-057-02 — Query-parameter shape for the fixTree/bulk-edit variants

**Resolved: Option A — two independent booleans**, `with_parent_id` and `for_bulk_edit`, both default `false`, both admin-gated, combinable in one request (FR-057-04). Rejected alternative: a single `mode` enum (`default|fix_tree|bulk_edit`) — simpler surface, but the user described the two needs (parent_id for fixTree; joins for bulk edit) as clearly separate concerns, not mutually exclusive modes.

#### Q-057-03 — Field set for `for_bulk_edit=true`

**Resolved: Option B — full parity with today's `BulkAlbumResource`** (all ~19 non-base fields; DO-057-02). This lets a later, separate frontend feature source the Bulk Album Edit admin page's row data from this endpoint as a drop-in, without a second near-duplicate endpoint. Combined with Q-057-04's "never paginate" resolution, this means the bulk-edit mode trades the user's stated "a few joins" framing for completeness — accepted explicitly by the user when choosing this option over the lighter minimal-subset alternative.

#### Q-057-04 — Pagination

**Resolved: Option A — never paginate**, under any flag combination; the endpoint always returns the complete curated set in one response. Required by the "tree structure is built client-side from `_lft`/`_rgt`" design — a paginated subset cannot be turned into a valid tree. Matches `FullTree::check()`'s existing unpaginated `toBase()->get()` precedent exactly.

#### Q-057-05 — Move-picker thumbnail needed, discovered while scoping Feature 058

**Resolved:** add `cover_ids` (FR-057-09) to the base/default response. While scoping Feature 058 (migrating the v2 move-target picker to consume this endpoint), it became clear the picker's current thumbnail display had no field to migrate to — the original minimal 4-field shape had no cover concept at all. Rather than let Feature 058 bolt on a workaround, the gap was fixed at its source: `cover_id`/`auto_cover_id_max_privilege`/`auto_cover_id_least_privilege` all already live on `albums` (confirmed via migration audit), so exposing a resolved cover id costs zero extra joins/queries, reusing `HasAlbumThumb`'s existing priority rule as a pure function (no relation load). The endpoint still returns only an ID, not bytes/a URL — Feature 058's consumers pair it with the separate Feature 056 v3 Asset endpoint.
