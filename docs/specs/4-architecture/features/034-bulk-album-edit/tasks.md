# Feature 034 Tasks – Bulk Album Edit

_Status: Draft_  
_Last updated: 2026-04-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-034-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Backend Foundations

- [ ] T-034-01 – Create Request classes, Resource classes, Controller stub, and register routes (FR-034-08 to FR-034-12).  
  _Intent:_ Scaffold five Request classes (`IndexBulkAlbumRequest`, `IdsBulkAlbumRequest`, `PatchBulkAlbumRequest`, `SetOwnerBulkAlbumRequest`, `DeleteBulkAlbumRequest`) under `app/Http/Requests/Admin/BulkAlbumEdit/`. Create `BulkAlbumResource` and `BulkAlbumIdsResource` Spatie Data classes under `app/Http/Resources/Admin/`. Create `app/Http/Controllers/Admin/BulkAlbumController.php` with five stub methods. Register five routes in `routes/api_v2.php` protected by admin middleware.  
  _Verification commands:_  
  - `php artisan route:list | grep BulkAlbum`  
  - `php artisan phpstan analyse --level=6`  
  - `./vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Use existing admin-middleware group pattern as in `WebhookController`. `PatchBulkAlbumRequest` must validate that at least one optional field is present (custom `after` validation rule).

### I2 – GET List Endpoint

- [ ] T-034-02 – Write feature tests for `GET /api/v2/BulkAlbumEdit` (S-034-01, S-034-02, S-034-10).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/IndexTest.php`. Tests: albums returned in `_lft ASC` order; search filter returns only matching albums; pagination meta (`current_page`, `last_page`, `total`) correct; non-admin receives 403; unauthenticated receives 401; empty list when no albums.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IndexTest`  
  _Notes:_ Use `BaseApiWithDataTest` fixtures or create a minimal album tree factory helper.

- [ ] T-034-03 – Implement `BulkAlbumController::index()` (FR-034-01, FR-034-02, FR-034-03, FR-034-11).  
  _Intent:_ Join `albums` + `base_albums` + `users` (for `owner_name`), call `withDepth()`, order by `_lft ASC`. Apply optional `LIKE %search%` on `base_albums.title`. Call `paginate($per_page)`. Map to `BulkAlbumResource`. Validate `per_page` in `{25, 50, 100}`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IndexTest`  
  - `php artisan phpstan analyse --level=6`

### I3 – GET IDs Endpoint

- [ ] T-034-04 – Write feature tests for `GET /api/v2/BulkAlbumEdit::ids` (S-034-05, FR-034-12).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/IdsTest.php`. Tests: returns all IDs ordered by `_lft`; search filters IDs; `capped: false` when ≤ 1 000; `capped: true` and 1 000 IDs returned when total > 1 000; admin-only gate.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IdsTest`

