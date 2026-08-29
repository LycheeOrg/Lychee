# Feature Plan 062 – Album Timeline Buckets Frontend Adoption

_Linked specification:_ `docs/specs/4-architecture/features/062-album-timeline-buckets-adoption/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-30

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Let a viewer scroll a 7,000+-direct-child album's subalbum grid as smoothly as a 20-child one — sticky date/title-prefix headers rendered from the server's own bucket aggregation, right-click actions correct the instant they're needed, and drag-select working across the whole list, not just whatever happens to be on screen — all while the flag-off path stays byte-for-byte what it is today. Success is measured by: DOM node count for `<AlbumThumb>` staying bounded (viewport + overscan) regardless of total child count, verified against a 7,000+-child fixture (NFR-062-03); client-side rights combination matching direct `AlbumPolicy` calls exactly across guest/regular/owner/admin/multi-group-overlap callers (NFR-062-04); and every one of Feature 058's and Feature 061's already-shipped pieces (`<Thumb>`, `AlbumListState`, the three v3 endpoints) reused as-is with zero modification beyond Feature 061's own FR-061-26 ordering fix (already applied, see below).

## Scope Alignment

- **In scope:** `AlbumsState.ts` extended with a tier-1+2 fetch pipeline plus a positional-walk boundary derivation (no join or sort needed — tier 2 already arrives ordered per Feature 061 FR-061-26) and a tier-3 background-fetch/rights-merge pipeline, flag-gated; a new pure row-flattening composable (`virtualAlbumRows.ts`) shared by grid and list rendering; a new virtualized grid component (`AlbumThumbPanelVirtualList.vue`) and virtualized list-view component (`AlbumListViewVirtual.vue`), both built on `@tanstack/vue-virtual` following `AlbumNavTree.vue`'s established conventions; client-side rights-combination logic; adapting joined v3 data into `ThumbAlbumResource`-shaped objects so `AlbumThumb.vue`/`contextMenu.ts` need no changes; `TagAlbum`/`PersonAlbum` parity via 061's existing tier 2/3 support; reimplementing `dragAndSelect.ts`'s album-selection branch against precomputed tile geometry instead of DOM queries, scoped to the flag-on virtualized path; hiding the album `<Pagination>` control and re-fetch-on-mutation wiring for the flag-on path.
- **Out of scope:** Photo-grid virtualization (any layout mode); root-scope (`Albums.vue`) bucketing/virtualization; any further v2/v3 backend change beyond Feature 061's own already-applied FR-061-26 fix; any change to `dragAndSelect.ts`'s photo-selection branch or its flag-off album branch; a new feature flag; an automated frontend test suite; visual redesign of `AlbumThumb.vue` itself; a windowed/paginated variant of tier 2; any client-side sorting or grouping of tier 2's rows (redundant now that the backend guarantees their order).

## Dependencies & Interfaces

- Feature 061's three endpoints and resources: `AlbumBucketResource`/`AlbumBucketController`, `AlbumChildrenDataResource`/`AlbumChildrenDataController` (now ordering its rows per FR-061-26), `AlbumChildrenRightsResource`/`AlbumChildrenRightsController` (consumed as-is, zero further backend changes).
- `App\Policies\AlbumPolicy::canEdit()`/`canDownload()`/`canDelete()` (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`) — reference-only, the ground truth the client-side combination formula (FR-062-04) must match; not called or modified by this feature.
- `resources/js/stores/AlbumsState.ts` (extend) — existing store backing `AlbumPanel.vue`'s subalbum section.
- `resources/js/stores/AlbumState.ts` — read-only dependency for `albumId`/navigation state; its v2 `loadAlbums()`/`loadMoreAlbums()` remain the flag-off path, untouched.
- `resources/js/stores/UserState.ts` (`useUserStore()`) — `user.id`/`user.may_upload`/`isGuest`/`isAdmin`, needed for FR-062-04's combination formula.
- `resources/js/v8/components/thumbs/Thumb.vue` (Feature 058, reused unchanged) — cover rendering for each virtualized tile.
- `resources/js/v8/components/gallery/albumModule/AlbumNavTree.vue` — existing `@tanstack/vue-virtual` usage in this codebase; the direct convention/pattern template for DO-062-03/04 (`useVirtualizer` construction, `translate3d` positioning, single spacer sized to `getTotalSize()`, `contain` hints).
- `@tanstack/vue-virtual` (already a direct `package.json` dependency — no new install).
- `resources/js/v8/components/gallery/albumModule/AlbumThumbPanel.vue`/`AlbumThumbPanelList.vue`/`AlbumListView.vue` — branch point: render the new virtualized components instead of today's plain `v-for` when the flag is on and v3 data is present; otherwise fully unchanged.
- `resources/js/v8/composables/contextMenus/contextMenu.ts` — read-only dependency; the exact field reads (`selectedAlbum.rights.can_edit`/`can_move`/`can_delete`/`can_download`) this feature's adapted `ThumbAlbumResource` objects must satisfy (FR-062-06).
- `resources/js/composables/album/dragAndSelect.ts` (extend the album-selection branch only) — `dragAndSelect.ts:187-190`'s current `document.querySelectorAll` call site.
- `resources/js/v8/components/gallery/albumModule/SelectDrag.vue`, `AlbumPanel.vue` (`#galleryView` scroll container) — existing mount points, unchanged.
- `resources/js/services/album-service.ts`'s `AlbumService.clearCache()` convention — the existing invalidation-trigger call sites (`AlbumDelete.vue`, `DeleteDialog.vue`, `Unlock.vue`, `AlbumVisibility.vue`) this feature's own re-fetch (FR-062-13) is wired alongside.

