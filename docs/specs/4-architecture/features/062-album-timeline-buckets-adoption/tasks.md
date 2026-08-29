# Feature 062 Tasks – Album Timeline Buckets Frontend Adoption

_Status: Draft_
_Last updated: 2026-08-30_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-062-`) inside the same parentheses immediately after the task title (omit categories that do not apply).

## Checklist

### I1 – Store: tier 1+2 fetch, positional boundary walk

- [ ] T-062-01 – Implement `AlbumsState.ts`'s new tier 1+2 fetch action, gated on `modules.is_struct_of_array_enabled`, called from the album-navigation flow instead of `AlbumService.getAlbums()` when the flag is on (F-062-01).
  _Intent:_ Parallel fetch of `GET .../children/buckets` and `GET .../children` for the current `album_id`.
  _Verification commands:_ `npm run check`
- [ ] T-062-02 – Implement the positional boundary-walk as a small, independently callable pure function: given tier 1's `bucket_ids`/`counts`/`labels` and tier 2's already-ordered flat children array (Feature 061 FR-061-26 guarantees the match), slice into consecutive runs of `counts[0]`, `counts[1]`, ... children each; if `sum(counts) !== children.length`, fall back to one single unbucketed section covering every child (F-062-02).
  _Intent:_ No join, no sort — purely a positional slice, since the backend already guarantees the order.
  _Verification commands:_ `npm run check`
- [ ] T-062-03 – Manual verification against Feature 061's existing per-source fixture parents (created_at/min_taken_at/max_taken_at/title-date_prefix/title-alphabetical/owner_id): correct bucket order/labels, correct slice boundaries (F-062-01/02, S-062-02, S-062-04).
  _Intent:_ Scenario verification, not a new automated test (this repo has no frontend suite).
  _Verification commands:_ Manual browser check, both flag states, against each fixture parent.

### I2 – Store: background rights fetch + client-side combination

- [ ] T-062-04 – Implement `AlbumsState.ts`'s tier 3 background-fetch action, fired immediately after T-062-01's tier 1+2 resolve; every child's `can_edit`/`can_download`/`can_delete`/`can_move` defaults to `false` until it resolves (F-062-03).
  _Intent:_ Background fetch, not interaction-time.
  _Verification commands:_ `npm run check`
- [ ] T-062-05 – Implement the client-side rights-combination formula as a small pure function: `can_edit = (owner_id === current_user.id && current_user.may_upload) || grants_edit[i]`; `can_download = (owner_id === current_user.id) || grants_download[i]`; `can_delete = (owner_id === current_user.id) || can_delete_children`; `can_move = (owner_id === current_user.id) || can_move_children`; reads `useUserStore()` for `current_user` (F-062-04/05).
  _Intent:_ Make the combination logic match `AlbumPolicy::canEdit`/`canDownload`/`canDelete` exactly (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`).
  _Verification commands:_ `npm run check`
- [ ] T-062-06 – Manual cross-check against Feature 061's existing rights fixtures: individually-shared child, parent-level delete grant, multi-group overlap, admin caller, guest caller — compare this feature's computed booleans against the already-passing backend `AlbumChildrenRightsV3Test` assertions for the same fixtures (F-062-04, NFR-062-04, S-062-05..09/16).
  _Intent:_ The single most important verification in this increment — a silent divergence here is a permission bug.
  _Verification commands:_ Manual browser check against each fixture; cross-reference `tests/Feature_v3/Album/AlbumChildrenRightsV3Test.php`'s fixture setup.

### I3 – Adapter: joined data → `ThumbAlbumResource` shape

- [ ] T-062-07 – Implement the adapter turning each child (plus T-062-05's rights) into a `ThumbAlbumResource`-shaped object (`rights` from T-062-05's output, `can_share`/`can_share_with_users`/`can_transfer`/`can_upload`/`can_access_original` all `false`, `timeline: null`) (F-062-06).
  _Intent:_ Zero-change consumption by `AlbumThumb.vue`/`contextMenu.ts`.
  _Verification commands:_ `npm run check`
- [ ] T-062-08 – Manual side-by-side comparison, flag on vs. off, same fixture album: `AlbumThumb.vue` rendering and the right-click menu's available actions are identical modulo virtualization/sticky headers (F-062-06, NFR-062-02).
  _Intent:_ Confirm the adapter is a true drop-in.
  _Verification commands:_ Manual browser check.

### I4 – Shared composable: row flattening + tile geometry

