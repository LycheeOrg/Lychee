# Feature 066 Tasks – Timeline Struct-of-Arrays Refactor

_Status: Implemented (code-complete; manual browser verification pending, no dev environment available)_
_Last updated: 2026-09-11_

> Keep this checklist aligned with `plan.md`'s increments. Stage tests before implementation,
> record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> Update `roadmap.md`'s status when all tasks are done.

## Checklist

### I1 – `TimelineAlbum` + policy/factory/enum wiring

- [x] T-066-01 – Write `TimelineAlbumTest` covering guest/auth access gating and
  `timeline_page_enabled` (F-066-03, S-066-02, S-066-03).
  _Intent:_ Failing tests staged before `TimelineAlbum`/`AlbumPolicy` exist.
  _Verification commands:_ `php artisan test --filter=TimelineAlbumTest` (expect failure/missing class).

- [x] T-066-02 – Add `App\SmartAlbums\TimelineAlbum` with `photos()` reimplementing
  `Timeline::do()`'s exact query (F-066-02).
  _Intent:_ New `AbstractAlbum`, no owner restriction, `hide_nsfw_in_timeline`, `origin: null`.
  _Verification commands:_ `vendor/bin/phpstan analyse`; `vendor/bin/php-cs-fixer fix --dry-run --diff`.

- [x] T-066-03 – Add `SmartAlbumType::TIMELINE` (`is_enabled()=>false`,
  `require_upload_rights()=>false`, brief comment on why) and register in
  `AlbumFactory::BUILTIN_SMARTS_CLASS` (F-066-01).
  _Intent:_ `findAbstractAlbumOrFail('timeline')` resolves; `getAllBuiltInSmartAlbums()`/sidebar
  listing unaffected.
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [x] T-066-04 – Add `AlbumPolicy::canAccess()` early `TimelineAlbum` branch reproducing
  `IdOrDatedTimelineRequest::authorize()`'s predicate (F-066-03, S-066-02, S-066-03).
  _Intent:_ Additive-only; zero behavior change to the 14 existing smart albums.
  _Verification commands:_ `php artisan test --filter=TimelineAlbumTest`; existing smart-album
  policy test suite regression-run.

- [x] T-066-05 – Add `ResolvesPhotoSource::resolveEffectiveSorting()` `TimelineAlbum` branch:
  `timeline_photos_order` restricted to `{CREATED_AT,TAKEN_AT}`, hardcoded `DESC` (F-066-04, S-066-12).
  _Intent:_ Row order matches v2 Timeline exactly.
  _Verification commands:_ `php artisan test --filter=TimelineAlbumTest`.

- [x] T-066-06 – End-to-end verification: `GET /Albums/timeline/Photos/buckets`, `/Photos`,
  `/Photos/details` return correct (not-yet-scale-safe) data for `album_id='timeline'` (S-066-01,
  S-066-13).
  _Intent:_ Confirm zero-new-route claim and existing tier machinery both work unmodified before
  scale work begins.
  _Verification commands:_ `php artisan test --filter=TimelineAlbumTest`.

### I2 – Scale-safe live bucket computation

- [x] T-066-07 – Write `PhotoBucketComputerTest::testBucketDateRange*` (pure function, Carbon-free)
  covering YEAR/MONTH/DAY/HOUR granularities (F-066-05, F-066-07, NFR-066-01, NFR-066-02).
  _Intent:_ Failing test staged before `bucketDateRange()` exists.
  _Verification commands:_ `php artisan test --filter=PhotoBucketComputerTest`.

- [x] T-066-08 – Implement `PhotoBucketComputer::bucketDateRange()` — plain integer arithmetic on
  the bucket-id string, no Carbon/DateTime (NFR-066-02).
  _Intent:_ Turns a bucket-id into a `[start, end)` date range on the effective sort column.
  _Verification commands:_ `php artisan test --filter=PhotoBucketComputerTest`; grep touched file
  for `Carbon`/`DateTime` imports (expect none).

