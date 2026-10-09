# Open Questions – Feature 087

Open questions for [Feature 087](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-087-01~~ | 087 – Upload list virtualization | Medium | Do miniatures survive a row scrolling out and back? (A keep them for the life of the queue, revoked when the list is cleared · B rebuild on every mount · C bounded cache of the last N) | Resolved (Option A; owner, 2026-10-09; spec FR-087-06, Feature 077 FR-077-03, FR-077-07) | 2026-10-09 | 2026-10-09 |
| ~~Q-087-02~~ | 087 – Upload list virtualization | Medium | What happens when the modal is dismissed (Escape, click outside) while uploads run? (A not dismissible while uploads run · B keep the content mounted while hidden · C unchanged) | Resolved (Option A, header close button hidden too; owner, 2026-10-09; spec FR-087-07) | 2026-10-09 | 2026-10-09 |

## Question Details

### ~~Q-087-01~~ – Miniature retention across remounts

**Context:** Feature 077 keeps a built miniature until its row unmounts and revokes its object URL on unmount (FR-077-03, FR-077-07). Without virtualization, rows unmount only when the list is cleared. With virtualization, a row unmounts each time it leaves the overscan window. The list scrolls to each started row (FR-087-05), so every image in the queue gets a miniature as uploads progress. A miniature is an 80×80 WebP blob of a few KB; decoding a 20 MP JPEG takes roughly 0.1–0.3 s, two at a time.

- **Option A (chosen):** Keep each miniature for the life of the queue. `uploadThumbnail.ts` stores the object URL by `Uploadable.uid`; a row mounting again reuses it; every URL is revoked when the list is cleared (close, cancel, album change). FR-077-03 and FR-077-07 are amended accordingly.
  - ✅ No second decode and no placeholder flash when scrolling back.
  - ✅ Same memory as without virtualization: every row passed keeps its miniature.
  - ❌ Memory grows with the rows passed, a few MB per thousand images.
- **Option B:** Rebuild on every mount. Feature 077 unchanged.
  - ✅ No new code; miniatures held only by mounted rows.
  - ❌ Scrolling back decodes every visible image again; the icon shows until each pair of decodes ends.
- **Option C:** Bounded cache keeping the last N miniatures (for example 500).
  - ✅ Bounded memory, few second decodes.
  - ❌ Eviction code for a small gain over A.

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-087-06; Feature 077 FR-077-03 and FR-077-07 refer to it.

### ~~Q-087-02~~ – Dismissing the modal while uploads run

**Context:** `UploadPanel.vue`'s `UModal` is `dismissible` (Escape, click outside) while its Close button is disabled during uploads. The Reka dialog under `UModal` unmounts its content when hidden (`unmountOnHide` defaults to `true`), so dismissing the modal unmounts every row, running ones included. Vue drops the `upload:completed` events of unmounted rows: no further file starts and the counters stop. Reopening the modal mounts the `uploading` rows again, and each starts its file from the first chunk while the detached chunk loop may still be sending it. This is the failure FR-087-02 prevents for scrolling.

- **Option A (chosen):** The modal is not dismissible while uploads run (`:dismissible="!showCancel"`). Escape and click outside do nothing until every file has ended; Cancel stays available.
  - ✅ One binding, consistent with the disabled Close button.
  - ✅ No stalled queue and no file sent twice.
  - ❌ The modal covers the page until the queue ends or is cancelled.
- **Option B:** Keep the content mounted while hidden (`:unmount-on-hide="false"`). Running rows stay mounted with the modal closed; uploads continue in the background and reopening shows live progress.
  - ✅ Uploads continue while browsing the same album.
  - ❌ Changing album clears a hidden modal's list (`route.params.albumId` watcher) and leaving the page unmounts the panel: both detach running uploads again, so this needs its own design.
- **Option C:** Unchanged; handled in a later feature.
  - ✅ Scope limited to virtualization.
  - ❌ The stall and the duplicate upload remain.

**Resolution:** Option A. The header close button of `UModal` (`close`, a `DialogClose`) closes the dialog whatever `dismissible` says, so it is hidden while uploads run as well. Recorded in [spec.md](spec.md) FR-087-07.
