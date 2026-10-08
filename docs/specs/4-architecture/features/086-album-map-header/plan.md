# Feature Plan 086 – Album Map Header

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented  
_Last updated:_ 2026-10-07

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

An album editor picks **Map** as the header; visitors see the album's photos as dots on a map above the hero card, and fall back to the random-photo header when the map cannot be shown. Success: S-086-01 … S-086-15 covered by green Feature_v3 tests, `npm run check` clean, manual check S-086-16.

## Scope Alignment

- **In scope:** `map` sentinel and `PATCH /Album` flag (FR-086-01, FR-086-02); album head decision (FR-086-03, FR-086-04, FR-086-12); `GET /api/v3/Map/album` (FR-086-05, FR-086-06); v8 header component, selector and context-menu sync (FR-086-07 … FR-086-11).
- **Out of scope:** v7, the Map page, clustering, other album types (spec Non-Goals).

## Dependencies & Interfaces

- Feature 067: `ResolvesMapPhotoSource` (`resolveAlbumQuery()`, `resolveAlbumIds()`), `CacheKeyProvider` map tags, `ManagedCacheMapListingInvalidator`.
- `AlbumPolicy::CAN_ACCESS_MAP`, `AlbumConfig::is_map_accessible`.
- Leaflet + `leaflet-gpx` (existing dependencies), `/Map::provider` (v2).
- ADR-086-01.

## Assumptions & Risks