## Assumptions & Risks

- **Assumptions:** The 7,000+-direct-children scale figure Feature 061 confirmed remains the operative target; `@tanstack/vue-virtual`'s documented sticky-header pattern (flattened row list + a custom range extractor keeping the active section's header row always rendered) is sufficient without needing a second, independent sticky-positioning mechanism; Feature 061's FR-061-26 ordering guarantee (tier 2's row order matches tier 1's bucket boundaries exactly) holds for every response this feature reads.
- **Risks / Mitigations:**
  - *Risk:* Reimplementing `dragAndSelect.ts`'s album-selection branch (FR-062-11) against precomputed geometry could silently diverge from the DOM-query branch's exact selection semantics (e.g. edge/corner intersection rules). *Mitigation:* write the geometry-intersection test first against the same fixture/rectangle cases the existing DOM-query branch already handles correctly today (flag-off), confirming byte-for-byte identical selections before extending to the unmounted-tile case DOM-query cannot handle at all.
  - *Risk:* Tier 1 and tier 2 are two separate requests (FR-062-01) — a mutation racing between them (e.g. a subalbum deleted by another session mid-navigation) could produce a `sum(counts) !== children.length` mismatch even though each response is individually correct and correctly ordered. *Mitigation:* FR-062-02's defensive single-unbucketed-section fallback for a count mismatch, plus FR-062-13's re-fetch-on-mutation covers the same-session case; a genuinely concurrent cross-session edit is an accepted, rare staleness window (matches Feature 061's own accepted-staleness precedent for its rights-endpoint cache, FR-061-22).
  - *Risk:* `itemsPerRow` (FR-062-07) depends on `#galleryView`'s content width, which can change (window resize, nav-panel toggle in `AlbumNavPanel.vue`, browser zoom) — a stale `itemsPerRow` would misplace every tile below the resize point. *Mitigation:* `ResizeObserver` on the scroll container recomputes `itemsPerRow` reactively; `useVirtualizer`'s own `getTotalSize()`/row math is a pure function of the current `itemsPerRow`, so a resize is a cheap recompute, not a data re-fetch.
  - *Risk:* Sticky bucket headers (new UI behavior, not present today) could visually clash with `AlbumHero.vue`'s own sticky/hero elements or the existing sticky toolbar bug already flagged in `STUDY-MOBILE-v8.md` finding #10 (title text sliced by a sticky header on scroll). *Mitigation:* verify the new sticky bucket headers sit correctly relative to any existing sticky ancestor via a manual scroll-through pass on both mobile and desktop viewports before considering S-062-02 done; if the pre-existing #10 clipping bug reproduces with sticky bucket headers too, flag it as a follow-up rather than silently accepting a new visible defect.
  - *Risk:* `AlbumThumb.vue`'s responsive tile width uses Tailwind breakpoint classes (`sm:w-[calc(25vw-1rem)]` etc., per `virtual-scrolling-study.md`) rather than a single JS-computed value — `itemsPerRow` computation (FR-062-07) needs to read the *actual rendered* tile width (e.g. via a one-time `getBoundingClientRect()` on a mounted probe tile, recomputed on the same `ResizeObserver` callback as the container width) rather than hand-duplicating the Tailwind breakpoint logic in JS, to avoid the two ever drifting out of sync. *Mitigation:* explicit implementation note in the relevant task (T-062-xx) — measure, don't duplicate.
  - *Risk:* Guest/anonymous browsing (no `UserState.user`) must not throw when FR-062-04's formula reads `current_user.id`/`current_user.may_upload`. *Mitigation:* `useUserStore().isGuest`/`user?.id` already returns a defined (non-throwing) shape for a guest per `UserState.ts`'s existing getters; the formula's owner-branch naturally evaluates to `false` when there's no id to match, verified by S-062-09.

## Implementation Drift Gate

Run the Analysis Gate checklist (`docs/specs/5-operations/analysis-gate-checklist.md`) once this plan and `tasks.md` exist, before I1 begins. Record the outcome under "Analysis Gate" below. Run the Implementation Drift Gate section of the same checklist once all tasks are `[x]` and record findings before marking this feature Complete.

## Increment Map

1. **I1 – Store: tier 1+2 fetch, positional boundary walk**
   - _Goal:_ `AlbumsState.ts` fetches buckets+children together when the flag is on and derives bucket-section boundaries via a positional walk over tier 1's `counts` — no join or sort, since Feature 061 FR-061-26 already guarantees tier 2's row order matches tier 1's bucket boundaries.
   - _Preconditions:_ Spec approved (this plan committed); Feature 061 (incl. FR-061-26) already ships tier 1/2 ordered.
   - _Steps:_ New action(s) + getters on `AlbumsState.ts`; the boundary-walk logic itself factored into a small pure function (`counts[] + flatChildren[] → {bucketId, label, startIndex, count}[]`), including the defensive `sum(counts) !== children.length` fallback; manual verification against Feature 061's existing per-source fixture parents (created_at/min_taken_at/max_taken_at/title-date_prefix/title-alphabetical/owner_id).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-01/02 satisfied; S-062-02/04 pass manually.

2. **I2 – Store: background rights fetch + client-side combination**
   - _Goal:_ Tier 3 fetched immediately after tier 1+2 resolve; combination formula applied reactively.
   - _Preconditions:_ I1 merged.
   - _Steps:_ New action + reactive merge on `AlbumsState.ts`; combination formula implemented as a small pure function, manually cross-checked against Feature 061's existing rights fixtures (individually-shared child, parent-level delete grant, multi-group overlap, admin, guest).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-03/04/05 satisfied; NFR-062-04 verified; S-062-05..09/16 pass manually.

3. **I3 – Adapter: joined data → `ThumbAlbumResource` shape**
   - _Goal:_ `AlbumThumb.vue`/`contextMenu.ts` render/behave identically fed by either data source.
   - _Preconditions:_ I2 merged.
   - _Steps:_ Adapter function; manual side-by-side comparison, flag on vs. off, same fixture album.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-06 satisfied; NFR-062-02 verified.

4. **I4 – Shared composable: row flattening + tile geometry**
   - _Goal:_ Pure, reusable `virtualAlbumRows.ts` (DO-062-02) — the one piece both virtualized renderers and the reimplemented drag-select depend on.
   - _Preconditions:_ I1 merged (needs bucket-boundary metadata's real shape).
   - _Steps:_ Implement `(children[], bucketMeta[], itemsPerRow) → {rows, geometryLookup}`; manually verify against a small hand-computed fixture (2 buckets, uneven counts, non-multiple-of-itemsPerRow) before wiring into any renderer.
   - _Commands:_ `npm run check`.
   - _Exit:_ DO-062-02 ready for consumption by I5/I6/I8.

5. **I5 – Grid virtualization**
   - _Goal:_ `AlbumThumbPanelVirtualList.vue` (DO-062-03), wired into `AlbumThumbPanel.vue`'s branch point.
   - _Preconditions:_ I3, I4 merged.
   - _Steps:_ `useVirtualizer` over I4's flattened rows, sticky header rows, `ResizeObserver`-driven `itemsPerRow` (measured, not duplicated — see plan Risks); manual DevTools element-count check against a 7,000+-child fixture.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-07/08/09 satisfied; NFR-062-03 verified; S-062-02/03/04 pass manually.

6. **I6 – List-view virtualization**
   - _Goal:_ `AlbumListViewVirtual.vue` (DO-062-04), degenerate `itemsPerRow = 1` reuse of I4/I5's mechanism.
   - _Preconditions:_ I5 merged.
   - _Steps:_ Thin wrapper reusing I4's composable; manual verification, flag on, `album_view_mode: 'list'`.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-10 satisfied; S-062-11 passes manually.

7. **I7 – Pagination control removal for the flag-on path**
   - _Goal:_ Hide `<Pagination>` for the subalbum section when the flag is on.
   - _Preconditions:_ I5 merged.
   - _Steps:_ Conditional in `AlbumPanel.vue`; manual verification both flag states.
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-12 satisfied.

8. **I8 – Drag-select against precomputed geometry**
   - _Goal:_ Fix, not just document, the DOM-query gap for the album-selection branch.
   - _Preconditions:_ I4, I5 merged.
   - _Steps:_ New geometry-intersection branch in `dragAndSelect.ts`, gated to flag-on + virtualized panel; manual verification first against today's DOM-query branch's own existing correct cases (parity baseline), then against the unmounted-tile case DOM-query cannot handle (drag spanning above/below viewport in a large fixture).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-11 satisfied; S-062-12 passes manually.

9. **I9 – Mutation re-fetch wiring**
   - _Goal:_ Tier 1+2+3 re-fetch after in-panel mutations (FR-062-13).
   - _Preconditions:_ I2 merged.
   - _Steps:_ Wire the new store action alongside each existing `AlbumService.clearCache()` call site relevant to this section's own mutations; manual verification per mutation type, confirming scroll-position stability (NFR-062-05).
   - _Commands:_ `npm run check`.
   - _Exit:_ FR-062-13 satisfied; NFR-062-05 verified; S-062-13/14 pass manually.

10. **I10 – TagAlbum/PersonAlbum parity pass**
    - _Goal:_ Confirm the full pipeline (I1–I9) behaves correctly when `album_id` resolves to a `TagAlbum`/`PersonAlbum`.
    - _Preconditions:_ I1–I9 merged.
    - _Steps:_ Manual verification against a Tag/Person listing fixture — instance-wide default order (Feature 061 FR-061-26), including the always-`false` delete/move check (FR-062-05).
    - _Commands:_ `npm run check`.
    - _Exit:_ S-062-10 passes manually.

11. **I11 – Accessibility pass**
    - _Goal:_ `aria-posinset`/`aria-setsize` (or equivalent) reflect true position/total, not just the mounted subset.
    - _Preconditions:_ I5/I6 merged.
    - _Steps:_ Add attributes to virtualized tile/row wrappers; manual screen-reader or DOM-inspection spot check.
    - _Commands:_ N/A (manual).
    - _Exit:_ NFR-062-07 verified.

12. **I12 – Documentation**
    - _Goal:_ Satisfy Documentation Deliverables.
    - _Preconditions:_ I1–I11 merged.
    - _Steps:_ Update `api-design.md`, `knowledge-map.md`, `roadmap.md`.
    - _Commands:_ N/A (docs only).
    - _Exit:_ All Documentation Deliverables checked off.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-062-01 | I1–I12 (regression guard, exercised throughout) | Flag-off behavior untouched. |
| S-062-02 | I1, I5 | Bucket order/labels, tier 2's server-guaranteed row order. |
| S-062-03 | I5 | Bounded DOM node count at 7,000+-child scale. |
| S-062-04 | I1, I5 | `bucketable: false` fallback. |
| S-062-05 | I2 | Rights not-yet-loaded default. |
| S-062-06 | I2 | Rights loaded, correct combination. |
| S-062-07 | I2 | Individually-shared child. |
| S-062-08 | I2 | Owner branch. |
| S-062-09 | I2 | Guest branch. |
| S-062-10 | I10 | TagAlbum/PersonAlbum parity. |
| S-062-11 | I6 | List-view virtualization. |
| S-062-12 | I8 | Drag-select across unmounted tiles. |
| S-062-13 | I9 | Scroll-position stability across a mutation re-fetch. |
| S-062-14 | I9 | New subalbum appears correctly placed. |
| S-062-15 | I1–I12 | Flag toggle is a pure config change. |
| S-062-16 | I2 | Admin caller, zero explicit grants. |

## Analysis Gate

_Not yet run — record the outcome here once `tasks.md` exists and before I1 begins._

## Exit Criteria

- All FR/NFR rows in `spec.md` verified per their Measurement column.
- `npm run format`/`npm run check` clean.
- DevTools element-count evidence attached to the Implementation Drift Gate report confirming NFR-062-03 against a 7,000+-child fixture.
- Manual rights-combination cross-check evidence (guest/owner/individually-shared/multi-group-overlap/admin) attached confirming NFR-062-04.
- Documentation Deliverables (I12) complete.
- Implementation Drift Gate report recorded in this plan.

## Follow-ups / Backlog

- **Photo-grid virtualization** — the next phase per `virtual-scrolling-study.md`'s own recommended ordering (grid/square layouts next, justified/masonry last). Not started here.
- **Root-scope (`Albums.vue`) bucketing/virtualization** — blocked on a future backend feature giving `Top::queryRootAlbums()` a v3/bucketed equivalent; out of scope for both 061 and this feature.
- **`bucket_id`-windowed tier-2 pagination** — if a single parent's whole-album-at-once tier-2 payload ever proves too large in practice, Feature 061's own Follow-ups already earmarks the fix (a `bucket_id` query param); revisit only with real payload numbers, not pre-emptively.
- **`dragAndSelect.ts`'s photo-selection branch** — left DOM-query-based (Non-Goals); revisit only if/when photo-grid virtualization (see above) makes that branch's own DOM-query assumption a real problem.

## Amendment Log

- **2026-08-30:** the original draft of this plan included a new backend `AlbumConfig::$effective_album_sorting_column`/`_order` field (I1) plus a client-side join/sort step (in what was then I2), because tier 2 shipped with zero row ordering. That gap was fixed at the source instead — Feature 061 spec.md gained FR-061-26, `AlbumChildrenDataController` now orders its own rows — so this plan's I1 became a much smaller positional-walk step and every downstream increment renumbered down by one. No functional scope was lost; this feature's job shrank because Feature 061's contract got stronger.
