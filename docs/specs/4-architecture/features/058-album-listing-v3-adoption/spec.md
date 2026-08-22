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

1. The Move-target picker (`resources/js/v8/components/forms/album/SearchTargetAlbum.vue`, shared by `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue`) — today calls `GET /api/v2/Album::getTargetListAlbums` (`Actions\Album\ListAlbums`), which fully hydrates Eloquent models, builds the tree server-side, computes breadcrumb strings, and resolves a thumbnail URL. Moves to the v3 default mode; tree/breadcrumb/subtree-exclusion move client-side; the thumbnail is preserved via the separate Feature 056 v3 Asset endpoint (not dropped) using the `cover_id` Feature 057 already added (Q-057-05).
2. The admin Fix Tree page (`resources/js/v8/views/FixTree.vue`) — today calls `GET /Maintenance::fullTree`. Moves to `GET /api/v3/Albums?with_parent_id=true`; a thin adapter reshapes the SoA response back into the `AlbumTree[]` array-of-structs shape the existing WASM validity-checking module (`resources/js/v8/composables/album/treeOperations.ts`) already consumes, so that module itself is untouched. The write-back `POST /Maintenance::fullTree` is untouched.
3. The admin Bulk Album Edit page (`resources/js/v8/views/BulkAlbumEdit.vue`) — today calls paginated, searchable `GET /BulkAlbumEdit`. Moves to a single `GET /api/v3/Albums?for_bulk_edit=true` fetch; pagination (both its "numbered" and "infinite-scroll" modes), debounced search, and "select all matching" become client-side operations over the in-memory result. The write endpoints (`PATCH`/`POST ::setOwner`/`DELETE /BulkAlbumEdit`) are untouched.

All three consumers are gated by **one** combined flag (Q-058-02), following the existing `App\Http\Resources\Rights\ModulesRightsResource` pattern (`is_*_enabled` booleans exposed to the frontend init payload, e.g. `is_mod_webshop_enabled`) rather than inventing a new exposure mechanism.

## Goals

- Add `'album-listing-v3' => (bool) env('ALBUM_LISTING_V3_ENABLED', false)` to `config/features.php`, following the exact existing pattern (e.g. `'webhook'`/`env('WEBHOOK_ENABLED', false)`).
- Expose it to the frontend as `ModulesRightsResource::$is_album_listing_v3_enabled`, mirroring `is_mod_webshop_enabled`'s existing `config('features.webshop')`-backed resolver.
- Migrate all three v2 consumers to v3 when the flag is on, with zero behavior change when it is off (default).
- Preserve thumbnails in the move-target picker via the Feature 056 v3 Asset endpoint, not drop them.
- Keep every migrated Vue component's external contract (props/events) unchanged, so no unrelated caller needs to change.
- Leave every v2 backend route/controller fully functional and untouched — the flag only changes what the frontend calls.

## Non-Goals