- [ ] T-034-05 – Implement `BulkAlbumController::ids()` (FR-034-04, FR-034-12).  
  _Intent:_ Query `albums.id` joined with `base_albums`, ordered by `_lft ASC`, optional `LIKE` filter. Limit to 1 001 rows; if result count > 1 000, set `capped: true` and slice to 1 000. Return `BulkAlbumIdsResource`.  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\IdsTest`  
  - `php artisan phpstan analyse --level=6`

### I4 – PATCH Endpoint (Bulk Field Update)

- [ ] T-034-06 – Write feature tests for `PATCH /api/v2/BulkAlbumEdit` (S-034-06, S-034-07, S-034-10, S-034-11, S-034-12, FR-034-08).  
  _Intent:_ Create `tests/Feature_v2/BulkAlbumEdit/PatchTest.php`. Tests: only provided fields updated (other fields unchanged); admin-only gate; 422 when `album_ids` empty; 422 when no optional fields present; 422 for invalid enum value; transaction rollback on simulated DB error; updating `base_albums` fields (`description`, `copyright`, `is_nsfw`, `photo_layout`, `photo_sorting`); updating `albums` fields (`license`, `album_thumb_aspect_ratio`, `album_timeline`).  
  _Verification commands:_  
  - `php artisan test --filter BulkAlbumEdit\\PatchTest`

- [ ] T-034-07 – Create `app/Actions/Admin/BulkEditAlbumsAction.php` (FR-034-08, NFR-034-02).  
  _Intent:_ Accepts `album_ids` (array) and a resolved DTO/array of nullable field values. Splits fields into `base_albums` columns and `albums` columns. Chunks updates at 100 using `whereIn()->chunk(100, ...)`. Only includes columns that are non-null in the payload (partial update).  
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

- [ ] T-034-17 – Implement album list rows with depth indicators and checkboxes (FR-034-01, FR-034-14, S-034-01, S-034-04, UI-034-01, UI-034-02).  
  _Intent:_ Render table/list with columns: checkbox, depth-indicator prefix (`└─`, `├─`, `│`, indented by depth), title, owner, license, is_nsfw chip, created_at. Header row has "select all on page" checkbox. Track selection in a `Set<string>` of album IDs.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-034-18 – Implement "Select all matching" button with cap warning (FR-034-04, S-034-05, UI-034-10).  
  _Intent:_ Button calls `getIds(search)`. On response: merge all returned IDs into `selectedIds`. If `capped: true`, show warning toast "Only the first 1,000 albums have been selected." Display total selection count.  
  _Verification commands:_  
  - `npm run check`

### I9 – Action Buttons + Edit Fields Modal

- [ ] T-034-19 – Implement action buttons bar and Edit Fields dialog (FR-034-06, FR-034-07, S-034-06, S-034-07, S-034-11, S-034-12, UI-034-03, UI-034-05 to UI-034-07).  
  _Intent:_ Render three `<Button>` components (Delete, Set Owner, Edit Fields) above the list, disabled when `selectedIds.size === 0`. Edit Fields `<Dialog>`: render one row per editable field with a `<Checkbox>` enable toggle and a field input/dropdown. On Apply: build payload from only checked fields; call `patchAlbums()`; show loading; on success: success toast + reload + clear selection; on error: error toast.  
  _Verification commands:_  
  - `npm run check && npm run format -- --check`

- [ ] T-034-20 – Wire Delete button (FR-034-10, S-034-09, UI-034-05 to UI-034-07).  
  _Intent:_ On Delete click: call `deleteAlbums(Array.from(selectedIds))`; show loading state; on success: toast + reload + clear selection; on error: error toast.  
  _Verification commands:_  
  - `npm run check`

### I10 – Set Owner Modal

- [ ] T-034-21 – Implement Set Owner dialog with user dropdown (FR-034-09, S-034-08, S-034-13, S-034-14, UI-034-04).  
  _Intent:_ Set Owner `<Dialog>`: fetch users list from existing users API (`GET /api/v2/User`), populate `<Select>` dropdown. Display warning: "All selected albums will be moved to the root level and their descendants will also be transferred." On Transfer: call `setOwner({album_ids, owner_id})`; loading state; success toast + reload + clear selection; error toast.  
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
  _Intent:_ Add `@OA\Get`, `@OA\Patch`, `@OA\Post`, `@OA\Delete` annotations with request/response schemas for all five endpoints.  
  _Verification commands:_  
  - Manual review.

- [ ] T-034-25 – Update roadmap and knowledge-map (documentation deliverables).  
  _Intent:_ Add Feature 034 to `docs/specs/4-architecture/roadmap.md` Active Features table. Update `docs/specs/4-architecture/knowledge-map.md` to reference `BulkAlbumController` and `BulkAlbumEdit.vue`. Update feature status in roadmap as work progresses.  
  _Verification commands:_  
  - Manual review.

## Notes / TODOs

- **Q-034-01 (open):** Should `TagAlbum` records appear in the list with a visual indicator but non-editable album-specific fields (license, album_sorting, aspect_ratio, album_timeline)? Currently spec says TagAlbums are out of scope. Revisit if admins report they need bulk-edit of TagAlbum base fields (description, copyright, is_nsfw, owner).
- **Q-034-02 (open):** Should the depth indicator be computed server-side (add `depth` field to `BulkAlbumResource` via Nestedset `withDepth()`) or client-side from `_lft`/`_rgt` ranges? Server-side is simpler and more reliable. The `withDepth()` scope from Kalnoy Nestedset is the natural choice.
- **Q-034-03 (open):** Should bulk delete require a specific confirmation click (e.g., the user must type "DELETE") given that it is destructive and irreversible? The problem statement says "no confirmation", but this may be reconsidered for the delete action specifically.
- **Q-034-04 (open):** Should the "Select all matching" endpoint respect the admin's own albums only, or truly all albums regardless of ownership? Per problem statement this is admin-only functionality so all albums are in scope.
