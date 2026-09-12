# Feature Plan 066 – Timeline Struct-of-Arrays Refactor

_Linked specification:_ `docs/specs/4-architecture/features/066-timeline-struct-of-arrays/spec.md`
_Status:_ Implemented (code-complete; manual browser verification pending, no dev environment available)
_Last updated:_ 2026-09-11

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs
> from `spec.md` where relevant, log any new high- or medium-impact questions in
> [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications
> are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Timeline reaches functional and architectural parity with the already-shipped per-album SoA
photo-listing stack (Feature 064/065): same three-tier API shape, same virtualized rendering
mechanism, same feature-flag coexistence with v2. Success signals: (1) zero new routes — Timeline
is served entirely through `album_id='timeline'` against existing v3 endpoints; (2) `ratios`/
`details` response times for a requested bucket window are bounded by that window's size, not
library size; (3) a save/edit to one bucket's photos never evicts another bucket's warm cache
entry; (4) the v8 Timeline grid renders virtualized, with layout-correct placeholder sizing for
not-yet-loaded buckets and no perceptible height "jump" for content already scrolled past; (5) the
v2 Timeline path is provably untouched when the SoA flag is off.

## Scope Alignment

- **In scope:** `TimelineAlbum` + policy/factory/enum wiring; SQL-pushdown live bucket computation
  for the global scope; bucket-windowed `ratios` (new optional params); fine-grained cache
  invalidation; incremental frontend store (`TimelineState.ts`); virtualized rendering with
  placeholder sizing and scroll-driven prefetch; deep-link resolution; `TimelineDates.vue`
  migration; flag gating; documentation updates.
- **Out of scope:** Removing/altering v2 Timeline; changing Feature 064/065's whole-scope album
  behavior; a new sharing/visibility-changed cache-invalidation event (TTL-bounded staleness
  accepted, see Follow-ups); any `scope=own|shared` split for Timeline.

## Dependencies & Interfaces

- Feature 064 (`app/Actions/Photo/StructOfArrays/*`, `PhotoChildrenController`,
  `GetPhotoBucketsRequest`/`GetPhotoRatiosRequest`/`GetPhotoDetailsRequest`) — extended, not
  forked.
- Feature 056 (`PhotoAssetController`/`GetPhotoAssetRequest`) — reused unmodified.
- Feature 065 (`AlbumState.ts`'s `isPhotoSoaActive` pattern, `adaptPhotoTile.ts`,
  `PhotoGridVirtual.vue`, `analyticPhotoLayout.ts`, `photo-children-v3-service.ts`) — reused as
  pattern/code, extended where noted.
- Existing smart-album subsystem (`BaseSmartAlbum`, `SmartAlbumType`, `AlbumFactory`) — extended
  with one new case.
- Existing managed-cache subsystem (`CacheKeyProvider`, `ManagedCachePhotoListingInvalidator`,
  `ManagedCacheService`) — extended with a finer tag granularity.
- `[[feedback_avoid_carbon_server_side]]`, `[[feedback_no_hooks_explicit_writes]]`,
  `[[feedback_no_full_test_suite]]`, `[[feedback_no_mariadb_mysql_access]]`,
  `[[feedback_no_defensive_dead_code_docs]]` — all apply throughout.

## Assumptions & Risks

- **Assumptions:** `SmartAlbumType::TIMELINE` with `is_enabled()=false` correctly excludes it from
  the generic smart-album sidebar/listing without other side effects (to verify in I1). The
  installed `@tanstack/vue-virtual` version's dynamic-measurement/`measure()` mechanism is
  sufficient for reflow without an explicit scroll-offset-preserving option (to verify in I7/I8).
- **Risks / Mitigations:**
  - *Risk:* Fine-grained cache tagging adds real complexity to `ManagedCachePhotoListingInvalidator`
    for comparatively low payoff if Timeline usage is low-traffic. *Mitigation:* the coarse tag
    alone (cheap after the SQL-pushdown fix) is a safe fallback if fine-grained tagging proves not
    worth the complexity during I4 — flag for a lighter-weight revision rather than blocking.
  - *Risk:* Placeholder-to-real reflow causes visible scroll jank despite the layout-mode-correct
    placeholder sizing. *Mitigation:* I6/I7 include an explicit manual-verification checkpoint
    before proceeding to I8 (deep-link, which compounds the same mechanism).
  - *Risk:* No local dev/browser environment available in the authoring session — all
    scroll-behavior/reflow/deep-link UX must be manually verified later, not assumed correct from
    typechecks alone (mirrors Feature 063/065's own documented gap).

## Implementation Drift Gate

Before marking any increment complete, re-read the touched files' current state (not just the
diff being authored) and confirm: (a) no Eloquent model events/mutators were introduced for
derived state (`[[feedback_no_hooks_explicit_writes]]` — sync must stay explicit, call-site-driven);
(b) no Carbon import was introduced in touched backend files (NFR-066-02); (c) `make phpstan` /
`vendor/bin/php-cs-fixer fix --dry-run --diff` / `npm run check` are clean for every changed file
before moving to the next increment, not batched at the end. Record any drift found and its fix
directly in this file's increment entries, not a separate log.

## Increment Map

1. **I1 – `TimelineAlbum` + policy/factory/enum wiring**
   - _Goal:_ FR-066-01 through FR-066-04 — Timeline resolves and authorizes correctly as an
     `AbstractAlbum`, with existing (not-yet-scale-safe) tier queries already returning correct
     results.
   - _Preconditions:_ None (first code increment).
   - _Steps:_ New `app/SmartAlbums/TimelineAlbum.php` (override `photos()` per FR-066-02); add
     `SmartAlbumType::TIMELINE` (`is_enabled()=>false`, `require_upload_rights()=>false` with a
     short comment on why); register in `AlbumFactory::BUILTIN_SMARTS_CLASS`; add the
     `AlbumPolicy::canAccess()` early branch (FR-066-03); add the `ResolvesPhotoSource::resolveEffectiveSorting()`
     branch (FR-066-04). Tests first: a `TimelineAlbumTest` covering S-066-01..03, S-066-12,
     S-066-13.
   - _Commands:_ `php artisan test --filter=TimelineAlbumTest`, `vendor/bin/phpstan analyse`,
     `vendor/bin/php-cs-fixer fix --dry-run --diff`.
   - _Exit:_ `findAbstractAlbumOrFail('timeline')` resolves; `GET /Albums/timeline/Photos/buckets`
     (etc.) return correct (if not yet scale-safe) data end-to-end.

2. **I2 – Scale-safe live bucket computation**
   - _Goal:_ FR-066-05, FR-066-07, NFR-066-01, NFR-066-02.
   - _Preconditions:_ I1.
   - _Steps:_ New `PhotoBucketComputer::bucketDateRange()` (pure, Carbon-free, unit test first);
     new SQL-pushdown `GROUP BY` branch in `QueryPhotoBuckets::queryLiveBuckets()` for a
     `TimelineAlbum` source (move, don't duplicate, `Timeline.php:172-186`'s per-driver truncation
     SQL); same treatment for `QueryPhotoDetails::resolveLiveBucketPhotoIds()`, `TimelineAlbum`
     only — `TagAlbum`/`PersonAlbum` untouched.
   - _Commands:_ `php artisan test --filter=PhotoBucketComputerTest`,
     `php artisan test --filter=PhotoBucketsV3Test`, `php artisan test --filter=PhotoDetailsV3Test`,
     `vendor/bin/phpstan analyse`.
   - _Exit:_ Buckets/details response cost bounded by requested window, not library size (S-066-01,
     S-066-08); zero Carbon imports in touched files.

3. **I3 – Windowed `ratios`**
   - _Goal:_ FR-066-06, FR-066-07 (ratios side), NFR-066-03.
   - _Preconditions:_ I2 (reuses `bucketDateRange()`).
   - _Steps:_ `GetPhotoRatiosRequest` gains `bucketIds()`/`photoIds()` + validation (DO-066-02);
     `QueryPhotoRatios::do()` gains `$bucketIds`/`$photoIds` params, SQL pushdown for `Album` (pivot
     column) and `TimelineAlbum` (`bucketDateRange()`/`whereIn`) sources; `CacheKeyProvider::photoRatiosKey()`
     gains scope digest (DO-066-04). Regression-run existing `PhotoRatiosV3Test` unmodified first
     to establish the NFR-066-03 baseline, then add new scoped-param cases (S-066-04..07).
   - _Commands:_ `php artisan test --filter=PhotoRatiosV3Test`, `vendor/bin/phpstan analyse`.
   - _Exit:_ Whole-scope album calls byte-identical; windowed/single-photo Timeline calls correct
     and cache-key-distinct.

4. **I4 – Cache invalidation**
   - _Goal:_ FR-066-09, FR-066-10, NFR-066-04.
   - _Preconditions:_ I3.
   - _Steps:_ `CacheKeyProvider::photoListingBucketTag()`; `PhotoChildrenController::index()`/`details()`
     pass fine tags when bucket-scoped; `ManagedCachePhotoListingInvalidator` Timeline-aware
     branches on `handlePhotoSaved()`/`handlePhotoMoved()`/`handlePhotoDeleted()` (per spec's
     documented coarse-only tradeoff for deletes); verify/add a config-change coarse-flush listener
     for `hide_nsfw_in_timeline`/`timeline_photos_order`/`timeline_photos_granularity`/
     `timeline_page_enabled`/`timeline_photos_public` if no blanket mechanism already exists.
   - _Commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`,
     `vendor/bin/phpstan analyse`.
   - _Exit:_ S-066-10/S-066-11 pass; a save to one bucket provably leaves another bucket's cache
     entry untouched.

5. **I5 – Frontend service + incremental store**
   - _Goal:_ FR-066-11.
   - _Preconditions:_ I3 (needs the extended `ratios` API).
   - _Steps:_ Extend `photo-children-v3-service.ts`'s `getRatios()` with `bucketIds`/`photoIds`
     params, fold the scope digest into the axios-cache-interceptor `id`; new
     `resources/js/stores/TimelineState.ts` (DO-066-05) with `bucketsV3`, per-bucket load-state
     map, flat incremental `tiles`/`ratios`, `requestBucketWindow()`; reuse `adaptPhotoTile.ts`
     unmodified.
   - _Commands:_ `npm run check`.
   - _Exit:_ Dev-console-verified: scrolling triggers correct windowed fetches, no duplicate
     in-flight requests for an already-loading bucket.

6. **I6 – Per-bucket layout cache + placeholder sizing**
   - _Goal:_ FR-066-12 (layout half), NFR-066-05.
   - _Preconditions:_ I5.
   - _Steps:_ Factor `computePhotoLayout()`'s per-bucket WASM call into a cacheable
     `Map<bucketId, {boxes, containerHeight}>`; placeholder computed via the same primitive fed
     uniform `1.0`-ratio input; new `computeTimelineBucketLayout()` (DO-066-06) valid mid-load,
     replacing `computeBucketBoundaries()`'s full-load-only assumption for this store.
   - _Commands:_ `npm run check`; unit tests for the pure layout-cache/placeholder functions where
     practical.
   - _Exit:_ Placeholder heights verified layout-mode-correct in isolation (unit-testable, no
     browser needed for this increment).

7. **I7 – `PhotoGridVirtual.vue` Timeline wiring + prefetch-on-scroll**
   - _Goal:_ FR-066-11 (fetch trigger), FR-066-12 (rendering half).
   - _Preconditions:_ I6.
   - _Steps:_ Point the grid at `TimelineState.ts` instead of `AlbumState.ts`; wire scroll-proximity-driven
     `requestBucketWindow()` calls (overscan-based); confirm existing `watch([chunks, scrollMargin],
     measure())` reflow behavior is sufficient, or add the scroll-offset-preserving option if the
     installed TanStack version needs it (Assumptions).
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual scroll-smoothness check (S-066-14/15) — flagged pending if no browser available
     this session, not claimed done.

8. **I8 – Deep-link resolution**
   - _Goal:_ FR-066-13.
   - _Preconditions:_ I7.
   - _Steps:_ Photo-id → bucket resolution via `ratios?photo_ids[]=`; estimated-offset scroll jump;
     eager target-bucket(±neighbor) load; bidirectional prefetch on landing.
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual `/timeline/:date/:photoId` landing-accuracy check (S-066-16) — flagged pending
     if no browser available.

9. **I9 – `TimelineDates.vue` migration**
   - _Goal:_ FR-066-14.
   - _Preconditions:_ I5 (needs `bucketsV3`).
   - _Steps:_ Swap `props.dates: TimelineData[]` for `props.buckets: PhotoBucketResource`; regroup
     by year off `bucket_ids[i]`; emit `load(bucketId)`; update DOM data-attributes.
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual visual comparison against v2 side rail (S-066-18) — flagged pending if no
     browser available.

10. **I10 – Flag gating + v2 coexistence verification**
    - _Goal:_ FR-066-15, NFR-066-06.
    - _Preconditions:_ I1–I9.
    - _Steps:_ `isTimelineSoaActive`-style dispatcher in `Timeline.vue` on the same
      `is_struct_of_array_enabled` flag; confirm v2 routes/components untouched by diff review.
    - _Commands:_ `npm run check`, full diff review of `routes/api_v2.php`,
      `TimelineController.php`, `Timeline.php`, `PhotoThumbPanel.vue` (expect zero changes).
    - _Exit:_ Flag off ⇒ provably byte-identical v2 behavior (S-066-17).

11. **I11 – Documentation**
    - _Goal:_ Documentation Deliverables in spec.md.
    - _Preconditions:_ I1–I10 complete.
    - _Steps:_ Update `api-design.md`, `knowledge-map.md`, `frontend-gallery.md`; move Feature 066's
      roadmap.md row from Active to Completed with an accurate status note.
    - _Commands:_ None (docs only).
    - _Exit:_ All four docs updated; roadmap.md reflects true completion status.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-066-01 | I1, I2 | Buckets tier, whole-library. |
| S-066-02 | I1 | Guest access gating. |
| S-066-03 | I1 | `timeline_page_enabled` gate. |
| S-066-04 | I3 | Whole-scope regression guard. |
| S-066-05 | I3 | Bucket-windowed ratios. |
| S-066-06 | I3 | Mutually-exclusive param validation. |
| S-066-07 | I3 | Single-photo ratios (deep-link resolver). |
| S-066-08 | I2 | Bucket-scoped details, SQL pushdown. |
| S-066-09 | I1 | Asset endpoint, unmodified. |
| S-066-10 | I4 | Fine-tag eviction. |
| S-066-11 | I4 | Coarse-only delete eviction (documented tradeoff). |
| S-066-12 | I1 | Sort-column fallback. |
| S-066-13 | I1 | NSFW config key isolation. |
| S-066-14 | I7 | Scroll-driven prefetch. |
| S-066-15 | I6, I7 | Placeholder sizing + reflow. |
| S-066-16 | I8 | Deep-link landing. |
| S-066-17 | I10 | v2 coexistence. |
| S-066-18 | I9 | Side rail migration. |

## Analysis Gate

Not yet run. To be completed after I1–I4 (backend) land, before I5 begins, per this project's
established pattern of running the Analysis Gate checklist once the spec/plan have stabilized
against real implementation — record findings here, not in a separate file.

## Exit Criteria

- All 11 increments' exit criteria met.
- `vendor/bin/phpstan analyse` and `vendor/bin/php-cs-fixer fix --dry-run --diff` clean across all
  touched backend files.
- `npm run check` clean across all touched frontend files.
- All new/changed scoped PHPUnit test filters pass; existing `PhotoRatiosV3Test`/`PhotoDetailsV3Test`/
  `PhotoBucketsV3Test` suites regression-pass unmodified.
- Manual browser verification of S-066-14..18 completed (or explicitly flagged as outstanding, not
  silently assumed) before the feature is marked Complete in `roadmap.md`.
- Documentation Deliverables (I11) applied.

## Follow-ups / Backlog

- A dedicated sharing/visibility-changed cache-invalidation event for Timeline (currently
  TTL-bounded staleness, accepted per this spec's Non-Goals).
- Revisit whether fine-grained per-bucket cache tagging is worth its complexity once real Timeline
  usage/traffic patterns are observed (flagged as a Risk above).
- Consider whether `TagAlbum`/`PersonAlbum`'s existing full-scan live-bucket computation should
  also move to the `bucketDateRange()` SQL-pushdown approach if those sources' scale assumptions
  ever change.
