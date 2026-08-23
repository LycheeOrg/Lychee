# Feature 058 – Album Listing v3 Adoption

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-22 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/058-album-listing-v3-adoption/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/058-album-listing-v3-adoption/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Feature 057 built `GET /api/v3/Albums` but deliberately left every existing v2 consumer untouched. This feature switches the three existing v2 album-listing consumers over to it, **entirely behind one new combined `.env`-driven feature flag** so the cutover is reversible without a code change:

1. The Move-target picker (`resources/js/v8/components/forms/album/SearchTargetAlbum.vue`, shared by `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue`) — today calls `GET /api/v2/Album::getTargetListAlbums` (`Actions\Album\ListAlbums`), which fully hydrates Eloquent models, builds the tree server-side, computes breadcrumb strings, and resolves a thumbnail URL. Moves to the v3 default mode; tree/breadcrumb/subtree-exclusion move client-side via a new shared store; the thumbnail is preserved via a new reusable `<Thumb>` component that queries the separate Feature 056 v3 Asset endpoint (not dropped) using the `cover_id` Feature 057 already added (Q-057-05).
2. The admin Fix Tree page (`resources/js/v8/views/FixTree.vue`) — today calls `GET /Maintenance::fullTree`. Moves to `GET /api/v3/Albums?with_parent_id=true`; a thin adapter reshapes the SoA response back into the `AlbumTree[]` array-of-structs shape the existing WASM validity-checking module (`resources/js/v8/composables/album/treeOperations.ts`) already consumes, so that module itself is untouched. The write-back `POST /Maintenance::fullTree` is untouched.
3. The admin Bulk Album Edit page (`resources/js/v8/views/BulkAlbumEdit.vue`) — today calls paginated, searchable `GET /BulkAlbumEdit`. Moves to a single `GET /api/v3/Albums?for_bulk_edit=true` fetch; pagination (both its "numbered" and "infinite-scroll" modes), debounced search, and "select all matching" become client-side operations over the in-memory result. The write endpoints (`PATCH`/`POST ::setOwner`/`DELETE /BulkAlbumEdit`) are untouched.

All three consumers are gated by **one** combined flag (Q-058-02), following the existing `App\Http\Resources\Rights\ModulesRightsResource` pattern (`is_*_enabled` booleans exposed to the frontend init payload, e.g. `is_mod_webshop_enabled`) rather than inventing a new exposure mechanism. The flag is named `struct-of-array` / `STRUCT_OF_ARRAY_ENABLED` (Q-058-03) rather than album-listing-specific, because it is intended to also gate a future Photos SoA v3 endpoint — this feature only implements the albums side, but the flag's name and scope are chosen so that future work doesn't need a second flag.

Beyond the pure data-source swap, this feature introduces two new pieces of shared frontend infrastructure that the three consumers (and their eight call sites) draw on rather than each reinventing:

- **A shared album-list store** (`resources/js/stores/AlbumListState.ts`) that fetches `GET /api/v3/Albums` (base mode) once per session, caches the raw Struct-of-Arrays response, and exposes a `tree` computed property that reconstructs the album hierarchy purely from `_lft`/`_rgt` (a nested-set stack algorithm — not the admin-gated `parent_ids`, since this store must also serve non-admin move/merge dialogs). The same store also exposes a pure subtree-exclusion helper used to prevent cyclic parent/child relationships when moving or merging albums (Q-058-04).
- **A reusable `<Thumb :album-id :photo-id :type>` component** that resolves a photo asset via the Feature 056 v3 Asset endpoint, caches the resolved image per `(album_id, photo_id, type)` so the same asset is never fetched twice in a session, and cancels its in-flight request if the component unmounts before the response arrives.

## Goals

- Add `'struct-of-array' => (bool) env('STRUCT_OF_ARRAY_ENABLED', false)` to `config/features.php`, following the exact existing pattern (e.g. `'webhook'`/`env('WEBHOOK_ENABLED', false)`).
- Expose it to the frontend as `ModulesRightsResource::$is_struct_of_array_enabled`, mirroring `is_mod_webshop_enabled`'s existing `config('features.webshop')`-backed resolver.
- Migrate all three v2 consumers to v3 when the flag is on, with zero behavior change when it is off (default).
- Provide a shared, session-cached album-list store with a `_lft`/`_rgt`-derived tree, so the three consumers (and the four dialogs that embed the Move-target picker) fetch the base album list at most once.
- Provide a pure, multi-root subtree-exclusion helper on that store so that moving or merging one or more albums can never offer a target that would create a cyclic parent/child relationship.
- Provide a reusable `<Thumb>` component that renders a v3 asset with request de-duplication/caching and unmount-safe cancellation, and use it everywhere this feature needs a thumbnail (replacing a bare `<img src="...">`).
- Invalidate the shared album-list store on every identity transition — login (`LoginForm.vue`, `WebauthnModal.vue`), registration's auto-login (`RegisterPage.vue`), and logout (`LeftMenu.vue`) — mirroring the existing `AlbumService.clearCache()` convention already called from all four of those places, so a session that gains or loses album visibility is never served a list cached under the prior identity.
- Invalidate the shared album-list store after every regular-Album mutation that changes what `GET /api/v3/Albums` would return — not just move/merge, but delete, unlock, and visibility/protection-policy changes, plus Fix Tree's own repair save and server-folder import (which can add/remove albums) — so the picker never offers a stale target.
- Reproduce v2's synthetic "move to root" option (offered whenever the moving/merging album isn't already at the top level) and its breadcrumb-path text, so the flag-on picker has full functional and visual parity with today's v2 dropdown, not just thumbnail/exclusion parity.
- Keep every migrated Vue component's external contract (props/events) unchanged, so no unrelated caller needs to change.
- Leave every v2 backend route/controller fully functional and untouched — the flag only changes what the frontend calls.