- [ ] T-062-09 – Implement `resources/js/v8/composables/album/virtualAlbumRows.ts`: `(children[], bucketMeta[], itemsPerRow) → {rows, geometryLookup}` — one header row per bucket, `ceil(count / itemsPerRow)` tile rows per bucket, plus a `(row, col) → absolute box` lookup (F-062-07, DO-062-02).
  _Intent:_ Single shared implementation for both virtualized renderers and the drag-select reimplementation.
  _Verification commands:_ `npm run check`
- [ ] T-062-10 – Manual verification against a small hand-computed fixture (2 buckets, uneven counts, a count not evenly divisible by `itemsPerRow`): row list and geometry lookup match hand-calculated expected values.
  _Intent:_ Catch off-by-one errors in the flattening/geometry math before any renderer depends on it.
  _Verification commands:_ Manual check (e.g. a throwaway console/script harness).

### I5 – Grid virtualization

- [ ] T-062-11 – Implement `AlbumThumbPanelVirtualList.vue`: `useVirtualizer` over T-062-09's flattened rows, sticky header rows (`position: sticky; top: 0`), `ResizeObserver`-driven `itemsPerRow` computed from a *measured* tile width (not a JS duplication of `AlbumThumb.vue`'s Tailwind breakpoints — see plan.md Risks), mirroring `AlbumNavTree.vue`'s `translate3d`/single-spacer/`contain` conventions (F-062-07/08).
  _Intent:_ Core virtualized grid renderer.
  _Verification commands:_ `npm run check`
- [ ] T-062-12 – Wire the `bucketable: false` fallback: headerless windowed tile rows, no sticky-header rows in the flattened list (F-062-09).
  _Intent:_ Mirror the existing `isTimeline && buckets.length > 1` flat-fallback gate.
  _Verification commands:_ `npm run check`
- [ ] T-062-13 – Wire `AlbumThumbPanel.vue`'s branch point: render `AlbumThumbPanelVirtualList.vue` instead of today's plain `v-for` when the flag is on and v3 data is present; flag-off path unchanged.
  _Intent:_ Integration point.
  _Verification commands:_ `npm run check`
- [ ] T-062-14 – Manual DevTools element-count check against a 7,000+-child fixture album, scrolled to top/middle/bottom: mounted `<AlbumThumb>` count stays bounded (F-062-07, NFR-062-03, S-062-03).
  _Intent:_ The scale verification this whole feature exists for.
  _Verification commands:_ Manual DevTools Elements-panel check.
- [ ] T-062-15 – Manual verification: sticky headers render correct labels/order for each bucketable-source fixture parent (created_at/min_taken_at/max_taken_at/both title modes); `OWNER_ID`-sorted parent virtualizes with no headers (F-062-08/09, S-062-02/04).
  _Intent:_ Scenario verification.
  _Verification commands:_ Manual browser check.
- [ ] T-062-16 – Manual scroll-through pass on both mobile and desktop viewports: confirm the new sticky bucket headers don't clash with `AlbumHero.vue` or reproduce the pre-existing sticky-toolbar clipping bug (`STUDY-MOBILE-v8.md` finding #10); if it reproduces, log it as a follow-up rather than silently accepting it.
  _Intent:_ Plan.md Risks item — new sticky UI interacting with existing sticky UI.
  _Verification commands:_ Manual browser check, mobile + desktop viewport widths.

### I6 – List-view virtualization

- [ ] T-062-17 – Implement `AlbumListViewVirtual.vue` as a thin wrapper reusing T-062-09's composable with `itemsPerRow = 1` (F-062-10, DO-062-04).
  _Intent:_ Reuse, not a second implementation.
  _Verification commands:_ `npm run check`
- [ ] T-062-18 – Wire `AlbumListView.vue`'s (or its parent's) branch point for `album_view_mode: 'list'` + flag on; manual verification, including toggling `album_view_mode` mid-session without a re-fetch (F-062-10, S-062-11).
  _Intent:_ Integration + scenario verification.
  _Verification commands:_ Manual browser check.

### I7 – Pagination control removal for the flag-on path

- [ ] T-062-19 – Hide the album `<Pagination>` instance in `AlbumPanel.vue` when the flag is on for the subalbum section; leave `AlbumState.ts`'s `loadMoreAlbums()`/`goToAlbumsPage()` in place, unreachable in that mode (F-062-12).
  _Intent:_ Nothing left to paginate once tier 2 is whole-album-at-once.
  _Verification commands:_ `npm run check`; manual check both flag states.

### I8 – Drag-select against precomputed geometry

- [ ] T-062-20 – Manual baseline pass: exercise today's flag-off `dragAndSelect.ts` DOM-query album-selection branch against a small set of drag rectangles (fully inside viewport, edge-touching, corner-touching) and record the exact resulting selections — the parity baseline the new branch must match.
  _Intent:_ Establish ground truth before reimplementing, per plan.md Risks (avoid silently diverging semantics).
  _Verification commands:_ Manual browser check, flag off.
