# Feature 062 Tasks – Album Timeline Buckets Frontend Adoption

_Status: In Progress — all code-level tasks (I1-I9, I11, I13) implemented; manual browser/DevTools verification tasks and I10/I12's remaining doc updates still outstanding (no dev server/database available this session)._
_Last updated: 2026-08-31_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-062-`) inside the same parentheses immediately after the task title (omit categories that do not apply).

## Checklist

### I1 – Store: tier 1+2 fetch, positional boundary walk, stale-response guard, per-album cache

- [x] T-062-01 – Implement `AlbumsState.ts`'s new tier 1+2 fetch action, gated on `modules.is_struct_of_array_enabled`, called from the album-navigation flow instead of `AlbumService.getAlbums()` when the flag is on (F-062-01).
  _Intent:_ Parallel fetch of `GET .../children/buckets` and `GET .../children` for the current `album_id`.
  _Note:_ Implemented as `loadAlbumsV3()` on `AlbumState.ts` (singular — the per-navigation store), dispatched via `loadAlbumsAuto()`; adapted tiles written into `AlbumsState.ts` (plural)'s existing `albums` field.
  _Verification commands:_ `npm run check`
- [x] T-062-02 – Implement the positional boundary-walk as a small, independently callable pure function: given tier 1's `bucket_ids`/`counts`/`labels` and tier 2's already-ordered flat children array (Feature 061 FR-061-26 guarantees the match), slice into consecutive runs of `counts[0]`, `counts[1]`, ... children each; if `sum(counts) !== children.length`, fall back to one single unbucketed section covering every child — the same flat rendering path F-062-09 uses for `bucketable: false` (F-062-02).
  _Intent:_ No join, no sort — purely a positional slice, since the backend already guarantees the order.
  _Note:_ `computeBucketBoundaries()`, `resources/js/v8/utils/albumBucketBoundaries.ts`.
  _Verification commands:_ `npm run check`
- [ ] T-062-03 – Manual verification against Feature 061's existing per-source fixture parents (created_at/min_taken_at/max_taken_at/title-date_prefix/title-alphabetical/owner_id): correct bucket order/labels, correct slice boundaries (F-062-01/02, S-062-02, S-062-04).
  _Intent:_ Scenario verification, not a new automated test (this repo has no frontend suite).
  _Verification commands:_ Manual browser check, both flag states, against each fixture parent. **Not yet run — no dev server/database available this session.**
- [x] T-062-04 – Implement a monotonic request-generation counter on `AlbumsState.ts`, incremented on every `loadAlbumsV3()` call; each in-flight tier 1/2/3 fetch captures its own generation at start; a response is applied to the store only if that generation still matches the store's current one, otherwise silently discarded (F-062-18).
  _Intent:_ Navigating away before a fetch resolves must never let its late response overwrite whatever album is now open.
  _Note:_ No new generation-counter mechanism added — `loadAlbumsV3()`/`loadAlbumsV3Rights()` reuse `AlbumState.ts`'s existing `requestedAlbumId`-capture-and-compare guard (`loadHead()`/`loadAlbums()`/`loadPhotos()`/`load()` already use the identical pattern).
  _Verification commands:_ `npm run check`
- [x] T-062-05 – Implement a short-TTL, per-`album_id` in-memory cache (`Map<albumId, {data, expiresAt}>`) on `AlbumsState.ts`: `loadAlbumsV3()` checks the cache before firing tier 1+2, populates it on resolve; invalidated by the same trigger set T-062-32 wires for FR-062-13's re-fetch (F-062-19).
  _Intent:_ Mirror v2's `AlbumService` caching convention rather than always re-fetching.
  _Note:_ No bespoke Pinia-level cache added — reuses `AlbumService`'s existing `axios-cache-interceptor` convention directly via a new `AlbumChildrenV3Service` (`resources/js/services/album-children-v3-service.ts`); `AlbumService.clearCache()` extended to also clear its three cache entries.
  _Verification commands:_ `npm run check`
- [ ] T-062-06 – Manual verification: (a) navigating from album A to album B before A's fetches resolve (throttled network, DevTools) never applies A's late response to B's state (S-062-26); (b) revisiting a recently-browsed album renders from cache with no new network request, and a mutation trigger (once T-062-32 lands) invalidates that entry (S-062-25).
  _Intent:_ Scenario verification for both new store-level mechanisms.
  _Verification commands:_ Manual browser check, DevTools Network panel, throttled + normal speed. **Not yet run — no dev server/database available this session.**

### I2 – Store: background rights fetch + client-side combination

- [x] T-062-07 – Implement `AlbumsState.ts`'s tier 3 background-fetch action, fired immediately after T-062-01's tier 1+2 resolve; every child's `can_edit`/`can_download`/`can_delete`/`can_move` defaults to `false` until it resolves (F-062-03).
  _Intent:_ Background fetch, not interaction-time.
  _Note:_ `loadAlbumsV3Rights()` on `AlbumState.ts`.
  _Verification commands:_ `npm run check`
- [x] T-062-08 – Implement the client-side rights-combination formula as a small pure function: `can_edit = (owner_id === current_user.id && current_user.may_upload) || grants_edit[i]`; `can_download = (owner_id === current_user.id) || grants_download[i]`; `can_delete = (owner_id === current_user.id) || can_delete_children`; `can_move = (owner_id === current_user.id) || can_move_children`; reads `useUserStore()` for `current_user` (F-062-04/05).
  _Intent:_ Make the combination logic match `AlbumPolicy::canEdit`/`canDownload`/`canDelete` exactly (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`).
  _Note:_ `combineAlbumChildRights()` (`resources/js/v8/utils/adaptAlbumChildTile.ts`). Two corrections found during implementation: (1) `current_user.may_upload` isn't exposed by `UserResource` — uses `albumsStore.rootRights?.can_upload` instead (equivalent value, per `AlbumPolicy::canUpload($user, null)`); (2) the `owner_id === current_user.id` shortcut is gated by `isRegularAlbumParent` (`albumStore.modelAlbum !== undefined`) — applying it unconditionally would over-grant rights for `TagAlbum`/`PersonAlbum` parents, since `owner_id` there is the tag/person's own owner, not each matched child's (see F-062-05).
  _Verification commands:_ `npm run check`
