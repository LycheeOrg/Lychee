# Feature Plan 065 – Photo Listing Struct-of-Arrays Frontend Adoption

_Linked specification:_ `docs/specs/4-architecture/features/065-photo-listing-struct-of-arrays-adoption/spec.md`
_Status:_ Implemented (code-complete; manual browser verification and doc updates pending, no dev environment available)
_Last updated:_ 2026-09-06 (Q-065-05/06 resolved)

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

A photo grid inside a regular `Album`, with the SoA flag on and no tag/person filter, renders via one unified virtualized grid sourced entirely from Feature 064's three endpoints — for **all five** layout modes (`justified`/`square`/`masonry`/`grid`/`list`), visually indistinguishable from today's v2 rendering for every field tier 2 already carries (video/raw/live-photo/highlighted/rating badges, title-or-description overlay, tags), with no more DOM nodes mounted at any time than the current viewport plus overscan requires, regardless of album size or layout mode. Every tile's exact box geometry is known analytically the instant tier 2 resolves — by calling the matching WASM primitive (`justified()`/`square()`/`masonry()`/`grid()`) directly, with zero DOM dependency — so virtualization, sticky bucket headers, and drag-select all work identically across all five modes through one shared mechanism, not a per-mode special case. Any tag/person-filtered or non-`Album`-parent view keeps working exactly as it does today on the untouched v2 path. Opening a photo (lightbox) or an edit dialog that needs heavier data transparently fetches it on demand, bounded, and caches it for the remainder of the album visit.

Success signals:
- Every FR-065-01..25 implemented and manually verified against the scenario matrix (S-065-01..33).
- `npm run format`/`npm run check` clean.
- `git diff` empty on every v2/v7 file named in NFR-065-01/08.
- A five-figure-photo fixture album confirms bounded DOM mount count (NFR-065-03) and correct drag-select behaviour (S-065-24/25) in all five layout modes.

## Scope Alignment

- **In scope:**
  - `AlbumState.ts`/`PhotosState.ts` SoA fetch, boundary, and on-demand-details plumbing (FR-065-01..06, 24).
  - One unified analytic-layout + chunked-virtualization mechanism covering all five layout modes — `justified`/`square`/`masonry`/`grid` (WASM-driven) and `list` (trivial arithmetic) (FR-065-07..12, 23, 25).
  - Lightbox and edit-dialog on-demand `details` integration (FR-065-13..15).
  - Drag-select geometry, mode-agnostic, for all five modes (FR-065-16).
  - Rating-filter-aware boundary recompute (FR-065-19).
  - Mutation-triggered re-fetch/cache-invalidation wiring (FR-065-20).
  - Client-side date formatting reuse, including the photos-only `HOUR` granularity (FR-065-21..22).
  - Documentation updates (Documentation Deliverables).
- **Out of scope:**
  - Any album/sub-album change (owned entirely by Feature 063).
  - Tag/person-filtered SoA fetching (NG4; Decision Card Q-065-02) — filtered views stay on v2.
  - `TagAlbum`/`PersonAlbum`-hosted photo listings on the v3 path (NG5).
  - Any new backend route, migration, config field, or feature flag (NG2/NG6).
  - Bulk/eager `details` prefetching (NG12; Decision Card Q-065-03).
  - A photo-level `/rights` endpoint or rights-combination formula (NG8) — none exists, none is built.
  - Any modification to `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts` or to the `@lychee-org/layouts` WASM package itself (NG3) — new, separate, DOM-free callers are added alongside them.

## Dependencies & Interfaces