- **Assumptions:** `char(24)` padding on PostgreSQL is handled by trimming `header_id` before comparing.
- **Risks / Mitigations:**
  - Unbounded point payload for huge albums (owner's choice). Mitigation: four-column `toBase()` rows, canvas renderer (NFR-086-02, NFR-086-06).
  - Cache staleness inherited from Feature 067: a photo change in a sub-album flushes the sub-album's and root's map tags, not the parent's, so a parent's cached points can lag until the TTL. The map-or-image decision itself is never cached. Accepted, same behaviour as the Map page.
  - `declined_if:is_compact,true` must treat JSON booleans correctly. Mitigation: S-086-02 test.

## Implementation Drift Gate

2026-10-07, agent review. All 13 tasks `[x]`.

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-086-01, FR-086-02 | `AlbumController::MAP_HEADER`, `UpdateAlbumRequest` (`is_map_header` required, `declined_if`), `SetHeader::do(is_map:)`, `EditableBaseAlbumResource` (trimmed `header_id`), v7 `AlbumProperties.vue` sends `false` | `AlbumMapHeaderTest` patch cases (S-086-01 … S-086-03, S-086-17); every existing `PATCH /Album` test payload sends the field |
| FR-086-03, FR-086-04 | `HasHeaderUrl::isMapHeaderShown()`, `HeadAlbumResource`, `PreFormattedAlbumData::$is_map_header` | `AlbumMapHeaderTest` head cases (S-086-04 … S-086-09) |
| FR-086-05, FR-086-06, NFR-086-01, NFR-086-02 | `QueryAlbumMapPoints`, `MapPointResource`, `GetMapAlbumRequest`, `MapListingController::album()`, `CacheKeyProvider::mapAlbumPointsKey()`, route | `MapAlbumPointsTest` (S-086-09 … S-086-13) |
| FR-086-07 … FR-086-09, NFR-086-04 … NFR-086-06 | `AlbumMapHeader.vue` (`data-stop-drag-select`), `AlbumHero.vue`, `dragAndSelect.ts::isInteractiveTarget()` (`Element` targets) | Playwright runs below; `vite build` emits `AlbumMapHeader-*.js` as a dynamic entry |
| FR-086-10 | `AlbumProperties.vue`, `album-service.ts`, lang key `gallery.album.properties.map_header` (23 locales) | Playwright run below; `LangTest` |
| FR-086-11 | `AlbumPanel.vue` set-as-header sync | `AlbumMapHeaderTest::testSetPhotoAsHeaderReplacesMapHeader` (backend); frontend by code review |
| FR-086-12 | `HasHeaderUrl::getByQuery()` skips the sentinel | `AlbumMapHeaderTest::testHeaderImageOfMapAlbumIsARandomPhoto` |
| NFR-086-03 | one `EXISTS` only when `header_id = 'map'` (short-circuit `&&`) | code review |

**Verification evidence**
- Test classes, run one at a time, all green: `AlbumMapHeaderTest` (13), `MapAlbumPointsTest` (10), `MapListingV3Test` (17), `AlbumUpdateTest` + `AlbumSetHeaderTest` (22), `MetaTest` + `AlbumHeadEndpointTest` + `AlbumUpdateFocusTest` + `AlbumTest` + `PaginationIntegrationTest` + `AlbumConfigDateScrubberTest` (85), `LangTest` (2).
- After making `is_map_header` required: `AlbumMapHeaderTest`, `AlbumUpdateTest`, `AlbumTitleSyncTest`, `AlbumMatchingAlbumsTest`, `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest`, `UpdateAlbumDateScrubberTest`, `TitleSplitIntegrityTest`, `AlbumSlugCrudTest` (87) green.
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`, eslint on every touched frontend file: clean. `php artisan typescript:transform` run.
- Real app, scratch instance (SQLite, storage and uploads in the agent scratchpad, PHP built-in server with `variables_order=EGPCS`, `map_display` and `map_include_subalbums` on), sample photos imported with `lychee:sync` (album with four geotagged photos and a sub-album with one, album without geotagged photo), driven with Playwright + system Chromium, once with `STRUCT_OF_ARRAY_ENABLED` off (18 checks) and once on (19 checks), all passing: selector lists and keeps "Use map header", choosing it autosaves and shows the map, 30svh band (16rem on a phone), no header image, title in the hero card, dots drawn (sub-album photo included), wheel scrolls the page without zooming, dragging on desktop, clicking a dot opens that photo, album without geotagged photo falls back to the photo header, no page errors, touch: no dragging, pinch zoom kept.
- S-086-18, scratch instance rebuilt with a 41-point London–Berlin GPX track on the album: before the fix, dragging on tiles and on the track line drew the album's selection rectangle (6/8); after it, 7/7: tiles and track line pan the map without a rectangle, the attribution flag starts nothing, dragging below the map still selects, a dot click still opens its photo.
- The login route allows 10 attempts per hour: reuse a saved `storageState`; `php artisan cache:clear` on the scratch instance resets the limiter.

**Findings:** none open. NFR-086-05 reworded: Leaflet already reaches the album page through the photo sidebar map, so only the header component is lazy. FR-086-07 failure path reworded: the global API error handling shows its overlay for a failed points request.

## Increment Map

1. **I1 – Map sentinel and PATCH flag** (FR-086-01, FR-086-02, FR-086-11; S-086-01 … S-086-03, S-086-14)
   - _Steps:_ `AlbumMapHeaderTest` cases first (fail), then `AlbumController::MAP_HEADER`, `RequestAttribute::IS_MAP_HEADER_ATTRIBUTE`, `UpdateAlbumRequest` rule + accessor, `SetHeader::do(..., is_map:)`, controller wiring.
   - _Commands:_ `php artisan test --filter=AlbumMapHeaderTest`, `make phpstan`.
2. **I2 – Album map points endpoint** (FR-086-05, FR-086-06, NFR-086-01, NFR-086-02; S-086-09 … S-086-13)
   - _Steps:_ `MapAlbumPointsTest` first, then `MapPointResource`, `QueryAlbumMapPoints` (`do()`, `hasPoints()`), `GetMapAlbumRequest`, `MapListingController::album()`, `CacheKeyProvider::mapAlbumPointsKey()`, route.
   - _Commands:_ `php artisan test --filter=MapAlbumPointsTest`, `php artisan test --filter=MapListingV3Test`, `make phpstan`.
3. **I3 – Album head decision** (FR-086-03, FR-086-04, FR-086-12, NFR-086-03; S-086-04 … S-086-09, S-086-15)
   - _Steps:_ head cases in `AlbumMapHeaderTest` first, then `PreFormattedAlbumData::$is_map_header`, decision helper used by `HeadAlbumResource`, `HasHeaderUrl` ignores the sentinel, `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=AlbumMapHeaderTest`, `php artisan test --filter=MetaTest`, `make phpstan`.
4. **I4 – v8 selector and services** (FR-086-10, FR-086-11)
   - _Steps:_ `UpdateAbumData.is_map_header`, `MapV3Service.getAlbumPoints()`, `AlbumProperties.vue` Map option, `AlbumPanel.vue` set-as-header sync.
   - _Commands:_ `npm run format`, `npm run check`.
5. **I5 – v8 map header component** (FR-086-07 … FR-086-09, NFR-086-04 … NFR-086-06)
   - _Steps:_ `AlbumMapHeader.vue` (lazy), `AlbumHero.vue` integration.
   - _Commands:_ `npm run format`, `npm run check`, `npm run build`.
6. **I6 – Docs and verification**
   - _Steps:_ knowledge map, roadmap, drift gate report, manual browser check (S-086-16).
7. **I7 – Required `is_map_header`** (FR-086-02; S-086-03, S-086-17)
   - _Steps:_ `AlbumMapHeaderTest` sends `is_map_header: false` as the v7 payload and expects 422 without it (fail first); `UpdateAlbumRequest` rule `required`; `is_map_header` added to every existing `PATCH /Album` test payload; `UpdateAbumData.is_map_header` required, v7 `AlbumProperties.vue` sends `false`.
   - _Commands:_ `php artisan test --filter=<each touched class>`, `make phpstan`, `npm run format`, `npm run check`.
8. **I8 – Map interaction never starts the drag selection** (FR-086-08; S-086-18)
   - _Steps:_ reproduce on the scratch instance with Playwright (selection rectangle appears when dragging the map, also on a GPX track line); mark the map band `data-stop-drag-select="true"`; let `dragAndSelect.ts::isInteractiveTarget()` accept any `Element` target so SVG track lines and icons resolve their ancestors too; rebuild and rerun.
   - _Commands:_ `npm run format`, `npm run check`, eslint on touched files, `vite build`, scratch Playwright run.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-086-01 … S-086-03 | I1, I7 / T-086-01, T-086-02, T-086-11, T-086-12 | `AlbumMapHeaderTest` |
| S-086-04 … S-086-08 | I3 / T-086-05, T-086-06 | `AlbumMapHeaderTest` |
| S-086-09 | I2, I3 / T-086-03, T-086-05 | both tests |
| S-086-10 … S-086-13 | I2 / T-086-03, T-086-04 | `MapAlbumPointsTest` |
| S-086-14 | I1 / T-086-01 | `AlbumMapHeaderTest` |
| S-086-15 | I3 / T-086-05 | `AlbumMapHeaderTest` (Meta) |
| S-086-16 | I5, I6 / T-086-09, T-086-10 | manual |
| S-086-17 | I7 / T-086-11, T-086-12 | `AlbumMapHeaderTest` |
| S-086-18 | I8 / T-086-13 | scratch Playwright run |

## Analysis Gate

2026-10-07, agent review against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md):

1. Specification completeness — pass: FR/NFR populated, Q-086-01 … Q-086-06 folded into FR-086-03 … FR-086-10, ASCII mock-ups present.
2. Open questions — pass: none open; ADR-086-01 covers the endpoint and the server-side decision.
3. Plan alignment — pass: plan links spec and tasks; dependencies match.
4. Tasks coverage — pass: every FR maps to a task; tests precede code in I1 … I3; frontend verified by type-check and manual check.
5. Working agreements — pass: no new dependency; decision logic isolated in one helper; ADR-086-01 and Feature 067 ADRs reviewed.
6. Tooling — pass: commands listed per increment.

## Exit Criteria

- All tasks `[x]`; `AlbumMapHeaderTest`, `MapAlbumPointsTest`, `MapListingV3Test`, `AlbumUpdateTest`, `MetaTest` green.
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check` clean.
- `php artisan typescript:transform` run; roadmap and knowledge map updated.

## Follow-ups / Backlog

- Map cache: flush ancestor album tags when a sub-album photo changes (shared with Feature 067).
