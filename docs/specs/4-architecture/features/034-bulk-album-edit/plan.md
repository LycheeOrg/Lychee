# Feature Plan 034 – Bulk Album Edit

_Linked specification:_ `docs/specs/4-architecture/features/034-bulk-album-edit/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-04-14

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Admins can bulk-edit album metadata (including public visibility properties) across the entire gallery in a single page operation — selecting albums by name search, applying partial field updates inline or via batch modal, transferring ownership (with full tree cascading), or deleting albums (with a minimal delete-confirmation dialog). The feature reuses existing model actions (`Transfer::do()`, `Delete::do()`, `SetProtectionPolicy::do()`) and follows all Lychee PHP/Vue coding conventions.

**Success signals:**
- All five REST endpoints return correct responses for happy-path and error scenarios.
- Ownership transfer produces a correct nested-set tree (no orphan or corrupt `_lft`/`_rgt`).
- Visibility fields (is_public, is_link_required, grants_*) editable both inline and via modal.
- Inline cell editing triggers immediate single-album PATCH; errors revert cell.
- Delete confirmation dialog shown; Cancel prevents deletion.
- PHPStan level 6 and `php-cs-fixer` pass with no new errors.
- `vue-tsc` (`npm run check`) passes with no new errors.
- All feature tests in `tests/Feature_v2/BulkAlbumEdit/` pass.

## Scope Alignment

- **In scope:**
  - Backend: `BulkAlbumController`, five route methods, `BulkEditAlbumsAction`, `BulkAlbumResource`, `BulkAlbumIdsResource`, five Request classes; `SetProtectionPolicy::do()` reused for visibility fields.
  - Frontend: `BulkAlbumEdit.vue`, `bulk-album-edit-service.ts`, route `/bulk-album-edit`, left-menu entry; inline cell editing; visibility toggles; delete confirmation dialog.
  - Feature tests (REST) and unit tests for the action.
- **Out of scope:**
  - Bulk editing of `TagAlbum` metadata (excluded per Q-034-01 → A).
  - Confirmation dialogs for field edits or ownership transfer (banner only; delete retains minimal confirmation per Q-034-03 → B).
  - Editing per-user / per-group access permissions (only public permission slot).
  - Notifications/emails on ownership change.

## Dependencies & Interfaces

| Dependency | Notes |
|------------|-------|
| `App\Actions\Album\Transfer` | Reused for ownership transfer (per album). |
| `App\Actions\Album\Delete` | Reused for bulk delete. |
| `App\Actions\Album\SetProtectionPolicy` | Reused for visibility fields (is_public, is_link_required, grants_*). Called per-album within the PATCH transaction. |
| `App\Models\Album` (Kalnoy Nestedset `NodeTrait`) | `makeRoot()`, `descendants()`. `_lft`/`_rgt` included in resource (no `withDepth()` needed). |
| `App\Models\BaseAlbumImpl` | `whereIn()`, `chunk()`, `update()` for bulk field updates. |
| `App\Rules\BooleanRequireSupportRule` | Reused to gate `grants_upload: true` on SE licence. |
| `App\Policies\AlbumPolicy` | Admin check reused. |
| PrimeVue (`Checkbox`, `Button`, `Select`, `Paginator`, `Message`, `Dialog`, `InputText`, `ToggleSwitch`) | UI primitives. |
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
- _Preconditions:_ Spec approved; Q-034-01 through Q-034-04 all resolved.
- _Steps:_
  1. Create `app/Http/Requests/Admin/BulkAlbumEdit/IndexBulkAlbumRequest.php` (GET list: `search`, `page`, `per_page`).
  2. Create `app/Http/Requests/Admin/BulkAlbumEdit/IdsBulkAlbumRequest.php` (GET ids: `search`).
  3. Create `app/Http/Requests/Admin/BulkAlbumEdit/PatchBulkAlbumRequest.php` (PATCH: `album_ids`, optional metadata + visibility fields; `BooleanRequireSupportRule` on `grants_upload`; at least one optional field required via `after` rule).
  4. Create `app/Http/Requests/Admin/BulkAlbumEdit/SetOwnerBulkAlbumRequest.php` (POST setOwner: `album_ids`, `owner_id`).
  5. Create `app/Http/Requests/Admin/BulkAlbumEdit/DeleteBulkAlbumRequest.php` (DELETE: `album_ids`).
  6. Create `app/Http/Resources/Admin/BulkAlbumResource.php` (Spatie Data — includes `_lft`, `_rgt`, visibility fields; NO `depth`).
  7. Create `app/Http/Resources/Admin/BulkAlbumIdsResource.php` (Spatie Data).
  8. Create `app/Http/Controllers/Admin/BulkAlbumController.php` with five stub methods returning 204.
  9. Register routes in `routes/api_v2.php` under admin middleware group.
- _Commands:_ `php artisan route:list | grep BulkAlbum`
- _Exit:_ Routes registered; all stubs return 204; PHPStan 0 errors.

### I2 – GET /BulkAlbumEdit (List Endpoint)

- _Goal:_ Implement the paginated album list ordered by `_lft ASC` with optional `LIKE` search, including `_lft`, `_rgt`, and visibility fields in the response (FR-034-01, FR-034-02, FR-034-03, FR-034-11, FR-034-14, FR-034-15, S-034-01, S-034-02).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `IndexTest`: list order (`_lft ASC`), search filter, pagination meta fields, `_lft`/`_rgt` present in response, visibility fields (`is_public`, `is_link_required`, `grants_*`) present and correctly derived from `access_permissions`.
  2. Implement `BulkAlbumController::index()`: join `albums` + `base_albums` + `users` (for `owner_name`) + `LEFT JOIN access_permissions` (public permission slot only: `user_id IS NULL AND user_group_id IS NULL`). No `withDepth()` — include `_lft` and `_rgt` directly. Order by `_lft ASC`. Optional `LIKE %search%` on `base_albums.title`. Paginate at `$per_page`. Map to `BulkAlbumResource`.
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

- _Goal:_ Implement partial bulk field update across `base_albums`, `albums`, and `access_permissions` tables; supports both bulk multi-album requests and single-album inline edits via the same endpoint (FR-034-07, FR-034-08, FR-034-15, FR-034-16, S-034-06, S-034-07, S-034-11, S-034-12, S-034-16 through S-034-19).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write feature tests `PatchTest`: partial update (only provided fields saved, others unchanged); admin-only gate; 422 when `album_ids` empty; 422 when no optional fields present; 422 for invalid enum value; transaction rollback on simulated DB error; updating `base_albums` fields; updating `albums` fields; updating visibility fields via `SetProtectionPolicy`; SE gate on `grants_upload: true`; single-album inline edit (album_ids = [one ID]).
  2. Create `app/Actions/Admin/BulkEditAlbumsAction.php`: accepts `album_ids` and a resolved payload array. Separates fields into:
     - `base_albums` columns → chunked `whereIn()->update()` (chunk 100).
     - `albums` columns → chunked `whereIn()->update()` (chunk 100).
     - Visibility fields (`is_public`, `is_link_required`, `grants_*`) → load each album, call `SetProtectionPolicy::do()`.
  3. Implement `BulkAlbumController::patch()`: validate `PatchBulkAlbumRequest`, resolve enum/type fields, call `BulkEditAlbumsAction` inside `DB::transaction()`. Return 204.
- _Commands:_ `php artisan test --filter BulkAlbumEdit\\PatchTest`
- _Exit:_ All `PatchTest` tests pass; chunked update confirmed; visibility tests pass.

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

### I8 – BulkAlbumEdit.vue — List + Filter + Pagination + Depth

- _Goal:_ Implement the page skeleton: warning banner, filter input, pagination mode toggle, page-size selector, album list with client-side depth indicators (O(n) stack algorithm), checkboxes, and visibility columns (FR-034-01 through FR-034-05, FR-034-14, FR-034-15, S-034-01 through S-034-04, UI-034-01, UI-034-02, UI-034-09, UI-034-10, UI-034-15, UI-034-16).
- _Preconditions:_ I7 complete.
- _Steps:_
  1. Create `resources/js/views/BulkAlbumEdit.vue`.
  2. Implement non-dismissible `<Message severity="warn">` banner.
  3. Implement filter input (debounced 300 ms; clears selection on change; resets page to 1).
  4. Implement page-size selector (25/50/100) and mode toggle (infinite scroll / numbered).
  5. Implement client-side depth computation: after loading a page, run the O(n) stack algorithm on `_lft`/`_rgt` to assign `computedDepth` to each row.
  6. Render album rows: depth-based tree prefix (`└─`, `├─`, `│`), title (click-to-edit — I9), owner, license (click-to-edit — I9), and all other editable metadata cells.
  7. Render visibility columns as icon-toggles: is_public (always active), is_link_required / grants_full_photo_access / grants_download / grants_upload (disabled/greyed when not public; grants_upload hidden when not SE).
  8. Implement per-row and header checkboxes.
  9. Implement "Select all on page" and "Select all matching" buttons with cap warning toast.
  10. Implement PrimeVue `Paginator` for numbered mode; `IntersectionObserver` sentinel for infinite scroll.
- _Commands:_ `npm run check && npm run format -- --check`
- _Exit:_ Page renders; list loads with correct depth indicators; filter and pagination work; visibility columns shown correctly; selection state correct.

### I9 – BulkAlbumEdit.vue — Inline Editing + Action Buttons + Edit Fields Modal

- _Goal:_ Implement inline cell editing, action buttons, and the Edit Fields bulk modal (FR-034-06, FR-034-07, FR-034-15, FR-034-16, S-034-06, S-034-07, S-034-11, S-034-12, S-034-16 through S-034-19, UI-034-03, UI-034-06 through UI-034-08, UI-034-12 through UI-034-16).
- _Preconditions:_ I8 complete; I4 backend complete.
- _Steps:_
  1. **Inline editing:** Each metadata cell (description, copyright, license, photo_layout, sorting, aspect_ratio, timeline, is_nsfw) is click-to-edit. Clicking a cell activates an inline editor (dropdown for enums, input for text). Pressing Enter or blurring the field sends a PATCH with `album_ids: [thisId]` and the changed field. Escape restores the original value. Show a micro loading state on the cell. On 422: show inline error, restore original value.
  2. **Visibility toggles inline:** Clicking a toggle (is_public, is_link_required, grants_*) immediately sends a PATCH with the single album and the toggled field. Disable grants_* when is_public=false.
  3. **Action buttons bar:** Three `<Button>` components (Delete, Set Owner, Edit Fields) above the list; disabled when `selectedIds.size === 0`.
  4. **Edit Fields `<Dialog>` (bulk modal):** Render one row per editable field (metadata section + visibility section) each with a `<Checkbox>` enable toggle and input/dropdown. Only checked fields sent on Apply. For visibility section: "Public" must be checked to enable dependent fields in the modal. On Apply: call `patchAlbums()`; loading; success toast + reload + clear selection; error toast.
  5. **Wire Delete button:** Opens delete confirmation dialog (see I10).
- _Commands:_ `npm run check && npm run format -- --check`
- _Exit:_ Inline edits work (enter/blur/escape); visibility toggles work; modal sends only checked fields; success/error states correct.

### I10 – BulkAlbumEdit.vue — Delete Confirmation Dialog + Set Owner Modal

- _Goal:_ Implement the delete confirmation dialog and the Set Owner modal (FR-034-09, FR-034-10, S-034-08, S-034-09, S-034-13, S-034-14, S-034-20, UI-034-04, UI-034-05).
- _Preconditions:_ I8 complete; I5 backend complete; I6 backend complete.
- _Steps:_
  1. **Delete confirmation `<Dialog>`:** Shows "You are about to permanently delete N albums…"; "Confirm Delete" sends `deleteAlbums()`; "Cancel" closes dialog; success toast + reload + clear selection; error toast.
  2. **Set Owner `<Dialog>`:** Fetch users list from existing users API (`GET /api/v2/User`); populate `<Select>` dropdown; display root-demotion warning; "Transfer" calls `setOwner()`; loading; success toast + reload + clear selection; error toast.
- _Commands:_ `npm run check && npm run format -- --check`
- _Exit:_ Delete confirmation shown and functional; Set Owner modal opens and works.

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
| S-034-01 | I2 / T-034-02 | IndexTest — list order; `_lft`/`_rgt` in response |
| S-034-02 | I2 / T-034-03 | IndexTest — filter |
| S-034-03 | I8 / T-034-16 | Frontend — pagination mode toggle |
| S-034-04 | I8 / T-034-17 | Frontend — header checkbox |
| S-034-05 | I3 / T-034-05, I8 / T-034-18 | IdsTest (all albums, no owner filter) + frontend cap toast |
| S-034-06 | I4 / T-034-07, I9 / T-034-19 | PatchTest + Edit Fields modal |
| S-034-07 | I4 / T-034-07, I9 / T-034-19 | PatchTest license + modal |
| S-034-08 | I5 / T-034-09, I10 / T-034-22 | SetOwnerTest + Set Owner modal |
| S-034-09 | I6 / T-034-11, I10 / T-034-22 | DeleteTest + delete confirmation dialog |
| S-034-10 | I2 / T-034-02 | Admin-only gate tested in IndexTest |
| S-034-11 | I4 / T-034-08 | PatchTest — no fields present → 422 |
| S-034-12 | I4 / T-034-08 | PatchTest — bad enum → 422 |
| S-034-13 | I5 / T-034-10 | SetOwnerTest — bad user_id |
| S-034-14 | I5 / T-034-09 | SetOwnerTest — sub-album demoted to root |
| S-034-15 | I6 / T-034-12 | DeleteTest — 500 albums |
| S-034-16 | I9 / T-034-19 | Inline cell edit → PATCH single album |
| S-034-17 | I8+I9 / T-034-17+T-034-19 | is_public toggle ON inline |
| S-034-18 | I8+I9 / T-034-17+T-034-19 | is_public toggle OFF inline |
| S-034-19 | I4+I9 / T-034-07+T-034-19 | grants_upload SE gate |
| S-034-20 | I10 / T-034-22 | Delete dialog Cancel → no DELETE sent |

## Analysis Gate

_Not yet executed._

## Exit Criteria

- [ ] All five REST endpoints return correct responses for all scenarios (S-034-01 through S-034-20).
- [ ] `php artisan test` passes with no failures.
- [ ] PHPStan level 6: 0 errors.
- [ ] `php-cs-fixer`: no violations.
- [ ] `npm run check` (vue-tsc): 0 errors.
- [ ] `npm run format -- --check`: passes.
- [ ] Warning banner is non-dismissible and visible on page load.
- [ ] No `<DataTable>` component used in `BulkAlbumEdit.vue`.
- [ ] Tree integrity verified after bulk ownership transfer.
- [ ] Inline cell editing works for metadata and visibility fields.
- [ ] Delete confirmation dialog shown before any DELETE request is sent.
- [ ] Visibility toggles (is_public, is_link_required, grants_*) work inline and via modal; grants_upload hidden when not SE.
- [ ] Depth indicator computed client-side (O(n) stack algorithm) with correct tree prefixes.
- [ ] Roadmap and knowledge-map docs updated.

## Follow-ups / Backlog

- Consider bulk editing `TagAlbum` metadata in a future iteration.
- Consider adding an "undo last bulk operation" (audit log + restore) if demand is high.
- Consider exposing a CLI command (`lychee:bulk-album-edit`) for headless/script-driven bulk updates.
