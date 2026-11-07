# Feature 110 – Gallery Size Configuration

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-07 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #110 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
v8 album tiles get their width from a hard-coded table of viewport fractions per breakpoint (`resources/js/v8/composables/album/albumTileWidth.ts`, `TILE_WIDTH_FORMULA`); an admin cannot change it. Photo tiles already have five settings (`photo_layout_*`), but their effect is only visible by saving and opening an album. This feature adds an album tile size setting (v8 only, ADR-110-01), an admin page previewing the size settings live on generated placeholders, and a live-tweak drawer on the real gallery opened from Spotlight only. Layers: one config migration, `InitConfig` (+ regenerated TypeScript types), v8 front end (album tile geometry and cover variant, page, drawer, Spotlight action, route, admin dashboard tile), lang files.

## Goals
- An admin sets the v8 album tile size as a minimum tile width in CSS px (FR-110-01 … FR-110-03, ADR-110-01).
- An admin sets the gap between v8 album tiles (FR-110-16).
- Album covers are sharp on high-density screens (FR-110-12, ADR-110-01).
- An admin tunes the 9 size settings (DO-110-03) on one page, sees the result immediately on placeholders at chosen screen widths, then saves (FR-110-04 … FR-110-11).
- An admin tunes the same settings live on the real gallery from a Spotlight action (FR-110-13 … FR-110-15).
- Preview and live drawer lay tiles out exactly as the gallery does (NFR-110-01).

