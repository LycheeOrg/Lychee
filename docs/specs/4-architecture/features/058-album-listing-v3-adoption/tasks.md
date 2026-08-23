# Feature 058 Tasks – Album Listing v3 Adoption

_Status: Draft_
_Last updated: 2026-08-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-058-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.
>
> **v8 only.** Every file this feature touches lives under `resources/js/v8/` (or the shared, non-versioned `resources/js/stores/`/`resources/js/services/`). Several tasks below name a `resources/js/v7/...` file with the same basename purely to say "leave it alone" — never edit it.

## Checklist

- [ ] T-058-01 – Confirm Feature 057 is implemented (dependency gate).
  _Intent:_ This feature's I2-I8 all require a real `GET /api/v3/Albums` to call; confirm `docs/specs/4-architecture/features/057-album-listing-v3/tasks.md` is fully `[x]` before proceeding (already true on this branch — backend for 056/057 exists).
  _Verification commands:_ N/A (documentation check).
  _Notes:_ Plan Dependencies.

- [ ] T-058-02 – Feature test + implement `config/features.php` entry and `ModulesRightsResource::$is_struct_of_array_enabled` (FR-058-01, FR-058-02).
  _Intent:_ Test asserts init payload's `modules.is_struct_of_array_enabled` matches `config('features.struct-of-array')` for both `true`/`false`. Written to fail first, then implement.
  _Verification commands:_
  - `php artisan test --filter=ModulesRightsResource` (confirm exact existing test class name at implementation time)
  - `make phpstan`
  _Notes:_ Plan I1.

