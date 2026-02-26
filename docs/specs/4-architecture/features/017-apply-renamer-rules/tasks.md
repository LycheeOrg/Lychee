# Feature 017 Tasks – Apply Renamer Rules & Watermark Confirmation

_Status: Draft_  
_Last updated: 2026-02-26_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions.

## Checklist

### I1 – Backend: Preview Endpoint & Renamer Filter

- [x] T-017-01 – Extend `Renamer` constructor to accept optional `rule_ids` filter.  
  _Intent:_ Add `?array $rule_ids = null` parameter. When provided, filter `$this->rules` to only those whose IDs are in the array. `PhotoRenamer` and `AlbumRenamer` pass through.  
  _Verification commands:_  
  - `php artisan test --filter=RenamerTest`  
  - `make phpstan`

- [x] T-017-01b – Extend `RenameRequest` and `RenamerController::rename()` with optional `rule_ids[]`.  
  _Intent:_ Add optional `rule_ids[]` validation to `RenameRequest`. Pass to `PhotoRenamer`/`AlbumRenamer` in `rename()`. When absent, backward-compatible (all enabled rules).  
  _Verification commands:_  
  - `php artisan test --filter=RenamerRules`  
  - `make phpstan`

- [x] T-017-02 – Create `PreviewRenameRequest` form request (FR-017-04, S-017-10, S-017-11, S-017-12).  
  _Intent:_ Validate `album_id`, `target` (photos|albums), `scope` (current|descendants), `rule_ids[]` (required, non-empty), optional `photo_ids[]`/`album_ids[]`. Authorize via album/photo edit policies.  
  _Verification commands:_  
  - `make phpstan`

- [x] T-017-03 – Write feature tests for preview endpoint (S-017-08, S-017-09, S-017-10, S-017-11, S-017-12).  
  _Intent:_ Test 401 (unauthenticated), 403 (forbidden), 422 (invalid input incl. empty rule_ids), 200 (changes found), 200 (no changes/empty), descendants scope, rule_ids filtering. Extend `BaseApiWithDataTest`, use `RequireSE` trait.  
  _Verification commands:_  
  - `php artisan test --filter=RenamerPreviewTest`

- [x] T-017-03 – Implement `preview()` method in `RenamerController` (FR-017-04).  
  _Intent:_ Resolve items by album_id+scope or explicit IDs. Apply `PhotoRenamer`/`AlbumRenamer` with selected `rule_ids`. Return `[{ id, original_title, new_title }]` filtered to changed items only.  
  _Verification commands:_  
  - `php artisan test --filter=RenamerPreviewTest`  
  - `make phpstan`

- [x] T-017-04 – Register `POST /Renamer::preview` route (API-017-01).  
  _Intent:_ Add route in `routes/api_v2.php` with `support:se` middleware, adjacent to existing Renamer routes.  
  _Verification commands:_  
  - `php artisan route:list --name=Renamer`  
  - `php artisan test --filter=RenamerPreviewTest`

### I2 – Frontend: Renamer Service Methods

- [x] T-017-05 – Add `preview()` method to `renamer-service.ts` (API-017-01).  
  _Intent:_ Call `POST /Renamer::preview` with typed request/response including `rule_ids[]`. Define `PreviewRenameRequest` and `PreviewRenameResponse` TypeScript types.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-06 – Add `rename()` method to `renamer-service.ts` (API-017-02).  
  _Intent:_ Call `PATCH /Renamer` with `photo_ids[]`/`album_ids[]` and optional `rule_ids[]`. This endpoint already exists but has no frontend caller.  
  _Verification commands:_  
  - `npm run check`

### I3 – Frontend: Modal State & Gallery Modal Toggle

- [x] T-017-07 – Add `is_apply_renamer_visible` to `ModalsState.ts` (UI-017-02).  
  _Intent:_ New boolean flag in the togglables store, defaulting to `false`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-08 – Add `toggleApplyRenamer()` to `galleryModals.ts`.  
  _Intent:_ New toggle function following the existing pattern. Export the ref and toggle function.  
  _Verification commands:_  
  - `npm run check`

