# Feature 077 – Tasks

| Field | Value |
|-------|-------|
| Linked spec | [spec.md](spec.md) |
| Linked plan | [plan.md](plan.md) |

- [x] T-077-01 Throwaway Node assertion script for the pure helpers (icon mapping, `shouldDecode`, `squareCropRect`, queue cap of 2 and cancellation). Confirm it fails (FR-077-01, FR-077-02, FR-077-03, FR-077-07, S-077-02, S-077-03, S-077-05).
- [x] T-077-02 `resources/js/v8/utils/uploadThumbnail.ts`: helpers plus `createUploadThumbnail`. Assertion script green (FR-077-01, FR-077-02, FR-077-03, FR-077-07).
- [x] T-077-03 `v8/components/forms/upload/UploadingLine.vue`: 40×40 box on the inline-start side, icon/miniature, `useIntersectionObserver` (200 px margin) feeding the queue, cleanup on unmount (FR-077-01..04, FR-077-06, FR-077-07, S-077-01, S-077-04, S-077-06).
- [x] T-077-04 `v8/components/modals/UploadPanel.vue`: list box `h-48` → `h-72` (FR-077-05).
- [x] T-077-05 `npm run format`, `npm run check`.
- [x] T-077-06 Knowledge map entry for `uploadThumbnail.ts` / `UploadingLine.vue`; roadmap progress.
- [ ] T-077-07 Manual browser check of S-077-01..07 (LTR + RTL, Chromium + Firefox, Safari for HEIC if available).
