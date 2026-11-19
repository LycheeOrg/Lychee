# Open Questions – Feature 044

Open questions for [Feature 044](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-044-01~~ | 044 – Folder Drop | High | `Timeline.vue` also calls `useMouseEvents` — extending the signature breaks it | Resolved (B+A) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-02~~ | 044 – Folder Drop | High | `setup` (UploadConfig) ownership — architectural resolution required | Resolved (A – TogglablesStateStore) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-03~~ | 044 – Folder Drop | Medium | `UploadPanel.uploadCompleted()` clears route-level cache only | Resolved (B – clear all album_ids) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-04~~ | 044 – Folder Drop | Medium | `existingAlbums` scope — pinned albums excluded from name-match pool | Resolved (B – albums only, accept edge case) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-05~~ | 044 – Folder Drop | Medium | Child albums in `Album.vue` — wrong store property referenced in plan | Resolved (A – both views use albumsStore.albums) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-06~~ | 044 – Folder Drop | Low | Plan.md "Out of scope" contradicts spec on recursive sub-folder processing | Resolved (implement recursive sub-albums) | 2026-06-13 | 2026-06-13 |
| ~~Q-044-07~~ | 044 – Folder Drop | Low | `UploadPanel` internal drop zone bypasses `folderDrop.ts` | Resolved (A – out of scope, document boundary) | 2026-06-13 | 2026-06-13 |

## Question Details

### ❓ Q-044-01 · `Timeline.vue` also calls `useMouseEvents` — signature change will break it

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Default-value parameters

**Question**  
I3/T-044-07 extends `useMouseEvents` with three new parameters (`parent_id`, `existingAlbums`, `setup`). `Timeline.vue` is a third caller of this composable (line 402) not mentioned anywhere in the spec, plan, or tasks. If its call-site is not updated, `npm run check` fails with a TypeScript arity error, blocking the entire increment. Should the new parameters have default values so `Timeline.vue` requires no change, or should it be explicitly updated?

---

#### 🅰️ (**recommended**) Option A – Default-value parameters; `Timeline.vue` unchanged

- **Idea:** Declare the three new parameters with defaults in the `useMouseEvents` signature: `parent_id: Ref<string | null> = ref(null)`, `existingAlbums: Ref<...[]> = ref([])`, `setup: Ref<UploadConfig | undefined> = ref(undefined)`. The existing three-arg call in `Timeline.vue` continues to compile unmodified. Folder drops on the Timeline view behave identically to the Albums page (album created at root, since `parent_id` defaults to `null`), which is acceptable given that Timeline already supports flat-file uploads.
- **Spec impact:** Note in T-044-07 that default values are used; annotate `Timeline.vue` as an unaffected third caller.
- **Pros:**
  - ✅ Zero changes to `Timeline.vue` — not in scope, not touched.
  - ✅ TypeScript arity error eliminated without widening scope.
  - ✅ Folder drops on Timeline "just work" (root context, unlimited depth) with no extra code.
- **Cons:**
  - ❌ Default ref instances created per call may be mildly surprising to future readers expecting explicit values.

---

#### 🅱️ Option B – Update `Timeline.vue` explicitly

- **Idea:** Add `Timeline.vue` to T-044-08 scope. Pass `ref(null)`, `ref([])`, and the shared `setup` ref (once Q-044-02 is resolved) from `Timeline.vue`.
- **Spec impact:** T-044-08 gains a third file; `Timeline.vue` is added to the affected modules list in the spec Overview.
- **Pros:**
  - ✅ Explicit — every call site is self-documenting.
- **Cons:**
  - ❌ Expands scope of T-044-08 to a file not in scope.
  - ❌ Requires resolving Q-044-02 before Timeline.vue can be updated.

---

#### 🅲 Option C – Separate the folder-drop detection from `useMouseEvents` entirely

- **Idea:** Do not extend `useMouseEvents`. Instead, create a thin `useFolderDropEvents` composable that is only called from `Albums.vue` and `Album.vue`, leaving `useMouseEvents` signature unchanged.
- **Spec impact:** Affects I3 architecture; plan I3 and T-044-07 need restructuring. `Albums.vue` and `Album.vue` register two composables for drop events.
- **Pros:**
  - ✅ `useMouseEvents` stays backward-compatible.
- **Cons:**
  - ❌ Two overlapping drop-event registrations on the same `window` — risk of double-handling or event ordering bugs.
  - ❌ Larger refactor than planned.

---

**Next action**  
Decide between 🅰️ and 🅱️ before T-044-07 implementation begins. If 🅰️ is chosen, update T-044-07 notes to document the defaults and add a comment in `Timeline.vue`'s vicinity explaining why it omits the new params. If 🅱️, add `Timeline.vue` to T-044-08.

---

### ❓ Q-044-02 · `setup` (UploadConfig) is owned by `UploadPanel` and not available at drop time

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Lift UploadConfig into a shared Pinia store

**Question**  
`UploadPanel.vue` loads `UploadConfig` in its own `onMounted` and stores it in a local `ref`. `useMouseEvents` (called during `Albums.vue`/`Album.vue` script-setup, before UploadPanel mounts) needs `folder_upload_enabled` and `folder_upload_max_depth` from that config at drop time. How should `UploadConfig` be made available to `useMouseEvents` without a timing race or duplicate HTTP request?

---

#### 🅰️ (**recommended**) Option A – Lift UploadConfig into a shared Pinia store

- **Idea:** Create a small `useGallerySetupStore` (or extend `useTogglablesStateStore`) that owns a `setup: UploadConfig | undefined` ref and a `load()` action calling `UploadService.getSetUp()`. `Albums.vue` and `Album.vue` call `store.load()` in `onMounted`; `UploadPanel` reads from the same store instead of loading independently. `useMouseEvents` receives the store's reactive `setup` ref.
- **Spec impact:** Add the new store to the Affected Modules list; update T-044-07, T-044-08, and the UploadPanel I1 step to note the ownership change.
- **Pros:**
  - ✅ Single HTTP request — loaded once, shared everywhere.
  - ✅ Reactive: `useMouseEvents` reads the live value even if the load resolves after script-setup.
  - ✅ Consistent with Pinia patterns already used for all other shared state.
- **Cons:**
  - ❌ Requires a new store (small, but a change beyond the minimal footprint in NFR-044-04 scope).

---

#### 🅱️ Option B – Load `setup` inside `useMouseEvents` itself

- **Idea:** `useMouseEvents` calls `UploadService.getSetUp()` internally and stores the result in a composable-level `ref`. No new parameters needed; callers are unchanged.
- **Spec impact:** T-044-07 is self-contained; T-044-08 requires no changes for `setup` propagation.
- **Pros:**
  - ✅ Caller sites (`Albums.vue`, `Album.vue`, `Timeline.vue`) need no changes for `setup`.
  - ✅ Fully self-contained composable.
- **Cons:**
  - ❌ Second HTTP request — `UploadPanel` still loads its own copy; two concurrent calls to `GET /Gallery::getUploadLimits` on each page load.
  - ❌ Config value lives in the composable's closure, not in a globally inspectable store.

---

#### 🅲 Option C – Load `setup` in the view and pass as a parameter

- **Idea:** `Albums.vue` and `Album.vue` each call `UploadService.getSetUp()` in `onMounted` and pass the resulting ref to `useMouseEvents`. `UploadPanel` is refactored to accept `setup` as a prop instead of loading it.
- **Spec impact:** `UploadPanel`'s prop interface changes; T-044-07 and T-044-08 each gain a `setup` loading step; UploadPanel's I1 step needs updating.
- **Pros:**
  - ✅ Explicit data flow — easy to trace.
- **Cons:**
  - ❌ Second HTTP request (same as Option B).
  - ❌ Requires refactoring `UploadPanel`'s internal setup loading to a prop — larger change than planned.
  - ❌ `Albums.vue` and `Album.vue` each load the config independently; if they unmount and remount, the request fires again.

---

**Next action**  
Decide before T-044-07 and T-044-08 implementation begins. If 🅰️: create the store first, then reference it in T-044-07/T-044-08 and in the UploadPanel `onMounted`. If 🅱️: update T-044-07 to add an internal `getSetUp()` call and note the duplicate-request trade-off in a code comment.

---

### ❓ Q-044-03 · `UploadPanel.uploadCompleted()` clears only the route-level album cache

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – No change; add clarifying comment

**Question**  
`UploadPanel.uploadCompleted()` calls `AlbumService.clearCache(albumId.value ?? "unsorted")`, where `albumId` comes from the route. Files uploaded via folder drop carry per-file `album_id` overrides pointing to newly created albums. Those new albums' caches are never cleared. Is this a problem, or is `emits("refresh")` sufficient?

---

#### 🅰️ (**recommended**) Option A – No change; document why it is safe

- **Idea:** Newly created folder-drop albums have no pre-existing cache entry — there is nothing stale to clear. The `emits("refresh")` call that follows causes the parent view to reload the album list from the server, making the new albums visible. Add a short comment in `uploadCompleted` noting this intentional decision.
- **Spec impact:** Add a comment note to T-044-02 task.
- **Pros:**
  - ✅ Zero code change in `UploadPanel` — minimal footprint (NFR-044-04).
  - ✅ Correct in practice: new albums cannot have stale cache entries.
- **Cons:**
  - ❌ If an album pre-existed and was reused (name-match path), its cache is not cleared. Photos uploaded to the reused album won't appear until navigation forces a reload. However, the `emits("refresh")` on the Albums/Album page will also reload the page, which typically re-fetches the album content.

---

#### 🅱️ Option B – Clear cache for all unique `album_id`s in `list_upload_files`

- **Idea:** In `uploadCompleted`, after `AlbumService.clearCache(albumId.value ?? "unsorted")`, collect all distinct `album_id` values from `list_upload_files` and call `clearCache` for each.
- **Spec impact:** T-044-02 task gains a `clearCache` loop step.
- **Pros:**
  - ✅ Explicitly handles the reused-existing-album case (S-044-12, S-044-13) where the target album had prior cached data.
- **Cons:**
  - ❌ More code in `UploadPanel`; requires importing store-awareness into the component.
  - ❌ May be unnecessary if the parent `refresh` already invalidates album contents.

---

**Next action**  
Confirm which `AlbumService.clearCache` semantics apply to an album that was reused via name-match (not newly created). If the cache holds stale photo counts or thumbnails that the `refresh` emit would not update, choose 🅱️. Otherwise confirm 🅰️ and add the comment.

---

### ❓ Q-044-04 · `existingAlbums` scope — pinned albums excluded from name-match pool

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Include pinned albums; exclude shared albums

**Question**  
The plan proposes `existingAlbums = albumsStore.albums.map(...)` for `Albums.vue`. But `albumsStore.pinnedAlbums` is a separate array. When `deduplicate_pinned_albums` is off, a pinned album may not appear in `albumsStore.albums`. Dropping a folder whose name matches a pinned album would then create a duplicate. Should pinned albums be included in the lookup?

---

#### 🅰️ (**recommended**) Option A – Include pinned albums; exclude shared albums

- **Idea:** `existingAlbums = computed(() => [...albumsStore.albums, ...albumsStore.pinnedAlbums].filter((a, i, arr) => arr.findIndex(b => b.id === a.id) === i).map(a => ({ id: a.id, title: a.title })))`. Shared albums are excluded because the current user may not have upload rights to them — silently routing files there would produce 403 errors.
- **Spec impact:** Update T-044-08 `Albums.vue` computed-property definition. Note in FR-044-11 that the match pool includes pinned albums at the root level.
- **Pros:**
  - ✅ No duplicate album created when dropping over a pinned album's name.
  - ✅ Shared albums excluded — no risk of accidental upload to read-only shared albums.
- **Cons:**
  - ❌ Slightly more complex computed expression than `albumsStore.albums` alone.

---

#### 🅱️ Option B – Use only `albumsStore.albums`; accept the pinned edge case

- **Idea:** Keep the plan as written. When `deduplicate_pinned_albums` is enabled (the default), pinned albums are also in `albumsStore.albums` and will be matched correctly. Only the non-default config creates a gap.
- **Spec impact:** None — plan is already written this way.
- **Pros:**
  - ✅ Simplest implementation; matches the plan exactly.
- **Cons:**
  - ❌ With `deduplicate_pinned_albums = false`, dropping a folder whose name matches a pinned album creates a duplicate.

---

#### 🅲 Option C – Include pinned and shared albums in the lookup

- **Idea:** Also search `albumsStore.sharedAlbums` (flattened). If a match is found in a shared album, use it as the target regardless of upload rights.
- **Spec impact:** Requires a `can_upload` rights check before using a shared album match.
- **Pros:**
  - ✅ Maximum match coverage.
- **Cons:**
  - ❌ Shared albums may not accept uploads; routing files there silently produces per-file 403 errors in the UploadPanel.
  - ❌ Requires rights-checking logic in `resolveOrCreateAlbum` for the matched-album case.

---

**Next action**  
Confirm the default value of `deduplicate_pinned_albums` in production deployments. If the default causes pinned albums to also appear in `albumsStore.albums`, 🅱️ is acceptable for most users and 🅰️ adds safety for edge-case configs. Either way, update T-044-08 notes with the chosen expression.

---

### ❓ Q-044-05 · Child albums in `Album.vue` are stored in `albumsStore.albums`, not a separate property

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Both views use `albumsStore.albums`; update task notes

**Question**  
Plan T-044-08 says `Album.vue` should use "child albums of current album from store" without naming the property. In practice, `albumStore.loadAlbums()` writes child albums into `albumsStore.albums` — the same array used by the root Albums page. There is no separate `albumStore.childAlbums`. Should both views use `albumsStore.albums`, and should T-044-08 be corrected to reflect this?

---

#### 🅰️ (**recommended**) Option A – Both views use `albumsStore.albums`; update T-044-08 notes

- **Idea:** In both `Albums.vue` and `Album.vue`, `existingAlbums` is computed from `albumsStore.albums`. T-044-08 notes are updated to name the property explicitly, preventing implementers from searching for a non-existent `albumStore.childAlbums`.
- **Spec impact:** T-044-08 notes updated. No code changes beyond what T-044-08 already specifies.
- **Pros:**
  - ✅ Correct — `albumsStore.albums` is the canonical source at every level.
  - ✅ Zero extra code; just a documentation clarification.
- **Cons:**
  - ❌ None.

---

#### 🅱️ Option B – Add a `childAlbums` computed getter to `albumStore`

- **Idea:** Add `get childAlbums() { return useAlbumsStore().albums; }` to `albumStore` as a named alias, then use it in `Album.vue`.
- **Spec impact:** `albumStore` gains a new getter; T-044-08 references the getter.
- **Pros:**
  - ✅ Semantically clearer — readers of `Album.vue` see "childAlbums" not the generic "albums".
- **Cons:**
  - ❌ Unnecessary abstraction over a simple array reference — the alias adds cognitive overhead for no functional gain.
  - ❌ Changes a store outside the feature's stated backend footprint (NFR-044-04).

---

**Next action**  
This is a documentation-only fix. Update T-044-08 notes to read "use `albumsStore.albums` (from `useAlbumsStore()`) in both views" and close this question.

---

### ❓ Q-044-06 · Plan.md "Out of scope" section still lists "Recursive sub-folder processing"

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Delete the contradicting line

**Question**  
`plan.md` "Out of scope" explicitly lists "Recursive sub-folder processing," but FR-044-12, T-044-05b, and plan increment I2 fully implement it. This is a copy-paste artifact from a pre-rev-3 draft. Should the line be deleted or reworded?

---

#### 🅰️ (**recommended**) Option A – Delete "Recursive sub-folder processing" from Out of scope

- **Idea:** Remove that single bullet point. The spec and tasks are already consistent; the plan body contradicts only its own "Out of scope" section.
- **Spec impact:** One-line deletion in `plan.md`.
- **Pros:**
  - ✅ Eliminates the contradiction with no ambiguity.
  - ✅ Zero code impact.
- **Cons:**
  - ❌ None.

---

#### 🅱️ Option B – Replace the line with what is actually still deferred

- **Idea:** Replace "Recursive sub-folder processing" with "Section headers in UploadPanel per folder during multi-folder uploads" and "Progress indicator during album creation" — the two items that the plan Follow-ups section lists as Phase 2.
- **Spec impact:** Two lines updated in `plan.md` Out of scope; Follow-ups section cross-referenced.
- **Pros:**
  - ✅ More informative — explains what is still deferred rather than just removing the artifact.
- **Cons:**
  - ❌ The Phase 2 items are already in the Follow-ups section; duplicating them in Out of scope is minor redundancy.

---

**Next action**  
Apply 🅰️ immediately — this is a documentation-only fix with no code impact. Edit `plan.md` "Out of scope" and close.

---

### ❓ Q-044-07 · `UploadPanel`'s internal drag zone bypasses `folderDrop.ts`

**Status:** Open  
**Feature:** 044 – Folder Drag-and-Drop Album Creation  
**Preferred option:** 🅰️ (**recommended**) Option A – Declare out of scope; document the boundary

**Question**  
`UploadPanel.vue` has an internal "drag here" drop zone (lines 48–55) shown when 0 files are queued. Its `upload()` handler (lines 151–164) reads `event.target.files` only and never calls `handleFolderDrop`. Dropping a folder onto the open UploadPanel dialog — instead of the album grid behind it — silently does nothing (directories do not appear in `dataTransfer.files`). Is this scenario in scope?

---

#### 🅰️ (**recommended**) Option A – Declare out of scope; document the drop boundary in the spec

- **Idea:** The supported path is: drag a folder onto the album grid (window-level `drop` event registered in `Albums.vue`/`Album.vue`). The UploadPanel's internal zone is designed for file-picker or flat-file drops when the panel is already open. Add a sentence to the spec Overview and/or UI mock-up section noting that folder drops must target the album grid; the UploadPanel dialog itself is a flat-file-only zone.
- **Spec impact:** One-sentence annotation in spec.md Overview or UI section.
- **Pros:**
  - ✅ No code changes to `UploadPanel.vue`.
  - ✅ The primary user journey (drop on album grid → panel opens automatically) is unaffected.
  - ✅ Consistent with the spec's UI mock-ups, which all show drops onto the album grid before the panel opens.
- **Cons:**
  - ❌ A user who drops a folder onto an already-open UploadPanel gets no feedback — the drop silently fails. This edge case could be confusing.

---

#### 🅱️ Option B – Route UploadPanel's internal `upload()` through `handleFolderDrop`

- **Idea:** Refactor `UploadPanel.upload()` to call `handleFolderDrop(event.dataTransfer.items, ...)` when directory entries are detected, before falling back to the file-input path.
- **Spec impact:** `UploadPanel` must receive `parent_id` and `existingAlbums` (or access them from the shared store resolved in Q-044-02). Adds UploadPanel to the affected-modules list.
- **Pros:**
  - ✅ Consistent behavior: dropping a folder anywhere in the UI triggers album creation.
- **Cons:**
  - ❌ Requires `UploadPanel` to know about `parent_id` and `existingAlbums` — significant coupling increase.
  - ❌ The internal drop zone fires a DOM `change` event from a file input, not a `DragEvent`; `dataTransfer.items` may not be available in that path.
  - ❌ Expands scope beyond NFR-044-04 (minimal footprint).

---

#### 🅲 Option C – Disable the internal drag zone when folder uploads are enabled

- **Idea:** When `setup.folder_upload_enabled === true`, hide the internal "drag here" zone in UploadPanel (or show a tooltip: "Drop folders onto the album grid below").
- **Spec impact:** UploadPanel template conditionally renders the drag zone; requires `setup` to be accessible in UploadPanel (tied to Q-044-02 resolution).
- **Pros:**
  - ✅ Removes the ambiguous surface without adding folder logic to UploadPanel.
- **Cons:**
  - ❌ Degrades the flat-file drop experience inside an open UploadPanel when folder uploads are enabled — a regression for users who intentionally open UploadPanel first and then drag files.

---

**Next action**  
Confirm whether an already-open UploadPanel is a realistic drop target in the product's UX. If it is rare (users typically drop on the grid first, which auto-opens the panel), 🅰️ is the right call. If it is common, consider 🅱️ only after Q-044-02 is resolved (since UploadPanel would need the resolved `setup` source).
