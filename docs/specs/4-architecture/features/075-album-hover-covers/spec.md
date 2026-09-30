# Feature 075 – Album Hover Side Covers

| Field | Value |
|-------|-------|
| Status | Implemented 2026-09-30 — manual browser verification pending (T-075-22) |
| Last updated | 2026-09-30 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #075 |
| Decisions | [ADR-075-01](../../../6-decisions/ADR-075-01-side-covers-as-precomputed-columns.md) (Q-075-01, Q-075-02) |
| Source | [GitHub discussion #4742](https://github.com/LycheeOrg/Lychee/discussions/4742) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Album tiles fan out into three stacked images on hover (v8 `AlbumThumbVirtual.vue`, `AlbumThumb.vue`, v7 `AlbumThumb.vue`), but every layer shows the album's single cover. Lychee v4 showed two *different* photos behind the cover. This feature stores two more photo ids per album and privilege level next to the existing precomputed cover (ADR-0003), and two more per viewer row in the tag/person/smart cover cache (`album_user_thumbs`, ADR-0010), exposes them through the v3 struct-of-arrays listings behind a gallery setting, and feeds them to the two back layers of the v8 struct-of-arrays tile. Per ADR-075-01 both stores grow by fixed columns, not by rows.

Affected layers: `albums` and `album_user_thumbs` schema, `RecomputeAlbumStatsJob` and `RecomputeAlbumUserThumbsJob`, `CachesAlbumUserThumb`, the v3 listing resources and their builders, `GetPhotoAssetRequest`'s cover exception, `PurgeAlbumUserThumbs`, a config migration, the generated TypeScript types and the v8 struct-of-arrays tile.

Per `[[project_v8_migration_scope]]` and Q-075-05 this is v8 struct-of-arrays only. v7, the v8 flag-off tile and every v2 resource are untouched.

## Goals
- G1: On hover, a regular, tag, person or smart album tile shows two distinct photos behind its cover (Q-075-02 → B).
- G2: A global gallery setting, default on, turns the effect on and off with immediate effect in both directions (Q-075-03 → B).
- G3: No additional read-path query per album row. The side ids ride on the same row read as the cover (Q-075-01 → A, ADR-075-01).
- G4: The side photos are ranks 2 and 3 of the same ordered query that picks the cover (Q-075-04 → A).
- G5: A least-privilege side cover never exposes a photo the viewer cannot see, by the same dual-privilege rule the cover already follows.

## Non-Goals
- NG1: v7 and the v8 flag-off tile (`AlbumThumb.vue`, fed by v2 `ThumbAlbumResource`). No v2 resource and no `Album::$with` entry changes (Q-075-05 → A).
- NG2: List-view rows (`AlbumListItemVirtual.vue`) and Flow tiles. They have no hover fan.
- NG3: More than two side covers. The tile has exactly two back layers.
- NG4: Smart albums with `SA_random_thumbs` on. Their cover is never cached, so they have no side covers.
- NG5: Computing side covers live inside a listing request. Listings only read what the job or the cache already stored.
- NG6: Changing the fan-out animation, its CSS classes or the tile layout.
- NG7: Any new frontend flag. When the setting is off the backend sends `null` sides and the tile falls back to the cover.
- NG8: A frontend unit-test runner. None exists (`npm run check` is `vue-tsc` only).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-075-01 | `albums` gains four nullable `char(24)` columns: `auto_cover_id_max_privilege_2`, `auto_cover_id_max_privilege_3`, `auto_cover_id_least_privilege_2`, `auto_cover_id_least_privilege_3`, each a foreign key to `photos.id` with ON DELETE SET NULL, mirroring the existing pair. `Album` declares them as nullable string attributes. | Migration adds columns and FKs. `down()` drops FKs then columns. | Column names are fixed in code, not user input. | A deleted photo nulls the column, no dangling id (S-075-14). | None. | Q-075-01 → A, ADR-075-01, ADR-0003 |
| FR-075-02 | `RecomputeAlbumStatsJob` takes the first three rows of its existing ordered cover query per privilege level (`is_highlighted DESC`, then the album's effective photo sorting, the same NSFW context) and stores them as ranks 1, 2, 3. Fewer qualifying photos leave the higher ranks `NULL`. | One query per privilege level with `limit(3)` replaces `first()` (S-075-01). | None. | Album with one photo: ranks 2 and 3 `NULL` (S-075-02). Empty album: all `NULL`. | Existing job debug log line also prints the side ids. | Q-075-04 → A |
| FR-075-03 | Existing albums receive side covers when they are next recomputed by the regular event-driven job. `php artisan lychee:recompute-album-stats` (bulk mode) fills every album at once. `FulfillPreCompute` is unchanged: it keeps selecting albums whose primary cover columns are `NULL`. | Bulk command fills ranks 2 and 3 for every album with enough photos. | None. | Until recomputed, an upgraded album has `NULL` sides and its tile shows the cover in all three layers (FR-075-12). | None. | ADR-0003 §1 |
| FR-075-04 | New config `album_hover_side_covers_enabled` (category `Gallery`, `type_range` bool, default `1`, `level` 0, description "Show two different photos behind the album cover on hover"). Listed in `SettingsController::V8_CONFIGS` (hidden while `features.v8` is off) and in `SettingsController::ALBUM_LISTING_COARSE_FLUSH_CONFIGS` so a change flushes the cached listings (S-075-18). | Setting on: side arrays carry ids. Setting off: every side entry is `null`, `cover_ids` unchanged (S-075-05). | Standard bool config validation. | None. | None. | Q-075-03 → B, Feature 053 |
| FR-075-05 | Side resolution for a regular album row is a pure helper `SideCoverIds::forAlbumRow(row, primary, user, unlocked_album_ids, enabled): array{?string, ?string}` (the lock test is exposed as `SideCoverIds::isHiddenByLock(row, unlocked_album_ids)` for the cache path of FR-075-09). Rule: (1) `enabled === false` or `primary === null` → `[null, null]`. (2) Pick the max-privilege triple when `user->may_administrate` or `user->id === row->owner_id`, else the least-privilege triple (same test as `AlbumListController::rawCoverId()`). (3) Drop `NULL`s and any id equal to `primary`, keep order, take the first two, pad with `null`. (4) If the album is password-locked and not unlocked, return `[null, null]` unless `show_cover_of_locked_albums` is on. `show_selected_cover_on_locked_albums` never reveals sides, since sides are always auto-picked. | Owner sees max-privilege sides, guest least-privilege (S-075-04). Manual `cover_id` equal to auto rank 1 → sides are ranks 2, 3. Manual cover equal to rank 2 → sides are ranks 1, 3 (S-075-06). | None. | Locked album: sides `null` (S-075-07). | None. | Q-075-04 → A, Q-075-05 → A |
| FR-075-06 | `AlbumDataResource` gains `cover_ids_2` and `cover_ids_3` (`(string\|null)[]`, one entry per album, same index as `cover_ids`). Both producers fill them via FR-075-05: `BuildAlbumDataResource` (album children, tag/person children, search) and `AlbumRootController::toAlbumDataResource()` (root). The row selects add the four columns of FR-075-01. | Arrays present on `GET /Albums/{id}`, `/Albums/root`, `/Search/albums` (S-075-16). | None. | None. | None. | ADR-0009 |
| FR-075-07 | `AlbumCategoryResource` gains the same `cover_ids_2` / `cover_ids_3`. Pinned (`AlbumPinnedController`) uses FR-075-05. Smart, tag and person listings use FR-075-09. | Arrays present on `/Albums/pinned`, `/Albums/smart`, `/Albums/tags`, `/Albums/persons`. | None. | None. | None. | Q-075-02 → B |
| FR-075-08 | `album_user_thumbs` gains nullable `char(24)` `photo_id_2` and `photo_id_3`, each a foreign key to `photos.id` with ON DELETE SET NULL (the row survives, `photo_id` keeps its existing cascade). `Thumb::createManyFromQueryable(queryable, sorting, limit = 3): list<Thumb>` returns up to three `Thumb`s from one ordered query. `CachesAlbumUserThumb::getCachedOrLiveThumb()` seeds all three columns from that list (`CachesAlbumUserThumb::cacheColumns()`) and returns the first. `RecomputeAlbumUserThumbsJob::refreshForViewer()` rewrites all three, and still deletes the row when no photo qualifies. | Cache row holds three ids after the first view (S-075-10, S-075-15). | None. | Fewer than three photos: higher columns `NULL`. | None. | Q-075-02 → B, ADR-075-01 |
| FR-075-09 | `AlbumSmartController`, `AlbumTagController` and `AlbumPersonController` read the viewer's cache rows for all listed ids in **one** query (`AlbumUserThumb::rowsForViewer(ids)`: `toBase()`, `whereIn('album_id')`, `where('user_id', Auth::id())`, three photo columns, keyed by album id). Primary: smart → cached `photo_id` (else the live `get_thumb()` as today); tag → `cover_id` when set, else cached `photo_id`; person → cached `photo_id`. Sides: `SideCoverIds::fromCacheRow(row, primary, enabled)` = the cache triple minus `NULL`s and minus `primary`, first two; `[null, null]` when the setting is off or there is no row. Tag rows then pass the existing locked-cover gate; a gated (`null`) primary forces `[null, null]` sides. | Tag without manual cover shows the cached cover and sides; with manual cover, sides exclude it (S-075-11). Person shows cached cover and sides (S-075-12). | None. | No cache row yet (never viewed): primary as today, sides `null`. | None. | Q-075-02 → B, NG5 |
| FR-075-10 | `GetPhotoAssetRequest::isPhotoOfAlbum()` accepts, for a regular `Album`, a photo equal to any of the seven cover columns (`cover_id`, the two existing auto covers, the four of FR-075-01). `isComputedAlbumThumb()` matches `photo_id`, `photo_id_2` or `photo_id_3` of the viewer's row. Everything else about the request is unchanged. | A side photo living in a descendant album is served for the parent's asset URL (S-075-08). A rank-2 cached smart cover is served (S-075-10). | None. | A photo that is neither a cover column nor a member stays 403 (S-075-09). | None. | ADR-0010, FR-056-08 |
| FR-075-11 | `PurgeAlbumUserThumbs::forBaseAlbums()` deletes a row when `photo_id`, `photo_id_2` or `photo_id_3` points at a photo of the revoked albums. `forUsers()` is unchanged. | Revoking public access to the source album drops a viewer's row whose side cover came from it (S-075-13). | None. | None. | None. | ADR-0010 |
| FR-075-12 | Frontend: `AdaptedAlbumTile` gains `cover_id_2` and `cover_id_3` (`string \| null`); `adaptAlbumChildTile.ts`, `adaptCategoryTile.ts` and the root store's tile assembly fill them from the arrays. In `AlbumThumbVirtual.vue` the first back layer renders `cover_id_2 ?? cover_id`, the second `cover_id_3 ?? cover_id`, the front layer `cover_id`. Badges, overlay and the no-cover branch key on `cover_id` as today. | Hovering shows three different photos (S-075-17). | None. | `null` sides: all three layers show the cover, identical to today. | None. | Q-075-05 → A |
| FR-075-13 | `php artisan typescript:transform` is rerun so `resources/js/lychee.d.ts` carries the new arrays. | Type-check green. | `npm run check`. | None. | None. | Coding conventions |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-075-01 | No additional query per album row in any v3 listing. Regular-album listings stay at their current query count. Smart, tag and person listings add at most one query per request (the cache read of FR-075-09; smart already has it). | Owner's performance north star (700k photos, 7k albums). | Assert query count in `AlbumChildrenSideCoversV3Test` / `AlbumCategoryV3Test` with `DB::enableQueryLog()`. | Feature 057/061/062 SoA path. | ADR-0003, ADR-0009 |
| NFR-075-02 | `RecomputeAlbumStatsJob` runs no additional query for side covers. | Write-path cost stays flat. | Query-count assertion in `RecomputeAlbumStatsJobTest`. | FR-075-02. | Q-075-04 → A |
| NFR-075-03 | A least-privilege side cover is chosen by the same `PhotoQueryPolicy::applySearchabilityFilter()` as the least-privilege cover, and a cache side cover by the same viewer-filtered `photos()` relation as the cached cover. | No private photo leak. | `AlbumCoverSecurityTest` extended with side columns (S-075-03). | FR-075-02, FR-075-08. | ADR-0003 §5 |
| NFR-075-04 | Asset authorization keeps zero per-request permission re-checks; the write-side purge invariant of ADR-0010 is extended to the two side columns. | Hottest endpoint in the application. | Code review, S-075-13. | FR-075-10, FR-075-11. | ADR-0010 |
| NFR-075-05 | No change to v2 resources, `Album::$with`, `HasAlbumThumb` or v7 code. | Q-075-05 → A. | Empty diff on those paths in the drift gate. | None. | `[[project_v8_migration_scope]]` |
| NFR-075-06 | `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check` and every touched test class green. | Quality gate. | Commands in tasks.md. | None. | AGENTS.md |

## UI / Interaction Mock-ups

Album tile at rest and on hover, v8 struct-of-arrays grid. Only the photo shown by the two back layers changes; the fan geometry is untouched.

```
 At rest (all modes)              Hover, setting ON               Hover, setting OFF / sides NULL
+-------------------+            +-------------------+            +-------------------+
|                   |            |  .-----------.    |            |  .-----------.    |
|                   |            | / photo #3    \   |            | / cover       \   |
|      cover        |            ||  .-----------.|  |            ||  .-----------.|  |
|      (rank 1)     |            || / photo #2   \|  |            || / cover      \|  |
|                   |            |||   cover      ||  |            |||   cover      ||  |
|                   |            |||   (rank 1)   ||  |            |||   (rank 1)   ||  |
|                   |            ||'--------------'|  |            ||'--------------'|  |
| Title      12 ▸   |            | Title      12 ▸   |            | Title      12 ▸   |
+-------------------+            +-------------------+            +-------------------+
 front layer only visible         back layers rotate -2° / +6°     identical to today
```

Settings → Gallery (v8 only):

```
 Show two different photos behind the album cover on hover     [ ON  ⇄ off ]
   Applies to album, tag, person and smart album tiles. Side photos follow the
   album's sort order. Turning this off sends no extra thumbnails.
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-075-01 | Album subtree with ≥3 photos: job stores ranks 1–3 per privilege level, in the album's effective order with highlighted first. |
| S-075-02 | Album with one photo: rank 1 set, ranks 2–3 `NULL`. Empty album: all `NULL`. |
| S-075-03 | Subtree with public and private photos: least-privilege ranks contain only public photos; max-privilege ranks may contain private ones. |
| S-075-04 | Same album listed for owner/admin and for a guest: owner gets max-privilege sides, guest least-privilege sides. |
| S-075-05 | Setting off: `cover_ids_2` / `cover_ids_3` are all `null` on every listing, `cover_ids` unchanged. |
| S-075-06 | Manual `cover_id` equal to auto rank 1 → sides ranks 2, 3. Manual cover equal to rank 2 → sides ranks 1, 3. Manual cover not among the auto ranks → sides ranks 1, 2. |
| S-075-07 | Password-locked album not unlocked: sides `null`; with `show_cover_of_locked_albums` on: sides shown; with only `show_selected_cover_on_locked_albums` on and a manual cover: primary shown, sides `null`. |
| S-075-08 | `GET /api/v3/Asset/{albumId}/{photoId}/small` for a side photo stored in a descendant album returns 200 for a viewer who can access the album. |
| S-075-09 | Same endpoint for a photo that is neither a cover column nor a member of the album returns 403. |
| S-075-10 | Smart album first viewed by a user: cache row has three ids; `/Albums/smart` returns them; the asset endpoint serves the rank-2 id for that smart album. |
| S-075-11 | Tag album without manual cover: `/Albums/tags` returns the cached primary and sides once a row exists, `null` sides before. With a manual cover: primary is the manual cover, sides exclude it. |
| S-075-12 | Person album with a cache row: `/Albums/persons` returns cached primary and sides. |
| S-075-13 | Revoking public access to an album whose photo is only a side cover in a viewer's cache row deletes that row. |
| S-075-14 | Deleting a photo used as a side cover nulls the `albums` side column and the cache side column; the cache row survives with its primary. |
| S-075-15 | `RecomputeAlbumUserThumbsJob` rewrites all three ids for every viewer row; deletes the row when no photo qualifies. |
| S-075-16 | `/Albums/root`, `/Albums/{id}`, `/Albums/pinned` and `/Search/albums` all carry the two arrays with one entry per album. |
| S-075-17 | Frontend tile: back layers request the side ids through `ThumbAssetService`; `null` sides fall back to the cover (manual browser check). |
| S-075-18 | Changing the setting dispatches the album-listing cache flush, so the next listing reflects the new value. |

## Test Strategy
- **Models / Jobs:** `tests/Precomputing/CoverSelection/RecomputeAlbumStatsJobTest` (S-075-01, 02, NFR-075-02), `Console/AlbumCoverSecurityTest` (S-075-03), new `tests/Unit/Actions/SideCoverIdsTest` for the pure helper (S-075-05..07 rules, both entry points), `tests/Unit/Jobs/RecomputeAlbumUserThumbsJobTest` (S-075-15), `tests/Unit/Models/TagAlbumTest` / `PersonAlbumTest` (three-column seeding, S-075-10 seeding half).
- **REST API v3:** new `tests/Feature_v3/Album/AlbumSideCoversV3Test` (S-075-04..07, S-075-16, NFR-075-01 for children/root/pinned), `AlbumCategoryV3Test` extended (S-075-10..12), `Photo/PhotoAssetV3Test` extended (S-075-08, 09, 10 asset half), new `tests/Feature_v3/Sharing/PurgeSideCoversV3Test` (S-075-13, S-075-14; Feature_v3 base inherits the v2 helpers, no new Feature_v2 tests).
- **Settings:** `tests/Feature_v3/Album/AlbumSideCoversV3Test` also covers S-075-18 via `Event::fake()` on `AlbumListingCacheFlushRequested`.
- **Frontend (v8):** `npm run check` after `php artisan typescript:transform`. S-075-17 is a manual browser check (NG8).
- **Docs/Contracts:** regenerated `resources/js/lychee.d.ts`; knowledge-map entries for the helper and the widened stores.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-075-01 | `albums.auto_cover_id_{max,least}_privilege_{2,3}` nullable `char(24)`, FK `photos.id` ON DELETE SET NULL. | persistence, `Album` model |
| DO-075-02 | `album_user_thumbs.photo_id_2`, `photo_id_3` nullable `char(24)`, FK `photos.id` ON DELETE SET NULL. | persistence, `AlbumUserThumb` model |
| DO-075-03 | `App\Actions\Album\StructOfArrays\SideCoverIds` — `forAlbumRow()`, `fromCacheRow()` (both `array{0:?string,1:?string}`) and `isHiddenByLock()` pure static helpers. | actions |
| DO-075-04 | `Thumb::createManyFromQueryable(Relation\|Builder, SortingCriterion, int $limit = 3): list<Thumb>`; `CachesAlbumUserThumb::cacheColumns(list<Thumb>)`; `AlbumUserThumb::rowsForViewer(array $album_ids)`. | models/extensions |
| DO-075-05 | Config `album_hover_side_covers_enabled` (bool, default `1`, Gallery, level 0). | config |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-075-01 | REST GET `/api/v3/Albums/{album_id}`, `/api/v3/Albums/root`, `/api/v3/Search/albums` | `AlbumDataResource` + `cover_ids_2`, `cover_ids_3`. | Additive fields only. |
| API-075-02 | REST GET `/api/v3/Albums/pinned`, `/smart`, `/tags`, `/persons` | `AlbumCategoryResource` + `cover_ids_2`, `cover_ids_3`. | Additive fields only. |
| API-075-03 | REST GET `/api/v3/Asset/{album_id}/{photo_id}/{size_variant}` | Cover exception widened to side columns. | No route change. |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-075-01 | `php artisan lychee:recompute-album-stats` | Unchanged command; bulk mode now also fills the side columns (FR-075-03). |

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-075-01 | existing `tests/Samples/*.jpg` | Uploaded three or more times into nested albums by the new tests. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-075-01 | Tile hover, sides present | Back layers show `cover_id_2` / `cover_id_3`. |
| UI-075-02 | Tile hover, sides `null` | Back layers show `cover_id` (today's look). |

## Telemetry & Observability
None. The existing job debug line gains the side ids.

## Documentation Deliverables
- Roadmap row 075 (status, progress).
- Knowledge map: `SideCoverIds`, widened `AlbumUserThumb` / `Album` computed fields, `Thumb::createManyFromQueryable`, `PurgeAlbumUserThumbs` register line.
- ADR-075-01 (accepted).
- Upgrade note in the plan: run `php artisan lychee:recompute-album-stats` once to backfill sides.

## Fixtures & Sample Data
Existing samples only (FX-075-01).

## Spec DSL

```
domain_objects:
  - id: DO-075-01
    name: albums side cover columns
    fields:
      - name: auto_cover_id_max_privilege_2
        type: char(24)|null
      - name: auto_cover_id_max_privilege_3
        type: char(24)|null
      - name: auto_cover_id_least_privilege_2
        type: char(24)|null
      - name: auto_cover_id_least_privilege_3
        type: char(24)|null
  - id: DO-075-02
    name: album_user_thumbs side columns
    fields:
      - name: photo_id_2
        type: char(24)|null
      - name: photo_id_3
        type: char(24)|null
  - id: DO-075-03
    name: SideCoverIds
    methods: [forAlbumRow, fromCacheRow]
  - id: DO-075-04
    name: Thumb::createManyFromQueryable
  - id: DO-075-05
    name: album_hover_side_covers_enabled
    type: bool
    default: "1"
routes:
  - id: API-075-01
    method: GET
    path: /api/v3/Albums/{album_id} | /api/v3/Albums/root | /api/v3/Search/albums
    added_fields: [cover_ids_2, cover_ids_3]
  - id: API-075-02
    method: GET
    path: /api/v3/Albums/pinned | /smart | /tags | /persons
    added_fields: [cover_ids_2, cover_ids_3]
  - id: API-075-03
    method: GET
    path: /api/v3/Asset/{album_id}/{photo_id}/{size_variant}
cli_commands:
  - id: CLI-075-01
    command: php artisan lychee:recompute-album-stats
telemetry_events: []
fixtures:
  - id: FX-075-01
    path: tests/Samples/
ui_states:
  - id: UI-075-01
    description: hover with sides
  - id: UI-075-02
    description: hover without sides
```
