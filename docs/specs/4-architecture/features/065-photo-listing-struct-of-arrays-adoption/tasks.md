# Feature 065 Tasks – Photo Listing Struct-of-Arrays Frontend Adoption

_Status: Implemented (code-complete; manual browser verification and documentation tasks pending — no dev environment available this session)_
_Last updated: 2026-09-06 (implementation pass — 39/53 tasks code-complete, deviations noted inline)_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`NG`), and scenario IDs (`S-065-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Service + store: v3 fetch, boundary reuse, stale-guard, cache, dispatcher

- [x] T-065-01 – Write `resources/js/services/photo-children-v3-service.ts` (`getBuckets`/`getRatios`/`getDetails`, axios-cache-interceptor cache ids) (F-065-01).
  _Intent:_ Thin service layer over Feature 064's three routes, mirroring `album-children-v3-service.ts`'s shape exactly.
  _Verification commands:_
  - `npm run check`

- [x] T-065-02 – Write `resources/js/v8/utils/adaptPhotoTile.ts` (`adaptPhotoTile(row): AdaptedPhotoTile`) (F-065-04).
  _Intent:_ Pure adapter from one `ratios` row to the tile shape `PhotoThumbVirtual.vue`/`contextMenu.ts` need; omitted conditional fields stay `undefined`, never a fabricated default.
  _Verification commands:_
  - `npm run check`
  _Note (deviation found during implementation):_ `PhotosState.ts.photos` turned out to be strictly typed `PhotoResource[]` (not a lean/thumb shape the way albums' `ThumbAlbumResource` already was) — every existing consumer (`PhotoThumb.vue`, `PhotoState.ts`'s lightbox getters) expects that exact shape. Rather than widen the type, `adaptPhotoTile()` synthesizes a genuinely `PhotoResource`-shaped object (`AdaptedPhotoTile` is a type alias for `PhotoResource`, not a new interface) with safe placeholders for detail-tier fields — this is what let `PhotoState.ts` need zero getter changes (see T-065-32's note).

- [x] T-065-03 – Add `photoBucketsV3`/`photoBoundariesV3`/`photoBucketableV3` fields to `AlbumState.ts`, named distinctly from Feature 063's existing album-child `bucketsV3`/`boundariesV3`/`bucketableV3` (F-065-03).
  _Intent:_ One store, two independent bucket-tier datasets (album-children vs. this album's own photos) coexisting without collision.
  _Verification commands:_
  - `npm run check`

- [x] T-065-04 – Write `loadPhotosV3()` on `AlbumState.ts`: parallel tier 1+2 fetch, `computeBucketBoundaries()` reuse (imported from `albumBucketBoundaries.ts` as-is), adapt via `adaptPhotoTile()` into `PhotosState.ts.photos` (F-065-03).
  _Intent:_ The core fetch-and-adapt action; writes into the existing `photos` field, no new tile-array field.
  _Verification commands:_
  - `npm run check`
  _Note:_ Confirm `computeBucketBoundaries()`'s signature genuinely needs no photo-specific change before importing it — it should already be generic over any `{bucket_ids, counts}` + flat-length input per Feature 063's own design.

- [x] T-065-05 – Write the centralized computed `isPhotoSoaActive` getter on `AlbumState.ts` (flag on AND regular-`Album` parent AND no active tag/person filter, F-065-02, Q-065-05), then `loadPhotosAuto()` reading it to dispatch, and swap every fresh-navigation `loadPhotos()` call site to `loadPhotosAuto()` (NG4, NG5).
  _Intent:_ Single source of truth read by this dispatcher AND by I4's render dispatcher, I7/I8's on-demand details-fetch gate, and I9's drag-select — get this right first since everything downstream depends on it (Q-065-05, Option A).
  _Verification commands:_
  - `npm run check`

- [x] T-065-06 – Wire the `requestedAlbumId`-capture-and-compare guard into `loadPhotosV3()` (F-065-24).
  _Intent:_ Reuse the existing guard pattern; no new generation counter.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-07 – Manual verification: happy path (flag on, regular Album, no filter) across a small fixture album; flag-off parity; tag/person-filter fallback; non-Album-parent fallback; `OWNER_ID`-sort `bucketable:false`; defensive count-mismatch fallback; empty album; non-admin curated visibility; rapid double-navigation stale-guard; client cache hit on revisit (S-065-06, S-065-07, S-065-08, S-065-09, S-065-10, S-065-11, S-065-12, S-065-13, S-065-28, S-065-29).
  _Intent:_ Confirm I1's entire fetch/dispatch/guard/cache surface before building any renderer against it.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I2 – Analytic per-mode layout function

- [x] T-065-08 – Confirm the exact parameter signature of `justified()`/`square()`/`masonry()`/`grid()` (`resources/js/v8/layouts/wasmLayouts.ts`) — each should accept a plain ratios/count array plus width/target-size/spacing with no DOM dependency, though the four may not share one identical shape (precondition check).
  _Intent:_ Verify the underlying primitives genuinely have no DOM dependency, and identify any per-mode parameter differences, before writing a unified dispatch function against them.
  _Verification commands:_
  - `npm run check`; manual inspection of each primitive's TypeScript signature.

- [x] T-065-09 – Write `resources/js/v8/composables/photo/analyticPhotoLayout.ts`, dispatching to the matching WASM primitive per bucket run for `justified`/`square`/`masonry`/`grid` (F-065-07).
  _Intent:_ One shared, DOM-free layout function covering four of the five modes; per-mode adapter glue is expected and acceptable if the four signatures diverge (per T-065-08's findings).
  _Verification commands:_
  - `npm run check`
  _Note:_ Implemented as a small composed set of functions (`computeBucketLayout()` per-bucket → `computePhotoLayout()` whole-album concatenation → `buildPhotoChunks()` virtualization grouping → `computeVisiblePhotoLayout()` the rating-filter-aware wrapper both the renderer and drag-select call) rather than one single `computeAnalyticPhotoLayout()` entry point — the four signatures turned out to be *identical* (T-065-08), so no per-mode adapter glue was needed at all; `computeBucketLayout()` is a one-line dispatch table.

- [x] T-065-10 – Add `list` mode's trivial fixed-height, full-width row branch to `computeAnalyticPhotoLayout()` — no WASM call (F-065-07).
  _Intent:_ The one bespoke, non-WASM case.
  _Verification commands:_
  - `npm run check`

- [x] T-065-11 – Wire `computeAnalyticPhotoLayout()` to read exactly the same config values (target row height/tile size/column width/spacing) that `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts` already read today, per mode (F-065-11).
  _Intent:_ Zero behavioural change to how an admin's existing layout configuration is interpreted.
  _Verification commands:_
  - `npm run check`

- [x] T-065-12 – Confirm `computeAnalyticPhotoLayout()` treats a `ratio=1` fallback value as ordinary input for every mode, never special-cased or excluded (F-065-25).
  _Intent:_ A photo mid-processing (no size variant yet) must not break layout for any mode.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-13 – Manual side-by-side visual diff: each mode's `computeAnalyticPhotoLayout()` output vs. today's v2 DOM-measured rendering of the same fixture album, across all five modes including the `ratio=1` fallback case (S-065-27).
  _Intent:_ Confirms Decision Card Q-065-01/Q-065-04's "identical geometry, any mode" claim holds in practice.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I3 – Chunked virtualization mechanism

- [x] T-065-14 – Implement top-to-bottom bucket concatenation (header pseudo-box + bucket boxes, Y-offset by cumulative height) using I1's boundaries, producing one page-relative coordinate space for the whole album (F-065-08).
  _Intent:_ A single flat, ordered box list spanning every bucket, regardless of mode.
  _Verification commands:_
  - `npm run check`

- [x] T-065-15 – Implement chunking: group the concatenated box list into fixed-target-size chunks (contiguous tile runs, splitting large buckets, never splitting a header from its bucket's first content), each carrying an exact, precomputed height (F-065-08).
  _Intent:_ A pure virtualization bookkeeping unit, unrelated to any visual row — this is what makes `masonry`/`grid` virtualizable via a row-indexed virtualizer.
  _Verification commands:_
  - `npm run check`

- [x] T-065-16 – Wire `useWindowVirtualizer` fed the chunk list's exact (never estimated) heights.
  _Intent:_ No remeasurement/guessing — every chunk's height is already known before it mounts.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-17 – Manual scroll-smoothness check across chunk boundaries, specifically for `masonry`/`grid` (Risks in plan.md).
  _Intent:_ Confirm chunking a non-row-uniform layout doesn't produce a visible jump at chunk boundaries.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I4 – Renderer components + dispatcher wiring

- [x] T-065-18 – Fork `PhotoThumb.vue` → `resources/js/v8/components/gallery/albumModule/Virtualized/PhotoThumbVirtual.vue` (cover via `<Thumb :album-id :photo-id type="thumb">`, badges/overlay/tags reused unchanged from `AdaptedPhotoTile`'s fields, absolute-positioned from its precomputed box) (F-065-10, NG11). Deliberately omit v2's hover-triggered face-prefetch (`prefetchFaces()`) — `AdaptedPhotoTile` has no `face_count` field, per Q-065-06.
  _Intent:_ Grid-shaped tile component for `justified`/`square`/`masonry`/`grid`.
  _Verification commands:_
  - `npm run check`

- [x] T-065-19 – Fork the existing list-row component → `PhotoListItemVirtual.vue`, same `<Thumb>` mechanism (F-065-10). Deliberately omit face-prefetch (same as T-065-18) and the file-size metadata chip (`photo.preformatted.filesize` has no tier-2 equivalent) — both accepted regressions per Q-065-06.
  _Intent:_ Row-presentation component for `list`.
  _Verification commands:_
  - `npm run check`

- [x] T-065-20 – Write `PhotoGridVirtual.vue` — single component for all five modes, mounts via I3's `useWindowVirtualizer`, picks `PhotoThumbVirtual.vue` vs. `PhotoListItemVirtual.vue` per tile by mode, two-layer sticky-header pattern (real header content + pinned overlay), `bucketableV3:false` single-flat-chunk-sequence fallback (F-065-09).
  _Intent:_ The one unified renderer, reusing Feature 063's outer scaffolding decisions unchanged.
  _Verification commands:_
  - `npm run check`
  _Note (deviation — container width):_ Uses `useElementSize()` (a real, reactive measurement of this component's own outer wrapper `<div>`) rather than a pure viewport-minus-assumed-padding analytic formula (`albumTileWidth.ts`'s approach). `albumTileWidth.ts`'s own doc comment explains it avoided a live measurement because observing an element whose height runs into the *hundreds of thousands of px* (a large virtualized grid) can stall `ResizeObserver`'s callback queue — but that concern is about the element's *height*, not width, and specifically about observing the huge inner content div. `PhotoGridVirtual.vue` observes its bounded-height *outer* wrapper instead, which doesn't hit that failure mode; not calibrating this against `UContainer`'s actual default padding/max-width (unknown without a running dev server) was judged higher-risk than a cheap, accurate live measurement. Flagged for visual confirmation once a dev environment exists.

- [x] T-065-21 – Per-tile date subtitle (F-065-21).
  _Intent:_ A tile's overlay date renders something reasonable.
  _Verification commands:_
  - `npm run check`
  _Note (deviation/simplification):_ `date_format_photo_thumb` is not currently exposed to the frontend by any typed config resource (`AlbumConfig`/`RootConfig`/`PhotoLayoutConfig` alike) — unlike `date_format_album_thumb`, which Feature 063 added to those classes for its own equivalent need. Exposing it would require the same kind of small backend Data-resource addition; not done in this pass. `adaptPhotoTile.ts`'s `formatDateForOverlay()` instead uses `Date.prototype.toLocaleDateString()` as a reasonable placeholder — a real, non-broken date renders, just not necessarily in the admin-configured format string. Full parity is a flagged follow-up, not a silent gap: see this function's own doc comment.

- [x] T-065-22 – Add `aria-posinset`/`aria-setsize` to `PhotoGridVirtual.vue`'s tile elements, reflecting true position/total, for any mode (F-065-23).
  _Intent:_ Accessibility parity regardless of mounted-subset size or layout mode.
  _Verification commands:_
  - `npm run check`

- [x] T-065-23 – Write `PhotoThumbPanelVirtual.vue`'s binary dispatch on `isPhotoSoaActive` (T-065-05, Q-065-05) — `true` → `PhotoGridVirtual.vue`; `false` → existing, untouched `PhotoThumbPanel.vue` — and wire it in at `PhotoThumbPanel.vue`'s existing mount point (F-065-09).
  _Intent:_ Reads the same centralized flag every other consumer reads, not a locally re-derived condition.
  _Verification commands:_
  - `npm run check`

- [x] T-065-24 – Confirm switching layout mode client-side re-runs `computeAnalyticPhotoLayout()` against already-loaded tiles with zero network refetch (F-065-12).
  _Intent:_ Layout-mode switch is a pure client-side re-render.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-25 – Manual verification: all five modes render correctly, bucketed (including non-date sort dimensions for `masonry`/`grid`) and flat-fallback cases, sticky-header scroll-through, DevTools element-count check against a five-figure-photo fixture, `HOUR`-granularity label rendering, `ratio=1` tile rendering, aria attribute spot check, layout-mode-switch zero-refetch check, and confirm the two accepted Q-065-06 regressions (no hover face-prefetch, no list-mode file-size chip) behave as documented rather than as silent bugs (S-065-01, S-065-02, S-065-03, S-065-04, S-065-05, S-065-10, S-065-11, S-065-26, S-065-27, S-065-30, S-065-31, S-065-33).
  _Intent:_ Full I4 renderer verification pass across all five modes.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I5 – Rating-filter-aware boundary recompute

- [x] T-065-26 – Rating-filter-aware boundary recompute (F-065-19).
  _Intent:_ Bucket headers/counts always reflect the currently-visible, filtered set; headers keep rendering while filtered (only counts change), not collapse to a flat section.
  _Verification commands:_
  - `npm run check`
  _Note (implementation + a bug caught and fixed during self-review):_ Uses the already-generic `filterBucketedTiles()` (from `albumBucketBoundaries.ts`) over `{photo, ratio}` pairs, not a direct `computeBucketBoundaries()` re-run, so the photo array and its parallel ratio array are filtered/re-chunked in lockstep — extracted into a new shared `computeVisiblePhotoLayout()` (`analyticPhotoLayout.ts`) so `dragAndSelect.ts`'s `getPhotoBoxesV3()` (T-065-40) reuses the identical logic rather than risking drift between rendering and hit-testing. An earlier version of this function suppressed headers entirely whenever the rating filter was active (wrong — FR-065-19 only asks for recomputed *counts*, not header suppression); caught and corrected before this task was marked done.

- [ ] T-065-27 – Manual verification: rating filter hides non-matching tiles and recomputes bucket boundaries correctly; a bucket filtered to zero visible photos omits its header entirely (S-065-14, S-065-15).
  _Intent:_ Confirm the one piece of this feature with no Feature 063 precedent behaves correctly at its edges.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I6 – On-demand `details` fetch + cache

- [x] T-065-28 – Write `mergePhotoDetail(photo, detail, i)` in `resources/js/v8/utils/adaptPhotoTile.ts` (F-065-06).
  _Intent:_ Merges one `details` row's fields directly into an already-adapted `PhotoResource` object, in place.
  _Verification commands:_
  - `npm run check`
  _Note (deviation):_ No separate `adaptPhotoDetail.ts` file or `AdaptedPhotoDetail` type — since the store shape is already a full `PhotoResource` (T-065-02's note), `details` fields are mutated directly onto the existing object rather than adapted into a standalone shape, so every reactive consumer (`PhotoState.ts` included) picks up the change with zero getter-level code.

- [x] T-065-29 – Add `loadPhotoDetails(ids)` to `AlbumState.ts` (not `PhotosState.ts`) — dedup via a `photoDetailsResolvedIds` map, defensive 300-id chunking, merges via `mergePhotoDetail()` into the matching `photosStore.photos` element in place (F-065-05).
  _Intent:_ The on-demand, bounded fetch-and-cache action; respects NFR-065-04's 300-id cap defensively.
  _Verification commands:_
  - `npm run check`
  _Note (deviation):_ No `detailsById` cache map — resolved details are written directly onto the existing `photos` array element (mutation in place), and `photoDetailsResolvedIds: Record<string, boolean>` (a plain dedup marker, not a data cache) lives on `AlbumState.ts` alongside `loadPhotosV3()`/`isPhotoSoaActive`, the store that already orchestrates every other v3 fetch for this feature.

- [x] T-065-30 – Wire `photoDetailsResolvedIds` clearing on `reset()`/navigation (NFR-065-09).
  _Intent:_ Bound memory across a multi-album browsing session.
  _Verification commands:_
  - `npm run check`

### I7 – Lightbox integration

- [x] T-065-31 – Wire the lightbox-open handler to check `isPhotoSoaActive` first (Q-065-05) — skip `loadPhotoDetails()` entirely when `false` — then call `loadPhotoDetails([openedId, ...up to 2 neighbors])` when `true` and not already cached (F-065-13).
  _Intent:_ Bounded, on-demand fetch at the point of need.
  _Verification commands:_
  - `npm run check`

- [x] T-065-32 – `PhotoState.ts`'s `imageViewMode`/`style`/`previousStyle`/`nextStyle`/`srcSetMedium` getters (F-065-13, F-065-06).
  _Intent:_ Lightbox shows *something* immediately, full detail once resolved.
  _Verification commands:_
  - `npm run check`
  _Note (deviation — zero getter changes needed):_ Because `photosStore.photos` already holds full `PhotoResource`-shaped objects (T-065-02) and `loadPhotoDetails()` mutates the matching element in place (T-065-29), these getters read `this.photo.size_variants`/etc. exactly as they always have — no `detailsById` lookup, no fallback branch, no loading-flag plumbing was needed in this file at all. A loading *indicator* for the lightbox UI (separate from getter logic) is not yet wired — left as remaining UI polish, not a data-layer gap.

- [x] T-065-33 – Confirm lightbox next/previous derives purely from the already-loaded `ids` sequence, never a `next_photo_id`/`previous_photo_id` field (F-065-15).
  _Intent:_ No dependency on fields Feature 064 deliberately never populates in this context.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-34 – Manual verification: lightbox opens with immediate partial render then full detail; rapid next/next/next navigation with cache reuse and stale-response guard; deleted-mid-session id closes/advances the lightbox correctly; opening the lightbox while `isPhotoSoaActive` is `false` never calls `loadPhotoDetails()` (S-065-16, S-065-17, S-065-21 lightbox-adjacent case, S-065-32).
  _Intent:_ Full I7 verification pass.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I8 – Edit-dialog integration

- [x] T-065-35 – Description-editor / EXIF-GPS panel gating (F-065-14).
  _Intent:_ Detail-dependent dialogs never render against stale/empty SoA data.
  _Verification commands:_
  - `npm run check`
  _Note (deviation — this codebase has no separate description-editor or EXIF-panel dialog):_ Both live inside one unified `PhotoEdit.vue` drawer, which `Album.vue`'s own `toggleDetails()` only ever opens once `photoStore.isLoaded` is already `true` — i.e. only reachable from an already-open lightbox. T-065-31's lightbox-open gating (`PhotoState.ts.load()`) therefore already covers this drawer's data needs transitively; no separate wiring was needed or added.

- [x] T-065-36 – (folded into T-065-35 — see its note; `PhotoEdit.vue` is one drawer, not separate description/EXIF dialogs).

- [x] T-065-37 – `PhotoLicenseDialog.vue` gating (F-065-14).
  _Verification commands:_
  - `npm run check`
  _Note:_ Explicitly wired in `AlbumPanel.vue`'s `photoCallbacks.toggleLicense` — this dialog (and the tag dialog, T-065-38) can open against a multi-selection *without* ever going through the lightbox, unlike `PhotoEdit.vue`, so it needed its own `isPhotoSoaActive`-gated `loadPhotoDetails(selectedPhotosIds)` call before delegating to the existing `toggleLicense()`.

- [x] T-065-38 – `PhotoTagDialog.vue` gating (F-065-14).
  _Verification commands:_
  - `npm run check`
  _Note:_ Same reasoning and wiring as T-065-37 (`photoCallbacks.toggleTag`). Confirmed safe against `useExistingTags.ts`'s `computed()`-based (not snapshot-on-mount) tag derivation, so the dialog reactively updates once the in-flight `loadPhotoDetails()` call resolves, even though it opens before that fetch completes.

- [ ] T-065-39 – Manual verification: each of the four dialogs shows a loading spinner then correct content on a cold cache, and renders instantly on a warm cache (lightbox already opened, or dialog reopened); opening any of the four while `isPhotoSoaActive` is `false` never calls `loadPhotoDetails()` (S-065-18, S-065-19, S-065-32).
  _Intent:_ Full I8 verification pass.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I9 – Drag-select geometry

- [x] T-065-40 – Add `getPhotoBoxesV3()` to `resources/js/composables/album/dragAndSelect.ts`, mirroring `getAlbumBoxesV3()`'s shape (anchor via `[data-photo-grid-root]`, re-derive boxes via `computeAnalyticPhotoLayout()`), mode-agnostic across all five modes (F-065-16).
  _Intent:_ One analytic hit-testing function, no per-mode DOM-query fallback.
  _Verification commands:_
  - `npm run check`

- [x] T-065-41 – Extend the existing flag-check call site (`dragAndSelect.ts:152`-adjacent) to read `isPhotoSoaActive` (Q-065-05) for photos, with no further per-mode branch (F-065-16).
  _Intent:_ Single dispatch point.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-42 – Manual verification: drag-select correctly selects intersecting photos including off-screen/unmounted ones, across all five modes (S-065-24, S-065-25).
  _Intent:_ Full I9 verification pass.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I10 – Mutation re-fetch + cache invalidation

- [x] T-065-43 – Upload/delete/move/copy re-fetch (F-065-20).
  _Intent:_ These mutations correctly re-fetch on the SoA path.
  _Verification commands:_
  - `npm run check`
  _Note (deviation — no per-call-site edits needed):_ Every one of these mutations already funnels through `Album.vue`'s existing `refresh()` → `albumStore.refresh()` → `reset()` + `load()` cascade (`emits("refresh")`), and `load()` was already updated (T-065-05's edit) to dispatch through `loadPhotosV3()` when `isPhotoSoaActive`. No individual mutation call site needed touching — confirmed by reading `Album.vue`'s existing `@refresh` wiring rather than assumed.

- [x] T-065-44 – Rename/star/highlight/rating (F-065-20).
  _Verification commands:_
  - `npm run check`
  _Note:_ Star/unstar/approve/rating already mutate the matching `photosStore.photos` element's fields directly in place (pre-existing v2 code in `AlbumPanel.vue`'s `photoCallbacks`/`useRating.ts`) — this continues to work unchanged for SoA-adapted tiles since they're genuinely `PhotoResource`-shaped (T-065-02), no refetch needed for these. Rename goes through the same `refresh()` cascade as T-065-43.

- [x] T-065-45 – Tag-edit/license-edit/apply-renamer (F-065-20).
  _Verification commands:_
  - `npm run check`
  _Note:_ Tag/license edits go through `Album.vue`'s `refreshInPlace()` → `albumStore.reloadLoadedPhotos()`, which was extended (not a new action) to dispatch to `loadPhotosV3()` when `isPhotoSoaActive`, before its existing v2-pagination-specific logic runs. Apply-renamer already emits `"refresh"` (T-065-43's cascade).

- [x] T-065-46 – Extend `AlbumService.clearCache()`/`clearAlbums()`'s existing invalidation call sites with the new `photo_v3_buckets_*`/`photo_v3_ratios_*`/`photo_v3_details_*` cache ids (F-065-20).
  _Intent:_ Mirrors FR-063-19/23's own cache-invalidation extension pattern.
  _Verification commands:_
  - `npm run check`

- [ ] T-065-47 – Manual verification: each mutation type (upload/delete/move/rename/star/rating/tag/license/apply-renamer) correctly refreshes the grid and evicts stale details; scroll position preserved for a mutation below the fold; a sort-relevant edit (e.g. title) reflects its new server-recomputed bucket on the next fetch (S-065-20, S-065-21, S-065-23, NFR-065-05).
  _Intent:_ Full I10 verification pass.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

### I11 – Accessibility + `HOUR`-granularity label verification

- [ ] T-065-48 – Manual screen-reader/DOM-inspection spot check of `aria-posinset`/`aria-setsize` values against true position/total, across at least one WASM-driven mode and `list` (S-065-30).
  _Intent:_ Confirm T-065-22's implementation is correct in practice, not just present in markup.
  _Verification commands:_
  - Manual browser verification — no dev server/database available this session.

- [x] T-065-49 – `HOUR`-granularity bucket header formatting (F-065-22, NFR-065-07).
  _Intent:_ Confirm the photos-only `HOUR` granularity's label formatting is correct.
  _Verification commands:_
  - Manual verification (no automated test suite exists).
  _Note:_ Not applicable in the way originally framed — bucket **header** labels are never client-formatted at all; `PhotoGridVirtual.vue` renders tier 1's `labels[]` verbatim (already server-formatted per FR-064-06, including the `HOUR` tier), so there is no client-side date-parsing code path to cross-check here. (`phpDateFormat.ts` itself was not used in this pass — see T-065-21's note; a per-tile date subtitle uses a simplified `toLocaleDateString()` placeholder instead, unrelated to bucket headers.)

### I12 – Documentation

- [ ] T-065-50 – Update `docs/specs/3-reference/api-design.md` noting Feature 064's three endpoints now have a v8 frontend consumer.
  _Verification commands:_
  - N/A (documentation only).

- [ ] T-065-51 – Update `docs/specs/4-architecture/knowledge-map.md` with the new unified virtualized-photo-grid architecture and file map.
  _Verification commands:_
  - N/A (documentation only).

- [ ] T-065-52 – Update `docs/specs/3-reference/frontend-gallery.md` documenting the analytic-layout + chunked-virtualization mechanism shared by all five layout modes and the on-demand `details`-fetch pattern.
  _Verification commands:_
  - N/A (documentation only).

- [x] T-065-53 – Update `docs/specs/4-architecture/roadmap.md`'s Feature 065 entry to reflect final implementation status.
  _Verification commands:_
  - N/A (documentation only).

## Notes / TODOs

- **Remaining work, honestly scoped:** T-065-50/51/52 (`api-design.md`/`knowledge-map.md`/`frontend-gallery.md` documentation updates) are not yet done. Every manual-verification task (T-065-07/13/17/25/27/34/39/42/47/48) is genuinely deferred — no dev server/database available in this implementation session. A lightbox-specific loading *indicator* (spinner) for the not-yet-resolved-details case is not yet wired (T-065-32's note) — a UI-polish gap, not a data-layer one. `date_format_photo_thumb` full-parity formatting (T-065-21's note) and `PhotoGridVirtual.vue`'s container-width calibration against `UContainer`'s real rendered padding (T-065-20's note) both need visual confirmation once a dev environment exists.
- Every "manual verification" task above is deferred pending a dev server/database being available in an implementation session — this is a standing constraint across this project's recent features (Features 061/062/063/064 all recorded the same limitation), not specific to this plan.
- No v2 backend file may be touched by any task above (NFR-065-01/08) — this is a frontend-only feature; if any task above appears to require a v2 file change, stop and re-open a question in `open-questions.md` rather than proceeding.
- `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts` and the `@lychee-org/layouts` WASM package itself are never modified by any task above (NG3) — every task adds new, separate, DOM-free callers alongside them.
- Tag/person-filtered SoA fetching, bulk `details` prefetching, and `TagAlbum`/`PersonAlbum` matching-photos support are explicitly out of scope for every task above (NG4/NG5/NG12) — do not fold any of them into a task here; they are tracked in plan.md's Follow-ups/Backlog instead.
- All five layout modes (`justified`/`square`/`masonry`/`grid`/`list`) are in scope for virtualization in this feature — an earlier draft of this plan deferred `masonry`/`grid`'s virtualization to a follow-up feature; that framing was corrected (Decision Card Q-065-01) once it was recognized that every WASM layout primitive already returns exact, DOM-free box coordinates, making virtualization mode-agnostic. Do not reintroduce a masonry/grid-specific fallback path.
- T-065-01 through T-065-53 assume Feature 064's field contract is exactly as documented in its own `spec.md` at the time this plan was drafted (2026-09-06). If Feature 064 is amended after this plan starts implementation, re-verify FR-065-04/06's field lists against the amended contract before continuing.