## Non-Goals
- v7: no new setting is read, no page, no drawer (shared files touched: NFR-110-07).
- Real gallery content on the preview page (owner directive 2026-11-07: placeholders only). The live drawer shows the real gallery by design (Q-110-02).
- Per-user or per-album size settings: settings stay instance-wide. The per-album aspect ratio (`albums.album_thumb_aspect_ratio`) is unchanged and wins over the default, as on the server.
- "Reset to defaults" (would duplicate migration defaults in the front end). Page and drawer offer "Revert" to the stored values.
- Allowing `photo_layout_gap = 0`: the `positive` type range rejects 0 today; unchanged.
- `medium` covers for tiles wider than 360 CSS px on 2× screens: `medium` is not served by the v3 asset API (existing backlog row from Feature 109).
- Any other entry point to the live drawer (button, menu, route, keyboard shortcut).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-110-01 | New config `album_thumb_min_width` (category `Gallery`, `type_range` `positive`, default `175`, not expert) sets the v8 album tile size at `sm` and above: `itemsPerRow = max(1, floor((containerWidth + gap) / (min + gap)))`, then `tileWidth = (containerWidth − gap × (itemsPerRow − 1)) / itemsPerRow`, so `min ≤ tileWidth < min + (min + gap) / itemsPerRow`. Column count and tile width depend on the width only, never on the number of albums: a row with fewer albums than columns keeps the tile width and leaves the remaining slots empty; tiles fill a row only when `min` exceeds half the container. Values are CSS px: screen density never changes column counts. Replaces `TILE_WIDTH_FORMULA`. Listed in `SettingsController::V8_CONFIGS` (hidden from Settings without `features.v8`). | Every grid using `useAlbumTileWidth` / `computeAlbumTileGeometry` (root albums, album children, non-virtual `AlbumThumb.vue`, drag selection in `dragAndSelect.ts`) uses the setting. | Server: strictly positive integer (existing `positive` check). | — | None. | Q-110-01 A, ADR-110-01. |
| FR-110-02 | `InitConfig` exposes `album_thumb_min_width` (int); `LycheeState` stores it (default `175` before `Gallery::Init` resolves). | v8 reads it from the store synchronously before the first layout. | — | — | None. | FR-110-01. |
| FR-110-03 | Below `sm`, `number_albums_per_row_mobile` decides the column count; `album_thumb_min_width` is ignored there. | Phone layout unchanged. | — | — | None. | Existing behaviour. |
| FR-110-04 | New v8 admin page `/admin/gallery-sizes` (route name `gallery-sizes`), tile in the admin dashboard `extensions` group beside "Watermark" and "Landing page" (no link from Settings › Gallery: removed at the owner's request, 2026-11-07). Visible and usable only with `rights.settings.can_edit`. | Admin opens the page from the tile. | Non-admin: no tile; direct URL shows the not-allowed state. | Settings load fails: error toast, controls disabled (UI-110-04). | None. | Q-110-02 A. |
| FR-110-05 | The page loads the stored values of the 9 settings (DO-110-03) and shows one control each: slider + number input for the 7 integers, a select with the 6 `AspectRatioType` values, a 1/2/3 switch for `number_albums_per_row_mobile`. That setting is level 1 (Lychee SE, `ConfigIntegrity::SE_FIELDS`): its control shows only when `Settings` returns it (SE or SE preview); otherwise the Phone preset uses the stored value from `LycheeState` and the setting is never sent. Double-clicking a setting's label (number rows, aspect ratio, per row on phones) puts back that setting's stored value; a layout-specific one also shows its layout; the label's tooltip says so (owner 2026-11-07). | Controls start at the stored values. | Number input clamped to the control range (UI-110-05). | — | None. | Owner request; aspect ratio added by owner; mobile per row from Q-110-03 A. |
| FR-110-06 | The preview is reactive: every control change re-lays out the preview at once, with no request and no save. | Moving a slider moves the tiles while dragging. | — | — | None. | Owner directive 2026-11-07. |
| FR-110-07 | Preview content is generated in the browser: N album placeholders and M photo placeholders, N and M set on the page (page-local, not saved; defaults 12 and 60, ranges 1–100 and 1–500). Photos get random aspect ratios from a fixed mix (DO-110-02); albums get a title "Album n" and the selected aspect ratio. "Shuffle" draws a new set. No real data, no image loading. | Changing N or M changes the number of tiles; Shuffle changes photo ratios. | N, M clamped to their ranges. | — | None. | Owner directive 2026-11-07. |
| FR-110-08 | A layout switch (Justified, Masonry, Square, Grid) selects the photo layout shown. Changing a layout-specific setting switches to that layout; the rows of the other layouts' settings are muted (no colour highlight, owner 2026-11-07); `photo_layout_gap` applies to all four (as `PhotoGridVirtual.vue`). | Editing "Column width (masonry)" shows masonry. | — | — | None. | Owner request. |
| FR-110-09 | Width presets Phone 390, Tablet 820, Laptop 1440, Desktop 1920, Wide 2560 px and "This window" (default). The preview is laid out as for a window of that width (gallery padding, scrollbar and breakpoint as in `useAlbumTileWidth`, nav closed) and scaled down to fit its pane; the scale is shown beside the presets. Phone (< `sm`) uses `number_albums_per_row_mobile` columns. | At Laptop the preview shows the column count a 1440 px visitor sees. | — | — | None. | Q-110-03 A. |
| FR-110-10 | "Save" writes the changed settings in one `SettingsService.setConfigs` call; "Revert" restores the stored values. An unsaved-changes dot shows while values differ from the stored ones. | Success toast; stored values = page values; dot cleared. | Server validation error: error toast with the server message, values and dot kept. | Network/5xx: same as validation error. | None. | `WatermarkPreview.vue` pattern. |
| FR-110-11 | After a successful save (page or drawer), the admin's session uses the new values without a reload: `LayoutState.config`, `LycheeState.album_thumb_min_width`, `LycheeState.album_thumb_gap`, `LycheeState.number_albums_per_row_mobile`, `AlbumsState.rootConfig.album_thumb_css_aspect_ratio` and, when the open album has no aspect ratio of its own, `AlbumState.config.album_thumb_css_aspect_ratio` are updated in memory. | Back in the gallery the new sizes apply. | — | — | None. | `LayoutState.load()` returns early once loaded. |
| FR-110-12 | v8 album tiles (`AlbumThumbVirtual.vue`) no longer pin `type="small"`: `Thumb.vue` picks `small2x` when `devicePixelRatio > 1`, else `small` (FR-102-06 rule, as photo tiles). | 2× screens load `small2x` covers. | — | Missing `small2x`: server falls back down the variant chain (`ResolvesSizeVariantAssetTrait`). | None. | Q-110-04 B, ADR-110-01. |
| FR-110-13 | Spotlight action "Adjust gallery sizes (live)" for admins (`rights.settings.can_edit`) on routes `gallery`, `album`, `flow-album`, `timeline`, `search`. It is the only way to open the live drawer. | Selecting it closes Spotlight and opens the drawer. | Hidden for non-admins and on other routes. | — | None. | Q-110-02 C (owner: "only activable by the spotlight search"). |
| FR-110-14 | Live drawer: non-modal side drawer over the real gallery with the same 9 controls as the page, grouped under plain "Albums" and "Photos" headings without fieldset borders, each shown only when the page has those tiles: root gallery albums only (own, shared, pinned, smart, tag, person), album pages sub-albums and/or photos, timeline photos, search result albums and/or photos; with neither, a one-line note (`liveGallerySections`, owner 2026-11-07) (no placeholder counts, Shuffle or width presets), Save, Revert, Close. While open, the gallery uses the drawer's values (live override, DO-110-04): album grids, photo grids and drag selection re-lay out at once. Editing a layout-specific value switches the shown photo layout; closing restores the layout shown before opening. The default aspect ratio applies to the root gallery and to albums without their own aspect ratio (`editable.aspect_ratio`). Gallery keyboard shortcuts are off while the drawer is open (`ModalsState.is_modal_open`), so typing a value never triggers them. The override starts once the stored values are loaded: opening never moves a tile. | Admin drags "Minimum tile width": album tiles resize under the drawer. | Same clamping as the page. | Save failure: as FR-110-10. | None. | Q-110-02 C. |
| FR-110-15 | Closing the drawer discards unsaved values: the override is cleared and the gallery returns to the stored values. The drawer stays open and its values stay applied across navigation between gallery routes; leaving the gallery routes closes it (discarding). | Close → gallery as before opening. | — | — | None. | Q-110-02 C, lightweight decision 2026-11-07. |
| FR-110-16 | New config `album_thumb_gap` (category `Gallery`, `type_range` `int` so 0 is allowed, default `16`, not expert, in `V8_CONFIGS`, exposed by `InitConfig`, stored in `LycheeState`) sets the gap between v8 album tiles, between columns and between rows. From 768 px (`md`) the gap is the value; at 640–767 px `round(value / 2)`; below 640 px `round(value / 4)`. The default gives today's 4/8/16 px. It replaces `GAP_PX` in `computeAlbumTileGeometry`, so the virtual grids, the non-virtual `AlbumThumbPanel.vue` (smart, tag, person and pinned albums, tag page), drag selection, the preview and the live drawer all use it. Album list view unchanged. | Admin sets 30: 30 px gaps on desktop, 15 px on small tablets, 8 px on phones. | Server: non-negative integer (`int` check); controls 0–48. | Missing value: front end uses 16. | None. | Owner request 2026-11-07, Q-110-05 A. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-110-01 | Preview geometry equals gallery geometry: albums through `computeAlbumTileGeometry`, photos through `computeBucketLayout` (same WASM primitives as `PhotoGridVirtual.vue`); live mode feeds the same consumers through one override (DO-110-04); no second copy of the layout math. | A preview that differs from the gallery misleads. | Vitest: preview geometry equals direct calls for the same inputs; effective-value composables return the override while set. | `albumTileWidth.ts`, `analyticPhotoLayout.ts`, `wasmLayouts.ts`. | Owner request. |
| NFR-110-02 | Re-layout of 100 albums + 500 photos completes within one frame budget (≤ 16 ms) on a desktop browser; placeholders are plain elements (no images). | FR-110-06 "while dragging". | Browser check on the scratch instance (`performance.now()` around the layout computation). | — | Owner directive. |
| NFR-110-03 | Placeholder generation is a pure function of `(seed, albumCount, photoCount)`; Shuffle changes the seed. | Deterministic tests. | Vitest. | — | Test-first cadence. |
| NFR-110-04 | No new dependency. | AGENTS.md. | `package.json`, `composer.json` unchanged. | — | AGENTS.md. |
| NFR-110-05 | All strings in lang files: new `lang/*/gallery_sizes.php` and the `all_settings.php` entries for `album_thumb_min_width` and `album_thumb_gap`, in all 23 locales (English text where untranslated). | i18n parity. | Lang key consistency checks. | `lang/*`. | Repo convention. |
| NFR-110-06 | Accessibility: each control has a visible label and value; sliders keyboard-operable; preview tiles `aria-hidden`, the preview region has a text summary ("7 albums per row · 9 photo rows"). | WCAG 2.2 AA. | Keyboard pass in the browser check. | — | Repo UI practice. |
| NFR-110-07 | v7 behaviour unchanged; the two settings are hidden from Settings without `features.v8` (`V8_CONFIGS`), and their descriptions do not mention v8 (owner, 2026-11-07). Shared files touched: `resources/js/router/paths.ts` (v7 maps the new route name to its Placeholder, no v7 link to it), `LycheeState.ts` (new field), `ModalsState.ts` (drawer flag), `dragAndSelect.ts` (v3 path only). | Scope. | `git diff --stat`: no `resources/js/v7/` path. | — | Owner: "v8 only". |
| NFR-110-08 | On 2× screens album covers load `small2x` (≈ 4× the pixels of `small`). Accepted for sharpness. | ADR-110-01. | — | — | Q-110-04 B. |

## UI / Interaction Mock-ups

### Page, desktop (≥ xl): controls left, preview right

```
+------------------------------------------------------------------------------------------+
| ☰  Gallery sizes                                              ● unsaved  [Revert] [Save] |
+------------------------------------------------------------------------------------------+
| ┌ Albums ──────────────────────────┐  Width: [Phone][Tablet][Laptop][Desktop][Wide][*⛶*] |
| │ Minimum tile width        175 px │         1696 px · shown at 74 %                     |
| │ ├────────●───────────────┤ [175] │  ┌─────────────────────────────────────────────────┐ |
| │ Gap                        16 px │  │                                                 │ |
| │ Aspect ratio         [ 1:1    ▾] │  │ ┌─────┐┌─────┐┌─────┐┌─────┐┌─────┐┌─────┐┌─────┐│ |
| │ Per row on phones   [1][2][*3*]  │  │ │     ││     ││     ││     ││     ││     ││     ││ |
| └──────────────────────────────────┘  │ └─────┘└─────┘└─────┘└─────┘└─────┘└─────┘└─────┘│ |
| ┌ Photos ──────────────────────────┐  │ Album 1 Album 2 Album 3 …                        │ |
| │ Layout [*Justified*][Masonry]    │  │ ┌─────────┐┌───┐┌──────────────┐┌──────┐┌──────┐ │ |
| │        [Square][Grid]            │  │ │         ││   ││              ││      ││      │ │ |
| │ Gap                        12 px │  │ └─────────┘└───┘└──────────────┘└──────┘└──────┘ │ |
| │ Row height (justified)    320 px │  │ ┌────┐┌────────────┐┌─────┐┌────────┐┌───────┐   │ |
| │ Column width (masonry)    300 px │  │ │    ││            ││     ││        ││       │   │ |
| │ Column width (grid)       250 px │  │ └────┘└────────────┘└─────┘└────────┘└───────┘   │ |
| │ Column width (square)     200 px │  │ …                                                │ |
| └──────────────────────────────────┘  └─────────────────────────────────────────────────┘ |
| ┌ Placeholders ────────────────────┐  8 albums per row · 9 photo rows                     |
| │ Albums [ 12 ]   Photos [ 60 ]    │                                                      |
| │ [⟳ Shuffle]                      │                                                      |
| └──────────────────────────────────┘                                                      |
+------------------------------------------------------------------------------------------+
  ⇄ any change re-lays out the preview at once (FR-110-06), nothing is sent until [Save]
  ● shown while values differ from the stored ones (FR-110-10)
  ⚠ save error: toast with the server message, values kept
```

### Page, narrow (< xl): controls stack above the preview

```
+--------------------------------------+
| ☰  Gallery sizes   ● [Revert] [Save] |
+--------------------------------------+
| ▸ Albums        (collapsible)        |
| ▸ Photos                             |
| ▸ Placeholders                       |
| Width: [*Phone*][Tablet][…]          |
| ┌──────────────────────────────────┐ |
| │ ┌────┐┌────┐┌────┐               │ |
| │ └────┘└────┘└────┘               │ |
| │ ┌──────┐┌──┐┌─────┐              │ |
| └──────────────────────────────────┘ |
+--------------------------------------+
```

### Spotlight entry and live drawer

```
 ┌ Spotlight ─────────────────────────────┐
 │ > gallery si                           │
 │   ⇲ Adjust gallery sizes (live)   ◀ admin, gallery routes only
 └────────────────────────────────────────┘

+--------------------------------------------------------------+------------------------------+
| real gallery (album / root / timeline / search)              | Gallery sizes (live)     [✕] |
| ┌─────┐┌─────┐┌─────┐┌─────┐┌─────┐┌─────┐                    | Minimum tile width    175 px |
| │ alb │└─────┘└─────┘└─────┘└─────┘└─────┘   ⇄ re-lays out   | ├──────●──────────┤  [175]   |
| └─────┘                                         while dragging| Gap (albums)          16 px  |
|                                                              | Aspect ratio     [ 1:1  ▾]   |
| ┌───────┐┌──┐┌─────────┐┌─────┐                              | Per row on phones [1][2][*3*]|
| │ photo ││  ││         ││     │                              | Layout [*Just.*][Mas.][Sq.][G]|
| └───────┘└──┘└─────────┘└─────┘                              | Gap / Row height / Columns … |
| (scrollable, clickable: drawer is non-modal)                 | ● unsaved  [Revert]  [Save]  |
+--------------------------------------------------------------+------------------------------+
  [✕] with unsaved values: discarded, gallery returns to the stored values (FR-110-15)
```

### UI-110-05 · Control ranges

| Control | Setting | Min | Max | Step |
|---------|---------|-----|-----|------|
| Minimum tile width | `album_thumb_min_width` | 80 | 480 | 5 |
| Gap (albums) | `album_thumb_gap` | 0 | 48 | 1 |
| Aspect ratio | `default_album_thumb_aspect_ratio` | 6 values of `AspectRatioType` | | |
| Per row on phones | `number_albums_per_row_mobile` | 1 | 3 | 1 |
| Gap | `photo_layout_gap` | 1 | 48 | 1 |
| Row height (justified) | `photo_layout_justified_row_height` | 80 | 800 | 10 |
| Column width (masonry / grid / square) | `photo_layout_*_column_width` | 80 | 800 | 10 |
| Albums / Photos (page only, not saved) | — | 1 / 1 | 100 / 500 | 1 |

A stored value outside the range is shown as is in the number input (slider pinned at its end) and is not changed unless the admin edits it.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-110-01 | Fresh install, v8, 1440 px window, nav closed: root album grid shows 7 per row (min 175, Appendix A). |
| S-110-02 | Admin sets `album_thumb_min_width` = 300 in Settings: same window shows 4 per row after reload. |
| S-110-03 | Window < 640 px: column count = `number_albums_per_row_mobile`, whatever `album_thumb_min_width` is. |
| S-110-04 | Admin opens the page: controls show the stored values, preview shows 12 album and 60 photo placeholders. |
| S-110-05 | Admin drags "Row height (justified)": preview switches to Justified, rows change height while dragging; no request sent. |
| S-110-06 | Admin sets Photos to 200: 200 photo tiles; value not saved. |
| S-110-07 | Shuffle: photo ratios change, counts stay. |
| S-110-08 | Aspect ratio 1:1 → 2:3: album placeholders get taller, columns unchanged. |
| S-110-09 | Save with changes: one `setConfigs` call with the changed keys only, success toast, dot cleared; back in the gallery the new sizes apply without reload. |
| S-110-10 | Save rejected by the server: error toast with message, values and dot kept. |
| S-110-11 | Revert: values return to the stored ones, dot cleared. |
| S-110-12 | Non-admin: no tile, no Spotlight action; direct URL shows the not-allowed state. |
| S-110-13 | Width preset Phone: album placeholders use `number_albums_per_row_mobile` columns; Wide (2560) shows 13 per row at min 175. |
| S-110-14 | One album, 1440 px window, min 175: one 183 px tile, six empty slots; the tile does not widen to fill the row (same in the preview with Albums = 1). |
| S-110-15 | 2× screen: album tile covers request `small2x`; 1× screen: `small`. |
| S-110-16 | Admin on an album opens Spotlight, picks "Adjust gallery sizes (live)": drawer opens, gallery stays visible and scrollable. |
| S-110-17 | In the drawer, admin changes min width, gap and masonry width: album and photo grids re-lay out at once, photo layout switches to masonry; drag selection boxes match the tiles. |
| S-110-18 | Drawer closed with unsaved values: gallery returns to the stored values and the layout shown before opening. |
| S-110-19 | Drawer Save: as S-110-09; the gallery keeps the values after the drawer closes. |
| S-110-20 | Album with its own aspect ratio: changing the default aspect ratio in the drawer leaves its child tiles unchanged; on the root gallery they change. |
| S-110-21 | Drawer open, admin navigates album → root gallery: drawer stays open, values stay applied; navigating to Settings closes it and discards. |
| S-110-22 | Default `album_thumb_gap` 16: gaps 4 px at 390, 8 px at 700, 16 px at 1440 (unchanged from before the setting). |
| S-110-23 | `album_thumb_gap` 30 at 1440, min 175: 6 columns of 204.5 px, 30 px gaps; at 0: 7 columns of 196.7 px touching. |
| S-110-24 | Live drawer: changing the album gap moves the root grid and the smart-album panel together; drag selection follows. |
| S-110-25 | Drawer on the root gallery shows only Albums; inside an album with photos and no sub-albums only Photos; on the timeline only Photos; an empty album shows the note. |
| S-110-26 | Double-click on "Minimum tile width" after editing it to 300: back to the stored 175, unsaved marker cleared if nothing else changed; on a layout-specific label the shown layout switches to it. |

## Test Strategy
- **REST API:** `tests/Feature_v3/Gallery/AlbumThumbGapConfigTest.php` (default 16, 0 accepted, `-1`/`abc` rejected, v8 visibility) and `tests/Feature_v3/Gallery/AlbumThumbMinWidthConfigTest.php` (red first, pattern of `PanZoomConfigTest`): default 175 in `Gallery::Init`; updated value; `0` and `abc` rejected; hidden from `Settings` without `features.v8`, listed with it. No migration test (owner rule).
- **Frontend (v8, Vitest):**
  - `albumTileWidth.test.ts`: min-width rule, exact-fit boundary, stretch bound, independence from album count, below-`sm` branch, Appendix A rows (S-110-01 … 03, S-110-13, S-110-14).
  - `AlbumThumbVirtual.test.ts` (or source guard): covers mount `Thumb` without `type` (S-110-15).
  - `gallerySizes.test.ts`: parse from `ConfigCategoryResource`, clamp, changed-keys payload (FR-110-05, FR-110-10).
  - `galleryPreviewPlaceholders.test.ts`: determinism, counts, ratio mix (NFR-110-03).
  - `galleryPreviewGeometry.test.ts`: equals direct `computeAlbumTileGeometry` / `computeBucketLayout` calls; presets, Phone branch, scale (NFR-110-01, FR-110-09).
  - `useGallerySizesDraft.test.ts`: load, dirty, revert, save success (payload + store updates FR-110-11), save failure (S-110-09 … 11).
  - `gallerySizesLive.test.ts`: effective tile width / aspect ratio / photo config follow the override, album's own ratio wins, close restores layout (FR-110-14, FR-110-15).
  - Spotlight action gating test (FR-110-13).
- **Browser:** scratch instance, S-110-04 … 21, NFR-110-02 timing, keyboard pass.
- **Contracts:** `lychee.d.ts` regenerated (`InitConfig`).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-110-01 | Config `album_thumb_min_width` (migration `2026_11_07_000001_add_album_thumb_configs.php`, with DO-110-05): `Gallery`, `positive`, default `175`, `is_expert` false, level 0, order after `number_albums_per_row_mobile`; in `V8_CONFIGS`. | migration, `SettingsController`, `InitConfig`, `LycheeState` |
| DO-110-05 | Config `album_thumb_gap`: `Gallery`, `int`, default `16`, `is_expert` false, level 0, order 26; in `V8_CONFIGS`. | migration, `SettingsController`, `InitConfig`, `LycheeState` |
| DO-110-02 | Placeholder set `{ albums: { id, title }[], photoRatios: number[] }` from `(seed, albumCount, photoCount)`; ratio mix 3:2, 2:3, 4:3, 3:4, 1:1, 16:9, 9:16, 3:1, weighted towards 3:2 and 4:3. | `v8/utils/galleryPreviewPlaceholders.ts` |
| DO-110-03 | `GallerySizes`: `album_thumb_min_width`, `album_thumb_gap`, `default_album_thumb_aspect_ratio` (`AspectRatioType`), `number_albums_per_row_mobile` (1–3), `photo_layout_gap`, `photo_layout_justified_row_height`, `photo_layout_masonry_column_width`, `photo_layout_grid_column_width`, `photo_layout_square_column_width`. | `v8/utils/gallerySizes.ts` |
| DO-110-04 | Live override: Pinia store holding `GallerySizes \| null` and the layout shown before opening; read by `useAlbumTileWidth`, the album aspect-ratio composable and the photo layout config composable. | `v8/composables/gallerySizes/` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-110-01 | REST GET `/api/v2/Gallery::Init` | Adds `album_thumb_min_width` and `album_thumb_gap`. | `InitConfig` |
| API-110-02 | REST (existing) `GET /api/v2/Settings`, `POST /api/v2/Settings::setConfigs` | Load and save from page and drawer. | No new endpoint. |
| API-110-03 | Web GET `/admin/gallery-sizes` | Serves the SPA (`VueController`, `migration:complete`, `login_required:always`). | `routes/web_v2.php` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-110-01 | Clean | Values = stored; Save disabled. |
| UI-110-02 | Dirty | Any value differs; dot shown; Save and Revert enabled. |
| UI-110-03 | Saving | Save pressed; buttons disabled until the response. |
| UI-110-04 | Load error | Settings could not be loaded; controls disabled, toast. |
| UI-110-06 | Live | Drawer open; gallery reads the override. |

## Telemetry & Observability
None: front-end page, drawer and one config; Lychee has no client telemetry for settings pages.

## Documentation Deliverables
- Roadmap row (Feature 110).
- Knowledge map: min-width rule in `albumTileWidth.ts`, page and drawer reusing the layout functions, live override.
- ADR-110-01.
- Setting description in `lang/*/all_settings.php`.

## Fixtures & Sample Data
None (placeholders are generated).

## Spec DSL

```
domain_objects:
  - id: DO-110-01
    name: album_thumb_min_width
    fields:
      - name: value
        type: integer
        constraints: ">= 1, default 175"
  - id: DO-110-02
    name: PreviewPlaceholderSet
    fields:
      - name: albums
        type: "{id,title}[]"
        constraints: "1..100"
      - name: photoRatios
        type: number[]
        constraints: "1..500"
  - id: DO-110-03
    name: GallerySizes
  - id: DO-110-04
    name: GallerySizesLiveOverride
routes:
  - id: API-110-01
    method: GET
    path: /api/v2/Gallery::Init
  - id: API-110-03
    method: GET
    path: /admin/gallery-sizes
ui_states:
  - id: UI-110-01
    description: Clean
  - id: UI-110-02
    description: Dirty (unsaved changes)
  - id: UI-110-03
    description: Saving
  - id: UI-110-04
    description: Load error
  - id: UI-110-06
    description: Live drawer open
```

## Appendix

### A. Today's album column counts vs a minimum width

Container = viewport − 48 px padding − 15 px scrollbar, nav closed, gap by breakpoint (`GAP_PX`). "cols@px" = columns and stretched tile width.

| Viewport | Today (`TILE_WIDTH_FORMULA`) | min 160 | min 175 | min 180 | min 200 |
|----------|------------------------------|---------|---------|---------|---------|
| 768 | 4@164 | 4@164 | 3@224 | 3@224 | 3@224 |
| 1024 | 5@179 | 5@179 | 5@179 | 4@228 | 4@228 |
| 1280 | 6@190 | 7@160 | 6@190 | 6@190 | 5@231 |
| 1440 | 6@216 | 7@183 | 7@183 | 7@183 | 6@216 |
| 1920 | 9@192 | 10@171 | 9@192 | 9@192 | 8@218 |
| 2560 | 23@93 | 14@164 | 13@177 | 12@193 | 11@212 |
| 3840 | 24@142 | 21@165 | 19@184 | 19@184 | 17@207 |

Sweep 640–2559 px, share of widths with the same column count as today: 160 → 38 %, 170 → 58 %, **175 → 59 %** (87 % within one column), 180 → 55 %, 200 → 23 %. 2560 CSS px is what 4K at 150 % and 5K at 200 % report; today's `6xl` fraction (0.04 vw) gives them 93–142 px tiles. Script: scratchpad `tiles.mjs`, `sweep.mjs` (session 2026-11-07).

### B. Cover resolution on high-density screens (Q-110-04)

`small` is `small_max_height` (default 360) tall. A 183 px tile on a 2× screen needs 366 device px: landscape covers about right, portrait covers (`small` 240 px wide) and 2:3 tiles (275 px tall → 549 device px) upscaled 1.5×. `small2x` (720 px) covers tiles up to 360 CSS px on 2× screens.
