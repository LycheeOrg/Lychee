# Feature 034 Tasks – Bulk Album Edit

_Status: Draft — resolutions incorporated_  
_Last updated: 2026-04-14_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-034-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Backend Foundations

- [ ] T-034-01 – Create Request classes, Resource classes, Controller stub, and register routes (FR-034-08 to FR-034-12, FR-034-15).  
  _Intent:_ Scaffold five Request classes (`IndexBulkAlbumRequest`, `IdsBulkAlbumRequest`, `PatchBulkAlbumRequest`, `SetOwnerBulkAlbumRequest`, `DeleteBulkAlbumRequest`) under `app/Http/Requests/Admin/BulkAlbumEdit/`. Create `BulkAlbumResource` (Spatie Data — includes `_lft`, `_rgt`, visibility fields `is_public`/`is_link_required`/`grants_full_photo_access`/`grants_download`/`grants_upload`; **no** `depth` field per Q-034-02) and `BulkAlbumIdsResource` under `app/Http/Resources/Admin/`. Create `app/Http/Controllers/Admin/BulkAlbumController.php` with five stub methods. Register five routes in `routes/api_v2.php` protected by admin middleware. `PatchBulkAlbumRequest` must include visibility fields and apply `BooleanRequireSupportRule` on `grants_upload` (requires SE). `PatchBulkAlbumRequest` custom `after` rule ensures at least one optional field is present.  
  _Verification commands:_  
  - `php artisan route:list | grep BulkAlbum`  
  - `php artisan phpstan analyse --level=6`  
  - `./vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Use existing admin-middleware group pattern as in `WebhookController`.

### I2 – GET List Endpoint

- [ ] T-034-02 – Write feature tests for `GET /api/v2/BulkAlbumEdit` (S-034-01, S-034-02, S-034-10, FR-034-14, FR-034-15).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/IndexTest.php`. Tests: albums returned in `_lft ASC` order; search filter returns only matching albums; pagination meta (`current_page`, `last_page`, `total`) correct; non-admin receives 403; unauthenticated receives 401; empty list when no albums; `_lft` and `_rgt` present in each row (no `depth` field); visibility fields (`is_public`, `is_link_required`, `grants_*`) correctly derived from the public `access_permissions` record (user_id IS NULL, user_group_id IS NULL); TagAlbums not included in results (Q-034-01 → A).  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IndexTest`  
  _Notes:_ Use `BaseApiWithDataTest` fixtures or create a minimal album tree factory helper.

- [ ] T-034-03 – Implement `BulkAlbumController::index()` (FR-034-01, FR-034-02, FR-034-03, FR-034-11, FR-034-14, FR-034-15).  
  _Intent:_ Join `albums` + `base_albums` + `users` (for `owner_name`) + `LEFT JOIN access_permissions ap ON ap.base_album_id = base_albums.id AND ap.user_id IS NULL AND ap.user_group_id IS NULL`. **Do not call `withDepth()`** — include `albums._lft` and `albums._rgt` directly in the SELECT. Derive `is_public` (ap.id IS NOT NULL), `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload` from the joined row. Order by `_lft ASC`. Apply optional `LIKE %search%` on `base_albums.title`. Validate `per_page` in `{25, 50, 100}`. Map to `BulkAlbumResource`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IndexTest`  
  - `php artisan phpstan analyse --level=6`

### I3 – GET IDs Endpoint

- [ ] T-034-04 – Write feature tests for `GET /api/v2/BulkAlbumEdit::ids` (S-034-05, FR-034-12).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/IdsTest.php`. Tests: returns all IDs ordered by `_lft`; search filters IDs; `capped: false` when ≤ 1 000; `capped: true` and exactly 1 000 IDs returned when total > 1 000; admin-only gate; returns IDs for all albums regardless of owner (Q-034-04 → A).  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IdsTest`

