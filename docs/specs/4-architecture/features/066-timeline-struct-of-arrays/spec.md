# Feature 066 – Timeline Struct-of-Arrays Refactor

| Field | Value |
|-------|-------|
| Status | Implemented (code-complete; manual browser verification and doc-completeness follow-up pending, no dev environment available) |
| Last updated | 2026-09-11 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/066-timeline-struct-of-arrays/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/066-timeline-struct-of-arrays/tasks.md` |
| Roadmap entry | #066 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

The v8 global Timeline (`/timeline`, the cross-album "all photos by date" view) still runs on the
v2 paginated API (`GET /api/Timeline`, page-based, a "count-youngers-then-resolve-page" trick for
deep-linking) and renders through the old, non-virtualized `PhotoThumbPanel.vue` path — every
loaded photo across every loaded page is a live DOM node, positioned by imperative style
mutation. Per-album photo listing already moved to a proven v3 "struct-of-arrays" (SoA) API
(Feature 064 backend, Feature 065 frontend adoption): three tiers (`buckets`/`ratios`/`details`)
returned as parallel-array JSON, plus a decoupled `Asset` endpoint for lazy thumbnail fetch,
consumed by an already-built virtualized grid (`PhotoGridVirtual.vue`, `@tanstack/vue-virtual`,
analytic WASM layout). This feature brings Timeline onto that same API shape and rendering path,
full-stack, in one effort — unlike album listing (naturally bounded by album size), Timeline's
photo tier fetches progressively, windowed by bucket, since a user's whole library can vastly
exceed any single album.

## Goals

- Timeline is servable through the existing v3 photo-tier routes (`buckets`/`ratios`/`details`)
  and the existing `Asset` endpoint, with `album_id = 'timeline'`, with zero new routes.
- The `ratios` tier gains bucket-windowed (`bucket_ids[]`) and single-photo (`photo_ids[]`)
  scoping, usable by any caller, with existing whole-scope album callers unaffected.
- Live (non-materialized) `bucket_id` computation for the global scope is SQL-pushdown bounded by
  the requested window, not by full-library row scans.
- Cache invalidation for Timeline's windowed data is scoped per bucket, so a save/edit anywhere in
  the library does not thrash every other cached bucket.
- The v8 Timeline frontend renders through the virtualized `PhotoGridVirtual.vue` mechanism,
  fetching bucket windows incrementally as the user scrolls, with layout-mode-correct placeholder
  sizing for not-yet-loaded buckets.
- Deep-linking (`/timeline/:date?/:photoId?`) resolves and scrolls to the correct position without
  requiring the whole library to be loaded first.
- The v2 Timeline API and rendering path remain fully intact and reachable, coexisting behind the
  same `is_struct_of_array_enabled` flag Feature 065 already introduced.

## Non-Goals

- Removing or deprecating the v2 `GET /api/Timeline`/`::dates`/`::init` routes, `TimelineController`,
  `app/Actions/Photo/Timeline.php`, or the old `PhotoThumbPanel.vue` rendering path — they stay,
  flag-gated, exactly like Feature 065 kept the v2 album-listing path.
- Changing Feature 064/065's existing whole-scope-at-once behavior for per-album photo listing —
  album callers of `ratios`/`details` are unaffected; windowing is additive and opt-in via new,
  optional params.
- A new sharing/visibility-changed cache-invalidation event. Album-visibility changes (made
  public/shared while a Timeline bucket's cache is warm) are accepted as TTL-bounded staleness,
  consistent with how `managed_cache_ttl` already bounds every other listing's worst case — this
  is a genuinely new (but bounded) exposure introduced by adding a cache where v2 Timeline had
  none, tracked as a Follow-up, not solved here.
- A `scope=own|shared` split (Feature 062's root-album-listing pattern) — Timeline has exactly one
  global scope, no per-owner split.
- Any change to `TagAlbum`/`PersonAlbum`'s existing live `bucket_id` computation path
  (`QueryPhotoBuckets::queryLiveBuckets()`/`QueryPhotoDetails::resolveLiveBucketPhotoIds()`'s
  current full-scan behavior) — those remain untouched; only a new `TimelineAlbum`-specific
  SQL-pushdown branch is added alongside them.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-066-01 | Timeline is modeled as `App\SmartAlbums\TimelineAlbum`, a new `AbstractAlbum` resolvable via `AlbumFactory::findAbstractAlbumOrFail('timeline')`, reachable through the existing v3 photo-tier routes with `album_id='timeline'`. | Every existing `PhotoChildrenController`/`PhotoAssetController` route resolves and serves Timeline data with no new route registration. | `SmartAlbumType::TIMELINE` accepted by `AlbumIDRule`. | Unknown `album_id` still 404s via existing `findAbstractAlbumOrFail()` behavior. | None. | Architecture decision, this feature. |
| FR-066-02 | `TimelineAlbum::photos()` reimplements `Timeline::do()`'s exact query: `PhotoQueryPolicy::applySearchabilityFilter()` with `origin: null`, no per-owner restriction, `include_nsfw: !config('hide_nsfw_in_timeline')`. | Visible photo set matches v2 Timeline's result set exactly for the same viewer/config. | Config keys read directly (`hide_nsfw_in_timeline`), never `BaseSmartAlbum`'s `hide_nsfw_in_smart_albums`/`enable_smart_album_per_owner`. | N/A (query construction only). | None. | Verified against `app/Actions/Photo/Timeline.php:59-65`. |
| FR-066-03 | `AlbumPolicy::canAccess()` gains an early `TimelineAlbum` branch reproducing `IdOrDatedTimelineRequest::authorize()`'s predicate: `(Auth::check() \|\| config('timeline_photos_public')) && config('timeline_page_enabled')`. | Guests can access when `timeline_photos_public` is on; authenticated users always can (subject to `timeline_page_enabled`). | Existing 14 smart albums' `canSee()` routing is untouched (branch is additive, checked first). | Access denied (403) when `timeline_page_enabled` is off, or guest with `timeline_photos_public` off. | None. | Verified against `app/Policies/AlbumPolicy.php:119-128`, `app/Http/Requests/Timeline/IdOrDatedTimelineRequest.php`. |
| FR-066-04 | `ResolvesPhotoSource::resolveEffectiveSorting()` gains a `TimelineAlbum` branch: sort column from `timeline_photos_order`, restricted to `{CREATED_AT, TAKEN_AT}` (fallback `TAKEN_AT`), direction hardcoded `DESC`. | Row order matches v2 Timeline's order exactly. | Any other configured sort column silently falls back to `TAKEN_AT`, matching `Timeline.php:54-57`. | N/A. | None. | Verified against `app/Actions/Photo/StructOfArrays/ResolvesPhotoSource.php:100-107`. |
| FR-066-05 | `GET /api/v3/Albums/timeline/Photos/buckets` returns `{bucket_ids[], counts[], labels[], bucketable:true}` for the whole visible library, computed via a driver-specific SQL `GROUP BY` on truncated date (not a PHP row scan). Replaces `GET /api/Timeline::dates` for v3 consumers. | Buckets response time is bounded by distinct-bucket count, not photo count. | N/A. | N/A. | None. | This feature; NFR-066-01. |
| FR-066-06 | `ratios` tier (`GET /api/v3/Albums/{album_id}/Photos`) gains optional, mutually exclusive `bucket_ids[]`/`photo_ids[]` params. Omitting both preserves today's whole-scope behavior unchanged. | Album callers unaffected (byte-identical response for unchanged requests); Timeline callers get exactly the requested bucket(s)/photo(s). | `GetPhotoRatiosRequest` validates `sometimes\|array`, each param `prohibits` the other. | Both provided → 422. | None. | This feature; mirrors `GetPhotoDetailsRequest.php:65-82`. |
| FR-066-07 | For a `TimelineAlbum` source, `bucket_ids[]`-scoped `ratios`/`details` queries resolve via `PhotoBucketComputer::bucketDateRange()` (SQL date-range pushdown), not the existing PHP full-scan (`queryLiveBuckets()`/`resolveLiveBucketPhotoIds()`). `TagAlbum`/`PersonAlbum` keep the existing full-scan path unchanged. | Query cost bounded by requested window size, not library size. | N/A. | N/A. | None. | NFR-066-01. |
| FR-066-08 | `GET /api/v3/Asset/timeline/{photo_id}/{size_variant}` is served by the existing, unmodified `PhotoAssetController`/`GetPhotoAssetRequest` — `isPhotoOfAlbum()`'s generic fallback (`$album->photos()->whereKey($photo_id)->exists()`) already works for any `AbstractAlbum`. | Thumbnail requests for any Timeline-visible photo succeed with zero backend change. | Standard `AlbumPolicy::CAN_ACCESS` + membership check, unchanged. | Photo not in Timeline's visible set → same 403/404 as any other album. | None. | Verified against `app/Http/Requests/Photo/GetPhotoAssetRequest.php`. |
| FR-066-09 | Cache entries for Timeline's `ratios`/`details` tiers are tagged per requested bucket (`photoListingBucketTag('timeline', $bucketId)`) in addition to the existing coarse `photoListingTag('timeline')`; `buckets` tier keeps only the coarse tag. | A photo save/move evicts only its own bucket's fine tag (+ cheap coarse tag), leaving other cached buckets warm. | N/A. | N/A. | None. | NFR-066-04. |
| FR-066-10 | `ManagedCachePhotoListingInvalidator::handlePhotoSaved()`/`handlePhotoMoved()` gain a Timeline-aware branch: resolve the touched photo's current bucket, evict its fine tag + the coarse tag. `handlePhotoDeleted()` falls back to coarse-only eviction (no photo_ids available post-delete). | Routine saves/moves elsewhere in the library don't invalidate an unrelated bucket's cache. | N/A. | N/A. | None. | This feature. |
| FR-066-11 | A new `resources/js/stores/TimelineState.ts` fetches `buckets` eagerly (whole-scope, once) and `ratios` incrementally per bucket window via `requestBucketWindow()`, deduping in-flight/loaded buckets. | Scrolling the Timeline grid triggers fetches only for buckets entering the prefetch range. | Backend `features.struct-of-array` gate + frontend `is_struct_of_array_enabled` gate, mirroring Feature 065. | Flag off → old `TimelineState.ts`/`PhotoThumbPanel.vue` path used, unchanged. | None. | This feature. |
| FR-066-12 | `PhotoGridVirtual.vue` renders Timeline via a per-bucket layout cache; not-yet-loaded buckets get a placeholder height computed by the same WASM layout primitive fed uniform `1.0`-ratio input (not an arbitrary constant), and reflow to real geometry once their `ratios` window resolves. | No layout-mode-incorrect placeholder heights; smooth reflow as buckets load. | N/A. | N/A. | None. | This feature; NFR-066-05. |
| FR-066-13 | Deep-linking to `/timeline/:date?/:photoId?` resolves the target photo's bucket via `ratios?photo_ids[]=<id>`, eagerly loads that bucket window (± neighbors), computes an estimated scroll offset from cached/placeholder preceding-bucket heights, scrolls, and triggers bidirectional prefetch so the estimate self-corrects as the user scrolls back up. A bare `/timeline/:date` link skips photo resolution (date is already the bucket-id string). | Deep links land at (or converge quickly to) the correct position without loading the whole library first. | N/A. | Photo/date not found → existing not-found handling, unchanged shape. | None. | This feature. |
| FR-066-14 | `TimelineDates.vue`'s side quick-nav rail consumes the `buckets` tier response (`{bucket_ids, counts, labels}`) instead of `GET /api/Timeline::dates`'s `{time_date, format}[]`; year-grouping/emit/scroll-target logic is mechanically adapted to the new field names. | Side rail behavior matches v2 visually; clicking an entry loads/scrolls to that bucket. | N/A. | N/A. | None. | This feature. |
| FR-066-15 | The v2 Timeline routes, controller, action, and `PhotoThumbPanel.vue` rendering path remain fully intact and reachable when the SoA flag is off. | Flag off → v2 behavior byte-identical to pre-feature. | N/A. | N/A. | None. | Mirrors Feature 065's own coexistence precedent. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-066-01 | Live `bucket_id` computation for `TimelineAlbum` (buckets grouping, bucket-windowed ratios/details) must be SQL-pushdown bounded by the requested window/distinct-bucket-count, never a full-library PHP row scan. | Timeline's scope is potentially far larger than any single album — the existing `TagAlbum`/`PersonAlbum` live-scan pattern (proven safe at album scale) would not be safe at library scale. | Manual/scoped timing check against a large local fixture; query plan review (no per-row PHP loop over the full candidate set). | `PhotoBucketComputer::bucketDateRange()`. | This feature; `[[feedback_avoid_carbon_server_side]]` (no Carbon in the new helper). |
| NFR-066-02 | No Carbon usage in any new/changed server-side Timeline code (`bucketDateRange()`, the moved per-driver `GROUP BY` truncation logic). | Direct owner preference, already enforced elsewhere in this codebase's bucket-label code (`AlbumBucketController::computeLabels()`'s rewrite history). | Code review / grep for `Carbon`/`DateTime` imports in touched files. | `[[feedback_avoid_carbon_server_side]]`. | Direct owner instruction (memory). |
| NFR-066-03 | `ratios`/`details` whole-scope behavior for existing (non-Timeline) callers is byte-identical before and after this feature — the new `bucket_ids[]`/`photo_ids[]` params are additive and optional. | Feature 064/065 are shipped, tested, in production use — zero regression tolerance. | Existing `PhotoRatiosV3Test`/`PhotoDetailsV3Test` suites pass unmodified plus new scoped-param test cases. | `tests/Feature_v3/Photo/PhotoRatiosV3Test.php`. | This feature. |
| NFR-066-04 | A cache-affecting write to one bucket's photos must not evict another bucket's cached `ratios`/`details` entries. | Windowed caching is pointless if any single save thrashes the whole timeline's cache. | New invalidator tests asserting untouched-bucket cache entries survive a same-request unrelated-bucket save. | `CacheKeyProvider`, `ManagedCachePhotoListingInvalidator`. | This feature. |
| NFR-066-05 | Placeholder heights for not-yet-loaded buckets must be computed via the real layout primitive (uniform-ratio input), not a fixed/guessed constant, to avoid visible layout jumps disproportionate to actual content. | User-visible scroll jank is a real regression risk for a feature whose whole point is smooth infinite scrolling. | Manual browser verification (no automated precedent for this class of check in this codebase, per Feature 065's own testing-approach note). | `analyticPhotoLayout.ts`, WASM layout primitives. | This feature. |
| NFR-066-06 | Zero behavior change to the v2 Timeline path when `features.struct-of-array`/`is_struct_of_array_enabled` is off. | Coexistence requirement, mirrors Feature 065. | Manual/regression check of `/timeline` with the flag off. | Feature flag machinery already shipped by Feature 065. | Mirrors Feature 065's own NFR precedent. |

## UI / Interaction Mock-ups

```
Timeline (flag on, virtualized)
┌─────────────────────────────────────────────┬───────┐
│  ▾ March 2026                    (sticky)    │  2026 │
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐                 │  ├─Mar│
│  │▓▓▓▓│ │▓▓▓▓│ │░░░░│ │░░░░│  ← real / placeholder │  ├─Feb│
│  └────┘ └────┘ └────┘ └────┘                 │  ├─Jan│
│  ▾ February 2026                             │  2025 │
│  ┌────┐ ┌────┐ ┌────┐                        │  ├─Dec│
│  │░░░░│ │░░░░│ │░░░░│  ← placeholder (bucket  │  ├─Nov│
│  └────┘ └────┘ └────┘    not yet fetched)     │   ...  │
└─────────────────────────────────────────────┴───────┘
  scrolling down → forward prefetch triggers fetch for
  "February 2026" bucket before it enters the viewport;
  placeholder tiles (░) reflow to real geometry (▓) once
  its `ratios` window resolves, no jump for already-passed
  content above.
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-066-01 | `GET /Albums/timeline/Photos/buckets` returns the correct library-wide `{bucket_ids, counts, labels}` for an authenticated user's full visible photo set. |
| S-066-02 | Same request as guest, `timeline_photos_public=true` → succeeds; `timeline_photos_public=false` → 403. |
| S-066-03 | `timeline_page_enabled=false` → 403 regardless of auth state. |
| S-066-04 | `GET /Albums/{real_album_id}/Photos` (whole-scope, no new params) returns byte-identical output to pre-feature — regression guard. |
| S-066-05 | `GET /Albums/timeline/Photos?bucket_ids[]=2026-03` returns exactly that bucket's photos, correctly ordered, `bucket_ids` field all `"2026-03"`. |
| S-066-06 | `GET /Albums/timeline/Photos?bucket_ids[]=2026-03&photo_ids[]=abc` → 422 (mutually exclusive). |
| S-066-07 | `GET /Albums/timeline/Photos?photo_ids[]=<id>` resolves exactly that photo, including its correct `bucket_ids[0]`. |
| S-066-08 | `GET /Albums/timeline/Photos/details?bucket_id=2026-03` returns full detail rows for that bucket only, via SQL-pushdown (not a full-library scan). |
| S-066-09 | `GET /Asset/timeline/{photo_id}/thumb` serves the thumbnail for any Timeline-visible photo, 403 for a photo outside the current viewer's visibility. |
| S-066-10 | Saving/editing a photo in bucket `2026-03` evicts only that bucket's fine cache tag (+ coarse `buckets` tag); a previously warmed `2026-02` cache entry survives. |
| S-066-11 | Deleting a photo evicts the coarse tag only (documented tradeoff — no photo_ids available to resolve a fine bucket post-delete). |
| S-066-12 | Sort config set to an unsupported column (e.g. `TITLE`) → silently falls back to `TAKEN_AT`, matching v2. |
| S-066-13 | `hide_nsfw_in_timeline=true` hides NSFW photos from all three tiers; unrelated `hide_nsfw_in_smart_albums` setting has no effect on Timeline. |
| S-066-14 | Frontend: scrolling the virtualized grid triggers `requestBucketWindow()` only for buckets entering the overscan range; already-loaded buckets are not re-fetched. |
| S-066-15 | Frontend: a not-yet-loaded bucket renders at a placeholder height matching its real layout-mode packing for uniform `1.0` ratios; once loaded, height/content reflow with no visible jump for content already scrolled past. |
| S-066-16 | Frontend: `/timeline/2026-03/<photoId>` deep link scrolls to and loads the correct bucket, opens the lightbox on the target photo. |
| S-066-17 | Frontend: `is_struct_of_array_enabled` off → old `TimelineState.ts`/`PhotoThumbPanel.vue` path used, v2 backend routes hit, unchanged from pre-feature behavior. |
| S-066-18 | `TimelineDates.vue` side rail, populated from the `buckets` tier, groups by year and scroll-jumps to the clicked bucket, matching v2's visual behavior. |

