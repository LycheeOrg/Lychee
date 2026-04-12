# Feature Plan 034 – Bulk Album Edit

_Linked specification:_ `docs/specs/4-architecture/features/034-bulk-album-edit/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-04-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Admins can bulk-edit album metadata across the entire gallery in a single page operation — selecting albums by name search, applying partial field updates, transferring ownership (with full tree cascading), or deleting albums — with no confirmation dialogs and no DataTable component. The feature reuses existing model actions (`Transfer::do()`, `Delete::do()`) and follows all Lychee PHP/Vue coding conventions.

**Success signals:**
- All five REST endpoints return correct responses for happy-path and error scenarios.
- Ownership transfer produces a correct nested-set tree (no orphan or corrupt `_lft`/`_rgt`).
- PHPStan level 6 and `php-cs-fixer` pass with no new errors.
- `vue-tsc` (`npm run check`) passes with no new errors.
- All feature tests in `tests/Feature_v2/BulkAlbumEdit/` pass.

## Scope Alignment

- **In scope:**
  - Backend: `BulkAlbumController`, five route methods, `BulkEditAlbumsAction`, `BulkAlbumResource`, `BulkAlbumIdsResource`, four Request classes.
  - Frontend: `BulkAlbumEdit.vue`, `bulk-album-edit-service.ts`, route `/bulk-album-edit`, left-menu entry.
  - Feature tests (REST) and unit tests for the action.
- **Out of scope:**
  - Bulk editing of `TagAlbum` metadata.
  - Bulk editing of access permissions / protection policies.
  - Confirmation dialogs.
  - Notifications/emails on ownership change.

## Dependencies & Interfaces

| Dependency | Notes |
|------------|-------|
| `App\Actions\Album\Transfer` | Reused for ownership transfer (per album). |
| `App\Actions\Album\Delete` | Reused for bulk delete. |
| `App\Models\Album` (Kalnoy Nestedset `NodeTrait`) | `makeRoot()`, `descendants()`, `withDepth()`. |
| `App\Models\BaseAlbumImpl` | `whereIn()`, `chunk()`, `update()` for bulk field updates. |
| `App\Policies\AlbumPolicy` | Admin check reused. |
| PrimeVue (`Checkbox`, `Button`, `Select`, `Paginator`, `Message`, `Dialog`) | UI primitives. |
| `App\Http\Requests\BaseApiRequest` | Base class for new requests. |

## Assumptions & Risks

- **Assumptions:**
  - The Kalnoy Nestedset library's `makeRoot()` is safe to call on an already-root album.
  - The `Transfer::do()` action wraps its operations correctly; we wrap the loop in an outer DB transaction.
  - PrimeVue `Paginator` component is suitable for numbered pagination UI.
  - The existing `users` list endpoint (used by User management) can be reused to populate the owner dropdown.
- **Risks / Mitigations:**
  - **Tree corruption during batch transfer:** Mitigated by processing albums one-at-a-time with `makeRoot()` (NFR-034-03). If one transfer fails, the outer transaction rolls back all changes.
  - **Large album sets timing out:** Mitigated by NFR-034-02 (chunked updates) and keeping ownership transfer in a transaction with a generous timeout. For extremely large sets (>500 albums), admins are advised to batch their selections.
  - **Stale selection after filter change:** Mitigated by clearing selection on filter change client-side.

## Implementation Drift Gate

Run the following commands before marking each increment complete:
```bash
php artisan test --filter BulkAlbumEdit
php artisan phpstan analyse --level=6
./vendor/bin/php-cs-fixer fix --dry-run
npm run check
npm run format -- --check
```

## Increment Map

### I1 – Backend Foundations: Request Classes + Resource + Route Skeleton

- _Goal:_ Scaffold all Request classes, the `BulkAlbumResource`/`BulkAlbumIdsResource` Spatie Data classes, the `BulkAlbumController` stub, and register routes in `api_v2.php`. No business logic yet.
- _Preconditions:_ Spec approved.
- _Steps:_
  1. Create `app/Http/Requests/Admin/BulkAlbumEdit/IndexBulkAlbumRequest.php` (GET list: `search`, `page`, `per_page`).
  2. Create `app/Http/Requests/Admin/BulkAlbumEdit/IdsBulkAlbumRequest.php` (GET ids: `search`).
  3. Create `app/Http/Requests/Admin/BulkAlbumEdit/PatchBulkAlbumRequest.php` (PATCH: `album_ids`, optional fields).
  4. Create `app/Http/Requests/Admin/BulkAlbumEdit/SetOwnerBulkAlbumRequest.php` (POST setOwner: `album_ids`, `owner_id`).
  5. Create `app/Http/Requests/Admin/BulkAlbumEdit/DeleteBulkAlbumRequest.php` (DELETE: `album_ids`).
  6. Create `app/Http/Resources/Admin/BulkAlbumResource.php` (Spatie Data).
  7. Create `app/Http/Resources/Admin/BulkAlbumIdsResource.php` (Spatie Data).
  8. Create `app/Http/Controllers/Admin/BulkAlbumController.php` with five stub methods returning 204.
  9. Register routes in `routes/api_v2.php` under admin middleware group.
- _Commands:_ `php artisan route:list | grep BulkAlbum`
- _Exit:_ Routes registered; all stubs return 204; PHPStan 0 errors.

### I2 – GET /BulkAlbumEdit (List Endpoint)

- _Goal:_ Implement the paginated album list ordered by `_lft ASC` with optional `LIKE` search (FR-034-01, FR-034-02, FR-034-03, FR-034-11, S-034-01, S-034-02).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `IndexTest`: list order, search filter, pagination meta fields.
  2. Implement `BulkAlbumController::index()`: join `albums` + `base_albums`, `withDepth()`, `orderBy('_lft')`, optional `LIKE` filter, `paginate($per_page)`.
  3. Map results to `BulkAlbumResource`.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\IndexTest`
