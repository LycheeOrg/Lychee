# Feature 088 – Frame Overlays

| Field | Value |
|-------|-------|
| Status | Ready for implementation |
| Last updated | 2026-10-13 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #088 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The Frame (`/frame/{albumId?}`) shows one random photo at a time, fullscreen, refreshed every `mod_frame_refresh` seconds from `GET /api/v2/Frame`. This feature adds two optional boxes over the photo: a **clock box** (current time and date) and an **info box** (photo title, the albums holding the photo, EXIF data), each with its own position, background colour, background opacity and box opacity. Admin settings in the Frame category are the defaults; URL query parameters override them per device (ADR-088-01). A new admin setting lets album frames draw from sub-albums too, which is where the album line is offered. Affected layers: settings (`configs` migration, `lang/*/all_settings.php`), `FrameController`, `FrameData` and `AlbumConfig` on REST v2, and the v8 Frame view. The v7 Frame view is unchanged (Q-088-02); the server-side photo selection (FR-088-16, FR-088-17) applies to both frontends.

## Goals
- A clock box showing the current time (12- or 24-hour) and/or the date, each part switchable, positioned in a corner or the center.
- An info box showing the photo title, the photo's albums and EXIF data, each part switchable, positioned in a corner.
- EXIF in three switchable groups: exposure (focal length, aperture, shutter speed, ISO), camera (camera and lens), capture date (`taken_at`).
- Background colour, background opacity and box opacity per box.
- Every value set by the admin and overridable per device through the Frame URL.
- Album frames optionally drawing from the album and its sub-albums.
- With the default settings, the Frame looks and behaves as it does today.