- [ ] T-062-21 – Implement the geometry-intersection album-selection branch in `dragAndSelect.ts`, gated to flag-on + the virtualized panel active, using T-062-09's geometry lookup (F-062-11, DO-062-05).
  _Intent:_ Replace DOM query with a data-driven intersection test.
  _Verification commands:_ `npm run check`
- [ ] T-062-22 – Manual verification: T-062-20's exact rectangles reproduce identical selections under the new branch (parity); then a drag spanning from above the viewport to below it in the 7,000+-child fixture selects the full expected set, including tiles never mounted during the drag (F-062-11, S-062-12).
  _Intent:_ Confirm both parity and the actual fix.
  _Verification commands:_ Manual browser check, flag on.

### I9 – Mutation re-fetch wiring

- [ ] T-062-23 – Implement `AlbumsState.ts`'s re-fetch action (tier 1+2 re-fetch + tier 3 re-arm) and wire it alongside each existing `AlbumService.clearCache()` call site relevant to the subalbum section's own in-panel mutations (create, delete, move, rename, lock/unlock, visibility change) (F-062-13).
  _Intent:_ Keep the virtualized dataset correct after a same-session mutation.
  _Verification commands:_ `npm run check`
- [ ] T-062-24 – Manual verification per mutation type: create/delete/move/rename/lock/unlock/visibility-change a subalbum from within the open parent, confirm the grid/list reflects it immediately, correct bucket placement included (F-062-13, S-062-14).
  _Intent:_ Scenario verification, one pass per mutation type (mirrors Feature 058's S-058-19..23 precedent).
  _Verification commands:_ Manual browser check, one pass per mutation type.
- [ ] T-062-25 – Manual verification: delete a subalbum several screens below the current scroll position, confirm the visible tiles above it do not shift after the resulting re-fetch (NFR-062-05, S-062-13).
  _Intent:_ Scroll-position stability check.
  _Verification commands:_ Manual browser check.

### I10 – TagAlbum/PersonAlbum parity pass

- [ ] T-062-26 – Manual verification against a `TagAlbum`/`PersonAlbum` fixture: matching-albums set matches v2 parity, in the order tier 2 already returns them (instance-wide default sort, Feature 061 FR-061-26); Delete/Move disabled unless the caller owns that specific matching album (F-062-05, S-062-10).
  _Intent:_ Confirm the whole I1–I9 pipeline behaves correctly for both non-`Album` browse types.
  _Verification commands:_ Manual browser check.

### I11 – Accessibility pass

- [ ] T-062-27 – Add `aria-posinset`/`aria-setsize` (or an equivalent `role="grid"` pattern) to virtualized tile/row wrappers, reflecting true position/total rather than the mounted subset's (NFR-062-07).
  _Intent:_ Avoid silently regressing assistive-tech semantics via virtualization.
  _Verification commands:_ `npm run check`
- [ ] T-062-28 – Manual screen-reader or DOM-inspection spot check against a large fixture album, confirming reported position/total match the true child count, not the mounted count.
  _Intent:_ NFR verification.
  _Verification commands:_ Manual check.

### I12 – Documentation

- [ ] T-062-29 – Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`, `docs/specs/4-architecture/roadmap.md`.
  _Intent:_ Documentation Deliverables.
  _Verification commands:_ N/A (review only).

## Notes / TODOs

- Photo-grid virtualization, root-scope (`Albums.vue`) bucketing/virtualization, `bucket_id`-windowed tier-2 pagination, and `dragAndSelect.ts`'s photo-selection branch are explicitly out of scope for this feature's tasks — see plan.md Follow-ups. Do not fold them into any task above.
- No v2 backend file may be touched by any task above — see spec.md Non-Goals / NFR-062-01. (Feature 061's own FR-061-26 ordering fix is already applied and complete — it belongs to Feature 061's task list, not this one.)
- No client-side sorting or grouping of tier 2's rows — the backend already guarantees their order (Feature 061 FR-061-26). Do not reintroduce a join/sort step; T-062-02's positional walk is the whole story.
- No automated frontend test suite exists in this repo; every "manual verification" task above is the actual verification step, not a placeholder for a future automated test.

## Amendment Log

- **2026-08-30:** removed the original I1 (a new backend `AlbumConfig` field) and simplified what was I2's join/sort logic down to a positional walk (now I1/T-062-01..03), after Feature 061 was amended (FR-061-26) to guarantee tier 2's row order matches tier 1's bucket boundaries. All subsequent increments renumbered down by one; no task content beyond the removed/simplified ones changed.
