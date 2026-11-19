# Feature 077 – Plan

| Field | Value |
|-------|-------|
| Linked spec | [spec.md](spec.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Last updated | 2026-10-02 |

## Scope
- **In scope:** v8 `UploadingLine.vue` (miniature, placeholder, lazy generation, cleanup), v8 `UploadPanel.vue` (list height), a new helper module `resources/js/v8/utils/uploadThumbnail.ts`.
- **Out of scope:** v7, backend, shared upload entry points (`composables/album/folderDrop.ts`, `uploadEvents.ts`, `CameraCapture.vue`): they already push `File` objects into `list_upload_files`, so they need no change.

## Dependencies
No new library. Uses `useIntersectionObserver` from `@vueuse/core` (already a dependency), `<UIcon>` from `@nuxt/ui` with the bundled Lucide collection, and the browser APIs `createImageBitmap`, `HTMLCanvasElement.toBlob` and `URL.createObjectURL`.

## Design
`resources/js/v8/utils/uploadThumbnail.ts`, small pure helpers composed by the component:
- `uploadPlaceholderIcon(mime: string): "lucide:image" | "lucide:video" | "lucide:file"` (FR-077-02).
- `shouldDecode(mime: string): boolean`: `true` only for `image/*` (FR-077-02).
- `squareCropRect(width, height): { sx, sy, size }`: centre square of the source (FR-077-01).
- `enqueueThumbnail(job)`: shared queue capped at 2 concurrent jobs (FR-077-03, NFR-077-01). It returns a cancel function that drops a job that has not started yet (FR-077-07).
- `createUploadThumbnail(file): Promise<string>`: `createImageBitmap(file, { imageOrientation: "from-image" })` → `squareCropRect` → draw on an 80×80 canvas → `bitmap.close()` → `toBlob("image/webp")` → `URL.createObjectURL`. Rejects on decode failure (FR-077-01).

`UploadingLine.vue`:
- Root becomes `flex gap-x-3 items-center`: a `size-10 shrink-0 rounded overflow-hidden bg-elevated` box, then the existing column (name/status line, cancel span, progress bar) in a `min-w-0 flex-1` wrapper. The flex row mirrors itself in RTL (NFR-077-03).
- `thumbUrl` ref and `icon` ref (starts at `uploadPlaceholderIcon(file.type)`). `<img v-if="thumbUrl" class="size-full object-cover">`, otherwise `<UIcon :name="icon" class="size-5 text-muted">` centred.
- `useIntersectionObserver(box, …, { root: () => props.scrollRoot, rootMargin: "200px" })`. The root is the list's own scroll box, passed by `UploadPanel.vue` as the new optional `scrollRoot` prop: with the viewport as root, `rootMargin` would not extend into the inner scroll box. On the first intersection, if `shouldDecode`, enqueue `createUploadThumbnail`, then stop observing. Success → `thumbUrl`. Failure → `icon = "lucide:image"`.
- `onUnmounted`: cancel the queued job, revoke `thumbUrl`, and set `unmounted = true` so a job still running revokes its URL when it completes (FR-077-07).

`UploadPanel.vue`: list box `h-48` → `h-72` (FR-077-05), template ref `listBox` passed as `:scroll-root`.

## Increments
1. **I1 – Helpers** (T-077-01, T-077-02): write a throwaway Node assertion script for the pure helpers first (icon mapping, `shouldDecode`, crop rect for landscape/portrait/square, a queue that never runs more than 2 jobs and drops cancelled jobs), see it fail, then implement `uploadThumbnail.ts`. The repo has no frontend unit-test runner (same approach as Feature 075). The script lives in the scratchpad and is not committed.
2. **I2 – Component** (T-077-03, T-077-04): `UploadingLine.vue` layout, lazy generation and cleanup; `UploadPanel.vue` height.
3. **I3 – Quality gate and docs** (T-077-05, T-077-06): `npm run format`, `npm run check`, knowledge map.
4. **I4 – Manual browser check** (T-077-07): S-077-01..07 on a running v8 instance, LTR and RTL, Chromium and Firefox (plus Safari for HEIC if available).

## Commands
- `npm run format`
- `npm run check`
- `node --experimental-strip-types <scratchpad>/check-upload-thumbnail.ts` (I1 only)

## Verification
- I1 assertion script (`node --experimental-strip-types`): icon mapping, `shouldDecode`, `squareCropRect`, queue cap and cancellation, slot freed after a rejected job. Green.
- `createUploadThumbnail` was bundled with esbuild and run in headless Chromium, Firefox and WebKit (Playwright, cached browser builds) on `tests/Samples`: `aarhus.jpg` and `orientation-{90,180,270,hflip}.jpg` give 80×80 `image/webp` blobs of 2–5 KB. The mean per-channel difference against the browser's EXIF-aware `<img>` rendering is 2–8, against 84–110 for a 180°-rotated reference, so orientation is applied. `classic-car.heic` rejects with `InvalidStateError` in all three, which leads to the `lucide:image` fallback.
- `npm run format`, `npm run check`, `npx eslint` on the three touched files: green.
- Not yet verified: the component in a running v8 instance (T-077-07).

## Scenario Tracking

| Scenario ID | Increment / Task | Notes |
|-------------|------------------|-------|
| S-077-01 | I2, I4 / T-077-03, T-077-07 | Orientation checked with a rotated-EXIF JPEG. |
| S-077-02 | I1, I4 / T-077-01, T-077-07 | Decode failure → `lucide:image`. |
| S-077-03 | I1, I4 / T-077-01, T-077-07 | Icon mapping asserted in the I1 script. |
| S-077-04 | I2, I4 / T-077-03, T-077-07 | RTL locale. |
| S-077-05 | I1, I4 / T-077-01, T-077-07 | Concurrency cap asserted in I1; responsiveness checked manually. |
| S-077-06 | I2, I4 / T-077-03, T-077-07 | Blob URLs gone from DevTools → Memory after close. |
| S-077-07 | I4 / T-077-07 | Entry points untouched; checked manually. |

## Analysis Gate
2026-10-02, agent review.
- Spec completeness: pass. FRs, NFRs and ASCII mock-ups (LTR, RTL, placeholders) are present, and Q-077-01..03 are folded into FR-077-01..05/07 and NFR-077-01.
- Open questions: pass. No `Open` entries. No ADR: the change is local to two v8 components and one helper, with no cross-module or persistence impact.
- Plan alignment: pass.
- Tasks coverage: pass. FR-077-01/02/03/07 → T-077-01..03; FR-077-04/06 → T-077-03; FR-077-05 → T-077-04. Tests come first via the I1 assertion script. Component behaviour is checked manually (T-077-07) because there is no frontend unit-test runner.
- Working agreements: pass. No new dependency, offline-safe icons, branching kept inside small helpers.
- Tooling: commands listed above.

## Exit Criteria
- T-077-01..06 `[x]`, `npm run format` and `npm run check` green.
- Roadmap and knowledge map updated.
- T-077-07 manual check done, or flagged as pending in the roadmap.