## Non-Goals
- The v7 Frame view (Q-088-02).
- Text colour, font, or font size settings.
- Other photo data: description, tags, location, GPS coordinates, rating, licence.
- A settings panel inside the Frame view.
- Overriding `mod_frame_include_subalbums` from the URL: it changes which photos the server picks.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-088-01 | The clock box shows the current time when the time part is on. | Hours and minutes from the device clock, in the 12-hour (with day period) or 24-hour format chosen by `clock_time_format`, refreshed at each minute boundary. | – | – | – | Owner request |
| FR-088-02 | The clock box shows the current date when the date part is on. | Weekday, day, month and year from the device clock, below the time when both are on. Time and date use `Intl.DateTimeFormat` in the visitor's browser locale when the Lychee language is English, and in the Lychee language otherwise (the rule of `phpDateFormat.ts`). The box is not rendered when both parts are off. | – | – | – | Owner request |
| FR-088-03 | The clock box position is one of `top_left`, `top_right`, `bottom_left`, `bottom_right`, `center`. | Positions are physical: `top_left` is the top-left corner in right-to-left languages too. `center` places the box in the middle of the screen, over the photo. | Any other value is rejected when the setting is saved. | – | – | Owner request |
| FR-088-04 | Each box has a background colour, a background opacity (0–100 %) and a box opacity (0–100 %). | The background is the colour at the background opacity; the box opacity applies to the whole box (text and background). An empty colour shows black. | Colour must be empty or `#rrggbb`; opacities integers 0–100. | – | – | Owner request |
| FR-088-05 | The info box shows the photo title when the title part is on. | Same rule as the photo view: no title for guests when `file_name_hidden` is on; an empty title is left out. | – | – | – | Owner request |
| FR-088-06 | The info box shows the albums holding the photo when the album part is on, in the frames that offer the album line: the fully random frame (no album, `random_album_id` empty) and album frames in sub-album mode (FR-088-16). | The albums listed are the photo's albums the viewer can browse to (`AlbumQueryPolicy::applyBrowsabilityFilter`, the rule of album search), so hidden albums reachable by link only are left out. Titles sorted by title (`title_base`, `title_index`), separated by ` · `. The line is left out when no album remains. | – | In other frames `albums` is `null` and no line is shown. | – | Q-088-03 |
| FR-088-07 | The info box shows EXIF data when the EXIF part is on, in three groups each switchable. | **Exposure:** focal length, aperture, shutter speed, ISO. **Camera:** make and model, then lens. **Capture date:** `taken_at` formatted with `date_format_photo_overlay`. Exposure and camera values are `null` when `display_exif_data` is off, as in the photo view; the capture date is not (the photo view shows it too). Each missing value is left out; a group with no value is left out. | – | – | – | Owner request |
| FR-088-08 | The info box position is one of `top_left`, `top_right`, `bottom_left`, `bottom_right`. | Physical positions as in FR-088-03. | `center` and any other value are rejected when the setting is saved, and ignored in the URL. | – | – | Owner request |
| FR-088-09 | The info box is not rendered when it has nothing to show. | All parts off, or every enabled part empty for this photo. | – | – | – | Owner request |
| FR-088-10 | `GET /api/v2/Frame` returns the admin values of every overlay setting and the photo information that the privacy rules allow, whatever the overlay switches say. | `clock` and `info` carry the admin values; `photo` carries title, albums, EXIF and capture date subject to FR-088-05 … FR-088-07 only. The switches are applied in the browser after the URL overrides (FR-088-15, ADR-088-01). | – | When no photo is found, `photo` is `null` and the info box is not rendered. | – | ADR-088-01 |
| FR-088-11 | The info box follows the photo on screen; the clock box does not. | The info box sits under the shutter and its content is replaced together with the image, so it fades out and in with the photo. The clock box sits above the shutter and stays visible during transitions. | – | – | – | Owner request |
| FR-088-12 | Boxes sharing a corner, and the Back button's corner. | Boxes set to the same corner stack in one column, clock box first. The corner holding the Back button (top left, top right in right-to-left languages) starts its column below the button. | – | – | – | Q-088-05 |
| FR-088-13 | The overlay settings are admin settings in the Frame category (`Mod Frame`), free on every instance (`level = 0`). | Listed in DO-088-05. Each depends on `mod_frame_enabled` only (`required_keys`), so a box can be styled while it is switched off by default and switched on per device. | Values checked by `Configs::sanity()` against their `type_range`. | – | – | Q-088-01, Q-088-04 |
| FR-088-14 | The default settings leave the Frame as today. | Every switch off except the three EXIF groups (on, so switching EXIF on shows all of it); time format `24h`; backgrounds `#000000` at 50 %; boxes at 100 %; clock box `bottom_right`; info box `bottom_left`; `mod_frame_include_subalbums` off. | – | – | – | Owner request |
| FR-088-15 | URL query parameters on `/frame/{albumId?}` override the admin values on the device that opens the URL. | One parameter per overlay setting, named after the setting without `mod_frame_` (DO-088-05), for example `/frame?clock_time_enabled=1&clock_position=top_right&clock_background_color=1e293b`. Switches take `1` or `0`; positions and time format take their setting's values; colours take six hex digits with or without a leading `#` (written `%23`); opacities take integers 0–100. A parameter that is absent uses the admin value. | A parameter whose value is not allowed is ignored and the admin value applies. | – | – | Q-088-01 |
| FR-088-16 | Sub-album mode: with `mod_frame_include_subalbums` on, frames on a regular album draw from the album and all its sub-albums. | Applies to `/frame/{albumId}` and to `/frame` when `random_album_id` names a regular album. Photos come from `Album::all_photos()` (searchability filter from the album), in random order, videos skipped as today. Tag albums and smart albums are unaffected. | – | An album with no photo in its tree answers as an empty album does today (`PhotoCollectionEmptyException`). | – | Q-088-03 |
| FR-088-17 | In sub-album mode the album's Frame button is shown when the album or any sub-album holds a photo the viewer may see. | `AlbumConfig::$is_mod_frame_enabled` checks `all_photos()` instead of the album's direct photos for regular albums. | – | – | – | Q-088-03 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-088-01 | No network access beyond the Lychee server. | Lychee runs offline. | Time and date come from the device clock and `Intl`; no font, script or service from another host. | – | Owner directive (offline only) |
| NFR-088-02 | No new dependency. | Dependency policy. | `composer.json` and `package.json` unchanged. | – | AGENTS.md |
| NFR-088-03 | No photo data the viewer could not already see. | Privacy. | Albums filtered by browsability; title and EXIF follow `file_name_hidden` and `display_exif_data`. Covered by feature tests. | `AlbumQueryPolicy`, `PhotoResource` rules | ADR-088-01 |
| NFR-088-04 | Readable from across a room on any photo. | A frame is watched from a distance. | White text with a text shadow; font size scales with the viewport (`clamp()`); checked on a light and a dark photo with background opacity 0. | – | Owner request |
| NFR-088-05 | One clock timer at a time. | Frames run for days. | One timeout re-armed for the next minute boundary; cleared when the Frame is left. | – | – |
| NFR-088-06 | At most one extra query per refresh. | Frames refresh every few seconds. | The album line costs one album query, run only in frames that offer it. | – | – |