- [ ] T-058-03 – Add `album-list-v3-service.ts` and the shared `AlbumListState` Pinia store (`tree`, `getExcludedTargetIds`, `isTopLevel`, `buildBreadcrumb`) (FR-058-03, FR-058-04).
  _Intent:_ DO-058-02/DO-058-03; base-mode-only fetch (no `with_parent_id`/`for_bulk_edit`), `ensureLoaded()`/`invalidate()`, nested-set-stack `tree` getter, and three pure `lft`/`rgt`-derived getter methods: `getExcludedTargetIds(rootIds)` (multi-root exclusion), `isTopLevel(albumId)` (root-option trigger), `buildBreadcrumb(albumId)` (full ancestor-chain path text, no truncation — CSS handles overflow). No UI consumer wired yet — verify from the browser console against hand-built fixtures (single-root exclusion against `ListAlbums::do()`'s existing behavior; multi-root against a hand-built ancestor/descendant selection; `isTopLevel`/`buildBreadcrumb` against a hand-built 2-3 level tree).
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: browser-console exercise of `tree`/`getExcludedTargetIds`/`isTopLevel`/`buildBreadcrumb` against a real session.
  _Notes:_ Plan I2.

- [ ] T-058-04 – Manual verification pass: shared store tree/exclusion/root/breadcrumb correctness (NFR-058-03).
  _Intent:_ Side-by-side comparison of the store's `tree`, single-root `getExcludedTargetIds`, `isTopLevel`, and `buildBreadcrumb` output against `ListAlbums::do()`/`flatten()`'s existing server-side behavior for the same fixture data (full breadcrumb text only — not `shorten()`'s truncation algorithm, Q-058-06); separately verify the multi-root exclusion case against a hand-built selection containing an ancestor/descendant pair.
  _Verification commands:_ Manual/browser-based (no automated frontend suite exists in this repo).
  _Notes:_ Plan I2.

- [ ] T-058-05 – Add `Thumb.vue` + `thumb-asset-service.ts` (FR-058-05).
  _Intent:_ DO-058-05/DO-058-06; blob fetch via axios with `AbortController` signal, module-level cache keyed by `(albumId, photoId, type)`, abort on unmount, placeholder fallback for `photoId === null` or a failed/aborted fetch. Verified standalone (e.g. dropped into an existing dev page), before any real consumer depends on it.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: DevTools Network panel — confirm request de-dup for two same-key `<Thumb>` instances (S-058-13), confirm `(canceled)` status when unmounted mid-request (S-058-12).
  _Notes:_ Plan I3.

- [ ] T-058-06 – Rewire `SearchTargetAlbum.vue` onto the shared store + `<Thumb>`, add "move to root" option and breadcrumb labels (FR-058-06, FR-058-10, NFR-058-02, NFR-058-03, NFR-058-07, S-058-02, S-058-03, S-058-10, S-058-11, S-058-12, S-058-13, S-058-16, S-058-17, S-058-18).
  _Intent:_ Flag-gated v3 path: `ensureLoaded()` from the store, filter via `getExcludedTargetIds(props.albumIds ?? [])`; when `props.albumIds` is non-empty and `!isTopLevel(props.albumIds[0])`, prepend a synthetic `{id: null}` "move to root" row (mirrors `AlbumController::getTargetListAlbums`'s first-selected-album rule); render each option's label via `buildBreadcrumb` through an `#item-label` slot override (flag-off branch keeps `label-key="original"` unchanged); thumbnails via `<Thumb>` (root row → `photo-id: null` → placeholder).
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` in both flag states; `AlbumMergeDialog.vue` with 3 albums selected including an ancestor/descendant pair; DevTools Network panel across sequential dialog opens for de-dup (S-058-10); moving album with vs. without a parent to confirm the root option's presence/absence (S-058-16/17); two same-titled albums under different parents to confirm breadcrumb disambiguation (S-058-18).
  _Notes:_ Plan I4.

- [ ] T-058-07 – Wire shared-store `invalidate()` into all identity-transition and regular-Album-mutation call sites (FR-058-11, FR-058-12, NFR-058-09, NFR-058-10, S-058-14, S-058-15, S-058-19, S-058-20, S-058-21, S-058-22, S-058-23, S-058-24).
  _Intent:_ Ten v8 call sites, each an additive one-line call next to an existing `AlbumService.clearCache()`/`clearAlbums()` call (never touch the matching `resources/js/v7/...` file). Identity: `LoginForm.vue:125`, `WebauthnModal.vue:65`, `RegisterPage.vue:125`, `LeftMenu.vue:170`. Mutations: `AlbumMove.vue`/`MoveDialog.vue`/`AlbumMergeDialog.vue` (move/merge — likely already wired in T-058-06's dialog work; confirm here if not), `AlbumDelete.vue:67`, `DeleteDialog.vue:105` (album-delete path), `Unlock.vue:48`, `AlbumVisibility.vue:149`, `FixTree.vue:156` (post-`updateFullTree()`), `ImportFromServer.vue:170`. Do **not** wire: `AlbumPanel.vue`/`Albums.vue` (`togglePin`), any Tag/Person-Album dialog (`AlbumCreatePersonDialog.vue`, `AlbumCreateTagDialog.vue`, `TagRenameDialog.vue`, `TagDeleteDialog.vue`, `TagPanel.vue`, `TagMergeDialog.vue`), or `Search.vue`/`Timeline.vue`'s star/unstar callbacks — confirmed irrelevant to this store's tracked fields (Q-058-07).
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual, one pass per included call site: perform the action, reopen a Move/Merge dialog (from T-058-06), confirm the store reflects the change. Login: password (S-058-14) and WebAuthn. Registration (S-058-24). Logout (S-058-15). Delete (S-058-19). Unlock (S-058-20). Visibility change (S-058-21). Fix Tree save (S-058-22) — may need to stub/precede T-058-09 with a minimal Fix Tree v3 read path, or verify this scenario after T-058-09 instead. Server-folder import (S-058-23). Spot-check one excluded site (e.g. pin-toggle) to confirm the store is *not* invalidated.
  _Notes:_ Plan I5. S-058-22's full verification may be more natural after T-058-09 (Fix Tree's v3 read path) exists — order the manual pass accordingly, the wiring itself has no such dependency.

- [ ] T-058-08 – Manual verification pass: move-picker parity, flag on vs. off (NFR-058-02, NFR-058-03, S-058-01).
  _Intent:_ Side-by-side comparison of breadcrumb text, thumbnail, root option, and subtree-exclusion behavior between the v2 (flag-off) and v3 (flag-on) paths for the same fixture data.
  _Verification commands:_ Manual/browser-based (no automated frontend suite exists in this repo).
  _Notes:_ Plan I4/I5.

- [ ] T-058-09 – Add SoA→`AlbumTree[]` adapter and rewire `FixTree.vue` (FR-058-07, S-058-04).
  _Intent:_ DO-058-04; `fetch()` calls the v3 endpoint with `with_parent_id=true` when flagged on (its own separate admin-gated request, not the shared store), adapts the response, hands it to the existing `prepareAlbums()`/WASM pipeline unchanged. `updateFullTree()` untouched except for T-058-07's added `invalidate()` call on its completion.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: run validity-check/repair against seeded inconsistent-tree fixture data, both flag states.
  _Notes:_ Plan I6.

- [ ] T-058-10 – Rewire `BulkAlbumEdit.vue` for client-side pagination/search/select-all (FR-058-08, NFR-058-04, S-058-05, S-058-06, S-058-07, S-058-08).
  _Intent:_ Single `for_bulk_edit=true` fetch when flagged on (its own separate admin-gated request, not the shared store); `load(page)`, debounced search, and "select all matching" reimplemented as in-memory operations; both "numbered" and "infinite-scroll" UI modes keep existing markup. Write endpoints untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: numbered pagination, infinite scroll, search, select-all-matching, both flag states.
  _Notes:_ Plan I7.

- [ ] T-058-11 – Manual verification pass: Fix Tree and Bulk Album Edit parity, flag on vs. off (S-058-01, S-058-04..08).
  _Intent:_ Confirm identical validity-check/repair results (Fix Tree) and identical pagination/search/select-all behavior (Bulk Album Edit) between v2 and v3 paths.
  _Verification commands:_ Manual/browser-based.
  _Notes:_ Plan I6/I7.

- [ ] T-058-12 – Full manual verification pass S-058-01..24 (both flag states) + quality gate + docs sync (NFR-058-05).
  _Intent:_ Confirm S-058-09 (pure `.env` toggle, no rebuild) in addition to re-confirming every other scenario end to end. Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 058 row to Completed. Confirm via `git diff` that no file under `resources/js/v7/` was touched (NFR-058-01).
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test --filter=ModulesRightsResource`
  - `git diff --stat -- resources/js/v7/` (expect empty)
  _Notes:_ Plan I8. Prepare commit summary per AGENTS.md commit protocol; do not commit directly.

## Notes / TODOs

- Feature 057/056 (backend) already implemented on this branch — T-058-01 is a formality, not a blocker.
- `STRUCT_OF_ARRAY_ENABLED` is named for future reuse by a Photos SoA v3 endpoint (Q-058-03); no photos work is included in this task list.
- v8 only — see the checklist header note. Every `resources/js/v7/...` counterpart file stays untouched.