- **No backend v2 changes.** `Actions\Album\ListAlbums`, `Admin\Maintenance\FullTree::check()`, `Admin\BulkAlbumController::index()` are not modified, deprecated, or removed. They remain the flag-off path and continue serving any other consumer not covered by this feature's audit.
- **No further changes to Feature 057's contract.** This feature consumes `GET /api/v3/Albums` (and the Feature 056 Asset endpoint) exactly as specified; if a genuine new gap is found, it is fixed in 057's spec, not worked around here.
- **No per-consumer flags** (Q-058-02) — one combined flag only.
- **No server-side pagination/search reintroduced for Bulk Album Edit.** Its client-side full-list approach is an accepted trade-off (NFR-058-04), inherited from Feature 057's Q-057-04 "never paginate" resolution.
- **No automated frontend test suite is introduced** — this repo has none today (confirmed during Feature 049); verification is manual/browser-based, consistent with existing precedent (e.g. Feature 054).
- **No visual UI redesign.** The three surfaces look the same to the user; this feature only swaps their data source.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-058-01 | `config/features.php` gains `'album-listing-v3' => (bool) env('ALBUM_LISTING_V3_ENABLED', false)`. | `App\Assets\Features::active('album-listing-v3')` reflects the env var. | N/A. | N/A. | None. | User instruction. |
| FR-058-02 | `App\Http\Resources\Rights\ModulesRightsResource` gains `public bool $is_album_listing_v3_enabled`, resolved by a new private `isAlbumListingV3Enabled(): bool` returning `config('features.album-listing-v3') === true` (no DB config layer, no auth-gating — a pure client-behavior switch). | Frontend init payload's `modules.is_album_listing_v3_enabled` matches the env var. | N/A. | N/A. | None. | `ModulesRightsResource` existing pattern (`app/Http/Resources/Rights/ModulesRightsResource.php:210-243`). |
| FR-058-03 | When `modules.is_album_listing_v3_enabled === true`, `SearchTargetAlbum.vue` fetches `GET /api/v3/Albums` (new `AlbumListV3Service`) instead of `AlbumService.getTargetListAlbums()`; when `false`, behavior is byte-for-byte unchanged from today. | Dropdown lists the same albums as v2 would, correctly excluding the moving album's own subtree. | N/A (no new user input). | Empty curated list → picker shows the existing "no target" state (`error_no_target`), unchanged from today. | None. | Move-target picker consumer. |
| FR-058-04 | The v3 path builds album hierarchy/breadcrumb strings client-side from `_lft`/`_rgt` (new small TS helper — not the existing WASM module, see NFR-058-06), excludes the moving album's own subtree (mirrors `ListAlbums::do()`'s `_lft`/`_rgt` exclusion), and renders each row's thumbnail as `<img src="/api/v3/Asset/{album_id}/{cover_id}/{size_variant}">` (session-cookie-authenticated, same as existing v2 image serving — no signed-link params needed for a logged-in SPA request); falls back to the existing "no image" placeholder asset when `cover_id` is `null`. | Thumbnail renders identically to today for albums with a cover; placeholder renders for albums without one. | N/A. | A 403/404 from the Asset endpoint (e.g. race with a concurrent delete) falls back to the placeholder, not a broken image icon. | None. | Q-057-05; Feature 056 (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`). |
| FR-058-05 | When the flag is on, `FixTree.vue` fetches `GET /api/v3/Albums?with_parent_id=true` instead of `GET /Maintenance::fullTree`; a new thin adapter transposes the SoA response into `AlbumTree[]` before handing it to `prepareAlbums()`/the WASM module, unchanged otherwise. `POST /Maintenance::fullTree` (save) is untouched. | Validity-check/repair results identical to today for the same data. | N/A. | N/A. | None. | Fix Tree consumer; `FullTree::check()` precedent. |
| FR-058-06 | When the flag is on, `BulkAlbumEdit.vue` fetches `GET /api/v3/Albums?for_bulk_edit=true` once instead of paginated `GET /BulkAlbumEdit` calls; `load(page)`, the debounced search handler, and "select all matching" (today's separate `GET /BulkAlbumEdit::ids`) are reimplemented as client-side operations (filter/slice) over the single in-memory result. Write endpoints unchanged. | Both "numbered" and "infinite-scroll" table modes, and search, behave identically to today from the user's perspective. | N/A. | N/A. | None. | Bulk Album Edit consumer. |
| FR-058-07 | All three consumers gate on the same single flag (`modules.is_album_listing_v3_enabled`) — no independent per-consumer flag exists. | Toggling the one env var switches all three together. | N/A. | N/A. | None. | Q-058-02. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-058-01 | No v2 backend route/controller is modified, deprecated, or removed. | Flag-off must be a true no-op; other unaudited consumers of the v2 routes must keep working. | `git diff` shows no changes under `app/Http/Controllers/Gallery/AlbumController.php` (`getTargetListAlbums`), `app/Http/Controllers/Admin/Maintenance/FullTree.php`, `app/Http/Controllers/Admin/BulkAlbumController.php`, or `routes/api_v2.php`. | Feature 057 (already built, untouched by that feature too). | Non-Goals. |
| NFR-058-02 | `SearchTargetAlbum.vue`'s external prop/event contract (`album-ids` prop; `selected`/`no-target` events) is unchanged. | So `AlbumMove.vue`, `MoveDialog.vue`, `PhotoCopyDialog.vue`, `AlbumMergeDialog.vue` require zero changes. | Manual verification: exercise all 4 call sites with the flag on and off. | `resources/js/v8/components/forms/album/SearchTargetAlbum.vue`. | Goals. |
| NFR-058-03 | Client-side tree-building/exclusion for the move-picker (FR-058-04) produces results identical to today's server-side `ListAlbums::do()` for the same fixture data. | Regression safety for a security-relevant behavior (can't move an album into its own descendant). | Manual side-by-side comparison, flag on vs. off, same fixture album set. | `Actions\Album\ListAlbums::do()` (`app/Actions/Album/ListAlbums.php`). | User instruction (no behavior regressions). |
| NFR-058-04 | Bulk Album Edit's full-list-in-memory approach is an accepted scale trade-off; no server-side pagination is reintroduced. | Inherits Feature 057's Q-057-04 "never paginate" resolution. | Documented here and in Follow-ups; not solved in this feature. | Q-057-04. | Q-058-01 resolution. |
| NFR-058-05 | `npm run format`/`npm run check` clean on all changed frontend files; `make phpstan`/`vendor/bin/php-cs-fixer fix` clean on the small backend flag-exposure change. | Repo quality gate. | Run both. | AGENTS.md. | AGENTS.md. |
| NFR-058-06 | The move-picker's tree/breadcrumb construction uses a new, small, purpose-built TS helper — not the existing WASM `treeOperations.ts` module. | That module is purpose-built for Fix Tree's heavier validity-checking/repair workflow; routing a simple breadcrumb transform through it would be unnecessary coupling and overhead. | Code review: no new import of `treeOperations.ts`'s WASM bindings from the move-picker's code path. | `resources/js/v8/composables/album/treeOperations.ts`. | Design decision (this plan). |

