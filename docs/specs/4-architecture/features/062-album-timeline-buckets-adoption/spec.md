# Feature 062 – Album Timeline Buckets Frontend Adoption

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-30 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/062-album-timeline-buckets-adoption/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/062-album-timeline-buckets-adoption/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Feature 061 shipped three backend-only v3 endpoints for an album's direct children — `GET .../children/buckets` (bucket counts + ready-to-render labels), `GET .../children` (per-child render data, whole-album-at-once), `GET .../children/rights` (background-fetched permission signals) — deliberately without a frontend consumer. This feature is that consumer: it replaces the subalbum section of `AlbumPanel.vue` (today driven by `AlbumState.ts`'s v2 `loadAlbums()`/`loadMoreAlbums()`, paginated through `AlbumChildrenController`) with a virtual-scrolled rendering fed by all three endpoints, **entirely behind the existing `is_struct_of_array_enabled` flag** (Feature 058's flag) — mirroring Feature 058's relationship to Feature 057 exactly, and explicitly called out as the intended next step in Feature 061's plan.md Follow-ups.

**Amendment (2026-08-30):** while drafting this feature it surfaced that `GET .../children` (tier 2) had shipped with zero row ordering at all — its query had no `ORDER BY` clause, and the shipped test suite's own comment said so explicitly ("the children endpoint has no bucket_id ordering guarantee of its own"). Rather than have this feature compensate for that client-side (the original draft added a new backend field plus a client-side join/sort step for exactly this reason), the gap was fixed at the source: Feature 061 spec.md gained FR-061-26, and `AlbumChildrenDataController` now orders its rows by `bucket_id` first (mirroring the buckets endpoint's own ordering exactly, `"unknown"` always last) then the effective per-child sort criterion. This means tier 2's flat response already arrives grouped and ordered to match tier 1's bucket boundaries — this feature's store only needs a **positional walk** using tier 1's per-bucket `counts` to find section boundaries, never a client-side join, group-by, or sort. The rest of this feature — virtualization, sticky headers, background rights fetch, client-side rights combination, drag-select — is unaffected by that fix and unchanged from the original design.

Three things still make this more than a data-source swap (unlike 058's three consumers, which kept v2's rendering model and only changed the fetch):

1. **No pagination at all.** Tier 2 returns every direct child in one response — up to the confirmed real-world scale of 7,000+ children under one parent. Rendering that many `<AlbumThumb>` components simultaneously (today's plain `v-for`, per `virtual-scrolling-study.md`'s "Current architecture" findings) would be the exact DOM-node/mount-cost blowup that study document warns about. This feature introduces real windowed rendering for the album grid — the first virtual-scroll consumer in this codebase for a multi-column tile grid (a single-column precedent already exists: `AlbumNavTree.vue`'s `useVirtualizer` usage, which this feature's grid virtualizer extends from one column to N).
2. **Sticky headers must come from the server's bucket aggregation, not client-side re-derivation.** Today's `AlbumThumbPanel.vue`/`splitter.ts` groups an already-loaded flat array by each item's own `.timeline.time_date` field, re-scanning the whole array on every reactive change. This feature instead trusts tier 1's `{bucket_ids, counts, labels}` as the authoritative section boundaries and header text — zero client-side date math (FR-061-18 already promises ready-to-render labels for exactly this reason).
3. **Right-click/multi-select rights are combined client-side, not server-computed.** Feature 061 deliberately ships raw `owner_id`/`can_delete_children`/`can_move_children`/`grants_edit`/`grants_download` signals rather than the combined `can_edit`/`can_download`/`can_delete`/`can_move` booleans `contextMenu.ts` actually reads per selected album today. This feature implements that combination, matching `AlbumPolicy::canEdit`/`canDownload`/`canDelete`'s real logic exactly (verified against `app/Policies/AlbumPolicy.php`, see FR-062-04).

The three endpoints are fetched together on album navigation (not paginated further), with tier 3 (rights) fetched in the background immediately after tier 1+2 render — exactly the sequencing Feature 061's Overview specifies. `TagAlbum`/`PersonAlbum` browsing is included (Feature 061 built tier 2/3 support for both via FR-061-24/25 specifically so this feature could consume it uniformly, matching today's v2 behavior where `AlbumPanel.vue`'s subalbum section already renders identically regardless of the browsed album's type).

## Goals