- [ ] T-062-09 – Manual cross-check against Feature 061's existing rights fixtures: individually-shared child, parent-level delete grant, multi-group overlap, admin caller, guest caller — compare this feature's computed booleans against the already-passing backend `AlbumChildrenRightsV3Test` assertions for the same fixtures (F-062-04, NFR-062-04, S-062-05..09/16).
  _Intent:_ The single most important verification in this increment — a silent divergence here is a permission bug.
  _Verification commands:_ Manual browser check against each fixture; cross-reference `tests/Feature_v3/Album/AlbumChildrenRightsV3Test.php`'s fixture setup. **Not yet run — no dev server/database available this session.**

### I3 – Adapter: joined data → `ThumbAlbumResource` shape, plus the new tile component

- [x] T-062-10 – Implement the adapter turning each child (plus T-062-08's rights) into an `AdaptedAlbumTile` object (`ThumbAlbumResource` plus `cover_id`): `rights` from T-062-08's output, `can_share`/`can_share_with_users`/`can_transfer`/`can_upload`/`can_access_original` all `false`, `timeline: null`, `thumb: null`; `is_pinned`/`is_public`/`is_link_required` mapped straight through from tier 2's own fields (requires Feature 061 FR-061-27 shipped); `formatted_min_max` computed via T-062-39's `phpDateFormat.ts` from tier 2's raw `min_taken_at`/`max_taken_at` (F-062-06).
  _Intent:_ Zero-change consumption by `contextMenu.ts` — including the Pin/Unpin label and public/hidden badges, both broken by these fields' prior absence.
  _Note:_ `adaptAlbumChildTile()` (`resources/js/v8/utils/adaptAlbumChildTile.ts`); also exports `DEFAULT_ALBUM_CHILD_RIGHTS` (all-`false`, used before tier 3 resolves).
  _Verification commands:_ `npm run check`
- [x] T-062-11 – Implement `resources/js/v8/components/gallery/albumModule/Virtualized/AlbumThumbVirtual.vue`, forked from `AlbumThumb.vue`: reuse `AlbumThumbOverlay.vue`/`AlbumThumbDecorations.vue`/`ThumbBadge.vue`/the responsive width and `aspect-*` classes/selection-drag styling/`data-album-id` unchanged, but replace `AlbumThumbImage.vue`'s pre-built-URL lookup with `<Thumb :album-id="child.id" :photo-id="child.cover_id" type="thumb">` (`resources/js/v8/components/thumbs/Thumb.vue`, the same component `AlbumNavTree.vue` already uses) — plus the no-cover fallback (password icon when `child.cover_id === null && child.is_password_required`, generic no-image icon otherwise) (F-062-15, DO-062-06).
  _Intent:_ `Thumb.vue`/`ThumbAssetService`/the Feature 056 Asset endpoint are all reused unchanged — this task only wires a new caller, no new fetching/caching logic.
  _Verification commands:_ `npm run check`
- [ ] T-062-12 – Manual side-by-side comparison, flag on vs. off, same fixture album: right-click menu's available actions are identical, including the Pin/Unpin label for an already-pinned subalbum (S-062-21) and the public/hidden badges (S-062-22) (F-062-06, NFR-062-02); tile rendering is equivalent modulo virtualization/sticky headers/the two accepted regressions (no video play-icon, no blur-up placeholder). Also verify a mixed-cover-state fixture: real `cover_id` renders correct pixels, `null` + password-protected shows the password icon, `null` + not shows the generic no-image icon (F-062-15, S-062-18, S-062-20); and the date subtitle against a non-default `date_format_album_thumb`/`thumb_min_max_order` (S-062-23).
  _Intent:_ Confirm the metadata adapter, the tile component, and the date-format integration are all correct before anything else depends on them.
  _Verification commands:_ Manual browser check. **Not yet run — no dev server/database available this session.**

### I4 – Shared composable: row flattening + tile geometry

- [x] T-062-13 – Implement `resources/js/v8/composables/album/virtualAlbumRows.ts`: `(children[], bucketMeta[], itemsPerRow, tileWidth, aspectRatioNumber) → {rows, geometryLookup}` — one header row per bucket, `ceil(count / itemsPerRow)` tile rows per bucket (each tile row's height = `tileWidth ÷ aspectRatioNumber`, F-062-14), plus a `(row, col) → absolute box` lookup (F-062-07, DO-062-02).
  _Intent:_ Single shared implementation for both virtualized renderers and the drag-select reimplementation; height is computed, never measured.
  _Note:_ `buildVirtualAlbumRows()`, signature extended with `showHeaders`/`gap` params. `getTileBox()` is O(1) per call (per-child top/col precomputed once during construction) — an initial O(rows)-per-call version was caught and fixed given drag-select tests every child, mounted or not, at the 7,000+-child scale.
  _Verification commands:_ `npm run check`
- [ ] T-062-14 – Manual verification against a small hand-computed fixture (2 buckets, uneven counts, a count not evenly divisible by `itemsPerRow`, at least two different `aspectRatioNumber` values): row list, row heights, and geometry lookup all match hand-calculated expected values (F-062-14).
  _Intent:_ Catch off-by-one errors in the flattening/geometry/height math before any renderer depends on it.
  _Verification commands:_ Manual check (e.g. a throwaway console/script harness). **Not yet run as a standalone fixture check — exercised indirectly via `npm run check` and code review only.**

### I5 – Grid virtualization

- [x] T-062-15 – Implement `resources/js/v8/composables/album/albumTileWidth.ts` (DO-062-09): reactive `itemsPerRow`/`tileWidth`, computed analytically — container width via a `getWidth.ts`-style measurement (`window.innerWidth` minus the scroll container's real computed padding/scrollbar, `ResizeObserver`-driven), current Tailwind breakpoint via `useBreakpoints()` (`@vueuse/core`) extended with this project's actual `sm`/`md`/`lg`/`xl`/`2xl`/`3xl`/`4xl` pixel thresholds (source these during this task — not found in any `@theme` block during spec drafting), tile width from one shared breakpoint→`calc()`-formula lookup table also referenced by `AlbumThumbVirtual.vue`'s width classes; below `sm`, `itemsPerRow = number_albums_per_row_mobile` directly, no formula (F-062-14).
  _Intent:_ No probe tile, no DOM measurement of any tile element — resolves Q-062-10.
  _Note:_ Confirmed via the actual compiled production CSS that this project defines no `3xl`/`4xl` breakpoint at all (dead classes) — `useBreakpoints(breakpointsTailwind)`'s default 5 thresholds are used unextended. Measures the grid's own root element (`useElementSize()`), not `getWidth.ts` (a different, unrelated photo-layout helper). Pure geometry math factored out as `computeAlbumTileGeometry()` for T-062-28's reuse.
  _Verification commands:_ `npm run check`
- [x] T-062-16 – Implement `resources/js/v8/components/gallery/albumModule/Virtualized/AlbumThumbPanelVirtualList.vue`: `useVirtualizer` over T-062-13's flattened rows, sticky header rows (`position: sticky; top: 0`), tile rows rendering T-062-11's `AlbumThumbVirtual.vue`, `itemsPerRow`/tile width from T-062-15, feeding T-062-13's analytical row-height math (F-062-14), following `AlbumNavTree.vue`'s `translate3d`/single-spacer/`contain` conventions without replicating its single-column internals (F-062-07/08).
  _Intent:_ Core virtualized grid renderer, in the new `Virtualized/` component directory (kept separate from the flag-off components so it can be removed as a unit later).
  _Note:_ Named `AlbumThumbGridVirtual.vue`. Uses `useWindowVirtualizer` (with a reactive `scrollMargin`), not `useVirtualizer` against a nested scroll box — `AlbumPanel.vue`'s content shares one ordinary page/window scroll (`#galleryView` carries no `overflow-y`/bounded height in v8, unlike `AlbumNavTree.vue`'s own fixed-height sidebar). `position: sticky` does not apply to the `translate3d`-positioned rows, so the "sticky header" is a separate always-present overlay element showing the current section's label once its real header has scrolled past the top, not the real header row itself going sticky.
  _Verification commands:_ `npm run check`
- [x] T-062-17 – Wire the `bucketable: false` fallback: headerless windowed tile rows, no sticky-header rows in the flattened list (F-062-09).
  _Intent:_ Mirror the existing `isTimeline && buckets.length > 1` flat-fallback gate.
  _Verification commands:_ `npm run check`
- [x] T-062-18 – Wire `AlbumThumbPanel.vue`'s branch point: render `AlbumThumbPanelVirtualList.vue` instead of today's plain `v-for` when the flag is on and v3 data is present; flag-off path unchanged.
  _Intent:_ Integration point.
  _Note:_ Wired at `AlbumPanel.vue` instead, one level up: `AlbumThumbPanel.vue`+`<Pagination>` (v2) vs. a new `AlbumThumbPanelVirtual.vue` dispatcher (v3, itself choosing between `AlbumThumbGridVirtual.vue`/`AlbumListViewVirtual.vue` by `album_view_mode`) — `AlbumThumbPanel.vue` itself is untouched, not branched internally.
  _Verification commands:_ `npm run check`
- [ ] T-062-19 – Manual DevTools element-count check against a 7,000+-child fixture album, scrolled to top/middle/bottom: mounted `AlbumThumbVirtual.vue` count stays bounded (F-062-07, NFR-062-03, S-062-03).
  _Intent:_ The scale verification this whole feature exists for.
  _Verification commands:_ Manual DevTools Elements-panel check. **Not yet run — no dev server/database available this session.**
- [ ] T-062-20 – Manual verification: sticky headers render correct labels/order for each bucketable-source fixture parent (created_at/min_taken_at/max_taken_at/both title modes); `OWNER_ID`-sorted parent virtualizes with no headers (F-062-08/09, S-062-02/04). Also verify tile row height/`itemsPerRow` for each of the six `album_thumb_css_aspect_ratio` values and each of the seven Tailwind breakpoints, including a live resize across a threshold, confirming no visible layout shift/pop-in and no probe-tile flash (F-062-14, S-062-17).
  _Intent:_ Scenario verification.
  _Verification commands:_ Manual browser check. **Not yet run — no dev server/database available this session; note only 5 breakpoints are modeled (3xl/4xl confirmed dead), not seven.**
- [ ] T-062-21 – Manual scroll-through pass on both mobile and desktop viewports: confirm the new sticky bucket headers don't clash with `AlbumHero.vue` or reproduce the pre-existing sticky-toolbar clipping bug (`STUDY-MOBILE-v8.md` finding #10); if it reproduces, log it as a follow-up rather than silently accepting it.
  _Intent:_ Plan.md Risks item — new sticky UI interacting with existing sticky UI.
  _Verification commands:_ Manual browser check, mobile + desktop viewport widths. **Not yet run — no dev server/database available this session.**
- [ ] T-062-22 – Manual DevTools Network-panel check, scrolling the 7,000+-child fixture: cover-image requests fire only as tiles mount (not upfront for the whole response), and scrolling a tile out of the overscan band releases its blob URL (`ThumbAssetService`'s existing ref-count/eviction behavior, exercised here for the first time at virtualization's mount/unmount cadence) (F-062-15, S-062-19).
  _Intent:_ Confirm lazy fetch/release composes correctly with virtualization — no new code, just the first real exercise of `Thumb.vue`'s existing lifecycle at this scale.
  _Verification commands:_ Manual DevTools Network-panel check. **Not yet run — no dev server/database available this session.**

### I6 – List-view virtualization

- [x] T-062-23 – Implement `resources/js/v8/components/gallery/albumModule/Virtualized/AlbumListViewVirtual.vue` as a thin wrapper reusing T-062-13's composable with `itemsPerRow = 1` and a fixed row height (matching `AlbumListItem.vue`'s own fixed thumbnail height, not the aspect-ratio math T-062-13 uses for the grid) (F-062-10, DO-062-04).
  _Intent:_ Reuse, not a second implementation; same new `Virtualized/` directory as T-062-11.
  _Note:_ `LIST_ROW_HEIGHT = 40`, passed as `tileWidth` with `aspectRatioNumber = 1` so `buildVirtualAlbumRows()`'s height math collapses to the constant unchanged. `useWindowVirtualizer`, same as T-062-16.
  _Verification commands:_ `npm run check`
- [x] T-062-24 – Implement `resources/js/v8/components/gallery/albumModule/Virtualized/AlbumListItemVirtual.vue`, forked from `AlbumListItem.vue`: reuse `ListBadge.vue` and its layout/badge logic unchanged, replace its own `AlbumThumbImage.vue` usage with `<Thumb :album-id="child.id" :photo-id="child.cover_id" type="thumb">` at the row's existing fixed thumbnail size (`h-8 md:h-5`) — same `<Thumb>` mechanism and no-cover fallback as T-062-11 (F-062-17, DO-062-07).
  _Intent:_ List view has the identical `AlbumThumbImage.vue` dependency grid view had — this closes that gap the same way T-062-11 closed it for the grid.
  _Note:_ Also drops the `isSmartAlbum` branch and tag/person-album badges (dead code for adapted tiles — direct children of a real album are never themselves smart/tag/person albums, FR-061-24).
  _Verification commands:_ `npm run check`
- [ ] T-062-25 – Wire `AlbumListView.vue`'s (or its parent's) branch point for `album_view_mode: 'list'` + flag on, rendering T-062-24's component per row; manual verification, including toggling `album_view_mode` mid-session without a re-fetch (F-062-10, S-062-11), and the mixed-cover-state fixture in list view (S-062-24).
  _Intent:_ Integration + scenario verification.
  _Note:_ The wiring itself is done — via `AlbumThumbPanelVirtual.vue`'s dispatcher (T-062-18's note), not a branch inside `AlbumListView.vue`. Manual verification not yet run.
  _Verification commands:_ Manual browser check. **Not yet run — no dev server/database available this session.**

