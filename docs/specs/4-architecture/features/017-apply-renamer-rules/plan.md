# Feature Plan 017 – Apply Renamer Rules & Watermark Confirmation

_Linked specification:_ `docs/specs/4-architecture/features/017-apply-renamer-rules/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-26

## Vision & Success Criteria

Surface the existing Renamer engine in the gallery UI so users can apply their renaming rules to existing photo/album titles without manual intervention. A two-step dialog (options → preview → apply) gives users confidence before committing changes. The watermark button gets a small confirmation gate to prevent accidental bulk watermarking.

**Success signals:**
- Users can trigger renaming from the Album Hero and from context menus on selected items.
- A preview step shows exactly which titles will change before any mutation.
- Watermarking requires explicit confirmation.
- All new UI strings are translation-ready.

## Scope Alignment

- **In scope:**
  - New `POST /Renamer::preview` backend endpoint.
  - Frontend `rename()` method in `renamer-service.ts`.
  - `ApplyRenamerDialog.vue` multi-step dialog component.
  - `WatermarkConfirmDialog.vue` confirmation dialog.
  - Hero button in `AlbumHero.vue`.
  - Context menu items in `contextMenu.ts` for photos and albums.
  - Modal state flags and gallery modal toggles.
  - English translations + placeholders for 21 other languages.
  - Feature tests for the preview endpoint.

- **Out of scope:**
  - Renamer rule CRUD (already implemented).
  - Changes to the Renamer engine internals.
  - Undo/rollback of applied renames.
  - Smart Album / Tag Album hero button (context menu still works if selection available).

## Dependencies & Interfaces

| Dependency | Kind | Notes |
|-----------|------|-------|
| `app/Metadata/Renamer/Renamer.php` | Backend | Core rule engine — reused for preview and apply. |
| `app/Metadata/Renamer/PhotoRenamer.php` | Backend | Photo-specific renamer. |
| `app/Metadata/Renamer/AlbumRenamer.php` | Backend | Album-specific renamer. |
| `app/Http/Controllers/RenamerController.php` | Backend | Existing controller — add `preview()` action. |
| `app/Http/Requests/Renamer/RenameRequest.php` | Backend | Existing request — reused by apply step. |
| `resources/js/services/renamer-service.ts` | Frontend | Add `preview()` and `rename()` methods. |
| `resources/js/stores/ModalsState.ts` | Frontend | Add `is_apply_renamer_visible` flag. |
| `resources/js/composables/modalsTriggers/galleryModals.ts` | Frontend | Add `toggleApplyRenamer()`. |
| `resources/js/composables/contextMenus/contextMenu.ts` | Frontend | Add renamer items to photo/album menus. |
| `resources/js/components/gallery/albumModule/AlbumHero.vue` | Frontend | Add hero button + watermark confirm. |
| PrimeVue Dialog, RadioButton, Button, DataTable/ScrollPanel | Frontend | UI components. |

## Assumptions & Risks

- **Assumptions:**
  - The SE renamer module is already gated by middleware (`support:se`); the frontend checks `is_se_enabled`.
  - The existing `PATCH /Renamer` endpoint handles the actual rename and does not need modification.
  - Albums have a flat `photos` relationship and a `children` (sub-albums) relationship for descendant traversal.

- **Risks / Mitigations:**
  - **Timeout on large descendant trees:** Mitigated by showing a warning in the UI (FR-017-03). Backend uses chunked processing. Future: consider queue-based async processing (out of scope for now).
  - **Preview drift:** Between preview and apply, titles could change. Acceptable for MVP — preview is informational, not a lock.

## Increment Map

### I1 – Backend: Preview Endpoint (≤ 90 min)

- _Goal:_ Create `POST /Renamer::preview` that returns before/after title pairs.
- _Preconditions:_ Renamer engine classes exist and work.
- _Steps:_
  1. Create `PreviewRenameRequest` form request with validation for `album_id`, `target` (photos|albums), `scope` (current|descendants), optional `photo_ids[]`, optional `album_ids[]`.
  2. Add `preview()` method to `RenamerController`:
     - If `photo_ids[]` or `album_ids[]` supplied (context menu path), use those directly.
     - Otherwise, resolve items from `album_id` + `scope`:
       - `current` + `photos`: photos where `album_id = ?`
       - `descendants` + `photos`: photos in album and all descendant albums (recursive CTE or `_lft`/`_rgt` nested set).
       - `current` + `albums`: direct child albums of `album_id`.
       - `descendants` + `albums`: all descendant albums.
     - Instantiate `PhotoRenamer` or `AlbumRenamer` for the authenticated user.
     - For each item, compute `new_title = renamer.handle(original_title)`.
     - Return only items where `new_title !== original_title`.
  3. Register route `POST /Renamer::preview` with `support:se` middleware.
  4. Write feature tests: 401, 403, 422, 204 (no changes), 200 (with changes), descendants scope.
- _Commands:_ `php artisan test --filter=RenamerPreview`, `make phpstan`
- _Exit:_ Preview endpoint returns correct diffs; all tests green.

### I2 – Frontend: Renamer Service Methods (≤ 30 min)

- _Goal:_ Add `preview()` and `rename()` to `renamer-service.ts`.
- _Preconditions:_ I1 complete (endpoint exists).
- _Steps:_
  1. Add `preview()` method calling `POST /Renamer::preview`.
  2. Add `rename()` method calling `PATCH /Renamer`.
  3. Define TypeScript types for request/response.
- _Commands:_ `npm run check`
- _Exit:_ Service methods compile without errors.

### I3 – Frontend: Modal State & Gallery Modal Toggle (≤ 20 min)

- _Goal:_ Wire up `is_apply_renamer_visible` in the modal system.
- _Preconditions:_ None.
- _Steps:_
  1. Add `is_apply_renamer_visible: false` to `ModalsState.ts`.
  2. Add `toggleApplyRenamer()` to `galleryModals.ts`.
  3. Export the new toggle and ref.
- _Commands:_ `npm run check`
- _Exit:_ Toggle function available for use by dialog and hero button.

### I4 – Frontend: ApplyRenamerDialog Component (≤ 90 min)

- _Goal:_ Build the two-step dialog component.
- _Preconditions:_ I2, I3 complete.
- _Steps:_
  1. Create `resources/js/components/forms/renamer/ApplyRenamerDialog.vue`.
  2. Props: `parentId`, optional `photoIds`, optional `albumIds`, optional `lockedTarget` (photos|albums|null).
  3. Step 1: RadioButtons for target (Photos/Albums — disabled if `lockedTarget` set) and scope (Current/Descendants). Warning text when descendants selected. Preview button.
  4. Step 2: Scrollable list of `original → new` pairs. Empty state. Apply and Cancel buttons.
  5. Loading state while preview API call in flight.
  6. On Apply: call `RenamerService.rename()` with collected IDs, emit `renamed` event, close dialog, show success toast.
  7. On Cancel (either step): close dialog, reset state.
- _Commands:_ `npm run check`
- _Exit:_ Component renders both steps correctly.

### I5 – Frontend: WatermarkConfirmDialog Component (≤ 30 min)

- _Goal:_ Simple confirmation dialog for watermarking.
- _Preconditions:_ None.
- _Steps:_
  1. Create `resources/js/components/forms/album/WatermarkConfirmDialog.vue` using the same pattern as `PhotoLicenseDialog`.
  2. Props: `albumId`.
  3. Confirm button calls `AlbumService.watermark(albumId)` and shows toast.
  4. Cancel button closes without action.
- _Commands:_ `npm run check`
- _Exit:_ Component renders and handles confirm/cancel.

### I6 – Frontend: AlbumHero Integration (≤ 45 min)

- _Goal:_ Add the renamer button and watermark confirmation to AlbumHero.
- _Preconditions:_ I4, I5 complete.
- _Steps:_
  1. Add renamer icon button to `AlbumHero.vue` icon row (near watermark button). Visibility: `is_se_enabled && userStore.isLoggedIn && albumStore.rights?.can_edit`. Emit `openApplyRenamer` event.
  2. Replace direct `watermark()` call with emit `openWatermarkConfirm` (or use inline dialog with `v-model`).
  3. Integrate `WatermarkConfirmDialog` in `AlbumHero.vue` (or parent `Album.vue`).
  4. Wire `ApplyRenamerDialog` in `Album.vue` — listen to `openApplyRenamer` from hero, pass `albumId`.
- _Commands:_ `npm run check`
- _Exit:_ Hero button opens renamer dialog; watermark button opens confirmation.

### I7 – Frontend: Context Menu Integration (≤ 45 min)

- _Goal:_ Add "Apply Renamer Rules" items to photo and album context menus.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Add `toggleApplyRenamer` to `PhotoCallbacks` and `AlbumCallbacks` types.
  2. Add menu item to `photoMenu()`: label `"gallery.menus.apply_renamer"`, icon `pi pi-language`, access: `can_edit && is_se_enabled`.
  3. Add menu item to `photosMenu()`: label `"gallery.menus.apply_renamer_all"`.
  4. Add menu item to `albumMenu()` and `albumsMenu()`.
  5. Wire callbacks in `AlbumPanel.vue` and other gallery panels (Tag, Timeline, Search) where context menus are used.
  6. When triggered from context menu, pass `lockedTarget` and selected IDs to `ApplyRenamerDialog`.
- _Commands:_ `npm run check`
- _Exit:_ Context menu items appear and open dialog with correct pre-selection.

### I8 – Translations (≤ 30 min)

- _Goal:_ Add all new UI strings.
- _Preconditions:_ I4, I5, I7 complete (all strings known).
- _Steps:_
  1. Add English translations to `lang/php_en.json` under appropriate keys.
  2. Add placeholder entries to all other `lang/php_*.json` files.
- _Commands:_ `npm run check`
- _Exit:_ No missing translation warnings.

### I9 – Quality Gate & Manual Testing (≤ 60 min)

- _Goal:_ Pass all quality checks and verify end-to-end flow.
- _Preconditions:_ All previous increments complete.
- _Steps:_
  1. `vendor/bin/php-cs-fixer fix`
  2. `npm run format`
  3. `npm run check`
  4. `php artisan test`
  5. `make phpstan`
  6. Manual testing: hero button flow, context menu flow, watermark confirmation, empty preview, descendants warning.
- _Commands:_ Listed above.
- _Exit:_ All gates green. Manual scenarios pass.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-017-01 | I6 | Hero button visibility |
| S-017-02 | I6 | SE check |
| S-017-03 | I6 | can_edit check |
| S-017-04 | I6 | Opens dialog |
| S-017-05 | I4 | Default radio values |
| S-017-06 | I4 | Warning display |
| S-017-07 | I4 | Cancel flow |
| S-017-08 | I1, I4 | Preview with results |
| S-017-09 | I1, I4 | Empty preview |
| S-017-10 | I1 | 401 test |
| S-017-11 | I1 | 403 test |
| S-017-12 | I1 | 422 test |
| S-017-13 | I4 | Apply success |
| S-017-14 | I4 | Apply failure |
| S-017-15 | I7 | Single photo context menu |
| S-017-16 | I7 | Multi photo context menu |
| S-017-17 | I7 | Single album context menu |
| S-017-18 | I7 | Multi album context menu |
| S-017-19 | I7 | Locked target |
| S-017-20 | I5, I6 | Watermark cancel |
| S-017-21 | I5, I6 | Watermark confirm |

## Exit Criteria

- [ ] Preview endpoint returns correct diffs (feature tests green).
- [ ] ApplyRenamerDialog renders both steps, handles all states.
- [ ] Hero button visible under correct conditions, opens dialog.
- [ ] Context menu items appear for photos and albums, open dialog with locked target.
- [ ] WatermarkConfirmDialog gates watermark execution.
- [ ] All translations present (English full, others placeholder).
- [ ] PHPStan, php-cs-fixer, npm format, npm check, php artisan test — all green.
- [ ] Manual testing of full flow passes.

## Follow-ups / Backlog

- Queue-based async renaming for very large descendant trees (avoids timeout).
- Undo/rollback support (would require storing original titles before overwrite).
- Progress indicator / streaming results for large previews.
- Extend to Smart Albums / Tag Albums hero button (currently context-menu only).

---

*Last updated: 2026-02-26*
