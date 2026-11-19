# Feature 075 – Plan

| Field | Value |
|-------|-------|
| Linked spec | [spec.md](spec.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Last updated | 2026-10-01 |

## Approach
One increment (< 90 min), frontend only.

1. `AlbumThumbImage.vue` (v7, v8): append ` 2x` to the cover `srcset` (FR-075-01).
2. `PhotoState.ts`: new `sizesMedium` getter next to `srcSetMedium`; bind it as `:sizes` in v7/v8 `PhotoBox.vue` (FR-075-02).
3. `resources/js/embed/utils/srcset.ts`: `getSrcset`, `getSizes(photo, w, h, fit)`, `getViewportSizes`; bound in `EmbedWidget.vue` (grid, filmstrip strip + main viewer) and `Lightbox.vue` (FR-075-03). Existing `src` selection is kept as the fallback.

v7 is edited too: Feature 049 keeps v7 under normal maintenance until the v8 cutover, and v7 is the default UI.

## Verification
- `npm run format`, `npm run check`.
- The repository has no frontend unit-test runner (only Playwright), so the helper was checked with a throwaway Node script (`node check.ts` with `node:assert`): srcset string, `cover`/`contain` sizes, square tile with a 3:2 photo, thumb-only photo → empty.
- Browser check (Chromium, DPR 1, 1130×395 viewport): `sizes="min(100vw, 1.5000 * 100vh)"` selected the 1080w candidate over 1620w, while `100vw` selected 1620w, so math functions in `sizes` are evaluated.
- Live check against a production instance before the change: see spec Overview.

## Intent log
- Prompted by an operator report: Firefox on a 1920×1080 display loaded `2x` variants. Investigated in the built-in browser on the operator's gallery and its embed page; findings in spec Overview.
- Virtualized grids deliberately out of scope (spec Non-Goals).