### I7 – Pagination control removal for the flag-on path

- [x] T-062-26 – Hide the album `<Pagination>` instance in `AlbumPanel.vue` when the flag is on for the subalbum section; leave `AlbumState.ts`'s `loadMoreAlbums()`/`goToAlbumsPage()` in place, unreachable in that mode (F-062-12).
  _Intent:_ Nothing left to paginate once tier 2 is whole-album-at-once.
  _Verification commands:_ `npm run check`; manual check both flag states (manual portion not yet run this session).

### I8 – Drag-select against precomputed geometry

- [ ] T-062-27 – Manual baseline pass: exercise today's flag-off `dragAndSelect.ts` DOM-query album-selection branch against a small set of drag rectangles (fully inside viewport, edge-touching, corner-touching) and record the exact resulting selections — the parity baseline the new branch must match.
  _Intent:_ Establish ground truth before reimplementing, per plan.md Risks (avoid silently diverging semantics).
  _Verification commands:_ Manual browser check, flag off. **Not yet run — no dev server/database available this session.**
- [x] T-062-28 – Implement the geometry-intersection album-selection branch in `dragAndSelect.ts`, gated to flag-on + the virtualized panel active, using T-062-13's geometry lookup (F-062-11, DO-062-05).
  _Intent:_ Replace DOM query with a data-driven intersection test.
  _Note:_ `getAlbumBoxesV3()` recomputes `computeAlbumTileGeometry()`/`buildVirtualAlbumRows()` fresh at drag-start (not shared with the mounted component instance) and anchors the result via the existing `getBounding()` helper on a new `[data-album-grid-root]` marker element — this keeps the new boxes in `dragAndSelect.ts`'s own existing coordinate system (whatever `#galleryView`'s scroll model is) without needing to know or duplicate it. NSFW-hidden tiles are excluded to match `getBoxes()`'s DOM-absence for them.
  _Verification commands:_ `npm run check`