## Test Strategy

- **Core (query/action layer):** New/extended PHPUnit coverage for `TimelineAlbum::photos()`,
  `ResolvesPhotoSource`'s `TimelineAlbum` branches, `PhotoBucketComputer::bucketDateRange()`
  (pure function, unit-testable), `QueryPhotoBuckets`/`QueryPhotoRatios`/`QueryPhotoDetails`'s new
  SQL-pushdown paths — scoped `--filter=` runs against this repo's existing SQLite test setup, per
  `[[feedback_no_full_test_suite]]`.
- **REST:** New `tests/Feature_v3/Timeline/` suite (mirrors `tests/Feature_v3/Photo/PhotoBucketsV3Test.php`'s
  structure) covering S-066-01 through S-066-13; existing `PhotoRatiosV3Test`/`PhotoDetailsV3Test`
  regression-run unmodified to prove NFR-066-03.
- **Cache:** New tests mirroring `tests/Unit/.../ManagedCachePhotoListingInvalidatorTest.php`'s
  existing structure, asserting fine-vs-coarse tag eviction scoping (NFR-066-04).
- **UI (JS):** `npm run check` (vue-tsc + eslint) for all changed/new frontend files; unit tests
  for the pure `computeTimelineBucketLayout()`/placeholder-sizing functions where practical.
  Scroll-behavior/reflow/deep-link-landing scenarios (S-066-14..16) require manual browser
  verification — no dev environment is available in the authoring session
  (`[[feedback_no_mariadb_mysql_access]]`), flagged as pending exactly like Feature 063/065.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` updated for the two extended request
  shapes (`ratios`'s new params); no OpenAPI/telemetry snapshots exist in this project's tooling.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-066-01 | `App\SmartAlbums\TimelineAlbum` — new `AbstractAlbum`/`BaseSmartAlbum` subclass, `photos()` fully overridden. | Backend |
| DO-066-02 | `GetPhotoRatiosRequest` gains `bucketIds(): ?array`, `photoIds(): ?array` accessors, `sometimes\|array` + `prohibits`-each-other validation. | Backend |
| DO-066-03 | `PhotoBucketComputer::bucketDateRange(string $bucketId, TimelinePhotoGranularity $granularity): array{0:string,1:string}` — pure, Carbon-free date-range helper. | Backend |
| DO-066-04 | `CacheKeyProvider::photoListingBucketTag(string $albumId, string $bucketId): string` / `photoRatiosScopeDigest(?array $bucketIds, ?array $photoIds): string`. | Backend |
| DO-066-05 | `TimelineState.ts` — incremental Pinia store: `bucketsV3`, per-bucket load-state map, flat append-only `tiles`/`ratios`, `requestBucketWindow(bucketIds)`. | Frontend |
| DO-066-06 | `computeTimelineBucketLayout()` — boundary/layout computation valid mid-load (unlike `computeBucketBoundaries()`, which asserts full-load). | Frontend |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-066-01 | REST `GET /api/v3/Albums/timeline/Photos/buckets` | Whole-library bucket counts/labels. | Reuses Feature 064's route verbatim (API-064-01), `album_id='timeline'`. Replaces `GET /api/Timeline::dates` for v3 consumers. |
| API-066-02 | REST `GET /api/v3/Albums/timeline/Photos[?bucket_ids[]=...\|&photo_ids[]=...]` | Bucket-windowed or single-photo ratios. | Reuses Feature 064's route (API-064-02); params are new, optional, additive. |
| API-066-03 | REST `GET /api/v3/Albums/timeline/Photos/details[?bucket_id=...\|&photo_ids[]=...]` | On-demand rich detail fetch. | Reuses Feature 064's route (API-064-03) and existing param shape unmodified. |
| API-066-04 | REST `GET /api/v3/Asset/timeline/{photo_id}/{size_variant}` | Thumbnail/pixel fetch. | Reuses Feature 056's route unmodified. |

### CLI Commands / Flags

None.

### Telemetry Events

None — mirrors Feature 063/065's own "no telemetry introduced" precedent.

### Fixtures & Sample Data

No new committed fixtures. A locally-generated large (tens-of-thousands-of-photos) library is
used for manual scale verification of the SQL-pushdown bucket computation and windowed-fetch
scroll behavior, mirroring Feature 063/065's own precedent for uncommitted scale fixtures.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-066-01 | Virtualized Timeline grid, buckets loaded on-scroll | Flag on, scrolling down → buckets fetch just ahead of the viewport, placeholders reflow to real tiles. |
| UI-066-02 | Placeholder (not-yet-loaded) bucket | Bucket outside the prefetch range → layout-mode-correct placeholder height, no tiles rendered. |
| UI-066-03 | Deep-link landing | `/timeline/:date/:photoId` → grid scrolls to (estimated, then corrected) target bucket, lightbox opens on the target photo. |
| UI-066-04 | Side quick-nav rail | `TimelineDates.vue`, populated from the `buckets` tier, clicking an entry scrolls/loads that bucket. |
| UI-066-05 | v2 fallback | Flag off → old paginated `TimelineState.ts`/`PhotoThumbPanel.vue` path, unchanged. |

## Telemetry & Observability

None — no telemetry events are introduced by this feature.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — document `ratios`'s new `bucket_ids[]`/`photo_ids[]`
  params and note Timeline as a consumer of the existing v3 photo-tier routes via `album_id='timeline'`.
- `docs/specs/4-architecture/knowledge-map.md` — record `TimelineAlbum`, the bucket-windowed fetch
  pattern, and the fine/coarse cache-tag scheme.
- `docs/specs/3-reference/frontend-gallery.md` — document the incremental `TimelineState.ts`
  store, placeholder-sizing/prefetch-on-scroll mechanism, and deep-link resolution flow.
- `docs/specs/4-architecture/roadmap.md` — add Feature 066's entry.

## Fixtures & Sample Data

No new committed fixtures (see Fixtures & Sample Data above under the Interface Catalogue).

## Spec DSL

```
domain_objects:
  - id: DO-066-01
    name: TimelineAlbum
  - id: DO-066-02
    name: GetPhotoRatiosRequest (extended)
    fields:
      - name: bucket_ids
        type: array<string>
        constraints: "optional, prohibits photo_ids"
      - name: photo_ids
        type: array<string>
        constraints: "optional, prohibits bucket_ids"
  - id: DO-066-03
    name: PhotoBucketComputer::bucketDateRange
  - id: DO-066-04
    name: CacheKeyProvider photo-listing bucket tag
  - id: DO-066-05
    name: TimelineState.ts
  - id: DO-066-06
    name: computeTimelineBucketLayout