- New Pinia store logic (extending `resources/js/stores/AlbumsState.ts`, the existing store backing `AlbumPanel.vue`'s subalbum section) that, when the flag is on, fetches tier 1 (`GET .../children/buckets`) and tier 2 (`GET .../children`) together on album navigation, and derives bucket-section boundaries from tier 2's already-ordered flat array via a positional walk keyed by tier 1's per-bucket `counts` (Feature 061 FR-061-26 guarantees tier 2's row order matches tier 1's bucket boundaries exactly — no client-side grouping or sorting needed).
- Background-fetch tier 3 (`GET .../children/rights`) immediately after tier 1+2 resolve — not on right-click/selection — and reactively merge it into each child's rights once it arrives; every right defaults to `false` until the background fetch resolves (safe default, matches Feature 061's Overview sequencing intent).
- Implement the client-side rights-combination logic Feature 061 deliberately left unimplemented: `can_edit = (owner_id === current_user.id && current_user.may_upload) || grants_edit[i]`; `can_download = (owner_id === current_user.id) || grants_download[i]`; `can_delete = (owner_id === current_user.id) || can_delete_children`; `can_move = (owner_id === current_user.id) || can_move_children` — verified to match `AlbumPolicy::canEdit`/`canDownload`/`canDelete`'s actual logic (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`), reusing the fact that every direct child of a regular `Album` always shares that parent's exact `owner_id` (the same invariant 061 used to exclude `OWNER_ID` from bucketing).
- Adapt each child into a `App.Http.Resources.Models.ThumbAlbumResource`-shaped object (client-side-constructed `rights`, `timeline` left `null` — no longer needed once buckets drive sectioning) so `AlbumThumb.vue`, `contextMenu.ts`, and every other existing consumer of `ThumbAlbumResource` need zero changes to their own prop/rights contracts.
- Virtualize the album grid: a new `AlbumThumbPanelVirtualList.vue`, a single `useVirtualizer` instance over a flattened row list (one row per bucket header, one row per `itemsPerRow`-wide slice of tiles), mirroring `AlbumNavTree.vue`'s established `useVirtualizer`/`translate3d`/single-spacer conventions, extended from one column to N (items-per-row computed from the scroll container's width via `ResizeObserver`). Bucket header rows render sticky (`position: sticky; top: 0`) within the scroll container, giving the same "section header stays pinned while its content scrolls" behavior `AlbumThumbPanel.vue`'s existing `UCollapsible` header does not need to reproduce (that header isn't sticky today; this is new, and intentional — see UI States).
- Virtualize the album list view (`album_view_mode === 'list'`) the same way, degenerately (one tile per row, `itemsPerRow = 1`) — same flattened-row `useVirtualizer` model, reusing the same composable.
- Fall back to a single, unbucketed windowed section (no sticky headers) when tier 1 reports `bucketable: false` — mirrors today's existing `isTimeline && buckets.length > 1` gate in `AlbumThumbPanel.vue`/`PhotoThumbPanel.vue`.
- Reimplement `resources/js/composables/album/dragAndSelect.ts`'s album-selection path against the same precomputed tile-geometry model the virtualizer already has to build (row/column → absolute box), instead of `document.querySelectorAll('[data-album-id]')` DOM traversal — the current implementation can only ever select currently-mounted tiles, which becomes actively wrong (not just incomplete) once most tiles are intentionally unmounted. Scoped to the album-grid path only when the flag is on; the v2/flag-off and photo-grid drag-select paths are untouched.
- Stop calling `AlbumState.ts`'s v2 `loadAlbums()`/`loadMoreAlbums()` and hide the albums `<Pagination>` control for the current album's subalbum section when the flag is on (there is nothing to paginate — tier 2 is whole-album-at-once); both remain fully intact and unchanged for the flag-off path.
- Re-fetch (not merely re-render) the joined tier 1+2 dataset after any mutation performed from within the currently-open album's subalbum section that changes what those endpoints would return — create, delete, move, rename (affects sort order/bucket), lock/unlock, visibility change — mirroring the existing `AlbumService.clearCache()` call convention those same actions already trigger today.

## Non-Goals