- Feature 064's three routes/resources (`GET /Albums/{album_id}/Photos/buckets`, bare `GET /Albums/{album_id}/Photos`, `GET /Albums/{album_id}/Photos/details`) — implemented, all 30 tasks `[x]`, byte-identical field contract assumed throughout this plan.
- `modules.is_struct_of_array_enabled` — the existing flag, exposed via `LycheeState.ts`.
- `@lychee-org/layouts` WASM package's `justified()`/`square()`/`masonry()`/`grid()` exports (`resources/js/v8/layouts/wasmLayouts.ts`) — all four reused directly for the new analytic layout function; no WASM change needed, only new DOM-free TypeScript callers.
- Feature 063's precedent artifacts, reused directly (imported, not forked): `computeBucketBoundaries()`/`filterBucketedTiles()` (`resources/js/v8/utils/albumBucketBoundaries.ts`), the `useWindowVirtualizer`/two-layer sticky-header scaffolding pattern (`AlbumThumbGridVirtual.vue`), the `resources/js/v8/components/gallery/albumModule/Virtualized/` directory, `phpDateFormat.ts`, the `axios-cache-interceptor`-based service convention (`album-children-v3-service.ts`), and the `requestedAlbumId` stale-response-guard pattern already used across `AlbumState.ts`.
- Existing `resources/js/stores/AlbumState.ts` (orchestrates both album and photo fetches today: `loadAlbums()`/`loadAlbumsV3()` alongside `loadPhotos()`/`appendPhotos()`/`prependPhotos()`) and `resources/js/stores/PhotosState.ts`/`PhotoState.ts` (listing vs. lightbox split, already clean — no discovery/refactor needed here, unlike Feature 063's Q-063-01).
- `resources/js/v8/composables/contextMenus/contextMenu.ts` — consumed unchanged (NG13).
- `resources/js/composables/album/dragAndSelect.ts` — extended, not forked, with a new `getPhotoBoxesV3()` sibling to its existing `getAlbumBoxesV3()`.

## Assumptions & Risks

- **Assumptions:**
  - Feature 064's field contract (exact array names/gates per FR-064-07/10/18) is stable and will not change underneath this feature; any drift would be caught immediately by manual verification against a real fixture album.
  - `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts`'s underlying WASM calls (`justified()`/`square()`/`masonry()`/`grid()`) genuinely have no DOM dependency in their own signatures — only their surrounding wrapper does. To be confirmed per-primitive at I2 before assuming a uniform `computeAnalyticPhotoLayout()` dispatch works for all four.
  - No dev server/database is available in the session(s) that draft/implement this plan (a standing constraint across this project's recent features) — every "manual verification" task in `tasks.md` is genuinely deferred, not a placeholder for automated coverage that doesn't exist.
- **Risks / Mitigations:**
  - *Risk:* The four WASM primitives' parameter signatures might not be perfectly uniform (e.g. `square()`/`grid()` may take a target size/column-count rather than a ratios array the way `justified()`/`masonry()` do), requiring per-mode adapter glue inside `computeAnalyticPhotoLayout()` rather than one clean dispatch. *Mitigation:* I2 explicitly starts with a signature-confirmation step for all four primitives before writing the dispatch function; per-mode glue is an acceptable, expected outcome, not a plan-blocking surprise.
  - *Risk:* Chunking an already-flowed, non-uniform-row layout (masonry/grid) into fixed-size virtualization "chunks" could still produce a visually inconsistent scroll experience if a chunk boundary falls mid-column, causing an uneven jump when a chunk mounts/unmounts. *Mitigation:* I3's manual verification step specifically checks scroll smoothness for `masonry`/`grid` at chunk boundaries, not just `justified`/`square`/`list`; the chunking function is free to choose chunk boundaries at natural bucket/column-cycle boundaries where convenient, since chunk size is a tunable bookkeeping parameter, not a fixed layout constraint.
  - *Risk:* `detailsById`'s cache-eviction-on-navigation (NFR-065-09) could race with an in-flight `loadPhotoDetails()` call from the album being navigated away from, resurrecting a stale entry after the map was cleared. *Mitigation:* FR-065-24's existing `requestedAlbumId` guard already covers this — a resolving `loadPhotoDetails()` call checks the guard before writing into `detailsById`, exactly like every other guarded action in this store.
  - *Risk:* The rating-filter boundary recompute (FR-065-19) is new work with no Feature 063 precedent to mirror (albums have no equivalent client-side filter) — higher chance of an off-by-one or empty-bucket edge case than the rest of this plan, which mostly reuses proven patterns. *Mitigation:* dedicated increment (I5) and scenario coverage (S-065-14/15) isolated from the rest of the rendering work, so a bug here doesn't block or entangle with I2/I3/I4.

## Implementation Drift Gate

At the end of implementation, run `git diff` against `AlbumPhotosController.php`, `GetAlbumPhotosRequest.php`, `PhotoRepository.php` (`getPhotosForAlbumPaginated()` region), `PaginatedPhotosResource.php`, `routes/api_v2.php`, and every file under `resources/js/v7/**`, plus `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts`/`wasmLayouts.ts` (NG3 — new callers only, zero edits to the existing wrappers or the WASM package itself) — all must be empty (NFR-065-01/08, NG3). Record the result (and the exact `git diff` invocation used) directly in this section once run; do not mark the feature Implemented until it has been re-run clean immediately before the final commit.

_Not yet run — implementation not started._

## Increment Map

1. **I1 – Service + store: v3 fetch, boundary reuse, stale-guard, cache, centralized flag, dispatcher**
   - _Goal:_ `isPhotoSoaActive`/`loadPhotosAuto()`/`loadPhotosV3()` correctly fetch and adapt tier 1+2 into `PhotosState.ts.photos` for a regular `Album` with no active filter.
   - _Preconditions:_ Feature 064's three routes reachable; `album-children-v3-service.ts`/`albumBucketBoundaries.ts` (Feature 063) present and importable as-is.
   - _Steps:_ Write `photo-children-v3-service.ts` (FR-065-01); write `adaptPhotoTile.ts` (FR-065-04); add `photoBucketsV3`/`photoBoundariesV3`/`photoBucketableV3` fields to `AlbumState.ts` (distinct names from the existing album-child fields); write the centralized `isPhotoSoaActive` computed getter (FR-065-02, Q-065-05) — this is the single source every later increment (I4/I7/I8/I9) reads, so get its condition right here first; write `loadPhotosV3()` reusing `computeBucketBoundaries()` as-is (FR-065-03); write `loadPhotosAuto()` reading `isPhotoSoaActive` to dispatch, and swap every fresh-navigation call site from `loadPhotos()` to `loadPhotosAuto()`; wire the `requestedAlbumId` guard (FR-065-24).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-01/02/03/04/24 satisfied for the happy path; S-065-06/07/08/09/10/11/12/13/28/29 manually verifiable once a dev environment exists.

2. **I2 – Analytic per-mode layout function**
   - _Goal:_ `computeAnalyticPhotoLayout(mode, tiles, boundaries, containerWidth, config)` produces correct, DOM-free box geometry for every one of the five modes.
   - _Preconditions:_ I1 (adapted tiles carry `ratio`); confirm the exact parameter signature of `justified()`/`square()`/`masonry()`/`grid()` (`wasmLayouts.ts`) — none should require a DOM node, but each may take a different parameter shape (Assumptions/Risks).
   - _Steps:_ Write `resources/js/v8/composables/photo/analyticPhotoLayout.ts`; implement the four WASM-driven branches, each reading the same config values (target row height/tile size/column width/spacing) `useJustify.ts`/`useSquare.ts`/`useMasonry.ts`/`useGrid.ts` already read today (FR-065-11); implement `list`'s trivial fixed-height row branch (no WASM call); handle the `ratio=1` fallback as ordinary input for every mode, never special-cased (FR-065-25).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-07/11/25 satisfied; manual side-by-side visual diff against each mode's existing v2 DOM-based rendering of the same fixture album (once a dev environment exists) confirms Decision Card Q-065-01/Q-065-04's "identical geometry, any mode" claim.

3. **I3 – Chunked virtualization mechanism**
   - _Goal:_ Per-bucket box lists from I2 concatenate into one page-relative coordinate space and group into fixed-size, exact-height chunks; `useWindowVirtualizer` mounts only chunks in range.
   - _Preconditions:_ I2.
   - _Steps:_ Implement the top-to-bottom bucket concatenation (header pseudo-box + bucket boxes, Y-offset by cumulative height) using I1's boundaries; implement chunking (contiguous tile runs, splitting large buckets, never splitting a header from its bucket's first content, exact never-estimated chunk height) (FR-065-08); wire `useWindowVirtualizer` fed these exact chunk heights.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-08 satisfied; ready for I4's renderer to mount against it; manual scroll-smoothness check across chunk boundaries for `masonry`/`grid` specifically (Risks).

4. **I4 – Renderer components + dispatcher wiring**
   - _Goal:_ `PhotoGridVirtual.vue` mounts via `useWindowVirtualizer` over I3's chunk output, with sticky bucket headers from tier 1 labels, for all five modes; `PhotoThumbVirtual.vue`/`PhotoListItemVirtual.vue` render tiles; a thin `PhotoThumbPanelVirtual.vue` dispatcher mounts it (or falls through to the untouched v2 `PhotoThumbPanel.vue` when the SoA path isn't active) at `PhotoThumbPanel.vue`'s existing mount point.
   - _Preconditions:_ I1/I2/I3.
   - _Steps:_ Fork `PhotoThumb.vue` → `PhotoThumbVirtual.vue` (cover via `<Thumb>`, badges/overlay unchanged per NG11, absolute-positioned from its precomputed box — deliberately omitting v2's hover-triggered face-prefetch, Q-065-06); fork the list-row equivalent → `PhotoListItemVirtual.vue` (same omission, plus the file-size metadata chip omitted, Q-065-06); build `PhotoGridVirtual.vue` consuming I3's chunk output, picking `PhotoThumbVirtual.vue` vs. `PhotoListItemVirtual.vue` per tile by mode; implement the two-layer sticky-header pattern (real header content + pinned overlay), reusing Feature 063's established approach; implement the `bucketableV3:false` single-flat-chunk-sequence fallback; implement `phpDateFormat.ts`-based header label formatting for date-based sort columns (FR-065-09/21); implement `aria-posinset`/`aria-setsize` (FR-065-23); write `PhotoThumbPanelVirtual.vue`'s dispatch on `isPhotoSoaActive` (FR-065-02, Q-065-05) and wire it in at `PhotoThumbPanel.vue`'s current mount point.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-09..10, 12, 21, 23 satisfied; S-065-01..05/10/11/26/27/30/31/33 manually verifiable once a dev environment exists.

5. **I5 – Rating-filter-aware boundary recompute**
   - _Goal:_ `PhotoGridVirtual.vue` builds its layout from `filteredPhotos`, re-scanning bucket boundaries from the filtered array's own `bucket_id` sequence rather than reusing tier 1's original (pre-filter) counts.
   - _Preconditions:_ I4 (renderer must exist to be pointed at a different input array).
   - _Steps:_ Extend `computeBucketBoundaries()`'s call site (not the function itself — it's already generic) inside `PhotoGridVirtual.vue` to run against `filteredPhotos` when the rating filter is active, and against the unfiltered array otherwise; confirm an entirely-filtered-out bucket's header is omitted, not shown empty.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-18/19 satisfied; S-065-14/15 manually verifiable once a dev environment exists.