- _Exit:_ All `IndexTest` tests pass.

### I3 – GET /BulkAlbumEdit::ids (IDs Endpoint)

- _Goal:_ Implement the "select all matching" IDs endpoint capped at 1 000 (FR-034-04, FR-034-12, S-034-05).
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Write feature tests `IdsTest`: no search returns all IDs (capped), search filters IDs, `capped` flag set when > 1 000.
  2. Implement `BulkAlbumController::ids()`: query album IDs ordered by `_lft`, limit 1 001, cap and set `capped` flag.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\IdsTest`
- _Exit:_ All `IdsTest` tests pass.

### I4 – PATCH /BulkAlbumEdit (Bulk Field Update)

- _Goal:_ Implement partial bulk field update across `base_albums` and `albums` tables (FR-034-07, FR-034-08, S-034-06, S-034-07, S-034-11, S-034-12).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `PatchTest`: partial update (only checked fields saved), admin-only gate, transaction rollback on error, 422 for no fields present, 422 for bad enum.
  2. Create `app/Actions/Admin/BulkEditAlbumsAction.php`: accepts `album_ids` + array of resolved field values. Splits fields between `base_albums` and `albums` tables. Chunks update at 100.
  3. Implement `BulkAlbumController::patch()`: validate request, call action in `DB::transaction()`.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\PatchTest`
- _Exit:_ All `PatchTest` tests pass; chunked update confirmed.

### I5 – POST /BulkAlbumEdit::setOwner (Ownership Transfer)