- [ ] T-062-29 – Manual verification: T-062-27's exact rectangles reproduce identical selections under the new branch (parity); then a drag spanning from above the viewport to below it in the 7,000+-child fixture selects the full expected set, including tiles never mounted during the drag (F-062-11, S-062-12).
  _Intent:_ Confirm both parity and the actual fix.
  _Verification commands:_ Manual browser check, flag on. **Not yet run — no dev server/database available this session.**

### I9 – Mutation re-fetch wiring

- [x] T-062-30 – Implement `AlbumsState.ts`'s re-fetch action (tier 1+2 re-fetch + tier 3 re-arm, plus invalidating T-062-05's per-album cache entry) and wire it alongside each existing `AlbumService.clearCache()` call site relevant to the subalbum section's own in-panel mutations (create, delete, move, rename, lock/unlock, visibility change) (F-062-13).
  _Intent:_ Keep the virtualized dataset correct after a same-session mutation.
  _Note:_ No dedicated re-fetch action or new wiring was needed: every relevant mutation flow already ends in `emits("refresh")` → `Album.vue`'s `refresh()` → `albumStore.refresh()` → `load()` → `loadAlbumsAuto()` → `loadAlbumsV3()`, the same cascade v2 already relies on. A `refreshAlbumsV3()` method was initially added per this task's original wording, found to have no real caller once the cascade was traced, and removed. Where a v2 mutation flow doesn't end in `emits("refresh")` (e.g. `AlbumVisibility.vue`), v3 correctly inherits that same pre-existing lazy, on-next-visit-only invalidation via `AlbumService.clearCache()`'s extension (T-062-05).
  _Verification commands:_ `npm run check`
