# Open Questions – Feature 088

Open questions for [Feature 088](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-088-01~~ | 088 – Frame overlays | High | Where are the overlay settings set and stored? (A admin settings in the Frame category, one look for every frame · B admin defaults plus per-device overrides in a panel inside the Frame · C per-device URL query parameters only) | Resolved (Options A and C: admin settings are the defaults, URL query parameters override them per device; owner, 2026-10-13; spec FR-088-13, FR-088-15, ADR-088-01) | 2026-10-10 | 2026-10-13 |
| ~~Q-088-02~~ | 088 – Frame overlays | High | Which frontends get the overlays? (A v8 only, v7 Frame unchanged · B v7 and v8) | Resolved (Option A, v8 only; owner, 2026-10-13; spec Overview, Non-Goals) | 2026-10-10 | 2026-10-13 |
| ~~Q-088-03~~ | 088 – Frame overlays | High | "Random with sub-albums" does not exist today: album frames use the album's direct photos only. Which frames offer the album line? (A new setting making album frames include sub-albums, album line in fully random and that mode · B no new mode, album line in fully random and on tag/smart album frames · C no new mode, album line in fully random only) | Resolved (Option A, `mod_frame_include_subalbums`; owner, 2026-10-13; spec FR-088-06, FR-088-16, FR-088-17) | 2026-10-10 | 2026-10-13 |
| ~~Q-088-04~~ | 088 – Frame overlays | Medium | Free or Supporter Edition? (A free · B SE only · C clock free, info box SE) | Resolved (Option A, free; owner, 2026-10-13; spec FR-088-13) | 2026-10-10 | 2026-10-13 |
| ~~Q-088-05~~ | 088 – Frame overlays | Medium | Both boxes in the same corner, and the Back button's corner (A boxes stack, the box in the Back button's corner sits below it · B the same corner for both boxes is rejected, Back button as A · C Back button hides after 3 s without pointer movement, boxes stack) | Resolved (Option A; owner, 2026-10-13; spec FR-088-12) | 2026-10-10 | 2026-10-13 |

## Question Details

### ~~Q-088-01~~ – Where the overlay settings live

**Context:** The Frame has three settings, all admin settings in the `Mod Frame` category of the `configs` table (`mod_frame_enabled`, `mod_frame_refresh`, `random_album_id`, plus `hide_nsfw_in_frame` under NSFW). The config system has a `color` type (theme colours, `landing_hero_text_color`), a bounded `int:0:100` type (`landing_hero_text_opacity`) and enum types rendered by `ConfigGroup.vue`. The feature adds about 17 values (two boxes × toggles, time format, position, background colour, background opacity, box opacity). A frame often runs on a dedicated device (wall tablet, TV) opened by a guest or a kiosk account.

- **Option A:** Admin settings in the existing Frame category. `GET /Frame` returns them with the photo; every frame on the instance looks the same.
  - ✅ No new UI: the settings page renders the existing `0|1`, `color`, percent and enum types.
  - ✅ Works for guests and kiosk devices without anything to set on the device.
  - ❌ Two frames on one instance cannot look different.
- **Option B:** Admin defaults as in A, plus a settings panel inside the Frame view whose overrides are kept in the device's browser storage.
  - ✅ Each device can differ (a TV across the room vs a tablet on a desk).
  - ❌ Two sets of controls to build and test, and a panel to open on a fullscreen kiosk view.
  - ❌ Browser storage can be cleared or blocked; the device then falls back silently.
- **Option C:** Per-device only: the Frame reads query parameters (for example `/frame?clock_position=bottom_right&info_exif_enabled=1`), so each device bookmarks its own URL.
  - ✅ Per-device, no storage, no new settings rows.
  - ❌ No UI: about 17 parameters to type by hand, easy to get wrong, hard to discover.

**Resolution:** Options A and C together. The admin settings are the defaults; each URL query parameter, named after its setting without the `mod_frame_` prefix, overrides that setting on the device that opens the URL. Recorded in [spec.md](spec.md) FR-088-13 and FR-088-15, and in ADR-088-01 (overrides merged in the browser, the response carries every part the privacy rules allow).

### ~~Q-088-02~~ – Frontend scope