- _Goal:_ Implement bulk ownership transfer using existing `Transfer::do()` per album (FR-034-09, S-034-08, S-034-13, S-034-14).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `SetOwnerTest`: ownership transferred, album moved to root, descendants updated, tree integrity, admin-only, 422 for bad user_id.
  2. Implement `BulkAlbumController::setOwner()`: load each album (only `Album` type; skip `TagAlbum`), call `Transfer::do()` inside a `DB::transaction()` loop.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\SetOwnerTest`
- _Exit:_ All `SetOwnerTest` tests pass; `Album::fixTree()` confirms no tree errors after transfer.

### I6 – DELETE /BulkAlbumEdit (Bulk Delete)

- _Goal:_ Implement bulk delete delegating to `Delete::do()` (FR-034-10, S-034-09, S-034-15).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `DeleteTest`: albums deleted, descendants deleted, admin-only gate.
  2. Implement `BulkAlbumController::destroy()`: call `(new Delete())->do($album_ids)`.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\DeleteTest`
- _Exit:_ All `DeleteTest` tests pass.

### I7 – Frontend Service + Route

- _Goal:_ Create `bulk-album-edit-service.ts` wrapping all five API calls, register route `/bulk-album-edit`, add left-menu entry (FR-034-13, UI-034-01).
- _Preconditions:_ I1 complete (routes registered).
- _Steps:_
  1. Create `resources/js/services/bulk-album-edit-service.ts` with functions: `getAlbums(params)`, `getIds(search)`, `patchAlbums(payload)`, `setOwner(payload)`, `deleteAlbums(ids)`.
  2. Register route in `resources/js/router/routes.ts`: `{ name: 'bulk-album-edit', path: '/bulk-album-edit', component: BulkAlbumEdit }`.
  3. Add left-menu entry in `resources/js/composables/contextMenus/leftMenu.ts` (admin-only guard).
  4. Add `lang/en/bulk_album_edit.php` translation file with all keys.
- _Commands:_ `npm run check`
- _Exit:_ Route navigable; service functions typed correctly; `npm run check` passes.

### I8 – BulkAlbumEdit.vue — List + Filter + Pagination

- _Goal:_ Implement the page skeleton: warning banner, filter input, pagination mode toggle, page-size selector, album list with depth indicators and checkboxes (FR-034-01 through FR-034-05, FR-034-14, S-034-01 through S-034-04, UI-034-01, UI-034-02, UI-034-08, UI-034-09).
- _Preconditions:_ I7 complete.
- _Steps:_
  1. Create `resources/js/views/BulkAlbumEdit.vue`.
  2. Implement warning `Message` banner (non-dismissible, severity="warn").
  3. Implement filter input (debounced 300 ms; clears selection on change).
  4. Implement page-size selector (25/50/100) and mode toggle (infinite scroll / numbered pages).
  5. Implement album list rows: depth-based indentation, title, owner, license, is_nsfw, created_at.
  6. Implement per-row and header checkboxes.
  7. Implement "Select all on page" and "Select all matching" buttons with cap warning toast.
  8. Implement PrimeVue `Paginator` for numbered mode; Intersection Observer for infinite scroll.
- _Commands:_ `npm run check && npm run format -- --check`
- _Exit:_ Page renders; list loads; filter works; pagination switches; selection state correct.

### I9 – BulkAlbumEdit.vue — Action Buttons + Edit Fields Modal

- _Goal:_ Implement action buttons and the Edit Fields modal (FR-034-06, FR-034-07, S-034-06, S-034-07, S-034-11, S-034-12, UI-034-03, UI-034-05 through UI-034-07).
- _Preconditions:_ I8 complete; I4 backend complete.
- _Steps:_
  1. Implement action buttons bar above list (Delete, Set Owner, Edit Fields) — disabled when selection empty.
  2. Implement Edit Fields `Dialog`: per-field enable checkboxes + input/dropdown; build request payload from only enabled fields.
  3. Wire "Apply" to `patchAlbums()`; show loading state; on success: toast + refresh list + clear selection.
  4. Wire "Delete" to `deleteAlbums()`; on success: toast + refresh.
