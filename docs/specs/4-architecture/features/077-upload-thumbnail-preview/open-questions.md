# Open Questions – Feature 077

Open questions for [Feature 077](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-077-01~~ | 077 – Upload thumbnail preview | High | How is the miniature built from the local `File` (memory with hundreds of large photos)? | Resolved (Option A — downscaled bitmap, lazy, 2 at a time; owner, 2026-10-02; spec FR-077-01/03/07, NFR-077-01) | 2026-10-02 | 2026-10-02 |
| ~~Q-077-02~~ | 077 – Upload thumbnail preview | Medium | What do videos and non-decodable files (HEIC outside Safari, `raw_formats`, decode failures) show? | Resolved (Option A — Lucide icon by MIME family; owner, 2026-10-02; spec FR-077-02) | 2026-10-02 | 2026-10-02 |
| ~~Q-077-03~~ | 077 – Upload thumbnail preview | Medium | Miniature size and list layout. | Resolved (Option A — 40 px miniature, list `h-72`; owner, 2026-10-02; spec FR-077-04/05) | 2026-10-02 | 2026-10-02 |

## Question Details

### ~~Q-077-01~~ – Miniature generation technique

**Context:** The list can hold hundreds of files of 20–50 MP each. An `<img>` showing the original file keeps a full-size decoded bitmap (~100–200 MB for a 50 MP photo) while it is rendered. The list scrolls inside a fixed-height box (`h-48`).

- **Option A (chosen):** downscale once with `createImageBitmap(file, { resizeWidth, resizeHeight, resizeQuality })`, draw on a canvas, store a small blob object URL (a few KB) per row. Generation runs only when the row scrolls into view (`IntersectionObserver`) through a small shared queue (e.g. 2 at a time). Bitmap closed immediately, object URL revoked on unmount.
  - ✅ Memory per row is a few KB, whatever the source size.
  - ✅ No decode work for rows never scrolled to.
  - ❌ More code (queue + observer); each visible file is decoded once at full size.
- **Option B:** `URL.createObjectURL(file)` straight into `<img loading="lazy" decoding="async">`, revoked on unmount.
  - ✅ ~10 lines of code.
  - ❌ Browser keeps full-size decoded bitmaps for rendered rows; decoding happens on the main paint path, can stutter with large files.
- **Option C:** `FileReader.readAsDataURL` (suggested in the issue).
  - ✅ Simple.
  - ❌ Whole file copied into a base64 string (+33 %) held in JS memory per row, plus the decoded bitmap. Worst of the three.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-077-01, FR-077-03, FR-077-07 and NFR-077-01.

### ~~Q-077-02~~ – Videos and non-decodable files

**Context:** Accepted uploads include videos (`mp4`, `mov`, `webm`, `avi`, `wmv`, …), HEIC/HEIF (decoded only by Safari), and any extension listed in the `raw_formats` config (stored, not processed).

- **Option A (chosen):** a Lucide icon placeholder by MIME family in the same box: `lucide:image` for images that fail to decode, `lucide:video` for videos, `lucide:file` for anything else. No decoding attempted for videos.
  - ✅ Predictable, cheap, offline (icons are bundled).
  - ❌ Videos get no real preview.
- **Option B:** as A, plus a video poster frame: load the file in a hidden `<video>`, seek to ~0.1 s, draw the frame on a canvas. Falls back to the icon when the codec is unsupported (`avi`, `wmv`, often `mov`).
  - ✅ Real previews for `mp4`/`webm`.
  - ❌ More code, codec-dependent results, video decoding cost.
- **Option C:** nothing at all for such files; the box stays empty.
  - ✅ Minimal.
  - ❌ Ragged-looking list; no hint of the file type.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-077-02.

### ~~Q-077-03~~ – Miniature size and list layout

**Context:** A row today is one text line plus a progress bar (~28 px). The list box is `h-48` (192 px), showing ~6 rows.

- **Option A (chosen):** 40×40 px square miniature (`object-cover`) spanning the text line and the progress bar; list box raised from `h-48` to `h-72` so about the same number of rows stays visible.
  - ✅ Recognisable miniature, rows stay compact.
  - ❌ Modal gets taller (288 px list).
- **Option B:** 40×40 px miniature, list box kept at `h-48`.
  - ✅ Modal size unchanged.
  - ❌ Only ~4 rows visible.
- **Option C:** 64×64 px miniature, list box raised to `h-80`.
  - ✅ Photos clearly recognisable.
  - ❌ ~4–5 rows visible, more vertical scrolling for large batches.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-077-04 and FR-077-05.
