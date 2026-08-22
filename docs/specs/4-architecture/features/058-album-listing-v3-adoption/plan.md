# Feature Plan 058 – Album Listing v3 Adoption

_Linked specification:_ `docs/specs/4-architecture/features/058-album-listing-v3-adoption/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Flip `ALBUM_LISTING_V3_ENABLED=true` in `.env` and all three consumers (Move-target picker, Fix Tree, Bulk Album Edit) transparently run on the lighter, cacheable v3 endpoint with no visible behavior change to the end user — including thumbnails. Flip it back (or leave it unset) and everything behaves exactly as it does today. Success = FR-058-01..07 implemented, S-058-01..09 manually verified in both flag states, `npm run check`/`format` and `make phpstan`/`php-cs-fixer` clean.

## Scope Alignment

- **In scope:** `config/features.php` entry; `ModulesRightsResource` addition; new `album-list-v3-service.ts`; flag-gated rewiring of `SearchTargetAlbum.vue`, `FixTree.vue`, `BulkAlbumEdit.vue`; new small TS tree/breadcrumb helper; new SoA→AoS adapter for Fix Tree.
- **Out of scope:** Any change to `routes/api_v2.php`, `Actions\Album\ListAlbums`, `FullTree::check()`, `BulkAlbumController`, or Feature 057/056's backend contracts; any change to `treeOperations.ts`'s WASM module itself; any new backend routes.

## Dependencies & Interfaces

- Feature 057 (`GET /api/v3/Albums`, `AlbumListResource`, `cover_ids`) — **must already be implemented** before this feature's work can be manually verified end-to-end (I2 onward depends on a real v3 endpoint to call). If 057 isn't implemented yet when this plan starts, front-load 057's implementation first.
- Feature 056 (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`) — already implemented (roadmap: Completed Features).
- `App\Http\Resources\Rights\ModulesRightsResource` (`app/Http/Resources/Rights/ModulesRightsResource.php`).
- `resources/js/services/album-service.ts` (`getTargetListAlbums`, to be left in place as the flag-off path).
- `resources/js/services/maintenance-service.ts` (`fullTreeGet`, left in place).
- `resources/js/services/bulk-album-edit-service.ts` (`getAlbums`/`getIds`, left in place).
- `resources/js/v8/composables/album/treeOperations.ts` (WASM bridge, untouched — only fed adapted data).
- `resources/js/v8/components/forms/album/SearchTargetAlbum.vue`, `resources/js/v8/views/FixTree.vue`, `resources/js/v8/views/BulkAlbumEdit.vue`.

## Assumptions & Risks

- **Assumptions:** Feature 057 is implemented and its `cover_ids`/`for_bulk_edit`/`with_parent_id` behavior matches its spec exactly (re-verify at I1 if any drift occurred since 057's Implementation Drift Gate).
- **Risks / Mitigations:**
  - *Risk:* An `<img>` tag hitting `GET /api/v3/Asset/...` for an album whose cover photo was deleted between the listing fetch and render (race) returns a 403/404, showing a broken-image icon. *Mitigation:* bind the `<img>`'s `@error` handler to swap to the existing placeholder asset (same technique likely already used for other thumbnail displays in this codebase — confirm at I2 and reuse).
  - *Risk:* Client-side subtree exclusion for the move-picker (FR-058-04) is security-relevant (prevents moving an album into its own descendant) — a bug here is worse than a bug in, say, a display-only breadcrumb. *Mitigation:* NFR-058-03's explicit side-by-side manual comparison against `ListAlbums::do()`'s existing exclusion logic (`app/Actions/Album/ListAlbums.php:44-53`) before considering I2 done; also note the backend `Move`/`MoveAlbums` action itself presumably re-validates the target isn't a descendant server-side regardless (confirm at I2 — if it does, this client-side exclusion is UX-only, not the actual security boundary, which lowers this risk's severity and should be documented).
  - *Risk:* Bulk Album Edit holding the full curated album list in browser memory could be slow on very large installs. *Mitigation:* accepted (NFR-058-04), not solved here; flagged in Follow-ups.
  - *Risk:* No automated frontend test suite exists to catch a regression automatically. *Mitigation:* thorough manual verification per Feature 054's precedent, in both flag states, before considering the feature done.

## Implementation Drift Gate

At I1, re-read `app/Http/Resources/Rights/ModulesRightsResource.php` and Feature 057's `AlbumListController`/`AlbumListResource` to confirm no drift since this plan was drafted (2026-08-22). Record findings here.

## Increment Map

