# Feature 087 Tasks – Upload List Virtualization

_Status: Testing_  
_Last updated: 2026-10-09_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-087-01 – Assertion script for the pure helpers (FR-087-02, FR-087-04, FR-087-06, S-087-09).  
  _Intent:_ throwaway Node script in the session scratchpad covering `hasEnded`, `uploadingIndexes`, `withPinnedIndexes` (pinned before, inside, after the range, duplicates, empty lists) and the miniature store (keep and read, release revokes every URL, a keep after a release revokes and returns `false`). Run it before the helpers exist and see it fail.  
  _Verification commands:_  
  - `node --experimental-strip-types <scratchpad>/check-upload-list.ts`  
  _Notes:_ not committed (no frontend unit-test runner, same approach as Feature 077).

- [x] T-087-02 – Implement `v8/utils/uploadList.ts` and the miniature store in `v8/utils/uploadThumbnail.ts` (FR-087-02, FR-087-04, FR-087-06).  
  _Intent:_ make T-087-01 pass.  
  _Verification commands:_  
  - `node --experimental-strip-types <scratchpad>/check-upload-list.ts`

- [x] T-087-03 – Virtualize the list in `UploadPanel.vue` (FR-087-01, FR-087-02, FR-087-03, FR-087-05, NFR-087-01, NFR-087-03, S-087-01, S-087-02, S-087-03, S-087-05, S-087-06).  
  _Intent:_ `useVirtualizer` on the list box with measured rows, `gap`, padding, `uid` keys, range extractor pinning running rows, `scrollToIndex` in `uploadNext()`, `clearList()` releasing kept miniatures.  
  _Verification commands:_  
  - `npm run check`

- [x] T-087-04 – `UploadingLine.vue`: `uid` prop, full bar for ended rows, kept miniature (FR-087-04, FR-087-06, S-087-04, S-087-09).  
  _Verification commands:_  
  - `npm run check`

- [x] T-087-05 – Lock the modal while uploads run (FR-087-07, S-087-10).  
  _Intent:_ `:dismissible="!showCancel"` and `:close="!showCancel"` on `UModal`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-087-06 – Quality gate.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint resources/js/v8/components/modals/UploadPanel.vue resources/js/v8/components/forms/upload/UploadingLine.vue resources/js/v8/utils/uploadThumbnail.ts resources/js/v8/utils/uploadList.ts`

- [x] T-087-07 – Playwright on a scratch instance (S-087-01 … S-087-07, S-087-09, S-087-10).  
  _Intent:_ `vite build`, scratch instance with SQLite, storage and uploads in the session scratchpad; Chromium run asserting row counts, completion and counters, one send per file, scroll position, miniature reuse, modal lock. Results recorded in [plan.md](plan.md).  
  _Verification commands:_  
  - `npx vite build`  
  - `node <scratchpad>/upload-virtualization.mjs`

- [x] T-087-08 – Documentation (spec Documentation Deliverables).  
  _Intent:_ knowledge map, roadmap row, drift report in [plan.md](plan.md).

- [ ] T-087-09 – Owner check: RTL locale (S-087-08, NFR-087-03).

- [x] T-087-10 – One kept miniature per file when a row mounts again during its decode (FR-087-06, S-087-09).  
  _Intent:_ `keepUploadThumbnail()` keeps the first miniature of a uid and revokes later ones, returning the URL to show. Assertion script extended first (second keep of a uid revokes its URL and returns the first); Playwright Run D reproduces the leak on the build before the fix (large JPEGs, list scrolled out and back during decodes, every WebP object URL created must be revoked after Cancel), then passes after it.  
  _Verification commands:_  
  - `node --experimental-strip-types <scratchpad>/check-upload-list.ts`  
  - `npm run format`, `npm run check`, `npx eslint` on the touched files  
  - `npx vite build`, `node <scratchpad>/upload-virtualization.mjs <scratchpad> ACD`

## Notes / TODOs
- Scratch-instance login route allows 10 attempts per hour: reuse a saved Playwright `storageState`.
