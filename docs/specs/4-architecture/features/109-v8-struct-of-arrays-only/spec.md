# Feature 109 – v8 Struct-of-Arrays Only

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-07 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #109 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
v8 carries two code paths for each listing that has a v3 struct-of-arrays (SoA) endpoint: v3 when `STRUCT_OF_ARRAY_ENABLED=true`, v2 otherwise (default `false`). Three independent switches decide what an installation runs: `V8_ENABLED` (v8 features), `NUXT_UI_ENABLED` (which bundle is served) and `STRUCT_OF_ARRAY_ENABLED` (v3 listings). This feature leaves one: `V8_ENABLED` serves the v8 bundle, enables the v8 features and the v3 listing endpoints, and v8 always takes the v3 path (Q-109-01, ADR-109-01). v7 stays on v2 (Q-109-02 A). Layers: `config/features.php`, 19 v3 FormRequests, `ModulesRightsResource`, `InitConfig`, two Blade views, `ToAdminSetter`, the shared Pinia stores, `app-v8.ts`, v8 components and the v7 Maintenance page. It is the first step of the settings simplification: once v8 has no v2 branch, the settings that only feed v2 paths can be audited and removed in a later feature.

## Current State (2026-11-07, before this feature)

### Switches
- `config/features.php` `'v8' => (bool) env('V8_ENABLED', false)`: breadcrumbs (`Actions\Album\Breadcrumb`), local geodecoding (`Metadata\Geodecoder`, `GeodecodeLocationJob`, `LocalGeoDecodingServiceCheck`), and the `V8_CONFIGS` / `ai_vision_*` settings in `SettingsController::getAll()`. `phpunit*.xml` set `V8_ENABLED=true`.
- `'nuxt_ui' => (bool) env('NUXT_UI_ENABLED', false)`: `vueapp.blade.php` serves `app-v8.ts` instead of `app.ts`; `components/meta.blade.php` adds the SVG icon; `Redirections\ToAdminSetter` sends the first-admin flow to `admin-setup` instead of `install-admin` (ADR-0007). Tests: `Install\AdminSetupTest`, `Unit\Redirections\ToAdminSetterTest`.
- `'struct-of-array' => (bool) env('STRUCT_OF_ARRAY_ENABLED', false)`: `InitConfig::$is_struct_of_array_enabled` → `LycheeState.is_struct_of_array_enabled`, and the server gate below.
- No code ties the three together: v8 on the v7 bundle (v7 face recognition, AI Vision settings, breadcrumbs) and the v8 bundle without v8 features both run today.

### Server gate (`config('features.struct-of-array') === true` in `authorize()`)
`Album\GetAlbumBucketsRequest`, `Album\GetAlbumCategoryRequest`, `Album\GetAlbumChildrenDataRequest`, `Album\GetAlbumChildrenRightsRequest`, `Album\GetScopedAlbumsRequest`, `Face\GetFacePhotoAssetRequest`, `Face\GetFaceQueueRequest`, `Flow\GetFlowListRequest`, `Maintenance\RecomputeBucketsRequest`, `Map\GetMapBucketsRequest`, `Map\GetMapPhotosRequest`, `Map\GetMapTracksRequest`, `Metrics\GetLiveMetricsListRequest`, `Person\GetPeopleListRequest`, `Photo\GetPhotoBucketsRequest`, `Photo\GetPhotoDetailsRequest`, `Photo\GetPhotoRatiosRequest`, `Photo\GetPhotoUrlsRequest`, `Search\GetSearchV3Request`; plus `ModulesRightsResource::canQuickNameFaces()`. Not gated: `GET /api/v3/Albums` (`AlbumListV3Request`, used by v8 Fix Tree and Bulk Album Edit), `Albums::accessPermissions`, `Map/album`, `Insights`, `Asset`.

