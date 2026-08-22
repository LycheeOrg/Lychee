# Feature Plan 058 – Album Listing v3 Adoption

_Linked specification:_ `docs/specs/4-architecture/features/058-album-listing-v3-adoption/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Flip `STRUCT_OF_ARRAY_ENABLED=true` in `.env` and all three consumers (Move-target picker, Fix Tree, Bulk Album Edit) transparently run on the lighter, cacheable v3 endpoint — with full functional parity to v2 (including the "move to root" option), a new breadcrumb display the v8 picker doesn't have today, thumbnails rendered through a shared, cancellation-safe `<Thumb>` component instead of a bare `<img>`, and the Move/Merge picker drawing its album list from one session-cached shared store instead of re-fetching per dialog — correctly invalidated on every login/registration/logout transition and every regular-Album mutation that changes what the store should return. Flip the flag back (or leave it unset) and everything behaves exactly as it does today. Success = FR-058-01..12 implemented, S-058-01..24 manually verified in both flag states, `npm run check`/`format` and `make phpstan`/`php-cs-fixer` clean.

## Scope Alignment

- **In scope:** `config/features.php` entry (`struct-of-array`); `ModulesRightsResource` addition (`is_struct_of_array_enabled`); new `album-list-v3-service.ts`; new shared `AlbumListState` Pinia store (tree + multi-root exclusion); new `Thumb.vue` component + `thumb-asset-service.ts`; flag-gated rewiring of `SearchTargetAlbum.vue`, `FixTree.vue`, `BulkAlbumEdit.vue`; new SoA→AoS adapter for Fix Tree.
- **Out of scope:** Any change to `routes/api_v2.php`, `Actions\Album\ListAlbums`, `FullTree::check()`, `BulkAlbumController`, `Actions\Album\Move`, or Feature 057/056's backend contracts; any change to `treeOperations.ts`'s WASM module itself; any new backend routes; any Photos SoA endpoint or consumer (the flag is named generally for that future work, but nothing photos-related is built here); **anything under `resources/js/v7/`** — every v8 file this feature reads or wires `AlbumListState.invalidate()` into (`SearchTargetAlbum.vue`, `FixTree.vue`, `BulkAlbumEdit.vue`, `LoginForm.vue`, `WebauthnModal.vue`, `RegisterPage.vue`, `LeftMenu.vue`, `AlbumDelete.vue`, `DeleteDialog.vue`, `Unlock.vue`, `AlbumVisibility.vue`, `ImportFromServer.vue`) has an independent `resources/js/v7/...` counterpart with its own `AlbumService.clearCache()`/`clearAlbums()` call, left untouched — this feature is v8-only, consistent with the project's default of forking/replacing in v8 rather than editing v7 in place.

## Dependencies & Interfaces

- Feature 057 (`GET /api/v3/Albums`, `AlbumListResource`, `cover_ids`) — **already implemented** (backend, this branch).
- Feature 056 (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`) — already implemented; authorization is resolved through `album_id` via `AlbumPolicy::CAN_ACCESS` plus photo-membership/cover checks (`GetPhotoAssetRequest`), which is why `<Thumb>` takes `album-id` as a required prop, not just `photo-id`.
- `App\Http\Resources\Rights\ModulesRightsResource` (`app/Http/Resources/Rights/ModulesRightsResource.php`).
- `resources/js/services/album-service.ts` (`getTargetListAlbums`, `clearCache`, `clearAlbums` — the flag-off path and the existing cache-invalidation convention this feature's store mirrors, NFR-058-09).
- `resources/js/services/maintenance-service.ts` (`fullTreeGet`, left in place).
- `resources/js/services/bulk-album-edit-service.ts` (`getAlbums`/`getIds`, left in place).
- `resources/js/services/upload-service.ts` — existing precedent for the `AbortController`/axios `signal` pattern `thumb-asset-service.ts` reuses.
- `resources/js/stores/AlbumsState.ts` / `AlbumState.ts` — existing Pinia `defineStore("id", {state, getters, actions})` Options-API convention the new `AlbumListState.ts` follows.
- `resources/js/v8/composables/album/treeOperations.ts` (WASM bridge, untouched — only fed adapted data).
- `resources/js/v8/components/forms/album/SearchTargetAlbum.vue`, `resources/js/v8/views/FixTree.vue`, `resources/js/v8/views/BulkAlbumEdit.vue`.
- `resources/js/v8/components/forms/album/AlbumMove.vue`, `resources/js/v8/components/forms/gallery-dialogs/MoveDialog.vue`, `resources/js/v8/components/forms/photo/PhotoCopyDialog.vue`, `resources/js/v8/components/forms/gallery-dialogs/AlbumMergeDialog.vue` — the four `SearchTargetAlbum` call sites; none require prop/event changes (NFR-058-02), but all four benefit from the shared store's de-duplication (NFR-058-07).
- `resources/js/v8/components/forms/auth/LoginForm.vue` (`login()`, `:125`), `resources/js/v8/components/modals/WebauthnModal.vue` (`login()`, `:65`), `resources/js/v8/views/RegisterPage.vue` (registration success, `:125`), and `resources/js/v8/menus/LeftMenu.vue` (`logout()`, `:170`, alongside `photoStore.reset()`/`photosStore.reset()`/`albumsStore.reset()`/`albumStore.reset()`) — the four existing identity-transition `AlbumService.clearCache()` call sites the shared store's `invalidate()` is additionally wired into (FR-058-11).
- `resources/js/v8/components/forms/album/AlbumDelete.vue` (`:67`), `resources/js/v8/components/forms/gallery-dialogs/DeleteDialog.vue` (`executeDeleteAlbum()`, `:105`), `resources/js/v8/components/forms/album/Unlock.vue` (`:48`), `resources/js/v8/components/forms/album/AlbumVisibility.vue` (`:149`), `resources/js/v8/views/FixTree.vue` (post-save, `:156`), `resources/js/v8/components/modals/ImportFromServer.vue` (`:170`) — the six existing regular-Album-mutation `AlbumService.clearCache()`/`clearAlbums()` call sites the shared store's `invalidate()` is additionally wired into (FR-058-12), found via an audit of every such call site in `resources/js/v8` (full list and exclusion reasoning in Q-058-07).

## Assumptions & Risks

- **Assumptions:** Feature 057's `cover_ids`/`for_bulk_edit`/`with_parent_id` behavior matches its spec exactly (re-verify at I1 if any drift occurred since 057's Implementation Drift Gate). Feature 056's Asset endpoint route is `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` (confirmed against `routes/api_v3.php` and `GetPhotoAssetRequest` while drafting this revision — `album_id` is required because authorization is resolved through `AlbumPolicy::CAN_ACCESS` on that album plus a photo-membership/cover check, not through `PhotoPolicy` alone).
- **Risks / Mitigations:**
  - *Risk:* Client-side subtree exclusion (FR-058-04) is security-relevant (prevents moving/merging an album into its own or a co-selected album's descendant) — a bug here is worse than a bug in, say, a display-only breadcrumb. *Mitigation:* NFR-058-03's explicit side-by-side manual comparison against `ListAlbums::do()`'s existing single-root exclusion, plus a dedicated multi-root fixture for the merge case (no server precedent exists for that case — see next risk). *Confirmed during this plan's drafting:* the actual server-side backstop is `NodeTrait::appendNode()`'s `\LogicException` guard (`vendor/lychee-org/nestedset/src/NodeTrait.php:1199-1200`), called per-album from `Actions\Album\Move::do()` with no visible dedicated HTTP-exception translation — so a client that bypassed this feature's exclusion would likely hit an uncaught 500, not a clean 4xx. This raises the severity of getting FR-058-04 right: it is the actual UX-facing (and arguably only graceful) safety net, not merely a nicety. Whether the backend should translate that `LogicException` into a proper 4xx is a candidate follow-up, explicitly out of scope here (Non-Goals).
  - *Risk:* `<Thumb>`'s module-level object-URL cache (DO-058-06) grows unbounded for the lifetime of the page/SPA session (no eviction), and blob object URLs are not `URL.revokeObjectURL()`'d as long as they stay cached. *Mitigation:* accepted at this feature's scale — a move/merge picker shows at most a few dozen albums, not thousands (unlike Bulk Album Edit's list, NFR-058-04); flagged in Follow-ups if it ever needs an eviction policy. Revoking on unmount is explicitly *not* done, since the whole point of the cache is that a second render of the same `(album_id, photo_id, type)` — e.g. reopening the same dialog — must not re-fetch.
  - *Risk:* An `<img>` inside `<Thumb>` for an album whose cover photo was deleted between the listing fetch and render (race) returns a 403/404 from the Asset endpoint. *Mitigation:* `<Thumb>` itself owns this fallback (FR-058-05) — catch the rejected blob fetch and swap to the placeholder, no `@error` handler needed on a raw `<img>` since the component controls the fetch directly (this is simpler than the previously-drafted `@error`-on-`<img>` approach, since `<Thumb>` never sets `src` to the real endpoint URL directly).
  - *Risk:* The shared store (FR-058-03) going stale after a move/merge performed via the very dialogs that read it. *Mitigation:* NFR-058-09 — wire the store's `invalidate()` into the same call sites that already call `AlbumService.clearCache()`/`clearAlbums()` today (`AlbumMove.vue`, `MoveDialog.vue`, `AlbumMergeDialog.vue`), so this is an additive one-line call at each existing invalidation point, not new logic.
  - *Risk:* The shared store is identity-scoped but has no built-in notion of identity — a list cached as a guest (or a lower-privileged user) would silently under-serve the actual visibility of a user who then logs in within the same SPA session, without a hard page reload in between (unlike `LeftMenu.vue`'s current logout flow, which does `window.location.href = ...` and so incidentally wipes all in-memory state today). *Mitigation:* FR-058-11/NFR-058-10 — wire `invalidate()` into all four existing identity-transition call sites (`LoginForm.vue`, `WebauthnModal.vue`, `RegisterPage.vue`, `LeftMenu.vue`), the same additive one-line-call pattern as the move/merge case, rather than relying on logout's incidental full-page-reload behavior (which none of the other three have, and which logout could lose if that flow is ever changed to a SPA-only transition). `WebauthnModal.vue` and `RegisterPage.vue` were found only by auditing every existing `AlbumService.clearCache()` call site — a first pass that only checked `LoginForm.vue` (the obvious one) would have missed both.
  - *Risk:* A regular-Album mutation outside the move/merge dialogs (delete, unlock, visibility change, Fix Tree's own repair save, server-folder import) also changes what the shared store should return, and none of those are dialogs this feature otherwise touches. *Mitigation:* FR-058-12 — the same audit that found the two extra login call sites also found these six mutation call sites; wire `invalidate()` into each, and explicitly document (Q-058-07) why the *other* `AlbumService.clearCache()` call sites found in that audit (pin-toggle, Tag/Person-Album operations, photo star/highlight) are correctly left alone — none of them change a field this store tracks.
  - *Risk:* Every v8 file this feature reads or wires into has an independent `resources/js/v7/...` counterpart with its own `AlbumService.clearCache()` call (confirmed via `find` across all 11 non-picker files) — easy to accidentally wire the wrong tree's file, or to wonder later why v7 wasn't "fixed" too. *Mitigation:* v7 is explicitly out of scope (Scope Alignment); v7 never reads from `AlbumListState`, so leaving its independent cache-clear calls untouched carries no staleness risk — only v8's rewired `SearchTargetAlbum.vue`/`FixTree.vue`/`BulkAlbumEdit.vue` consume the new store.
  - *Risk:* Bulk Album Edit holding the full curated album list in browser memory could be slow on very large installs. *Mitigation:* accepted (NFR-058-04), not solved here; flagged in Follow-ups. Unaffected by the shared-store work, since Bulk Album Edit deliberately does **not** use the shared store (it needs `for_bulk_edit` fields the store never requests, per Q-058-04's reasoning).
  - *Risk:* No automated frontend test suite exists to catch a regression automatically, and the new caching/cancellation behavior (`<Thumb>`, the shared store) is inherently timing-sensitive, which is harder to verify by eye than a plain data-source swap. *Mitigation:* thorough manual verification per Feature 054's precedent, in both flag states, using the browser DevTools Network panel specifically for the cancellation (S-058-12) and de-duplication (S-058-10) scenarios — these cannot be eyeballed from the rendered UI alone.

## Implementation Drift Gate

At I1, re-read `app/Http/Resources/Rights/ModulesRightsResource.php` and Feature 057's `AlbumListController`/`AlbumListResource` to confirm no drift since this plan was drafted (2026-08-22). Record findings here.

## Increment Map

1. **I1 – Backend flag + init exposure**
   - _Goal:_ `config/features.php` entry and `ModulesRightsResource` addition, independently testable before any frontend work starts.
   - _Preconditions:_ Feature 057 implemented (confirmed on this branch).
   - _Steps:_
     - Feature test first: assert `modules.is_struct_of_array_enabled` in the init response matches `config('features.struct-of-array')` for both `true`/`false` (set via `config()` override in the test, not `.env`, per this repo's existing config-flag test convention).
     - Add `'struct-of-array' => (bool) env('STRUCT_OF_ARRAY_ENABLED', false)` to `config/features.php`, following the existing doc-comment-block style.
     - Add `public bool $is_struct_of_array_enabled = false;` + `isStructOfArrayEnabled()` resolver to `ModulesRightsResource`, wired into its constructor.
   - _Commands:_ `php artisan test --filter=ModulesRightsResource` (or the existing init-payload test class name — confirm exact name at I1), `make phpstan`.
   - _Exit:_ Flag round-trips correctly; `make phpstan` clean.

2. **I2 – Shared album-list store**
   - _Goal:_ `AlbumListState.ts` (DO-058-03) built and independently verifiable before any consumer wires into it — this is the foundation FR-058-06/07/08 all sit on.
   - _Preconditions:_ I1 done; Feature 057's endpoint reachable.
   - _Steps:_
     - Add `resources/js/services/album-list-v3-service.ts` (DO-058-02) if not already present from a prior increment; confirm its shape matches `App.Http.Resources.V3.AlbumListResource`'s generated TS types.
     - Add `resources/js/stores/AlbumListState.ts`: state (raw base-mode arrays, `isLoading`, `error`), `ensureLoaded()`/`invalidate()` actions, `tree` getter (nested-set stack reconstruction — implement and manually sanity-check against a small hand-built fixture: a 2-level tree with a sibling pair), `getExcludedTargetIds(rootIds: string[])` getter/function (single-root case first, verify against `ListAlbums::do()`'s existing behavior; then the multi-root case against a hand-built fixture with a selected ancestor/descendant pair).
     - Manual verification: exercise the store from the browser console (no UI wired yet) against a real logged-in session — confirm `tree` shape and `getExcludedTargetIds` results for a few hand-picked album ids.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ Store's `tree`/`getExcludedTargetIds` verified correct against hand-built fixtures; no consumer wired yet.

3. **I3 – `<Thumb>` component**
   - _Goal:_ `Thumb.vue` + `thumb-asset-service.ts` (DO-058-05/06) built and independently verifiable (e.g. temporarily dropped into an existing dev page) before the move-picker depends on it.
   - _Preconditions:_ I1 done (flag exists, though `<Thumb>` itself is not flag-gated — it is just a component the flag-on path uses).
   - _Steps:_
     - Add `resources/js/services/thumb-asset-service.ts`: `getObjectUrl(albumId, photoId, type, signal)`, module-level `Map` cache keyed by `` `${albumId}:${photoId}:${type}` ``, axios `GET /api/v3/Asset/{albumId}/{photoId}/{type}` with `responseType: 'blob'` and the passed `signal`.
     - Add `resources/js/v8/components/thumbs/Thumb.vue`: `onMounted`/prop-`watch` creates an `AbortController`, calls the service, sets the resolved object URL; `onBeforeUnmount` calls `controller.abort()`; `photoId === null` or a rejected/aborted fetch renders the existing placeholder asset (reuse whatever helper `PhotoThumb.vue`/`useImageHelpers` already exposes for the no-image icon).
     - Manual verification: render several `<Thumb>` instances for the same `(albumId, photoId, type)` on one page, confirm only one network request in DevTools; unmount one mid-request (e.g. via `v-if` toggle), confirm the request shows `(canceled)`.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-12/13-equivalent behavior manually confirmed in isolation, before any real consumer uses the component.

4. **I4 – Move-target picker (`SearchTargetAlbum.vue`)**
   - _Goal:_ Flag-gated v3 path wired to the I2 store and I3 component, covering both single-root Move and multi-root Merge through the same unchanged `album-ids` prop, with full v2 parity (root option) plus the new breadcrumb display.
   - _Preconditions:_ I1-I3 done.
   - _Steps:_
     - Rewire `SearchTargetAlbum.vue`: when `modules.is_struct_of_array_enabled`, call the store's `ensureLoaded()`, filter by `getExcludedTargetIds(props.albumIds ?? [])`, prepend the synthetic "move to root" row when `props.albumIds` is non-empty and `!isTopLevel(props.albumIds[0])` (mirroring `AlbumController::getTargetListAlbums`'s first-selected-album rule), render each option's label via `buildBreadcrumb` through an `#item-label` slot override, render each row's thumbnail via `<Thumb :album-id :photo-id="row.cover_id" type="thumb">` (root row gets `photo-id: null` → placeholder); else keep today's `AlbumService.getTargetListAlbums()` path and `label-key="original"` binding unchanged.
     - Manual verification: exercise `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` in both flag states (NFR-058-02); specifically exercise `AlbumMergeDialog.vue` with 3 albums selected including an ancestor/descendant pair (S-058-11); check the Network panel across dialog opens for de-duplication (S-058-10); verify the root option appears/doesn't appear correctly (S-058-16/17); verify breadcrumb text disambiguates two same-titled albums under different parents (S-058-18).
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-01/02/03/10/11/12/13/16/17/18 manually verified.

5. **I5 – Shared-store invalidation wiring**
   - _Goal:_ Wire the store's `invalidate()` into every existing `AlbumService.clearCache()`/`clearAlbums()` call site identified as relevant (FR-058-11/12) — kept as its own increment, separate from I4, since it touches ten unrelated v8 files rather than the picker itself, and is independently testable.
   - _Preconditions:_ I2 done (store must exist); I4 not required first, but verification is easiest once the picker (I4) can display the effect of each invalidation.
   - _Steps:_
     - Identity transitions (FR-058-11): wire `invalidate()` into `LoginForm.vue`'s `login()` success handler (`:125`), `WebauthnModal.vue`'s `login()` success handler (`:65`), `RegisterPage.vue`'s registration success handler (`:125`), and `LeftMenu.vue`'s `logout()` handler (`:170`) — each alongside its existing `AlbumService.clearCache()` call. All four are v8-only; do not touch the `resources/js/v7/...` counterparts (Scope Alignment).
     - Regular-Album mutations (FR-058-12): wire `invalidate()` into `AlbumMove.vue`/`MoveDialog.vue`/`AlbumMergeDialog.vue` (move/merge, already implied by NFR-058-09), `AlbumDelete.vue:67`, `DeleteDialog.vue:105` (album-delete path), `Unlock.vue:48`, `AlbumVisibility.vue:149`, `FixTree.vue:156` (after `updateFullTree()`'s save), and `ImportFromServer.vue:170` — again v8-only.
     - Explicitly do **not** touch: `AlbumPanel.vue`/`Albums.vue` (`togglePin`), `AlbumCreatePersonDialog.vue`/`AlbumCreateTagDialog.vue`/`TagRenameDialog.vue`/`TagDeleteDialog.vue`/`TagPanel.vue`/`TagMergeDialog.vue` (Tag/Person-Album operations), `Search.vue`/`Timeline.vue` (star/unstar) — confirmed irrelevant to this store's tracked fields (Q-058-07).
     - Manual verification: one pass per included call site (S-058-11/14/15/19..24), confirming the picker (from I4) reflects the change after each; spot-check one excluded call site (e.g. pin-toggle) to confirm the store is *not* invalidated, as expected.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-14/15/19/20/21/22/23/24 manually verified.

6. **I6 – Fix Tree page**
   - _Goal:_ Flag-gated v3 path with a SoA→AoS adapter, zero changes to the WASM module, using its own separate admin-gated fetch (not the I2 shared store).
   - _Preconditions:_ I1 done.
   - _Steps:_
     - Add the SoA→`AlbumTree[]` adapter (DO-058-04).
     - Rewire `FixTree.vue`'s `fetch()`: when the flag is on, call `AlbumListV3Service.getAlbums({with_parent_id: true})` + adapter instead of `MaintenanceService.fullTreeGet()`; feed the result into the existing `prepareAlbums()`/WASM pipeline unchanged. `updateFullTree()` (save) untouched regardless of flag, except for I5's added `invalidate()` call on its completion.
     - Manual verification: run the existing validity-check/repair flow in both flag states against the same seeded inconsistent-tree fixture data.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-04 manually verified.

7. **I7 – Bulk Album Edit page**
   - _Goal:_ Flag-gated v3 path with client-side pagination/search/select-all, using its own separate admin-gated fetch (not the I2 shared store), the largest rewrite of the three.
   - _Preconditions:_ I1 done.
   - _Steps:_
     - Rewire `BulkAlbumEdit.vue`: when the flag is on, fetch once via `AlbumListV3Service.getAlbums({for_bulk_edit: true})`; reimplement `load(page)` as an in-memory slice, the debounced search handler as an in-memory filter (matching v2's title-substring semantics), and "select all matching" as a filter over the same in-memory list (no `::ids` call). Both "numbered" and "infinite-scroll" UI modes keep their existing markup/props, only their data source changes.
     - Manual verification: numbered pagination, infinite scroll, search, select-all-matching, in both flag states.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-05/06/07/08 manually verified.

8. **I8 – Quality gate, docs, wrap-up**
   - _Goal:_ Full verification and documentation sync.
   - _Preconditions:_ I1–I7 done.
   - _Steps:_
     - `npm run format`; `npm run check`; `make phpstan`; `vendor/bin/php-cs-fixer fix`; `php artisan test --filter=ModulesRightsResource`.
     - Full manual pass of S-058-01..24 (both flag states), documenting results in this plan's Implementation Drift Gate.
     - Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 058 row to Completed.
     - Prepare commit summary per AGENTS.md commit protocol — do not commit directly.
   - _Commands:_ as above.
   - _Exit:_ All tasks.md items `[x]`; quality gate green.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-058-01 | I1-I7 / T-058-01..12 | Flag-off regression guard, checked at every increment. |
| S-058-02 | I4 / T-058-06 | Move dialog, flag on. |
| S-058-03 | I4 / T-058-06 | Move dialog, missing cover. |
| S-058-04 | I6 / T-058-09 | Fix Tree, flag on. |
| S-058-05 | I7 / T-058-10 | Bulk edit, numbered pagination. |
| S-058-06 | I7 / T-058-10 | Bulk edit, infinite scroll. |
| S-058-07 | I7 / T-058-10 | Bulk edit, search. |
| S-058-08 | I7 / T-058-10 | Bulk edit, select-all-matching. |
| S-058-09 | I8 / T-058-12 | Pure `.env` toggle, no rebuild. |
| S-058-10 | I4 / T-058-06 | Shared-store request de-duplication across dialogs. |
| S-058-11 | I4 / T-058-06; invalidation re-verified I5 / T-058-07 | Merge dialog, multi-root cyclic exclusion; also exercises move/merge invalidation. |
| S-058-12 | I3 / T-058-05; re-verified I4 / T-058-06 | Thumb request cancellation on unmount. |
| S-058-13 | I3 / T-058-05; re-verified I4 / T-058-06 | Thumb cache reuse across renders. |
| S-058-14 | I5 / T-058-07 | Shared-store invalidation on login (visibility gain). |
| S-058-15 | I5 / T-058-07 | Shared-store invalidation on logout (visibility narrowing). |
| S-058-16 | I4 / T-058-06 | "Move to root" option shown when applicable. |
| S-058-17 | I4 / T-058-06 | "Move to root" option hidden when already at root. |
| S-058-18 | I4 / T-058-06 | Breadcrumb disambiguates same-titled albums. |
| S-058-19 | I5 / T-058-07 | Shared-store invalidation on album delete. |
| S-058-20 | I5 / T-058-07 | Shared-store invalidation on album unlock. |
| S-058-21 | I5 / T-058-07 | Shared-store invalidation on visibility/protection-policy change. |
| S-058-22 | I5 / T-058-07 | Shared-store invalidation on Fix Tree repair save. |
| S-058-23 | I5 / T-058-07 | Shared-store invalidation on server-folder import. |
| S-058-24 | I5 / T-058-07 | Shared-store invalidation on registration (auto-login). |

## Analysis Gate

Not yet run. Per AGENTS.md, run the analysis gate checklist once spec, plan, and tasks agree, before starting I1.

## Exit Criteria

- FR-058-01..12 and NFR-058-01..10 implemented.
- S-058-01..24 manually verified in both flag states.
- `npm run check`/`npm run format` clean; `make phpstan`/`vendor/bin/php-cs-fixer fix` clean.
- Docs updated (`api-design.md`, `knowledge-map.md`, `roadmap.md`).
- Open questions Q-058-01..07 remain resolved (recorded in spec.md Appendix).
- No file under `resources/js/v7/` touched (NFR-058-01).

## Follow-ups / Backlog

- If Bulk Album Edit's full-in-memory approach proves too heavy on very large installs, revisit with a dedicated paginated/searchable v3 variant — no evidence of a problem exists yet, so not pursued now (NFR-058-04).
- Whether `Actions\Album\Move::do()` should catch `NodeTrait::appendNode()`'s `\LogicException` and translate it into a clean 4xx (instead of an uncaught 500) is a candidate backend follow-up, surfaced while investigating Q-058-05 — explicitly out of scope for this feature (Non-Goals: no backend change to `Actions\Album\Move`).
- `<Thumb>`'s object-URL cache has no eviction policy (Risks) — acceptable at this feature's scale; revisit if a future consumer renders far more distinct thumbnails per session than the move/merge picker does.
- `STRUCT_OF_ARRAY_ENABLED` is deliberately named for future reuse by a Photos SoA v3 endpoint (Q-058-03) — that endpoint and its consumer(s) are a separate future feature, not started here.
- The client-side breadcrumb builder (FR-058-04) deliberately doesn't reproduce `ListAlbums::shorten()`'s byte-length proportional-truncation algorithm, relying on CSS truncation instead (Q-058-06) — revisit only if that visually diverges from v2's truncation in a way users notice.
- v7 is entirely out of scope (Non-Goals) — if v7 is ever retired or v8 becomes the only UI, its now-redundant `AlbumService.clearCache()` call sites mirrored by this feature's invalidation wiring become dead code, worth a cleanup pass at that point, not now.
- Once this feature is stable, consider a separate future feature to retire the now-unused v2 endpoints entirely — explicitly out of scope here (NFR-058-01), since other unaudited consumers may still exist.