## UI / Interaction Mock-ups

Clock box at `top_right` with time and date, info box at `bottom_left` with every part on (landscape screen, left-to-right language):

```
+--------------------------------------------------------------------------+
| [<]                                                    +---------------+ |
|                                                        |     14:32     | |
|              +--------------------------------+        | Saturday      | |
|              |                                |        | 10 Oct 2026   | |
|              |                                |        +---------------+ |
|              |          random photo          |                          |
|              |                                |                          |
|              |                                |                          |
|              +--------------------------------+                          |
| +-----------------------------------+                                    |
| | Sunset over the bay               |   <- title                         |
| | Holidays 2024 · Best of           |   <- albums                        |
| | 35 mm  f/2.8  1/250 s  ISO 100    |   <- exposure                      |
| | Fujifilm X-T5 · XF35mmF1.4 R      |   <- camera                        |
| | 12 Aug 2024 19:47                 |   <- capture date                  |
| +-----------------------------------+                                    |
+--------------------------------------------------------------------------+
```

Both boxes at `top_left`, 12-hour time: the column starts below the Back button, clock box first:

```
+--------------------------------------------------------------------------+
| [<]                                                                      |
| +-----------+                                                            |
| |  2:32 PM  |   +--------------------------------+                       |
| +-----------+   |                                |                       |
| +-------------------------+                      |                       |
| | Sunset over the bay     |   random photo       |                       |
| | 35 mm  f/2.8  1/250 s   |                      |                       |
| +-------------------------+                      |                       |
|                 +--------------------------------+                       |
+--------------------------------------------------------------------------+
```

Clock box at `center`, time only, info box off:

```
+--------------------------------------------------------------------------+
| [<]                                                                      |
|              +--------------------------------+                          |
|              |                                |                          |
|              |          +-----------+         |                          |
|              |          |   14:32   |         |                          |
|              |          +-----------+         |                          |
|              |                                |                          |
|              +--------------------------------+                          |
+--------------------------------------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-088-01 | Default settings: the response carries the default `clock` and `info` values; the Frame renders no box. |
| S-088-02 | Admin settings changed: the response carries the new values. |
| S-088-03 | Time on (24h), date off: the clock box shows `14:32` only and changes at the next minute boundary. |
| S-088-04 | Time format `12h`: the clock box shows `2:32 PM` (locale wording). |
| S-088-05 | Time and date on, position `center`: one box in the middle, time above date. |
| S-088-06 | Info position `center` is rejected when saved. |
| S-088-07 | Photo with title and EXIF: the response carries title, focal, aperture, shutter, ISO, make, model, lens and formatted `taken_at`. |
| S-088-08 | Guest with `file_name_hidden` on: `title` is `null`. |
| S-088-09 | `display_exif_data` off: exposure and camera values `null`, `taken_at` still present. |
| S-088-10 | Photo without lens and ISO: those values `null`, the rest of the group shown. |
| S-088-11 | Fully random frame: `albums` lists the photo's albums the viewer can browse to, sorted by title. |
| S-088-12 | Fully random frame, photo also in a hidden (link-only) public album: that album's title is not listed for a guest. |
| S-088-13 | Album frame, sub-album mode off: `albums` is `null`. |
| S-088-14 | Sub-album mode on, album whose only photo is in a sub-album: the frame returns that photo and `albums` lists its album. |
| S-088-15 | Sub-album mode on: the album's Frame button is shown for an album whose photos are all in sub-albums; off: not shown. |
| S-088-16 | Info parts on but every value empty for this photo: no info box. |
| S-088-17 | URL `?clock_time_enabled=1&clock_position=top_right` with admin values off: the clock box shows at the top right on that device only. |
| S-088-18 | URL with an invalid value (`clock_position=middle`, `info_position=center`, `clock_opacity=150`, `clock_background_color=red`): ignored, admin value used. |
| S-088-19 | Both boxes at `top_left`: one column below the Back button, clock box first. |
| S-088-20 | Background colour, background opacity and box opacity applied to the rendered box. |

## Test Strategy
- **REST API (v2), Feature_v3 suite:** `FrameOverlayTest` on `GET /api/v2/Frame` covers S-088-01, S-088-02, S-088-07 … S-088-14. `FrameSubAlbumsTest` covers S-088-14 and S-088-15 (`GET /api/v2/Album::head` config). `FrameTest` (Feature_v2) stays green.
- **Settings:** a Feature_v3 test saving `mod_frame_info_position = center` and an invalid clock position through `POST /api/v2/Settings::setConfigs` covers S-088-06.
- **Unit:** `FramePhotoDataTest` for the photo information builder (missing values, privacy switches) where a feature test would need many fixtures.
- **Frontend:** `npm run check`; S-088-03 … S-088-05 and S-088-16 … S-088-20 checked with Playwright on a scratch instance (mocked clock for the minute rollover and the 12-hour format, light and dark photos).
- **Docs/Contracts:** regenerated TypeScript types for `FrameData` and the new enums.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-088-01 | `FrameData`: `timeout`, `src`, `srcset`, `clock` (DO-088-02), `info` (DO-088-03), `photo` (DO-088-04, nullable). | REST, frontend |
| DO-088-02 | `FrameClockData`: `is_time_enabled`, `time_format` (`FrameTimeFormat`), `is_date_enabled`, `position` (`FrameBoxPosition`), `background_color`, `background_opacity`, `opacity`. | REST, frontend |
| DO-088-03 | `FrameInfoData`: `is_title_enabled`, `is_albums_enabled`, `is_exif_enabled`, `is_exif_exposure_enabled`, `is_exif_camera_enabled`, `is_exif_taken_at_enabled`, `position` (`FrameBoxPosition`), `background_color`, `background_opacity`, `opacity`. | REST, frontend |
| DO-088-04 | `FramePhotoData`: `title`, `albums` (string list or `null` when the frame does not offer the line), `focal`, `aperture`, `shutter`, `iso`, `make`, `model`, `lens`, `taken_at`; each `null` when hidden or missing. | REST, frontend |
| DO-088-05 | Settings (category `Mod Frame`, `level = 0`, `required_keys = mod_frame_enabled`); the URL parameter is the key without `mod_frame_`, except `mod_frame_include_subalbums`, which has none. See the table below. | settings, frontend |
| DO-088-06 | Enums `FrameBoxPosition` (`top_left`, `top_right`, `bottom_left`, `bottom_right`, `center`) and `FrameTimeFormat` (`24h`, `12h`). | settings, REST, frontend |

Settings (DO-088-05):

| Key | Type range | Default |
|-----|-----------|---------|
| `mod_frame_include_subalbums` | `0|1` | `0` |
| `mod_frame_clock_time_enabled` | `0|1` | `0` |
| `mod_frame_clock_time_format` | `24h|12h` | `24h` |
| `mod_frame_clock_date_enabled` | `0|1` | `0` |
| `mod_frame_clock_position` | `top_left|top_right|bottom_left|bottom_right|center` | `bottom_right` |
| `mod_frame_clock_background_color` | `color` | `#000000` |
| `mod_frame_clock_background_opacity` | `int:0:100` | `50` |
| `mod_frame_clock_opacity` | `int:0:100` | `100` |
| `mod_frame_info_title_enabled` | `0|1` | `0` |
| `mod_frame_info_albums_enabled` | `0|1` | `0` |
| `mod_frame_info_exif_enabled` | `0|1` | `0` |
| `mod_frame_info_exif_exposure_enabled` | `0|1` | `1` |
| `mod_frame_info_exif_camera_enabled` | `0|1` | `1` |
| `mod_frame_info_exif_taken_at_enabled` | `0|1` | `1` |
| `mod_frame_info_position` | `top_left|top_right|bottom_left|bottom_right` | `bottom_left` |
| `mod_frame_info_background_color` | `color` | `#000000` |
| `mod_frame_info_background_opacity` | `int:0:100` | `50` |
| `mod_frame_info_opacity` | `int:0:100` | `100` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-088-01 | REST GET /api/v2/Frame | Adds `clock`, `info` and `photo` to the response; sub-album mode for album frames. | `FrameRequest`, `FrameController::get`, `FrameData` |
| API-088-02 | REST GET /api/v2/Album::head (and the other album endpoints carrying `AlbumConfig`) | `config.is_mod_frame_enabled` follows FR-088-17. | `AlbumConfig` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-088-01 | Clock box | Time and/or date on after URL overrides: box at its position, above the shutter, styled per FR-088-04. |
| UI-088-02 | Info box | At least one non-empty part on after URL overrides: box at its position, under the shutter, styled per FR-088-04. |
| UI-088-03 | No boxes | Every part off or empty: the Frame as today. |
| UI-088-04 | Shared corner | Both boxes in one corner: stacked, clock box first, below the Back button in its corner. |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap row 088; knowledge-map entry for the Frame overlays; ADR-088-01.
- Setting descriptions and help texts in `lang/<locale>/all_settings.php` (English text in every locale, the convention for new keys), then `php artisan lang:json`. The help text of `mod_frame_clock_time_enabled` and `mod_frame_info_title_enabled` names the URL parameters.

