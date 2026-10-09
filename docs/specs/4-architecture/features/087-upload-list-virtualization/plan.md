# Feature Plan 087 – Upload List Virtualization

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Testing  
_Last updated:_ 2026-10-09

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Queuing thousands of files keeps the v8 upload modal light: a bounded number of mounted rows (NFR-087-01), every upload completes and is counted (G2), and the list looks and scrolls as before (G3). Success: the Playwright run on a scratch instance passes S-087-01 … S-087-07, S-087-09, S-087-10; `npm run check`, `npm run format` and eslint are green.

## Scope Alignment
- **In scope:** `v8/components/modals/UploadPanel.vue` (virtualizer, running rows kept mounted, `scrollToIndex`, list clearing, modal lock), `v8/components/forms/upload/UploadingLine.vue` (`uid` prop, full bar for ended rows, kept miniature), `v8/utils/uploadThumbnail.ts` (miniature store), new `v8/utils/uploadList.ts` (pure helpers).
- **Out of scope:** v7, backend, upload entry points (`composables/album/folderDrop.ts`, `uploadEvents.ts`, `CameraCapture.vue`), the upload engine inside `UploadingLine.vue`.

## Dependencies & Interfaces
- `@tanstack/vue-virtual` (installed, `virtual-core` 3.17.11): `useVirtualizer`, `defaultRangeExtractor`, `Range`, `measureElement`, `scrollToIndex`, options `gap`, `paddingStart`, `paddingEnd`, `getItemKey`, `rangeExtractor`. No new dependency (NFR-087-02).
- `@vueuse/core` 15 `useIntersectionObserver` with `immediate`.
- `ModalsState.list_upload_files` (`Uploadable[]`, shared with v7, unchanged).
- Feature 077 (`uploadThumbnail.ts`), FR-077-03 and FR-077-07 amended by FR-087-06.

## Design
`v8/utils/uploadList.ts` (pure):
- `hasEnded(status)`: `done`, `warning` or `error` (FR-087-04, `UploadPanel` counters).
- `uploadingIndexes(files)`: ascending indices whose status is `uploading` (FR-087-02).
- `withPinnedIndexes(range, pinned)`: ascending union without duplicates of two ascending index lists (FR-087-02).

`v8/utils/uploadThumbnail.ts` (FR-087-06):
- module `Map<uid, objectUrl>` and a list generation counter.
- `keptUploadThumbnail(uid)`, `uploadListGeneration()`, `keepUploadThumbnail(uid, url, generation)` (stores and returns `true`, or revokes and returns `false` when the list was cleared since `generation`), `releaseUploadThumbnails()` (revokes every kept URL, clears, bumps the generation).

`UploadPanel.vue`:
- List box `h-72 overflow-y-auto pr-3` (existing classes, `py-4` moved to the virtualizer padding) holding a relative sizer of `getTotalSize()` px; rows `absolute top-0 inset-x-0`, `translateY(start)`, `data-index`, measured by a function ref calling `measureElement` (FR-087-01, NFR-087-03). Keyed by `item.key` (= `uid`).
- `useVirtualizer` options: `count`, `getScrollElement: () => listBox.value`, `estimateSize` of a single-line row, `overscan`, `gap: 4`, `paddingStart: 16`, `paddingEnd: 16`, `getItemKey: uid`, `rangeExtractor: (range) => withPinnedIndexes(defaultRangeExtractor(range), pinned)` with `pinned = uploadingIndexes(list)` read in the options `computed`, so a change of running rows yields a new extractor and the virtualizer recomputes its range (FR-087-02).
- `uploadNext()` scrolls with `virtualizer.scrollToIndex(lastIdx, { align: "center", behavior: "smooth" })` (FR-087-05).
- `clearList()` empties `list_upload_files` and calls `releaseUploadThumbnails()`; used by `close()`, `cancel()` and the album-change watcher (FR-087-06).
- `UModal` gets `:dismissible="!showCancel"` and `:close="!showCancel"` (FR-087-07).

`UploadingLine.vue`:
- New `uid` prop. `progressBar` is 100 when `hasEnded(status)` (FR-087-04).
- `thumbUrl` starts from `keptUploadThumbnail(uid)`; the observer is created with `immediate: thumbUrl === undefined`. A finished decode goes through `keepUploadThumbnail(uid, url, generation)` with the generation read when the decode was queued; the row shows it only while mounted. `onUnmounted` drops the queued job and no longer revokes (FR-087-06).