1. **I1 – Backend flag + init exposure**
   - _Goal:_ `config/features.php` entry and `ModulesRightsResource` addition, independently testable before any frontend work starts.
   - _Preconditions:_ Feature 057 implemented (or its I1-I4 at minimum, so `GET /api/v3/Albums` exists to call later).
   - _Steps:_
     - Feature test first: assert `modules.is_album_listing_v3_enabled` in the init response matches `config('features.album-listing-v3')` for both `true`/`false` (set via `config()` override in the test, not `.env`, per this repo's existing config-flag test convention).
     - Add `'album-listing-v3' => (bool) env('ALBUM_LISTING_V3_ENABLED', false)` to `config/features.php`, following the existing doc-comment-block style.
     - Add `public bool $is_album_listing_v3_enabled = false;` + `isAlbumListingV3Enabled()` resolver to `ModulesRightsResource`, wired into its constructor.
   - _Commands:_ `php artisan test --filter=ModulesRightsResource` (or the existing init-payload test class name — confirm exact name at I1), `make phpstan`.
   - _Exit:_ Flag round-trips correctly; `make phpstan` clean.

2. **I2 – Move-target picker (`SearchTargetAlbum.vue`)**
   - _Goal:_ Flag-gated v3 path for the highest-traffic, lowest-complexity consumer first (de-risks the pattern before the two heavier admin pages).
   - _Preconditions:_ I1 done; Feature 057's endpoint reachable.
   - _Steps:_
     - Add `resources/js/services/album-list-v3-service.ts` (DO-058-02).
     - Add the tree/breadcrumb/exclusion TS helper (DO-058-03) — confirm at this step whether the backend `Move`/`MoveAlbums` action re-validates descendant-safety server-side (per the Risks note above) and document the finding in this plan before proceeding.
     - Rewire `SearchTargetAlbum.vue`: when `modules.is_album_listing_v3_enabled`, call the new service, run the helper, render thumbnails via `<img src="/api/v3/Asset/{id}/{cover_id}/{size_variant}">` with an `@error` fallback to the placeholder; else keep today's `AlbumService.getTargetListAlbums()` path unchanged.
     - Manual verification: exercise `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` in both flag states (NFR-058-02).
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-01/02/03 manually verified.

3. **I3 – Fix Tree page**
   - _Goal:_ Flag-gated v3 path with a SoA→AoS adapter, zero changes to the WASM module.
   - _Preconditions:_ I1 done.
   - _Steps:_
     - Add the SoA→`AlbumTree[]` adapter (DO-058-04).
     - Rewire `FixTree.vue`'s `fetch()`: when the flag is on, call `AlbumListV3Service.getAlbums({with_parent_id: true})` + adapter instead of `MaintenanceService.fullTreeGet()`; feed the result into the existing `prepareAlbums()`/WASM pipeline unchanged. `updateFullTree()` (save) untouched regardless of flag.
     - Manual verification: run the existing validity-check/repair flow in both flag states against the same seeded inconsistent-tree fixture data.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-04 manually verified.

4. **I4 – Bulk Album Edit page**
   - _Goal:_ Flag-gated v3 path with client-side pagination/search/select-all, the largest rewrite of the three.
   - _Preconditions:_ I1 done.
   - _Steps:_
     - Rewire `BulkAlbumEdit.vue`: when the flag is on, fetch once via `AlbumListV3Service.getAlbums({for_bulk_edit: true})`; reimplement `load(page)` as an in-memory slice, the debounced search handler as an in-memory filter (matching v2's title-substring semantics), and "select all matching" as a filter over the same in-memory list (no `::ids` call). Both "numbered" and "infinite-scroll" UI modes keep their existing markup/props, only their data source changes.
     - Manual verification: numbered pagination, infinite scroll, search, select-all-matching, in both flag states.
   - _Commands:_ `npm run check`, `npm run format`.
   - _Exit:_ S-058-05/06/07/08 manually verified.

5. **I5 – Quality gate, docs, wrap-up**
   - _Goal:_ Full verification and documentation sync.
   - _Preconditions:_ I1–I4 done.
   - _Steps:_
     - `npm run format`; `npm run check`; `make phpstan`; `vendor/bin/php-cs-fixer fix`; `php artisan test --filter=ModulesRightsResource`.
     - Full manual pass of S-058-01..09 (both flag states), documenting results in this plan's Implementation Drift Gate.
     - Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 058 row to Completed.
     - Prepare commit summary per AGENTS.md commit protocol — do not commit directly.
   - _Commands:_ as above.
   - _Exit:_ All tasks.md items `[x]`; quality gate green.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-058-01 | I1-I4 / T-058-01..08 | Flag-off regression guard, checked at every increment. |
| S-058-02 | I2 / T-058-03 | Move dialog, flag on. |
| S-058-03 | I2 / T-058-03 | Move dialog, missing cover. |
| S-058-04 | I3 / T-058-05 | Fix Tree, flag on. |
| S-058-05 | I4 / T-058-07 | Bulk edit, numbered pagination. |
| S-058-06 | I4 / T-058-07 | Bulk edit, infinite scroll. |
| S-058-07 | I4 / T-058-07 | Bulk edit, search. |
| S-058-08 | I4 / T-058-07 | Bulk edit, select-all-matching. |
| S-058-09 | I5 / T-058-08 | Pure `.env` toggle, no rebuild. |

## Analysis Gate

Not yet run. Per AGENTS.md, run the analysis gate checklist once spec, plan, and tasks agree, before starting I1 — and confirm Feature 057 is implemented first (see Dependencies).

## Exit Criteria

- FR-058-01..07 and NFR-058-01..06 implemented.
- S-058-01..09 manually verified in both flag states.
- `npm run check`/`npm run format` clean; `make phpstan`/`vendor/bin/php-cs-fixer fix` clean.
- Docs updated (`api-design.md`, `knowledge-map.md`, `roadmap.md`).
- Open questions Q-058-01..02 remain resolved (recorded in spec.md Appendix).

## Follow-ups / Backlog

- If Bulk Album Edit's full-in-memory approach proves too heavy on very large installs, revisit with a dedicated paginated/searchable v3 variant — no evidence of a problem exists yet, so not pursued now (NFR-058-04).
- Once this feature is stable, consider a separate future feature to retire the now-unused v2 endpoints entirely — explicitly out of scope here (NFR-058-01), since other unaudited consumers may still exist.