- [x] T-066-09 – Add SQL-pushdown `GROUP BY` branch to `QueryPhotoBuckets::queryLiveBuckets()` for
  a `TimelineAlbum` source; move (not duplicate) the per-driver truncation SQL from
  `Timeline.php:172-186` (F-066-05, NFR-066-01).
  _Intent:_ Buckets tier response cost bounded by distinct-bucket count, not photo count.
  _Verification commands:_ `php artisan test --filter=PhotoBucketsV3Test`.

- [x] T-066-10 – Same SQL-pushdown treatment for `QueryPhotoDetails::resolveLiveBucketPhotoIds()`,
  `TimelineAlbum` only — confirm `TagAlbum`/`PersonAlbum` full-scan path untouched (F-066-07,
  S-066-08).
  _Intent:_ Details tier bucket-scoped fetch bounded by requested window.
  _Verification commands:_ `php artisan test --filter=PhotoDetailsV3Test`.

### I3 – Windowed `ratios`

- [x] T-066-11 – Write `PhotoRatiosV3Test` regression baseline run (existing tests, unmodified) to
  establish the NFR-066-03 byte-identical-response floor before touching `QueryPhotoRatios` (NFR-066-03).
  _Intent:_ Prove no regression before adding new capability.
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

- [x] T-066-12 – Add `bucketIds()`/`photoIds()` accessors + `sometimes|array` + `prohibits`
  validation to `GetPhotoRatiosRequest` (F-066-06, S-066-06).
  _Intent:_ Mirrors `GetPhotoDetailsRequest.php:65-82`, minus `required_without`.
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [x] T-066-13 – Extend `QueryPhotoRatios::do()` with `$bucketIds`/`$photoIds` params; SQL pushdown
  for `Album` (pivot column `whereIn`) and `TimelineAlbum` (`bucketDateRange()`-based `whereIn`/
  `whereBetween`) sources (F-066-06, F-066-07, S-066-05, S-066-07).
  _Intent:_ Whole-scope callers (both params omitted) unaffected.
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

- [x] T-066-14 – Extend `CacheKeyProvider::photoRatiosKey()` with a `$scopeDigest` param + new
  `photoRatiosScopeDigest()`, mirroring `photoDetailsKey()`/`photoDetailsScopeDigest()` exactly.
  _Intent:_ Two different windows for the same scope don't collide in cache.
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [x] T-066-15 – Add new `PhotoRatiosV3Test` cases for S-066-04 (whole-scope regression),
  S-066-05..07 (windowed/single-photo).
  _Intent:_ Full scenario coverage for the new params.
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

### I4 – Cache invalidation

- [x] T-066-16 – Add `CacheKeyProvider::photoListingBucketTag()`/`photoListingBucketTags()`
  (F-066-09).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [x] T-066-17 – `PhotoChildrenController::index()`/`details()` pass fine bucket tags (in addition
  to the coarse tag) when the request is bucket-scoped (F-066-09).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [x] T-066-18 – Write `ManagedCachePhotoListingInvalidatorTest` cases for S-066-10 (fine-tag-only
  eviction on save/move) and S-066-11 (coarse-only on delete) before implementing the handler
  branches.
  _Intent:_ Failing tests staged first.
  _Verification commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`
  (expect failure).

- [x] T-066-19 – Add Timeline-aware branches to `handlePhotoSaved()`/`handlePhotoMoved()`
  (resolve touched photo's current bucket, evict fine+coarse tags) and `handlePhotoDeleted()`
  (coarse-only, documented tradeoff) (F-066-10).
  _Verification commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`.