## UI / Interaction Mock-ups

No visual change — all three surfaces render identically with the flag on or off; this feature swaps only their underlying data source. See [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](../../spec-guidelines/ui-ascii-mockups.md); no new mock-up is meaningful here since the existing UI for these three pages is unchanged.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-058-01 | Flag off (default): all three consumers behave exactly as today (regression guard). |
| S-058-02 | Flag on, Move dialog: picker lists the correct curated albums via v3, correct thumbnail via the v3 Asset endpoint, correct exclusion of the moving album's own subtree, correct breadcrumb text. |
| S-058-03 | Flag on, Move dialog, an album with `cover_id === null`: placeholder image shown, no broken `<img>`/failed request. |
| S-058-04 | Flag on, Fix Tree page: same validity-check/repair behavior as v2; WASM operates identically on the SoA→AoS-adapted data. |
| S-058-05 | Flag on, Bulk Album Edit, "numbered" pagination mode: correct page slices from the in-memory list. |
| S-058-06 | Flag on, Bulk Album Edit, "infinite-scroll" mode: correct incremental reveal from the in-memory list. |
| S-058-07 | Flag on, Bulk Album Edit, search box: correct client-side filtered results, matching v2's title-substring search semantics. |
| S-058-08 | Flag on, Bulk Album Edit, "select all matching": selects the correct filtered set from the in-memory list, no separate `::ids` call. |
| S-058-09 | Toggling `ALBUM_LISTING_V3_ENABLED` (pure `.env`/config change, no code deploy) flips behavior on the next request — confirms no build-time-only wiring. |

## Test Strategy

