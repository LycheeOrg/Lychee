# Feature 095 – Scrubber Label Formats

| Field | Value |
|-------|-------|
| Status | Implemented 2026-10-16 (drift gate in plan) |
| Last updated | 2026-10-16 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #095 |
| Decisions | — (Q-095-01 … 09 resolved in [open-questions.md](open-questions.md)) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Feature 094 made the date scrubber rail label the unit the data spans, with four fixed formats (`Y`, `M`, `d/m`, `H:00`, N-094-01). This feature makes those four formats admin settings, makes sub-album rails format their lens with the album day format, and fixes two defaults: the ambiguous `g:i` of the hour section headers and the year-less `j M` of the album day format.

### Label inventory

| ID | Where | Shown | Format source before → after |
|----|-------|-------|------------------------------|
| L-095-01 | Rail resting labels, `/timeline` and albums | year | `Y` constant → `date_scrubber_format_year` |
| L-095-02 | Rail resting labels | month | `M` constant → `date_scrubber_format_month` |
| L-095-03 | Rail resting labels | day | `d/m` constant → `date_scrubber_format_day` |
| L-095-04 | Rail resting labels, `/timeline` only | hour | `H:00` constant → `date_scrubber_format_hour` |
| L-095-05 | Lens and pill, `/timeline` at year / month / day | bucket label | server `timeline_photo_date_format_year` / `_month` / `_day`, defaults `Y`, `M Y`, `j M Y` (unchanged) |
| L-095-06 | Lens and pill, `/timeline` at hour | day + hour, or hour only on one day | `timeline_photo_date_format_day` + `H:00` constant → `timeline_photo_date_format_day` + `date_scrubber_format_hour` |
| L-095-07 | Lens and pill, album photo rails | day | `timeline_photo_date_format_day` (unchanged) |
| L-095-08 | Lens and pill, sub-album rails | day | `timeline_photo_date_format_day` (`j M Y`) → `timeline_album_date_format_day`, default `j M` → `j M Y` (Q-095-07) |
| L-095-09 | Section headers (grid) | per bucket | server formats; defaults `timeline_photo_date_format_hour` `g:i` → `H:00` and `timeline_album_date_format_day` `j M` → `j M Y`; hour headers of a listing spanning several days use the new `timeline_photo_date_format_hour_multi_day` (`j M Y H:00`, FR-095-08) |

Supporter Edition settings (`level 1`) are not listed in the admin Settings of a non-SE instance (`SettingsController::getAll`, unless `enable_se_preview`), and saving one there fails with 422 (`ConfigKeyRequireSupportRule`). The backend always serves the stored value: an instance that stops being SE keeps the formats it set, as it does for the other timeline formats.

All formats are PHP `date()` strings: the server applies them with `date()`, the browser with `phpDateFormat()`, which implements the same letters. Labels are rendered as text (Vue interpolation), so a format string cannot inject markup.

## Goals

- G1 – The admin sets the rail's resting label formats for year, month, day and hour (L-095-01 … 04); the hourly lens and pill use the same hour format.
- G2 – Defaults reproduce Feature 094 exactly.
- G3 – Settings sit with the other timeline date formats.
- G4 – The day order (`DD/MM`) is free to change on every instance; the other three formats are Supporter Edition, like the other timeline formats.
- G5 – Every pill matches the section headers of its own view (L-095-08).
- G6 – Hour section headers are unambiguous: the hour alone when every photo is on one day, the date and hour otherwise; sub-album day labels carry the year (L-095-08, L-095-09).
- G7 – Section headers always match the server's buckets: distinct buckets never merge because their labels are equal (FR-095-09).

## Non-Goals