- **No photo-level virtual scrolling.** Scoped entirely to the subalbum (children) section of `AlbumPanel.vue`. Photo grid virtualization (justified/masonry/grid layouts) is deferred to a later feature per `virtual-scrolling-study.md`'s own phased recommendation ("Albums first... Photos... last").
- **No root-scope (top-level gallery / `Albums.vue`) virtualization or bucketing.** Feature 061 explicitly excluded `Actions\Albums\Top::queryRootAlbums()` from tier 1 (no materialized `bucket_id` concept applies to a mixed-ownership, mixed-album-type root listing); this feature only consumes what 061 shipped, so it inherits that same boundary.
- **No further changes to Feature 061's contract beyond FR-061-26 (already applied, see Overview).** This feature consumes the three endpoints exactly as they now stand; if a genuine new gap is found, it is fixed in 061's spec, not worked around here.
- **No client-side sorting or grouping of tier 2's rows.** Feature 061's FR-061-26 fix means tier 2 already arrives ordered to match tier 1's bucket boundaries — introducing a redundant client-side sort/group step here would be dead code duplicating a guarantee the backend already provides.
- **No v2 backend removal, deprecation, or modification** (beyond FR-061-26's already-applied ordering fix, which belongs to Feature 061, not this one). `AlbumChildrenController`, `GetAlbumChildrenRequest`, `AlbumRepository::getChildrenPaginated()` are untouched and remain the flag-off path, exactly matching Feature 058's NFR-058-01 precedent. "Stop calling v2" (061 plan.md Follow-ups) means the frontend stops calling it *when the flag is on* — not that the route is deleted.
- **No change to `AlbumState.ts`'s photo pagination/timeline logic**, `PhotoThumbPanel.vue`, `splitter.ts`'s photo-side usage, or any photo-selection/drag-select path. Only the album-grid drag-select path is touched, and only when the flag is on.
- **No fix to `dragAndSelect.ts`'s photo-selection geometry lookup or any other consumer of its DOM-query helpers.** Only the album-selection branch is reimplemented against precomputed geometry (Goals); the shared module's photo-selection code path, and its exported DOM-query helpers used elsewhere, are left exactly as-is.
- **No new feature flag.** Reuses `modules.is_struct_of_array_enabled` as-is, consistent with Feature 058's Q-058-02/03 resolution (one combined flag, generically named).
- **No automated frontend test suite.** This repo has none (confirmed Feature 049, reaffirmed Feature 058); verification is manual/browser-based, per Feature 058's precedent.
- **No visual redesign of `AlbumThumb.vue`/tile appearance.** Tiles render identically to today; what changes is which tiles are mounted at any moment, and that bucket section headers become sticky (a deliberate, called-out addition, not incidental).
- **No windowed/paginated variant of tier 2 introduced on the frontend.** Consumes `GET .../children` exactly as 061 ships it — one whole-album-at-once fetch per album navigation. If that payload ever proves too large in practice, 061's own Follow-ups already earmarks a `bucket_id` query-param extension to that endpoint as the fix; this feature does not preempt that.
- **v8 only — no v7 changes**, consistent with every prior struct-of-array feature (057/058/061). `resources/js/v7/...`'s independent, unwindowed subalbum rendering and its own `AlbumService.clearCache()` calls are untouched.
- **No change to how `AlbumThumb.vue` itself resolves a cover image.** Continues to use `<Thumb>` (Feature 058), unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-062-01 | When `modules.is_struct_of_array_enabled === true`, navigating to an album (`AlbumState.ts`'s existing album-load flow) triggers a new `AlbumsState.ts` action that fetches tier 1 (`GET .../children/buckets`) and tier 2 (`GET .../children`) for that `album_id`, in parallel, instead of calling `AlbumService.getAlbums()` (v2). | Both requests resolve; the store holds the raw tier 1/2 responses plus the derived bucket-boundary data FR-062-02 builds from them. | N/A. | Either request failing leaves `albumsStore.albums` empty and surfaces the existing empty-state UI (`gallery.album.no_results`) — no separate new error UI. | None. | Mirrors `AlbumState.ts:192` `loadAlbums()`'s existing call site and error handling. |
| FR-062-02 | The store derives bucket-section boundaries via a **positional walk**, not a join or sort: tier 2's children are already ordered to match tier 1's bucket order and per-bucket count exactly (Feature 061 FR-061-26) — the store simply slices tier 2's flat array into consecutive runs of `counts[0]`, `counts[1]`, ... children each, labeling each run with the corresponding `bucket_ids[i]`/`labels[i]`. Produces bucket-boundary metadata (`{bucketId, label, startIndex, count}[]`) alongside the unmodified tier 2 array. | Walking the flat array in order visits buckets in tier 1's order and, within each bucket, children in tier 2's own (server-guaranteed correct) order — verified against a fixture spanning at least two buckets with 3+ children each. | N/A (derived). | `sum(counts) !== children.length` (should not happen per FR-061-17's correlation guarantee, S-061-30/42 — only a same-session race between the two separate HTTP requests could cause it, see plan.md Risks): the store falls back to one single unbucketed section covering every returned child, rather than slicing against boundaries it can no longer trust. | None. | FR-061-26 (tier 2 ordering guarantee); FR-061-17 (bucket_id join key, still present on each child for this fallback/defensive check). |
| FR-062-03 | Immediately after tier 1+2 resolve (FR-062-01/02), the store fires tier 3 (`GET .../children/rights`) in the background, independent of any user interaction. Until it resolves, every child's `can_edit`/`can_download`/`can_delete`/`can_move` defaults to `false` (safe default — a right-click before the background fetch completes offers no actions rather than incorrect ones); once resolved, the store reactively updates every already-rendered child's rights in place. | A right-click immediately after navigation, before the background fetch completes, shows a context menu with all album-mutating actions disabled rather than either blocking or offering incorrect actions; a right-click after it completes shows the correct actions with zero additional latency (Feature 061's Overview goal: "zero interaction-time latency"). | N/A. | Tier 3 request failing leaves every right `false` permanently for that album visit — no retry, no separate error UI (matches the low-stakes nature of a permissions-menu-only feature; the user can still view/navigate normally). | None. | Feature 061 Overview ("background-fetched... not on the right-click/selection event itself"); FR-061-19/DO-061-10. |
| FR-062-04 | Client-side rights combination, applied per child once tier 3 resolves (FR-062-03): `can_edit = (owner_id === current_user.id && current_user.may_upload) \|\| grants_edit[i]`; `can_download = (owner_id === current_user.id) \|\| grants_download[i]`; `can_delete = (owner_id === current_user.id) \|\| can_delete_children`; `can_move = (owner_id === current_user.id) \|\| can_move_children` — `owner_id`/`can_delete_children`/`can_move_children` are tier 3's whole-response fields (uniform across every direct child of a real `Album`, per FR-061-20's invariant that direct siblings always share their parent's `owner_id`); `current_user` read from `useUserStore()`. Guest (`current_user === undefined`/`isGuest`) → `owner_id === current_user.id` branch is always `false` (no id to match), reducing to the raw `grants_*`/`can_*_children` values tier 3 already resolved correctly for a guest caller (FR-061-21/S-061-38). | Computed values match direct `AlbumPolicy::canEdit`/`canDownload`/`canDelete` calls for the same `(child, caller)` pair exactly, including the admin case (tier 3 already resolves `grants_*`/`can_*_children` to `true` for admins server-side, NFR-061-10 — no separate admin branch needed client-side). | N/A (derived). | N/A — every input has a defined value (booleans, or `owner_id`/`current_user.id` both always strings once loaded). | None. | `AlbumPolicy::canEdit`/`canDownload`/`canDelete` (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`); Feature 061 Non-Goals ("the client already knows its own identity ... combining ... is a small, well-defined client computation — deliberately not replicated server-side"). |
| FR-062-05 | For `TagAlbum`/`PersonAlbum` parents, tier 3's `can_delete_children`/`can_move_children` are always `false` (FR-061-25) — the client-side formula in FR-062-04 still applies unchanged (`(owner_id === current_user.id) \|\| false` reduces correctly to owner-only), so no special-casing is needed for this album type beyond what FR-062-04 already computes. | Right-click menu on a Tag/Person listing's tiles never offers bulk delete/move unless the caller owns that specific matching album; per-tile edit/download remain accurate via `grants_edit`/`grants_download`. | N/A. | N/A. | None. | FR-061-25. |
| FR-062-06 | Each child, plus its combined rights (FR-062-04), is adapted into a `App.Http.Resources.Models.ThumbAlbumResource`-shaped object before being placed into `albumsStore.albums` — `rights` populated from FR-062-04's computed booleans (`can_share`/`can_share_with_users`/`can_transfer`/`can_upload`/`can_access_original` set to `false`, matching Feature 061's deliberate scope exclusion of those signals from tier 3 — none of them are read by the right-click menu on a selection of albums, confirmed against `contextMenu.ts`'s actual field reads), `timeline` set to `null` (bucket sectioning no longer depends on it in this mode — see FR-062-08). | `AlbumThumb.vue`, `contextMenu.ts`, and every other existing `ThumbAlbumResource` consumer render/behave identically whether fed a v2 response or this feature's adapted objects — zero changes to those components' own prop/field contracts. | N/A. | N/A. | None. | Mirrors Feature 058's Q-058-04/FR-058-04 "adapter feeds unchanged consumer" pattern (there: SoA→`AlbumTree[]` for Fix Tree; here: joined v3 data→`ThumbAlbumResource[]`). |
| FR-062-07 | New `resources/js/v8/components/gallery/albumModule/AlbumThumbPanelVirtualList.vue` — a single `useVirtualizer` instance over a flattened row list built from FR-062-02's bucket-boundary metadata: one row per bucket header (rendered `position: sticky; top: 0` within the scroll container) followed by `ceil(bucketCount / itemsPerRow)` tile rows per bucket, `itemsPerRow` computed from `#galleryView`'s content width via `ResizeObserver` divided by `AlbumThumb`'s own responsive tile width breakpoints. Mirrors `AlbumNavTree.vue`'s existing `useVirtualizer(count, getScrollElement, estimateSize, overscan, getItemKey)` / single absolutely-positioned spacer / `translate3d` conventions, generalized from one tile per row to N. | Only tiles within the viewport plus a fixed overscan band are mounted at any time; scrolling to any position shows the correct tiles with no visible pop-in beyond the overscan margin; `scrollHeight` stays correct throughout (single spacer sized to `virtualizer.getTotalSize()`). | N/A. | Zero children in a bucket-count-1 (`bucketable: false`) album → falls through to FR-062-09's flat fallback instead of rendering a single headerless "row" wrapper. | None. | `virtual-scrolling-study.md` "Reactivity model to adopt" section; `AlbumNavTree.vue` (existing precedent). |
| FR-062-08 | Bucket header rows render their text directly from tier 1's `labels[i]` (FR-061-18) — zero client-side date parsing/formatting; `AlbumThumbPanel.vue`'s existing `splitter.ts`-based grouping (computed from each item's own `.timeline.time_date`/`.format`) is bypassed entirely for the flag-on/v3 path (each adapted child's `timeline` field is `null`, FR-062-06 — nothing reads it in this mode). | Sticky headers show the exact label text 061's buckets endpoint computed, with zero drift from what `TimelineData::fromAlbum` would produce for the same config (already guaranteed server-side, S-061-31/32/33). | N/A. | N/A. | None. | FR-061-18; supersedes `splitter.ts` usage for this one rendering path only (flag-off path is fully unaffected, `splitter.ts` itself is not modified). |
| FR-062-09 | When tier 1 reports `bucketable: false` (FR-061-06 — e.g. `OWNER_ID`-sorted parent), the store's boundary metadata collapses to one implicit bucket covering every child (no sticky header row), and `AlbumThumbPanelVirtualList.vue` renders it as plain windowed tile rows with no header row — mirrors today's existing `isTimeline && buckets.length > 1` flat-fallback gate in `AlbumThumbPanel.vue`, generalized to the v3/virtualized path. | An `OWNER_ID`-sorted album's subalbum section still virtualizes (DOM-node benefit is not lost), just without section headers. | N/A. | N/A. | None. | FR-061-06; existing `AlbumThumbPanel.vue:119`/`PhotoThumbPanel.vue:106` gate (`virtual-scrolling-study.md` "Falls back to flat rendering" section). |
| FR-062-10 | New `resources/js/v8/components/gallery/albumModule/AlbumListViewVirtual.vue` (or equivalent extension of `AlbumListView.vue`), used when `album_view_mode === 'list'` and the flag is on: the same flattened bucket-header-plus-rows `useVirtualizer` model as FR-062-07, degenerate to `itemsPerRow = 1` (one list row per virtual row) — reuses the same underlying composable/row-flattening logic as the grid case rather than a second independent implementation. | List view exhibits the same DOM-node-bounded behavior as grid view for the same album; toggling `album_view_mode` between `grid`/`list` mid-session (existing UI control) switches virtualized renderers without needing to re-fetch tier 1/2/3. | N/A. | N/A. | None. | User instruction (cover both view modes, not just the default grid). |
| FR-062-11 | `resources/js/composables/album/dragAndSelect.ts`'s album-selection branch, when the flag is on and the active album panel is the virtualized one, is reimplemented against FR-062-07's precomputed tile-geometry model (row/column index → absolute box, already computed to place tiles) instead of `document.querySelectorAll('[data-album-id]')` (`dragAndSelect.ts:187-190`) — a drag gesture selects every child whose precomputed box intersects the drag rectangle, mounted or not. The photo-selection branch and the flag-off album branch are untouched, still DOM-query-based. | Drag-selecting a region that spans currently-unmounted (virtualized-out) tiles selects them correctly — verified by dragging from above the viewport to below it in a large fixture album, then confirming the full expected set is selected, not just the tiles that happened to be mounted. | N/A. | N/A. | None. | Risk identified in `virtual-scrolling-study.md` ("whether anything ... assumes all photos/albums are simultaneously mounted"); confirmed via `dragAndSelect.ts:187-190`'s actual DOM-query implementation. |
| FR-062-12 | `Pagination.vue`'s album instance in `AlbumPanel.vue` (`:has-more="albumStore.hasMoreAlbums"` etc.) is not rendered when the flag is on for the currently-browsed album's subalbum section (tier 2 is whole-album-at-once — there is nothing left to paginate); `AlbumState.ts`'s `loadMoreAlbums()`/`goToAlbumsPage()` call sites for this section become unreachable in that mode but are not deleted (still used by the flag-off path). | No pagination control is shown for the album grid when the flag is on; scrolling further simply reveals more of the already-fully-loaded, virtualized dataset. | N/A. | N/A. | None. | Mirrors Feature 058's Bulk Album Edit precedent (FR-058-08: fetch once, no server pagination) applied to this consumer. |
| FR-062-13 | The store re-fetches tier 1+2 (FR-062-01) — and re-arms the tier 3 background fetch (FR-062-03) — after any mutation performed from within the currently-open album's subalbum section that changes what those two endpoints would return for `album_id`: create a subalbum, delete/move/rename a subalbum, lock/unlock, visibility/protection-policy change — mirroring the exact call sites `AlbumService.clearCache()` already fires from today for the equivalent v2 refresh (`AlbumDelete.vue`, `DeleteDialog.vue`, `Unlock.vue`, `AlbumVisibility.vue`, plus the existing subalbum-creation/move/rename flows already wired into `AlbumPanel.vue`/`AlbumsState.ts`). | Creating, deleting, moving, renaming, locking/unlocking, or changing visibility of a subalbum from within the open parent immediately reflects in the virtualized grid/list, correct bucket placement included. | N/A. | N/A. | None. | Mirrors Feature 058's FR-058-12 invalidation-net audit, scoped to this feature's own new fetch instead of `AlbumListState`. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-062-01 | No v2 backend route/controller is modified, deprecated, or removed, and no `resources/js/v7/...` file is touched. | Flag-off must stay a true no-op; v7 is out of scope for all struct-of-array work (established convention). | `git diff` shows no changes under `app/Http/Controllers/Gallery/AlbumChildrenController.php`, `routes/api_v2.php`, or anywhere under `resources/js/v7/`. | Feature 058 NFR-058-01 precedent. | Non-Goals. |
| NFR-062-02 | `AlbumThumb.vue`'s and `contextMenu.ts`'s external prop/field contracts are unchanged — both consume the flag-on adapted data exactly as they consume today's v2 `ThumbAlbumResource[]`. | So no unrelated caller of either needs to change. | Manual verification: exercise the subalbum grid, list view, and right-click menu with the flag on and off, confirm identical rendering/behavior modulo virtualization and the newly-sticky headers. | FR-062-06. | Goals. |
| NFR-062-03 | The virtualizer never mounts more than `2 × overscan + (rows in viewport) × itemsPerRow` `<AlbumThumb>` instances at once, regardless of total child count (verified at the confirmed 7,000+-child scale). | The entire reason this feature exists — reproducing today's "mount everything" behavior at that scale would defeat the purpose. | Manual DevTools element-count check against a 7,000+-child fixture album, scrolled to top/middle/bottom. | `virtual-scrolling-study.md` problem statement; FR-062-07. |
| NFR-062-04 | Client-side rights combination (FR-062-04) never diverges from direct `AlbumPolicy::canEdit`/`canDownload`/`canDelete` calls for the same `(child, caller)` pair, across guest/regular/admin/multi-group-overlap callers. | A silent divergence here is a permission bug in a context menu — over-granting is a security issue, under-granting is a functional regression. | Manual verification against each of Feature 061's own rights fixtures (individually-shared child, parent-level delete grant, multi-group overlap, admin, guest) re-exercised through this feature's frontend combination logic, cross-checked against the same fixtures' already-passing backend assertions. | `AlbumPolicy`; NFR-061-09/10 (backend half of this same correctness chain). |
| NFR-062-05 | Scroll position is preserved across a tier 1+2 re-fetch (FR-062-13) for content above the mutated item — a delete/move elsewhere in the list must not visibly jump the viewport. | Baseline UX expectation for any list mutation, called out generically as a risk in `virtual-scrolling-study.md` ("Risks / edge cases"). | Manual verification: delete a subalbum several screens below the current scroll position, confirm the visible tiles above do not shift. | `virtual-scrolling-study.md` Risks section. | User-experience baseline. |
| NFR-062-06 | `npm run format`/`npm run check` clean on all changed frontend files. | Repo quality gate. | Run both. | AGENTS.md. | AGENTS.md. |
| NFR-062-07 | Keyboard/screen-reader semantics are not silently degraded by virtualization: rendered tiles carry `aria-posinset`/`aria-setsize` (or an equivalent `role="grid"` pattern) reflecting the child's true position/total, not just the mounted subset's. | Called out as a specific risk in `virtual-scrolling-study.md` ("Keyboard/screen-reader semantics"). | Manual screen-reader spot check (or DOM inspection of the relevant `aria-*` attributes) against a large fixture album. | `virtual-scrolling-study.md` Risks section. | Accessibility. |

## UI / Interaction Mock-ups

The subalbum grid's individual tile appearance is unchanged (`AlbumThumb.vue` untouched). What's new, visible with the flag on: (1) bucket section headers become **sticky** — scrolling through a bucket's tiles keeps that bucket's date/title-prefix label pinned at the top of the scroll container until the next bucket's header reaches it and takes over, matching the pinned-header behavior of e.g. a contacts app's alphabetical index, rather than today's plain-flow (non-sticky) `UCollapsible`-style headers; (2) scrolling far past the currently-mounted tiles may show a brief blank band for a few frames before the overscan band catches up (same class of behavior as any virtualized list, tunable via the overscan constant); (3) list view (`album_view_mode: 'list'`) gains the same sticky-header/windowing behavior as grid view. See [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](../../spec-guidelines/ui-ascii-mockups.md); no new mock-up is meaningful beyond this description.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-062-01 | Flag off (default): the subalbum section behaves exactly as today — v2 pagination, unwindowed rendering, non-sticky timeline headers via `splitter.ts` (regression guard). |
| S-062-02 | Flag on, parent with 3+ buckets, 3+ children each: grid renders correct sticky headers in tier 1's order, correct children per bucket, in the order tier 2 already returns them (FR-062-02). |
| S-062-03 | Flag on, parent with 7,000+ children fixture: DevTools element count confirms only a bounded window of `<AlbumThumb>` instances mounted at any scroll position (NFR-062-03). |
| S-062-04 | Flag on, parent sorted by `OWNER_ID` (`bucketable: false`): grid still virtualizes, with no sticky headers (FR-062-09). |
| S-062-05 | Flag on, right-click on a subalbum immediately after navigation, before the background rights fetch resolves: context menu shows every album-mutating action disabled (FR-062-03). |
| S-062-06 | Flag on, right-click on a subalbum after the background rights fetch resolves: correct actions offered with zero additional latency, matching a direct `AlbumPolicy` check for that child/caller (FR-062-04, NFR-062-04). |
| S-062-07 | Flag on, a subalbum individually shared with the caller (`grants_edit`, no equivalent grant on siblings): only that subalbum's Edit action is enabled; siblings' Edit remains disabled unless they qualify some other way. |
| S-062-08 | Flag on, caller is the owner of the browsed parent album: every direct child's Edit/Download/Delete/Move actions are enabled regardless of any explicit grant (owner-based branch of FR-062-04). |
| S-062-09 | Flag on, guest caller: rights reduce to whatever public grants tier 3 already resolved; no owner-id match is ever attempted (FR-062-04). |
| S-062-10 | Flag on, `TagAlbum`/`PersonAlbum` browsed: grid/list render the same matching-albums set v2 would (parity, via 061's FR-061-24), in the order tier 2 already returns them (instance-wide default sort, per FR-061-26); Delete/Move are always disabled unless the caller owns that specific matching album (FR-062-05). |
| S-062-11 | Flag on, list view (`album_view_mode: 'list'`): same virtualization/sticky-header behavior as grid view (FR-062-10). |
| S-062-12 | Flag on, drag-select a rectangle spanning from above the viewport to below it, in a large fixture album: every child whose true position intersects the rectangle is selected, including ones never mounted during the drag (FR-062-11). |
| S-062-13 | Flag on, delete a subalbum several screens below the current scroll position: the visible tiles above the deleted one do not visibly shift after the resulting re-fetch (NFR-062-05). |
| S-062-14 | Flag on, create a new subalbum from within the open parent: it appears in the correct bucket/position on the next render, without a manual page reload. |
| S-062-15 | Toggling `STRUCT_OF_ARRAY_ENABLED` (pure `.env`/config change) flips behavior on the next request — confirms no build-time-only wiring, mirrors Feature 058's S-058-09. |
| S-062-16 | Flag on, admin caller, fixture with zero explicit grants anywhere: every right-click action is enabled for every child (owner/admin-agnostic combination formula still correct because tier 3 already resolved `grants_*`/`can_*_children` to `true` server-side for admins, NFR-061-10). |

## Test Strategy

- **Core:** N/A.
- **Application:** N/A — this feature makes no backend changes of its own (the one backend gap it surfaced, FR-061-26, was fixed and tested as part of Feature 061, not this feature).
- **REST:** No new endpoints — this feature is a pure consumer of Feature 061's three. No new REST test files.
- **CLI:** N/A.
- **UI (JS/Selenium):** No automated frontend suite exists in this repo (confirmed Feature 049, reaffirmed Feature 058). Manual/browser-based verification for S-062-01..16, per Feature 058's precedent — including a large (7,000+-child) fixture album for the scale-sensitive scenarios (S-062-03, S-062-12) and the standard rights fixture set already built for Feature 061 (individually-shared child, parent-level delete grant, multi-group overlap, admin, guest) re-exercised through this feature's UI for S-062-05..09/16.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` — note the frontend adoption alongside Feature 061's entries; `docs/specs/4-architecture/knowledge-map.md` updated with the new composables/components/store additions.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-062-01 | `resources/js/stores/AlbumsState.ts` (extended, existing store) — new state: raw tier 1/2/3 responses for the currently-browsed `album_id`; new getters: bucket-boundary metadata derived from tier 1's `counts` via a positional walk over tier 2's already-ordered array (FR-062-02); new actions: `loadAlbumsV3()` (tier 1+2, FR-062-01), `loadAlbumsV3Rights()` (tier 3 background fetch, FR-062-03), `refreshAlbumsV3()` (re-fetch after a mutation, FR-062-13). | UI |
| DO-062-02 | New composable `resources/js/v8/composables/album/virtualAlbumRows.ts` — pure function(s) turning `(children[], bucketMeta[], itemsPerRow)` into a flattened virtualizer row list (header rows + tile rows) and a tile-geometry lookup (`{row, col} → box`); shared by the grid virtual list (FR-062-07), the list-view virtual list (FR-062-10), and the reimplemented drag-select geometry test (FR-062-11). | UI |
| DO-062-03 | New `resources/js/v8/components/gallery/albumModule/AlbumThumbPanelVirtualList.vue` — virtualized grid renderer (FR-062-07/08/09). | UI |
| DO-062-04 | New `resources/js/v8/components/gallery/albumModule/AlbumListViewVirtual.vue` (or equivalent `AlbumListView.vue` extension) — virtualized list-view renderer (FR-062-10). | UI |
| DO-062-05 | `resources/js/composables/album/dragAndSelect.ts` — album-selection branch reimplemented against DO-062-02's geometry lookup when the flag is on and the active panel is virtualized (FR-062-11). | UI |

### API Routes / Services

No new backend routes — reuses API-061-01/02/03 (`GET /Albums/{album_id}/children/buckets`, `.../children`, `.../children/rights`), the last two now ordered per Feature 061 FR-061-26.

### CLI Commands / Flags

None.

### Telemetry Events

None.

### Fixtures & Sample Data

Reuses Feature 061's existing `Feature_v3` fixture graph (bucketed parents, rights fixtures) for manual verification; a locally-generated 7,000+-child fixture (not committed — matches Feature 061's own Implementation Drift Gate approach of a disposable fixture DB copy) for the scale-sensitive scenarios.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-062-01 | Grid view, flag on, bucketable album | Sticky section headers, windowed tile rows (FR-062-07/08). |
| UI-062-02 | Grid view, flag on, non-bucketable (`OWNER_ID`-sorted) album | Windowed tile rows, no section headers (FR-062-09). |
| UI-062-03 | List view, flag on | Same sticky-header/windowing behavior as grid view (FR-062-10). |
| UI-062-04 | Right-click, rights not yet loaded | Every mutating menu action disabled (FR-062-03). |
| UI-062-05 | Right-click, rights loaded | Correct actions enabled per FR-062-04's combination. |
| UI-062-06 | Drag-select spanning unmounted tiles | Full expected selection, not just mounted tiles (FR-062-11). |

## Telemetry & Observability

No new telemetry events.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — note the frontend adoption alongside Feature 061's entries.
- `docs/specs/4-architecture/knowledge-map.md` — reference `AlbumThumbPanelVirtualList.vue`, `AlbumListViewVirtual.vue`, `virtualAlbumRows.ts`, and the `AlbumsState.ts` additions.
- `docs/specs/4-architecture/roadmap.md` — Feature 062 entry.

## Spec DSL

```
domain_objects:
  - id: DO-062-01
    name: AlbumsState (extended) - v3 fetch + positional bucket walk + rights merge
  - id: DO-062-02
    name: virtualAlbumRows composable
  - id: DO-062-03
    name: AlbumThumbPanelVirtualList.vue
  - id: DO-062-04
    name: AlbumListViewVirtual.vue
  - id: DO-062-05
    name: dragAndSelect.ts album-selection geometry reimplementation
routes: []
fixtures: []
ui_states:
  - id: UI-062-01
    description: Grid view sticky headers + windowing, bucketable album
  - id: UI-062-02
    description: Grid view windowing, non-bucketable album, no headers
  - id: UI-062-03
    description: List view sticky headers + windowing
  - id: UI-062-04
    description: Context menu before rights loaded - all mutating actions disabled
  - id: UI-062-05
    description: Context menu after rights loaded - correct actions enabled
  - id: UI-062-06
    description: Drag-select across unmounted tiles - full correct selection
```

## Appendix

### Decision Cards (Q-062-01..03)

#### Q-062-01 — Where the tier 1+2 fetch/boundary logic lives

**Resolved:** extends the existing `resources/js/stores/AlbumsState.ts` (the store already backing `AlbumPanel.vue`'s subalbum section, holding `albums: ThumbAlbumResource[]`) rather than introducing a parallel store, so the flag-on and flag-off paths both populate the exact same store field with the exact same shape (FR-062-06) — the lowest-blast-radius way to keep `AlbumThumb.vue`/`contextMenu.ts` unaware which path fed them. Mirrors Feature 058's Q-058-04 reasoning (shared state, existing store family) but scoped to the already-relevant per-album store rather than a new session-wide one, since this data is inherently per-currently-browsed-album, not cross-component shared like `AlbumListState`.

**Amendment (2026-08-30):** the original draft of this card also covered a client-side join/sort responsibility, motivated by tier 2 shipping unordered. That gap was fixed at the source instead (Feature 061 FR-061-26) — see the spec Overview's Amendment note — so this store's actual job is now a much smaller positional walk over tier 1's `counts`, not a join or sort.

#### Q-062-02 — Grid virtualization technique

**Resolved:** a single `useVirtualizer` instance over a flattened row list (bucket header rows interleaved with N-tile rows), extending `AlbumNavTree.vue`'s already-established single-column `useVirtualizer` pattern to a multi-column grid with sticky section headers — rather than hand-rolling a binary-search-over-precomputed-boxes composable from scratch (the approach `virtual-scrolling-study.md` sketches for the harder justified-photo-layout case). `@tanstack/vue-virtual` is already a **direct** dependency (confirmed in `package.json`, not merely transitive as `virtual-scrolling-study.md` originally noted before this package was later added directly) and already has one real usage in this exact codebase to mirror conventions from (`translate3d` positioning, single absolutely-positioned spacer sized to `getTotalSize()`, `contain: strict`/`layout size paint`). The flattened-row-with-sticky-header-rows approach is `@tanstack/vue-virtual`'s own documented pattern for sticky section headers, not a bespoke technique.

#### Q-062-03 — Fixing vs. documenting the `dragAndSelect.ts` DOM-query gap

**Resolved:** fixed, not merely documented as a degradation (FR-062-11) — the geometry a virtualizer needs to compute (row/column → absolute box for every child, not just mounted ones) is already the exact input `dragAndSelect.ts`'s album-selection branch needs to test rectangle-intersection against, so reusing it turns a would-be regression into a strict improvement over today's DOM-query approach (which already silently only worked for the fully-mounted case, before this feature ever introduces partial mounting). Scoped narrowly: only the album-selection branch, only when the flag is on and the active panel is virtualized — the photo-selection branch and the flag-off album branch are both left exactly as they are today (Non-Goals).