### Front-end branch points
- Shared stores (used by v7 and v8): `AlbumsState.load()` (`loadV3`/`loadV2`), `AlbumState.loadAlbumsAuto()` and `isPhotoSoaActive`, `TimelineState.isTimelineSoaActive`, `SearchState.isSearchSoaActive`, `MapState.isMapSoaActive`, `FlowState.isFlowSoaActive`, `composables/album/dragAndSelect.ts`.
- v8 (reads of the flag or of the four view getters): `views/gallery-panels/Timeline.vue` (10), `Albums.vue` (8), `Search.vue` (7), `Map.vue` (7), `Flow.vue` (4); `components/gallery/albumModule/AlbumPanel.vue` (7), `components/forms/album/SearchTargetAlbum.vue` (4), `components/gallery/searchModule/ResultPanel.vue` (2), `components/drawers/LiveMetrics.vue` (1), `views/admin/Maintenance.vue` (2), `composables/album/albumTileWidth.ts` (2). `isPhotoSoaActive` is also read by `AlbumPanel.vue`, `PhotoThumbPanelVirtual.vue`, `albumDateScrubberState.ts` and `dragAndSelect.ts`.
- v7: `views/admin/Maintenance.vue` (Recompute buckets card). v7 views never render v3-only state (`sharedAlbumsV3`, `AlbumNavPanel`).

### Tests
34 test files set `features.struct-of-array` (27 `config([... => true])`, 23 `=> false`, 8 `Config::set`), under `tests/Feature_v3/`, `tests/AssistedVision/` and `tests/Feature_v2/StructOfArrayInitTest.php`. No Vitest test reads the flag.

## Goals
- G1: one switch, `V8_ENABLED`, selects the v8 bundle, the v8 features and the v3 listing endpoints (FR-109-03, FR-109-04).
- G2: every v8 listing that has a v3 path always uses it (FR-109-01) and v8 code holds no v2 branch for it (FR-109-02).
- G3: v7 keeps today's flag-off behaviour (FR-109-05).