- **Core:** N/A.
- **Application:** N/A — no new backend logic beyond the flag/resolver (covered by a small Feature test on `ModulesRightsResource`'s init payload).
- **REST:** Feature test asserting `modules.is_album_listing_v3_enabled` reflects `config('features.album-listing-v3')` (both `true`/`false`).
- **CLI:** N/A.
- **UI (JS/Selenium):** No automated frontend suite exists in this repo (confirmed, Feature 049). Manual/browser-based verification for S-058-01..09, per Feature 054's precedent (ad hoc Playwright/Chromium session if available, or direct browser click-through), covering both flag states.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` — note the flag-gated v3 adoption alongside the existing v3 endpoint entries.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-058-01 | `App\Http\Resources\Rights\ModulesRightsResource` gains `public bool $is_album_listing_v3_enabled` (TypeScript-exported, existing `#[TypeScript]` class). | REST, UI |
| DO-058-02 | New `resources/js/services/album-list-v3-service.ts` — `AlbumListV3Service.getAlbums(params: {with_parent_id?: boolean; for_bulk_edit?: boolean}): Promise<AxiosResponse<App.Http.Resources.V3.AlbumListResource>>`, calling `GET /api/v3/Albums`. | UI |
| DO-058-03 | New TS helper (e.g. `resources/js/v8/composables/album/buildAlbumTreeFromFlatList.ts`) — pure function: `(rows: {id, title, lft, rgt, cover_id}[]) => tree with breadcrumb paths`, plus a subtree-exclusion function mirroring `ListAlbums::do()`. | UI |
| DO-058-04 | New thin adapter (e.g. in `FixTree.vue` or a small composable) — `AlbumListResource` (SoA, with `parent_ids`) → `AlbumTree[]` (AoS), feeding the existing `prepareAlbums()`/WASM pipeline unchanged. | UI |

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
| UI-058-01 | Move-picker, flag on, cover present | `<img>` shows the album's resolved cover via the v3 Asset endpoint. |
| UI-058-02 | Move-picker, flag on, cover absent | Existing "no image" placeholder asset shown. |
| UI-058-03 | Bulk Album Edit, flag on, numbered mode | Pagination controls operate over the in-memory list, same visual behavior as today. |
| UI-058-04 | Bulk Album Edit, flag on, infinite-scroll mode | Sentinel-row intersection reveals more in-memory rows, same visual behavior as today. |

## Telemetry & Observability

No new telemetry events.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — note the flag-gated frontend adoption.
- `docs/specs/4-architecture/knowledge-map.md` — reference the new `album-list-v3-service.ts`, the tree-building helper, and the `ModulesRightsResource` addition.
- `docs/specs/4-architecture/roadmap.md` — Feature 058 entry.

## Fixtures & Sample Data

None new.

## Spec DSL

```
domain_objects:
  - id: DO-058-01
    name: ModulesRightsResource.is_album_listing_v3_enabled
    fields:
      - name: is_album_listing_v3_enabled
        type: bool
  - id: DO-058-02
    name: AlbumListV3Service
  - id: DO-058-03
    name: buildAlbumTreeFromFlatList
  - id: DO-058-04
    name: SoA-to-AlbumTree adapter
routes: []
fixtures: []
ui_states:
  - id: UI-058-01
    description: Move-picker thumbnail via v3 Asset endpoint
  - id: UI-058-02
    description: Move-picker placeholder when no cover
  - id: UI-058-03
    description: Bulk Album Edit numbered pagination over in-memory list
  - id: UI-058-04
    description: Bulk Album Edit infinite-scroll over in-memory list
```

## Appendix

### Decision Cards (Q-058-01..02)

#### Q-058-01 — Migration scope & approach

**Resolved: all three consumers, with client-side compensation for pagination/search/breadcrumb/exclusion — and thumbnails preserved, not dropped**, via the Feature 056 v3 Asset endpoint paired with the `cover_id` field Feature 057 was amended to expose (Q-057-05). This was a direct user correction of the initially-proposed "drop thumbnails" default — the user pointed out the v3 Asset endpoint already exists and should be used instead of accepting a UX regression.

#### Q-058-02 — Feature-flag granularity

**Resolved: Option A — one combined flag**, `album-listing-v3` in `config/features.php` / `ALBUM_LISTING_V3_ENABLED` in `.env`, gating all three consumers together via `ModulesRightsResource::$is_album_listing_v3_enabled`.
