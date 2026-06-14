# Feature Plan 044 – Folder Drag-and-Drop Album Creation

_Linked specification:_ `docs/specs/4-architecture/features/044-folder-drop-album-creation/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-06-13 (rev 3 — symbolic links, feature toggle, max depth config)

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Users can drag a folder from their desktop directly onto the Lychee Albums page and have an album created and populated automatically, removing the multi-step flow of: create album → navigate into album → drag files.

**Success Criteria:**
- Dropping a folder on the Albums page creates an album named after the folder, or routes to an existing album with the same name (case-insensitive).
- All supported images/videos at the top level of the dropped folder are uploaded to the resolved/created album.
- Sub-folders inside the dropped folder are processed recursively: each becomes a sub-album (resolved or created) with the same name-matching logic.
- Multiple folders dropped at once each process independently in parallel.
- Individual file drops continue to work exactly as before.
- Dropping a folder inside an existing album creates/resolves a sub-album under it.
- No backend changes required.

## Scope Alignment

**In scope:**
- Backend: one migration adding `folder_upload_enabled` and `folder_upload_max_depth` config entries
- Backend: extend `UploadConfig.php` resource to expose both settings; regenerate TypeScript types
- Backend: admin UI settings for both config entries
- Frontend: New `folderDrop.ts` composable (folder detection, existing-album lookup, album creation, recursive directory traversal with depth enforcement)
- Frontend: Extending the `Uploadable` type with optional `album_id` override
- Frontend: Modifying `uploadEvents.ts` to branch on folder vs. file entries (respecting `folder_upload_enabled`)
- Frontend: Modifying `UploadPanel.vue` to use per-file `album_id` when present
- Frontend: Propagating `parent_id` context and loaded albums from Albums.vue and Album.vue to the drop handler
- Browser fallback for missing `webkitGetAsEntry()` support

**Out of scope:**
- Backend changes beyond config migration and UploadConfig extension
- Zip/archive extraction
- Per-file album rename during upload
- Folder drop onto the open UploadPanel dialog (only the album-grid backdrop is supported)

## Dependencies & Interfaces

| Dependency | Type | Impact |
|------------|------|--------|
| `Uploadable` type in `uploadEvents.ts` | Type definition | Add optional `album_id` field |
| `UploadPanel.vue` | Component | Pass `uploadable.album_id ?? albumId` to `UploadingLine` |
| `AlbumService.createAlbum()` | Service | Called per folder/sub-folder when no existing album name matches |
| `Albums.vue` / `Album.vue` | Views | Must pass current `parent_id` and loaded albums list to drop handler |
| Pinia albums store | State | Source of currently-loaded albums for name-match lookup |
| `FileSystemDirectoryEntry` Web API | Browser API | Core mechanism for reading dropped folders and sub-folders recursively |

## Assumptions & Risks

**Assumptions:**
- The existing `UploadingLine` component already accepts `albumId` as a prop and uses it for upload targeting — only the source of that value changes (from route to per-file).
- `AlbumService.createAlbum()` returns the new album's ID on success.
- File extension filtering at the frontend level is sufficient (unsupported extensions will result in upload-level errors that are already handled gracefully).
- The `readEntries()` loop pattern is reliable across supported browsers.

**Risks / Mitigations:**

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| `readEntries()` batch limit (100 entries/call) causes incomplete folder reading | High (folders with 100+ files) | High | Always loop `readEntries()` until empty batch returned (NFR-044-02) |
| Safari incompatibility with FileSystem API | Medium | Low | Graceful fallback to flat-file upload (FR-044-10) |
| Name-match lookup misses because Pinia store doesn't have child albums loaded | Medium | Low | Acceptable fallback: create a new album (correct for newly created tree levels); no data loss |
| Deep folder nesting causes very long upload sessions | Low | Low | Processing is async; user can monitor via UploadPanel; no stack overflow risk (each level is a separate Promise chain, not synchronous recursion) |
| Mixed drop (files + folders) routing error | Medium | Medium | Explicit branching in `dropUpload` — directory entries handled separately from file entries |

## Implementation Drift Gate

After completing all increments:
1. Run `npm run check` — zero TypeScript errors
2. Run `npm run format` — no formatting issues
3. Manual test: drop single folder, verify album created + files uploaded
4. Manual test: drop two folders simultaneously, verify two albums
5. Manual test: drop individual files only, verify existing behavior unchanged
6. Manual test: drop folder inside an album, verify sub-album created
7. Manual test: drop folder with 150+ files, verify all files queued
8. Manual test: drop folder matching existing album name, verify reuse (no duplicate)
9. Manual test: drop folder with sub-folders, verify recursive album tree

## Increment Map

### I-1 – Backend: Config entries and UploadConfig extension (~45 min)

_Goal:_ Add `folder_upload_enabled` and `folder_upload_max_depth` to the backend config system and expose them via `UploadConfig` so the frontend can read them.

_Preconditions:_ None

_Steps:_
1. Create a migration adding two config entries:
   - `folder_upload_enabled`: boolean, default `1` (true), category Upload
   - `folder_upload_max_depth`: integer (string in configs table), default `0` (unlimited), category Upload
2. Add both fields to `UploadConfig.php` resource, reading from the configs table.
3. Regenerate TypeScript types (`php artisan typescript:transform`).
4. Add admin UI settings for both entries in the Upload settings section.
5. Run `php artisan test --filter=UploadConfig`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.

_Commands:_
- `php artisan migrate`
- `php artisan test --filter=UploadConfig`
- `php artisan typescript:transform`
- `make phpstan`
- `vendor/bin/php-cs-fixer fix`

_Exit:_ Both config entries in DB with defaults. `UploadConfig` response includes `folder_upload_enabled` and `folder_upload_max_depth`. TypeScript types updated.

---

### I0 – Extend `Uploadable` type with `album_id` (~15 min)

_Goal:_ Allow individual upload queue entries to target a specific album, overriding the route-level album context.

_Preconditions:_ None

_Steps:_
1. In `resources/js/composables/album/uploadEvents.ts`, add `album_id?: string` to the `Uploadable` type.
2. Run `npm run check` to confirm no downstream type errors.

_Commands:_
- `npm run check`

_Exit:_ `Uploadable` has optional `album_id`. No TypeScript errors.

---

### I1 – Update `UploadPanel.vue` to use per-file `album_id` (~20 min)

_Goal:_ When an `Uploadable` has an `album_id` override, use that instead of the route-level `albumId` when rendering `UploadingLine`.

_Preconditions:_ I0 complete

_Steps:_
1. In `UploadPanel.vue`, locate the `UploadingLine` rendering loop (line ~33–44).
2. Change `:album-id="albumId"` to `:album-id="uploadable.album_id ?? albumId"`.
3. Run `npm run check` and `npm run format`.

_Commands:_
- `npm run check`
- `npm run format`

_Exit:_ `UploadingLine` receives per-file album ID when present. TypeScript clean.

---

### I2 – Create `folderDrop.ts` composable (~120 min)

_Goal:_ New composable that encapsulates all folder-specific drop logic: detecting directory entries, existing album name resolution, recursive sub-folder processing, file enumeration, and album creation.

_Preconditions:_ I0 complete

_Steps:_
1. Create `resources/js/composables/album/folderDrop.ts`.
2. Implement `getEntry(item: DataTransferItem): FileSystemEntry | null` — calls `webkitGetAsEntry?.() ?? getAsEntry?.()`.
3. Implement `readDirectoryEntries(dirEntry: FileSystemDirectoryEntry): Promise<FileSystemEntry[]>` — loops `createReader().readEntries()` until empty batch (handles 100+ entries per NFR-044-02). Returns all `FileSystemEntry` items (both files and sub-directories).
4. Implement `resolveOrCreateAlbum(name: string, parent_id: string | null, existingAlbums: { id: string, title: string }[]): Promise<string>`:
   - Case-insensitive search of `existingAlbums` for `name`.
   - If found, return its `id` immediately (no API call).
   - If not found, call `AlbumService.createAlbum({ title: name, parent_id })` and return the new album ID.
5. Implement `processDirectory(dirEntry: FileSystemDirectoryEntry, parent_id: string | null, existingAlbums: { id: string, title: string }[], list_upload_files: Ref<Uploadable[]>, currentDepth: number, maxDepth: number): Promise<void>` (recursive):
   - If `maxDepth > 0 && currentDepth > maxDepth`: return immediately (depth exceeded).
   - Call `resolveOrCreateAlbum(dirEntry.name, parent_id, existingAlbums)` → `albumId`.
   - Call `readDirectoryEntries(dirEntry)` → `entries`.
   - For each `FileSystemFileEntry`: convert to `File` and push to `list_upload_files` with `{ status: "waiting", album_id: albumId }`.
   - For each `FileSystemDirectoryEntry`: call `processDirectory(subEntry, albumId, [], list_upload_files, currentDepth + 1, maxDepth)` recursively. (Pass empty `existingAlbums` for sub-levels not yet loaded in the store — new albums will be created, which is correct.)
   - On `resolveOrCreateAlbum` error: show error toast; return without processing files or sub-folders for this directory.
6. Implement `handleFolderDrop(items: DataTransferItemList, parent_id: string | null, existingAlbums: { id: string, title: string }[], list_upload_files: Ref<Uploadable[]>, maxDepth: number): Promise<boolean>`:
   - Iterate items; classify via `getEntry()`.
   - Flat file entries: push to queue without `album_id` override.
   - Directory entries: call `processDirectory()` for each, all in parallel via `Promise.allSettled`.
   - Return `true` if any items were queued.
7. Export `handleFolderDrop` and `getEntry`.

_Commands:_
- `npm run check`
- `npm run format`

_Exit:_ `folderDrop.ts` exists, exports `handleFolderDrop`. TypeScript clean. No circular dependencies.

---

### I3 – Modify `uploadEvents.ts` to use folder drop handler (~45 min)

_Goal:_ Branch the `dropUpload` function on whether dropped items contain directories, using the new `folderDrop.ts` composable, and respect the `folder_upload_enabled` and `folder_upload_max_depth` config values.

_Preconditions:_ I2 complete, I-1 complete (UploadConfig types available)

_Steps:_
1. In `uploadEvents.ts`, update the `useMouseEvents` signature to accept `parent_id: Ref<string | null>`, `existingAlbums: Ref<{ id: string, title: string }[]>`, and `setup: Ref<UploadConfig | undefined>` (to read `folder_upload_enabled` and `folder_upload_max_depth`).
2. Import `handleFolderDrop` and `getEntry` from `./folderDrop`.
3. Rewrite `dropUpload(e: DragEvent)`:
   - If `setup.value?.folder_upload_enabled !== true`: skip the folder-drop path entirely, fall through to flat-file path.
   - If `e.dataTransfer.items` is available and any item returns a `FileSystemDirectoryEntry` from `getEntry()`:
     - Call `handleFolderDrop(e.dataTransfer.items, parent_id.value, existingAlbums.value, list_upload_files, setup.value?.folder_upload_max_depth ?? 0)`.
     - After resolution: if return value is `true`, set `is_upload_visible.value = true`.
   - Else (no directory entries, or `items` API unavailable):
     - Existing flat-file logic using `e.dataTransfer.files` (unchanged — preserves NFR-044-01).
4. Run `npm run check`.

_Commands:_
- `npm run check`
- `npm run format`

_Exit:_ `dropUpload` branches correctly. Existing flat-file path unchanged. TypeScript clean.

---

### I4 – Update `Albums.vue` and `Album.vue` to pass `parent_id`, `existingAlbums`, and `setup` (~30 min)

_Goal:_ Supply the correct `parent_id`, loaded albums list, and UploadConfig (for feature flag + max depth) to `useMouseEvents`.

_Preconditions:_ I3 complete

_Steps:_
1. In `Albums.vue`:
   - `parent_id` = `ref(null)` (root albums page, no parent).
   - `existingAlbums` = computed list of albums from the albums store at the root level (titles + IDs).
   - Pass `parent_id`, `existingAlbums`, and the existing `setup` ref (UploadConfig) to `useMouseEvents`.
2. In `Album.vue`:
   - `parent_id` = `computed(() => route.params.albumId as string | null)`.
   - `existingAlbums` = computed list of child albums of the current album from the store.
   - Pass all three to `useMouseEvents`.
3. Check whether `UploadPanel` already loads and exposes `setup` in a way both views can share — if so, reuse the same ref; otherwise load it in the view and pass down.
4. Run `npm run check`.

_Commands:_
- `npm run check`
- `npm run format`

_Exit:_ Both views pass correct parent context and loaded albums. TypeScript clean. No regressions.

---

### I5 – Integration testing and documentation (~30 min)

_Goal:_ Manual end-to-end verification of all key scenarios.

_Preconditions:_ I0–I4 complete

_Steps:_
1. Manual test all scenarios from the Branch & Scenario Matrix (S-044-01 through S-044-11).
2. Verify no regressions: individual file drops on both Albums and Album pages still work.
3. Run full quality gate.

_Commands:_
- `npm run check`
- `npm run format`

_Exit:_ All scenarios pass. Quality gate clean.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-044-01 | I2, I3, I4 / T-044-05, T-044-07, T-044-08 | Root drop → album at root |
| S-044-02 | I2, I3, I4 / T-044-05, T-044-08 | Inside album drop → sub-album |
| S-044-03 | I2, I3 / T-044-06 | Multiple folders |
| S-044-04 | I2, I3 / T-044-07 | Mixed drop |
| S-044-05 | I3 / T-044-07 | Flat file drop unchanged |
| S-044-06 | I2 / T-044-04 | 100+ files — batch loop |
| S-044-07 | I2 / T-044-05 | Unsupported file types |
| S-044-08 | I3 / T-044-07 | Permission gate |
| S-044-09 | I2 / T-044-06 | Album creation failure |
| S-044-10 | I3 / T-044-07 | Browser fallback |
| S-044-11 | I2 / T-044-05 | Empty folder |
| S-044-12 | I2 / T-044-05 | Existing album same casing |
| S-044-13 | I2 / T-044-05 | Existing album different casing |
| S-044-14 | I2 / T-044-05 | No existing album match |
| S-044-15 | I2 / T-044-05b | Sub-folder → sub-album |
| S-044-16 | I2 / T-044-05b | Deep nesting (3+ levels) |
| S-044-17 | I2 / T-044-05 + T-044-05b | Sub-folder matches existing sub-album |
| S-044-18 | I2 / T-044-06 | Sub-folder album failure mid-recursion |

## Exit Criteria

- [ ] `folder_upload_enabled` and `folder_upload_max_depth` config entries in DB with correct defaults
- [ ] `UploadConfig` response includes both fields; TypeScript types regenerated
- [ ] Admin UI shows both settings in Upload section
- [ ] `Uploadable` type includes optional `album_id` field
- [ ] `UploadPanel.vue` passes `uploadable.album_id ?? albumId` to `UploadingLine`
- [ ] `folderDrop.ts` composable exists: `getEntry`, `readDirectoryEntries`, `resolveOrCreateAlbum`, `processDirectory` (with depth enforcement), `handleFolderDrop`
- [ ] `dropUpload` in `uploadEvents.ts` respects `folder_upload_enabled` flag and routes folder drops correctly
- [ ] Flat-file drop behavior unchanged (no regressions)
- [ ] `Albums.vue` and `Album.vue` pass correct `parent_id`, `existingAlbums`, and `setup` to `useMouseEvents`
- [ ] All scenarios from S-044-01 to S-044-24 verified manually
- [ ] `npm run check` passes with zero errors
- [ ] `npm run format` produces no changes

## Follow-ups / Backlog

- Phase 2: Recursive sub-folder traversal → nested album tree creation
- Phase 2: Show folder name as section header in UploadPanel during multi-folder uploads
- Phase 2: Progress indicator during album creation (before files are queued)

---

*Last updated: 2026-06-13*
