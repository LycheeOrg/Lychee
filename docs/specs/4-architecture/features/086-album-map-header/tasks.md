# Feature 086 Tasks – Album Map Header

_Status: Complete_  
_Last updated: 2026-10-07_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

- [x] T-086-01 – Tests: `PATCH /Album` map flag and set-as-header (FR-086-01, FR-086-02, FR-086-11, S-086-01, S-086-02, S-086-03, S-086-14).  
  _Intent:_ `tests/Feature_v3/Album/AlbumMapHeaderTest.php` update cases, failing first.  
  _Verification commands:_ `php artisan test --filter=AlbumMapHeaderTest`

- [x] T-086-02 – Implement the map sentinel and `is_map_header` (FR-086-01, FR-086-02).  
  _Intent:_ `AlbumController::MAP_HEADER`, `RequestAttribute::IS_MAP_HEADER_ATTRIBUTE`, `UpdateAlbumRequest`, `SetHeader`, `AlbumController::updateAlbum()`.  
  _Verification commands:_ `php artisan test --filter=AlbumMapHeaderTest`, `php artisan test --filter=AlbumUpdateTest`, `make phpstan`

- [x] T-086-03 – Tests: `GET /api/v3/Map/album` (FR-086-05, FR-086-06, NFR-086-01, S-086-09, S-086-10, S-086-11, S-086-12, S-086-13).  
  _Intent:_ `tests/Feature_v3/Map/MapAlbumPointsTest.php`, failing first.  
  _Verification commands:_ `php artisan test --filter=MapAlbumPointsTest`

- [x] T-086-04 – Implement the album map points endpoint (FR-086-05, FR-086-06, NFR-086-02).  
  _Intent:_ `MapPointResource`, `QueryAlbumMapPoints`, `GetMapAlbumRequest`, `MapListingController::album()`, `CacheKeyProvider::mapAlbumPointsKey()`, route in `routes/api_v3.php`.  
  _Verification commands:_ `php artisan test --filter=MapAlbumPointsTest`, `php artisan test --filter=MapListingV3Test`, `make phpstan`

- [x] T-086-05 – Tests: album head map-or-image decision and social card (FR-086-03, FR-086-04, FR-086-12, S-086-04 … S-086-09, S-086-15).  
  _Intent:_ head cases in `AlbumMapHeaderTest`, failing first.  
  _Verification commands:_ `php artisan test --filter=AlbumMapHeaderTest`

- [x] T-086-06 – Implement the album head decision (FR-086-03, FR-086-04, FR-086-12, NFR-086-03).  
  _Intent:_ `PreFormattedAlbumData::$is_map_header`, `HeadAlbumResource`, `HasHeaderUrl`, `php artisan typescript:transform`.  
  _Verification commands:_ `php artisan test --filter=AlbumMapHeaderTest`, `php artisan test --filter=MetaTest`, `make phpstan`

- [x] T-086-07 – v8 services and properties selector (FR-086-10).  
  _Intent:_ `album-service.ts` `is_map_header`, `map-v3-service.ts` `getAlbumPoints()`, `AlbumProperties.vue` Map option.  
  _Verification commands:_ `npm run format`, `npm run check`

- [x] T-086-08 – v8 set-as-header sync (FR-086-11).  
  _Intent:_ `AlbumPanel.vue` clears `preFormattedData.is_map_header` when a photo becomes the header.  
  _Verification commands:_ `npm run check`

- [x] T-086-09 – v8 map header component (FR-086-07, FR-086-08, FR-086-09, NFR-086-04 … NFR-086-06).  
  _Intent:_ lazy `AlbumMapHeader.vue`, `AlbumHero.vue` integration.  
  _Verification commands:_ `npm run format`, `npm run check`, `npm run build`

- [x] T-086-10 – Docs, quality gate and manual check (S-086-16).  
  _Intent:_ knowledge map, roadmap, drift gate report; `vendor/bin/php-cs-fixer fix`, `make phpstan`; browser check of header, selector, touch behaviour, dot click.  
  _Verification commands:_ listed in plan Exit Criteria

- [x] T-086-11 – Tests: `is_map_header` is required (FR-086-02, S-086-03, S-086-17).  
  _Intent:_ `AlbumMapHeaderTest` payload sends `is_map_header: false` for the v7 case, new case without the field expects 422, failing first.  
  _Verification commands:_ `php artisan test --filter=AlbumMapHeaderTest`

- [x] T-086-12 – Make `is_map_header` required in the request, existing test payloads and both frontends (FR-086-02).  
  _Intent:_ `UpdateAlbumRequest` rule `required`; `is_map_header => false` in every `PATCH /Album` test payload (`AlbumUpdateTest`, `AlbumTitleSyncTest`, `AlbumMatchingAlbumsTest`, `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest`, `UpdateAlbumDateScrubberTest`, `TitleSplitIntegrityTest`, `AlbumSlugCrudTest`); `UpdateAbumData.is_map_header` required; v7 `AlbumProperties.vue` sends `false`.  
  _Verification commands:_ `php artisan test --filter=<each class above>`, `make phpstan`, `npm run format`, `npm run check`

- [x] T-086-13 – Map interaction never starts the album drag selection (FR-086-08, S-086-18).  
  _Intent:_ reproduce with Playwright first, then `data-stop-drag-select="true"` on the `AlbumMapHeader.vue` band and `Element` targets in `dragAndSelect.ts::isInteractiveTarget()`.  
  _Verification commands:_ `npm run format`, `npm run check`, eslint, `vite build`, scratch Playwright run

## Notes / TODOs

- Owner review on a real touch device; MySQL/PostgreSQL through CI.