- N-095-01 – A format editor with live preview; settings use the existing `StringField`.
- N-095-02 – Per-user or per-album formats.
- N-095-03 – Separate lens/pill formats (Q-095-01 → A): the lens keeps the server labels and the day formats.
- N-095-04 – Server-side validation of format letters beyond `STRING_REQ`, and length validation (Q-095-03 → A clips instead).
- N-095-05 – v7.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-095-01 | Four new configs in category `Mod Timeline`, `STRING_REQ`, each with the `datetime.format.php` link in its details and the note that the rail is 64 px wide and longer labels are cut: `date_scrubber_format_year` = `Y`, `date_scrubber_format_month` = `M`, `date_scrubber_format_hour` = `H:00` at `level 1` and listed in `ConfigIntegrity::SE_FIELDS`; `date_scrubber_format_day` = `d/m` at `level 0`, not in `SE_FIELDS`. Descriptions and details in every `lang/<locale>/all_settings.php`, translated into each locale like the existing keys (AI-generated translations, as in the repo's translation commits). Settings-page `order`: year 23, month 24, day 25, hour 26, right after the quick-access date formats (19–22); no existing row moves (Q-095-06). | Admin on a free instance sets the day format to `j.n.` → day labels `14.10.` on `/timeline` and albums. | Empty value → 422 (`STRING_REQ`). | Non-SE instance → the year, month and hour settings are not listed and saving them returns 422 (`ConfigKeyRequireSupportRule`); the day setting is listed and saved. | none | User request; Q-095-01, Q-095-02 |
| FR-095-02 | `InitConfig` serves `date_scrubber_format_year`, `_month`, `_day`, `_hour` and `date_scrubber_album_label_format` (from `timeline_album_date_format_day`); `LycheeState` holds them with the defaults of FR-095-01 and `j M Y`. | Defaults → rail identical to Feature 094 (every EX-094-* row). | — | — | none | G2, Q-095-04 |
| FR-095-03 | `railTicks.ts` takes the formats as a parameter (`TickFormats = { year, month, day, hour }`) instead of the `TICK_FORMATS` / `DAY_TICK_FORMAT` / `HOUR_TICK_FORMAT` constants, which are removed. `TimelineDatesV3.vue` reads them from `LycheeState`. | — | — | — | none | FR-095-01 |
| FR-095-04 | The hourly lens and pill (L-095-06) use `date_scrubber_format_hour` for the hour part, in both modes (with the day, and hour only). | Hour format `G\h` → lens `14 Oct 2026 15h`; one day → `15h`. | — | — | none | Q-095-01 |
| FR-095-05 | Resting labels are clipped to the rail's inner width (52 px: `max-w-13`, `overflow-hidden`, no ellipsis), so they never cover the grid, in LTR and RTL. Label alignment is unchanged from Feature 094 (the rail is `dir="ltr"`), so the clipped part is the end of the text. Label and dot culling (FR-094-04/06) are unchanged. | `l j F Y` → the label shows its first ~7 characters inside the rail. | — | — | none | Q-095-03 |
| FR-095-06 | Sub-album rails format their lens and pill days with `date_scrubber_album_label_format` (`timeline_album_date_format_day`); photo rails keep `date_scrubber_label_format` (`timeline_photo_date_format_day`). | `timeline_album_date_format_day = d.m.Y` → sub-album pill `14.10.2026`, matching the sub-album day headers. | — | — | none | Q-095-04 |
| FR-095-07 | Migration fixing two defaults on rows that still hold them, other values left alone: `timeline_photo_date_format_hour` `g:i` → `H:00`, `timeline_album_date_format_day` `j M` → `j M Y` (Q-095-05, Q-095-07). Rollback turns `H:00` back into `g:i` and `j M Y` into `j M`. The migration then clears the application cache (`Artisan::call('cache:clear')` in a try/catch, as the `bump_version*` migrations do), because the settings-controller flush of FR-092-13 does not run for a direct DB update and cached listings would keep `6:00` labels. | Instance on the defaults → hour headers `18:00` instead of `6:00`; sub-album day headers and pill `14 Oct 2026` instead of `14 Oct`. | Customised value (`h A`, `d.m.`) → unchanged. | — | none | Q-095-05, Q-095-07; Feature 092 backlog |
| FR-095-08 | Hour headers by span (Q-095-08): `BucketScheme::labels()` formats hour ids (precision 4) with `timeline_photo_date_format_hour` (**same-day** format, default `H:00`) when every hour id of the list is on the same day, and with the new config `timeline_photo_date_format_hour_multi_day` (**multi-day** format, default `j M Y H:00`, category `Mod Timeline`, `STRING_REQ`, level 1, in `SE_FIELDS`, order 18 so it follows the hour format) otherwise. The decision is per listing (the whole `buckets` tier: `/timeline`, album, tag, person and smart album photo listings), not per header. Year/month/day ids are unaffected. A change of the new config flushes every cached photo listing (`PHOTO_LISTING_COARSE_FLUSH_CONFIGS`, FR-092-13). Lang entries in every locale. | Album, hour granularity, photos on 2026-10-14 only → `18:00`, `15:00`; photos on 2026-10-13 and 2026-10-14 → `13 Oct 2026 22:00`, `14 Oct 2026 01:00`. | Empty value → 422. | — | none | Q-095-08 |
| FR-095-09 | `computeBucketBoundaries()` (`albumBucketBoundaries.ts`) merges adjacent buckets only when both labels are `unknown` (the root shared scope, where a guest sees every owner as `unknown`); buckets with any other equal labels stay separate sections. | Four hour buckets labelled `10:00` on four days → four sections, the rail is offered. | Root shared scope as a guest → one `unknown` section, as before. | — | none | Q-095-08 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-095-01 | No new dependency. | AGENTS.md | `package.json`, `composer.json` unchanged. | — | AGENTS.md |
| NFR-095-02 | `lychee.d.ts` regenerated; the diff is limited to the five `InitConfig` fields. | Typed frontend | `php artisan typescript:transform`. | — | — |
| NFR-095-03 | Changing a rail format takes effect on the next full page load, with no recompute and no listing flush: rail formats are applied in the browser, and `Gallery::Init` has no server cache (its client copy lives in memory only). | Feature 092 | Browser check S-095-02. | — | FR-092-13 |

## UI / Interaction Mock-ups

Admin Settings → Timeline (`[SE]` = the existing SE tag). The new rows follow the quick-access date formats (order 23–26, Q-095-06). On a non-SE instance only the day row is listed.

```
 Timeline
 ┌──────────────────────────────────────────────────────────────────────┐
 │ Quick access date format at hour granularity        [ …            ] │
 │ ──────────────────────────────────────────────────────────────────── │
 │ Date scrubber label at year granularity      [SE]   [ Y            ] │
 │ Date scrubber label at month granularity     [SE]   [ M            ] │
 │ Date scrubber label at day granularity              [ d/m          ] │
 │ Date scrubber label at hour granularity      [SE]   [ H:00         ] │
 │   See datetime.format.php. The rail is 64 px wide: longer labels     │
 │   are cut.                                                           │
 └──────────────────────────────────────────────────────────────────────┘
```

Rail with day format `j.n.` and hour format `G\h`, and a too-long day format `l j F` (clipped):

```
┌──────┐      ┌──────┐      ┌──────┐
│ 28.10│      │  18h │      │Wednes│
│   •  │      │      │      │   •  │
│ 21.10│      │▶ 15h │      │Wednes│
│▶14.10│      │  11h │      │▶Wedne│
└──────┘      └──────┘      └──────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-095-01 | Defaults → every Feature 094 Example Catalogue row unchanged (FR-095-02, FR-095-03). |
| S-095-02 | Free instance, day format `j.n.` → `/timeline` and album rails show `14.10.`; takes effect on reload (FR-095-01, NFR-095-03). |
| S-095-03 | SE instance, hour format `G\h` → rail `15h`, hourly lens `14 Oct 2026 15h`, one-day lens `15h` (FR-095-04). |
| S-095-04 | Empty format → 422, value unchanged (FR-095-01). |
| S-095-05 | Day format `l j F Y` → labels clipped inside the rail, no overlap with the grid, LTR and RTL (FR-095-05). |
| S-095-06 | Non-SE instance → year, month, hour settings not listed, saving them returns 422; day setting listed and saved (FR-095-01). |
| S-095-07 | `timeline_album_date_format_day = d.m.Y` → sub-album rail pill `14.10.2026`; photo rail pill keeps `timeline_photo_date_format_day`; with the defaults, sub-album pill `14 Oct 2026` (FR-095-06, FR-095-07). |
| S-095-08 | Migration on `g:i` → `H:00` and `j M` → `j M Y`; on `h A` / `d.m.` → unchanged; rollback restores `g:i` and `j M`; application cache cleared (FR-095-07). |
| S-095-10 | Settings page → the four rows follow the quick-access date formats, year … hour (FR-095-01). |
| S-095-11 | Hour granularity, one day of photos → headers `18:00`; two days → `13 Oct 2026 22:00` … (FR-095-08). |
| S-095-12 | Album with one photo at 10:00 on each of four days, hour granularity → four headers, the rail is shown (FR-095-08, FR-095-09). |
| S-095-13 | Root albums listing as a guest, shared scope → one `unknown` section (FR-095-09). |
| S-095-09 | `InitConfig` returns the five fields with their defaults and with updated values (FR-095-02). |

## Test Strategy

- **Feature_v3 (`BaseApiWithDataTest`):** extend `DateFormatsInitTest` with the five fields (defaults, updated values) — S-095-09; settings update rejects an empty format — S-095-04; non-SE save of a year/month/hour format → 422, of the day format → 200 — S-095-06.
- **Migration:** a test of the FR-095-07 update (`g:i` → `H:00`, custom value kept) — S-095-08.
- **Static:** `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`.
- **Browser:** `run-lychee` with the Feature 094 seed — S-095-01 … 03, 05 … 07; the Node helper check of Feature 094 rerun with default formats.

## Interface & Contract Catalogue

| Surface | Change |
|---------|--------|
| `configs` | + `timeline_photo_date_format_hour_multi_day` (`j M Y H:00`, level 1, SE, order 18); + `date_scrubber_format_year` / `_month` / `_hour` (level 1, SE), `date_scrubber_format_day` (level 0), orders 23–26; defaults `timeline_photo_date_format_hour` `g:i` → `H:00`, `timeline_album_date_format_day` `j M` → `j M Y` |
| `ConfigIntegrity::SE_FIELDS` | + `date_scrubber_format_year`, `_month`, `_hour` |
| `InitConfig` / `LycheeState` | + `date_scrubber_format_year`, `_month`, `_day`, `_hour`, `date_scrubber_album_label_format` |
| `railTicks.ts` | `TickFormats` parameter; constants removed |
| `BucketScheme::labels()` | same-day / multi-day hour format by span (FR-095-08) |
| `albumBucketBoundaries.ts` | merge only `unknown` labels (FR-095-09) |
| `SettingsController::PHOTO_LISTING_COARSE_FLUSH_CONFIGS` | + `timeline_photo_date_format_hour_multi_day` |
| `TimelineDatesV3.vue`, `Timeline.vue`, `albumDateScrubber.ts` | pass the formats; sub-album lens uses the album day format |
| `lang/<locale>/all_settings.php` | descriptions and details for the four keys, translated per locale |

## Telemetry & Observability

None.

## Documentation Deliverables

- Feature 094 spec: N-094-01 points to this feature.
- Feature 092 plan: backlog item "Default `timeline_photo_date_format_hour`" closed.
- Knowledge map: `InitConfig` date-format list.
- Roadmap row #095.
