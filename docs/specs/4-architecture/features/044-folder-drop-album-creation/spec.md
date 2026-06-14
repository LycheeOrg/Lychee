# Feature 044 – Folder Drag-and-Drop Album Creation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-06-13 (rev 3 — symbolic links, feature toggle, max depth config) |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/044-folder-drop-album-creation/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/044-folder-drop-album-creation/tasks.md` |
| Roadmap entry | #044 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature enables users to drag a folder from their operating system desktop (or file manager) and drop it onto the Albums page (or inside an album) in Lychee. When a folder is dropped, Lychee maps the folder tree onto Lychee's album tree: the top-level dropped folder becomes an album (or targets an existing one with the same name), sub-folders become sub-albums recursively, and all supported image/video files at each level are uploaded to their corresponding album. If multiple folders are dropped simultaneously, each is processed in parallel.

**Existing album reuse:** Before creating an album for a dropped folder or sub-folder, Lychee checks whether an album with that name already exists as a direct child of the target parent (case-insensitive match). If one is found, it is used as the upload target — no new album is created. This makes the drop operation idempotent and allows users to resume or supplement existing albums by dropping folders again.

**Recursive sub-folders:** A sub-folder found inside a dropped folder triggers the same logic recursively: resolve or create a sub-album (with the parent album as context), then process its files and sub-folders. The recursion depth is bounded by the `folder_upload_max_depth` configuration (see FR-044-14).

**Symbolic links:** The browser's `FileSystemEntry` API follows symbolic links transparently — a symlink to a file is presented as a `FileSystemFileEntry`, a symlink to a directory as a `FileSystemDirectoryEntry`. No special handling is required; the configurable max depth provides an implicit guard against pathological cases such as circular symlinks.

**Configuration:** Two admin settings control the feature: `folder_upload_enabled` (master toggle) and `folder_upload_max_depth` (recursion limit). Both are exposed via the existing `GET /Gallery::getUploadLimits` endpoint (UploadConfig). This requires minimal backend additions.

The current drag-and-drop handler processes flat file lists only (via `dataTransfer.files`). This feature extends it to detect directory entries via the `DataTransferItem.webkitGetAsEntry()` Web API, distinguish folders from individual files, and orchestrate album resolution/creation before queuing files for upload.

**Affected modules:** Frontend — `composables/album/uploadEvents.ts`, `composables/album/folderDrop.ts` (new), `components/modals/UploadPanel.vue`. Backend (minimal) — one migration, `UploadConfig.php` resource extension, `UploadPhotoRequest.php` (none), no new controllers or routes.

## Goals

- Allow users to drag a folder from their desktop onto the Albums page and have an album resolved or created automatically.
- Name the album after the dropped folder's name; if an album with that name already exists at the target level, use it instead of creating a duplicate.
- Upload all supported media files from the dropped folder into the resolved/created album.
- Recursively process sub-folders: each sub-folder becomes a sub-album (resolved or created) with the same name-matching logic, up to the configured max depth.
- Allow admins to enable or disable the entire folder drag-and-drop feature via a config setting.
- Allow admins to configure the maximum recursion depth for sub-folder processing.
- Support dropping multiple folders at once (each processed independently in parallel).
- Show per-file upload progress in the existing UploadPanel modal.
- Support dropping folders inside an existing album (creates/targets sub-albums of that album).
- Fall back gracefully to existing flat-file upload behavior when individual files (not folders) are dropped, or when the feature is disabled.

## Non-Goals