## Assumptions & Risks
- **Assumptions:** rows stay keyed by `uid` in one `v-for`, so Vue keeps a running row's instance when it leaves the default range (it stays in the extracted range). `list_upload_files` is only appended to or replaced by an empty array, so list indices stay stable for the life of a queue.
- **Risks / Mitigations:**
  - A running row unmounting would restart its file when mounted again → Playwright asserts that each file is sent once (chunk requests counted per file name) while the list is scrolled away from running rows.
  - Smooth `scrollToIndex` on measured rows can land off target → virtual-core 3.17 reconciles the scroll until the target is stable; checked in Playwright (S-087-05).
  - The modal lock hides the header close button only while files remain → Playwright checks the button and Escape before and after the queue ends.

## Implementation Drift Gate
After T-087-08, map each FR/NFR to code and to the Playwright checks in a drift report below, rerun `npm run check`, `npm run format` and eslint on the touched files, and record any divergence in [open-questions.md](open-questions.md).

### Drift Gate Report – 2026-10-09

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-087-01, NFR-087-01 | `UploadPanel.vue` (`useVirtualizer`, `measureRow`, sizer, `gap`/`paddingStart`/`paddingEnd`) | Run A: 14 rows mounted at the start, 17 at the top while uploads run, 21 when finished (bound 24); scroll height 72 028 px for 2000 files, equal to 16 + 2000 × 32 + 1999 × 4 + 16 (the 32 px estimate is the single-line row height). Run C: 16 px padding, 4 px gaps, 32 px rows. |
| FR-087-02, FR-087-03 | `uploadList.ts::uploadingIndexes()`, `withPinnedIndexes()`, `rangeExtractor` in `UploadPanel.vue` | Run A: running rows 300–302 mounted with the list scrolled to the top, same DOM nodes (expando kept), their completions counted (300 → 303) and rows 303–305 started; every one of 2000 files sent once. Negative control (default range only): rows unmounted, counter stuck at 300, no next start. |
| FR-087-04 | `UploadingLine.vue::progressBar` (`hasEnded`) | Run A: 422 and 409 rows show their message and `aria-valuenow` 100, also after unmount and remount. Run B: unsupported text file row. |
| FR-087-05 | `uploadNext()` → `scrollToIndex(…, { align: "center", behavior: "smooth" })` | Run A: rows 292–312 mounted around running rows 300–302; after the next starts, row 305 inside the list box. |
| FR-087-06 | `uploadThumbnail.ts` (`keepUploadThumbnail`, `keptUploadThumbnail`, `releaseUploadThumbnails`, generation), `UploadingLine.vue`, `clearList()` | Assertion script; Run A: same blob URL after unmount and remount, URL revoked (fetch fails) after Close. |
| FR-087-07 | `UModal :dismissible="!showCancel" :close="!showCancel"` | Run A: no header close button, Escape and click outside keep the modal open while files remain; button back and Escape closes after completion. |
| NFR-087-02 | — | `package.json` unchanged. |
| NFR-087-03 | rows `absolute inset-x-0` | T-087-09 (owner, RTL). |

Verification commands: `node --experimental-strip-types <scratchpad>/check-upload-list.ts`, `npm run format`, `npm run check`, `npx eslint` on the four touched files, `npx vite build`, `node <scratchpad>/upload-virtualization.mjs <scratchpad> ABC`.

Scratch instance: SQLite database, uploads and temporary upload folders in the session scratchpad, `CACHE_STORE=array` (the shared file cache holds files owned by the web server user), `upload_processing_limit` = 3, PHP built-in server started from `public/` with 4 workers and `variables_order=EGPCS`, Playwright with the system Chromium. Run A mocks `POST /api/v2/Photo` and holds responses so running rows can be checked with the list scrolled away; Run B goes through the real backend (40 distinct JPEGs created, one text file rejected, 3 uploads in flight at most); Run C appends files one by one through the store, as a folder drop does. 41/41 checks green, no page errors. The login answers 500 on this checkout because `storage/logs/login.log` is owned by the web server user; the session is authenticated before the log write fails.

No divergence from the spec. Low-level adjustment: the list box keeps its `pr-3` class (`py-4` moves into the virtualizer padding).