## Non-Goals

- **No backend v2 changes.** `Actions\Album\ListAlbums`, `Admin\Maintenance\FullTree::check()`, `Admin\BulkAlbumController::index()` are not modified, deprecated, or removed. They remain the flag-off path and continue serving any other consumer not covered by this feature's audit.
- **No further changes to Feature 057's contract.** This feature consumes `GET /api/v3/Albums` (and the Feature 056 Asset endpoint) exactly as specified; if a genuine new gap is found, it is fixed in 057's spec, not worked around here.
- **No per-consumer flags** (Q-058-02) — one combined flag only.
- **No Photos SoA endpoint or consumer.** `STRUCT_OF_ARRAY_ENABLED` is named generally so it can gate a future Photos SoA v3 endpoint, but this feature implements and gates only the albums side (Q-058-03); a photos equivalent is explicitly out of scope here.
- **No server-side pagination/search reintroduced for Bulk Album Edit.** Its client-side full-list approach is an accepted trade-off (NFR-058-04), inherited from Feature 057's Q-057-04 "never paginate" resolution.
- **No backend change to `Actions\Album\Move`'s cycle handling.** The existing `NodeTrait::appendNode()` guard (throws `\LogicException` when the target is the moved node itself or one of its descendants) is left as-is; this feature's client-side exclusion (FR-058-04) is the UX-facing safety net, not a change to the server-side backstop (see plan.md Risks and Q-058-05).
- **No automated frontend test suite is introduced** — this repo has none today (confirmed during Feature 049); verification is manual/browser-based, consistent with existing precedent (e.g. Feature 054).
- **No visual UI redesign.** Fix Tree and Bulk Album Edit look the same to the user; the Move/Merge/Copy picker's overall layout is also unchanged, with two narrow, deliberate additions when the flag is on: breadcrumb text per option (Q-058-06) and, when applicable, a "move to root" option (both reproduce/complete v2 parity rather than introduce new design). This feature otherwise only swaps the three surfaces' underlying data source and thumbnail-loading mechanism.
- **v8 only — no v7 changes.** Every component this feature touches or reads for its `AlbumService.clearCache()`/`clearAlbums()` invalidation wiring (`SearchTargetAlbum.vue`, `FixTree.vue`, `BulkAlbumEdit.vue`, `LoginForm.vue`, `WebauthnModal.vue`, `RegisterPage.vue`, `LeftMenu.vue`, `AlbumDelete.vue`, `DeleteDialog.vue`, `Unlock.vue`, `AlbumVisibility.vue`, `ImportFromServer.vue`) has a `resources/js/v7/...` counterpart with the same filename and its own independent `AlbumService.clearCache()`/`clearAlbums()` call. None of those v7 files are read, modified, or wired into the new `AlbumListState` store — v7 keeps calling only the existing v2 `AlbumService` cache-clear, unchanged, consistent with this project's v8-migration convention (fork/replace in v8, never edit v7 in place). This is safe with no staleness risk: v7 never reads from `AlbumListState`, since only v8's rewired `SearchTargetAlbum.vue`/`FixTree.vue`/`BulkAlbumEdit.vue` consume it.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-058-01 | `config/features.php` gains `'struct-of-array' => (bool) env('STRUCT_OF_ARRAY_ENABLED', false)`. | `App\Assets\Features::active('struct-of-array')` reflects the env var. | N/A. | N/A. | None. | User instruction; Q-058-03. |
| FR-058-02 | `App\Http\Resources\Rights\ModulesRightsResource` gains `public bool $is_struct_of_array_enabled`, resolved by a new private `isStructOfArrayEnabled(): bool` returning `config('features.struct-of-array') === true` (no DB config layer, no auth-gating — a pure client-behavior switch). | Frontend init payload's `modules.is_struct_of_array_enabled` matches the env var. | N/A. | N/A. | None. | `ModulesRightsResource` existing pattern (`app/Http/Resources/Rights/ModulesRightsResource.php:210-243`). |
| FR-058-03 | New Pinia store `resources/js/stores/AlbumListState.ts` (`useAlbumListStore`). `ensureLoaded()` calls `GET /api/v3/Albums` in **base mode only** (no `with_parent_id`/`for_bulk_edit` — those are admin-gated per FR-057-02/03 and this store must also serve non-admin move/merge dialogs), caches the raw `ids`/`titles`/`lft`/`rgt`/`cover_ids` arrays in store state, and is a no-op on a second call unless `invalidate()` was called first. | Repeated calls to `ensureLoaded()` across multiple mounted consumers in the same session issue at most one network request. | N/A. | A failed fetch leaves the store's `error` state set; consumers fall back to their existing empty/"no target" state. | None. | Q-058-04; DO-058-03. |
| FR-058-04 | The store exposes a computed `tree` — the album hierarchy reconstructed purely from `_lft`/`_rgt` via a nested-set stack algorithm (ascending `_lft` order; pop the stack while the current album's `_lft` exceeds the stack top's `_rgt`; the new stack top, if any, is the parent) — and three pure functions, all derived directly from the flat `lft`/`rgt` arrays (no tree walk needed): (1) `getExcludedTargetIds(rootIds: string[]): Set<string>`, the union over every id in `rootIds` of that album's own id plus every descendant (whose `(lft, rgt)` range is strictly contained within it); `rootIds` accepts one id (single-album Move) or several (multi-album Merge) uniformly. (2) `isTopLevel(albumId: string): boolean` — true iff no other album's `(lft, rgt)` range strictly contains `albumId`'s (i.e. it has no ancestor among the visible set). (3) `buildBreadcrumb(albumId: string): string` — the full ancestor-chain path, ancestor titles joined by `/` then the album's own title appended, mirroring `ListAlbums::do()`'s `flatten()` prefixing (`title = prefix + '/' + node.title`) — full-path text only; the client relies on CSS truncation (e.g. this UI kit's existing `truncate` utility) for overflow rather than reproducing the server's byte-length proportional-shortening algorithm (`ListAlbums::shorten()`'s `SHORTEN_BY=80` logic) — a deliberate simplification, not algorithm-for-algorithm parity. | For any `rootIds`, `getExcludedTargetIds` makes every remaining album a legal move/merge target with no cyclic parent/child relationship possible; `isTopLevel`/`buildBreadcrumb` reproduce v2's root-option and breadcrumb behavior without needing the admin-gated `parent_ids`. | N/A (pure functions, no I/O). | Empty `rootIds` → empty exclusion set (no-op), not an error. | None. | Q-058-05; mirrors `ListAlbums::do()`'s single-root `_lft`/`_rgt` exclusion (`app/Actions/Album/ListAlbums.php:44-53`), generalized to N roots; `ListAlbums::do()`'s root-option/breadcrumb behavior (`app/Actions/Album/ListAlbums.php:69-119`, `AlbumController::getTargetListAlbums`). |
| FR-058-05 | New component `resources/js/v8/components/thumbs/Thumb.vue`, props `albumId: string`, `photoId: string \| null`, `type: App.Enum.SizeVariantAssetType` (default `'thumb'`). Fetches `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` as a blob via axios with an `AbortController` signal (mirrors `upload-service.ts`'s existing signal usage), caches the resolved object URL keyed by `` `${albumId}:${photoId}:${type}` `` in a module-level `Map` so the same asset is never re-fetched within the session, and aborts its own in-flight request in `onBeforeUnmount`. Renders the existing "no image" placeholder asset immediately (no request) when `photoId === null`, and falls back to the same placeholder on a 403/404/aborted response. | Thumbnail renders identically to today for albums with a cover; placeholder renders for albums without one; a second `<Thumb>` for the same `(album_id, photo_id, type)` renders instantly from cache. | N/A. | A 403/404 from the Asset endpoint (e.g. race with a concurrent delete) falls back to the placeholder, not a broken image icon. | None. | Q-057-05; Feature 056 (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`); DO-058-05/06. |
| FR-058-06 | When `modules.is_struct_of_array_enabled === true`, `SearchTargetAlbum.vue` calls the shared store's `ensureLoaded()` (FR-058-03) instead of `AlbumService.getTargetListAlbums()`; filters the store's flat album list by `getExcludedTargetIds(props.albumIds ?? [])` (FR-058-04); when `props.albumIds` is non-empty and `!isTopLevel(props.albumIds[0])` (mirroring `AlbumController::getTargetListAlbums`'s existing `$parent_id = $albums->first()->parent_id` rule — v2 already only inspects the *first* selected album even for a multi-select merge, so this reproduces that exact precedent rather than inventing new multi-root root-option logic), prepends a synthetic `{id: null, breadcrumb: trans('gallery.root')}` "move to root" row, exactly as `ListAlbums::do()` does server-side; omitted entirely when `props.albumIds` is empty/undefined (photo move/copy — no root row today, unchanged); builds each option's breadcrumb label via `buildBreadcrumb` (FR-058-04) instead of the plain title, rendered through an `#item-label` slot override (the flag-off/v2 branch keeps its existing `label-key="original"` binding unchanged, per NFR-058-02); renders each row's thumbnail via `<Thumb :album-id="row.id" :photo-id="row.cover_id" type="thumb">` (FR-058-05), with the synthetic root row rendering the placeholder (`photo-id: null`). When `false`, behavior is byte-for-byte unchanged from today. | Dropdown lists the same albums as v2 would, correctly excluding the moving/merging album(s)' own subtree(s), offering "move to root" exactly when v2 would, and disambiguating same-titled albums via breadcrumb text (a new, real UX capability — today's v8 picker does not render breadcrumbs at all, see NFR-058-03). | N/A (no new user input). | Empty curated list → picker shows the existing "no target" state (`error_no_target`), unchanged from today. | None. | Move-target picker consumer; Q-058-04/05. |
| FR-058-07 | When the flag is on, `FixTree.vue` fetches `GET /api/v3/Albums?with_parent_id=true` (its own separate, admin-gated request — **not** the shared store from FR-058-03, which never requests `parent_ids`) instead of `GET /Maintenance::fullTree`; a new thin adapter transposes the SoA response into `AlbumTree[]` before handing it to `prepareAlbums()`/the WASM module, unchanged otherwise. `POST /Maintenance::fullTree` (save) is untouched. | Validity-check/repair results identical to today for the same data. | N/A. | N/A. | None. | Fix Tree consumer; `FullTree::check()` precedent. |
| FR-058-08 | When the flag is on, `BulkAlbumEdit.vue` fetches `GET /api/v3/Albums?for_bulk_edit=true` once (also its own separate, admin-gated request, not the shared store) instead of paginated `GET /BulkAlbumEdit` calls; `load(page)`, the debounced search handler, and "select all matching" (today's separate `GET /BulkAlbumEdit::ids`) are reimplemented as client-side operations (filter/slice) over the single in-memory result. Write endpoints unchanged. | Both "numbered" and "infinite-scroll" table modes, and search, behave identically to today from the user's perspective. | N/A. | N/A. | None. | Bulk Album Edit consumer. |
| FR-058-09 | All three consumers gate on the same single flag (`modules.is_struct_of_array_enabled`) — no independent per-consumer flag exists. | Toggling the one env var switches all three together. | N/A. | N/A. | None. | Q-058-02. |
| FR-058-10 | Album Merge's existing `SearchTargetAlbum` call site (`AlbumMergeDialog.vue`) already passes its full `albumIds: string[]` (one or more source albums) through the unchanged `album-ids` prop (NFR-058-02) — no dialog-specific special-casing is added; the multi-root support already required by FR-058-04/06 covers the merge case natively, since `getExcludedTargetIds` accepts any number of root ids. | Merging 2+ albums simultaneously excludes all of them, and all of their descendants, from the target list. | N/A. | N/A. | None. | User instruction (cyclic-dependency safety for merge). |
| FR-058-11 | The shared store's `invalidate()` (FR-058-03) is called from every existing identity-transition call site that already calls `AlbumService.clearCache()`: `LoginForm.vue`'s `login()` success handler (`:125`), `WebauthnModal.vue`'s `login()` success handler (`:65` — a second, independent login entry point, e.g. passkey login from the same modal group as `LoginForm.vue` but not nested inside it), `RegisterPage.vue`'s registration success handler (`:125` — registration auto-logs the new user in), and `LeftMenu.vue`'s `logout()` handler (`:170`, alongside its existing `*Store.reset()` calls) — a login, registration, or logout changes which albums `AlbumQueryPolicy::applyVisibilityFilter()` returns for the caller, so a list cached under the prior identity must never be served under the new one. | Store re-fetches on the next `ensureLoaded()` call after any of the four transitions. | N/A. | N/A. | None. | User instruction ("clear the AlbumListState [on login] because we will gain access to more"); existing `AlbumService.clearCache()` login/registration/logout convention (4 call sites). |
| FR-058-12 | The shared store's `invalidate()` (FR-058-03) is also called from every existing regular-Album-mutation call site whose action changes a field `GET /api/v3/Albums` returns (`id`/`title`/`lft`/`rgt`/`cover_id`) beyond the move/merge dialogs already covered by NFR-058-09: `AlbumDelete.vue:67`, `DeleteDialog.vue:105` (its album-delete path only), `Unlock.vue:48`, `AlbumVisibility.vue:149`, `FixTree.vue:156` (after `MaintenanceService.updateFullTree()`'s save — the repair write-back itself is untouched per FR-058-07, but its completion must invalidate this feature's own shared store), and `ImportFromServer.vue:170` (server-folder import can create or, via `delete_missing_albums`, remove regular albums). Deliberately **excluded**, with each call site's own `AlbumService.clearCache()`/`clearAlbums()` call left as-is: `AlbumPanel.vue:419`/`Albums.vue:229` (`togglePin` — pin status isn't a field this store tracks), `AlbumCreatePersonDialog.vue:75`/`AlbumCreateTagDialog.vue:75`/`TagRenameDialog.vue:57`/`TagDeleteDialog.vue:63`/`TagPanel.vue:93,98`/`TagMergeDialog.vue:58` (all operate on `TagAlbum`/`PersonAlbum` models, not the regular `Album` rows `GET /api/v3/Albums` queries — confirmed against FR-057-01's `albums._lft`-ordered query and `FullTree::check()`'s Album-table-only precedent), and `Search.vue`/`Timeline.vue`'s star/unstar callbacks (photo highlight state, not any SoA-tracked album field). | Store re-fetches on the next `ensureLoaded()` after any of the six included mutations; unaffected by any of the explicitly-excluded ones. | N/A. | N/A. | None. | User instruction (widen the invalidation net to regular-Album mutations); audit of all `AlbumService.clearCache()`/`clearAlbums()` call sites in `resources/js/v8`. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-058-01 | No v2 backend route/controller is modified, deprecated, or removed, and no `resources/js/v7/...` file is touched. | Flag-off must be a true no-op; other unaudited consumers of the v2 routes must keep working; v7 is legacy and out of scope for this feature (Non-Goals). | `git diff` shows no changes under `app/Http/Controllers/Gallery/AlbumController.php` (`getTargetListAlbums`), `app/Http/Controllers/Admin/Maintenance/FullTree.php`, `app/Http/Controllers/Admin/BulkAlbumController.php`, `routes/api_v2.php`, or anywhere under `resources/js/v7/`. | Feature 057 (already built, untouched by that feature too). | Non-Goals. |
| NFR-058-02 | `SearchTargetAlbum.vue`'s external prop/event contract (`album-ids` prop; `selected`/`no-target` events) is unchanged. | So `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` require zero changes. | Manual verification: exercise all 4 call sites with the flag on and off. | `resources/js/v8/components/forms/album/SearchTargetAlbum.vue`. | Goals. |
| NFR-058-03 | Client-side tree-building/exclusion for the move-picker (FR-058-04) produces results identical to today's server-side `ListAlbums::do()` for the same fixture data in the single-root case, and — since no server precedent exists for the multi-root (merge) case — is reasoned from first principles and manually verified against a fixture with a selected parent/child pair and a selected pair of unrelated albums. Separately, the "move to root" option and breadcrumb text (FR-058-04/06) are verified for **behavioral** parity with `ListAlbums::do()`/`flatten()` (same root-option trigger condition, same full breadcrumb path text) — **not** pixel-for-pixel `short_title` truncation parity, since the client deliberately uses CSS truncation instead of reproducing `ListAlbums::shorten()`'s algorithm (documented simplification, FR-058-04). Note also that today's v8 `SearchTargetAlbum.vue` doesn't render breadcrumb text at all (`label-key="original"`, no use of `title`/`short_title`) — so this is new rendered behavior for v8, not a restoration of an existing v8 display. | Regression safety for a security-relevant behavior (can't move or merge an album into its own descendant, or into a co-selected album's subtree), plus full functional/visual parity with v2's dropdown (not just thumbnail/exclusion parity). | Manual side-by-side comparison, flag on vs. off, same fixture album set; dedicated multi-root fixture for the merge case; dedicated fixture with a root-level album and a nested album to exercise the "move to root" option's presence/absence; dedicated fixture with two same-titled albums under different parents to exercise breadcrumb disambiguation. | `Actions\Album\ListAlbums::do()`/`flatten()`/`shorten()` (`app/Actions/Album/ListAlbums.php`); `NodeTrait::appendNode()`'s `\LogicException` guard (`vendor/lychee-org/nestedset/src/NodeTrait.php:1199-1200`) as the documented server-side backstop (see plan.md Risks). | User instruction (no behavior regressions; "Add breadcrumb display" decision); Q-058-05. |
| NFR-058-04 | Bulk Album Edit's full-list-in-memory approach is an accepted scale trade-off; no server-side pagination is reintroduced. | Inherits Feature 057's Q-057-04 "never paginate" resolution. | Documented here and in Follow-ups; not solved in this feature. | Q-057-04. | Q-058-01 resolution. |
| NFR-058-05 | `npm run format`/`npm run check` clean on all changed frontend files; `make phpstan`/`vendor/bin/php-cs-fixer fix` clean on the small backend flag-exposure change. | Repo quality gate. | Run both. | AGENTS.md. | AGENTS.md. |
| NFR-058-06 | Neither the shared store's `tree` computed (FR-058-04) nor the move-picker's breadcrumb construction (FR-058-06) uses the existing WASM `treeOperations.ts` module. | That module is purpose-built for Fix Tree's heavier validity-checking/repair workflow; routing a simple tree reconstruction/breadcrumb transform through it would be unnecessary coupling and overhead. | Code review: no new import of `treeOperations.ts`'s WASM bindings from the store or the move-picker's code path. | `resources/js/v8/composables/album/treeOperations.ts`. | Design decision (this plan). |
| NFR-058-07 | The shared store (FR-058-03) de-duplicates concurrent/sequential `ensureLoaded()` calls: mounting the Move dialog, then closing it and opening the Merge dialog, then `PhotoCopyDialog`, all within one session, issues at most one `GET /api/v3/Albums` request, verified via the browser Network panel. | Avoids re-fetching the same curated list for every dialog open, the whole point of a shared store. | Manual DevTools Network-panel check, S-058-10. | FR-058-03. | User instruction ("saves the global list of albums when fetched"). |
| NFR-058-08 | `<Thumb>` (FR-058-05) aborts its underlying HTTP request — not merely its own state update — when unmounted before the response arrives. | Avoids wasted bandwidth/server load from thumbnails for rows the user scrolled past or a dialog closed before rendering finished. | Manual DevTools Network-panel check: request shows as `(canceled)`, S-058-12. | FR-058-05. | User instruction ("cancel the request if unmounted"). |
| NFR-058-09 | The shared store's cached list is invalidated (re-fetch on next `ensureLoaded()`) after any of the regular-Album mutations listed in FR-058-12 — move, merge, delete, unlock, visibility change, Fix Tree save, server-folder import — mirroring the existing `AlbumService.clearCache()`/`clearAlbums()` convention already called at each of those exact sites today. | Prevents the shared list from going stale mid-session after the user performs an action that changes what it should return. | Manual verification per mutation: perform the action, reopen a Move/Merge dialog, confirm the store reflects the change (S-058-11/19..23). | `resources/js/services/album-service.ts` (`clearCache`/`clearAlbums`, existing convention); FR-058-12. | User instruction ("saves the global list ... when fetched", implies staying correct across mutations; widened to all regular-Album mutations on follow-up). |
| NFR-058-10 | The shared store never serves a list fetched under a different auth identity than the current one — a cached pre-login (guest or less-privileged) list must not under-serve a freshly-logged-in or freshly-registered user's actual visibility, and a cached pre-logout list must not linger and over-serve (or leak titles from) the prior identity after logout. | `AlbumQueryPolicy::applyVisibilityFilter()`'s result set is identity-dependent; the shared store's whole purpose (session-wide caching) is unsafe unless it tracks identity transitions. | Manual: as a guest (or lower-privileged user), open a dialog that populates the store, then log in (password or WebAuthn) as a user with visibility into more albums — reopening the picker shows the newly-visible albums (S-058-14); same check after registering a new account (S-058-24); separately, log out after the store is populated and confirm the next dialog open re-fetches rather than reusing the prior identity's cached list (S-058-15). | FR-058-11. | User instruction. |

## UI / Interaction Mock-ups

Fix Tree and Bulk Album Edit's layout is unchanged with the flag on or off. The Move/Merge/Copy picker's overall layout (dropdown, thumbnail slot) is also unchanged, with three visible differences when the flag is on: (1) the thumbnail, previously a bare `<img>`, is now rendered by `<Thumb>` — visually identical once loaded, but may show a brief blank/placeholder state while its request is in flight, matching how any other lazily-loaded thumbnail in this app already behaves; (2) each option's label shows a breadcrumb path (e.g. `Vacations / 2024 / Beach`) rather than just the album's own title — new for v8 (Q-058-06); (3) a "move to root" option appears when applicable, restoring existing v2 functionality the flag-off path already has. See [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](../../spec-guidelines/ui-ascii-mockups.md); no new mock-up is meaningful here.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-058-01 | Flag off (default): all three consumers behave exactly as today (regression guard). |
| S-058-02 | Flag on, Move dialog (single album): picker lists the correct curated albums via the shared store, correct thumbnail via `<Thumb>`, correct exclusion of the moving album's own subtree, correct breadcrumb text. |
| S-058-03 | Flag on, Move dialog, an album with `cover_id === null`: placeholder image shown via `<Thumb>`, no broken `<img>`/failed request. |
| S-058-04 | Flag on, Fix Tree page: same validity-check/repair behavior as v2; WASM operates identically on the SoA→AoS-adapted data (fetched separately from the shared store, with `parent_ids`). |
| S-058-05 | Flag on, Bulk Album Edit, "numbered" pagination mode: correct page slices from the in-memory list (fetched separately from the shared store, with bulk-edit fields). |
| S-058-06 | Flag on, Bulk Album Edit, "infinite-scroll" mode: correct incremental reveal from the in-memory list. |
| S-058-07 | Flag on, Bulk Album Edit, search box: correct client-side filtered results, matching v2's title-substring search semantics. |
| S-058-08 | Flag on, Bulk Album Edit, "select all matching": selects the correct filtered set from the in-memory list, no separate `::ids` call. |
| S-058-09 | Toggling `STRUCT_OF_ARRAY_ENABLED` (pure `.env`/config change, no code deploy) flips behavior on the next request — confirms no build-time-only wiring. |
| S-058-10 | Flag on: opening the Move dialog, closing it, then opening the Merge dialog (or Photo Copy dialog) in the same session issues at most one `GET /api/v3/Albums` network request total (shared store de-dup, NFR-058-07). |
| S-058-11 | Flag on, Merge dialog, 3 albums selected where one is an ancestor of another among the selected set: target picker excludes all 3 selected albums and every descendant of each — no selectable target can create a cycle (multi-root exclusion, FR-058-04/10). |
| S-058-12 | Flag on, Move dialog opened then immediately closed while a row's `<Thumb>` request is still in flight: DevTools Network panel shows that request as canceled, not merely its result discarded (NFR-058-08). |
| S-058-13 | Flag on, the same `(album_id, photo_id, type)` is rendered by `<Thumb>` twice in the same session (e.g. dialog reopened): the second render shows the image immediately from cache, no second network request (NFR-058-07-adjacent, cache verification). |
| S-058-14 | Flag on: as a guest (or a less-privileged user), open a dialog that populates the shared store, then log in as a user with visibility into more albums — reopening the picker shows the newly-visible albums, not the stale pre-login set (FR-058-11, NFR-058-10). |
| S-058-15 | Flag on: with the shared store populated, log out — the next dialog open (post-redirect, or pre-redirect if that ever changes) re-fetches under the new (guest) identity rather than reusing the prior user's cached list (FR-058-11, NFR-058-10). |
| S-058-16 | Flag on, Move/Merge dialog, the moving/first-selected album currently has a parent (not already at gallery root): picker includes a "move to root" option; selecting and confirming it moves the album to root, matching v2 (FR-058-04/06). |
| S-058-17 | Flag on, Move/Merge dialog, the moving/first-selected album is already at the gallery root: no "move to root" option is shown, matching v2 (FR-058-04/06). |
| S-058-18 | Flag on, two albums with the same title exist under different parents and both are valid targets: their breadcrumb text (not just plain title) disambiguates them in the dropdown — new v8 UX capability, not present today (FR-058-06, NFR-058-03). |
| S-058-19 | Flag on: delete an album (via `AlbumDelete.vue` or `DeleteDialog.vue`), then reopen a Move/Merge dialog — the deleted album no longer appears as a target (FR-058-12). |
| S-058-20 | Flag on: unlock a password-protected album, then reopen a Move/Merge dialog — the newly-unlocked album (and any now-visible descendants) appears as a target (FR-058-12). |
| S-058-21 | Flag on: change an album's visibility/protection policy, then reopen the picker — the change is reflected (FR-058-12). |
| S-058-22 | Flag on: run Fix Tree's repair/save, then open the Move dialog — the picker reflects the repaired `_lft`/`_rgt` values, not the pre-repair ones (FR-058-12). |
| S-058-23 | Flag on: import photos from a server folder (creating new albums), then open the picker — the newly-imported albums appear as valid targets (FR-058-12). |
| S-058-24 | Flag on: register a new account (auto-login), then open the picker — reflects the new account's visibility, not a stale guest-scoped list (FR-058-11, NFR-058-10). |

## Test Strategy

- **Core:** N/A.
- **Application:** N/A — no new backend logic beyond the flag/resolver (covered by a small Feature test on `ModulesRightsResource`'s init payload).
- **REST:** Feature test asserting `modules.is_struct_of_array_enabled` reflects `config('features.struct-of-array')` (both `true`/`false`).
- **CLI:** N/A.
- **UI (JS/Selenium):** No automated frontend suite exists in this repo (confirmed, Feature 049). Manual/browser-based verification for S-058-01..24, per Feature 054's precedent (ad hoc Playwright/Chromium session if available, or direct browser click-through plus DevTools Network-panel inspection for S-058-10/12/13, a real login/registration/logout cycle for S-058-14/15/24, and one pass per mutation type for S-058-19..23), covering both flag states.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` — note the flag-gated v3 adoption alongside the existing v3 endpoint entries.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-058-01 | `App\Http\Resources\Rights\ModulesRightsResource` gains `public bool $is_struct_of_array_enabled` (TypeScript-exported, existing `#[TypeScript]` class). | REST, UI |
| DO-058-02 | New `resources/js/services/album-list-v3-service.ts` — `AlbumListV3Service.getAlbums(params: {with_parent_id?: boolean; for_bulk_edit?: boolean}): Promise<AxiosResponse<App.Http.Resources.V3.AlbumListResource>>`, calling `GET /api/v3/Albums`. Used directly by `FixTree.vue`/`BulkAlbumEdit.vue` (their own admin-gated params) and internally by DO-058-03's store (base params only). | UI |
| DO-058-03 | New Pinia store `resources/js/stores/AlbumListState.ts` (`useAlbumListStore`) — state: raw base-mode `AlbumListResource` (`ids`/`titles`/`lft`/`rgt`/`cover_ids`), `isLoading`, `error`; actions: `ensureLoaded()`, `invalidate()`; getters: `tree` (nested-set stack reconstruction from `_lft`/`_rgt`, `{id, title, cover_id, lft, rgt, depth, children}[]`), `getExcludedTargetIds(rootIds: string[]): Set<string>` (pure `lft`/`rgt` range-containment union over one or more roots), `isTopLevel(albumId: string): boolean`, `buildBreadcrumb(albumId: string): string` (both also pure `lft`/`rgt` derivations, FR-058-04). The latter three are Pinia "getter methods" (a getter returning a function of its argument, since a plain Pinia getter takes no parameters) — an established Pinia pattern, called as e.g. `store.getExcludedTargetIds(ids)`, not `store.getExcludedTargetIds`. Follows the existing `defineStore("id", {state, getters, actions})` Options-API convention (`resources/js/stores/AlbumsState.ts` precedent). | UI |
| DO-058-04 | New thin adapter (e.g. in `FixTree.vue` or a small composable) — `AlbumListResource` (SoA, with `parent_ids`) → `AlbumTree[]` (AoS), feeding the existing `prepareAlbums()`/WASM pipeline unchanged. | UI |
| DO-058-05 | New component `resources/js/v8/components/thumbs/Thumb.vue` — props `albumId: string`, `photoId: string \| null`, `type?: App.Enum.SizeVariantAssetType` (default `'thumb'`); no emitted events; renders an `<img>` bound to a resolved object URL or the placeholder asset. | UI |
| DO-058-06 | New `resources/js/services/thumb-asset-service.ts` — `ThumbAssetService.getObjectUrl(albumId: string, photoId: string, type: App.Enum.SizeVariantAssetType, signal: AbortSignal): Promise<string>` backing `<Thumb>` (DO-058-05): fetches `GET /api/v3/Asset/{album_id}/{photo_id}/{type}` as a blob via axios (`responseType: 'blob'`, `signal`), converts to an object URL via `URL.createObjectURL`, and memoizes in-flight/resolved promises in a module-level `Map` keyed by `` `${albumId}:${photoId}:${type}` `` so concurrent/repeated callers share one request and one object URL. | UI |

### API Routes / Services

No new backend routes — reuses API-057-01 (`GET /api/v3/Albums`) and API-056-01 (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`).

### CLI Commands / Flags

None.

### Telemetry Events

None.

### Fixtures & Sample Data

Reuses existing Feature_v2/v3 fixtures; no new fixture files.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-058-01 | Move-picker, flag on, cover present | `<Thumb>` shows the album's resolved cover via the v3 Asset endpoint. |
| UI-058-02 | Move-picker, flag on, cover absent (`cover_id === null`) | `<Thumb>` shows the existing "no image" placeholder asset immediately, no request issued. |
| UI-058-03 | Bulk Album Edit, flag on, numbered mode | Pagination controls operate over the in-memory list, same visual behavior as today. |
| UI-058-04 | Bulk Album Edit, flag on, infinite-scroll mode | Sentinel-row intersection reveals more in-memory rows, same visual behavior as today. |
| UI-058-05 | `<Thumb>`, request in flight | Blank/placeholder state until the blob resolves (no explicit spinner beyond what today's picker already shows while `options === undefined`). |
| UI-058-06 | `<Thumb>`, request fails (403/404) | Falls back to the "no image" placeholder asset, same as the no-cover case. |
| UI-058-07 | Merge dialog, flag on, 3+ albums selected including an ancestor/descendant pair | Target picker excludes all selected albums and all of their descendants (FR-058-04/10). |
| UI-058-08 | Move/Merge dialog, flag on, moving album not already at root | A "move to root" option is offered, matching v2 (FR-058-04/06). |
| UI-058-09 | Move/Merge dialog, flag on | Each option's label shows its breadcrumb path, not just its own title — new v8 UX capability (FR-058-06). |

## Telemetry & Observability

No new telemetry events.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — note the flag-gated frontend adoption.
- `docs/specs/4-architecture/knowledge-map.md` — reference the new `album-list-v3-service.ts`, the `AlbumListState` store, `Thumb.vue`/`thumb-asset-service.ts`, and the `ModulesRightsResource` addition.
- `docs/specs/4-architecture/roadmap.md` — Feature 058 entry.

## Fixtures & Sample Data

None new.

## Spec DSL

```
domain_objects:
  - id: DO-058-01
    name: ModulesRightsResource.is_struct_of_array_enabled
    fields:
      - name: is_struct_of_array_enabled
        type: bool
  - id: DO-058-02
    name: AlbumListV3Service
  - id: DO-058-03
    name: AlbumListState (Pinia store: tree + getExcludedTargetIds)
  - id: DO-058-04
    name: SoA-to-AlbumTree adapter
  - id: DO-058-05
    name: Thumb.vue
  - id: DO-058-06
    name: ThumbAssetService
routes: []
fixtures: []
ui_states:
  - id: UI-058-01
    description: Move-picker thumbnail via Thumb/v3 Asset endpoint
  - id: UI-058-02
    description: Move-picker placeholder when no cover
  - id: UI-058-03
    description: Bulk Album Edit numbered pagination over in-memory list
  - id: UI-058-04
    description: Bulk Album Edit infinite-scroll over in-memory list
  - id: UI-058-05
    description: Thumb request in flight
  - id: UI-058-06
    description: Thumb request failure fallback
  - id: UI-058-07
    description: Merge dialog multi-root cyclic exclusion
  - id: UI-058-08
    description: Move/Merge dialog "move to root" option
  - id: UI-058-09
    description: Move/Merge dialog breadcrumb label
```

## Appendix

### Decision Cards (Q-058-01..07)

#### Q-058-01 — Migration scope & approach

**Resolved: all three consumers, with client-side compensation for pagination/search/breadcrumb/exclusion — and thumbnails preserved, not dropped**, via a reusable `<Thumb>` component (FR-058-05) wrapping the Feature 056 v3 Asset endpoint, paired with the `cover_id` field Feature 057 was amended to expose (Q-057-05). This was a direct user correction of the initially-proposed "drop thumbnails" default — the user pointed out the v3 Asset endpoint already exists and should be used instead of accepting a UX regression.

**Addendum (2026-08-22):** a spec review found that today's v8 `SearchTargetAlbum.vue` doesn't actually render breadcrumb text at all (`label-key="original"`, plain title only) — so "breadcrumb" compensation here is new v8 UX, not a restoration. Asked explicitly whether to drop that scope or build it: **resolved to build it** (FR-058-04/06, Q-058-06).

#### Q-058-02 — Feature-flag granularity

**Resolved: Option A — one combined flag**, gating all three consumers together via `ModulesRightsResource::$is_struct_of_array_enabled` (renamed per Q-058-03).

#### Q-058-03 — Flag naming/scope

**Resolved:** the `.env` variable is `STRUCT_OF_ARRAY_ENABLED` (config key `'struct-of-array'`, resource field `is_struct_of_array_enabled`) rather than the originally-drafted `ALBUM_LISTING_V3_ENABLED`/`'album-listing-v3'`/`is_album_listing_v3_enabled`. Direct user correction: the flag is intended to also gate a future Photos SoA v3 endpoint, so it should be named after the response-shape convention (ADR-0009) it toggles, not after this specific feature. Feature 058 itself still only builds and gates the albums side (Non-Goals); a photos consumer is future work under the same flag.

#### Q-058-04 — Where the shared album list lives and how its tree is computed

**Resolved:** a Pinia store (`AlbumListState.ts`, DO-058-03), matching this codebase's existing `resources/js/stores/*State.ts` convention, rather than a bare composable — because the requirement is a **shared, cached, cross-component** list (four dialog call sites plus potentially more later), which is exactly Pinia's role here (c.f. `AlbumsState.ts`, `AlbumState.ts`). The tree is computed purely from `_lft`/`_rgt` via a nested-set stack reconstruction, deliberately **not** from `parent_ids` — `with_parent_id=true` is admin-gated (FR-057-02, 403 for non-admins), and this store must also serve the Move/Merge/Copy dialogs for ordinary, non-admin users. Fix Tree keeps its own separate, admin-gated, `parent_ids`-bearing fetch (FR-058-07) precisely because it needs a field the shared store cannot request.

#### Q-058-05 — Cyclic-dependency prevention for Album Move and Album Merge

**Resolved:** a single pure function, `getExcludedTargetIds(rootIds: string[])` (FR-058-04), computes the union of each root album's own id and every descendant id via `_lft`/`_rgt` range containment, and accepts one root (Move) or several (Merge) uniformly — no separate code path for the multi-album case. Investigation found the actual server-side backstop is `NodeTrait::appendNode()`'s `\LogicException` (`vendor/lychee-org/nestedset/src/NodeTrait.php:1199-1200`, "Node must not be a descendant"), thrown by `Actions\Album\Move::do()` per-album with no dedicated HTTP-exception mapping visible in that action — meaning a client that bypassed this feature's exclusion would likely surface an uncaught 500, not a clean 4xx. This feature's client-side filter is therefore the real UX-facing safety net; whether the backend should catch that `LogicException` and translate it to a proper 4xx is flagged as a candidate follow-up (Non-Goals: no backend change made here).

#### Q-058-06 — Breadcrumb display and the "move to root" option

**Resolved:** both are built, as real, rendered UI additions to `SearchTargetAlbum.vue` (FR-058-04/06), not dropped as dead scope. Investigation found that (a) today's v8 picker doesn't render breadcrumb text at all despite `TargetAlbumResource` carrying it server-side, and (b) v2's synthetic "move to root" option (`ListAlbums::do()`, prepended whenever `$albums->first()->parent_id !== null`) wasn't addressed anywhere in this feature's original drafting — a real functional-parity gap, not just cosmetic. Both turned out to be derivable purely from `_lft`/`_rgt` (an album "is root" iff no other album's range contains it — the same containment check `getExcludedTargetIds` already needs), so neither requires the admin-gated `parent_ids` mode, keeping Q-058-04's non-admin-store reasoning intact. The breadcrumb's *full path text* mirrors `ListAlbums::do()`'s `flatten()` exactly; its *truncation* deliberately does not reproduce `ListAlbums::shorten()`'s byte-length proportional-shortening algorithm — CSS truncation is used instead, since the v8 dropdown component can truncate visually (a simplification, not full algorithmic parity).

#### Q-058-07 — Breadth of the shared store's mutation-invalidation net

**Resolved:** widened beyond move/merge/login/logout to every other existing `AlbumService.clearCache()`/`clearAlbums()` call site whose action changes a field `GET /api/v3/Albums` returns — delete, unlock, visibility/protection-policy change, Fix Tree's repair save, and server-folder import (FR-058-12) — plus two more identity-transition call sites found during the same audit, WebAuthn login and registration's auto-login (FR-058-11). Explicitly **not** widened to pin-toggling (no tracked field changes) or Tag-Album/Person-Album operations (confirmed, via FR-057-01's `albums._lft`-ordered query and `FullTree::check()`'s precedent, that `GET /api/v3/Albums` only ever queries the regular `Album` model, never `TagAlbum`/`PersonAlbum`) — those call sites' existing `AlbumService.clearCache()`/`clearAlbums()` calls are left untouched, and this feature adds nothing there.
