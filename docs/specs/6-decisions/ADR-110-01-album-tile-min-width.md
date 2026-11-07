# ADR-110-01: v8 Album Tiles Sized by a Minimum Width, Covers Chosen by Screen Density

- **Status:** Accepted
- **Date:** 2026-11-07
- **Related features/specs:** Feature 110 (docs/specs/4-architecture/features/110-gallery-size-config/spec.md), Feature 102 (FR-102-06, density of grid tiles), Feature 061/063 (v8 virtualized album grids)
- **Related open questions:** Q-110-01, Q-110-04, Q-110-05 (amendment)

## Context

v8 album grids (`AlbumRootGridVirtual.vue`, `AlbumThumbGridVirtual.vue`, non-virtual `AlbumThumb.vue`, drag selection in `dragAndSelect.ts`) take their geometry from `computeAlbumTileGeometry()` in `resources/js/v8/composables/album/albumTileWidth.ts`. At `sm` and above a nominal tile width comes from a per-breakpoint table (`TILE_WIDTH_FORMULA`, `viewportWidth × vwFraction − remOffset`), decides the column count, and the tiles are then stretched to fill the row. Admins cannot change it, the size jumps at each breakpoint (a 1536 px window gets smaller tiles than a 1535 px one), and the `6xl` tier gives 2560 CSS px windows (4K at 150 %, 5K at 200 %) 23 columns of 93 px tiles.

The five photo settings (`photo_layout_*_column_width`, `photo_layout_justified_row_height`) are CSS px targets, fed straight to the layout primitives.

Album tile covers are pinned to `type="small"` in `AlbumThumbVirtual.vue`, while `Thumb.vue`'s own rule (FR-102-06) picks `small2x` when `devicePixelRatio > 1`. On 2× screens a default-size tile (≈ 183 CSS px, 366 device px) already upscales portrait covers and 2:3 tiles 1.5×; larger tiles, which this feature allows, make it worse.

## Decision

1. New config `album_thumb_min_width` (CSS px, `positive`, default 175, v8 only via `SettingsController::V8_CONFIGS`). At `sm` and above: `itemsPerRow = max(1, floor((containerWidth + gap) / (min + gap)))`, `tileWidth = (containerWidth − gap × (itemsPerRow − 1)) / itemsPerRow`. `TILE_WIDTH_FORMULA` is removed. Below `sm`, `number_albums_per_row_mobile` still decides. The gap comes from `album_thumb_gap` (amended 2026-11-07, Q-110-05 A, FR-110-16): the value from 768 px, half of it at 640–767 px, a quarter below 640 px; default 16 keeps the former 4/8/16 px.
2. The rule depends on the container width only; the number of albums never changes tile width.
3. Values are CSS px; screen density never changes column counts.
4. `AlbumThumbVirtual.vue` stops pinning the cover variant; `Thumb.vue`'s FR-102-06 rule applies (`small2x` when `devicePixelRatio > 1`). A missing `small2x` falls back server side.

## Consequences

### Positive
- Album and photo size settings share one meaning (minimum CSS px), which the preview page and live drawer can show directly.
- Tile size grows steadily with the window; the 2560 px+ outlier is gone (13 columns of 177 px at the default).
- Sharp covers on high-density screens, same rule as photo tiles.

### Negative
- Not a 1:1 copy of today: the default 175 gives today's column count at 59 % of widths 640–2559 px, within one column at 87 %. Existing installs see some column counts change after upgrade.
- On 2× screens every album cover loads `small2x` (≈ 4× the pixels of `small`), also where `small` would cover a small tile.
- Tiles wider than 360 CSS px on 2× screens still upscale `small2x`; `medium` is not served by the v3 asset API (backlog).

## Alternatives Considered

- **Q-110-01 B – scale % on today's table:** default reproduces today; abstract unit, keeps the breakpoint jumps and the 2560 px outlier.
- **Q-110-01 C – Small/Medium/Large preset:** simplest; no fine control, unlike the photo settings.
- **Q-110-04 A – choose by device-pixel size** (`max(tileWidth, tileHeight) × devicePixelRatio > small_max_height`): loads `small2x` only when needed and also helps large tiles on 1× screens; needs `small_max_height` in `InitConfig` and a chooser. Owner chose B (2026-11-07) for the existing rule.
- **Q-110-04 C – keep `small`:** no bandwidth change; larger tiles soft on high-density screens.

## Security / Privacy Impact

None: a layout setting and a different size variant of the same photo through the same asset authorization.

## Operational Impact

- More bytes per album page on high-density screens (`small2x`).
- One more config row; no schema change.

## Links

- Related spec sections: `docs/specs/4-architecture/features/110-gallery-size-config/spec.md` FR-110-01 … 03, FR-110-12, NFR-110-08, Appendix A/B
- Related ADRs: ADR-102-01 (direct tile URLs, same density rule)