routes:
  - id: API-066-01
    method: GET
    path: /api/v3/Albums/timeline/Photos/buckets
  - id: API-066-02
    method: GET
    path: /api/v3/Albums/timeline/Photos
  - id: API-066-03
    method: GET
    path: /api/v3/Albums/timeline/Photos/details
  - id: API-066-04
    method: GET
    path: /api/v3/Asset/timeline/{photo_id}/{size_variant}
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-066-01
    description: Virtualized Timeline grid, buckets loaded on-scroll
  - id: UI-066-02
    description: Placeholder (not-yet-loaded) bucket
  - id: UI-066-03
    description: Deep-link landing
  - id: UI-066-04
    description: Side quick-nav rail
  - id: UI-066-05
    description: v2 fallback
```

## Appendix

### Decision Cards

**Q-066-01 — Model Timeline as a new `AbstractAlbum` (`TimelineAlbum`), or a wholly separate
parallel query/controller stack?**

- **Context:** The existing v3 photo-tier stack (`ResolvesPhotoSource`, `QueryPhotoBuckets`/
  `Ratios`/`Details`, `PhotoChildrenController`, `PhotoAssetController`) already handles four
  `AbstractAlbum` kinds uniformly (`Album`, `TagAlbum`, `PersonAlbum`, `BaseSmartAlbum`). A
  built-in smart album (`RecentAlbum`) already demonstrates a cross-album, library-wide photo
  scope resolvable by string id via `AlbumFactory`.
- **Options considered:** (A) Reuse `BaseSmartAlbum::photos()` unmodified via the `smart_condition`
  mechanism. (B) New `TimelineAlbum extends BaseSmartAlbum` with `photos()` fully overridden,
  reusing everything else. (C) Fully separate Timeline-specific controller/actions, no
  `AbstractAlbum` involvement.
- **Decision:** (B). Option (A) was verified unsound: `BaseSmartAlbum::photos()` hardcodes
  `enable_smart_album_per_owner`/`hide_nsfw_in_smart_albums` gates Timeline has never used (its
  own `hide_nsfw_in_timeline` is a distinct, longstanding config key), and `AlbumPolicy::canSee()`
  requires `may_upload` rights, which contradicts Timeline's actual
  `timeline_photos_public`/`timeline_page_enabled`-driven access rule. Option (C) would duplicate
  ~700 lines of proven, tested query/cache/request logic for ~10% actual divergence (visibility
  predicate, sort-config keys, access policy). Option (B) isolates exactly the four verified
  mismatches into small, additive, surgical edits (`TimelineAlbum.php`, one `AlbumPolicy` branch,
  one `ResolvesPhotoSource` branch, one `SmartAlbumType`/`AlbumFactory` registration) while reusing
  the dedup-safe `BaseSmartAlbum` branch in `ResolvesPhotoSource::resolvePhotoQuery()` unchanged,
  and the `Asset` endpoint unchanged entirely.
- **Resolution date:** 2026-09-11.
- **Spec impact:** FR-066-01 through FR-066-04, FR-066-08.

**Q-066-02 — Whole-scope-at-once `ratios` fetch (matching the existing album pattern), or
bucket-windowed?**

- **Context:** Album SoA (Feature 064/065) fetches an album's entire `ratios` tier in one request,
  accepted "for now" since albums are naturally bounded. Timeline has no such bound — a user's
  full library can be orders of magnitude larger than any single album.
- **Decision:** Bucket-windowed. `ratios` gains optional `bucket_ids[]`/`photo_ids[]` scoping,
  backward-compatible (omitted → today's whole-scope behavior, unaffecting existing album callers).
  This is new capability, not previously present anywhere in the v3 API, and required corresponding
  new work in cache-key scoping (fine per-bucket tags), frontend incremental fetch orchestration,
  and placeholder/reflow rendering — all captured in this spec's FRs/NFRs above.
- **Resolution date:** 2026-09-11 (decided directly by the feature owner before drafting).
- **Spec impact:** FR-066-06, FR-066-07, FR-066-09, FR-066-10, FR-066-11, FR-066-12, NFR-066-01, NFR-066-04, NFR-066-05.

**Q-066-03 — One combined feature doc, or a 066/067 backend/frontend split (mirroring 061/063,
064/065)?**

- **Context:** Those precedent splits exist because their backend and frontend halves were
  genuinely separate efforts shipped at different times (backend marked Complete before frontend
  adoption began).
- **Decision:** One combined doc (this one). The feature owner explicitly scoped this Timeline
  effort as full-stack, done together, not phased — splitting the doc would misrepresent that as
  two separable efforts and require two `spec.md`/`plan.md`/`tasks.md` trees kept in lockstep for
  no benefit. The Increment Map in `plan.md` still orders backend increments before frontend ones.
- **Resolution date:** 2026-09-11.
- **Spec impact:** Doc structure only; no FR/NFR impact.