## Fixtures & Sample Data
Existing photos and albums of `BaseApiWithDataTest` (`album1` with `subAlbum1`, `photo1`, `subPhoto1`, public `album4`); EXIF values set on the photo models in the tests.

## Spec DSL

```
domain_objects:
  - id: DO-088-01
    name: FrameData
    fields:
      - { name: timeout, type: integer }
      - { name: src, type: string }
      - { name: srcset, type: string }
      - { name: clock, type: DO-088-02 }
      - { name: info, type: DO-088-03 }
      - { name: photo, type: DO-088-04, nullable: true }
  - id: DO-088-02
    name: FrameClockData
    fields:
      - { name: is_time_enabled, type: boolean }
      - { name: time_format, type: enum, values: [24h, 12h] }
      - { name: is_date_enabled, type: boolean }
      - { name: position, type: enum, values: [top_left, top_right, bottom_left, bottom_right, center] }
      - { name: background_color, type: string, constraints: "empty or #rrggbb" }
      - { name: background_opacity, type: integer, constraints: "0..100" }
      - { name: opacity, type: integer, constraints: "0..100" }
  - id: DO-088-03
    name: FrameInfoData
    fields:
      - { name: is_title_enabled, type: boolean }
      - { name: is_albums_enabled, type: boolean }
      - { name: is_exif_enabled, type: boolean }
      - { name: is_exif_exposure_enabled, type: boolean }
      - { name: is_exif_camera_enabled, type: boolean }
      - { name: is_exif_taken_at_enabled, type: boolean }
      - { name: position, type: enum, values: [top_left, top_right, bottom_left, bottom_right] }
      - { name: background_color, type: string, constraints: "empty or #rrggbb" }
      - { name: background_opacity, type: integer, constraints: "0..100" }
      - { name: opacity, type: integer, constraints: "0..100" }
  - id: DO-088-04
    name: FramePhotoData
    fields:
      - { name: title, type: string, nullable: true }
      - { name: albums, type: "string[]", nullable: true }
      - { name: focal, type: string, nullable: true }
      - { name: aperture, type: string, nullable: true }
      - { name: shutter, type: string, nullable: true }
      - { name: iso, type: string, nullable: true }
      - { name: make, type: string, nullable: true }
      - { name: model, type: string, nullable: true }
      - { name: lens, type: string, nullable: true }
      - { name: taken_at, type: string, nullable: true }
routes:
  - id: API-088-01
    method: GET
    path: /api/v2/Frame
  - id: API-088-02
    method: GET
    path: /api/v2/Album::head
ui_states:
  - id: UI-088-01
    description: Clock box
  - id: UI-088-02
    description: Info box
  - id: UI-088-03
    description: No boxes
  - id: UI-088-04
    description: Shared corner
```