- [ ] T-034-05 – Implement `BulkAlbumController::ids()` (FR-034-04, FR-034-12).  
  _Intent:_ Query `albums.id` joined with `base_albums`, ordered by `_lft ASC`, optional `LIKE` filter. Limit to 1 001 rows; if result count > 1 000, set `capped: true` and slice to 1 000. Return `BulkAlbumIdsResource`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IdsTest`  
  - `php artisan phpstan analyse --level=6`

### I4 – PATCH Endpoint (Bulk Field Update)

- [ ] T-034-06 – Write feature tests for `PATCH /api/v2/BulkAlbumEdit` (S-034-06, S-034-07, S-034-10 through S-034-12, S-034-16 through S-034-19, FR-034-08, FR-034-15, FR-034-16).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/PatchTest.php`. Tests: only provided fields updated (other fields unchanged); admin-only gate; 422 when `album_ids` empty; 422 when no optional fields present; 422 for invalid enum value; transaction rollback on simulated DB error; updating `base_albums` fields (`description`, `copyright`, `is_nsfw`, `photo_layout`, `photo_sorting`); updating `albums` fields (`license`, `album_thumb_aspect_ratio`, `album_timeline`); updating visibility fields via `SetProtectionPolicy` (`is_public` creates/deletes access_permissions record; `is_link_required`/`grants_*` update the record); 422 when `grants_upload: true` and not SE; single-album inline edit (album_ids with one ID) works identically to bulk; `is_public=false` removes the access_permissions record.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\PatchTest`

- [ ] T-034-07 – Create `app/Actions/Admin/BulkEditAlbumsAction.php` (FR-034-08, FR-034-15, NFR-034-02).  
  _Intent:_ Accepts `album_ids` (array) and a resolved payload array of nullable field values. Separates the payload into three groups:
  1. `base_albums` columns (`description`, `copyright`, `photo_layout`, `photo_sorting_col`, `photo_sorting_order`, `photo_timeline`, `is_nsfw`) → `BaseAlbumImpl::query()->whereIn('id', $album_ids)->chunk(100, fn($chunk) => $chunk->update($base_data))`.
  2. `albums` columns (`license`, `album_thumb_aspect_ratio`, `album_timeline`, `album_sorting_col`, `album_sorting_order`) → `Album::query()->whereIn('id', $album_ids)->chunk(100, ...)`.
  3. Visibility fields (`is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`) → load each album as `BaseAlbum`, build `AlbumProtectionPolicy`, call `SetProtectionPolicy::do()`.
  All groups execute within the caller's transaction. Groups with no fields in the payload are skipped.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\PatchTest`  
  - `php artisan phpstan analyse --level=6`

- [ ] T-034-08 – Implement `BulkAlbumController::patch()` (FR-034-08).  
  _Intent:_ Validate `PatchBulkAlbumRequest`, resolve enum/type fields, call `BulkEditAlbumsAction` inside `DB::transaction()`. Return 204 on success.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\PatchTest`  
  - `php artisan phpstan analyse --level=6`

### I5 – POST setOwner Endpoint (Ownership Transfer)

- [ ] T-034-09 – Write feature tests for `POST /api/v2/BulkAlbumEdit::setOwner` (S-034-08, S-034-13, S-034-14, FR-034-09).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/SetOwnerTest.php`. Tests: album `owner_id` updated on `base_albums`; album moved to root (`parent_id` null); descendants `owner_id` updated; tree has no errors after transfer (use `Album::countErrors()`); admin-only gate; 422 for non-existent `owner_id`; 422 for empty `album_ids`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\SetOwnerTest`

- [ ] T-034-10 – Implement `BulkAlbumController::setOwner()` (FR-034-09, NFR-034-03).  
  _Intent:_ Validate `SetOwnerBulkAlbumRequest`. Load each album in a `DB::transaction()` loop. For each, call `Transfer::do($album, $owner_id)`. Only process `Album` models (not `TagAlbum`). Return 204 on success.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\SetOwnerTest`  
  - `php artisan phpstan analyse --level=6`

### I6 – DELETE Endpoint (Bulk Delete)

- [ ] T-034-11 – Write feature tests for `DELETE /api/v2/BulkAlbumEdit` (S-034-09, S-034-15, FR-034-10).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/DeleteTest.php`. Tests: albums and their descendants deleted; photos deleted; admin-only gate; 422 for empty `album_ids`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\DeleteTest`

