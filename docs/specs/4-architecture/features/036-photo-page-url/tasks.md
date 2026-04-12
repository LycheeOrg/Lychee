# Feature 036 Tasks – Photo Page URL

_Status: Complete_  
_Last updated: 2026-04-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-036-`) inside the same parentheses immediately after the task title.

## Checklist

- [x] T-036-01 – Add `photoPageMap` state and `recordPhotoPages` helper to `PhotosState` (FR-036-05).  
  _Intent:_ Extend the photos store with a map from photo ID to page number, cleared on `reset()` and populated by all photo-loading actions.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ `photoPageMap` is a flat `Record<string, number>`. It is not persisted between sessions — it is rebuilt each time photos are loaded.

- [x] T-036-02 – Add `page` parameter to `setPhotos` and `appendPhotos` (FR-036-05, S-036-04).  
  _Intent:_ Ensure the page number is recorded in `photoPageMap` whenever photos are set or appended, so `photoRoute()` can retrieve it.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ Default value of `page = 1` maintains backwards compatibility with all existing callers.

- [x] T-036-03 – Implement `prependPhotos` in `PhotosState` (FR-036-03, S-036-01).  
  _Intent:_ Add a method to insert a page of photos at the beginning of the existing collection, fixing boundary navigation links and handling timeline mode.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ For timeline mode, new groups are either merged into existing groups (same header) or `unshift`-ed to the front. Boundary links between the last prepended photo and the first existing photo are repaired.

- [x] T-036-04 – Add `photos_min_page` state and `prepend` mode to `loadPhotos` in `AlbumState` (FR-036-03).  
  _Intent:_ Track the earliest loaded page and route `loadPhotos(page, false, true)` calls to `prependPhotos`. Prepend calls skip the `photos_loading` flag so they don't block the load-more guard.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ `photos_min_page` is reset to 1 (the start page) in `reset()` and in the non-prepend, non-append branch of `loadPhotos`. The pagination state (`photos_current_page` etc.) is only updated for non-prepend calls.

- [x] T-036-05 – Add `startPage` parameter to `load()` in `AlbumState` (FR-036-02, S-036-01).  
  _Intent:_ When `startPage > 1`, load that page first (awaited) so the photo panel can render immediately; then fire-and-forget background prepend calls for pages `startPage-1` down to `1`.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ Prepend calls are `void this.loadPhotos(p, false, true)` — intentionally not added to the `loader` array so `albumStore.load()` resolves as soon as the target page and sub-albums are loaded, not when all previous pages are fetched.

- [x] T-036-06 – Update `photoRoute.ts` to include `?page=N` query param (FR-036-01, S-036-04, S-036-05, S-036-06).  
  _Intent:_ When generating a route for a photo, look up `photosStore.photoPageMap[photoId]` and attach `query: { page: String(page) }` to album and flow-album routes.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ Search, tag, and timeline routes are left unchanged (deferred — see plan Follow-ups).

- [x] T-036-07 – Read `?page` from route query in `Album.vue` and pass to `albumStore.load()` (FR-036-02, S-036-01, S-036-02).  
  _Intent:_ Add `getStartPage()` helper that safely parses `route.query.page` (returns 1 for absent/invalid). Pass the result to `albumStore.load(startPage)` in the `load()` function.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ The `refresh()` path intentionally does not pass a `startPage` (it always reloads from page 1 because refresh is used after mutations, not navigation).

- [x] T-036-08 – Add reactive `watch` on `photosStore.photos` in `Album.vue` to retry `photoStore.load()` (FR-036-02, FR-036-03, S-036-01).  
  _Intent:_ When background prepend calls add photos to the store, the photo panel must reactively try again to find the photo. Without this watch, a photo on page N is displayed only after `albumStore.load()` resolves — but if `?page` was slightly wrong (or the photo arrived via a prepend), the photo panel would remain hidden.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ The guard `!photoStore.isLoaded` prevents redundant calls once the photo is already displayed.

- [x] T-036-09 – Update `AlbumPanel.vue` to refresh URL after pagination events (FR-036-04, S-036-03).  
  _Intent:_ Replace the inline `albumStore.loadMorePhotos()` call with an `async loadMorePhotosAndUpdateUrl()` function that also calls `router.replace()`. Update `goToPhotosPage()` similarly.  
  _Verification commands:_  
  - `npm run build`  
  _Notes:_ `router.replace()` preserves the existing route params and merges into existing query params, so the `photoId` in the URL is not lost.

- [x] T-036-10 – Create spec, plan, and tasks files (documentation).  
  _Intent:_ Ensure the specification pipeline is complete for feature 036.  
  _Verification commands:_ _(none — documentation only)_  
  _Notes:_ Spec at `docs/specs/4-architecture/features/036-photo-page-url/spec.md`, plan at `plan.md`, tasks at `tasks.md` (this file).

## Notes / TODOs

- **Deferred:** A `Album::photoPage` backend endpoint would allow old/external URLs without `?page` to auto-resolve the correct page. This is a follow-up item in `plan.md`.
- **Timeline prepend ordering:** The timeline `prependPhotos` uses `unshift` for new groups not matching an existing header. The correct visual order of prepended timeline groups depends on the album sort order; for typical date-descending albums (newest first, page 1), prepending an earlier page (older photos, higher page N) will insert the group at the front of the timeline array. This is correct for date-descending sort but may need revisiting for ascending sort.