## Non-Goals
- N-109-01: v2 endpoints stay: v7 and API clients use them.
- N-109-02: no change to v3 payloads or to the other checks of the gated requests (rights, AI Vision settings, SE).
- N-109-03: v8 listings with no v3 counterpart stay on v2 (Q-109-03 A, FR-109-06): album photos with a tag or person filter (`GET /api/v2/Album::photos` with `tag_ids`/`person_id`), the tag page (`GET /api/v2/Tag`), the People page and the merge-person picker (`GET /api/v2/People`), person detail photos (`GET /api/v2/Person/{id}/photos`), Spotlight search and the landing-config featured-item picker (`GET /api/v2/Search`), folder drop (`AlbumService.getAlbums`) and preview data (`getTargetListAlbums`). Follow-ups in the roadmap backlog.
- N-109-04: no settings removal: that is the follow-up settings simplification feature (appendix).
- N-109-05: the shared stores keep their v2 branches (v7 uses them) until v7 is retired.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-109-01 | v8 loads root albums, album children, album photos without a tag/person filter, timeline, search page, map, flow, live metrics, the move-target picker and the drag-select geometry through v3. | Same behaviour as today's v8 with `STRUCT_OF_ARRAY_ENABLED=true`, with no flag beyond `V8_ENABLED`. | Existing v3 request validation. | Existing v3 error handling (toast, empty state). | None new. | Owner, 2026-11-07 |
| FR-109-02 | v8 code (`resources/js/v8`) reads neither `is_struct_of_array_enabled` nor `isTimelineSoaActive`, `isSearchSoaActive`, `isMapSoaActive`, `isFlowSoaActive`; their v2-only markup, functions and imports are deleted. `isPhotoSoaActive` stays: in v8 it is `false` only while a tag or person filter is active (FR-109-06). | v8 renders only the v3 state of those views. | `vp run typecheck`, scoped `vp check`. | – | – | Owner, 2026-11-07; Q-109-02 A |
| FR-109-03 | `features.struct-of-array` and `STRUCT_OF_ARRAY_ENABLED` are removed. The 19 gated v3 requests and `ModulesRightsResource::canQuickNameFaces()` require `config('features.v8') === true` instead; every other check is unchanged. Requests that are ungated today stay ungated. | `V8_ENABLED=true`: the 19 requests answer as they do today with the SoA flag on. | Unchanged. | `V8_ENABLED=false`: the 19 requests answer 403, as with the SoA flag off today; quick naming is not offered. | – | Q-109-01 (owner), ADR-109-01 |
| FR-109-04 | `features.nuxt_ui` and `NUXT_UI_ENABLED` are removed. `features.v8` decides the bundle served by `vueapp.blade.php` (`app-v8.ts`/`app-v8.css` vs `app.ts`), the SVG icon in `components/meta.blade.php`, and the first-admin redirect target in `ToAdminSetter` (`admin-setup` vs `install-admin`, ADR-0007). | `V8_ENABLED=true`: v8 bundle, v8 features, v3 listings. | – | `V8_ENABLED=false`: v7 bundle, no v8 features (breadcrumbs, local geodecoding, v8/AI Vision settings), no v3 listings. v8 features on the v7 bundle are no longer possible. | – | Q-109-01 (owner), ADR-109-01 |
| FR-109-05 | v7 stays on v2. `InitConfig::$is_struct_of_array_enabled` is removed (TypeScript types regenerated). `app-v8.ts` sets `LycheeState.is_struct_of_array_enabled = true` at boot, before the first store load; `app.ts` never sets it, and `LycheeState.load()` no longer writes it. The shared stores keep both paths and choose by that field. | v8: every shared store takes its v3 path from the first request. | – | v7: the shared stores take their v2 path; the v7 Maintenance "Recompute buckets" card is hidden (its request is 403 without `V8_ENABLED`). | – | Q-109-02 A |
| FR-109-06 | v8 listings without a v3 counterpart (N-109-03) keep their v2 calls; an album with an active tag or person filter keeps loading its photos through v2 (`isPhotoSoaActive` `false`). | Filtered album, tag page, People, person photos, Spotlight and the landing picker behave as today. | – | – | – | Q-109-03 A |
| FR-109-07 | The v3 Flow card header (`AlbumCardV3.vue`) renders like v2 `HeaderImage.vue`: with `InitResource::$image_header_cover` = `fit` the header photo is shown whole (`object-contain`) over a blurred, cropped copy of itself; with `cover` it is cropped (as today); while "show more" is open the header photo is shown full width at its natural height (no crop, no fixed height), in both modes. The NSFW blur covers the header in every state. The header photo is `small2x` (720 px tall, Q-109-06 A; v2 used `medium`, not served by the v3 asset API); the blurred background copy is `thumb`. | `flow_image_header_cover` takes effect in v8 again. | – | A failed thumbnail load keeps `<Thumb>`'s own placeholder. | – | Q-109-05 A |
| FR-109-09 | The v3 Flow card shows v2 `TopImages.vue`'s grid whenever it has no header photo (the image header off, as in v2): up to 5 preview photos (`small`); a "+N" tile when the album holds more, N = `max(numPhotos, preview count) − 5`; one full-height row when fewer than 4 photos; NSFW blur; a click opens that photo. The carousel stays tied to the image header (v2 rule). The loading skeleton shows while the preview loads, header on or off. No grid when the preview is empty or failed. | `flow_image_header_enabled` off shows the grid again in v8. | – | Preview load failed: no image area (Feature 068 behaviour). | – | Q-109-07 A |
| FR-109-08 | The v3 map (`Map.vue`) opens fitted to its content, like v2: for an album, the bounds of the album's points from `GET /api/v3/Map/album` (honours `map_include_subalbums`); for the gallery map, the bounds of the first whole-world fetch (bucket centroids, single photos of grid cells, individual photos). Bounds are padded by 10 % of their span on each side (v2 rule). An album without geotagged photos fits to its tracks once they load. Fitting happens once per opening; viewport changes after it are the user's. | Map opens on the photos instead of the whole world. | – | Album points request failing, or no content: stays on the whole-world view (today's behaviour), no toast for the fit itself. | – | Q-109-05 A |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-109-01 | No behaviour change for installations that run `V8_ENABLED=true`, `NUXT_UI_ENABLED=true`, `STRUCT_OF_ARRAY_ENABLED=true` today. | Most v8 testers run that way. | The rewritten v3 feature tests, the AdminSetup/ToAdminSetter tests and `MetaTest` green; browser check of the FR-109-01 views. | Features 051, 058–069, 079, 093. | Owner, 2026-11-07 |
| NFR-109-02 | No new dependency, no migration. | AGENTS.md | Diff review. | – | AGENTS.md |
| NFR-109-03 | v7 (`V8_ENABLED=false`) behaves as today's v7 with all three flags off. | Q-109-02 A | Browser check of the v7 root albums, an album, timeline and Maintenance. | – | Q-109-02 A |
| NFR-109-04 | Gating tests are rewritten against `features.v8`, not removed; the only removed test is `StructOfArrayInitTest` (subject to Q-109-04). | AGENTS.md (tests are compulsory) | Test count per rewritten class unchanged. | – | Q-109-01 (owner) |

## UI / Interaction Mock-ups
No new UI. A v8 installation that ran without the SoA flag now gets the v3 layout every flag-on installation already has, for example on the root albums page:

```
Before (SoA flag off, v8)                 After (every v8 installation)
+--------------------------------------+  +--------+-----------------------------+
| Albums                               |  | Tree   | Albums                      |
| [tile][tile][tile][tile]             |  | > Trips| [tile][tile][tile]          |
| [tile][tile][tile][tile]             |  |   2025 | [tile][tile][tile]          |
| (whole list, one v2 request)         |  | > Pets | (buckets, virtual scroll)   |
| Shared with me                       |  |        | Shared with me              |
| [tile][tile]                         |  | [<]    | [tile][tile]                |
+--------------------------------------+  +--------+-----------------------------+
                                           [<] collapses the album tree
                                           (AlbumNavPanel); on mobile it opens
                                           from a floating button.
```

Flow card top-images grid (FR-109-09), image header off:

```
8+ photos                                   fewer than 4 photos
+-------+-------+-------+-------+---------+ +-------------+-------------+
| photo | photo | photo | photo |         | |             |             |
+-------+-------+-------+-------+   + 15  | |    photo    |    photo    |
|        photo                  |         | |             |             |
+-------------------------------+---------+ +-------------+-------------+
```

Flow card header (FR-109-07), 384 px header box:

```
cover (today)                fit                          "show more" open
+--------------------------+ +--------------------------+ +--------------------------+
|##########################| |::::::+------------+::::::| |                          |
|######## cropped #########| |:blur:|   whole    |:blur:| |   whole photo, natural   |
|########  photo  #########| |::::::|   photo    |::::::| |   height, full width     |
|##########################| |::::::+------------+::::::| |                          |
+--------------------------+ +--------------------------+ |                          |
| Title  by owner          | | Title  by owner          | +--------------------------+
+--------------------------+ +--------------------------+ | Title  by owner  ...     |
                                                          +--------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-109-01 | `V8_ENABLED=true`, no other flag: `/` serves `app-v8.ts`; root albums, an album, timeline, search, map and flow load through `/api/v3/...`. |
| S-109-02 | `V8_ENABLED=true`, album with an active tag or person filter: photos load through `GET /api/v2/Album::photos` (unchanged). |
| S-109-03 | `V8_ENABLED=false`: `/` serves `app.ts`; the shared stores use v2; Maintenance hides Recompute buckets. |
| S-109-04 | `V8_ENABLED=false`: each of the 19 gated v3 requests answers 403; quick naming is not offered. |
| S-109-05 | `V8_ENABLED=true`: each of the 19 gated v3 requests answers as today with the SoA flag on (other checks unchanged). |
| S-109-06 | No admin yet: `V8_ENABLED=true` redirects to `admin-setup`, `V8_ENABLED=false` to `install-admin`. |
| S-109-07 | Meta tags: the SVG icon is present only with `V8_ENABLED=true`. |
| S-109-08 | `GET /api/v2/Gallery::Init` no longer carries `is_struct_of_array_enabled`. |
| S-109-09 | Flow, image header on, `flow_image_header_cover = fit`: the header shows the whole photo over a blurred copy. |
| S-109-10 | Flow, `flow_image_header_cover = cover`: the header photo is cropped to the header height (unchanged). |
| S-109-11 | Flow, compact mode, "show more" clicked on a card: the header photo shows at its natural height (it stays open, as in v2). |
| S-109-12 | Map of an album with geotagged photos: opens fitted to their padded bounds; an album with only tracks fits to the tracks once loaded. |
| S-109-14 | Flow, image header off, album with 8 or more photos: 5 photos in two rows and a "+N" tile; no carousel. |
| S-109-15 | Flow, image header off, album with fewer than 4 photos: one full-height row, no "+N". |
| S-109-16 | Flow, image header off: a grid photo click opens that photo; the skeleton shows while the preview loads. |
| S-109-13 | Gallery map: opens fitted to the padded bounds of the first world fetch; a gallery without geotagged photos stays on the whole world. |

## Test Strategy
- **REST API (v3):** in the 33 test files other than `StructOfArrayInitTest`, `features.struct-of-array => true` lines are removed (`V8_ENABLED=true` comes from `phpunit.xml`) and `=> false` cases switch to `features.v8 => false` with the same 403 expectation (S-109-04, S-109-05). Red first: with the SoA lines gone, the success cases fail until the gates move.
- **Install / redirect / meta:** `AdminSetupTest`, `ToAdminSetterTest` toggle `features.v8` instead of `features.nuxt_ui` (S-109-06); `MetaTest` covers the icon (S-109-07).
- **Init:** `StructOfArrayInitTest` removed (Q-109-04); S-109-08 shows in the regenerated `lychee.d.ts` (field gone) and in `vp run typecheck` (no reader left).
- **Frontend (v8):** `vp run typecheck`, scoped `vp check`, existing Vitest store tests; `AlbumCardV3.test.ts` (S-109-09 … 11, red first); `mapContentBounds.test.ts` for the padded-bounds helper (S-109-12, S-109-13, red first); browser check of S-109-01, S-109-02, S-109-09 … 13 on a scratch instance with only `V8_ENABLED=true`.
- **Frontend (v7):** browser check of S-109-03 with `V8_ENABLED=false`.
- **Docs/Contracts:** `php artisan typescript:transform` after the `InitConfig` change.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-109-01 | `InitConfig::$is_struct_of_array_enabled`: removed (FR-109-05). `LycheeState.is_struct_of_array_enabled` stays as a front-end field set by `app-v8.ts`. | `app/Http/Resources/GalleryConfigs/InitConfig.php`, `resources/js/lychee.d.ts`, `stores/LycheeState.ts`, `app-v8.ts` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-109-01 | REST GET `/api/v3/Albums/{root,smart,persons,tags,pinned}*`, `/Albums/{id}*`, `/Albums/{id}/Photos*`, `/Search/*`, `/Map/{buckets,Photos,tracks}`, `/Flow`, `/Metrics`, `/People`, `/Faces/*`, `Maintenance::recomputeBuckets` | Require `features.v8` (FR-109-03). | 19 FormRequests listed under Current State. |
| API-109-02 | REST GET `/api/v2/Gallery::Init` | Loses `is_struct_of_array_enabled` (FR-109-05). | `InitConfig` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-109-01 | `V8_ENABLED` | The only switch: bundle, v8 features, v3 listings (FR-109-03, FR-109-04). |
| CLI-109-02 | `NUXT_UI_ENABLED`, `STRUCT_OF_ARRAY_ENABLED` | Removed; ignored if still present in `.env`. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-109-01 | v8 listing views | Always the v3 (bucketed, virtual-scroll) state, except an album with a tag/person filter (v2). |

## Telemetry & Observability
None.

## Documentation Deliverables
- ADR-109-01 (one v8 switch); ADR-0007 amended (`features.v8` replaces `nuxt_ui`).
- `config/features.php` `v8` docblock; `nuxt_ui` and `struct-of-array` entries removed.
- `docs/specs/2-how-to/customize-v8-nuxt-ui.md`: `V8_ENABLED=true` instead of `NUXT_UI_ENABLED=true`; `app-v8.ts` header comment.
- Knowledge map: SoA entries no longer describe a flag-gated dual path for v8.
- Roadmap row #109; backlog rows for the N-109-03 listings.

## Spec DSL

```
domain_objects:
  - id: DO-109-01
    name: InitConfig.is_struct_of_array_enabled
    status: removed
routes:
  - id: API-109-01
    method: GET
    path: /api/v3/{Albums,Search,Map,Flow,Metrics,People,Faces}/...
    gate: features.v8
  - id: API-109-02
    method: GET
    path: /api/v2/Gallery::Init
    change: drops is_struct_of_array_enabled
cli_commands:
  - id: CLI-109-01
    command: V8_ENABLED
  - id: CLI-109-02
    command: NUXT_UI_ENABLED, STRUCT_OF_ARRAY_ENABLED
    status: removed
ui_states:
  - id: UI-109-01
    description: v8 listing views always in the v3 state (filtered album excepted)
```

## Appendix – Settings audit (2026-11-07)
The audit that motivated this feature (528 config keys in the migrated test database; a key counts as used when a live `ConfigManager` read exists in server logic, or in a resource field the v8 front end reads) flagged, for the later settings feature:
- Not used: `date_format_photo_thumb` (no reader since #2504), `map_display_direction` (since #3291), `photos_wraparound` (read only in commented-out code, `HasPrepPhotoCollection.php`), `default_all_settings` (sent in `SettingsConfig`, read by no front end, since #3081), `cache_ttl` (response cache removed in #4594).
- Vestigial: `cache_enabled` (response cache removed in #4594; still disables album password protection in `AlbumRightsResource`).
- v7 only: `gallery_header_bar_gradient` (`RootConfig::$is_header_bar_gradient`, read only by v7 `AlbumsHeader.vue`).
