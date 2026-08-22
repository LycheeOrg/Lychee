# Feature 058 Tasks – Album Listing v3 Adoption

_Status: Draft_
_Last updated: 2026-08-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-058-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

## Checklist

- [ ] T-058-01 – Confirm Feature 057 is implemented (dependency gate).
  _Intent:_ This feature's I2-I7 all require a real `GET /api/v3/Albums` to call; confirm `docs/specs/4-architecture/features/057-album-listing-v3/tasks.md` is fully `[x]` before proceeding (already true on this branch — backend for 056/057 exists).
  _Verification commands:_ N/A (documentation check).
  _Notes:_ Plan Dependencies.

- [ ] T-058-02 – Feature test + implement `config/features.php` entry and `ModulesRightsResource::$is_struct_of_array_enabled` (FR-058-01, FR-058-02).
  _Intent:_ Test asserts init payload's `modules.is_struct_of_array_enabled` matches `config('features.struct-of-array')` for both `true`/`false`. Written to fail first, then implement.
  _Verification commands:_
  - `php artisan test --filter=ModulesRightsResource` (confirm exact existing test class name at implementation time)
  - `make phpstan`
  _Notes:_ Plan I1.

- [ ] T-058-03 – Add `album-list-v3-service.ts` and the shared `AlbumListState` Pinia store (`tree` + `getExcludedTargetIds`) (FR-058-03, FR-058-04).
  _Intent:_ DO-058-02/DO-058-03; base-mode-only fetch (no `with_parent_id`/`for_bulk_edit`), `ensureLoaded()`/`invalidate()`, nested-set-stack `tree` getter, pure multi-root `getExcludedTargetIds(rootIds)`. No UI consumer wired yet — verify from the browser console against hand-built fixtures (single-root against `ListAlbums::do()`'s existing behavior; multi-root against a hand-built ancestor/descendant selection).
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: browser-console exercise of `tree`/`getExcludedTargetIds` against a real session.
  _Notes:_ Plan I2.

- [ ] T-058-04 – Manual verification pass: shared store tree/exclusion correctness (NFR-058-03).
  _Intent:_ Side-by-side comparison of the store's `tree` and single-root `getExcludedTargetIds` output against `ListAlbums::do()`'s existing server-side behavior for the same fixture data; separately verify the multi-root case against a hand-built selection containing an ancestor/descendant pair.
  _Verification commands:_ Manual/browser-based (no automated frontend suite exists in this repo).
  _Notes:_ Plan I2.

- [ ] T-058-05 – Add `Thumb.vue` + `thumb-asset-service.ts` (FR-058-05).
  _Intent:_ DO-058-05/DO-058-06; blob fetch via axios with `AbortController` signal, module-level cache keyed by `(albumId, photoId, type)`, abort on unmount, placeholder fallback for `photoId === null` or a failed/aborted fetch. Verified standalone (e.g. dropped into an existing dev page), before any real consumer depends on it.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: DevTools Network panel — confirm request de-dup for two same-key `<Thumb>` instances (S-058-13), confirm `(canceled)` status when unmounted mid-request (S-058-12).
  _Notes:_ Plan I3.

- [ ] T-058-06 – Rewire `SearchTargetAlbum.vue` onto the shared store + `<Thumb>`, wire store invalidation into move/merge and login/logout call sites (FR-058-06, FR-058-10, FR-058-11, NFR-058-02, NFR-058-07, NFR-058-09, NFR-058-10, S-058-02, S-058-03, S-058-10, S-058-11, S-058-12, S-058-13, S-058-14, S-058-15).
  _Intent:_ Flag-gated v3 path: `ensureLoaded()` from the store, filter via `getExcludedTargetIds(props.albumIds ?? [])`, breadcrumbs from `tree`, thumbnails via `<Thumb>`. Wire `AlbumMove.vue`'s/`AlbumMergeDialog.vue`'s existing post-mutation `AlbumService.clearCache()`/`clearAlbums()` call sites to also call the store's `invalidate()`. Additionally wire `LoginForm.vue`'s `login()` success handler (line 125) and `LeftMenu.vue`'s `logout()` handler (line 170) to call the store's `invalidate()`, next to their existing `AlbumService.clearCache()` calls — a login/logout changes which albums are visible, so a list cached under the prior identity must not be served under the new one.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` in both flag states; `AlbumMergeDialog.vue` with 3 albums selected including an ancestor/descendant pair; DevTools Network panel across sequential dialog opens for de-dup (S-058-10); populate the store as a guest/lower-privileged user, log in, confirm newly-visible albums appear (S-058-14); populate the store as a logged-in user, log out, confirm the next fetch reflects the new identity (S-058-15).
  _Notes:_ Plan I4.

- [ ] T-058-07 – Add SoA→`AlbumTree[]` adapter and rewire `FixTree.vue` (FR-058-07, S-058-04).
  _Intent:_ DO-058-04; `fetch()` calls the v3 endpoint with `with_parent_id=true` when flagged on (its own separate admin-gated request, not the shared store), adapts the response, hands it to the existing `prepareAlbums()`/WASM pipeline unchanged. `updateFullTree()` untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: run validity-check/repair against seeded inconsistent-tree fixture data, both flag states.
  _Notes:_ Plan I5.

- [ ] T-058-08 – Rewire `BulkAlbumEdit.vue` for client-side pagination/search/select-all (FR-058-08, NFR-058-04, S-058-05, S-058-06, S-058-07, S-058-08).
  _Intent:_ Single `for_bulk_edit=true` fetch when flagged on (its own separate admin-gated request, not the shared store); `load(page)`, debounced search, and "select all matching" reimplemented as in-memory operations; both "numbered" and "infinite-scroll" UI modes keep existing markup. Write endpoints untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: numbered pagination, infinite scroll, search, select-all-matching, both flag states.
  _Notes:_ Plan I6.

- [ ] T-058-09 – Manual verification pass: Fix Tree and Bulk Album Edit parity, flag on vs. off (S-058-01, S-058-04..08).
  _Intent:_ Confirm identical validity-check/repair results (Fix Tree) and identical pagination/search/select-all behavior (Bulk Album Edit) between v2 and v3 paths.
  _Verification commands:_ Manual/browser-based.
  _Notes:_ Plan I5/I6.

- [ ] T-058-10 – Full manual verification pass S-058-01..15 (both flag states) + quality gate + docs sync (NFR-058-05).
  _Intent:_ Confirm S-058-09 (pure `.env` toggle, no rebuild) in addition to re-confirming S-058-01..08/10..15 end to end, including the shared-store de-duplication, `<Thumb>` cancellation/cache scenarios, and login/logout invalidation via DevTools plus a real auth cycle. Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 058 row to Completed.
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test --filter=ModulesRightsResource`
  _Notes:_ Plan I7. Prepare commit summary per AGENTS.md commit protocol; do not commit directly.

## Notes / TODOs

- Feature 057/056 (backend) already implemented on this branch — T-058-01 is a formality, not a blocker.
- `STRUCT_OF_ARRAY_ENABLED` is named for future reuse by a Photos SoA v3 endpoint (Q-058-03); no photos work is included in this task list.