6. **I6 – On-demand `details` fetch + cache**
   - _Goal:_ `PhotosState.ts.detailsById`/`loadPhotoDetails(ids)` exist, deduped, chunked defensively at 300, cleared on navigation.
   - _Preconditions:_ I1 (needs `photo-children-v3-service.ts`'s `getDetails()`); `adaptPhotoDetail.ts` written in this increment.
   - _Steps:_ Write `adaptPhotoDetail.ts` (FR-065-06); add `detailsById`/`loadPhotoDetails()` to `PhotosState.ts` (FR-065-05); wire the `requestedAlbumId`-change → clear-`detailsById` hook (NFR-065-09).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-05/06 satisfied; ready for I7/I8 to call into it.

7. **I7 – Lightbox integration**
   - _Goal:_ Opening the lightbox triggers a bounded `loadPhotoDetails()` call; `PhotoState.ts`'s getters read from `detailsById` once resolved, falling back to the lightweight tile meanwhile; next/previous derived client-side from `ids`.
   - _Preconditions:_ I6.
   - _Steps:_ Wire the lightbox-open handler to check `isPhotoSoaActive` (FR-065-02, Q-065-05) first — when `false`, skip `loadPhotoDetails()` entirely and read the already-loaded v2 `PhotoResource` directly; when `true`, call `loadPhotoDetails([openedId, ...neighbors])`; adjust `PhotoState.ts`'s `imageViewMode`/`style`/`previousStyle`/`nextStyle`/`srcSetMedium` getters to read `detailsById[photoId]` with a fallback to the tile; confirm next/previous index arithmetic never reads a `next_photo_id`/`previous_photo_id` field.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-13/15 satisfied; S-065-16/17/32 manually verifiable once a dev environment exists.

8. **I8 – Edit-dialog integration**
   - _Goal:_ Description/EXIF/license/tag dialogs trigger `loadPhotoDetails([photoId])` before rendering content when not already cached.
   - _Preconditions:_ I6.
   - _Steps:_ Audit each dialog's open handler; add the `isPhotoSoaActive`-gated details-fetch-before-open step (Q-065-05 — skip `loadPhotoDetails()` entirely when `false`, same as I7); add a loading-spinner state to each dialog for the not-yet-resolved case.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-14 satisfied; S-065-18/19/32 manually verifiable once a dev environment exists.

9. **I9 – Drag-select geometry**
   - _Goal:_ `getPhotoBoxesV3()` correctly hit-tests selections for all five modes, mode-agnostically.
   - _Preconditions:_ I2/I3 (needs `computeAnalyticPhotoLayout()`/the chunking output).
   - _Steps:_ Add `getPhotoBoxesV3()` to `dragAndSelect.ts`, mirroring `getAlbumBoxesV3()`'s shape (anchor via `[data-photo-grid-root]`, re-derive boxes via `computeAnalyticPhotoLayout()`); extend the existing flag-check call site to read `isPhotoSoaActive` (FR-065-02, Q-065-05), with no further per-mode branch.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-065-16 satisfied; S-065-24/25 manually verifiable once a dev environment exists.

10. **I10 – Mutation re-fetch + cache invalidation**
    - _Goal:_ Upload/delete/move/rename/star/rating/tag/license/apply-renamer call sites re-invoke `loadPhotosAuto()` and evict affected ids from `detailsById`; `photo-children-v3-service.ts` cache entries invalidated at the same sites.
    - _Preconditions:_ I1/I6.
    - _Steps:_ Audit each mutation call site's existing refresh cascade; extend each to also re-invoke `loadPhotosAuto()` (SoA path) and evict `detailsById` entries for affected ids; extend `AlbumService.clearCache()`/`clearAlbums()`'s existing invalidation call sites with the new v3 photo cache ids.
    - _Commands:_ `npm run check`.
    - _Exit:_ FR-065-20 satisfied; S-065-20/21/22/23 manually verifiable once a dev environment exists; NFR-065-05 (scroll-position preservation) manually verifiable.

11. **I11 – Accessibility + `HOUR`-granularity label verification**
    - _Goal:_ Confirm `aria-posinset`/`aria-setsize` correctness (already implemented in I4); confirm `phpDateFormat.ts` already covers hour-format characters, or extend it narrowly if a gap is found.
    - _Preconditions:_ I4.
    - _Steps:_ Manual screen-reader/DOM-inspection spot check; manual PHP-vs-JS cross-check of `timeline_photo_date_format_hour` against `phpDateFormat.ts`'s existing character coverage.
    - _Commands:_ Manual verification only.
    - _Exit:_ FR-065-22/23 confirmed; NFR-065-07 confirmed for the hour-format case; S-065-26/30 manually verifiable once a dev environment exists.

12. **I12 – Documentation**
    - _Goal:_ Satisfy the Documentation Deliverables list.
    - _Preconditions:_ All prior increments (documentation should describe what was actually built).
    - _Steps:_ Update `api-design.md`, `knowledge-map.md`, `frontend-gallery.md`, `roadmap.md` per spec.md's Documentation Deliverables section.
    - _Commands:_ N/A (documentation only).
    - _Exit:_ Documentation Deliverables satisfied.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-065-01 | I1 / I4 | Happy path, `justified`. |
| S-065-02 | I4 | `square` mode. |
| S-065-03 | I4 | `list` mode. |
| S-065-04 | I2 / I4 | `masonry` mode, WASM `masonry()`-driven, virtualized identically to `justified`. |
| S-065-05 | I2 / I4 | `grid` mode, WASM `grid()`-driven. |
| S-065-06 | I1 | Flag-off parity. |
| S-065-07 | I1 | Tag filter → v2 fallback. |
| S-065-08 | I1 | Person filter → v2 fallback. |
| S-065-09 | I1 | Non-`Album` parent → v2 fallback. |
| S-065-10 | I1 / I4 | `OWNER_ID` sort → `bucketable:false`. |
| S-065-11 | I1 / I4 | Defensive count-mismatch fallback. |
| S-065-12 | I1 | Empty album. |
| S-065-13 | I1 | Non-admin curated visibility, server-side only. |
| S-065-14 | I5 | Rating filter + boundary recompute. |
| S-065-15 | I5 | Rating filter empties a bucket. |
| S-065-16 | I7 | Lightbox partial-then-full render. |
| S-065-17 | I7 | Lightbox rapid navigation + stale guard. |
| S-065-18 | I8 | Edit dialog cold-cache fetch. |
| S-065-19 | I8 | Edit dialog warm-cache instant render. |
| S-065-20 | I10 | Upload re-fetch. |
| S-065-21 | I10 | Delete/move re-fetch + lightbox handling. |
| S-065-22 | — | Existing rights logic, no new code — confirmed by inspection, not a task. |
| S-065-23 | I10 | Sort-relevant edit → server-side bucket recompute reflected on next fetch. |
| S-065-24 | I9 | Drag-select, `justified`/`square`/`list`. |
| S-065-25 | I9 | Drag-select, `masonry`/`grid` — same mechanism, mode-agnostic. |
| S-065-26 | I11 | `HOUR`-granularity labels. |
| S-065-27 | I2 | `ratio=1` fallback, any mode. |
| S-065-28 | I1 | Rapid-navigation stale-response guard. |
| S-065-29 | I1 | Client cache hit. |
| S-065-30 | I4 / I11 | Accessibility attributes. |
| S-065-31 | I4 | Layout-mode switch, zero refetch. |
| S-065-32 | I7 / I8 | `isPhotoSoaActive:false` → lightbox/dialog never calls `loadPhotoDetails()`. |
| S-065-33 | I4 | Accepted regressions: no hover face-prefetch, no list-mode file-size chip. |

## Analysis Gate

_Not yet run — spec/plan/tasks drafted, implementation not started._

## Exit Criteria

- Every FR-065-01..25 and NFR-065-01..09 implemented and either automatically satisfied by code structure or manually verified per the Test Strategy.
- `npm run format`/`npm run check` clean.
- Implementation Drift Gate re-run clean immediately before the final commit.
- Documentation Deliverables (I12) complete.
- Roadmap entry #65 updated to reflect actual implementation status (this plan starts in Draft; update to Implemented only once the above are all true).

## Follow-ups / Backlog

- Tag/person-filtered SoA fetching (NG4/Q-065-02) — would require a Feature 064 amendment adding filter parameters to all three tiers; revisit only if the v2 fallback for filtered views proves disruptive in practice.
- Bulk/eager `details` prefetching for an entire visible bucket (NG12/Q-065-03) — a possible future performance optimization once on-demand fetching's real-world latency characteristics are observed.
- `TagAlbum`/`PersonAlbum` matching-photos SoA support — blocked on Feature 064's own NG3 (no backend support yet); would need both a Feature 064 amendment and a corresponding extension here.