- [x] T-062-31 – Audit for an existing call site that fires on a subalbum's cover-photo change (manual cover selection, or `RecomputeAlbumStatsJob`/`AlbumComputedDataUpdated`-style completion after an automatic `auto_cover_id_*` recompute); wire T-062-30's re-fetch action there, adding a new call site if none exists (F-062-13, Q-062-09).
  _Intent:_ Tile rendering now depends on `cover_id` (F-062-15) — a cover-only change must not go unnoticed the way it would have before this feature.
  _Note:_ `AlbumPanel.vue`'s `albumCallbacks.setAsCover`/`photoCallbacks.setAsCover` both already call `AlbumService.clearCache()` + `emits("refresh")` — covered by T-062-30's cascade, no new call site needed.
  _Verification commands:_ `npm run check`
- [ ] T-062-32 – Manual verification per mutation type: create/delete/move/rename/lock/unlock/visibility-change/**cover-change** a subalbum from within the open parent, confirm the grid/list reflects it immediately, correct bucket placement and cover pixels included (F-062-13, S-062-14).
  _Intent:_ Scenario verification, one pass per mutation type (mirrors Feature 058's S-058-19..23 precedent).
  _Verification commands:_ Manual browser check, one pass per mutation type. **Not yet run — no dev server/database available this session.**
- [ ] T-062-33 – Manual verification: delete a subalbum several screens below the current scroll position, confirm the visible tiles above it do not shift after the resulting re-fetch (NFR-062-05, S-062-13).
  _Intent:_ Scroll-position stability check.
  _Verification commands:_ Manual browser check. **Not yet run — no dev server/database available this session.**

### I10 – TagAlbum/PersonAlbum parity pass

- [ ] T-062-34 – Manual verification against a `TagAlbum`/`PersonAlbum` fixture: matching-albums set matches v2 parity, in the order tier 2 already returns them (instance-wide default sort, Feature 061 FR-061-26); Delete/Move disabled unless the caller owns that specific matching album (F-062-05, S-062-10).
  _Intent:_ Confirm the whole I1–I9 pipeline behaves correctly for both non-`Album` browse types.
  _Verification commands:_ Manual browser check. **Not yet run — no dev server/database available this session.** Implementation-level correctness (the `isRegularAlbumParent` gate) is covered by T-062-08's note and code review.

### I11 – Accessibility pass

- [x] T-062-35 – Add `aria-posinset`/`aria-setsize` (or an equivalent `role="grid"` pattern) to virtualized tile/row wrappers, reflecting true position/total rather than the mounted subset's (NFR-062-07).
  _Intent:_ Avoid silently regressing assistive-tech semantics via virtualization.
  _Note:_ `role="list"` on each grid/list virtualizer's root, `role="listitem"` + `aria-posinset`/`aria-setsize` on each tile wrapper (grid) / `AlbumListItemVirtual.vue` instance (list, via attribute fallthrough). Header rows are left role-less (a full `role="group"`-per-bucket pattern was judged out of scope for this pass).
  _Verification commands:_ `npm run check`
- [ ] T-062-36 – Manual screen-reader or DOM-inspection spot check against a large fixture album, confirming reported position/total match the true child count, not the mounted count.
  _Intent:_ NFR verification.
  _Verification commands:_ Manual check. **Not yet run — no dev server/database available this session.**

### I12 – Documentation

- [x] T-062-37 – Update `docs/specs/3-reference/api-design.md` (including FR-061-27's three new tier 2 fields), `docs/specs/4-architecture/knowledge-map.md`, `docs/specs/4-architecture/roadmap.md`.
  _Intent:_ Documentation Deliverables.
  _Note:_ FR-061-27's `api-design.md` update was done as part of Feature 061's own amendment. This feature's `spec.md`/`plan.md`/`tasks.md` received a consolidated correction pass (store-name split, simplified guard/cache mechanisms, rights-bug fix, `may_upload` fix, dead `3xl`/`4xl` breakpoints, window-virtualizer architecture, `AlbumThumbGridVirtual.vue`/`AlbumThumbPanelVirtual.vue` naming) once implementation surfaced these corrections. `knowledge-map.md`/`roadmap.md` updates for Feature 062 specifically are still outstanding.
  _Verification commands:_ N/A (review only).

### I13 – Client-side date formatting (`phpDateFormat.ts`)

- [x] T-062-38 – Add `date_format_album_thumb`/`thumb_min_max_order` fields to `App\Http\Resources\GalleryConfigs\AlbumConfig`/`RootConfig` (this feature's own small backend addition — not a Feature 061 change; these are pre-existing general config resources already exposing `album_thumb_css_aspect_ratio` the same per-album-then-instance-default way), and regenerate the TypeScript types (F-062-16).
  _Intent:_ T-062-39's `phpDateFormat.ts` and T-062-10's adapter both need these fields to exist before they can read them.
  _Note:_ Confirmed via `ThumbAlbumResource::formatMinMaxDate()` that these two configs have no per-album override (always a flat instance-wide read) — unlike `album_thumb_css_aspect_ratio`. Fixed two `Search.vue` (v7 and v8) literal `AlbumConfig` fallback objects that needed the two new required fields added.
  _Verification commands:_ `make phpstan`; `vendor/bin/php-cs-fixer fix --dry-run`; confirmed both fields appear in regenerated `lychee.d.ts`; `npm run check`.
- [x] T-062-39 – Implement `resources/js/v8/utils/phpDateFormat.ts` (DO-062-08): `phpDateFormat(format: string, date: Date): string`, adapted from the reference PHP-date-to-JS conversion (community gist) — day/month/year/time format characters, `\`-escaped literals, unrecognized characters pass through literally (matching PHP's own behavior); plus the min/max join+ordering logic mirroring `ThumbAlbumResource::formatMinMaxDate()`'s exact branching: both present and equal → single value (the *only* collapse case); both present and different → ordered join; either one missing, including exactly one present → stays `null` (matches `formatMinMaxDate()`'s single early-return branch — "only one present" is *not* a collapse case), reading T-062-38's new `date_format_album_thumb`/`thumb_min_max_order` fields on `AlbumConfig`/`RootConfig` (F-062-16).
  _Intent:_ T-062-10's adapter depends on this — land it first or in parallel, not after.
  _Verification commands:_ `npm run check`
- [ ] T-062-40 – Manual cross-check: render the same set of dates through both PHP's `date()` (throwaway Tinker/test script) and `phpDateFormat()` for a representative format-string set (default `'M Y'`, plus several admin-plausible variants exercising day/month/year/time tokens and escaped literals, plus the exactly-one-of-min/max-present case); confirm byte-for-byte identical output (NFR-062-08).
  _Verification commands:_ Manual check. **Not yet run — no PHP runtime cross-check performed this session; the branching logic was verified by direct reading of `formatMinMaxDate()`'s source instead.**
  _Intent:_ A silent divergence here is a wrong, visible date on every tile.
  _Verification commands:_ Manual cross-check, PHP script vs. browser console/throwaway harness.

## Notes / TODOs

- Photo-grid virtualization, root-scope (`Albums.vue`) bucketing/virtualization, `bucket_id`-windowed tier-2 pagination, and `dragAndSelect.ts`'s photo-selection branch are explicitly out of scope for this feature's tasks — see plan.md Follow-ups. Do not fold them into any task above.
- No v2 backend file may be touched by any task above — see spec.md Non-Goals / NFR-062-01. Feature 061's own `AlbumChildrenDataController`/`AlbumChildrenDataResource` (FR-061-27, `is_pinned`/`is_public`/`is_link_required`) belong to Feature 061's own task list (T-061-48..50), not this one — this feature's tasks only consume the result. T-062-38 (`AlbumConfig`/`RootConfig` fields) is the one genuine exception to "no backend work in this feature's own tasks" — those two resources aren't v2, aren't owned by Feature 061, and this feature is their only consumer for these two new fields.
- No client-side sorting or grouping of tier 2's rows — the backend already guarantees their order (Feature 061 FR-061-26). Do not add a join/sort step; T-062-02's positional walk is the whole story.
- No change to `Thumb.vue`, `ThumbAssetService`, or the Feature 056 Asset endpoint — T-062-11/24 are new callers, not modifications. Do not add new fetching/caching logic to compensate for anything; if it looks like `Thumb.vue` is missing something this feature needs, that belongs in a Feature 056 follow-up, not here.
- No general-purpose PHP-date-format-to-JS library — `phpDateFormat.ts` (T-062-39) is scoped to this feature's own `formatted_min_max` need, not extracted for reuse elsewhere.
- No automated frontend test suite exists in this repo; every "manual verification" task above is the actual verification step, not a placeholder for a future automated test.
