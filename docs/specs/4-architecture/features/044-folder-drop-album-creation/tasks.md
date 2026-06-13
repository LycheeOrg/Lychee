# Feature 044 Tasks – Folder Drag-and-Drop Album Creation

_Status: Draft_  
_Last updated: 2026-06-13 (rev 3 — symbolic links, feature toggle, max depth config)_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I-1 – Backend: Config entries and UploadConfig extension

- [ ] T-044-00a – Create migration for `folder_upload_enabled` and `folder_upload_max_depth` config entries (DO-044-03, DO-044-04, CFG-044-01, CFG-044-02).  
  _Intent:_ Add two config entries to the `configs` table so admins can control folder drag-and-drop availability and recursion depth.  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan test --filter=Config`  
  _Notes:_ `folder_upload_enabled` — boolean (type_range "0|1"), default `1`, category Upload. `folder_upload_max_depth` — integer, default `0` (unlimited), category Upload. Follow the pattern of existing config migrations.

- [ ] T-044-00b – Extend `UploadConfig.php` to expose both settings (DO-044-05, FR-044-13, FR-044-14, API-044-03).  
  _Intent:_ Add `folder_upload_enabled` and `folder_upload_max_depth` as computed properties on the `UploadConfig` resource so the frontend can read them via `GET /Gallery::getUploadLimits`.  
  _File:_ `app/Http/Resources/GalleryConfigs/UploadConfig.php`  
  _Verification commands:_  
  - `php artisan test --filter=UploadConfig`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ `folder_upload_enabled`: `Configs::getValueAsBool('folder_upload_enabled', true)`. `folder_upload_max_depth`: `Configs::getValueAsInt('folder_upload_max_depth', 0)` (0 = unlimited).

- [ ] T-044-00c – Regenerate TypeScript types (DO-044-05).  
  _Intent:_ Run `php artisan typescript:transform` so `App.Http.Resources.GalleryConfigs.UploadConfig` includes the new fields in the frontend type system.  
  _Verification commands:_  
  - `php artisan typescript:transform`  
  - `npm run check`  
  _Notes:_ Verify `lychee.d.ts` includes `folder_upload_enabled: boolean` and `folder_upload_max_depth: number`.

- [ ] T-044-00d – Add admin UI settings for both config entries (FR-044-13, FR-044-14).  
  _Intent:_ Allow admins to toggle folder drag-and-drop and set max depth from the Settings UI.  
  _Notes:_ Migration metadata (cat, type_range, description, order) should be sufficient for Lychee's auto-generated config UI. Verify settings appear in Upload section after migration.

---

### Increment I0 – Extend `Uploadable` type with `album_id`

- [ ] T-044-01 – Add `album_id?: string` to the `Uploadable` type (DO-044-01).  
  _Intent:_ Allow each queue entry to carry an album ID override so that files dropped from a folder can be routed to the newly-created album rather than the route-level album.  
  _File:_ `resources/js/composables/album/uploadEvents.ts`  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Only the type definition changes here. No behavior change. Existing code that does not set `album_id` is unaffected.

---

### Increment I1 – Update `UploadPanel.vue` to use per-file `album_id`

- [ ] T-044-02 – Pass `uploadable.album_id ?? albumId` to `UploadingLine` (FR-044-04).  
  _Intent:_ When an `Uploadable` entry has an `album_id` override (set during folder drop), use that instead of the route-level album ID. Preserves existing behavior when no override is present.  
  _File:_ `resources/js/components/modals/UploadPanel.vue` (~line 37)  
  _Change:_ `:album-id="albumId"` → `:album-id="uploadable.album_id ?? albumId"`  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  _Notes:_ No visual change. Only affects which album photos land in.

---

### Increment I2 – Create `folderDrop.ts` composable

- [ ] T-044-03 – Implement `getEntry()` helper with browser fallback (FR-044-01, FR-044-10).  
  _Intent:_ Wrap the `webkitGetAsEntry()` / `getAsEntry()` call behind a safe helper that returns `null` if neither API is available, enabling graceful degradation on unsupported browsers.  
  _File:_ `resources/js/composables/album/folderDrop.ts` (new)  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Use `item.webkitGetAsEntry?.() ?? (item as any).getAsEntry?.() ?? null` pattern.

- [ ] T-044-04 – Implement `readDirectoryEntries(dirEntry)` with batch loop (FR-044-03, FR-044-12, NFR-044-02).  
  _Intent:_ Read all direct children (both files and sub-directories) from a directory entry, looping `readEntries()` until an empty batch is returned. Handles folders with more than 100 entries (browser batch limit). Returns all `FileSystemEntry` objects — callers decide what to do with files vs. directories.  
  _File:_ `resources/js/composables/album/folderDrop.ts`  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Returns `Promise<FileSystemEntry[]>`. Do NOT filter to files-only here — sub-directories are needed for recursive processing (FR-044-12). Conversion from `FileSystemFileEntry` to `File` happens in the caller via the `.file()` callback wrapped in a Promise.

- [ ] T-044-05 – Implement `resolveOrCreateAlbum()` — existing album lookup before creation (FR-044-02, FR-044-11, S-044-12, S-044-13, S-044-14, S-044-17).  
  _Intent:_ Before calling the create API, perform a case-insensitive name search of the provided `existingAlbums` list. Return the existing album's ID if found; otherwise call `AlbumService.createAlbum()` and return the new ID. This is the idempotency mechanism that prevents duplicate albums when dropping the same folder twice.  
  _File:_ `resources/js/composables/album/folderDrop.ts`  
  _Signature:_  
  ```ts
  function resolveOrCreateAlbum(
    name: string,
    parent_id: string | null,
    existingAlbums: { id: string; title: string }[]
  ): Promise<string>
  ```  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Match: `existingAlbums.find(a => a.title.toLowerCase() === name.toLowerCase())`. First match wins if duplicates exist. Throws/rejects on creation failure so callers can catch per-folder.

- [ ] T-044-05b – Implement `processDirectory()` — recursive sub-folder handling with depth enforcement (FR-044-03, FR-044-04, FR-044-12, FR-044-14, FR-044-15, S-044-15, S-044-16, S-044-18, S-044-20, S-044-21, S-044-22, S-044-23, S-044-24).  
  _Intent:_ The core recursive function. For a given `FileSystemDirectoryEntry`, parent context, and current depth: enforce the max depth limit, resolve/create the album, enumerate all children (including those reached via symbolic links, which are transparent), push files to the upload queue with the album ID override, and recursively call itself for any sub-directory entries.  
  _File:_ `resources/js/composables/album/folderDrop.ts`  
  _Signature:_  
  ```ts
  function processDirectory(
    dirEntry: FileSystemDirectoryEntry,
    parent_id: string | null,
    existingAlbums: { id: string; title: string }[],
    list_upload_files: Ref<Uploadable[]>,
    currentDepth: number,
    maxDepth: number
  ): Promise<void>
  ```  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ First check: `if (maxDepth > 0 && currentDepth > maxDepth) return;`. Pass empty array `[]` as `existingAlbums` for recursive calls. `currentDepth` starts at 1 for the top-level dropped folder. Increment by 1 for each recursive call. Symbolic links: no special handling — the browser handles them transparently as regular file/directory entries. Sub-directory entries processed with `Promise.allSettled` to isolate failures.

- [ ] T-044-06 – Implement `handleFolderDrop()` and error handling (FR-044-02, FR-044-05, FR-044-06, FR-044-14, S-044-03, S-044-04, S-044-09, S-044-18).  
  _Intent:_ Top-level orchestrator. Classifies dropped items into directory entries and flat file entries. Runs `processDirectory()` for each directory in parallel via `Promise.allSettled`. Pushes flat files to the queue without an album_id override. Shows error toast on per-folder failure. Returns `true` if any items were queued.  
  _File:_ `resources/js/composables/album/folderDrop.ts`  
  _Signature:_  
  ```ts
  function handleFolderDrop(
    items: DataTransferItemList,
    parent_id: string | null,
    existingAlbums: { id: string; title: string }[],
    list_upload_files: Ref<Uploadable[]>,
    maxDepth: number
  ): Promise<boolean>
  ```  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Pass `currentDepth = 1` and `maxDepth` to each `processDirectory` call. Use `Promise.allSettled` (not `Promise.all`) so one failed folder does not cancel others. Check `result.status === "rejected"` to show error toasts. Error toast: use the same notification mechanism as other upload errors (identify pattern from existing components before coding).

---

### Increment I3 – Modify `uploadEvents.ts` to use folder drop handler

- [ ] T-044-07 – Update `useMouseEvents` to accept `parent_id`, `existingAlbums`, and `setup`, and branch on folder vs. file drops (FR-044-01, FR-044-06, FR-044-07, FR-044-08, FR-044-10, FR-044-11, FR-044-13, NFR-044-01, S-044-04, S-044-05, S-044-08, S-044-10, S-044-19).  
  _Intent:_ Add `parent_id: Ref<string | null>`, `existingAlbums: Ref<{ id: string; title: string }[]>`, and `setup: Ref<UploadConfig | undefined>` to `useMouseEvents`. In `dropUpload`: first check `folder_upload_enabled` — if false, skip to flat-file path. If enabled, check for directory entries and call `handleFolderDrop(items, parent_id.value, existingAlbums.value, list_upload_files, setup.value?.folder_upload_max_depth ?? 0)`. Open UploadPanel when it resolves `true`. Else fall through to flat-file path unchanged.  
  _File:_ `resources/js/composables/album/uploadEvents.ts`  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  _Notes:_ The flat-file fallback path must be completely preserved. The `can_upload` guard must still be checked first. `setup` may be undefined if `UploadConfig` hasn't loaded yet — in that case treat `folder_upload_enabled` as `true` (default behavior) to avoid blocking on load.

---

### Increment I4 – Update `Albums.vue` and `Album.vue` to pass `parent_id`

- [ ] T-044-08 – Pass `parent_id`, `existingAlbums`, and `setup` to `useMouseEvents` in `Albums.vue` and `Album.vue` (FR-044-02, FR-044-11, FR-044-13, FR-044-14, S-044-01, S-044-02, S-044-12, S-044-13, S-044-19, S-044-20).  
  _Intent:_ Ensure dropped folders create/resolve albums in the correct location, with the correct name-matching context, and respecting the admin feature flag and depth limit.  
  _Files:_  
  - `resources/js/views/gallery-panels/Albums.vue`:
    - `const parent_id = ref<string | null>(null)`
    - `const existingAlbums = computed(() => albumsStore.albums.map(a => ({ id: a.id, title: a.title })))` (adjust to actual store shape)
    - Pass `parent_id`, `existingAlbums`, and the shared `setup` ref (UploadConfig) to `useMouseEvents`
  - `resources/js/views/gallery-panels/Album.vue`:
    - `const parent_id = computed(() => route.params.albumId as string | null)`
    - `const existingAlbums = computed(() => /* child albums of current album from store */)` (adjust to actual store shape)
    - Pass all three to `useMouseEvents`  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  _Notes:_ Verify exact store property names before coding. Determine whether `setup` (UploadConfig) is already available as a shared ref or needs to be loaded per-view. If `UploadPanel` owns it, consider lifting it to a shared store or loading it in the view directly via `UploadService.getSetUp()`.

---

### Increment I5 – Integration testing and quality gate

- [ ] T-044-09 – Manual integration test: single folder drop and existing album reuse (S-044-01, S-044-06, S-044-07, S-044-12, S-044-13, S-044-14).  
  _Intent:_ Verify the golden path and idempotency.  
  _Verification steps:_  
  - Drop one folder (5+ images): album appears with folder name, all images in album.  
  - Drop the same folder again (same name): no duplicate album; new images added to existing album.  
  - Drop a folder with the same name but different casing: same existing album used.  
  - Drop a folder name that does not exist: new album created.  
  - Drop a folder with 150+ images to test batch reading (S-044-06).  
  _Notes:_ Test in Chrome and Firefox minimum.

- [ ] T-044-10 – Manual integration test: multiple folders, mixed drops, and recursive sub-folders (S-044-03, S-044-04, S-044-05, S-044-15, S-044-16, S-044-17).  
  _Intent:_ Verify parallel album creation, mixed-drop routing, and recursive sub-folder album tree creation.  
  _Verification steps:_  
  - Drop two folders simultaneously: both albums appear, files routed correctly.  
  - Drop one folder + one image file: album created, image uploaded to current album/unsorted.  
  - Drop only flat files: existing behavior, no album created (regression check — critical).  
  - Drop a folder containing sub-folders: verify album tree created (parent album + sub-albums), files routed to correct level.  
  - Drop a deeply nested folder (3+ levels): verify full recursive album tree.  
  - Drop a folder containing a sub-folder whose name matches an existing child album: existing sub-album reused.

- [ ] T-044-11 – Manual integration test: sub-album creation inside an album (S-044-02).  
  _Intent:_ Verify that dropping a folder while inside an existing album creates a sub-album with the correct parent.  
  _Verification steps:_  
  - Navigate into an existing album.  
  - Drop a folder (with sub-folders).  
  - Confirm: sub-album appears inside the parent album with folder name, images inside sub-album, recursive sub-albums created if present.

- [ ] T-044-12 – Manual integration test: empty folder and error scenarios (S-044-09, S-044-11, S-044-18).  
  _Intent:_ Verify graceful handling of edge cases.  
  _Verification steps:_  
  - Drop a folder with 0 images: album is created, UploadPanel either stays closed or opens with 0 files.  
  - Simulate album creation error (e.g., temporarily remove create permission): toast error shown, no crash.  
  - Drop a folder with a sub-folder that fails album creation: parent album and sibling sub-folders continue normally; error toast for failed sub-folder.

- [ ] T-044-13 – Manual integration test: feature toggle and depth limit (S-044-19, S-044-20, S-044-21, S-044-22).  
  _Intent:_ Verify admin config settings control the feature correctly.  
  _Verification steps:_  
  - Set `folder_upload_enabled = false`: drop a folder — no album created, files treated as flat upload.  
  - Set `folder_upload_max_depth = 1`: drop a folder with sub-folders — top album created, sub-folders ignored.  
  - Set `folder_upload_max_depth = 2`: drop a folder with 3 levels — top + one sub-level created, third level ignored.  
  - Set `folder_upload_max_depth = 0`: full unlimited recursion (same as default).

- [ ] T-044-14 – Full quality gate (NFR-044-03, NFR-044-04).  
  _Intent:_ Ensure no TypeScript, formatting, or PHP regressions introduced by this feature.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `php artisan test`  
  - `npm run check`  
  - `npm run format`  
  _Notes:_ All must pass with zero errors before feature is marked complete.

- [ ] T-044-15 – Update roadmap.md with feature 044 entry and mark complete.  
  _Intent:_ Keep the roadmap current.  
  _File:_ `docs/specs/4-architecture/roadmap.md`  
  _Notes:_ Move to Completed section when all tasks are done.

## Notes / TODOs

- **Notification/toast:** Before writing T-044-06 error handling, identify how existing components show error toasts. Look at how `AlbumCreateDialog.vue` or the upload panel surfaces errors — use the same pattern.
- **`Album.vue` mouse events:** Confirm that `Album.vue` uses `useMouseEvents`. If it uses a different composable or a different pattern, adapt T-044-08 accordingly.
- **File extension filtering:** The existing upload service and backend handle unsupported file types gracefully (error status in the upload line). No need to pre-filter in the frontend beyond what the browser's FileSystem API provides; the upload error path already covers unsupported types.
- **`parent_id` type in `useMouseEvents`:** Use `Ref<string | null>` to be consistent with existing parameters (`can_upload`, `is_upload_visible`).
- **`existingAlbums` store shape:** The exact property path in the Pinia albums store for root-level albums and child albums must be verified by reading the store before coding T-044-08. Adapt the computed property accordingly.
- **`processDirectory` recursion:** There is no synchronous call stack risk — each recursive call is a Promise chain, not a synchronous stack frame. Deep nesting will create many concurrent Promises but will not stack-overflow. The `maxDepth` check is the primary safety bound.
- **Symbolic links:** The browser's `FileSystemEntry` API follows symbolic links transparently (a symlink to a file → `FileSystemFileEntry`; a symlink to a directory → `FileSystemDirectoryEntry`). OS file managers typically resolve symlinks before handing entries to the browser. Circular symlinks are extremely unlikely in a drag-drop context and are implicitly bounded by `folder_upload_max_depth`. No special handling required.
- **`folder_upload_enabled` default when `setup` not yet loaded:** Treat as enabled (true) to avoid blocking drops while the UploadConfig loads. This is consistent with the feature being on by default.
- **Sharing `setup` (UploadConfig) between view and composable:** Currently, `UploadPanel` loads and owns the `setup` ref internally. Determine before T-044-08 whether to: (a) lift `setup` into a shared Pinia store, (b) load it separately in each view, or (c) pass it as a prop/inject. Option (a) is cleanest but requires a store change; option (b) causes a duplicate request. Evaluate against existing patterns.

---

*Last updated: 2026-06-13*