**Context:** `resources/js/v7/views/gallery-panels/Frame.vue` and `resources/js/v8/views/gallery-panels/Frame.vue` are near-identical copies on the same `GET /api/v2/Frame` endpoint. v8 is replacing v7; refactors default to v8 only, new UI features are decided per feature.

- **Option A:** v8 only. The v7 Frame stays as it is and ignores the new response fields.
  - ✅ Half the frontend work and tests.
  - ✅ Consistent with v8 replacing v7.
  - ❌ v7 users do not get the overlays.
- **Option B:** v7 and v8. The overlay component is written for each copy.
  - ✅ Same Frame for every user.
  - ❌ Two copies to build and keep in step, for a frontend that is being retired.

**Resolution:** Option A. Recorded in [spec.md](spec.md) Overview and Non-Goals.

### ~~Q-088-03~~ – Which frames offer the album line

**Context:** Album frames (`/frame/{albumId}`, and `/frame` when `random_album_id` is set) draw from `Album::photos()`, the album's direct photos only; the Frame button in the album hero is shown only when the album has direct photos. The fully random frame (`/frame` with no `random_album_id`) draws from every searchable photo. `Album::all_photos()` (`HasManyPhotosRecursively`) returns the photos of an album and its descendants with the viewer's searchability filter applied. Tag and smart albums (Recent, Highlighted, …) can mix photos from several albums.

- **Option A:** Add an admin setting "Album frames include sub-albums" (default off). When on, album frames draw from `all_photos()` and the album hero shows the Frame button when the album or any descendant has a photo. The album line is offered in the fully random frame and in album frames with this setting on.
  - ✅ The album line appears exactly where photos come from several albums.
  - ✅ The recursive relation and its access filters already exist.
  - ❌ Widens the feature with a new pool mode and a change to the hero's Frame button rule.
- **Option B:** No new mode. The album line is offered in the fully random frame and on tag and smart album frames.
  - ✅ No change to which photos a frame shows.
  - ❌ Not what "random with sub-albums" names; regular albums never show it.
- **Option C:** No new mode. The album line is offered in the fully random frame only.
  - ✅ Smallest scope.
  - ❌ Drops half of the request.

**Resolution:** Option A, as the admin setting `mod_frame_include_subalbums` (named like `map_include_subalbums`). Recorded in [spec.md](spec.md) FR-088-06, FR-088-16 and FR-088-17.

### ~~Q-088-04~~ – Free or Supporter Edition

**Context:** The Frame is a free module. Settings reserved to Supporter Edition use `level = 1` in `configs` and are ignored without a valid SE licence (for example the rating smart albums). Precedents: the landing page kept its classic layout free and made the new layouts SE; Insights is SE with a preview.

- **Option A:** Free for every instance (`level = 0`).
  - ✅ Small extension of a free module, no gating code.
  - ❌ No SE incentive.
- **Option B:** SE only (`level = 1`). Without SE the Frame looks as it does today.
  - ✅ Adds a visible SE benefit.
  - ❌ Gating in the controller and the settings page, plus tests for both editions.
- **Option C:** Clock box free, info box (title, albums, EXIF) SE.
  - ✅ The basic clock stays free.
  - ❌ Gating code as in B, and a split that is harder to explain.

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-088-13.

### ~~Q-088-05~~ – Shared corners and the Back button

**Context:** The clock box offers five positions (four corners and center); the info box offers the four corners. Both can be set to the same corner. The Frame's Back button (`GoBack`) is always visible in the top-left corner (top-right in right-to-left languages), so a box in that corner would overlap it.

- **Option A:** Both boxes may share a corner and stack in one column, clock box first. A box in the Back button's corner is placed below the button.
  - ✅ Every combination of settings renders without overlap.
  - ✅ The Back button keeps its current behaviour.
  - ❌ Stacked boxes take more vertical space in that corner.
- **Option B:** Saving the same corner for both boxes is rejected by validation. Back button handled as in A.
  - ✅ One box per corner, simple layout.
  - ❌ Settings are saved one at a time, so moving both boxes needs an intermediate position; an error the admin has to work around.
- **Option C:** As A, and the Back button hides after 3 s without pointer movement or touch, coming back on the next one.
  - ✅ A cleaner kiosk display.
  - ❌ Changes the Back button's current behaviour, which was not requested.

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-088-12.