- _Commands:_ `npm run check`
- _Exit:_ Edit Fields modal opens; only enabled fields sent; success/error toasts work.

### I10 – BulkAlbumEdit.vue — Set Owner Modal

- _Goal:_ Implement Set Owner modal with user dropdown (FR-034-09, S-034-08, S-034-13, S-034-14, UI-034-04).
- _Preconditions:_ I8 complete; I5 backend complete.
- _Steps:_
  1. Implement Set Owner `Dialog`: user dropdown populated from existing users API; warning message about root demotion.
  2. Wire "Transfer" to `setOwner()`; on success: toast + refresh + clear selection.
- _Commands:_ `npm run check`
- _Exit:_ Set Owner modal opens; user dropdown populated; transfer triggers correct API call.

### I11 – Quality Gates + Documentation

- _Goal:_ Run full quality gates, fix any issues, update documentation.
- _Preconditions:_ I2–I10 complete.
- _Steps:_
  1. Run `php artisan test` — all tests pass.
  2. Run `php artisan phpstan analyse --level=6` — 0 errors.
  3. Run `./vendor/bin/php-cs-fixer fix --dry-run` — no violations.
  4. Run `npm run check && npm run format -- --check` — passes.
  5. Add OpenAPI docblocks to `BulkAlbumController` methods.
  6. Update `docs/specs/4-architecture/roadmap.md` (status → In Progress → Complete).
  7. Update `docs/specs/4-architecture/knowledge-map.md`.
- _Commands:_ See above.
- _Exit:_ All gates pass; docs updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-034-01 | I2 / T-034-02 | IndexTest — list order |
| S-034-02 | I2 / T-034-03 | IndexTest — filter |
| S-034-03 | I8 / T-034-16 | Frontend — pagination mode toggle |
| S-034-04 | I8 / T-034-17 | Frontend — header checkbox |
| S-034-05 | I3 / T-034-05, I8 / T-034-18 | IdsTest + frontend cap toast |
| S-034-06 | I4 / T-034-07, I9 / T-034-19 | PatchTest + Edit Fields modal |
| S-034-07 | I4 / T-034-07, I9 / T-034-19 | PatchTest license + UI |
| S-034-08 | I5 / T-034-09, I10 / T-034-22 | SetOwnerTest + Set Owner modal |
| S-034-09 | I6 / T-034-11, I9 / T-034-20 | DeleteTest + UI |
| S-034-10 | I2 / T-034-02 | Admin-only gate tested in IndexTest |
| S-034-11 | I4 / T-034-08 | PatchTest — no fields present → 422 |
| S-034-12 | I4 / T-034-08 | PatchTest — bad enum → 422 |
| S-034-13 | I5 / T-034-10 | SetOwnerTest — bad user_id |
| S-034-14 | I5 / T-034-09 | SetOwnerTest — sub-album demoted to root |
| S-034-15 | I6 / T-034-12 | DeleteTest — 500 albums |

## Analysis Gate

_Not yet executed._

## Exit Criteria

- [ ] All five REST endpoints return correct responses for all scenarios (S-034-01 through S-034-15).
- [ ] `php artisan test` passes with no failures.
- [ ] PHPStan level 6: 0 errors.
- [ ] `php-cs-fixer`: no violations.
- [ ] `npm run check` (vue-tsc): 0 errors.
- [ ] `npm run format -- --check`: passes.
- [ ] Warning banner is non-dismissible and visible on page load.
- [ ] No `<DataTable>` component used in `BulkAlbumEdit.vue`.
- [ ] Tree integrity verified after bulk ownership transfer.
- [ ] Roadmap and knowledge-map docs updated.

## Follow-ups / Backlog

- Consider bulk editing `TagAlbum` metadata in a future iteration.
- Consider adding an "undo last bulk operation" (audit log + restore) if demand is high.
- Consider exposing a CLI command (`lychee:bulk-album-edit`) for headless/script-driven bulk updates.