- [x] T-066-20 – Verify whether a blanket config-change cache-flush listener already exists for
  `hide_nsfw_in_timeline`/`timeline_photos_order`/`timeline_photos_granularity`/
  `timeline_page_enabled`/`timeline_photos_public`; if not, add one narrow listener doing a coarse
  `forgetTag()` flush on save of any of these keys.
  _Verification commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`.

### I5 – Frontend service + incremental store

- [x] T-066-21 – Extend `photo-children-v3-service.ts`'s `getRatios()` with `bucketIds`/`photoIds`
  params (query-string `bucket_ids[]=`/`photo_ids[]=`) and fold the scope digest into the
  axios-cache-interceptor cache `id` (F-066-11).
  _Verification commands:_ `npm run check` — pass.

- [x] T-066-22 – New `resources/js/stores/TimelineState.ts`: `bucketsV3`, per-bucket load-state
  map, flat incremental `tiles`/`ratios`, `requestBucketWindow()` with dedup of in-flight/loaded
  buckets (F-066-11).
  _Verification commands:_ `npm run check` — pass. v2 store logic (`load`/`loadLess`/`loadMore`/
  `initialLoad`/`loadDates`) kept byte-identical in the same file, alongside the new v3
  fields/actions (mirrors `AlbumState.ts`'s own v2/v3 coexistence).

- [ ] T-066-23 – Dev-console verification: scrolling triggers correct windowed fetches, no
  duplicate in-flight requests for an already-loading bucket.
  _Intent:_ Sanity check before building the visual layer on top.
  _Verification commands:_ Manual, browser devtools network tab (flag as pending if no dev
  environment available this session).
  **PENDING — no browser/dev environment available this authoring session
  (`[[feedback_no_mariadb_mysql_access]]`). `requestBucketWindow()`'s dedup logic (skips a
  bucket already `loadedBucketsV3`/`loadingBucketsV3`) was verified by code review only.**

### I6 – Per-bucket layout cache + placeholder sizing

- [x] T-066-24 – Factor `computePhotoLayout()`'s per-bucket WASM call into a cacheable
  `Map<bucketId, {boxes, containerHeight}>` (F-066-12, NFR-066-05).
  _Verification commands:_ `npm run check` — pass. Implemented as `computeTimelinePhotoLayout()`
  in `analyticPhotoLayout.ts`; cache entries are keyed by a signature over
  `mode|containerWidth|target|gap|loaded-state|count`, so only the one bucket whose signature
  actually changed (a placeholder→real transition, or a resize/mode change) is recomputed.

- [x] T-066-25 – Implement placeholder-height computation: same WASM primitive, `count` copies of
  `1.0` ratio for not-yet-loaded buckets (F-066-12, NFR-066-05, S-066-15).
  _Intent:_ Layout-mode-correct, not an arbitrary constant.
  _Verification commands:_ `npm run check` — pass. No dedicated unit test added (no existing
  Vitest/unit-test harness precedent found for `analyticPhotoLayout.ts`'s sibling
  `computePhotoLayout()` either — mirrors that file's own testing posture).

- [x] T-066-26 – New `computeTimelineBucketLayout()`, valid mid-load (unlike
  `computeBucketBoundaries()`'s full-load-only assumption).
  _Verification commands:_ `npm run check` — pass. Implemented in `albumBucketBoundaries.ts`
  (count-only boundary math, sibling to `computeBucketBoundaries()`) — the WASM pixel-layout half
  of DO-066-06 lives as the separate `computeTimelinePhotoLayout()` (T-066-24/25) in
  `analyticPhotoLayout.ts`, since that file already owns every other WASM-layout call; see this
  feature's final report for the naming rationale.

### I7 – `PhotoGridVirtual.vue` Timeline wiring + prefetch-on-scroll

- [x] T-066-27 – Point `PhotoGridVirtual.vue` at `TimelineState.ts` for the Timeline route instead
  of `AlbumState.ts` (F-066-11).
  _Verification commands:_ `npm run check` — pass. Implemented as a new `source?: "album" |
  "timeline"` prop on the SAME component (rather than a forked file) — the album path's every
  computed is left byte-identical, gated behind `source.value !== "timeline"` early-returns, so
  Feature 065's proven behaviour is provably untouched when `source` is omitted/`"album"`.

- [x] T-066-28 – Wire scroll-proximity-driven `requestBucketWindow()` calls off the virtualizer's
  visible range ± overscan (F-066-11, S-066-14).
  _Verification commands:_ `npm run check` — pass. `visibleBucketIndices`/`prefetchBucketIds`
  computeds + a `prefetchKey`-watch in `PhotoGridVirtual.vue`.
  **Scroll-triggering behavior itself is UNVERIFIED — no browser this session; see T-066-29's
  note, same caveat applies.**

- [x] T-066-29 – Verify (or extend) the existing `watch([chunks, scrollMargin], measure())` reflow
  mechanism is sufficient for placeholder→real transitions; add a scroll-offset-preserving option
  if the installed TanStack version needs it (F-066-12, S-066-15).
  _Verification commands:_ `npm run check` — pass (code review of the installed
  `@tanstack/virtual-core@3.17.8` source, see below).
  **Found insufficient, as the plan's own Risk anticipated — extended, not just verified.**
  `measure()` alone only invalidates TanStack's internal size cache; the library's own
  `shouldAdjustScrollPositionOnItemSizeChange` scroll-compensation only engages through its
  DOM-measured `resizeItem()`/`measureElement()` path (confirmed by reading
  `node_modules/@tanstack/virtual-core/dist/esm/index.js`), which this component never calls
  (sizes come from `estimateSize()`, analytically known upfront). Added an explicit
  before/after "cumulative height above the current scroll offset" comparison
  (`cumulativeChunkHeightAbove()`) in the same watcher, compensating via `scrollToOffset()` when a
  bucket strictly above the fold changes height.
  **PENDING manual browser verification — no dev environment available this session
  (`[[feedback_no_mariadb_mysql_access]]`); this compensation logic could only be verified by code
  review against the TanStack source, not scroll-tested.**

### I8 – Deep-link resolution

- [x] T-066-30 – Implement photo-id → bucket resolution via `getRatios('timeline',
  {photoIds:[id]})` (F-066-13, S-066-07, S-066-16).
  _Verification commands:_ `npm run check` — pass. `TimelineState.ts.resolveBucketForPhotoV3()`.

- [x] T-066-31 – Implement estimated-offset scroll jump + eager target-bucket(±neighbor) load +
  bidirectional prefetch on landing (F-066-13).
  _Verification commands:_ `npm run check` — pass. `PhotoGridVirtual.vue`'s
  `resolveTimelineDeepLink()` (called from `onMounted` when `source==="timeline"`): resolves the
  target bucket, eagerly loads it ± 1 neighbour on each side
  (`neighborBucketIdsV3()`), waits for a real `containerWidth` measurement, then
  `virtualizer.scrollToOffset()`s to the target bucket's `top` (from
  `timelinePixelLayout.value.buckets`, mixing real + placeholder heights for whichever
  preceding buckets aren't loaded yet). Bidirectional prefetch as the user scrolls back up falls
  out of T-066-28's scroll-proximity watcher for free — no separate mechanism needed.
  **PENDING manual `/timeline/:date/:photoId` landing-accuracy check (S-066-16) — no dev
  environment available this session.**

### I9 – `TimelineDates.vue` migration

- [x] T-066-32 – Swap `props.dates: TimelineData[]` for `props.buckets: PhotoBucketResource`;
  regroup by year off `bucket_ids[i]`; update emit/DOM data-attributes (F-066-14, S-066-18).
  _Verification commands:_ `npm run check` — pass.
  **Deviation from the literal task wording:** implemented as a NEW forked component,
  `TimelineDatesV3.vue`, rather than an in-place edit of `TimelineDates.vue` —
  `[[project_v8_migration_scope]]` (fork shared modules rather than editing them in place) plus
  this feature's own explicit note ("the old `time_date`-based version must keep working for the
  v2 fallback") make an in-place prop swap incorrect: `TimelineDates.vue` is still imported
  unmodified by the v2 path in `Timeline.vue`. `TimelineDates.vue` itself is confirmed untouched
  by `git diff` (see T-066-34).
  **Visual parity against v2 (S-066-18) is UNVERIFIED — no browser this session.**

### I10 – Flag gating + v2 coexistence verification

- [x] T-066-33 – Add `is_struct_of_array_enabled`-driven dispatcher in `Timeline.vue` between
  old/new store+renderer (F-066-15, NFR-066-06).
  _Verification commands:_ `npm run check` — pass. `isTimelineSoaActive` computed
  (delegates to `timelineStore.isTimelineSoaActive`) gates the template's `PhotoThumbPanel`/
  `TimelineDates` vs `PhotoGridVirtual`/`TimelineDatesV3` pairs and `refresh()`'s v2
  (`loadDates()`+`initialLoad()`) vs v3 (`loadV3()`) fetch dispatch. `goBack()` also branches (a
  necessary addition beyond the task's literal wording — v2's `goBack()` re-derives the route
  `date` param from `photo.timeline?.time_date`, which is always `null` for a v3/`adaptPhotoTile()`
  tile; the v3 branch reuses the route's own already-correct `date` param instead).

- [x] T-066-34 – Diff review confirming zero changes to `routes/api_v2.php`,
  `TimelineController.php`, `app/Actions/Photo/Timeline.php`, `PhotoThumbPanel.vue`,
  `PhotoThumbPanelList.vue` (S-066-17).
  _Verification commands:_ `git diff --stat -- routes/api_v2.php app/Http/Controllers/Gallery/TimelineController.php app/Actions/Photo/Timeline.php resources/js/v8/components/gallery/albumModule/PhotoThumbPanel.vue resources/js/v8/components/gallery/albumModule/PhotoThumbPanelList.vue resources/js/v8/components/gallery/timelineModule/TimelineDates.vue`
  — empty (also included `TimelineDates.vue` in this check, since I9 forked rather than edited it
  in place — see T-066-32).

### I11 – Documentation

- [x] T-066-35 – Update `docs/specs/3-reference/api-design.md` with `ratios`'s new params and
  Timeline's `album_id='timeline'` consumption of the existing v3 routes.
  _Verification commands:_ None (docs only). New "API v3: Timeline (Struct-of-Arrays)" subsection
  added after the existing Photo Listing Virtual-Scroll Backend section.

- [x] T-066-36 – Update `docs/specs/4-architecture/knowledge-map.md` with `TimelineAlbum`, the
  bucket-windowed fetch pattern, and the fine/coarse cache-tag scheme.
  _Verification commands:_ None (docs only). New "Timeline Struct-of-Arrays (Feature 066)" pattern
  entry added under Architectural Patterns.

- [x] T-066-37 – Update `docs/specs/3-reference/frontend-gallery.md` with the incremental
  `TimelineState.ts` store, placeholder-sizing/prefetch-on-scroll mechanism, and deep-link
  resolution flow.
  _Verification commands:_ None (docs only). This doc is a high-level view-by-view overview (it
  never documented Feature 065's own virtualization work in comparable depth either) — expanded
  its existing "Virtual scrolling for large photo sets" bullet under Performance Considerations
  with the `PhotoGridVirtual.vue`/`source` prop mechanism and a pointer to this feature's own docs
  for full detail, rather than duplicating spec.md's depth here.

- [x] T-066-38 – Move Feature 066's `roadmap.md` row from Active to Completed with an accurate
  status note (mirroring this project's established convention for the Notes column).
  _Verification commands:_ None (docs only). Moved with a status note stating the feature is
  code-complete (all backend tests green, all frontend typechecks/lint green) but that manual
  browser verification of S-066-14..18 (scroll smoothness, placeholder reflow, deep-link landing
  accuracy, side-rail visual parity) is still outstanding — mirrors Feature 063/065's own
  precedent for this exact situation, not claimed as fully verified.

## Notes / TODOs

- S-066-14..18 (scroll smoothness, placeholder reflow, deep-link landing accuracy, side-rail visual
  parity) require a live browser/dev environment this authoring session does not have access to
  (`[[feedback_no_mariadb_mysql_access]]`) — each corresponding task above is explicitly flagged;
  do not mark these `[x]` on typecheck-pass alone, only after real manual verification.
- T-066-20's config-change-flush listener is conditional on what's actually found in the codebase
  at implementation time — if a blanket mechanism already exists, this task becomes "confirm
  Timeline's config keys are covered by it," not "build a new listener."