### I4 – Frontend: ApplyRenamerDialog Component

- [x] T-017-09 – Create `ApplyRenamerDialog.vue` — Step 1 UI (FR-017-02, FR-017-03, S-017-05, S-017-06, S-017-07, S-017-22, S-017-23, S-017-24, S-017-25, S-017-26).  
  _Intent:_ Dialog with RadioButtons for target (Photos/Albums) and scope (Current/Descendants). Scope radio **shown only when target is Albums** — hidden for Photos (photos have no descendants, per Q-017-01 resolution). Warning text conditionally shown when descendants selected. `lockedTarget` prop disables target radio. Scrollable rule checklist fetched from `RenamerService.list()` on dialog open, filtered by target type (`is_photo_rule`/`is_album_rule`). Each rule shows order, name (`rule` field), and mode summary. Rules pre-checked per `is_enabled`. Preview button disabled when no rules checked. Cancel and Preview buttons.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-10 – Create `ApplyRenamerDialog.vue` — Step 2 UI (FR-017-05, S-017-08, S-017-09).  
  _Intent:_ Scrollable list of `original → new` title pairs. Enhanced empty-state message (per Q-017-02 resolution): "No titles would change. If you haven't configured renamer rules yet, visit Settings → Renamer Rules." with clickable link to settings. Apply disabled when empty. Loading spinner while preview API in flight.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-11 – Create `ApplyRenamerDialog.vue` — Apply logic (FR-017-06, S-017-13, S-017-14).  
  _Intent:_ On Apply click, call `RenamerService.rename()` with IDs from preview response and selected `rule_ids`. Clear album cache, emit `renamed`, close dialog, show toast. On error, show error toast, keep dialog open.  
  _Verification commands:_  
  - `npm run check`

### I5 – Frontend: WatermarkConfirmDialog Component

- [x] T-017-12 – Create `WatermarkConfirmDialog.vue` (FR-017-10, S-017-20, S-017-21).  
  _Intent:_ Simple PrimeVue Dialog with confirmation message. Cancel closes. Confirm calls `AlbumService.watermark(albumId)`, shows success toast, emits `confirmed` event.  
  _Verification commands:_  
  - `npm run check`

### I6 – Frontend: AlbumHero Integration

- [x] T-017-13 – Add renamer icon button to `AlbumHero.vue` (FR-017-01, S-017-01, S-017-02, S-017-03, S-017-04, UI-017-01).  
  _Intent:_ New `<a>` icon button (`pi pi-language` or `pi pi-sort-alpha-down`) in the icon row. Visible when `leftMenu.initData?.modules.is_mod_renamer_enabled && userStore.isLoggedIn && albumStore.rights?.can_edit`. Emits `openApplyRenamer`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-14 – Replace direct watermark call with confirmation dialog in `AlbumHero.vue` (FR-017-10).  
  _Intent:_ Remove direct `AlbumService.watermark()` call from `watermark()` function. Instead, open `WatermarkConfirmDialog` (either inline with `v-model` or via emit to parent). Keep the watermark icon button; only change the click behaviour.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-15 – Integrate `ApplyRenamerDialog` in `Album.vue` (UI-017-02).  
  _Intent:_ Add `<ApplyRenamerDialog>` alongside other dialog components. Bind `v-model:visible` to `is_apply_renamer_visible`. Pass `parentId` = `albumId`. Listen to `@renamed` for unselect + refresh.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-16 – Integrate `WatermarkConfirmDialog` in parent view (UI-017-07).  
  _Intent:_ Wire the confirmation dialog to the watermark hero button. Ensure watermarking only executes after confirmation.  
  _Verification commands:_  
  - `npm run check`

### I7 – Frontend: Context Menu Integration