- [ ] T-034-12 – Implement `BulkAlbumController::destroy()` (FR-034-10).  
  _Intent:_ Validate `DeleteBulkAlbumRequest`. Call `(new Delete())->do($album_ids)`. Return 204.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\DeleteTest`  
  - `php artisan phpstan analyse --level=6`

### I7 – Frontend Service + Route + Menu

- [ ] T-034-13 – Create `resources/js/services/bulk-album-edit-service.ts` (FR-034-11, FR-034-12, FR-034-08, FR-034-09, FR-034-10).  
  _Intent:_ Five typed functions using axios: `getAlbums(params: IndexParams)`, `getIds(search?: string)`, `patchAlbums(payload: PatchPayload)`, `setOwner(payload: SetOwnerPayload)`, `deleteAlbums(ids: string[])`. All use `.then()` style (no async/await). Return typed response objects.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-034-14 – Register route, add left-menu entry, create translation file (FR-034-13).  
  _Intent:_ Add `{ name: 'bulk-album-edit', path: '/bulk-album-edit', component: BulkAlbumEdit }` to `routes.ts`. Add admin-gated entry to `leftMenu.ts` (label: "bulk_album_edit.title", icon: `pi pi-table`, route: `bulk-album-edit`). Create `lang/en/bulk_album_edit.php` with all UI string keys.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Add the Vue component import lazily: `const BulkAlbumEdit = () => import("@/views/BulkAlbumEdit.vue")`.

### I8 – BulkAlbumEdit.vue — List + Filter + Pagination

- [ ] T-034-15 – Create `BulkAlbumEdit.vue` with warning banner and page skeleton (FR-034-05, NFR-034-04, NFR-034-07, UI-034-01).  
  _Intent:_ Create `resources/js/views/BulkAlbumEdit.vue`. Add non-dismissible `<Message severity="warn">` banner. Add Toolbar with `<OpenLeftMenu />`. Admin-only guard (redirect to `/` if not admin). Page-level reactive state: `albums`, `selectedIds`, `page`, `perPage`, `paginationMode`, `search`, `loading`.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-034-16 – Implement filter input, pagination mode toggle, and page-size selector (FR-034-02, FR-034-03, S-034-02, S-034-03, UI-034-08, UI-034-09).  
  _Intent:_ Debounced (300 ms) filter input clears selection and resets page to 1 on change. Page-size selector (`<Select>` with options 25/50/100). Mode toggle button (infinite scroll / numbered). For numbered mode: use PrimeVue `Paginator`. For infinite scroll: use `IntersectionObserver` on a sentinel element at list bottom.  
  _Verification commands:_  
  - `npm run check && npm run format -- --check`

- [ ] T-034-17 – Implement album list rows with depth indicators, checkboxes, and visibility columns (FR-034-01, FR-034-14, FR-034-15, S-034-01, S-034-04, S-034-17, S-034-18, UI-034-01, UI-034-02, UI-034-12, UI-034-15, UI-034-16).  
  _Intent:_ Render table/list with columns: checkbox, depth-indicator prefix (`└─`, `├─`, `│`), title (click-to-edit), owner, license (click-to-edit dropdown), is_nsfw (toggle), is_public (toggle), is_link_required (toggle, disabled when not public), grants_full_photo_access (toggle, disabled when not public), grants_download (toggle, disabled when not public), grants_upload (toggle, disabled when not public or not SE; hidden when not SE), created_at. Header row has "select all on page" checkbox. Track selection in a `Set<string>` of album IDs.

  **Depth algorithm (Q-034-02 → B):** After fetching a page, run a single O(n) pass over rows (already sorted by `_lft`): maintain a stack of `_rgt` values; pop while `row._lft > stack.top`; `computedDepth = stack.length`; push `row._rgt`. Use `computedDepth` to choose the correct tree-prefix character.

  **Visibility toggle inline (S-034-17, S-034-18):** Clicking any toggle sets a loading micro-state on that cell, sends `patchAlbums({ album_ids: [row.id], <field>: newValue })`, updates the row on success, or reverts the toggle on 422.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-034-18 – Implement "Select all matching" button with cap warning (FR-034-04, S-034-05, UI-034-10).  
  _Intent:_ Button calls `getIds(search)`. On response: merge all returned IDs into `selectedIds`. If `capped: true`, show warning toast "Only the first 1,000 albums have been selected." Display total selection count.  
  _Verification commands:_  
  - `npm run check`

### I9 – Action Buttons + Edit Fields Modal

- [ ] T-034-19 – Implement inline cell editing, action buttons bar, and Edit Fields bulk dialog (FR-034-06, FR-034-07, FR-034-15, FR-034-16, S-034-06, S-034-07, S-034-11, S-034-12, S-034-16, S-034-19, UI-034-03, UI-034-06 to UI-034-08, UI-034-12 to UI-034-14).  
  _Intent:_

  **Inline cell editing (FR-034-16, S-034-16):** Each metadata cell (except title and slug) activates an inline editor on click. For enum fields: a `<Select>` dropdown overlays the cell. For text fields: an `<InputText>` overlays the cell. Pressing Enter or blurring confirms; Escape restores original value. Confirming sends `patchAlbums({ album_ids: [row.id], <field>: newValue })`. On success: update the cell value. On 422: restore original value + show inline error styling (red border).

  **Action buttons bar:** Three `<Button>` components (Delete, Set Owner, Edit Fields) above the list; disabled when `selectedIds.size === 0`.

  **Edit Fields `<Dialog>` — bulk modal (FR-034-07):** Render two sections: "Metadata" and "Visibility". Each field has a `<Checkbox>` enable toggle. Only checked fields are included in the PATCH payload. In the Visibility section: if "Public" is not checked, all other visibility sub-fields are automatically unchecked and disabled. `grants_upload` hidden if not SE. On Apply: call `patchAlbums()`; loading state on Apply button; success toast + reload list + clear selection; error toast.

  **Wire Delete button:** Opens delete confirmation dialog (T-034-22/I10).  
  _Verification commands:_  
  - `npm run check && npm run format -- --check`

- [ ] T-034-20 – Wire Delete button → delete confirmation dialog (FR-034-10, S-034-09, S-034-20, UI-034-05).  
  _Intent:_ On Delete button click: open delete confirmation `<Dialog>` showing "You are about to permanently delete N albums and all their sub-albums and photos. This action cannot be undone." with two actions: "Cancel" (closes dialog, nothing sent) and "Confirm Delete" (calls `deleteAlbums(Array.from(selectedIds))`). Loading state on Confirm button. On success: toast + reload + clear selection. On error: error toast.  
  _Verification commands:_  
  - `npm run check`

### I10 – Set Owner Modal + Delete Confirmation Dialog

- [ ] T-034-21 – Implement Set Owner dialog with user dropdown and delete confirmation dialog (FR-034-09, FR-034-10, S-034-08, S-034-09, S-034-13, S-034-14, S-034-20, UI-034-04, UI-034-05).  
  _Intent:_ **Set Owner `<Dialog>`:** Fetch users list from `GET /api/v2/User`, populate `<Select>` dropdown. Display warning: "All selected albums will be moved to the root level and their descendants will also be transferred." On Transfer: call `setOwner({album_ids, owner_id})`; loading state on Transfer button; success toast + reload + clear selection; error toast. **Delete confirmation `<Dialog>`** (connected from T-034-20): already described in T-034-20; this task verifies the full flow including `deleteAlbums()` back-end call and cancel path (S-034-20).  
  _Verification commands:_  
  - `npm run check && npm run format -- --check`

### I11 – Quality Gates + Documentation

- [ ] T-034-22 – Run full backend quality gate (NFR-034-05).  
  _Intent:_ Run `php artisan test` (all tests); `php artisan phpstan analyse --level=6`; `./vendor/bin/php-cs-fixer fix --dry-run`. Fix any issues.  
  _Verification commands:_  
  - `php artisan test`  
  - `php artisan phpstan analyse --level=6`  
  - `./vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-034-23 – Run full frontend quality gate (NFR-034-06).  
  _Intent:_ Run `npm run check` (vue-tsc); `npm run format -- --check`; `npm run build`. Fix any issues.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format -- --check`  
  - `npm run build`

- [ ] T-034-24 – Add OpenAPI docblocks to `BulkAlbumController` methods.  
  _Intent:_ Add `@OA\Get`, `@OA\Patch`, `@OA\Post`, `@OA\Delete` annotations with request/response schemas for all five endpoints. Include visibility fields in PATCH schema.  
  _Verification commands:_  
  - Manual review.

- [ ] T-034-25 – Update roadmap and knowledge-map (documentation deliverables).  
  _Intent:_ Add Feature 034 to `docs/specs/4-architecture/roadmap.md` Active Features table. Update `docs/specs/4-architecture/knowledge-map.md` to reference `BulkAlbumController` and `BulkAlbumEdit.vue`. Update feature status in roadmap as work progresses.  
  _Verification commands:_  
  - Manual review.

## Notes / Resolution History

All four open questions resolved 2026-04-14:

| Q-ID | Resolution | Spec impact |
|------|-----------|-------------|
| Q-034-01 → **Option A** | Only `Album` records in list; TagAlbums excluded | FR-034-01, T-034-02/03 — query joins only `albums` table |
| Q-034-02 → **Option B** | Depth computed client-side in O(n) linear pass | FR-034-14, T-034-03 — no `withDepth()`, `_lft`/`_rgt` in resource; T-034-17 — stack algorithm in Vue |
| Q-034-03 → **Option B** | Delete shows minimal confirmation dialog | FR-034-10, T-034-20/21 — dialog added; Cancel prevents DELETE |
| Q-034-04 → **Option A** | "Select all matching" returns all albums (no owner filter) | FR-034-12, T-034-04/05 |

Additional requirements added 2026-04-14:
- Visibility properties (`is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`) added to FR-034-15 — editable both inline and in bulk modal.
- Inline row editing added as FR-034-16 — uses the same PATCH endpoint with a single album ID.
- `BulkEditAlbumsAction` extended to handle `SetProtectionPolicy::do()` for visibility fields (T-034-07).
- `BulkAlbumResource` updated: no `depth` field; includes `_lft`, `_rgt`, and all visibility fields (T-034-01).
- Edit Fields modal expanded with "Visibility" section (T-034-19).
- Delete confirmation dialog added (T-034-20, T-034-21).
