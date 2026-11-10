# Open Questions – Feature 110

Open questions for [Feature 110](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-110-01~~ | 110 | High | Album tile size rule: (A) minimum tile width in px like the photo column widths, replaces the vw table at `sm`+, default 175, (B) scale % on today's vw table, default 100, (C) Small/Medium/Large preset | Resolved (A, owner, 2026-11-07: FR-110-01, ADR-110-01) | 2026-11-07 | 2026-11-07 |
| ~~Q-110-02~~ | 110 | Medium | Where the preview lives: (A) own admin page `/admin/gallery-sizes` with dashboard tile and Save, (B) panel inside Settings › Gallery under the fields, (C) live-tweak drawer on the gallery itself | Resolved (A + C, owner, 2026-11-07: C opened from Spotlight only; FR-110-04, FR-110-13 … 15) | 2026-11-07 | 2026-11-07 |
| ~~Q-110-03~~ | 110 | Medium | Preview width: (A) device presets laid out at true width and scaled to fit, plus `number_albums_per_row_mobile` control for Phone, (B) preview pane's own width only, (C) full-width preview with controls in a collapsible drawer | Resolved (A, owner, 2026-11-07: FR-110-05, FR-110-09) | 2026-11-07 | 2026-11-07 |
| ~~Q-110-04~~ | 110 | High | High-density screens: v8 album tiles always load `small` (360 px tall), already soft on 2× screens and worse with larger tiles: (A) pick `small2x` when the tile's device-pixel size exceeds `small`, (B) unpin and use `Thumb`'s rule (`small2x` whenever DPR > 1), (C) keep `small`, backlog | Resolved (B, owner, 2026-11-07, after first answering A: FR-110-12, ADR-110-01) | 2026-11-07 | 2026-11-07 |
| ~~Q-110-05~~ | 110 | Medium | Album gap setting `album_thumb_gap` (owner request 2026-11-07) across screen widths, today 4/8/16 px: (A) one value used from 768 px, scaled ½ at 640–767 and ¼ below 640, default 16 = today, (B) value from 768 px, smaller screens keep 4/8 capped by it, (C) same value at every width | Resolved (A, owner, 2026-11-07: FR-110-16, ADR-110-01 amended) | 2026-11-07 | 2026-11-07 |

## Question Details

### ~~Q-110-01~~ · Album tile size rule

**Status:** Resolved (A, owner, 2026-11-07: FR-110-01, ADR-110-01)
**Feature:** F-110 – Gallery Size Configuration
**Preferred option:** 🅰️ (**recommended**) Option A – Minimum tile width in px

**Question**
v8 album tiles take their width from `TILE_WIDTH_FORMULA` (a vw fraction per breakpoint, `albumTileWidth.ts`). What does the new setting control?

#### 🅰️ (**recommended**) Option A – Minimum tile width in px
- **Idea:** `album_thumb_min_width` (px, default 175). Columns = as many tiles of at least that width as fit the container; the leftover width is shared by the columns (today's stretch step, adds less than `(min + gap) / columns`). The album count never changes the tile width: one album is one column-wide tile, not a full row (owner check 2026-11-07, S-110-14). Replaces the vw table at `sm` and above; below `sm` `number_albums_per_row_mobile` still decides.
- **Spec impact:** FR-110-01 as drafted; `TILE_WIDTH_FORMULA` removed; new `albumTileWidth.test.ts`.
- **Pros:**
  - ✅ Same meaning as `photo_layout_square/grid/masonry_column_width`, so the preview page explains itself.
  - ✅ Tile size grows steadily with the window; no jumps at breakpoints (today the tile at 1536 px is smaller than at 1535 px).
  - ✅ Removes the 2560 px+ outlier (today 23–24 columns of 93–142 px tiles).
- **Cons:**
  - ❌ Not a 1:1 copy of today: 175 px gives today's column count at 59 % of widths 640–2559 px, within one column at 87 % (spec Appendix A).
  - ❌ Existing installs see a column count change at some widths after upgrade.

#### 🅱️ Option B – Scale percentage on today's table
- **Idea:** `album_thumb_size_scale` (%, default 100) multiplies the nominal tile width of `TILE_WIDTH_FORMULA`.
- **Spec impact:** FR-110-01 becomes a multiplier; vw table kept.
- **Pros:**
  - ✅ Default 100 reproduces today exactly; no change on upgrade.
- **Cons:**
  - ❌ Abstract unit, the size still jumps at each breakpoint and keeps the 2560 px+ outlier.
  - ❌ Different meaning from the five photo settings on the same page.

#### 🅲 Option C – Preset Small / Medium / Large
- **Idea:** enum setting mapping to three minimum widths (e.g. 140 / 175 / 240 px) with Option A's rule.
- **Spec impact:** FR-110-01 enum; preview slider becomes a 3-way switch.
- **Pros:**
  - ✅ Simplest choice for admins.
- **Cons:**
  - ❌ No fine control; less reason for a preview.
  - ❌ Different from the photo settings, which are free px values.

**Next action**
Owner picks A, B or C; the answer goes into FR-110-01, DO-110-01 and S-110-01 … 03.

### ~~Q-110-02~~ · Where the preview lives

**Status:** Resolved (A + C, owner, 2026-11-07: C opened from Spotlight only; FR-110-04, FR-110-13 … 15)
**Feature:** F-110 – Gallery Size Configuration
**Preferred option:** 🅰️ (**recommended**) Option A – Own admin page

**Question**
Where does the admin open the preview, and how are the values saved?

#### 🅰️ (**recommended**) Option A – Own admin page
- **Idea:** v8 page `/admin/gallery-sizes` (route name `gallery-sizes`), tile on the admin dashboard next to "Watermark" and "Landing page"; controls left, preview right, Save / Revert (as `WatermarkPreview.vue`, `LandingConfig.vue`). A link to it from Settings › Gallery.
- **Spec impact:** FR-110-04, FR-110-10, API-110-03 as drafted.
- **Pros:**
  - ✅ Follows two existing pages; room for a wide preview.
  - ✅ Settings page code (`ConfigGroup.vue`) untouched.
- **Cons:**
  - ❌ Seven settings editable in two places.
  - ❌ One more route, web route and dashboard tile.

#### 🅱️ Option B – Panel inside Settings › Gallery
- **Idea:** preview panel rendered under the Gallery fields, driven by their unsaved values; saved with the Settings page's own Save.
- **Spec impact:** FR-110-04 becomes a Settings change; no route.
- **Pros:**
  - ✅ One place to edit; no new route.
- **Cons:**
  - ❌ Narrow column, preview cramped; the 7 fields are spread among ~30 Gallery settings.
  - ❌ Special-casing in the generic `ConfigGroup.vue`.

#### 🅲 Option C – Live drawer on the gallery
- **Idea:** admin opens a slideover on an album page; the real grid re-lays out live; save from the drawer.
- **Spec impact:** FR-110-07 (placeholders) would not hold: the grid shows real content.
- **Pros:**
  - ✅ Most realistic.
- **Cons:**
  - ❌ Conflicts with the owner's placeholder directive.
  - ❌ Touches the album stores and grids; larger risk.

**Next action**
Owner picks A, B or C; the answer goes into FR-110-04, FR-110-10 and API-110-03.

### ~~Q-110-03~~ · Preview width

**Status:** Resolved (A, owner, 2026-11-07: FR-110-05, FR-110-09)
**Feature:** F-110 – Gallery Size Configuration
**Preferred option:** 🅰️ (**recommended**) Option A – Device presets, scaled to fit

**Question**
Column counts depend on the window width, and the preview pane is narrower than the gallery. At what width does the preview lay out?

#### 🅰️ (**recommended**) Option A – Device presets, scaled to fit
- **Idea:** presets Phone 390, Tablet 820, Laptop 1440, Desktop 1920, Wide 2560 px and "This window"; the preview is laid out at that width and scaled down to fit the pane. Because Phone uses `number_albums_per_row_mobile`, that setting gets a control on the page too.
- **Spec impact:** FR-110-09 as drafted; an 8th setting on the page; S-110-13.
- **Pros:**
  - ✅ Shows what a visitor at each width sees, including phone and ultra-wide.
  - ✅ Same layout functions, only the width input changes.
- **Cons:**
  - ❌ Scaled tiles at Wide (≈ 50 %) are small to judge.
  - ❌ Adds `number_albums_per_row_mobile`, which the request did not list.

#### 🅱️ Option B – Preview pane's own width
- **Idea:** lay out at the pane's real width, no presets.
- **Spec impact:** FR-110-09 removed.
- **Pros:**
  - ✅ Simplest; tiles at true size.
- **Cons:**
  - ❌ Column counts differ from the gallery on the same screen (pane is ~400 px narrower).

#### 🅲 Option C – Full-width preview, controls in a drawer
- **Idea:** preview fills the window; controls in a collapsible side drawer over it.
- **Spec impact:** FR-110-09 becomes "window width"; mock-up changes.
- **Pros:**
  - ✅ True size and true column count for the admin's own screen.
- **Cons:**
  - ❌ Drawer hides part of the preview while editing; no view of other widths.

**Next action**
Owner picks A, B or C; the answer goes into FR-110-09, the mock-ups and S-110-13.

### ~~Q-110-04~~ · Album cover sharpness on high-density screens

**Status:** Resolved (B, owner, 2026-11-07, after first answering A: FR-110-12, ADR-110-01)
**Feature:** F-110 – Gallery Size Configuration
**Preferred option:** 🅰️ (**recommended**) Option A – Pick the variant from the tile's device-pixel size

**Question**
Tile layout is in CSS px, so density does not change column counts (a 4K screen at 200 % reports 1920 px, like a 1080p one). Image sharpness does: `AlbumThumbVirtual.vue` pins `type="small"` (height `small_max_height`, default 360) on every screen, while `Thumb.vue` alone would pick `small2x` when `devicePixelRatio > 1`. A 183 px tile on a 2× screen needs 366 device px: landscape covers are about right, portrait covers (`small` 240 px wide) and 2:3 tiles (275 px tall, 549 device px) are upscaled 1.5×; a 300 px minimum width upscales `small` at least 1.7×. Which variant should album tiles load?

#### 🅰️ (**recommended**) Option A – Pick the variant from the tile's device-pixel size
- **Idea:** load `small2x` when `max(tileWidth, tileHeight) × devicePixelRatio > small_max_height`, else `small`. `small_max_height` added to `InitConfig`. A missing `small2x` already falls back to `small` server side (`ResolvesSizeVariantAssetTrait`). The preview shows the tile size and the variant a 1× and a 2× screen would load.
- **Spec impact:** new FR (album tile variant), `InitConfig` field, Vitest for the pure chooser.
- **Pros:**
  - ✅ Sharp on 2× screens and with large tiles on 1× screens.
  - ✅ Loads the larger file only when the tile needs it.
- **Cons:**
  - ❌ On 2× screens nearly every tile at default size crosses the threshold: ≈ 4× the pixels per cover.
  - ❌ Above 360 CSS px on a 2× screen even `small2x` (720 px) is upscaled; `medium` is not served by the v3 asset API (backlog row from Feature 109).

#### 🅱️ Option B – Use `Thumb`'s existing density rule
- **Idea:** drop `type="small"` in `AlbumThumbVirtual.vue`; `Thumb.vue` picks `small2x` when `devicePixelRatio > 1` (Feature 102, FR-102-06), as photo tiles do.
- **Spec impact:** one-line change, new FR referencing FR-102-06.
- **Pros:**
  - ✅ Smallest change; same rule as photo tiles.
- **Cons:**
  - ❌ Large tiles on 1× screens stay soft (`small` for a 400 px tile).
  - ❌ `small2x` also on 1.25× laptops with small tiles that `small` covers.

#### 🅲 Option C – Keep `small`
- **Idea:** no change; backlog row.
- **Spec impact:** Non-Goal.
- **Pros:**
  - ✅ No bandwidth change.
- **Cons:**
  - ❌ Larger album tiles, the point of this feature, look soft on high-density screens; placeholders in the preview cannot show it.

**Next action**
Owner picks A, B or C; the answer goes into a new FR, `InitConfig` (Option A) and the preview.

### ~~Q-110-05~~ · Album gap across screen widths

**Status:** Resolved (A, owner, 2026-11-07: FR-110-16, ADR-110-01 amended)
**Feature:** F-110 – Gallery Size Configuration
**Preferred option:** 🅰️ (**recommended**) Option A – One value, scaled down on smaller screens

**Question**
Owner request (2026-11-07): a setting for the gap between albums. Settled without a question: new config `album_thumb_gap` (v8 only, type `int` so 0 is allowed, unlike `photo_layout_gap`; range 0–48 px), used both between columns and between rows, editable on the preview page and in the live drawer (Albums section); list view unchanged; the Non-Goal "configurable album tile gap" is withdrawn. Open: today the gap depends on the width (`GAP_PX`: 4 px below 640, 8 px at 640–767, 16 px from 768). How should one value apply across widths?

#### 🅰️ (**recommended**) Option A – One value, scaled down on smaller screens
- **Idea:** the value applies from 768 px; at 640–767 px half of it, below 640 px a quarter (rounded). Default 16 gives today's 4/8/16 exactly.
- **Spec impact:** new FR (setting, rule, default), `GAP_PX` replaced by the rule, `InitConfig` field, control in the Albums section.
- **Pros:**
  - ✅ No visible change on upgrade.
  - ✅ One number; phones keep tight gaps; no jump between widths.
- **Cons:**
  - ❌ The value is not literal below 768 px; the setting's details must say so.

#### 🅱️ Option B – Literal from 768 px, small screens keep today's gaps
- **Idea:** the value applies from 768 px; below, today's 4 px and 8 px stay, capped by the value (`min(value, 4)`, `min(value, 8)`). Default 16 = today.
- **Spec impact:** same as A with a different rule.
- **Pros:**
  - ✅ Literal on tablets and desktops; no change on upgrade.
- **Cons:**
  - ❌ Large gaps jump at 768 px (30 px above, 8 px just below).
  - ❌ Two hard-coded small-screen values remain.

#### 🅲 Option C – Same value at every width
- **Idea:** the value applies everywhere, as `photo_layout_gap` does.
- **Spec impact:** same as A with a different rule and a default to choose.
- **Pros:**
  - ✅ Simplest rule, same as the photo gap.
- **Cons:**
  - ❌ No default reproduces today: 16 widens phone gaps from 4 to 16 px (3 columns on a 390 px phone lose 24 px of tile width).

**Next action**
Owner picks A, B or C; the answer goes into the new FR, `albumTileWidth.ts`, the preview and the drawer.