- [x] T-017-17 – Add `toggleApplyRenamer` to `PhotoCallbacks` and `AlbumCallbacks` types (UI-017-08).  
  _Intent:_ Extend callback type definitions in `contextMenu.ts`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-18 – Add "Apply Renamer Rules" to `photoMenu()` (S-017-15, FR-017-07).  
  _Intent:_ New menu item after "License" with icon `pi pi-language`, access gated by `can_edit && is_mod_renamer_enabled`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-19 – Add "Apply Renamer Rules to Selected" to `photosMenu()` (S-017-16).  
  _Intent:_ Bulk variant for multi-photo selection.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-20 – Add "Apply Renamer Rules" to `albumMenu()` and `albumsMenu()` (S-017-17, S-017-18, FR-017-08).  
  _Intent:_ Menu items for single and multi-album selection.  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-21 – Wire callbacks in `AlbumPanel.vue` (S-017-19).  
  _Intent:_ Add `toggleApplyRenamer` to `photoCallbacks` and `albumCallbacks` objects. Set `lockedTarget` and pass selected IDs to dialog when triggered from context menu. For photos: scope hidden, `photo_ids[]` sent directly. For albums: scope shown, `album_ids[]` + `scope` sent (per Q-017-01 resolution).  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-22 – Wire callbacks in other gallery panels: Tag, Timeline, Search.  
  _Intent:_ Repeat I7 wiring for `TagPanel`, `TimelinePanel`, `SearchPanel` (or equivalent) where context menus are used.  
  _Verification commands:_  
  - `npm run check`

### I8 – Translations

- [x] T-017-23 – Add English translations to `lang/en/gallery.php` and `lang/en/dialogs.php`.  
  _Intent:_ Keys for hero tooltip, context menu items (in `gallery.php` menus/hero arrays), dialog titles, step labels, button labels, warning text, empty state, toasts, watermark confirmation (in `dialogs.php`).  
  _Verification commands:_  
  - `npm run check`

- [x] T-017-24 – Add placeholder translations to 21 other `lang/<locale>/gallery.php` and `lang/<locale>/dialogs.php` files.  
  _Intent:_ Copy English values as placeholders for ar, bg, cz, de, el, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW.  
  _Verification commands:_  
  - `npm run check`

### I9 – Quality Gate & Manual Testing

- [x] T-017-25 – PHP quality gate (backend changes).  
  _Intent:_ Run formatting, tests, and static analysis.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`

- [x] T-017-26 – Frontend quality gate (frontend changes).  
  _Intent:_ Run formatting and checks.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-017-27 – Manual test: hero button flow (S-017-01 through S-017-14, S-017-22 through S-017-26).  
  _Intent:_ Verify: button visibility conditions, Step 1 defaults, rule loading/filtering, descendants warning, preview with/without changes, apply success/failure, cache refresh.  
  _Notes:_ Document results in plan appendix.

- [ ] T-017-28 – Manual test: context menu flow (S-017-15 through S-017-19).  
  _Intent:_ Verify: menu items appear for single/multi photo/album selection, target locked, preview and apply work with explicit IDs.  
  _Notes:_ Document results in plan appendix.

- [ ] T-017-29 – Manual test: watermark confirmation (S-017-20, S-017-21).  
  _Intent:_ Verify: cancel does not watermark, confirm triggers watermark + toast.  
  _Notes:_ Document results in plan appendix.

## Notes / TODOs

- The existing `PATCH /Renamer` endpoint already handles batch renaming. The new `preview()` endpoint mirrors its logic but returns diffs instead of mutating. Both now accept optional `rule_ids[]` to select which rules to apply.
- The `PreviewRenameRequest` will need descendant resolution logic. Consider extracting an `AlbumDescendantResolver` helper if the query is reused elsewhere.
- For context menu integration, the `lockedTarget` prop on the dialog prevents the user from switching between photos/albums when the selection type is already known.

---

*Last updated: 2026-02-26*
