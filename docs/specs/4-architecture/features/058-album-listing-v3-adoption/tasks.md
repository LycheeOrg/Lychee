# Feature 058 Tasks – Album Listing v3 Adoption

_Status: Draft_
_Last updated: 2026-08-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-058-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

## Checklist

- [ ] T-058-01 – Confirm Feature 057 is implemented (or implement it first) before starting this feature (dependency gate).
  _Intent:_ This feature's I2-I5 all require a real `GET /api/v3/Albums` to call; verify `docs/specs/4-architecture/features/057-album-listing-v3/tasks.md` is fully `[x]` before proceeding.
  _Verification commands:_ N/A (documentation check).
  _Notes:_ Plan Dependencies.

- [ ] T-058-02 – Feature test + implement `config/features.php` entry and `ModulesRightsResource::$is_album_listing_v3_enabled` (FR-058-01, FR-058-02).
  _Intent:_ Test asserts init payload's `modules.is_album_listing_v3_enabled` matches `config('features.album-listing-v3')` for both `true`/`false`. Written to fail first, then implement.
  _Verification commands:_
  - `php artisan test --filter=ModulesRightsResource` (confirm exact existing test class name at implementation time)
  - `make phpstan`
  _Notes:_ Plan I1.

- [ ] T-058-03 – Add `album-list-v3-service.ts`, tree/breadcrumb/exclusion helper, and rewire `SearchTargetAlbum.vue` (FR-058-03, FR-058-04, NFR-058-02, NFR-058-03, NFR-058-06, S-058-02, S-058-03).
  _Intent:_ DO-058-02/DO-058-03; flag-gated v3 path with client-side tree/breadcrumb/subtree-exclusion and `<img>`-based thumbnails (v3 Asset endpoint) with placeholder fallback on error. Confirm and document whether `Move`/`MoveAlbums` re-validates descendant-safety server-side (Risks note in plan.md).
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` in both flag states.
  _Notes:_ Plan I2.

- [ ] T-058-04 – Manual verification pass: move-picker parity, flag on vs. off (NFR-058-02, NFR-058-03, S-058-01, S-058-02, S-058-03).
  _Intent:_ Side-by-side comparison of breadcrumb text, thumbnail, and subtree-exclusion behavior between the v2 and v3 paths for the same fixture data.
  _Verification commands:_ Manual/browser-based (no automated frontend suite exists in this repo).
  _Notes:_ Plan I2.

- [ ] T-058-05 – Add SoA→`AlbumTree[]` adapter and rewire `FixTree.vue` (FR-058-05, S-058-04).
  _Intent:_ DO-058-04; `fetch()` calls the v3 endpoint with `with_parent_id=true` when flagged on, adapts the response, hands it to the existing `prepareAlbums()`/WASM pipeline unchanged. `updateFullTree()` untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: run validity-check/repair against seeded inconsistent-tree fixture data, both flag states.
  _Notes:_ Plan I3.

- [ ] T-058-06 – Manual verification pass: Fix Tree parity, flag on vs. off (S-058-01, S-058-04).
  _Intent:_ Confirm identical validity-check/repair results between v2 and v3 paths.
  _Verification commands:_ Manual/browser-based.
  _Notes:_ Plan I3.

- [ ] T-058-07 – Rewire `BulkAlbumEdit.vue` for client-side pagination/search/select-all (FR-058-06, NFR-058-04, S-058-05, S-058-06, S-058-07, S-058-08).
  _Intent:_ Single `for_bulk_edit=true` fetch when flagged on; `load(page)`, debounced search, and "select all matching" reimplemented as in-memory operations; both "numbered" and "infinite-scroll" UI modes keep existing markup. Write endpoints untouched.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  - Manual: numbered pagination, infinite scroll, search, select-all-matching, both flag states.
  _Notes:_ Plan I4.

- [ ] T-058-08 – Full manual verification pass S-058-01..09 (both flag states) + quality gate + docs sync (NFR-058-05).
  _Intent:_ Confirm S-058-09 (pure `.env` toggle, no rebuild) in addition to re-confirming S-058-01..08 end to end. Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 058 row to Completed.
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test --filter=ModulesRightsResource`
  _Notes:_ Plan I5. Prepare commit summary per AGENTS.md commit protocol; do not commit directly.

## Notes / TODOs

- Depends on Feature 057 being implemented first (T-058-01) — not yet started as of this writing.