## Increment Map
1. **I1 – Pure helpers** (T-087-01, T-087-02)
   - _Goal:_ `uploadList.ts` and the miniature store in `uploadThumbnail.ts`.
   - _Preconditions:_ spec FR-087-02, FR-087-04, FR-087-06.
   - _Steps:_ write a throwaway Node assertion script in the session scratchpad (the repo has no frontend unit-test runner, as in Feature 077), run it and see it fail, then implement until it passes.
   - _Commands:_ `node --experimental-strip-types <scratchpad>/check-upload-list.ts`.
   - _Exit:_ script green.
2. **I2 – Virtualized list** (T-087-03, T-087-04, T-087-05)
   - _Goal:_ FR-087-01 … FR-087-07 in `UploadPanel.vue` and `UploadingLine.vue`.
   - _Preconditions:_ I1.
   - _Steps:_ `UploadingLine.vue` (`uid`, full bar for ended rows, kept miniature); `UploadPanel.vue` (virtualizer, pinned running rows, `scrollToIndex`, `clearList()`); modal lock.
   - _Commands:_ `npm run check`.
   - _Exit:_ type-check green.
3. **I3 – Quality gate** (T-087-06)
   - _Commands:_ `npm run format`, `npm run check`, `npx eslint` on the touched files.
4. **I4 – Scratch-instance verification** (T-087-07)
   - _Goal:_ S-087-01 … S-087-07, S-087-09, S-087-10 in a real browser.
   - _Steps:_ `vite build`; scratch instance (SQLite, storage and uploads in the session scratchpad, PHP built-in server); Playwright + Chromium: queue many small generated JPEGs through the file input, assert row counts, completion, counters, single upload per file, scroll position, miniatures reused, modal lock.
   - _Exit:_ every check passes; results recorded below.
5. **I5 – Documentation** (T-087-08)
   - _Steps:_ knowledge map, roadmap, drift report.
6. **I6 – Owner check** (T-087-09): S-087-08 (RTL) in a real browser.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-087-01 | I2, I4 / T-087-03, T-087-07 | Row count in the list box. |
| S-087-02 | I2, I4 / T-087-03, T-087-07 | Scroll away from running rows; counter and single send per file. |
| S-087-03 | I2, I4 / T-087-03, T-087-07 | `upload_processing_limit` > 1 on the scratch instance. |
| S-087-04 | I2, I4 / T-087-04, T-087-07 | Error row from an unsupported file type. |
| S-087-05 | I2, I4 / T-087-03, T-087-07 | Running row inside the list box after each start. |
| S-087-06 | I2, I4 / T-087-03, T-087-07 | Short queue, padding and gaps measured. |
| S-087-07 | I4 / T-087-07 | Entry points untouched; files appended one by one. |
| S-087-08 | I6 / T-087-09 | Owner, RTL locale. |
| S-087-09 | I1, I2, I4 / T-087-01, T-087-02, T-087-04, T-087-07 | Store asserted in I1; no second decode in the browser. |
| S-087-10 | I2, I4 / T-087-05, T-087-07 | Escape, click outside, header close button. |

## Analysis Gate
2026-10-09, agent review:
1. Specification completeness: objectives, FR and NFR populated; Q-087-01 and Q-087-02 folded into FR-087-06 and FR-087-07; ASCII mock-ups present. Pass.
2. Open questions: none open. No ADR: the decisions stay inside two v8 components and one helper module. Pass.
3. Plan alignment: plan links spec and tasks; dependencies and success criteria match the spec. Pass.
4. Tasks coverage: FR-087-01 … 07 map to T-087-03 … 05; helpers tested first (T-087-01 before T-087-02); browser checks enumerate each scenario. Pass.
5. Working agreements: no new dependency, v8 only, branching kept in three pure helpers. ADRs referencing Feature 077 or 087: none. Pass.
6. Tooling: commands listed per increment. Pass.

## Exit Criteria
- T-087-01 … T-087-08 `[x]`; T-087-09 left to the owner.
- `npm run format`, `npm run check`, eslint on touched files green.
- Playwright run green and recorded.
- Roadmap and knowledge map updated; Feature 077 spec references FR-087-06.

## Follow-ups / Backlog
- Cancel clears the list without aborting the requests of running rows; those files finish uploading in the background.