- Automatic album creation from zipped folders or archives.
- Renaming the album during or after the drop (use the existing rename flow).
- Deduplication of photos that already exist in the target album.
- Uploading folder contents to the unsorted album (dropped folders always target a named album).
- Special handling for symbolic links (they are followed transparently by the browser API).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-044-01 | Detect folder drop on the Albums or Album page | When user drops an item whose `DataTransferItem.webkitGetAsEntry()` returns a `FileSystemDirectoryEntry`, the folder handling path is taken instead of the flat-file path. | Drop event is handled; entry type is `isDirectory === true`. | If `webkitGetAsEntry()` is unavailable (unsupported browser), fall back to flat-file upload of any files in `dataTransfer.files`. | No telemetry. | User requirement |
| FR-044-02 | Resolve or create album for dropped folder | Before creating an album, search the currently loaded child albums of the target parent for an album whose `title` matches the folder name (case-insensitive). If found, use that album's ID. If not found, call `POST /Album` with `title` = folder name, `parent_id` = current album ID (or null on root Albums page). | Existing album is reused without a new API call; new album is created only when no match exists. | If album creation fails (network error, auth error), show error toast and do not start upload for that folder. | No telemetry. | User requirement |
| FR-044-03 | Read all entries (files and sub-folders) from dropped folder | After resolving/creating an album, enumerate the folder's direct contents using `FileSystemDirectoryEntry.createReader().readEntries()` in a batch loop. Collect file entries for upload and directory entries for recursive processing. | Files appear in the UploadPanel queue with the correct album's ID; sub-folders are processed recursively per FR-044-12. | Files with unsupported extensions are silently skipped or result in an upload-level error consistent with existing behavior. | No telemetry. | User requirement |
| FR-044-04 | Queue files for upload to the new album | Each accepted file from the folder is added to `list_upload_files` with `status: "waiting"` and an `album_id` override set to the newly created album's ID. The UploadPanel uses this per-file `album_id` in preference to the route-level album ID. | `UploadingLine` sends `album_id` from the `Uploadable` entry, photos land in the correct album. | N/A | No telemetry. | User requirement |
| FR-044-05 | Support multiple folder drops simultaneously | Dropping two or more folders at once creates two or more albums. Album creations are triggered in parallel (Promise.all). File reading and queuing begins after all album IDs are resolved. | Two albums appear with correct names; all files routed to correct albums. | If one album creation fails, the other(s) proceed normally. A toast error is shown for the failed folder. | No telemetry. | User requirement |
| FR-044-06 | Mixed drop: folders and individual files | When a drop contains both folders and individual flat files, folders trigger the album-creation path and individual files are added to the existing upload queue (targeting the current route's album or unsorted). | Both paths execute. Individual files appear in the queue without an `album_id` override; folder files appear with overrides. | N/A | No telemetry. | User requirement |
| FR-044-07 | Respect the `can_upload` permission gate | The folder drop handler must check `can_upload` exactly like the existing flat-file handler. Unauthenticated or read-only users see no behavior change. | Drop is silently ignored when `can_upload` is false. | N/A | No telemetry. | Security requirement |
| FR-044-08 | Show UploadPanel immediately on folder drop | After album creation succeeds, set `is_upload_visible = true` to open the upload modal and trigger the upload queue. | UploadPanel opens showing the queued files and starts uploading automatically. | N/A | No telemetry. | User requirement |
| FR-044-09 | Refresh album list after uploads complete | After all uploads for a folder drop are complete, emit `refresh` so the parent view reloads the album list and the new album's thumbnail appears. | Album appears in the gallery grid with a thumbnail. | N/A | No telemetry. | User requirement |
| FR-044-10 | Fallback for browsers without FileSystem API | If `webkitGetAsEntry()` is not available on any `DataTransferItem`, treat the drop as a flat-file drop using `dataTransfer.files`, preserving existing behavior. | Existing upload behavior unchanged on unsupported browsers. | N/A | No telemetry. | Compatibility requirement |
| FR-044-11 | Existing album name match — reuse instead of creating | Before making a `POST /Album` call for any folder (top-level or sub-folder), perform a case-insensitive search of the already-loaded albums at that level. The search scope is the children of the target parent that are currently in the Pinia albums store. If a match is found, that album's ID is used as the target; `POST /Album` is not called. | Dropping a folder whose name matches an existing album routes files to the existing album. No duplicate album is created. | If the store does not have the child albums loaded (e.g., the current view has not loaded the sub-albums of the target parent), fall back to creating a new album. Log a console warning. | No telemetry. | User requirement |
| FR-044-12 | Recursive sub-folder → sub-album processing | When enumerating a folder's contents (FR-044-03), any `FileSystemDirectoryEntry` encountered is processed recursively: resolve or create a sub-album (FR-044-11 / FR-044-02) with the current album as parent, then process the sub-folder's files and sub-sub-folders using the same algorithm. Recursion depth is bounded by `folder_upload_max_depth` (FR-044-14). | A folder tree `2026/June/Paris/` dropped on the root Albums page produces albums `2026 → June → Paris`, each containing its own photos. | If a sub-folder's album resolution/creation fails, skip that sub-folder's files and report the error; sibling sub-folders and parent files continue normally. | No telemetry. | User requirement |
| FR-044-13 | Admin feature toggle `folder_upload_enabled` | A boolean config entry (default: `true`). When set to `false`, folder drag-and-drop is disabled: the drop handler ignores all `FileSystemDirectoryEntry` items and falls through to the existing flat-file path. The `UploadConfig` resource exposes a `folder_upload_enabled` boolean field so the frontend can read it via `GET /Gallery::getUploadLimits`. | Feature is enabled by default; admin can disable it. When disabled, dropping a folder behaves exactly like dropping its constituent files (existing flat-file path). | N/A | No telemetry. | User requirement |
| FR-044-14 | Admin configurable `folder_upload_max_depth` | An integer config entry (default: `0`, meaning unlimited). When set to a positive integer N, sub-folder processing stops at depth N — that is, the dropped folder itself is depth 1, its direct sub-folders are depth 2, and so on. Sub-folders encountered at depth > N are ignored (their files are not uploaded and no album is created for them). The `UploadConfig` resource exposes `folder_upload_max_depth: number` so the frontend can enforce the limit. | With max_depth = 1, only the dropped folder creates an album; sub-folders are ignored. With max_depth = 2, one level of sub-folders is processed. With max_depth = 0 (default), no limit. | N/A | No telemetry. | User requirement |
| FR-044-15 | Symbolic links followed transparently | The browser's `FileSystemEntry` API resolves symbolic links before returning entries: a symlink to a file appears as `FileSystemFileEntry`, a symlink to a directory appears as `FileSystemDirectoryEntry`. The implementation requires no special symlink detection or handling — standard file/directory processing applies. The `folder_upload_max_depth` guard (FR-044-14) implicitly limits runaway recursion for circular symlinks, which are extremely rare in drag-drop contexts. | Files reached via symlinks are uploaded normally. Directories reached via symlinks are processed as ordinary directories (subject to depth limit). | N/A | No telemetry. | Compatibility requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-044-01 | No regressions in existing flat-file drag-and-drop | Correctness | Existing file drops continue to work; no change in behavior when only files (not folders) are dropped. | `uploadEvents.ts` refactor | Core stability |
| NFR-044-02 | `readEntries()` batch handling | Correctness | Browser `readEntries()` returns at most 100 entries per call. The implementation must loop until the reader returns an empty batch, so folders with more than 100 files are fully processed. | `FileSystemDirectoryReader` API | Browser API constraint |
| NFR-044-03 | Frontend follows Vue3/TypeScript conventions | Maintainability | Template-first component structure, Composition API, regular function declarations, `.then()` instead of async/await, axios calls in services directory. | Prettier, `npm run check` | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-044-04 | Minimal backend footprint | Scope | Backend changes are limited to: one migration adding two config entries, and extending `UploadConfig.php` to expose them. No new controllers, routes, or jobs. | Existing config/resource pattern | Scope constraint |
| NFR-044-05 | Album creation errors are non-blocking | UX | A failure to create one album does not block other queued folders or file uploads. Error shown via toast. | `AlbumService.createAlbum` | Resilience |
| NFR-044-06 | Max depth enforced in frontend only | Scope | `folder_upload_max_depth` is enforced by the frontend when traversing the directory tree — no backend validation needed since the upload endpoint is not depth-aware. | `processDirectory` depth parameter | Scope constraint |

## UI / Interaction Mock-ups

### 1. User drops a single folder — album creation in progress

```
┌─────────────────────────────────────────────────────────┐
│  My Albums                                              │
│                                                         │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────────────────┐   │
│  │      │  │      │  │      │  │ Creating album…  │   │
│  │ Trip │  │ Dogs │  │ Work │  │  "Vacation 2026" │   │
│  │      │  │      │  │      │  │                  │   │
│  └──────┘  └──────┘  └──────┘  └──────────────────┘   │
│                                                         │
│         ┌───────────────────────────────────────────┐   │
│         │  UploadPanel — 0 / 47 uploaded            │   │
│         │  IMG_001.jpg  ████░░░░  35%               │   │
│         │  IMG_002.jpg  Waiting                     │   │
│         │  …                                        │   │
│         └───────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### 2. User drops two folders simultaneously

```
┌─────────────────────────────────────────────────────────┐
│  My Albums                                              │
│                                                         │
│  ┌──────┐  ┌──────┐  ┌────────────────┐  ┌──────────┐ │
│  │ Trip │  │ Dogs │  │ Vacation 2026  │  │ Wedding  │ │
│  │      │  │      │  │   (47 files)   │  │  (82     │ │
│  │      │  │      │  │                │  │  files)  │ │
│  └──────┘  └──────┘  └────────────────┘  └──────────┘ │
│                                                         │
│  ┌──────────────────────────────────────────────────┐   │
│  │  UploadPanel — 3 / 129 uploaded                  │   │
│  │  IMG_001.jpg   [Vacation 2026]  Done ✓           │   │
│  │  IMG_002.jpg   [Vacation 2026]  Done ✓           │   │
│  │  IMG_003.jpg   [Vacation 2026]  ████░░  48%      │   │
│  │  WED_001.jpg   [Wedding]        Waiting           │   │
│  │  …                                               │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### 3. Mixed drop — one folder + individual files

```
Drop event contains:
  • Folder: "Paris Trip"           → creates album "Paris Trip"
  • File:   selfie.jpg             → uploads to current album (or unsorted)
  • File:   video.mp4              → uploads to current album (or unsorted)
```

### 4. Drop inside an existing album — creates sub-album

```
User is inside album "2026"
User drops folder "June"
  → creates sub-album "June" with parent = "2026"
  → uploads all images to "June"
```

### 5. Drop a folder that already exists — reuses existing album

```
Albums page already contains: "Vacation 2026"
User drops folder named "vacation 2026" (different casing)
  → case-insensitive match found → uses existing "Vacation 2026" album ID
  → no new album created
  → new images from the folder uploaded to the existing album
```

### 6. Drop a folder with sub-folders — recursive album tree

```
User drops folder structure:
  2026/
    june.jpg
    July/
      beach.jpg
      Paris/
        eiffel.jpg

Result:
  Album "2026" (created or reused at root)
    ├── june.jpg         → uploaded to "2026"
    ├── Sub-album "July" (created or reused under "2026")
    │     ├── beach.jpg  → uploaded to "July"
    │     └── Sub-album "Paris" (created or reused under "July")
    │           └── eiffel.jpg → uploaded to "Paris"
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-044-01 | Drop one folder on root Albums page → album created at root, all files uploaded to it |
| S-044-02 | Drop one folder inside an album → sub-album created, files uploaded to sub-album |
| S-044-03 | Drop two folders simultaneously → two albums created in parallel, files routed correctly |
| S-044-04 | Drop a folder + individual files → album created for folder; files go to current context |
| S-044-05 | Drop only individual files (no folders) → existing flat-file upload behavior, no album creation |
| S-044-06 | Drop folder with 150 files → `readEntries()` loop correctly reads all 150 (beyond 100-entry batch limit) |
| S-044-07 | Drop folder with mixed files (images + non-supported) → only supported files queued; unsupported silently skipped or handled by existing upload error path |
| S-044-08 | Drop folder as unauthenticated user → drop ignored (can_upload guard) |
| S-044-09 | Album creation fails (network error) → toast error shown, no files queued for that folder |
| S-044-10 | Browser without `webkitGetAsEntry()` → falls back to flat-file upload from `dataTransfer.files` |
| S-044-11 | Drop folder with 0 supported files → album still created/resolved (empty), upload panel not opened (or opened with 0 files) |
| S-044-12 | Drop folder whose name matches existing album (same casing) → existing album used, no new album created, files routed to it |
| S-044-13 | Drop folder whose name matches existing album (different casing, e.g. "vacation 2026" vs "Vacation 2026") → case-insensitive match found, existing album used |
| S-044-14 | Drop folder whose name does NOT match any existing album → new album created normally |
| S-044-15 | Drop folder with sub-folder → top-level album created/resolved, sub-album created/resolved under it, files in each level routed correctly |
| S-044-16 | Drop folder with deeply nested sub-folders (3+ levels) → full album tree created/resolved recursively |
| S-044-17 | Drop folder with sub-folder whose name matches an existing sub-album → existing sub-album reused, no duplicate |
| S-044-18 | Sub-folder album resolution/creation fails mid-recursion → error toast for that sub-folder; parent album and sibling sub-folders continue |
| S-044-19 | `folder_upload_enabled = false` → folder drop falls through to existing flat-file behavior, no album created |
| S-044-20 | `folder_upload_max_depth = 1` → only top folder creates album; sub-folders ignored |
| S-044-21 | `folder_upload_max_depth = 2` → top folder + one sub-level; deeper sub-folders ignored |
| S-044-22 | `folder_upload_max_depth = 0` → unlimited recursion (default) |
| S-044-23 | Drop folder with symbolic link to a file → file uploaded normally |
| S-044-24 | Drop folder with symbolic link to a sub-directory → sub-directory processed as a regular directory entry |

## Test Strategy

- **UI (JS):** Unit tests for the new `folderDrop.ts` composable covering: directory entry detection, recursive batch reading, album creation orchestration, existing album name matching (case-insensitive), recursive sub-folder processing, and mixed-drop behavior. Test the 100+ file batch edge case with a mocked `FileSystemDirectoryReader`.
- **Integration (manual):** Drag real folder from desktop, verify album creation + upload in browser.
- No backend tests needed (no backend changes).

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-044-01 | `Uploadable` extended with optional `album_id?: string` override field | `composables/album/uploadEvents.ts` |
| DO-044-02 | `ResolvedAlbum` type: `{ albumId: string; dirEntry: FileSystemDirectoryEntry }` — result of resolving/creating an album for a directory entry | `composables/album/folderDrop.ts` (new) |
| DO-044-03 | Config: `folder_upload_enabled` (boolean, default: true) — admin master toggle for folder drag-and-drop | Migration, `UploadConfig.php` |
| DO-044-04 | Config: `folder_upload_max_depth` (integer, default: 0 = unlimited) — maximum sub-folder depth to process | Migration, `UploadConfig.php` |
| DO-044-05 | `UploadConfig` resource extended with `folder_upload_enabled: boolean` and `folder_upload_max_depth: number` fields | `UploadConfig.php`, TypeScript types |

### API Routes / Services

No new API routes. Existing endpoints used:

| ID | Transport | Endpoint | Usage |
|----|-----------|----------|-------|
| API-044-01 | POST /Album | `AlbumService.createAlbum({ title, parent_id })` | Creates the album named after the dropped folder |
| API-044-02 | POST /Photo | `UploadService.upload(...)` | Uploads each file (unchanged) |
| API-044-03 | GET /Gallery::getUploadLimits | Existing `UploadConfig` resource | Extended to include `folder_upload_enabled` and `folder_upload_max_depth` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-044-01 | Folder drop detected | `dragover` / `drop` event fires; folder entries found via `webkitGetAsEntry()` |
| UI-044-02 | Album creation pending | Request in flight; no files queued yet |
| UI-044-03 | Files queued | Album created; `list_upload_files` populated with `album_id` overrides |
| UI-044-04 | Upload panel open | `is_upload_visible = true`; panel shows queue and starts uploading |
| UI-044-05 | Upload complete | Panel shows 100% / "Completed"; album refresh triggered |
| UI-044-06 | Album creation error | Toast shown; no files queued for failed folder |

## Telemetry & Observability

No telemetry events required. Standard network request failures are surfaced via the existing toast/error system.

## Documentation Deliverables

- Update upload documentation to describe folder drag-and-drop behavior.
- Note browser compatibility (Chrome, Firefox, Edge supported; Safari may have partial support).

## Fixtures & Sample Data

No additional fixtures required. Existing upload test setup applies.

## Spec DSL

```yaml
config:
  - id: CFG-044-01
    key: folder_upload_enabled
    type: boolean
    default: true
    category: Upload
    description: "Enable folder drag-and-drop album creation"
  - id: CFG-044-02
    key: folder_upload_max_depth
    type: integer
    default: 0
    category: Upload
    description: "Max sub-folder recursion depth (0 = unlimited)"

domain_objects:
  - id: DO-044-01
    name: Uploadable
    fields:
      - name: album_id
        type: string
        constraints: "optional, overrides route-level album ID when set"
  - id: DO-044-02
    name: ResolvedAlbum
    fields:
      - name: albumId
        type: string
      - name: dirEntry
        type: FileSystemDirectoryEntry
  - id: DO-044-05
    name: UploadConfig
    fields:
      - name: folder_upload_enabled
        type: boolean
        constraints: "from CFG-044-01"
      - name: folder_upload_max_depth
        type: number
        constraints: "from CFG-044-02; 0 = no limit"

routes:
  - id: API-044-01
    method: POST
    path: /Album
    notes: "Existing endpoint, no changes"
  - id: API-044-02
    method: POST
    path: /Photo
    notes: "Existing endpoint, no changes"
  - id: API-044-03
    method: GET
    path: /Gallery::getUploadLimits
    response_changes:
      - field: folder_upload_enabled
        type: boolean
      - field: folder_upload_max_depth
        type: number

ui_states:
  - id: UI-044-01
    description: Folder drop detected on Albums or Album page (feature enabled)
  - id: UI-044-02
    description: Album creation in progress (silent — no spinner, files not yet queued)
  - id: UI-044-03
    description: Files queued in UploadPanel with album_id overrides
  - id: UI-044-04
    description: UploadPanel open and uploading
  - id: UI-044-05
    description: Upload complete, album list refreshed
  - id: UI-044-06
    description: Album creation error, toast displayed
  - id: UI-044-07
    description: Feature disabled (folder_upload_enabled = false) — folder drop falls through to flat-file path
  - id: UI-044-08
    description: Depth limit exceeded — sub-folder silently skipped, no album created for it
```

## Appendix

### Browser Compatibility

The `DataTransferItem.webkitGetAsEntry()` method (standardized as `getAsEntry()` in the File and Directory Entries API) is supported in:
- Chrome 13+ ✓
- Firefox 50+ ✓
- Edge 14+ ✓
- Safari 11.1+ (partial — `webkitGetAsEntry()` works; `FileSystemDirectoryReader` works)

The implementation should call `item.webkitGetAsEntry?.() ?? item.getAsEntry?.()` for forward compatibility.

### Current Drag-Drop Flow (Flat Files)

1. User drops files onto Albums or Album page
2. `dropUpload(e)` reads `e.dataTransfer.files` (flat `FileList`)
3. Each file pushed to `list_upload_files` with `status: "waiting"` (no `album_id`)
4. `is_upload_visible = true` opens UploadPanel
5. UploadPanel uses `route.params.albumId` as the target for all files

### Proposed Folder Drop Flow

1. Frontend reads `setup.folder_upload_enabled` from `UploadConfig`; if false, treat the drop as flat files (existing path).
2. User drops folder(s) onto Albums or Album page.
3. `dropUpload(e)` reads `e.dataTransfer.items` and calls `webkitGetAsEntry()` on each.
4. Items with `isDirectory === true` are collected; items with `isFile === true` follow existing flat path.
5. For each top-level directory, call `processDirectory(dirEntry, parent_id, existingAlbums, currentDepth=1)` (new recursive function):
   a. If `setup.folder_upload_max_depth > 0 && currentDepth > setup.folder_upload_max_depth`: return immediately (depth exceeded).
   b. Check loaded albums at `parent_id` level for a case-insensitive name match → if found, use existing album ID.
   c. If no match: call `AlbumService.createAlbum({ title: dirEntry.name, parent_id })` → get `albumId`.
   d. Enumerate directory contents via `createReader().readEntries()` loop (batch until empty). Symbolic link entries are handled transparently by the browser — no special cases.
   e. Push each file entry (converted to `File`) into `list_upload_files` with `{ status: "waiting", album_id: albumId }`.
   f. For each sub-directory entry found: call `processDirectory(subDirEntry, albumId, [], currentDepth+1)` recursively.
6. Flat file items (non-directory) are pushed to `list_upload_files` without an `album_id` override.
7. `is_upload_visible = true` once any items are queued.
8. UploadPanel passes `uploadable.album_id ?? albumId` (from route) to each `UploadingLine`.

### Album Name Matching

The match is performed against the `title` field of albums already loaded into the Pinia store at the relevant parent level. The comparison is case-insensitive (`dirEntry.name.toLowerCase() === album.title.toLowerCase()`). If multiple albums share the same case-insensitive name (degenerate case), the first match wins. The Pinia store is not re-fetched before the match — this uses whatever is currently in memory. If the view has not loaded child albums (e.g., the store only has top-level albums but not sub-albums of a newly created album), no match will be found and a new album will be created — which is correct behaviour for newly created tree levels.

### Related Components

- `resources/js/composables/album/uploadEvents.ts` – existing drag-drop composable
- `resources/js/composables/album/folderDrop.ts` – new composable (folder detection, album creation, file reading)
- `resources/js/components/modals/UploadPanel.vue` – upload modal (pass per-file album_id)
- `resources/js/components/forms/upload/UploadingLine.vue` – individual file upload (already receives album_id)
- `resources/js/services/album-service.ts` – `createAlbum()` used for album creation
- `resources/js/views/gallery-panels/Albums.vue` – registers drop handlers
- `resources/js/views/gallery-panels/Album.vue` – registers drop handlers (sub-album path)

---

*Last updated: 2026-06-13 (rev 3)*
